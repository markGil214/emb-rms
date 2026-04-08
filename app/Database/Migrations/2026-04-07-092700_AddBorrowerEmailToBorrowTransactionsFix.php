<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBorrowerEmailToBorrowTransactionsFix extends Migration
{
    public function up()
    {
        if (!$this->tableExists('borrow_transactions')) {
            return;
        }

        if (!$this->columnExists('borrow_transactions', 'borrower_email')) {
            $this->forge->addColumn('borrow_transactions', [
                'borrower_email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->tableExists('borrow_transactions') && $this->columnExists('borrow_transactions', 'borrower_email')) {
            $this->forge->dropColumn('borrow_transactions', 'borrower_email');
        }
    }

    private function tableExists(string $table): bool
    {
        $dbName = $this->db->getDatabase();
        $result = $this->db->query(
            "SELECT table_name
             FROM information_schema.tables
             WHERE table_schema = ?
               AND table_name = ?",
            [$dbName, $table]
        )->getRowArray();

        return !empty($result);
    }

    private function columnExists(string $table, string $column): bool
    {
        $dbName = $this->db->getDatabase();
        $result = $this->db->query(
            "SELECT column_name
             FROM information_schema.columns
             WHERE table_schema = ?
               AND table_name = ?
               AND column_name = ?",
            [$dbName, $table, $column]
        )->getRowArray();

        return !empty($result);
    }
}
