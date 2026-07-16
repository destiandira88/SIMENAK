<?php
/**
 * @var string                       $title
 * @var string                       $page_title
 * @var list<array<string, mixed>>   $users
 * @var string                       $filterSegment
 * @var string                       $filterJenis
 * @var int                          $countSemua
 * @var int                          $countPelanggan
 * @var int                          $countInternal
 * @var string                       $viewerRole
 * @var bool                         $canCreateStaff
 * @var bool                         $canCreatePelanggan
 * @var bool                         $canManagePelanggan
 * @var array<int, true>             $pelangganActiveOrdersMap
 */
$jenisOptions = [
    ''              => 'Semua Skema Pelanggan',
    'perseorangan'  => 'Perseorangan',
    'kerjasama'     => 'Kerja Sama Perusahaan',
];

$generatedPassword = session()->getFlashdata('pengguna_password_generated');
$generatedEmail    = session()->getFlashdata('pengguna_password_email');
$generatedNama     = session()->getFlashdata('pengguna_password_nama');
$openTambahModal   = session()->getFlashdata('open_tambah_pengguna');
$openEditPelanggan = (int) session()->getFlashdata('open_edit_pelanggan');
$openEditStaff       = (int) session()->getFlashdata('open_edit_staff');
$pelangganActiveOrdersMap = (array) ($pelangganActiveOrdersMap ?? []);
$filterSegment            = (string) ($filterSegment ?? 'semua');
$segmentCounts            = [
    'semua'     => (int) ($countSemua ?? 0),
    'pelanggan' => (int) ($countPelanggan ?? 0),
    'internal'  => (int) ($countInternal ?? 0),
];
$segmentTabs              = [
    'semua'     => 'Semua',
    'pelanggan' => 'Pelanggan',
    'internal'  => 'Internal',
];

$penggunaSegmentQuery = static function (string $segmentKey) use ($filterJenis): string {
    $params = [];
    if ($segmentKey !== 'semua') {
        $params['segment'] = $segmentKey;
    }
    $jenisVal = (string) ($filterJenis ?? '');
    if ($jenisVal !== '' && $segmentKey !== 'internal') {
        $params['jenis'] = $jenisVal;
    }

    $query = http_build_query($params);

    return site_url('pengguna' . ($query !== '' ? '?' . $query : ''));
};
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Pengguna') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Manajemen Pengguna') ?><?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Manajemen Pengguna<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>
<?php if (($viewerRole ?? '') === 'admin'): ?>
Kelola akun pelanggan — daftar staff internal tampil read-only.
<?php elseif (($viewerRole ?? '') === 'owner'): ?>
Kelola staff internal — data pelanggan tampil read-only.
<?php else: ?>
Kelola akun pelanggan & staff internal.
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<style>
.pengguna-tab {
    padding: 10px 4px;
    margin-right: 24px;
    font-size: 14px;
    font-weight: 600;
    color: #64748B;
    border-bottom: 2px solid transparent;
    transition: color .2s, border-color .2s;
    white-space: nowrap;
}

.pengguna-tab:hover {
    color: #051747;
}

.pengguna-tab.is-active {
    color: #2E5CE6;
    border-bottom-color: #2E5CE6;
}

#kerjasamaModal { display: none; }
#kerjasamaModal.is-open { display: flex; }
#tambahPenggunaModal { display: none; }
#tambahPenggunaModal.is-open { display: flex; }
#editPelangganModal { display: none; }
#editPelangganModal.is-open { display: flex; }
#editStaffModal { display: none; }
#editStaffModal.is-open { display: flex; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-4 flex justify-end">
    <button type="button" id="openTambahPenggunaModal"
        class="inline-flex items-center justify-center gap-1.5 bg-[#051747] text-white text-sm font-bold px-5 py-2.5 rounded-full hover:bg-[#2E5CE6] transition-colors shrink-0">
        <?= view('partials/ui_svg_icon', ['icon' => 'plus', 'class' => 'h-4 w-4 shrink-0']) ?>
        <?= ($canCreateStaff ?? false) ? 'Tambah Staff' : 'Tambah Pelanggan' ?>
    </button>
</div>

