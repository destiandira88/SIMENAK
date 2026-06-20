<?php
/**
 * @var array<string, mixed>              $order
 * @var list<array<string, mixed>>       $revisList
 * @var array<string, mixed>|null        $lastRevis
 */
$kodeOrder   = (string) ($order['kode_order'] ?? '');
$idOrder     = (int) ($order['id_order'] ?? 0);
$status      = (string) ($order['status'] ?? '');
$sisaKuota   = (int) ($order['sisa_kuota'] ?? 0);
$kuotaRevisi = (int) ($order['kuota_revisi'] ?? 0);

$revisiBadges = [
    'uploaded'        => ['label' => 'Diunggah', 'class' => 'bg-blue-100 text-blue-800'],
    'diajukan_revisi' => ['label' => 'Revisi Diajukan', 'class' => 'bg-amber-100 text-amber-800'],
    'acc'             => ['label' => 'ACC', 'class' => 'bg-emerald-100 text-emerald-800'],
    'ditolak'         => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-800'],
];

$catatanPelanggan = '';
if (is_array($lastRevis) && ($lastRevis['status'] ?? '') === 'diajukan_revisi') {
    $catatanPelanggan = (string) ($lastRevis['catatan_revisi'] ?? '');
}
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Upload Draft') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Upload Draft') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<p class="text-xs text-slate-400 mb-4">
    <a href="<?= esc(site_url('antrian-desain')) ?>" class="hover:text-[#051747]">Antrian Desain</a>
    <span class="mx-1">/</span>
    <span class="text-slate-500">Upload Draft</span>
    <span class="mx-1">/</span>
    <span class="font-semibold text-[#051747]"><?= esc($kodeOrder) ?></span>
</p>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-3 space-y-5">
        <div class="rounded-xl p-5 text-white" style="background:var(--navy);">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-white/60 text-xs uppercase font-semibold mb-1">Kode Order</p>
                    <p class="font-mono font-bold text-lg"><?= esc($kodeOrder) ?></p>
                </div>
                <div>
                    <p class="text-white/60 text-xs uppercase font-semibold mb-1">Pelanggan</p>
                    <p class="font-semibold"><?= esc((string) ($order['nama_pelanggan'] ?? '-')) ?></p>
                </div>
                <div>
                    <p class="text-white/60 text-xs uppercase font-semibold mb-1">Status</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-white/15">
                        <?= esc(getOrderStatusLabel($order)) ?>
                    </span>
                </div>
                <div>
                    <p class="text-white/60 text-xs uppercase font-semibold mb-1">Sisa Kuota</p>
                    <p class="font-bold text-lg <?= $sisaKuota <= 1 ? 'text-red-300' : 'text-emerald-300' ?>">
                        <?= esc((string) $sisaKuota) ?>/<?= esc((string) $kuotaRevisi) ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if ($catatanPelanggan !== ''): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-xs font-bold uppercase text-amber-800 mb-2">Catatan Revisi dari Pelanggan</p>
                <p class="text-sm text-amber-900 whitespace-pre-line"><?= esc($catatanPelanggan) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-[#051747] mb-4">Upload Draft Baru</h3>
            <form method="post"
                action="<?= esc(site_url('manajemen-desain/' . $idOrder . '/upload')) ?>"
                enctype="multipart/form-data"
                class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">File Draft <span class="text-red-500">*</span></label>
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
                    Upload Draft →
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
            <h3 class="font-bold text-[#051747] mb-4 pb-3 border-b border-slate-100">Riwayat Draft</h3>
            <?php if ($revisList === []): ?>
                <p class="text-sm text-slate-400 text-center py-6">Belum ada draft.</p>
            <?php else: ?>
                <div class="space-y-3 max-h-[32rem] overflow-y-auto pr-1">
                    <?php foreach ($revisList as $r): ?>
                        <?php
                        $revStatus = (string) ($r['status'] ?? 'uploaded');
                        $badge     = $revisiBadges[$revStatus] ?? ['label' => $revStatus, 'class' => 'bg-slate-100 text-slate-600'];
                        $fileDraft = (string) ($r['file_draft'] ?? '');
                        $versi     = (int) ($r['versi'] ?? 0);
                        $isAccDraft = $revStatus === 'acc';
                        $cardClass  = $isAccDraft
                            ? 'border-2 border-emerald-500 bg-white shadow-sm'
                            : 'border border-slate-200 bg-white';
                        ?>
                        <div class="<?= esc($cardClass) ?> rounded-xl p-3">
                            <?php if ($isAccDraft): ?>
                                <p class="text-[10px] font-bold uppercase text-emerald-700 mb-2">✓ Dipilih cetak</p>
                            <?php endif; ?>
                            <div class="flex gap-3">
                                <div class="w-16 h-14 bg-slate-100 rounded-lg overflow-hidden shrink-0">
                                    <?php if ($fileDraft !== ''): ?>
                                        <a href="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>" target="_blank" rel="noopener noreferrer">
                                            <img src="<?= esc(base_url('uploads/draft_desain/' . $fileDraft)) ?>"
                                                alt="v<?= esc((string) $versi) ?>"
                                                class="w-full h-full object-cover"
                                                onerror="this.parentElement.innerHTML='<span class=\'flex items-center justify-center h-full text-xl\'>🖼</span>'">
                                        </a>
                                    <?php else: ?>
                                        <span class="flex items-center justify-center h-full text-xl">🖼</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between gap-2 items-start">
                                        <p class="text-sm font-bold text-[#051747]">v<?= esc((string) $versi) ?></p>
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold <?= esc($badge['class']) ?>">
                                            <?= esc($badge['label']) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($r['catatan_prod'])): ?>
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?= esc((string) $r['catatan_prod']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($r['catatan_revisi'])): ?>
                                        <p class="text-xs text-amber-700 mt-1 line-clamp-2"><?= esc((string) $r['catatan_revisi']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($r['created_at'])): ?>
                                        <p class="text-[10px] text-slate-400 mt-1">
                                            <?= esc(date('d M Y H:i', strtotime((string) $r['created_at']))) ?>
                                        </p>
                                    <?php endif; ?>
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
(function () {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileDraft');
    const placeholder = document.getElementById('dropPlaceholder');
    const previewWrap = document.getElementById('dropPreview');
    const previewImg = document.getElementById('previewImg');
    const previewName = document.getElementById('previewName');
    const clearBtn = document.getElementById('clearPreview');

    if (!dropZone || !fileInput) return;

    function showPreview(file) {
        if (!file || !file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = function (e) {
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

    dropZone.addEventListener('click', function (e) {
        if (e.target.id !== 'clearPreview') fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (this.files && this.files[0]) showPreview(this.files[0]);
    });

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.classList.add('border-[#2E5CE6]', 'bg-blue-50/50');
    });

    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('border-[#2E5CE6]', 'bg-blue-50/50');
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('border-[#2E5CE6]', 'bg-blue-50/50');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });

    if (clearBtn) clearBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        clearPreview();
    });
})();
</script>
<?= $this->endSection() ?>
