<?php

use Config\MockupProducts;

/**
 * Normalisasi 1 layer transform.
 *
 * @param array<string, mixed> $vals
 * @return array{id: string, file: string, ox: float, oy: float, scale: float, rotate: float, cx: float, cy: float, cw: float, ch: float}
 */
function normalizeMockupLayer(array $vals, string $fallbackId = 'l1'): array
{
    $ox     = max(-80.0, min(80.0, (float) ($vals['ox'] ?? 0)));
    $oy     = max(-80.0, min(80.0, (float) ($vals['oy'] ?? 0)));
    $scale  = max(0.4, min(2.5, (float) ($vals['scale'] ?? 1)));
    $rotate = (float) ($vals['rotate'] ?? 0);
    $rotate = fmod($rotate, 360.0);
    if ($rotate < 0) {
        $rotate += 360.0;
    }
    $rotate = (float) (round($rotate / 90) * 90);
    if ($rotate >= 360.0) {
        $rotate = 0.0;
    }

    $cx = max(0.0, min(99.0, (float) ($vals['cx'] ?? 0)));
    $cy = max(0.0, min(99.0, (float) ($vals['cy'] ?? 0)));
    $cw = max(5.0, min(100.0 - $cx, (float) ($vals['cw'] ?? 100)));
    $ch = max(5.0, min(100.0 - $cy, (float) ($vals['ch'] ?? 100)));

    $file = trim((string) ($vals['file'] ?? 'primary'));
    if ($file === '') {
        $file = 'primary';
    }
    // Amankan nama file (primary / pending_* / basename saja)
    if ($file !== 'primary' && ! str_starts_with($file, 'pending_')) {
        $file = basename(str_replace(['\\', '/'], '', $file));
    }

    $id = trim((string) ($vals['id'] ?? $fallbackId));
    if ($id === '') {
        $id = $fallbackId;
    }

    return [
        'id'     => $id,
        'file'   => $file,
        'ox'     => round($ox, 2),
        'oy'     => round($oy, 2),
        'scale'  => round($scale, 3),
        'rotate' => $rotate,
        'cx'     => round($cx, 2),
        'cy'     => round($cy, 2),
        'cw'     => round($cw, 2),
        'ch'     => round($ch, 2),
    ];
}

/**
 * Normalisasi override area slot mockup (persen relatif frame).
 *
 * @param array<string, mixed> $vals
 * @return array{top: float, left: float, width: float, height: float}|null
 */
function normalizeMockupSlot(array $vals): ?array
{
    $width  = max(8.0, min(100.0, (float) ($vals['width'] ?? 0)));
    $height = max(8.0, min(100.0, (float) ($vals['height'] ?? 0)));
    if ($width <= 0 || $height <= 0) {
        return null;
    }
    $left = max(0.0, min(100.0 - $width, (float) ($vals['left'] ?? 0)));
    $top  = max(0.0, min(100.0 - $height, (float) ($vals['top'] ?? 0)));

    return [
        'top'    => round($top, 2),
        'left'   => round($left, 2),
        'width'  => round($width, 2),
        'height' => round($height, 2),
    ];
}

/**
 * Normalisasi JSON penyesuaian mockup per sudut (multi-layer).
 * Bentuk:
 * {"cover":{"layers":[{"id":"l1","file":"primary","ox":0,...,"cw":100,"ch":100}, ...],"slot":{"top":..,"left":..,"width":..,"height":..}}}
 * Legacy (tanpa layers) otomatis jadi 1 layer dari field ox/oy/scale/crop.
 *
 * @return array<string, array{layers: list<array<string, mixed>>, slot?: array{top: float, left: float, width: float, height: float}}>
 */
function normalizeMockupAdjust(mixed $raw): array
{
    if (is_string($raw)) {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        try {
            $raw = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return [];
        }
    }

    if (! is_array($raw)) {
        return [];
    }

    // JSON object kosong {} → array kosong di PHP; biarkan loop no-op → return [].
    // Encode ke view HARUS jadi {} bukan [] (lihat encodeMockupAdjust / json di partial).

    $out = [];
    foreach ($raw as $sudut => $vals) {
        if (! is_string($sudut) || $sudut === '' || ! is_array($vals)) {
            continue;
        }

        $layersIn = $vals['layers'] ?? null;
        $layers   = [];

        if (is_array($layersIn) && $layersIn !== []) {
            $i = 0;
            foreach ($layersIn as $layer) {
                if (! is_array($layer)) {
                    continue;
                }
                $i++;
                if ($i > 4) {
                    break; // maks 4 gambar per sudut
                }
                $layers[] = normalizeMockupLayer($layer, 'l' . $i);
            }
        } else {
            // Legacy single-transform → 1 layer
            $layers[] = normalizeMockupLayer($vals, 'l1');
        }

        if ($layers === []) {
            $layers[] = normalizeMockupLayer(['file' => 'primary'], 'l1');
        }

        $entry = ['layers' => $layers];
        if (isset($vals['slot']) && is_array($vals['slot'])) {
            $slot = normalizeMockupSlot($vals['slot']);
            if ($slot !== null) {
                $entry['slot'] = $slot;
            }
        }
        $out[$sudut] = $entry;
    }

    return $out;
}

