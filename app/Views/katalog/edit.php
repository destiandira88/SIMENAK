<?= $this->extend('layouts/main') ?>

<?php
$readOnly = (bool) ($readOnly ?? false);
$pageTitle = $readOnly ? 'Detail Produk' : 'Ubah Produk';
?>

<?= $this->section('title') ?><?= esc($title ?? $pageTitle) ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($pageTitle) ?><?= $this->endSection() ?>

<?= $this->section('banner_title') ?><?= esc($pageTitle) ?><?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?><?= $readOnly ? 'Lihat detail produk katalog.' : 'Perbarui informasi produk katalog.' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$katalog = isset($katalog) && is_array($katalog) ? $katalog : [];
$kategoriOptions = [
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
];
$idKatalog   = (int) ($katalog['id_katalog'] ?? 0);
$isActive    = (int) ($katalog['is_active'] ?? 0) === 1;
$kategoriKey = (string) ($katalog['kategori'] ?? '');
$kategoriLabel = $kategoriOptions[$kategoriKey] ?? str_replace('_', ' ', $kategoriKey);
$hargaDasar  = (float) ($katalog['harga_dasar'] ?? 0);
helper(['deadline', 'notification']);
$hargaDasarOld = old('harga_dasar');
if ($hargaDasarOld !== null && $hargaDasarOld !== '') {
    $hargaDasarParsed = parseRupiahAmount($hargaDasarOld);
    $hargaDasarDisplay = $hargaDasarParsed > 0
        ? 'Rp ' . number_format($hargaDasarParsed, 0, ',', '.')
        : '';
} else {
    $hargaDasarDisplay = $hargaDasar > 0
        ? 'Rp ' . number_format($hargaDasar, 0, ',', '.')
        : '';
}
$estimasiFormValue = old('estimasi_hari');
if ($estimasiFormValue === null || $estimasiFormValue === '') {
    $estimasiFormValue = (string) parseEstimasiHariKerja((string) ($katalog['estimasi_hari'] ?? ''));
}
?>

<div class="text-xs text-slate-400 mb-2">
    <a href="<?= site_url('katalog/kelola') ?>" class="hover:text-[#051747]">Katalog</a>
    <span class="mx-1">›</span>
    <span class="text-slate-500"><?= esc($pageTitle) ?></span>
</div>

<h2 class="sr-only"><?= esc($pageTitle) ?></h2>

<?php if ($readOnly): ?>
<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Nama Produk</p>
                <p class="text-base font-semibold text-[#051747]"><?= esc((string) ($katalog['nama_produk'] ?? '-')) ?></p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Kategori</p>
                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-800">
                    <?= esc($kategoriLabel) ?>
                </span>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Harga Dasar</p>
                <p class="text-base font-semibold text-[#051747]">Rp <?= esc(number_format($hargaDasar, 0, ',', '.')) ?></p>
                <p class="text-xs text-slate-400 mt-1">Harga per satuan produk</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Estimasi Pengerjaan</p>
                <p class="text-sm text-slate-700"><?= esc((string) ($katalog['estimasi_hari'] ?? '-')) ?></p>
            </div>
        </div>

        <div class="space-y-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Kuota Revisi Default</p>
                <p class="text-sm text-slate-700"><?= esc((string) ($katalog['kuota_revisi_default'] ?? '0')) ?>x</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Min Order + Satuan</p>
                <p class="text-sm text-slate-700">
                    <?= esc((string) ($katalog['min_order'] ?? '0')) ?>
                    <?= esc((string) ($katalog['satuan'] ?? '')) ?>
                </p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Status Produk</p>
                <?php if ($isActive): ?>
                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">● Aktif</span>
                <?php else: ?>
                    <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">● Nonaktif</span>
                <?php endif; ?>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">Kode Produk</p>
                <p class="text-sm font-mono text-slate-600"><?= esc((string) ($katalog['kode_katalog'] ?? '-')) ?></p>
            </div>
        </div>
    </div>

    <div class="mt-6 pt-6 border-t border-slate-100">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">Deskripsi</p>
        <?php if (trim((string) ($katalog['deskripsi'] ?? '')) !== ''): ?>
            <p class="text-sm text-slate-700 whitespace-pre-line leading-relaxed"><?= esc((string) $katalog['deskripsi']) ?></p>
        <?php else: ?>
            <p class="text-sm text-slate-400 italic">Tidak ada deskripsi.</p>
        <?php endif; ?>
    </div>

    <div class="mt-6 pt-6 border-t border-slate-100">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-3">Gambar Produk</p>
        <?php if (!empty($katalog['gambar'])): ?>
            <?php $gambarUrl = base_url('uploads/katalog/' . $katalog['gambar']); ?>
            <button
                type="button"
                class="group relative block rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/40"
                data-katalog-zoom="<?= esc($gambarUrl) ?>"
                data-katalog-zoom-alt="<?= esc((string) ($katalog['nama_produk'] ?? 'Gambar produk')) ?>"
                aria-label="Perbesar foto produk">
                <img
                    src="<?= esc($gambarUrl) ?>"
                    alt="<?= esc((string) ($katalog['nama_produk'] ?? 'Gambar produk')) ?>"
                    class="w-40 h-40 object-cover rounded-xl border border-slate-200 cursor-zoom-in transition-opacity group-hover:opacity-90">
                <span class="absolute bottom-2 right-2 bg-[#051747] text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                    Zoom
                </span>
            </button>
            <p class="mt-2 text-xs text-slate-400">Klik gambar untuk melihat ukuran penuh.</p>
        <?php else: ?>
            <div class="w-40 h-40 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center">
                <span class="text-xs text-slate-400">Belum ada gambar</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
        <a href="<?= site_url('katalog/kelola') ?>" class="btn-outline inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm text-center">
            <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-4 w-4 shrink-0']) ?>
            Kembali ke Katalog
        </a>
        <a href="<?= site_url('form-template/' . $idKatalog) ?>" class="btn-primary inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm text-white">
            Lihat Form Template
            <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-right', 'class' => 'h-4 w-4 shrink-0']) ?>
        </a>
    </div>
