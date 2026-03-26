<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropBorrowerIdForeignKey extends Migration
{
	public function up()
	{
		// Only drop the foreign key constraint if it exists
		$db = \Config\Database::connect();
		$tables = $db->listTables();
		
		if (in_array('borrow_transactions', $tables)) {
			// Check if the foreign key exists before attempting to drop it
			try {
				$this->forge->dropForeignKey('borrow_transactions', 'borrow_transactions_borrower_id_foreign');
			} catch (\Exception $e) {
				// Foreign key doesn't exist, which is fine
			}
			
			// Make borrower_id nullable if the column exists
			$fields = $db->getFieldData('borrow_transactions');
			$columnExists = false;
			foreach ($fields as $field) {
				if ($field->name === 'borrower_id') {
					$columnExists = true;
					break;
				}
			}
			
			if ($columnExists) {
				$this->forge->modifyColumn('borrow_transactions', [
					'borrower_id' => [
						'type' => 'INT',
						'constraint' => 11,
						'null' => true,
					],
				]);
			}
		}
	}

	public function down()
	{
		// Restore borrower_id as NOT NULL with foreign key
		$this->forge->modifyColumn('borrow_transactions', [
			'borrower_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'null' => false,
			],
		]);
		
		// Re-add the foreign key constraint
		$this->db->query(
			"ALTER TABLE borrow_transactions
			 ADD CONSTRAINT borrow_transactions_borrower_id_foreign
			 FOREIGN KEY (borrower_id) REFERENCES borrowers(borrower_id)
			 ON DELETE CASCADE ON UPDATE CASCADE"
		);
	}
}
