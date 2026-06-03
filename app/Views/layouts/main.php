<?php
$role        = (string) session()->get('role');
$namaUser    = (string) session()->get('nama');
$idUser      = (int) session()->get('id_user');
$currentPath = trim(uri_string(), '/');

$unreadCount = 0;
if ($idUser > 0) {
    try {
        $unreadCount = model(\App\Models\NotificationModel::class)->getUnreadCount($idUser);
    } catch (\Throwable $e) {
        $unreadCount = 0;
    }
}

$menusByRole = [
    'pelanggan' => [
        ['label' => 'Dashboard',     'url' => 'dashboard',           'icon' => 'home'],
        ['label' => 'Pesanan Saya',  'url' => 'order',                 'icon' => 'clipboard'],
        ['label' => 'Katalog',       'url' => 'katalog',             'icon' => 'grid'],
        ['label' => 'Profil',        'url' => 'profil',              'icon' => 'user'],
    ],
    'admin' => [
        ['label' => 'Dashboard',              'url' => 'dashboard',              'icon' => 'home'],
        ['label' => 'List Pemesanan',         'url' => 'list-pemesanan',         'icon' => 'clipboard'],
        ['label' => 'Katalog',                'url' => 'katalog/kelola',                'icon' => 'grid'],
        ['label' => 'Pengguna',               'url' => 'pengguna',               'icon' => 'users'],
        ['label' => 'Verifikasi Perusahaan',  'url' => 'verifikasi-perusahaan',  'icon' => 'shield'],
        ['label' => 'Pengiriman',             'url' => 'pengiriman',             'icon' => 'truck'],
        ['label' => 'Laporan',                'url' => 'laporan',                'icon' => 'chart'],
    ],
    'keuangan' => [
        ['label' => 'Dashboard',            'url' => 'dashboard',            'icon' => 'home'],
        ['label' => 'Verifikasi DP',        'url' => 'verifikasi-dp',        'icon' => 'wallet'],
        ['label' => 'Verifikasi Pelunasan', 'url' => 'verifikasi-pelunasan', 'icon' => 'check-circle'],
    ],
    'produksi' => [
        ['label' => 'Dashboard',       'url' => 'dashboard',       'icon' => 'home'],
        ['label' => 'Antrian Desain',  'url' => 'antrian-desain',  'icon' => 'layers'],
        ['label' => 'Manajemen Desain', 'url' => 'manajemen-desain', 'icon' => 'edit'],
    ],
    'owner' => [
        ['label' => 'Dashboard', 'url' => 'dashboard', 'icon' => 'home'],
        ['label' => 'Laporan',   'url' => 'laporan',   'icon' => 'chart'],
    ],
];

$menuItems = $menusByRole[$role] ?? [];

$isMenuActive = static function (string $menuUrl) use ($currentPath): bool {
    if ($menuUrl === 'dashboard') {
        return $currentPath === '' || $currentPath === 'dashboard';
    }

    return $currentPath === $menuUrl || str_starts_with($currentPath, $menuUrl . '/');
};

