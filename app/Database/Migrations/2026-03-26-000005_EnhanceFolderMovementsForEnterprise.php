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
        // Ensure enterprise columns exist before adding constraints/indexes.
        // This keeps migration safe on fresh clones and partially upgraded DBs.
        if (!$this->columnExists('folders', 'is_in_transit')) {
            $this->forge->addColumn('folders', [
                'is_in_transit' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => false,
                ],
            ]);
        }

        if (!$this->columnExists('folders', 'current_movement_id')) {
            $this->forge->addColumn('folders', [
                'current_movement_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'null' => true,
                ],
            ]);
        }

        $folderMovementColumns = [
            'from_location_label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'to_location_label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'confirmed_from_building' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'confirmed_to_building' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'approved_by' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'completed_by' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'movement_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'folder_status_at_start' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'folder_status_at_completion' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'status_conflict_detected' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
        ];

        foreach ($folderMovementColumns as $columnName => $definition) {
            if (!$this->columnExists('folder_movements', $columnName)) {
                $this->forge->addColumn('folder_movements', [$columnName => $definition]);
            }
        }

        // ===== FOREIGN KEYS using raw SQL =====
        $db = \Config\Database::connect();
        
        // Foreign key for approved_by on folder_movements table
        if (!$this->foreignKeyExists('folder_movements', 'fk_fm_approved_by')) {
            $db->query('
                ALTER TABLE folder_movements 
                ADD CONSTRAINT fk_fm_approved_by 
                FOREIGN KEY (approved_by) REFERENCES users(user_id) 
                ON DELETE SET NULL ON UPDATE CASCADE
            ');
        }
        
        // Foreign key for completed_by on folder_movements table
        if (!$this->foreignKeyExists('folder_movements', 'fk_fm_completed_by')) {
            $db->query('
                ALTER TABLE folder_movements 
                ADD CONSTRAINT fk_fm_completed_by 
                FOREIGN KEY (completed_by) REFERENCES users(user_id) 
                ON DELETE SET NULL ON UPDATE CASCADE
            ');
        }

        // Foreign key for current_movement_id on folders table
        if (!$this->foreignKeyExists('folders', 'fk_folders_current_movement')) {
            $db->query('
                ALTER TABLE folders 
                ADD CONSTRAINT fk_folders_current_movement 
                FOREIGN KEY (current_movement_id) REFERENCES folder_movements(movement_id) 
                ON DELETE SET NULL ON UPDATE CASCADE
            ');
        }

        // ===== INDEXES using raw SQL =====
        // Index on folders table for in_transit lookup
        if (!$this->indexExists('folders', 'idx_folders_in_transit')) {
            $db->query('
                ALTER TABLE folders 
                ADD INDEX idx_folders_in_transit (is_in_transit)
            ');
        }
        
        // Indexes on folder_movements table
        if (!$this->indexExists('folder_movements', 'idx_movement_code')) {
            $db->query('
                ALTER TABLE folder_movements 
                ADD INDEX idx_movement_code (movement_code)
            ');
        }
        
        if (!$this->indexExists('folder_movements', 'idx_fm_approved_by')) {
            $db->query('
                ALTER TABLE folder_movements 
                ADD INDEX idx_fm_approved_by (approved_by)
            ');
        }
        
        if (!$this->indexExists('folder_movements', 'idx_fm_completed_by')) {
            $db->query('
                ALTER TABLE folder_movements 
                ADD INDEX idx_fm_completed_by (completed_by)
            ');
        }
        
        if (!$this->indexExists('folder_movements', 'idx_status_conflict')) {
            $db->query('
                ALTER TABLE folder_movements 
                ADD INDEX idx_status_conflict (status_conflict_detected)
            ');
        }
        
        // Composite index for audit queries
        if (!$this->indexExists('folder_movements', 'idx_fm_audit_dates')) {
            $db->query('
                ALTER TABLE folder_movements 
                ADD INDEX idx_fm_audit_dates (approved_at, completed_at)
            ');
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        $builder = $this->db->table('information_schema.columns');
        $result = $builder
            ->select('column_name')
            ->where('table_schema', $this->db->database)
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->get()
            ->getRowArray();

        return !empty($result);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $builder = $this->db->table('information_schema.statistics');
        $result = $builder
            ->select('index_name')
            ->where('table_schema', $this->db->database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->get()
            ->getRowArray();

        return !empty($result);
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $builder = $this->db->table('information_schema.table_constraints');
        $result = $builder
            ->select('constraint_name')
            ->where('table_schema', $this->db->database)
            ->where('table_name', $table)
            ->where('constraint_type', 'FOREIGN KEY')
            ->where('constraint_name', $constraintName)
            ->get()
            ->getRowArray();

        return !empty($result);
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
