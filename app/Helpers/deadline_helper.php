<?php

/**
 * Ambil jumlah hari kerja dari string estimasi (mis. "6 hari kerja" atau legacy "5-7 hari kerja").
 */
function parseEstimasiMinHari(string $estimasi): int
{
    if (preg_match_all('/\d+/', $estimasi, $matches) > 0 && !empty($matches[0])) {
        $nums = array_map('intval', $matches[0]);

        return max(1, min($nums));
    }

    return 3;
}

function parseEstimasiHariKerja(string $estimasi): int
{
    return parseEstimasiMinHari($estimasi);
}

function formatEstimasiHariKerja(int $hari): string
{
    $hari = max(1, $hari);

    return $hari . ' hari kerja';
}

/**
 * Format estimasi tanpa minimum 1 (untuk hitung real-time konfirmasi harga custom).
 */
function formatEstimasiHariKerjaExact(int $hari): string
{
    return max(0, $hari) . ' hari kerja';
}

/**
 * Tanggal hari ini menurut timezone aplikasi (WIB).
 */
function todayYmdApp(): string
{
    return date('Y-m-d');
}

/**
 * Hitung hari kerja (Sen–Jum) dari besok ($today+1) sampai $deadlineYmd inklusif.
 * Hari $todayYmd tidak dihitung (produksi dianggap mulai besok).
 */
function countHariKerjaSampaiDeadline(string $deadlineYmd, ?string $todayYmd = null): int
{
    if ($deadlineYmd === '') {
        return 0;
    }

    $today = $todayYmd ?? todayYmdApp();

    if ($deadlineYmd < $today) {
        return 0;
    }

    $d   = new DateTime($today);
    $d->modify('+1 day');
    $end = new DateTime($deadlineYmd);
    $count = 0;

    while ($d <= $end) {
        if ((int) $d->format('N') <= 5) {
            $count++;
        }
        $d->modify('+1 day');
    }

    return $count;
}

/**
 * Hitung jumlah hari kerja (Sen–Jum) inklusif antara dua tanggal Y-m-d (legacy).
 */
function countHariKerjaBetween(string $startYmd, string $endYmd): int
{
    if ($startYmd === '' || $endYmd === '') {
        return 1;
    }

    if ($endYmd < $startYmd) {
        return 1;
    }

    $d     = new DateTime($startYmd);
    $end   = new DateTime($endYmd);
    $count = 0;

    while ($d <= $end) {
        if ((int) $d->format('N') <= 5) {
            $count++;
        }
        $d->modify('+1 day');
    }

    return max(1, $count);
}

/**
 * Estimasi pengerjaan custom dari tanggal deadline produksi (hitung sejak besok, Sen–Jum).
 */
function estimasiCustomFromDeadline(string $deadlineYmd, ?string $startYmd = null): string
{
    return formatEstimasiHariKerjaExact(countHariKerjaSampaiDeadline($deadlineYmd, $startYmd));
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
