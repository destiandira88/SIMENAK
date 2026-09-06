<?php

/**
 * Preview draft + mockup multi-sudut.
 *
 * @var string|null                        $draftUrl
 * @var list<array<string, mixed>>         $mockupAngles
 * @var string|null                        $previewTitle
 * @var bool                               $editable
 * @var array<string, mixed>|string|null   $mockupAdjust
 * @var string                             $adjustInputName
 * @var array<string, string>              $layerUrls  fileKey => absolute URL
 * @var bool                               $showAdjustSaveButton  tombol Simpan di sticky bar (panel penyesuaian)
 */
$draftUrl          = $draftUrl ?? null;
$mockupAngles      = is_array($mockupAngles ?? null) ? $mockupAngles : [];
$previewTitle      = $previewTitle ?? 'Preview Desain';
$editable          = (bool) ($editable ?? false);
$mockupAdjustMap   = normalizeMockupAdjust($mockupAdjust ?? []);
$adjustInputName   = (string) ($adjustInputName ?? 'mockup_adjust');
$showAdjustSaveButton = (bool) ($showAdjustSaveButton ?? false);
$layerUrls         = is_array($layerUrls ?? null) ? $layerUrls : [];
if ($draftUrl && empty($layerUrls['primary'])) {
    $layerUrls['primary'] = $draftUrl;
}
$layerUrls = resolveMockupLayerUrls($draftUrl, $mockupAdjustMap) + $layerUrls;
// Penting: empty PHP [] harus di-JSON-kan sebagai {} agar JS tidak dapat Array
// (JSON.stringify(Array) menghapus key sudut → history Undo rusak / seperti Reset).
$mockupAdjustJson = $mockupAdjustMap === []
    ? '{}'
    : (json_encode($mockupAdjustMap, JSON_UNESCAPED_SLASHES) ?: '{}');
$layerUrlsJson = json_encode($layerUrls, JSON_UNESCAPED_SLASHES) ?: '{}';
$anglesJson = json_encode($mockupAngles, JSON_UNESCAPED_SLASHES) ?: '[]';

$hasAngles         = $mockupAngles !== [];
$hasMultipleAngles = count($mockupAngles) > 1;
$hasDraftSrc       = $draftUrl !== null && $draftUrl !== '';
$hasMockup         = $hasAngles && ($hasDraftSrc || $editable);
$uid               = 'mockup_' . substr(md5(($draftUrl ?? '') . count($mockupAngles) . ($editable ? 'e' : 'r') . microtime()), 0, 8);
?>
<?php if (! $hasDraftSrc && ! $editable): ?>
    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-sm text-slate-400">
        Belum ada file draft untuk dipreview.
    </div>
<?php elseif (! $hasMockup): ?>
    <div class="rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between gap-2">
            <p class="text-sm font-bold text-[#051747]"><?= esc($previewTitle) ?></p>
            <?php if ($hasDraftSrc): ?>
                <a href="<?= esc($draftUrl) ?>" target="_blank" rel="noopener noreferrer"
                    class="text-xs font-semibold text-[#2E5CE6] hover:underline">Buka ukuran penuh</a>
            <?php endif; ?>
        </div>
        <div class="p-4 flex justify-center bg-[#F0F2F8]">
            <?php if ($hasDraftSrc): ?>
                <img src="<?= esc($draftUrl) ?>" alt="Draft desain"
                    class="max-h-80 w-auto max-w-full object-contain rounded-lg shadow-sm">
            <?php endif; ?>
        </div>
        <p class="px-4 py-2 text-[11px] text-slate-400 text-center border-t border-slate-100">
            Preview belum tersedia untuk produk ini (menampilkan draft asli).
        </p>
    </div>
