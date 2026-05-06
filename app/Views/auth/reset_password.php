<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - EMB RMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>">
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">Reset Password</h1>
        <p class="text-sm text-gray-600 mb-6">Enter the code sent to your email, then choose a new password.</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= url_to('password.update') ?>" method="post" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Reset Code</label>
                <input id="code" name="code" type="text" maxlength="8" value="<?= esc(old('code')) ?>" required
                    class="w-full uppercase tracking-widest rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white rounded-lg py-2 font-semibold">
                Update Password
            </button>
        </form>

        <a href="<?= url_to('password.forgot') ?>" class="block text-center text-sm text-gray-600 mt-4 hover:text-gray-800">Request new code</a>
    </div>
</body>
</html>
