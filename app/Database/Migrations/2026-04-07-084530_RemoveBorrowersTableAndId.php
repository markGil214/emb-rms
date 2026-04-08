<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveBorrowersTableAndId extends Migration
{
    public function up()
    {
        // Drop borrower_id column (will auto-drop FK)
        if ($this->db->fieldExists('borrower_id', 'borrow_transactions')) {
            $this->forge->dropColumn('borrow_transactions', 'borrower_id');
        }

        // Drop borrowers table
        if ($this->db->tableExists('borrowers')) {
            $this->forge->dropTable('borrowers');
        }
    }

    public function down()
    {
        // Non-reversible
    }
}
