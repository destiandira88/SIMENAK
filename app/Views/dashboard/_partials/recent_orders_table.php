<?php
/**
 * @var list<array<string, mixed>> $orders
 * @var string                     $variant admin|pelanggan|owner
 * @var string                     $sectionTitle
 * @var string                     $searchId
 * @var string                     $tbodyId
 * @var string                     $searchPlaceholder
 * @var bool                       $showDateFilter
 * @var string                     $dateFromId
 * @var string                     $dateToId
 * @var string                     $entriesId
 * @var string                     $entriesInfoId
 * @var string                     $paginationId
 * @var string                     $prevPageId
 * @var string                     $nextPageId
 * @var string                     $pageInfoId
 */
$variant           = $variant ?? 'pelanggan';
$orders            = $orders ?? [];
$sectionTitle      = $sectionTitle ?? 'Pesanan Terbaru';
$searchId          = $searchId ?? 'dashboardOrdersSearch';
$tbodyId           = $tbodyId ?? 'dashboardOrdersBody';
$searchPlaceholder = $searchPlaceholder ?? 'Cari kode atau produk...';
$showDateFilter    = $showDateFilter ?? false;
$dateFromId        = $dateFromId ?? 'dashboardDateFrom';
$dateToId          = $dateToId ?? 'dashboardDateTo';
$entriesId         = $entriesId ?? 'entriesSelect';
$entriesInfoId     = $entriesInfoId ?? 'entriesInfo';
$paginationId      = $paginationId ?? 'tablePagination';
$prevPageId        = $prevPageId ?? 'prevPageBtn';
$nextPageId        = $nextPageId ?? 'nextPageBtn';
$pageInfoId        = $pageInfoId ?? 'pageInfo';
$isAdmin           = $variant === 'admin';
$isOwner           = $variant === 'owner';
$colspan           = $isAdmin ? 9 : ($isOwner ? 6 : 7);
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
    <h3 class="text-base font-bold text-[#051747]"><?= esc($sectionTitle) ?></h3>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:flex-wrap sm:justify-end">
        <?php if ($showDateFilter): ?>
            <div class="list-pemesanan-date-range">
                <label for="<?= esc($dateFromId) ?>" class="list-pemesanan-date-label">Dari</label>
                <input
                    id="<?= esc($dateFromId) ?>"
                    type="date"
                    class="list-pemesanan-date-input"
                    aria-label="Filter tanggal mulai">
                <span class="list-pemesanan-date-sep" aria-hidden="true">-</span>
                <label for="<?= esc($dateToId) ?>" class="list-pemesanan-date-label">Sampai</label>
                <input
                    id="<?= esc($dateToId) ?>"
                    type="date"
                    class="list-pemesanan-date-input"
                    aria-label="Filter tanggal akhir">
            </div>
        <?php endif; ?>
        <label for="<?= esc($searchId) ?>" class="sr-only">Cari pesanan</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input
                id="<?= esc($searchId) ?>"
                type="search"
                placeholder="<?= esc($searchPlaceholder) ?>"
                autocomplete="off">
        </div>
    </div>
</div>

