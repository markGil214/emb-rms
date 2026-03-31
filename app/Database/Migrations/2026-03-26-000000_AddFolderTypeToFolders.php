<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFolderTypeToFolders extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('folder_type', 'folders')) {
            $this->forge->addColumn('folders', [
                'folder_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'company_name',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('folder_type', 'folders')) {
            $this->forge->dropColumn('folders', 'folder_type');
        }
    }
}
