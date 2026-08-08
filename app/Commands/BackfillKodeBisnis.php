<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackfillKodeBisnis extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'backfill-kode-bisnis';
    protected $description = 'Isi kode_user/kode_pelanggan/kode_katalog/kode_revisi/kode_kirim yang masih NULL';
    protected $usage       = 'backfill-kode-bisnis';

    private function pad(int $value, int $width): string
    {
        return str_pad((string) $value, $width, '0', STR_PAD_LEFT);
    }

    public function run(array $params): void
    {
        $db = \Config\Database::connect();

        $updated = [
            'users'      => 0,
            'pelanggan'  => 0,
            'katalog'    => 0,
            'revisi'     => 0,
            'pengiriman' => 0,
        ];

        $db->transStart();

        try {
            // users
            if ($db->fieldExists('kode_user', 'users')) {
                $users = $db->table('users')
                    ->select('id_user')
                    ->groupStart()
                    ->where('kode_user', null)
                    ->orWhere('kode_user', '')
                    ->groupEnd()
                    ->get()
                    ->getResultArray();

                foreach ($users as $row) {
                    $idUser = (int) ($row['id_user'] ?? 0);
                    $kode   = 'USR-' . $this->pad($idUser, 5);
                    $db->table('users')
                        ->where('id_user', $idUser)
                        ->update(['kode_user' => $kode]);
                    $updated['users']++;
                }
            }

            // pelanggan
            if ($db->fieldExists('kode_pelanggan', 'pelanggan')) {
                $pelanggans = $db->table('pelanggan')
                    ->select('id_pelanggan')
                    ->groupStart()
                    ->where('kode_pelanggan', null)
                    ->orWhere('kode_pelanggan', '')
                    ->groupEnd()
                    ->get()
                    ->getResultArray();

                foreach ($pelanggans as $row) {
                    $idPelanggan = (int) ($row['id_pelanggan'] ?? 0);
                    $kode         = 'PLG-' . $this->pad($idPelanggan, 5);
                    $db->table('pelanggan')
                        ->where('id_pelanggan', $idPelanggan)
                        ->update(['kode_pelanggan' => $kode]);
                    $updated['pelanggan']++;
                }
            }

            // katalog
            if ($db->fieldExists('kode_katalog', 'katalog')) {
                $katalogs = $db->table('katalog')
                    ->select('id_katalog')
                    ->groupStart()
                    ->where('kode_katalog', null)
                    ->orWhere('kode_katalog', '')
                    ->groupEnd()
                    ->get()
                    ->getResultArray();

                foreach ($katalogs as $row) {
                    $idKatalog = (int) ($row['id_katalog'] ?? 0);
                    $kode      = 'PRD-' . $this->pad($idKatalog, 3);
                    $db->table('katalog')
                        ->where('id_katalog', $idKatalog)
                        ->update(['kode_katalog' => $kode]);
                    $updated['katalog']++;
                }
            }

            // revisi_desain
            if ($db->fieldExists('kode_revisi', 'revisi_desain')) {
                $revisis = $db->table('revisi_desain rd')
                    ->select('rd.id_revisi, rd.id_order, rd.versi, o.kode_order')
                    ->join('orders o', 'o.id_order = rd.id_order', 'left')
                    ->groupStart()
                    ->where('rd.kode_revisi', null)
                    ->orWhere('rd.kode_revisi', '')
                    ->groupEnd()
                    ->get()
                    ->getResultArray();

                foreach ($revisis as $row) {
                    $idRevisi = (int) ($row['id_revisi'] ?? 0);
                    $versi    = (int) ($row['versi'] ?? 0);
                    $kodeOrder = (string) ($row['kode_order'] ?? '');

                    if ($kodeOrder === '') {
                        // Kalau relasi order tidak ada (data rusak), skip saja.
                        continue;
                    }

                    $kode = 'REV-' . $kodeOrder . '-V' . $versi;
                    $db->table('revisi_desain')
                        ->where('id_revisi', $idRevisi)
                        ->update(['kode_revisi' => $kode]);
                    $updated['revisi']++;
                }
            }

            // pengiriman
            if ($db->fieldExists('kode_kirim', 'pengiriman')) {
                $kirimRows = $db->table('pengiriman p')
                    ->select('p.id_order, p.kode_kirim, o.kode_order')
                    ->join('orders o', 'o.id_order = p.id_order', 'left')
                    ->groupStart()
                    ->where('p.kode_kirim', null)
                    ->orWhere('p.kode_kirim', '')
                    ->groupEnd()
                    ->get()
                    ->getResultArray();

                foreach ($kirimRows as $row) {
                    $idOrder = (int) ($row['id_order'] ?? 0);
                    $kodeOrder = (string) ($row['kode_order'] ?? '');

                    if ($kodeOrder === '') {
                        continue;
                    }

                    $kode = 'KRM-' . $kodeOrder;
                    $db->table('pengiriman')
                        ->where('id_order', $idOrder)
                        ->update(['kode_kirim' => $kode]);
                    $updated['pengiriman']++;
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \RuntimeException('Backfill kode gagal.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            CLI::error('[BackfillKodeBisnis] Error: ' . $e->getMessage());
            return;
        }

        CLI::write('Backfill selesai:', 'green');
        CLI::write('users: ' . $updated['users'], 'yellow');
        CLI::write('pelanggan: ' . $updated['pelanggan'], 'yellow');
        CLI::write('katalog: ' . $updated['katalog'], 'yellow');
        CLI::write('revisi: ' . $updated['revisi'], 'yellow');
        CLI::write('pengiriman: ' . $updated['pengiriman'], 'yellow');
    }
}