<div class="admin-data-table-wrap">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <?php if ($isOwner): ?>
                        <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kode">
                            Kode Pesanan<span class="sort-icon">↕</span>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="produk">
                            Produk<span class="sort-icon">↕</span>
                        </th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="total">
                            Total<span class="sort-icon">↕</span>
                        </th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                            Status<span class="sort-icon">↕</span>
                        </th>
                    <?php else: ?>
                        <th class="px-4 py-3 text-left font-semibold">No</th>
                        <?php if ($isAdmin): ?>
                            <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kode">
                                Kode Pesanan<span class="sort-icon">↕</span>
                            </th>
                            <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                            <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="produk">
                                Produk<span class="sort-icon">↕</span>
                            </th>
                            <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="tanggal">
                                Tanggal<span class="sort-icon">↕</span>
                            </th>
                            <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="tipe">
                                Tipe<span class="sort-icon">↕</span>
                            </th>
                            <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="total">
                                Total Harga<span class="sort-icon">↕</span>
                            </th>
                            <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                                Status<span class="sort-icon">↕</span>
                            </th>
                        <?php else: ?>
                            <th class="px-4 py-3 text-left font-semibold">Kode Pesanan</th>
                            <th class="px-4 py-3 text-left font-semibold">Produk</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold">Total</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <?php endif; ?>
                        <th class="px-4 py-3 text-left font-semibold w-12"></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="<?= esc($tbodyId) ?>">
                <?php if ($orders === []): ?>
                    <tr id="emptyDataRow">
                        <td colspan="<?= esc((string) $colspan) ?>" class="py-16 text-center">
                            <div class="text-4xl mb-3">📦</div>
                            <p class="text-sm font-medium text-slate-500">Belum ada pesanan</p>
                            <p class="text-xs text-slate-400 mt-1">
                                <?php if ($isOwner || $isAdmin): ?>
                                    Pesanan baru akan muncul di sini.
                                <?php else: ?>
                                    Mulai pesan dari katalog!
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <?php
                        $kodeOrder     = (string) ($order['kode_order'] ?? '');
                        $namaPelanggan = (string) ($order['nama_pelanggan'] ?? '-');
                        $isCustom      = (int) ($order['is_custom'] ?? 0) === 1;
                        $namaProduk    = $isCustom
                            ? 'Pesanan Custom'
                            : (string) ($order['nama_produk'] ?? '-');
                        $status        = (string) ($order['status'] ?? '');
                        $totalHarga    = (float) ($order['total_harga'] ?? 0);
                        $createdAt     = (string) ($order['created_at'] ?? '');
                        $tsCreated     = $createdAt !== '' ? strtotime($createdAt) : 0;
                        $tipeKey       = $isCustom ? 'custom' : 'standar';
                        $noTelp        = trim((string) ($order['no_telp'] ?? ''));
                        $searchText    = mb_strtolower(trim(
                            $kodeOrder . ' ' . $namaPelanggan . ' ' . $noTelp . ' ' . $namaProduk . ' ' . $status
                        ));
                        $detailUrl     = site_url('order/detail/' . $kodeOrder);
                        $tglTampil     = $tsCreated > 0 ? date('d M Y', $tsCreated) : '-';
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-status="<?= esc($status) ?>"
                            <?php if ($isAdmin || $isOwner): ?>
                            data-kode="<?= esc(mb_strtolower($kodeOrder)) ?>"
                            data-produk="<?= esc(mb_strtolower($namaProduk)) ?>"
                            data-tanggal="<?= esc((string) $tsCreated) ?>"
                            data-tipe="<?= esc($tipeKey) ?>"
                            data-total="<?= esc((string) $totalHarga) ?>"
                            <?php endif; ?>>
                            <?php if ($isOwner): ?>
                                <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                                <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747] whitespace-nowrap">
                                    <?php if ($kodeOrder !== ''): ?>
                                        <a href="<?= esc($detailUrl) ?>" class="hover:text-[#2E5CE6] hover:underline">
                                            <?= esc($kodeOrder) ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5">
                                    <?= view('partials/pelanggan_kontak_cell', [
                                        'nama'   => $namaPelanggan,
                                        'noTelp' => $noTelp,
                                    ]) ?>
                                </td>
                                <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                                <td class="px-4 py-3.5 font-semibold text-[#051747]">
                                    <?php if ($totalHarga > 0): ?>
                                        Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?>
                                    <?php else: ?>
                                        <span class="text-xs text-amber-600 font-medium italic">Menunggu Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                        <?= esc(getOrderStatusLabel($order, 'owner')) ?>
                                    </span>
                                </td>
                            <?php else: ?>
                                <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                                <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                    <?= esc($kodeOrder ?: '-') ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                    <td class="px-4 py-3.5">
                                        <?= view('partials/pelanggan_kontak_cell', [
                                            'nama'   => $namaPelanggan,
                                            'noTelp' => $noTelp,
                                        ]) ?>
                                    </td>
                                    <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                                    <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap"><?= esc($tglTampil) ?></td>
                                    <td class="px-4 py-3.5">
                                        <?php if ($isCustom): ?>
                                            <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-[#FEF3C7] text-[#92400E]">Custom</span>
                                        <?php else: ?>
                                            <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-[#DBEAFE] text-[#1E40AF]">Standar</span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                                    <td class="px-4 py-3.5 text-slate-500 whitespace-nowrap"><?= esc($tglTampil) ?></td>
                                <?php endif; ?>
                                <td class="px-4 py-3.5 font-semibold text-[#051747]">
                                    <?php if ($totalHarga > 0): ?>
                                        Rp <?= esc(number_format($totalHarga, 0, ',', '.')) ?>
                                    <?php else: ?>
                                        <span class="text-xs text-amber-600 font-medium italic">Menunggu Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                        <?= esc(getOrderStatusLabel($order)) ?>
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
                                            <a href="<?= esc($detailUrl) ?>" role="menuitem">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="emptyFilterRow" class="hidden">
                        <td colspan="<?= esc((string) $colspan) ?>" class="py-12 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pesanan yang cocok dengan filter.</p>
                            <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci<?= $showDateFilter ? ' atau rentang tanggal' : '' ?>.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($orders !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => $entriesId,
            'entriesInfoId' => $entriesInfoId,
            'paginationId'  => $paginationId,
            'prevPageId'    => $prevPageId,
            'nextPageId'    => $nextPageId,
            'pageInfoId'    => $pageInfoId,
        ]) ?>
    <?php endif; ?>
</div>
