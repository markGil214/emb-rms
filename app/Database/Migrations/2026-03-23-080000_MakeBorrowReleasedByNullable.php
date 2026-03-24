<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeBorrowReleasedByNullable extends Migration
{
    public function up()
    {
        // Make released_by nullable since it's set only on approval
        $this->forge->modifyColumn('borrow_transactions', [
            'released_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ]);
    }

    public function down()
    {
        // Revert to NOT NULL
        $this->forge->modifyColumn('borrow_transactions', [
            'released_by' => ['type' => 'INT', 'unsigned' => true, 'null' => false],
        ]);
    }
}
