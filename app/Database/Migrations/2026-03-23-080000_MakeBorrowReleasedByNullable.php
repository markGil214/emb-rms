<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeBorrowReleasedByNullable extends Migration
{
    public function up()
    {
        // Make released_by nullable since it's set only on approval
        $this->forge->modifyColumn('borrow_transactions', [
            'released_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
    }

    public function down()
    {
        $nullCount = $this->db->query(
            "SELECT COUNT(*) AS total FROM borrow_transactions WHERE released_by IS NULL"
        )->getRowArray();

        // Cannot enforce NOT NULL when nullable data exists.
        if ((int) ($nullCount['total'] ?? 0) > 0) {
            return;
        }

        $fkName = 'borrow_transactions_released_by_foreign';
        $hadFk = $this->foreignKeyExists('borrow_transactions', $fkName);

        if ($hadFk) {
            $this->forge->dropForeignKey('borrow_transactions', $fkName);
        }

        $this->forge->modifyColumn('borrow_transactions', [
            'released_by' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
        ]);

        if ($hadFk) {
            $this->db->query(
                "ALTER TABLE borrow_transactions
                 ADD CONSTRAINT borrow_transactions_released_by_foreign
                 FOREIGN KEY (released_by) REFERENCES users(user_id)
                 ON DELETE CASCADE ON UPDATE CASCADE"
            );
        }
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $dbName = $this->db->getDatabase();
        $result = $this->db->query(
            "SELECT constraint_name
             FROM information_schema.table_constraints
             WHERE table_schema = ?
               AND table_name = ?
               AND constraint_type = 'FOREIGN KEY'
               AND constraint_name = ?",
            [$dbName, $table, $constraintName]
        )->getRowArray();

        return !empty($result);
    }
}
