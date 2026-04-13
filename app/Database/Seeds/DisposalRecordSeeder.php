<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DisposalRecordSeeder extends Seeder
{
    public function run()
    {
        // Get archive records (disposal depends on archives)
        $archives = $this->db->table('archive_records')
            ->select('archive_id, folder_id')
            ->orderBy('archive_id', 'ASC')
            ->get()
            ->getResultArray();

        $users = $this->db->table('users')->get()->getResultArray();

        if (empty($archives) || empty($users)) {
            echo "⚠️  Skipping DisposalRecordSeeder: Requires archive records and users to be seeded first.\n";
            return;
        }

        $approver = $users[1] ?? $users[0]; // Admin

        $disposals = [];
        
        // Use first 3 archives for disposal seeding
        $archiveCount = 0;
        foreach ($archives as $archive) {
            if ($archiveCount >= 3) break;

            if ($archiveCount === 0) {
                // Pending disposal (disposal_date IS NULL)
                $disposals[] = [
                    'archive_id' => $archive['archive_id'],
                    'disposal_date' => null, // Pending
                    'disposal_method' => 'Destruction',
                    'approved_by' => $approver['user_id'],
                    'compliance_reference' => 'COMP-2026-001',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-20 days')),
                ];
            } elseif ($archiveCount === 1) {
                // Completed disposal (disposal_date IS set) - Destruction
                $disposals[] = [
                    'archive_id' => $archive['archive_id'],
                    'disposal_date' => date('Y-m-d', strtotime('-10 days')),
                    'disposal_method' => 'Destruction',
                    'approved_by' => $approver['user_id'],
                    'compliance_reference' => 'COMP-2026-002',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                ];
            } else {
                // Completed disposal - Transfer
                $disposals[] = [
                    'archive_id' => $archive['archive_id'],
                    'disposal_date' => date('Y-m-d', strtotime('-5 days')),
                    'disposal_method' => 'Transfer',
                    'approved_by' => $approver['user_id'],
                    'compliance_reference' => 'COMP-2026-003',
                    'created_at' => date('Y-m-d H:i:s', strtotime('-25 days')),
                ];
            }

            $archiveCount++;
        }

        foreach ($disposals as $disposal) {
            $existing = $this->db->table('disposal_records')
                ->where('archive_id', $disposal['archive_id'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $this->db->table('disposal_records')
                    ->where('disposal_id', $existing['disposal_id'])
                    ->update($disposal);
                continue;
            }

            $this->db->table('disposal_records')->insert($disposal);
        }
    }
}
