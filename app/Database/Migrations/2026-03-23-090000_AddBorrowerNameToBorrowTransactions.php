<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBorrowerNameToBorrowTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('borrow_transactions', [
            'borrower_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'borrower_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('borrow_transactions', 'borrower_name');
    }
}
