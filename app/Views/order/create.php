<?php

/**
 * @var array<string, mixed>              $katalog
 * @var list<array<string, mixed>>       $fields
 * @var array<string, mixed>|null       $pelanggan
 * @var string                           $title
 * @var string                           $page_title
 */
$isVerified      = (int) ($pelanggan['is_verified'] ?? 0);
$companyDisabled = $isVerified === 0;
$idKatalog       = (int) ($katalog['id_katalog'] ?? 0);
$minOrder        = (int) ($katalog['min_order'] ?? 1);
$hargaDasar      = (float) ($katalog['harga_dasar'] ?? 0);
$kuotaRevisi     = (int) ($katalog['kuota_revisi_default'] ?? 0);
$satuan          = (string) ($katalog['satuan'] ?? 'pcs');
$namaProduk      = (string) ($katalog['nama_produk'] ?? '-');
$kategoriKey     = (string) ($katalog['kategori'] ?? '');
$estimasiHari    = (string) ($katalog['estimasi_hari'] ?? '-');
$deskripsiKatalog = trim((string) ($katalog['deskripsi'] ?? ''));
$gambarKatalog   = trim((string) ($katalog['gambar'] ?? ''));
$deadlineMin     = date('Y-m-d', strtotime('+3 days'));

$kategoriLabel = match ($kategoriKey) {
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
    default         => str_replace('_', ' ', $kategoriKey),
};
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
            <span>💰 Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?>/<?= esc($satuan) ?></span>
            <span>📦 Min. <?= esc((string) $minOrder) ?> <?= esc($satuan) ?></span>
            <span>⏱ <?= esc($estimasiHari) ?></span>
            <span>🎨 <?= esc((string) $kuotaRevisi) ?>x revisi</span>
        </div>
    </div>
</div>

