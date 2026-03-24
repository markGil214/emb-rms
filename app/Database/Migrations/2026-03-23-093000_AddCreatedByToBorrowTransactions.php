<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCreatedByToBorrowTransactions extends Migration
{
	public function up()
	{
		// Add created_by column to track which user created the borrow request
		$this->forge->addColumn('borrow_transactions', [
			'created_by' => [
				'type' => 'INT',
				'constraint' => 11,
				'null' => true,
				'comment' => 'User ID who created the borrow request',
			],
		]);
	}

	public function down()
	{
		// Drop the column
		$this->forge->dropColumn('borrow_transactions', 'created_by');
	}
}
