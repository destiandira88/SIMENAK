<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ─── Public ───────────────────────────────────────────────────────────────────
// $routes->get('/', 'Home::index');
$routes->get('/', 'LandingController::index');

$routes->get('login', 'AuthController::login');
$routes->get('portal', 'AuthController::login');
$routes->post('login', 'AuthController::loginProcess');
$routes->post('login-ajax', 'AuthController::loginAjax');
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::registerProcess');
$routes->post('register-ajax', 'AuthController::registerAjax');
$routes->get('lupa-sandi', 'AuthController::showForgotPasswordForm');
$routes->post('lupa-sandi', 'AuthController::processForgotPassword');
$routes->get('atur-ulang-sandi', 'AuthController::showResetPasswordForm');
$routes->post('atur-ulang-sandi', 'AuthController::processResetPassword');
// Alias lama (link email / bookmark sebelumnya)
$routes->post('forgot-password', 'AuthController::processForgotPassword');
$routes->get('reset-password', 'AuthController::showResetPasswordForm');
$routes->post('reset-password', 'AuthController::processResetPassword');
$routes->get('check-session', 'AuthController::checkSession');
$routes->get('csrf-sync', 'AuthController::csrfSync');
$routes->get('logout', 'AuthController::logout');
$routes->get('buat-password-baru', 'AuthController::showBuatPasswordBaru');
$routes->post('buat-password-baru', 'AuthController::processBuatPasswordBaru');
$routes->get('auth/google', 'AuthController::redirectToGoogle');
$routes->get('auth/google/callback', 'AuthController::googleCallback');

$routes->get('tracking/(:segment)', 'TrackingController::show/$1');