<?php if ($generatedPassword !== null && $generatedPassword !== ''): ?>
    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900 mb-4">
        <p class="font-bold text-[#051747] flex items-center gap-2 mb-2">
            <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            Kata Sandi Akun Baru (tampil sekali)
        </p>
        <p>Akun: <strong><?= esc((string) $generatedNama) ?></strong> · Email: <strong><?= esc((string) $generatedEmail) ?></strong></p>
        <p class="mt-1">Password: <code class="bg-white/80 px-2 py-0.5 rounded font-mono text-[#051747]"><?= esc((string) $generatedPassword) ?></code></p>
        <p class="text-xs text-amber-800 mt-2">Sampaikan ke pengguna melalui channel aman. Password tidak ditampilkan lagi setelah halaman di-refresh.</p>
    </div>
<?php endif; ?>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4 border-b border-slate-200">
    <div class="flex flex-wrap items-end gap-0 overflow-x-auto" role="tablist" aria-label="Segment pengguna">
        <?php foreach ($segmentTabs as $segmentKey => $segmentLabel): ?>
            <a href="<?= esc($penggunaSegmentQuery($segmentKey)) ?>"
                class="pengguna-tab <?= $filterSegment === $segmentKey ? 'is-active' : '' ?>"
                role="tab"
                aria-selected="<?= $filterSegment === $segmentKey ? 'true' : 'false' ?>">
                <?= esc($segmentLabel) ?> (<?= esc((string) ($segmentCounts[$segmentKey] ?? 0)) ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:flex-wrap sm:justify-end mb-3 sm:mb-4">
        <?php if ($filterSegment !== 'internal'): ?>
        <form method="get" action="<?= esc(site_url('pengguna')) ?>"
            class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
            <?php if ($filterSegment !== 'semua'): ?>
                <input type="hidden" name="segment" value="<?= esc($filterSegment) ?>">
            <?php endif; ?>
            <label for="filterJenis" class="sr-only">Filter skema pelanggan</label>
            <select id="filterJenis" name="jenis" onchange="this.form.submit()" class="filter-select min-w-[220px]">
                <?php foreach ($jenisOptions as $val => $label): ?>
                    <option value="<?= esc($val) ?>" <?= ($filterJenis ?? '') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>

        <label for="penggunaSearch" class="sr-only">Cari pengguna</label>
        <div class="search-control w-full sm:w-auto">
            <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
            <input id="penggunaSearch" type="search" placeholder="Cari nama, email, no. telp..." autocomplete="off">
        </div>
    </div>
</div>

<div class="admin-data-table-wrap mb-4">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Nama</th>
                    <th class="px-4 py-3 text-left font-semibold">Email</th>
                    <th class="px-4 py-3 text-left font-semibold">Role</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">No. Telp</th>
                    <th class="px-4 py-3 text-left font-semibold">Terdaftar</th>
                    <th class="px-4 py-3 text-left font-semibold">Jenis Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold w-16">Aksi</th>
                </tr>
            </thead>
            <tbody id="penggunaBody">
                <?php if ($users === []): ?>
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-500 text-sm">Tidak ada pengguna.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $u): ?>
                        <?php
                        helper('notification');
                        $idPelangganRow = (int) ($u['id_pelanggan'] ?? 0);
                        $rowRole        = (string) ($u['role'] ?? '');
                        $idUserRow      = (int) ($u['id_user'] ?? 0);
                        $isStaffInternal = in_array($rowRole, ['admin', 'keuangan', 'produksi'], true);
                        $isActive       = (int) ($u['is_active'] ?? 1) === 1;
                        $canEditStaff   = false;
                        $canToggleStaff = false;
                        $canTogglePelanggan = false;
                        $hasActiveOrders    = $idPelangganRow > 0 && isset($pelangganActiveOrdersMap[$idPelangganRow]);

                        if ($rowRole === 'pelanggan' && ($canManagePelanggan ?? false)) {
                            $canTogglePelanggan = true;
                        }

                        if ($isStaffInternal && ($viewerRole ?? '') === 'owner') {
                            $canEditStaff   = true;
                            $canToggleStaff = true;
                        }

                        $isKerjasama    = $idPelangganRow > 0 && pelangganIsKerjasamaPerusahaan($u);
                        $searchBlob = strtolower(implode(' ', array_filter([
                            (string) ($u['nama'] ?? ''),
                            (string) ($u['email'] ?? ''),
                            (string) ($u['role'] ?? ''),
                            (string) ($u['no_telp'] ?? ''),
                            (string) ($u['nama_perusahaan'] ?? ''),
                            $isKerjasama ? 'kerja sama perusahaan' : 'perseorangan',
                        ])));
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]" data-search="<?= esc($searchBlob) ?>">
                            <td class="row-num px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]"><?= esc((string) ($u['nama'] ?? '-')) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($u['email'] ?? '-')) ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 capitalize"><?= esc($rowRole !== '' ? $rowRole : '-') ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($isStaffInternal || $rowRole === 'owner' || $rowRole === 'pelanggan'): ?>
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Aktif</span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-200 text-slate-600">Nonaktif</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-300">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($u['no_telp'] ?? '—')) ?></td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= !empty($u['created_at']) ? esc(date('d M Y', strtotime((string) $u['created_at']))) : '-' ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (($u['role'] ?? '') === 'pelanggan'): ?>
                                    <?php if ($isKerjasama): ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                            Kerja Sama · <?= esc((string) ($u['nama_perusahaan'] ?? 'Perusahaan')) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Perseorangan</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-300">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($rowRole === 'pelanggan' && $idPelangganRow > 0 && ($canManagePelanggan ?? false)): ?>
                                    <?= view('partials/pengguna_action_menu', [
                                        'idPelanggan'        => $idPelangganRow,
                                        'namaPerusahaan'     => (string) ($u['nama_perusahaan'] ?? ''),
                                        'namaPelanggan'      => (string) ($u['nama'] ?? ''),
                                        'emailPelanggan'     => (string) ($u['email'] ?? ''),
                                        'telpPelanggan'      => (string) ($u['no_telp'] ?? ''),
                                        'alamatPelanggan'    => (string) ($u['alamat'] ?? ''),
                                        'isKerjasamaAktif'   => $isKerjasama,
                                        'isActive'           => $isActive,
                                        'canTogglePelanggan' => $canTogglePelanggan,
                                        'hasActiveOrders'    => $hasActiveOrders,
                                    ]) ?>
                                <?php elseif ($canEditStaff || $canToggleStaff): ?>
                                    <?= view('partials/pengguna_staff_action_menu', [
                                        'idUser'     => $idUserRow,
                                        'namaStaff'  => (string) ($u['nama'] ?? ''),
                                        'emailStaff' => (string) ($u['email'] ?? ''),
                                        'staffRole'  => $rowRole,
                                        'isActive'   => $isActive,
                                        'canEdit'    => $canEditStaff,
                                        'canToggle'  => $canToggleStaff,
                                    ]) ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-300">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="penggunaEmptyFilter" class="hidden">
                        <td colspan="9" class="py-12 text-center text-slate-500 text-sm">Tidak ada data yang cocok dengan pencarian.</td>
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

