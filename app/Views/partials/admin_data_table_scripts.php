<script>
    (function() {
        const config = window.adminDataTableConfig || {};
        const searchInput = document.getElementById(config.searchId || 'tableSearchInput');
        const filterStatus = document.getElementById(config.filterId || 'tableFilterStatus');
        const dateFromInput = config.dateFromId ? document.getElementById(config.dateFromId) : null;
        const dateToInput = config.dateToId ? document.getElementById(config.dateToId) : null;
        const entriesSelect = document.getElementById(config.entriesId || 'entriesSelect');
        const entriesInfo = document.getElementById(config.entriesInfoId || 'entriesInfo');
        const tbody = document.getElementById(config.tbodyId || 'tableBody');
        const emptyFilterRow = document.getElementById(config.emptyFilterRowId || 'emptyFilterRow');
        const tablePagination = document.getElementById(config.paginationId || 'tablePagination');
        const prevPageBtn = document.getElementById(config.prevPageId || 'prevPageBtn');
        const nextPageBtn = document.getElementById(config.nextPageId || 'nextPageBtn');
        const pageInfo = document.getElementById(config.pageInfoId || 'pageInfo');
        const rowSelector = config.rowSelector || 'tr.data-table-row';
        const tableEl = tbody?.closest('table');
        const sortableHeaders = config.enableSort && tableEl
            ? tableEl.querySelectorAll('.sortable-th')
            : [];

        let currentPage = 1;
        let sortColumn = null;
        let sortDir = 'asc';

        function resetActionDropdown(dropdown) {
            if (!dropdown) return;
            dropdown.classList.add('hidden');
            dropdown.style.position = '';
            dropdown.style.top = '';
            dropdown.style.left = '';
            dropdown.style.right = '';
            dropdown.style.zIndex = '';
        }

        function positionActionDropdown(btn, dropdown) {
            dropdown.classList.remove('hidden');
            dropdown.style.position = 'fixed';
            dropdown.style.zIndex = '60';

            const rect = btn.getBoundingClientRect();
            const menuWidth = dropdown.offsetWidth || 168;
            let left = rect.right - menuWidth;
            let top = rect.bottom + 6;

            if (left < 8) left = 8;
            if (left + menuWidth > window.innerWidth - 8) {
                left = Math.max(8, window.innerWidth - menuWidth - 8);
            }

            const menuHeight = dropdown.offsetHeight || 80;
            if (top + menuHeight > window.innerHeight - 8) {
                top = Math.max(8, rect.top - menuHeight - 6);
            }

            dropdown.style.left = left + 'px';
            dropdown.style.top = top + 'px';
        }

        function closeAllActionMenus(exceptBtn) {
            document.querySelectorAll('[data-action-toggle]').forEach((btn) => {
                if (btn === exceptBtn) return;
                btn.classList.remove('is-open');
                btn.setAttribute('aria-expanded', 'false');
                resetActionDropdown(btn.closest('.action-menu')?.querySelector('.action-dropdown'));
            });
        }

        document.querySelectorAll('[data-action-toggle]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const dropdown = btn.closest('.action-menu')?.querySelector('.action-dropdown');
                if (!dropdown) return;

                const isOpen = !dropdown.classList.contains('hidden');
                closeAllActionMenus(btn);

                if (isOpen) {
                    resetActionDropdown(dropdown);
                    btn.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    positionActionDropdown(btn, dropdown);
                    btn.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.querySelectorAll('.action-dropdown').forEach((dropdown) => {
            dropdown.addEventListener('click', (e) => e.stopPropagation());
        });

        document.addEventListener('click', () => closeAllActionMenus());
        window.addEventListener('scroll', () => closeAllActionMenus(), true);
        window.addEventListener('resize', () => closeAllActionMenus());

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

            let matchDate = true;
            const dateFrom = dateFromInput?.value || '';
            const dateTo = dateToInput?.value || '';
            if (dateFrom !== '' || dateTo !== '') {
                const ts = parseInt(row.dataset.tanggal || '0', 10);
                if (ts <= 0) {
                    matchDate = false;
                } else {
                    if (dateFrom !== '') {
                        const fromTs = Math.floor(new Date(dateFrom + 'T00:00:00').getTime() / 1000);
                        if (ts < fromTs) matchDate = false;
                    }
                    if (dateTo !== '' && matchDate) {
                        const toTs = Math.floor(new Date(dateTo + 'T23:59:59').getTime() / 1000);
                        if (ts > toTs) matchDate = false;
                    }
                }
            }

            return matchSearch && matchFilter && matchTab && matchDate;
        }

        function getSortValue(row, col) {
            switch (col) {
                case 'kode':
                    return (row.dataset.kode || '').toLowerCase();
                case 'produk':
                    return (row.dataset.produk || '').toLowerCase();
                case 'tanggal':
                    return parseInt(row.dataset.tanggal || '0', 10);
                case 'tipe':
                    return (row.dataset.tipe || '').toLowerCase();
                case 'total':
                    return parseFloat(row.dataset.total || '0');
                case 'status':
                    return (row.dataset.status || '').toLowerCase();
                case 'pelanggan':
                    return (row.dataset.pelanggan || '').toLowerCase();
                case 'jenis':
                    return (row.dataset.jenis || '').toLowerCase();
                case 'metode':
                    return (row.dataset.metode || '').toLowerCase();
                case 'kategori':
                    return (row.dataset.kategori || '').toLowerCase();
                case 'selesai':
                    return parseInt(row.dataset.selesai || '0', 10);
                case 'pesan':
                    return parseInt(row.dataset.pesan || '0', 10);
                case 'deadline':
                    return parseInt(row.dataset.deadline || '0', 10);
                case 'kodebayar':
                    return (row.dataset.kodebayar || '').toLowerCase();
                case 'kodeorder':
                    return (row.dataset.kodeorder || '').toLowerCase();
                case 'nominal':
                    return parseFloat(row.dataset.nominal || '0');
                case 'upload':
                    return parseInt(row.dataset.upload || '0', 10);
                case 'verifikasi':
                    return parseInt(row.dataset.verifikasi || '0', 10);
                default: {
                    const raw = row.dataset[col];
                    if (raw === undefined) return '';
                    if (/^-?\d+(\.\d+)?$/.test(raw)) {
                        return raw.includes('.') ? parseFloat(raw) : parseInt(raw, 10);
                    }
                    return String(raw).toLowerCase();
                }
            }
        }

        function compareRows(a, b) {
            const va = getSortValue(a, sortColumn);
            const vb = getSortValue(b, sortColumn);

            if (typeof va === 'number' && typeof vb === 'number') {
                if (va < vb) return sortDir === 'asc' ? -1 : 1;
                if (va > vb) return sortDir === 'asc' ? 1 : -1;
                return 0;
            }

            const sa = String(va);
            const sb = String(vb);
            if (sa < sb) return sortDir === 'asc' ? -1 : 1;
            if (sa > sb) return sortDir === 'asc' ? 1 : -1;
            return 0;
        }

        function updateSortIcons() {
            sortableHeaders.forEach((th) => {
                const col = th.dataset.sort;
                const icon = th.querySelector('.sort-icon');
                th.classList.toggle('is-sorted', col === sortColumn);
                if (!icon) return;
                icon.textContent = col !== sortColumn ? '↕' : (sortDir === 'asc' ? '↑' : '↓');
            });
        }

        function applyTableState() {
            closeAllActionMenus();

            const rows = getRows();
            if (!rows.length || !tbody) return;

            if (config.enableSort && sortColumn) {
                rows.sort(compareRows);
                rows.forEach((row) => {
                    tbody.insertBefore(row, emptyFilterRow || null);
                });
            }

            const matchedRows = rows.filter(rowMatches);
            const rawPageSize = parseInt(entriesSelect?.value || '10', 10);
            const pageSize = rawPageSize <= 0 ? Math.max(matchedRows.length, 1) : rawPageSize;
            const totalMatched = matchedRows.length;
            const totalPages = rawPageSize <= 0 ? 1 : Math.max(1, Math.ceil(totalMatched / pageSize));

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
                    entriesInfo.textContent = 'Menampilkan 0 data';
                } else {
                    const from = startIndex + 1;
                    const to = Math.min(endIndex, totalMatched);
                    entriesInfo.textContent = `Menampilkan ${from} sampai ${to} dari ${totalMatched} data`;
                }
            }

            if (tablePagination) {
                const showPagination = rawPageSize > 0 && totalMatched > pageSize;
                tablePagination.classList.toggle('hidden', !showPagination);
                tablePagination.classList.toggle('flex', showPagination);
            }

            if (pageInfo) {
                pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
            }

            if (prevPageBtn) prevPageBtn.disabled = currentPage <= 1;
            if (nextPageBtn) nextPageBtn.disabled = currentPage >= totalPages;

            if (config.enableSort) {
                updateSortIcons();
            }
        }

        [searchInput, filterStatus, dateFromInput, dateToInput].forEach((el) => {
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

        sortableHeaders.forEach((th) => {
            th.addEventListener('click', () => {
                const col = th.dataset.sort;
                if (!col) return;

                if (sortColumn === col) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = col;
                    sortDir = 'asc';
                }

                updateSortIcons();
                currentPage = 1;
                applyTableState();
            });
        });

        if (typeof config.onReady === 'function') {
            config.onReady({
                applyTableState,
                setPage: (p) => { currentPage = p; },
                resetSort: () => {
                    sortColumn = null;
                    sortDir = 'asc';
                    updateSortIcons();
                },
            });
        }

        applyTableState();
    })();
</script>
