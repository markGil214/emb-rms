<?php

namespace App\Controllers;

use App\Models\RackShelfModel;
use App\Libraries\FileCodeGenerator;

class RackManagementController extends BaseController
{
    /** @var RackShelfModel */
    protected $rackShelfModel;

    public function __construct()
    {
        $this->rackShelfModel = new RackShelfModel();
    }

    public function index()
    {
        return view('racks/index', [
            'title' => 'Manage Racks',
            'locations' => $this->rackShelfModel->getAllOrdered(),
            'racks' => $this->rackShelfModel->getAllowedRacks(),
            'occupancyMap' => $this->rackShelfModel->getOccupancyMap(),
        ]);
    }

    public function edit(int $locationId)
    {
        $location = $this->rackShelfModel->find($locationId);
        if (! $location) {
            return redirect()->to('/manage-racks')->with('error', 'Rack not found.');
        }

        return view('racks/index', [
            'title' => 'Manage Racks',
            'locations' => $this->rackShelfModel->getAllOrdered(),
            'racks' => $this->rackShelfModel->getAllowedRacks(),
            'occupancyMap' => $this->rackShelfModel->getOccupancyMap(),
            'editLocation' => $location,
        ]);
    }

    public function store()
    {
        $rules = [
            'rack' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[20]',
            'shelf' => 'required|max_length[50]|alpha_numeric_space',
            'capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
        ];
        $messages = [
            'rack' => [
                'required' => 'Please select a rack.',
                'integer' => 'Rack must be a number.',
                'greater_than_equal_to' => 'Rack must be between 1 and 20.',
                'less_than_equal_to' => 'Rack must be between 1 and 20.',
            ],
            'shelf' => [
                'required' => 'Shelf label is required.',
                'max_length' => 'Shelf label must not exceed 50 characters.',
                'alpha_numeric_space' => 'Shelf label may only contain letters, numbers, and spaces.',
            ],
            'capacity' => [
                'integer' => 'Capacity must be a whole number.',
                'greater_than_equal_to' => 'Capacity cannot be negative. Use 0 for unlimited.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors())->with('modal', 'add-rack');
        }

        $rack = trim((string) $this->request->getPost('rack'));
        $shelf = trim((string) $this->request->getPost('shelf'));

        if (! in_array($rack, $this->rackShelfModel->getAllowedRacks(), true)) {
            return redirect()->back()->withInput()->with('error', 'Selected rack was not found. Please choose a rack between 1 and 20.')->with('modal', 'add-rack');
        }

        if ($this->rackShelfModel->rackShelfExists($rack, $shelf)) {
            return redirect()->back()->withInput()->with('error', 'Shelf "' . $shelf . '" already exists in rack "' . $rack . '".')->with('modal', 'add-rack');
        }

        $saved = $this->rackShelfModel->insert([
            'rack' => $rack,
            'shelf' => $shelf,
            'capacity' => (int) ($this->request->getPost('capacity') ?: 0),
            'current_count' => 0,
        ]);

        if (! $saved) {
            return redirect()->back()->withInput()->with('errors', $this->rackShelfModel->errors())->with('modal', 'add-rack');
        }

        return redirect()->to('/manage-racks')->with('success', 'Shelf "' . $shelf . '" added to rack "' . $rack . '".');
    }

    public function update(int $locationId)
    {
        $location = $this->rackShelfModel->find($locationId);
        if (! $location) {
            return redirect()->to('/manage-racks')->with('error', 'Rack was not found.');
        }

        $rules = [
            'rack' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[20]',
            'shelf' => 'required|max_length[50]|alpha_numeric_space',
            'capacity' => 'permit_empty|integer|greater_than_equal_to[0]',
        ];
        $messages = [
            'rack' => [
                'required' => 'Please select a rack.',
                'integer' => 'Rack must be a number.',
                'greater_than_equal_to' => 'Rack must be between 1 and 20.',
                'less_than_equal_to' => 'Rack must be between 1 and 20.',
            ],
            'shelf' => [
                'required' => 'Shelf label is required.',
                'max_length' => 'Shelf label must not exceed 50 characters.',
                'alpha_numeric_space' => 'Shelf label may only contain letters, numbers, and spaces.',
            ],
            'capacity' => [
                'integer' => 'Capacity must be a whole number.',
                'greater_than_equal_to' => 'Capacity cannot be negative. Use 0 for unlimited.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->to(route_to('racks.edit', $locationId))->withInput()->with('errors', $this->validator->getErrors());
        }

        $rack = trim((string) $this->request->getPost('rack'));
        $shelf = trim((string) $this->request->getPost('shelf'));

        if ($this->rackShelfModel->rackShelfExists($rack, $shelf, $locationId)) {
            return redirect()->to(route_to('racks.edit', $locationId))->withInput()->with('error', 'Rack and shelf combination already exists.');
        }

        $newCapacity = (int) ($this->request->getPost('capacity') ?: 0);

        // Don't let a shelf be capped below what it already holds, or the
        // records sitting there would be silently over capacity.
        $occupied = $this->rackShelfModel->getOccupancy($locationId);
        if ($newCapacity < $occupied) {
            return redirect()->to(route_to('racks.edit', $locationId))
                ->withInput()
                ->with('error', 'Capacity cannot be lower than the ' . $occupied . ' folder(s) already stored on this shelf.');
        }

        $updated = $this->rackShelfModel->update($locationId, [
            'rack' => $rack,
            'shelf' => $shelf,
            'capacity' => $newCapacity,
        ]);

        if (! $updated) {
            return redirect()->to(route_to('racks.edit', $locationId))->withInput()->with('errors', $this->rackShelfModel->errors());
        }

        // Cascade to every folder at this location so location_code doesn't
        // go stale until someone happens to open that folder individually.
        \Config\Database::connect()->table('folders')
            ->where('location_id', $locationId)
            ->update(['location_code' => FileCodeGenerator::generateLocationCode($rack, $shelf)]);

        return redirect()->to('/manage-racks')->with('success', 'Shelf updated to rack "' . $rack . '", shelf "' . $shelf . '".');
    }

    public function delete(int $locationId)
    {
        $location = $this->rackShelfModel->find($locationId);
        if (! $location) {
            return redirect()->to('/manage-racks')->with('error', 'Location not found.');
        }

        if (! $this->canDeleteLocation($locationId)) {
            return redirect()->to('/manage-racks')->with('error', 'Cannot delete shelf "' . $location['shelf'] . '" in rack "' . $location['rack'] . '" because it is still used by records.');
        }

        $this->rackShelfModel->delete($locationId);

        return redirect()->to('/manage-racks')->with('success', 'Shelf "' . $location['shelf'] . '" from rack "' . $location['rack'] . '" deleted.');
    }

    public function deleteRack()
    {
        $rules = [
            'delete_rack' => 'required|max_length[50]',
            'rack_confirm_text' => 'required|max_length[50]',
            'rack_confirm_ack' => 'required|in_list[1]',
        ];
        $messages = [
            'delete_rack' => [
                'required' => 'Please select a rack to delete.',
                'max_length' => 'Selected rack value is too long.',
            ],
            'rack_confirm_text' => [
                'required' => 'Type the rack name to confirm deletion.',
                'max_length' => 'Confirmation text is too long.',
            ],
            'rack_confirm_ack' => [
                'required' => 'You must confirm you understand this action cannot be undone.',
                'in_list' => 'You must confirm you understand this action cannot be undone.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $rack = trim((string) $this->request->getPost('delete_rack'));
        $rackConfirmText = trim((string) $this->request->getPost('rack_confirm_text'));

        if ($rackConfirmText !== $rack) {
            return redirect()->back()->withInput()->with('error', 'Confirmation text must exactly match the selected rack name ("' . $rack . '").');
        }

        $locations = $this->rackShelfModel->where('rack', $rack)->findAll();

        if (empty($locations)) {
            return redirect()->to('/manage-racks')->with('error', 'Rack not found.');
        }

        $blockedShelves = [];
        foreach ($locations as $location) {
            if (! $this->canDeleteLocation((int) $location['location_id'])) {
                $blockedShelves[] = $location['shelf'];
            }
        }

        if (! empty($blockedShelves)) {
            return redirect()->to('/manage-racks')->with('error', 'Cannot delete rack "' . $rack . '". Blocked shelves: ' . implode(', ', $blockedShelves) . '.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->rackShelfModel->where('rack', $rack)->delete();

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/manage-racks')->with('error', 'Failed to delete rack.');
        }

        return redirect()->to('/manage-racks')->with('success', 'Rack "' . $rack . '" deleted with ' . count($locations) . ' shelf(s).');
    }

    protected function canDeleteLocation(int $locationId): bool
    {
        $db = \Config\Database::connect();

        $foldersCount = (int) $db->table('folders')->where('location_id', $locationId)->countAllResults();
        $relocationFromCount = (int) $db->table('relocation_requests')->where('from_location_id', $locationId)->countAllResults();
        $relocationToCount = (int) $db->table('relocation_requests')->where('to_location_id', $locationId)->countAllResults();
        $archiveCount = (int) $db->table('archive_records')->where('archive_location_id', $locationId)->countAllResults();

        return ($foldersCount + $relocationFromCount + $relocationToCount + $archiveCount) === 0;
    }
}