<?php else: ?>
    <div class="rounded-xl border border-slate-200 bg-white <?= $editable ? '' : 'overflow-hidden' ?>" id="<?= esc($uid) ?>"
        data-mockup-root
        data-editable="<?= $editable ? '1' : '0' ?>"
        data-angles="<?= esc($anglesJson) ?>"
        data-adjust="<?= esc($mockupAdjustJson) ?>"
        data-layer-urls="<?= esc($layerUrlsJson) ?>"
        data-draft="<?= esc($draftUrl ?? '') ?>">
        <div class="px-4 py-2.5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 <?= $editable ? 'rounded-t-xl' : '' ?>">
            <div>
                <p class="text-sm font-bold text-[#051747]" data-mockup-title><?= esc($previewTitle) ?></p>
                <?php if ($editable): ?>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Atur setiap gambar pada preview. Anda dapat menambahkan beberapa gambar dalam satu sisi, lalu menyimpan penyesuaiannya.
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <?php if (! $editable): ?>
                    <button type="button" data-mockup-toggle
                        class="text-xs font-bold px-3 py-1.5 rounded-full border border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors">
                        Lihat Draft Asli
                    </button>
                <?php endif; ?>
                <button type="button" data-mockup-fullsize
                    class="text-xs font-semibold text-[#2E5CE6] hover:underline">
                    Full size
                </button>
            </div>
        </div>

        <?php if ($editable): ?>
            <div class="sticky top-14 lg:top-16 z-10 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between gap-3"
                data-mockup-toolbar>
                <div class="flex items-center gap-2">
                    <button type="button" data-mockup-undo
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-[#051747] hover:border-[#051747] hover:bg-[#F0F2F8] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        title="Undo (Ctrl+Z)" aria-label="Undo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                            <path d="M9 14 4 9l5-5"/>
                            <path d="M4 9h10.5a5.5 5.5 0 0 1 0 11H11"/>
                        </svg>
                    </button>
                    <button type="button" data-mockup-redo
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-[#051747] hover:border-[#051747] hover:bg-[#F0F2F8] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                        title="Redo (Ctrl+Y)" aria-label="Redo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                            <path d="M15 14l5-5-5-5"/>
                            <path d="M20 9H9.5a5.5 5.5 0 0 0 0 11H13"/>
                        </svg>
                    </button>
                </div>
                <?php if ($showAdjustSaveButton): ?>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 bg-emerald-500 text-white px-5 py-2.5 rounded-full font-bold text-sm uppercase hover:bg-emerald-600 transition-colors shrink-0">
                        Simpan penyesuaian
                    </button>
                <?php else: ?>
                    <span class="text-[11px] text-slate-400 hidden sm:inline">Penyesuaian otomatis tersimpan saat draft diunggah.</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="relative bg-[#F0F2F8] p-4" data-mockup-stage>
            <?php if ($editable): ?>
                <div class="flex flex-col gap-4 lg:grid lg:grid-cols-[minmax(0,1fr)_280px] lg:gap-2 lg:items-stretch">
                    <!-- Canvas -->
                    <div class="order-1 min-w-0 flex flex-col">
                        <div class="lg:sticky lg:top-28 space-y-3 flex-1 flex flex-col min-h-0">
                            <div class="relative mx-auto w-full flex-1 min-h-[16rem] aspect-[4/3] lg:aspect-auto bg-slate-200 rounded-lg overflow-hidden shadow-sm"
                                data-mockup-frame>
                                <img data-mockup-bg alt="Mockup produk" class="absolute inset-0 w-full h-full object-contain select-none pointer-events-none">
                                <div data-mockup-slot class="absolute overflow-hidden ring-2 ring-[#2E5CE6]/70 ring-offset-1 cursor-grab active:cursor-grabbing"
                                    style="top:20%;left:20%;width:60%;height:60%; touch-action:none;">
                                    <canvas data-mockup-canvas class="absolute inset-0 w-full h-full pointer-events-none" style="mix-blend-mode:multiply;"></canvas>
                                    <span class="absolute -top-1.5 -left-1.5 h-3.5 w-3.5 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nwse-resize z-10 hidden" data-mockup-slot-handle="nw"></span>
                                    <span class="absolute -top-1.5 -right-1.5 h-3.5 w-3.5 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nesw-resize z-10 hidden" data-mockup-slot-handle="ne"></span>
                                    <span class="absolute -bottom-1.5 -left-1.5 h-3.5 w-3.5 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nesw-resize z-10 hidden" data-mockup-slot-handle="sw"></span>
                                    <span class="absolute -bottom-1.5 -right-1.5 h-3.5 w-3.5 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nwse-resize z-10 hidden" data-mockup-slot-handle="se"></span>
                                </div>
                            </div>

                            <?php if ($hasMultipleAngles): ?>
                                <div class="flex items-center justify-center gap-4 lg:hidden" data-mockup-controls>
                                    <button type="button" data-mockup-prev
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#051747] text-white hover:bg-[#2E5CE6] transition-colors"
                                        aria-label="Sudut sebelumnya">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                            <polyline points="15 18 9 12 15 6"/>
                                        </svg>
                                    </button>
                                    <p class="text-sm font-semibold text-[#051747] min-w-[5rem] text-center" data-mockup-label>Depan</p>
                                    <button type="button" data-mockup-next
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-[#051747] text-white hover:bg-[#2E5CE6] transition-colors"
                                        aria-label="Sudut berikutnya">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                            <polyline points="9 18 15 12 9 6"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex justify-center gap-1.5 lg:hidden" data-mockup-dots></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Kanan: transform controls -->
                    <div class="order-4 lg:order-2 min-w-0 bg-white border border-slate-200 rounded-2xl shadow-sm p-3 space-y-0">
                        <div class="border-b border-slate-200 pb-4 mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 mb-3">Crop &amp; Rotate</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" data-mockup-crop-open
                                    class="text-xs font-bold px-4 py-2 rounded-full bg-[#051747] text-white hover:bg-[#2E5CE6] transition-colors">
                                    Crop layer aktif
                                </button>
                                <button type="button" data-mockup-rotate-ccw
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-[#F0F2F8] text-[#051747] hover:border-[#051747] transition-colors"
                                    title="Putar −90°" aria-label="Putar kiri">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                        <polyline points="3 3 3 8 8 8"/>
                                    </svg>
                                </button>
                                <button type="button" data-mockup-rotate-cw
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-[#F0F2F8] text-[#051747] hover:border-[#051747] transition-colors"
                                    title="Putar +90°" aria-label="Putar kanan">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                        <path d="M21 12a9 9 0 1 1-9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
                                        <polyline points="21 3 21 8 16 8"/>
                                    </svg>
                                </button>
                                <span class="text-xs font-semibold text-[#051747] min-w-[3rem] text-center" data-mockup-rotate-label>0°</span>
                            </div>
                        </div>

                        <div class="border-b border-slate-200 pb-4 mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 mb-3">Zoom</p>
                            <div class="flex items-center gap-2 mb-2">
                                <button type="button" data-mockup-zoom-out
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#051747] text-white hover:bg-[#2E5CE6] transition-colors"
                                    aria-label="Zoom out">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                        <circle cx="11" cy="11" r="8"/>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                        <line x1="8" y1="11" x2="14" y2="11"/>
                                    </svg>
                                </button>
                                <div class="min-w-[4.5rem] text-center flex-1">
                                    <span class="text-sm font-extrabold text-[#051747]" data-mockup-scale-label>100%</span>
                                </div>
                                <button type="button" data-mockup-zoom-in
                                    class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#051747] text-white hover:bg-[#2E5CE6] transition-colors"
                                    aria-label="Zoom in">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                        <circle cx="11" cy="11" r="8"/>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                        <line x1="11" y1="8" x2="11" y2="14"/>
                                        <line x1="8" y1="11" x2="14" y2="11"/>
                                    </svg>
                                </button>
                            </div>
                            <input type="range" data-mockup-scale min="40" max="250" value="100" step="1"
                                class="w-full accent-[#051747] cursor-pointer">
                        </div>

                        <div class="border-b border-slate-200 pb-4 mb-4">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 mb-3">Geser layer</p>
                            <div class="flex flex-col items-center gap-1">
                                <button type="button" data-mockup-nudge="0,-3"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-[#F0F2F8] text-[#051747]"
                                    aria-label="Geser atas">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                        <polyline points="18 15 12 9 6 15"/>
                                    </svg>
                                </button>
                                <div class="flex items-center gap-1">
                                    <button type="button" data-mockup-nudge="-3,0"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-[#F0F2F8] text-[#051747]"
                                        aria-label="Geser kiri">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                            <polyline points="15 18 9 12 15 6"/>
                                        </svg>
                                    </button>
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-400" title="Seret di preview" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                                            <polyline points="5 9 2 12 5 15"/>
                                            <polyline points="9 5 12 2 15 5"/>
                                            <polyline points="15 19 12 22 9 19"/>
                                            <polyline points="19 9 22 12 19 15"/>
                                            <line x1="2" y1="12" x2="22" y2="12"/>
                                            <line x1="12" y1="2" x2="12" y2="22"/>
                                        </svg>
                                    </span>
                                    <button type="button" data-mockup-nudge="3,0"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-[#F0F2F8] text-[#051747]"
                                        aria-label="Geser kanan">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                            <polyline points="9 18 15 12 9 6"/>
                                        </svg>
                                    </button>
                                </div>
                                <button type="button" data-mockup-nudge="0,3"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-[#F0F2F8] text-[#051747]"
                                    aria-label="Geser bawah">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button type="button" data-mockup-slot-mode
                                class="w-full text-xs font-bold px-4 py-2 rounded-full border border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors"
                                aria-pressed="false">
                                Atur area mockup
                            </button>
                            <button type="button" data-mockup-slot-reset
                                class="w-full text-xs font-bold px-4 py-2 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors hidden">
                                Reset area ke default
                            </button>
                            <button type="button" data-mockup-reset
                                class="w-full text-xs font-bold px-4 py-2 rounded-full border border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors">
                                Reset layer aktif
                            </button>
                            <p class="text-[10px] text-slate-500 leading-relaxed" data-mockup-slot-hint>
                                Aktifkan <span class="font-semibold">Atur area mockup</span> lalu seret kotak / pojok (seperti crop) jika posisi area desain kurang pas.
                            </p>
                        </div>
                    </div>

                    <!-- Sisi: teks di tengah, full lebar grid -->
                    <div class="order-2 lg:order-3 lg:col-span-2 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500 mb-1">Sisi</p>
                        <?php if ($hasMultipleAngles): ?>
                            <div class="flex flex-wrap justify-center gap-2" data-mockup-sudut-tabs>
                                <?php foreach ($mockupAngles as $ai => $ang): ?>
                                    <button type="button"
                                        data-mockup-sudut-idx="<?= (int) $ai ?>"
                                        class="shrink-0 text-xs font-bold px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 hover:border-[#2E5CE6] transition-colors <?= $ai === 0 ? 'border-[#051747] bg-blue-50 text-[#051747]' : '' ?>">
                                        <?= esc((string) ($ang['sudut_label'] ?? $ang['sudut'] ?? 'Sisi')) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm font-semibold text-[#051747]" data-mockup-label><?= esc((string) ($mockupAngles[0]['sudut_label'] ?? 'Depan')) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Card layer: full lebar, kanan sejajar panel Reset -->
                    <div class="order-3 lg:order-4 lg:col-span-2 bg-white border border-slate-200 rounded-2xl shadow-sm p-3 space-y-3 w-full" data-mockup-edit-bar>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-500" data-mockup-layer-heading>Gambar di sisi <?= esc(mb_strtoupper((string) ($mockupAngles[0]['sudut_label'] ?? 'Depan'))) ?></p>
                            <p class="text-[10px] text-slate-500">Pilih layer untuk menyesuaikan gambar.</p>
                        </div>
                        <div class="space-y-1.5" data-mockup-layer-list></div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-mockup-add-primary
                                class="text-xs font-bold px-3 py-2 rounded-full border border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors">
                                + Dari draft utama
                            </button>
                            <label class="text-xs font-bold px-3 py-2 rounded-full bg-[#051747] text-white hover:bg-[#2E5CE6] transition-colors cursor-pointer">
                                + Gambar lain
                                <input type="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="sr-only" data-mockup-add-file>
                            </label>
                        </div>
                        <input type="hidden" name="<?= esc($adjustInputName) ?>" value="<?= esc($mockupAdjustJson) ?>" data-mockup-adjust-input>
                        <div class="hidden" data-mockup-pending-inputs></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="relative mx-auto w-full max-w-lg aspect-[4/3] bg-slate-200 rounded-lg overflow-hidden shadow-sm select-none<?= $hasMultipleAngles ? ' cursor-grab active:cursor-grabbing touch-pan-y' : '' ?>"
                    data-mockup-frame<?= $hasMultipleAngles ? ' title="Geser kiri/kanan untuk memutar sisi"' : '' ?>>
                    <img data-mockup-bg alt="Mockup produk" class="absolute inset-0 w-full h-full object-contain select-none pointer-events-none">
                    <div data-mockup-slot class="absolute overflow-hidden pointer-events-none"
                        style="top:20%;left:20%;width:60%;height:60%;">
                        <canvas data-mockup-canvas class="absolute inset-0 w-full h-full pointer-events-none" style="mix-blend-mode:multiply;"></canvas>
                    </div>
                </div>

                <div class="hidden mt-3 flex justify-center" data-mockup-raw>
                    <img src="<?= esc($draftUrl) ?>" alt="Draft asli"
                        class="max-h-80 w-auto max-w-full object-contain rounded-lg shadow-sm">
                </div>

                <div class="flex items-center justify-center gap-4 mt-4" data-mockup-controls>
                    <?php if ($hasMultipleAngles): ?>
                        <button type="button" data-mockup-prev
                            class="h-10 w-10 rounded-full bg-[#051747] text-white text-lg font-bold hover:bg-[#2E5CE6] transition-colors"
                            aria-label="Sudut sebelumnya">◀</button>
                    <?php endif; ?>
                    <p class="text-sm font-semibold text-[#051747] min-w-[5rem] text-center" data-mockup-label>Depan</p>
                    <?php if ($hasMultipleAngles): ?>
                        <button type="button" data-mockup-next
                            class="h-10 w-10 rounded-full bg-[#051747] text-white text-lg font-bold hover:bg-[#2E5CE6] transition-colors"
                            aria-label="Sudut berikutnya">▶</button>
                    <?php endif; ?>
                </div>
                <?php if ($hasMultipleAngles): ?>
                    <div class="flex justify-center gap-1.5 mt-2" data-mockup-dots></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <p class="px-4 py-2.5 text-[11px] text-slate-500 text-center border-t border-slate-100 <?= $editable ? 'rounded-b-xl' : '' ?>">
            Preview hanya simulasi visual dan dapat sedikit berbeda dari hasil cetak.
        </p>
    </div>

    <?php if ($editable): ?>
    <!-- Modal crop di luar card overflow agar tidak merusak preview/file -->
    <div class="fixed inset-0 z-[90] hidden items-center justify-center bg-black/50 p-4" id="<?= esc($uid) ?>_crop" data-mockup-crop-modal>
        <div class="bg-white rounded-2xl shadow-lg w-full max-w-xl overflow-hidden border border-slate-100" role="dialog" aria-modal="true">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                <div>
                    <p class="text-sm font-bold text-[#051747]">Crop layer aktif</p>
                    <p class="text-[11px] text-slate-500" data-mockup-crop-sudut-label>—</p>
                </div>
                <button type="button" data-mockup-crop-cancel
                    class="text-xs font-bold px-3 py-1.5 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Batal</button>
            </div>
            <div class="p-4 bg-[#F0F2F8]">
                <div class="relative mx-auto max-h-[55vh] w-full select-none" data-mockup-crop-stage style="touch-action:none;">
                    <img alt="Crop sumber" data-mockup-crop-img
                        class="block max-h-[55vh] max-w-full mx-auto rounded-lg shadow-sm pointer-events-none">
                    <div data-mockup-crop-box
                        class="absolute border-2 border-[#2E5CE6] bg-[#2E5CE6]/15 cursor-move"
                        style="left:10%;top:10%;width:80%;height:80%;">
                        <span class="absolute -top-1 -left-1 h-3 w-3 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nwse-resize" data-crop-handle="nw"></span>
                        <span class="absolute -top-1 -right-1 h-3 w-3 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nesw-resize" data-crop-handle="ne"></span>
                        <span class="absolute -bottom-1 -left-1 h-3 w-3 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nesw-resize" data-crop-handle="sw"></span>
                        <span class="absolute -bottom-1 -right-1 h-3 w-3 bg-white border-2 border-[#2E5CE6] rounded-sm cursor-nwse-resize" data-crop-handle="se"></span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 text-center mt-3">Seret kotak / pojok. Draft yang dipilih tidak berubah — hanya area crop layer ini.</p>
            </div>
            <div class="px-4 py-3 border-t border-slate-100 flex flex-wrap justify-end gap-2">
                <button type="button" data-mockup-crop-reset
                    class="text-xs font-bold px-4 py-2 rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50">Full gambar</button>
                <button type="button" data-mockup-crop-apply
                    class="text-xs font-bold px-4 py-2 rounded-full bg-emerald-500 text-white hover:bg-emerald-600">Terapkan crop</button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    (function () {
        var root = document.getElementById(<?= json_encode($uid) ?>);
        if (!root || root.dataset.mockupBound) return;
        root.dataset.mockupBound = '1';

        var angles;
        try { angles = JSON.parse(root.getAttribute('data-angles') || '[]'); } catch (e) { angles = []; }
        if (!angles.length) return;

        var adjustMap;
        try { adjustMap = JSON.parse(root.getAttribute('data-adjust') || '{}'); } catch (e) { adjustMap = {}; }
        if (!adjustMap || typeof adjustMap !== 'object') adjustMap = {};
        // Fix kritis: JSON [] jadi Array; stringify-nya menghapus key "cover"/"dalam" → Undo seperti Reset.
        if (Array.isArray(adjustMap)) {
            adjustMap = Object.assign({}, adjustMap);
        }

        var layerUrls;
        try { layerUrls = JSON.parse(root.getAttribute('data-layer-urls') || '{}'); } catch (e) { layerUrls = {}; }
        if (!layerUrls || typeof layerUrls !== 'object' || Array.isArray(layerUrls)) layerUrls = {};

        var editable = root.getAttribute('data-editable') === '1';
        var idx = 0;
        var activeLayerIdx = 0;
        var showRaw = false;
        var imgCache = {};
        var paintGen = 0;
        var pendingFiles = {};

        var bg = root.querySelector('[data-mockup-bg]');
        var slot = root.querySelector('[data-mockup-slot]');
        var canvas = root.querySelector('[data-mockup-canvas]');
        var label = root.querySelector('[data-mockup-label]');
        var labels = root.querySelectorAll('[data-mockup-label]');
        var frame = root.querySelector('[data-mockup-frame]');
        var rawBox = root.querySelector('[data-mockup-raw]');
        var controls = root.querySelector('[data-mockup-controls]');
        var dotsWrap = root.querySelector('[data-mockup-dots]');
        var toggleBtn = root.querySelector('[data-mockup-toggle]');
        var scaleInput = root.querySelector('[data-mockup-scale]');
        var scaleLabel = root.querySelector('[data-mockup-scale-label]');
        var rotateLabel = root.querySelector('[data-mockup-rotate-label]');
        var resetBtn = root.querySelector('[data-mockup-reset]');
        var undoBtn = root.querySelector('[data-mockup-undo]');
        var redoBtn = root.querySelector('[data-mockup-redo]');
        var adjustInput = root.querySelector('[data-mockup-adjust-input]');
        var layerList = root.querySelector('[data-mockup-layer-list]');
        var layerHeading = root.querySelector('[data-mockup-layer-heading]');
        var sudutTabs = root.querySelector('[data-mockup-sudut-tabs]');
        var pendingBox = root.querySelector('[data-mockup-pending-inputs]');
        var ctx = canvas ? canvas.getContext('2d') : null;

        var cropModal = document.getElementById(<?= json_encode($uid . '_crop') ?>) || root.querySelector('[data-mockup-crop-modal]');

        // Undo / Redo — snapshot object polos (bukan Array)
        var historyStack = [];
        var historyPtr = -1;
        var HISTORY_MAX = 50;
        var applyingHistory = false;
        var wheelZoomTimer = null;

        function cloneAdjustMap(map) {
            var plain = Array.isArray(map) ? Object.assign({}, map) : (map || {});
            return JSON.parse(JSON.stringify(plain));
        }

        function snapshotState() {
            if (Array.isArray(adjustMap)) {
                adjustMap = Object.assign({}, adjustMap);
            }
            return {
                adjust: cloneAdjustMap(adjustMap),
                idx: idx,
                activeLayerIdx: activeLayerIdx
            };
        }

        function updateUndoRedoUi() {
            if (undoBtn) undoBtn.disabled = !(historyPtr > 0);
            if (redoBtn) redoBtn.disabled = !(historyPtr >= 0 && historyPtr < historyStack.length - 1);
        }

        function flushPendingZoomCommit() {
            if (!wheelZoomTimer) return;
            clearTimeout(wheelZoomTimer);
            wheelZoomTimer = null;
            commitHistory();
        }

        function commitHistory() {
            if (!editable || applyingHistory) return;
            if (Array.isArray(adjustMap)) {
                adjustMap = Object.assign({}, adjustMap);
            }
            var snap = snapshotState();
            var serialized = JSON.stringify(snap);
            if (historyPtr >= 0 && JSON.stringify(historyStack[historyPtr]) === serialized) {
                updateUndoRedoUi();
                return;
            }
            historyStack = historyStack.slice(0, historyPtr + 1);
            historyStack.push(snap);
            if (historyStack.length > HISTORY_MAX) {
                historyStack.shift();
            }
            historyPtr = historyStack.length - 1;
            updateUndoRedoUi();
        }

        function restoreHistory(snap) {
            if (!snap) return;
            applyingHistory = true;
            try {
                adjustMap = cloneAdjustMap(snap.adjust || {});
                if (Array.isArray(adjustMap)) {
                    adjustMap = Object.assign({}, adjustMap);
                }
                idx = typeof snap.idx === 'number' ? snap.idx : 0;
                activeLayerIdx = typeof snap.activeLayerIdx === 'number' ? snap.activeLayerIdx : 0;
                if (idx < 0 || idx >= angles.length) idx = 0;
                var layers = (adjustMap[currentSudut()] && adjustMap[currentSudut()].layers) || [];
                if (activeLayerIdx >= layers.length) activeLayerIdx = Math.max(0, layers.length - 1);
                apply();
            } finally {
                applyingHistory = false;
                updateUndoRedoUi();
            }
        }

        function undo() {
            flushPendingZoomCommit();
            if (historyPtr <= 0) return;
            historyPtr -= 1;
            restoreHistory(historyStack[historyPtr]);
        }

        function redo() {
            flushPendingZoomCommit();
            if (historyPtr < 0 || historyPtr >= historyStack.length - 1) return;
            historyPtr += 1;
            restoreHistory(historyStack[historyPtr]);
        }

        function defaultLayer(file) {
            return {
                id: 'l' + Date.now() + '_' + Math.floor(Math.random() * 999),
                file: file || 'primary',
                ox: 0, oy: 0, scale: 1, rotate: 0,
                cx: 0, cy: 0, cw: 100, ch: 100
            };
        }

        function ensureSudut(sudut) {
            if (!adjustMap[sudut] || !Array.isArray(adjustMap[sudut].layers) || !adjustMap[sudut].layers.length) {
                var prevSlot = (adjustMap[sudut] && adjustMap[sudut].slot) ? adjustMap[sudut].slot : null;
                adjustMap[sudut] = { layers: [defaultLayer('primary')] };
                if (prevSlot) adjustMap[sudut].slot = prevSlot;
            }
        }

        function defaultSlotFromAngle(a) {
            a = a || {};
            return {
                top: Number(a.slot_top) || 20,
                left: Number(a.slot_left) || 20,
                width: Number(a.slot_width) || 60,
                height: Number(a.slot_height) || 60
            };
        }

        function clampSlotRect(r) {
            var width = Math.max(8, Math.min(100, Number(r.width) || 60));
            var height = Math.max(8, Math.min(100, Number(r.height) || 60));
            var left = Math.max(0, Math.min(100 - width, Number(r.left) || 0));
            var top = Math.max(0, Math.min(100 - height, Number(r.top) || 0));
            return {
                top: Math.round(top * 100) / 100,
                left: Math.round(left * 100) / 100,
                width: Math.round(width * 100) / 100,
                height: Math.round(height * 100) / 100
            };
        }

        function getSlotRect() {
            var a = angles[idx] || angles[0] || {};
            var sudut = a.sudut || '';
            if (sudut) ensureSudut(sudut);
            var s = sudut && adjustMap[sudut] ? adjustMap[sudut].slot : null;
            if (s && typeof s === 'object') {
                return clampSlotRect(s);
            }
            return clampSlotRect(defaultSlotFromAngle(a));
        }

        function setSlotRect(rect, commit) {
            var sudut = currentSudut();
            if (!sudut) return;
            ensureSudut(sudut);
            adjustMap[sudut].slot = clampSlotRect(rect);
            applySlotStyle();
            syncHidden();
            requestAnimationFrame(paint);
            if (commit) commitHistory();
        }

        function applySlotStyle() {
            if (!slot) return;
            var r = getSlotRect();
            slot.style.top = r.top + '%';
            slot.style.left = r.left + '%';
            slot.style.width = r.width + '%';
            slot.style.height = r.height + '%';
        }

        function currentSudut() {
            var a = angles[idx] || angles[0] || {};
            return a.sudut || '';
        }

        function getLayers(sudut) {
            ensureSudut(sudut);
            return adjustMap[sudut].layers;
        }

        function getActiveLayer() {
            var layers = getLayers(currentSudut());
            if (activeLayerIdx < 0 || activeLayerIdx >= layers.length) activeLayerIdx = 0;
            return layers[activeLayerIdx];
        }

        function syncHidden() {
            if (Array.isArray(adjustMap)) {
                adjustMap = Object.assign({}, adjustMap);
            }
            if (adjustInput) adjustInput.value = JSON.stringify(adjustMap);
            syncPendingInputs();
        }

        function syncPendingInputs() {
            if (!pendingBox) return;
            pendingBox.innerHTML = '';
            Object.keys(pendingFiles).forEach(function (pid) {
                var input = document.createElement('input');
                input.type = 'file';
                input.name = 'layer_file[' + pid + ']';
                input.className = 'sr-only';
                try {
                    var dt = new DataTransfer();
                    dt.items.add(pendingFiles[pid]);
                    input.files = dt.files;
                    pendingBox.appendChild(input);
                } catch (err) {}
            });
        }

        function loadImage(key, url, cb) {
            if (!url) { setTimeout(function () { cb && cb(null); }, 0); return; }
            if (imgCache[key] && imgCache[key].__src === url && imgCache[key].complete && imgCache[key].naturalWidth) {
                setTimeout(function () { cb && cb(imgCache[key]); }, 0);
                return;
            }
            var im = new Image();
            im.__src = url;
            im.onload = function () { imgCache[key] = im; cb && cb(im); };
            im.onerror = function () { cb && cb(null); };
            im.src = url;
            imgCache[key] = im;
        }

        function drawLayer(ctx, img, layer, w, h) {
            if (!img || !img.naturalWidth) return;
            var nw = img.naturalWidth, nh = img.naturalHeight;
            var sx = nw * (layer.cx / 100);
            var sy = nh * (layer.cy / 100);
            var sw = nw * (layer.cw / 100);
            var sh = nh * (layer.ch / 100);
            if (sw < 1 || sh < 1) return;
            var fit = Math.min(w / sw, h / sh);
            var dw = sw * fit, dh = sh * fit;
            ctx.save();
            ctx.translate(w / 2, h / 2);
            ctx.translate((layer.ox / 100) * w, (layer.oy / 100) * h);
            ctx.rotate((layer.rotate || 0) * Math.PI / 180);
            ctx.scale(layer.scale || 1, layer.scale || 1);
            ctx.drawImage(img, sx, sy, sw, sh, -dw / 2, -dh / 2, dw, dh);
            ctx.restore();
        }

        function paint() {
            if (!canvas || !ctx || !slot) return;
            var gen = ++paintGen;
            var rect = slot.getBoundingClientRect();
            var w = Math.max(1, Math.round(rect.width));
            var h = Math.max(1, Math.round(rect.height));
            if (canvas.width !== w || canvas.height !== h) {
                canvas.width = w; canvas.height = h;
            }

            var layers = getLayers(currentSudut());
            var total = layers.length;
            if (!total) {
                ctx.clearRect(0, 0, w, h);
                return;
            }
            var loaded = 0;
            layers.forEach(function (layer) {
                var key = layer.file || 'primary';
                var url = layerUrls[key] || (key === 'primary' ? root.getAttribute('data-draft') : '');
                loadImage(key, url, function () {
                    if (gen !== paintGen) return;
                    loaded++;
                    if (loaded < total) return;
                    if (gen !== paintGen) return;
                    ctx.clearRect(0, 0, w, h);
                    layers.forEach(function (L) {
                        var k = L.file || 'primary';
                        drawLayer(ctx, imgCache[k], L, w, h);
                    });
                });
            });

            var al = getActiveLayer();
            if (al) {
                if (scaleInput) scaleInput.value = String(Math.round((al.scale || 1) * 100));
                if (scaleLabel) scaleLabel.textContent = Math.round((al.scale || 1) * 100) + '%';
                if (rotateLabel) rotateLabel.textContent = Math.round(al.rotate || 0) + '°';
            }
        }

        function renderLayerList() {
            if (!layerList) return;
            var layers = getLayers(currentSudut());
            layerList.innerHTML = '';
            layers.forEach(function (L, i) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full flex items-center justify-between gap-2 px-3 py-2 rounded-xl border text-left text-xs font-semibold transition-colors ' +
                    (i === activeLayerIdx
                        ? 'border-[#051747] bg-blue-50 text-[#051747]'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-[#2E5CE6]');
                var name = (L.file === 'primary') ? 'Draft utama' : (String(L.file).indexOf('pending_') === 0 ? 'Gambar baru' : L.file);
                btn.innerHTML = '<span>Layer ' + (i + 1) + '  ' + name + '</span>';
                if (layers.length > 1) {
                    var rm = document.createElement('span');
                    rm.textContent = 'Hapus';
                    rm.className = 'text-red-500 text-[10px] font-bold';
                    rm.addEventListener('click', function (e) {
                        e.stopPropagation();
                        flushPendingZoomCommit();
                        var layers2 = getLayers(currentSudut());
                        layers2.splice(i, 1);
                        if (activeLayerIdx >= layers2.length) activeLayerIdx = Math.max(0, layers2.length - 1);
                        syncHidden();
                        renderLayerList();
                        paint();
                        commitHistory();
                    });
                    btn.appendChild(rm);
                }
                btn.addEventListener('click', function () {
                    activeLayerIdx = i;
                    renderLayerList();
                    paint();
                });
                layerList.appendChild(btn);
            });
        }

        function renderDots() {
            if (!dotsWrap || angles.length <= 1) return;
            dotsWrap.innerHTML = '';
            angles.forEach(function (_, i) {
                var d = document.createElement('button');
                d.type = 'button';
                d.className = 'h-2 w-2 rounded-full ' + (i === idx ? 'bg-[#051747]' : 'bg-slate-300');
                d.addEventListener('click', function () { idx = i; activeLayerIdx = 0; apply(); });
                dotsWrap.appendChild(d);
            });
        }

        function syncSudutTabs() {
            if (!sudutTabs) return;
            var buttons = sudutTabs.querySelectorAll('[data-mockup-sudut-idx]');
            buttons.forEach(function (btn) {
                var i = parseInt(btn.getAttribute('data-mockup-sudut-idx') || '-1', 10);
                var active = i === idx;
                btn.className = 'shrink-0 text-left text-xs font-bold px-3 py-2 rounded-xl border transition-colors ' +
                    (active
                        ? 'border-[#051747] bg-blue-50 text-[#051747]'
                        : 'border-slate-200 bg-white text-slate-600 hover:border-[#2E5CE6]');
            });
        }

        function apply() {
            var a = angles[idx] || angles[0];
            if (!a) return;
            var sudut = a.sudut || '';
            ensureSudut(sudut);
            if (bg) bg.src = a.background_url;
            if (frame) {
                if (sudut === 'cover') frame.style.aspectRatio = '480 / 990';
                else if (sudut === 'dalam') frame.style.aspectRatio = '720 / 990';
                else frame.style.aspectRatio = '4 / 3';
            }
            if (slot) {
                applySlotStyle();
            }
            var labelText = a.sudut_label || sudut || '';
            if (labels && labels.length) {
                labels.forEach(function (el) { el.textContent = labelText; });
            } else if (label) {
                label.textContent = labelText;
            }
            if (layerHeading) {
                layerHeading.textContent = 'Gambar di sisi ' + String(labelText || '').toUpperCase();
            }
            renderDots();
            syncSudutTabs();
            renderLayerList();
            syncHidden();
            requestAnimationFrame(paint);
        }

        function setMode() {
            if (!editable && showRaw) {
                if (frame) frame.classList.add('hidden');
                if (rawBox) { rawBox.classList.remove('hidden'); rawBox.classList.add('flex'); }
                if (controls) controls.classList.add('hidden');
                if (dotsWrap) dotsWrap.classList.add('hidden');
                if (toggleBtn) toggleBtn.textContent = 'Lihat Mockup';
            } else {
                if (frame) frame.classList.remove('hidden');
                if (rawBox) { rawBox.classList.add('hidden'); rawBox.classList.remove('flex'); }
                if (controls) controls.classList.remove('hidden');
                if (dotsWrap && angles.length > 1) dotsWrap.classList.remove('hidden');
                if (toggleBtn) toggleBtn.textContent = 'Lihat Draft Asli';
            }
        }

        var prev = root.querySelector('[data-mockup-prev]');
        var next = root.querySelector('[data-mockup-next]');
        function goSudut(delta) {
            if (angles.length <= 1) return;
            idx = (idx + delta + angles.length) % angles.length;
            activeLayerIdx = 0;
            apply();
        }
        if (prev) prev.addEventListener('click', function () { goSudut(-1); });
        if (next) next.addEventListener('click', function () { goSudut(1); });

        // Drag horizontal di frame = putar antar sudut (read-only / area di luar slot edit).
        if (frame && angles.length > 1) {
            var spinDragging = false;
            var spinStartX = 0;
            var spinThreshold = 48;
            frame.addEventListener('pointerdown', function (e) {
                if (e.button != null && e.button !== 0) return;
                if (!editable && showRaw) return;
                if (editable && slot && (e.target === slot || slot.contains(e.target))) return;
                spinDragging = true;
                spinStartX = e.clientX;
                try { frame.setPointerCapture(e.pointerId); } catch (err) {}
            });
            frame.addEventListener('pointerup', function (e) {
                if (!spinDragging) return;
                spinDragging = false;
                try { frame.releasePointerCapture(e.pointerId); } catch (err) {}
                var dx = e.clientX - spinStartX;
                if (Math.abs(dx) < spinThreshold) return;
                // Geser kiri → sisi berikutnya (putar), geser kanan → sebelumnya
                goSudut(dx < 0 ? 1 : -1);
            });
            frame.addEventListener('pointercancel', function (e) {
                if (!spinDragging) return;
                spinDragging = false;
                try { frame.releasePointerCapture(e.pointerId); } catch (err) {}
            });
        }

        if (sudutTabs) {
            sudutTabs.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-mockup-sudut-idx]');
                if (!btn || !sudutTabs.contains(btn)) return;
                var i = parseInt(btn.getAttribute('data-mockup-sudut-idx') || '-1', 10);
                if (isNaN(i) || i < 0 || i >= angles.length) return;
                idx = i;
                activeLayerIdx = 0;
                apply();
            });
        }
        if (toggleBtn) toggleBtn.addEventListener('click', function () {
            showRaw = !showRaw;
            setMode();
        });
        window.addEventListener('resize', function () { paint(); });

        function openMockupFullSize() {
            var a = angles[idx] || angles[0];
            if (!a || !bg) return;

            function renderAndOpen(bgImg) {
                if (!bgImg || !bgImg.naturalWidth) return;
                var nw = bgImg.naturalWidth;
                var nh = bgImg.naturalHeight;
                var out = document.createElement('canvas');
                out.width = nw;
                out.height = nh;
                var octx = out.getContext('2d');
                if (!octx) return;

                octx.fillStyle = '#F0F2F8';
                octx.fillRect(0, 0, nw, nh);
                octx.drawImage(bgImg, 0, 0, nw, nh);

                var slotRect = getSlotRect();
                var slotX = (Number(slotRect.left) || 0) / 100 * nw;
                var slotY = (Number(slotRect.top) || 0) / 100 * nh;
                var slotW = Math.max(1, (Number(slotRect.width) || 60) / 100 * nw);
                var slotH = Math.max(1, (Number(slotRect.height) || 60) / 100 * nh);

                var sudutKey = a.sudut || currentSudut();
                var layers = getLayers(sudutKey);
                var total = layers.length;
                if (!total) {
                    openCanvas(out);
                    return;
                }

                var layerCanvas = document.createElement('canvas');
                layerCanvas.width = Math.max(1, Math.round(slotW));
                layerCanvas.height = Math.max(1, Math.round(slotH));
                var lctx = layerCanvas.getContext('2d');
                if (!lctx) {
                    openCanvas(out);
                    return;
                }

                var loaded = 0;
                layers.forEach(function (layer) {
                    var key = layer.file || 'primary';
                    var url = layerUrls[key] || (key === 'primary' ? root.getAttribute('data-draft') : '');
                    loadImage(key, url, function () {
                        loaded++;
                        if (loaded < total) return;
                        lctx.clearRect(0, 0, layerCanvas.width, layerCanvas.height);
                        layers.forEach(function (L) {
                            var k = L.file || 'primary';
                            drawLayer(lctx, imgCache[k], L, layerCanvas.width, layerCanvas.height);
                        });
                        octx.save();
                        octx.globalCompositeOperation = 'multiply';
                        octx.drawImage(layerCanvas, slotX, slotY, slotW, slotH);
                        octx.restore();
                        openCanvas(out);
                    });
                });
            }

            function openCanvas(canvasEl) {
                try {
                    canvasEl.toBlob(function (blob) {
                        if (!blob) {
                            var dataUrl = canvasEl.toDataURL('image/png');
                            window.open(dataUrl, '_blank', 'noopener,noreferrer');
                            return;
                        }
                        var url = URL.createObjectURL(blob);
                        var win = window.open(url, '_blank', 'noopener,noreferrer');
                        if (!win) {
                            var aTag = document.createElement('a');
                            aTag.href = url;
                            aTag.target = '_blank';
                            aTag.rel = 'noopener noreferrer';
                            aTag.click();
                        }
                        window.setTimeout(function () { URL.revokeObjectURL(url); }, 60000);
                    }, 'image/png');
                } catch (err) {
                    try {
                        window.open(canvasEl.toDataURL('image/png'), '_blank', 'noopener,noreferrer');
                    } catch (e2) {}
                }
            }

            if (bg.complete && bg.naturalWidth) {
                renderAndOpen(bg);
                return;
            }
            var bgUrl = bg.getAttribute('src') || (a.background_url || '');
            if (!bgUrl) return;
            var im = new Image();
            im.onload = function () { renderAndOpen(im); };
            im.src = bgUrl;
        }

        var fullBtn = root.querySelector('[data-mockup-fullsize]');
        if (fullBtn) {
            fullBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openMockupFullSize();
            });
        }
        if (editable && slot) {
            var dragging = false, startX = 0, startY = 0, startOx = 0, startOy = 0;
            var scaleDragging = false;
            var scaleStartVal = 100;
            var slotAdjustMode = false;
            var slotDrag = null; // { type: 'move'|'nw'|..., startX, startY, startRect }
            var slotModeBtn = root.querySelector('[data-mockup-slot-mode]');
            var slotResetBtn = root.querySelector('[data-mockup-slot-reset]');
            var slotHandles = slot.querySelectorAll('[data-mockup-slot-handle]');

            function syncSlotModeUi() {
                if (slotModeBtn) {
                    slotModeBtn.setAttribute('aria-pressed', slotAdjustMode ? 'true' : 'false');
                    slotModeBtn.textContent = slotAdjustMode ? 'Selesai atur area' : 'Atur area mockup';
                    slotModeBtn.className = slotAdjustMode
                        ? 'w-full text-xs font-bold px-4 py-2 rounded-full bg-[#051747] text-white hover:bg-[#2E5CE6] transition-colors'
                        : 'w-full text-xs font-bold px-4 py-2 rounded-full border border-[#051747] text-[#051747] hover:bg-[#051747] hover:text-white transition-colors';
                }
                if (slotResetBtn) {
                    if (slotAdjustMode) slotResetBtn.classList.remove('hidden');
                    else slotResetBtn.classList.add('hidden');
                }
                slotHandles.forEach(function (h) {
                    if (slotAdjustMode) h.classList.remove('hidden');
                    else h.classList.add('hidden');
                });
                if (slot) {
                    if (slotAdjustMode) {
                        slot.classList.add('ring-amber-400');
                        slot.classList.remove('ring-[#2E5CE6]/70');
                        slot.style.cursor = 'move';
                    } else {
                        slot.classList.remove('ring-amber-400');
                        slot.classList.add('ring-[#2E5CE6]/70');
                        slot.style.cursor = '';
                    }
                }
            }

            if (slotModeBtn) {
                slotModeBtn.addEventListener('click', function () {
                    slotAdjustMode = !slotAdjustMode;
                    syncSlotModeUi();
                });
            }
            if (slotResetBtn) {
                slotResetBtn.addEventListener('click', function () {
                    flushPendingZoomCommit();
                    var sudut = currentSudut();
                    if (!sudut || !adjustMap[sudut]) return;
                    delete adjustMap[sudut].slot;
                    applySlotStyle();
                    syncHidden();
                    requestAnimationFrame(paint);
                    commitHistory();
                });
            }
            syncSlotModeUi();

            function bumpZoom(delta, deferCommit) {
                if (!deferCommit) flushPendingZoomCommit();
                var L = getActiveLayer();
                L.scale = Math.max(0.4, Math.min(2.5, (L.scale || 1) + delta));
                syncHidden(); paint();
                if (deferCommit) {
                    if (wheelZoomTimer) clearTimeout(wheelZoomTimer);
                    wheelZoomTimer = setTimeout(function () {
                        wheelZoomTimer = null;
                        commitHistory();
                    }, 280);
                } else {
                    commitHistory();
                }
            }
            function nudge(dx, dy) {
                flushPendingZoomCommit();
                var L = getActiveLayer();
                L.ox = Math.max(-80, Math.min(80, (L.ox || 0) + dx));
                L.oy = Math.max(-80, Math.min(80, (L.oy || 0) + dy));
                syncHidden(); paint();
                commitHistory();
            }
            function bumpRotate(delta) {
                flushPendingZoomCommit();
                var L = getActiveLayer();
                var r = ((Number(L.rotate) || 0) + delta) % 360;
                if (r < 0) r += 360;
                L.rotate = r;
                syncHidden(); paint();
                commitHistory();
            }

            function resizeSlotFromHandle(handle, startRect, dxPct, dyPct) {
                var r = {
                    top: startRect.top,
                    left: startRect.left,
                    width: startRect.width,
                    height: startRect.height
                };
                if (handle === 'nw' || handle === 'sw') {
                    var newLeft = startRect.left + dxPct;
                    var newWidth = startRect.width - dxPct;
                    if (newWidth >= 8) {
                        r.left = newLeft;
                        r.width = newWidth;
                    }
                }
                if (handle === 'ne' || handle === 'se') {
                    r.width = startRect.width + dxPct;
                }
                if (handle === 'nw' || handle === 'ne') {
                    var newTop = startRect.top + dyPct;
                    var newHeight = startRect.height - dyPct;
                    if (newHeight >= 8) {
                        r.top = newTop;
                        r.height = newHeight;
                    }
                }
                if (handle === 'sw' || handle === 'se') {
                    r.height = startRect.height + dyPct;
                }
                return clampSlotRect(r);
            }

            slot.addEventListener('pointerdown', function (e) {
                if (e.button != null && e.button !== 0) return;
                e.preventDefault();
                flushPendingZoomCommit();
                var handleEl = e.target && e.target.closest ? e.target.closest('[data-mockup-slot-handle]') : null;
                var handle = handleEl ? (handleEl.getAttribute('data-mockup-slot-handle') || '') : '';

                if (slotAdjustMode || handle) {
                    if (!frame) return;
                    var rect = frame.getBoundingClientRect();
                    if (!rect.width || !rect.height) return;
                    slotDrag = {
                        type: handle || 'move',
                        startX: e.clientX,
                        startY: e.clientY,
                        startRect: getSlotRect(),
                        frameW: rect.width,
                        frameH: rect.height
                    };
                    try { slot.setPointerCapture(e.pointerId); } catch (err) {}
                    return;
                }

                dragging = true;
                slot.setPointerCapture(e.pointerId);
                var L = getActiveLayer();
                startX = e.clientX; startY = e.clientY;
                startOx = L.ox || 0; startOy = L.oy || 0;
            });
            slot.addEventListener('pointermove', function (e) {
                if (slotDrag) {
                    var dxPct = ((e.clientX - slotDrag.startX) / slotDrag.frameW) * 100;
                    var dyPct = ((e.clientY - slotDrag.startY) / slotDrag.frameH) * 100;
                    var next;
                    if (slotDrag.type === 'move') {
                        next = clampSlotRect({
                            top: slotDrag.startRect.top + dyPct,
                            left: slotDrag.startRect.left + dxPct,
                            width: slotDrag.startRect.width,
                            height: slotDrag.startRect.height
                        });
                    } else {
                        next = resizeSlotFromHandle(slotDrag.type, slotDrag.startRect, dxPct, dyPct);
                    }
                    setSlotRect(next, false);
                    return;
                }
                if (!dragging) return;
                var rect = slot.getBoundingClientRect();
                var L = getActiveLayer();
                L.ox = Math.max(-80, Math.min(80, startOx + ((e.clientX - startX) / rect.width) * 100));
                L.oy = Math.max(-80, Math.min(80, startOy + ((e.clientY - startY) / rect.height) * 100));
                syncHidden(); paint();
            });
            function endDrag(e) {
                if (slotDrag) {
                    slotDrag = null;
                    try { slot.releasePointerCapture(e.pointerId); } catch (err) {}
                    commitHistory();
                    return;
                }
                if (!dragging) return;
                dragging = false;
                try { slot.releasePointerCapture(e.pointerId); } catch (err) {}
                var L = getActiveLayer();
                if ((L.ox || 0) !== startOx || (L.oy || 0) !== startOy) {
                    commitHistory();
                }
            }
            slot.addEventListener('pointerup', endDrag);
            slot.addEventListener('pointercancel', endDrag);
            slot.addEventListener('wheel', function (e) {
                if (slotAdjustMode) return;
                e.preventDefault();
                bumpZoom(e.deltaY < 0 ? 0.1 : -0.1, true);
            }, { passive: false });

            var zoomInBtn = root.querySelector('[data-mockup-zoom-in]');
            var zoomOutBtn = root.querySelector('[data-mockup-zoom-out]');
            if (zoomInBtn) zoomInBtn.addEventListener('click', function () { bumpZoom(0.1, false); });
            if (zoomOutBtn) zoomOutBtn.addEventListener('click', function () { bumpZoom(-0.1, false); });
            var rotCw = root.querySelector('[data-mockup-rotate-cw]');
            var rotCcw = root.querySelector('[data-mockup-rotate-ccw]');
            if (rotCw) rotCw.addEventListener('click', function () { bumpRotate(90); });
            if (rotCcw) rotCcw.addEventListener('click', function () { bumpRotate(-90); });
            root.querySelectorAll('[data-mockup-nudge]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var p = (btn.getAttribute('data-mockup-nudge') || '0,0').split(',');
                    nudge(Number(p[0]) || 0, Number(p[1]) || 0);
                });
            });
            if (scaleInput) {
                scaleInput.addEventListener('pointerdown', function () {
                    flushPendingZoomCommit();
                    scaleDragging = true;
                    scaleStartVal = Number(scaleInput.value) || 100;
                });
                scaleInput.addEventListener('input', function () {
                    var L = getActiveLayer();
                    L.scale = Math.max(0.4, Math.min(2.5, (Number(scaleInput.value) || 100) / 100));
                    syncHidden(); paint();
                });
                scaleInput.addEventListener('change', function () {
                    if (scaleDragging && (Number(scaleInput.value) || 100) !== scaleStartVal) {
                        commitHistory();
                    }
                    scaleDragging = false;
                });
            }
            if (resetBtn) resetBtn.addEventListener('click', function () {
                flushPendingZoomCommit();
                var layers = getLayers(currentSudut());
                var file = (layers[activeLayerIdx] && layers[activeLayerIdx].file) || 'primary';
                layers[activeLayerIdx] = defaultLayer(file);
                layers[activeLayerIdx].id = 'l' + Date.now();
                syncHidden(); renderLayerList(); paint();
                commitHistory();
            });
            if (undoBtn) undoBtn.addEventListener('click', function (e) {
                e.preventDefault();
                undo();
            });
            if (redoBtn) redoBtn.addEventListener('click', function (e) {
                e.preventDefault();
                redo();
            });
            document.addEventListener('keydown', function (e) {
                if (!editable) return;
                var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                if (tag === 'input' || tag === 'textarea') return;
                var mod = e.ctrlKey || e.metaKey;
                if (!mod) return;
                var key = (e.key || '').toLowerCase();
                if (key === 'z' && !e.shiftKey) {
                    e.preventDefault();
                    undo();
                } else if (key === 'y' || (key === 'z' && e.shiftKey)) {
                    e.preventDefault();
                    redo();
                }
            });

            var addPrimary = root.querySelector('[data-mockup-add-primary]');
            var addFile = root.querySelector('[data-mockup-add-file]');
            if (addPrimary) addPrimary.addEventListener('click', function () {
                flushPendingZoomCommit();
                var layers = getLayers(currentSudut());
                if (layers.length >= 4) { alert('Maksimal 4 gambar per sudut.'); return; }
                layers.push(defaultLayer('primary'));
                activeLayerIdx = layers.length - 1;
                syncHidden(); renderLayerList(); paint();
                commitHistory();
            });
            if (addFile) addFile.addEventListener('change', function () {
                flushPendingZoomCommit();
                var file = this.files && this.files[0];
                this.value = '';
                if (!file || !file.type.match(/^image\/(jpeg|png)$/)) {
                    alert('Format harus JPG/PNG.');
                    return;
                }
                if (file.size > 1024 * 1024) {
                    alert('Maksimal 1MB.');
                    return;
                }
                var layers = getLayers(currentSudut());
                if (layers.length >= 4) { alert('Maksimal 4 gambar per sudut.'); return; }
                var pid = 'pending_' + Date.now();
                pendingFiles[pid] = file;
                layerUrls[pid] = URL.createObjectURL(file);
                layers.push(defaultLayer(pid));
                activeLayerIdx = layers.length - 1;
                syncHidden(); renderLayerList(); paint();
                commitHistory();
            });

            // Crop modal — tidak menyentuh input file draft
            var cropStage = cropModal ? cropModal.querySelector('[data-mockup-crop-stage]') : null;
            var cropImg = cropModal ? cropModal.querySelector('[data-mockup-crop-img]') : null;
            var cropBox = cropModal ? cropModal.querySelector('[data-mockup-crop-box]') : null;
            var cropSudutLabel = cropModal ? cropModal.querySelector('[data-mockup-crop-sudut-label]') : null;
            var cropOpen = root.querySelector('[data-mockup-crop-open]');
            var cropApply = cropModal ? cropModal.querySelector('[data-mockup-crop-apply]') : null;
            var cropCancel = cropModal ? cropModal.querySelector('[data-mockup-crop-cancel]') : null;
            var cropReset = cropModal ? cropModal.querySelector('[data-mockup-crop-reset]') : null;

            function imgDisplayRect() {
                if (!cropImg || !cropStage) return null;
                var ir = cropImg.getBoundingClientRect();
                var sr = cropStage.getBoundingClientRect();
                return { left: ir.left - sr.left, top: ir.top - sr.top, width: ir.width, height: ir.height };
            }
            function setCropBoxFromPct(cx, cy, cw, ch) {
                var r = imgDisplayRect();
                if (!r || !cropBox) return;
                cropBox.style.left = (r.left + r.width * cx / 100) + 'px';
                cropBox.style.top = (r.top + r.height * cy / 100) + 'px';
                cropBox.style.width = (r.width * cw / 100) + 'px';
                cropBox.style.height = (r.height * ch / 100) + 'px';
            }
            function readCropBoxPct() {
                var r = imgDisplayRect();
                if (!r || !cropBox || r.width < 1 || r.height < 1) return { cx: 0, cy: 0, cw: 100, ch: 100 };
                var br = cropBox.getBoundingClientRect();
                var sr = cropStage.getBoundingClientRect();
                var left = br.left - sr.left - r.left;
                var top = br.top - sr.top - r.top;
                var cx = Math.max(0, Math.min(99, left / r.width * 100));
                var cy = Math.max(0, Math.min(99, top / r.height * 100));
                var cw = Math.max(5, Math.min(100 - cx, br.width / r.width * 100));
                var ch = Math.max(5, Math.min(100 - cy, br.height / r.height * 100));
                return { cx: cx, cy: cy, cw: cw, ch: ch };
            }
            function openCrop() {
                if (!cropModal || !cropImg) return;
                var L = getActiveLayer();
                var key = L.file || 'primary';
                var url = layerUrls[key] || root.getAttribute('data-draft') || '';
                if (!url) { alert('Gambar layer belum tersedia.'); return; }
                var a = angles[idx] || {};
                if (cropSudutLabel) {
                    cropSudutLabel.textContent = (a.sudut_label || currentSudut()) + ' · Layer ' + (activeLayerIdx + 1);
                }
                // Jangan sentuh source draft / file input — hanya preview crop
                cropImg.onload = function () {
                    setCropBoxFromPct(L.cx || 0, L.cy || 0, L.cw || 100, L.ch || 100);
                };
                if (cropImg.src !== url) cropImg.src = url;
                else setCropBoxFromPct(L.cx || 0, L.cy || 0, L.cw || 100, L.ch || 100);
                cropModal.classList.remove('hidden');
                cropModal.classList.add('flex');
            }
            function closeCrop() {
                if (!cropModal) return;
                cropModal.classList.add('hidden');
                cropModal.classList.remove('flex');
            }
            if (cropOpen) cropOpen.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openCrop();
            });
            if (cropCancel) cropCancel.addEventListener('click', function (e) {
                e.preventDefault();
                closeCrop();
            });
            if (cropReset) cropReset.addEventListener('click', function (e) {
                e.preventDefault();
                setCropBoxFromPct(0, 0, 100, 100);
            });
            if (cropApply) cropApply.addEventListener('click', function (e) {
                e.preventDefault();
                flushPendingZoomCommit();
                var pct = readCropBoxPct();
                var L = getActiveLayer();
                L.cx = pct.cx; L.cy = pct.cy; L.cw = pct.cw; L.ch = pct.ch;
                syncHidden();
                closeCrop();
                paint();
                commitHistory();
            });
            if (cropModal) {
                cropModal.addEventListener('click', function (e) {
                    if (e.target === cropModal) closeCrop();
                });
            }
            if (cropBox && cropStage) {
                var cropDrag = null;
                cropBox.addEventListener('pointerdown', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var handle = e.target.getAttribute('data-crop-handle');
                    var br = cropBox.getBoundingClientRect();
                    var sr = cropStage.getBoundingClientRect();
                    cropDrag = {
                        handle: handle || 'move',
                        startX: e.clientX, startY: e.clientY,
                        left: br.left - sr.left, top: br.top - sr.top,
                        width: br.width, height: br.height
                    };
                    cropBox.setPointerCapture(e.pointerId);
                });
                cropBox.addEventListener('pointermove', function (e) {
                    if (!cropDrag) return;
                    var r = imgDisplayRect();
                    if (!r) return;
                    var dx = e.clientX - cropDrag.startX;
                    var dy = e.clientY - cropDrag.startY;
                    var left = cropDrag.left, top = cropDrag.top, width = cropDrag.width, height = cropDrag.height;
                    var h = cropDrag.handle;
                    if (h === 'move') { left += dx; top += dy; }
                    else {
                        if (h.indexOf('e') >= 0) width += dx;
                        if (h.indexOf('s') >= 0) height += dy;
                        if (h.indexOf('w') >= 0) { left += dx; width -= dx; }
                        if (h.indexOf('n') >= 0) { top += dy; height -= dy; }
                    }
                    if (width < 24) width = 24;
                    if (height < 24) height = 24;
                    left = Math.max(r.left, Math.min(r.left + r.width - width, left));
                    top = Math.max(r.top, Math.min(r.top + r.height - height, top));
                    width = Math.min(width, r.left + r.width - left);
                    height = Math.min(height, r.top + r.height - top);
                    cropBox.style.left = left + 'px';
                    cropBox.style.top = top + 'px';
                    cropBox.style.width = width + 'px';
                    cropBox.style.height = height + 'px';
                });
                cropBox.addEventListener('pointerup', function () { cropDrag = null; });
                cropBox.addEventListener('pointercancel', function () { cropDrag = null; });
            }

            root.setDraftSrc = function (url) {
                root.setAttribute('data-draft', url || '');
                delete imgCache.primary;
                if (url) {
                    layerUrls.primary = url;
                } else {
                    delete layerUrls.primary;
                }
                paint();
            };

            // Pastikan file pending ikut form submit
            var form = root.closest('form');
            if (form) {
                form.addEventListener('submit', function () { syncPendingInputs(); });
            }
        }

        // Ganti draft + adjust (mis. pelanggan pilih versi di radio ACC).
        root.setReadOnlyPreview = function (payload) {
            payload = payload || {};
            var url = String(payload.draftUrl || '');
            var title = String(payload.title || '');
            var nextAdjust = payload.adjust;
            var nextUrls = payload.layerUrls;

            // Batalkan paint async versi lama agar tidak menimpa canvas.
            paintGen += 1;
            if (canvas && ctx) {
                ctx.clearRect(0, 0, canvas.width || 0, canvas.height || 0);
            }

            root.setAttribute('data-draft', url);
            imgCache = {};
            layerUrls = (nextUrls && typeof nextUrls === 'object' && !Array.isArray(nextUrls))
                ? Object.assign({}, nextUrls)
                : {};
            if (url) {
                layerUrls.primary = url;
            }

            adjustMap = cloneAdjustMap(
                (nextAdjust && typeof nextAdjust === 'object') ? nextAdjust : {}
            );
            if (Array.isArray(adjustMap)) {
                adjustMap = Object.assign({}, adjustMap);
            }

            var titleEl = root.querySelector('[data-mockup-title]');
            if (titleEl && title !== '') {
                titleEl.textContent = title;
            }
            if (rawBox) {
                var rawImg = rawBox.querySelector('img');
                if (rawImg) {
                    rawImg.src = url || '';
                }
            }

            showRaw = false;
            idx = 0;
            activeLayerIdx = 0;
            angles.forEach(function (a) {
                if (a && a.sudut) ensureSudut(a.sudut);
            });
            apply();
            setMode();
            syncHidden();
        };

        // Pastikan setiap sudut punya minimal 1 layer
        angles.forEach(function (a) {
            if (a && a.sudut) ensureSudut(a.sudut);
        });

        apply();
        setMode();
        syncHidden();
        if (editable) {
            commitHistory(); // baseline — Undo mundur per aksi, bukan langsung ke sini kecuali 1 langkah
            updateUndoRedoUi();
        }
    })();
    </script>
<?php endif; ?>
