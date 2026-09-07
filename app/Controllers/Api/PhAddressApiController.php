<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

/**
 * Address suggestions backed by the `ph_addresses` table (imported once from
 * the `ph-address` npm package's SQLite database -- see
 * App\Database\Seeds\PhAddressSeeder). Powers the autocomplete on the
 * Company Location field in document-records/create.php.
 */
class PhAddressApiController extends BaseController
{
    private const MIN_CHARS = 2;
    private const LIMIT = 15;

    /**
     * GET /api/ph-address/search?q=...
     *
     * Accepts comma-separated refinement the same way ph-address itself
     * does: "barangay", "barangay, city", or "barangay, city, province" --
     * each comma narrows the match further.
     */
    public function search()
    {
        $query = trim((string) $this->request->getGet('q'));

        if (mb_strlen($query) < self::MIN_CHARS) {
            return $this->response->setJSON([]);
        }

        $parts = array_map('trim', explode(',', $query, 3));
        $db = \Config\Database::connect();

        // A plain prefix match ("starts with") was too strict -- e.g. typing
        // "Rizal" found nothing for a barangay named "New Rizal". Match the
        // term anywhere in the name instead, but still rank a starts-with
        // hit above a mid-string one so the more likely match comes first.
        $conditions = ['name LIKE ?'];
        $bindings = ['%' . $db->escapeLikeString($parts[0]) . '%'];

        if (! empty($parts[1])) {
            $conditions[] = 'city_mun_name LIKE ?';
            $bindings[] = '%' . $db->escapeLikeString($parts[1]) . '%';
        }

        if (! empty($parts[2])) {
            $conditions[] = 'prov_name LIKE ?';
            $bindings[] = '%' . $db->escapeLikeString($parts[2]) . '%';
        }

        $where = implode(' AND ', $conditions);
        $bindings[] = $db->escapeLikeString($parts[0]) . '%';
        $bindings[] = self::LIMIT;

        // Barangay-level hits first (the common case for a specific company
        // address), then city/municipality, then province/region.
        $rows = $db->query("
            SELECT id, name, level, city_mun_name, prov_name, reg_name
            FROM ph_addresses
            WHERE {$where}
            ORDER BY
                CASE WHEN name LIKE ? THEN 0 ELSE 1 END,
                FIELD(level, 'Bgy', 'City', 'Mun', 'SubMun', 'SGU', 'Dist', 'Prov', 'Reg'),
                name ASC,
                city_mun_name ASC
            LIMIT ?
        ", $bindings)->getResultArray();

        $suggestions = array_map(static function (array $row): array {
            $segments = [$row['name']];

            if (! empty($row['city_mun_name']) && $row['city_mun_name'] !== $row['name']) {
                $segments[] = $row['city_mun_name'];
            }

            if (! empty($row['prov_name']) && $row['prov_name'] !== end($segments)) {
                $segments[] = $row['prov_name'];
            }

            return [
                'id' => (int) $row['id'],
                'label' => implode(', ', $segments),
            ];
        }, $rows);

        return $this->response->setJSON($suggestions);
    }
}
