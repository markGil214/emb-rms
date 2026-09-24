<?php

namespace App\Models;

use CodeIgniter\Model;

class RackShelfModel extends Model
{
    /**
     * Folder statuses that do not occupy shelf space.
     *
     * A folder awaiting creation approval has not been accepted onto a shelf
     * yet, and a declined one never will be -- neither should count against
     * capacity or show up in shelf occupancy.
     */
    public const NON_OCCUPYING_STATUSES = ['Pending', 'Declined'];

    protected $DBGroup          = 'default';
    protected $table            = 'locations';
    protected $primaryKey       = 'location_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'rack',
        'shelf',
        'capacity',
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
        return $this->select('location_id, rack, shelf, capacity, current_count')
            ->orderBy('rack', 'ASC')
            ->orderBy('shelf', 'ASC')
            ->findAll();
    }

    /**
     * Live folder count for every location, keyed by location_id.
     *
     * The `current_count` column on `locations` is not maintained by any
     * write path, so occupancy is always derived from the folders table.
     */
    public function getOccupancyMap(): array
    {
        $rows = $this->db->table('folders')
            ->select('location_id, COUNT(*) as folder_count')
            ->whereNotIn('status', self::NON_OCCUPYING_STATUSES)
            ->groupBy('location_id')
            ->get()
            ->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['location_id']] = (int) $row['folder_count'];
        }

        return $map;
    }

    /**
     * How many folders currently occupy a location.
     *
     * @param int|null $excludeFolderId Folder to ignore, so a folder being
     *                                  moved doesn't count against its own
     *                                  destination check.
     */
    public function getOccupancy(int $locationId, ?int $excludeFolderId = null): int
    {
        $builder = $this->db->table('folders')
            ->where('location_id', $locationId)
            ->whereNotIn('status', self::NON_OCCUPYING_STATUSES);

        if ($excludeFolderId !== null) {
            $builder->where('folder_id !=', $excludeFolderId);
        }

        return (int) $builder->countAllResults();
    }

    /**
     * Slots left on a shelf, or null when it has no limit.
     *
     * A capacity of 0 means "unlimited" -- shelves created before capacity
     * was configurable default to 0, and must keep accepting folders.
     */
    public function getRemainingCapacity(int $locationId, ?int $excludeFolderId = null): ?int
    {
        $location = $this->find($locationId);
        if (! $location) {
            return null;
        }

        $capacity = max(0, (int) ($location['capacity'] ?? 0));

        return max(0, $capacity - $this->getOccupancy($locationId, $excludeFolderId));
    }

    /**
     * Whether one more folder can be placed at this location.
     *
     * A capacity of 0 means the shelf holds nothing at all, so it never has
     * room until a real capacity is set for it in Manage Racks.
     */
    public function hasRoomFor(int $locationId, ?int $excludeFolderId = null): bool
    {
        $remaining = $this->getRemainingCapacity($locationId, $excludeFolderId);

        return $remaining !== null && $remaining > 0;
    }

    /**
     * Human-readable "Rack 3 - Shelf B is full (50/50)" style message for
     * validation feedback.
     */
    public function capacityMessage(int $locationId): string
    {
        $location = $this->find($locationId);
        if (! $location) {
            return 'The selected shelf could not be found.';
        }

        $capacity = max(0, (int) ($location['capacity'] ?? 0));
        $occupied = $this->getOccupancy($locationId);
        $rack = $location['rack'] ?? '?';
        $shelf = $location['shelf'] ?? '?';

        if ($capacity === 0) {
            return sprintf(
                'Rack %s - Shelf %s has no capacity set, so it cannot store folders. Set its capacity in Manage Racks first.',
                $rack,
                $shelf
            );
        }

        return sprintf(
            'Rack %s - Shelf %s is at full capacity (%d/%d). Choose another shelf or raise its capacity in Manage Racks.',
            $rack,
            $shelf,
            $occupied,
            $capacity
        );
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
