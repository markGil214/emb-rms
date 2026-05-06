<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBorrowNotesAndRelocationReason extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('notes', 'borrow_transactions')) {
            $this->forge->addColumn('borrow_transactions', [
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'purpose',
                ],
            ]);
        }

        if (!$this->db->fieldExists('reason', 'relocation_requests')) {
            $this->forge->addColumn('relocation_requests', [
                'reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'to_location_id',
                ],
            ]);

            return;
        }

        $this->forge->modifyColumn('relocation_requests', [
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('notes', 'borrow_transactions')) {
            $this->forge->dropColumn('borrow_transactions', 'notes');
        }
    }
}
