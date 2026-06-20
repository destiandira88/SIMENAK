<?php
/**
 * @var int    $idOrder
 * @var string $kodeOrder
 * @var bool   $bisaUpload
 * @var string $status
 * @var bool   $readOnly
 */
$idOrder    = (int) ($idOrder ?? 0);
$kodeOrder  = (string) ($kodeOrder ?? '');
$bisaUpload = (bool) ($bisaUpload ?? false);
$status     = (string) ($status ?? '');
$readOnly   = (bool) ($readOnly ?? false);
$uploadUrl  = site_url('manajemen-desain/' . $idOrder);
$historyUrl = site_url('manajemen-desain/' . $idOrder . '#history');
$detailUrl  = site_url('order/detail/' . $kodeOrder);
$updateUrl  = site_url('produksi/update-status');
?>
<div class="action-menu relative inline-block">
    <button
        type="button"
        class="action-menu-btn"
        aria-label="Menu aksi"
        aria-expanded="false"
        data-action-toggle>
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="5" r="1.75" />
            <circle cx="12" cy="12" r="1.75" />
            <circle cx="12" cy="19" r="1.75" />
        </svg>
    </button>
    <div class="action-dropdown hidden" role="menu">
        <?php if ($readOnly): ?>
        <a href="<?= esc($detailUrl) ?>" role="menuitem">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            Detail Pesanan
        </a>
        <a href="<?= esc($uploadUrl) ?>" role="menuitem">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Lihat Draft & Revisi
        </a>
        <a href="<?= esc(site_url('revisi/history/' . $kodeOrder)) ?>" role="menuitem">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Approval History
        </a>
        <?php else: ?>
        <a href="<?= esc($uploadUrl) ?>" role="menuitem">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Workspace / Upload
        </a>
        <a href="<?= esc($historyUrl) ?>" role="menuitem">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            History Revisi
        </a>
        <?php if ($status === 'proses_cetak'): ?>
            <form method="post"
                action="<?= esc($updateUrl) ?>"
                role="menuitem"
                class="border-t border-slate-100 js-action-confirm-form"
                data-confirm-variant="status-finishing"
                data-confirm-kode="<?= esc($kodeOrder) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id_order" value="<?= esc((string) $idOrder) ?>">
                <input type="hidden" name="new_status" value="finishing">
                <button type="submit"
                    class="flex w-full items-center gap-2 px-3 py-2 text-[13px] font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Cetak Selesai
                </button>
            </form>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
