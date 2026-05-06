<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRetentionToFolderFiles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('folder_files', [
            'retention_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'permanent',
                'after' => 'uploaded_by',
            ],
        ]);

        $this->forge->addColumn('folder_files', [
            'expiration_date' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'retention_type',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('folder_files', 'expiration_date');
        $this->forge->dropColumn('folder_files', 'retention_type');
    }
}
