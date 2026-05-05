<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $roles = $roles ?? []; ?>

<style>
    /* Custom Vanilla CSS for consistency with the dashboard */
    .form-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        max-width: 800px;
        margin: 0 auto;
        overflow: hidden;
    }

    .form-header {
        padding: 32px 40px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .form-body {
        padding: 40px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .label-text {
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .custom-input {
        height: 46px;
        padding: 0 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        background: #ffffff;
    }

    .custom-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .custom-select {
        height: 46px;
        padding: 0 40px 0 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        background-color: #ffffff;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 18px;
        appearance: none;
        outline: none;
        cursor: pointer;
    }

    .custom-select:focus {
        border-color: #3b82f6;
    }

    .btn-container {
        margin-top: 40px;
        padding-top: 32px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 16px;
    }

    .btn-base {
        height: 48px;
        padding: 0 32px;
        font-size: 14px;
        font-weight: 700;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-primary {
        background: #2563eb;
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
    }

    .btn-primary:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .btn-secondary {
        background: #ffffff;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    /* Alert Styling - MORE VIBRANT RED */
    .alert-box {
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 32px;
        font-size: 14px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        border-left: 6px solid;
    }

    .alert-error {
        background: #fff5f5;
        border: 1px solid #feb2b2;
        border-left-color: #f56565;
        color: #c53030;
    }

    .alert-error strong {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.05em;
    }

    .alert-error ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        font-weight: 600;
    }

    .alert-error li {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .alert-error li::before {
        content: '•';
        font-size: 20px;
        line-height: 1;
    }

    /* Info Box */
    .info-box {
        background: #eff6ff;
        border: 1px solid #dbeafe;
        color: #1e40af;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 32px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
</style>

<div class="max-w-8xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="form-card">
        <div class="form-header">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Add New User</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">Create a team member. They will receive an email with an OTP to set their password.</p>
                </div>
                <a href="<?= route_to('users.index') ?>" class="text-xs font-bold text-gray-400 hover:text-gray-900 uppercase tracking-widest flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to List
                </a>
            </div>
        </div>

        <div class="form-body">
            <!-- Error Alert Section (Styling Updated to be More Prominent) -->
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert-box alert-error">
                    <strong>
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        Validation Error
                    </strong>
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Password and status are automatically managed. An invitation email will be sent upon creation.</span>
            </div>

            <form method="POST" action="<?= route_to('users.store') ?>">
                <?= csrf_field() ?>
                
                <!-- Hidden fields for defaults -->
                <input type="hidden" name="status" value="Active">

                <div class="form-grid">
                    <div class="form-group">
                        <label class="label-text">Username</label>
                        <input type="text" name="username" value="<?= esc(old('username')) ?>" class="custom-input" placeholder="e.g. john_doe" required>
                    </div>

                    <div class="form-group">
                        <label class="label-text">Email Address</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" class="custom-input" placeholder="john@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="label-text">First Name</label>
                        <input type="text" name="first_name" value="<?= esc(old('first_name')) ?>" class="custom-input" placeholder="John">
                    </div>

                    <div class="form-group">
                        <label class="label-text">Last Name</label>
                        <input type="text" name="last_name" value="<?= esc(old('last_name')) ?>" class="custom-input" placeholder="Doe">
                    </div>

                    <div class="form-group full-width">
                        <label class="label-text">System Role</label>
                        <select name="role_id" class="custom-select" required>
                            <option value="">Select a role...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= esc($role['role_id']) ?>" <?= old('role_id') == $role['role_id'] ? 'selected' : '' ?>><?= esc(ucwords(str_replace('_', ' ', $role['role_name']))) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="btn-container">
                    <a href="<?= route_to('users.index') ?>" class="btn-base btn-secondary">Cancel</a>
                    <button type="submit" class="btn-base btn-primary">Send Invitation & Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>