<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run all seeders in order
     * Execution order is critical: roles → users → folders → features
     */
    public function run()
    {
        // 1. Seed system roles and permissions
        echo "Seeding roles and permissions...\n";
        $this->call('RoleSeeder');

        // 2. Seed users
        echo "Seeding users...\n";
        $this->call('UserSeeder');

        // 3. Seed locations and folders
        echo "Seeding folders and locations...\n";
        $this->call('FolderSeeder');

        // 4. Seed feature data (depends on users & folders)
        echo "Seeding relocation requests...\n";
        $this->call('RelocationRequestSeeder');

        echo "Seeding archive records...\n";
        $this->call('ArchiveRecordSeeder');

        echo "Seeding disposal records...\n";
        $this->call('DisposalRecordSeeder');

        echo "\n✅ All seeders executed successfully!\n";
    }
}