/**
 * Ganti file pending_* dengan nama file hasil upload.
 *
 * @param array<string, array{layers: list<array<string, mixed>>}> $adjust
 * @param array<string, string>                                    $pendingToFile pending_id => filename
 * @return array<string, array{layers: list<array<string, mixed>>}>
 */
function remapMockupLayerFiles(array $adjust, array $pendingToFile): array
{
    if ($pendingToFile === []) {
        return $adjust;
    }

    foreach ($adjust as $sudut => &$data) {
        if (! isset($data['layers']) || ! is_array($data['layers'])) {
            continue;
        }
        foreach ($data['layers'] as &$layer) {
            $f = (string) ($layer['file'] ?? '');
            if (isset($pendingToFile[$f])) {
                $layer['file'] = $pendingToFile[$f];
            }
        }
        unset($layer);
    }
    unset($data);

    return normalizeMockupAdjust($adjust);
}

/**
 * Map file key → URL absolut untuk canvas preview.
 *
 * @param array<string, array{layers?: list<array<string, mixed>>>} $adjust
 * @return array<string, string>
 */
function resolveMockupLayerUrls(?string $primaryUrl, array $adjust): array
{
    $urls = [];
    if ($primaryUrl !== null && $primaryUrl !== '') {
        $urls['primary'] = $primaryUrl;
    }

    foreach ($adjust as $data) {
        $layers = $data['layers'] ?? [];
        if (! is_array($layers)) {
            continue;
        }
        foreach ($layers as $layer) {
            $file = (string) ($layer['file'] ?? 'primary');
            if ($file === 'primary' || str_starts_with($file, 'pending_') || isset($urls[$file])) {
                continue;
            }
            $u = resolveDraftDesainUrl($file);
            if ($u !== null) {
                $urls[$file] = $u;
            }
        }
    }

    return $urls;
}

/**
 * Encode adjust map ke JSON untuk kolom revisi_desain.mockup_adjust.
 *
 * @param array<string, mixed> $adjust
 */
function encodeMockupAdjust(array $adjust): ?string
{
    $norm = normalizeMockupAdjust($adjust);
    if ($norm === []) {
        return '{}';
    }

    return json_encode($norm, JSON_UNESCAPED_SLASHES) ?: '{}';
}

/**
 * Absolute URL draft desain; null jika kosong/file hilang.
 */
function resolveDraftDesainUrl(?string $fileDraft): ?string
{
    $name = trim((string) $fileDraft);
    if ($name === '') {
        return null;
    }

    $name = ltrim(str_replace('\\', '/', $name), '/');
    if (str_starts_with($name, 'uploads/draft_desain/')) {
        $name = substr($name, strlen('uploads/draft_desain/'));
    }

    $name = ltrim($name, '/');
    if ($name === '' || ! is_file(FCPATH . 'uploads/draft_desain/' . $name)) {
        return null;
    }

    return base_url('uploads/draft_desain/' . $name);
}

/**
 * @return array{top: float, left: float, width: float, height: float}
 */
function getMockupSlotForProduk(int $idKatalog, string $sudut): array
{
    /** @var MockupProducts $cfg */
    $cfg  = config('MockupProducts');
    $slot = $cfg->slots[$idKatalog][$sudut] ?? null;
    if (is_array($slot)) {
        return [
            'top'    => (float) ($slot['top'] ?? 20),
            'left'   => (float) ($slot['left'] ?? 20),
            'width'  => (float) ($slot['width'] ?? 60),
            'height' => (float) ($slot['height'] ?? 60),
        ];
    }

    // Fallback untuk sudut tanpa slots[] khusus (undangan cover/dalam sudah dikalibrasi).
    return $cfg->defaultSlot;
}

/**
 * Resolve file fisik di public/assets/mockup/produk/{id}/{sudut}.{ext}
 */
