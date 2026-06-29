<?php
/**
 * @var bool $canCreateStaff
 * @var bool $canCreatePelanggan
 */
$canCreateStaff     = (bool) ($canCreateStaff ?? false);
$canCreatePelanggan = (bool) ($canCreatePelanggan ?? false);
$defaultType        = old('account_type', $canCreatePelanggan ? 'pelanggan' : 'staff');
if ($defaultType === 'staff' && !$canCreateStaff) {
    $defaultType = 'pelanggan';
}
if ($defaultType === 'pelanggan' && !$canCreatePelanggan) {
    $defaultType = 'staff';
}
$passwordMode = old('password_mode', 'manual');
$namaVal      = old('nama', '');
$emailVal     = old('email', '');
$telpVal      = old('no_telp', '');
$alamatVal    = old('alamat', '');
$staffRoleVal = old('staff_role', 'keuangan');
?>
<div id="tambahPenggunaModal" class="fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/50">
    <div class="relative w-full max-w-lg bg-white rounded-[20px] shadow-lg border border-slate-100 p-6 max-h-[90vh] overflow-y-auto">
        <button type="button" id="tambahPenggunaModalClose" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-2xl leading-none" aria-label="Tutup">&times;</button>
        <h3 class="text-lg font-bold text-[#051747] pr-8"><?= $canCreateStaff && !$canCreatePelanggan ? 'Tambah Staff Internal' : 'Tambah Pengguna' ?></h3>
        <p class="text-sm text-slate-500 mt-1 mb-4">
            <?php if ($canCreatePelanggan && $canCreateStaff): ?>
                Daftarkan akun pelanggan baru atau staff internal.
            <?php elseif ($canCreatePelanggan): ?>
                Daftarkan akun pelanggan baru.
            <?php else: ?>
                Daftarkan staff internal (Admin, Keuangan, atau Produksi).
            <?php endif; ?>
        </p>

        <form method="post" action="<?= esc(site_url('pengguna/simpan')) ?>" id="formTambahPengguna" class="<?= $canCreatePelanggan ? 'js-pelanggan-akun-form' : '' ?>">
            <?= csrf_field() ?>

            <?php if ($canCreatePelanggan): ?>
            <p class="text-xs text-slate-500 mb-4">
                <span class="text-red-500 font-bold">*</span> yang diberi bintang merah wajib diisi.
            </p>
            <?php endif; ?>

            <?php if ($canCreatePelanggan && $canCreateStaff): ?>
            <fieldset class="mb-4">
                <legend class="block text-sm font-semibold text-slate-700 mb-2">Jenis Akun</legend>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input type="radio" name="account_type" value="pelanggan" class="accent-[#051747]"
                            <?= $defaultType === 'pelanggan' ? 'checked' : '' ?>
                            data-account-type-radio>
                        Pelanggan
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input type="radio" name="account_type" value="staff" class="accent-[#051747]"
                            <?= $defaultType === 'staff' ? 'checked' : '' ?>
                            data-account-type-radio>
                        Staff Internal
                    </label>
                </div>
            </fieldset>
            <?php else: ?>
                <input type="hidden" name="account_type" value="<?= $canCreateStaff ? 'staff' : 'pelanggan' ?>">
            <?php endif; ?>

            <div class="space-y-4">
                <div>
                    <label for="tp_nama" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="tp_nama" name="nama" required minlength="3" maxlength="100"
                        pattern="[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*"
                        title="Hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter)"
                        autocomplete="name"
                        value="<?= esc($namaVal) ?>"
                        placeholder="Budi Santoso"
                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                    <p class="text-xs text-slate-400 mt-1">Hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter).</p>
                </div>
                <div>
                    <label for="tp_email" class="block text-sm font-semibold text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="tp_email" name="email" required maxlength="100"
                        autocomplete="email"
                        value="<?= esc($emailVal) ?>"
                        placeholder="nama@email.com"
                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                </div>

                <div id="tambahPelangganFields" class="space-y-4">
                    <div>
                        <label for="tp_no_telp" class="block text-sm font-semibold text-slate-700 mb-1">No. Telepon <span class="text-red-500">*</span></label>
                        <input type="tel" id="tp_no_telp" name="no_telp" required inputmode="tel"
                            autocomplete="tel"
                            pattern="^(\+62|08|022)[0-9]{8,13}$"
                            maxlength="20"
                            title="Format harus berupa angka dan diawali dengan 08, +62, atau 022"
                            value="<?= esc($telpVal) ?>"
                            placeholder="087778965442"
                            class="js-pelanggan-phone-input w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                        <p class="text-xs text-slate-400 mt-1">Format harus berupa angka dan diawali dengan 08, +62, atau 022 (Contoh: 087778965442) (8–13 digit setelah awalan).</p>
                    </div>
                    <div>
                        <label for="tp_alamat" class="block text-sm font-semibold text-slate-700 mb-1">Alamat <span class="text-red-500">*</span></label>
                        <textarea id="tp_alamat" name="alamat" rows="3" required minlength="10" maxlength="150"
                            placeholder="Alamat lengkap tempat tinggal"
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"><?= esc($alamatVal) ?></textarea>
                        <p class="text-xs text-slate-400 mt-1">Minimal 10 karakter · maks. 150 karakter.</p>
                    </div>

                    <fieldset>
                        <legend class="block text-sm font-semibold text-slate-700 mb-2">Kata Sandi</legend>
                        <div class="flex flex-wrap gap-4 mb-3">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="radio" name="password_mode" value="manual" class="accent-[#051747]"
                                    <?= $passwordMode === 'manual' ? 'checked' : '' ?>
                                    data-password-mode-radio>
                                Isi manual
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="radio" name="password_mode" value="auto" class="accent-[#051747]"
                                    <?= $passwordMode === 'auto' ? 'checked' : '' ?>
                                    data-password-mode-radio>
                                Generate otomatis
                            </label>
                        </div>
                        <div id="tpPasswordManualWrap" class="space-y-3">
                            <div>
                                <label for="tp_password" class="block text-sm font-semibold text-slate-700 mb-1">Kata Sandi <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="password" id="tp_password" name="password" minlength="8" required autocomplete="new-password"
                                        placeholder="Minimal 8 karakter"
                                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 pr-10 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 js-toggle-password" data-target="tp_password" aria-label="Tampilkan kata sandi">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label for="tp_password_confirm" class="block text-sm font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="password" id="tp_password_confirm" name="password_confirm" minlength="8" required autocomplete="new-password"
                                        placeholder="Ulangi kata sandi"
                                        class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 pr-10 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 js-toggle-password" data-target="tp_password_confirm" aria-label="Tampilkan konfirmasi kata sandi">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p id="tpPasswordAutoNote" class="hidden text-xs text-slate-500">Kata sandi akan digenerate otomatis dan ditampilkan sekali setelah simpan.</p>
                    </fieldset>
                </div>

                <div id="tambahStaffFields" class="hidden space-y-4">
                    <div>
                        <label for="staff_role" class="block text-sm font-semibold text-slate-700 mb-1">Peran Staff <span class="text-red-500">*</span></label>
                        <select id="staff_role" name="staff_role"
                            class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                            <?php foreach (['admin' => 'Admin', 'keuangan' => 'Keuangan', 'produksi' => 'Produksi'] as $val => $label): ?>
                                <option value="<?= esc($val) ?>" <?= $staffRoleVal === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p class="text-xs text-slate-500">Kata sandi digenerate otomatis dan ditampilkan sekali setelah simpan.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-slate-100">
                <button type="submit"
                    class="bg-[#051747] text-white text-sm font-bold px-6 py-2.5 rounded-full hover:bg-[#2E5CE6] transition-colors">
                    Simpan
                </button>
                <button type="button" id="tambahPenggunaModalCancel"
                    class="inline-flex items-center justify-center border border-slate-300 text-slate-600 text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-slate-50 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
