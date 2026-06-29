<?php
/**
 * @var string $formAction
 */
$formAction = (string) ($formAction ?? site_url('pengguna/staff/update/0'));
?>
<form method="post"
    action="<?= esc($formAction) ?>"
    id="formEditStaff"
    class="js-action-confirm-form"
    data-confirm-variant="save-pelanggan"
    data-confirm-title="Simpan Perubahan?"
    data-confirm-message="Apakah Anda yakin ingin menyimpan perubahan pada data staff internal?">
    <?= csrf_field() ?>

    <div class="space-y-4">
        <div>
            <label for="es_nama" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" id="es_nama" name="nama" required minlength="3" maxlength="100"
                pattern="[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*"
                title="Hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter)"
                placeholder="Nama staff"
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        </div>
        <div>
            <label for="es_email" class="block text-sm font-semibold text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" id="es_email" name="email" required maxlength="100"
                placeholder="staff@email.com"
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
        </div>
        <div>
            <label for="es_staff_role" class="block text-sm font-semibold text-slate-700 mb-1">Peran Staff <span class="text-red-500">*</span></label>
            <select id="es_staff_role" name="staff_role" required
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                <option value="admin">Admin</option>
                <option value="keuangan">Keuangan</option>
                <option value="produksi">Produksi</option>
            </select>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-slate-100">
        <button type="submit"
            class="bg-[#051747] text-white text-sm font-bold px-6 py-2.5 rounded-full hover:bg-[#2E5CE6] transition-colors">
            Simpan Perubahan
        </button>
        <button type="button" id="editStaffModalCancel"
            class="inline-flex items-center justify-center border border-slate-300 text-slate-600 text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-slate-50 transition-colors">
            Batal
        </button>
    </div>
</form>
