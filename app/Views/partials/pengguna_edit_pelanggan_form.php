<?php
/**
 * Form edit data pelanggan (dipakai di template modal halaman Pengguna).
 *
 * @var string $formAction
 */
$formAction = (string) ($formAction ?? site_url('pengguna/pelanggan/update/0'));
?>
<form method="post"
    action="<?= esc($formAction) ?>"
    id="formEditPelanggan"
    class="js-action-confirm-form js-pelanggan-akun-form"
    data-confirm-variant="save-pelanggan"
    data-confirm-title="Simpan Perubahan?"
    data-confirm-message="Apakah Anda yakin ingin menyimpan perubahan pada data pengguna?">
    <?= csrf_field() ?>

    <div class="space-y-4">
        <div>
            <label for="ep_nama" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" id="ep_nama" name="nama" required minlength="3" maxlength="100"
                pattern="[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*"
                title="Hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter)"
                placeholder="Budi Santoso"
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        </div>
        <div>
            <label for="ep_email" class="block text-sm font-semibold text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" id="ep_email" name="email" required maxlength="100"
                placeholder="nama@email.com"
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        </div>
        <div>
            <label for="ep_no_telp" class="block text-sm font-semibold text-slate-700 mb-1">No. Telepon <span class="text-red-500">*</span></label>
            <input type="tel" id="ep_no_telp" name="no_telp" required inputmode="tel"
                pattern="^(\+62|08|022)[0-9]{8,13}$"
                title="Format harus berupa angka dan diawali dengan 08, +62, atau 022"
                placeholder="087778965442"
                class="js-pelanggan-phone-input w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
            <p class="text-xs text-slate-400 mt-1">Format angka, diawali 08, +62, atau 022 (maks. 13 digit setelah awalan)</p>
        </div>
        <div>
            <label for="ep_alamat" class="block text-sm font-semibold text-slate-700 mb-1">Alamat <span class="text-red-500">*</span></label>
            <textarea id="ep_alamat" name="alamat" required rows="3" minlength="10" maxlength="150"
                placeholder="Alamat lengkap tempat tinggal"
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"></textarea>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-slate-100">
        <button type="submit"
            class="bg-[#051747] text-white text-sm font-bold px-6 py-2.5 rounded-full hover:bg-[#2E5CE6] transition-colors">
            Simpan Perubahan
        </button>
        <button type="button" id="editPelangganModalCancel"
            class="inline-flex items-center justify-center border border-slate-300 text-slate-600 text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-slate-50 transition-colors">
            Batal
        </button>
    </div>
</form>
