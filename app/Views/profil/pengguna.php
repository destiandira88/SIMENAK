<?php
/**
 * @var string                       $title
 * @var string                       $page_title
 * @var list<array<string, mixed>>   $users
 * @var string                       $filterRole
 * @var string                       $filterVerifikasi
 */
$verifikasiOptions = [
    ''           => 'Semua Verifikasi / Tier',
    'belum'      => 'Belum Terverifikasi',
'pemula'     => 'Terverifikasi-Pemula',
        'terpercaya' => 'Terverifikasi-Terpercaya',
    'suspend'    => 'Disuspend',
];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Pengguna') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Manajemen Pengguna') ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-xl font-extrabold text-[#051747]">Manajemen Pengguna</h2>
    <p class="text-sm text-slate-500 mt-1">List akun terdaftar di SIMENAK</p>
</div>

<div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between mb-4">
    <form method="get" action="<?= esc(site_url('pengguna')) ?>"
        class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
        <label for="filterRole" class="sr-only">Filter role</label>
        <select id="filterRole" name="role" onchange="this.form.submit()" class="filter-select min-w-[140px]">
            <option value="" <?= $filterRole === '' ? 'selected' : '' ?>>Semua Role</option>
            <?php foreach (['pelanggan', 'admin', 'keuangan', 'produksi', 'owner'] as $r): ?>
                <option value="<?= esc($r) ?>" <?= $filterRole === $r ? 'selected' : '' ?>><?= esc(ucfirst($r)) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="filterVerifikasi" class="sr-only">Filter verifikasi tier</label>
        <select id="filterVerifikasi" name="verifikasi" onchange="this.form.submit()" class="filter-select min-w-[200px]">
            <?php foreach ($verifikasiOptions as $val => $label): ?>
                <option value="<?= esc($val) ?>" <?= $filterVerifikasi === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <label for="penggunaSearch" class="sr-only">Cari pengguna</label>
    <div class="search-control w-full lg:w-auto lg:min-w-[260px]">
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <input
            id="penggunaSearch"
            type="search"
            placeholder="Cari nama, email, no. telp..."
            autocomplete="off">
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold">Email</th>
                    <th class="px-4 py-3 text-left font-semibold">Role</th>
                    <th class="px-4 py-3 text-left font-semibold">No. Telp</th>
                    <th class="px-4 py-3 text-left font-semibold">Terdaftar</th>
                    <th class="px-4 py-3 text-left font-semibold">Verifikasi / Tier</th>
                    <th class="px-4 py-3 text-left font-semibold w-16">Aksi</th>
                </tr>
            </thead>
            <tbody id="penggunaBody">
                <?php if ($users === []): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-500 text-sm">Tidak ada pengguna.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                        helper('notification');
                        $idPelangganRow = (int) ($u['id_pelanggan'] ?? 0);
                        $orderLancar    = $idPelangganRow > 0 ? countOrderLancarPerusahaan($idPelangganRow) : 0;
                        $canPromote     = $idPelangganRow > 0 && canPromotePerusahaanToTerpercaya([
                            'id_pelanggan'    => $idPelangganRow,
                            'is_verified'     => $u['is_verified'] ?? 0,
                            'tier_perusahaan' => $u['tier_perusahaan'] ?? null,
                            'is_suspended'    => $u['is_suspended'] ?? 0,
                        ]);
                        $searchBlob = strtolower(implode(' ', array_filter([
                            (string) ($u['nama'] ?? ''),
                            (string) ($u['email'] ?? ''),
                            (string) ($u['role'] ?? ''),
                            (string) ($u['no_telp'] ?? ''),
                            (string) ($u['nama_perusahaan'] ?? ''),
                            ((int) ($u['is_verified'] ?? 0) === 1) ? 'terverifikasi perusahaan' : 'belum terverifikasi',
                            (string) ($u['tier_perusahaan'] ?? ''),
                        ])));
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]"
                            data-search="<?= esc($searchBlob) ?>">
                            <td class="px-4 py-3 font-semibold text-[#051747]"><?= esc((string) ($u['nama'] ?? '-')) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($u['email'] ?? '-')) ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 capitalize">
                                    <?= esc((string) ($u['role'] ?? '-')) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($u['no_telp'] ?? '—')) ?></td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= !empty($u['created_at'])
                                    ? esc(date('d M Y', strtotime((string) $u['created_at'])))
                                    : '-' ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (($u['role'] ?? '') === 'pelanggan'): ?>
                                    <?php if ((int) ($u['is_verified'] ?? 0) === 1): ?>
                                        <div class="space-y-1">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                ✓ <?= esc((string) ($u['nama_perusahaan'] ?? 'Perusahaan')) ?>
                                            </span>
                                            <?php if (!empty($u['tier_perusahaan'])): ?>
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= esc(getTierPerusahaanBadgeClass((string) $u['tier_perusahaan'])) ?>">
                                                    <?= esc(getTierPerusahaanLabel((string) $u['tier_perusahaan'])) ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ((int) ($u['is_suspended'] ?? 0) === 1): ?>
                                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Suspend</span>
                                            <?php endif; ?>
                                            <?php if ((string) ($u['tier_perusahaan'] ?? '') === 'pemula'): ?>
                                                <p class="text-[10px] text-slate-400"><?= esc((string) $orderLancar) ?>/3 order lancar</p>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">Belum terverifikasi</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-300">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (($u['role'] ?? '') === 'pelanggan' && $idPelangganRow > 0):
                                    $isVerifiedPerusahaan = (int) ($u['is_verified'] ?? 0) === 1;
                                    $hasAdminActions = $canPromote
                                        || (string) ($u['tier_perusahaan'] ?? '') === 'terpercaya'
                                        || $isVerifiedPerusahaan;
                                ?>
                                    <?php if ($hasAdminActions): ?>
                                    <?= view('partials/pengguna_action_menu', [
                                        'idPelanggan'          => $idPelangganRow,
                                        'namaPerusahaan'       => (string) ($u['nama_perusahaan'] ?? ''),
                                        'namaPelanggan'        => (string) ($u['nama'] ?? ''),
                                        'canPromote'           => $canPromote,
                                        'isTerpercaya'         => (string) ($u['tier_perusahaan'] ?? '') === 'terpercaya',
                                        'isSuspended'          => (int) ($u['is_suspended'] ?? 0) === 1,
                                        'isVerifiedPerusahaan' => $isVerifiedPerusahaan,
                                    ]) ?>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-300">-</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-300">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="penggunaEmptyFilter" class="hidden">
                        <td colspan="7" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada data yang cocok dengan pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($users !== []): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'penggunaEntries',
            'entriesInfoId' => 'penggunaEntriesInfo',
            'paginationId'  => 'penggunaPagination',
            'prevPageId'    => 'penggunaPrevPage',
            'nextPageId'    => 'penggunaNextPage',
            'pageInfoId'    => 'penggunaPageInfo',
        ]) ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if ($users !== []): ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'penggunaSearch',
        tbodyId: 'penggunaBody',
        emptyFilterRowId: 'penggunaEmptyFilter',
        entriesId: 'penggunaEntries',
        entriesInfoId: 'penggunaEntriesInfo',
        paginationId: 'penggunaPagination',
        prevPageId: 'penggunaPrevPage',
        nextPageId: 'penggunaNextPage',
        pageInfoId: 'penggunaPageInfo',
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?php endif; ?>
<?= $this->endSection() ?>
