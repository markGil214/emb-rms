<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * BorrowRepository::executeReturn() never cleared folders.current_borrow_
 * transaction_id when a folder was returned (fixed alongside this
 * migration), so any folder returned before this fix can still have the FK
 * pointing at its now-Returned transaction while status correctly reads
 * 'Available'. That disagreement caused ArchiveDisposalController::
 * declineArchiveRequest() to wrongly restore such folders to 'Borrowed'.
 * One-time repair for data already corrupted by the bug.
 */
class CleanupStaleCurrentBorrowTransactionId extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('folders')) {
            return;
        }

        $this->db->query(
            "UPDATE folders SET current_borrow_transaction_id = NULL WHERE status = 'Available' AND current_borrow_transaction_id IS NOT NULL"
        );
    }

    public function down()
    {
        // No down: the cleared values can't be reconstructed, and there's
        // nothing correct to restore them to.
    }
}
