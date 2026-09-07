<?php

namespace App\Database\Seeds;

use App\Libraries\XlsxReader;
use CodeIgniter\Database\Seeder;

/**
 * Rebuilds `ph_addresses` from the PSA's official PSGC quarterly publication
 * datafile (downloaded as an .xlsx from https://psa.gov.ph/classification/psgc).
 * That file lists every region/province/city-municipality/barangay as a flat,
 * hierarchically-ordered sheet with a "Geographic Level" column but no parent
 * id -- ancestry is inferred here from row order, matching the same
 * reg_name/prov_name/city_mun_name shape the table already had (originally
 * seeded once from the `ph-address` npm package's bundled SQLite, now
 * superseded by this direct PSA source).
 *
 * To refresh with a newer quarter: download PSA's new
 * "...-Publication-Datafile.xlsx", replace the file at PSGC_FILE (or update
 * that constant to its new name), and rerun -- this always truncates and
 * reimports from scratch.
 *
 * Run with: php spark db:seed PhAddressSeeder
 */
class PhAddressSeeder extends Seeder
{
    private const PSGC_FILE = ROOTPATH . 'PSGC-2Q-2026-Publication-Datafile.xlsx';
    private const SHEET_NAME = 'PSGC';
    private const BATCH_SIZE = 500;

    public function run()
    {
        if (! is_file(self::PSGC_FILE)) {
            echo 'PSGC publication file not found at: ' . self::PSGC_FILE . "\n";
            return;
        }

        $db = \Config\Database::connect();
        $db->table('ph_addresses')->truncate();

        $rows = XlsxReader::rows(self::PSGC_FILE, self::SHEET_NAME);
        array_shift($rows); // header row

        // Rolling ancestry state as we walk the sheet top to bottom.
        $currentReg = null;
        $currentProv = null;
        $currentTopCity = null; // City/Mun filling the province tier (NCR-style: no real province above it)
        $currentParent = null;  // nearest immediate City/Mun/SubMun/Dist/SGU container

        $batch = [];
        $imported = 0;

        foreach ($rows as $cells) {
            $psgc = trim($cells[1] ?? '');
            $name = trim($cells[2] ?? '');
            $code = trim($cells[3] ?? '');
            $level = trim($cells[4] ?? '');

            if ($name === '') {
                continue;
            }

            // A couple of PSA rows ship with a blank Geographic Level: a
            // region-tier placeholder ("Special Geographic Area", for
            // island territories with no region of their own) and a
            // province-tier placeholder for a lone city that isn't part of
            // any province ("City of Isabela (Not a Province)"). Recognize
            // them so rows beneath them still attribute to the right
            // region/province instead of leaking the previous one.
            if ($level === '') {
                $level = (strpos($name, '(Not a Province)') !== false) ? 'Prov' : 'Reg';
            }

            switch ($level) {
                case 'Reg':
                    $ownReg = null;
                    $ownProv = null;
                    $ownCity = null;
                    $currentReg = $name;
                    $currentProv = null;
                    $currentTopCity = null;
                    $currentParent = null;
                    break;

                case 'Prov':
                    $ownReg = $currentReg;
                    $ownProv = null;
                    $ownCity = null;
                    $currentProv = $name;
                    $currentTopCity = null;
                    $currentParent = null;
                    break;

                case 'City':
                case 'Mun':
                    $ownReg = $currentReg;

                    if ($currentProv !== null) {
                        // Standard case: city/municipality under a real province.
                        $ownProv = $currentProv;
                        $ownCity = null;
                        $currentParent = $name;
                    } else {
                        // NCR-style: no province above this city -- it fills
                        // that tier itself so barangays beneath it still get
                        // a sensible, non-null "province" label.
                        $ownProv = $name;
                        $ownCity = null;
                        $currentTopCity = $name;
                        $currentParent = $name;
                    }
                    break;

                case 'SubMun':
                case 'Dist':
                case 'SGU':
                    $ownReg = $currentReg;
                    $ownProv = $currentProv ?? $currentTopCity;
                    $ownCity = $currentParent;
                    $currentParent = $name;
                    break;

                case 'Bgy':
                default:
                    $ownReg = $currentReg;
                    $ownProv = $currentProv ?? $currentTopCity;
                    $ownCity = $currentParent;
                    break;
            }

            $batch[] = [
                'psgc'          => $psgc ?: null,
                'code'          => $code ?: null,
                'name'          => $name,
                'level'         => $level ?: null,
                'city_mun_name' => $ownCity,
                'prov_name'     => $ownProv,
                'reg_name'      => $ownReg,
            ];

            if (count($batch) >= self::BATCH_SIZE) {
                $db->table('ph_addresses')->insertBatch($batch);
                $imported += count($batch);
                $batch = [];
                echo "  {$imported}\r";
            }
        }

        if (! empty($batch)) {
            $db->table('ph_addresses')->insertBatch($batch);
            $imported += count($batch);
        }

        echo "\nImported {$imported} address rows into ph_addresses from " . self::PSGC_FILE . "\n";
    }
}
