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
        helper('activity_log');

        $builder = $this->builder();
        $cutoff  = activityLogRetentionCutoffDate();
        $today   = date('Y-m-d');

        // Lantai retensi selalu diterapkan.
        $builder->where('created_at >=', $cutoff . ' 00:00:00');

        $modul = trim((string) ($filters['modul'] ?? ''));
        if ($modul !== '') {
            $builder->where('modul', $modul);
        }

        $dari = trim((string) ($filters['dari'] ?? ''));
        if ($dari !== '') {
            if ($dari < $cutoff) {
                $dari = $cutoff;
            }
            if ($dari > $today) {
                $dari = $today;
            }
            $builder->where('created_at >=', $dari . ' 00:00:00');
        }

        $sampai = trim((string) ($filters['sampai'] ?? ''));
        if ($sampai !== '') {
            if ($sampai < $cutoff) {
                $sampai = $cutoff;
            }
            if ($sampai > $today) {
                $sampai = $today;
            }
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

    /**
     * Hapus log di luar jendela retensi.
     */
    public function purgeOutsideRetentionWindow(): int
    {
        helper('activity_log');

        $cutoff = activityLogRetentionCutoffDateTime();

        $this->builder()
            ->where('created_at <', $cutoff)
            ->delete();

        return (int) $this->db->affectedRows();
    }
}
