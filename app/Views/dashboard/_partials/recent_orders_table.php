<?php
/**
 * @var list<array<string, mixed>> $orders
 * @var string                     $variant admin|pelanggan
 * @var string                     $sectionTitle
 * @var string                     $searchId
 * @var string                     $tbodyId
 * @var string                     $searchPlaceholder
 */
$variant           = $variant ?? 'pelanggan';
$orders            = $orders ?? [];
$sectionTitle      = $sectionTitle ?? 'Pesanan Terbaru';
$searchId          = $searchId ?? 'dashboardOrdersSearch';
$tbodyId           = $tbodyId ?? 'dashboardOrdersBody';
$searchPlaceholder = $searchPlaceholder ?? 'Cari kode atau produk...';
$isAdmin           = $variant === 'admin';
$colspan           = 7;
?>
<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4">
    <h3 class="text-base font-bold text-[#051747]"><?= esc($sectionTitle) ?></h3>
    <label for="<?= esc($searchId) ?>" class="sr-only">Cari pesanan</label>
    <div class="search-control">
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

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Order</th>
                    <?php if ($isAdmin): ?>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <?php if (!$isAdmin): ?>
                        <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-left font-semibold">Total</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold w-12"></th>
                </tr>
            </thead>
            <tbody id="<?= esc($tbodyId) ?>">
                <?php if ($orders === []): ?>
                    <tr id="emptyDataRow">
                        <td colspan="<?= esc((string) $colspan) ?>" class="py-16 text-center">
                            <div class="text-4xl mb-3">📦</div>
                            <p class="text-sm font-medium text-slate-500">
                                <?= $isAdmin ? 'Belum ada pesanan' : 'Belum ada pesanan' ?>
                            </p>
                            <p class="text-xs text-slate-400 mt-1">
                                <?= $isAdmin ? 'Pesanan baru akan muncul di sini.' : 'Mulai pesan dari katalog!' ?>
                            </p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <?php
                        $kodeOrder     = (string) ($order['kode_order'] ?? '');
                        $namaPelanggan = (string) ($order['nama_pelanggan'] ?? '-');
                        $namaProduk    = !empty($order['is_custom'])
                            ? 'Pemesanan Custom'
                            : (string) ($order['nama_produk'] ?? '-');
                        $status        = (string) ($order['status'] ?? '');
                        $searchText    = mb_strtolower(trim(
                            $kodeOrder . ' ' . $namaPelanggan . ' ' . $namaProduk . ' ' . $status
                        ));
                        $detailUrl     = site_url('order/detail/' . $kodeOrder);
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-status="<?= esc($status) ?>">
                            <td class="row-num px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodeOrder ?: '-') ?>
                            </td>
                            <?php if ($isAdmin): ?>
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-[#051747]"><?= esc($namaPelanggan) ?></p>
                                </td>
                            <?php endif; ?>
                            <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                            <?php if (!$isAdmin): ?>
                                <td class="px-4 py-3.5 text-slate-500">
                                    <?= esc(date('d M Y', strtotime((string) ($order['created_at'] ?? 'now')))) ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-4 py-3.5 font-semibold text-[#051747]">
                                Rp <?= esc(number_format((float) ($order['total_harga'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getStatusLabel($status)) ?>
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
                                        <?php if ($isAdmin): ?>
                                            <button
                                                type="button"
                                                class="action-danger"
                                                role="menuitem"
                                                data-dashboard-delete="<?= esc($kodeOrder, 'attr') ?>">
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="emptyFilterRow" class="hidden">
                        <td colspan="<?= esc((string) $colspan) ?>" class="py-12 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pesanan yang cocok dengan pencarian.</p>
                            <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci.</p>
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
