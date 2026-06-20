<?php
/**
 * @var int    $idPelanggan
 * @var string $namaPerusahaan
 * @var string $namaPelanggan
 * @var bool   $canPromote
 * @var bool   $isTerpercaya
 * @var bool   $isVerifiedPerusahaan
 */
$idPelanggan            = (int) ($idPelanggan ?? 0);
$namaPerusahaan         = (string) ($namaPerusahaan ?? '');
$namaPelanggan          = (string) ($namaPelanggan ?? '');
$canPromote             = (bool) ($canPromote ?? false);
$isTerpercaya           = (bool) ($isTerpercaya ?? false);
$isSuspended            = (bool) ($isSuspended ?? false);
$isVerifiedPerusahaan   = (bool) ($isVerifiedPerusahaan ?? false);
$labelPerusahaan = $namaPerusahaan !== '' ? $namaPerusahaan : $namaPelanggan;
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
        <?php if ($canPromote): ?>
            <form method="post"
                action="<?= esc(site_url('pengguna/' . $idPelanggan . '/promosikan')) ?>"
                role="menuitem"
                class="js-action-confirm-form"
                data-confirm-variant="accept"
                data-confirm-title="Promosikan ke Terpercaya?"
                data-confirm-message="Promosikan <?= esc($labelPerusahaan) ?> ke tier Terpercaya?">
                <?= csrf_field() ?>
                <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    Promosikan
                </button>
            </form>
        <?php endif; ?>

        <?php if ($isTerpercaya): ?>
            <form method="post"
                action="<?= esc(site_url('pengguna/' . $idPelanggan . '/demote')) ?>"
                role="menuitem"
                class="<?= $canPromote ? 'border-t border-slate-100 ' : '' ?>js-action-confirm-form"
                data-confirm-variant="warning"
                data-confirm-title="Turunkan ke Pemula?"
                data-confirm-message="Turunkan tier perusahaan <?= esc($labelPerusahaan) ?> ke Pemula?">
                <?= csrf_field() ?>
                <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-amber-700 hover:bg-amber-50 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    Turunkan ke Pemula
                </button>
            </form>
        <?php endif; ?>

        <?php if ($isVerifiedPerusahaan): ?>
        <form method="post"
            action="<?= esc(site_url('pengguna/' . $idPelanggan . '/suspend')) ?>"
            role="menuitem"
            class="<?= ($canPromote || $isTerpercaya) ? 'border-t border-slate-100 ' : '' ?>js-action-confirm-form"
            data-confirm-variant="<?= $isSuspended ? 'accept' : 'danger' ?>"
            data-confirm-title="<?= $isSuspended ? 'Cabut Suspend?' : 'Suspend Akun?' ?>"
            data-confirm-message="<?= $isSuspended
                ? 'Aktifkan kembali akun perusahaan ' . esc($labelPerusahaan) . ' (tier Pemula)?'
                : 'Suspend akun perusahaan ' . esc($labelPerusahaan) . '?' ?>">
            <?= csrf_field() ?>
            <button type="submit"
                class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold <?= $isSuspended
                    ? 'text-emerald-700 hover:bg-emerald-50'
                    : 'text-red-600 hover:bg-red-50' ?> transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <?php if ($isSuspended): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    <?php endif; ?>
                </svg>
                <?= $isSuspended ? 'Cabut Suspend' : 'Suspend' ?>
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>
