<?php

namespace App\Commands;

use App\Models\ActivityLogModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanupActivityLogs extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'cleanup-activity-logs';
    protected $description = 'Hapus riwayat aktivitas di luar jendela retensi (awal bulan atau 7 hari ke belakang)';
    protected $usage       = 'cleanup-activity-logs';

    public function run(array $params): void
    {
        helper('activity_log');

        try {
            $cutoff = activityLogRetentionCutoffDate();
            $deleted = model(ActivityLogModel::class)->purgeOutsideRetentionWindow();
            CLI::write(
                "Selesai. Cutoff retensi: {$cutoff}. Baris dihapus: {$deleted}.",
                'green'
            );
        } catch (\Throwable $e) {
            log_message('error', '[CleanupActivityLogs] {msg}', ['msg' => $e->getMessage()]);
            CLI::error('Error: ' . $e->getMessage());
        }
    }
}
