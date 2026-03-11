<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? esc($title) : 'EMB Records System' ?></title>
    <link rel="stylesheet" href="assets/css/tailwind.css">
</head>
<body class="font-sans bg-gradient-to-br from-green-50 to-white min-h-screen flex items-center justify-center p-5">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-7xl w-full flex min-h-[500px]">
        <!-- Left Side - Branding -->
        <div class="bg-gradient-to-br from-green-600 to-green-400 text-white p-10 flex flex-col justify-center items-center text-center flex-1">
            <img src="/images/EMB-Logo.png" alt="EMB Records Logo"
                class="w-40 h-auto max-h-40 object-contain rounded-xl mb-5">
            <h1 class="text-5xl font-bold mb-5 flex items-center gap-4">
                EMB Record System
            </h1>
            <p class="text-lg opacity-90">Secure Records Management Solution</p>
        </div>

        <!-- Right Side - Content -->
        <div class="p-10 flex flex-col justify-center flex-1">
            <?php if(session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            
            <?php if(session()->getFlashdata('success')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</body>
</html>
