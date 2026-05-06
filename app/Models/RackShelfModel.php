<?php

namespace App\Models;

use CodeIgniter\Model;

class RackShelfModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'locations';
    protected $primaryKey       = 'location_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'rack',
        'shelf',
        'current_count',
        'folder_label',
        'coordinates_3d',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAllOrdered(): array
    {
        return $this->select('location_id, rack, shelf, current_count')
            ->orderBy('rack', 'ASC')
            ->orderBy('shelf', 'ASC')
            ->findAll();
    }

    public function rackShelfExists(string $rack, string $shelf, ?int $ignoreId = null): bool
    {
        $builder = $this->where('rack', $rack)->where('shelf', $shelf);

        if ($ignoreId !== null) {
            $builder->where('location_id !=', $ignoreId);
        }

        return $builder->countAllResults() > 0;
    }

    public function getDistinctRacks(): array
    {
        $rows = $this->select('rack')
            ->groupBy('rack')
            ->orderBy('rack', 'ASC')
            ->findAll();

        $racks = [];
        foreach ($rows as $row) {
            if (! empty($row['rack'])) {
                $racks[] = (string) $row['rack'];
            }
        }

        return $racks;
    }

    public function rackExists(string $rack): bool
    {
        return $this->where('rack', $rack)->countAllResults() > 0;
    }

    public function getAllowedRacks(): array
    {
        $allowed = [];
        for ($rack = 1; $rack <= 20; $rack++) {
            $allowed[] = (string) $rack;
        }

        return $allowed;
    }
}
