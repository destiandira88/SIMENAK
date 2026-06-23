<?php

/**
 * @var array<string, mixed>              $order
 * @var list<array<string, mixed>>       $attrs
 * @var list<array<string, mixed>>       $revisList
 * @var array<string, mixed>|null        $lastRevis
 * @var bool                             $canUpload
 * @var bool                             $readOnly
 */
$readOnly    = (bool) ($readOnly ?? false);
$kodeOrder   = (string) ($order['kode_order'] ?? '');
$idOrder     = (int) ($order['id_order'] ?? 0);
$status      = (string) ($order['status'] ?? '');
$sisaKuota   = (int) ($order['sisa_kuota'] ?? 0);
$kuotaRevisi = (int) ($order['kuota_revisi'] ?? 0);

$revisiBadges = [
    'uploaded'        => ['label' => 'Diunggah', 'dot' => 'bg-blue-500', 'class' => 'bg-blue-100 text-blue-800'],
    'diajukan_revisi' => ['label' => 'Revisi Diajukan', 'dot' => 'bg-amber-500', 'class' => 'bg-amber-100 text-amber-800'],
    'acc'             => ['label' => 'ACC ✓', 'dot' => 'bg-emerald-500', 'class' => 'bg-emerald-100 text-emerald-800'],
    'ditolak'         => ['label' => 'Ditolak', 'dot' => 'bg-red-500', 'class' => 'bg-red-100 text-red-800'],
];

$catatanPelanggan = '';
if (is_array($lastRevis) && ($lastRevis['status'] ?? '') === 'diajukan_revisi') {
    $catatanPelanggan = (string) ($lastRevis['catatan_revisi'] ?? '');
}
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Ruang Kerja Produksi') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Ruang Kerja Produksi') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<p class="text-xs text-slate-400 mb-4">
    <a href="<?= esc(site_url($readOnly ? 'manajemen-desain' : 'antrian-desain')) ?>" class="hover:text-[#051747]">
        <?= $readOnly ? 'Manajemen Desain' : 'Antrian Desain' ?>
    </a>
    <span class="mx-1">/</span>
    <span class="font-semibold text-[#051747]">
        <?= $readOnly ? 'Detail-' : 'Ruang Kerja-' ?><?= esc($kodeOrder) ?>
    </span>
