<?php

namespace App\Controllers;

use App\Models\PelangganModel;
use CodeIgniter\HTTP\RedirectResponse;

class ProfilController extends BaseController
{
    protected $helpers = ['form', 'url', 'notification'];

    public function index(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        return redirect()->to(site_url('dashboard?profil=1'));
    }

    public function update(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $rules = [
            'nama'    => 'required|min_length[3]|max_length[100]',
            'no_telp' => 'required|regex_match[/^[0-9]{10,13}$/]',
            'alamat'  => 'permit_empty|max_length[150]',
        ];

        if (!$this->validate($rules)) {
            return $this->backProfilModal()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $idUser      = (int) session()->get('id_user');
        $idPelanggan = (int) session()->get('id_pelanggan');
        $db          = \Config\Database::connect();

        try {
            $db->transStart();

            $db->table('users')->where('id_user', $idUser)->update([
                'nama' => trim((string) $this->request->getPost('nama')),
            ]);

            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                'no_telp' => trim((string) $this->request->getPost('no_telp')),
                'alamat'  => trim((string) $this->request->getPost('alamat')),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menyimpan profil.');
            }

            session()->set('nama', trim((string) $this->request->getPost('nama')));
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::update] {msg}', ['msg' => $e->getMessage()]);

            return $this->backProfilModal()
                ->withInput()
                ->with('error', 'Gagal memperbarui profil.');
        }

