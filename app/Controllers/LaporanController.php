<?php

namespace App\Controllers;

use App\Models\LaporanModel;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\RedirectResponse;

class LaporanController extends BaseController
{
    protected $helpers = ['url', 'notification'];

    public function index(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if ($role !== 'owner') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan owner hanya untuk role Owner.');
        }

        $filters = $this->resolveOwnerDateFilters();

        try {
            $report = model(LaporanModel::class)->buildOwnerReport($filters['dari'], $filters['sampai']);
        } catch (\Throwable $e) {
            log_message('error', 'Laporan owner index: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Gagal memuat data laporan.');
        }

        return view('laporan/owner', [
            'title'   => 'Laporan',
            'page_title' => 'Laporan Owner',
            'report'  => $report,
            'filters' => $filters,
        ]);
    }

    public function export(): RedirectResponse|DownloadResponse
    {
        $role = (string) session()->get('role');
        if ($role !== 'owner') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan owner hanya untuk role Owner.');
        }

        $filters = $this->resolveOwnerDateFilters();

        try {
            $report = model(LaporanModel::class)->buildOwnerReport($filters['dari'], $filters['sampai']);
        } catch (\Throwable $e) {
            log_message('error', 'Laporan owner export: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('laporan'))
                ->with('error', 'Gagal mengekspor laporan.');
        }

        $filename = sprintf(
            'laporan_owner_%s_%s.csv',
            str_replace('-', '', $filters['dari']),
            str_replace('-', '', $filters['sampai'])
        );
        $csv      = $this->buildOwnerCsv($report);

        return $this->response->download($filename, $csv);
    }

    public function adminIndex(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['admin', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan admin tidak diizinkan.');
        }

        $filters = $this->resolveAdminFilters();

        try {
            $report = model(LaporanModel::class)->buildAdminReport(
                $filters['dari'],
                $filters['sampai'],
                $filters['status'],
                $filters['kategori'],
                $filters['jenis_pelanggan'],
                $filters['tipe_pesanan']
            );
        } catch (\Throwable $e) {
            log_message('error', 'Laporan admin index: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Gagal memuat laporan admin.');
        }

        return view('laporan/admin', [
            'title'      => 'Laporan Admin',
            'page_title' => 'Laporan Admin',
            'report'     => $report,
            'filters'    => $filters,
            'readOnly'   => $role === 'owner',
        ]);
    }

    public function adminExport(): RedirectResponse|DownloadResponse
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['admin', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan admin tidak diizinkan.');
        }

        $filters = $this->resolveAdminFilters();

        try {
            $report = model(LaporanModel::class)->buildAdminReport(
                $filters['dari'],
                $filters['sampai'],
                $filters['status'],
                $filters['kategori'],
                $filters['jenis_pelanggan'],
                $filters['tipe_pesanan']
            );
        } catch (\Throwable $e) {
            log_message('error', 'Laporan admin export: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('laporan-admin'))
                ->with('error', 'Gagal mengekspor laporan admin.');
        }

        $filename = sprintf(
            'laporan_admin_%s_%s.csv',
            $filters['dari'],
            $filters['sampai']
        );
        $csv = $this->buildAdminCsv($report, $filters);

        return $this->response->download($filename, $csv);
    }

    public function keuanganIndex(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['keuangan', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan transaksi tidak diizinkan.');
        }

        $filters = $this->resolveKeuanganFilters();

        try {
            $report = model(LaporanModel::class)->buildKeuanganReport(
                $filters['dari'],
                $filters['sampai'],
                $filters['jenis'],
                $filters['status']
            );
        } catch (\Throwable $e) {
            log_message('error', 'Laporan keuangan index: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Gagal memuat laporan transaksi.');
        }

        return view('laporan/keuangan', [
            'title'      => 'Laporan Transaksi',
            'page_title' => 'Laporan Transaksi',
            'report'     => $report,
            'filters'    => $filters,
            'readOnly'   => $role === 'owner',
        ]);
    }

    public function keuanganExport(): RedirectResponse|DownloadResponse
    {
        $role = (string) session()->get('role');
        if (!in_array($role, ['keuangan', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan transaksi tidak diizinkan.');
        }

        $filters = $this->resolveKeuanganFilters();

        try {
            $report = model(LaporanModel::class)->buildKeuanganReport(
                $filters['dari'],
                $filters['sampai'],
                $filters['jenis'],
                $filters['status']
            );
        } catch (\Throwable $e) {
            log_message('error', 'Laporan keuangan export: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('laporan-keuangan'))
                ->with('error', 'Gagal mengekspor laporan transaksi.');
        }

        $filename = sprintf(
            'laporan_transaksi_%s_%s.csv',
            $filters['dari'],
            $filters['sampai']
        );
        $csv = $this->buildKeuanganCsv($report, $filters);

        return $this->response->download($filename, $csv);
    }

    public function produksiIndex(): RedirectResponse|string
    {
        $role = (string) session()->get('role');
        if ($role !== 'owner') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan desain hanya untuk role Owner.');
        }

        $filters = $this->resolveProduksiFilters();

        try {
            $report = model(LaporanModel::class)->buildProduksiReport(
                $filters['dari'],
                $filters['sampai'],
                $filters['kategori']
            );
        } catch (\Throwable $e) {
            log_message('error', 'Laporan produksi index: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Gagal memuat laporan desain.');
        }

        return view('laporan/produksi', [
            'title'      => 'Laporan Desain',
            'page_title' => 'Laporan Desain',
            'report'     => $report,
            'filters'    => $filters,
            'readOnly'   => true,
        ]);
    }

    public function produksiExport(): RedirectResponse|DownloadResponse
    {
        $role = (string) session()->get('role');
        if ($role !== 'owner') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses laporan desain hanya untuk role Owner.');
        }

        $filters = $this->resolveProduksiFilters();

        try {
            $report = model(LaporanModel::class)->buildProduksiReport(
                $filters['dari'],
                $filters['sampai'],
                $filters['kategori']
            );
        } catch (\Throwable $e) {
            log_message('error', 'Laporan produksi export: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('laporan-produksi'))
                ->with('error', 'Gagal mengekspor laporan desain.');
        }

        $filename = sprintf(
            'laporan_produksi_%s_%s.csv',
            $filters['dari'],
            $filters['sampai']
        );
        $csv = $this->buildProduksiCsv($report, $filters);

        return $this->response->download($filename, $csv);
    }

    /**
     * @return array{dari: string, sampai: string, kategori: string}
     */
    private function resolveProduksiFilters(): array
    {
        $dari     = trim((string) ($this->request->getGet('dari') ?? date('Y-m-01')));
        $sampai   = trim((string) ($this->request->getGet('sampai') ?? date('Y-m-d')));
        $kategori = trim((string) ($this->request->getGet('kategori') ?? 'semua'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
            $dari = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
            $sampai = date('Y-m-d');
        }

        $allowedKategori = ['semua', 'cetak_offset', 'cetak_digital', 'desain_grafis', 'media_promosi'];
        if (!in_array($kategori, $allowedKategori, true)) {
            $kategori = 'semua';
        }

        return [
            'dari'     => $dari,
            'sampai'   => $sampai,
            'kategori' => $kategori,
        ];
    }

    /**
     * @param array<string, mixed> $report
     * @param array{dari: string, sampai: string, kategori: string} $filters
     */
    private function buildProduksiCsv(array $report, array $filters): string
    {
        $lines    = [];
        $range    = $report['range'] ?? [];
        $snapshot = $report['snapshot'] ?? [];
        $aktivitas = $report['aktivitas'] ?? [];

        $lines[] = $this->csvRow(['Laporan Desain SIMENAK']);
        $lines[] = $this->csvRow(['Periode aktivitas revisi', (string) ($range['label'] ?? '-')]);
        $lines[] = $this->csvRow(['Filter Kategori', $filters['kategori']]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Antrian Desain Saat Ini (ringkasan hari ini)']);
        $lines[] = $this->csvRow(['Antrian Desain Aktif', (string) ($snapshot['pesananAktif'] ?? 0)]);
        $lines[] = $this->csvRow(['Tahap Desain', (string) ($snapshot['tahapDesain'] ?? 0)]);
        $lines[] = $this->csvRow(['Tahap Cetak & Finishing', (string) ($snapshot['tahapCetakFinish'] ?? 0)]);
        $lines[] = $this->csvRow(['Melewati Deadline', (string) ($snapshot['melewatiDeadline'] ?? 0)]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Aktivitas Revisi dalam Periode']);
        $lines[] = $this->csvRow(['Total Draft Diupload', (string) ($aktivitas['totalDraft'] ?? 0)]);
        $lines[] = $this->csvRow(['ACC', (string) ($aktivitas['acc'] ?? 0)]);
        $lines[] = $this->csvRow(['Ditolak', (string) ($aktivitas['ditolak'] ?? 0)]);
        $lines[] = $this->csvRow(['Diajukan Revisi', (string) ($aktivitas['diajukanRevisi'] ?? 0)]);
        $lines[] = $this->csvRow(['Menunggu Respon', (string) ($aktivitas['menungguRespon'] ?? $aktivitas['uploaded'] ?? 0)]);
        $lines[] = $this->csvRow([
            'Rata-rata Penolakan/Pesanan',
            $aktivitas['rataRevisi'] !== null ? (string) $aktivitas['rataRevisi'] : '-',
        ]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Daftar Pesanan (aktivitas revisi dalam periode)']);
        $lines[] = $this->csvRow([
            'Kode Order', 'Pelanggan', 'Produk', 'Deadline', 'Status', 'Kuota', 'Penolakan', 'Versi Terakhir', 'Versi Dipilih', 'Telat',
        ]);
        foreach ($report['daftarPesanan'] ?? [] as $row) {
            $deadline = trim((string) ($row['deadline'] ?? ''));
            $lines[] = $this->csvRow([
                (string) ($row['kode_order'] ?? '-'),
                (string) ($row['nama_pelanggan'] ?? '-'),
                (int) ($row['is_custom'] ?? 0) === 1 ? 'Pesanan Custom' : (string) ($row['nama_produk'] ?? '-'),
                $deadline !== '' ? $deadline : '-',
                (string) ($row['status'] ?? '-'),
                (int) ($row['sisa_kuota'] ?? 0) . '/' . (int) ($row['kuota_revisi'] ?? 0),
                (string) ($row['jumlah_revisi_periode'] ?? 0),
                (string) ($row['versi_terakhir'] ?? 0),
                (int) ($row['versi_dipilih'] ?? 0) > 0 ? (string) ($row['versi_dipilih'] ?? 0) : '-',
                !empty($row['is_telat']) ? 'Ya' : 'Tidak',
            ]);
        }

        return "\xEF\xBB\xBF" . implode("\r\n", $lines);
    }

    /**
     * @return array{dari: string, sampai: string, jenis: string, status: string}
     */
    private function resolveKeuanganFilters(): array
    {
        $dari   = trim((string) ($this->request->getGet('dari') ?? date('Y-m-01')));
        $sampai = trim((string) ($this->request->getGet('sampai') ?? date('Y-m-d')));
        $jenis  = trim((string) ($this->request->getGet('jenis') ?? 'semua'));
        $status = trim((string) ($this->request->getGet('status') ?? 'semua'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
            $dari = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
            $sampai = date('Y-m-d');
        }

        if (!in_array($jenis, ['semua', 'dp', 'pelunasan'], true)) {
            $jenis = 'semua';
        }
        if (!in_array($status, ['semua', 'terverifikasi', 'menunggu'], true)) {
            $status = 'semua';
        }

        return [
            'dari'   => $dari,
            'sampai' => $sampai,
            'jenis'  => $jenis,
            'status' => $status,
        ];
    }

    /**
     * @param array<string, mixed> $report
     * @param array{dari: string, sampai: string, jenis: string, status: string} $filters
     */
    private function buildKeuanganCsv(array $report, array $filters): string
    {
        $lines  = [];
        $range  = $report['range'] ?? [];
        $model  = model(LaporanModel::class);

        $lines[] = $this->csvRow(['Laporan Transaksi SIMENAK']);
        $lines[] = $this->csvRow(['Periode', (string) ($range['label'] ?? '-')]);
        $lines[] = $this->csvRow(['Filter Jenis', $filters['jenis']]);
        $lines[] = $this->csvRow(['Filter Status', $filters['status']]);
        $lines[] = $this->csvRow([]);

        $summary = $report['summaryCards'] ?? [];
        $lines[] = $this->csvRow(['Ringkasan']);
        $lines[] = $this->csvRow(['Total Pemasukan (terverifikasi)', (string) ($summary['totalPemasukan'] ?? 0)]);
        $lines[] = $this->csvRow(['Pemasukan DP', (string) ($summary['pemasukanDp'] ?? 0)]);
        $lines[] = $this->csvRow(['Pemasukan Pelunasan', (string) ($summary['pemasukanPelunasan'] ?? 0)]);
        $lines[] = $this->csvRow(['Menunggu Verifikasi (saat ini)', (string) ($summary['menungguVerifikasi'] ?? 0)]);
        $lines[] = $this->csvRow(['  — DP menunggu', (string) ($summary['menungguDp'] ?? 0)]);
        $lines[] = $this->csvRow(['  — Pelunasan menunggu', (string) ($summary['menungguPelunasan'] ?? 0)]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Rekap Per Jenis Pembayaran (terverifikasi dalam periode)']);
        $lines[] = $this->csvRow(['Jenis', 'Jumlah Transaksi', 'Total Nominal', 'Rata-rata/Transaksi']);
        foreach ($report['rekapJenis']['rows'] ?? [] as $row) {
            $lines[] = $this->csvRow([
                (string) ($row['label'] ?? '-'),
                (string) ($row['jumlah'] ?? 0),
                (string) ($row['nominal'] ?? 0),
                (string) ($row['rata_rata'] ?? 0),
            ]);
        }
        $totals = $report['rekapJenis']['totals'] ?? [];
        $lines[] = $this->csvRow([
            'TOTAL',
            (string) ($totals['jumlah'] ?? 0),
            (string) ($totals['nominal'] ?? 0),
            (string) ($totals['rata_rata'] ?? 0),
        ]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Daftar Transaksi']);
        $lines[] = $this->csvRow([
            'Kode Bayar', 'Kode Order', 'Pelanggan', 'Jenis', 'Nominal', 'Status',
            'Tgl Upload', 'Tgl Verifikasi', 'Diverifikasi Oleh',
        ]);
        foreach ($report['daftarTransaksi'] ?? [] as $row) {
            $tglUpload = trim((string) ($row['tgl_upload'] ?? ''));
            $tglVerif  = trim((string) ($row['tgl_verifikasi'] ?? ''));
            $lines[] = $this->csvRow([
                (string) ($row['kode_payment'] ?? '-'),
                (string) ($row['kode_order'] ?? '-'),
                (string) ($row['nama_pelanggan'] ?? '-'),
                $model->paymentJenisLabel((string) ($row['jenis'] ?? '')),
                (string) ($row['nominal'] ?? 0),
                (string) ($row['status'] ?? '-'),
                $tglUpload !== '' ? date('Y-m-d H:i', strtotime($tglUpload)) : '-',
                $tglVerif !== '' ? date('Y-m-d H:i', strtotime($tglVerif)) : '-',
                trim((string) ($row['email_verifikator'] ?? '')) !== '' ? (string) $row['email_verifikator'] : '-',
            ]);
        }

        return "\xEF\xBB\xBF" . implode("\r\n", $lines);
    }

    /**
     * @return array{dari: string, sampai: string, status: string, kategori: string, jenis_pelanggan: string, tipe_pesanan: string}
     */
    private function resolveAdminFilters(): array
    {
        $dari = trim((string) ($this->request->getGet('dari') ?? date('Y-m-01')));
        $sampai = trim((string) ($this->request->getGet('sampai') ?? date('Y-m-d')));
        $status = trim((string) ($this->request->getGet('status') ?? 'semua'));
        $kategori = trim((string) ($this->request->getGet('kategori') ?? 'semua'));
        $jenisPelanggan = trim((string) ($this->request->getGet('jenis_pelanggan') ?? 'semua'));
        $tipePesanan = trim((string) ($this->request->getGet('tipe_pesanan') ?? 'semua'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
            $dari = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
            $sampai = date('Y-m-d');
        }

        $allowedStatus = ['semua', 'selesai', 'proses', 'dibatalkan'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'semua';
        }

        $allowedKategori = ['semua', 'cetak_offset', 'cetak_digital', 'desain_grafis', 'media_promosi'];
        if (!in_array($kategori, $allowedKategori, true)) {
            $kategori = 'semua';
        }

        $allowedJenis = ['semua', 'perusahaan', 'perseorangan'];
        if (!in_array($jenisPelanggan, $allowedJenis, true)) {
            $jenisPelanggan = 'semua';
        }

        $allowedTipe = ['semua', 'standar', 'custom'];
        if (!in_array($tipePesanan, $allowedTipe, true)) {
            $tipePesanan = 'semua';
        }

        return [
            'dari'             => $dari,
            'sampai'           => $sampai,
            'status'           => $status,
            'kategori'         => $kategori,
            'jenis_pelanggan'  => $jenisPelanggan,
            'tipe_pesanan'     => $tipePesanan,
        ];
    }

    /**
     * @param array<string, mixed> $report
     * @param array{dari: string, sampai: string, status: string, kategori: string} $filters
     */
    private function buildAdminCsv(array $report, array $filters): string
    {
        $lines   = [];
        $range   = $report['range'] ?? [];

        $lines[] = $this->csvRow(['Laporan Admin SIMENAK']);
        $lines[] = $this->csvRow(['Periode', (string) ($range['label'] ?? '-')]);
        $lines[] = $this->csvRow(['Filter Status', $filters['status']]);
        $lines[] = $this->csvRow(['Filter Kategori', $filters['kategori']]);
        $lines[] = $this->csvRow(['Filter Jenis Pelanggan', $filters['jenis_pelanggan']]);
        $lines[] = $this->csvRow(['Filter Tipe Pesanan', $filters['tipe_pesanan']]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Ringkasan', 'Periode: ' . (string) ($range['label'] ?? '-')]);
        $summary = $report['summaryCards'] ?? [];
        $lines[] = $this->csvRow(['Total Pesanan', (string) ($summary['totalPesanan'] ?? 0)]);
        $lines[] = $this->csvRow(['Pesanan Masuk', (string) ($summary['pesananMasuk'] ?? 0)]);
        $lines[] = $this->csvRow(['Pesanan Selesai', (string) ($summary['pesananSelesai'] ?? 0)]);
        $lines[] = $this->csvRow(['Pesanan Dibatalkan', (string) ($summary['pesananDibatalkan'] ?? 0)]);
        $lines[] = $this->csvRow(['Potensi Pesanan Aktif', (string) ($report['potensiAktif'] ?? 0)]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Rekap Per Kategori']);
        $lines[] = $this->csvRow(['Kategori', 'Jumlah Pesanan', 'Total Pendapatan', 'Rata-rata/Pesanan']);
        foreach ($report['rekapKategori']['rows'] ?? [] as $row) {
            $lines[] = $this->csvRow([
                (string) ($row['label'] ?? '-'),
                (string) ($row['jumlah'] ?? 0),
                (string) ($row['pendapatan'] ?? 0),
                (string) ($row['rata_rata'] ?? 0),
            ]);
        }
        $totals = $report['rekapKategori']['totals'] ?? [];
        $lines[] = $this->csvRow([
            'TOTAL',
            (string) ($totals['jumlah'] ?? 0),
            (string) ($totals['pendapatan'] ?? 0),
            (string) ($totals['rata_rata'] ?? 0),
        ]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Daftar Pesanan']);
        $lines[] = $this->csvRow([
            'Kode Order', 'Pelanggan', 'Jenis', 'Tipe', 'Produk', 'Kategori',
            'Tgl Pesan', 'Deadline', 'Total', 'Status', 'Metode', 'Tgl Selesai',
        ]);
        $laporanModel = model(LaporanModel::class);
        foreach ($report['daftarPesanan'] ?? [] as $row) {
            $tglSelesai = $row['tgl_selesai'] ?? null;
            $createdAt  = (string) ($row['created_at'] ?? '');
            $deadline   = trim((string) ($row['deadline'] ?? ''));
            $namaProduk = trim((string) ($row['nama_produk'] ?? ''));
            if ($namaProduk === '') {
                $namaProduk = '-';
            }
            $lines[] = $this->csvRow([
                (string) ($row['kode_order'] ?? '-'),
                (string) ($row['nama_pelanggan'] ?? '-'),
                $laporanModel->jenisPelangganLabel((string) ($row['jenis_pelanggan'] ?? '')),
                $laporanModel->tipePesananLabel((int) ($row['is_custom'] ?? 0)),
                $namaProduk,
                $laporanModel->kategoriLabel((string) ($row['kategori'] ?? '')),
                $createdAt !== '' ? date('Y-m-d', strtotime($createdAt)) : '-',
                $deadline !== '' ? $deadline : '-',
                (string) ($row['total_harga'] ?? 0),
                (string) ($row['status'] ?? '-'),
                $laporanModel->metodePengirimanLabel((string) ($row['metode_pengiriman'] ?? '')),
                $tglSelesai ? date('Y-m-d', strtotime((string) $tglSelesai)) : '-',
            ]);
        }

        return "\xEF\xBB\xBF" . implode("\r\n", $lines);
    }

    /**
     * @return array{dari: string, sampai: string}
     */
    private function resolveOwnerDateFilters(): array
    {
        $dari   = trim((string) ($this->request->getGet('dari') ?? date('Y-m-01')));
        $sampai = trim((string) ($this->request->getGet('sampai') ?? date('Y-m-d')));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
            $dari = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
            $sampai = date('Y-m-d');
        }

        if (strtotime($dari) > strtotime($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        return [
            'dari'   => $dari,
            'sampai' => $sampai,
        ];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolvePeriodFilter(): array
    {
        $month = (int) ($this->request->getGet('bulan') ?? date('n'));
        $year  = (int) ($this->request->getGet('tahun') ?? date('Y'));

        $month = max(1, min(12, $month));
        $year  = max(2000, min(2100, $year));

        return [$month, $year];
    }

    /**
     * @param array<string, mixed> $report
     */
    private function buildOwnerCsv(array $report): string
    {
        $lines   = [];
        $period  = $report['period'] ?? [];

        $lines[] = $this->csvRow(['Laporan Owner SIMENAK']);
        $lines[] = $this->csvRow(['Periode', (string) ($period['label'] ?? '-')]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Ringkasan']);
        $lines[] = $this->csvRow(['Total Pesanan', (string) ($report['totalPesanan'] ?? 0)]);
        $lines[] = $this->csvRow(['Total Pendapatan', (string) ($report['totalPendapatan'] ?? 0)]);
        $lines[] = $this->csvRow(['Pertumbuhan Pesanan (%)', $this->formatGrowthCsv($report['growthOrders'] ?? null)]);
        $lines[] = $this->csvRow(['Rata-rata Selesai (hari)', $report['avgSelesaiHari'] !== null ? (string) $report['avgSelesaiHari'] : '-']);
        $lines[] = $this->csvRow(['Pesanan Dibatalkan', (string) ($report['pesananDibatalkan'] ?? 0)]);
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Segmen Pelanggan']);
        $lines[] = $this->csvRow(['Jenis', 'Jumlah Pesanan', '% Pesanan', 'Pendapatan', 'Rata-rata/Pesanan']);
        foreach ($report['segmenPelanggan']['slices'] ?? [] as $row) {
            $lines[] = $this->csvRow([
                (string) ($row['label'] ?? '-'),
                (string) ($row['count'] ?? 0),
                (string) ($row['pct_count'] ?? 0),
                (string) ($row['revenue'] ?? 0),
                (string) ($row['rata_rata'] ?? 0),
            ]);
        }
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Segmen Pemesanan']);
        $lines[] = $this->csvRow(['Jenis', 'Jumlah Pesanan', '% Pesanan', 'Pendapatan', 'Rata-rata/Pesanan']);
        foreach ($report['segmenPemesanan']['slices'] ?? [] as $row) {
            $lines[] = $this->csvRow([
                (string) ($row['label'] ?? '-'),
                (string) ($row['count'] ?? 0),
                (string) ($row['pct_count'] ?? 0),
                (string) ($row['revenue'] ?? 0),
                (string) ($row['rata_rata'] ?? 0),
            ]);
        }
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Rekap Per Kategori']);
        $lines[] = $this->csvRow(['Kategori', 'Jumlah Pesanan', 'Total Pendapatan', 'Rata-rata/Pesanan']);
        foreach ($report['rekapKategori'] ?? [] as $row) {
            $lines[] = $this->csvRow([
                (string) ($row['label'] ?? '-'),
                (string) ($row['jumlah'] ?? 0),
                (string) ($row['pendapatan'] ?? 0),
                (string) ($row['rata_rata'] ?? 0),
            ]);
        }
        $lines[] = $this->csvRow([]);

        $lines[] = $this->csvRow(['Top Produk Terlaris']);
        $lines[] = $this->csvRow(['Nama Produk', 'Jumlah Pesanan', 'Total Pendapatan']);
        foreach ($report['topProduk'] ?? [] as $row) {
            $lines[] = $this->csvRow([
                (string) ($row['nama_produk'] ?? '-'),
                (string) ($row['jumlah'] ?? 0),
                (string) ($row['pendapatan'] ?? 0),
            ]);
        }

        return "\xEF\xBB\xBF" . implode("\r\n", $lines);
    }

    /**
     * @param list<string|int|float> $fields
     */
    private function csvRow(array $fields): string
    {
        $escaped = array_map(static function ($value) {
            $text = str_replace('"', '""', (string) $value);

            return '"' . $text . '"';
        }, $fields);

        return implode(';', $escaped);
    }

    private function formatGrowthCsv(?float $value): string
    {
        if ($value === null) {
            return '-';
        }

        return ($value >= 0 ? '+' : '') . number_format($value, 1, ',', '.');
    }
}
