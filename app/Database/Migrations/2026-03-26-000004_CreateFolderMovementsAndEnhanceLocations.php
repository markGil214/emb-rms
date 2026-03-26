<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFolderMovementsAndEnhanceLocations extends Migration
{
    public function up()
    {
        // 1. Add detailed location fields to folders
        $this->forge->addColumn('folders', [
            'building' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'location_id'
            ],
            'room' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'building'
            ],
            'cabinet' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'room'
            ],
            'shelf' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'cabinet'
            ],
            'box' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'shelf'
            ]
        ]);

        // 2. Add reason_type to relocation_requests
        $this->forge->addColumn('relocation_requests', [
            'reason_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'default' => 'Other',
                'after' => 'reason'
            ]
        ]);

        // 3. Create folder_movements table for historical tracking
        $this->forge->addField([
            'movement_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true
            ],
            'folder_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'from_location_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'to_location_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'from_building' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'to_building' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],
            'from_room' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'to_room' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'from_cabinet' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'to_cabinet' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'from_shelf' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'to_shelf' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],
            'relocation_request_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true
            ],
            'moved_by' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'moved_at' => [
                'type' => 'DATETIME',
                'null' => false
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false
            ]
        ]);

        $this->forge->addKey('movement_id', true);
        $this->forge->addForeignKey('folder_id', 'folders', 'folder_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('from_location_id', 'locations', 'location_id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('to_location_id', 'locations', 'location_id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('relocation_request_id', 'relocation_requests', 'relocation_id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('moved_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('folder_movements');

        // Add indexes for performance
        $this->forge->addKey(['folder_id', 'moved_at'], false, false, 'idx_folder_movements');
        $this->forge->addKey(['moved_at'], false, false, 'idx_movement_date');
    }

    public function down()
    {
        // Drop folder_movements table
        $this->forge->dropTable('folder_movements');

        // Remove reason_type from relocation_requests
        $this->forge->dropColumn('relocation_requests', 'reason_type');

        // Remove location fields from folders
        $this->forge->dropColumn('folders', ['building', 'room', 'cabinet', 'shelf', 'box']);
    }
}
