<script>
    (function() {
        const config = window.adminDataTableConfig || {};
        const searchInput = document.getElementById(config.searchId || 'tableSearchInput');
        const filterStatus = document.getElementById(config.filterId || 'tableFilterStatus');
        const entriesSelect = document.getElementById(config.entriesId || 'entriesSelect');
        const entriesInfo = document.getElementById(config.entriesInfoId || 'entriesInfo');
        const tbody = document.getElementById(config.tbodyId || 'tableBody');
        const emptyFilterRow = document.getElementById(config.emptyFilterRowId || 'emptyFilterRow');
        const tablePagination = document.getElementById(config.paginationId || 'tablePagination');
        const prevPageBtn = document.getElementById(config.prevPageId || 'prevPageBtn');
        const nextPageBtn = document.getElementById(config.nextPageId || 'nextPageBtn');
        const pageInfo = document.getElementById(config.pageInfoId || 'pageInfo');
        const rowSelector = config.rowSelector || 'tr.data-table-row';

        let currentPage = 1;

        function closeAllActionMenus(exceptBtn) {
            document.querySelectorAll('[data-action-toggle]').forEach((btn) => {
                if (btn === exceptBtn) return;
                btn.classList.remove('is-open');
                btn.setAttribute('aria-expanded', 'false');
                btn.closest('.action-menu')?.querySelector('.action-dropdown')?.classList.add('hidden');
            });
        }

        document.querySelectorAll('[data-action-toggle]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdown = btn.closest('.action-menu')?.querySelector('.action-dropdown');
                const isOpen = !dropdown?.classList.contains('hidden');
                closeAllActionMenus();
                if (!isOpen && dropdown) {
                    dropdown.classList.remove('hidden');
                    btn.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.addEventListener('click', () => closeAllActionMenus());

        function getRows() {
            return tbody ? [...tbody.querySelectorAll(rowSelector)] : [];
        }

        function rowMatches(row) {
            const q = (searchInput?.value || '').trim().toLowerCase();
            const status = filterStatus?.value || '';
            const matchSearch = q === '' || (row.dataset.search || '').includes(q);
            let matchFilter = true;

            if (status !== '' && typeof config.matchFilter === 'function') {
                matchFilter = config.matchFilter(status, row.dataset.status || '');
            } else if (status !== '') {
                matchFilter = (row.dataset.status || '') === status;
            }

            let matchTab = true;
            if (typeof config.getTabFilter === 'function') {
                matchTab = config.getTabFilter(row);
            }

            return matchSearch && matchFilter && matchTab;
        }

        function applyTableState() {
            const rows = getRows();
            if (!rows.length || !tbody) return;

            const matchedRows = rows.filter(rowMatches);
            const pageSize = parseInt(entriesSelect?.value || '10', 10);
            const totalMatched = matchedRows.length;
            const totalPages = Math.max(1, Math.ceil(totalMatched / pageSize));

            if (currentPage > totalPages) currentPage = totalPages;

            const startIndex = (currentPage - 1) * pageSize;
            const endIndex = startIndex + pageSize;

            rows.forEach((row) => { row.style.display = 'none'; });
            matchedRows.forEach((row, index) => {
                row.style.display = (index >= startIndex && index < endIndex) ? '' : 'none';
            });

            if (emptyFilterRow) {
                emptyFilterRow.classList.toggle('hidden', totalMatched > 0);
            }

            let visibleNum = startIndex + 1;
            matchedRows.slice(startIndex, endIndex).forEach((row) => {
                const cell = row.querySelector('.row-num');
                if (cell) cell.textContent = String(visibleNum++);
            });

            if (entriesInfo) {
                if (totalMatched === 0) {
                    entriesInfo.textContent = 'Showing 0 entries';
                } else {
                    const from = startIndex + 1;
                    const to = Math.min(endIndex, totalMatched);
                    entriesInfo.textContent = `Showing ${from} to ${to} of ${totalMatched} entries`;
                }
            }

            if (tablePagination) {
                const showPagination = totalMatched > pageSize;
                tablePagination.classList.toggle('hidden', !showPagination);
                tablePagination.classList.toggle('flex', showPagination);
            }

            if (pageInfo) {
                pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
            }

            if (prevPageBtn) prevPageBtn.disabled = currentPage <= 1;
            if (nextPageBtn) nextPageBtn.disabled = currentPage >= totalPages;
        }

        [searchInput, filterStatus].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', () => { currentPage = 1; applyTableState(); });
            el.addEventListener('change', () => { currentPage = 1; applyTableState(); });
        });

        if (entriesSelect) {
            entriesSelect.addEventListener('change', () => { currentPage = 1; applyTableState(); });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', () => {
                if (currentPage > 1) { currentPage--; applyTableState(); }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', () => {
                currentPage++;
                applyTableState();
            });
        }

        if (typeof config.onReady === 'function') {
            config.onReady({ applyTableState, setPage: (p) => { currentPage = p; } });
        }

        applyTableState();
    })();
</script>
