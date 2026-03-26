<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLifecycleTimestampsAndAuditToBorrowTransactions extends Migration
{
	public function up()
	{
		// Check which columns exist before adding them
		$db = \Config\Database::connect();
		$fields = $db->getFieldData('borrow_transactions');
		$existingColumns = [];
		foreach ($fields as $field) {
			$existingColumns[] = $field->name;
		}

		$columnsToAdd = [];
		
		if (!in_array('approved_at', $existingColumns)) {
			$columnsToAdd['approved_at'] = [
				'type' => 'DATETIME',
				'null' => true,
				'comment' => 'When admin approved the borrow request',
			];
		}
		
		if (!in_array('approved_by', $existingColumns)) {
			$columnsToAdd['approved_by'] = [
				'type' => 'INT',
				'constraint' => 11,
				'null' => true,
				'comment' => 'User ID who approved the request',
			];
		}
		
		if (!empty($columnsToAdd)) {
			$this->forge->addColumn('borrow_transactions', $columnsToAdd);
		}
	}

	public function down()
	{
		// Drop the new columns
		$this->forge->dropColumn('borrow_transactions', 'approved_at');
		$this->forge->dropColumn('borrow_transactions', 'approved_by');
	}
}
