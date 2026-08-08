<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;

class ActivityLogController extends BaseController
{
    public function index()
    {
        if ((string) session()->get('role') !== 'owner') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Akses ditolak.');
        }

        helper('activity_log');

        $cutoff = activityLogRetentionCutoffDate();
        $today  = date('Y-m-d');

        $dari   = trim((string) $this->request->getGet('dari'));
        $sampai = trim((string) $this->request->getGet('sampai'));

        // Clamp input filter ke jendela retensi (dan tidak melewati hari ini).
        if ($dari !== '' && $dari < $cutoff) {
            $dari = $cutoff;
        }
        if ($dari !== '' && $dari > $today) {
            $dari = $today;
        }
        if ($sampai !== '' && $sampai < $cutoff) {
            $sampai = $cutoff;
        }
        if ($sampai !== '' && $sampai > $today) {
            $sampai = $today;
        }

        $filters = [
            'dari'   => $dari,
            'sampai' => $sampai,
            'modul'  => trim((string) $this->request->getGet('modul')),
            'cari'   => trim((string) $this->request->getGet('cari')),
        ];

        $logs = model(ActivityLogModel::class)->getFiltered($filters);

        return view('activity_log/index', [
            'title'         => 'Riwayat Aktivitas',
            'page_title'    => 'Riwayat Aktivitas',
            'logs'          => $logs,
            'filters'       => $filters,
            'modulOptions'  => activityLogModulOptions(),
            'aksiLabels'    => activityLogAksiLabels(),
            'retentionFrom' => $cutoff,
            'retentionTo'   => $today,
        ]);
    }
}
