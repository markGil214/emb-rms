<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Philippine address reference data (regions, provinces, cities/
 * municipalities, barangays with PSGC codes), imported once from the
 * `ph-address` npm package's bundled SQLite database. That package is a
 * Node.js + SQLite library -- it can't run inside this PHP app or a
 * browser -- so its data is imported here and served through our own API
 * instead. See App\Database\Seeds\PhAddressSeeder for the import.
 */
class CreatePhAddressesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('ph_addresses')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'psgc' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'level' => [
                // Reg, Prov, City, Mun, SubMun, SGU, Dist, Bgy
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'city_mun_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'prov_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'reg_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('name');
        $this->forge->addKey('level');
        $this->forge->addKey('city_mun_name');
        $this->forge->addKey('prov_name');
        $this->forge->createTable('ph_addresses');
    }

    public function down()
    {
        $this->forge->dropTable('ph_addresses', true);
    }
}
