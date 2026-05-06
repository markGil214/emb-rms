<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php 
$roles = $roles ?? []; 
$users = $users ?? []; 
$allPermissions = $permissions ?? []; 
$firstGroupId = !empty($allPermissions) ? array_keys($allPermissions)[0] : '';
?>

<style>
    /* Modern User-Centric Matrix Styling */
    .matrix-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .tab-btn {
        padding: 12px 24px;
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 3px solid transparent;
        color: #64748b;
        transition: all 0.2s;
    }

    .tab-btn.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
        background: #eff6ff;
    }

    .matrix-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .matrix-table th {
        background: #f8fafc;
        padding: 16px;
        font-weight: 800;
        font-size: 11px;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e2e8f0;
        text-align: center;
    }

    .matrix-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        text-align: center;
    }

    .matrix-table td:first-child, .matrix-table th:first-child {
        text-align: left;
        position: sticky;
        left: 0;
        background: white;
        z-index: 10;
        width: 320px;
        border-right: 1px solid #f1f5f9;
    }

    .matrix-table th:first-child {
        background: #f8fafc;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        background: #3b82f6;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
        text-transform: uppercase;
    }

    .perm-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid #cbd5e1;
        cursor: pointer;
        transition: all 0.2s;
    }

    .perm-checkbox:checked {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .role-badge {
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .role-super_admin { background: #fee2e2; color: #991b1b; }
    .role-admin { background: #e0f2fe; color: #075985; }
    .role-records_officer { background: #f0fdf4; color: #166534; }

    #saveBar {
        position: fixed;
        bottom: 32px;
        left: 50%;
        transform: translateX(-50%);
        background: #1e293b;
        color: white;
        padding: 16px 32px;
        border-radius: 100px;
        display: flex;
        align-items: center;
        gap: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>

<div class="max-w-8xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">User Access Matrix</h1>
            <p class="text-gray-500 text-sm mt-1">Manage system-wide permissions and administrative authority.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="syncPermissions()" id="syncBtn" class="flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-bold text-sm shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                SYNC SYSTEM PERMISSIONS
            </button>
            <button onclick="saveAllChanges()" id="saveBtn" class="flex items-center px-6 py-2 bg-gray-900 text-white rounded-xl hover:bg-black transition-all font-bold text-sm shadow-lg shadow-gray-200">
                SAVE ALL CHANGES
            </button>
        </div>
    </div>

    <div class="matrix-card">
        <div class="overflow-x-auto">
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th class="border-b-2 border-gray-100">User Member</th>
                        <th class="border-b-2 border-gray-100">Account Status</th>
                        <?php foreach ($allPermissions as $groupId => $group): ?>
                            <?php foreach ($group as $key => $label): ?>
                                <th class="perm-col">
                                    <div class="flex flex-col items-center gap-1" title="<?= esc($label) ?>">
                                        <span class="whitespace-nowrap"><?= esc(str_replace(['view_', 'create_', 'update_', 'approve_'], '', $key)) ?></span>
                                        <span class="text-[9px] text-gray-400 normal-case"><?= esc(str_replace('_', ' ', $groupId)) ?></span>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php 
                            $roleKey = strtolower($user['role_name'] ?? $user['role'] ?? 'records_officer');
                            $isSuperAdmin = ($roleKey === 'super_admin' || $roleKey === 'superadmin');
                            $isCurrentUser = ($user['user_id'] == (auth_user()['user_id'] ?? 0));
                            $currentUserPermissions = $userPermissionsMap[$user['user_id']] ?? [];
                        ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar" style="background: <?= $isSuperAdmin ? '#991b1b' : ($roleKey === 'admin' ? '#075985' : '#166534') ?>">
                                        <?= substr($user['username'], 0, 1) ?>
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 text-sm"><?= esc($user['full_name'] ?: $user['username']) ?></span>
                                            <span class="role-badge role-<?= str_replace('superadmin', 'super_admin', $roleKey) ?>">
                                                <?= esc(str_replace('_', ' ', $roleKey)) ?>
                                            </span>
                                            <?php if ($isCurrentUser): ?>
                                                <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-black uppercase">You</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge" style="background: <?= $user['status'] === 'Active' ? '#f0fdf4' : ($user['status'] === 'Inactive' ? '#fee2e2' : '#fef9c3') ?>; color: <?= $user['status'] === 'Active' ? '#166534' : ($user['status'] === 'Inactive' ? '#991b1b' : '#854d0e') ?>;">
                                    <?= esc($user['status']) ?>
                                </span>
                            </td>
                            <?php foreach ($allPermissions as $group => $groupPerms): ?>
                                <?php foreach ($groupPerms as $key => $label): ?>
                                    <td class="px-6 py-4 border-b border-gray-100 text-center">
                                        <?php 
                                            $hasPermission = in_array($key, $currentUserPermissions);
                                            $isDisabled = ($isSuperAdmin || $isCurrentUser);
                                        ?>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" 
                                               class="perm-checkbox w-5 h-5 rounded border-gray-300 text-gray-900 focus:ring-gray-900 transition-all cursor-pointer"
                                               data-user="<?= $user['user_id'] ?>" 
                                               data-perm="<?= $key ?>"
                                               onchange="markChanged()"
                                               <?= $hasPermission ? 'checked' : '' ?>
                                               <?= $isDisabled ? 'disabled' : '' ?>>
                                        </label>
                                    </td>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="saveBar" class="hidden">
    <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-500/20 rounded-full flex items-center justify-center text-blue-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <span class="font-bold text-sm text-white">Unsaved permission changes</span>
    </div>
    <div class="flex gap-4">
        <button onclick="window.location.reload()" class="text-gray-400 hover:text-white text-xs font-bold uppercase tracking-widest">Discard</button>
        <button onclick="saveAllChanges()" id="saveBtn" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all">Save Matrix</button>
    </div>
</div>

<script>
    function markChanged() {
        $('#saveBar').removeClass('hidden');
    }

    function syncPermissions() {
        const btn = $('#syncBtn');
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> SYNCING...');
        
        $.ajax({
            url: '/permissions/sync',
            method: 'POST',
            success: function(response) {
                showAppAlert(response.message || 'System permissions synchronized successfully', 'success');
                setTimeout(() => window.location.reload(), 1500);
            },
            error: function() {
                showAppAlert('Failed to synchronize system permissions', 'error');
                btn.prop('disabled', false).html(originalText);
            }
        });
    }

    function saveAllChanges() {
        const btn = $('#saveBtn');
        btn.prop('disabled', true).text('SAVING...');

        const usersToUpdate = {};
        $('.perm-checkbox:not(:disabled)').each(function() {
            const userId = $(this).data('user');
            const perm = $(this).data('perm');
            const isChecked = $(this).is(':checked');

            if (!usersToUpdate[userId]) usersToUpdate[userId] = [];
            if (isChecked) usersToUpdate[userId].push(perm);
        });

        const promises = Object.keys(usersToUpdate).map(userId => {
            return $.ajax({
                url: `/permissions/save-permissions/${userId}`,
                type: 'POST',
                data: { permissions: usersToUpdate[userId] }
            });
        });

        $.when(...promises).done(function() {
            $('#saveBar').addClass('hidden');
            btn.prop('disabled', false).text('SAVE MATRIX');
            showAppAlert('Operational permissions updated successfully. Refreshing matrix...', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }).fail(function() {
            btn.prop('disabled', false).text('SAVE MATRIX');
            showAppAlert('Failed to update some users', 'error');
        });
    }

    $(document).ready(function() {
        // Ready
    });
</script>
<?= $this->endSection() ?>