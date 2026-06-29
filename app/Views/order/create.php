<?php

/**
 * @var array<string, mixed>              $katalog
 * @var list<array<string, mixed>>       $fields
 * @var array<string, mixed>|null       $pelanggan
 * @var string                           $title
 * @var string                           $page_title
 */
$isKerjasama     = pelangganIsKerjasamaPerusahaan($pelanggan);
$jenisSkema      = $isKerjasama ? 'perusahaan' : 'perseorangan';
$stepJenis       = $isKerjasama ? 2 : 1;
$stepJumlah      = $stepJenis + 1;
$stepSpesifikasi = $stepJumlah + 1;
$stepDetail      = ! empty($fields) ? $stepSpesifikasi + 1 : $stepJumlah + 1;
$stepReferensi   = $stepDetail + 1;
$stepDeadline    = $stepReferensi + 1;
$stepPengiriman  = $stepDeadline + 1;
$detailMinChars  = 5;
$alamatMinChars  = 10;
$orderFieldHint  = static function (string $type, bool $required): string {
    return match ($type) {
        'file'     => 'JPG, PNG, atau PDF · maks. 2MB',
        'date'     => 'Pilih tanggal pada kalender.',
        'time'     => 'Format waktu (contoh: 14:30).',
        'textarea' => $required ? 'Jelaskan spesifikasi dengan jelas.' : '',
        default    => '',
    };
};
$alamatProfil    = trim((string) ($pelanggan['alamat'] ?? ''));
$alamatKirimPrefill = trim((string) old('alamat_kirim', $alamatProfil));
$hasAlamatProfil = $alamatProfil !== '';
helper('notification');
$batasTanpaDp    = BATAS_ORDER_TANPA_DP;
$idKatalog       = (int) ($katalog['id_katalog'] ?? 0);
$minOrder        = (int) ($katalog['min_order'] ?? 1);
$hargaDasar      = (float) ($katalog['harga_dasar'] ?? 0);
$kuotaRevisi     = (int) ($katalog['kuota_revisi_default'] ?? 0);
$satuan          = (string) ($katalog['satuan'] ?? 'pcs');
$namaProduk      = (string) ($katalog['nama_produk'] ?? '-');
$kategoriKey     = (string) ($katalog['kategori'] ?? '');
$estimasiHari    = (string) ($katalog['estimasi_hari'] ?? '-');
helper('deadline');
$estimasiMinHariKerja = parseEstimasiHariKerja($estimasiHari);
$deskripsiKatalog = trim((string) ($katalog['deskripsi'] ?? ''));
$gambarKatalog   = trim((string) ($katalog['gambar'] ?? ''));
$kategoriLabel = match ($kategoriKey) {
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
    default         => str_replace('_', ' ', $kategoriKey),
};
$hasOldInput         = old('form_step') !== null
    || old('detail_pesanan') !== null
    || old('jumlah_order') !== null
    || old('deadline_diajukan') !== null;
