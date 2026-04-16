<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveRetentionColumnsFromArchiveRecords extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('archive_records', [
            'retention_status',
            'retention_expiry_date',
            'retention_policy_reference',
        ]);
    }

    public function down()
    {
        $this->forge->addColumn('archive_records', [
            'retention_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'default' => 'Active',
            ],
            'retention_expiry_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'retention_policy_reference' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }
}
