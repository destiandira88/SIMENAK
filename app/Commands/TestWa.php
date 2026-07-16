<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestWa extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'test-wa';
    protected $description = 'Kirim WhatsApp uji coba via Fonnte';
    protected $usage       = 'test-wa [nomor]';

    public function run(array $params): void
    {
        helper('notification');

        $nomor = trim((string) ($params[0] ?? ''));
        if ($nomor === '') {
            CLI::error('Usage: php spark test-wa 081234567890');

            return;
        }

        if (! isFonnteConfigured()) {
            CLI::error('fonnte.token belum diisi di file .env');

            return;
        }

        $pesan = buildNotifWaText(
            'Tes WhatsApp SIMENAK',
            'Ini pesan uji coba dari perintah php spark test-wa.',
            site_url('/')
        );

        $sent = sendNotifWa($nomor, $pesan);

        if ($sent) {
            CLI::write('WhatsApp terkirim ke ' . normalizeNomorWa($nomor), 'green');
        } else {
            CLI::error('Gagal mengirim. Cek fonnte.token dan log di writable/logs/');
        }
    }
}
