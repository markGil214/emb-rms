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
		if (!$this->tableExists('borrow_transactions') || !$this->tableExists('borrowers')) {
			return;
		}

		// Ensure compatible type with borrowers.borrower_id (unsigned INT)
		$this->forge->modifyColumn('borrow_transactions', [
			'borrower_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => true,
			],
		]);

		$dbName = $this->db->getDatabase();
		$constraint = $this->db->query(
			"SELECT constraint_name
			 FROM information_schema.referential_constraints
			 WHERE constraint_schema = ?
			   AND table_name = 'borrow_transactions'
			   AND constraint_name = 'borrow_transactions_borrower_id_foreign'",
			[$dbName]
		)->getRowArray();

		if (empty($constraint)) {
			$orphanCount = $this->db->query(
				"SELECT COUNT(*) AS total
				 FROM borrow_transactions bt
				 LEFT JOIN borrowers b ON b.borrower_id = bt.borrower_id
				 WHERE bt.borrower_id IS NOT NULL
				   AND b.borrower_id IS NULL"
			)->getRowArray();

			if ((int) ($orphanCount['total'] ?? 0) === 0) {
				$this->db->query(
					"ALTER TABLE borrow_transactions
					 ADD CONSTRAINT borrow_transactions_borrower_id_foreign
					 FOREIGN KEY (borrower_id) REFERENCES borrowers(borrower_id)
					 ON DELETE CASCADE ON UPDATE CASCADE"
				);
			}
		}

		$nullCount = $this->db->query(
			"SELECT COUNT(*) AS total
			 FROM borrow_transactions
			 WHERE borrower_id IS NULL"
		)->getRowArray();

		if ((int) ($nullCount['total'] ?? 0) === 0) {
			$hadBorrowerFk = $this->foreignKeyExists('borrow_transactions', 'borrow_transactions_borrower_id_foreign');

			if ($hadBorrowerFk) {
				$this->forge->dropForeignKey('borrow_transactions', 'borrow_transactions_borrower_id_foreign');
			}

			$this->forge->modifyColumn('borrow_transactions', [
				'borrower_id' => [
					'type' => 'INT',
					'constraint' => 11,
					'unsigned' => true,
					'null' => false,
				],
			]);

			if ($hadBorrowerFk) {
				$this->db->query(
					"ALTER TABLE borrow_transactions
					 ADD CONSTRAINT borrow_transactions_borrower_id_foreign
					 FOREIGN KEY (borrower_id) REFERENCES borrowers(borrower_id)
					 ON DELETE CASCADE ON UPDATE CASCADE"
				);
			}
		}
	}

	private function tableExists(string $tableName): bool
	{
		$dbName = $this->db->getDatabase();
		$result = $this->db->query(
			"SELECT table_name
			 FROM information_schema.tables
			 WHERE table_schema = ?
			   AND table_name = ?",
			[$dbName, $tableName]
		)->getRowArray();

		return !empty($result);
	}

	private function foreignKeyExists(string $tableName, string $constraintName): bool
	{
		$dbName = $this->db->getDatabase();
		$result = $this->db->query(
			"SELECT constraint_name
			 FROM information_schema.table_constraints
			 WHERE table_schema = ?
			   AND table_name = ?
			   AND constraint_type = 'FOREIGN KEY'
			   AND constraint_name = ?",
			[$dbName, $tableName, $constraintName]
		)->getRowArray();

		return !empty($result);
	}
}
