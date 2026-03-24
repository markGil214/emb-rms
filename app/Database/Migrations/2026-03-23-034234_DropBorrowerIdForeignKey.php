<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropBorrowerIdForeignKey extends Migration
{
	public function up()
	{
		// Drop the foreign key constraint on borrower_id
		$this->forge->dropForeignKey('borrow_transactions', 'borrow_transactions_borrower_id_foreign');
		
		// Make borrower_id nullable
		$this->forge->modifyColumn('borrow_transactions', [
			'borrower_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'null' => true,
			],
		]);
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
