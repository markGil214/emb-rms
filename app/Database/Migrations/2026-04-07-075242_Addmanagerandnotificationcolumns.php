<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Addmanagerandnotificationcolumns extends Migration
{
	public function up()
	{
		// Add notification tracking columns to borrow_transactions table
		$this->forge->addColumn('borrow_transactions', [
			'notification_status' => [
				'type'       => 'VARCHAR',
				'constraint' => '50',
				'default'    => 'None',
				'comment'    => 'Tracks sent notifications: None | 1_day_sent | 3_day_sent | 7_day_sent',
			],
			'last_notification_sent_at' => [
				'type'    => 'DATETIME',
				'null'    => true,
				'comment' => 'Timestamp of last notification sent',
			],
			'escalated_to_manager' => [
				'type'    => 'BOOLEAN',
				'default' => false,
				'comment' => 'True if escalated to manager at 7+ days overdue',
			],
		]);

		// Add indexes for performance
		$this->forge->addKey('notification_status', false, false, 'borrow_transactions');
	}

	public function down()
	{
		// Drop notification columns
		$this->forge->dropColumn('borrow_transactions', [
			'notification_status',
			'last_notification_sent_at',
			'escalated_to_manager',
		]);
	}
}
