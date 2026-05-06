<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    .verify-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
        max-width: 500px;
        margin: 60px auto;
        overflow: hidden;
    }

    .verify-header {
        padding: 32px;
        text-align: center;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }

    .verify-body {
        padding: 40px;
    }

    .icon-wrapper {
        width: 64px;
        height: 64px;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .label-text {
        font-size: 12px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
        display: block;
    }

    .custom-input {
        height: 52px;
        padding: 0 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 16px;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        width: 100%;
        background: #ffffff;
        text-align: center;
    }

    .custom-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .btn-verify {
        height: 52px;
        width: 100%;
        background: #1e293b;
        color: #ffffff;
        font-weight: 700;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 24px;
        font-size: 15px;
    }

    .btn-verify:hover {
        background: #0f172a;
        transform: translateY(-1px);
    }

    .target-user-info {
        background: #f1f5f9;
        padding: 12px 16px;
        border-radius: 10px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .status-preview {
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
    }
</style>

<div class="max-w-8xl mx-auto px-4">
    <div class="verify-card">
        <div class="verify-header">
            <div class="icon-wrapper">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Confirm Identity</h1>
            <p class="text-sm text-gray-500 mt-2">Please enter your administrator password to confirm this status update.</p>
        </div>

        <div class="verify-body">
            <div class="target-user-info">
                <div class="h-8 w-8 rounded bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-gray-900"><?= esc($user['username']) ?></p>
                    <p class="text-[10px] text-gray-500">Changing status to: 
                        <span class="status-preview <?= $newStatus === 'Active' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                            <?= esc($newStatus) ?>
                        </span>
                    </p>
                </div>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-6 p-4 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl text-sm font-bold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= route_to('users.update-status', $user['user_id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="<?= esc($newStatus) ?>">
                
                <div class="form-group">
                    <label class="label-text">Administrator Password</label>
                    <input type="password" name="admin_password" class="custom-input" placeholder="••••••••" required autofocus>
                </div>

                <button type="submit" class="btn-verify">Verify & Update Status</button>
                
                <a href="<?= route_to('users.index') ?>" class="block text-center mt-6 text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-widest transition-colors">
                    Cancel and Return
                </a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
