<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ─── Public ───────────────────────────────────────────────────────────────────
// $routes->get('/', 'Home::index');
$routes->get('/', 'LandingController::index');

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::loginProcess');
$routes->post('login-ajax', 'AuthController::loginAjax');
$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::registerProcess');
$routes->post('register-ajax', 'AuthController::registerAjax');
$routes->get('check-session', 'AuthController::checkSession');
$routes->get('logout', 'AuthController::logout');

$routes->get('tracking/(:segment)', 'TrackingController::show/$1');

// ─── Protected (auth required) ───────────────────────────────────────────────
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    $routes->get('dashboard', 'DashboardController::index');
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
    $routes->get('order/buat/(:num)', 'OrderController::create/$1');
    $routes->post('order/simpan', 'OrderController::store');
    $routes->get('order/detail/(:segment)', 'OrderController::detail/$1');
    $routes->post('order/(:segment)/upload-dp', 'PaymentController::uploadDp/$1');
    $routes->post('order/batalkan', 'OrderController::batalkan');
    $routes->post('custom-order/setuju', 'CustomOrderController::setuju');
    $routes->post('custom-order/tolak', 'CustomOrderController::tolak');

    // Katalog admin (protected)
    $routes->get('katalog/kelola', 'KatalogController::index');
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
        $routes->get('pesanan/buat/(:num)', 'OrderController::create/$1');
        $routes->post('pesanan/buat/(:num)', 'OrderController::store/$1');
        $routes->get('profil', 'ProfilController::index');
        $routes->post('profil', 'ProfilController::update');
        $routes->post('profil/verifikasi-perusahaan', 'ProfilController::verifikasiPerusahaan');
    });

    // ─── Admin ─────────────────────────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:admin'], static function ($routes) {
        $routes->get('list-pemesanan', 'OrderController::listPemesanan');
        $routes->get('list-pemesanan/(:num)', 'OrderController::detailById/$1');
        $routes->post('list-pemesanan/(:num)/status', 'OrderController::updateStatus/$1');
        $routes->post('pesanan/delete/(:segment)', 'OrderController::delete/$1');
        $routes->get('pengguna', 'ProfilController::pengguna');
        $routes->get('verifikasi-perusahaan', 'ProfilController::verifikasiPerusahaanList');
        $routes->post('verifikasi-perusahaan/(:num)', 'ProfilController::verifikasiPerusahaanProses/$1');
        $routes->get('pesanan-custom', 'CustomOrderController::redirectToList');
        $routes->get('custom-order', 'CustomOrderController::redirectToList');
        $routes->post('list-pemesanan/set-harga', 'CustomOrderController::setHarga');
        $routes->post('custom-order/set-harga', 'CustomOrderController::setHarga');
        $routes->get('pengiriman', 'PengirimanController::index');
        $routes->post('pengiriman/(:num)', 'PengirimanController::proses/$1');
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

    // ─── Produksi ──────────────────────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:produksi'], static function ($routes) {
        $routes->get('antrian-desain', 'RevisiController::antrianDesain');
        $routes->get('manajemen-desain', 'RevisiController::manajemenDesain');
        $routes->get('manajemen-desain/(:num)', 'RevisiController::detail/$1');
        $routes->post('manajemen-desain/(:num)/upload', 'RevisiController::upload/$1');
    });

    // ─── Laporan (admin + owner) ───────────────────────────────────────────────
    $routes->group('', ['filter' => 'role:admin,owner'], static function ($routes) {
        $routes->get('laporan', 'LaporanController::index');
        $routes->get('laporan/export', 'LaporanController::export');
    });
});
