<?= $this->extend('layouts/main') ?>
<?php $readOnly = (bool) ($readOnly ?? false); ?>

<?= $this->section('title') ?><?= esc($title ?? 'Kelola Form') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?>Kelola Form Template<?= $this->endSection() ?>

<?= $this->section('banner_title') ?><?= $readOnly ? 'Template Formulir' : 'Kelola Form' ?>: <?= esc($katalog['nama_produk'] ?? '-') ?><?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>
<?= $readOnly
    ? 'Lihat field formulir khusus untuk produk ini.'
    : 'Field hanya muncul saat pelanggan memesan produk ini, bukan produk lain meskipun namanya mirip.' ?>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    #deleteFieldModal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    #deleteFieldModal.is-open {
        opacity: 1;
    }

    #deleteFieldModal .delete-field-modal-panel {
        transform: scale(.95);
        opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
    }

    #deleteFieldModal.is-open .delete-field-modal-panel {
        transform: scale(1);
        opacity: 1;
    }

    #deleteFieldModal .delete-field-modal-card:hover {
        transform: none;
    }

    .btn-delete-field-cancel {
        background: #fff;
        color: #64748B;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-weight: 600;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-delete-field-cancel:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #475569;
    }

    .btn-delete-field-confirm {
        background: #EF4444;
        color: #fff;
        border-radius: 14px;
        font-weight: 700;
        transition: background-color .2s ease;
    }

    .btn-delete-field-confirm:hover {
        background: #DC2626;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
/**
 * @var array<string, mixed>              $katalog
 * @var list<array<string, mixed>>        $fields
 * @var string                            $title
 */
$fieldTypeLabels = [
    'text'     => 'Teks',
    'date'     => 'Tanggal',
    'time'     => 'Waktu',
    'textarea' => 'Area Teks',
    'file'     => 'Berkas',
];
?>

<div class="text-xs text-slate-400 mb-2">
    <a href="<?= site_url('katalog/kelola') ?>" class="hover:text-[#051747]">Katalog</a>
    <span class="mx-1">›</span>
    <span class="text-slate-500"><?= esc($katalog['nama_produk'] ?? '-') ?></span>
    <span class="mx-1">›</span>
    <span class="text-slate-500"><?= $readOnly ? 'Template Formulir' : 'Kelola Form' ?></span>
</div>

<?php if (!$readOnly): ?>
<p class="text-sm text-slate-500 mb-6">
    ID Katalog <span class="font-mono font-semibold text-[#051747]">#<?= esc((string) ($katalog['id_katalog'] ?? 0)) ?></span>
</p>
<?php else: ?>
<div class="mb-6"></div>
<?php endif; ?>

<?php if ($fields !== []): ?>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 mb-6 text-sm text-emerald-800">
        <?= esc((string) count($fields)) ?> field terdaftar untuk produk ini.
        Pelanggan melihatnya di halaman pesanan sebagai bagian <strong>Spesifikasi Khusus</strong>
        (URL: <span class="font-mono text-xs">order/create/<?= esc((string) ($katalog['id_katalog'] ?? 0)) ?></span>).
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 <?= $readOnly ? '' : 'xl:grid-cols-2 ' ?>gap-6">
    <?php if (!$readOnly): ?>
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-base font-semibold text-[#051747] mb-4">Tambah Field Baru</h3>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4 text-sm text-blue-700">
                Field ini akan muncul sebagai form khusus saat pelanggan memesan produk ini.
            </div>

            <form action="<?= site_url('form-template/simpan/' . (int) ($katalog['id_katalog'] ?? 0)) ?>" method="post" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="field_key" class="block text-sm font-semibold text-slate-700 mb-1.5">Field Key</label>
                    <input
                        type="text"
                        id="field_key"
                        name="field_key"
                        value="<?= esc(old('field_key')) ?>"
                        placeholder="contoh: nama_mempelai_pria"
                        pattern="[a-z0-9_]+"
                        class="input-field w-full px-3 py-2.5 font-mono text-sm"
                        required>
                    <p class="mt-1.5 text-xs text-slate-400">Hanya huruf kecil, angka, dan underscore (_)</p>
                </div>

                <div>
                    <label for="field_label" class="block text-sm font-semibold text-slate-700 mb-1.5">Label</label>
                    <input
                        type="text"
                        id="field_label"
                        name="field_label"
                        value="<?= esc(old('field_label')) ?>"
                        placeholder="contoh: Nama Mempelai Pria"
                        class="input-field w-full px-3 py-2.5"
                        required>
                </div>

                <div>
                    <label for="field_type" class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Field</label>
                    <select id="field_type" name="field_type" class="input-field w-full px-3 py-2.5" required>
                        <option value="">Pilih Tipe</option>
                        <?php foreach ($fieldTypeLabels as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('field_type') === $value ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="placeholder" class="block text-sm font-semibold text-slate-700 mb-1.5">Placeholder</label>
                    <input
                        type="text"
                        id="placeholder"
                        name="placeholder"
                        value="<?= esc(old('placeholder')) ?>"
                        class="input-field w-full px-3 py-2.5"
                        placeholder="Opsional">
                </div>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_required" value="1" <?= old('is_required') ? 'checked' : '' ?> class="w-4 h-4 accent-[#051747]">
                    <span class="text-sm font-medium text-slate-700">Wajib Diisi</span>
                </label>

                <button type="submit" class="btn-primary inline-flex w-full items-center justify-center gap-1.5 py-2.5 text-sm text-white">
                    <?= view('partials/ui_svg_icon', ['icon' => 'plus', 'class' => 'h-4 w-4 shrink-0']) ?>
                    Tambah Field
                </button>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-base font-semibold text-[#051747] mb-4">Field Terdaftar</h3>

        <?php if ($fields === []): ?>
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 text-sm text-slate-600">
                Produk ini menggunakan form umum. Pelanggan mengisi kolom Detail Pesanan biasa.
            </div>
        <?php else: ?>
            <div class="space-y-0">
                <?php foreach ($fields as $index => $f): ?>
                    <?php
                    $fieldType = (string) ($f['field_type'] ?? 'text');
                    $typeLabel = $fieldTypeLabels[$fieldType] ?? ucfirst($fieldType);
                    $isRequired = (int) ($f['is_required'] ?? 0) === 1;
                    $urutan     = (int) ($f['urutan'] ?? ($index + 1));
                    ?>
                    <?php if ($index > 0): ?>
                        <div class="border-t border-slate-100 my-4"></div>
                    <?php endif; ?>
                    <?php $idTemplate = (int) ($f['id_template'] ?? 0); ?>
                    <div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#051747] text-[11px] font-bold text-white">
                                    <?= esc((string) $urutan) ?>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-mono text-sm font-semibold text-[#051747] truncate">
                                        <?= esc((string) ($f['field_key'] ?? '-')) ?>
                                    </p>
                                    <p class="text-sm text-slate-600 truncate">
                                        <?= esc((string) ($f['field_label'] ?? '-')) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                <span class="inline-flex px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                                    <?= esc($typeLabel) ?>
                                </span>
                                <?php if ($isRequired): ?>
                                    <span class="inline-flex px-2.5 py-1 rounded-full bg-red-50 text-red-600 text-xs font-semibold">
                                        Wajib
                                    </span>
                                <?php endif; ?>
                                <?php if (!$readOnly): ?>
                                    <button
                                        type="button"
                                        onclick="toggleEdit(<?= $idTemplate ?>)"
                                        class="text-xs px-2 py-1 border border-slate-300 rounded hover:bg-slate-50">
                                        Ubah
                                    </button>
                                    <button
                                        type="button"
                                        data-open-delete-field-modal
                                        data-delete-id="<?= $idTemplate ?>"
                                        data-delete-label="<?= esc((string) ($f['field_label'] ?? $f['field_key'] ?? '-')) ?>"
                                        class="inline-flex text-xs px-3 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                                        Hapus
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!$readOnly): ?>
                            <div id="edit-<?= $idTemplate ?>" class="hidden mt-2 p-3 bg-slate-50 rounded-lg border">
                                <form method="POST" action="<?= site_url('form-template/update/' . $idTemplate) ?>">
                                    <?= csrf_field() ?>
                                    <div class="grid grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Label</label>
                                            <input
                                                type="text"
                                                name="field_label"
                                                value="<?= esc((string) ($f['field_label'] ?? '')) ?>"
                                                class="input-field w-full text-sm px-3 py-1.5 mt-1"
                                                required>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Placeholder</label>
                                            <input
                                                type="text"
                                                name="placeholder"
                                                value="<?= esc((string) ($f['placeholder'] ?? '')) ?>"
                                                class="input-field w-full text-sm px-3 py-1.5 mt-1">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-2 text-xs">
                                            <input
                                                type="checkbox"
                                                name="is_required"
                                                value="1"
                                                <?= $isRequired ? 'checked' : '' ?>>
                                            Wajib Diisi
                                        </label>
                                        <button
                                            type="submit"
                                            class="px-3 py-1.5 bg-[#051747] text-white text-xs rounded-lg hover:bg-[#2E5CE6]">
                                            Simpan
                                        </button>
                                        <button
                                            type="button"
                                            onclick="toggleEdit(<?= $idTemplate ?>)"
                                            class="px-3 py-1.5 border border-slate-300 text-xs rounded-lg hover:bg-slate-100">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<form id="deleteFieldForm" action="" method="post" class="hidden">
    <?= csrf_field() ?>
</form>

<div id="deleteFieldModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="deleteFieldModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-delete-field-modal></div>
    <div class="delete-field-modal-panel delete-field-modal-card relative w-full max-w-md bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg p-6 md:p-8 z-10">
        <div class="text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 border border-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h4 id="deleteFieldModalTitle" class="text-xl md:text-2xl font-extrabold text-[#051747] leading-tight">Hapus Field?</h4>
            <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                Apakah Anda yakin ingin hapus field <span id="deleteFieldLabel" class="font-semibold text-[#051747]"></span> ini?
            </p>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="button" data-close-delete-field-modal class="btn-delete-field-cancel flex-1 h-11 text-sm">
                Batal
            </button>
            <button type="button" id="deleteFieldConfirmBtn" class="btn-delete-field-confirm flex-1 h-11 text-sm">
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function toggleEdit(id) {
        const el = document.getElementById('edit-' + id);
        if (el) {
            el.classList.toggle('hidden');
        }
    }

    (function() {
        const deleteModal = document.getElementById('deleteFieldModal');
        const deleteForm = document.getElementById('deleteFieldForm');
        const deleteConfirmBtn = document.getElementById('deleteFieldConfirmBtn');
        const deleteLabelEl = document.getElementById('deleteFieldLabel');
        let closeTimer = null;

        const closeDeleteFieldModal = () => {
            if (!deleteModal) return;
            deleteModal.classList.remove('is-open');
            closeTimer = setTimeout(() => {
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                closeTimer = null;
            }, 250);
        };

        const openDeleteFieldModal = (id, label) => {
            if (!deleteModal || !deleteForm) return;

            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }

            deleteForm.action = '<?= site_url('form-template/hapus/') ?>' + id;
            if (deleteLabelEl) {
                deleteLabelEl.textContent = label;
            }

            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(() => {
                deleteModal.classList.add('is-open');
            });
        };

        document.querySelectorAll('[data-open-delete-field-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                openDeleteFieldModal(
                    btn.dataset.deleteId || '',
                    btn.dataset.deleteLabel || ''
                );
            });
        });

        document.querySelectorAll('[data-close-delete-field-modal]').forEach((btn) => {
            btn.addEventListener('click', closeDeleteFieldModal);
        });

        if (deleteConfirmBtn && deleteForm) {
            deleteConfirmBtn.addEventListener('click', () => {
                deleteForm.submit();
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && deleteModal?.classList.contains('is-open')) {
                closeDeleteFieldModal();
            }
        });
    })();
</script>
<?= $this->endSection() ?>