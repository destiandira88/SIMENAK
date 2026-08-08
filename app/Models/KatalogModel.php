<?php

namespace App\Models;

use CodeIgniter\Model;

class KatalogModel extends Model
{
    protected $table         = 'katalog';
    protected $primaryKey    = 'id_katalog';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'nama_produk',
        'kategori',
        'harga_dasar',
        'kuota_revisi_default',
        'min_order',
        'satuan',
        'estimasi_hari',
        'deskripsi',
        'is_active',
        'gambar',
        'kode_katalog',
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function getAktif(): array
    {
        return $this->where('is_active', 1)
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getByKategori(string $kategori): array
    {
        return $this->where('is_active', 1)
            ->where('kategori', $kategori)
            ->findAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getWithFormCount(): array
    {
        return $this->select('katalog.*, COUNT(ft.id_template) AS form_count')
            ->join('form_templates ft', 'ft.id_katalog = katalog.id_katalog', 'left')
            ->groupBy('katalog.id_katalog')
            ->orderBy('katalog.id_katalog', 'DESC')
            ->findAll();
    }
}
