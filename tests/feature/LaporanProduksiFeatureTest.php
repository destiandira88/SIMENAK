<?php

use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\ProduksiReportSeeder;
use Tests\Support\SimenakTestCase;

/**
 * Feature test HTTP untuk halaman Laporan Produksi (Owner).
 *
 * @internal
 */
final class LaporanProduksiFeatureTest extends SimenakTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'Tests\Support';

    protected $seed = ProduksiReportSeeder::class;

    public function testGuestIsRedirectedFromLaporanProduksi(): void
    {
        $result = $this->get('laporan-produksi');

        $result->assertRedirect();
    }

    public function testAdminCannotAccessLaporanProduksi(): void
    {
        $result = $this->withSession($this->sessionForRole('admin', 2, 'Admin Test'))
            ->get('laporan-produksi');

        $result->assertRedirect();
    }

    public function testOwnerCanViewLaporanProduksiPage(): void
    {
        $result = $this->withSession($this->ownerSession())
            ->get('laporan-produksi');

        $result->assertOK();
        $result->assertSee('Laporan Desain');
        $result->assertSee('Antrian Desain Saat Ini');
        $result->assertSee('Aktivitas Revisi dalam Periode');
        $result->assertSee('ORD-TEST-001');
        $result->assertSee('Distribusi Tahap Desain');
    }

    public function testOwnerCanFilterByDateRange(): void
    {
        $result = $this->withSession($this->ownerSession())
            ->get('laporan-produksi?dari=2020-01-01&sampai=2020-01-31');

        $result->assertOK();
        $result->assertSee('Laporan Desain');
        $result->assertDontSee('ORD-TEST-001');
    }

    public function testOwnerExportDoesNotRedirect(): void
    {
        $dari   = date('Y-m-01');
        $sampai = date('Y-m-d');

        $result = $this->withSession($this->ownerSession())
            ->get('laporan-produksi/export?dari=' . $dari . '&sampai=' . $sampai . '&kategori=semua');

        $this->assertFalse($result->isRedirect());
        $result->assertStatus(200);
    }

    public function testAdminExportIsDenied(): void
    {
        $result = $this->withSession($this->sessionForRole('admin', 2, 'Admin Test'))
            ->get('laporan-produksi/export?dari=2026-06-01&sampai=2026-06-30&kategori=semua');

        $result->assertRedirect();
    }

    public function testKeuanganCannotAccessLaporanProduksi(): void
    {
        $result = $this->withSession($this->sessionForRole('keuangan', 3, 'Keuangan Test'))
            ->get('laporan-produksi');

        $result->assertRedirect();
    }
}
