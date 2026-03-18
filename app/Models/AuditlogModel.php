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

    public function log($action, $entityType, $entityId, $oldData = null, $newData = null, $userId = null)
    {
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