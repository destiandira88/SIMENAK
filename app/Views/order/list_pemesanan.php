<?php

/**
 * @var list<array<string, mixed>> $orders
 * @var string                     $title
 * @var string                     $page_title
 * @var int                        $countAll
 * @var int                        $countStandar
 * @var int                        $countCustom
 * @var int                        $countMenunggu
 * @var string                     $activeTab
 * @var bool                       $readOnly
 */
$readOnly = (bool) ($readOnly ?? false);
$kategoriBadges = [
    'desain_grafis' => ['label' => 'Desain Grafis', 'class' => 'bg-purple-100 text-purple-800'],
    'cetak_digital' => ['label' => 'Cetak Digital', 'class' => 'bg-blue-100 text-blue-800'],
    'cetak_offset'  => ['label' => 'Cetak Offset', 'class' => 'bg-orange-100 text-orange-800'],
    'media_promosi' => ['label' => 'Media Promosi', 'class' => 'bg-green-100 text-green-800'],
];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'List Pemesanan') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'List Pemesanan') ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<style>
    .pemesanan-tab {
        padding: 10px 4px;
        margin-right: 24px;
        font-size: 14px;
        font-weight: 600;
        color: #64748B;
        border-bottom: 2px solid transparent;
        transition: color .2s, border-color .2s;
        white-space: nowrap;
    }

    .pemesanan-tab:hover {
        color: #051747;
    }

    .pemesanan-tab.is-active {
        color: #2E5CE6;
        border-bottom-color: #2E5CE6;
    }

    .badge-tipe-standar {
        background: #DBEAFE;
        color: #1E40AF;
    }

    .badge-tipe-custom {
        background: #FEF3C7;
        color: #92400E;
    }

    .list-pemesanan-date-range {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .list-pemesanan-date-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748B;
        white-space: nowrap;
    }

    .list-pemesanan-date-sep {
        font-size: 12px;
        font-weight: 600;
        color: #94A3B8;
        user-select: none;
    }

    .list-pemesanan-date-input {
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        padding: 8px 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        color: #4A5568;
        background-color: #fff;
        min-width: 130px;
        transition: border-color .2s, box-shadow .2s;
    }

    .list-pemesanan-date-input:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2 class="text-2xl font-extrabold text-[#051747]"><?= $readOnly ? 'Pesanan' : 'List Pemesanan' ?></h2>
    <p class="mt-1 text-sm text-slate-500">
        <?= $readOnly
            ? 'Pantau seluruh data pemesanan pelanggan.'
            : 'Pantau status dan detail seluruh pemesanan pelanggan' ?>
    </p>
</div>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4 border-b border-slate-200">
    <div class="flex flex-wrap items-end gap-0 overflow-x-auto" role="tablist" aria-label="Filter pemesanan">
        <button type="button" class="pemesanan-tab <?= $activeTab === 'semua' ? 'is-active' : '' ?>" data-tab="semua" role="tab" aria-selected="<?= $activeTab === 'semua' ? 'true' : 'false' ?>">
            Semua (<?= esc((string) $countAll) ?>)
        </button>
        <button type="button" class="pemesanan-tab <?= $activeTab === 'standar' ? 'is-active' : '' ?>" data-tab="standar" role="tab" aria-selected="<?= $activeTab === 'standar' ? 'true' : 'false' ?>">
            Standar (<?= esc((string) $countStandar) ?>)
        </button>
        <button type="button" class="pemesanan-tab <?= $activeTab === 'custom' ? 'is-active' : '' ?>" data-tab="custom" role="tab" aria-selected="<?= $activeTab === 'custom' ? 'true' : 'false' ?>">
            Custom (<?= esc((string) $countCustom) ?>)
        </button>
        <button type="button" class="pemesanan-tab relative <?= $activeTab === 'menunggu-harga' ? 'is-active' : '' ?>" data-tab="menunggu-harga" role="tab" aria-selected="<?= $activeTab === 'menunggu-harga' ? 'true' : 'false' ?>">
            Menunggu harga<?php if ($countMenunggu > 0): ?>
                <span class="ml-1.5 inline-flex min-w-[18px] h-[18px] items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full px-1 align-middle"><?= esc((string) $countMenunggu) ?></span>
            <?php else: ?>
                (<?= esc((string) $countMenunggu) ?>)
            <?php endif; ?>
        </button>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:flex-wrap sm:justify-end mb-3 sm:mb-4">
        <div class="list-pemesanan-date-range">
            <label for="listPemesananDateFrom" class="list-pemesanan-date-label">Dari</label>
            <input
                id="listPemesananDateFrom"
                type="date"
                class="list-pemesanan-date-input"
                aria-label="Filter tanggal mulai">
            <span class="list-pemesanan-date-sep" aria-hidden="true">-</span>
            <label for="listPemesananDateTo" class="list-pemesanan-date-label">Sampai</label>
            <input
                id="listPemesananDateTo"
                type="date"
                class="list-pemesanan-date-input"
                aria-label="Filter tanggal akhir">
        </div>
        <label for="listPemesananSearch" class="sr-only">Cari pemesanan</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input
                id="listPemesananSearch"
                type="search"
                placeholder="Cari kode, pelanggan, produk..."
                autocomplete="off">
        </div>
    </div>
