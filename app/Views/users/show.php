<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
    $user = $user ?? [];
    $activity = $activity ?? [];
    $activityByDay = $activityByDay ?? [];
    $actionMix = $actionMix ?? [];
    $permissions = $permissions ?? [];

    $status = (string) ($user['status'] ?? 'Active');
    $statusClass = 'status-' . strtolower($status);
    $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

    $formatDateTime = static function ($value): string {
        $value = trim((string) ($value ?? ''));
        // strpos rather than str_starts_with: composer declares PHP ^7.2.
        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return '--';
        }

        $time = strtotime($value);
        return $time ? date('M d, Y g:i A', $time) : '--';
    };

    $relativeTime = static function ($value): string {
        $time = strtotime((string) ($value ?? ''));
        if (! $time) {
            return '';
        }

        $diff = time() - $time;
        if ($diff < 60)    return 'just now';
        if ($diff < 3600)  return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        return floor($diff / 86400) . 'd ago';
    };

    // Rows written before the audit argument-order fix stored the user id in
    // `action` and the real action name in `entity_type`. Prefer whichever
    // column actually holds a name so old entries stay readable.
    $actionLabel = static function (array $row): string {
        $action = trim((string) ($row['action'] ?? ''));
        $entity = trim((string) ($row['entity_type'] ?? ''));

        if ($action === '' || ctype_digit($action)) {
            $action = $entity;
        }

        return $action === '' ? 'Unknown action' : ucfirst(str_replace('_', ' ', $action));
    };

    $peakDay = 1;
    foreach ($activityByDay as $day) {
        $peakDay = max($peakDay, (int) $day['total']);
    }
    $activeDays = count(array_filter($activityByDay, static function (array $d): bool {
        return (int) $d['total'] > 0;
    }));
?>

