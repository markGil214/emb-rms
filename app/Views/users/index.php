<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $users = $users ?? []; ?>
<?php $filters = $filters ?? []; ?>
<?php $stats = $stats ?? ['total' => 0, 'active' => 0, 'inactive' => 0, 'pending' => 0]; ?>
<?php $currentUserId = session()->get('user_id'); ?>

<style>
    /* Custom Vanilla CSS for precise UI control */
    .filter-container {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 16px 24px;
        margin-bottom: 24px;
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .search-wrapper {
        position: relative;
        flex: 1;
        min-width: 300px;
    }

    .search-wrapper svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        width: 18px;
        height: 18px;
    }

    .custom-input {
        width: 100%;
        height: 42px;
        padding: 0 12px 0 40px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        background: #ffffff;
    }

    .custom-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .custom-select {
        height: 42px;
        padding: 0 36px 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        color: #1e293b;
        background-color: #ffffff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 16px;
        appearance: none;
        outline: none;
        min-width: 140px;
        cursor: pointer;
    }

    .custom-select:focus {
        border-color: #3b82f6;
    }

    .filter-btn {
        height: 42px;
        padding: 0 24px;
        background: #1e293b;
        color: #ffffff;
        font-weight: 600;
        font-size: 14px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .filter-btn:hover {
        background: #0f172a;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-info p:first-child {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-info p:last-child {
        font-size: 24px;
        font-weight: 900;
        color: #0f172a;
        margin-top: 2px;
    }

    .user-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .status-active { background: #ecfdf5; color: #065f46; }
    .status-active::before { background: #10b981; }
    
    .status-inactive { background: #fef2f2; color: #991b1b; }
    .status-inactive::before { background: #ef4444; }

    .status-pending { background: #fffbeb; color: #92400e; }
    .status-pending::before { background: #f59e0b; }

    .role-badge {
        padding: 4px 10px;
        background: #f1f5f9;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        border-radius: 6px;
        text-transform: uppercase;
        border: 1px solid #e2e8f0;
    }

    /* Action Dropdown Styling */
    .action-select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quick-select {
        height: 34px;
        padding: 0 28px 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #475569;
        background-color: #f8fafc;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 6px center;
        background-size: 12px;
        appearance: none;
        outline: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .quick-select:hover:not(:disabled) {
        background-color: #ffffff;
        border-color: #94a3b8;
    }

    .quick-select:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .save-action-btn {
        height: 34px;
        padding: 0 16px;
        background: #3b82f6;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }

    .save-action-btn:hover:not(:disabled) {
        background: #2563eb;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
    }

    .save-action-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #94a3b8;
    }

    /* Alert Styling */
    .alert-box {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bcf0da;
        color: #166534;
    }

    .alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }
</style>

<div class="max-w-8xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert-box alert-success">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert-box alert-warning">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <?= session()->getFlashdata('warning') ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">User Management</h1>
            <p class="text-base text-gray-500 mt-1">Manage system access, monitor account status, and control security blocks.</p>
        </div>
        <a href="<?= route_to('users.create') ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-lg shadow-blue-100 transition-all transform hover:-translate-y-0.5">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Add New User
        </a>
    </div>

    <!-- Filters Section -->
    <div class="filter-container">
        <form method="GET" action="<?= route_to('users.index') ?>">
            <div class="filter-row">
                <div class="search-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Search by name, email, or role..." class="custom-input">
                </div>
                
                <select name="status" class="custom-select">
                    <option value="">All Statuses</option>
                    <option value="Active" <?= ($filters['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= ($filters['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="Pending" <?= ($filters['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                </select>

                <select name="limit" class="custom-select" style="min-width: 80px;">
                    <option value="25" <?= ($filters['limit'] ?? '25') === '25' ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= ($filters['limit'] ?? '') === '50' ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= ($filters['limit'] ?? '') === '100' ? 'selected' : '' ?>>100</option>
                    <option value="all" <?= ($filters['limit'] ?? '') === 'all' ? 'selected' : '' ?>>All</option>
                </select>

                <button type="submit" class="filter-btn">Apply Filter</button>
            </div>
        </form>
    </div>

    <!-- Stats Section -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue-50 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="stat-info">
                <p>Total Users</p>
                <p><?= (int) $stats['total'] ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-emerald-50 text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="stat-info">
                <p>Active</p>
                <p><?= (int) $stats['active'] ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-amber-50 text-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="stat-info">
                <p>Pending</p>
                <p><?= (int) $stats['pending'] ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-rose-50 text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <div class="stat-info">
                <p>Inactive</p>
                <p><?= (int) $stats['inactive'] ?></p>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="user-table-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-widest">ID</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-widest">Email</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-widest">Role</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-widest">Activity</th>
                        <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-500 uppercase tracking-widest">Quick Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 text-sm font-medium">No users found match your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <?php
                                $fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
                                $displayName = $fullName !== '' ? $fullName : ($user['username'] ?? '-');
                                $status = trim((string) ($user['status'] ?? 'Active')) ?: 'Active';
                                $initials = strtoupper(substr($user['username'] ?? 'U', 0, 1));
                                $isSelf = ($currentUserId == $user['user_id']);
                                
                                $statusClass = 'status-active';
                                if ($status === 'Inactive') $statusClass = 'status-inactive';
                                if ($status === 'Pending') $statusClass = 'status-pending';
                            ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400">#<?= esc($user['user_id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-black"><?= $initials ?></div>
                                        <div class="text-sm font-bold text-gray-900"><?= esc($displayName) ?> <?= $isSelf ? '<span class="text-[10px] text-blue-500 font-bold ml-1">(You)</span>' : '' ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= esc($user['email'] ?? '') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="role-badge"><?= esc($user['role_name'] ?? ($user['role'] ?? 'USER')) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= esc($status) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[11px] text-gray-500">
                                    Joined: <?= !empty($user['created_at']) ? esc(date('M d, Y', strtotime($user['created_at']))) : '--' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="flex justify-end">
                                        <form method="GET" action="<?= route_to('users.status-confirm', $user['user_id']) ?>" class="action-select-wrapper">
                                            <select name="status" class="quick-select" <?= $isSelf ? 'disabled' : '' ?> title="<?= $isSelf ? 'You cannot deactivate yourself' : '' ?>">
                                                <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                                                <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                                <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            </select>
                                            <button type="submit" class="save-action-btn" <?= $isSelf ? 'disabled' : '' ?>>
                                                Update
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>