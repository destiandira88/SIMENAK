<?php
$role        = (string) session()->get('role');
$namaUser    = (string) session()->get('nama');
$idUser      = (int) session()->get('id_user');
$currentPath = trim(uri_string(), '/');

$unreadCount = 0;
$userEmail   = '';
if ($idUser > 0) {
    try {
        $unreadCount = model(\App\Models\NotificationModel::class)->getUnreadCount($idUser);
    } catch (\Throwable $e) {
        $unreadCount = 0;
    }

    try {
        $userRow = \Config\Database::connect()
            ->table('users')
            ->select('email')
            ->where('id_user', $idUser)
            ->get()
            ->getRowArray();
        $userEmail = (string) ($userRow['email'] ?? '');
    } catch (\Throwable $e) {
        $userEmail = '';
    }
}

$profilData      = null;
$openProfilModal      = (bool) session()->getFlashdata('open_profil_modal');
$openProfilFromQuery  = service('request')->getGet('profil') === '1';

if ($role === 'pelanggan' && $idUser > 0) {
    $idPelanggan = (int) session()->get('id_pelanggan');

    if ($idPelanggan > 0) {
        try {
            $dbProfil = \Config\Database::connect();

            $profilUser = $dbProfil->table('users')
                ->where('id_user', $idUser)
                ->get()
                ->getRowArray();

            $profilPelanggan = $dbProfil->table('pelanggan')
                ->where('id_pelanggan', $idPelanggan)
                ->get()
                ->getRowArray();

            if ($profilUser !== null && $profilPelanggan !== null) {
                helper('notification');
                $verifikasiTerbaru = $dbProfil->table('verifikasi_perusahaan')
                    ->where('id_pelanggan', $idPelanggan)
                    ->orderBy('tgl_pengajuan', 'DESC')
                    ->get()
                    ->getRowArray();

                if (
                    trim((string) ($profilPelanggan['alamat'] ?? '')) === ''
                    && ! empty($verifikasiTerbaru['alamat_kantor'])
                ) {
                    $alamatKantor = mb_substr(trim((string) $verifikasiTerbaru['alamat_kantor']), 0, 150);
                    $dbProfil->table('pelanggan')
                        ->where('id_pelanggan', $idPelanggan)
                        ->update(['alamat' => $alamatKantor]);
                    $profilPelanggan['alamat'] = $alamatKantor;
                }

                $profilData = [
                    'user'              => $profilUser,
                    'pelanggan'         => $profilPelanggan,
                    'verifikasiTerbaru' => $verifikasiTerbaru ?: null,
                ];
            }
        } catch (\Throwable $e) {
            $profilData = null;
        }
    }
}

$menusByRole = [
    'pelanggan' => [
        ['label' => 'Beranda',     'url' => 'dashboard',           'icon' => 'home'],
        ['label' => 'Pesanan Saya',  'url' => 'order',                 'icon' => 'clipboard'],
        ['label' => 'Katalog',       'url' => 'katalog',             'icon' => 'grid'],
    ],
    'admin' => [
        ['label' => 'Beranda',              'url' => 'dashboard',              'icon' => 'home'],
        ['label' => 'List Pemesanan',         'url' => 'list-pemesanan',         'icon' => 'clipboard'],
        ['label' => 'Katalog',                'url' => 'katalog/kelola',                'icon' => 'grid'],
        ['label' => 'Pengguna',               'url' => 'pengguna',               'icon' => 'users'],
        ['label' => 'Pengiriman',             'url' => 'pengiriman',             'icon' => 'truck'],
        ['label' => 'Laporan',                'url' => 'laporan-admin',          'icon' => 'chart'],
    ],
    'keuangan' => [
        ['label' => 'Beranda',            'url' => 'dashboard',            'icon' => 'home'],
        ['label' => 'Verifikasi DP',        'url' => 'verifikasi-dp',        'icon' => 'wallet'],
        ['label' => 'Riwayat Pembayaran',   'url' => 'riwayat-pembayaran',   'icon' => 'clock'],
        ['label' => 'Verifikasi Pelunasan', 'url' => 'verifikasi-pelunasan', 'icon' => 'check-circle'],
        ['label' => 'Laporan Transaksi',  'url' => 'laporan-keuangan',     'icon' => 'chart'],
    ],
    'produksi' => [
        ['label' => 'Beranda',       'url' => 'dashboard',       'icon' => 'home'],
        ['label' => 'Antrian Desain',  'url' => 'antrian-desain',  'icon' => 'layers'],
        ['label' => 'Manajemen Desain', 'url' => 'manajemen-desain', 'icon' => 'edit'],
    ],
    'owner' => [
        ['label' => 'Beranda',          'url' => 'dashboard',          'icon' => 'home'],
        ['label' => 'Pesanan',            'url' => 'list-pemesanan',     'icon' => 'clipboard'],
        ['label' => 'Katalog',            'url' => 'katalog/kelola',     'icon' => 'grid'],
        ['label' => 'Pengguna',           'url' => 'pengguna',           'icon' => 'users'],
        ['label' => 'Riwayat Pembayaran', 'url' => 'riwayat-pembayaran', 'icon' => 'clock'],
        ['label' => 'Riwayat Aktivitas',  'url' => 'riwayat-aktivitas',  'icon' => 'shield'],
        ['label' => 'Manajemen Desain',   'url' => 'manajemen-desain',   'icon' => 'edit'],
        [
            'label'    => 'Laporan',
            'icon'     => 'chart',
            'url'      => 'laporan',
            'children' => [
                ['label' => 'Ringkasan Bisnis', 'url' => 'laporan'],
                ['label' => 'Pemesanan',        'url' => 'laporan-admin'],
                ['label' => 'Transaksi',        'url' => 'laporan-keuangan'],
                ['label' => 'Desain',             'url' => 'laporan-produksi'],
            ],
        ],
    ],
];