<style>
    .detail-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }

    .detail-card__head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .detail-card__title { font-size: 15px; font-weight: 700; color: #0f172a; }
    .detail-card__subtitle { margin-top: 2px; font-size: 11px; color: #64748b; }
    .detail-card__body { padding: 16px 20px; }

    .detail-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        flex-shrink: 0;
        border-radius: 9999px;
        background: #3b82f6;
        color: #fff;
        font-size: 20px;
        font-weight: 800;
        text-transform: uppercase;
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

    .field-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px solid #f8fafc;
        font-size: 13px;
    }

    .field-row:last-child { border-bottom: 0; }
    .field-row__label { color: #64748b; font-weight: 600; }
    .field-row__value { color: #0f172a; text-align: right; word-break: break-word; }

    .perm-chip {
        display: inline-block;
        margin: 0 4px 4px 0;
        padding: 3px 8px;
        border-radius: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 10px;
        font-weight: 700;
    }

    /* 30-day activity sparkline */
    .spark {
        display: flex;
        align-items: flex-end;
        gap: 2px;
        height: 56px;
        margin-top: 4px;
    }

    .spark__bar {
        flex: 1;
        min-height: 2px;
        border-radius: 2px 2px 0 0;
        background: #cbd5e1;
    }

    .spark__bar--active { background: #3b82f6; }

    .timeline { position: relative; }

    .timeline__item {
        position: relative;
        padding: 10px 0 10px 26px;
        border-bottom: 1px solid #f8fafc;
    }

    .timeline__item:last-child { border-bottom: 0; }

    .timeline__item::before {
        content: '';
        position: absolute;
        left: 6px;
        top: 16px;
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        background: #3b82f6;
    }

    .timeline__item::after {
        content: '';
        position: absolute;
        left: 9.5px;
        top: 24px;
        bottom: -10px;
        width: 1px;
        background: #e2e8f0;
    }

    .timeline__item:last-child::after { display: none; }

    .timeline__action { font-size: 13px; font-weight: 700; color: #0f172a; }
    .timeline__meta { margin-top: 2px; font-size: 11px; color: #64748b; }

    .empty-note {
        padding: 28px 12px;
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
    }

    .dark .detail-card { background: var(--color-bg-secondary, #1a1f2e); border-color: var(--color-border, #334155); }
    .dark .detail-card__head { border-color: var(--color-border, #334155); }
    .dark .detail-card__title, .dark .field-row__value, .dark .timeline__action { color: var(--color-text, #f1f5f9); }
    .dark .detail-card__subtitle, .dark .field-row__label, .dark .timeline__meta { color: var(--color-text-muted, #94a3b8); }
    .dark .field-row, .dark .timeline__item { border-color: rgba(148, 163, 184, 0.12); }
    .dark .role-badge { background: var(--color-bg-tertiary, #242b3c); color: var(--color-text-secondary, #cbd5e1); border-color: var(--color-border, #334155); }
    .dark .perm-chip { background: rgba(59, 130, 246, 0.15); color: #93c5fd; }
    .dark .spark__bar { background: #334155; }
    .dark .timeline__item::after { background: #334155; }
</style>

<div class="max-w-8xl mx-auto px-4 py-6 sm:px-6 lg:px-8">

    <a href="<?= route_to('users.index') ?>" class="inline-flex items-center gap-1 mb-4 text-sm font-semibold text-blue-600 hover:text-blue-800">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Users
    </a>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        <!-- Account information -->
        <div class="detail-card">
            <div class="detail-card__head">
                <div class="detail-avatar"><?= esc(mb_substr((string) ($user['username'] ?? '?'), 0, 1)) ?></div>
                <div class="min-w-0">
                    <p class="detail-card__title"><?= esc($fullName !== '' ? $fullName : ($user['username'] ?? 'Unknown')) ?></p>
                    <p class="detail-card__subtitle">@<?= esc($user['username'] ?? '') ?></p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="role-badge"><?= esc($user['role_name'] ?? ($user['role'] ?? 'User')) ?></span>
                        <span class="status-badge <?= esc($statusClass) ?>"><?= esc($status) ?></span>
                    </div>
                </div>
            </div>
            <div class="detail-card__body">
                <div class="field-row">
                    <span class="field-row__label">Email</span>
                    <span class="field-row__value"><?= esc($user['email'] ?? '--') ?></span>
                </div>
                <div class="field-row">
                    <span class="field-row__label">User ID</span>
                    <span class="field-row__value">#<?= (int) ($user['user_id'] ?? 0) ?></span>
                </div>
                <div class="field-row">
                    <span class="field-row__label">Joined</span>
                    <span class="field-row__value"><?= esc($formatDateTime($user['created_at'] ?? null)) ?></span>
                </div>
                <div class="field-row">
                    <span class="field-row__label">Last updated</span>
                    <span class="field-row__value"><?= esc($formatDateTime($user['updated_at'] ?? null)) ?></span>
                </div>
                <?php if (! empty($user['inactive_at'])): ?>
                    <div class="field-row">
                        <span class="field-row__label">Deactivated</span>
                        <span class="field-row__value"><?= esc($formatDateTime($user['inactive_at'])) ?></span>
                    </div>
                <?php endif; ?>

                <p class="mt-4 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-gray-400">
                    Permissions (<?= count($permissions) ?>)
                </p>
                <?php if (! empty($permissions)): ?>
                    <div>
                        <?php foreach ($permissions as $permission): ?>
                            <span class="perm-chip"><?= esc(str_replace('_', ' ', (string) $permission)) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500">No permissions granted.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity -->
        <div class="xl:col-span-2 detail-card">
            <div class="detail-card__head">
                <div class="min-w-0">
                    <p class="detail-card__title">Activity (Last 30 Days)</p>
                    <p class="detail-card__subtitle">
                        <?= (int) $activityTotal ?> action<?= (int) $activityTotal === 1 ? '' : 's' ?>
                        across <?= $activeDays ?> active day<?= $activeDays === 1 ? '' : 's' ?>
                    </p>
                </div>
                <span class="ml-auto flex-shrink-0 px-2.5 py-1 rounded-full text-sm font-extrabold <?= (int) $activityTotal > 0 ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-400' ?>">
                    <?= (int) $activityTotal ?>
                </span>
            </div>
            <div class="detail-card__body">

                <?php if (! empty($activityByDay)): ?>
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Daily activity</p>
                    <div class="spark">
                        <?php foreach ($activityByDay as $day): ?>
                            <?php
                                $total = (int) $day['total'];
                                $height = $total > 0 ? max(8, (int) round(($total / $peakDay) * 100)) : 2;
                            ?>
                            <div class="spark__bar <?= $total > 0 ? 'spark__bar--active' : '' ?>"
                                 style="height: <?= $height ?>%"
                                 title="<?= esc($day['label']) ?>: <?= $total ?> action(s)"></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        <span><?= esc($activityByDay[0]['label'] ?? '') ?></span>
                        <span><?= esc($activityByDay[count($activityByDay) - 1]['label'] ?? '') ?></span>
                    </div>
                <?php endif; ?>

                <?php if (! empty($actionMix)): ?>
                    <p class="mt-5 mb-2 text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Most frequent actions</p>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($actionMix as $mix): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                                <?= esc($actionLabel($mix)) ?>
                                <span class="px-1.5 rounded-full bg-white text-gray-600 text-[10px] font-extrabold"><?= (int) $mix['total'] ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <p class="mt-5 mb-1 text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Timeline</p>
                <?php if (! empty($activity)): ?>
                    <div class="timeline max-h-[26rem] overflow-y-auto pr-1">
                        <?php foreach ($activity as $row): ?>
                            <div class="timeline__item">
                                <p class="timeline__action"><?= esc($actionLabel($row)) ?></p>
                                <p class="timeline__meta">
                                    <?= esc($formatDateTime($row['created_at'] ?? null)) ?>
                                    <?php $rel = $relativeTime($row['created_at'] ?? null); ?>
                                    <?php if ($rel !== ''): ?>
                                        &middot; <?= esc($rel) ?>
                                    <?php endif; ?>
                                    <?php if (! empty($row['entity_id']) && (int) $row['entity_id'] > 0): ?>
                                        &middot; ref #<?= (int) $row['entity_id'] ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($activity) >= 100): ?>
                        <p class="mt-2 text-xs text-gray-500">Showing the 100 most recent entries.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-note">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        No recorded activity in the last 30 days.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
