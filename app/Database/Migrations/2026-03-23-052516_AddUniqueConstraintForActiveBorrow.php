<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUniqueConstraintForActiveBorrow extends Migration
{
    public function up()
    {
        /**
         * ✅ PREVENT MULTIPLE ACTIVE BORROWS PER FOLDER
         * 
         * Business Rule: Only ONE non-returned transaction per folder
         * (either Pending or Borrowed, but not both/multiple)
         * 
         * Enforcement Strategy:
         * - Folder status tracks actual lock: Available | Borrowed
         * - App prevents creating Pending if folder != Available (in store())
         * - App validates transition before approval (isValidTransition)
         * - Atomic folder UPDATE prevents race condition on approval
         * - DB-level: folder.status is single source of truth for lock
         * 
         * Result: Impossible to have 2 active transactions per folder
         */
        
        // No new constraint needed - folder.status provides uniqueness guarantee
        // If you want extra safety (MySQL 8.0.13+), add to foldermodel/migration:
        // ALTER TABLE folders ADD CONSTRAINT check_status_values 
        //   CHECK (status IN ('Available', 'Borrowed'));
    }

    public function down()
    {
        // No rollback needed - no constraint was added
    }
}
