<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Enhanced Folder Movements for Enterprise Production
 * 
 * ENTERPRISE UPGRADE Phase 1:
 * Adds critical RMS features for audit readiness and operational integrity
 * 
 * Changes:
 * 1. In-Transit State: folders now track current movement status
 * 2. Location Snapshot: movement history immune to location changes
 * 3. Approval Audit: complete chain of custody from approval to completion
 * 4. Movement Numbering: unique identifiers for audit trail
 * 5. Race Condition Safeguards: additional validation fields
 */
class EnhanceFolderMovementsForEnterprise extends Migration
{
    public function up()
    {
        // NOTE: All schema columns have already been added in previous migrations.
        // This migration adds the foreign key constraints and indexes needed for enterprise features.
        
        // ===== FOREIGN KEYS using raw SQL =====
        // Use raw queries to avoid CodeIgniter forge issues with table context
        $db = \Config\Database::connect();
        
        // Foreign key for approved_by on folder_movements table
        $db->query('
            ALTER TABLE folder_movements 
            ADD CONSTRAINT fk_fm_approved_by 
            FOREIGN KEY (approved_by) REFERENCES users(user_id) 
            ON DELETE SET NULL ON UPDATE CASCADE
        ');
        
        // Foreign key for completed_by on folder_movements table
        $db->query('
            ALTER TABLE folder_movements 
            ADD CONSTRAINT fk_fm_completed_by 
            FOREIGN KEY (completed_by) REFERENCES users(user_id) 
            ON DELETE SET NULL ON UPDATE CASCADE
        ');

        // Foreign key for current_movement_id on folders table
        $db->query('
            ALTER TABLE folders 
            ADD CONSTRAINT fk_folders_current_movement 
            FOREIGN KEY (current_movement_id) REFERENCES folder_movements(movement_id) 
            ON DELETE SET NULL ON UPDATE CASCADE
        ');

        // ===== INDEXES using raw SQL =====
        // Index on folders table for in_transit lookup
        $db->query('
            ALTER TABLE folders 
            ADD INDEX idx_folders_in_transit (is_in_transit)
        ');
        
        // Indexes on folder_movements table
        $db->query('
            ALTER TABLE folder_movements 
            ADD INDEX idx_movement_code (movement_code)
        ');
        
        $db->query('
            ALTER TABLE folder_movements 
            ADD INDEX idx_fm_approved_by (approved_by)
        ');
        
        $db->query('
            ALTER TABLE folder_movements 
            ADD INDEX idx_fm_completed_by (completed_by)
        ');
        
        $db->query('
            ALTER TABLE folder_movements 
            ADD INDEX idx_status_conflict (status_conflict_detected)
        ');
        
        // Composite index for audit queries
        $db->query('
            ALTER TABLE folder_movements 
            ADD INDEX idx_fm_audit_dates (approved_at, completed_at)
        ');
    }

    public function down()
    {
        // Remove from folders table
        $this->forge->dropForeignKey('folders', 'fk_folders_current_movement');
        $this->forge->dropColumn('folders', ['is_in_transit', 'current_movement_id']);

        // Remove from folder_movements table
        $this->forge->dropForeignKey('folder_movements', 'fk_fm_approved_by');
        $this->forge->dropForeignKey('folder_movements', 'fk_fm_completed_by');
        
        $this->forge->dropColumn('folder_movements', [
            'from_location_label',
            'to_location_label',
            'confirmed_from_building',
            'confirmed_to_building',
            'approved_by',
            'approved_at',
            'completed_by',
            'completed_at',
            'movement_code',
            'folder_status_at_start',
            'folder_status_at_completion',
            'status_conflict_detected'
        ]);
    }
}