        return $this->backProfilModal()
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function verifikasiPerusahaan(): RedirectResponse
    {
        if ((string) session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $rules = [
            'nama_perusahaan' => 'required|min_length[3]|max_length[150]',
            'no_npwp'         => 'permit_empty|max_length[20]',
            'jabatan_pic'     => 'required|min_length[2]|max_length[100]',
            'wa_perusahaan'   => 'required|regex_match[/^[0-9]{10,13}$/]',
            'alamat_kantor'   => 'required|min_length[10]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->backProfilModal()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $idPelanggan = (int) session()->get('id_pelanggan');
        $db          = \Config\Database::connect();

        $pelanggan = $db->table('pelanggan')
            ->where('id_pelanggan', $idPelanggan)
            ->get()
            ->getRowArray();

        if ($pelanggan === null) {
            return $this->backProfilModal()->with('error', 'Data pelanggan tidak ditemukan.');
        }

        if ((int) ($pelanggan['is_verified'] ?? 0) === 1) {
            return $this->backProfilModal()->with('info', 'Akun Anda sudah terverifikasi sebagai perusahaan.');
        }

        $pending = $db->table('verifikasi_perusahaan')
            ->where('id_pelanggan', $idPelanggan)
            ->where('status', 'pending')
            ->countAllResults();

        if ($pending > 0) {
            return $this->backProfilModal()->with('warning', 'Pengajuan verifikasi masih menunggu tinjauan admin.');
        }

        $fileNpwp = $this->request->getFile('dokumen_npwp');
        $fileKtp  = $this->request->getFile('dokumen_ktp_pic');
        $fileMou  = $this->request->getFile('dokumen_mou');

        if ($fileNpwp === null || !$fileNpwp->isValid()) {
            return $this->backProfilModal()->with('error', 'Dokumen NPWP wajib diunggah.');
        }

        if ($fileKtp === null || !$fileKtp->isValid()) {
            return $this->backProfilModal()->with('error', 'Dokumen KTP PIC wajib diunggah.');
        }

        if ($fileMou === null || !$fileMou->isValid()) {
            return $this->backProfilModal()->with('error', 'Dokumen MOU / surat kerjasama wajib diunggah.');
        }

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $maxSize = 2 * 1024 * 1024;

        foreach (['npwp' => $fileNpwp, 'ktp' => $fileKtp, 'mou' => $fileMou] as $label => $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $allowed, true)) {
                return $this->backProfilModal()->with('error', 'Format dokumen ' . $label . ' tidak valid. Gunakan JPG, PNG, atau PDF.');
            }
            if ($file->getSize() > $maxSize) {
                return $this->backProfilModal()->with('error', 'Ukuran dokumen ' . $label . ' maksimal 2MB.');
            }
        }

        if (strtolower($fileMou->getExtension()) !== 'pdf') {
            return $this->backProfilModal()->with('error', 'Dokumen MOU harus berformat PDF.');
        }

        $namaPerusahaan = trim((string) $this->request->getPost('nama_perusahaan'));
        $jabatanPic     = trim((string) $this->request->getPost('jabatan_pic'));
        $waPerusahaan   = trim((string) $this->request->getPost('wa_perusahaan'));
        $alamatKantor   = trim((string) $this->request->getPost('alamat_kantor'));
        $noNpwpRaw      = trim((string) $this->request->getPost('no_npwp'));
        $noNpwp         = null;

        if ($noNpwpRaw !== '') {
            $noNpwp = normalizeNpwp($noNpwpRaw);
            if ($noNpwp === null) {
                return $this->backProfilModal()
                    ->withInput()
                    ->with('error', 'Format NPWP tidak valid. Masukkan 15 digit angka, contoh: 12.345.678.9-012.345.');
            }
        }

        $namaPelanggan  = (string) session()->get('nama');

        try {
            $uploadDir = FCPATH . 'uploads/dokumen_verifikasi/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new \RuntimeException('Folder upload tidak tersedia.');
            }

            $this->ensureUploadIndex($uploadDir);

            $npwpName = time() . '_npwp_' . $fileNpwp->getClientName();
            $ktpName  = time() . '_ktp_' . $fileKtp->getClientName();
            $mouName  = time() . '_mou_' . $fileMou->getClientName();
            $fileNpwp->move($uploadDir, $npwpName);
            $fileKtp->move($uploadDir, $ktpName);
            $fileMou->move($uploadDir, $mouName);

            $db->transStart();

            $db->table('verifikasi_perusahaan')->insert([
                'id_pelanggan'    => $idPelanggan,
                'nama_perusahaan' => $namaPerusahaan,
                'no_npwp'         => $noNpwp,
                'jabatan_pic'     => $jabatanPic,
                'wa_perusahaan'   => $waPerusahaan,
                'alamat_kantor'   => $alamatKantor,
                'dokumen_npwp'    => $npwpName,
                'dokumen_ktp_pic' => $ktpName,
                'dokumen_mou'     => $mouName,
                'status'          => 'pending',
                'tgl_pengajuan'   => date('Y-m-d H:i:s'),
            ]);

            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                'nama_perusahaan' => $namaPerusahaan,
            ]);

            $adminUsers = $db->table('users')->where('role', 'admin')->get()->getResultArray();
            $verifUrl   = site_url('verifikasi-perusahaan');

            foreach ($adminUsers as $admin) {
                sendNotifEmail(
                    (string) $admin['email'],
                    'Pengajuan Verifikasi Perusahaan Baru',
                    '<p>Halo <strong>' . esc((string) $admin['nama']) . '</strong>,</p>'
                    . '<p>Pelanggan <strong>' . esc($namaPelanggan) . '</strong> mengajukan verifikasi perusahaan '
                    . '<strong>' . esc($namaPerusahaan) . '</strong>.</p>'
                    . '<p><a href="' . esc($verifUrl) . '">Buka halaman verifikasi perusahaan</a></p>'
                );

                sendNotifInApp(
                    (int) $admin['id_user'],
                    null,
                    'Pengajuan Verifikasi Perusahaan',
                    "{$namaPelanggan} mengajukan verifikasi perusahaan {$namaPerusahaan}."
                );
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menyimpan pengajuan verifikasi.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::verifikasiPerusahaan] {msg}', ['msg' => $e->getMessage()]);

            return $this->backProfilModal()->with('error', 'Gagal mengirim pengajuan verifikasi.');
        }

        return $this->backProfilModal()
            ->with('success', 'Pengajuan verifikasi perusahaan berhasil dikirim. Menunggu tinjauan admin.');
    }

    public function verifikasiPerusahaanList(): RedirectResponse|string
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $db     = \Config\Database::connect();
        $filter = (string) ($this->request->getGet('status') ?? 'pending');

        $builder = $db->table('verifikasi_perusahaan vp')
            ->select(
                'vp.*, u.nama AS nama_pelanggan, u.email AS email_pelanggan, '
                . 'p.no_telp, p.is_verified, admin.nama AS nama_admin'
            )
            ->join('pelanggan p', 'p.id_pelanggan = vp.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->join('users admin', 'admin.id_user = vp.id_admin', 'left')
            ->orderBy('vp.tgl_pengajuan', 'DESC');

        if (in_array($filter, ['pending', 'verified', 'rejected'], true)) {
            $builder->where('vp.status', $filter);
        }

        $pengajuan = $builder->get()->getResultArray();

        $counts = [
            'pending'  => $db->table('verifikasi_perusahaan')->where('status', 'pending')->countAllResults(),
            'verified' => $db->table('verifikasi_perusahaan')->where('status', 'verified')->countAllResults(),
            'rejected' => $db->table('verifikasi_perusahaan')->where('status', 'rejected')->countAllResults(),
        ];

        return view('profil/verifikasi_perusahaan_list', [
            'title'      => 'Verifikasi Perusahaan',
            'page_title' => 'Verifikasi Perusahaan',
            'pengajuan'  => $pengajuan,
            'filter'     => $filter,
            'counts'     => $counts,
        ]);
    }

    public function verifikasiPerusahaanProses(int $idVerify): RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $aksi = (string) $this->request->getPost('aksi');
        if (!in_array($aksi, ['acc', 'tolak'], true)) {
            return redirect()->back()->with('error', 'Aksi tidak dikenal.');
        }

        $catatanAdmin = trim((string) $this->request->getPost('catatan_admin'));
        if ($aksi === 'tolak' && $catatanAdmin === '') {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $db       = \Config\Database::connect();
        $idAdmin  = (int) session()->get('id_user');
        $pengajuan = $db->table('verifikasi_perusahaan vp')
            ->select('vp.*, u.id_user AS id_user_pelanggan, u.nama AS nama_pelanggan, u.email AS email_pelanggan')
            ->join('pelanggan p', 'p.id_pelanggan = vp.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('vp.id_verify', $idVerify)
            ->where('vp.status', 'pending')
            ->get()
            ->getRowArray();

        if ($pengajuan === null) {
            return redirect()->back()->with('error', 'Pengajuan verifikasi tidak ditemukan atau sudah diproses.');
        }

        $idPelanggan     = (int) $pengajuan['id_pelanggan'];
        $idUserPelanggan = (int) $pengajuan['id_user_pelanggan'];
        $namaPerusahaan  = (string) $pengajuan['nama_perusahaan'];
        $namaPelanggan   = (string) $pengajuan['nama_pelanggan'];

        try {
            $db->transStart();

            if ($aksi === 'acc') {
                $db->table('verifikasi_perusahaan')->where('id_verify', $idVerify)->update([
                    'status'           => 'verified',
                    'catatan_admin'    => $catatanAdmin !== '' ? $catatanAdmin : null,
                    'tgl_verifikasi'   => date('Y-m-d H:i:s'),
                    'id_admin'         => $idAdmin,
                ]);

                $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                    'is_verified'     => 1,
                    'jenis'           => 'perusahaan',
                    'nama_perusahaan' => $namaPerusahaan,
                    'tier_perusahaan' => 'pemula',
                    'is_suspended'    => 0,
                ]);

                sendNotifEmail(
                    (string) $pengajuan['email_pelanggan'],
                    '[No-Reply] Verifikasi Perusahaan Disetujui',
                    '<p>Halo <strong>' . esc($namaPelanggan) . '</strong>,</p>'
                    . '<p>Pengajuan verifikasi perusahaan <strong>' . esc($namaPerusahaan) . '</strong> telah <strong>disetujui</strong>.</p>'
                    . '<p>Akun perusahaan aktif dengan tier <strong>Pemula</strong>: wajib DP setiap pesanan, pelunasan sebelum pengiriman.</p>'
                    . '<p>Setelah 3 order lancar, admin dapat mempromosikan ke tier Terpercaya (tanpa DP untuk order ≤ Rp 5 juta).</p>'
                );

                sendNotifInApp(
                    $idUserPelanggan,
                    null,
                    'Verifikasi Perusahaan Disetujui',
                    "Verifikasi perusahaan {$namaPerusahaan} disetujui. Tier Pemula aktif."
                );

                $flashMsg = 'Verifikasi perusahaan berhasil disetujui.';
            } else {
                $db->table('verifikasi_perusahaan')->where('id_verify', $idVerify)->update([
                    'status'           => 'rejected',
                    'catatan_admin'    => $catatanAdmin,
                    'tgl_verifikasi'   => date('Y-m-d H:i:s'),
                    'id_admin'         => $idAdmin,
                ]);

                $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                    'is_verified' => 0,
                ]);

                sendNotifEmail(
                    (string) $pengajuan['email_pelanggan'],
                    '[No-Reply] Verifikasi Perusahaan Ditolak',
                    '<p>Halo <strong>' . esc($namaPelanggan) . '</strong>,</p>'
                    . '<p>Pengajuan verifikasi perusahaan <strong>' . esc($namaPerusahaan) . '</strong> <strong>ditolak</strong>.</p>'
                    . '<p><strong>Alasan:</strong> ' . esc($catatanAdmin) . '</p>'
                    . '<p>Silakan perbaiki dokumen dan ajukan ulang melalui halaman Profil.</p>'
                );

                sendNotifInApp(
                    $idUserPelanggan,
                    null,
                    'Verifikasi Perusahaan Ditolak',
                    "Verifikasi perusahaan {$namaPerusahaan} ditolak: {$catatanAdmin}"
                );

                $flashMsg = 'Pengajuan verifikasi perusahaan ditolak.';
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal memproses verifikasi.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::verifikasiPerusahaanProses] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memproses verifikasi perusahaan.');
        }

        return redirect()->to(site_url('verifikasi-perusahaan'))
            ->with($aksi === 'acc' ? 'success' : 'warning', $flashMsg);
    }

    public function pengguna(): RedirectResponse|string
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $db         = \Config\Database::connect();
        $role       = (string) ($this->request->getGet('role') ?? '');
        $verifikasi = (string) ($this->request->getGet('verifikasi') ?? '');

        $builder = $db->table('users u')
            ->select('u.id_user, u.nama, u.email, u.role, u.created_at, p.id_pelanggan, p.no_telp, p.is_verified, p.nama_perusahaan, p.tier_perusahaan, p.is_suspended')
            ->join('pelanggan p', 'p.id_user = u.id_user', 'left')
            ->orderBy('u.created_at', 'DESC');

        if ($role !== '' && in_array($role, ['pelanggan', 'admin', 'keuangan', 'produksi', 'owner'], true)) {
            $builder->where('u.role', $role);
        }

        if (in_array($verifikasi, ['belum', 'pemula', 'terpercaya', 'suspend'], true)) {
            $builder->where('u.role', 'pelanggan');

            match ($verifikasi) {
                'belum' => $builder->groupStart()
                    ->where('p.is_verified', 0)
                    ->orWhere('p.is_verified IS NULL', null, false)
                    ->groupEnd(),
                'pemula' => $builder
                    ->where('p.is_verified', 1)
                    ->where('p.tier_perusahaan', 'pemula')
                    ->where('p.is_suspended', 0),
                'terpercaya' => $builder
                    ->where('p.is_verified', 1)
                    ->where('p.tier_perusahaan', 'terpercaya')
                    ->where('p.is_suspended', 0),
                'suspend' => $builder
                    ->where('p.is_verified', 1)
                    ->where('p.is_suspended', 1),
                default => null,
            };
        }

        $users = $builder->get()->getResultArray();

        return view('profil/pengguna', [
            'title'           => 'Pengguna',
            'page_title'      => 'Manajemen Pengguna',
            'users'           => $users,
            'filterRole'      => $role,
            'filterVerifikasi' => $verifikasi,
        ]);
    }

    public function promosikanTerpercaya(int $idPelanggan): RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        helper('notification');
        $db       = \Config\Database::connect();
        $pelanggan = $db->table('pelanggan p')
            ->select('p.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('p.id_pelanggan', $idPelanggan)
            ->get()
            ->getRowArray();

        if ($pelanggan === null) {
            return redirect()->back()->with('error', 'Pelanggan tidak ditemukan.');
        }

        if (!canPromotePerusahaanToTerpercaya($pelanggan)) {
            $count = countOrderLancarPerusahaan($idPelanggan);

            return redirect()->back()->with('error', "Belum memenuhi syarat promosi ({$count}/3 order lancar).");
        }

        try {
            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                'tier_perusahaan' => 'terpercaya',
            ]);

            $namaPerusahaan = (string) ($pelanggan['nama_perusahaan'] ?? 'Perusahaan');
            sendNotifEmail(
                (string) $pelanggan['email'],
                '[No-Reply] Tier Perusahaan Diperbarui-Terpercaya',
                '<p>Halo <strong>' . esc((string) $pelanggan['nama']) . '</strong>,</p>'
                . '<p>Akun perusahaan <strong>' . esc($namaPerusahaan) . '</strong> dipromosikan ke tier <strong>Terpercaya</strong>.</p>'
                . '<p>Order ≤ Rp 5.000.000 tanpa DP. Order di atas Rp 5.000.000 tetap wajib DP.</p>'
            );
            sendNotifInApp(
                (int) $pelanggan['id_user_pelanggan'],
                null,
                'Tier Terpercaya Aktif',
                "Perusahaan {$namaPerusahaan} dipromosikan ke tier Terpercaya."
            );
        } catch (\Throwable $e) {
            log_message('error', '[ProfilController::promosikanTerpercaya] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal mempromosikan tier perusahaan.');
        }

        return redirect()->back()->with('success', 'Pelanggan berhasil dipromosikan ke tier Terpercaya.');
    }

    public function toggleSuspend(int $idPelanggan): RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        helper('notification');
        $db        = \Config\Database::connect();
        $pelanggan = $db->table('pelanggan p')
            ->select('p.*, u.id_user AS id_user_pelanggan, u.nama, u.email')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('p.id_pelanggan', $idPelanggan)
            ->where('p.is_verified', 1)
            ->get()
            ->getRowArray();

        if ($pelanggan === null) {
            return redirect()->back()->with('error', 'Pelanggan perusahaan tidak ditemukan.');
        }

        $newSuspended = (int) ($pelanggan['is_suspended'] ?? 0) === 1 ? 0 : 1;

        try {
            $update = ['is_suspended' => $newSuspended];
            if ($newSuspended === 1 && (string) ($pelanggan['tier_perusahaan'] ?? '') === 'terpercaya') {
                $update['tier_perusahaan'] = 'pemula';
            }

            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update($update);

            $namaPerusahaan = (string) ($pelanggan['nama_perusahaan'] ?? 'Perusahaan');
            if ($newSuspended === 1) {
                sendNotifInApp(
                    (int) $pelanggan['id_user_pelanggan'],
                    null,
                    'Akun Perusahaan Disuspend',
                    "Akun perusahaan {$namaPerusahaan} disuspend. Pesanan perusahaan dinonaktifkan sementara."
                );
                $flash = 'Akun perusahaan disuspend.';
            } else {
                sendNotifInApp(
                    (int) $pelanggan['id_user_pelanggan'],
                    null,
                    'Suspend Dicabut',
                    "Akun perusahaan {$namaPerusahaan} kembali aktif (tier Pemula)."
                );
                $flash = 'Suspend dicabut. Tier kembali ke Pemula.';
            }
        } catch (\Throwable $e) {
            log_message('error', '[ProfilController::toggleSuspend] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memperbarui status suspend.');
        }

        return redirect()->back()->with('success', $flash);
    }

    public function demoteTerpercaya(int $idPelanggan): RedirectResponse
    {
        if ((string) session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $db = \Config\Database::connect();
        $pelanggan = $db->table('pelanggan')
            ->where('id_pelanggan', $idPelanggan)
            ->where('is_verified', 1)
            ->where('tier_perusahaan', 'terpercaya')
            ->get()
            ->getRowArray();

        if ($pelanggan === null) {
            return redirect()->back()->with('error', 'Pelanggan tier Terpercaya tidak ditemukan.');
        }

        $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
            'tier_perusahaan' => 'pemula',
        ]);

        return redirect()->back()->with('success', 'Tier perusahaan diturunkan ke Pemula.');
    }

    private function ensureUploadIndex(string $uploadDir): void
    {
        $indexFile = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . 'index.html';
        if (is_file($indexFile)) {
            return;
        }

        file_put_contents($indexFile, '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><p>Forbidden</p></body></html>');
    }

    private function backProfilModal(): RedirectResponse
    {
        return redirect()->back()->with('open_profil_modal', true);
    }
}
