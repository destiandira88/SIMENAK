<?php
/**
 * @var int    $idUser
 * @var string $namaStaff
 * @var string $emailStaff
 * @var string $staffRole
 * @var bool   $isActive
 * @var bool   $canEdit
 * @var bool   $canToggle
 */
$idUser    = (int) ($idUser ?? 0);
$namaStaff  = (string) ($namaStaff ?? '');
$emailStaff = (string) ($emailStaff ?? '');
$staffRole  = (string) ($staffRole ?? '');
$isActive  = (bool) ($isActive ?? true);
$canEdit   = (bool) ($canEdit ?? false);
$canToggle = (bool) ($canToggle ?? false);

if (!$canEdit && !$canToggle) {
    echo '<span class="text-xs text-slate-300">-</span>';
    return;
}
?>
<div class="action-menu relative inline-block">
    <button
        type="button"
        class="action-menu-btn"
        aria-label="Menu aksi staff"
        aria-expanded="false"
        data-action-toggle>
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="5" r="1.75" />
            <circle cx="12" cy="12" r="1.75" />
            <circle cx="12" cy="19" r="1.75" />
        </svg>
    </button>
    <div class="action-dropdown hidden" role="menu">
        <?php if ($canEdit): ?>
            <button type="button"
                role="menuitem"
                class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-slate-700 hover:bg-slate-50 transition-colors js-open-edit-staff-modal"
                data-staff-id="<?= esc((string) $idUser) ?>"
                data-staff-nama="<?= esc($namaStaff) ?>"
                data-staff-email="<?= esc($emailStaff) ?>"
                data-staff-role="<?= esc($staffRole) ?>">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Data
            </button>
        <?php endif; ?>
        <?php if ($canToggle): ?>
            <form method="post"
                action="<?= esc(site_url('pengguna/' . $idUser . '/toggle-status')) ?>"
                role="menuitem"
                class="js-action-confirm-form"
                data-confirm-variant="<?= $isActive ? 'toggle-deactivate' : 'toggle-activate' ?>"
                data-confirm-title="<?= $isActive ? 'Nonaktifkan Akun Staff?' : 'Aktifkan Akun Staff?' ?>"
                data-confirm-message="<?= esc($isActive
                    ? 'Nonaktifkan akun ' . $namaStaff . ' (' . ucfirst($staffRole) . ')? Staff tidak dapat login hingga diaktifkan kembali.'
                    : 'Aktifkan kembali akun ' . $namaStaff . ' (' . ucfirst($staffRole) . ')? Staff dapat login seperti biasa.', 'attr') ?>">
                <?= csrf_field() ?>
                <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold <?= $isActive ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' ?> transition-colors">
                    <?php if ($isActive): ?>
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        Nonaktifkan
                    <?php else: ?>
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Aktifkan
                    <?php endif; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