</div>

<?php else: ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <form action="<?= site_url('katalog/simpan-ubah/' . $idKatalog) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <p class="form-note mb-4">
            <span class="text-red-500 font-bold">*</span> yang diberi bintang merah wajib diisi.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label for="nama_produk" class="form-label">Nama Produk <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="nama_produk"
                        name="nama_produk"
                        value="<?= esc(old('nama_produk', $katalog['nama_produk'] ?? '')) ?>"
                        class="input-field w-full px-3.5 py-2.5"
                        required>
                </div>

                <div>
                    <label for="kategori" class="form-label">Kategori <span class="text-red-500">*</span></label>
                    <select id="kategori" name="kategori" class="input-field w-full px-3.5 py-2.5" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategoriOptions as $value => $label): ?>
                            <?php
                            $selected = old('kategori') !== null
                                ? old('kategori') === $value
                                : ($katalog['kategori'] ?? '') === $value;
                            ?>
                            <option value="<?= esc($value) ?>" <?= $selected ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="harga_dasar" class="form-label">Harga Dasar (Rp) <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="harga_dasar"
                        name="harga_dasar"
                        value="<?= esc($hargaDasarDisplay) ?>"
                        inputmode="numeric"
                        autocomplete="off"
                        placeholder="Rp 0"
                        class="js-harga-custom-rupiah-input input-field w-full px-3.5 py-2.5"
                        required>
                    <p class="form-hint">Harga per satuan produk</p>
                </div>

                <div>
                    <label for="estimasi_hari" class="form-label">Estimasi Pengerjaan <span class="text-red-500">*</span></label>
                    <div class="flex items-end gap-3">
                        <input
                            type="number"
                            id="estimasi_hari"
                            name="estimasi_hari"
                            value="<?= esc($estimasiFormValue) ?>"
                            placeholder="6"
                            min="1"
                            max="180"
                            step="1"
                            onwheel="this.blur()"
                            class="input-field w-40 px-3.5 py-2.5"
                            required>
                        <span class="form-affix pb-2.5">hari kerja</span>
                    </div>
                    <p class="form-hint">Angka bulat · dihitung hari kerja (Sen–Jum), contoh: 6 hari kerja.</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="kuota_revisi_default" class="form-label">Kuota Revisi Default <span class="text-red-500">*</span></label>
                    <input
                        type="number"
                        id="kuota_revisi_default"
                        name="kuota_revisi_default"
                        value="<?= esc(old('kuota_revisi_default', (string) ($katalog['kuota_revisi_default'] ?? '3'))) ?>"
                        min="1"
                        max="10"
                        class="input-field w-full px-3.5 py-2.5"
                        required>
                    <p class="form-hint">Jumlah maksimal revisi desain</p>
                </div>

                <div>
                    <label class="form-label">Min Order + Satuan <span class="text-red-500">*</span></label>
                    <div class="flex gap-3">
                        <input
                            type="number"
                            name="min_order"
                            value="<?= esc(old('min_order', (string) ($katalog['min_order'] ?? ''))) ?>"
                            placeholder="100"
                            min="1"
                            class="input-field flex-1 px-3.5 py-2.5"
                            required>
                        <input
                            type="text"
                            name="satuan"
                            value="<?= esc(old('satuan', $katalog['satuan'] ?? '')) ?>"
                            placeholder="pcs"
                            class="input-field w-32 px-3.5 py-2.5"
                            required>
                    </div>
                    <p class="form-hint">Contoh: 100 pcs, 1 buku, 50 lembar</p>
                </div>

                <div>
                    <label for="deskripsi" class="form-label">
                        Deskripsi (Komponen yang sudah include di harga)
                    </label>
                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="4"
                        placeholder="Contoh: Hardcover linen, ukuran 12x17cm, 4 halaman, laminasi doff, ribbon pita, sablon emas, amplop custom"
                        class="input-field w-full px-3.5 py-2.5 resize-none"><?= esc(old('deskripsi', $katalog['deskripsi'] ?? '')) ?></textarea>
                    <p class="form-hint">
                        Isi Deskripsi sedetail mungkin, ini ditampilkan di katalog public dan form pesanan agar pelanggan tahu spesifikasi.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <label class="form-label">
                Gambar Produk
                <span class="normal-case font-medium text-slate-400"> (Kosongkan jika tidak ingin mengubah)</span>
            </label>

            <?php if (!empty($katalog['gambar'])): ?>
                <?php $gambarUrl = base_url('uploads/katalog/' . $katalog['gambar']); ?>
                <div class="mb-3 flex items-center gap-4">
                    <button
                        type="button"
                        class="group relative block rounded-xl focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/40"
                        data-katalog-zoom="<?= esc($gambarUrl) ?>"
                        data-katalog-zoom-alt="<?= esc((string) ($katalog['nama_produk'] ?? 'Gambar produk')) ?>"
                        aria-label="Perbesar foto produk">
                        <img
                            src="<?= esc($gambarUrl) ?>"
                            alt="<?= esc((string) ($katalog['nama_produk'] ?? 'Gambar produk')) ?>"
                            class="w-24 h-24 object-cover rounded-xl border border-slate-200 cursor-zoom-in transition-opacity group-hover:opacity-90">
                    </button>
                    <p class="form-upload-text">Gambar saat ini. Unggah baru untuk mengganti.</p>
                </div>
            <?php endif; ?>

            <div
                class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:border-[#2E5CE6] transition-colors cursor-pointer"
                onclick="document.getElementById('inputGambar').click()">
                <div id="previewContainer" class="hidden mb-3">
                    <img id="previewImg" src="" alt="Preview" class="w-32 h-32 object-cover rounded-lg mx-auto">
                </div>
                <div id="uploadPlaceholder">
                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="form-upload-text">Klik untuk upload gambar produk</p>
                    <p class="form-upload-hint">JPG, PNG, atau WEBP · maks. 2MB</p>
                </div>
                <span id="fileName" class="hidden form-upload-text mt-2 block"></span>
            </div>
            <input type="file" id="inputGambar" name="gambar" accept=".jpg,.jpeg,.png,.webp" class="hidden">
        </div>

        <div class="mt-6 pt-6 border-t border-slate-100">
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-3 cursor-pointer">
                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    <?= old('is_active') !== null
                        ? ((int) old('is_active') === 1 ? 'checked' : '')
                        : ((int) ($katalog['is_active'] ?? 0) === 1 ? 'checked' : '') ?>
                    class="w-4 h-4 accent-[#051747]">
                <span class="form-choice">Produk Aktif (tersedia untuk dipesan)</span>
            </label>
        </div>

        <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
            <a href="<?= site_url('katalog/kelola') ?>" class="btn-outline inline-flex items-center justify-center px-5 py-2.5 text-sm text-center">
                Batal
            </a>
            <button type="submit" class="btn-primary px-5 py-2.5 text-sm text-white">
                Simpan Produk
            </button>
        </div>
    </form>
</div>

<?php endif; ?>

<?= $this->include('partials/katalog_gambar_zoom') ?>

<?= $this->endSection() ?>

<?php if (!$readOnly): ?>
<?= $this->section('scripts') ?>
<script>
    document.getElementById('inputGambar')?.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewContainer').classList.remove('hidden');
            document.getElementById('uploadPlaceholder').classList.add('hidden');
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileName').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });
</script>
<?= view('partials/rupiah_input_format_script') ?>
<?= $this->endSection() ?>
<?php endif; ?>
