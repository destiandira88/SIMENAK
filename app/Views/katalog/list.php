<?php

/**
 * @var list<array<string, mixed>> $katalog
 * @var string                     $title
 */
$tabLabels = [
    'all'           => 'Semua',
    'desain_grafis' => 'Desain',
    'cetak_digital' => 'Digital',
    'cetak_offset'  => 'Offset',
    'media_promosi' => 'Promosi',
];
$kategoriLabelMap = [
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
];
$userRole   = (string) session()->get('role');
$isLoggedIn = (bool) session()->get('isLoggedIn');

$resolveGambarUrl = static function (array $item): ?string {
    $gambarRaw = trim((string) ($item['gambar'] ?? ''));
    if ($gambarRaw === '') {
        return null;
    }

    $normalized = ltrim(str_replace('\\', '/', $gambarRaw), '/');

    if (str_starts_with($normalized, 'uploads/katalog/')) {
        $gambarRelPath = substr($normalized, strlen('uploads/katalog/'));
    } elseif (str_starts_with($normalized, 'katalog/')) {
        $gambarRelPath = substr($normalized, strlen('katalog/'));
    } else {
        $gambarRelPath = $normalized;
    }

    $gambarRelPath = ltrim($gambarRelPath, '/');

    if ($gambarRelPath !== '' && is_file(FCPATH . 'uploads/katalog/' . $gambarRelPath)) {
        return base_url('uploads/katalog/' . $gambarRelPath);
    }

    return null;
};
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Katalog Produk') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?>Katalog Produk<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .katalog-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 20px;
        box-shadow: 0 1px 3px rgba(5, 23, 71, .06);
        transition: transform .2s, box-shadow .2s;
    }

    .katalog-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(5, 23, 71, .08);
    }

    .btn-katalog-primary {
        background: #051747;
        color: #fff;
        border-radius: 999px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        transition: background .2s;
    }

    .btn-katalog-primary:hover {
        background: #2E5CE6;
    }

    .btn-katalog-outline {
        border: 1.5px solid #051747;
        color: #051747;
        border-radius: 999px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        transition: background .2s, color .2s;
    }

    .btn-katalog-outline:hover {
        background: #051747;
        color: #fff;
    }

    #catalogSearchBar {
        border-color: #83A2CD;
        transition: border-color .3s, box-shadow .3s, transform .3s;
        box-shadow: 0 2px 12px 0 rgba(46, 92, 230, .05);
        transform: scale(1);
    }

    #catalogSearchBar.focused {
        border-color: #2E5CE6 !important;
        box-shadow: 0 4px 20px 0 rgba(46, 92, 230, .16), 0 0 7px 0 rgba(64, 175, 253, .1);
        transform: scale(1.025);
    }

    .entries-select {
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        padding: 6px 28px 6px 10px;
        color: #4A5568;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%238896A5' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 8px center;
        background-size: 14px;
        appearance: none;
    }

    .entries-select:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div>
    <p class="text-[11px] uppercase tracking-[0.2em] text-[#2E5CE6] font-bold">Our Digital Store</p>
    <h2 class="mt-2 text-3xl sm:text-4xl font-extrabold text-[#051747]">Katalog Percetakan</h2>
    <p class="mt-2 text-sm text-slate-500">Pilih paket produk andalan kami, lalu lakukan pesanan secara digital.</p>
</div>

<?php if ($userRole === 'admin'): ?>
    <div class="mt-4 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm">
        Anda login sebagai Admin.
        <a href="<?= site_url('katalog/kelola') ?>" class="font-semibold underline hover:text-[#051747]">Kelola katalog produk</a>
    </div>
<?php endif; ?>

<div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
    <div class="flex flex-wrap gap-2">
        <?php foreach ($tabLabels as $key => $label): ?>
            <button
                type="button"
                class="catalog-tab <?= $key === 'all' ? 'bg-[#051747] text-white' : 'bg-white text-slate-600 border border-slate-200' ?> px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-[0.12em]"
                data-tab="<?= esc($key) ?>">
                <?= esc($label) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center sm:gap-2">
        <label for="catalogSearchInput" class="sr-only">Cari katalog</label>
        <div
            id="catalogSearchBar"
            class="flex w-full items-center gap-1.5 rounded-full border border-[#83A2CD] bg-white py-1 pl-3 pr-1 transition-all duration-300 sm:min-w-[200px] sm:max-w-[280px]">
            <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input
                id="catalogSearchInput"
                type="search"
                placeholder="Cari..."
                autocomplete="off"
                class="min-w-0 flex-1 border-0 bg-transparent py-1.5 text-xs font-semibold uppercase tracking-[0.08em] text-[#2E5CE6] placeholder:text-slate-400 placeholder:normal-case placeholder:tracking-normal placeholder:font-normal focus:outline-none focus:ring-0">
            <button type="button" id="catalogSearchBtn" class="shrink-0 rounded-full bg-[#051747] px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-[#2E5CE6]">
                Cari
            </button>
        </div>
    </div>
