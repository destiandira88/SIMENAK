<?php

/**
 * @var string $dropdownId
 * @var string $csvUrl
 * @var string $pdfUrl
 */
$dropdownId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($dropdownId ?? 'laporanExport'));
?>
<div class="laporan-export-menu relative" data-export-menu="<?= esc($dropdownId) ?>">
    <button
        type="button"
        class="btn-laporan-export inline-flex items-center gap-2 rounded-[14px] border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-50"
        title="Ekspor laporan"
        aria-label="Ekspor laporan"
        aria-haspopup="true"
        aria-expanded="false"
        aria-controls="<?= esc($dropdownId) ?>Menu"
        data-export-toggle="<?= esc($dropdownId) ?>">
        Ekspor
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="w-4 h-4 shrink-0" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M7 10l5 5m0 0l5-5m-5 5V4" />
        </svg>
    </button>
    <div
        id="<?= esc($dropdownId) ?>Menu"
        class="laporan-export-dropdown hidden absolute right-0 top-[calc(100%+6px)] z-[120] min-w-[148px] rounded-xl border border-[#E2E8F0] bg-white p-1.5 shadow-lg"
        role="menu"
        aria-label="Pilihan format ekspor">
        <a
            href="<?= esc($csvUrl) ?>"
            class="laporan-export-item flex items-center gap-2 rounded-lg px-2.5 py-2 text-xs font-semibold text-[#051747] transition-colors hover:bg-[#F0F2F8]"
            role="menuitem">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 text-slate-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            CSV
        </a>
        <a
            href="<?= esc($pdfUrl) ?>"
            class="laporan-export-item flex items-center gap-2 rounded-lg px-2.5 py-2 text-xs font-semibold text-[#051747] transition-colors hover:bg-[#F0F2F8]"
            role="menuitem">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 text-slate-500" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            PDF
        </a>
    </div>
</div>

<script>
(function () {
    if (window.__laporanExportMenuInit) {
        return;
    }
    window.__laporanExportMenuInit = true;

    function closeAllExportMenus() {
        document.querySelectorAll('.laporan-export-dropdown').forEach(function (el) {
            el.classList.add('hidden');
        });
        document.querySelectorAll('[data-export-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-expanded', 'false');
        });
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-export-toggle]');
        if (toggle) {
            event.preventDefault();
            event.stopPropagation();
            const id = toggle.getAttribute('data-export-toggle');
            const menu = document.getElementById(id + 'Menu');
            if (!menu) {
                return;
            }
            const isOpen = !menu.classList.contains('hidden');
            closeAllExportMenus();
            if (!isOpen) {
                menu.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }
            return;
        }
        if (!event.target.closest('.laporan-export-menu')) {
            closeAllExportMenus();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAllExportMenus();
        }
    });
})();
</script>