</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">
    <div class="lg:col-span-2">
        <?= view('partials/produksi_acuan_pesanan', [
            'order' => $order,
            'attrs' => $attrs,
        ]) ?>
    </div>

    <div class="lg:col-span-3 space-y-5">
        <?php if ($catatanPelanggan !== ''): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-xs font-bold uppercase text-amber-800 mb-2">Catatan Revisi dari Pelanggan</p>
                <p class="text-sm text-amber-900 whitespace-pre-line"><?= esc($catatanPelanggan) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$readOnly): ?>
        <div id="upload" class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 scroll-mt-4">
            <h3 class="font-bold text-[#051747] mb-1">Unggah Draf Baru</h3>
            <p class="text-xs text-slate-500 mb-4">Unggah versi draft terbaru untuk direview pelanggan.</p>

            <?php if ($canUpload): ?>
                <form method="post"
                    action="<?= esc(site_url('manajemen-desain/' . $idOrder . '/upload')) ?>"
                    enctype="multipart/form-data"
                    class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Berkas Draf <span class="text-red-500">*</span></label>
                        <div id="dropZone"
                            class="border-2 border-dashed border-slate-200 rounded-xl p-8 text-center cursor-pointer hover:border-[#2E5CE6] hover:bg-blue-50/30 transition-colors">
                            <input type="file" name="file_draft" id="fileDraft" accept=".jpg,.jpeg,.png" class="sr-only" required>
                            <div id="dropPlaceholder">
                                <p class="text-3xl mb-2">📁</p>
                                <p class="text-sm font-semibold text-[#051747]">Klik atau seret file ke sini</p>
                                <p class="text-xs text-slate-500 mt-1">JPG / PNG, maks. 1MB</p>
                            </div>
                            <div id="dropPreview" class="hidden">
                                <img id="previewImg" src="" alt="Preview" class="max-h-48 mx-auto rounded-lg object-contain mb-2">
                                <p id="previewName" class="text-xs text-slate-600"></p>
                                <button type="button" id="clearPreview" class="mt-2 text-xs text-red-600 font-semibold hover:underline">
                                    Hapus file
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="catatan_prod" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Produksi (opsional)</label>
                        <textarea name="catatan_prod" id="catatan_prod" rows="3"
                            placeholder="Catatan untuk pelanggan tentang perubahan pada draft ini..."
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-[#051747] text-white py-3 rounded-full font-bold text-sm uppercase hover:bg-[#2E5CE6] transition-colors">
                        Unggah Draf →
                    </button>
                </form>
            <?php else: ?>
                <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-6 text-center text-sm text-slate-500">
                    <?php if (in_array($status, ['proses_cetak', 'finishing'], true)): ?>
                        Pesanan sudah melewati tahap upload draft.
                    <?php elseif ($status === 'terverifikasi'): ?>
                        Siap upload draft pertama setelah pesanan masuk antrian desain.
                    <?php else: ?>
                        Unggah draf tidak tersedia—menunggu review atau persetujuan pelanggan pada draf sebelumnya.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div id="history" class="scroll-mt-4">
            <h3 class="text-sm font-bold text-[#051747] uppercase tracking-wide mb-4">Riwayat Revisi Desain</h3>

            <?php if ($revisList === []): ?>
                <div class="bg-white rounded-xl border border-slate-100 p-8 text-center text-slate-500 text-sm">
                    Belum ada riwayat revisi desain.
                </div>
            <?php else: ?>
                <div class="relative pl-6 space-y-6">
                    <div class="absolute left-2 top-2 bottom-2 w-px bg-slate-200"></div>
                    <?php foreach ($revisList as $r): ?>
                        <?php
                        $revStatus  = (string) ($r['status'] ?? 'uploaded');
                        $badge      = $revisiBadges[$revStatus] ?? ['label' => $revStatus, 'dot' => 'bg-slate-400', 'class' => 'bg-slate-100 text-slate-600'];
                        $versi      = (int) ($r['versi'] ?? 0);
                        $revCode    = 'REV-' . str_pad((string) $idOrder, 4, '0', STR_PAD_LEFT)
                            . '-' . str_pad((string) $versi, 2, '0', STR_PAD_LEFT);
                        $fileDraft  = (string) ($r['file_draft'] ?? '');
                        $isAccDraft = $revStatus === 'acc';
                        $cardClass  = $isAccDraft
                            ? 'bg-white border-2 border-emerald-500 shadow-sm'
                            : 'bg-white border border-slate-100 shadow-sm';
                        ?>
                        <div class="relative">
                            <span class="absolute -left-6 top-4 w-3.5 h-3.5 rounded-full ring-4 ring-white <?= esc($badge['dot']) ?>"></span>
                            <div class="<?= esc($cardClass) ?> rounded-xl p-4">
                                <?php if ($isAccDraft): ?>
                                    <p class="text-[10px] font-bold uppercase tracking-wide text-emerald-700 mb-2">✓ Draft dipilih untuk cetak</p>
                                <?php endif; ?>
                                <div class="flex flex-wrap justify-between gap-2 items-start mb-3">
                                    <p class="font-bold text-[#051747] text-sm">
                                        Draft v<?= esc((string) $versi) ?>-<?= esc($revCode) ?>
                                    </p>
                                    <div class="text-right">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-semibold <?= esc($badge['class']) ?>">
                                            <?= esc($badge['label']) ?>
                                        </span>
                                        <?php if (!empty($r['created_at'])): ?>
                                            <p class="text-[10px] text-slate-400 mt-1">
                                                <?= esc(date('d M Y H:i', strtotime((string) $r['created_at']))) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex gap-4 flex-col sm:flex-row">
                                    <div class="w-full sm:w-28 h-24 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                                        <?php if ($fileDraft !== ''): ?>
                                            <a href="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>" target="_blank" rel="noopener noreferrer">
                                                <img src="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>"
                                                    alt="Draft"
                                                    class="w-full h-full object-cover"
                                                    onerror="this.style.display='none'">
                                            </a>
                                        <?php else: ?>
                                            <span class="flex items-center justify-center h-full text-2xl">🖼</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 text-sm space-y-2">
                                        <?php if (!empty($r['catatan_prod'])): ?>
                                            <p><span class="font-semibold text-slate-600">Catatan Produksi:</span>
                                                <?= esc((string) $r['catatan_prod']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($r['catatan_revisi'])): ?>
                                            <p class="text-amber-800 bg-amber-50 rounded-lg px-3 py-2">
                                                <span class="font-semibold">Catatan Revisi Pelanggan:</span>
                                                <?= esc((string) $r['catatan_revisi']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileDraft');
        if (!dropZone || !fileInput) return;

        const placeholder = document.getElementById('dropPlaceholder');
        const previewWrap = document.getElementById('dropPreview');
        const previewImg = document.getElementById('previewImg');
        const previewName = document.getElementById('previewName');
        const clearBtn = document.getElementById('clearPreview');

        function showPreview(file) {
            if (!file || !file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewName.textContent = file.name;
                placeholder.classList.add('hidden');
                previewWrap.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        function clearPreview() {
            fileInput.value = '';
            previewImg.src = '';
            previewWrap.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }

        dropZone.addEventListener('click', function(e) {
            if (e.target.id !== 'clearPreview') fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) showPreview(this.files[0]);
        });

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.classList.add('border-[#2E5CE6]', 'bg-blue-50/50');
        });

        dropZone.addEventListener('dragleave', function() {
            dropZone.classList.remove('border-[#2E5CE6]', 'bg-blue-50/50');
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('border-[#2E5CE6]', 'bg-blue-50/50');
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                fileInput.files = e.dataTransfer.files;
                showPreview(e.dataTransfer.files[0]);
            }
        });

        if (clearBtn) clearBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            clearPreview();
        });

        if (window.location.hash === '#history') {
            const el = document.getElementById('history');
            if (el) el.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    })();
</script>
<?= $this->endSection() ?>