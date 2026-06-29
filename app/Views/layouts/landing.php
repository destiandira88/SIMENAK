<?php
$isLoggedIn = (bool) session()->get('isLoggedIn');
$userRole   = (string) session()->get('role');
$userName   = (string) session()->get('nama');
$isCustomer = $isLoggedIn && $userRole === 'pelanggan';
$openModalOnLoad = session()->getFlashdata('open_modal');
$forgotPasswordNotice = session()->getFlashdata('forgot_password_notice');
$forgotPasswordError  = session()->getFlashdata('forgot_password_error');
$ajaxRoutePath = static function (string $route): string {
    $path = parse_url(site_url($route), PHP_URL_PATH);

    return is_string($path) && $path !== '' ? $path : '/' . ltrim($route, '/');
};

$roleLabels = [
    'pelanggan' => 'Pelanggan',
    'admin'     => 'Admin',
    'keuangan'  => 'Keuangan',
    'produksi'  => 'Produksi',
    'owner'     => 'Pemilik',
];
$navMenus = [
    ['label' => 'Tentang Kami', 'href' => '#about'],
    ['label' => 'Layanan Kami', 'href' => '#services'],
    ['label' => 'Proses Kerja', 'href' => '#process'],
    ['label' => 'Portofolio', 'href' => '#portfolio'],
    ['label' => 'Katalog Produk', 'href' => '#catalog'],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= view('partials/csrf_meta') ?>
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

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #051747;
            margin-bottom: 6px;
        }

        .form-hint {
            font-size: 11px;
            line-height: 1.5;
            color: #94a3b8;
            margin-top: 6px;
        }

        .form-note {
            font-size: 11px;
            color: #94a3b8;
        }

        /* Info/warning merah — referensi .quota-info.danger */
        .notice-danger {
            background-color: #FEE2E2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        .notice-danger svg {
            color: #991B1B;
        }

        .notice-success {
            background-color: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
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
            font-size: 13px;
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

        #forgotPasswordModal .card:hover,
        #resetPasswordModal .card:hover {
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
            ⚡ SOLUSI CETAK PROFESIONAL • PENGIRIMAN PRIORITAS SELURUH INDONESIA ⚡
        </p>
    </div>

    <header class="fixed top-8 left-0 right-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="glass-nav rounded-full px-4 md:px-6 h-14 flex items-center justify-between">
                <a href="<?= site_url('/') ?>" class="flex items-center gap-2 shrink-0">
                    <span class="w-8 h-8  bg-[#051747] text-white text-xs font-bold flex items-center justify-center">Z</span>
                    <div>
                        <p class="text-[11px] font-extrabold tracking-tight text-[#051747] leading-none">Z'PLACK <span class="text-[#2E5CE6]">SIMENAK</span></p>
                        <p class="text-[9px] text-slate-500 tracking-[0.12em] uppercase">Solusi Cetak Terintegrasi</p>
                    </div>
                </a>
                <ul class="hidden lg:flex items-center gap-6">
                    <?php foreach ($navMenus as $menu): ?>
                        <li><a href="<?= esc($menu['href']) ?>" class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500 hover:text-[#051747]"><?= esc($menu['label']) ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($isCustomer): ?>
                    <div class="flex items-center gap-2 md:gap-3">
                        <a href="<?= site_url('dashboard') ?>" class="btn-primary px-4 py-2 text-[10px] md:text-xs">Beranda</a>
                        <div class="hidden sm:flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5">
                            <span class="w-7 h-7 rounded-full bg-[#2E5CE6] text-white text-xs font-bold flex items-center justify-center"><?= esc(strtoupper(substr($userName ?: 'P', 0, 1))) ?></span>
                            <span class="text-xs font-semibold text-slate-700 max-w-[130px] truncate"><?= esc($userName ?: 'Pelanggan') ?></span>
                        </div>
                        <button type="button" data-open-logout-modal class="btn-outline px-4 py-2 text-xs hidden md:inline-flex">Keluar</button>
                    </div>
                <?php elseif ($isLoggedIn): ?>
                    <div class="flex items-center gap-2 md:gap-3">
                        <a href="<?= site_url('dashboard') ?>" class="btn-primary px-4 py-2 text-[10px] md:text-xs">Portal <?= esc($roleLabels[$userRole] ?? ucfirst($userRole)) ?></a>
                        <button type="button" data-open-logout-modal class="btn-outline px-4 py-2 text-xs hidden md:inline-flex">Keluar</button>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-2 md:gap-3">
                        <button type="button" data-open-modal="registerModal" class="btn-outline px-4 py-2 text-[10px] md:text-xs">Daftar</button>
                        <button type="button" data-open-modal="loginModal" class="btn-primary px-5 py-2 text-[10px] md:text-xs">Masuk</button>
                    </div>
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
                <p class="text-sm font-bold uppercase tracking-[0.15em]">Tautan Cepat</p>
                <ul class="mt-3 space-y-2 text-sm text-white/70">
                    <li><a href="#about" class="hover:text-white">Tentang Kami</a></li>
                    <li><a href="#services" class="hover:text-white">Layanan</a></li>
                    <li><a href="#catalog" class="hover:text-white">Katalog</a></li>
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
        <div class="border-t border-white/10 py-4 text-center text-xs text-white/60">&copy; <?= esc((string) date('Y')) ?> Z'Plack SIMENAK. Hak cipta dilindungi.</div>
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
                        Masuk sebagai pelanggan untuk melakukan pemesanan di SIMENAK Z'Plack
                    </p>
                </div>

                <div id="loginAlert" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
                <form id="loginForm" method="post" action="<?= site_url('login-ajax') ?>" class="mt-6 space-y-5"><?= csrf_field() ?>
                    <div>
                        <label for="login_email" class="form-label">Email</label>
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
                        <label for="login_password" class="form-label">Kata Sandi</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </span>
                            <input id="login_password" name="password" type="password" class="modal-input pr-11" placeholder="••••••••" required>
                            <button type="button" id="loginPasswordToggle" class="modal-input-toggle" aria-label="Tampilkan kata sandi">
                                <svg id="loginEyeShow" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="loginEyeHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.029a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2 text-right">
                            <button type="button" class="text-xs font-semibold text-[#2E5CE6] hover:underline" data-switch-modal="forgotPasswordModal">Lupa Password?</button>
                        </div>
                    </div>
                    <button type="submit" id="loginSubmitBtn" class="w-full btn-primary py-3 text-xs">Masuk Sekarang</button>
                </form>
                <p class="mt-4 text-sm text-slate-500 text-center">Belum punya akun? <button type="button" class="font-semibold text-[#2E5CE6] hover:underline" data-switch-modal="registerModal">Daftar di sini</button></p>
            </div>
        </div>
        <div id="registerModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" data-close-modal></div>
            <div class="relative w-full max-w-lg card p-6 md:p-8 z-10 max-h-[90vh] overflow-y-auto" id="registerModalCard">
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
                <form id="registerForm" method="post" action="<?= site_url('register-ajax') ?>" enctype="multipart/form-data" class="mt-6 space-y-5"><?= csrf_field() ?>

                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        <span class="text-red-500 font-bold">*</span> yang diberi bintang merah wajib diisi.
                    </p>


                    <div class="grid md:grid-cols-2 gap-x-4 gap-y-5">
                        <div class="md:col-span-2">
                            <label for="reg_nama" class="form-label">Nama Lengkap <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="modal-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </span>
                                <input id="reg_nama" name="nama" type="text" class="modal-input" placeholder="Budi Santoso" minlength="3" maxlength="100" pattern="[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*" title="Hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter)" required>
                            </div>
                            <p class="form-hint">Hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter).</p>
                        </div>
                        <div class="md:col-span-2">
                            <label for="reg_email" class="form-label">Email <span class="text-red-500">*</span></label>
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
                            <label for="reg_no_telp" class="form-label">No. Telepon <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="modal-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </span>
                                <input id="reg_no_telp" name="no_telp" type="tel" class="modal-input" placeholder="087778965442" inputmode="tel" pattern="^(\+62|08|022)[0-9]{8,13}$" maxlength="20" title="Format harus berupa angka dan diawali dengan 08, +62, atau 022" required>
                            </div>
                            <p class="form-hint">Format harus berupa angka dan diawali dengan 08, +62, atau 022 (Contoh: 087778965442) (8–13 digit setelah awalan).</p>
                        </div>
                        <div class="md:col-span-2">
                            <label for="reg_alamat" class="form-label">Alamat <span class="text-red-500">*</span></label>
                            <textarea id="reg_alamat" name="alamat" rows="2" maxlength="150" required class="modal-input min-h-[88px] resize-y" placeholder="Alamat lengkap tempat tinggal"></textarea>
                            <p class="form-hint">Minimal 10 karakter · maks. 150 karakter.</p>
                        </div>
                        <div>
                            <label for="reg_password" class="form-label">Kata Sandi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="modal-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                <input id="reg_password" name="password" type="password" class="modal-input pr-11" placeholder="••••••••" required>
                                <button type="button" id="regPasswordToggle" class="modal-input-toggle" aria-label="Tampilkan kata sandi">
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
                            <label for="reg_password_confirm" class="form-label">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="modal-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </span>
                                <input id="reg_password_confirm" name="password_confirm" type="password" class="modal-input pr-11" placeholder="••••••••" required>
                                <button type="button" id="regPasswordConfirmToggle" class="modal-input-toggle" aria-label="Tampilkan konfirmasi kata sandi">
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
                        <p class="md:col-span-2 form-hint">
                            Minimal 8 karakter dengan kombinasi huruf, angka, dan simbol.
                        </p>

                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" id="registerSubmitBtn" class="w-full btn-primary py-3 text-xs">Daftar Sekarang</button>
                    </div>
                </form>
                <p class="mt-4 text-sm text-slate-500 text-center">Sudah punya akun? <button type="button" class="font-semibold text-[#2E5CE6] hover:underline" data-switch-modal="loginModal">Masuk di sini</button></p>
            </div>
        </div>

        <div id="forgotPasswordModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" data-close-modal></div>
            <div class="relative w-full max-w-md card p-6 md:p-8 z-10">
                <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" data-close-modal aria-label="Tutup">&times;</button>

                <div class="text-center pt-2">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-[#EEF4FF] border border-[#DBEAFE]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#2E5CE6]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h4 class="text-2xl md:text-[28px] font-extrabold text-[#051747] leading-tight">Lupa Password?</h4>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed px-2">
                        Masukkan email akun Anda. Kami akan mengirim link reset kata sandi jika email terdaftar.
                    </p>
                </div>

                <div id="forgotPasswordAlert" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
                <form id="forgotPasswordForm" method="post" action="<?= site_url('lupa-sandi') ?>" class="mt-6 space-y-5"><?= csrf_field() ?>
                    <div>
                        <label for="forgot_email" class="form-label">Email</label>
                        <div class="relative">
                            <span class="modal-input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <input id="forgot_email" name="email" type="email" class="modal-input" placeholder="nama@email.com" required autocomplete="email">
                        </div>
                    </div>
                    <button type="submit" id="forgotPasswordSubmitBtn" class="w-full btn-primary py-3 text-xs">Kirim Link Reset</button>
                </form>
                <p class="mt-4 text-sm text-slate-500 text-center">Ingat password? <button type="button" class="font-semibold text-[#2E5CE6] hover:underline" data-switch-modal="loginModal">Masuk di sini</button></p>
            </div>
        </div>

        <div id="resetPasswordModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" data-close-modal></div>
            <div class="relative w-full max-w-lg card p-6 md:p-8 z-10 max-h-[90vh] overflow-y-auto">
                <button type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" data-close-modal aria-label="Tutup">&times;</button>

                <div class="text-center pt-2">
                    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-[#EEF4FF] border border-[#DBEAFE]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-[#2E5CE6]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h4 class="text-2xl md:text-[28px] font-extrabold text-[#051747] leading-tight">Buat Password Baru</h4>
                    <p class="mt-2 text-sm text-slate-500 leading-relaxed px-2">
                        Masukkan kata sandi baru untuk akun SIMENAK Anda.
                    </p>
                </div>

                <div id="resetPasswordAlert" class="hidden mt-4 px-4 py-3 rounded-xl text-sm font-medium"></div>
                <form id="resetPasswordForm" method="post" action="<?= site_url('atur-ulang-sandi') ?>" class="mt-6 space-y-5"><?= csrf_field() ?>
                    <input type="hidden" name="token" id="reset_password_token" value="">

                    <div class="grid md:grid-cols-2 gap-x-4 gap-y-5">
                        <div>
                            <label for="reset_password" class="form-label">Kata Sandi Baru <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="modal-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </span>
                                <input id="reset_password" name="password" type="password" class="modal-input pr-11" placeholder="••••••••" required autocomplete="new-password">
                                <button type="button" id="resetPasswordToggle" class="modal-input-toggle" aria-label="Tampilkan kata sandi">
                                    <svg id="resetEyeShow" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="resetEyeHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.029a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <div id="resetPasswordStrength" class="mt-2.5">
                                <p class="text-xs text-slate-500">
                                    Keamanan Sandi:
                                    <span id="resetPasswordStrengthLabel" class="password-strength-label font-semibold text-slate-400">—</span>
                                </p>
                                <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div id="resetPasswordStrengthBar" class="password-strength-bar h-full rounded-full bg-slate-200" style="width:0%"></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="reset_password_confirm" class="form-label">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="modal-input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </span>
                                <input id="reset_password_confirm" name="password_confirm" type="password" class="modal-input pr-11" placeholder="••••••••" required autocomplete="new-password">
                                <button type="button" id="resetPasswordConfirmToggle" class="modal-input-toggle" aria-label="Tampilkan konfirmasi kata sandi">
                                    <svg id="resetConfirmEyeShow" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="resetConfirmEyeHide" xmlns="http://www.w3.org/2000/svg" class="hidden h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858 3.029a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="md:col-span-2 form-hint">
                            Minimal 8 karakter dengan kombinasi huruf, angka, dan simbol.
                        </p>
                    </div>
                    <button type="submit" id="resetPasswordSubmitBtn" class="w-full btn-primary py-3 text-xs">Simpan Password Baru</button>
                </form>
                <p class="mt-4 text-sm text-slate-500 text-center">Link kedaluwarsa? <button type="button" class="font-semibold text-[#2E5CE6] hover:underline" data-switch-modal="forgotPasswordModal">Minta link baru</button></p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        (() => {
            const APP_ENDPOINTS = <?= json_encode([
                                        'csrfSync'      => $ajaxRoutePath('csrf-sync'),
                                        'loginAjax'     => $ajaxRoutePath('login-ajax'),
                                        'registerAjax'  => $ajaxRoutePath('register-ajax'),
                                        'resetSandi'    => $ajaxRoutePath('atur-ulang-sandi'),
                                    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

            const modals = document.querySelectorAll('#loginModal, #registerModal, #forgotPasswordModal, #resetPasswordModal');
            const csrfFieldName = document.querySelector('meta[name="csrf-field"]')?.content || 'csrf_test_name';
            const csrfHeaderName = '<?= esc(config('Security')->headerName) ?>';
            const openModalOnLoad = <?= json_encode($openModalOnLoad ?: null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            const forgotPasswordNotice = <?= json_encode($forgotPasswordNotice ?: null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            const forgotPasswordError = <?= json_encode($forgotPasswordError ?: null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

            const toAjaxUrl = (endpoint) => {
                if (!endpoint) {
                    return endpoint;
                }

                if (/^https?:\/\//i.test(endpoint)) {
                    try {
                        const absolute = new URL(endpoint);
                        endpoint = `${absolute.pathname}${absolute.search}`;
                    } catch (e) {
                        return endpoint;
                    }
                }

                const path = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;

                return `${window.location.origin}${path}`;
            };

            const csrfSyncUrl = toAjaxUrl(APP_ENDPOINTS.csrfSync);

            const closeAll = () => {
                modals.forEach((m) => {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                });
                document.body.classList.remove('overflow-hidden');
            };

            const getCsrfValue = (form) => {
                const input = form?.querySelector(`input[name="${csrfFieldName}"]`);
                if (input?.value) {
                    return input.value;
                }
                return document.querySelector('meta[name="csrf-token"]')?.content || '';
            };

            const applyCsrfToken = (token) => {
                if (!token) {
                    return;
                }

                document.querySelectorAll(`input[name="${csrfFieldName}"]`).forEach((input) => {
                    input.value = token;
                });

                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) {
                    meta.content = token;
                }
            };

            const refreshCsrfTokens = async () => {
                try {
                    const response = await fetch(csrfSyncUrl, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    });

                    if (!response.ok) {
                        return false;
                    }

                    const data = await response.json();
                    if (data?.token) {
                        applyCsrfToken(data.token);
                        return true;
                    }
                } catch (e) {
                    /* ignore */
                }

                return false;
            };

            const openById = async (id) => {
                if (id === 'loginModal' || id === 'registerModal' || id === 'forgotPasswordModal' || id === 'resetPasswordModal') {
                    await refreshCsrfTokens();
                }

                const m = document.getElementById(id);
                if (!m) return;

                m.classList.remove('hidden');
                m.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const showAlert = (el, message, isSuccess) => {
                if (!el) return;
                el.textContent = message;
                el.classList.remove('hidden', 'notice-danger', 'notice-success', 'flex', 'gap-3', 'items-start');
                el.classList.add(isSuccess ? 'notice-success' : 'notice-danger');
            };

            const showForgotPasswordSuccessAlert = (el, message, devNote = '') => {
                if (!el) return;
                el.classList.remove('hidden', 'notice-danger', 'notice-success');
                el.classList.add('notice-success', 'flex', 'gap-3', 'items-start');
                el.innerHTML = `
                    <span class="shrink-0 flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-white" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <div class="min-w-0 space-y-2 pt-0.5">
                        <p class="leading-relaxed"></p>
                    </div>
                `;
                el.querySelector('p').textContent = message;
                if (devNote) {
                    const devEl = document.createElement('p');
                    devEl.className = 'text-xs leading-relaxed text-amber-800';
                    devEl.textContent = devNote;
                    el.querySelector('.space-y-2').appendChild(devEl);
                }
            };

            const isCsrfFailure = (result) => {
                if (result?.status === 403) {
                    return true;
                }

                const raw = (result?.rawText || '').toLowerCase();
                return raw.includes('not allowed') || raw.includes('csrf');
            };

            const postFormAjax = async (form, isRetry = false) => {
                applyCsrfToken(getCsrfValue(form));

                const formData = new FormData(form);
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfHeaderName]: getCsrfValue(form),
                };

                const targetUrl = form.getAttribute('data-action-url') || form.action;

                let response;
                try {
                    response = await fetch(targetUrl, {
                        method: 'POST',
                        headers,
                        body: formData,
                        credentials: 'same-origin',
                    });
                } catch (networkError) {
                    return {
                        ok: false,
                        status: 0,
                        data: null,
                        rawText: '',
                        parseError: true,
                        networkError: networkError?.message || 'Failed to fetch',
                        requestUrl: targetUrl,
                    };
                }

                const rawText = await response.text();
                let data = null;

                try {
                    if (rawText.trim() !== '') {
                        data = JSON.parse(rawText);
                    }
                } catch (parseError) {
                    const csrfFailed = isCsrfFailure({
                        status: response.status,
                        rawText
                    });
                    if (csrfFailed && !isRetry && await refreshCsrfTokens()) {
                        return postFormAjax(form, true);
                    }

                    return {
                        ok: response.ok,
                        status: response.status,
                        data: null,
                        rawText,
                        parseError: true,
                    };
                }

                const headerToken = response.headers.get(csrfHeaderName);
                if (headerToken) {
                    applyCsrfToken(headerToken);
                }

                if (isCsrfFailure({
                        status: response.status,
                        rawText,
                        data
                    }) && !isRetry && await refreshCsrfTokens()) {
                    return postFormAjax(form, true);
                }

                return {
                    ok: response.ok,
                    status: response.status,
                    data,
                    rawText,
                    parseError: false,
                    requestUrl: targetUrl,
                };
            };

            const csrfExpiredMessage = 'Token keamanan formulir kedaluwarsa. Silakan coba lagi — halaman akan memperbarui token otomatis.';

            const extractAjaxErrorMessage = (result, fallback) => {
                const data = result?.data;

                const mapCsrfMessage = (text) => {
                    if (typeof text !== 'string') {
                        return text;
                    }

                    const lower = text.toLowerCase();
                    if (lower.includes('not allowed') || lower.includes('csrf')) {
                        void refreshCsrfTokens();
                        return csrfExpiredMessage;
                    }

                    return text;
                };

                if (result?.networkError || result?.status === 0) {
                    const url = result?.requestUrl ? ` (${result.requestUrl})` : '';
                    return `Tidak dapat menghubungi server${url}. Pastikan Apache/XAMPP atau \`php spark serve\` sedang berjalan, lalu refresh halaman (Ctrl+F5) dan coba lagi.`;
                }

                if (result?.parseError && typeof result?.rawText === 'string') {
                    const raw = result.rawText.toLowerCase();
                    if (raw.includes('not allowed') || raw.includes('csrf')) {
                        void refreshCsrfTokens();
                        return csrfExpiredMessage;
                    }
                }

                if (data) {
                    if (typeof data.message === 'string' && data.message.trim() !== '') {
                        return mapCsrfMessage(data.message.trim());
                    }

                    if (data.errors && typeof data.errors === 'object') {
                        const errorMessages = Object.values(data.errors)
                            .flatMap((value) => Array.isArray(value) ? value : [value])
                            .map((value) => String(value).trim())
                            .filter(Boolean);

                        if (errorMessages.length > 0) {
                            return errorMessages.join(' ');
                        }
                    }
                }

                if (result?.parseError) {
                    if (result.status === 404) {
                        return 'Endpoint registrasi tidak ditemukan. Pastikan URL aplikasi benar (cek baseURL).';
                    }

                    const snippet = (result.rawText || '').replace(/\s+/g, ' ').trim().slice(0, 160);
                    return snippet !== '' ?
                        `Server mengembalikan respons tidak valid (HTTP ${result.status}): ${snippet}` :
                        `Server mengembalikan respons tidak valid (HTTP ${result.status}). Refresh halaman lalu coba lagi.`;
                }

                if (result?.status === 413) {
                    return 'Ukuran unggahan terlalu besar. Pastikan setiap dokumen maks. 2MB.';
                }

                return fallback;
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

            const bindAjaxFormAction = (form, url) => {
                if (!form || !url) return;
                const resolved = toAjaxUrl(url);
                form.action = resolved;
                form.setAttribute('data-action-url', resolved);
            };

            bindAjaxFormAction(loginForm, APP_ENDPOINTS.loginAjax);
            bindAjaxFormAction(registerForm, APP_ENDPOINTS.registerAjax);

            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            const forgotPasswordAlert = document.getElementById('forgotPasswordAlert');
            const resetPasswordAlert = document.getElementById('resetPasswordAlert');

            bindAjaxFormAction(resetPasswordForm, APP_ENDPOINTS.resetSandi);

            const handleDeepLinkModals = async () => {
                const params = new URLSearchParams(window.location.search);
                const resetToken = params.get('reset_token');
                const openModal = params.get('open') || openModalOnLoad;
                const resetSuccess = params.get('reset_success') === '1';

                if (resetToken) {
                    const tokenInput = document.getElementById('reset_password_token');
                    if (tokenInput) {
                        tokenInput.value = resetToken;
                    }
                    await openById('resetPasswordModal');
                } else if (openModal) {
                    await openById(openModal);
                    if (openModal === 'loginModal' && resetSuccess) {
                        showAlert(
                            loginAlert,
                            'Password berhasil diubah. Silakan masuk dengan kata sandi baru.',
                            true
                        );
                    }
                }

                if (resetToken || openModal || resetSuccess) {
                    const cleanUrl = window.location.pathname + window.location.hash;
                    window.history.replaceState({}, document.title, cleanUrl);
                }

                if (forgotPasswordNotice) {
                    await openById('forgotPasswordModal');
                    const devPrefix = ' Catatan dev:';
                    let mainMsg = forgotPasswordNotice;
                    let devNote = '';
                    const devIdx = forgotPasswordNotice.indexOf(devPrefix);
                    if (devIdx !== -1) {
                        mainMsg = forgotPasswordNotice.substring(0, devIdx).trim();
                        devNote = forgotPasswordNotice.substring(devIdx).trim();
                    }
                    showForgotPasswordSuccessAlert(forgotPasswordAlert, mainMsg, devNote);
                } else if (forgotPasswordError) {
                    await openById('forgotPasswordModal');
                    showAlert(forgotPasswordAlert, forgotPasswordError, false);
                }
            };

            void handleDeepLinkModals();

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
                        loginPasswordToggle.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                    });
                }

                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const btn = document.getElementById('loginSubmitBtn');
                    btn.disabled = true;
                    btn.textContent = 'Memproses...';

                    try {
                        const result = await postFormAjax(loginForm);
                        if (result.ok && result.data?.success) {
                            window.location.href = result.data.redirect || '<?= site_url('dashboard') ?>';
                            return;
                        }
                        showAlert(loginAlert, extractAjaxErrorMessage(result, 'Gagal masuk.'), false);
                        if (result.data?.redirect) {
                            setTimeout(() => {
                                window.location.href = result.data.redirect;
                            }, 2200);
                        }
                    } catch (err) {
                        showAlert(loginAlert, extractAjaxErrorMessage({
                            networkError: err?.message,
                            status: 0,
                            requestUrl: loginForm?.getAttribute('data-action-url') || loginForm?.action,
                        }, 'Terjadi kesalahan jaringan. Silakan coba lagi.'), false);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Masuk Sekarang';
                    }
                });
            }

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

            const passwordStrengthLevels = [{
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

            const passwordStrengthBarColors = passwordStrengthLevels.map((level) => level.bar);

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

                if (score <= 1) return passwordStrengthLevels[0];
                if (score === 2) return passwordStrengthLevels[1];
                if (score === 3 || score === 4) return passwordStrengthLevels[2];
                return passwordStrengthLevels[3];
            };

            const bindPasswordStrengthMeter = (inputEl, labelEl, barEl) => {
                const reset = () => {
                    if (!labelEl || !barEl) return;
                    labelEl.textContent = '—';
                    labelEl.className = 'password-strength-label font-semibold text-slate-400';
                    barEl.style.width = '0%';
                    passwordStrengthBarColors.forEach((color) => barEl.classList.remove(color));
                    barEl.classList.add('bg-slate-200');
                };

                const update = (password) => {
                    if (!labelEl || !barEl) return;

                    const level = getPasswordStrength(password);
                    if (!level) {
                        reset();
                        return;
                    }

                    labelEl.textContent = level.label;
                    labelEl.className = `password-strength-label font-semibold ${level.text}`;
                    passwordStrengthBarColors.forEach((color) => barEl.classList.remove(color));
                    barEl.classList.remove('bg-slate-200');
                    barEl.classList.add(level.bar);
                    barEl.style.width = `${level.width}%`;
                };

                if (inputEl) {
                    inputEl.addEventListener('input', () => update(inputEl.value));
                }

                return {
                    reset,
                    update
                };
            };

            if (registerForm) {
                const phonePattern = /^(\+62|08|022)[0-9]{8,13}$/;
                const namaLengkapPattern = /^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*$/u;

                const bindPhoneInput = (input) => {
                    if (!input) return;
                    input.addEventListener('input', () => {
                        let v = input.value.replace(/[^\d+]/g, '');
                        if (v.includes('+')) {
                            v = '+' + v.replace(/\+/g, '');
                        }
                        input.value = v.slice(0, 20);
                    });
                };

                const regPhoneInput = document.getElementById('reg_no_telp');
                bindPhoneInput(regPhoneInput);

                setupPasswordToggle(
                    document.getElementById('reg_password'),
                    document.getElementById('regPasswordToggle'),
                    document.getElementById('regEyeShow'),
                    document.getElementById('regEyeHide'),
                    'Tampilkan kata sandi',
                    'Sembunyikan kata sandi'
                );

                setupPasswordToggle(
                    document.getElementById('reg_password_confirm'),
                    document.getElementById('regPasswordConfirmToggle'),
                    document.getElementById('regConfirmEyeShow'),
                    document.getElementById('regConfirmEyeHide'),
                    'Tampilkan konfirmasi kata sandi',
                    'Sembunyikan konfirmasi kata sandi'
                );

                const regPasswordInput = document.getElementById('reg_password');
                const regStrengthMeter = bindPasswordStrengthMeter(
                    regPasswordInput,
                    document.getElementById('regPasswordStrengthLabel'),
                    document.getElementById('regPasswordStrengthBar')
                );

                registerForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const nama = document.getElementById('reg_nama')?.value.trim() || '';
                    if (nama.length < 3 || nama.length > 100 || !namaLengkapPattern.test(nama)) {
                        showAlert(registerAlert, 'Nama lengkap hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter).', false);
                        return;
                    }

                    const phone = regPhoneInput ? regPhoneInput.value.trim() : '';
                    if (!phonePattern.test(phone)) {
                        showAlert(registerAlert, 'Format no. telepon harus berupa angka dan diawali dengan 08, +62, atau 022 (Contoh: 087778965442) (8–13 digit setelah awalan).', false);
                        return;
                    }

                    const alamat = document.getElementById('reg_alamat')?.value.trim() || '';
                    if (alamat.length < 10 || alamat.length > 150) {
                        showAlert(registerAlert, 'Alamat wajib diisi (minimal 10 karakter, maks. 150 karakter).', false);
                        return;
                    }

                    const btn = document.getElementById('registerSubmitBtn');
                    btn.disabled = true;
                    btn.textContent = 'Memproses...';

                    try {
                        const result = await postFormAjax(registerForm);
                        if (result.ok && result.data?.success) {
                            showAlert(registerAlert, result.data.message, true);
                            registerForm.reset();
                            regStrengthMeter?.reset();
                            setTimeout(async () => {
                                await refreshCsrfTokens();
                                closeAll();
                                await openById('loginModal');
                                showAlert(loginAlert, result.data.message, true);
                            }, 1200);
                            return;
                        }
                        showAlert(
                            registerAlert,
                            extractAjaxErrorMessage(result, 'Registrasi gagal. Periksa kembali semua field wajib.'),
                            false
                        );
                    } catch (err) {
                        showAlert(registerAlert, extractAjaxErrorMessage({
                            networkError: err?.message,
                            status: 0,
                            requestUrl: registerForm?.getAttribute('data-action-url') || registerForm?.action,
                        }, 'Terjadi kesalahan jaringan. Silakan coba lagi.'), false);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Daftar Sekarang';
                    }
                });
            }

            if (forgotPasswordForm) {
                forgotPasswordForm.addEventListener('submit', () => {
                    const btn = document.getElementById('forgotPasswordSubmitBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = 'Mengirim...';
                    }
                });
            }

            if (resetPasswordForm) {
                setupPasswordToggle(
                    document.getElementById('reset_password'),
                    document.getElementById('resetPasswordToggle'),
                    document.getElementById('resetEyeShow'),
                    document.getElementById('resetEyeHide'),
                    'Tampilkan kata sandi',
                    'Sembunyikan kata sandi'
                );

                setupPasswordToggle(
                    document.getElementById('reset_password_confirm'),
                    document.getElementById('resetPasswordConfirmToggle'),
                    document.getElementById('resetConfirmEyeShow'),
                    document.getElementById('resetConfirmEyeHide'),
                    'Tampilkan konfirmasi kata sandi',
                    'Sembunyikan konfirmasi kata sandi'
                );

                const resetPasswordInput = document.getElementById('reset_password');
                const resetStrengthMeter = bindPasswordStrengthMeter(
                    resetPasswordInput,
                    document.getElementById('resetPasswordStrengthLabel'),
                    document.getElementById('resetPasswordStrengthBar')
                );

                resetPasswordForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const password = resetPasswordInput?.value || '';
                    const confirm = document.getElementById('reset_password_confirm')?.value || '';
                    const token = document.getElementById('reset_password_token')?.value || '';

                    if (password.length < 8) {
                        showAlert(resetPasswordAlert, 'Kata sandi minimal 8 karakter.', false);
                        return;
                    }

                    if (password !== confirm) {
                        showAlert(resetPasswordAlert, 'Konfirmasi kata sandi tidak sama.', false);
                        return;
                    }

                    if (!token) {
                        showAlert(resetPasswordAlert, 'Link reset password tidak valid. Silakan minta link baru.', false);
                        return;
                    }

                    const btn = document.getElementById('resetPasswordSubmitBtn');
                    btn.disabled = true;
                    btn.textContent = 'Menyimpan...';

                    try {
                        const result = await postFormAjax(resetPasswordForm);
                        if (result.ok && result.data?.success) {
                            closeAll();
                            if (result.data.redirect) {
                                window.location.href = result.data.redirect;
                                return;
                            }
                            await openById('loginModal');
                            showAlert(loginAlert, result.data.message, true);
                            resetPasswordForm.reset();
                            resetStrengthMeter?.reset();
                            return;
                        }

                        const fallback = result.status === 410 ?
                            'Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.' :
                            'Gagal mengubah kata sandi.';
                        showAlert(resetPasswordAlert, extractAjaxErrorMessage(result, fallback), false);

                        if (result.status === 410) {
                            setTimeout(async () => {
                                closeAll();
                                await openById('forgotPasswordModal');
                            }, 2200);
                        }
                    } catch (err) {
                        showAlert(resetPasswordAlert, extractAjaxErrorMessage({
                            networkError: err?.message,
                            status: 0,
                            requestUrl: resetPasswordForm?.getAttribute('data-action-url') || resetPasswordForm?.action,
                        }, 'Terjadi kesalahan jaringan. Silakan coba lagi.'), false);
                    } finally {
                        btn.disabled = false;
                        btn.textContent = 'Simpan Password Baru';
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