$menuItems = $menusByRole[$role] ?? [];

$isMenuActive = static function (string $menuUrl) use ($currentPath): bool {
    if ($menuUrl === 'dashboard') {
        return $currentPath === '' || $currentPath === 'dashboard';
    }

    return $currentPath === $menuUrl || str_starts_with($currentPath, $menuUrl . '/');
};

$isMenuGroupActive = static function (array $item) use ($isMenuActive): bool {
    foreach ($item['children'] ?? [] as $child) {
        if ($isMenuActive((string) ($child['url'] ?? ''))) {
            return true;
        }
    }

    return false;
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
        'clock'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
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
    <script>
        (function() {
            try {
                if (localStorage.getItem('simenak-dashboard-theme') === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
                if (localStorage.getItem('simenak-sidebar-collapsed') === '1') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
    <?= view('partials/csrf_meta') ?>
    <title><?= esc($this->renderSection('title') ?: 'SIMENAK') ?>-Z'Plack</title>
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
            --bg-sidebar: #FFFFFF;
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
            --sidebar-width: 256px;
            --sidebar-width-collapsed: 72px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-body);
            background: var(--bg-page);
        }

        .sidebar {
            background: var(--bg-sidebar);
            width: var(--sidebar-width);
            transition: width 0.25s ease, transform 0.3s ease;
        }

        html.sidebar-collapsed .sidebar {
            width: var(--sidebar-width-collapsed);
        }

        html.sidebar-collapsed .sidebar-brand-detail,
        html.sidebar-collapsed .sidebar-label,
        html.sidebar-collapsed .sidebar-chevron {
            display: none;
        }

        html.sidebar-collapsed .sidebar-brand {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        html.sidebar-collapsed .sidebar-link,
        html.sidebar-collapsed .sidebar-link-parent {
            justify-content: center;
            gap: 0;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        html.sidebar-collapsed .sidebar-submenu {
            display: none !important;
        }

        .main-wrapper {
            transition: margin-left 0.25s ease;
        }

        .sidebar-collapse-btn {
            display: none;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg-white);
            color: var(--text-body);
            flex-shrink: 0;
            transition: background-color .2s, border-color .2s, color .2s;
        }

        .sidebar-collapse-btn:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: var(--navy);
        }

        .topbar-divider {
            display: none;
            width: 1px;
            height: 1.25rem;
            background: var(--border);
            flex-shrink: 0;
        }

        .sidebar-link {
            color: var(--text-body);
            transition:
                background-color .28s cubic-bezier(0.4, 0, 0.2, 1),
                color .28s cubic-bezier(0.4, 0, 0.2, 1),
                transform .28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-link > svg:not(.sidebar-chevron) {
            flex-shrink: 0;
            transition: transform .28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html:not(.sidebar-collapsed) .sidebar-link:not(.active):hover,
        html:not(.sidebar-collapsed) .sidebar-link-parent:not(.active):hover {
            transform: translateX(4px);
        }

        html.sidebar-collapsed .sidebar-link:not(.active):hover,
        html.sidebar-collapsed .sidebar-link-parent:not(.active):hover {
            transform: scale(1.06);
        }

        .sidebar-link:not(.active):hover > svg:not(.sidebar-chevron),
        .sidebar-link-parent:not(.active):hover > svg:not(.sidebar-chevron) {
            transform: scale(1.08);
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

        .sidebar-link-parent {
            border: none;
            background: transparent;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
        }

        .sidebar-link-parent.is-expanded {
            color: var(--navy);
            font-weight: 600;
        }

        .sidebar-chevron {
            margin-left: auto;
            flex-shrink: 0;
            transition: transform .2s ease;
        }

        .sidebar-chevron.is-open {
            transform: rotate(180deg);
        }

        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height .25s ease;
        }

        .sidebar-submenu.is-open {
            max-height: 220px;
        }

        .sidebar-sublink {
            display: block;
            padding: 7px 12px 7px 48px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-body);
            transition:
                background-color .28s cubic-bezier(0.4, 0, 0.2, 1),
                color .28s cubic-bezier(0.4, 0, 0.2, 1),
                transform .28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html:not(.sidebar-collapsed) .sidebar-sublink:not(.active):hover {
            transform: translateX(4px);
        }

        .sidebar-sublink:hover {
            background: rgba(5, 23, 71, .06);
        }

        .sidebar-sublink.active {
            background: var(--navy);
            color: #ffffff;
        }

        .sidebar-sublink.active:hover {
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

        .admin-data-table-wrap {
            position: relative;
            background: var(--bg-white);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .admin-data-table-wrap::before {
            content: '';
            display: block;
            width: 3.5rem;
            height: 3px;
            background: var(--blue-accent);
            border-radius: 0 0 3px 0;
        }

        .admin-data-table-wrap::after {
            content: '';
            display: block;
            height: 1px;
            background: var(--border);
        }

        .admin-table-footer {
            border-top: 1px solid var(--border);
        }

        .admin-table-pagination {
            border-top: 1px solid var(--border);
        }

        .admin-table-pagination-btn {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg-white);
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            transition: background-color .2s, border-color .2s, color .2s;
        }

        .admin-table-pagination-btn:hover:not(:disabled) {
            background: #F8FAFC;
            border-color: #CBD5E1;
            color: var(--navy);
        }

        .admin-table-pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .input-field,
        .form-input {
            border: 1.5px solid var(--border);
            border-radius: var(--radius-md);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            color: var(--navy);
        }

        .input-field:focus,
        .form-input:focus {
            border-color: var(--blue-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--navy);
            margin-bottom: 6px;
        }

        .form-section-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 12px;
        }

        .form-hint {
            font-size: 11px;
            line-height: 1.5;
            color: #94a3b8;
            margin-top: 6px;
        }

        .form-affix {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }

        .form-note {
            font-size: 11px;
            color: #94a3b8;
        }

        .form-choice {
            font-size: 13px;
            font-weight: 500;
            color: #334155;
        }

        .form-upload-text {
            font-size: 13px;
            color: #64748b;
        }

        .form-upload-hint {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .form-hint-error {
            font-size: 11px;
            line-height: 1.5;
            color: #ef4444;
            margin-top: 6px;
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

        /* Sidebar overlay untuk mobile/tablet */
        #sidebarOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(5, 23, 71, 0.5);
            z-index: 39;
            backdrop-filter: blur(2px);
        }

        #sidebarOverlay.active {
            display: block;
        }

        /* Mobile/tablet: sidebar di-hide by default */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 40;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0 !important;
            }
        }

        /* Desktop: sidebar selalu tampil */
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0) !important;
            }

            .main-wrapper {
                margin-left: var(--sidebar-width);
            }

            html.sidebar-collapsed .main-wrapper {
                margin-left: var(--sidebar-width-collapsed);
            }

            .sidebar-collapse-btn {
                display: inline-flex;
            }

            .topbar-divider {
                display: block;
            }

            #hamburgerBtn {
                display: none !important;
            }

            #sidebarOverlay {
                display: none !important;
            }
        }

        /* Tabel responsive */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Tabel: text lebih kecil di mobile */
        @media (max-width: 767px) {
            table {
                font-size: 0.75rem;
            }

            th,
            td {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
        }

        .theme-toggle-btn {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            background: transparent;
            transition: background-color .2s ease, border-color .2s ease, color .2s ease;
        }

        .theme-toggle-btn:hover {
            background: rgba(5, 23, 71, .05);
            color: var(--navy);
        }

        .theme-toggle-btn svg {
            width: 1.125rem;
            height: 1.125rem;
        }

        #notifBellBadge.is-pulsing {
            animation: notifBadgePulse 1s ease-in-out infinite;
        }

        @keyframes notifBadgePulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.35;
                transform: scale(0.9);
            }
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --bg-page: #0c111c;
            --bg-sidebar: #080d18;
            --bg-white: #151f2e;
            --text-body: #98a2b3;
            --text-muted: #667085;
            --border: rgba(255, 255, 255, 0.08);
            --navy: #f2f4f7;
            --navy-mid: #d0d5dd;
            --blue-accent: #3b6ef6;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.24);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.28);
            --shadow-lg: 0 16px 40px rgba(0, 0, 0, 0.34);
        }

        html[data-theme="dark"] .glass-topbar {
            background: #101828;
            border-bottom-color: rgba(255, 255, 255, 0.06);
        }

        html[data-theme="dark"] .glass-topbar.is-scrolled {
            background: rgba(16, 24, 40, 0.92);
            border-bottom-color: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        html[data-theme="dark"] .sidebar {
            background: var(--bg-sidebar);
            border-color: rgba(255, 255, 255, 0.05) !important;
        }

        html[data-theme="dark"] .sidebar .border-b {
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        html[data-theme="dark"] .sidebar-link,
        html[data-theme="dark"] .sidebar-sublink {
            color: #98a2b3;
        }

        html[data-theme="dark"] .sidebar-link-parent.is-expanded {
            color: #f2f4f7;
        }

        html[data-theme="dark"] .sidebar-chevron {
            color: #667085;
        }

        html[data-theme="dark"] .sidebar-link:hover,
        html[data-theme="dark"] .sidebar-sublink:hover {
            background: rgba(255, 255, 255, 0.04);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .sidebar-link.active,
        html[data-theme="dark"] .sidebar-sublink.active {
            background: var(--blue-accent);
            color: #fff;
        }

        html[data-theme="dark"] .sidebar-link.active:hover,
        html[data-theme="dark"] .sidebar-sublink.active:hover {
            background: #4f7ff7;
            color: #fff;
        }

        html[data-theme="dark"] .theme-toggle-btn {
            border-color: rgba(255, 255, 255, 0.1);
            color: #98a2b3;
        }

        html[data-theme="dark"] .theme-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #e4e7ec;
            border-color: rgba(255, 255, 255, 0.14);
        }

        html[data-theme="dark"] #mainTopbar button[data-open-notif-modal]:hover,
        html[data-theme="dark"] #hamburgerBtn:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        html[data-theme="dark"] #mainTopbar h1,
        html[data-theme="dark"] #hamburgerIcon,
        html[data-theme="dark"] #mainTopbar button[data-open-notif-modal] svg {
            color: #e4e7ec;
        }

        html[data-theme="dark"] #sidebarOverlay {
            background: rgba(2, 6, 23, 0.78);
        }

        html[data-theme="dark"] .card {
            background: var(--bg-white);
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: none;
        }

        html[data-theme="dark"] .card:hover {
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
        }

        html[data-theme="dark"] .main-wrapper .bg-white {
            background-color: var(--bg-white) !important;
        }

        html[data-theme="dark"] .main-wrapper .border-slate-100,
        html[data-theme="dark"] .main-wrapper .border-slate-200,
        html[data-theme="dark"] .main-wrapper .border-\[\#E2E8F0\] {
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        html[data-theme="dark"] .main-wrapper .text-\[\#051747\] {
            color: #f2f4f7 !important;
        }

        html[data-theme="dark"] .main-wrapper .text-slate-400,
        html[data-theme="dark"] .main-wrapper .text-slate-500,
        html[data-theme="dark"] .main-wrapper .text-slate-600 {
            color: #98a2b3 !important;
        }

        html[data-theme="dark"] .main-wrapper .bg-slate-50,
        html[data-theme="dark"] .main-wrapper .bg-\[\#F8FAFF\] {
            background-color: #1a2438 !important;
        }

        html[data-theme="dark"] .main-wrapper .hover\:bg-slate-50:hover,
        html[data-theme="dark"] .main-wrapper .hover\:bg-slate-100:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        html[data-theme="dark"] .main-wrapper table tbody tr {
            border-color: rgba(255, 255, 255, 0.05);
        }

        html[data-theme="dark"] .main-wrapper table tbody td {
            color: #d0d5dd;
        }

        html[data-theme="dark"] .input-field,
        html[data-theme="dark"] .form-input,
        html[data-theme="dark"] .entries-select {
            background: #0f1624;
            border-color: rgba(255, 255, 255, 0.1);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .form-label,
        html[data-theme="dark"] .form-section-label {
            color: #d0d5dd;
        }

        html[data-theme="dark"] .form-hint,
        html[data-theme="dark"] .form-note {
            color: #667085;
        }

        html[data-theme="dark"] .btn-primary {
            background: var(--blue-accent);
        }

        html[data-theme="dark"] .btn-primary:hover {
            background: #4f7ff7;
        }

        /* —— Modal, dropdown akun, notifikasi (di luar .main-wrapper) —— */
        html[data-theme="dark"] .notif-modal-panel,
        html[data-theme="dark"] .profil-modal-panel,
        html[data-theme="dark"] .logout-modal-panel,
        html[data-theme="dark"] .action-confirm-panel,
        html[data-theme="dark"] .katalog-confirm-panel,
        html[data-theme="dark"] .delete-field-modal-panel,
        html[data-theme="dark"] .deadline-min-panel,
        html[data-theme="dark"] .fixed.inset-0 .relative.bg-white.rounded-\[20px\] {
            background: var(--bg-white) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        html[data-theme="dark"] .fixed.inset-0 .text-\[\#051747\],
        html[data-theme="dark"] #mainTopbar .text-\[\#051747\],
        html[data-theme="dark"] #userMenuDropdown .text-\[\#051747\] {
            color: #f2f4f7 !important;
        }

        html[data-theme="dark"] .fixed.inset-0 .text-slate-400,
        html[data-theme="dark"] .fixed.inset-0 .text-slate-500,
        html[data-theme="dark"] .fixed.inset-0 .text-slate-600,
        html[data-theme="dark"] #userMenuDropdown .text-slate-400,
        html[data-theme="dark"] #userMenuDropdown .text-slate-500 {
            color: #98a2b3 !important;
        }

        html[data-theme="dark"] #userMenuDropdown {
            background: var(--bg-white) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
        }

        html[data-theme="dark"] #userMenuDropdown .border-\[\#E2E8F0\] {
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        html[data-theme="dark"] #userMenuBtn:hover,
        html[data-theme="dark"] .fixed.inset-0 button.hover\:bg-slate-50:hover,
        html[data-theme="dark"] .fixed.inset-0 button.hover\:bg-slate-100:hover {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        html[data-theme="dark"] .hover\:bg-\[\#F0F2F8\]:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        html[data-theme="dark"] .notif-item {
            background: #0f1624;
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-theme="dark"] .notif-item:hover {
            background: #1a2438;
            box-shadow: var(--shadow-md);
        }

        html[data-theme="dark"] .notif-item.unread {
            background: rgba(59, 110, 246, 0.1);
            border-left-color: var(--blue-accent);
        }

        html[data-theme="dark"] .btn-notif-mark-all {
            background: transparent;
        }

        html[data-theme="dark"] .btn-notif-mark-all:hover:not(:disabled) {
            background: var(--blue-accent);
            color: #fff;
        }

        html[data-theme="dark"] #notifEmpty .bg-slate-50 {
            background: #1a2438 !important;
        }

        html[data-theme="dark"] .btn-logout-cancel,
        html[data-theme="dark"] .btn-action-confirm-cancel,
        html[data-theme="dark"] .btn-katalog-confirm-cancel,
        html[data-theme="dark"] .btn-delete-field-cancel {
            background: #0f1624;
            border-color: rgba(255, 255, 255, 0.1);
            color: #98a2b3;
        }

        html[data-theme="dark"] .btn-logout-cancel:hover,
        html[data-theme="dark"] .btn-action-confirm-cancel:hover,
        html[data-theme="dark"] .btn-katalog-confirm-cancel:hover,
        html[data-theme="dark"] .btn-delete-field-cancel:hover {
            background: #1a2438;
            border-color: rgba(255, 255, 255, 0.14);
            color: #e4e7ec;
        }

        html[data-theme="dark"] #profilModal .bg-\[\#F8FAFF\] {
            background-color: #1a2438 !important;
        }

        html[data-theme="dark"] #profilModal .border-\[\#E2E8F0\] {
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        html[data-theme="dark"] #profilModal input:not([disabled]),
        html[data-theme="dark"] #profilModal textarea,
        html[data-theme="dark"] .fixed.inset-0 textarea,
        html[data-theme="dark"] .fixed.inset-0 input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([disabled]) {
            background: #0f1624 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #e4e7ec !important;
        }

        html[data-theme="dark"] #profilModal input[disabled] {
            background: #1a2438 !important;
            color: #667085 !important;
        }

        /* —— Search bar & filter —— */
        html[data-theme="dark"] .search-control,
        html[data-theme="dark"] .filter-select {
            background-color: #0f1624;
            border-color: rgba(255, 255, 255, 0.1);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .search-control:focus-within,
        html[data-theme="dark"] .filter-select:focus {
            border-color: var(--blue-accent);
            box-shadow: 0 0 0 3px rgba(59, 110, 246, 0.15);
        }

        html[data-theme="dark"] .search-control input {
            color: #e4e7ec;
        }

        html[data-theme="dark"] .search-control input::placeholder {
            color: #667085;
        }

        html[data-theme="dark"] #catalogSearchBar {
            background: #0f1624 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            box-shadow: none;
        }

        html[data-theme="dark"] #catalogSearchBar.focused {
            border-color: var(--blue-accent) !important;
            box-shadow: 0 0 0 3px rgba(59, 110, 246, 0.15) !important;
        }

        html[data-theme="dark"] #catalogSearchBar input {
            color: #e4e7ec;
        }

        html[data-theme="dark"] #catalogSearchBar input::placeholder {
            color: #667085;
        }

        /* —— Kartu katalog —— */
        html[data-theme="dark"] .katalog-card {
            background: var(--bg-white);
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: none;
        }

        html[data-theme="dark"] .katalog-card:hover {
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
        }

        html[data-theme="dark"] .katalog-card-media,
        html[data-theme="dark"] .katalog-card-placeholder {
            background: #0f1624;
        }

        html[data-theme="dark"] .katalog-min-order-tag {
            background: #1a2438;
            color: #98a2b3;
        }

        html[data-theme="dark"] .katalog-card a[href*="/order/create/"] {
            background: #838df4 !important;
            border-color: #838df4 !important;
            color: #fff !important;
        }

        html[data-theme="dark"] .katalog-card a[href*="/order/create/"]:hover {
            background: #949cf7 !important;
            border-color: #949cf7 !important;
            color: #fff !important;
        }

        /* —— Form spesifikasi khusus (order/create) —— */
        html[data-theme="dark"] #spesifikasiKhususWrapper .bg-indigo-50,
        html[data-theme="dark"] #customNote.bg-indigo-50 {
            background-color: #1a2438 !important;
            border-color: rgba(131, 141, 244, 0.28) !important;
        }

        html[data-theme="dark"] #spesifikasiKhususWrapper .text-indigo-900 {
            color: #c7d2fe !important;
        }

        html[data-theme="dark"] #customNote.text-indigo-800 {
            color: #c7d2fe !important;
        }

        html[data-theme="dark"] #customNote .text-indigo-600 {
            color: #838df4 !important;
        }

        html[data-theme="dark"] .order-eav-spec-card {
            background-color: #1a2438 !important;
            border-color: rgba(131, 141, 244, 0.28) !important;
            box-shadow: none !important;
        }

        html[data-theme="dark"] .order-eav-spec-card .order-eav-spec-title {
            color: #c7d2fe !important;
            border-color: rgba(131, 141, 244, 0.22) !important;
        }

        html[data-theme="dark"] .order-eav-spec-card .text-slate-500 {
            color: #98a2b3 !important;
        }

        html[data-theme="dark"] .order-eav-spec-card .text-slate-700,
        html[data-theme="dark"] .order-eav-spec-card ol {
            color: #e4e7ec !important;
        }

        html[data-theme="dark"] .order-eav-spec-card .text-slate-400 {
            color: #667085 !important;
        }

        html[data-theme="dark"] .main-wrapper .bg-blue-50 {
            background-color: #1a2438 !important;
        }

        html[data-theme="dark"] .main-wrapper .bg-indigo-100 {
            background-color: rgba(131, 141, 244, 0.15) !important;
        }

        html[data-theme="dark"] .main-wrapper .border-indigo-200 {
            border-color: rgba(131, 141, 244, 0.28) !important;
        }

        html[data-theme="dark"] .peer:checked ~ .peer-checked\:bg-blue-50 {
            background-color: rgba(131, 141, 244, 0.12) !important;
        }

        html[data-theme="dark"] .peer:checked ~ .peer-checked\:border-\[\#051747\] {
            border-color: #838df4 !important;
        }

        html[data-theme="dark"] .form-input.bg-slate-100 {
            background: #0f1624 !important;
            color: #667085 !important;
        }

        html[data-theme="dark"] #uploadZone {
            background: #0f1624;
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        html[data-theme="dark"] #uploadZone:hover {
            border-color: #838df4 !important;
        }

        html[data-theme="dark"] .form-input::file-selector-button {
            background: #838df4;
            color: #fff;
        }

        html[data-theme="dark"] .form-input::file-selector-button:hover {
            background: #949cf7;
        }

        html[data-theme="dark"] .catalog-tab.bg-white {
            background-color: #151f2e !important;
            color: #98a2b3 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }

        html[data-theme="dark"] #catalogImageLightboxClose {
            background: rgba(21, 31, 46, 0.95);
            color: #e4e7ec;
        }

        html[data-theme="dark"] #catalogImageLightboxCaption {
            background: rgba(21, 31, 46, 0.92);
            color: #e4e7ec;
        }

        /* —— Tabel: hover baris —— */
        html[data-theme="dark"] .main-wrapper tr.data-table-row:hover,
        html[data-theme="dark"] .main-wrapper tr.hover\:bg-\[\#F8FAFF\]:hover,
        html[data-theme="dark"] .main-wrapper tbody tr.border-b:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }

        html[data-theme="dark"] .main-wrapper table thead tr[style*="var(--navy)"] {
            background: #1a2438 !important;
            color: #e4e7ec !important;
        }

        /* —— Tombol ekspor laporan —— */
        html[data-theme="dark"] .btn-laporan-export {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.12);
            color: #98a2b3;
        }

        html[data-theme="dark"] .btn-laporan-export:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.18);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .btn-laporan-filter {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.12);
            color: #98a2b3;
        }

        html[data-theme="dark"] .btn-laporan-filter:hover,
        html[data-theme="dark"] .btn-laporan-filter.is-open {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.18);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .laporan-admin-filter-panel {
            background: #151f2e;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        html[data-theme="dark"] .laporan-admin-filter-panel .filter-field label {
            color: #98a2b3;
        }

        html[data-theme="dark"] .laporan-admin-filter-panel .filter-field input,
        html[data-theme="dark"] .laporan-admin-filter-panel .filter-field select {
            background: #0f1624;
            border-color: rgba(255, 255, 255, 0.1);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .list-pemesanan-date-input {
            background: #0f1624;
            border-color: rgba(255, 255, 255, 0.1);
            color: #e4e7ec;
            color-scheme: dark;
        }

        html[data-theme="dark"] .list-pemesanan-date-input:focus {
            border-color: var(--blue-accent);
            box-shadow: 0 0 0 3px rgba(59, 110, 246, 0.15);
        }

        html[data-theme="dark"] .list-pemesanan-date-label {
            color: #98a2b3;
        }

        html[data-theme="dark"] .list-pemesanan-date-sep {
            color: #667085;
        }

        html[data-theme="dark"] .admin-data-table-wrap {
            background: var(--bg-white);
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: none;
        }

        html[data-theme="dark"] .admin-data-table-wrap::before {
            background: #3b6ef6;
        }

        html[data-theme="dark"] .admin-data-table-wrap::after {
            background: rgba(255, 255, 255, 0.08);
        }

        html[data-theme="dark"] .admin-table-footer,
        html[data-theme="dark"] .admin-table-pagination {
            border-color: rgba(255, 255, 255, 0.06);
        }

        html[data-theme="dark"] .admin-table-footer .text-slate-600,
        html[data-theme="dark"] .admin-table-footer .text-slate-500,
        html[data-theme="dark"] .admin-table-pagination .text-slate-500 {
            color: #98a2b3 !important;
        }

        html[data-theme="dark"] .admin-table-pagination-btn {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.12);
            color: #98a2b3;
        }

        html[data-theme="dark"] .admin-table-pagination-btn:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.18);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .sidebar-collapse-btn {
            background: transparent;
            border-color: rgba(255, 255, 255, 0.12);
            color: #98a2b3;
        }

        html[data-theme="dark"] .sidebar-collapse-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.18);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .topbar-divider {
            background: rgba(255, 255, 255, 0.1);
        }

        /* —— Menu aksi tabel —— */
        html[data-theme="dark"] .action-menu-btn {
            background: #0f1624;
            border-color: rgba(255, 255, 255, 0.1);
            color: #98a2b3;
        }

        html[data-theme="dark"] .action-menu-btn:hover,
        html[data-theme="dark"] .action-menu-btn.is-open {
            background: rgba(59, 110, 246, 0.12);
            border-color: var(--blue-accent);
            color: #e4e7ec;
        }

        html[data-theme="dark"] .action-dropdown {
            background: #151f2e;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        html[data-theme="dark"] .action-dropdown a,
        html[data-theme="dark"] .action-dropdown button {
            color: #98a2b3;
        }

        html[data-theme="dark"] .action-dropdown a:hover,
        html[data-theme="dark"] .action-dropdown button:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #e4e7ec;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body class="min-h-screen" <?= ($openProfilModal || ($role === 'pelanggan' && $openProfilFromQuery)) ? ' data-open-profil="1"' : '' ?>>

    <div id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside id="mainSidebar" class="sidebar fixed left-0 top-0 bottom-0 z-40 flex flex-col border-r" style="border-color:var(--border);">
        <div class="sidebar-brand px-6 py-6 border-b" style="border-color:var(--border);">
            <a href="<?= site_url('dashboard') ?>" class="block" title="Z'Plack SIMENAK">
                <div class="sidebar-brand-detail">
                    <span class="text-xl font-extrabold tracking-tight" style="color:var(--navy);">Z'PLACK</span>
                    <span class="block text-sm font-bold tracking-widest" style="color:#6b9fff;">SIMENAK</span>
                </div>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <?php foreach ($menuItems as $item): ?>
                <?php if (!empty($item['children'])): ?>
                    <?php
                    $groupActive = $isMenuGroupActive($item);
                    $submenuOpen = $groupActive;
                    ?>
                    <div class="sidebar-group">
                        <button
                            type="button"
                            class="sidebar-link sidebar-link-parent flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium w-full <?= $submenuOpen ? 'is-expanded' : '' ?>"
                            data-sidebar-toggle
                            aria-expanded="<?= $submenuOpen ? 'true' : 'false' ?>"
                            aria-controls="sidebar-submenu-<?= esc(md5((string) ($item['label'] ?? 'group'))) ?>">
                            <?= $iconSvg($item['icon'] ?? 'home') ?>
                            <span class="sidebar-label truncate"><?= esc($item['label']) ?></span>
                            <svg class="sidebar-chevron w-4 h-4 text-slate-400 <?= $submenuOpen ? 'is-open' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            id="sidebar-submenu-<?= esc(md5((string) ($item['label'] ?? 'group'))) ?>"
                            class="sidebar-submenu <?= $submenuOpen ? 'is-open' : '' ?>">
                            <?php foreach ($item['children'] as $child): ?>
                                <?php $childActive = $isMenuActive((string) ($child['url'] ?? '')); ?>
                                <a href="<?= site_url($child['url']) ?>"
                                    class="sidebar-sublink <?= $childActive ? 'active' : '' ?>">
                                    <?= esc($child['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php $active = $isMenuActive($item['url']); ?>
                    <a href="<?= site_url($item['url']) ?>"
                        class="sidebar-link flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium <?= $active ? 'active' : '' ?>">
                        <?= $iconSvg($item['icon']) ?>
                        <span class="sidebar-label"><?= esc($item['label']) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper min-h-screen flex flex-col">

        <!-- Topbar -->
        <header id="mainTopbar" class="glass-topbar sticky top-0 z-30 h-14 lg:h-16 flex items-center justify-between px-4 lg:px-6">
            <div class="flex items-center gap-3 min-w-0">
                <!-- Hamburger: mobile/tablet -->
                <button id="hamburgerBtn"
                    type="button"
                    class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition-colors shrink-0"
                    aria-label="Buka menu">
                    <svg id="hamburgerIcon" class="w-6 h-6 text-[#051747]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <!-- Sidebar collapse: desktop -->
                <button id="sidebarCollapseBtn"
                    type="button"
                    class="sidebar-collapse-btn"
                    aria-label="Ciutkan sidebar"
                    aria-expanded="true"
                    title="Ciutkan sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 7h11"/>
                        <path stroke-linecap="round" d="M4 12h16"/>
                        <path stroke-linecap="round" d="M4 17h8"/>
                    </svg>
                </button>
                <span class="topbar-divider" aria-hidden="true"></span>
                <h1 class="text-base lg:text-lg font-bold text-[#051747] truncate max-w-[200px] sm:max-w-none">
                    <?= esc($this->renderSection('page_title') ?: 'Beranda') ?>
                </h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button"
                    id="themeToggleBtn"
                    class="theme-toggle-btn"
                    title="Ubah tema tampilan"
                    aria-label="Ubah tema tampilan">
                    <svg id="themeIconSun" class="hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M3 12h2m14 0h2M4.22 19.78l1.42-1.42M17.36 6.64l1.42-1.42M12 8a4 4 0 100 8 4 4 0 000-8z"/>
                    </svg>
                    <svg id="themeIconMoon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z"/>
                    </svg>
                </button>

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
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full px-1 <?= $unreadCount > 0 ? 'is-pulsing' : 'hidden' ?>">
                        <?= $unreadCount > 99 ? '99+' : esc((string) $unreadCount) ?>
                    </span>
                </button>

                <!-- User Account Menu -->
                <?php if ($idUser > 0 && session()->get('isLoggedIn')): ?>
                    <?php
                    $isKerjasamaPelanggan = false;
                    if ($role === 'pelanggan' && is_array($profilData) && isset($profilData['pelanggan'])) {
                        helper('notification');
                        $isKerjasamaPelanggan = pelangganIsKerjasamaPerusahaan($profilData['pelanggan']);
                    }
                    ?>
                    <?= view('partials/user_account_menu', [
                        'namaUser'             => $namaUser,
                        'role'                 => $role,
                        'userEmail'            => $userEmail,
                        'showProfilLink'       => $role === 'pelanggan' && $profilData !== null,
                        'isKerjasamaPelanggan' => $isKerjasamaPelanggan,
                    ]) ?>
                <?php endif; ?>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-4 lg:p-6" style="background:var(--bg-page);">

            <?php
            $bannerRoles = ['admin', 'keuangan', 'produksi', 'owner', 'pelanggan'];
            if (in_array($role, $bannerRoles, true)):
                $bannerTitle = trim((string) $this->renderSection('banner_title', true));
                $bannerSubtitle = trim((string) $this->renderSection('banner_subtitle', true));
                $bannerSubtitleAllowHtml = trim((string) $this->renderSection('banner_subtitle_allow_html', true)) === '1';
                $pageTitleBreadcrumb = trim((string) ($this->renderSection('page_title', true) ?: ''));
                if ($bannerTitle === '') {
                    $bannerTitle = $pageTitleBreadcrumb !== '' ? $pageTitleBreadcrumb : 'Beranda';
                }
                echo view('partials/page_banner', [
                    'title'               => $bannerTitle,
                    'subtitle'            => $bannerSubtitle,
                    'subtitleAllowHtml'   => $bannerSubtitleAllowHtml,
                    'breadcrumb'          => $pageTitleBreadcrumb,
                    'image'               => $role === 'pelanggan' ? 'orders.png' : 'cs.png',
                ]);
            endif;
            ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

    <?= view('partials/flash_toast') ?>
    <?= view('partials/notification_modal') ?>
    <?= view('partials/logout_modal') ?>
    <?= view('partials/action_confirm_modal') ?>
    <?php if ($role === 'pelanggan' && $profilData !== null): ?>
        <?= view('partials/profil_modal', ['profilData' => $profilData]) ?>
    <?php endif; ?>
    <?php if ($idUser > 0 && session()->get('isLoggedIn')): ?>
        <?= view('partials/user_account_menu_scripts') ?>
    <?php endif; ?>
    <?= $this->renderSection('scripts') ?>
    <script>
        (function() {
            const themeBtn = document.getElementById('themeToggleBtn');
            const sunIcon = document.getElementById('themeIconSun');
            const moonIcon = document.getElementById('themeIconMoon');
            const storageKey = 'simenak-dashboard-theme';

            const isDarkTheme = () => document.documentElement.getAttribute('data-theme') === 'dark';

            const syncThemeToggleIcon = () => {
                if (!sunIcon || !moonIcon) {
                    return;
                }

                const dark = isDarkTheme();
                sunIcon.classList.toggle('hidden', !dark);
                moonIcon.classList.toggle('hidden', dark);
            };

            const applyTheme = (theme) => {
                if (theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }

                try {
                    localStorage.setItem(storageKey, theme === 'dark' ? 'dark' : 'light');
                } catch (e) {}

                syncThemeToggleIcon();
            };

            syncThemeToggleIcon();

            themeBtn?.addEventListener('click', () => {
                applyTheme(isDarkTheme() ? 'light' : 'dark');
            });
        })();
    </script>
    <script>
        (function() {
            const topbar = document.getElementById('mainTopbar');
            if (!topbar) return;

            const onScroll = () => {
                topbar.classList.toggle('is-scrolled', window.scrollY > 8);
            };

            window.addEventListener('scroll', onScroll, {
                passive: true
            });
            onScroll();
        })();
    </script>
    <script>
        (function() {
            const sidebar = document.getElementById('mainSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const hamburger = document.getElementById('hamburgerBtn');
            if (!sidebar || !overlay || !hamburger) return;

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            hamburger.addEventListener('click', function() {
                sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
            });

            overlay.addEventListener('click', closeSidebar);

            sidebar.querySelectorAll('a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1024) closeSidebar();
                });
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    closeSidebar();
                }
            });

            sidebar.querySelectorAll('[data-sidebar-toggle]').forEach(function(toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const submenu = toggleBtn.nextElementSibling;
                    if (!submenu) return;

                    const isOpen = submenu.classList.toggle('is-open');
                    toggleBtn.classList.toggle('is-expanded', isOpen);
                    toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                    const chevron = toggleBtn.querySelector('.sidebar-chevron');
                    if (chevron) {
                        chevron.classList.toggle('is-open', isOpen);
                    }
                });
            });
        })();
    </script>
    <script>
        (function() {
            const collapseBtn = document.getElementById('sidebarCollapseBtn');
            const storageKey = 'simenak-sidebar-collapsed';

            const isCollapsed = () => document.documentElement.classList.contains('sidebar-collapsed');

            const applyCollapsed = (collapsed) => {
                document.documentElement.classList.toggle('sidebar-collapsed', collapsed);

                if (collapseBtn) {
                    collapseBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    collapseBtn.setAttribute('aria-label', collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
                    collapseBtn.setAttribute('title', collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
                }

                try {
                    localStorage.setItem(storageKey, collapsed ? '1' : '0');
                } catch (e) {}
            };

            collapseBtn?.addEventListener('click', () => {
                applyCollapsed(!isCollapsed());
            });

            if (collapseBtn) {
                applyCollapsed(isCollapsed());
            }
        })();
    </script>
</body>

</html>