<form
    method="post"
    action="<?= esc(site_url('pesanan/buat/' . $idKatalog)) ?>"
    enctype="multipart/form-data"
    id="formPesan">
    <?= csrf_field() ?>
    <input type="hidden" name="id_katalog" value="<?= esc((string) $idKatalog) ?>">
    <p class="text-xs text-slate-400 mb-4">
        <span class="text-red-500 font-bold">*</span> yang diberi bintang merah wajib diisi.
    </p>

    <div id="step1">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">1. Jenis Pemesanan <span class="text-red-500">*</span></p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="cursor-pointer block">
                <input
                    type="radio"
                    name="jenis_pelanggan"
                    value="perseorangan"
                    class="sr-only peer"
                    checked
                    id="radioPerseorangan">
                <div class="border-2 border-slate-200 rounded-2xl p-4 transition-all peer-checked:border-[#051747] peer-checked:bg-blue-50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-xl shrink-0">👤</div>
                        <div>
                            <p class="font-bold text-[#051747] text-sm">Perseorangan</p>
                            <p class="text-xs text-slate-500 mt-0.5">Bayar DP 50% di awal pesanan</p>
                        </div>
                    </div>
                </div>
            </label>

            <label class="<?= $companyDisabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' ?> block">
                <input
                    type="radio"
                    name="jenis_pelanggan"
                    value="perusahaan"
                    class="sr-only peer"
                    id="radioPerusahaan"
                    <?= $companyDisabled ? 'disabled' : '' ?>>
                <div class="border-2 border-slate-200 rounded-2xl p-4 transition-all peer-checked:border-[#051747] peer-checked:bg-blue-50">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-xl shrink-0">🏢</div>
                        <div>
                            <p class="font-bold text-[#051747] text-sm">Kerja Sama Perusahaan</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <?= $isVerified
                                    ? 'Invoice setelah barang diterima, tanpa DP'
                                    : '⚠️ Belum terverifikasi — ajukan di Profil' ?>
                            </p>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <div id="warningPerusahaan" class="hidden mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
            ⚠️ Akun belum terverifikasi sebagai perusahaan. Pesanan akan diproses dengan skema perseorangan (DP 50%).
            <a href="<?= site_url('profil') ?>" class="underline font-semibold ml-1">Ajukan verifikasi →</a>
        </div>

        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3 mt-6">2. Jenis Produk</p>

        <div class="flex flex-wrap gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    type="radio"
                    name="is_custom"
                    value="0"
                    checked
                    class="w-4 h-4 accent-[#051747]"
                    onchange="toggleCustom(false)">
                <span class="text-sm font-medium text-slate-700">Produk Standar</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input
                    type="radio"
                    name="is_custom"
                    value="1"
                    class="w-4 h-4 accent-[#051747]"
                    onchange="toggleCustom(true)">
                <span class="text-sm font-medium text-slate-700">Pesanan Custom</span>
            </label>
        </div>

        <div id="customNote" class="hidden mt-3 p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-sm text-indigo-800">
            💡 Pesanan custom memerlukan konfirmasi harga dari Admin.
            Estimasi akan tampil sebagai Rp 0 hingga Admin mengkonfirmasi.
            <br>Tambahkan catatan spesifikasi khusus di bawah ini.
        </div>
        <div id="catatanCustomWrapper" class="hidden mt-3">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan Spesifikasi Custom</label>
            <textarea
                name="catatan_custom"
                rows="3"
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                placeholder="Jelaskan spesifikasi custom yang diinginkan..."></textarea>
        </div>

        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3 mt-6">3. Jumlah Pesanan <span class="text-red-500">*</span></p>

        <div class="flex items-end gap-3">
            <input
                type="number"
                name="jumlah_order"
                id="jumlahOrder"
                value="<?= esc((string) $minOrder) ?>"
                min="<?= esc((string) $minOrder) ?>"
                class="w-40 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
            <span class="text-sm font-medium text-slate-600 pb-2.5"><?= esc($satuan) ?></span>
        </div>
        <p class="text-xs text-slate-400 mt-1.5">
            Minimum <?= esc((string) $minOrder) ?> <?= esc($satuan) ?>
        </p>
        <p id="errJumlah" class="hidden text-xs text-red-500 mt-1">
            Minimum order <?= esc((string) $minOrder) ?> <?= esc($satuan) ?>
        </p>

        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3 mt-6">4. Detail Pesanan <span class="text-red-500">*</span></p>

        <textarea
            name="detail_pesanan"
            id="detailPesanan"
            rows="4"
            required
            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
            placeholder="Jelaskan kebutuhan cetak (warna, dll)"></textarea>

        <?php if (!empty($fields)): ?>
            <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5 mt-6">
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
                        $inputClass  = 'w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10';
                        ?>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
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
                                    <?= $isRequired ? 'required' : '' ?>></textarea>
                            <?php elseif ($fieldType === 'file'): ?>
                                <input
                                    type="file"
                                    name="eav_file[<?= esc($fieldKey) ?>]"
                                    accept=".jpg,.jpeg,.png,.pdf"
                                    class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#051747] file:text-white hover:file:bg-[#2E5CE6]"
                                    <?= $isRequired ? 'required' : '' ?>>
                                <p class="text-xs text-slate-400 mt-1">JPG, PNG, PDF — Maks 2MB</p>
                            <?php else: ?>
                                <input
                                    type="<?= esc(in_array($fieldType, ['text', 'date', 'time'], true) ? $fieldType : 'text') ?>"
                                    name="eav[<?= esc($fieldKey) ?>]"
                                    placeholder="<?= esc($placeholder) ?>"
                                    class="<?= esc($inputClass) ?>"
                                    <?= $isRequired ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">
                5. Referensi Desain
                <span class="normal-case font-normal text-slate-400 ml-1">(Opsional)</span>
            </p>

            <div
                id="uploadZone"
                class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-[#2E5CE6] transition-colors cursor-pointer">
                <div id="uploadPlaceholder">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <p class="text-sm text-slate-500">Klik untuk upload referensi desain</p>
                    <p class="text-xs text-slate-300 mt-1">JPG, PNG, PDF — Maks 2MB</p>
                </div>
                <div id="uploadPreview" class="hidden">
                    <p id="uploadFileName" class="text-sm text-slate-600"></p>
                </div>
                <input
                    type="file"
                    id="inputReferensi"
                    name="referensi_desain"
                    class="hidden"
                    accept=".jpg,.jpeg,.png,.pdf">
            </div>
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

    <div id="step2" class="hidden">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">6. Deadline Pengerjaan <span class="text-red-500">*</span></p>

        <div class="flex items-start gap-4">
            <div class="flex-1">
                <input
                    type="date"
                    name="deadline"
                    id="inputDeadline"
                    required
                    class="w-full max-w-xs border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                <p id="deadlineHelper" class="text-xs text-slate-400 mt-1.5"></p>
            </div>
            <div class="flex-shrink-0 bg-red-50 border border-red-200 rounded-xl p-3 text-xs text-red-700 max-w-xs">
                ℹ️ Tanggal sebelum estimasi selesai tidak dapat dipilih.
                Estimasi pengerjaan: <?= esc($estimasiHari) ?> (Terhitung sejak desain disetujui)
            </div>
        </div>

        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3 mt-6">7. Metode Pengiriman <span class="text-red-500">*</span></p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="cursor-pointer block">
                <input type="radio" name="metode_pengiriman" value="kurir" class="sr-only peer" checked>
                <div class="border-2 border-slate-200 rounded-2xl p-4 transition-all peer-checked:border-[#051747] peer-checked:bg-blue-50">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl" aria-hidden="true">🚚</span>
                        <div>
                            <p class="font-bold text-[#051747] text-sm">Dikirim via Kurir</p>
                            <p class="text-xs text-slate-500">Isi alamat pengiriman</p>
                        </div>
                    </div>
                </div>
            </label>

            <label class="cursor-pointer block">
                <input type="radio" name="metode_pengiriman" value="ambil_sendiri" class="sr-only peer">
                <div class="border-2 border-slate-200 rounded-2xl p-4 transition-all peer-checked:border-[#051747] peer-checked:bg-blue-50">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl" aria-hidden="true">🏪</span>
                        <div>
                            <p class="font-bold text-[#051747] text-sm">Ambil Sendiri</p>
                            <p class="text-xs text-slate-500">Z'Plack, Cimahi, Bandung Barat</p>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <div id="alamatField" class="mt-4">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                Alamat Pengiriman Lengkap <span class="text-red-500">*</span>
            </label>
            <textarea
                name="alamat_kirim"
                id="alamatKirim"
                rows="3"
                required
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                placeholder="Jl. Nama Jalan No. X, RT/RW, Kelurahan, Kecamatan, Kota"></textarea>
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
                ℹ️ Skema perusahaan: tanpa DP. Invoice aktif setelah pesanan diterima.
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const minOrder = <?= (int) $minOrder ?>;
    const hargaDasar = <?= (float) $hargaDasar ?>;
    const satuan = <?= json_encode($satuan, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    document.getElementById('btnNextStep')?.addEventListener('click', function() {
        const jumlah = parseInt(document.getElementById('jumlahOrder')?.value, 10) || 0;
        const detail = (document.getElementById('detailPesanan')?.value || '').trim();

        if (jumlah < minOrder) {
            document.getElementById('errJumlah')?.classList.remove('hidden');
            document.getElementById('jumlahOrder')?.focus();
            return;
        }
        if (!detail) {
            alert('Detail pesanan wajib diisi.');
            return;
        }

        document.getElementById('step1')?.classList.add('hidden');
        document.getElementById('step2')?.classList.remove('hidden');
        updateStepIndicator(2);
        window.scrollTo(0, 0);
    });

    document.getElementById('btnPrevStep')?.addEventListener('click', function() {
        document.getElementById('step2')?.classList.add('hidden');
        document.getElementById('step1')?.classList.remove('hidden');
        updateStepIndicator(1);
        window.scrollTo(0, 0);
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
        updateSummary();
    }

    document.querySelectorAll('[name="metode_pengiriman"]').forEach((r) => {
        r.addEventListener('change', function() {
            const show = this.value === 'kurir';
            document.getElementById('alamatField')?.classList.toggle('hidden', !show);
            const alamat = document.getElementById('alamatKirim');
            if (alamat) {
                alamat.required = show;
            }
        });
    });

    function updateSummary() {
        const jumlah = parseInt(document.getElementById('jumlahOrder')?.value, 10) || 0;
        const isCustom = document.querySelector('[name="is_custom"]:checked')?.value === '1';
        const isPerseorangan = document.querySelector('[name="jenis_pelanggan"]:checked')?.value === 'perseorangan';

        const hargaSatuan = isCustom ? 0 : hargaDasar;
        const total = hargaSatuan * jumlah;
        const dp = total * 0.5;

        const summaryJumlah = document.getElementById('summaryJumlah');
        const summarySatuan = document.getElementById('summarySatuan');
        const summaryTotal = document.getElementById('summaryTotal');
        const summaryDP = document.getElementById('summaryDP');
        const summaryDPAmount = document.getElementById('summaryDPAmount');
        const summaryCustomNote = document.getElementById('summaryCustomNote');
        const summaryPerusahaanInfo = document.getElementById('summaryPerusahaanInfo');

        if (summaryJumlah) {
            summaryJumlah.textContent = jumlah + ' ' + satuan;
        }
        if (summarySatuan) {
            summarySatuan.textContent = isCustom
                ? 'Dikonfirmasi Admin'
                : 'Rp ' + hargaSatuan.toLocaleString('id-ID') + ' / ' + satuan;
        }
        if (summaryTotal) {
            summaryTotal.textContent = isCustom
                ? 'Dikonfirmasi Admin'
                : 'Rp ' + total.toLocaleString('id-ID');
        }

        const dpWrap = document.getElementById('summaryDP');
        if (isPerseorangan && !isCustom && total > 0) {
            dpWrap.classList.remove('hidden');
            if (summaryDPAmount) {
                summaryDPAmount.textContent = 'Rp ' + dp.toLocaleString('id-ID');
            }
        } else {
            dpWrap.classList.add('hidden');
        }

        summaryCustomNote?.classList.toggle('hidden', !isCustom);

        if (summaryPerusahaanInfo) {
            summaryPerusahaanInfo.classList.toggle('hidden', isPerseorangan || isCustom);
        }
    }

    function toYMD(d) {
        return d.toISOString().split('T')[0];
    }

    document.addEventListener('DOMContentLoaded', function() {
        const estimasiStr = <?= json_encode((string) ($katalog['estimasi_hari'] ?? ''), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const angka = estimasiStr.match(/\d+/g);
        const minHari = angka ? Math.max(...angka.map(Number)) : 3;

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const minDate = new Date(today);
        minDate.setDate(minDate.getDate() + minHari);

        const inputDeadline = document.getElementById('inputDeadline');
        if (!inputDeadline) return;

        inputDeadline.min = toYMD(minDate);
        inputDeadline.value = toYMD(minDate);

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
                'Paling cepat: ' + tglTampil +
                ' (estimasi pengerjaan ' + estimasiStr + ')';
        }

        inputDeadline.addEventListener('change', function() {
            const pilihan = new Date(this.value);
            if (pilihan < minDate) {
                this.value = toYMD(minDate);
                alert('Deadline tidak bisa sebelum estimasi selesai pengerjaan.');
            }
        });
    });

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
            document.getElementById('uploadPlaceholder')?.classList.add('hidden');
            document.getElementById('uploadPreview')?.classList.remove('hidden');
            const fileNameEl = document.getElementById('uploadFileName');
            if (fileNameEl) {
                fileNameEl.textContent = '📎 ' + this.files[0].name;
            }
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