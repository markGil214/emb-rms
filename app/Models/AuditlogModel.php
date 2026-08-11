<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'audit_logs';
    protected $primaryKey = 'log_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDelete = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'entity_type', 'entity_id', 'action', 'old_data', 'new_data'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = null;  // audit_logs doesn't have updated_at column

    /**
     * Record an audit entry.
     *
     * Argument order matters: callers previously passed
     * ($userId, $action, $details), which silently wrote the user id into
     * `action` and the action name into `entity_type`. The guard below makes
     * that mistake visible in the logs instead of quietly corrupting the
     * audit trail.
     */
    public function log($action, $entityType, $entityId, $oldData = null, $newData = null, $userId = null)
    {
        if (is_numeric($action)) {
            log_message(
                'warning',
                'AuditLogModel::log() received a numeric $action ("' . $action . '"). '
                . 'Expected log($action, $entityType, $entityId, $oldData, $newData, $userId) '
                . '-- check the argument order at the call site.'
            );
        }

        return $this->insert([
            'user_id' => $userId ?? session('user_id'),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
        ]);
    }

    public function getAuditLog($limit = 50, $offset = 0)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }
}