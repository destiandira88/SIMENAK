<?php helper('deadline'); ?>
<script>
    (function() {
        /** Tanggal hari ini (WIB) dari server — konsisten dengan PHP app_timezone Asia/Jakarta. */
        const todayYmd = <?= json_encode(todayYmdApp(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        const addDaysYmd = (ymd, days) => {
            const parts = ymd.split('-').map(Number);
            if (parts.length !== 3) {
                return ymd;
            }
            const [y, m, d] = parts;
            const dt = new Date(Date.UTC(y, m - 1, d));
            dt.setUTCDate(dt.getUTCDate() + days);
            return dt.getUTCFullYear() + '-'
                + String(dt.getUTCMonth() + 1).padStart(2, '0') + '-'
                + String(dt.getUTCDate()).padStart(2, '0');
        };

        const isWeekdayYmd = (ymd) => {
            const parts = ymd.split('-').map(Number);
            if (parts.length !== 3) {
                return false;
            }
            const [y, m, d] = parts;
            const dow = new Date(Date.UTC(y, m - 1, d)).getUTCDay();
            return dow >= 1 && dow <= 5;
        };

        /** Sen–Jum dari besok (today+1) sampai deadline inklusif; hari ini tidak dihitung. */
        const countHariKerjaSampaiDeadline = (deadlineYmd) => {
            if (!deadlineYmd || deadlineYmd < todayYmd) {
                return 0;
            }
            let count = 0;
            let cur = addDaysYmd(todayYmd, 1);
            while (cur <= deadlineYmd) {
                if (isWeekdayYmd(cur)) {
                    count++;
                }
                cur = addDaysYmd(cur, 1);
            }
            return count;
        };

        const formatEstimasiLabel = (hari) => hari + ' hari kerja';

        const syncEstimasi = (form) => {
            const deadlineInput = form.querySelector('[data-deadline-produksi]');
            const estimasiHidden = form.querySelector('[data-estimasi-hidden]');
            const estimasiHariHidden = form.querySelector('[data-estimasi-hari-value]');
            const estimasiTextEl = form.querySelector('[data-estimasi-text]');
            if (!deadlineInput || !estimasiHidden) {
                return;
            }

            const hari = countHariKerjaSampaiDeadline(deadlineInput.value);
            const estimasiText = formatEstimasiLabel(hari);

            estimasiHidden.value = estimasiText;
            if (estimasiHariHidden) {
                estimasiHariHidden.value = String(hari);
            }
            if (estimasiTextEl) {
                estimasiTextEl.textContent = estimasiText;
            }
        };

        const bindForm = (form) => {
            const deadlineInput = form.querySelector('[data-deadline-produksi]');
            if (!deadlineInput) {
                return;
            }
            syncEstimasi(form);
            deadlineInput.addEventListener('change', () => syncEstimasi(form));
            deadlineInput.addEventListener('input', () => syncEstimasi(form));
        };

        document.querySelectorAll('.js-custom-set-harga-form').forEach(bindForm);
    })();
</script>
