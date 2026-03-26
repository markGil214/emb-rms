<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSoftDeleteAndStatusConstraintsToBorrowTransactions extends Migration
{
	public function up()
	{
		if (!$this->columnExists('borrow_transactions', 'deleted_at')) {
			$this->forge->addColumn('borrow_transactions', [
				'deleted_at' => [
					'type' => 'DATETIME',
					'null' => true,
					'comment' => 'Soft delete timestamp',
				],
			]);
		}

		if ($this->db->DBDriver === 'MySQLi' && !$this->checkConstraintExists('borrow_transactions', 'chk_status_valid')) {
			try {
				$sql = "ALTER TABLE borrow_transactions 
						ADD CONSTRAINT chk_status_valid 
						CHECK (status IN ('Pending', 'Borrowed', 'Returned'))";
				$this->db->query($sql);
			} catch (\Throwable $e) {
				// Ignore if engine/version does not support CHECK.
			}
		}
	}

	public function down()
	{
		if ($this->db->DBDriver === 'MySQLi' && $this->checkConstraintExists('borrow_transactions', 'chk_status_valid')) {
			try {
				$this->db->query('ALTER TABLE borrow_transactions DROP CHECK chk_status_valid');
			} catch (\Throwable $e) {
				try {
					$this->db->query('ALTER TABLE borrow_transactions DROP CONSTRAINT chk_status_valid');
				} catch (\Throwable $inner) {
					// Ignore if constraint does not exist or syntax differs.
				}
			}
		}

		if ($this->columnExists('borrow_transactions', 'deleted_at')) {
			$this->forge->dropColumn('borrow_transactions', 'deleted_at');
		}
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

	private function checkConstraintExists(string $table, string $constraintName): bool
	{
		$dbName = $this->db->getDatabase();
		$result = $this->db->query(
			"SELECT constraint_name
			 FROM information_schema.table_constraints
			 WHERE table_schema = ?
			   AND table_name = ?
			   AND constraint_type = 'CHECK'
			   AND constraint_name = ?",
			[$dbName, $table, $constraintName]
		)->getRowArray();

		return !empty($result);
	}
}
