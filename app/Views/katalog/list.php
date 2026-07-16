<?php

/**
 * @var list<array<string, mixed>> $katalog
 * @var string                     $title
 */
$kategoriLabelMap = [
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
];
$kategoriTabShort = [
    'desain_grafis' => 'Desain',
    'cetak_digital' => 'Digital',
    'cetak_offset'  => 'Offset',
    'media_promosi' => 'Promosi',
];
/** Warna badge per kategori — selaras dengan order/index.php (bg-*-100 / text-*-800) */
$kategoriBadgeStyle = [
    'desain_grafis' => ['bg' => '#F3E8FF', 'text' => '#6B21A8'],
    'cetak_digital' => ['bg' => '#DBEAFE', 'text' => '#1E40AF'],
    'cetak_offset'  => ['bg' => '#FFEDD5', 'text' => '#9A3412'],
    'media_promosi' => ['bg' => '#DCFCE7', 'text' => '#166534'],
];
$kategoriBadgeStyleDefault = ['bg' => '#F1F5F9', 'text' => '#475569'];
$kategoriOrder = array_keys($kategoriLabelMap);

$userRole       = (string) session()->get('role');
$isLoggedIn     = (bool) session()->get('isLoggedIn');
$canCreateOrder = $canCreateOrder ?? true;

/** @var array<string, true> */
$categoriesPresent = [];
foreach ($katalog as $row) {
    $catKey = (string) ($row['kategori'] ?? '');
    if ($catKey !== '') {
        $categoriesPresent[$catKey] = true;
    }
}

$tabLabels = ['all' => 'Semua'];
foreach ($kategoriOrder as $catKey) {
    if (isset($categoriesPresent[$catKey])) {
        $tabLabels[$catKey] = $kategoriTabShort[$catKey] ?? ucwords(str_replace('_', ' ', $catKey));
    }
}
foreach (array_keys($categoriesPresent) as $catKey) {
    if (!isset($tabLabels[$catKey])) {
        $tabLabels[$catKey] = ucwords(str_replace('_', ' ', $catKey));
    }
}

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
<?= $this->section('page_title') ?>Katalog Percetakan<?= $this->endSection() ?>
<?= $this->section('banner_title') ?>Katalog Percetakan<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Pilih paket produk andalan kami, lalu lakukan pesanan secara digital.<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .catalog-tab-scroll {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .catalog-tab-scroll::-webkit-scrollbar {
        display: none;
    }

    .catalog-tab {
        flex-shrink: 0;
        white-space: nowrap;
    }

    .katalog-card {
        background: #fff;
        border: 1px solid #E8EDF3;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
        transition: transform .22s ease, box-shadow .22s ease;
        overflow: hidden;
    }

    .katalog-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.1);
    }

    .katalog-card-media {
        position: relative;
        aspect-ratio: 4 / 3;
        background: #F1F5F9;
        overflow: hidden;
    }

    .katalog-card-badge {
        position: absolute;
        top: 0.625rem;
        left: 0.625rem;
        z-index: 2;
        padding: 0.25rem 0.625rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
        line-height: 1.2;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }

    .katalog-card-media img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        pointer-events: none;
    }

    .katalog-card-image-zoom {
        position: absolute;
        inset: 0;
        z-index: 1;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
    }

    .katalog-card-image-zoom:focus-visible {
        outline: 2px solid #2E5CE6;
        outline-offset: -2px;
    }

    #catalogImageLightbox {
        position: fixed;
        inset: 0;
        z-index: 100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.82);
    }

    #catalogImageLightbox.is-open {
        display: flex;
    }

    #catalogImageLightbox img {
        max-width: min(100%, 56rem);
        max-height: 90vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 0.5rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    }

    #catalogImageLightboxClose {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border: 0;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.95);
        color: #051747;
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
    }

    #catalogImageLightboxCaption {
        position: absolute;
        bottom: 1.25rem;
        left: 50%;
        transform: translateX(-50%);
        max-width: 90%;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.92);
        color: #051747;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .katalog-card-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #F1F5F9;
    }

    .katalog-min-order-tag {
        display: inline-block;
        margin-top: 0.5rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        background: #F1F5F9;
        font-size: 0.6875rem;
        font-weight: 500;
        color: #475569;
        line-height: 1.3;
    }

    #catalogSearchBar {
        border-color: #CBD5E1;
        transition: border-color .2s, box-shadow .2s;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
    }

    #catalogSearchBar.focused {
        border-color: #2E5CE6 !important;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, 0.12);
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