</div>

<?php if ($katalog === []): ?>
    <div class="mt-8 katalog-card p-12 text-center">
        <div class="text-4xl mb-3">📦</div>
        <p class="text-sm font-medium text-slate-500">Belum ada produk aktif di katalog</p>
    </div>
<?php else: ?>
    <div class="mt-8 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-5 sm:p-6">
            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5" id="catalogGrid">
                <?php foreach ($katalog as $item): ?>
                    <?php
                    $idKatalog     = (int) ($item['id_katalog'] ?? 0);
                    $category      = (string) ($item['kategori'] ?? '');
                    $namaProduk    = (string) ($item['nama_produk'] ?? '-');
                    $categoryLabel = $kategoriLabelMap[$category] ?? str_replace('_', ' ', $category);
                    $gambarUrl     = $resolveGambarUrl($item);
                    $hargaDasar    = (float) ($item['harga_dasar'] ?? 0);
                    $satuan        = (string) ($item['satuan'] ?? 'pcs');
                    $minOrder      = (int) ($item['min_order'] ?? 1);
                    $deskripsi     = trim((string) ($item['deskripsi'] ?? ''));

                    $orderUrl = $userRole === 'pelanggan'
                        ? site_url('pesanan/buat/' . $idKatalog)
                        : site_url('order/create/' . $idKatalog);
                    ?>
                    <article
                        class="katalog-card p-4 catalog-item flex flex-col"
                        data-category="<?= esc($category) ?>"
                        data-name="<?= esc(mb_strtolower($namaProduk)) ?>"
                        data-category-label="<?= esc(mb_strtolower($categoryLabel)) ?>"
                        data-search="<?= esc(mb_strtolower(trim($namaProduk . ' ' . $categoryLabel . ' ' . $deskripsi))) ?>">
                        <?php if ($gambarUrl !== null): ?>
                            <img
                                src="<?= esc($gambarUrl) ?>"
                                alt="<?= esc($namaProduk) ?>"
                                class="h-36 w-full rounded-2xl object-cover border border-slate-100 bg-slate-50">
                        <?php else: ?>
                            <div class="h-36 rounded-2xl bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center">
                                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>

                        <h4 class="mt-4 text-sm font-extrabold text-[#051747] uppercase leading-snug">
                            <?= esc($namaProduk) ?>
                        </h4>
                        <p class="mt-1 text-xs uppercase tracking-[0.12em] text-[#2E5CE6] font-bold">
                            <?= esc($categoryLabel) ?>
                        </p>

                        <?php if ($deskripsi !== ''): ?>
                            <p class="mt-2 text-xs text-slate-500 line-clamp-2 flex-1"><?= esc($deskripsi) ?></p>
                        <?php else: ?>
                            <div class="flex-1"></div>
                        <?php endif; ?>

                        <p class="mt-3 text-sm text-slate-500">Mulai</p>
                        <p class="text-2xl font-extrabold text-[#051747]">
                            Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?>
                            <span class="text-sm font-semibold text-slate-400">/<?= esc($satuan) ?></span>
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Min. <?= esc((string) $minOrder) ?> <?= esc($satuan) ?></p>

                        <?php if ($userRole === 'pelanggan' && $isLoggedIn): ?>
                            <a
                                href="<?= esc($orderUrl) ?>"
                                class="mt-4 w-full inline-flex items-center justify-center btn-katalog-outline py-2.5 text-[10px]">
                                Pilih &amp; Pesan Sekarang
                            </a>
                        <?php elseif ($userRole === 'admin'): ?>
                            <a
                                href="<?= esc(site_url('katalog/edit/' . $idKatalog)) ?>"
                                class="mt-4 w-full inline-flex items-center justify-center btn-katalog-outline py-2.5 text-[10px]">
                                Kelola Produk
                            </a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>

            <p id="catalogEmpty" class="hidden mt-8 text-center text-sm text-slate-500">
                Produk tidak ditemukan. Coba kata kunci lain atau pilih kategori berbeda.
            </p>
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
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (() => {
        const tabs = document.querySelectorAll('.catalog-tab');
        const cards = document.querySelectorAll('.catalog-item');
        const searchInput = document.getElementById('catalogSearchInput');
        const searchBtn = document.getElementById('catalogSearchBtn');
        const searchBar = document.getElementById('catalogSearchBar');
        const catalogEmpty = document.getElementById('catalogEmpty');
        const entriesSelect = document.getElementById('entriesSelect');
        const entriesInfo = document.getElementById('entriesInfo');
        const tablePagination = document.getElementById('tablePagination');
        const prevPageBtn = document.getElementById('prevPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');
        const pageInfo = document.getElementById('pageInfo');
        let activeTab = 'all';
        let currentPage = 1;

        if (searchBar && searchInput) {
            const setFocused = (focused) => searchBar.classList.toggle('focused', focused);
            searchInput.addEventListener('focus', () => setFocused(true));
            searchInput.addEventListener('blur', () => setFocused(false));
            searchBar.addEventListener('mousedown', (e) => {
                if (e.target === searchBar) {
                    e.preventDefault();
                    searchInput.focus();
                }
            });
        }

        const applyCatalogFilters = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            const matched = [];

            cards.forEach((card) => {
                const category = card.getAttribute('data-category') || '';
                const name = card.getAttribute('data-name') || '';
                const categoryLabel = card.getAttribute('data-category-label') || '';
                const searchBlob = card.getAttribute('data-search') || '';
                const tabMatch = activeTab === 'all' || category === activeTab;
                const searchMatch = query === '' ||
                    name.includes(query) ||
                    category.includes(query) ||
                    categoryLabel.includes(query) ||
                    searchBlob.includes(query);

                if (tabMatch && searchMatch) {
                    matched.push(card);
                } else {
                    card.classList.add('hidden');
                }
            });

            const pageSize = parseInt(entriesSelect?.value || '10', 10);
            const totalMatched = matched.length;
            const totalPages = Math.max(1, Math.ceil(totalMatched / pageSize));

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = startIndex + pageSize;

            matched.forEach((card, index) => {
                card.classList.toggle('hidden', index < startIndex || index >= endIndex);
            });

            if (catalogEmpty) {
                catalogEmpty.classList.toggle('hidden', totalMatched > 0);
            }

            if (entriesInfo) {
                if (totalMatched === 0) {
                    entriesInfo.textContent = 'Showing 0 entries';
                } else {
                    const from = startIndex + 1;
                    const to = Math.min(endIndex, totalMatched);
                    entriesInfo.textContent = `Showing ${from} to ${to} of ${totalMatched} entries`;
                }
            }

            if (tablePagination) {
                const showPagination = totalMatched > pageSize;
                tablePagination.classList.toggle('hidden', !showPagination);
                tablePagination.classList.toggle('flex', showPagination);
            }

            if (pageInfo) {
                pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
            }

            if (prevPageBtn) {
                prevPageBtn.disabled = currentPage <= 1;
            }

            if (nextPageBtn) {
                nextPageBtn.disabled = currentPage >= totalPages;
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activeTab = tab.getAttribute('data-tab') || 'all';

                tabs.forEach((btn) => {
                    btn.classList.remove('bg-[#051747]', 'text-white');
                    btn.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                });

                tab.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                tab.classList.add('bg-[#051747]', 'text-white');

                currentPage = 1;
                applyCatalogFilters();
            });
        });

        const runSearch = () => {
            currentPage = 1;
            applyCatalogFilters();
        };

        if (searchBtn) {
            searchBtn.addEventListener('click', runSearch);
        }

        if (searchInput) {
            searchInput.addEventListener('input', runSearch);
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    runSearch();
                }
            });
        }

        if (entriesSelect) {
            entriesSelect.addEventListener('change', () => {
                currentPage = 1;
                applyCatalogFilters();
            });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    applyCatalogFilters();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', () => {
                currentPage++;
                applyCatalogFilters();
            });
        }

        applyCatalogFilters();
    })();
</script>
<?= $this->endSection() ?>