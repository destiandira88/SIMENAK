<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Hapus semua data & file hasil automation QA (black-box).
 *
 * Usage: php spark qa:cleanup [--dry-run] [--force]
 */
class QaCleanup extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'qa:cleanup';
    protected $description = 'Hapus data & file QA (@simenak.test, ORD-QA-*, nama/file mengandung qa)';
    protected $usage       = 'qa:cleanup [--dry-run] [--force]';

    private BaseConnection $db;

    /** @var list<int> */
    private array $qaUserIds = [];

    /** @var list<int> */
    private array $qaPelangganIds = [];

    /** @var list<int> */
    private array $qaOrderIds = [];

    /** @var list<int> */
    private array $qaKatalogIds = [];

    /** @var list<string> */
    private array $qaFilePaths = [];

    public function run(array $params): void
    {
        $dryRun = CLI::getOption('dry-run') !== null;
        $force  = CLI::getOption('force') !== null;
        $this->db = \Config\Database::connect();

        $this->collectQaTargets();
        $this->qaFilePaths = $this->collectQaFileTargets();

        CLI::write('=== QA Cleanup' . ($dryRun ? ' (DRY RUN)' : '') . ' ===', 'yellow');
        CLI::write('Users QA      : ' . count($this->qaUserIds));
        CLI::write('Pelanggan QA  : ' . count($this->qaPelangganIds));
        CLI::write('Orders QA     : ' . count($this->qaOrderIds));
        CLI::write('Katalog QA    : ' . count($this->qaKatalogIds));
        CLI::write('File QA       : ' . count($this->qaFilePaths));

        if (
            $this->qaUserIds === []
            && $this->qaOrderIds === []
            && $this->qaKatalogIds === []
            && $this->qaFilePaths === []
        ) {
            CLI::write('Tidak ada data atau file QA yang cocok dengan pola penghapusan.', 'green');

            return;
        }

        if ($dryRun) {
            foreach ($this->qaFilePaths as $path) {
                CLI::write('  [file] ' . $this->displayPath($path), 'cyan');
            }
            CLI::write('Dry run selesai — tidak ada baris/file dihapus.', 'cyan');

            return;
        }

        if (! $force && CLI::prompt('Lanjutkan hapus data & file QA di atas?', ['y', 'n']) !== 'y') {
            CLI::write('Dibatalkan.', 'yellow');

            return;
        }

        try {
            if ($this->qaUserIds !== [] || $this->qaOrderIds !== [] || $this->qaKatalogIds !== []) {
                $this->db->transStart();

                $counts = $this->deleteQaData();

                if ($this->db->transStatus() === false) {
                    $this->db->transRollback();
                    CLI::error('Transaksi database gagal — semua perubahan DB dibatalkan.');

                    return;
                }

                $this->db->transComplete();

                foreach ($counts as $table => $count) {
                    if ($count > 0) {
                        CLI::write(sprintf('%-22s %d', $table . ':', $count), 'green');
                    }
                }
            } else {
                CLI::write('Database: tidak ada baris QA tersisa.', 'cyan');
            }

            $deletedFiles = $this->deleteQaFiles();
            if ($deletedFiles > 0) {
                CLI::write(sprintf('%-22s %d', 'file dihapus:', $deletedFiles), 'green');
            }

            $this->removeEmptyQaWorkspace();

            CLI::write('Selesai — data & file QA dihapus.', 'green');
        } catch (\Throwable $e) {
            if ($this->db->transStatus()) {
                $this->db->transRollback();
            }
            log_message('error', '[QaCleanup] {msg}', ['msg' => $e->getMessage()]);
            CLI::error('Error: ' . $e->getMessage());
        }
    }

    private function collectQaTargets(): void
    {
        $userRows = $this->db->table('users')
            ->select('id_user')
            ->groupStart()
                ->like('email', '@simenak.test', 'before')
                ->orLike('nama', 'QA ', 'after')
            ->groupEnd()
            ->get()
            ->getResultArray();

        $this->qaUserIds = array_map(static fn (array $row): int => (int) $row['id_user'], $userRows);

        if ($this->qaUserIds !== []) {
            $pelRows = $this->db->table('pelanggan')
                ->select('id_pelanggan')
                ->whereIn('id_user', $this->qaUserIds)
                ->get()
                ->getResultArray();
            $this->qaPelangganIds = array_map(static fn (array $row): int => (int) $row['id_pelanggan'], $pelRows);
        }

        $orderBuilder = $this->db->table('orders')->select('id_order');
        $orderBuilder->groupStart()
            ->like('kode_order', 'ORD-QA-', 'after')
            ->orLike('detail_pesanan', 'QA ', 'after');
        if ($this->qaPelangganIds !== []) {
            $orderBuilder->orWhereIn('id_pelanggan', $this->qaPelangganIds);
        }
        $orderBuilder->groupEnd();

        $orderRows = $orderBuilder->get()->getResultArray();
        $this->qaOrderIds = array_map(static fn (array $row): int => (int) $row['id_order'], $orderRows);

        $katRows = $this->db->table('katalog')
            ->select('id_katalog')
            ->like('nama_produk', 'QA ', 'after')
            ->get()
            ->getResultArray();
        $this->qaKatalogIds = array_map(static fn (array $row): int => (int) $row['id_katalog'], $katRows);
    }

    /**
     * @return list<string>
     */
    private function collectQaFileTargets(): array
    {
        $files = [];

        $scanRoots = [
            WRITEPATH . 'qa',
            FCPATH . 'uploads',
        ];

        foreach ($scanRoots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if (! $fileInfo->isFile()) {
                    continue;
                }

                $path = $fileInfo->getPathname();
                if ($this->isQaArtifactPath($path)) {
                    $files[] = $path;
                }
            }
        }

        sort($files);

        return array_values(array_unique($files));
    }

    private function isQaArtifactPath(string $path): bool
    {
        $normalized = str_replace('\\', '/', $path);

        if (str_contains($normalized, '/writable/qa/')) {
            return true;
        }

        return $this->isQaFilename(basename($path));
    }

    private function isQaFilename(string $filename): bool
    {
        return (bool) preg_match('/qa/i', $filename);
    }

    /**
     * @return array<string, int>
     */
    private function deleteQaData(): array
    {
        $counts = [];

        if ($this->qaOrderIds !== []) {
            $counts['notifications (order)'] = $this->deleteWhereIn('notifications', 'id_order', $this->qaOrderIds);
            $counts['order_attributes']      = $this->deleteWhereIn('order_attributes', 'id_order', $this->qaOrderIds);
            $counts['revisi_desain']         = $this->deleteWhereIn('revisi_desain', 'id_order', $this->qaOrderIds);
            $counts['pengiriman']            = $this->deleteWhereIn('pengiriman', 'id_order', $this->qaOrderIds);
            $counts['payments (order)']      = $this->deleteWhereIn('payments', 'id_order', $this->qaOrderIds);
            $counts['orders']                = $this->deleteWhereIn('orders', 'id_order', $this->qaOrderIds);
        }

        if ($this->db->tableExists('payments')) {
            $counts['payments (PAY-QA)'] = $this->deleteLike('payments', 'kode_payment', 'PAY-QA-');
        }

        if ($this->qaUserIds !== []) {
            if ($this->db->tableExists('notifications')) {
                $counts['notifications (user)'] = $this->deleteWhereIn('notifications', 'id_user', $this->qaUserIds);
            }

            if ($this->db->tableExists('activity_logs')) {
                $counts['activity_logs'] = $this->deleteWhereIn('activity_logs', 'id_user', $this->qaUserIds);
            }

            if ($this->db->fieldExists('id_verifikator', 'payments')) {
                $this->db->table('payments')
                    ->whereIn('id_verifikator', $this->qaUserIds)
                    ->update(['id_verifikator' => null]);
            }

            if ($this->db->fieldExists('id_produksi', 'revisi_desain')) {
                $this->db->table('revisi_desain')
                    ->whereIn('id_produksi', $this->qaUserIds)
                    ->update(['id_produksi' => null]);
            }

            if ($this->db->fieldExists('id_admin', 'verifikasi_perusahaan')) {
                $this->db->table('verifikasi_perusahaan')
                    ->whereIn('id_admin', $this->qaUserIds)
                    ->update(['id_admin' => null]);
            }
        }

        if ($this->qaPelangganIds !== []) {
            $counts['verifikasi_perusahaan'] = $this->deleteWhereIn('verifikasi_perusahaan', 'id_pelanggan', $this->qaPelangganIds);
            $counts['pelanggan']             = $this->deleteWhereIn('pelanggan', 'id_pelanggan', $this->qaPelangganIds);
        }

        if ($this->db->tableExists('verifikasi_perusahaan')) {
            $counts['verifikasi_perusahaan (PT QA)'] = $this->deleteLike('verifikasi_perusahaan', 'nama_perusahaan', 'PT QA');
        }

        if ($this->qaKatalogIds !== []) {
            $counts['form_templates'] = $this->deleteWhereIn('form_templates', 'id_katalog', $this->qaKatalogIds);
            $counts['katalog']        = $this->deleteWhereIn('katalog', 'id_katalog', $this->qaKatalogIds);
        }

        if ($this->qaUserIds !== []) {
            $counts['password_resets'] = $this->deleteQaPasswordResets();
            $counts['users']           = $this->deleteWhereIn('users', 'id_user', $this->qaUserIds);
        }

        return $counts;
    }

    private function deleteQaFiles(): int
    {
        $deleted = 0;

        foreach ($this->qaFilePaths as $path) {
            if (! is_file($path)) {
                continue;
            }

            if (@unlink($path)) {
                $deleted++;
                continue;
            }

            CLI::write('Gagal hapus file: ' . $this->displayPath($path), 'red');
        }

        return $deleted;
    }

    private function removeEmptyQaWorkspace(): void
    {
        $qaDir = WRITEPATH . 'qa';
        if (! is_dir($qaDir)) {
            return;
        }

        $this->removeEmptyDirectories($qaDir);

        if ($this->directoryIsEmpty($qaDir)) {
            @rmdir($qaDir);
        }
    }

    private function removeEmptyDirectories(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeEmptyDirectories($path);
                if ($this->directoryIsEmpty($path)) {
                    @rmdir($path);
                }
            }
        }
    }

    private function directoryIsEmpty(string $dir): bool
    {
        $items = scandir($dir);

        return $items !== false && count($items) <= 2;
    }

    private function displayPath(string $path): string
    {
        $root = str_replace('\\', '/', ROOTPATH);
        $normalized = str_replace('\\', '/', $path);

        if (str_starts_with($normalized, $root)) {
            return substr($normalized, strlen($root));
        }

        return $normalized;
    }

    /**
     * @param list<int> $ids
     */
    private function deleteWhereIn(string $table, string $column, array $ids): int
    {
        if ($ids === [] || ! $this->db->tableExists($table)) {
            return 0;
        }

        $this->db->table($table)->whereIn($column, $ids)->delete();

        return (int) $this->db->affectedRows();
    }

    private function deleteLike(string $table, string $column, string $prefix): int
    {
        if (! $this->db->tableExists($table) || ! $this->db->fieldExists($column, $table)) {
            return 0;
        }

        $this->db->table($table)->like($column, $prefix, 'after')->delete();

        return (int) $this->db->affectedRows();
    }

    private function deleteQaPasswordResets(): int
    {
        if (! $this->db->tableExists('password_resets')) {
            return 0;
        }

        $this->db->table('password_resets')
            ->groupStart()
                ->like('email', '@simenak.test', 'before')
                ->orLike('email', 'qa.', 'after')
            ->groupEnd()
            ->delete();

        return (int) $this->db->affectedRows();
    }
}
