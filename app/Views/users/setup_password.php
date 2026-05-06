<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMB Records System - Setup Password</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <link rel="stylesheet" href="/css/loading-animation.css">

    <style>
        html,
        body,
        body * {
            font-size: 12px !important;
        }
    </style>
</head>

<body class="font-sans bg-gradient-to-br from-green-50 to-white min-h-screen flex items-center justify-center p-5">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-7xl w-full flex min-h-[500px]">
        <!-- Left Side - Branding -->
        <div
            class="relative text-white p-10 flex flex-col justify-center items-center text-center flex-1 overflow-hidden">
            <!-- Background Image with Dark Overlay -->
            <div class="absolute inset-0">
                <img src="/images/baguio.jpg" alt="Background"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black bg-opacity-40"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col items-center justify-center">
                <img src="/images/EMB-Logo.png" alt="EMB Records Logo"
                    class="w-40 h-auto max-h-40 object-contain rounded-xl mb-5">
                <h1 class="text-5xl font-bold mb-5 flex items-center gap-4 text-center">
                    EMB Record System
                </h1>
            </div>
        </div>

        <!-- Right Side - Setup Form -->
        <div class="p-10 flex flex-col justify-center flex-1">
            <div class="login-header text-center mb-10">
                <h2 class="text-3xl text-gray-800 font-semibold mb-2.5">Activate Account</h2>
                <p class="text-gray-600 text-sm">Welcome, <strong><?= esc($user['username']) ?></strong>! Please set your secure password to get started.</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-100 text-red-800 p-2.5 rounded-md mb-5 border border-red-300">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-100 text-red-800 p-2.5 rounded-md mb-5 border border-red-300 flex-col items-start gap-2">
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

            <form id="setupForm" method="POST" action="<?= route_to('users.complete-setup', $user['user_id']) ?>" class="space-y-6">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="otp" class="block mb-2 text-gray-800 font-medium text-sm">Activation Code (OTP)</label>
                    <input type="text" id="otp" name="otp" required placeholder="••••••••" maxlength="8" autocomplete="off" autofocus
                        class="w-full p-3 border-2 border-gray-200 rounded-lg text-sm text-center font-bold text-blue-600 bg-blue-50 tracking-widest transition-all duration-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <p class="text-xs text-gray-400 mt-2 text-center uppercase font-bold tracking-widest">Check your email for this code</p>
                </div>

                <div class="form-group relative">
                    <label for="password" class="block mb-2 text-gray-800 font-medium text-sm">Create Password</label>
                    <input type="password" id="password" name="password" required placeholder="At least 8 characters"
                        class="w-full p-3 pr-12 border-2 border-gray-200 rounded-lg text-sm transition-all duration-300 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                    <button type="button" id="togglePassword"
                        class="absolute right-3 top-[38px] text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                        <svg id="eyeSlashIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="block mb-2 text-gray-800 font-medium text-sm">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat your password"
                        class="w-full p-3 border-2 border-gray-200 rounded-lg text-sm transition-all duration-300 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100">
                </div>

                <button type="submit"
                    class="login-btn w-full p-3.5 bg-gradient-to-r from-green-600 to-green-400 text-white border-none rounded-lg text-base font-semibold cursor-pointer transition-all duration-300 mb-5 hover:-translate-y-0.5 hover:shadow-lg hover:shadow-green-300">
                    Finalize My Account
                </button>
            </form>

            <div class="signup-link text-center mt-8 text-sm text-gray-600">
                By activating, you agree to the system's usage policies.
            </div>
        </div>
    </div>

    <!-- Loading Animation Script -->
    <script src="/js/loading-animation.js"></script>

    <script>
        // Add input animations
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function () {
                this.parentElement.style.transform = 'scale(1.02)';
            });

            input.addEventListener('blur', function () {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeSlashIcon = document.getElementById('eyeSlashIcon');

        togglePassword.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        });

        // Setup form submission with loading animation
        document.getElementById('setupForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Show loading animation
            showLoading();

            // Get form data
            const formData = new FormData(this);

            // Submit form after showing loading
            setTimeout(() => {
                this.submit();
            }, 500);
        });
    </script>
</body>

</html>
