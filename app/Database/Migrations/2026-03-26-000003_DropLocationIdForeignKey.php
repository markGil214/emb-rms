<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropLocationIdForeignKey extends Migration
{
    public function up()
    {
        // Drop the foreign key constraint on location_id
        try {
            $this->forge->dropForeignKey('folders', 'folders_location_id_foreign');
        } catch (\Exception $e) {
            // Foreign key might not exist with this name, try alternative names
            try {
                $this->forge->dropForeignKey('folders', 'location_id');
            } catch (\Exception $e2) {
                // If still failing, log but continue
                log_message('info', 'Could not drop location_id foreign key: ' . $e2->getMessage());
            }
        }
    }

    public function down()
    {
        // Only attempt to restore the foreign key constraint if it's safe to do so
        // The location_id column may not exist in all scenarios, so we wrap in try-catch
        try {
            // Check if column exists by attempting a dummy operation
            $fields = $this->db->getFieldData('folders');
            $locationIdExists = false;
            
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($field->name) && $field->name === 'location_id') {
                        $locationIdExists = true;
                        break;
                    }
                }
            }

            // Only add foreign key if column exists
            if ($locationIdExists) {
                $this->forge->addForeignKey('location_id', 'locations', 'location_id', 'CASCADE', 'CASCADE');
            }
        } catch (\Throwable $e) {
            // Silently skip if restoration fails - this is expected in some scenarios
            log_message('debug', 'DropLocationIdForeignKey::down() - Restoration skipped: ' . $e->getMessage());
        }
    }
}
