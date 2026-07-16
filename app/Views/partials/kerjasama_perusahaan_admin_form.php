<?php
/**
 * @var string $formAction
 * @var string $namaPelanggan
 */
$formAction   = (string) ($formAction ?? '');
$namaPelanggan = (string) ($namaPelanggan ?? '');
?>
<form method="post"
    action="<?= esc($formAction) ?>"
    enctype="multipart/form-data"
    class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Nama Perusahaan <span class="text-red-500">*</span></label>
        <input type="text" name="nama_perusahaan" required maxlength="150"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Jabatan PIC <span class="text-red-500">*</span></label>
            <input type="text" name="jabatan_pic" required maxlength="100"
                class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. HP / WA Perusahaan <span class="text-red-500">*</span></label>
            <input type="text" name="wa_perusahaan" required maxlength="20" autocomplete="tel"
                data-wa-perusahaan-input
                placeholder="08xxxxxxxxxx atau +62xxxxxxxxxx"
                class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
            <p class="text-xs text-slate-400 mt-1">Diawali 08, +62, atau 022 · 8–13 digit setelah awalan</p>
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Alamat Kantor <span class="text-red-500">*</span></label>
        <textarea name="alamat_kantor" required rows="2" maxlength="255"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"></textarea>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. NPWP (opsional)</label>
        <input type="text" name="no_npwp" data-npwp-input maxlength="20" inputmode="numeric" autocomplete="off" placeholder="12.345.678.9-012.345"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        <p class="text-xs text-slate-400 mt-1">15 digit angka · format otomatis XX.XXX.XXX.X-XXX.XXX</p>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Catatan Admin (opsional)</label>
        <textarea name="catatan_admin" rows="2" maxlength="500"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
            placeholder="Referensi MOU, catatan internal, dll."></textarea>
    </div>

    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dokumen (opsional)</p>

    <div>
        <label class="block text-xs text-slate-500 mb-1">Dokumen NPWP</label>
        <input type="file" name="dokumen_npwp" accept=".jpg,.jpeg,.png,.pdf"
            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">KTP PIC</label>
        <input type="file" name="dokumen_ktp_pic" accept=".jpg,.jpeg,.png,.pdf"
            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">MOU / Surat Kerjasama (PDF)</label>
        <input type="file" name="dokumen_mou" accept=".pdf"
            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white">
    </div>

    <button type="submit"
        class="bg-[#051747] text-white rounded-full px-5 py-2.5 text-sm font-bold uppercase hover:bg-[#2E5CE6] transition-colors">
        Tetapkan Kerja Sama Perusahaan
    </button>
</form>
