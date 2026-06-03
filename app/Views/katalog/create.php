<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Tambah Produk') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?>Tambah Produk<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$kategoriOptions = [
    'desain_grafis' => 'Desain Grafis',
    'cetak_digital' => 'Cetak Digital',
    'cetak_offset'  => 'Cetak Offset',
    'media_promosi' => 'Media Promosi',
];
?>

<div class="text-xs text-slate-400 mb-2">
    <a href="<?= site_url('katalog/kelola') ?>" class="hover:text-[#051747]">Katalog</a>
    <span class="mx-1">›</span>
    <span class="text-slate-500">Tambah Produk</span>
</div>

<h2 class="text-2xl font-extrabold text-[#051747] mb-6">Tambah Produk</h2>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <form action="<?= site_url('katalog/simpan') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <p class="text-xs text-slate-400 mb-4">
            <span class="text-red-500 font-bold">*</span> yang diberi bintang merah wajib diisi.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label for="nama_produk" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Produk <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="nama_produk"
                        name="nama_produk"
                        value="<?= esc(old('nama_produk')) ?>"
                        class="input-field w-full px-3 py-2.5"
                        required>
                </div>

                <div>
                    <label for="kategori" class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                    <select id="kategori" name="kategori" class="input-field w-full px-3 py-2.5" required>
                        <option value="">Pilih Kategori</option>
                        <?php foreach ($kategoriOptions as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= old('kategori') === $value ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="harga_dasar" class="block text-sm font-semibold text-slate-700 mb-1.5">Harga Dasar <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-slate-500 shrink-0">Rp</span>
                        <input
                            type="number"
                            id="harga_dasar"
                            name="harga_dasar"
                            value="<?= esc(old('harga_dasar')) ?>"
                            placeholder="150000"
                            min="1"
                            step="1"
                            class="input-field w-full px-3 py-2.5"
                            required>
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Harga per satuan produk</p>
                </div>

                <div>
                    <label for="estimasi_hari" class="block text-sm font-semibold text-slate-700 mb-1.5">Estimasi Pengerjaan <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="estimasi_hari"
                        name="estimasi_hari"
                        value="<?= esc(old('estimasi_hari')) ?>"
                        placeholder="Contoh: 3-7 hari kerja"
                        class="input-field w-full px-3 py-2.5"
                        required>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="kuota_revisi_default" class="block text-sm font-semibold text-slate-700 mb-1.5">Kuota Revisi Default <span class="text-red-500">*</span></label>
                    <input
                        type="number"
                        id="kuota_revisi_default"
                        name="kuota_revisi_default"
                        value="<?= esc(old('kuota_revisi_default', '3')) ?>"
                        min="1"
                        max="10"
                        class="input-field w-full px-3 py-2.5"
                        required>
                    <p class="mt-1.5 text-xs text-slate-400">Jumlah maksimal revisi desain</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Min Order + Satuan <span class="text-red-500">*</span></label>
                    <div class="flex gap-3">
                        <input
                            type="number"
                            name="min_order"
                            value="<?= esc(old('min_order')) ?>"
                            placeholder="100"
                            min="1"
                            class="input-field flex-1 px-3 py-2.5"
                            required>
                        <input
                            type="text"
                            name="satuan"
                            value="<?= esc(old('satuan')) ?>"
                            placeholder="pcs"
                            class="input-field w-32 px-3 py-2.5"
                            required>
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Contoh: 100 pcs, 1 buku, 50 lembar</p>
                </div>

                <div>
                    <label for="deskripsi" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Deskripsi (Komponen yang sudah include di harga)
                    </label>
                    <textarea
                        id="deskripsi"
                        name="deskripsi"
                        rows="4"
                        placeholder="Contoh: Hardcover linen, bahan isian jasmine 21gr, ukuran 12x17cm, 4 halaman, laminasi doff, ribbon pita, "
                        class="input-field w-full px-3 py-2.5 resize-none"><?= esc(old('deskripsi')) ?></textarea>
                    <p class="mt-1.5 text-xs text-slate-400">
                        *Isi Deskripsi sedetail mungkin, ini ditampilkan di katalog public dan form pemesanan agar pelanggan tahu spesifikasi.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Gambar Produk
                <span class="text-slate-400 font-normal text-xs ml-1">(Opsional)</span>
            </label>
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
                    <p class="text-sm text-slate-400">Klik untuk upload gambar produk</p>
                    <p class="text-xs text-slate-300 mt-1">JPG, PNG, WEBP — Maks 2MB</p>
                </div>
                <span id="fileName" class="hidden text-sm text-slate-500 mt-2 block"></span>
            </div>
            <input type="file" id="inputGambar" name="gambar" accept=".jpg,.jpeg,.png,.webp" class="hidden">
        </div>

        <div class="mt-6 pt-6 border-t border-slate-100">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 accent-[#051747]">
                <span class="text-sm font-medium text-slate-700">Produk Aktif (tersedia untuk dipesan)</span>
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

<?= $this->endSection() ?>

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
<?= $this->endSection() ?>