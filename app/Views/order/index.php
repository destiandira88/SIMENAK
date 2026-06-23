<?php
/**
 * @var list<array<string, mixed>> $orders
 * @var string                     $title
 * @var string                     $page_title
 */
$kategoriBadges = [
    'desain_grafis' => ['label' => 'Desain Grafis', 'class' => 'bg-purple-100 text-purple-800'],
    'cetak_digital' => ['label' => 'Cetak Digital', 'class' => 'bg-blue-100 text-blue-800'],
    'cetak_offset'  => ['label' => 'Cetak Offset', 'class' => 'bg-orange-100 text-orange-800'],
    'media_promosi' => ['label' => 'Media Promosi', 'class' => 'bg-green-100 text-green-800'],
];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Pesanan Saya') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Pesanan Saya') ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-4">
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

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end mb-4">
    <label for="orderSearchInput" class="sr-only">Cari pesanan</label>
    <div class="search-control w-full sm:w-auto">
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <input
            id="orderSearchInput"
            type="search"
            placeholder="Cari kode atau nama produk..."
            autocomplete="off">
    </div>

    <select id="orderFilterStatus" class="filter-select w-full sm:w-auto" aria-label="Filter status">
        <option value="">Semua Status</option>
        <option value="aktif">Aktif</option>
        <option value="menunggu_bayar">Menunggu Pembayaran</option>
        <option value="selesai">Selesai</option>
        <option value="dibatalkan">Dibatalkan</option>
    </select>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Pesanan</th>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold">Tgl Pesan</th>
                    <th class="px-4 py-3 text-left font-semibold">Total</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="orderListBody">
                <?php if ($orders === []): ?>
                    <tr id="emptyDataRow">
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
                        $badge       = $kategoriBadges[$kategoriKey] ?? [
                            'label' => str_replace('_', ' ', $kategoriKey),
                            'class' => 'bg-slate-100 text-slate-600',
                        ];
                        $tglPesan = $createdAt !== ''
                            ? date('d M Y', strtotime($createdAt))
                            : '-';
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-status="<?= esc($status) ?>">
                            <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodeOrder) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-[#051747]"><?= esc($namaProduk) ?></p>
                                <?php if ($kategoriKey !== ''): ?>
                                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc($badge['class']) ?>">
                                        <?= esc($badge['label']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">
                                <?= esc($tglPesan) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <?php if ($totalHarga > 0): ?>
                                    <span class="font-semibold text-[#051747]">
                                        Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-amber-600 font-medium italic">Menunggu Admin</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getOrderStatusLabel($o)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="action-menu relative inline-block">
                                    <button
                                        type="button"
                                        class="action-menu-btn"
                                        aria-label="Menu aksi"
                                        aria-expanded="false"
                                        data-action-toggle>
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="5" r="1.75" />
                                            <circle cx="12" cy="12" r="1.75" />
                                            <circle cx="12" cy="19" r="1.75" />
                                        </svg>
                                    </button>
                                    <div class="action-dropdown hidden" role="menu">
                                        <a href="<?= esc(site_url('order/detail/' . $kodeOrder)) ?>" role="menuitem">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="emptyFilterRow" class="hidden">
                        <td colspan="7" class="py-12 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pesanan yang cocok dengan filter.</p>
                            <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci atau status.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-t border-slate-100">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <label for="entriesSelect" class="whitespace-nowrap">Tampilkan</label>
            <select id="entriesSelect" class="entries-select">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="whitespace-nowrap">data</span>
        </div>
        <p id="entriesInfo" class="text-xs text-slate-500"></p>
    </div>

    <div id="tablePagination" class="hidden items-center justify-between px-4 py-3 border-t border-slate-100">
        <button type="button" id="prevPageBtn" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
            ← Sebelumnya
        </button>
        <span id="pageInfo" class="text-xs text-slate-500"></span>
        <button type="button" id="nextPageBtn" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
            Selanjutnya →
        </button>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const AKTIF_STATUSES = [
        'terverifikasi', 'proses_desain', 'proses_revisi',
        'proses_cetak', 'finishing', 'siap_kirim', 'siap_diambil', 'dikirim',
        'pesanan_diterima', 'menunggu_verifikasi_lunas',
        'menunggu_konfirmasi_harga', 'menunggu_konfirmasi_pelanggan',
        'menunggu_verifikasi_dp',
    ];

    window.adminDataTableConfig = {
        searchId: 'orderSearchInput',
        filterId: 'orderFilterStatus',
        tbodyId: 'orderListBody',
        emptyFilterRowId: 'emptyFilterRow',
        matchFilter: function(filterVal, status) {
            if (filterVal === '') return true;
            if (filterVal === 'aktif') return AKTIF_STATUSES.includes(status);
            if (filterVal === 'menunggu_bayar') {
                return ['menunggu_verifikasi_dp', 'menunggu_verifikasi_lunas'].includes(status);
            }
            if (filterVal === 'selesai') return status === 'selesai';
            if (filterVal === 'dibatalkan') return status === 'dibatalkan';
            return true;
        },
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>
