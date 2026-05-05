<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnsureUserProfileColumns extends Migration
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

        if (! $this->columnExists('users', 'first_name')) {
            $this->db->query("ALTER TABLE users ADD COLUMN first_name VARCHAR(100) NULL AFTER username");
        }

        if (! $this->columnExists('users', 'last_name')) {
            $this->db->query("ALTER TABLE users ADD COLUMN last_name VARCHAR(100) NULL AFTER first_name");
        }

        if (! $this->columnExists('users', 'status')) {
            $this->db->query("ALTER TABLE users ADD COLUMN status VARCHAR(16) NOT NULL DEFAULT 'Active' AFTER role");
        }

        $this->db->query("UPDATE users SET status = 'Active' WHERE status IS NULL OR TRIM(status) = ''");
    }

    public function down()
    {
        if (! $this->tableExists('users')) {
            return;
        }

        if ($this->columnExists('users', 'status')) {
            $this->forge->dropColumn('users', 'status');
        }

        if ($this->columnExists('users', 'last_name')) {
            $this->forge->dropColumn('users', 'last_name');
        }

        if ($this->columnExists('users', 'first_name')) {
            $this->forge->dropColumn('users', 'first_name');
        }
    }
}
