<?php

namespace App\Models;

use CodeIgniter\Model;

class RevisiDesainModel extends Model
{
    protected $table         = 'revisi_desain';
    protected $primaryKey    = 'id_revisi';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_order',
        'id_produksi',
        'versi',
        'file_draft',
        'catatan_prod',
        'catatan_revisi',
        'status',
        'kode_revisi',
        'created_at',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getByOrder(int $idOrder): array
    {
        return $this->select('revisi_desain.*, users.nama AS nama_produksi')
            ->join('users', 'users.id_user = revisi_desain.id_produksi', 'left')
            ->where('revisi_desain.id_order', $idOrder)
            ->orderBy('revisi_desain.versi', 'ASC')
            ->findAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLatestByOrder(int $idOrder): ?array
    {
        $row = $this->select('revisi_desain.*, users.nama AS nama_produksi')
            ->join('users', 'users.id_user = revisi_desain.id_produksi', 'left')
            ->where('revisi_desain.id_order', $idOrder)
            ->orderBy('revisi_desain.versi', 'DESC')
            ->first();

        return $row ?: null;
    }

    public function getNextVersi(int $idOrder): int
    {
        $count = $this->where('id_order', $idOrder)->countAllResults();

        return $count + 1;
    }
}
