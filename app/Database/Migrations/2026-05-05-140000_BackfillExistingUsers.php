<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillExistingUsers extends Migration
{
    private function tableExists(string $table): bool
    {
        $result = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        )->getRowArray();

        return !empty($result) && (int) $result['cnt'] > 0;
    }

    private function columnExists(string $table, string $column): bool
    {
        $result = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        )->getRowArray();

        return !empty($result) && (int) $result['cnt'] > 0;
    }

    public function up()
    {
        if (! $this->tableExists('users')) {
            return;
        }

        $hasFirstName = $this->columnExists('users', 'first_name');
        $hasLastName = $this->columnExists('users', 'last_name');
        $hasStatus = $this->columnExists('users', 'status');

        if ($hasStatus) {
            $this->db->table('users')
                ->set('status', 'Active')
                ->where("status IS NULL OR TRIM(status) = ''", null, false)
                ->update();
        }

        if (! $hasFirstName || ! $hasLastName) {
            return;
        }

        $users = $this->db->table('users')->get()->getResultArray();
        foreach ($users as $user) {
            $firstName = trim((string) ($user['first_name'] ?? ''));
            $lastName = trim((string) ($user['last_name'] ?? ''));

            if ($firstName !== '' || $lastName !== '') {
                continue;
            }

            $username = trim((string) ($user['username'] ?? ''));
            if ($username === '') {
                continue;
            }

            $parts = preg_split('/[._\-\s]+/', $username) ?: [];
            $parts = array_values(array_filter($parts, static function ($value) {
                return trim((string) $value) !== '';
            }));

            $guessedFirst = $parts[0] ?? $username;
            $guessedLast = $parts[1] ?? '';

            $updateData = [];
            if ($firstName === '') {
                $updateData['first_name'] = ucfirst(strtolower($guessedFirst));
            }
            if ($lastName === '' && $guessedLast !== '') {
                $updateData['last_name'] = ucfirst(strtolower($guessedLast));
            }

            if (!empty($updateData)) {
                $this->db->table('users')
                    ->where('user_id', (int) $user['user_id'])
                    ->update($updateData);
            }
        }
    }

    public function down()
    {
        // One-way data backfill migration.
    }
}
