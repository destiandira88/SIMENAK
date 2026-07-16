<?php
/**
 * @var string $namaUser
 * @var string $role
 * @var string $userEmail
 * @var bool   $showProfilLink
 * @var bool   $isKerjasamaPelanggan
 */
$roleLabels = [
    'pelanggan' => 'Pelanggan',
    'admin'     => 'Admin',
    'keuangan'  => 'Keuangan',
    'produksi'  => 'Produksi',
    'owner'     => 'Pemilik',
];
$roleLabel = $roleLabels[$role] ?? ucfirst($role);
$isKerjasamaPelanggan = (bool) ($isKerjasamaPelanggan ?? false);
?>
<div class="relative pl-4 border-l" style="border-color:var(--border);">
    <button type="button"
        id="userMenuBtn"
        class="flex items-center gap-3 rounded-xl px-1 py-1 transition-colors hover:bg-slate-50"
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="Menu akun">
        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background:var(--blue-accent);">
            <?= esc(strtoupper(substr($namaUser ?: 'U', 0, 1))) ?>
        </div>
        <div class="hidden sm:block text-left">
            <p class="text-sm font-semibold text-[#051747] leading-tight max-w-[140px] truncate"><?= esc($namaUser ?: 'Pengguna') ?></p>
            <?php if ($role === 'pelanggan'): ?>
                <div class="flex flex-wrap items-center gap-1 mt-0.5">
                    <span class="text-xs" style="color:var(--text-muted);"><?= esc($roleLabel) ?></span>
                    <?php if ($isKerjasamaPelanggan): ?>
                        <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-semibold leading-none bg-emerald-100 text-emerald-800">
                            Kerjasama perusahaan
                        </span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-xs capitalize" style="color:var(--text-muted);"><?= esc($roleLabel) ?></p>
            <?php endif; ?>
        </div>
        <svg class="hidden sm:block w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div id="userMenuDropdown"
        class="hidden absolute right-0 top-full z-50 mt-2 w-56 overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-lg">
        <div class="px-4 py-3 border-b border-[#E2E8F0]">
            <p class="text-sm font-bold text-[#051747] truncate"><?= esc($namaUser ?: 'Pengguna') ?></p>
            <?php if ($userEmail !== ''): ?>
                <p class="text-xs text-slate-500 truncate"><?= esc($userEmail) ?></p>
            <?php endif; ?>
            <?php if ($role === 'pelanggan'): ?>
                <div class="flex flex-wrap items-center gap-1 mt-1">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-400"><?= esc($roleLabel) ?></span>
                    <?php if ($isKerjasamaPelanggan): ?>
                        <span class="inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-semibold leading-none bg-emerald-100 text-emerald-800 normal-case tracking-normal">
                            Kerjasama perusahaan
                        </span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mt-1"><?= esc($roleLabel) ?></p>
            <?php endif; ?>
        </div>
        <div class="p-2">
            <?php if ($showProfilLink): ?>
                <button type="button"
                    data-open-profil-modal
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-[#051747] transition-colors hover:bg-[#F0F2F8]">
                    <svg class="w-5 h-5 text-[#2E5CE6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profil Saya
                </button>
            <?php endif; ?>
            <button type="button"
                data-open-logout-modal
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-red-600 transition-colors hover:bg-red-50 <?= $showProfilLink ? 'mt-1 border-t border-[#E2E8F0] pt-2.5' : '' ?>">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </button>
        </div>
    </div>
</div>
