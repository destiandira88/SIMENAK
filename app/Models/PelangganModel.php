<?php

namespace App\Models;

use CodeIgniter\Model;

class PelangganModel extends Model
{
    protected $table         = 'pelanggan';
    protected $primaryKey    = 'id_pelanggan';
    protected $useTimestamps   = false;
    protected $allowedFields = [
        'id_user',
        'no_telp',
        'alamat',
        'jenis',
        'nama_perusahaan',
        'is_verified',
    ];

    /**
     * @return array<string, mixed>|null
     */
    public function getByIdUser(int $idUser): ?array
    {
        return $this->where('id_user', $idUser)
            ->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getWithUser(int $idPelanggan): ?array
    {
        return $this->select('pelanggan.*, users.nama, users.email, users.role')
            ->join('users', 'users.id_user = pelanggan.id_user')
            ->where('pelanggan.id_pelanggan', $idPelanggan)
            ->first();
    }
}
