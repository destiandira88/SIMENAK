<?php
$isLoggedIn = (bool) session()->get('isLoggedIn');
$userRole   = (string) session()->get('role');
$userName   = (string) session()->get('nama');
$isCustomer = $isLoggedIn && $userRole === 'pelanggan';

$navMenus = [
    ['label' => 'About Us', 'href' => '#about'],
    ['label' => 'Services We Provide', 'href' => '#services'],
    ['label' => 'Work Process', 'href' => '#process'],
    ['label' => 'Our Portfolio', 'href' => '#portfolio'],
    ['label' => 'Product Catalog', 'href' => '#catalog'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($this->renderSection('title') ?: "SIMENAK Z'Plack") ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --navy: #051747;
            --blue-accent: #2E5CE6;
            --bg-page: #F0F2F8;
            --border: #E2E8F0;
            --shadow-sm: 0 1px 3px rgba(5, 23, 71, .06);
            --shadow-md: 0 4px 16px rgba(5, 23, 71, .08)
        }

        html {
            scroll-behavior: smooth
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-page);
            color: #4A5568
        }

        .glass-nav {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, .75);
            border: 1px solid rgba(226, 232, 240, .7)
        }

        .btn-primary {
            background: #051747;
            color: #fff;
            border-radius: 999px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            transition: .2s
        }

        .btn-primary:hover {
            background: #2E5CE6
        }

        .btn-outline {
            border: 1.5px solid #051747;
            color: #051747;
            border-radius: 999px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            transition: .2s
        }

        .btn-outline:hover {
            background: #051747;
            color: #fff
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            transition: .2s
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md)
        }

        .input-field {
            border: 1.5px solid #E2E8F0;
            border-radius: 14px
        }

        .input-field:focus {
            border-color: #2E5CE6;
            box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
            outline: none
        }

        .modal-input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            pointer-events: none
        }

        .modal-input {
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            width: 100%;
            padding: 12px 16px 12px 44px;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s
        }

        .modal-input:focus {
            border-color: #2E5CE6;
            box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
            outline: none
        }

        .modal-input-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            transition: color .2s
        }

        .modal-input-toggle:hover {
            color: #051747
        }

        #loginModal .card:hover {
            transform: none
        }

        #registerModal .card:hover {
            transform: none
        }

        .password-strength-bar {
            transition: width .35s ease, background-color .35s ease
        }

        .password-strength-label {
            transition: color .35s ease
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body class="min-h-screen">
    <div class="fixed top-0 left-0 right-0 z-50 h-8 bg-[#051747] flex items-center justify-center px-4">
        <p class="text-white text-[10px] md:text-xs uppercase tracking-[0.2em] text-center truncate font-semibold">
            ⚡ Professional Printing Solution ⚡
        </p>
    </div>

    <header class="fixed top-8 left-0 right-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="glass-nav rounded-full px-4 md:px-6 h-14 flex items-center justify-between">
                <a href="<?= site_url('/') ?>" class="flex items-center gap-2 shrink-0">
                    <span class="w-8 h-8  bg-[#051747] text-white text-xs font-bold flex items-center justify-center">Z</span>
                    <div>
                        <p class="text-[11px] font-extrabold tracking-tight text-[#051747] leading-none">Z'PLACK <span class="text-[#2E5CE6]">SIMENAK</span></p>
                        <p class="text-[9px] text-slate-500 tracking-[0.12em] uppercase">Integrated Printing Solution</p>
                    </div>
                </a>
                <ul class="hidden lg:flex items-center gap-6">
                    <?php foreach ($navMenus as $menu): ?>
                        <li><a href="<?= esc($menu['href']) ?>" class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 hover:text-[#051747]"><?= esc($menu['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($isCustomer): ?>
                    <div class="flex items-center gap-2 md:gap-3">
                        <a href="<?= site_url('dashboard') ?>" class="btn-primary px-4 py-2 text-[10px] md:text-xs">Dashboard</a>
                        <div class="hidden sm:flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                            <span class="w-7 h-7 rounded-full bg-[#2E5CE6] text-white text-xs font-bold flex items-center justify-center"><?= esc(strtoupper(substr($userName ?: 'P', 0, 1))) ?></span>
                            <span class="text-xs font-semibold text-slate-700 max-w-[130px] truncate"><?= esc($userName ?: 'Pelanggan') ?></span>
                        </div>
                        <button type="button" data-open-logout-modal class="btn-outline px-4 py-2 text-xs hidden md:inline-flex">Logout</button>
                    </div>
                <?php elseif ($isLoggedIn): ?>
                    <div class="flex items-center gap-2 md:gap-3">
                        <a href="<?= site_url('dashboard') ?>" class="btn-primary px-4 py-2 text-[10px] md:text-xs">Portal <?= esc($userRole) ?></a>
                        <button type="button" data-open-logout-modal class="btn-outline px-4 py-2 text-xs hidden md:inline-flex">Logout</button>
                    </div>
                <?php else: ?>
                    <button type="button" data-open-modal="loginModal" class="btn-primary px-5 py-2 text-[10px] md:text-xs">Portal Login</button>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="pt-28">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="mt-16 bg-[#051747] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid md:grid-cols-3 gap-8">
            <div>
                <p class="text-lg font-extrabold">SIMENAK <span class="text-[#6b9fff]">Z'PLACK</span></p>
                <p class="mt-3 text-sm text-white/70 max-w-sm">Sistem informasi Pemesanan pada Percetakan Z'Plack Berbasis Web</p>
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em]">Quick Links</p>
                <ul class="mt-3 space-y-2 text-sm text-white/70">
                    <li><a href="#about" class="hover:text-white">About Us</a></li>
                    <li><a href="#services" class="hover:text-white">Services</a></li>
                    <li><a href="#catalog" class="hover:text-white">Catalog</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.15em]">Hubungi Kami</p>
                <ul class="mt-3 space-y-2 text-sm text-white/70">
                    <li>+62 812 3456 7890</li>
                    <li>support@zplack.com</li>
                    <li>Cimahi, Jawa Barat, Indonesia</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 py-4 text-center text-xs text-white/60">&copy; <?= esc((string) date('Y')) ?> Z'Plack SIMENAK. All rights reserved.</div>
    </footer>

    <?php if (!$isLoggedIn): ?>
        <div id="loginModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" data-close-modal></div>
            <div class="relative w-full max-w-md card p-6 md:p-8 z-10">
                <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" data-close-modal aria-label="Tutup">&times;</button>

                <div class="text-center pt-2">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-[#EEF4FF] border border-[#DBEAFE]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#2E5CE6]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V4h12v5M6 14H4a2 2 0 01-2-2v-4a2 2 0 012-2h16a2 2 0 012 2v4a2 2 0 01-2 2h-2M6 14v5h12v-5M6 14h12" />
                        </svg>
                    </div>
                    <h4 class="text-2xl md:text-[28px] font-extrabold text-[#051747] leading-tight">Selamat Datang 👋</h4>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed px-2">
                        Masuk ke SIMENAK Z'Plack Portal untuk melakukan pemesanan
                    </p>
                </div>

                <div id="loginAlert" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
                <form id="loginForm" method="post" action="<?= site_url('login-ajax') ?>" class="mt-6 space-y-5"><?= csrf_field() ?>
                    <div>
                        <label for="login_email" class="block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 mb-2">Email</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input id="login_email" name="email" type="email" class="modal-input" placeholder="nama@email.com" required>
                        </div>
                    </div>
                    <div>
                        <label for="login_password" class="block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 mb-2">Password</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input id="login_password" name="password" type="password" class="modal-input pr-11" placeholder="••••••••" required>
                            <button type="button" id="loginPasswordToggle" class="modal-input-toggle" aria-label="Tampilkan password">
                                <svg id="loginEyeShow" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="loginEyeHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.029a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" id="loginSubmitBtn" class="w-full btn-primary py-3 text-xs">Masuk Sekarang</button>
                </form>
                <p class="mt-4 text-sm text-slate-500 text-center">Belum punya akun? <button type="button" class="font-semibold text-[#2E5CE6] hover:underline" data-switch-modal="registerModal">Daftar di sini</button></p>
            </div>
        </div>
        <div id="registerModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" data-close-modal></div>
            <div class="relative w-full max-w-lg card p-6 md:p-8 z-10 max-h-[90vh] overflow-y-auto">
                <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" data-close-modal aria-label="Tutup">&times;</button>

                <div class="text-center pt-2">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-[#EEF4FF] border border-[#DBEAFE]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#2E5CE6]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <h4 class="text-2xl md:text-[28px] font-extrabold text-[#051747] leading-tight">Buat Akun Baru📝</h4>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed px-2">
                        Daftar untuk mulai pemesan di SIMENAK.
                    </p>
                </div>

                <div id="registerAlert" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
                <form id="registerForm" method="post" action="<?= site_url('register-ajax') ?>" class="mt-6 grid md:grid-cols-2 gap-x-4 gap-y-5"><?= csrf_field() ?>
                    <div class="md:col-span-2">
                        <label for="reg_nama" class="block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 mb-2">Nama Lengkap</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <input id="reg_nama" name="nama" type="text" class="modal-input" placeholder="Budi Santoso" required>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="reg_email" class="block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 mb-2">Email</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input id="reg_email" name="email" type="email" class="modal-input" placeholder="budi@domain.com" required>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="reg_no_telp" class="block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 mb-2">No. Telepon</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </span>
                            <input id="reg_no_telp" name="no_telp" type="tel" class="modal-input" placeholder="08xxxxxxxxxx" inputmode="numeric" pattern="[0-9]{10,13}" minlength="10" maxlength="13" required>
                        </div>
                    </div>
                    <div>
                        <label for="reg_password" class="block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 mb-2">Password</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input id="reg_password" name="password" type="password" class="modal-input pr-11" placeholder="••••••••" required>
                            <button type="button" id="regPasswordToggle" class="modal-input-toggle" aria-label="Tampilkan password">
                                <svg id="regEyeShow" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="regEyeHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.029a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div id="regPasswordStrength" class="mt-2.5">
                            <p class="text-xs text-slate-500">
                                Keamanan Sandi:
                                <span id="regPasswordStrengthLabel" class="password-strength-label font-semibold text-slate-400">—</span>
                            </p>
                            <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                <div id="regPasswordStrengthBar" class="password-strength-bar h-full rounded-full bg-slate-200" style="width:0%"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="reg_password_confirm" class="block text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500 mb-2">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </span>
                            <input id="reg_password_confirm" name="password_confirm" type="password" class="modal-input pr-11" placeholder="••••••••" required>
                            <button type="button" id="regPasswordConfirmToggle" class="modal-input-toggle" aria-label="Tampilkan konfirmasi password">
                                <svg id="regConfirmEyeShow" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="regConfirmEyeHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.029a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <p class="md:col-span-2 text-[11px] text-slate-400 leading-relaxed">
                        Minimal 8 karakter dengan kombinasi huruf, angka, dan simbol.
                    </p>
                    <div class="md:col-span-2">
                        <button type="submit" id="registerSubmitBtn" class="w-full btn-primary py-3 text-xs">Daftar Sekarang</button>
                    </div>
                </form>
                <p class="mt-4 text-sm text-slate-500 text-center">Sudah punya akun? <button type="button" class="font-semibold text-[#2E5CE6] hover:underline" data-switch-modal="loginModal">Login di sini</button></p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        (() => {
            const modals = document.querySelectorAll('#loginModal, #registerModal');
            const csrfTokenName = '<?= esc(csrf_token()) ?>';
            const csrfHeaderName = '<?= esc(config('Security')->headerName) ?>';

            const closeAll = () => {
                modals.forEach((m) => {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                });
                document.body.classList.remove('overflow-hidden');
            };

            const openById = (id) => {
                const m = document.getElementById(id);
                if (!m) return;
                m.classList.remove('hidden');
                m.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const showAlert = (el, message, isSuccess) => {
                if (!el) return;
                el.textContent = message;
                el.classList.remove('hidden', 'bg-red-50', 'border-red-200', 'text-red-800', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'border');
                el.classList.add(isSuccess ? 'bg-emerald-50' : 'bg-red-50', 'border', isSuccess ? 'border-emerald-200' : 'border-red-200', isSuccess ? 'text-emerald-800' : 'text-red-800');
            };

            const getCsrfValue = (form) => {
                const input = form.querySelector(`input[name="${csrfTokenName}"]`);
                return input ? input.value : '';
            };

            const postFormAjax = async (form) => {
                const formData = new FormData(form);
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfHeaderName]: getCsrfValue(form),
                };

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers,
                    body: formData,
                });

                const data = await response.json();
                const csrfInput = form.querySelector(`input[name="${csrfTokenName}"]`);
                if (response.headers.get(csrfHeaderName) && csrfInput) {
                    csrfInput.value = response.headers.get(csrfHeaderName);
                }

                return {
                    ok: response.ok,
                    data
                };
            };

            document.querySelectorAll('[data-open-modal]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-open-modal');
                    if (id) openById(id);
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach((btn) => btn.addEventListener('click', closeAll));
            document.querySelectorAll('[data-switch-modal]').forEach((btn) => btn.addEventListener('click', () => {
                closeAll();
                const id = btn.getAttribute('data-switch-modal');
                if (id) openById(id);
            }));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeAll();
            });

            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const loginAlert = document.getElementById('loginAlert');
            const registerAlert = document.getElementById('registerAlert');

            if (loginForm) {
                const loginPasswordInput = document.getElementById('login_password');
                const loginPasswordToggle = document.getElementById('loginPasswordToggle');
                const loginEyeShow = document.getElementById('loginEyeShow');
                const loginEyeHide = document.getElementById('loginEyeHide');

                if (loginPasswordToggle && loginPasswordInput) {
                    loginPasswordToggle.addEventListener('click', () => {
                        const isHidden = loginPasswordInput.type === 'password';
                        loginPasswordInput.type = isHidden ? 'text' : 'password';
                        loginEyeShow.classList.toggle('hidden', isHidden);
                        loginEyeHide.classList.toggle('hidden', !isHidden);
                        loginPasswordToggle.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
                    });
                }

                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = document.getElementById('loginSubmitBtn');
                    btn.disabled = true;
                    btn.textContent = 'Memproses...';

                    try {
                        const {
                            ok,
                            data
                        } = await postFormAjax(loginForm);
                        if (ok && data.success) {
                            window.location.href = data.redirect || '<?= site_url('dashboard') ?>';
                            return;
                        }
                        showAlert(loginAlert, data.message || 'Login gagal.', false);
                    } catch (err) {
                        showAlert(loginAlert, 'Terjadi kesalahan jaringan. Silakan coba lagi.', false);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Masuk Sekarang';
                    }
                });
            }

            if (registerForm) {
                const setupPasswordToggle = (input, toggleBtn, showIcon, hideIcon, showLabel, hideLabel) => {
                    if (!input || !toggleBtn) return;
                    toggleBtn.addEventListener('click', () => {
                        const isHidden = input.type === 'password';
                        input.type = isHidden ? 'text' : 'password';
                        if (showIcon) showIcon.classList.toggle('hidden', isHidden);
                        if (hideIcon) hideIcon.classList.toggle('hidden', !isHidden);
                        toggleBtn.setAttribute('aria-label', isHidden ? hideLabel : showLabel);
                    });
                };

                setupPasswordToggle(
                    document.getElementById('reg_password'),
                    document.getElementById('regPasswordToggle'),
                    document.getElementById('regEyeShow'),
                    document.getElementById('regEyeHide'),
                    'Tampilkan password',
                    'Sembunyikan password'
                );

                setupPasswordToggle(
                    document.getElementById('reg_password_confirm'),
                    document.getElementById('regPasswordConfirmToggle'),
                    document.getElementById('regConfirmEyeShow'),
                    document.getElementById('regConfirmEyeHide'),
                    'Tampilkan konfirmasi password',
                    'Sembunyikan konfirmasi password'
                );

                const regPhoneInput = document.getElementById('reg_no_telp');
                if (regPhoneInput) {
                    regPhoneInput.addEventListener('input', () => {
                        regPhoneInput.value = regPhoneInput.value.replace(/\D/g, '').slice(0, 13);
                    });
                }

                const regPasswordInput = document.getElementById('reg_password');
                const regStrengthLabel = document.getElementById('regPasswordStrengthLabel');
                const regStrengthBar = document.getElementById('regPasswordStrengthBar');

                const strengthLevels = [{
                        label: 'Lemah',
                        width: 25,
                        bar: 'bg-red-500',
                        text: 'text-red-600'
                    },
                    {
                        label: 'Sedang',
                        width: 50,
                        bar: 'bg-yellow-400',
                        text: 'text-yellow-600'
                    },
                    {
                        label: 'Kuat',
                        width: 75,
                        bar: 'bg-[#2E5CE6]',
                        text: 'text-[#2E5CE6]'
                    },
                    {
                        label: 'Sangat Kuat 🔥',
                        width: 100,
                        bar: 'bg-emerald-500',
                        text: 'text-emerald-600'
                    },
                ];

                const barColors = strengthLevels.map((level) => level.bar);

                const getPasswordStrength = (password) => {
                    if (!password) {
                        return null;
                    }

                    let score = 0;
                    if (password.length >= 8) score++;
                    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
                    if (/\d/.test(password)) score++;
                    if (/[^A-Za-z0-9]/.test(password)) score++;
                    if (password.length >= 12) score++;

                    if (score <= 1) return strengthLevels[0];
                    if (score === 2) return strengthLevels[1];
                    if (score === 3 || score === 4) return strengthLevels[2];
                    return strengthLevels[3];
                };

                const resetPasswordStrength = () => {
                    if (!regStrengthLabel || !regStrengthBar) return;
                    regStrengthLabel.textContent = '—';
                    regStrengthLabel.className = 'password-strength-label font-semibold text-slate-400';
                    regStrengthBar.style.width = '0%';
                    barColors.forEach((color) => regStrengthBar.classList.remove(color));
                    regStrengthBar.classList.add('bg-slate-200');
                };

                const updatePasswordStrength = (password) => {
                    if (!regStrengthLabel || !regStrengthBar) return;

                    const level = getPasswordStrength(password);
                    if (!level) {
                        resetPasswordStrength();
                        return;
                    }

                    regStrengthLabel.textContent = level.label;
                    regStrengthLabel.className = `password-strength-label font-semibold ${level.text}`;
                    barColors.forEach((color) => regStrengthBar.classList.remove(color));
                    regStrengthBar.classList.remove('bg-slate-200');
                    regStrengthBar.classList.add(level.bar);
                    regStrengthBar.style.width = `${level.width}%`;
                };

                if (regPasswordInput) {
                    regPasswordInput.addEventListener('input', () => {
                        updatePasswordStrength(regPasswordInput.value);
                    });
                }

                registerForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const phone = regPhoneInput ? regPhoneInput.value.trim() : '';
                    if (!/^\d{10,13}$/.test(phone)) {
                        showAlert(registerAlert, 'No. telepon harus berisi 10–13 digit angka.', false);
                        return;
                    }

                    const btn = document.getElementById('registerSubmitBtn');
                    btn.disabled = true;
                    btn.textContent = 'Memproses...';

                    try {
                        const {
                            ok,
                            data
                        } = await postFormAjax(registerForm);
                        if (ok && data.success) {
                            showAlert(registerAlert, data.message, true);
                            registerForm.reset();
                            resetPasswordStrength();
                            setTimeout(() => {
                                closeAll();
                                openById('loginModal');
                            }, 1200);
                            return;
                        }
                        showAlert(registerAlert, data.message || 'Registrasi gagal.', false);
                    } catch (err) {
                        showAlert(registerAlert, 'Terjadi kesalahan jaringan. Silakan coba lagi.', false);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Daftar Sekarang';
                    }
                });
            }
        })();
    </script>
    <?= view('partials/flash_toast') ?>
    <?= view('partials/logout_modal') ?>
    <?= $this->renderSection('scripts') ?>
</body>

</html>