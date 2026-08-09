<?php
/**
 * @var int    $idPelanggan
 * @var string $namaPerusahaan
 * @var string $namaPelanggan
 * @var string $emailPelanggan
 * @var string $telpPelanggan
 * @var string $alamatPelanggan
 * @var bool   $isKerjasamaAktif
 * @var bool   $isActive
 * @var bool   $canTogglePelanggan
 * @var bool   $hasActiveOrders
 */
$idPelanggan        = (int) ($idPelanggan ?? 0);
$namaPerusahaan     = (string) ($namaPerusahaan ?? '');
$namaPelanggan      = (string) ($namaPelanggan ?? '');
$emailPelanggan     = (string) ($emailPelanggan ?? '');
$telpPelanggan      = (string) ($telpPelanggan ?? '');
$alamatPelanggan    = (string) ($alamatPelanggan ?? '');
$isKerjasamaAktif   = (bool) ($isKerjasamaAktif ?? false);
$isActive           = (bool) ($isActive ?? true);
$canTogglePelanggan = (bool) ($canTogglePelanggan ?? false);
$hasActiveOrders    = (bool) ($hasActiveOrders ?? false);
$labelPelanggan     = $namaPerusahaan !== '' ? $namaPerusahaan : $namaPelanggan;
$canDeactivate      = $canTogglePelanggan && $isActive && !$hasActiveOrders;
$canActivate        = $canTogglePelanggan && !$isActive;
?>
<div class="action-menu relative inline-block">
    <button
        type="button"
        class="action-menu-btn"
        aria-label="Menu aksi admin"
        aria-expanded="false"
        data-action-toggle>
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="5" r="1.75" />
            <circle cx="12" cy="12" r="1.75" />
            <circle cx="12" cy="19" r="1.75" />
        </svg>
    </button>
    <div class="action-dropdown hidden" role="menu">
        <button type="button"
            role="menuitem"
            class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50 transition-colors js-open-edit-pelanggan-modal"
            data-pelanggan-id="<?= esc((string) $idPelanggan) ?>"
            data-pelanggan-nama="<?= esc($namaPelanggan) ?>"
            data-pelanggan-email="<?= esc($emailPelanggan) ?>"
            data-pelanggan-telp="<?= esc($telpPelanggan) ?>"
            data-pelanggan-alamat="<?= esc($alamatPelanggan) ?>">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Data
        </button>
        <?php if (!$isKerjasamaAktif): ?>
            <button type="button"
                role="menuitem"
                class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors js-open-kerjasama-modal"
                data-pelanggan-id="<?= esc((string) $idPelanggan) ?>"
                data-pelanggan-nama="<?= esc($namaPelanggan) ?>">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Tetapkan Kerja Sama
            </button>
        <?php else: ?>
            <form method="post"
                action="<?= esc(site_url('pengguna/' . $idPelanggan . '/cabut-kerjasama')) ?>"
                role="menuitem"
                class="js-action-confirm-form"
                data-confirm-variant="danger"
                data-confirm-title="Cabut Kerja Sama Perusahaan?"
                data-confirm-message="Cabut status kerja sama untuk <?= esc($labelPelanggan) ?>? Pesanan baru akan mengikuti skema perseorangan (DP 50%).">
                <?= csrf_field() ?>
                <input type="hidden" name="catatan_alasan" value="Kerja sama perusahaan dicabut oleh admin.">
                <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    Cabut Kerja Sama
                </button>
            </form>
        <?php endif; ?>
        <?php if ($canDeactivate): ?>
            <form method="post"
                action="<?= esc(site_url('pengguna/pelanggan/' . $idPelanggan . '/toggle-status')) ?>"
                role="menuitem"
                class="js-action-confirm-form"
                data-confirm-variant="toggle-deactivate"
                data-confirm-title="Nonaktifkan Akun Pelanggan?"
                data-confirm-message="Nonaktifkan akun <?= esc($labelPelanggan) ?>? Pelanggan tidak dapat login hingga diaktifkan kembali.">
                <?= csrf_field() ?>
                <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-amber-700 hover:bg-amber-50 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    Nonaktifkan Akun
                </button>
            </form>
        <?php elseif ($canTogglePelanggan && $isActive && $hasActiveOrders): ?>
            <span role="menuitem" class="block px-3 py-2 text-[12px] text-slate-400 leading-snug cursor-not-allowed">
                Nonaktifkan tidak tersedia, masih ada pesanan aktif.
            </span>
        <?php endif; ?>
        <?php if ($canActivate): ?>
            <form method="post"
                action="<?= esc(site_url('pengguna/pelanggan/' . $idPelanggan . '/toggle-status')) ?>"
                role="menuitem"
                class="js-action-confirm-form"
                data-confirm-variant="toggle-activate"
                data-confirm-title="Aktifkan Akun Pelanggan?"
                data-confirm-message="Aktifkan kembali akun <?= esc($labelPelanggan) ?>? Pelanggan dapat login dan membuat pesanan seperti biasa.">
                <?= csrf_field() ?>
                <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-emerald-700 hover:bg-emerald-50 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Aktifkan Akun
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
