<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesForFolders extends Migration
{
    public function up()
    {
        /**
         * ✅ DATABASE INDEXES FOR FOLDERS TABLE
         * 
         * Folders table is frequently queried to:
         * - Check availability (status = 'Available')
         * - List borrowed items (status = 'Borrowed')
         * - Find folder details
         * 
         * Index on status is critical because approval and return
         * operations atomically filter by status.
         */

        $this->forge->addKey('status', false);  // Find available/borrowed folders quickly

        // If implementing soft deletes on folders, also index:
        // $this->forge->addKey('deleted_at', false);
    }

    public function down()
    {
        $this->forge->dropKey('folders', 'status');
    }
}
