<style>
    .filter-select,
    .search-control {
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        color: #4A5568;
        background-color: #fff;
        transition: border-color .2s, box-shadow .2s;
    }

    .filter-select {
        padding: 8px 32px 8px 12px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%238896A5' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 14px;
        appearance: none;
        min-width: 130px;
    }

    .search-control {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        min-width: 200px;
        max-width: 280px;
    }

    .search-control:focus-within {
        border-color: #2E5CE6;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }

    .search-control input {
        min-width: 0;
        flex: 1;
        border: 0;
        background: transparent;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        color: #4A5568;
        outline: none;
    }

    .search-control input::placeholder {
        color: #8896A5;
    }

    .filter-select:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }

    .entries-select {
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        padding: 6px 28px 6px 10px;
        color: #4A5568;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%238896A5' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E") no-repeat right 8px center;
        background-size: 14px;
        appearance: none;
    }

    .entries-select:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }

    .action-menu-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        border: 1.5px solid #E2E8F0;
        background: #fff;
        color: #4A5568;
        transition: background .2s, border-color .2s, color .2s;
    }

    .action-menu-btn:hover,
    .action-menu-btn.is-open {
        background: #F8FAFF;
        border-color: #2E5CE6;
        color: #051747;
    }

    .action-dropdown {
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        z-index: 30;
        min-width: 168px;
        padding: 6px;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        box-shadow: 0 12px 40px rgba(15, 23, 43, .12);
    }

    .action-dropdown a,
    .action-dropdown button {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        color: #4A5568;
        text-align: left;
        transition: background .15s, color .15s;
    }

    .action-dropdown a:hover,
    .action-dropdown button:hover {
        background: #F8FAFF;
        color: #051747;
    }

    .list-pemesanan-date-range {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .list-pemesanan-date-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748B;
        white-space: nowrap;
    }

    .list-pemesanan-date-sep {
        font-size: 12px;
        font-weight: 600;
        color: #94A3B8;
        user-select: none;
    }

    .list-pemesanan-date-input {
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        padding: 8px 12px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        color: #4A5568;
        background-color: #fff;
        min-width: 130px;
        transition: border-color .2s, box-shadow .2s;
    }

    .list-pemesanan-date-input:focus {
        border-color: #2E5CE6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(46, 92, 230, .1);
    }

    .sortable-th {
        cursor: pointer;
        user-select: none;
        white-space: nowrap;
    }

    .sortable-th:hover {
        background: rgba(255, 255, 255, 0.06);
    }

    .sort-icon {
        display: inline-block;
        margin-left: 4px;
        font-size: 10px;
        opacity: 0.7;
    }

    .sortable-th.is-sorted .sort-icon {
        opacity: 1;
    }
</style>