$iconSvg = static function (string $icon): string {
    $icons = [
        'home'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'clipboard'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
        'grid'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        'user'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'users'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'shield'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'star'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
        'truck'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10m10 0h4m-4 0a2 2 0 11-4 0m4 0a2 2 0 10-4 0M4 16h4m0 0a2 2 0 11-4 0m4 0a2 2 0 10-4 0"/>',
        'chart'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'wallet'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'layers'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>',
        'edit'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
    ];

    $path = $icons[$icon] ?? $icons['home'];

    return '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">' . $path . '</svg>';
};
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($this->renderSection('title') ?: 'SIMENAK') ?> — Z'Plack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --navy: #051747;
            --navy-mid: #0a2860;
            --blue-accent: #2E5CE6;
            --bg-page: #F0F2F8;
            --bg-white: #FFFFFF;
            --text-body: #4A5568;
            --text-muted: #8896A5;
            --border: #E2E8F0;
            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --shadow-sm: 0 1px 3px rgba(47, 65, 86, .06);
            --shadow-md: 0 4px 16px rgba(47, 65, 86, .08);
            --shadow-lg: 0 12px 40px rgba(47, 65, 86, .12);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-body);
            background: var(--bg-page);
        }

        .sidebar {
            background: var(--bg-white);
            width: 256px;
        }

        .sidebar-link {
            color: var(--text-body);
            transition: background-color .2s ease, color .2s ease;
        }

        .sidebar-link:hover {
            background: rgba(5, 23, 71, .06);
        }

        .sidebar-link.active {
            background: var(--navy);
            color: #ffffff;
        }

        .sidebar-link.active:hover {
            background: var(--navy);
        }

        .btn-primary {
            background: var(--navy);
            border-radius: 999px;
            font-weight: 700;
            text-transform: uppercase;
            transition: background-color .2s ease;
        }

        .btn-primary:hover {
            background: var(--blue-accent);
        }

        .card {
            background: var(--bg-white);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            transition: box-shadow .2s ease, transform .2s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .input-field {
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .input-field:focus {
            border-color: var(--blue-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
        }

        .glass-topbar {
            background: var(--bg-white);
            border-bottom: 1px solid var(--border);
            transition: background .25s ease, backdrop-filter .25s ease, border-color .25s ease;
        }

        .glass-topbar.is-scrolled {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, .75);
            border-bottom-color: rgba(226, 232, 240, .7);
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body class="min-h-screen">

    <!-- Sidebar -->
    <aside class="sidebar fixed left-0 top-0 bottom-0 z-40 flex flex-col border-r" style="border-color:var(--border);">
        <div class="px-6 py-6 border-b" style="border-color:var(--border);">
            <a href="<?= site_url('dashboard') ?>" class="block">
                <span class="text-xl font-extrabold tracking-tight" style="color:var(--navy);">Z'PLACK</span>
                <span class="block text-sm font-bold tracking-widest" style="color:#6b9fff;">SIMENAK</span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <?php foreach ($menuItems as $item): ?>
                <?php $active = $isMenuActive($item['url']); ?>
                <a href="<?= site_url($item['url']) ?>"
                    class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium <?= $active ? 'active' : '' ?>">
                    <?= $iconSvg($item['icon']) ?>
                    <span><?= esc($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="px-4 py-4 border-t" style="border-color:var(--border);">
            <button type="button"
                data-open-logout-modal
                class="sidebar-link w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Keluar</span>
            </button>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="ml-64 min-h-screen flex flex-col">

        <!-- Topbar -->
        <header id="mainTopbar" class="glass-topbar sticky top-0 z-30 h-16 flex items-center justify-between px-6">
            <div>
                <h1 class="text-lg font-bold text-[#051747]">
                    <?= esc($this->renderSection('page_title') ?: 'Dashboard') ?>
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <!-- Notification Bell -->
                <button type="button"
                    data-open-notif-modal
                    class="relative p-2 rounded-lg hover:bg-slate-50 transition-colors"
                    title="Notifikasi"
                    aria-label="Buka notifikasi">
                    <svg class="w-6 h-6 text-[#4A5568]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span id="notifBellBadge"
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full px-1 <?= $unreadCount > 0 ? '' : 'hidden' ?>">
                        <?= $unreadCount > 99 ? '99+' : esc((string) $unreadCount) ?>
                    </span>
                </button>

                <!-- User Info -->
                <div class="flex items-center gap-3 pl-4 border-l" style="border-color:var(--border);">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background:var(--blue-accent);">
                        <?= esc(strtoupper(substr($namaUser ?: 'U', 0, 1))) ?>
                    </div>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-[#051747] leading-tight"><?= esc($namaUser ?: 'Pengguna') ?></p>
                        <p class="text-xs capitalize" style="color:var(--text-muted);"><?= esc($role ?: '-') ?></p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-6" style="background:var(--bg-page);">

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('warning')): ?>
                <div class="mb-4 px-4 py-3 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 text-sm font-medium">
                    <?= esc(session()->getFlashdata('warning')) ?>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <?= view('partials/notification_modal') ?>
    <?= view('partials/logout_modal') ?>
    <?= $this->renderSection('scripts') ?>
    <script>
        (function() {
            const topbar = document.getElementById('mainTopbar');
            if (!topbar) return;

            const onScroll = () => {
                topbar.classList.toggle('is-scrolled', window.scrollY > 8);
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
    </script>
</body>

</html>