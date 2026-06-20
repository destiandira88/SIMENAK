<?php

use App\Models\LaporanModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\ProduksiReportSeeder;
use Tests\Support\SimenakTestCase;

/**
 * Unit / integration test untuk buildProduksiReport() di LaporanModel.
 *
 * @internal
 */
final class LaporanModelProduksiTest extends SimenakTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'Tests\Support';

    protected $seed = ProduksiReportSeeder::class;

    private LaporanModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = model(LaporanModel::class);
    }

    public function testBuildProduksiReportHasExpectedStructure(): void
    {
        $report = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        );

        $this->assertArrayHasKey('range', $report);
        $this->assertArrayHasKey('snapshot', $report);
        $this->assertArrayHasKey('aktivitas', $report);
        $this->assertArrayHasKey('segmenTahap', $report);
        $this->assertArrayHasKey('daftarPesanan', $report);

        $this->assertArrayHasKey('pesananAktif', $report['snapshot']);
        $this->assertArrayHasKey('totalDraft', $report['aktivitas']);
        $this->assertArrayHasKey('slices', $report['segmenTahap']);
    }

    public function testSnapshotCountsPipelineOrders(): void
    {
        $snapshot = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['snapshot'];

        // ORD-001, 002, 004, 005 aktif di pipeline
        $this->assertSame(4, $snapshot['pesananAktif']);
        // proses_desain: 001 + 004 = 2, plus 005 terverifikasi = 3
        $this->assertSame(3, $snapshot['tahapDesain']);
        // proses_cetak: 002 = 1
        $this->assertSame(1, $snapshot['tahapCetakFinish']);
        // deadline kemarin + masih pipeline: ORD-001
        $this->assertSame(1, $snapshot['melewatiDeadline']);
    }

    public function testSnapshotIgnoresHistoricalDateFilter(): void
    {
        $currentMonth = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['snapshot'];

        $oldPeriod = $this->model->buildProduksiReport(
            '2020-01-01',
            '2020-01-31',
            'semua'
        )['snapshot'];

        $this->assertSame($currentMonth['pesananAktif'], $oldPeriod['pesananAktif']);
        $this->assertSame($currentMonth['tahapDesain'], $oldPeriod['tahapDesain']);
    }

    public function testAktivitasRevisiCountsInPeriod(): void
    {
        $aktivitas = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['aktivitas'];

        $this->assertSame(3, $aktivitas['totalDraft']);
        $this->assertSame(2, $aktivitas['acc']);
        $this->assertSame(1, $aktivitas['ditolak']);
        $this->assertSame(0, $aktivitas['diajukanRevisi']);
        $this->assertSame(0, $aktivitas['uploaded']);
        $this->assertSame(0.5, $aktivitas['rataRevisi']);
    }

    public function testSnapshotExcludesSelesaiAndShippedOrders(): void
    {
        $snapshot = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['snapshot'];

        // ORD-TEST-003 selesai — tidak ikut pipeline (4 aktif: 001, 002, 004, 005).
        $this->assertSame(4, $snapshot['pesananAktif']);
        $this->assertSame(
            ['terverifikasi', 'proses_desain', 'proses_revisi', 'proses_cetak', 'finishing'],
            array_keys($snapshot['perStatus'] ?? [])
        );
    }

    public function testPenolakanPeriodeCountsDitolakOnly(): void
    {
        $rows = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['daftarPesanan'];

        $byKode = [];
        foreach ($rows as $row) {
            $byKode[$row['kode_order']] = $row;
        }

        $this->assertSame(1, $byKode['ORD-TEST-001']['jumlah_revisi_periode']);
        $this->assertSame(0, $byKode['ORD-TEST-002']['jumlah_revisi_periode']);
    }

    public function testAktivitasEmptyOutsideSeededPeriod(): void
    {
        $aktivitas = $this->model->buildProduksiReport(
            '2020-01-01',
            '2020-01-31',
            'semua'
        )['aktivitas'];

        $this->assertSame(0, $aktivitas['totalDraft']);
        $this->assertNull($aktivitas['rataRevisi']);
    }

    public function testDaftarPesananOnlyIncludesOrdersWithRevisiInPeriod(): void
    {
        $rows = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['daftarPesanan'];

        $kodes = array_column($rows, 'kode_order');
        sort($kodes);

        $this->assertSame(['ORD-TEST-001', 'ORD-TEST-002'], $kodes);
    }

    public function testDaftarPesananExcludesOrderWithOnlyOldRevisi(): void
    {
        $rows = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['daftarPesanan'];

        $kodes = array_column($rows, 'kode_order');
        $this->assertNotContains('ORD-TEST-004', $kodes);
        $this->assertNotContains('ORD-TEST-005', $kodes);
    }

    public function testDaftarPesananRowHasPenolakanStatsAndTelatFlag(): void
    {
        $rows = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        )['daftarPesanan'];

        $byKode = [];
        foreach ($rows as $row) {
            $byKode[$row['kode_order']] = $row;
        }

        $this->assertSame(1, $byKode['ORD-TEST-001']['jumlah_revisi_periode']);
        $this->assertSame(2, $byKode['ORD-TEST-001']['versi_terakhir']);
        $this->assertTrue($byKode['ORD-TEST-001']['is_telat']);

        $this->assertSame(0, $byKode['ORD-TEST-002']['jumlah_revisi_periode']);
        $this->assertSame(1, $byKode['ORD-TEST-001']['versi_dipilih']);
        $this->assertSame(1, $byKode['ORD-TEST-002']['versi_dipilih']);
        $this->assertFalse($byKode['ORD-TEST-002']['is_telat']);
    }

    public function testKategoriFilterNarrowsSnapshotAndAktivitas(): void
    {
        $report = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'cetak_digital'
        );

        $this->assertSame(2, $report['snapshot']['pesananAktif']);
        $this->assertSame(2, $report['aktivitas']['totalDraft']);
        $this->assertCount(1, $report['daftarPesanan']);
        $this->assertSame('ORD-TEST-001', $report['daftarPesanan'][0]['kode_order']);
    }

    public function testSegmenTahapDonutMatchesSnapshotTotal(): void
    {
        $report = $this->model->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        );

        $this->assertSame(
            $report['snapshot']['pesananAktif'],
            $report['segmenTahap']['totalCount']
        );
    }
}