<div id="kerjasamaModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/50">
    <div class="relative w-full max-w-lg bg-white rounded-[20px] shadow-lg border border-slate-100 p-6 max-h-[90vh] overflow-y-auto">
        <button type="button" id="kerjasamaModalClose" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" aria-label="Tutup">&times;</button>
        <h3 class="text-lg font-bold text-[#051747] pr-8">Tetapkan Kerja Sama Perusahaan</h3>
        <p class="text-sm text-slate-500 mt-1 mb-4">Pelanggan: <strong id="kerjasamaModalNama">—</strong></p>
        <div id="kerjasamaModalFormWrap"></div>
    </div>
</div>

<template id="kerjasamaFormTemplate">
    <?= view('partials/kerjasama_perusahaan_admin_form', [
        'formAction' => site_url('pengguna/0/tetapkan-kerjasama'),
        'namaPelanggan' => '',
    ]) ?>
</template>

<div id="editPelangganModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/50">
    <div class="relative w-full max-w-lg bg-white rounded-[20px] shadow-lg border border-slate-100 p-6 max-h-[90vh] overflow-y-auto">
        <button type="button" id="editPelangganModalClose" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" aria-label="Tutup">&times;</button>
        <h3 class="text-lg font-bold text-[#051747] pr-8">Edit Data Pelanggan</h3>
        <p class="text-sm text-slate-500 mt-1 mb-4">Pelanggan: <strong id="editPelangganModalNama">—</strong></p>
        <div id="editPelangganFormWrap"></div>
    </div>
</div>

<template id="editPelangganFormTemplate">
    <?= view('partials/pengguna_edit_pelanggan_form', [
        'formAction' => site_url('pengguna/pelanggan/update/0'),
    ]) ?>
</template>

