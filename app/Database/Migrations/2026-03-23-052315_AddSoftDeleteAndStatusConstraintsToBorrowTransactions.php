<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSoftDeleteAndStatusConstraintsToBorrowTransactions extends Migration
{
	public function up()
	{
		// Add soft delete column if it doesn't exist
		$db = \Config\Database::connect();
		$fields = $db->getFieldData('borrow_transactions');
		$columnExists = false;
		foreach ($fields as $field) {
			if ($field->name === 'deleted_at') {
				$columnExists = true;
				break;
			}
		}

		if (!$columnExists) {
			$this->forge->addColumn('borrow_transactions', [
				'deleted_at' => [
					'type' => 'DATETIME',
					'null' => true,
					'comment' => 'Soft delete timestamp',
				],
			]);
		}

		// Add CHECK constraint on status values (MySQL 8.0+)
		// Allowed values: Pending, Borrowed, Returned
		try {
			$sql = "ALTER TABLE borrow_transactions 
					ADD CONSTRAINT chk_status_valid 
					CHECK (status IN ('Pending', 'Borrowed', 'Returned'))";
			$this->db->query($sql);
		} catch (\Throwable $e) {
			// Constraint might already exist, ignore
		}
	}

	public function down()
	{
		// Drop CHECK constraint (syntax differs by engine/version)
		try {
			$this->db->query('ALTER TABLE borrow_transactions DROP CHECK chk_status_valid');
		} catch (\Throwable $e) {
			try {
				$this->db->query('ALTER TABLE borrow_transactions DROP CONSTRAINT chk_status_valid');
			} catch (\Throwable $inner) {
				// Ignore if constraint does not exist or engine does not support CHECK constraints.
			}
		}
		
		// Drop column
		$this->forge->dropColumn('borrow_transactions', 'deleted_at');
	}
}
