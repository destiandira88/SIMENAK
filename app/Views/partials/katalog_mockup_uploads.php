<?php
/**
 * Upload / preview foto mockup per sudut — form Admin katalog create & edit.
 *
 * @var bool                $readOnly
 * @var array<string,string> $existingMockups sudut => url
 */
$readOnly         = (bool) ($readOnly ?? false);
$existingMockups  = isset($existingMockups) && is_array($existingMockups) ? $existingMockups : [];
/** @var \Config\MockupProducts $mockupCfg */
$mockupCfg  = config('MockupProducts');
$sudutList  = $mockupCfg->sudutList;
$hasAnyFile = $existingMockups !== [];
?>

<div class="mt-6 pt-6 border-t border-slate-100">
    <label class="form-label">
        Preview Mockup (Opsional)
    </label>
    <p class="form-hint mb-1">
        Unggah foto mockup untuk menampilkan draft desain pada produk.
    </p>
    <p class="form-hint mb-1">
        Undangan: Cover, Bagian Dalam
    </p>
    <p class="form-hint mb-1">
        Produk lainnya: Depan (utama), Samping / Atas (opsional)
    </p>
    <p class="form-hint mb-4">
        JPG, PNG, WEBP (Maks. 3 MB/file)
    </p>

    <?php if ($readOnly && !$hasAnyFile): ?>
        <p class="text-sm text-slate-400 italic">Belum ada foto mockup.</p>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($sudutList as $sudut => $label): ?>
                <?php
                $url      = (string) ($existingMockups[$sudut] ?? '');
                $inputId  = 'mockup_' . $sudut;
                $previewId = 'mockupPrev_' . $sudut;
                $phId     = 'mockupPh_' . $sudut;
                $nameId   = 'mockupName_' . $sudut;
                ?>
                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3"><?= esc($label) ?></p>

                    <?php if ($url !== ''): ?>
                        <div class="mb-3 flex items-start gap-3">
                            <button
                                type="button"
                                class="group relative block rounded-lg focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/40 shrink-0"
                                data-katalog-zoom="<?= esc($url) ?>"
                                data-katalog-zoom-alt="<?= esc('Mockup ' . $label) ?>"
                                aria-label="Perbesar mockup <?= esc($label) ?>">
                                <img
                                    src="<?= esc($url) ?>"
                                    alt="Mockup <?= esc($label) ?>"
                                    class="w-20 h-20 object-cover rounded-lg border border-slate-200 cursor-zoom-in transition-opacity group-hover:opacity-90">
                            </button>
                            <?php if (!$readOnly): ?>
                                <div class="min-w-0 pt-1">
                                    <p class="text-xs text-slate-500 mb-2">Sudah ada. Unggah baru untuk mengganti.</p>
                                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-red-600 hover:text-red-700">
                                        <input
                                            type="checkbox"
                                            name="mockup_hapus[<?= esc($sudut) ?>]"
                                            value="1"
                                            class="w-3.5 h-3.5 accent-red-500">
                                        Hapus sudut ini
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($readOnly): ?>
                        <div class="w-20 h-20 rounded-lg border border-dashed border-slate-200 bg-white flex items-center justify-center mb-1">
                            <span class="text-[10px] text-slate-400">Kosong</span>
                        </div>
                    <?php endif; ?>

                    <?php if (!$readOnly): ?>
                        <div
                            class="border-2 border-dashed border-slate-200 rounded-xl p-3 text-center hover:border-[#2E5CE6] transition-colors cursor-pointer bg-white"
                            onclick="document.getElementById('<?= esc($inputId) ?>')?.click()">
                            <div id="<?= esc($previewId) ?>" class="hidden mb-2">
                                <img src="" alt="Preview <?= esc($label) ?>" class="w-16 h-16 object-cover rounded-lg mx-auto">
                            </div>
                            <div id="<?= esc($phId) ?>">
                                <svg class="w-7 h-7 text-slate-300 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="form-upload-hint">Unggah <?= esc($label) ?></p>
                            </div>
                            <span id="<?= esc($nameId) ?>" class="hidden form-upload-text mt-1 block text-[11px] truncate"></span>
                        </div>
                        <input
                            type="file"
                            id="<?= esc($inputId) ?>"
                            name="mockup[<?= esc($sudut) ?>]"
                            accept=".jpg,.jpeg,.png,.webp"
                            class="hidden"
                            data-mockup-sudut="<?= esc($sudut) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
