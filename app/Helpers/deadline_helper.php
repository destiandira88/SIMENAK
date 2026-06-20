<?php

/**
 * Ambil angka minimum dari string estimasi, mis. "5-7 hari kerja" → 5.
 */
function parseEstimasiMinHari(string $estimasi): int
{
    if (preg_match_all('/\d+/', $estimasi, $matches) > 0 && !empty($matches[0])) {
        $nums = array_map('intval', $matches[0]);

        return max(1, min($nums));
    }

    return 3;
}

/**
 * Tambah N hari kerja (Sen–Jum) dari tanggal awal (inklusif hari awal jika weekday).
 */
function addHariKerja(string $startYmd, int $hari): string
{
    if ($hari <= 0) {
        return $startYmd;
    }

    $d       = new DateTime($startYmd);
    $counted = 0;

    while ($counted < $hari) {
        $weekday = (int) $d->format('N');
        if ($weekday <= 5) {
            $counted++;
            if ($counted >= $hari) {
                break;
            }
        }
        $d->modify('+1 day');
    }

    return $d->format('Y-m-d');
}

/**
 * Tanggal deadline paling cepat berdasarkan estimasi (asumsi mulai hitung dari $startYmd).
 */
function minDeadlineFromEstimasi(string $estimasi, ?string $startYmd = null): string
{
    $start = $startYmd ?? date('Y-m-d');

    return addHariKerja($start, parseEstimasiMinHari($estimasi));
}

function isDeadlineValidForEstimasi(string $deadline, string $estimasi, ?string $startYmd = null): bool
{
    if ($deadline === '') {
        return false;
    }

    return $deadline >= minDeadlineFromEstimasi($estimasi, $startYmd);
}

function formatTanggalId(string $ymd): string
{
    $ts = strtotime($ymd);
    if ($ts === false) {
        return $ymd;
    }

    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];

    return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}