<?php if ($userRole === 'admin'): ?>
    <div class="mt-4 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm">
        Anda login sebagai Admin.
        <a href="<?= site_url('katalog/kelola') ?>" class="font-semibold underline hover:text-[#051747]">Kelola katalog produk</a>
    </div>
<?php endif; ?>

<div class="mt-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div class="catalog-tab-scroll flex min-w-0 flex-nowrap gap-2 overflow-x-auto pb-0.5 lg:flex-wrap lg:overflow-visible">
        <?php foreach ($tabLabels as $key => $label): ?>
            <button
                type="button"
                class="catalog-tab px-4 py-2 rounded-full text-[11px] font-bold uppercase tracking-[0.14em] transition-colors <?= $key === 'all' ? 'bg-[#051747] text-white border border-[#051747]' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300' ?>"
                data-tab="<?= esc($key) ?>">
                <?= esc($label) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="w-full shrink-0 lg:w-auto lg:min-w-[260px] lg:max-w-[300px]">
        <label for="catalogSearchInput" class="sr-only">Cari katalog</label>
        <div
            id="catalogSearchBar"
            class="flex w-full items-center gap-2 rounded-full border bg-white py-1 pl-3.5 pr-1">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input
                id="catalogSearchInput"
                type="search"
                placeholder="Cari..."
                autocomplete="off"
                class="min-w-0 flex-1 border-0 bg-transparent py-2 text-sm text-[#051747] placeholder:text-slate-400 focus:outline-none focus:ring-0">
            <button
                type="button"
                id="catalogSearchBtn"
                class="shrink-0 rounded-full bg-[#051747] px-4 py-2 text-[10px] font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-[#2E5CE6]">
                Cari
            </button>
        </div>
    </div>
</div>

<?php if ($katalog === []): ?>
    <div class="mt-8 katalog-card p-12 text-center border border-slate-200">
        <div class="text-4xl mb-3">📦</div>
        <p class="text-sm font-medium text-slate-500">Belum ada produk aktif di katalog</p>
    </div>
