<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLifecycleTimestampsAndAuditToBorrowTransactions extends Migration
{
	public function up()
	{
		// Add lifecycle timestamp columns
		$this->forge->addColumn('borrow_transactions', [
			'approved_at' => [
				'type' => 'DATETIME',
				'null' => true,
				'comment' => 'When admin approved the borrow request',
			],
			'approved_by' => [
				'type' => 'INT',
				'constraint' => 11,
				'null' => true,
				'comment' => 'User ID who approved the request',
			],
		]);
	}

	public function down()
	{
		// Drop the new columns
		$this->forge->dropColumn('borrow_transactions', 'approved_at');
		$this->forge->dropColumn('borrow_transactions', 'approved_by');
	}
}
