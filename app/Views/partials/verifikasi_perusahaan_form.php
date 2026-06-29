<?php
/**
 * @var string                    $formAction
 * @var array<string, mixed>      $pelanggan
 * @var array<string, mixed>|null $verifikasiTerbaru
 */
$oldInput = static fn(string $key, string $fallback = '') => esc(old($key, $fallback));
?>
<form method="post"
    action="<?= esc($formAction) ?>"
    enctype="multipart/form-data"
    class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Nama Perusahaan</label>
        <input type="text" name="nama_perusahaan" required maxlength="150"
            value="<?= $oldInput('nama_perusahaan', (string) ($verifikasiTerbaru['nama_perusahaan'] ?? $pelanggan['nama_perusahaan'] ?? '')) ?>"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Jabatan PIC</label>
        <input type="text" name="jabatan_pic" required maxlength="100"
            value="<?= $oldInput('jabatan_pic', (string) ($verifikasiTerbaru['jabatan_pic'] ?? '')) ?>"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. HP / WA Perusahaan</label>
        <input type="text" name="wa_perusahaan" required pattern="[0-9]{10,13}" maxlength="13"
            value="<?= $oldInput('wa_perusahaan', (string) ($verifikasiTerbaru['wa_perusahaan'] ?? '')) ?>"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Alamat Kantor</label>
        <textarea name="alamat_kantor" required rows="2" maxlength="255"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"><?= $oldInput('alamat_kantor', (string) ($verifikasiTerbaru['alamat_kantor'] ?? '')) ?></textarea>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. NPWP (opsional)</label>
        <input type="text"
            name="no_npwp"
            data-npwp-input
            maxlength="20"
            inputmode="numeric"
            autocomplete="off"
            placeholder="12.345.678.9-012.345"
            value="<?= $oldInput('no_npwp', (string) ($verifikasiTerbaru['no_npwp'] ?? '')) ?>"
            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        <p class="text-xs text-slate-400 mt-1">15 digit angka · format otomatis XX.XXX.XXX.X-XXX.XXX</p>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Dokumen NPWP</label>
        <input type="file" name="dokumen_npwp" required accept=".jpg,.jpeg,.png,.pdf"
            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#2E5CE6]">
        <p class="text-xs text-slate-400 mt-1">JPG, PNG, atau PDF · maks. 2MB</p>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">KTP PIC / Penanggung Jawab</label>
        <input type="file" name="dokumen_ktp_pic" required accept=".jpg,.jpeg,.png,.pdf"
            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#2E5CE6]">
        <p class="text-xs text-slate-400 mt-1">JPG, PNG, atau PDF · maks. 2MB</p>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">MOU / Surat Kerjasama / SPK (PDF)</label>
        <input type="file" name="dokumen_mou" required accept=".pdf"
            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#2E5CE6]">
        <p class="text-xs text-slate-400 mt-1">PDF bermaterai · maks. 2MB</p>
    </div>

    <button type="submit"
        class="bg-[#051747] text-white rounded-full px-5 py-2.5 text-sm font-bold uppercase hover:bg-[#2E5CE6] transition-colors">
        Ajukan Verifikasi
    </button>
</form>
