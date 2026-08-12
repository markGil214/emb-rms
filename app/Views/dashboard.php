<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .dashboard-shell {
        padding: 1.5rem;
    }

    .dashboard-stats-grid {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 1rem !important;
        margin-bottom: 2rem !important;
    }

    .dashboard-content-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 1rem !important;
    }

    .dashboard-recent-users {
        grid-column: span 2 / span 2;
    }

    @media (max-width: 1279px) {
        .dashboard-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .dashboard-content-grid {
            grid-template-columns: 1fr !important;
        }

        .dashboard-recent-users {
            grid-column: auto;
        }
    }

    @media (max-width: 639px) {
        .dashboard-shell {
            padding: 1rem;
        }

        .dashboard-stats-grid {
            grid-template-columns: 1fr !important;
        }
    }

    /* Insight tiles: a coloured left accent carries the health state, so the
       number is not the only thing distinguishing a healthy tile from one
       that needs action. */
    .stat-tile {
        position: relative;
        overflow: hidden;
        padding: 1rem 1rem 1rem 1.25rem;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .stat-tile:hover {
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        transform: translateY(-2px);
    }

    .stat-tile::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: #cbd5e1;
    }

    .stat-tile--info::before {
        background: linear-gradient(180deg, #60a5fa, #2563eb);
    }

    .stat-tile--good::before {
        background: linear-gradient(180deg, #86efac, #16a34a);
    }

    .stat-tile--warn::before {
        background: linear-gradient(180deg, #fcd34d, #d97706);
    }

    .stat-tile--critical::before {
        background: linear-gradient(180deg, #fca5a5, #dc2626);
    }

    .stat-tile__label {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }

    .stat-tile__value {
        margin-top: 0.35rem;
        font-size: 1.875rem;
        font-weight: 800;
        line-height: 1.1;
        color: #0f172a;
    }

    .stat-tile__caption {
        margin-top: 0.35rem;
        font-size: 0.75rem;
        color: #64748b;
    }

    .stat-tile__icon {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 9999px;
    }

    .stat-tile--info .stat-tile__icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-tile--good .stat-tile__icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .stat-tile--warn .stat-tile__icon {
        background: #fef3c7;
        color: #d97706;
    }

    .stat-tile--critical .stat-tile__icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .stat-tile__pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.6rem;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.625rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .stat-tile--info .stat-tile__pill {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .stat-tile--good .stat-tile__pill {
        background: #f0fdf4;
        color: #15803d;
    }

    .stat-tile--warn .stat-tile__pill {
        background: #fffbeb;
        color: #b45309;
    }

    .stat-tile--critical .stat-tile__pill {
        background: #fef2f2;
        color: #b91c1c;
    }

    .stat-tile__bar {
        margin-top: 0.75rem;
        height: 0.375rem;
        border-radius: 9999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .stat-tile__bar span {
        display: block;
        height: 100%;
        border-radius: 9999px;
        transition: width 0.4s ease;
    }

    .stat-tile--info .stat-tile__bar span {
        background: #2563eb;
    }

    .stat-tile--good .stat-tile__bar span {
        background: #16a34a;
    }

    .stat-tile--warn .stat-tile__bar span {
        background: #d97706;
    }

    .stat-tile--critical .stat-tile__bar span {
        background: #dc2626;
    }

    .stat-tile__link {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.6rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: #2563eb;
    }

    .stat-tile__link:hover {
        color: #1e3a8a;
    }

    .dark .stat-tile {
        background: var(--color-bg-secondary, #1a1f2e);
        border-color: var(--color-border, #334155);
    }

    .dark .stat-tile__value {
        color: var(--color-text, #f1f5f9);
    }

    .dark .stat-tile__label,
    .dark .stat-tile__caption {
        color: var(--color-text-muted, #94a3b8);
    }

    .dark .stat-tile__bar {
        background: #334155;
    }

    .dark .stat-tile--info .stat-tile__icon {
        background: rgba(59, 130, 246, 0.2);
        color: #93c5fd;
    }

    .dark .stat-tile--good .stat-tile__icon {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }

    .dark .stat-tile--warn .stat-tile__icon {
        background: rgba(245, 158, 11, 0.2);
        color: #fcd34d;
    }

    .dark .stat-tile--critical .stat-tile__icon {
        background: rgba(220, 38, 38, 0.2);
        color: #fca5a5;
    }

    .dark .stat-tile--info .stat-tile__pill {
        background: rgba(59, 130, 246, 0.15);
        color: #93c5fd;
    }

    .dark .stat-tile--good .stat-tile__pill {
        background: rgba(34, 197, 94, 0.15);
        color: #86efac;
    }

    .dark .stat-tile--warn .stat-tile__pill {
        background: rgba(245, 158, 11, 0.15);
        color: #fcd34d;
    }

    .dark .stat-tile--critical .stat-tile__pill {
        background: rgba(220, 38, 38, 0.15);
        color: #fca5a5;
    }

    /* List panels share the tile vocabulary -- same shell, same tone names,
       same icon treatment -- so the two kinds of card read as one system. */
    .dash-panel {
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        overflow: hidden;
        transition: box-shadow 0.2s ease;
    }

    .dash-panel:hover {
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
    }

    .dash-panel__head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }

    .dash-panel__icon {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.625rem;
    }

    .dash-panel--info .dash-panel__icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .dash-panel--good .dash-panel__icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .dash-panel--warn .dash-panel__icon {
        background: #fef3c7;
        color: #d97706;
    }

    .dash-panel--critical .dash-panel__icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .dash-panel__title {
        font-size: 0.9375rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }

    .dash-panel__subtitle {
        margin-top: 0.15rem;
        font-size: 0.6875rem;
        color: #64748b;
    }

    .dash-panel__count {
        margin-left: auto;
        flex-shrink: 0;
        padding: 0.2rem 0.6rem;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #64748b;
    }

    .dash-panel--warn .dash-panel__count {
        background: #fef3c7;
        color: #b45309;
    }

    .dash-panel--critical .dash-panel__count {
        background: #fee2e2;
        color: #b91c1c;
    }

    .dash-panel--good .dash-panel__count {
        background: #dcfce7;
        color: #15803d;
    }

    .dash-panel__body {
        padding: 0.75rem 1.25rem 1rem;
    }

    .dash-label {
        margin: 0.5rem 0 0.5rem;
        font-size: 0.625rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .dash-row {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f8fafc;
    }

    .dash-row:last-child {
        border-bottom: 0;
    }

    .dash-row--muted {
        opacity: 0.55;
    }

    .dash-row__dot {
        flex-shrink: 0;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 9999px;
        background: #cbd5e1;
    }

    .dash-row__dot--good {
        background: #16a34a;
    }

    .dash-row__dot--warn {
        background: #d97706;
    }

    .dash-row__dot--critical {
        background: #dc2626;
    }

    .dash-row__main {
        min-width: 0;
        flex: 1;
    }

    .dash-row__label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dash-row__meta {
        margin-top: 0.1rem;
        font-size: 0.6875rem;
        color: #94a3b8;
    }

    .dash-row__meta--warn {
        color: #b45309;
        font-weight: 700;
    }

    .dash-row__meta--critical {
        color: #b91c1c;
        font-weight: 700;
    }

    .dash-row__value {
        flex-shrink: 0;
        min-width: 1.75rem;
        text-align: center;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 800;
        background: #f1f5f9;
        color: #475569;
    }

    .dash-row__value--good {
        background: #dcfce7;
        color: #15803d;
    }

    .dash-row__value--warn {
        background: #fef3c7;
        color: #b45309;
    }

    .dash-row__value--critical {
        background: #fee2e2;
        color: #b91c1c;
    }

    .dash-row__value--zero {
        background: #f8fafc;
        color: #cbd5e1;
    }

    .dash-avatar {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 9999px;
        background: #e0e7ff;
        color: #4338ca;
        font-size: 0.6875rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .dash-meter {
        margin-top: 0.3rem;
        height: 0.25rem;
        border-radius: 9999px;
        background: #f1f5f9;
        overflow: hidden;
    }

    .dash-meter span {
        display: block;
        height: 100%;
        border-radius: 9999px;
        background: #6366f1;
    }

    .dash-empty {
        padding: 1.25rem 0;
        text-align: center;
        font-size: 0.8125rem;
        color: #94a3b8;
    }

    .dash-empty__icon {
        width: 2rem;
        height: 2rem;
        margin: 0 auto 0.4rem;
        color: #cbd5e1;
    }

    .dark .dash-panel {
        background: var(--color-bg-secondary, #1a1f2e);
        border-color: var(--color-border, #334155);
    }

    .dark .dash-panel__head {
        border-color: var(--color-border, #334155);
    }

    .dark .dash-panel__title {
        color: var(--color-text, #f1f5f9);
    }

    .dark .dash-panel__subtitle,
    .dark .dash-label,
    .dark .dash-row__meta {
        color: var(--color-text-muted, #94a3b8);
    }

    .dark .dash-row {
        border-color: rgba(148, 163, 184, 0.12);
    }

    .dark .dash-row__label {
        color: var(--color-text-secondary, #cbd5e1);
    }

    .dark .dash-row__value {
        background: #334155;
        color: #cbd5e1;
    }

    .dark .dash-row__value--zero {
        background: rgba(148, 163, 184, 0.12);
        color: #64748b;
    }

    .dark .dash-row__value--good {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }

    .dark .dash-row__value--warn {
        background: rgba(245, 158, 11, 0.2);
        color: #fcd34d;
    }

    .dark .dash-row__value--critical {
        background: rgba(220, 38, 38, 0.2);
        color: #fca5a5;
    }

    .dark .dash-panel__count {
        background: #334155;
        color: #cbd5e1;
    }

    .dark .dash-panel--warn .dash-panel__count {
        background: rgba(245, 158, 11, 0.2);
        color: #fcd34d;
    }

    .dark .dash-panel--critical .dash-panel__count {
        background: rgba(220, 38, 38, 0.2);
        color: #fca5a5;
    }

    .dark .dash-panel--good .dash-panel__count {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }

    .dark .dash-panel--info .dash-panel__icon {
        background: rgba(59, 130, 246, 0.2);
        color: #93c5fd;
    }

    .dark .dash-panel--good .dash-panel__icon {
        background: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }

    .dark .dash-panel--warn .dash-panel__icon {
        background: rgba(245, 158, 11, 0.2);
        color: #fcd34d;
    }

    .dark .dash-panel--critical .dash-panel__icon {
        background: rgba(220, 38, 38, 0.2);
        color: #fca5a5;
    }

    .dark .dash-avatar {
        background: rgba(99, 102, 241, 0.25);
        color: #c7d2fe;
    }

    .dark .dash-meter {
        background: #334155; 
    }
</style>

<!-- Welcome Section -->
<div class="dashboard-shell p-4 sm:p-6 lg:p-8">
    <div class="mb-8">
        <p class="text-gray-600 text-sm">Here's what's happening in your system today.</p>
    </div>

    <?php
    // Shared by both card rows below.
    $icon = static function (string $path): string {
        return '<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">'
            . '<path stroke-linecap="round" stroke-linejoin="round" d="' . $path . '"></path></svg>';
    };

    // Each role's controller supplies a different key for these
    // two figures, so fall through the alternatives rather than
    // showing a hardcoded zero to admins and records officers.
    $totalDocuments = (int) ($stats['totalDocuments'] ?? $stats['accessibleDocuments'] ?? 0);
    $pendingCount = (int) ($stats['pendingApprovals'] ?? $stats['pendingRequests'] ?? 0);
    $availableCount = (int) ($documentStats['availableCount'] ?? 0);
    $availableShare = $totalDocuments > 0
        ? (int) round(($availableCount / $totalDocuments) * 100)
        : 0;
    ?>

    <!-- Stats Grid -->
    <div class="dashboard-stats-grid grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6 mb-8">

        <!-- Total Folders -->
        <div class="stat-tile stat-tile--info">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="stat-tile__label">Total Documents</p>
                    <p class="stat-tile__value"><?= number_format($totalDocuments) ?></p>
                    <p class="stat-tile__caption">In system</p>
                </div>
                <div class="stat-tile__icon"><?= $icon('M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z') ?></div>
            </div>
            <?php if (is_super_admin() && isset($stats['totalArchived'])): ?>
                <span class="stat-tile__pill"><?= number_format((int) $stats['totalArchived']) ?> archived</span>
            <?php endif; ?>
        </div>

        <?php if (is_super_admin()): ?>
            <!-- Active Users -->
            <div class="stat-tile stat-tile--good">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="stat-tile__label">Active Users</p>
                        <p class="stat-tile__value"><?= number_format((int) ($stats['totalUsers'] ?? 0)) ?></p>
                        <p class="stat-tile__caption">Registered accounts</p>
                    </div>
                    <div class="stat-tile__icon"><?= $icon('M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z') ?></div>
                </div>
                <?php if (!empty($userInsights['byRole'])): ?>
                    <span class="stat-tile__pill"><?= count($userInsights['byRole']) ?> role(s) in use</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Available Documents -->
        <div class="stat-tile stat-tile--good">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="stat-tile__label">Available</p>
                    <p class="stat-tile__value"><?= number_format($availableCount) ?></p>
                    <p class="stat-tile__caption">Ready to borrow</p>
                </div>
                <div class="stat-tile__icon"><?= $icon('M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z') ?></div>
            </div>
            <?php if ($totalDocuments > 0): ?>
                <div class="stat-tile__bar"><span style="width: <?= max(2, $availableShare) ?>%"></span></div>
                <span class="stat-tile__pill"><?= $availableShare ?>% of all documents</span>
            <?php endif; ?>
        </div>

        <!-- Pending Approvals -->
        <div class="stat-tile stat-tile--<?= $pendingCount > 0 ? 'warn' : 'good' ?>">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="stat-tile__label">Pending</p>
                    <p class="stat-tile__value"><?= number_format($pendingCount) ?></p>
                    <p class="stat-tile__caption">Awaiting approval</p>
                </div>
                <div class="stat-tile__icon"><?= $icon('M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-4h4') ?></div>
            </div>
            <span class="stat-tile__pill"><?= $pendingCount > 0 ? 'Needs review' : 'All clear' ?></span>
        </div>

    </div>

    <!-- Content Grid -->
    <div class="dashboard-content-grid grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

        <?php if (is_super_admin()): ?>
            <!-- Recent Users Table -->
            <div class="dashboard-recent-users xl:col-span-2 dash-panel dash-panel--info">
                <div class="dash-panel__head">
                    <div class="dash-panel__icon"><?= $icon('M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z') ?></div>
                    <div class="min-w-0">
                        <p class="dash-panel__title">Recent Users</p>
                        <p class="dash-panel__subtitle">Newest registered accounts</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($recentUsers as $user): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 font-semibold text-sm">
                                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: '-') ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?= $user['role'] === 'SuperAdmin' ? 'bg-purple-100 text-purple-800' : ($user['role'] === 'Admin' ? 'bg-blue-100 text-blue-800' :
                                                        'bg-green-100 text-green-800') ?>">
                                            <?= htmlspecialchars($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= (($user['status'] ?? 'Active') === 'Inactive') ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                            <?= htmlspecialchars($user['status'] ?? 'Active') ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-200">
                    <a href="<?= route_to('users.index') ?>" class="w-full px-4 py-2 text-sm bg-gray-50 text-gray-700 hover:bg-100 rounded-lg font-medium transition-colors">
                        View all users
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Document Status Chart -->
        <div class="<?= is_super_admin() ? '' : 'xl:col-span-3' ?> dash-panel dash-panel--info">
            <div class="dash-panel__head">
                <div class="dash-panel__icon"><?= $icon('M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z') ?></div>
                <div class="min-w-0">
                    <p class="dash-panel__title">Document Status</p>
                    <p class="dash-panel__subtitle">Current lifecycle distribution</p>
                </div>
            </div>
            <div class="dash-panel__body">
                <div class="relative w-48 h-48 mx-auto">
                    <canvas id="myChart" width="192" height="192"></canvas>
                </div>

                <!-- Chart Legend -->
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-gray-700">Available</span>
                        </div>
                        <span class="font-semibold text-gray-900"><?= $documentStats['availableCount'] ?? 0 ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                            <span class="text-gray-700">Borrowed</span>
                        </div>
                        <span class="font-semibold text-gray-900"><?= $documentStats['borrowedCount'] ?? 0 ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                            <span class="text-gray-700">Archived</span>
                        </div>
                        <span class="font-semibold text-gray-900"><?= $documentStats['archivedCount'] ?? 0 ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                            <span class="text-gray-700">Disposed</span>
                        </div>
                        <span class="font-semibold text-gray-900"><?= $documentStats['disposedCount'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php if (is_super_admin()): ?>
        <?php
        $capacity = $capacityInsights ?? [];
        $retention = $retentionInsights ?? [];
        $queue = $approvalQueue ?? [];
        $overdue = $overdueBorrowings ?? ['count' => 0, 'rows' => []];
        $userInsights = $userInsights ?? [];
        $storage = $storageInsights ?? [];

        $formatBytes = static function (int $bytes): string {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $i = 0;
            while ($bytes >= 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }

            return round($bytes, $i === 0 ? 0 : 1) . ' ' . $units[$i];
        };

        $ageInDays = static function (?string $timestamp): ?int {
            if (empty($timestamp) || $timestamp === '0000-00-00 00:00:00') {
                return null;
            }

            $time = strtotime($timestamp);
            if ($time === false) {
                return null;
            }

            return (int) floor((time() - $time) / 86400);
        };

        $queueTotal = array_sum(array_column($queue, 'count'));
        ?>

        <!-- ============ SUPER ADMIN INSIGHTS ============ -->
        <div class="mt-8 space-y-6">

            <!-- Row: storage + retention + integrity summary tiles -->
            <?php
            $utilisation = (int) ($capacity['utilisation'] ?? 0);
            $unsetShelves = (int) ($capacity['unsetShelves'] ?? 0);
            $overdueCount = (int) ($overdue['count'] ?? 0);

            $utilTone = $utilisation >= 90 ? 'critical' : ($utilisation >= 75 ? 'warn' : 'info');
            $utilPill = $utilisation >= 90 ? 'Critical' : ($utilisation >= 75 ? 'Filling up' : 'Healthy');
            ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                <div class="stat-tile stat-tile--<?= $utilTone ?>">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="stat-tile__label">Shelf Utilization</p>
                            <p class="stat-tile__value"><?= $utilisation ?>%</p>
                            <p class="stat-tile__caption"><?= (int) ($capacity['totalOccupied'] ?? 0) ?> of <?= (int) ($capacity['totalCapacity'] ?? 0) ?> slots used</p>
                        </div>
                        <div class="stat-tile__icon"><?= $icon('M5 12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v5zm0 0v5a2 2 0 002 2h10a2 2 0 002-2v-5M9 9h.01M9 16h.01') ?></div>
                    </div>
                    <div class="stat-tile__bar"><span style="width: <?= max(2, $utilisation) ?>%"></span></div>
                    <span class="stat-tile__pill"><?= $utilPill ?></span>
                </div>

                <div class="stat-tile stat-tile--<?= $unsetShelves > 0 ? 'warn' : 'good' ?>">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="stat-tile__label">Shelves Needing Setup</p>
                            <p class="stat-tile__value"><?= $unsetShelves ?></p>
                            <p class="stat-tile__caption">No capacity set &mdash; cannot accept folders</p>
                        </div>
                        <div class="stat-tile__icon"><?= $icon('M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z') ?></div>
                    </div>
                    <?php if ($unsetShelves > 0): ?>
                        <a href="<?= route_to('racks.index') ?>" class="stat-tile__link">Fix in Manage Racks &rarr;</a>
                    <?php else: ?>
                        <span class="stat-tile__pill">All configured</span>
                    <?php endif; ?>
                </div>

                <div class="stat-tile stat-tile--info">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="stat-tile__label">Stored Files</p>
                            <p class="stat-tile__value"><?= number_format((int) ($storage['fileCount'] ?? 0)) ?></p>
                            <p class="stat-tile__caption"><?= esc($formatBytes((int) ($storage['fileBytes'] ?? 0))) ?> on disk</p>
                        </div>
                        <div class="stat-tile__icon"><?= $icon('M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z') ?></div>
                    </div>
                    <span class="stat-tile__pill"><?= (int) ($storage['foldersWithoutFiles'] ?? 0) ?> folder(s) with no files</span>
                </div>

                <div class="stat-tile stat-tile--<?= $overdueCount > 0 ? 'critical' : 'good' ?>">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="stat-tile__label">Overdue Borrowings</p>
                            <p class="stat-tile__value"><?= $overdueCount ?></p>
                            <p class="stat-tile__caption">Past expected return date</p>
                        </div>
                        <div class="stat-tile__icon"><?= $icon('M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z') ?></div>
                    </div>
                    <span class="stat-tile__pill"><?= $overdueCount > 0 ? 'Needs follow-up' : 'On track' ?></span>
                </div>

            </div>

            <!-- Row: activity trend + approval queue -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="xl:col-span-2 dash-panel dash-panel--info">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">Records Activity (12 Months)</p>
                            <p class="dash-panel__subtitle">Created vs archived vs disposed per month</p>
                        </div>
                    </div>
                    <div class="dash-panel__body">
                        <div class="relative" style="height: 260px;">
                            <canvas id="activityTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <?php
                // The queue is only "stale" if something has actually
                // been waiting a week; that drives the panel's tone.
                $oldestWait = 0;
                foreach ($queue as $item) {
                    if ((int) $item['count'] > 0) {
                        $oldestWait = max($oldestWait, (int) ($ageInDays($item['oldest'] ?? null) ?? 0));
                    }
                }
                $queueTone = $queueTotal === 0 ? 'good' : ($oldestWait >= 7 ? 'critical' : 'warn');
                ?>
                <div class="dash-panel dash-panel--<?= $queueTone ?>">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v8m5-4h4') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">Approval Queue</p>
                            <p class="dash-panel__subtitle">
                                <?php if ($queueTotal === 0): ?>
                                    Nothing awaiting action
                                <?php elseif ($oldestWait > 0): ?>
                                    Oldest has waited <?= $oldestWait ?> day<?= $oldestWait === 1 ? '' : 's' ?>
                                <?php else: ?>
                                    <?= $queueTotal ?> item(s) awaiting action
                                <?php endif; ?>
                            </p>
                        </div>
                        <span class="dash-panel__count"><?= $queueTotal ?></span>
                    </div>
                    <div class="dash-panel__body">
                        <?php foreach ($queue as $item): ?>
                            <?php
                            $count = (int) $item['count'];
                            $age = $ageInDays($item['oldest'] ?? null);
                            $rowTone = $count === 0 ? '' : ($age !== null && $age >= 7 ? 'critical' : 'warn');
                            ?>
                            <div class="dash-row <?= $count === 0 ? 'dash-row--muted' : '' ?>">
                                <span class="dash-row__dot <?= $count === 0 ? 'dash-row__dot--good' : 'dash-row__dot--' . $rowTone ?>"></span>
                                <div class="dash-row__main">
                                    <p class="dash-row__label"><?= esc($item['label']) ?></p>
                                    <?php if ($count > 0 && $age !== null): ?>
                                        <p class="dash-row__meta <?= $age >= 7 ? 'dash-row__meta--critical' : 'dash-row__meta--warn' ?>">
                                            Oldest: <?= $age ?> day<?= $age === 1 ? '' : 's' ?> waiting
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <span class="dash-row__value <?= $count === 0 ? 'dash-row__value--zero' : 'dash-row__value--' . $rowTone ?>"><?= $count ?></span>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($queue)): ?>
                            <div class="dash-empty">
                                <svg class="dash-empty__icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Nothing awaiting approval.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Row: documents by folder type -->
            <?php
            $typeData = $typeBreakdown ?? ['types' => [], 'total' => 0, 'top' => null];
            $typeRows = $typeData['types'] ?? [];
            $topType = $typeData['top'] ?? null;
            ?>
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="xl:col-span-2 dash-panel dash-panel--info">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M9 17V7m4 10V11m4 6V9M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">Documents by Type</p>
                            <p class="dash-panel__subtitle">
                                <?php if ($topType): ?>
                                    <?= esc($topType['label']) ?> leads with <?= (int) $topType['count'] ?> of <?= (int) $typeData['total'] ?>
                                <?php else: ?>
                                    No documents recorded yet
                                <?php endif; ?>
                            </p>
                        </div>
                        <span class="dash-panel__count"><?= (int) $typeData['total'] ?></span>
                    </div>
                    <div class="dash-panel__body">
                        <?php if (!empty($typeRows)): ?>
                            <div class="relative" style="height: <?= max(180, count($typeRows) * 52) ?>px;">
                                <canvas id="typeBreakdownChart"></canvas>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 text-center py-8">No documents to chart yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dash-panel dash-panel--info">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">Type Ranking</p>
                            <p class="dash-panel__subtitle"><?= count($typeRows) ?> type(s) in use</p>
                        </div>
                    </div>
                    <div class="dash-panel__body space-y-3">
                        <?php foreach ($typeRows as $rank => $type): ?>
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="flex-shrink-0 w-5 h-5 rounded-full text-[10px] font-bold flex items-center justify-center <?= $rank === 0 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' ?>">
                                            <?= $rank + 1 ?>
                                        </span>
                                        <span class="text-sm text-gray-700 truncate" title="<?= esc($type['label'], 'attr') ?>"><?= esc($type['label']) ?></span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-900 whitespace-nowrap"><?= (int) $type['count'] ?></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full <?= $rank === 0 ? 'bg-blue-600' : 'bg-gray-400' ?>" style="width: <?= max(2, (int) $type['percent']) ?>%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5"><?= (int) $type['percent'] ?>% of all documents</p>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($typeRows)): ?>
                            <p class="text-sm text-gray-500 text-center py-4">Nothing to rank yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Row: shelf utilization + retention + audit activity -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="dash-panel dash-panel--<?= (int) ($capacity['fullShelves'] ?? 0) > 0 ? 'warn' : 'info' ?>">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M5 12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v5zm0 0v5a2 2 0 002 2h10a2 2 0 002-2v-5M9 9h.01M9 16h.01') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">Busiest Shelves</p>
                            <p class="dash-panel__subtitle"><?= (int) ($capacity['fullShelves'] ?? 0) ?> at capacity &middot; <?= (int) ($capacity['totalShelves'] ?? 0) ?> total</p>
                        </div>
                    </div>
                    <div class="dash-panel__body">
                        <?php if (!empty($capacity['topShelves'])): ?>
                            <div class="relative" style="height: 220px;">
                                <canvas id="shelfUtilChart"></canvas>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 text-center py-8">No shelves with a capacity set yet.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dash-panel dash-panel--<?= (int) ($retention['expired'] ?? 0) > 0 ? 'critical' : ((int) ($retention['due30'] ?? 0) > 0 ? 'warn' : 'info') ?>">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">Retention Outlook</p>
                            <p class="dash-panel__subtitle">Files by retention policy</p>
                        </div>
                    </div>
                    <div class="dash-panel__body">
                        <?php $retentionTotal = (int) ($retention['permanent'] ?? 0) + (int) ($retention['expiring'] ?? 0); ?>
                        <?php if ($retentionTotal > 0): ?>
                            <div class="relative w-40 h-40 mx-auto">
                                <canvas id="retentionChart"></canvas>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 text-center py-6">No files uploaded yet.</p>
                        <?php endif; ?>
                        <div class="mt-4 space-y-2 text-xs">
                            <div class="flex justify-between"><span class="text-gray-600">Expiring in 30 days</span><span class="font-semibold <?= (int) ($retention['due30'] ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900' ?>"><?= (int) ($retention['due30'] ?? 0) ?></span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Expiring in 60 days</span><span class="font-semibold text-gray-900"><?= (int) ($retention['due60'] ?? 0) ?></span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Expiring in 90 days</span><span class="font-semibold text-gray-900"><?= (int) ($retention['due90'] ?? 0) ?></span></div>
                            <div class="flex justify-between"><span class="text-gray-600">Already expired</span><span class="font-semibold <?= (int) ($retention['expired'] ?? 0) > 0 ? 'text-red-600' : 'text-gray-900' ?>"><?= (int) ($retention['expired'] ?? 0) ?></span></div>
                        </div>
                    </div>
                </div>

                <?php
                $topActors = $userInsights['topActors'] ?? [];
                $actionMix = $userInsights['actionMix'] ?? [];
                // Meters are relative to the busiest row in each list.
                $actorPeak = max(1, (int) max(array_merge([0], array_map('intval', array_column($topActors, 'total')))));
                $actionPeak = max(1, (int) max(array_merge([0], array_map('intval', array_column($actionMix, 'total')))));
                ?>
                <div class="dash-panel dash-panel--info">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M13 10V3L4 14h7v7l9-11h-7z') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">System Activity</p>
                            <p class="dash-panel__subtitle">
                                <?= (int) ($userInsights['audit']['last7'] ?? 0) ?> this week &middot;
                                <?= (int) ($userInsights['audit']['last30'] ?? 0) ?> this month
                            </p>
                        </div>
                        <span class="dash-panel__count"><?= (int) ($userInsights['audit']['total'] ?? 0) ?></span>
                    </div>
                    <div class="dash-panel__body">
                        <p class="dash-label">Most Active Users (30d)</p>
                        <?php if (!empty($topActors)): ?>
                            <?php foreach ($topActors as $actor): ?>
                                <?php
                                $name = (string) ($actor['username'] ?? 'Unknown');
                                $total = (int) $actor['total'];
                                ?>
                                <div class="dash-row">
                                    <span class="dash-avatar"><?= esc(mb_substr($name, 0, 1)) ?></span>
                                    <div class="dash-row__main">
                                        <p class="dash-row__label"><?= esc($name) ?></p>
                                        <div class="dash-meter"><span style="width: <?= max(4, (int) round(($total / $actorPeak) * 100)) ?>%"></span></div>
                                    </div>
                                    <span class="dash-row__value"><?= $total ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dash-empty">No recorded activity.</div>
                        <?php endif; ?>

                        <p class="dash-label">Top Actions (30d)</p>
                        <?php if (!empty($actionMix)): ?>
                            <?php foreach ($actionMix as $action): ?>
                                <?php $total = (int) $action['total']; ?>
                                <div class="dash-row">
                                    <span class="dash-row__dot"></span>
                                    <div class="dash-row__main">
                                        <p class="dash-row__label"><?= esc(ucfirst(str_replace('_', ' ', (string) ($action['action'] ?? '—')))) ?></p>
                                        <div class="dash-meter"><span style="width: <?= max(4, (int) round(($total / $actionPeak) * 100)) ?>%"></span></div>
                                    </div>
                                    <span class="dash-row__value"><?= $total ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dash-empty">No recorded actions.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Row: users + needs attention -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <?php
                $byRole = $userInsights['byRole'] ?? [];
                $byStatus = $userInsights['byStatus'] ?? [];
                $userTotal = array_sum(array_map('intval', array_column($byRole, 'total')));
                $rolePeak = max(1, (int) max(array_merge([0], array_map('intval', array_column($byRole, 'total')))));

                $pendingUsers = 0;
                foreach ($byStatus as $row) {
                    if (strcasecmp((string) ($row['status'] ?? ''), 'Pending') === 0) {
                        $pendingUsers = (int) $row['total'];
                    }
                }

                $statusTone = static function (string $status): string {
                    switch (strtolower($status)) {
                        case 'active':
                            return 'good';
                        case 'pending':
                            return 'warn';
                        case 'inactive':
                        case 'suspended':
                            return 'critical';
                        default:
                            return '';
                    }
                };
                ?>
                <div class="dash-panel dash-panel--<?= $pendingUsers > 0 ? 'warn' : 'info' ?>">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon"><?= $icon('M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z') ?></div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">User Accounts</p>
                            <p class="dash-panel__subtitle">
                                <?= $pendingUsers > 0
                                    ? $pendingUsers . ' awaiting activation'
                                    : 'All accounts activated' ?>
                            </p>
                        </div>
                        <span class="dash-panel__count"><?= $userTotal ?></span>
                    </div>
                    <div class="dash-panel__body">
                        <p class="dash-label">By Role</p>
                        <?php if (!empty($byRole)): ?>
                            <?php foreach ($byRole as $row): ?>
                                <?php $total = (int) $row['total']; ?>
                                <div class="dash-row">
                                    <span class="dash-row__dot"></span>
                                    <div class="dash-row__main">
                                        <p class="dash-row__label"><?= esc($row['role'] ?? 'Unassigned') ?></p>
                                        <div class="dash-meter"><span style="width: <?= max(4, (int) round(($total / $rolePeak) * 100)) ?>%"></span></div>
                                    </div>
                                    <span class="dash-row__value"><?= $total ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dash-empty">No roles assigned.</div>
                        <?php endif; ?>

                        <p class="dash-label">By Status</p>
                        <?php if (!empty($byStatus)): ?>
                            <?php foreach ($byStatus as $row): ?>
                                <?php
                                $status = (string) ($row['status'] ?? 'Unknown');
                                $tone = $statusTone($status);
                                ?>
                                <div class="dash-row">
                                    <span class="dash-row__dot <?= $tone ? 'dash-row__dot--' . $tone : '' ?>"></span>
                                    <div class="dash-row__main">
                                        <p class="dash-row__label"><?= esc($status) ?></p>
                                    </div>
                                    <span class="dash-row__value <?= $tone ? 'dash-row__value--' . $tone : '' ?>"><?= (int) $row['total'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="dash-empty">No accounts found.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                // Each check carries its own severity so a clear
                // system reads green rather than as a list of zeros.
                $checks = [
                    [
                        'label' => 'Folders on shelves with no capacity',
                        'count' => (int) ($storage['onUnsetShelves'] ?? 0),
                        'tone' => 'critical',
                        'hint' => 'These shelves cannot legally hold records',
                    ],
                    [
                        'label' => 'Folders with no files attached',
                        'count' => (int) ($storage['foldersWithoutFiles'] ?? 0),
                        'tone' => 'warn',
                        'hint' => 'Records created but never populated',
                    ],
                    [
                        'label' => 'Shelves at full capacity',
                        'count' => (int) ($capacity['fullShelves'] ?? 0),
                        'tone' => 'warn',
                        'hint' => 'No room for new records',
                    ],
                ];

                $issueCount = 0;
                $hasCritical = false;
                foreach ($checks as $check) {
                    if ($check['count'] > 0) {
                        $issueCount++;
                        $hasCritical = $hasCritical || $check['tone'] === 'critical';
                    }
                }
                if (!empty($overdue['rows'])) {
                    $issueCount++;
                    $hasCritical = true;
                }

                $attentionTone = $issueCount === 0 ? 'good' : ($hasCritical ? 'critical' : 'warn');
                ?>
                <div class="dash-panel dash-panel--<?= $attentionTone ?>">
                    <div class="dash-panel__head">
                        <div class="dash-panel__icon">
                            <?= $issueCount === 0
                                ? $icon('M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z')
                                : $icon('M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z') ?>
                        </div>
                        <div class="min-w-0">
                            <p class="dash-panel__title">Needs Attention</p>
                            <p class="dash-panel__subtitle">
                                <?= $issueCount === 0
                                    ? 'Everything looks healthy'
                                    : $issueCount . ' area(s) need review' ?>
                            </p>
                        </div>
                        <span class="dash-panel__count"><?= $issueCount ?></span>
                    </div>
                    <div class="dash-panel__body">
                        <?php foreach ($checks as $check): ?>
                            <?php $isClear = $check['count'] === 0; ?>
                            <div class="dash-row <?= $isClear ? 'dash-row--muted' : '' ?>">
                                <span class="dash-row__dot dash-row__dot--<?= $isClear ? 'good' : $check['tone'] ?>"></span>
                                <div class="dash-row__main">
                                    <p class="dash-row__label"><?= esc($check['label']) ?></p>
                                    <?php if (! $isClear): ?>
                                        <p class="dash-row__meta dash-row__meta--<?= $check['tone'] ?>"><?= esc($check['hint']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <span class="dash-row__value <?= $isClear ? 'dash-row__value--good' : 'dash-row__value--' . $check['tone'] ?>">
                                    <?= $isClear ? '&check;' : $check['count'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!empty($overdue['rows'])): ?>
                            <p class="dash-label">Most Overdue</p>
                            <?php foreach ($overdue['rows'] as $row): ?>
                                <?php $od = $ageInDays($row['expected_return_date'] ?? null); ?>
                                <div class="dash-row">
                                    <span class="dash-row__dot dash-row__dot--critical"></span>
                                    <div class="dash-row__main">
                                        <p class="dash-row__label" title="<?= esc($row['company_name'] ?? '', 'attr') ?>">
                                            <?= esc($row['file_code'] ?? '—') ?>
                                        </p>
                                        <p class="dash-row__meta">Borrowed by <?= esc($row['borrower_name'] ?? 'Unknown') ?></p>
                                    </div>
                                    <span class="dash-row__value dash-row__value--critical">
                                        <?= $od !== null ? $od . 'd' : 'late' ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>

    <!-- Chart JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('myChart').getContext('2d');

            var myChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Available', 'Borrowed', 'Archived', 'Disposed'],
                    datasets: [{
                        data: [<?= $documentStats['availableCount'] ?? 0 ?>, <?= $documentStats['borrowedCount'] ?? 0 ?>, <?= $documentStats['archivedCount'] ?? 0 ?>, <?= $documentStats['disposedCount'] ?? 0 ?>],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(234, 179, 8, 0.8)',
                            'rgba(220, 38, 38, 0.8)'
                        ],
                        borderColor: [
                            'rgba(34, 197, 94, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(234, 179, 8, 1)',
                            'rgba(220, 38, 38, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.parsed || 0;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

    <?php if (is_super_admin()): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var trend = <?= json_encode($monthlyTrend ?? ['labels' => [], 'created' => [], 'archived' => [], 'disposed' => []]) ?>;
                var trendCanvas = document.getElementById('activityTrendChart');

                if (trendCanvas) {
                    new Chart(trendCanvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: trend.labels,
                            datasets: [{
                                    label: 'Created',
                                    data: trend.created,
                                    borderColor: 'rgba(59, 130, 246, 1)',
                                    backgroundColor: 'rgba(59, 130, 246, 0.12)',
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Archived',
                                    data: trend.archived,
                                    borderColor: 'rgba(234, 179, 8, 1)',
                                    backgroundColor: 'rgba(234, 179, 8, 0.12)',
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Disposed',
                                    data: trend.disposed,
                                    borderColor: 'rgba(220, 38, 38, 1)',
                                    backgroundColor: 'rgba(220, 38, 38, 0.12)',
                                    fill: true,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }

                var typeRows = <?= json_encode(array_values($typeBreakdown['types'] ?? [])) ?>;
                var typeCanvas = document.getElementById('typeBreakdownChart');

                if (typeCanvas && typeRows.length) {
                    new Chart(typeCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: typeRows.map(function(t) {
                                return t.label;
                            }),
                            datasets: [{
                                label: 'Documents',
                                data: typeRows.map(function(t) {
                                    return t.count;
                                }),
                                // Rows arrive sorted desc, so index 0 is the leader.
                                backgroundColor: typeRows.map(function(t, i) {
                                    return i === 0 ? 'rgba(37, 99, 235, 0.85)' : 'rgba(148, 163, 184, 0.75)';
                                }),
                                borderRadius: 4,
                                maxBarThickness: 34
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var t = typeRows[context.dataIndex];
                                            return t.count + ' document(s) — ' + t.percent + '% of all';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                var shelves = <?= json_encode(array_values($capacityInsights['topShelves'] ?? [])) ?>;
                var shelfCanvas = document.getElementById('shelfUtilChart');

                if (shelfCanvas && shelves.length) {
                    new Chart(shelfCanvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: shelves.map(function(s) {
                                return s.label;
                            }),
                            datasets: [{
                                label: '% full',
                                data: shelves.map(function(s) {
                                    return s.percent;
                                }),
                                backgroundColor: shelves.map(function(s) {
                                    if (s.percent >= 90) return 'rgba(220, 38, 38, 0.75)';
                                    if (s.percent >= 75) return 'rgba(234, 179, 8, 0.75)';
                                    return 'rgba(34, 197, 94, 0.75)';
                                }),
                                borderRadius: 4
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            var s = shelves[context.dataIndex];
                                            return s.used + '/' + s.capacity + ' (' + s.percent + '%)';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function(v) {
                                            return v + '%';
                                        }
                                    }
                                },
                                y: {
                                    ticks: {
                                        font: {
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                var retention = <?= json_encode($retentionInsights ?? []) ?>;
                var retentionCanvas = document.getElementById('retentionChart');

                if (retentionCanvas) {
                    new Chart(retentionCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Permanent', 'Expiring'],
                            datasets: [{
                                data: [retention.permanent || 0, retention.expiring || 0],
                                backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(234, 179, 8, 0.8)'],
                                borderColor: ['rgba(59, 130, 246, 1)', 'rgba(234, 179, 8, 1)'],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        font: {
                                            size: 10
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    <?php endif; ?>

</div>

<?= $this->endSection() ?>