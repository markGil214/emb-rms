<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The live `roles` table has role_name = 'records_staff' for the base role,
 * but every other place that names this role — RoleSeeder.php, UserSeeder.php,
 * Config\Permissions::roleDefaults() (keyed 'records_officer'),
 * Admin\UserController's role name map, and PermissionController's
 * users.role sync — consistently uses 'records_officer'. No application
 * code references the literal string 'records_staff' (confirmed by search),
 * so this is a stray value from some earlier manual edit, not something
 * anything depends on. Renaming it closes the last naming gap: it's what
 * made Config\Permissions::roleDefaults()'s config-based default permissions
 * silently never apply to DB-assigned base-role users (name mismatch), and
 * what made PermissionController's role lookup-by-name fail for such users.
 */
class RenameRecordsStaffRoleToRecordsOfficer extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('roles')) {
            return;
        }

        $this->db->table('roles')
            ->where('role_name', 'records_staff')
            ->update(['role_name' => 'records_officer']);
    }

    public function down()
    {
        if (!$this->db->tableExists('roles')) {
            return;
        }

        $this->db->table('roles')
            ->where('role_name', 'records_officer')
            ->update(['role_name' => 'records_staff']);
    }
}
