<script>
    (function() {
        const formatRupiahDisplay = (digits) => {
            if (!digits) {
                return '';
            }

            const num = parseInt(digits, 10);
            if (Number.isNaN(num) || num <= 0) {
                return '';
            }

            return 'Rp ' + num.toLocaleString('id-ID');
        };

        document.querySelectorAll('.js-harga-custom-rupiah-input').forEach((input) => {
            const applyFormat = () => {
                const digits = input.value.replace(/\D/g, '').replace(/^0+/, '');
                const formatted = formatRupiahDisplay(digits);
                input.value = formatted;

                if (formatted) {
                    input.setSelectionRange(formatted.length, formatted.length);
                }
            };

            input.addEventListener('input', applyFormat);
            input.addEventListener('blur', applyFormat);
        });
    })();
</script>