</div>

<?php if ($activeTab === 'menunggu-harga' || $activeTab === 'custom'): ?>
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <?php if ($activeTab === 'menunggu-harga'): ?>
            Pesanan custom yang memerlukan penawaran harga dari Admin. Klik <strong>Set Harga</strong> pada menu aksi.
        <?php else: ?>
            Daftar pemesanan custom-termasuk yang menunggu konfirmasi harga dan konfirmasi pelanggan.
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="table-responsive">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kode">
                        Kode Order<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="produk">
                        Produk / Kategori<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="tipe">
                        Tipe<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="tanggal">
                        Tanggal Pesanan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="total">
                        Total Harga<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                        Status<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="listPemesananBody">
                <?php if ($orders === []): ?>
                    <tr id="emptyDataRow">
                        <td colspan="9" class="py-16 text-center">
                            <div class="text-4xl mb-3">📦</div>
                            <p class="text-sm font-medium text-slate-500">Belum ada pemesanan</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $o): ?>
                        <?php
                        $kodeOrder     = (string) ($o['kode_order'] ?? '');
                        $namaPelanggan = (string) ($o['nama_pelanggan'] ?? '-');
                        $noTelp        = trim((string) ($o['no_telp'] ?? ''));
                        $namaProduk    = (string) ($o['nama_produk'] ?? '-');
                        $kategoriKey   = (string) ($o['kategori'] ?? '');
                        $badge         = $kategoriBadges[$kategoriKey] ?? [
                            'label' => str_replace('_', ' ', $kategoriKey),
                            'class' => 'bg-slate-100 text-slate-600',
                        ];
                        $isCustom      = (int) ($o['is_custom'] ?? 0) === 1;
                        $status        = (string) ($o['status'] ?? '');
                        $totalHarga    = (float) ($o['total_harga'] ?? 0);
                        $jumlahOrder   = (int) ($o['jumlah_order'] ?? 0);
                        $satuan        = (string) ($o['satuan'] ?? 'pcs');
                        $idOrder       = (int) ($o['id_order'] ?? 0);
                        $searchText    = mb_strtolower(trim(
                            $kodeOrder . ' ' . $namaPelanggan . ' ' . $noTelp . ' ' . $namaProduk . ' ' . $badge['label']
                        ));
                        $tipeKey       = $isCustom ? 'custom' : 'standar';
                        $createdAt     = (string) ($o['created_at'] ?? '');
                        $tsCreated     = $createdAt !== '' ? strtotime($createdAt) : 0;
                        $tglPesan      = $tsCreated > 0 ? date('d M Y', $tsCreated) : '-';
                        $sortTotal     = ($status === 'menunggu_konfirmasi_harga' || ($isCustom && $totalHarga <= 0))
                            ? -1
                            : $totalHarga;
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-status="<?= esc($status) ?>"
                            data-tipe="<?= esc($tipeKey) ?>"
                            data-kode="<?= esc($kodeOrder) ?>"
                            data-produk="<?= esc($namaProduk) ?>"
                            data-tanggal="<?= esc((string) $tsCreated) ?>"
                            data-total="<?= esc((string) $sortTotal) ?>">
                            <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]"><?= esc($kodeOrder) ?></td>
                            <td class="px-4 py-3.5">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => $namaPelanggan,
                                    'noTelp' => $noTelp,
                                ]) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-semibold text-[#051747]"><?= esc($namaProduk) ?></p>
                                <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc($badge['class']) ?>">
                                    <?= esc($badge['label']) ?>
                                </span>
                                <p class="text-xs text-slate-500 mt-1">Jumlah: <?= esc((string) $jumlahOrder) ?> <?= esc($satuan) ?></p>
                            </td>
                            <td class="px-4 py-3.5">
                                <?php if ($isCustom): ?>
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold badge-tipe-custom">Custom</span>
                                <?php else: ?>
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold badge-tipe-standar">Standar</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap"><?= esc($tglPesan) ?></td>
                            <td class="px-4 py-3.5">
                                <?php if ($status === 'menunggu_konfirmasi_harga' || ($isCustom && $totalHarga <= 0)): ?>
                                    <span class="text-sm italic text-slate-400">Menunggu Admin</span>
                                <?php else: ?>
                                    <span class="font-semibold text-[#051747]">
                                        Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?>
                                    </span>
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
                                        <?php if (!$readOnly && $status === 'menunggu_konfirmasi_harga'): ?>
                                            <button
                                                type="button"
                                                role="menuitem"
                                                onclick="document.getElementById('modalSetHarga_<?= esc((string) $idOrder) ?>').classList.remove('hidden')">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Set Harga
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="emptyFilterRow" class="hidden">
                        <td colspan="9" class="py-12 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pemesanan yang cocok dengan filter.</p>
                            <p class="text-xs text-slate-400 mt-1">Coba ubah tab, rentang tanggal, atau kata kunci pencarian.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-t border-slate-100">
        <div class="flex items-center gap-2 text-sm text-slate-600">
            <label for="entriesSelect" class="whitespace-nowrap">Show</label>
            <select id="entriesSelect" class="entries-select">
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span class="whitespace-nowrap">entries</span>
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

<?php if (!$readOnly): ?>
    <?php foreach ($orders as $o): ?>
        <?php if (($o['status'] ?? '') === 'menunggu_konfirmasi_harga'): ?>
            <div id="modalSetHarga_<?= esc((string) ($o['id_order'] ?? 0)) ?>" class="hidden fixed inset-0 bg-black/50 z-50 p-4 flex items-center justify-center">
                <div class="bg-white rounded-2xl shadow-lg p-6 max-w-lg w-full border border-[#E2E8F0]">
                    <h3 class="font-bold text-lg text-[#051747]">
                        Set Harga Pesanan Custom
                        <span class="block text-xs text-slate-500 mt-1 font-normal"><?= esc((string) ($o['kode_order'] ?? '-')) ?></span>
                    </h3>

                    <div class="bg-slate-50 rounded-xl p-4 mb-4 mt-4">
                        <p class="text-sm text-slate-700">
                            <span class="font-semibold">Pelanggan:</span>
                            <?= esc((string) ($o['nama_pelanggan'] ?? '-')) ?>
                            <?php if (!empty($o['no_telp'])): ?>
                                · <span class="text-slate-500"><?= esc((string) $o['no_telp']) ?></span>
                            <?php endif; ?>
                        </p>
                        <p class="text-sm text-slate-700 mt-1"><span class="font-semibold">Produk:</span> <?= esc((string) ($o['nama_produk'] ?? '-')) ?></p>
                        <p class="text-sm text-slate-700 mt-1">
                            <span class="font-semibold">Jumlah:</span>
                            <?= esc((string) ($o['jumlah_order'] ?? 0)) ?> <?= esc((string) ($o['satuan'] ?? 'pcs')) ?>
                        </p>
                        <p class="text-sm text-slate-700 mt-2"><span class="font-semibold">Catatan:</span> <?= esc((string) ($o['catatan_custom'] ?? '-')) ?></p>
                        <?php if (!empty($o['deadline'])): ?>
                            <?php helper('deadline'); ?>
                            <p class="text-sm text-slate-700 mt-2">
                                <span class="font-semibold">Deadline diajukan pelanggan:</span>
                                <?= esc(formatTanggalId((string) $o['deadline'])) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <form method="post"
                        action="<?= esc(site_url('list-pemesanan/set-harga')) ?>"
                        class="js-action-confirm-form"
                        data-confirm-variant="offer"
                        data-confirm-kode="<?= esc((string) ($o['kode_order'] ?? '-')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_order" value="<?= esc((string) ($o['id_order'] ?? 0)) ?>">

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Harga yang Ditawarkan (Rp)</label>
                            <input
                                type="number"
                                name="harga_custom"
                                min="1"
                                step="1"
                                required
                                placeholder="Contoh: 350000"
                                onwheel="this.blur()"
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                        </div>

                        <div class="mb-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estimasi Pengerjaan</label>
                            <input
                                type="text"
                                name="estimasi_custom"
                                required
                                placeholder="Contoh: 5-7 hari kerja (setelah ACC desain)"
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                            <p class="text-xs text-slate-400 mt-1">Dihitung setelah pelanggan menyetujui desain.</p>
                        </div>

                        <div class="mb-3 bg-slate-50 border border-slate-200 rounded-xl p-3">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deadline Produksi Penawaran</label>
                            <input
                                type="date"
                                name="deadline"
                                required
                                min="<?= esc(date('Y-m-d')) ?>"
                                value="<?= esc((string) ($o['deadline'] ?? date('Y-m-d'))) ?>"
                                class="w-full max-w-xs border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                            <p class="text-xs text-slate-400 mt-1.5">
                                Barang selesai dikerjakan — belum termasuk pengiriman. Sesuaikan jika ajuan pelanggan tidak sanggup.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan untuk Pelanggan</label>
                            <textarea
                                name="catatan_admin_custom"
                                rows="3"
                                placeholder="Jelaskan rincian harga, material, dll"
                                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"></textarea>
                        </div>

                        <div class="flex gap-3 justify-end mt-4">
                            <button
                                type="button"
                                onclick="document.getElementById('modalSetHarga_<?= esc((string) ($o['id_order'] ?? 0)) ?>').classList.add('hidden')"
                                class="border border-slate-300 text-slate-600 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-slate-50 transition-colors">
                                Tutup
                            </button>
                            <button
                                type="submit"
                                class="bg-[#051747] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#2E5CE6] transition-colors">
                                Kirim Penawaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let activePemesananTab = <?= json_encode($activeTab, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    window.adminDataTableConfig = {
        searchId: 'listPemesananSearch',
        filterId: '',
        dateFromId: 'listPemesananDateFrom',
        dateToId: 'listPemesananDateTo',
        tbodyId: 'listPemesananBody',
        enableSort: true,
        getTabFilter: function(row) {
            if (activePemesananTab === 'standar') {
                return row.dataset.tipe === 'standar';
            }
            if (activePemesananTab === 'custom') {
                return row.dataset.tipe === 'custom';
            }
            if (activePemesananTab === 'menunggu-harga') {
                return row.dataset.status === 'menunggu_konfirmasi_harga';
            }
            return true;
        },
        onReady: function(api) {
            document.querySelectorAll('.pemesanan-tab').forEach((btn) => {
                btn.addEventListener('click', () => {
                    activePemesananTab = btn.dataset.tab || 'semua';
                    document.querySelectorAll('.pemesanan-tab').forEach((t) => {
                        const on = t.dataset.tab === activePemesananTab;
                        t.classList.toggle('is-active', on);
                        t.setAttribute('aria-selected', on ? 'true' : 'false');
                    });
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', activePemesananTab);
                    window.history.replaceState({}, '', url);
                    api.setPage(1);
                    api.applyTableState();
                });
            });
        },
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>