<?php
/**
 * @var list<array<string, mixed>> $orders
 * @var string                     $title
 * @var string                     $page_title
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Pesanan Saya') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Pesanan Saya') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-[#051747]">Pesanan Saya</h2>
        <p class="mt-1 text-sm text-slate-500">Riwayat dan status pesanan Anda</p>
    </div>
    <a
        href="<?= site_url('katalog') ?>"
        class="inline-flex items-center justify-center bg-[#051747] text-white px-5 py-2.5 rounded-full font-bold uppercase text-sm hover:bg-[#2E5CE6] transition-colors shrink-0">
        + Buat Pesanan Baru
    </a>
</div>

<div class="flex flex-wrap gap-3 mb-4">
    <input
        type="search"
        id="searchInput"
        placeholder="Cari kode atau nama produk..."
        class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-full sm:w-72 focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
    <select
        id="filterStatus"
        class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        <option value="">Semua Status</option>
        <option value="aktif">Aktif</option>
        <option value="menunggu_bayar">Menunggu Pembayaran</option>
        <option value="selesai">Selesai</option>
        <option value="dibatalkan">Dibatalkan</option>
    </select>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Order</th>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold">Tgl Pesan</th>
                    <th class="px-4 py-3 text-left font-semibold">Total</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders === []): ?>
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="text-4xl mb-3">📦</div>
                            <p class="text-sm font-medium text-slate-500">Belum ada pesanan</p>
                            <p class="text-xs text-slate-400 mt-1">Mulai pesan dari katalog kami</p>
                            <a
                                href="<?= site_url('katalog') ?>"
                                class="inline-flex mt-4 bg-[#051747] text-white px-5 py-2.5 rounded-full font-bold uppercase text-xs hover:bg-[#2E5CE6] transition-colors">
                                + Lihat Katalog
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $o): ?>
                        <?php
                        $kodeOrder   = (string) ($o['kode_order'] ?? '');
                        $namaProduk  = (string) ($o['nama_produk'] ?? '-');
                        $kategoriKey = (string) ($o['kategori'] ?? '');
                        $status      = (string) ($o['status'] ?? '');
                        $searchText  = mb_strtolower(trim($kodeOrder . ' ' . $namaProduk));
                        $totalHarga  = (float) ($o['total_harga'] ?? 0);
                        $createdAt   = (string) ($o['created_at'] ?? '');

                        $kategoriBadgeClass = match ($kategoriKey) {
                            'desain_grafis' => 'bg-purple-100 text-purple-800',
                            'cetak_digital' => 'bg-blue-100 text-blue-800',
                            'cetak_offset'  => 'bg-orange-100 text-orange-800',
                            'media_promosi' => 'bg-green-100 text-green-800',
                            default         => 'bg-slate-100 text-slate-600',
                        };
                        $kategoriLabel = match ($kategoriKey) {
                            'desain_grafis' => 'Desain Grafis',
                            'cetak_digital' => 'Cetak Digital',
                            'cetak_offset'  => 'Cetak Offset',
                            'media_promosi' => 'Media Promosi',
                            default         => str_replace('_', ' ', $kategoriKey),
                        };

                        $tglPesan = $createdAt !== ''
                            ? date('d M Y', strtotime($createdAt))
                            : '-';
                        ?>
                        <tr
                            class="border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-status="<?= esc($status) ?>">
                            <td class="px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodeOrder) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-[#051747]"><?= esc($namaProduk) ?></p>
                                <?php if ($kategoriKey !== ''): ?>
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc($kategoriBadgeClass) ?>">
                                        <?= esc($kategoriLabel) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">
                                <?= esc($tglPesan) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <?php if ($totalHarga > 0): ?>
                                    <span class="font-medium text-[#051747]">
                                        Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-amber-600 font-medium">Dikonfirmasi Admin</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getStatusLabel($status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <a
                                    href="<?= esc(site_url('order/detail/' . $kodeOrder)) ?>"
                                    class="inline-flex text-xs px-3 py-1.5 bg-[#051747] text-white rounded-lg hover:bg-[#2E5CE6] transition-colors">
                                    Detail →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('searchInput')?.addEventListener('input', filterTable);
    document.getElementById('filterStatus')?.addEventListener('change', filterTable);

    function filterTable() {
        const q = (document.getElementById('searchInput')?.value || '').toLowerCase();
        const f = document.getElementById('filterStatus')?.value || '';

        document.querySelectorAll('tbody tr[data-search]').forEach((row) => {
            const matchSearch = (row.dataset.search || '').toLowerCase().includes(q);
            const status = row.dataset.status || '';

            let matchFilter = true;
            if (f === 'aktif') {
                const aktifStatuses = [
                    'terverifikasi', 'proses_desain', 'proses_revisi',
                    'proses_cetak', 'finishing', 'siap_kirim', 'siap_diambil', 'dikirim',
                    'pesanan_diterima', 'menunggu_verifikasi_lunas',
                    'menunggu_konfirmasi_harga', 'menunggu_konfirmasi_pelanggan',
                    'menunggu_verifikasi_dp',
                ];
                matchFilter = aktifStatuses.includes(status);
            } else if (f === 'menunggu_bayar') {
                matchFilter = ['menunggu_verifikasi_dp', 'menunggu_verifikasi_lunas'].includes(status);
            } else if (f === 'selesai') {
                matchFilter = status === 'selesai';
            } else if (f === 'dibatalkan') {
                matchFilter = status === 'dibatalkan';
            }

            row.style.display = matchSearch && matchFilter ? '' : 'none';
        });
    }
</script>
<?= $this->endSection() ?>
