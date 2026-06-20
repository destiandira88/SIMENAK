<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table         = 'orders';
    protected $primaryKey    = 'id_order';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'kode_order',
        'id_pelanggan',
        'id_katalog',
        'jenis_pelanggan',
        'jumlah_order',
        'is_custom',
        'catatan_custom',
        'harga_custom',
        'referensi_desain',
        'detail_pesanan',
        'deadline',
        'metode_pengiriman',
        'alamat_kirim',
        'kuota_revisi',
        'sisa_kuota',
        'total_harga',
        'require_dp',
        'status',
        'created_at',
        'batas_upload_dp',
        'reminder_dp_sent',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getByPelanggan(int $idPelanggan): array
    {
        return $this->select(
            'orders.*, k.nama_produk, k.kategori, k.satuan, '
            . sqlLatestOrderStatusPaymentFields('orders.id_order')
        )
            ->join('katalog k', 'k.id_katalog = orders.id_katalog')
            ->where('orders.id_pelanggan', $idPelanggan)
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDetailLengkap(string $kodeOrder): ?array
    {
        $row = $this->select(
            'orders.*, k.nama_produk, k.kategori, k.estimasi_hari, '
            . 'k.kuota_revisi_default, k.min_order, k.satuan, '
            . 'k.gambar AS gambar_katalog, u.nama AS nama_pelanggan, u.email AS email_pelanggan, '
            . 'p.no_telp'
        )
            ->join('katalog k', 'k.id_katalog = orders.id_katalog')
            ->join('pelanggan p', 'p.id_pelanggan = orders.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('orders.kode_order', $kodeOrder)
            ->first();

        return $row ?: null;
    }

    public function updateStatus(int $idOrder, string $status): bool
    {
        return $this->update($idOrder, ['status' => $status]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getListPemesanan(): array
    {
        return $this->select(
            'orders.*, k.nama_produk, k.kategori, k.satuan, '
            . 'u.nama AS nama_pelanggan, u.email AS email_pelanggan, p.no_telp, '
            . sqlLatestOrderStatusPaymentFields('orders.id_order')
        )
            ->join('katalog k', 'k.id_katalog = orders.id_katalog')
            ->join('pelanggan p', 'p.id_pelanggan = orders.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->orderBy('orders.created_at', 'DESC')
            ->findAll();
    }
}
