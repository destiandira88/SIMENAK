<?php

/**
 * Catat aktivitas internal sistem (audit trail sederhana).
 */
function logActivity(string $aksi, string $modul, string $keterangan): void
{
    $session = session();
    $idUser  = (int) $session->get('id_user');

    if ($idUser <= 0) {
        return;
    }

    $keterangan = trim($keterangan);
    if ($keterangan === '') {
        return;
    }

    try {
        $db = db_connect();
        $db->table('activity_logs')->insert([
            'id_user'    => $idUser,
            'nama_user'  => (string) $session->get('nama'),
            'role'       => (string) $session->get('role'),
            'aksi'       => $aksi,
            'modul'      => $modul,
            'keterangan' => $keterangan,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    } catch (\Throwable $e) {
        log_message('error', '[logActivity] {msg}', ['msg' => $e->getMessage()]);
    }
}

function formatLogRupiah(int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * @return array<string, string>
 */
function activityLogModulOptions(): array
{
    return [
        ''           => 'Semua Modul',
        'katalog'    => 'Katalog',
        'kerjasama'  => 'Kerja Sama Perusahaan',
        'pembayaran' => 'Pembayaran',
        'pemesanan'  => 'Pemesanan',
        'pengiriman' => 'Pengiriman',
        'produksi'   => 'Produksi',
    ];
}

/**
 * @return array<string, string>
 */
function activityLogAksiLabels(): array
{
    return [
        'tambah'    => 'Tambah',
        'ubah'      => 'Ubah',
        'hapus'     => 'Hapus',
        'verifikasi'=> 'Verifikasi',
        'tolak'     => 'Tolak',
    ];
}

function getActivityLogAksiBadgeClass(string $aksi): string
{
    return match ($aksi) {
        'tambah'     => 'bg-blue-100 text-blue-800',
        'ubah'       => 'bg-amber-100 text-amber-800',
        'hapus'      => 'bg-red-100 text-red-800',
        'verifikasi' => 'bg-emerald-100 text-emerald-800',
        'tolak'      => 'bg-red-100 text-red-800',
        default      => 'bg-slate-100 text-slate-700',
    };
}

function getActivityLogRoleLabel(string $role): string
{
    return match ($role) {
        'admin'     => 'Admin',
        'keuangan'  => 'Keuangan',
        'produksi'  => 'Produksi',
        'owner'     => 'Owner',
        'pelanggan' => 'Pelanggan',
        default     => ucfirst($role),
    };
}
