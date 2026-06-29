<script>
(function () {
    if (window.__simenakPelangganAkunFieldsInit) {
        return;
    }
    window.__simenakPelangganAkunFieldsInit = true;

    const phonePattern = /^(\+62|08|022)[0-9]{8,13}$/;
    const namaLengkapPattern = /^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*$/u;

    window.normalizePelangganPhoneInput = function (input) {
        if (!input) {
            return;
        }

        let v = input.value.replace(/[^\d+]/g, '');
        if (v.includes('+')) {
            v = '+' + v.replace(/\+/g, '');
        }

        if (v.startsWith('+62')) {
            input.value = '+62' + v.slice(3).replace(/\D/g, '').slice(0, 13);
            return;
        }

        if (v.startsWith('022')) {
            input.value = '022' + v.slice(3).replace(/\D/g, '').slice(0, 13);
            return;
        }

        if (v.startsWith('08')) {
            input.value = '08' + v.slice(2).replace(/\D/g, '').slice(0, 13);
            return;
        }

        if (v.startsWith('+6')) {
            input.value = v.slice(0, 16);
            return;
        }

        if (v.startsWith('0')) {
            input.value = v.slice(0, 4);
            return;
        }

        input.value = v.slice(0, 2);
    };

    window.bindPelangganPhoneInputs = function (root) {
        (root || document).querySelectorAll('.js-pelanggan-phone-input').forEach(function (input) {
            if (input.dataset.phoneBound === '1') {
                return;
            }
            input.dataset.phoneBound = '1';
            input.addEventListener('input', function () {
                window.normalizePelangganPhoneInput(input);
            });
        });
    };

    window.validatePelangganAkunFormFields = function (form) {
        const nama = form.querySelector('[name="nama"]')?.value.trim() || '';
        if (nama.length < 3 || nama.length > 100 || !namaLengkapPattern.test(nama)) {
            return 'Nama lengkap hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter).';
        }

        const phone = form.querySelector('[name="no_telp"]')?.value.trim() || '';
        if (!phonePattern.test(phone)) {
            return 'Format no. telepon harus berupa angka dan diawali dengan 08, +62, atau 022 (Contoh: 087778965442) (8–13 digit setelah awalan).';
        }

        const alamatEl = form.querySelector('[name="alamat"]');
        if (alamatEl) {
            const alamat = alamatEl.value.trim();
            if (alamat.length < 10 || alamat.length > 150) {
                return 'Alamat wajib diisi (minimal 10 karakter, maks. 150 karakter).';
            }
        }

        return null;
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.bindPelangganPhoneInputs(document);
    });
})();
</script>
