<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

/**
 * Data uji deterministik untuk Laporan Produksi.
 */
class ProduksiReportSeeder extends Seeder
{
    public function run(): void
    {
        $db = $this->db;

        $db->table('users')->insertBatch([
            [
                'id_user'    => 1,
                'nama'       => 'Owner Test',
                'email'      => 'owner@test.local',
                'password'   => 'hash',
                'role'       => 'owner',
                'created_at' => Time::now()->toDateTimeString(),
            ],
            [
                'id_user'    => 2,
                'nama'       => 'Admin Test',
                'email'      => 'admin@test.local',
                'password'   => 'hash',
                'role'       => 'admin',
                'created_at' => Time::now()->toDateTimeString(),
            ],
            [
                'id_user'    => 5,
                'nama'       => 'Pelanggan Test',
                'email'      => 'pelanggan@test.local',
                'password'   => 'hash',
                'role'       => 'pelanggan',
                'created_at' => Time::now()->toDateTimeString(),
            ],
        ]);

        $db->table('pelanggan')->insert([
            'id_pelanggan' => 1,
            'id_user'      => 5,
            'jenis'        => 'perseorangan',
        ]);

        $db->table('katalog')->insertBatch([
            [
                'id_katalog'           => 1,
                'nama_produk'          => 'Kartu Nama',
                'kategori'             => 'cetak_digital',
                'harga_dasar'          => 30000,
                'kuota_revisi_default' => 2,
                'min_order'            => 100,
                'satuan'               => 'pcs',
                'is_active'            => 1,
            ],
            [
                'id_katalog'           => 2,
                'nama_produk'          => 'Kalender Dinding',
                'kategori'             => 'cetak_offset',
                'harga_dasar'          => 200000,
                'kuota_revisi_default' => 2,
                'min_order'            => 1,
                'satuan'               => 'pcs',
                'is_active'            => 1,
            ],
        ]);

        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $nextWeek   = date('Y-m-d', strtotime('+7 days'));
        $inPeriod   = date('Y-m-d 10:00:00');
        $outPeriod  = date('Y-m-d 10:00:00', strtotime('-120 days'));
        $now        = Time::now()->toDateTimeString();

        $db->table('orders')->insertBatch([
            [
                'id_order'          => 1,
                'kode_order'        => 'ORD-TEST-001',
                'id_pelanggan'      => 1,
                'id_katalog'        => 1,
                'jenis_pelanggan'   => 'perseorangan',
                'is_custom'         => 0,
                'jumlah_order'      => 100,
                'deadline'          => $yesterday,
                'metode_pengiriman' => 'ambil_sendiri',
                'kuota_revisi'      => 2,
                'sisa_kuota'        => 1,
                'total_harga'       => 3000000,
                'require_dp'        => 1,
                'status'            => 'proses_desain',
                'created_at'        => $now,
            ],
            [
                'id_order'          => 2,
                'kode_order'        => 'ORD-TEST-002',
                'id_pelanggan'      => 1,
                'id_katalog'        => 2,
                'jenis_pelanggan'   => 'perseorangan',
                'is_custom'         => 0,
                'jumlah_order'      => 1,
                'deadline'          => $nextWeek,
                'metode_pengiriman' => 'kurir',
                'kuota_revisi'      => 2,
                'sisa_kuota'        => 2,
                'total_harga'       => 200000,
                'require_dp'        => 1,
                'status'            => 'proses_cetak',
                'created_at'        => $now,
            ],
            [
                'id_order'          => 3,
                'kode_order'        => 'ORD-TEST-003',
                'id_pelanggan'      => 1,
                'id_katalog'        => 1,
                'jenis_pelanggan'   => 'perseorangan',
                'is_custom'         => 0,
                'jumlah_order'      => 100,
                'deadline'          => $nextWeek,
                'metode_pengiriman' => 'ambil_sendiri',
                'kuota_revisi'      => 2,
                'sisa_kuota'        => 0,
                'total_harga'       => 3000000,
                'require_dp'        => 1,
                'status'            => 'selesai',
                'created_at'        => $now,
            ],
            [
                'id_order'          => 4,
                'kode_order'        => 'ORD-TEST-004',
                'id_pelanggan'      => 1,
                'id_katalog'        => 1,
                'jenis_pelanggan'   => 'perseorangan',
                'is_custom'         => 0,
                'jumlah_order'      => 50,
                'deadline'          => $nextWeek,
                'metode_pengiriman' => 'ambil_sendiri',
                'kuota_revisi'      => 2,
                'sisa_kuota'        => 2,
                'total_harga'       => 1500000,
                'require_dp'        => 1,
                'status'            => 'proses_desain',
                'created_at'        => $now,
            ],
            [
                'id_order'          => 5,
                'kode_order'        => 'ORD-TEST-005',
                'id_pelanggan'      => 1,
                'id_katalog'        => 2,
                'jenis_pelanggan'   => 'perseorangan',
                'is_custom'         => 0,
                'jumlah_order'      => 1,
                'deadline'          => $nextWeek,
                'metode_pengiriman' => 'ambil_sendiri',
                'kuota_revisi'      => 2,
                'sisa_kuota'        => 2,
                'total_harga'       => 200000,
                'require_dp'        => 1,
                'status'            => 'terverifikasi',
                'created_at'        => $now,
            ],
        ]);

        $db->table('revisi_desain')->insertBatch([
            [
                'id_revisi'   => 1,
                'id_order'    => 1,
                'id_produksi' => 4,
                'versi'       => 1,
                'file_draft'  => 'draft_v1.pdf',
                'status'      => 'acc',
                'created_at'  => $inPeriod,
            ],
            [
                'id_revisi'   => 2,
                'id_order'    => 1,
                'id_produksi' => 4,
                'versi'       => 2,
                'file_draft'  => 'draft_v2.pdf',
                'status'      => 'ditolak',
                'created_at'  => $inPeriod,
            ],
            [
                'id_revisi'   => 3,
                'id_order'    => 2,
                'id_produksi' => 4,
                'versi'       => 1,
                'file_draft'  => 'kalender_v1.pdf',
                'status'      => 'acc',
                'created_at'  => $inPeriod,
            ],
            [
                'id_revisi'   => 4,
                'id_order'    => 4,
                'id_produksi' => 4,
                'versi'       => 1,
                'file_draft'  => 'lama.pdf',
                'status'      => 'uploaded',
                'created_at'  => $outPeriod,
            ],
        ]);
    }
}