function resolveProdukMockupAssetPath(int $idKatalog, string $sudut): ?string
{
    /** @var MockupProducts $cfg */
    $cfg = config('MockupProducts');
    if ($idKatalog <= 0 || ! isset($cfg->sudutList[$sudut])) {
        return null;
    }

    $dir = FCPATH . 'assets/mockup/produk/' . $idKatalog . '/';
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $path = $dir . $sudut . '.' . $ext;
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

/**
 * True jika folder produk memakai skema undangan (cover/dalam).
 * Dicek dari keberadaan file, bukan hardcode id_katalog.
 */
function produkMockupPakaiSkemaUndangan(int $idKatalog): bool
{
    if ($idKatalog <= 0) {
        return false;
    }

    return resolveProdukMockupAssetPath($idKatalog, 'cover') !== null
        || resolveProdukMockupAssetPath($idKatalog, 'dalam') !== null;
}

/**
 * Urutan sudut untuk scan carousel produk ini.
 *
 * @return list<string>
 */
function getMockupSudutOrderForProduk(int $idKatalog): array
{
    /** @var MockupProducts $cfg */
    $cfg = config('MockupProducts');

    return produkMockupPakaiSkemaUndangan($idKatalog)
        ? $cfg->sudutOrderUndangan
        : $cfg->sudutOrderDefault;
}

/**
 * Sudut mockup untuk produk — 100% dari scan folder assets.
 * Skema undangan: cover → dalam. Skema lama: depan → samping → atas.
 * Hanya sudut yang punya file. Return [] → UI fallback draft biasa.
 *
 * @return list<array{
 *   sudut: string,
 *   sudut_label: string,
 *   background_url: string,
 *   slot_top: float,
 *   slot_left: float,
 *   slot_width: float,
 *   slot_height: float
 * }>
 */
function getMockupAnglesForProduk(int $idKatalog): array
{
    if ($idKatalog <= 0) {
        return [];
    }

    /** @var MockupProducts $cfg */
    $cfg = config('MockupProducts');
    $out = [];

    foreach (getMockupSudutOrderForProduk($idKatalog) as $sudut) {
        $abs = resolveProdukMockupAssetPath($idKatalog, $sudut);
        if ($abs === null) {
            continue;
        }

        $rel  = 'assets/mockup/produk/' . $idKatalog . '/' . basename($abs);
        $slot = getMockupSlotForProduk($idKatalog, $sudut);
        $out[] = [
            'sudut'          => $sudut,
            'sudut_label'    => (string) ($cfg->sudutList[$sudut] ?? $sudut),
            'background_url' => base_url($rel),
            'slot_top'       => $slot['top'],
            'slot_left'      => $slot['left'],
            'slot_width'     => $slot['width'],
            'slot_height'    => $slot['height'],
        ];
    }

    return $out;
}

/**
 * Daftar URL mockup per sudut (untuk form kelola katalog Admin).
 *
 * @return array<string, string> sudut => absolute URL
 */
function listExistingMockupUrls(int $idKatalog): array
{
    if ($idKatalog <= 0) {
        return [];
    }

    /** @var MockupProducts $cfg */
    $cfg = config('MockupProducts');
    $out = [];

    foreach (array_keys($cfg->sudutList) as $sudut) {
        $abs = resolveProdukMockupAssetPath($idKatalog, $sudut);
        if ($abs === null) {
            continue;
        }

        $rel         = 'assets/mockup/produk/' . $idKatalog . '/' . basename($abs);
        $out[$sudut] = base_url($rel);
    }

    return $out;
}

/**
 * Hapus semua ekstensi file mockup untuk satu sudut.
 */
function deleteProdukMockupSudut(int $idKatalog, string $sudut): bool
{
    /** @var MockupProducts $cfg */
    $cfg = config('MockupProducts');
    if ($idKatalog <= 0 || ! isset($cfg->sudutList[$sudut])) {
        return false;
    }

    $dir     = FCPATH . 'assets/mockup/produk/' . $idKatalog . '/';
    $deleted = false;

    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        $path = $dir . $sudut . '.' . $ext;
        if (is_file($path) && @unlink($path)) {
            $deleted = true;
        }
    }

    return $deleted;
}

/**
 * Pastikan folder assets/mockup/produk/{id}/ ada (+ index.html anti listing).
 *
 * @return string|null pesan error, atau null jika OK
 */
function ensureProdukMockupDir(int $idKatalog): ?string
{
    if ($idKatalog <= 0) {
        return 'ID produk tidak valid.';
    }

    $dir = FCPATH . 'assets/mockup/produk/' . $idKatalog . '/';
    if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
        return 'Folder mockup tidak tersedia.';
    }

    $index = $dir . 'index.html';
    if (! is_file($index)) {
        @file_put_contents($index, '<html><body>403</body></html>');
    }

    return null;
}
