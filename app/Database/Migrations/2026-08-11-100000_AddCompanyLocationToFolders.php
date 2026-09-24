<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Records only stored `company_name`, so the company's physical location was
 * being crammed into that field (e.g. "ROD GAS STATION, Turod, Luna, Apayao").
 * This gives it a column of its own so the two can be searched and displayed
 * separately.
 */
class AddCompanyLocationToFolders extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('folders')) {
            return;
        }

        $fields = $this->db->getFieldNames('folders');

        if (!in_array('company_location', $fields, true)) {
            // `folders.updated_at` still carries the original migration's
            // '0000-00-00 00:00:00' default. Under this server's NO_ZERO_DATE
            // sql_mode, MySQL revalidates every column default on ANY ALTER
            // TABLE, so altering `folders` fails with "Invalid default value
            // for 'updated_at'" unless that mode is relaxed for the statement.
            $originalSqlMode = $this->db->query('SELECT @@SESSION.sql_mode AS m')->getRow()->m;
            $this->db->query("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', '')");

            try {
                $this->forge->addColumn('folders', [
                    'company_location' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'null'       => true,
                        'after'      => 'company_name',
                    ],
                ]);
            } finally {
                $this->db->query('SET SESSION sql_mode = ?', [$originalSqlMode]);
            }
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('folders')) {
            return;
        }

        $fields = $this->db->getFieldNames('folders');

        if (in_array('company_location', $fields, true)) {
            $originalSqlMode = $this->db->query('SELECT @@SESSION.sql_mode AS m')->getRow()->m;
            $this->db->query("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', '')");

            try {
                $this->forge->dropColumn('folders', 'company_location');
            } finally {
                $this->db->query('SET SESSION sql_mode = ?', [$originalSqlMode]);
            }
        }
    }
}
