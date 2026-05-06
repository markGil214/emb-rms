<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<style>
    .result-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        max-width: 550px;
        margin: 20px auto;
        overflow: hidden;
        text-align: center;
    }

    .result-header {
        padding: 60px 40px 40px;
    }

    .result-body {
        padding: 0 60px 60px;
    }

    .status-icon {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 32px;
        animation: scaleIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .icon-success { background: #ecfdf5; color: #10b981; }
    .icon-error { background: #fef2f2; color: #ef4444; }

    @keyframes scaleIn {
        from { transform: scale(0.5); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .result-title {
        font-size: 32px;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 16px;
        letter-spacing: -0.025em;
    }

    .result-text {
        font-size: 16px;
        color: #64748b;
        line-height: 1.6;
        margin-bottom: 40px;
    }

    .btn-action {
        height: 56px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        border-radius: 14px;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 16px;
    }

    .btn-success {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .btn-success:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.4);
    }

    .btn-error {
        background: #1e293b;
        color: #ffffff;
    }

    .btn-error:hover {
        background: #0f172a;
    }
</style>

<div class="max-w-8xl mx-auto px-4">
    <div class="result-card">
        <div class="result-header">
            <?php if ($success): ?>
                <div class="status-icon icon-success">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
            <?php else: ?>
                <div class="status-icon icon-error">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                </div>
            <?php endif; ?>
            
            <h1 class="result-title"><?= esc($title) ?></h1>
            <p class="result-text"><?= esc($message) ?></p>
        </div>

        <div class="result-body">
            <?php if ($success): ?>
                <a href="<?= route_to('login') ?>" class="btn-action btn-success">Go to Login Page</a>
            <?php else: ?>
                <a href="javascript:history.back()" class="btn-action btn-error">Go Back and Try Again</a>
            <?php endif; ?>

            <p class="mt-8 text-xs text-gray-400 font-bold uppercase tracking-widest">
                Environmental Management Bureau
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
