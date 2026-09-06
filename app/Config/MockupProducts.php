<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Slot % default per produk + sudut (kalibrasi AABB area blank).
 * Foto: public/assets/mockup/produk/{id_katalog}/{sudut}.jpg|png|webp
 * Admin unggah/ganti via form tambah & ubah katalog (bukan Produksi).
 *
 * Skema sudut:
 * - Undangan (ada cover/dalam di folder): cover → dalam
 * - Produk lain: depan → samping → atas
 */
class MockupProducts extends BaseConfig
{
    /**
     * Semua nama file yang dikenali + label carousel.
     * Urutan di array ini BUKAN urutan tampil final — lihat sudutOrderUndangan / sudutOrderDefault.
     *
     * @var array<string, string>
     */
    public array $sudutList = [
        'depan'   => 'Depan',
        'samping' => 'Samping',
        'atas'    => 'Atas',
        'cover'   => 'Cover',
        'dalam'   => 'Bagian Dalam',
    ];

    /**
     * Urutan carousel untuk undangan (kartu lipat).
     *
     * @var list<string>
     */
    public array $sudutOrderUndangan = ['cover', 'dalam'];

    /**
     * Urutan carousel skema lama (objek 3D / produk non-undangan).
     *
     * @var list<string>
     */
    public array $sudutOrderDefault = ['depan', 'samping', 'atas'];

    /**
     * @var array<int, array<string, array{top: float, left: float, width: float, height: float}>>
     */
    public array $slots = [
        // Undangan cover/dalam — kalibrasi dari pixel foto asli (sama untuk 1, 2, 12, 13)
        1 => [
            'cover' => ['top' => 17.68, 'left' => 26.04, 'width' => 48.96, 'height' => 56.57],
            'dalam' => ['top' => 20.00, 'left' => 19.17, 'width' => 65.42, 'height' => 54.14],
        ],
        2 => [
            'cover' => ['top' => 17.68, 'left' => 26.04, 'width' => 48.96, 'height' => 56.57],
            'dalam' => ['top' => 20.00, 'left' => 19.17, 'width' => 65.42, 'height' => 54.14],
        ],
        12 => [
            'cover' => ['top' => 17.68, 'left' => 26.04, 'width' => 48.96, 'height' => 56.57],
            'dalam' => ['top' => 20.00, 'left' => 19.17, 'width' => 65.42, 'height' => 54.14],
        ],
        13 => [
            'cover' => ['top' => 17.68, 'left' => 26.04, 'width' => 48.96, 'height' => 56.57],
            'dalam' => ['top' => 20.00, 'left' => 19.17, 'width' => 65.42, 'height' => 54.14],
        ],
        4 => [
            'depan' => ['top' => 10.0, 'left' => 30.0, 'width' => 40.0, 'height' => 72.0],
        ],
        5 => [
            'depan'   => ['top' => 28.0, 'left' => 18.0, 'width' => 60.0, 'height' => 42.0],
            'samping' => ['top' => 30.0, 'left' => 16.0, 'width' => 62.0, 'height' => 44.0],
        ],
        6 => [
            'depan' => ['top' => 24.0, 'left' => 12.0, 'width' => 74.0, 'height' => 58.0],
        ],
        8 => [
            'depan' => ['top' => 18.0, 'left' => 8.0, 'width' => 72.0, 'height' => 68.0],
        ],
        11 => [
            'depan' => ['top' => 14.0, 'left' => 10.0, 'width' => 40.0, 'height' => 46.0],
        ],
        20 => [
            'depan' => ['top' => 52.0, 'left' => 38.0, 'width' => 28.0, 'height' => 32.0],
        ],
    ];

    /**
     * Fallback AABB area blank (persentase relatif terhadap frame mockup).
     * Dipakai hanya jika slots[id_katalog][sudut] belum ada.
     * Undangan cover/dalam (1, 2, 12, 13) sudah punya slot khusus — tidak pakai ini.
     *
     * @var array{top: float, left: float, width: float, height: float}
     */
    public array $defaultSlot = [
        'top'    => 20.0,
        'left'   => 20.0,
        'width'  => 60.0,
        'height' => 60.0,
    ];
}