$clearOrderDraft     = false;
$flashClearDraft     = session()->getFlashdata('clear_order_draft_katalog');
if ($flashClearDraft !== null && (int) $flashClearDraft === $idKatalog) {
    $clearOrderDraft = true;
}
$initialFormStep      = (int) old('form_step', 1) === 2 ? 2 : 1;
$isCustomOld          = old('is_custom');
$isCustomSelected     = $isCustomOld !== null ? (int) $isCustomOld === 1 : false;
$metodePengirimanOld  = (string) old('metode_pengiriman', 'kurir');
$deadlineOld          = (string) old('deadline_diajukan', '');
$jumlahOrderVal       = old('jumlah_order') !== null && old('jumlah_order') !== ''
    ? (string) old('jumlah_order')
    : (string) $minOrder;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Buat Pesanan') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Buat Pesanan Baru') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex items-center justify-center gap-3 sm:gap-4 mb-8 max-w-xl mx-auto">
    <div class="flex items-center gap-2 shrink-0">
        <div id="stepDot1" class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold bg-[#051747] text-white">①</div>
        <span id="stepLabel1" class="text-xs sm:text-sm font-bold text-[#051747] whitespace-nowrap">Detail Pesanan</span>
    </div>
    <div class="flex-1 h-0.5 bg-slate-200 min-w-[40px] max-w-[100px]"></div>
    <div class="flex items-center gap-2 shrink-0">
        <div id="stepDot2" class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 border-slate-300 text-slate-400">②</div>
        <span id="stepLabel2" class="text-xs sm:text-sm text-slate-400 whitespace-nowrap">Pengiriman &amp; Konfirmasi</span>
    </div>
</div>

<div class="bg-[#051747] text-white rounded-2xl p-5 mb-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-start">
        <div class="flex items-start gap-4">
            <?php if ($gambarKatalog !== ''): ?>
                <button
                    type="button"
                    id="btnZoomProduk"
                    class="group relative shrink-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-white/60"
                    aria-label="Perbesar foto produk">
                    <img
                        src="<?= esc(base_url('uploads/katalog/' . $gambarKatalog)) ?>"
                        alt="<?= esc($namaProduk) ?>"
                        class="w-20 h-20 rounded-xl object-cover border border-white/20 bg-white/10 cursor-zoom-in transition-transform group-hover:scale-[1.02]">
                    <span class="absolute -bottom-1 -right-1 bg-white text-[#051747] text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm">Zoom</span>
                </button>
            <?php endif; ?>
            <div>
                <h2 class="text-xl font-bold"><?= esc($namaProduk) ?></h2>
                <?php if ($kategoriLabel !== ''): ?>
                    <span class="inline-flex mt-2 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-white/20 text-white">
                        <?= esc($kategoriLabel) ?>
                    </span>
                <?php endif; ?>
                <?php if ($deskripsiKatalog !== ''): ?>
                    <p class="text-xs text-blue-200 mt-2 leading-relaxed">
                        <span class="font-semibold text-white/90">Sudah Include:</span>
                        <?= esc($deskripsiKatalog) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-white/90 shrink-0">
            <span class="inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.57 1.03-2.75 2.93-3.07V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.63 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z" />
                </svg>
                Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?>/<?= esc($satuan) ?>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M20 2H4c-1 0-2 .9-2 2v3.01c0 .72.43 1.34 1 1.69V20c0 1.1 1.1 2 2 2h14c.9 0 2-.9 2-2V8.7c.57-.35 1-.97 1-1.69V4c0-1.1-1-2-2-2zm-5 12H9v-2h6v2zm5-7H4V4h16v3z" />
                </svg>
                Min. <?= esc((string) $minOrder) ?> <?= esc($satuan) ?>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
                </svg>
                <?= esc($estimasiHari) ?>
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2C6.49 2 2 6.49 2 12s4.49 10 10 10c1.38 0 2.5-1.12 2.5-2.5 0-.61-.23-1.21-.64-1.67-.08-.1-.13-.21-.13-.33 0-.28.22-.5.5-.5H16c3.31 0 6-2.69 6-6 0-4.96-4.49-9-10-9zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 8 6.5 8 8 8.67 8 9.5 7.33 11 6.5 11zm3-4C8.67 7 8 6.33 8 5.5S8.67 4 9.5 4s1.5.67 1.5 1.5S10.33 7 9.5 7zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 4 14.5 4s1.5.67 1.5 1.5S15.33 7 14.5 7zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 8 17.5 8s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z" />
                </svg>
                <?= esc((string) $kuotaRevisi) ?>x revisi
            </span>
        </div>
    </div>
</div>

<form
    method="post"
    action="<?= esc(site_url('order/simpan')) ?>"
    enctype="multipart/form-data"
    id="formPesan">
    <?= csrf_field() ?>
    <input type="hidden" name="id_katalog" value="<?= esc((string) $idKatalog) ?>">
    <input type="hidden" name="form_step" id="formStep" value="<?= esc((string) $initialFormStep) ?>">
    <p class="form-note mb-4">
        <span class="text-red-500 font-bold">*</span> yang diberi bintang merah wajib diisi.
    </p>

    <div id="step1" <?= $initialFormStep === 2 ? ' class="hidden"' : '' ?>>
        <input type="hidden" name="jenis_pelanggan" value="<?= esc($jenisSkema) ?>">

        <?php if ($isKerjasama): ?>
            <p class="form-section-label mb-3">1. Skema Pembayaran</p>

            <div class="border-2 border-[#051747] rounded-2xl p-4 bg-blue-50 mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-xl shrink-0">🏢</div>
                    <div>
                        <p class="font-bold text-[#051747] text-sm">Kerja Sama Perusahaan</p>
                        <p class="text-xs text-slate-600 mt-1">
                            Order ≤ Rp <?= esc(number_format($batasTanpaDp, 0, ',', '.')) ?> tanpa DP · pelunasan setelah diterima.
                            Order di atas Rp <?= esc(number_format($batasTanpaDp, 0, ',', '.')) ?> wajib DP 50% · sisa pelunasan tetap setelah diterima.
                        </p>
                        <p class="form-hint mt-2">Skema ditetapkan otomatis dari status akun Anda.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <p class="form-section-label mb-3 <?= $isKerjasama ? '' : 'mt-0' ?>"><?= esc((string) $stepJenis) ?>. Jenis Pesanan</p>

        <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    type="radio"
                    name="is_custom"
                    value="0"
                    <?= $isCustomSelected ? '' : 'checked' ?>
                    class="w-4 h-4 accent-[#051747]"
                    onchange="toggleCustom(false)">
                <span class="form-choice">Pesanan Standar</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    type="radio"
                    name="is_custom"
                    value="1"
                    <?= $isCustomSelected ? 'checked' : '' ?>
                    class="w-4 h-4 accent-[#051747]"
                    onchange="toggleCustom(true)">
                <span class="form-choice">Pesanan Custom</span>
            </label>
        </div>

        <p class="form-hint mt-2">
            Pesanan Standar menggunakan spesifikasi dan harga yang tersedia pada katalog.
            Pilih Pesanan Custom untuk kebutuhan di luar spesifikasi katalog yang memerlukan peninjauan harga oleh Admin.
        </p>

        <div id="customNote" class="<?= $isCustomSelected ? '' : 'hidden ' ?>mt-3 p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-sm text-indigo-800">
            <span class="inline-flex align-middle mr-1.5 text-indigo-600" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>Harga akan ditentukan setelah Admin meninjau spesifikasi yang diajukan.
            Silakan jelaskan kebutuhan atau spesifikasi khusus pada kolom di bawah ini.
        </div>
        <div id="catatanCustomWrapper" class="<?= $isCustomSelected ? '' : 'hidden ' ?>mt-3">
            <label class="form-label">Detail Spesifikasi Custom <span class="text-red-500">*</span></label>
            <textarea
                name="catatan_custom"
                id="catatanCustom"
                rows="3"
                minlength="10"
                <?= $isCustomSelected ? 'required' : 'disabled' ?>
                class="form-input w-full px-3.5 py-2.5"
                placeholder="Jelaskan spesifikasi custom yang diinginkan..."><?= esc((string) old('catatan_custom')) ?></textarea>
            <p class="form-hint">Minimal 10 karakter.</p>
        </div>

        <p class="form-section-label mb-3 mt-6"><?= esc((string) $stepJumlah) ?>. Jumlah Pesanan <span class="text-red-500">*</span></p>

        <div class="flex items-end gap-3">
            <input
                type="number"
                name="jumlah_order"
                id="jumlahOrder"
                value="<?= esc($jumlahOrderVal) ?>"
                min="<?= esc((string) $minOrder) ?>"
                step="1"
                onwheel="this.blur()"
                class="form-input w-40 px-3.5 py-2.5">
            <span class="form-affix pb-2.5"><?= esc($satuan) ?></span>
        </div>
        <p class="form-hint">
            Angka bulat · minimum <?= esc((string) $minOrder) ?> <?= esc($satuan) ?>.
        </p>
        <p id="errJumlah" class="hidden form-hint-error">
            Minimum order <?= esc((string) $minOrder) ?> <?= esc($satuan) ?>
        </p>

        <?php if (!empty($fields)): ?>
            <div id="spesifikasiKhususWrapper" class="<?= $isCustomSelected ? 'hidden' : '' ?>">
            <p class="form-section-label mb-3 mt-6"><?= esc((string) $stepSpesifikasi) ?>. Spesifikasi Khusus <span class="text-red-500">*</span></p>
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-lg" aria-hidden="true">📋</span>
                    <p class="font-semibold text-indigo-900 text-sm">
                        Spesifikasi Khusus <?= esc($namaProduk) ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($fields as $f): ?>
                        <?php
                        $fieldKey    = (string) ($f['field_key'] ?? '');
                        $fieldLabel  = (string) ($f['field_label'] ?? '');
                        $fieldType   = (string) ($f['field_type'] ?? 'text');
                        $placeholder = (string) ($f['placeholder'] ?? '');
                        $isRequired  = (int) ($f['is_required'] ?? 0) === 1;
                        $isHariOtomatis = in_array($fieldKey, ['akad_hari', 'resepsi_hari'], true);
                        $inputClass  = $isHariOtomatis
                            ? 'form-input w-full px-3.5 py-2.5 bg-slate-100 text-slate-600 cursor-not-allowed'
                            : 'form-input w-full px-3.5 py-2.5';
                        $oldVal      = old('eav.' . $fieldKey);
                        ?>
                        <div>
                            <label class="form-label">
                                <?= esc($fieldLabel) ?>
                                <?php if ($isRequired): ?>
                                    <span class="text-red-500 ml-0.5">*</span>
                                <?php endif; ?>
                            </label>

                            <?php if ($fieldType === 'textarea'): ?>
                                <textarea
                                    name="eav[<?= esc($fieldKey) ?>]"
                                    rows="3"
                                    placeholder="<?= esc($placeholder) ?>"
                                    class="<?= esc($inputClass) ?>"
                                    <?= $isRequired ? 'required' : '' ?>><?= esc((string) $oldVal) ?></textarea>
                                <?php $fieldHint = $orderFieldHint($fieldType, $isRequired); ?>
                                <?php if ($fieldHint !== ''): ?>
                                    <p class="form-hint"><?= esc($fieldHint) ?></p>
                                <?php endif; ?>
                            <?php elseif ($fieldType === 'file'): ?>
                                <input
                                    type="file"
                                    name="eav_file[<?= esc($fieldKey) ?>]"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="form-input block w-full file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[11px] file:font-bold file:bg-[#051747] file:text-white hover:file:bg-[#2E5CE6]"
                                    <?= $isRequired ? 'required' : '' ?>>
                                <?php $fieldHint = $orderFieldHint($fieldType, $isRequired); ?>
                                <?php if ($fieldHint !== ''): ?>
                                    <p class="form-hint"><?= esc($fieldHint) ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <input
                                    type="<?= esc(in_array($fieldType, ['text', 'date', 'time'], true) ? $fieldType : 'text') ?>"
                                    name="eav[<?= esc($fieldKey) ?>]"
                                    value="<?= esc((string) $oldVal) ?>"
                                    placeholder="<?= esc($isHariOtomatis ? '' : $placeholder) ?>"
                                    class="<?= esc($inputClass) ?>"
                                    <?= $isRequired ? 'required' : '' ?>
                                    <?= $isHariOtomatis ? 'readonly aria-readonly="true"' : '' ?>>
                                <?php
                                $fieldHint = $isHariOtomatis
                                    ? 'Terisi otomatis sesuai tanggal yang dipilih.'
                                    : $orderFieldHint($fieldType, $isRequired);
                                ?>
                                <?php if ($fieldHint !== ''): ?>
                                    <p class="form-hint"><?= esc($fieldHint) ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            </div>
        <?php endif; ?>

        <p class="form-section-label mb-3 mt-6">
            <?= esc((string) $stepDetail) ?>. Tambahkan catatan atau preferensi yang perlu diperhatikan
            <span class="normal-case font-normal text-slate-400 ml-1">(Opsional)</span>
        </p>

        <textarea
            name="detail_pesanan"
            id="detailPesanan"
            rows="4"
            class="form-input w-full px-3.5 py-2.5"
            placeholder="Contoh: Dominan warna biru, tambahkan logo perusahaan, atau instruksi lain terkait pesanan."><?= esc((string) old('detail_pesanan')) ?></textarea>

        <div class="mt-6">
            <p class="form-section-label mb-3">
                <?= esc((string) $stepReferensi) ?>. Referensi Desain
                <span id="referensiRequiredMark" class="<?= $isCustomSelected ? '' : 'hidden ' ?>text-red-500">*</span>
                <span id="referensiOptionalBadge" class="<?= $isCustomSelected ? 'hidden ' : '' ?>normal-case font-normal text-slate-400 ml-1">(Opsional)</span>
            </p>

            <div
                id="uploadZone"
                class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-[#2E5CE6] transition-colors cursor-pointer">
                <div id="uploadPlaceholder">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <p class="form-upload-text">Klik untuk upload referensi desain</p>
                    <p id="referensiUploadHint" class="form-upload-hint">JPG, PNG, atau PDF · maks. 2MB</p>
                </div>
                <div id="uploadPreview" class="hidden">
                    <p id="uploadFileName" class="form-upload-text"></p>
                </div>
                <input
                    type="file"
                    id="inputReferensi"
                    name="referensi_desain"
                    class="hidden"
                    accept=".jpg,.jpeg,.png,.pdf"
                    <?= $isCustomSelected ? 'required' : '' ?>>
            </div>
            <p id="errReferensi" class="hidden form-hint-error mt-2">Referensi desain wajib diunggah untuk pesanan custom.</p>
        </div>

        <div class="flex justify-end mt-8">
            <button
                type="button"
                id="btnNextStep"
                class="bg-[#051747] text-white px-8 py-3 rounded-full font-bold uppercase text-sm hover:bg-[#2E5CE6] transition-colors">
                Lanjut ke Pengiriman →
            </button>
        </div>
    </div>

    <div id="step2" <?= $initialFormStep === 2 ? '' : ' class="hidden"' ?>>
        <p class="form-section-label mb-3"><?= esc((string) $stepDeadline) ?>. Deadline Pengerjaan <span class="text-red-500">*</span></p>

        <div>
            <input
                type="date"
                name="deadline_diajukan"
                id="inputDeadline"
                required
                value="<?= esc($deadlineOld) ?>"
                class="form-input w-full max-w-xs px-3.5 py-2.5">
            <p class="form-hint">Pilih tanggal selesai pengerjaan sesuai estimasi produk.</p>
            <p id="deadlineHelper" class="form-hint"></p>
            <div class="notice-danger mt-2 w-full flex items-start gap-2 rounded-xl px-3.5 py-3 text-xs">
                <span class="shrink-0 mt-0.5" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <p class="min-w-0 flex-1 leading-relaxed">
                    Estimasi dihitung sejak desain disetujui. Waktu pengiriman tidak termasuk dalam deadline produksi.
                </p>
            </div>
        </div>

        <p class="form-section-label mb-3 mt-6"><?= esc((string) $stepPengiriman) ?>. Metode Pengiriman <span class="text-red-500">*</span></p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="cursor-pointer block">
                <input type="radio" name="metode_pengiriman" value="kurir" class="sr-only peer" <?= $metodePengirimanOld === 'kurir' ? 'checked' : '' ?>>
                <div class="border-2 border-slate-200 rounded-2xl p-4 transition-all peer-checked:border-[#051747] peer-checked:bg-blue-50">
                    <div class="flex items-center gap-3">
                        <span class="shrink-0 text-[#051747]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-1.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5.67-1.5 1.5-1.5 1.5.67 1.5 1.5z" />
                            </svg>
                        </span>
                        <div>
                            <p class="font-bold text-[#051747] text-sm">Dikirim via Kurir</p>
                            <p class="text-xs text-slate-500">Isi alamat pengiriman</p>
                        </div>
                    </div>
                </div>
            </label>

            <label class="cursor-pointer block">
                <input type="radio" name="metode_pengiriman" value="ambil_sendiri" class="sr-only peer" <?= $metodePengirimanOld === 'ambil_sendiri' ? 'checked' : '' ?>>
                <div class="border-2 border-slate-200 rounded-2xl p-4 transition-all peer-checked:border-[#051747] peer-checked:bg-blue-50">
                    <div class="flex items-center gap-3">
                        <span class="shrink-0 text-[#051747]" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2 2 7v2h20V7L12 2zm0 2.84L17.16 9H6.84L12 4.84zM4 11v9h5v-6h6v6h5v-9H4z" />
                            </svg>
                        </span>
                        <div>
                            <p class="font-bold text-[#051747] text-sm">Ambil Sendiri</p>
                            <p class="text-xs text-slate-500">Z'Plack, Cimahi, Bandung Barat</p>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <div id="alamatField" class="mt-4">
            <label class="form-label">
                Alamat Pengiriman Lengkap <span class="text-red-500">*</span>
            </label>
            <?php if ($hasAlamatProfil): ?>
                <p id="alamatProfilHint" class="form-hint mb-2">
                    Diisi otomatis dari profil Anda. Anda bisa mengubahnya khusus untuk pesanan ini.
                </p>
            <?php else: ?>
                <p id="alamatProfilHint" class="form-hint mb-2 text-amber-700">
                    Alamat profil belum diisi. Lengkapi di sini atau perbarui profil agar terisi otomatis di pesanan berikutnya.
                </p>
            <?php endif; ?>
            <textarea
                name="alamat_kirim"
                id="alamatKirim"
                rows="3"
                required
                minlength="<?= (int) $alamatMinChars ?>"
                class="form-input w-full px-3.5 py-2.5"
                placeholder="Jl. Nama Jalan No. X, RT/RW, Kelurahan, Kecamatan, Kota"><?= esc($alamatKirimPrefill) ?></textarea>
            <p class="form-hint">
                Minimal <?= (int) $alamatMinChars ?> karakter (nama jalan, RT/RW, kelurahan, kecamatan, kota).
            </p>
            <div class="notice-danger mt-3 flex items-start gap-2 rounded-xl px-3.5 py-3 text-xs">
                <span class="shrink-0 mt-0.5" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <p class="min-w-0 flex-1 leading-relaxed">
                    <strong class="font-semibold">Skema COD:</strong>
                    ongkos kirim dibayarkan langsung kepada kurir saat pesanan diterima.
                    Biaya pengiriman tidak termasuk dalam total pesanan di sistem.
                </p>
            </div>
        </div>

        <div class="bg-[#051747] text-white rounded-2xl p-5 mt-6">
            <h3 class="font-bold mb-4">Ringkasan Pesanan</h3>

            <div class="flex justify-between py-2 border-b border-white/10 text-sm">
                <span class="text-white/70">Produk</span>
                <span class="text-right font-medium"><?= esc($namaProduk) ?></span>
            </div>

            <div class="flex justify-between py-2 border-b border-white/10 text-sm">
                <span class="text-white/70">Harga Satuan</span>
                <span id="summarySatuan" class="text-sm text-right">
                    Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?> / <?= esc($satuan) ?>
                </span>
            </div>

            <div class="flex justify-between py-2 border-b border-white/10 text-sm">
                <span class="text-white/70">Jumlah</span>
                <span id="summaryJumlah"><?= esc((string) $minOrder) ?> <?= esc($satuan) ?></span>
            </div>

            <div class="flex justify-between py-2 border-b border-white/10 text-sm">
                <span class="text-white/70">Kuota Revisi</span>
                <span><?= esc((string) $kuotaRevisi) ?>x (termasuk)</span>
            </div>

            <div class="flex justify-between items-center py-3 mt-2">
                <span class="font-bold">TOTAL ESTIMASI</span>
                <span id="summaryTotal" class="font-bold text-xl">
                    Rp <?= esc(number_format($minOrder * $hargaDasar, 0, ',', '.')) ?>
                </span>
            </div>
            <p class="text-xs text-white/50 mt-1">Harga sudah termasuk semua komponen spesifikasi.</p>

            <div id="summaryDP" class="flex justify-between text-amber-300 text-sm">
                <span>DP 50% yang harus dibayar</span>
                <span id="summaryDPAmount">
                    Rp <?= esc(number_format(($minOrder * $hargaDasar) * 0.5, 0, ',', '.')) ?>
                </span>
            </div>

            <p id="summaryCustomNote" class="hidden text-amber-300 text-xs mt-2">
                * Harga dikonfirmasi Admin untuk pesanan custom
            </p>

            <div id="summaryPerusahaanInfo" class="hidden mt-2 p-2 bg-blue-900/30 rounded-lg text-xs text-blue-200">
                <span id="summaryPerusahaanText">ℹ️ Skema perusahaan aktif.</span>
            </div>

            <div class="flex gap-3 mt-4">
                <button
                    type="button"
                    id="btnPrevStep"
                    class="flex-1 border border-white/30 text-white px-4 py-2.5 rounded-full text-sm hover:bg-white/10 transition-colors">
                    ← Kembali
                </button>
                <button
                    type="submit"
                    class="flex-1 bg-white text-[#051747] px-4 py-2.5 rounded-full font-bold text-sm hover:bg-blue-50 transition-colors">
                    Simpan Pesanan →
                </button>
            </div>
        </div>
    </div>
</form>

<?php if ($gambarKatalog !== ''): ?>
    <div
        id="modalZoomProduk"
        class="hidden fixed inset-0 z-50 bg-black/75 p-4 sm:p-6 flex items-center justify-center"
        aria-hidden="true">
        <button
            type="button"
            id="btnCloseZoomProduk"
            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/15 text-white text-xl font-bold hover:bg-white/25 transition-colors"
            aria-label="Tutup preview foto">
            ×
        </button>
        <img
            src="<?= esc(base_url('uploads/katalog/' . $gambarKatalog)) ?>"
            alt="<?= esc($namaProduk) ?>"
            class="max-w-full max-h-[88vh] rounded-2xl object-contain shadow-2xl">
    </div>
<?php endif; ?>

<div id="modalDeadlineMin" class="deadline-min-modal fixed inset-0 z-[90] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="modalDeadlineMinTitle" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-deadline-min></div>
    <div class="deadline-min-panel relative w-full max-w-md bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg p-6 md:p-8 z-10">
        <button type="button" data-close-deadline-min class="absolute top-4 right-4 w-8 h-8 rounded-full border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-colors" aria-label="Tutup">
            ×
        </button>
        <div class="text-center pt-1">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 border border-amber-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h4 id="modalDeadlineMinTitle" class="text-xl md:text-2xl font-extrabold text-[#051747] leading-tight">Deadline Terlalu Cepat</h4>
            <p id="modalDeadlineMinMessage" class="mt-2 text-sm text-slate-500 leading-relaxed">
                Deadline tidak bisa sebelum estimasi selesai pengerjaan.
            </p>
            <p id="modalDeadlineMinDetail" class="hidden mt-3 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2.5 leading-relaxed"></p>
        </div>
        <button type="button" data-close-deadline-min class="mt-6 w-full h-11 rounded-full bg-[#051747] text-white text-sm font-bold hover:bg-[#2E5CE6] transition-colors">
            Mengerti
        </button>
    </div>
</div>

<style>
    .deadline-min-modal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    .deadline-min-modal.is-open {
        opacity: 1;
    }

    .deadline-min-panel {
        transform: scale(.95);
        opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
    }

    .deadline-min-modal.is-open .deadline-min-panel {
        transform: scale(1);
        opacity: 1;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const minOrder = <?= (int) $minOrder ?>;
    const hargaDasar = <?= (float) $hargaDasar ?>;
    const satuan = <?= json_encode($satuan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const isKerjasama = <?= $isKerjasama ? 'true' : 'false' ?>;
    const batasTanpaDp = <?= (int) $batasTanpaDp ?>;
    const defaultAlamatProfil = <?= json_encode($alamatProfil, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const detailMinChars = <?= (int) $detailMinChars ?>;
    const alamatMinChars = <?= (int) $alamatMinChars ?>;
    const catatanCustomMinChars = 10;
    const deadlineOld = <?= json_encode($deadlineOld !== '' ? $deadlineOld : null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const idKatalog = <?= (int) $idKatalog ?>;
    const DRAFT_KEY = 'simenak_order_draft_' + idKatalog;
    const hasServerOld = <?= $hasOldInput ? 'true' : 'false' ?>;
    const clearOrderDraft = <?= $clearOrderDraft ? 'true' : 'false' ?>;
    let saveDraftTimer = null;

    if (clearOrderDraft) {
        sessionStorage.removeItem(DRAFT_KEY);
    }

    function scheduleSaveOrderDraft() {
        clearTimeout(saveDraftTimer);
        saveDraftTimer = setTimeout(saveOrderDraft, 250);
    }

    function saveOrderDraft() {
        const form = document.getElementById('formPesan');
        if (!form) {
            return;
        }

        const fields = {};
        form.querySelectorAll('input, textarea, select').forEach((el) => {
            if (!el.name || el.name === 'csrf_test_name' || el.type === 'file') {
                return;
            }

            if (el.type === 'radio') {
                if (el.checked) {
                    fields[el.name] = el.value;
                }
                return;
            }

            if (el.type === 'checkbox') {
                fields[el.name] = el.checked;
                return;
            }

            fields[el.name] = el.value;
        });

        const referensiName = document.getElementById('uploadFileName')?.textContent?.trim() || '';
        if (referensiName !== '') {
            fields.__referensi_name = referensiName;
        }

        sessionStorage.setItem(DRAFT_KEY, JSON.stringify({
            step: document.getElementById('formStep')?.value || '1',
            fields: fields,
        }));
    }

    function restoreReferensiPreview(name) {
        if (!name) {
            return;
        }

        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const uploadPreview = document.getElementById('uploadPreview');
        const uploadFileName = document.getElementById('uploadFileName');

        uploadPlaceholder?.classList.add('hidden');
        uploadPreview?.classList.remove('hidden');
        if (uploadFileName) {
            uploadFileName.textContent = name.startsWith('📎') ? name : '📎 ' + name;
        }
    }

    function restoreOrderDraft() {
        if (hasServerOld) {
            return false;
        }

        const raw = sessionStorage.getItem(DRAFT_KEY);
        if (!raw) {
            return false;
        }

        try {
            const parsed = JSON.parse(raw);
            const form = document.getElementById('formPesan');
            if (!form || !parsed.fields) {
                return false;
            }

            Object.entries(parsed.fields).forEach(([name, value]) => {
                if (name === '__referensi_name') {
                    restoreReferensiPreview(String(value));
                    return;
                }

                const nodes = form.querySelectorAll('[name="' + CSS.escape(name) + '"]');
                if (nodes.length === 0) {
                    return;
                }

                const first = nodes[0];
                if (first.type === 'radio') {
                    nodes.forEach((node) => {
                        node.checked = node.value === String(value);
                    });
                    return;
                }

                if (first.type === 'checkbox') {
                    first.checked = !!value;
                    return;
                }

                first.value = value ?? '';
            });

            return true;
        } catch (error) {
            return false;
        }
    }

    function goToStep(step, updateHash = true) {
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const formStep = document.getElementById('formStep');

        if (!step1 || !step2) {
            return;
        }

        if (step === 2) {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            if (formStep) {
                formStep.value = '2';
            }
            updateStepIndicator(2);
            updateSummary();
            applyMetodePengiriman();
            if (updateHash && window.location.hash !== '#pengiriman') {
                history.replaceState(null, '', window.location.pathname + window.location.search + '#pengiriman');
            }
            window.scrollTo(0, 0);
            saveOrderDraft();
            return;
        }

        step2.classList.add('hidden');
        step1.classList.remove('hidden');
        if (formStep) {
            formStep.value = '1';
        }
        updateStepIndicator(1);
        if (updateHash && window.location.hash === '#pengiriman') {
            history.replaceState(null, '', window.location.pathname + window.location.search);
        }
        window.scrollTo(0, 0);
        saveOrderDraft();
    }

    function setContainerFieldsDisabled(containerId, disabled) {
        const container = document.getElementById(containerId);
        if (!container) {
            return;
        }

        container.querySelectorAll('input, textarea, select').forEach((el) => {
            el.disabled = disabled;
        });
    }

    function isStep1FieldActive(field, step1) {
        if (field.disabled) {
            return false;
        }

        let el = field;
        while (el && el !== step1) {
            if (el.classList?.contains('hidden')) {
                return false;
            }
            el = el.parentElement;
        }

        return true;
    }

    document.getElementById('btnNextStep')?.addEventListener('click', function() {
        const jumlah = parseInt(document.getElementById('jumlahOrder')?.value, 10) || 0;
        const isCustom = document.querySelector('[name="is_custom"]:checked')?.value === '1';
        const catatanCustom = (document.getElementById('catatanCustom')?.value || '').trim();
        const step1 = document.getElementById('step1');
        const errReferensi = document.getElementById('errReferensi');

        errReferensi?.classList.add('hidden');

        if (jumlah < minOrder) {
            document.getElementById('errJumlah')?.classList.remove('hidden');
            document.getElementById('jumlahOrder')?.focus();
            return;
        }
        if (isCustom && catatanCustom.length < catatanCustomMinChars) {
            const catatanEl = document.getElementById('catatanCustom');
            if (catatanEl) {
                catatanEl.setCustomValidity('Detail spesifikasi custom minimal ' + catatanCustomMinChars + ' karakter.');
                catatanEl.reportValidity();
                catatanEl.setCustomValidity('');
                catatanEl.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
            return;
        }
        if (isCustom) {
            const referensiInput = document.getElementById('inputReferensi');
            if (!referensiInput?.files?.length) {
                errReferensi?.classList.remove('hidden');
                document.getElementById('uploadZone')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return;
            }
        }

        if (step1) {
            const step1Fields = step1.querySelectorAll('input, textarea, select');
            for (const field of step1Fields) {
                if (field.type === 'radio' || field.type === 'hidden') {
                    continue;
                }
                if (field.id === 'inputReferensi') {
                    continue;
                }
                if (!isStep1FieldActive(field, step1)) {
                    continue;
                }
                if (field.type === 'file') {
                    if (field.required && !field.files?.length) {
                        field.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        return;
                    }
                    continue;
                }
                if (!field.checkValidity()) {
                    field.reportValidity();
                    field.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return;
                }
            }
        }

        goToStep(2);
    });

    document.getElementById('btnPrevStep')?.addEventListener('click', function() {
        saveOrderDraft();
        goToStep(1);
    });

    document.getElementById('formPesan')?.addEventListener('submit', function(e) {
        const formStep = document.getElementById('formStep');
        const step2 = document.getElementById('step2');
        const onStep2 = formStep?.value === '2' || (step2 && !step2.classList.contains('hidden'));

        if (formStep && onStep2) {
            formStep.value = '2';
        }

        const step1 = document.getElementById('step1');
        if (!step1) {
            return;
        }

        const isCustomSubmit = document.querySelector('[name="is_custom"]:checked')?.value === '1';
        const catatanSubmit = (document.getElementById('catatanCustom')?.value || '').trim();
        const errReferensiSubmit = document.getElementById('errReferensi');
        errReferensiSubmit?.classList.add('hidden');

        if (isCustomSubmit && catatanSubmit.length < catatanCustomMinChars) {
            e.preventDefault();
            goToStep(1, false);
            const catatanEl = document.getElementById('catatanCustom');
            catatanEl?.setCustomValidity('Detail spesifikasi custom minimal ' + catatanCustomMinChars + ' karakter.');
            catatanEl?.reportValidity();
            catatanEl?.setCustomValidity('');
            return;
        }

        if (isCustomSubmit) {
            const referensiInput = document.getElementById('inputReferensi');
            if (!referensiInput?.files?.length) {
                e.preventDefault();
                goToStep(1, false);
                errReferensiSubmit?.classList.remove('hidden');
                document.getElementById('uploadZone')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return;
            }
        }

        const step1Fields = step1.querySelectorAll('input, textarea, select');
        for (const field of step1Fields) {
            if (field.type === 'radio' || field.type === 'hidden') {
                continue;
            }
            if (field.id === 'inputReferensi') {
                continue;
            }
            if (!isStep1FieldActive(field, step1)) {
                continue;
            }
            if (field.type === 'file') {
                if (field.required && !field.files?.length) {
                    e.preventDefault();
                    goToStep(1, false);
                    field.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return;
                }
                continue;
            }
            if (!field.checkValidity()) {
                e.preventDefault();
                goToStep(1, false);
                field.reportValidity();
                field.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return;
            }
        }

        if (onStep2 && step2) {
            const step2Fields = step2.querySelectorAll('input, textarea, select');
            for (const field of step2Fields) {
                if (field.type === 'radio' || field.type === 'hidden') {
                    continue;
                }
                if (field.offsetParent === null) {
                    continue;
                }
                if (!field.checkValidity()) {
                    e.preventDefault();
                    goToStep(2, false);
                    field.reportValidity();
                    field.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return;
                }
            }
        }

        saveOrderDraft();
        sessionStorage.removeItem(DRAFT_KEY);
    });

    function updateStepIndicator(step) {
        const s1 = document.getElementById('stepDot1');
        const s2 = document.getElementById('stepDot2');
        const l1 = document.getElementById('stepLabel1');
        const l2 = document.getElementById('stepLabel2');

        if (!s1 || !s2) return;

        if (step === 1) {
            s1.classList.add('bg-[#051747]', 'text-white');
            s1.classList.remove('border-2', 'border-slate-300', 'text-slate-400');
            s2.classList.remove('bg-[#051747]', 'text-white');
            s2.classList.add('border-2', 'border-slate-300', 'text-slate-400');
            l1?.classList.add('font-bold', 'text-[#051747]');
            l1?.classList.remove('text-slate-400');
            l2?.classList.remove('font-bold', 'text-[#051747]');
            l2?.classList.add('text-slate-400');
        } else {
            s2.classList.add('bg-[#051747]', 'text-white');
            s2.classList.remove('border-2', 'border-slate-300', 'text-slate-400');
            l2?.classList.add('font-bold', 'text-[#051747]');
            l2?.classList.remove('text-slate-400');
        }
    }

    function toggleCustom(isCustom) {
        document.getElementById('customNote')?.classList.toggle('hidden', !isCustom);
        document.getElementById('catatanCustomWrapper')?.classList.toggle('hidden', !isCustom);
        document.getElementById('spesifikasiKhususWrapper')?.classList.toggle('hidden', isCustom);
        const catatanEl = document.getElementById('catatanCustom');
        if (catatanEl) {
            catatanEl.required = isCustom;
            catatanEl.disabled = !isCustom;
        }
        setContainerFieldsDisabled('spesifikasiKhususWrapper', isCustom);
        const referensiInput = document.getElementById('inputReferensi');
        if (referensiInput) {
            referensiInput.required = isCustom;
        }
        document.getElementById('errReferensi')?.classList.add('hidden');
        document.getElementById('referensiRequiredMark')?.classList.toggle('hidden', !isCustom);
        document.getElementById('referensiOptionalBadge')?.classList.toggle('hidden', isCustom);
        const referensiHint = document.getElementById('referensiUploadHint');
        if (referensiHint) {
            referensiHint.textContent = 'JPG, PNG, atau PDF · maks. 2MB';
        }
        updateSummary();
    }

    function applyMetodePengiriman() {
        const isKurir = document.querySelector('[name="metode_pengiriman"]:checked')?.value === 'kurir';
        document.getElementById('alamatField')?.classList.toggle('hidden', !isKurir);
        const alamat = document.getElementById('alamatKirim');
        if (alamat) {
            alamat.required = isKurir;
            alamat.minLength = isKurir ? alamatMinChars : 0;
            if (isKurir && alamat.value.trim() === '' && defaultAlamatProfil !== '') {
                alamat.value = defaultAlamatProfil;
            }
        }
    }

    function namaHariIndonesia(dateStr) {
        if (!dateStr) {
            return '';
        }

        const parts = dateStr.split('-').map((part) => parseInt(part, 10));
        if (parts.length !== 3 || parts.some(Number.isNaN)) {
            return '';
        }

        const date = new Date(parts[0], parts[1] - 1, parts[2]);
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        return date.toLocaleDateString('id-ID', { weekday: 'long' });
    }

    function bindHariOtomatisDariTanggal(tanggalFieldName, hariFieldName) {
        const form = document.getElementById('formPesan');
        if (!form) {
            return;
        }

        const tanggalInput = form.querySelector('[name="' + tanggalFieldName + '"]');
        const hariInput = form.querySelector('[name="' + hariFieldName + '"]');
        if (!tanggalInput || !hariInput) {
            return;
        }

        const syncHari = () => {
            hariInput.value = namaHariIndonesia(tanggalInput.value);
            scheduleSaveOrderDraft();
        };

        hariInput.readOnly = true;
        hariInput.setAttribute('aria-readonly', 'true');
        hariInput.classList.add('bg-slate-100', 'text-slate-600', 'cursor-not-allowed');

        tanggalInput.addEventListener('change', syncHari);
        tanggalInput.addEventListener('input', syncHari);

        if (tanggalInput.value) {
            syncHari();
        }
    }

    function initHariOtomatisFormUndangan() {
        bindHariOtomatisDariTanggal('eav[akad_tanggal]', 'eav[akad_hari]');
        bindHariOtomatisDariTanggal('eav[resepsi_tanggal]', 'eav[resepsi_hari]');
    }

    restoreOrderDraft();
    initHariOtomatisFormUndangan();

    let activeStep = parseInt(document.getElementById('formStep')?.value, 10) || 1;
    if (!hasServerOld) {
        try {
            const raw = sessionStorage.getItem(DRAFT_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (String(parsed.step) === '2') {
                    activeStep = 2;
                }
            }
        } catch (error) {
            // abaikan draft rusak
        }
    }
    if (window.location.hash === '#pengiriman') {
        activeStep = 2;
    }
    if (activeStep === 2) {
        goToStep(2, false);
    } else {
        updateStepIndicator(1);
    }
    toggleCustom(document.querySelector('[name="is_custom"]:checked')?.value === '1');

    applyMetodePengiriman();
    updateSummary();
    document.querySelectorAll('[name="metode_pengiriman"]').forEach((r) => {
        r.addEventListener('change', applyMetodePengiriman);
    });

    document.getElementById('formPesan')?.addEventListener('input', scheduleSaveOrderDraft);
    document.getElementById('formPesan')?.addEventListener('change', scheduleSaveOrderDraft);

    function updateSummary() {
        const jumlah = parseInt(document.getElementById('jumlahOrder')?.value, 10) || 0;
        const isCustom = document.querySelector('[name="is_custom"]:checked')?.value === '1';
        const jenisPelanggan = document.querySelector('[name="jenis_pelanggan"]')?.value || 'perseorangan';
        const isPerseorangan = jenisPelanggan === 'perseorangan';
        const isPerusahaan = jenisPelanggan === 'perusahaan';

        const hargaSatuan = isCustom ? 0 : hargaDasar;
        const total = Math.round(hargaSatuan * jumlah);
        const dp = Math.round(total * 0.5);

        const summaryJumlah = document.getElementById('summaryJumlah');
        const summarySatuan = document.getElementById('summarySatuan');
        const summaryTotal = document.getElementById('summaryTotal');
        const summaryDP = document.getElementById('summaryDP');
        const summaryDPAmount = document.getElementById('summaryDPAmount');
        const summaryCustomNote = document.getElementById('summaryCustomNote');
        const summaryPerusahaanInfo = document.getElementById('summaryPerusahaanInfo');
        const summaryPerusahaanText = document.getElementById('summaryPerusahaanText');

        if (summaryJumlah) {
            summaryJumlah.textContent = jumlah + ' ' + satuan;
        }
        if (summarySatuan) {
            summarySatuan.textContent = isCustom ?
                'Dikonfirmasi Admin' :
                'Rp ' + hargaSatuan.toLocaleString('id-ID') + ' / ' + satuan;
        }
        if (summaryTotal) {
            summaryTotal.textContent = isCustom ?
                'Dikonfirmasi Admin' :
                'Rp ' + total.toLocaleString('id-ID');
        }

        let showDp = false;
        if (!isCustom && total > 0) {
            if (isPerseorangan) {
                showDp = true;
            } else if (isPerusahaan && isKerjasama && total > batasTanpaDp) {
                showDp = true;
            }
        }

        const dpWrap = document.getElementById('summaryDP');
        if (showDp) {
            dpWrap?.classList.remove('hidden');
            if (summaryDPAmount) {
                summaryDPAmount.textContent = 'Rp ' + dp.toLocaleString('id-ID');
            }
        } else {
            dpWrap?.classList.add('hidden');
        }

        summaryCustomNote?.classList.toggle('hidden', !isCustom);

        if (summaryPerusahaanInfo) {
            const showInfo = isPerusahaan && isKerjasama && !isCustom;
            summaryPerusahaanInfo.classList.toggle('hidden', !showInfo);
            if (showInfo && summaryPerusahaanText) {
                if (total > batasTanpaDp) {
                    summaryPerusahaanText.textContent = 'ℹ️ Order > Rp 5 jt: wajib DP 50%. Sisa pelunasan setelah barang diterima.';
                } else {
                    summaryPerusahaanText.textContent = 'ℹ️ Tanpa DP. Pelunasan setelah pesanan diterima (nota tagihan).';
                }
            }
        }
    }

    function toYMD(d) {
        return d.toISOString().split('T')[0];
    }

    document.addEventListener('DOMContentLoaded', function() {
        const minHari = <?= (int) $estimasiMinHariKerja ?>;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const minDate = new Date(today);
        minDate.setDate(minDate.getDate() + minHari);

        const inputDeadline = document.getElementById('inputDeadline');
        if (!inputDeadline) return;

        inputDeadline.min = toYMD(minDate);
        if (!inputDeadline.value) {
            inputDeadline.value = deadlineOld || toYMD(minDate);
        }

        const opsi = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        };
        const tglTampil = minDate.toLocaleDateString('id-ID', opsi);
        const deadlineHelper = document.getElementById('deadlineHelper');
        if (deadlineHelper) {
            deadlineHelper.textContent =
                'Tanggal tercepat: ' + tglTampil +
                ' (' + minHari + ' hari kerja).';
        }

        inputDeadline.addEventListener('change', function() {
            const pilihan = new Date(this.value + 'T00:00:00');
            if (pilihan < minDate) {
                this.value = toYMD(minDate);
                openModalDeadlineMin(deadlineHelper?.textContent?.trim() || '');
            }
        });
    });

    (function initModalDeadlineMin() {
        const modal = document.getElementById('modalDeadlineMin');
        const detailEl = document.getElementById('modalDeadlineMinDetail');
        if (!modal) {
            return;
        }

        let closeTimer = null;

        const closeModal = () => {
            modal.classList.remove('is-open');
            closeTimer = setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
                closeTimer = null;
            }, 250);
        };

        window.openModalDeadlineMin = (detailText) => {
            if (detailEl) {
                if (detailText) {
                    detailEl.textContent = detailText;
                    detailEl.classList.remove('hidden');
                } else {
                    detailEl.textContent = '';
                    detailEl.classList.add('hidden');
                }
            }

            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            requestAnimationFrame(() => modal.classList.add('is-open'));
        };

        modal.querySelectorAll('[data-close-deadline-min]').forEach((el) => {
            el.addEventListener('click', closeModal);
        });

        modal.addEventListener('click', (event) => {
            if (event.target === modal.querySelector('.absolute.inset-0')) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('flex') && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();

    document.getElementById('jumlahOrder')?.addEventListener('input', function() {
        const val = parseInt(this.value, 10) || 0;
        document.getElementById('errJumlah')?.classList.toggle('hidden', val >= minOrder);
        updateSummary();
    });

    document.querySelectorAll('[name="is_custom"], [name="jenis_pelanggan"]').forEach((r) => {
        r.addEventListener('change', updateSummary);
    });

    document.getElementById('inputReferensi')?.addEventListener('change', function() {
        if (this.files[0]) {
            document.getElementById('errReferensi')?.classList.add('hidden');
            document.getElementById('uploadPlaceholder')?.classList.add('hidden');
            document.getElementById('uploadPreview')?.classList.remove('hidden');
            const fileNameEl = document.getElementById('uploadFileName');
            if (fileNameEl) {
                fileNameEl.textContent = '📎 ' + this.files[0].name;
            }
            scheduleSaveOrderDraft();
        }
    });

    document.getElementById('uploadZone')?.addEventListener('click', function() {
        document.getElementById('inputReferensi')?.click();
    });

    const btnZoomProduk = document.getElementById('btnZoomProduk');
    const modalZoomProduk = document.getElementById('modalZoomProduk');
    const btnCloseZoomProduk = document.getElementById('btnCloseZoomProduk');

    const closeZoomProduk = () => {
        modalZoomProduk?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    btnZoomProduk?.addEventListener('click', () => {
        modalZoomProduk?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    });

    btnCloseZoomProduk?.addEventListener('click', closeZoomProduk);

    modalZoomProduk?.addEventListener('click', (event) => {
        if (event.target === modalZoomProduk) {
            closeZoomProduk();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modalZoomProduk && !modalZoomProduk.classList.contains('hidden')) {
            closeZoomProduk();
        }
    });

    updateSummary();
</script>
<?= $this->endSection() ?>