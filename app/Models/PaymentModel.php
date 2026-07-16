<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id_payment';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'kode_payment',
        'id_order',
        'jenis',
        'nominal',
        'bukti_tf',
        'status',
        'catatan_tolak',
        'id_verifikator',
        'tgl_upload',
        'tgl_verifikasi',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getDpMenunggu(): array
    {
        return $this->select(
            'payments.*, orders.kode_order, orders.total_harga, orders.deadline_produksi, '
            . 'users.nama AS nama_pelanggan, users.email AS email_pelanggan, '
            . 'pelanggan.no_telp'
        )
            ->join('orders', 'orders.id_order = payments.id_order')
            ->join('pelanggan', 'pelanggan.id_pelanggan = orders.id_pelanggan')
            ->join('users', 'users.id_user = pelanggan.id_user')
            ->where('payments.jenis', 'dp')
            ->where('payments.status', 'menunggu')
            ->orderBy('payments.tgl_upload', 'ASC')
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getPelunasanMenunggu(): array
    {
        return $this->select(
            'payments.*, orders.kode_order, orders.total_harga, orders.deadline_produksi, '
            . 'users.nama AS nama_pelanggan, users.email AS email_pelanggan, '
            . 'pelanggan.no_telp'
        )
            ->join('orders', 'orders.id_order = payments.id_order')
            ->join('pelanggan', 'pelanggan.id_pelanggan = orders.id_pelanggan')
            ->join('users', 'users.id_user = pelanggan.id_user')
            ->where('payments.jenis', 'pelunasan')
            ->where('payments.status', 'menunggu')
            ->orderBy('payments.tgl_upload', 'ASC')
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getByOrder(int $idOrder): array
    {
        return $this->where('id_order', $idOrder)
            ->orderBy('tgl_upload', 'ASC')
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRiwayatSemua(): array
    {
        return $this->select(
            'payments.*, orders.kode_order, orders.total_harga, orders.deadline_produksi, orders.jenis_pelanggan, orders.status AS order_status,
         users.nama AS nama_pelanggan, users.email AS email_pelanggan,
         pelanggan.no_telp,
         verif.nama AS nama_verifikator'
        )
            ->join('orders', 'orders.id_order = payments.id_order')
            ->join('pelanggan', 'pelanggan.id_pelanggan = orders.id_pelanggan')
            ->join('users', 'users.id_user = pelanggan.id_user')
            ->join('users verif', 'verif.id_user = payments.id_verifikator', 'left')
            ->orderBy('payments.tgl_upload', 'DESC')
            ->findAll();
    }
}
