<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesForFolders extends Migration
{
    public function up()
    {
        /**
         * ✅ DATABASE INDEXES FOR FOLDERS TABLE
         * 
         * Folders table is frequently queried to:
         * - Check availability (status = 'Available')
         * - List borrowed items (status = 'Borrowed')
         * - Find folder details
         * 
         * Index on status is critical because approval and return
         * operations atomically filter by status.
         */

        $this->forge->addKey('status', false);  // Find available/borrowed folders quickly

        // If implementing soft deletes on folders, also index:
        // $this->forge->addKey('deleted_at', false);
    }

    public function down()
    {
        $this->dropIndexIfExists('folders', 'status');
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        $dbName = $this->db->getDatabase();
        $index = $this->db->query(
            "SELECT index_name
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?",
            [$dbName, $table, $indexName]
        )->getRowArray();

        if (!empty($index)) {
            $this->db->query("ALTER TABLE {$table} DROP INDEX {$indexName}");
        }
    }
}
