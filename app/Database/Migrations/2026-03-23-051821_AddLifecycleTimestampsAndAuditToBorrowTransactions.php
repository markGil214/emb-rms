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
		if ($this->columnExists('borrow_transactions', 'approved_at')) {
			$this->forge->dropColumn('borrow_transactions', 'approved_at');
		}

		if ($this->columnExists('borrow_transactions', 'approved_by')) {
			$this->forge->dropColumn('borrow_transactions', 'approved_by');
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
}
