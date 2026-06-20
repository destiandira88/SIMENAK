<?php

use App\Controllers\LaporanController;
use App\Models\LaporanModel;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\ProduksiReportSeeder;
use Tests\Support\SimenakTestCase;

/**
 * Unit test generator CSV Laporan Produksi.
 *
 * @internal
 */
final class LaporanProduksiCsvTest extends SimenakTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'Tests\Support';

    protected $seed = ProduksiReportSeeder::class;

    public function testBuildProduksiCsvContainsSnapshotAndOrderRows(): void
    {
        $report = model(LaporanModel::class)->buildProduksiReport(
            date('Y-m-01'),
            date('Y-m-d'),
            'semua'
        );

        $filters = [
            'dari'     => date('Y-m-01'),
            'sampai'   => date('Y-m-d'),
            'kategori' => 'semua',
        ];

        $controller = new LaporanController();
        $invokeCsv  = self::getPrivateMethodInvoker($controller, 'buildProduksiCsv');
        $csv        = $invokeCsv($report, $filters);

        $this->assertStringContainsString('Laporan Desain SIMENAK', $csv);
        $this->assertStringContainsString('Antrian Desain Aktif', $csv);
        $this->assertStringContainsString('ORD-TEST-001', $csv);
        $this->assertStringContainsString('Total Draft Diupload', $csv);
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
    }
}
