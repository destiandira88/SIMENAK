<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

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
            'no_telp' => 'required|max_length[20]',
            'alamat'  => 'required|min_length[10]|max_length[150]',
        ];

        $messages = pelangganAkunValidationMessages();

        if (!$this->validate($rules, $messages)) {
            return $this->backProfilModal()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $nama   = trim((string) $this->request->getPost('nama'));
        $noTelp = trim((string) $this->request->getPost('no_telp'));
        $alamat = trim((string) $this->request->getPost('alamat'));

        if ($formatError = validatePelangganAkunFormat($nama, $noTelp)) {
            return $this->backProfilModal()
                ->withInput()
                ->with('error', $formatError);
        }

        $idUser      = (int) session()->get('id_user');
        $idPelanggan = (int) session()->get('id_pelanggan');
        $db          = \Config\Database::connect();

        try {
            $db->transStart();

            $db->table('users')->where('id_user', $idUser)->update([
                'nama' => $nama,
            ]);

            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                'no_telp' => $noTelp,
                'alamat'  => $this->truncateAlamat($alamat),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menyimpan profil.');
            }

            session()->set('nama', $nama);
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

    public function pengguna(): RedirectResponse|string
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        $db         = \Config\Database::connect();
        $segment    = (string) ($this->request->getGet('segment') ?? 'semua');
        $jenisAkun  = (string) ($this->request->getGet('jenis') ?? '');

        if (!in_array($segment, ['semua', 'pelanggan', 'internal'], true)) {
            $segment = 'semua';
        }

        $builder = $db->table('users u')
            ->select('u.id_user, u.nama, u.email, u.role, u.is_active, u.created_at, p.id_pelanggan, p.no_telp, p.alamat, p.jenis, p.is_verified, p.nama_perusahaan')
            ->join('pelanggan p', 'p.id_user = u.id_user', 'left')
            ->orderBy('u.created_at', 'DESC');

        if ($segment === 'pelanggan') {
            $builder->where('u.role', 'pelanggan');
        } elseif ($segment === 'internal') {
            $builder->whereIn('u.role', ['admin', 'keuangan', 'produksi', 'owner']);
        }

        if (in_array($jenisAkun, ['perseorangan', 'kerjasama'], true) && $segment !== 'internal') {
            $builder->where('u.role', 'pelanggan');

            if ($jenisAkun === 'kerjasama') {
                $builder->where('p.jenis', 'perusahaan')
                    ->where('p.is_verified', 1);
            } else {
                $builder->groupStart()
                    ->where('p.jenis !=', 'perusahaan')
                    ->orWhere('p.is_verified', 0)
                    ->orWhere('p.is_verified IS NULL', null, false)
                    ->groupEnd();
            }
        }

        $users = $builder->get()->getResultArray();
        $viewerRole = (string) session()->get('role');

        $countSemua     = (int) $db->table('users')->countAllResults();
        $countPelanggan = (int) $db->table('users')->where('role', 'pelanggan')->countAllResults();
        $countInternal  = (int) $db->table('users')
            ->whereIn('role', ['admin', 'keuangan', 'produksi', 'owner'])
            ->countAllResults();

        $pelangganIds = array_values(array_filter(array_map(
            static fn (array $row): int => (int) ($row['id_pelanggan'] ?? 0),
            $users
        )));
        $pelangganActiveOrdersMap = pelanggansWithActiveOrdersMap($pelangganIds);

        return view('profil/pengguna', [
            'title'                    => 'Pengguna',
            'page_title'               => 'Manajemen Pengguna',
            'users'                    => $users,
            'filterSegment'            => $segment,
            'filterJenis'              => $jenisAkun,
            'countSemua'               => $countSemua,
            'countPelanggan'           => $countPelanggan,
            'countInternal'            => $countInternal,
            'viewerRole'               => $viewerRole,
            'canCreateStaff'           => $viewerRole === 'owner',
            'canCreatePelanggan'        => $viewerRole === 'admin',
            'canManagePelanggan'       => $viewerRole === 'admin',
            'pelangganActiveOrdersMap' => $pelangganActiveOrdersMap,
        ]);
    }

    public function simpanPengguna(): RedirectResponse|ResponseInterface
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        $accountType = (string) ($this->request->getPost('account_type') ?? 'pelanggan');
        if (!in_array($accountType, ['pelanggan', 'staff'], true)) {
            $accountType = 'pelanggan';
        }

        if ($accountType === 'staff') {
            if ((string) session()->get('role') !== 'owner') {
                return $this->forbiddenResponse();
            }

            return $this->storeStaffInternal();
        }

        if ($deny = $this->denyUnlessAdminPelangganMutation()) {
            return $deny;
        }

        return $this->storePelangganByAdmin();
    }

    public function updatePelanggan(int $idPelanggan): RedirectResponse|ResponseInterface
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        if ($deny = $this->denyUnlessAdminPelangganMutation()) {
            return $deny;
        }

        $pelanggan = $this->getPelangganWithUser($idPelanggan);
        if ($pelanggan === null) {
            return redirect()->to(site_url('pengguna'))
                ->with('error', 'Pelanggan tidak ditemukan.');
        }

        $idUser = (int) ($pelanggan['id_user_pelanggan'] ?? 0);
        $rules  = pelangganAkunValidationRules($idUser);

        if (!$this->validate($rules, pelangganAkunValidationMessages())) {
            return redirect()->to(site_url('pengguna'))
                ->withInput()
                ->with('open_edit_pelanggan', $idPelanggan)
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $nama   = trim((string) $this->request->getPost('nama'));
        $email  = trim((string) $this->request->getPost('email'));
        $noTelp = trim((string) $this->request->getPost('no_telp'));
        $alamat = trim((string) $this->request->getPost('alamat'));

        if ($formatError = validatePelangganAkunFormat($nama, $noTelp)) {
            return redirect()->to(site_url('pengguna'))
                ->withInput()
                ->with('open_edit_pelanggan', $idPelanggan)
                ->with('error', $formatError);
        }

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            $db->table('users')->where('id_user', $idUser)->update([
                'nama'  => $nama,
                'email' => $email,
            ]);

            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                'no_telp' => $noTelp,
                'alamat'  => $this->truncateAlamat($alamat),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal memperbarui pelanggan.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::updatePelanggan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->to(site_url('pengguna'))
                ->withInput()
                ->with('open_edit_pelanggan', $idPelanggan)
                ->with('error', 'Gagal memperbarui data pelanggan.');
        }

        return redirect()->to(site_url('pengguna'))
            ->with('success', 'Data pelanggan "' . $nama . '" berhasil diperbarui.');
    }

    public function toggleStatusPelanggan(int $idPelanggan): RedirectResponse|ResponseInterface
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        if ($deny = $this->denyUnlessAdminPelangganMutation()) {
            return $deny;
        }

        $viewerRole = (string) session()->get('role');
        if (!$this->canViewerTogglePelanggan($viewerRole)) {
            return $this->forbiddenResponse();
        }

        $pelanggan = $this->getPelangganWithUser($idPelanggan);
        if ($pelanggan === null) {
            return redirect()->to(site_url('pengguna'))
                ->with('error', 'Pelanggan tidak ditemukan.');
        }

        $idUser   = (int) ($pelanggan['id_user_pelanggan'] ?? 0);
        $user     = $this->getUserById($idUser);
        if ($user === null) {
            return redirect()->to(site_url('pengguna'))
                ->with('error', 'Akun pelanggan tidak ditemukan.');
        }

        $isActive    = (int) ($user['is_active'] ?? 1) === 1;
        $newIsActive = $isActive ? 0 : 1;
        $nama        = (string) ($user['nama'] ?? 'Pelanggan');

        if ($newIsActive === 0 && pelangganHasActiveOrders($idPelanggan)) {
            return redirect()->to(site_url('pengguna'))
                ->with('error', 'Akun "' . $nama . '" tidak dapat dinonaktifkan karena masih memiliki pesanan aktif. Tunggu hingga semua pesanan selesai atau dibatalkan.');
        }

        \Config\Database::connect()->table('users')->where('id_user', $idUser)->update([
            'is_active' => $newIsActive,
        ]);

        $statusLabel = $newIsActive ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->to(site_url('pengguna'))
            ->with('success', 'Akun pelanggan "' . $nama . '" berhasil ' . $statusLabel . '.');
    }

    public function toggleStatusStaff(int $idUser): RedirectResponse|ResponseInterface
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        if ($deny = $this->denyUnlessOwnerStaffMutation()) {
            return $deny;
        }

        $viewerRole = (string) session()->get('role');
        $user       = $this->getUserById($idUser);

        if ($user === null) {
            return redirect()->to(site_url('pengguna'))
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $targetRole = (string) ($user['role'] ?? '');

        if (!$this->canViewerToggleStaff($viewerRole, $targetRole)) {
            return $this->forbiddenResponse();
        }

        $isActive    = (int) ($user['is_active'] ?? 1) === 1;
        $newIsActive = $isActive ? 0 : 1;
        $db          = \Config\Database::connect();

        $db->table('users')->where('id_user', $idUser)->update([
            'is_active' => $newIsActive,
        ]);

        $nama        = (string) ($user['nama'] ?? 'Staff');
        $statusLabel = $newIsActive ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->to(site_url('pengguna'))
            ->with('success', 'Akun "' . $nama . '" berhasil ' . $statusLabel . '.');
    }

    public function updateStaffInternal(int $idUser): RedirectResponse|ResponseInterface
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        if ($deny = $this->denyUnlessOwnerStaffMutation()) {
            return $deny;
        }

        $user = $this->getUserById($idUser);
        if ($user === null) {
            return redirect()->to(site_url('pengguna'))
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $targetRole = (string) ($user['role'] ?? '');
        if (!$this->canViewerEditStaff($targetRole)) {
            return $this->forbiddenResponse();
        }

        $rules = [
            'nama'       => 'required|min_length[3]|max_length[100]',
            'email'      => 'required|valid_email|is_unique[users.email,id_user,' . $idUser . ']',
            'staff_role' => 'required|in_list[admin,keuangan,produksi]',
        ];

        if (!$this->validate($rules, $this->penggunaValidationMessages())) {
            return redirect()->to(site_url('pengguna'))
                ->withInput()
                ->with('open_edit_staff', $idUser)
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $nama      = trim((string) $this->request->getPost('nama'));
        $email     = trim((string) $this->request->getPost('email'));
        $staffRole = (string) $this->request->getPost('staff_role');

        if (!isValidNamaLengkap($nama)) {
            return redirect()->to(site_url('pengguna'))
                ->withInput()
                ->with('open_edit_staff', $idUser)
                ->with('error', 'Nama lengkap tidak valid (3–100 karakter, huruf/spasi/titik/kutip).');
        }

        if (!in_array($staffRole, ['admin', 'keuangan', 'produksi'], true)) {
            return redirect()->to(site_url('pengguna'))
                ->withInput()
                ->with('open_edit_staff', $idUser)
                ->with('error', 'Peran staff tidak valid.');
        }

        \Config\Database::connect()->table('users')->where('id_user', $idUser)->update([
            'nama'  => $nama,
            'email' => $email,
            'role'  => $staffRole,
        ]);

        return redirect()->to(site_url('pengguna'))
            ->with('success', 'Data staff "' . $nama . '" berhasil diperbarui.');
    }

    public function tetapkanKerjasamaPerusahaan(int $idPelanggan): RedirectResponse
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        if ($deny = $this->denyUnlessAdminPelangganMutation()) {
            return $deny;
        }

        $pelanggan = $this->getPelangganWithUser($idPelanggan);
        if ($pelanggan === null) {
            return redirect()->back()->with('error', 'Pelanggan tidak ditemukan.');
        }

        if (pelangganIsKerjasamaPerusahaan($pelanggan)) {
            return redirect()->back()->with('info', 'Pelanggan sudah memiliki status kerja sama perusahaan.');
        }

        $rules = [
            'nama_perusahaan' => 'required|min_length[3]|max_length[150]',
            'jabatan_pic'     => 'required|min_length[2]|max_length[100]',
            'wa_perusahaan'   => 'required|max_length[20]',
            'alamat_kantor'   => 'required|min_length[10]|max_length[255]',
            'no_npwp'         => 'permit_empty|max_length[20]',
            'catatan_admin'   => 'permit_empty|max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $namaPerusahaan = trim((string) $this->request->getPost('nama_perusahaan'));
        if (!isValidNamaPerusahaan($namaPerusahaan)) {
            return redirect()->back()->withInput()->with('error', 'Nama perusahaan tidak valid.');
        }

        $waPerusahaan = trim((string) $this->request->getPost('wa_perusahaan'));
        if (!isValidNoTelepon($waPerusahaan)) {
            return redirect()->back()->withInput()->with('error', 'Format no. HP/WA perusahaan harus berupa angka dan diawali dengan 08, +62, atau 022 (8–13 digit setelah awalan).');
        }

        $noNpwpRaw = trim((string) $this->request->getPost('no_npwp'));
        $noNpwp    = null;
        if ($noNpwpRaw !== '') {
            $noNpwp = normalizeNpwp($noNpwpRaw);
            if ($noNpwp === null) {
                return redirect()->back()->withInput()->with('error', 'Format NPWP tidak valid.');
            }
        }

        $uploads = $this->processKerjasamaUploads(false);
        if (!$uploads['success']) {
            return redirect()->back()->withInput()->with('error', $uploads['message']);
        }

        $idAdmin = (int) session()->get('id_user');
        $db      = \Config\Database::connect();

        try {
            $db->transStart();

            $db->table('verifikasi_perusahaan')->insert([
                'id_pelanggan'    => $idPelanggan,
                'nama_perusahaan' => $namaPerusahaan,
                'no_npwp'         => $noNpwp,
                'jabatan_pic'     => trim((string) $this->request->getPost('jabatan_pic')),
                'wa_perusahaan'   => $waPerusahaan,
                'alamat_kantor'   => trim((string) $this->request->getPost('alamat_kantor')),
                'dokumen_npwp'    => $uploads['dokumen_npwp'],
                'dokumen_ktp_pic' => $uploads['dokumen_ktp_pic'],
                'dokumen_mou'     => $uploads['dokumen_mou'],
                'status'          => 'verified',
                'catatan_admin'   => trim((string) $this->request->getPost('catatan_admin')) ?: null,
                'tgl_pengajuan'   => date('Y-m-d H:i:s'),
                'tgl_verifikasi'  => date('Y-m-d H:i:s'),
                'id_admin'        => $idAdmin,
            ]);

            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                'jenis'           => 'perusahaan',
                'is_verified'     => 1,
                'is_suspended'    => 0,
                'nama_perusahaan' => $namaPerusahaan,
                'alamat'          => mb_substr(trim((string) $this->request->getPost('alamat_kantor')), 0, 150),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menyimpan kerja sama perusahaan.');
            }

            sendNotifEmail(
                (string) $pelanggan['email'],
                '[No-Reply] Status Kerja Sama Perusahaan Aktif',
                '<p>Halo <strong>' . esc((string) $pelanggan['nama']) . '</strong>,</p>'
                . '<p>Akun Anda ditetapkan sebagai pelanggan <strong>Kerja Sama Perusahaan</strong> '
                . '(<strong>' . esc($namaPerusahaan) . '</strong>).</p>'
                . '<p>Order hingga Rp 5.000.000 tanpa DP (pelunasan setelah barang diterima). '
                . 'Order di atas Rp 5.000.000 wajib DP 50%, sisa pelunasan tetap setelah barang diterima.</p>'
            );
            sendNotifWaForEmail(
                $db,
                (string) $pelanggan['email'],
                buildNotifWaText(
                    'Kerja Sama Perusahaan Aktif',
                    "Akun Anda ditetapkan sebagai pelanggan kerja sama ({$namaPerusahaan}).",
                    site_url('order')
                )
            );
            sendNotifInApp(
                (int) $pelanggan['id_user_pelanggan'],
                null,
                'Kerja Sama Perusahaan Aktif',
                "Akun ditetapkan kerja sama perusahaan: {$namaPerusahaan}."
            );
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::tetapkanKerjasamaPerusahaan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal menetapkan kerja sama perusahaan.');
        }

        helper('activity_log');
        logActivity(
            'tambah',
            'kerjasama',
            'Menetapkan kerja sama perusahaan untuk ' . (string) ($pelanggan['nama'] ?? 'pelanggan')
            . ' (' . $namaPerusahaan . ')'
        );

        return redirect()->back()->with('success', 'Pelanggan berhasil ditetapkan sebagai Kerja Sama Perusahaan.');
    }

    public function cabutKerjasamaPerusahaan(int $idPelanggan): RedirectResponse
    {
        if ($deny = $this->denyUnlessPenggunaViewer()) {
            return $deny;
        }

        if ($deny = $this->denyUnlessAdminPelangganMutation()) {
            return $deny;
        }

        $pelanggan = $this->getPelangganWithUser($idPelanggan);
        if ($pelanggan === null || !pelangganIsKerjasamaPerusahaan($pelanggan)) {
            return redirect()->back()->with('error', 'Pelanggan kerja sama perusahaan tidak ditemukan.');
        }

        $catatan = trim((string) $this->request->getPost('catatan_alasan'));
        $db      = \Config\Database::connect();

        try {
            $db->transStart();

            $db->table('verifikasi_perusahaan')->insert([
                'id_pelanggan'    => $idPelanggan,
                'nama_perusahaan' => (string) ($pelanggan['nama_perusahaan'] ?? 'Perusahaan'),
                'status'          => 'rejected',
                'catatan_admin'   => $catatan !== '' ? $catatan : 'Status kerja sama perusahaan dicabut admin.',
                'tgl_pengajuan'   => date('Y-m-d H:i:s'),
                'tgl_verifikasi'  => date('Y-m-d H:i:s'),
                'id_admin'        => (int) session()->get('id_user'),
            ]);

            $db->table('pelanggan')->where('id_pelanggan', $idPelanggan)->update([
                'jenis'           => 'perseorangan',
                'is_verified'     => 0,
                'is_suspended'    => 0,
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal mencabut kerja sama.');
            }

            $namaPerusahaan = (string) ($pelanggan['nama_perusahaan'] ?? 'Perusahaan');
            sendNotifEmail(
                (string) $pelanggan['email'],
                '[No-Reply] Status Kerja Sama Perusahaan Dicabut',
                '<p>Halo <strong>' . esc((string) $pelanggan['nama']) . '</strong>,</p>'
                . '<p>Status kerja sama perusahaan (<strong>' . esc($namaPerusahaan) . '</strong>) telah dicabut.</p>'
                . '<p>Pesanan baru akan diproses dengan skema perseorangan (DP 50%, pelunasan sebelum pengiriman).</p>'
                . ($catatan !== '' ? '<p><strong>Catatan:</strong> ' . esc($catatan) . '</p>' : '')
            );
            sendNotifWaForEmail(
                $db,
                (string) $pelanggan['email'],
                buildNotifWaText(
                    'Kerja Sama Perusahaan Dicabut',
                    "Status kerja sama {$namaPerusahaan} dicabut. Pesanan baru mengikuti skema perseorangan."
                    . ($catatan !== '' ? " Catatan: {$catatan}" : ''),
                    site_url('order')
                )
            );
            sendNotifInApp(
                (int) $pelanggan['id_user_pelanggan'],
                null,
                'Kerja Sama Perusahaan Dicabut',
                "Status kerja sama {$namaPerusahaan} dicabut. Pesanan baru mengikuti skema perseorangan."
            );
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::cabutKerjasamaPerusahaan] {msg}', ['msg' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal mencabut kerja sama perusahaan.');
        }

        helper('activity_log');
        logActivity(
            'hapus',
            'kerjasama',
            'Mencabut kerja sama perusahaan untuk ' . (string) ($pelanggan['nama'] ?? 'pelanggan')
            . ' (' . (string) ($pelanggan['nama_perusahaan'] ?? 'Perusahaan') . ')'
        );

        return redirect()->back()->with('success', 'Status kerja sama perusahaan berhasil dicabut.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getPelangganWithUser(int $idPelanggan): ?array
    {
        $row = \Config\Database::connect()
            ->table('pelanggan p')
            ->select('p.*, u.id_user AS id_user_pelanggan, u.nama, u.email, u.role, u.is_active')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('p.id_pelanggan', $idPelanggan)
            ->where('u.role', 'pelanggan')
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * @return array{success: bool, message: string, dokumen_npwp?: ?string, dokumen_ktp_pic?: ?string, dokumen_mou?: ?string}
     */
    private function processKerjasamaUploads(bool $requireDocs): array
    {
        $fileNpwp = $this->request->getFile('dokumen_npwp');
        $fileKtp  = $this->request->getFile('dokumen_ktp_pic');
        $fileMou  = $this->request->getFile('dokumen_mou');

        $hasNpwp = $fileNpwp !== null && $fileNpwp->isValid() && !$fileNpwp->hasMoved();
        $hasKtp  = $fileKtp !== null && $fileKtp->isValid() && !$fileKtp->hasMoved();
        $hasMou  = $fileMou !== null && $fileMou->isValid() && !$fileMou->hasMoved();

        if ($requireDocs && (!$hasNpwp || !$hasKtp || !$hasMou)) {
            return ['success' => false, 'message' => 'Semua dokumen kerja sama wajib diunggah.'];
        }

        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        $maxSize = 2 * 1024 * 1024;
        $result  = [
            'success'         => true,
            'message'         => '',
            'dokumen_npwp'    => null,
            'dokumen_ktp_pic' => null,
            'dokumen_mou'     => null,
        ];

        foreach (['npwp' => $fileNpwp, 'ktp' => $fileKtp, 'mou' => $fileMou] as $label => $file) {
            if ($file === null || !$file->isValid() || $file->hasMoved()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $allowed, true)) {
                return ['success' => false, 'message' => 'Format dokumen ' . $label . ' tidak valid.'];
            }
            if ($file->getSize() > $maxSize) {
                return ['success' => false, 'message' => 'Ukuran dokumen ' . $label . ' maksimal 2MB.'];
            }
            if ($label === 'mou' && $ext !== 'pdf') {
                return ['success' => false, 'message' => 'Dokumen MOU harus PDF.'];
            }
        }

        try {
            $uploadDir = FCPATH . 'uploads/dokumen_verifikasi/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new \RuntimeException('Folder upload tidak tersedia.');
            }
            $this->ensureUploadIndex($uploadDir);

            if ($hasNpwp) {
                $name = time() . '_npwp_' . $fileNpwp->getClientName();
                $fileNpwp->move($uploadDir, $name);
                $result['dokumen_npwp'] = $name;
            }
            if ($hasKtp) {
                $name = time() . '_ktp_' . $fileKtp->getClientName();
                $fileKtp->move($uploadDir, $name);
                $result['dokumen_ktp_pic'] = $name;
            }
            if ($hasMou) {
                $name = time() . '_mou_' . $fileMou->getClientName();
                $fileMou->move($uploadDir, $name);
                $result['dokumen_mou'] = $name;
            }
        } catch (\Throwable $e) {
            log_message('error', '[ProfilController::processKerjasamaUploads] {msg}', ['msg' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Gagal mengunggah dokumen.'];
        }

        return $result;
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

    private function denyUnlessPenggunaViewer(): ?RedirectResponse
    {
        if (!in_array((string) session()->get('role'), ['admin', 'owner'], true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return null;
    }

    /**
     * Mutasi data pelanggan: hanya Admin (Owner view-only → 403).
     *
     * @return RedirectResponse|ResponseInterface|null
     */
    private function denyUnlessAdminPelangganMutation(): RedirectResponse|ResponseInterface|null
    {
        $role = (string) session()->get('role');

        if ($role === 'owner') {
            return $this->forbiddenResponse();
        }

        if ($role !== 'admin') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return null;
    }

    /**
     * Mutasi akun staff internal: hanya Owner (Admin read-only → 403).
     *
     * @return RedirectResponse|ResponseInterface|null
     */
    private function denyUnlessOwnerStaffMutation(): RedirectResponse|ResponseInterface|null
    {
        $role = (string) session()->get('role');

        if ($role === 'admin') {
            return $this->forbiddenResponse();
        }

        if ($role !== 'owner') {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return null;
    }

    private function forbiddenResponse(): ResponseInterface
    {
        return $this->response->setStatusCode(403)->setBody('Forbidden');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getUserById(int $idUser): ?array
    {
        if ($idUser <= 0) {
            return null;
        }

        $row = \Config\Database::connect()
            ->table('users')
            ->where('id_user', $idUser)
            ->get()
            ->getRowArray();

        return is_array($row) ? $row : null;
    }

    private function canViewerTogglePelanggan(string $viewerRole): bool
    {
        return $viewerRole === 'admin';
    }

    private function canViewerToggleStaff(string $viewerRole, string $targetRole): bool
    {
        if ($targetRole === 'owner' || !in_array($targetRole, ['admin', 'keuangan', 'produksi'], true)) {
            return false;
        }

        return $viewerRole === 'owner';
    }

    private function canViewerEditStaff(string $targetRole): bool
    {
        return in_array($targetRole, ['admin', 'keuangan', 'produksi'], true);
    }

    private function storePelangganByAdmin(): RedirectResponse
    {
        $post         = $this->request->getPost();
        $passwordMode = (string) ($post['password_mode'] ?? 'manual');
        if (!in_array($passwordMode, ['auto', 'manual'], true)) {
            $passwordMode = 'manual';
        }

        $rules = pelangganAkunValidationRules();

        if ($passwordMode === 'manual') {
            $rules['password']         = 'required|min_length[8]';
            $rules['password_confirm'] = 'required|matches[password]';
        }

        if (!$this->validate($rules, pelangganAkunValidationMessages())) {
            return redirect()->back()
                ->withInput()
                ->with('open_tambah_pengguna', true)
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $nama   = trim((string) $post['nama']);
        $email  = trim((string) $post['email']);
        $noTelp = trim((string) $post['no_telp']);
        $alamat = trim((string) $post['alamat']);

        if ($formatError = validatePelangganAkunFormat($nama, $noTelp)) {
            return redirect()->back()
                ->withInput()
                ->with('open_tambah_pengguna', true)
                ->with('error', $formatError);
        }

        $plainPassword = $passwordMode === 'auto'
            ? $this->generateRandomPassword()
            : (string) $post['password'];

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            $db->table('users')->insert([
                'nama'       => $nama,
                'email'      => $email,
                'password'   => password_hash($plainPassword, PASSWORD_DEFAULT),
                'role'       => 'pelanggan',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $idUser = (int) $db->insertID();

            $db->table('pelanggan')->insert([
                'id_user'     => $idUser,
                'no_telp'     => $noTelp,
                'jenis'       => 'perseorangan',
                'is_verified' => 0,
                'alamat'      => $this->truncateAlamat($alamat),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menyimpan pelanggan.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::storePelangganByAdmin] {msg}', ['msg' => $e->getMessage()]);

            $message = 'Gagal menambah pelanggan.';
            if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
                $message = 'Email sudah terdaftar.';
            }

            return redirect()->back()
                ->withInput()
                ->with('open_tambah_pengguna', true)
                ->with('error', $message);
        }

        $redirect = redirect()->to(site_url('pengguna'))
            ->with('success', 'Pelanggan "' . $nama . '" berhasil didaftarkan.');

        if ($passwordMode === 'auto') {
            $redirect = $redirect
                ->with('pengguna_password_generated', $plainPassword)
                ->with('pengguna_password_email', $email)
                ->with('pengguna_password_nama', $nama);
        }

        return $redirect;
    }

    private function storeStaffInternal(): RedirectResponse
    {
        $post     = $this->request->getPost();
        $staffRole = (string) ($post['staff_role'] ?? '');

        if (!in_array($staffRole, ['admin', 'keuangan', 'produksi'], true)) {
            return redirect()->back()
                ->withInput()
                ->with('open_tambah_pengguna', true)
                ->with('error', 'Peran staff tidak valid.');
        }

        $rules = [
            'nama'  => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
        ];

        if (!$this->validate($rules, $this->penggunaValidationMessages())) {
            return redirect()->back()
                ->withInput()
                ->with('open_tambah_pengguna', true)
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $nama = trim((string) $post['nama']);
        $email = trim((string) $post['email']);

        if (!isValidNamaLengkap($nama)) {
            return redirect()->back()
                ->withInput()
                ->with('open_tambah_pengguna', true)
                ->with('error', 'Nama lengkap tidak valid (3–100 karakter, huruf/spasi/titik/kutip).');
        }

        $plainPassword = $this->generateRandomPassword();
        $db            = \Config\Database::connect();

        try {
            $db->transStart();

            $db->table('users')->insert([
                'nama'       => $nama,
                'email'      => $email,
                'password'   => password_hash($plainPassword, PASSWORD_DEFAULT),
                'role'       => $staffRole,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Gagal menyimpan staff.');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ProfilController::storeStaffInternal] {msg}', ['msg' => $e->getMessage()]);

            $message = 'Gagal menambah staff internal.';
            if (str_contains(strtolower($e->getMessage()), 'duplicate')) {
                $message = 'Email sudah terdaftar.';
            }

            return redirect()->back()
                ->withInput()
                ->with('open_tambah_pengguna', true)
                ->with('error', $message);
        }

        return redirect()->to(site_url('pengguna'))
            ->with('success', 'Staff "' . $nama . '" (' . ucfirst($staffRole) . ') berhasil didaftarkan.')
            ->with('pengguna_password_generated', $plainPassword)
            ->with('pengguna_password_email', $email)
            ->with('pengguna_password_nama', $nama);
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $chars  = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $max    = strlen($chars) - 1;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }

        return $result;
    }

    private function truncateAlamat(string $alamat): string
    {
        return mb_substr(trim($alamat), 0, 150);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function penggunaValidationMessages(): array
    {
        return [
            'nama' => [
                'required'   => 'Nama wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.',
                'max_length' => 'Nama maksimal 100 karakter.',
            ],
            'email' => [
                'required'    => 'Email wajib diisi.',
                'valid_email' => 'Format email tidak valid.',
                'is_unique'   => 'Email sudah terdaftar.',
            ],
            'no_telp' => [
                'required'   => 'No. telepon wajib diisi.',
                'max_length' => 'No. telepon terlalu panjang.',
            ],
            'alamat' => [
                'required'   => 'Alamat wajib diisi.',
                'min_length' => 'Alamat minimal 10 karakter.',
                'max_length' => 'Alamat maksimal 150 karakter.',
            ],
            'password' => [
                'required'   => 'Kata sandi wajib diisi.',
                'min_length' => 'Kata sandi minimal 8 karakter.',
            ],
            'password_confirm' => [
                'required' => 'Konfirmasi kata sandi wajib diisi.',
                'matches'  => 'Konfirmasi kata sandi tidak sama.',
            ],
        ];
    }
}