<div id="editStaffModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/50">
    <div class="relative w-full max-w-lg bg-white rounded-[20px] shadow-lg border border-slate-100 p-6 max-h-[90vh] overflow-y-auto">
        <button type="button" id="editStaffModalClose" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" aria-label="Tutup">&times;</button>
        <h3 class="text-lg font-bold text-[#051747] pr-8">Edit Data Staff</h3>
        <p class="text-sm text-slate-500 mt-1 mb-4">Staff: <strong id="editStaffModalNama">—</strong></p>
        <div id="editStaffFormWrap"></div>
    </div>
</div>

<template id="editStaffFormTemplate">
    <?= view('partials/pengguna_edit_staff_form', [
        'formAction' => site_url('pengguna/staff/update/0'),
    ]) ?>
</template>

<?= view('partials/pengguna_tambah_modal', [
    'canCreateStaff'     => (bool) ($canCreateStaff ?? false),
    'canCreatePelanggan' => (bool) ($canCreatePelanggan ?? false),
]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= view('partials/pelanggan_akun_field_scripts') ?>
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
        rowSelector: 'tr.data-table-row',
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?php endif; ?>
<script>
(function () {
    const modal = document.getElementById('kerjasamaModal');
    const wrap = document.getElementById('kerjasamaModalFormWrap');
    const tpl = document.getElementById('kerjasamaFormTemplate');
    const namaEl = document.getElementById('kerjasamaModalNama');
    const closeBtn = document.getElementById('kerjasamaModalClose');
    if (!modal || !wrap || !tpl) return;

    function closeModal() {
        modal.classList.remove('is-open');
        wrap.innerHTML = '';
    }

    function formatNpwpValue(raw) {
        const digits = String(raw || '').replace(/\D/g, '').slice(0, 15);
        if (digits.length <= 2) return digits;
        if (digits.length <= 5) return digits.slice(0, 2) + '.' + digits.slice(2);
        if (digits.length <= 8) return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5);
        if (digits.length <= 9) {
            return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5, 8) + '.' + digits.slice(8);
        }
        if (digits.length <= 12) {
            return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5, 8) + '.'
                + digits.slice(8, 9) + '-' + digits.slice(9);
        }
        return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5, 8) + '.'
            + digits.slice(8, 9) + '-' + digits.slice(9, 12) + '.' + digits.slice(12);
    }

    function bindKerjasamaFormValidation(form) {
        if (!form || form.dataset.kerjasamaBound === '1') {
            return;
        }
        form.dataset.kerjasamaBound = '1';

        const phonePattern = /^(\+62|08|022)[0-9]{8,13}$/;
        const waInput = form.querySelector('[data-wa-perusahaan-input]');
        const npwpInput = form.querySelector('[data-npwp-input]');

        if (waInput) {
            waInput.addEventListener('input', function () {
                let value = waInput.value.replace(/[^\d+]/g, '');
                if (value.includes('+')) {
                    value = '+' + value.replace(/\+/g, '');
                }
                waInput.value = value.slice(0, 20);
            });
        }

        if (npwpInput) {
            npwpInput.addEventListener('input', function () {
                npwpInput.value = formatNpwpValue(npwpInput.value);
            });
        }

        form.addEventListener('submit', function (event) {
            const wa = waInput?.value.trim() || '';
            if (!phonePattern.test(wa)) {
                event.preventDefault();
                window.alert('Format no. HP/WA perusahaan harus berupa angka dan diawali dengan 08, +62, atau 022 (8–13 digit setelah awalan).');
                waInput?.focus();
                return;
            }

            const npwp = npwpInput?.value.trim() || '';
            if (npwp !== '') {
                const digits = npwp.replace(/\D/g, '');
                if (digits.length !== 15) {
                    event.preventDefault();
                    window.alert('Format NPWP tidak valid. Masukkan 15 digit angka.');
                    npwpInput?.focus();
                }
            }
        });
    }

    document.querySelectorAll('.js-open-kerjasama-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-pelanggan-id');
            const nama = btn.getAttribute('data-pelanggan-nama') || '—';
            if (!id) return;
            wrap.innerHTML = tpl.innerHTML;
            const form = wrap.querySelector('form');
            if (form) {
                form.action = form.action.replace('/0/', '/' + id + '/');
                bindKerjasamaFormValidation(form);
            }
            if (namaEl) namaEl.textContent = nama;
            modal.classList.add('is-open');
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
})();
</script>
<script>
(function () {
    const modal = document.getElementById('tambahPenggunaModal');
    const openBtn = document.getElementById('openTambahPenggunaModal');
    const closeBtn = document.getElementById('tambahPenggunaModalClose');
    const cancelBtn = document.getElementById('tambahPenggunaModalCancel');
    const form = document.getElementById('formTambahPengguna');
    if (!modal || !form) return;

    const pelangganFields = document.getElementById('tambahPelangganFields');
    const staffFields = document.getElementById('tambahStaffFields');
    const passwordManualWrap = document.getElementById('tpPasswordManualWrap');
    const passwordAutoNote = document.getElementById('tpPasswordAutoNote');
    const telpInput = document.getElementById('tp_no_telp');
    const alamatInput = document.getElementById('tp_alamat');
    const passwordInput = document.getElementById('tp_password');
    const passwordConfirmInput = document.getElementById('tp_password_confirm');
    const staffRoleSelect = document.getElementById('staff_role');

    function accountType() {
        const checked = form.querySelector('input[name="account_type"]:checked');
        if (checked) {
            return checked.value;
        }
        const hidden = form.querySelector('input[name="account_type"][type="hidden"]');
        return hidden ? hidden.value : 'pelanggan';
    }

    function passwordMode() {
        const checked = form.querySelector('input[name="password_mode"]:checked');
        return checked ? checked.value : 'manual';
    }

    function syncPelangganRequired(isPelanggan) {
        if (telpInput) telpInput.required = isPelanggan;
        if (alamatInput) alamatInput.required = isPelanggan;
        if (staffRoleSelect) staffRoleSelect.disabled = isPelanggan;
        if (isPelanggan) {
            const manual = passwordMode() === 'manual';
            if (passwordInput) {
                passwordInput.required = manual;
                passwordInput.disabled = !manual;
            }
            if (passwordConfirmInput) {
                passwordConfirmInput.required = manual;
                passwordConfirmInput.disabled = !manual;
            }
        } else {
            if (passwordInput) {
                passwordInput.required = false;
                passwordInput.disabled = true;
            }
            if (passwordConfirmInput) {
                passwordConfirmInput.required = false;
                passwordConfirmInput.disabled = true;
            }
        }
    }

    function syncPasswordMode() {
        if (accountType() !== 'pelanggan') return;
        const manual = passwordMode() === 'manual';
        if (passwordManualWrap) passwordManualWrap.classList.toggle('hidden', !manual);
        if (passwordAutoNote) passwordAutoNote.classList.toggle('hidden', manual);
        syncPelangganRequired(true);
    }

    function syncAccountType() {
        const isPelanggan = accountType() === 'pelanggan';
        if (pelangganFields) pelangganFields.classList.toggle('hidden', !isPelanggan);
        if (staffFields) staffFields.classList.toggle('hidden', isPelanggan);
        syncPelangganRequired(isPelanggan);
        if (isPelanggan) syncPasswordMode();
    }

    function openModal() {
        modal.classList.add('is-open');
        syncAccountType();
        window.bindPelangganPhoneInputs(modal);
    }

    function closeModal() {
        modal.classList.remove('is-open');
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    form.querySelectorAll('[data-account-type-radio]').forEach(function (radio) {
        radio.addEventListener('change', syncAccountType);
    });
    form.querySelectorAll('[data-password-mode-radio]').forEach(function (radio) {
        radio.addEventListener('change', syncPasswordMode);
    });

    form.querySelectorAll('.js-toggle-password').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = btn.getAttribute('data-target');
            const input = targetId ? document.getElementById(targetId) : null;
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    form.addEventListener('submit', function (e) {
        if (accountType() !== 'pelanggan') return;

        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
            return;
        }

        const err = window.validatePelangganAkunFormFields(form);
        if (err) {
            e.preventDefault();
            alert(err);
            return;
        }

        if (passwordMode() === 'manual') {
            const pwd = passwordInput?.value || '';
            const pwdConfirm = passwordConfirmInput?.value || '';
            if (pwd.length < 8) {
                e.preventDefault();
                alert('Kata sandi minimal 8 karakter.');
                return;
            }
            if (pwd !== pwdConfirm) {
                e.preventDefault();
                alert('Konfirmasi kata sandi tidak sama.');
            }
        }
    });

    syncAccountType();
    window.bindPelangganPhoneInputs(modal);

    <?php if ($openTambahModal): ?>
    openModal();
    <?php endif; ?>
})();
</script>
<script>
(function () {
    const modal = document.getElementById('editPelangganModal');
    const wrap = document.getElementById('editPelangganFormWrap');
    const tpl = document.getElementById('editPelangganFormTemplate');
    const namaEl = document.getElementById('editPelangganModalNama');
    const closeBtn = document.getElementById('editPelangganModalClose');
    if (!modal || !wrap || !tpl) return;

    function closeModal() {
        modal.classList.remove('is-open');
        wrap.innerHTML = '';
    }

    function openEditModal(btn) {
        const id = btn.getAttribute('data-pelanggan-id');
        if (!id) return;

        wrap.innerHTML = tpl.innerHTML;
        const form = wrap.querySelector('form');
        if (form) {
            form.action = form.action.replace(/\/update\/0$/, '/update/' + id);
        }

        const setVal = function (name, value) {
            const el = wrap.querySelector('[name="' + name + '"]');
            if (el) el.value = value || '';
        };

        setVal('nama', btn.getAttribute('data-pelanggan-nama'));
        setVal('email', btn.getAttribute('data-pelanggan-email'));
        setVal('no_telp', btn.getAttribute('data-pelanggan-telp'));
        setVal('alamat', btn.getAttribute('data-pelanggan-alamat'));

        if (namaEl) namaEl.textContent = btn.getAttribute('data-pelanggan-nama') || '—';

        wrap.querySelector('#editPelangganModalCancel')?.addEventListener('click', closeModal);
        window.bindPelangganPhoneInputs(wrap);
        modal.classList.add('is-open');
    }

    document.querySelectorAll('.js-open-edit-pelanggan-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openEditModal(btn);
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    <?php if ($openEditPelanggan > 0): ?>
    (function () {
        const btn = document.querySelector('.js-open-edit-pelanggan-modal[data-pelanggan-id="<?= (int) $openEditPelanggan ?>"]');
        if (btn) {
            openEditModal(btn);
            const setVal = function (name, value) {
                const el = wrap.querySelector('[name="' + name + '"]');
                if (el && value !== '') el.value = value;
            };
            setVal('nama', <?= json_encode(old('nama', '')) ?>);
            setVal('email', <?= json_encode(old('email', '')) ?>);
            setVal('no_telp', <?= json_encode(old('no_telp', '')) ?>);
            setVal('alamat', <?= json_encode(old('alamat', '')) ?>);
        }
    })();
    <?php endif; ?>
})();
</script>
<script>
(function () {
    const modal = document.getElementById('editStaffModal');
    const wrap = document.getElementById('editStaffFormWrap');
    const tpl = document.getElementById('editStaffFormTemplate');
    const namaEl = document.getElementById('editStaffModalNama');
    const closeBtn = document.getElementById('editStaffModalClose');
    if (!modal || !wrap || !tpl) return;

    function closeModal() {
        modal.classList.remove('is-open');
        wrap.innerHTML = '';
    }

    function openEditModal(btn) {
        const id = btn.getAttribute('data-staff-id');
        if (!id) return;

        wrap.innerHTML = tpl.innerHTML;
        const form = wrap.querySelector('form');
        if (form) {
            form.action = form.action.replace(/\/update\/0$/, '/update/' + id);
        }

        const setVal = function (name, value) {
            const el = wrap.querySelector('[name="' + name + '"]');
            if (el) el.value = value || '';
        };

        setVal('nama', btn.getAttribute('data-staff-nama'));
        setVal('email', btn.getAttribute('data-staff-email'));
        setVal('staff_role', btn.getAttribute('data-staff-role'));

        if (namaEl) namaEl.textContent = btn.getAttribute('data-staff-nama') || '—';

        wrap.querySelector('#editStaffModalCancel')?.addEventListener('click', closeModal);
        modal.classList.add('is-open');
    }

    document.querySelectorAll('.js-open-edit-staff-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openEditModal(btn);
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });

    <?php if ($openEditStaff > 0): ?>
    (function () {
        const btn = document.querySelector('.js-open-edit-staff-modal[data-staff-id="<?= (int) $openEditStaff ?>"]');
        if (btn) {
            openEditModal(btn);
            const setVal = function (name, value) {
                const el = wrap.querySelector('[name="' + name + '"]');
                if (el && value !== '') el.value = value;
            };
            setVal('nama', <?= json_encode(old('nama', '')) ?>);
            setVal('email', <?= json_encode(old('email', '')) ?>);
            setVal('staff_role', <?= json_encode(old('staff_role', '')) ?>);
        }
    })();
    <?php endif; ?>
})();
</script>
<?= $this->endSection() ?>
