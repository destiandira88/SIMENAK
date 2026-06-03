<?php

namespace App\Controllers;

class CustomOrderController extends BaseController
{
    public function redirectToList()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $tab = (string) ($this->request->getGet('tab') ?? 'custom');
        if (!in_array($tab, ['semua', 'custom', 'menunggu-harga'], true)) {
            $tab = 'custom';
        }

        return redirect()->to(site_url('list-pemesanan?tab=' . $tab));
    }

    public function setHarga()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder        = (int) $this->request->getPost('id_order');
        $hargaCustom    = (float) $this->request->getPost('harga_custom');
        $estimasiCustom = $this->request->getPost('estimasi_custom');
        $catatanAdmin   = $this->request->getPost('catatan_admin_custom');

        if ($hargaCustom <= 0) {
            return redirect()->back()->with('error', 'Harga harus lebih dari 0.');
        }

        $db = \Config\Database::connect();
        $order = $db->table('orders o')
            ->select('o.*, u.email, u.nama, u.id_user as id_user_pelanggan')
            ->join('pelanggan p', 'p.id_pelanggan = o.id_pelanggan')
            ->join('users u', 'u.id_user = p.id_user')
            ->where('o.id_order', $idOrder)
            ->where('o.status', 'menunggu_konfirmasi_harga')
            ->get()
            ->getRowArray();

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan atau sudah diproses.');
        }

        $db->table('orders')->update([
            'harga_custom'         => $hargaCustom,
            'total_harga'          => $hargaCustom,
            'estimasi_custom'      => $estimasiCustom,
            'catatan_admin_custom' => $catatanAdmin,
            'status'               => 'menunggu_konfirmasi_pelanggan',
        ], ['id_order' => $idOrder]);

        helper('notification');

        sendNotifEmail(
            $order['email'],
            "Penawaran Harga Pesanan Custom — {$order['kode_order']}",
            "<p>Halo <strong>{$order['nama']}</strong>,</p>
             <p>Admin Z'Plack telah menetapkan harga untuk pesanan custom kamu
             <strong>{$order['kode_order']}</strong>:</p>
             <ul>
               <li>Harga: <strong>Rp " . number_format($hargaCustom, 0, ',', '.') . "</strong></li>
               <li>Estimasi pengerjaan: <strong>{$estimasiCustom}</strong></li>
               <li>Catatan Admin: {$catatanAdmin}</li>
             </ul>
             <p>Silakan login ke SIMENAK dan konfirmasi apakah kamu
             <strong>Setuju</strong> atau <strong>Menolak</strong> penawaran ini.</p>"
        );

        sendNotifInApp(
            (int) $order['id_user_pelanggan'],
            $idOrder,
            'Penawaran Harga Custom',
            "Admin Z'Plack menawarkan harga Rp "
            . number_format($hargaCustom, 0, ',', '.')
            . " untuk pesanan {$order['kode_order']}. Silakan konfirmasi."
        );

        return redirect()->to(site_url('list-pemesanan?tab=custom'))
            ->with('success', 'Penawaran harga berhasil dikirim ke pelanggan.');
    }

    public function setuju()
    {
        if (session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder = (int) $this->request->getPost('id_order');
        $db = \Config\Database::connect();

        $order = $db->table('orders')
            ->where('id_order', $idOrder)
            ->where('id_pelanggan', (int) session()->get('id_pelanggan'))
            ->where('status', 'menunggu_konfirmasi_pelanggan')
            ->get()
            ->getRowArray();

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $pelanggan = $db->table('pelanggan')
            ->where('id_pelanggan', (int) session()->get('id_pelanggan'))
            ->get()
            ->getRowArray();

        $jenisDiminta = $order['jenis_pelanggan'];
        $isVerified   = (int) ($pelanggan['is_verified'] ?? 0);

        if ($jenisDiminta === 'perusahaan' && $isVerified === 1) {
            $newStatus = 'terverifikasi';
            $requireDp = 0;
        } else {
            $newStatus = 'menunggu_verifikasi_dp';
            $requireDp = 1;
        }

        $updateOrder = [
            'status'     => $newStatus,
            'require_dp' => $requireDp,
        ];
        if ($requireDp === 1) {
            $updateOrder['batas_upload_dp']  = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $updateOrder['reminder_dp_sent'] = 0;
        }

        $db->table('orders')->update($updateOrder, ['id_order' => $idOrder]);

        return redirect()->to(site_url('order/detail/' . $order['kode_order']))
            ->with(
                'success',
                'Penawaran disetujui! '
                . ($requireDp ? 'Silakan lakukan pembayaran DP.' : 'Pesanan masuk ke antrian produksi.')
            );
    }

    public function tolak()
    {
        if (session()->get('role') !== 'pelanggan') {
            return redirect()->to(site_url('dashboard'));
        }

        $idOrder = (int) $this->request->getPost('id_order');
        $db = \Config\Database::connect();

        $order = $db->table('orders o')
            ->select('o.*, u.id_user as admin_id_user')
            ->join('users u', "u.role = 'admin'", 'cross')
            ->where('o.id_order', $idOrder)
            ->where('o.id_pelanggan', (int) session()->get('id_pelanggan'))
            ->where('o.status', 'menunggu_konfirmasi_pelanggan')
            ->get()
            ->getRowArray();

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $db->table('orders')->update(
            ['status' => 'dibatalkan'],
            ['id_order' => $idOrder]
        );

        helper('notification');

        $admin = $db->table('users')->where('role', 'admin')->get()->getRowArray();
        if ($admin) {
            sendNotifInApp(
                (int) $admin['id_user'],
                $idOrder,
                'Penawaran Custom Ditolak',
                "Pelanggan " . session()->get('nama')
                . " menolak penawaran harga untuk {$order['kode_order']}."
            );
        }

        return redirect()->to(site_url('order/detail/' . $order['kode_order']))
            ->with('info', 'Penawaran ditolak. Pesanan dibatalkan.');
    }
}
