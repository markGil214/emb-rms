<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - EMB RMS</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>">
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">Forgot Password</h1>
        <p class="text-sm text-gray-600 mb-6">Enter your account email and we will send a reset code.</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <form action="<?= url_to('password.send-code') ?>" method="post" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" name="email" type="email" value="<?= esc(old('email')) ?>" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white rounded-lg py-2 font-semibold">
                Send Reset Code
            </button>
        </form>

        <a href="<?= base_url('/') ?>" class="block text-center text-sm text-gray-600 mt-4 hover:text-gray-800">Back to login</a>
    </div>
</body>
</html>
