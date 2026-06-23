<?php
/**
 * @var string $entriesId
 * @var string $entriesInfoId
 * @var string $paginationId
 * @var string $prevPageId
 * @var string $nextPageId
 * @var string $pageInfoId
 */
$entriesId     = $entriesId ?? 'entriesSelect';
$entriesInfoId = $entriesInfoId ?? 'entriesInfo';
$paginationId  = $paginationId ?? 'tablePagination';
$prevPageId    = $prevPageId ?? 'prevPageBtn';
$nextPageId    = $nextPageId ?? 'nextPageBtn';
$pageInfoId    = $pageInfoId ?? 'pageInfo';
$entryOptions  = $entryOptions ?? null;
?>
<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between px-4 py-3 border-t border-slate-100">
    <div class="flex items-center gap-2 text-sm text-slate-600">
        <label for="<?= esc($entriesId) ?>" class="whitespace-nowrap">Tampilkan</label>
        <select id="<?= esc($entriesId) ?>" class="entries-select">
            <?php if ($entryOptions === null): ?>
                <option value="5">5</option>
                <option value="10" selected>10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            <?php else: ?>
                <?php foreach ($entryOptions as $opt): ?>
                    <option value="<?= esc((string) ($opt['value'] ?? '10')) ?>" <?= !empty($opt['selected']) ? 'selected' : '' ?>>
                        <?= esc((string) ($opt['label'] ?? $opt['value'])) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <span class="whitespace-nowrap">data</span>
    </div>
    <p id="<?= esc($entriesInfoId) ?>" class="text-xs text-slate-500"></p>
</div>

<div id="<?= esc($paginationId) ?>" class="hidden items-center justify-between px-4 py-3 border-t border-slate-100">
    <button type="button" id="<?= esc($prevPageId) ?>" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
        ← Sebelumnya
    </button>
    <span id="<?= esc($pageInfoId) ?>" class="text-xs text-slate-500"></span>
    <button type="button" id="<?= esc($nextPageId) ?>" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
        Selanjutnya →
    </button>
</div>
