<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestEmail extends BaseCommand
{
    protected $group       = 'SIMENAK';
    protected $name        = 'test-email';
    protected $description = 'Kirim email uji coba (cek Mailpit di http://localhost:8025)';
    protected $usage       = 'test-email [email]';

    public function run(array $params): void
    {
        helper('notification');

        $to = trim((string) ($params[0] ?? ''));
        if ($to === '' || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Usage: php spark test-email nama@email.com');

            return;
        }

        $config = config('Email');
        CLI::write('SMTP: ' . $config->SMTPHost . ':' . $config->SMTPPort, 'yellow');

        $body = buildNotifEmailHtml(
            'Tes Email SIMENAK',
            '<p>Ini email uji coba dari perintah <code>php spark test-email</code>.</p>'
            . '<p>Jika Mailpit aktif, buka <a href="http://localhost:8025">http://localhost:8025</a>.</p>',
            site_url('/'),
            'Buka SIMENAK'
        );

        $sent = sendNotifEmail($to, 'Tes Email-SIMENAK Z\'Plack', $body);

        if ($sent) {
            CLI::write('Email terkirim ke ' . $to, 'green');
            CLI::write('Buka inbox Mailpit: http://localhost:8025', 'cyan');
        } else {
            CLI::error('Gagal mengirim. Pastikan Mailpit jalan: scripts\\start-mailpit.bat');
            CLI::write('Lihat writable/logs/log-' . date('Y-m-d') . '.log', 'yellow');
        }
    }
}
