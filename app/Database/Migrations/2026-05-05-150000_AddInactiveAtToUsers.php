<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInactiveAtToUsers extends Migration
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

        if (! $this->columnExists('users', 'inactive_at')) {
            $this->forge->addColumn('users', [
                'inactive_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'status',
                ],
            ]);
        }

        $this->db->query("UPDATE users SET inactive_at = COALESCE(updated_at, created_at, NOW()) WHERE status = 'Inactive' AND inactive_at IS NULL");
    }

    public function down()
    {
        if (! $this->tableExists('users')) {
            return;
        }

        if ($this->columnExists('users', 'inactive_at')) {
            $this->forge->dropColumn('users', 'inactive_at');
        }
    }
}