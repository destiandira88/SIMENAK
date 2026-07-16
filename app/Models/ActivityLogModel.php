<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table         = 'activity_logs';
    protected $primaryKey    = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_user',
        'nama_user',
        'role',
        'aksi',
        'modul',
        'keterangan',
        'created_at',
    ];

    /**
     * @param array{dari?: string, sampai?: string, modul?: string, cari?: string} $filters
     * @return list<array<string, mixed>>
     */
    public function getFiltered(array $filters = []): array
    {
        $builder = $this->builder();

        $modul = trim((string) ($filters['modul'] ?? ''));
        if ($modul !== '') {
            $builder->where('modul', $modul);
        }

        $dari = trim((string) ($filters['dari'] ?? ''));
        if ($dari !== '') {
            $builder->where('created_at >=', $dari . ' 00:00:00');
        }

        $sampai = trim((string) ($filters['sampai'] ?? ''));
        if ($sampai !== '') {
            $builder->where('created_at <=', $sampai . ' 23:59:59');
        }

        $cari = trim((string) ($filters['cari'] ?? ''));
        if ($cari !== '') {
            $builder->groupStart()
                ->like('keterangan', $cari)
                ->orLike('nama_user', $cari)
                ->groupEnd();
        }

        return $builder
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
