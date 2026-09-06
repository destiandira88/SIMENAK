<style>
    .produksi-status-badge-btn {
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: box-shadow .15s ease, border-color .15s ease, opacity .15s ease;
    }

    .produksi-status-badge-btn--editable {
        border-style: dashed;
        border-color: rgba(91, 33, 182, 0.45);
        padding-right: 0.5rem;
    }

    .produksi-status-badge-btn--editable:hover:not(:disabled):not(.is-loading) {
        box-shadow: 0 0 0 2px rgba(91, 33, 182, 0.2);
        border-color: rgba(91, 33, 182, 0.55);
    }

    .produksi-status-badge-chevron {
        flex-shrink: 0;
        opacity: 0.7;
        transition: transform .15s ease, opacity .15s ease;
    }

    .produksi-status-badge-btn--editable:hover:not(:disabled):not(.is-loading) .produksi-status-badge-chevron,
    .produksi-status-badge-btn--editable[aria-expanded="true"] .produksi-status-badge-chevron {
        opacity: 1;
    }

    .produksi-status-badge-btn--editable[aria-expanded="true"] .produksi-status-badge-chevron {
        transform: rotate(180deg);
    }

    .produksi-status-badge-btn.is-loading {
        cursor: wait;
        opacity: 0.75;
    }

    .produksi-status-quick-menu {
        position: fixed;
        z-index: 70;
        display: none;
        min-width: 11rem;
        padding: 6px;
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 12px 40px rgba(15, 23, 43, 0.15);
    }

    .produksi-status-quick-menu.is-open {
        display: block;
    }

    .produksi-status-quick-option {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 0.5rem;
        padding: 8px 10px;
        border: none;
        border-radius: 8px;
        background: transparent;
        cursor: pointer;
        transition: background .15s ease;
    }

    .produksi-status-quick-option:hover {
        background: #F8FAFF;
    }
</style>
