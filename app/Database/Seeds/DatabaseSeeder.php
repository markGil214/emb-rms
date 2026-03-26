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

        // 2.1 Map legacy users.role string into RBAC user_roles rows
        // Required for fresh clones so permission checks work immediately.
        echo "Migrating users to RBAC role assignments...\n";
        $this->call('MigrateExistingUsers');

        // 3. Seed locations and folders
        echo "Seeding folders and locations...\n";
        $this->call('FolderSeeder');

        // 4. Seed feature data (depends on users & folders)
        echo "Seeding borrow transactions...\n";
        $this->call('BorrowTransactionSeeder');

        echo "Seeding relocation requests...\n";
        $this->call('RelocationRequestSeeder');

        echo "Seeding archive records...\n";
        $this->call('ArchiveRecordSeeder');

        echo "Seeding disposal records...\n";
        $this->call('DisposalRecordSeeder');

        echo "\n✅ All seeders executed successfully!\n";
    }
}