// ─── Protected (auth required) ───────────────────────────────────────────────
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('admin/dashboard', 'DashboardController::index');
    $routes->get('keuangan/dashboard', 'DashboardController::index');
    $routes->get('produksi/dashboard', 'DashboardController::index');
    $routes->get('owner/dashboard', 'DashboardController::index');
    $routes->get('pelanggan/dashboard', 'DashboardController::index');
    $routes->post('logout', 'AuthController::logout');
    $routes->get('notifikasi', 'DashboardController::notifikasi');
    $routes->get('notifikasi/list', 'NotificationController::list');
    $routes->post('notifikasi/read-all', 'NotificationController::markAllRead');
    $routes->post('notifikasi/read/(:num)', 'NotificationController::markRead/$1');

    // Katalog publik dan pelanggan
    $routes->get('katalog', 'KatalogController::list');
    $routes->get('order/create/(:num)', 'OrderController::create/$1');

    // Order (pelanggan)
    $routes->get('order', 'OrderController::index');
    $routes->post('order/simpan', 'OrderController::store');
    $routes->get('order/detail/(:segment)', 'OrderController::detail/$1');
    $routes->post('order/(:segment)/upload-dp', 'PaymentController::uploadDp/$1');
    $routes->post('order/(:segment)/upload-pelunasan', 'PaymentController::uploadPelunasan/$1');
    $routes->get('order/(:segment)/nota-tagihan', 'PaymentController::notaTagihan/$1');
    $routes->get('order/(:segment)/bukti-pembayaran-pelunasan', 'PaymentController::buktiPembayaranPelunasan/$1');
    $routes->post('order/batalkan', 'OrderController::batalkan');
    $routes->post('custom-order/setuju', 'CustomOrderController::setuju');
    $routes->post('custom-order/tolak', 'CustomOrderController::tolak');
    $routes->get('revisi/history/(:segment)', 'RevisiController::approvalHistory/$1');

    // Katalog admin (protected)
    $routes->get('katalog/kelola', 'KatalogController::index');
    $routes->get('katalog/detail/(:num)', 'KatalogController::detail/$1');
    $routes->get('katalog/tambah', 'KatalogController::create');
    $routes->post('katalog/simpan', 'KatalogController::store');
    $routes->get('katalog/edit/(:num)', 'KatalogController::edit/$1');
    $routes->post('katalog/update/(:num)', 'KatalogController::update/$1');
    $routes->post('katalog/toggle/(:num)', 'KatalogController::toggleStatus/$1');
    $routes->post('katalog/hapus/(:num)', 'KatalogController::delete/$1');

    // Form templates (admin)
    $routes->get('form-template/(:num)', 'FormTemplateController::index/$1');
    $routes->post('form-template/simpan/(:num)', 'FormTemplateController::store/$1');
    $routes->post('form-template/update/(:num)', 'FormTemplateController::update/$1');
    $routes->post('form-template/hapus/(:num)', 'FormTemplateController::delete/$1');

    // ─── Pelanggan ─────────────────────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:pelanggan'], static function ($routes) {
        $routes->get('pesanan-saya', 'OrderController::redirectPesananSaya');
        $routes->get('pesanan-saya/(:segment)', 'OrderController::redirectPesananSayaDetail/$1');
        $routes->get('profil', 'ProfilController::index');
        $routes->post('profil', 'ProfilController::update');
        $routes->post('profil/ubah-password', 'ProfilController::ubahPassword');
        $routes->post('revisi/acc', 'RevisiController::acc');
        $routes->post('revisi/ajukan', 'RevisiController::ajukan');
        $routes->post('pesanan/konfirmasi-diterima', 'PengirimanController::konfirmasiDiterimaPelanggan');
    });

    // ─── Admin + Owner: lihat Manajemen Pengguna ───────────────────────────────
    $routes->group('', ['filter' => 'role:admin,owner'], static function ($routes) {
        $routes->get('list-pemesanan', 'OrderController::listPemesanan');
        $routes->get('list-pemesanan/(:num)', 'OrderController::detailById/$1');
        $routes->get('pengguna', 'ProfilController::pengguna');
        $routes->post('pengguna/simpan', 'ProfilController::simpanPengguna');
    });

    // ─── Admin only: kelola pelanggan (Owner view-only di controller) ──────────
    $routes->group('', ['filter' => 'role:admin'], static function ($routes) {
        $routes->post('pengguna/pelanggan/update/(:num)', 'ProfilController::updatePelanggan/$1');
        $routes->post('pengguna/pelanggan/(:num)/toggle-status', 'ProfilController::toggleStatusPelanggan/$1');
        $routes->post('pengguna/(:num)/tetapkan-kerjasama', 'ProfilController::tetapkanKerjasamaPerusahaan/$1');
        $routes->post('pengguna/(:num)/cabut-kerjasama', 'ProfilController::cabutKerjasamaPerusahaan/$1');
    });

    // ─── Owner only: kelola staff internal (Admin read-only di controller) ─────
    $routes->group('', ['filter' => 'role:owner'], static function ($routes) {
        $routes->post('pengguna/staff/update/(:num)', 'ProfilController::updateStaffInternal/$1');
        $routes->post('pengguna/(:num)/toggle-status', 'ProfilController::toggleStatusStaff/$1');
    });

    // ─── Admin + Owner: lihat pengiriman (Owner read-only di controller/view) ──
    $routes->group('', ['filter' => 'role:admin,owner'], static function ($routes) {
        $routes->get('pengiriman', 'PengirimanController::index');
    });

    // ─── Admin ─────────────────────────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:admin'], static function ($routes) {
        $routes->post('list-pemesanan/(:num)/status', 'OrderController::updateStatus/$1');
        $routes->get('pesanan-custom', 'CustomOrderController::redirectToList');
        $routes->get('custom-order', 'CustomOrderController::redirectToList');
        $routes->post('list-pemesanan/set-harga', 'CustomOrderController::setHarga');
        $routes->post('custom-order/set-harga', 'CustomOrderController::setHarga');
        $routes->post('pengiriman/(:num)', 'PengirimanController::proses/$1');
        $routes->post('pesanan/konfirmasi-diterima-manual', 'PengirimanController::konfirmasiDiterimaManualAdmin');
    });

    // ─── Keuangan + Owner (baca riwayat pembayaran) ───────────────────────────
    $routes->group('', ['filter' => 'role:keuangan,owner'], static function ($routes) {
        $routes->get('riwayat-pembayaran', 'PaymentController::riwayat');
        $routes->get('laporan-keuangan', 'LaporanController::keuanganIndex');
        $routes->get('laporan-keuangan/export', 'LaporanController::keuanganExport');
        $routes->get('laporan-keuangan/export-pdf', 'LaporanController::keuanganExportPdf');
    });

    // ─── Keuangan ──────────────────────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:keuangan'], static function ($routes) {
        $routes->get('verifikasi-dp', 'PaymentController::verifikasiDp');
        $routes->post('verifikasi-dp/(:num)/acc', 'PaymentController::accDp/$1');
        $routes->post('verifikasi-dp/(:num)/tolak', 'PaymentController::tolakDp/$1');
        $routes->get('verifikasi-pelunasan', 'PaymentController::verifikasiPelunasan');
        $routes->post('verifikasi-pelunasan/(:num)/acc', 'PaymentController::accPelunasan/$1');
        $routes->post('verifikasi-pelunasan/(:num)/tolak', 'PaymentController::tolakPelunasan/$1');
    });

    // ─── Produksi + Owner (baca manajemen desain) ─────────────────────────────
    $routes->group('', ['filter' => 'role:produksi,owner'], static function ($routes) {
        $routes->get('manajemen-desain', 'RevisiController::manajemenDesain');
        $routes->get('manajemen-desain/(:num)', 'RevisiController::detail/$1');
    });

    // ─── Produksi ──────────────────────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:produksi'], static function ($routes) {
        $routes->get('antrian-desain', 'RevisiController::antrianDesain');
        $routes->post('manajemen-desain/(:num)/upload', 'RevisiController::upload/$1');
        $routes->post('produksi/update-status', 'RevisiController::updateStatusProduksi');
    });

    // ─── Laporan Owner ─────────────────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:owner'], static function ($routes) {
        $routes->get('riwayat-aktivitas', 'ActivityLogController::index');
        $routes->get('laporan', 'LaporanController::index');
        $routes->get('laporan/export', 'LaporanController::export');
        $routes->get('laporan/export-pdf', 'LaporanController::exportPdf');
        $routes->get('laporan-produksi', 'LaporanController::produksiIndex');
        $routes->get('laporan-produksi/export', 'LaporanController::produksiExport');
        $routes->get('laporan-produksi/export-pdf', 'LaporanController::produksiExportPdf');
    });

    // ─── Laporan Admin (admin + owner baca) ────────────────────────────────────
    $routes->group('', ['filter' => 'role:admin,owner'], static function ($routes) {
        $routes->get('laporan-admin', 'LaporanController::adminIndex');
        $routes->get('laporan-admin/export', 'LaporanController::adminExport');
        $routes->get('laporan-admin/export-pdf', 'LaporanController::adminExportPdf');
    });
});
