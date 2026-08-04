<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * `alerts`, `document_versions`, and `folder_movements` are leftovers from
 * removed features: their migration files are already gone from this repo,
 * they hold zero rows, and the only remaining references in app code are
 * commented out (RelocationController, FolderController) or unrelated
 * (the `alerts` name is reused for an in-memory JS toast array in
 * layouts/main.php, nothing to do with this table). Dropping them removes
 * dead schema clutter the migrations ledger was carrying with no
 * corresponding CREATE migration anymore.
 */
class DropUnusedOrphanTables extends Migration
{
    private array $tables = ['alerts', 'document_versions', 'folder_movements'];

    public function up()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->tables as $table) {
            $this->forge->dropTable($table, true);
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // Intentionally no-op: these tables' original CREATE migrations no
        // longer exist in this repo, so there is nothing faithful to restore.
    }
}
