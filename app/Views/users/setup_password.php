<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<style>
    .activation-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        max-width: 500px;
        margin: 20px auto;
        overflow: hidden;
    }

    .activation-header {
        padding: 24px;
        text-align: center;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
    }

    .activation-body {
        padding: 32px;
    }

    .icon-box {
        width: 72px;
        height: 72px;
        background: #dcfce7;
        color: #166534;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .label-text {
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 10px;
        display: block;
    }

    .custom-input {
        height: 54px;
        padding: 0 20px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 15px;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        width: 100%;
        background: #ffffff;
    }

    .custom-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 5px rgba(59, 130, 246, 0.1);
    }

    .otp-input {
        letter-spacing: 0.5em;
        text-align: center;
        font-weight: 900;
        font-size: 24px;
        text-transform: uppercase;
        color: #2563eb;
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    .btn-activate {
        height: 56px;
        width: 100%;
        background: #2563eb;
        color: #ffffff;
        font-weight: 800;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 24px;
        font-size: 16px;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .btn-activate:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.4);
    }

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

    .alert-error {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        color: #991b1b;
    }
</style>

    <div class="activation-card">
        <div class="activation-header">
            <div class="icon-box">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Activate Account</h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Welcome, <strong><?= esc($user['username']) ?></strong>! Please set your secure password to get started.</p>
        </div>

        <div class="activation-body">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-box alert-error">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert-box alert-error flex-col items-start gap-2">
                    <strong class="text-xs uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Correction Required
                    </strong>
                    <ul class="text-xs list-disc list-inside opacity-90">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= route_to('users.complete-setup', $user['user_id']) ?>">
                <?= csrf_field() ?>
                
                <div class="mb-6">
                    <label class="label-text">Activation Code (OTP)</label>
                    <input type="text" name="otp" class="custom-input otp-input" placeholder="••••••••" required maxlength="8" autocomplete="off" autofocus>
                    <p class="text-[10px] text-gray-400 mt-2 text-center uppercase font-bold tracking-widest">Check your email for this code</p>
                </div>

                <div class="mb-6">
                    <label class="label-text">Create Password</label>
                    <input type="password" name="password" class="custom-input" placeholder="At least 8 characters" required>
                </div>

                <div class="mb-4">
                    <label class="label-text">Confirm Password</label>
                    <input type="password" name="confirm_password" class="custom-input" placeholder="Repeat your password" required>
                </div>

                <button type="submit" class="btn-activate">Finalize My Account</button>
                
                <p class="text-center mt-8 text-xs text-gray-400 font-medium">
                    By activating, you agree to the system's usage policies.
                </p>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>