<?php else: ?>
    <div class="mt-8">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 lg:gap-6" id="catalogGrid">
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
                    ? site_url('order/create/' . $idKatalog)
                    : site_url('order/create/' . $idKatalog);

                $badgeLabel = $kategoriTabShort[$category]
                    ?? ($categoryLabel !== '' ? $categoryLabel : 'Produk');
                $badgeStyle = $kategoriBadgeStyle[$category] ?? $kategoriBadgeStyleDefault;
                ?>
                <article
                    class="katalog-card catalog-item flex flex-col h-full"
                    data-category="<?= esc($category) ?>"
                    data-name="<?= esc(mb_strtolower($namaProduk)) ?>"
                    data-category-label="<?= esc(mb_strtolower($categoryLabel)) ?>"
                    data-search="<?= esc(mb_strtolower(trim($namaProduk . ' ' . $categoryLabel . ' ' . $deskripsi))) ?>">
                    <div class="katalog-card-media">
                        <span class="katalog-card-badge" style="background-color: <?= esc($badgeStyle['bg']) ?>; color: <?= esc($badgeStyle['text']) ?>">
                            <?= esc($badgeLabel) ?>
                        </span>
                        <?php if ($gambarUrl !== null): ?>
                            <button
                                type="button"
                                class="katalog-card-image-zoom js-catalog-image-zoom"
                                data-zoom-src="<?= esc($gambarUrl) ?>"
                                data-zoom-alt="<?= esc($namaProduk) ?>"
                                aria-label="Perbesar gambar <?= esc($namaProduk) ?>">
                                <img src="<?= esc($gambarUrl) ?>" alt="<?= esc($namaProduk) ?>">
                            </button>
                        <?php else: ?>
                            <div class="katalog-card-placeholder" aria-hidden="true">
                                <svg class="w-14 h-14 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.25">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col flex-1 p-4 sm:p-5">
                        <h3 class="text-sm font-extrabold text-[#051747] uppercase leading-snug tracking-wide">
                            <?= esc($namaProduk) ?>
                        </h3>
                        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-[#2E5CE6]">
                            <?= esc($categoryLabel) ?>
                        </p>

                        <?php if ($deskripsi !== ''): ?>
                            <p class="mt-2 text-xs leading-relaxed text-slate-500 line-clamp-2 flex-1"><?= esc($deskripsi) ?></p>
                        <?php else: ?>
                            <div class="mt-2 flex-1" aria-hidden="true"></div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <p class="text-xs text-slate-500">Mulai</p>
                            <p class="mt-0.5 text-xl sm:text-2xl font-extrabold text-[#051747] leading-tight">
                                Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?>
                                <span class="text-sm font-semibold text-slate-400">/<?= esc($satuan) ?></span>
                            </p>
                            <span class="katalog-min-order-tag">Min. <?= esc((string) $minOrder) ?> <?= esc($satuan) ?></span>
                        </div>

                        <?php if ($userRole === 'pelanggan' && $isLoggedIn): ?>
                            <?php if ($canCreateOrder): ?>
                                <a
                                    href="<?= esc($orderUrl) ?>"
                                    class="mt-4 w-full inline-flex items-center justify-center rounded-full border-[1.5px] border-[#051747] bg-white py-2.5 text-[10px] font-bold uppercase tracking-[0.08em] text-[#051747] transition-colors hover:bg-[#051747] hover:text-white">
                                    Pilih &amp; Pesan Sekarang
                                </a>
                            <?php else: ?>
                                <span class="mt-4 w-full inline-flex items-center justify-center rounded-full border border-amber-200 bg-amber-50 py-2.5 text-[10px] font-bold uppercase tracking-wide text-amber-800 cursor-not-allowed">
                                    Menunggu Verifikasi
                                </span>
                            <?php endif; ?>
                        <?php elseif ($userRole === 'admin'): ?>
                            <a
                                href="<?= esc(site_url('katalog/edit/' . $idKatalog)) ?>"
                                class="mt-4 w-full inline-flex items-center justify-center rounded-full border-[1.5px] border-[#051747] bg-white py-2.5 text-[10px] font-bold uppercase tracking-[0.08em] text-[#051747] transition-colors hover:bg-[#051747] hover:text-white">
                                Kelola Produk
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <p id="catalogEmpty" class="hidden mt-10 text-center text-sm text-slate-500">
            Produk tidak ditemukan. Coba kata kunci lain atau pilih kategori berbeda.
        </p>

        <?= view('partials/admin_data_table_footer') ?>
    </div>

    <div id="catalogImageLightbox" role="dialog" aria-modal="true" aria-label="Pratinjau gambar produk">
        <button type="button" id="catalogImageLightboxClose" aria-label="Tutup pratinjau">&times;</button>
        <img id="catalogImageLightboxImg" src="" alt="">
        <p id="catalogImageLightboxCaption" class="hidden"></p>
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

        const setTabActiveStyle = (tab, isActive) => {
            tab.classList.toggle('bg-[#051747]', isActive);
            tab.classList.toggle('text-white', isActive);
            tab.classList.toggle('border-[#051747]', isActive);
            tab.classList.toggle('bg-white', !isActive);
            tab.classList.toggle('text-slate-600', !isActive);
            tab.classList.toggle('border-slate-200', !isActive);
        };

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
                    entriesInfo.textContent = 'Menampilkan 0 data';
                } else {
                    const from = startIndex + 1;
                    const to = Math.min(endIndex, totalMatched);
                    entriesInfo.textContent = `Menampilkan ${from} sampai ${to} dari ${totalMatched} data`;
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

                tabs.forEach((btn) => setTabActiveStyle(btn, btn === tab));

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

        const lightbox = document.getElementById('catalogImageLightbox');
        const lightboxImg = document.getElementById('catalogImageLightboxImg');
        const lightboxCaption = document.getElementById('catalogImageLightboxCaption');
        const lightboxClose = document.getElementById('catalogImageLightboxClose');

        const closeLightbox = () => {
            if (!lightbox) return;
            lightbox.classList.remove('is-open');
            document.body.classList.remove('overflow-hidden');
            if (lightboxImg) {
                lightboxImg.src = '';
                lightboxImg.alt = '';
            }
            if (lightboxCaption) {
                lightboxCaption.textContent = '';
                lightboxCaption.classList.add('hidden');
            }
        };

        const openLightbox = (src, alt) => {
            if (!lightbox || !lightboxImg || !src) return;
            lightboxImg.src = src;
            lightboxImg.alt = alt || 'Gambar produk';
            if (lightboxCaption) {
                if (alt) {
                    lightboxCaption.textContent = alt;
                    lightboxCaption.classList.remove('hidden');
                } else {
                    lightboxCaption.classList.add('hidden');
                }
            }
            lightbox.classList.add('is-open');
            document.body.classList.add('overflow-hidden');
            lightboxClose?.focus();
        };

        document.querySelectorAll('.js-catalog-image-zoom').forEach((btn) => {
            btn.addEventListener('click', () => {
                openLightbox(btn.getAttribute('data-zoom-src') || '', btn.getAttribute('data-zoom-alt') || '');
            });
        });

        lightboxClose?.addEventListener('click', closeLightbox);

        lightbox?.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && lightbox?.classList.contains('is-open')) {
                closeLightbox();
            }
        });
    })();
</script>
<?= $this->endSection() ?>
