<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFolderTypesTable extends Migration
{
	public function up()
	{
		$this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => false,
            ],
			]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('folder_type');
	}

	public function down()
	{
		$this->forge->dropTable('folder_type');
	}
}
