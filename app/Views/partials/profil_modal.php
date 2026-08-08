<?php
/** @var array<string, mixed>|null $profilData */
if (! is_array($profilData ?? null)) {
    return;
}

$user              = is_array($profilData['user'] ?? null) ? $profilData['user'] : [];
$pelanggan         = is_array($profilData['pelanggan'] ?? null) ? $profilData['pelanggan'] : [];
$verifikasiTerbaru = is_array($profilData['verifikasiTerbaru'] ?? null) ? $profilData['verifikasiTerbaru'] : null;
helper('notification');
$isKerjasamaProfil = pelangganIsKerjasamaPerusahaan($pelanggan);
?>
<style>
    #profilModal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    #profilModal.is-open {
        opacity: 1;
    }

    #profilModal .profil-modal-panel {
        transform: translateY(12px) scale(.98);
        opacity: 0;
        transition: transform .28s cubic-bezier(.22, 1, .36, 1), opacity .28s ease;
    }

    #profilModal.is-open .profil-modal-panel {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    #userMenuDropdown {
        transform-origin: top right;
        animation: userMenuIn .18s ease;
    }

    @keyframes userMenuIn {
        from {
            opacity: 0;
            transform: translateY(-4px) scale(.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
</style>

<div id="profilModal"
    class="fixed inset-0 z-[85] hidden items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    aria-labelledby="profilModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-profil-modal></div>
    <div class="profil-modal-panel relative z-10 flex w-full max-w-3xl max-h-[90vh] flex-col overflow-hidden rounded-[24px] border border-[#E2E8F0] bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-[#E2E8F0] px-5 py-4 sm:px-6">
            <div>
                <h4 id="profilModalTitle" class="text-lg sm:text-xl font-extrabold text-[#051747]">Profil Saya</h4>
                <p class="mt-0.5 text-sm text-slate-500"><?= $isKerjasamaProfil ? 'Kelola data akun dan skema pembayaran' : 'Kelola data akun Anda' ?></p>
            </div>
            <button type="button"
                data-close-profil-modal
                class="shrink-0 rounded-full p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                aria-label="Tutup profil">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="overflow-y-auto px-5 py-5 sm:px-6 sm:py-6">
            <?= view('partials/profil_form_content', [
                'user'              => $user,
                'pelanggan'         => $pelanggan,
                'verifikasiTerbaru' => $verifikasiTerbaru,
                'orderLancarCount'  => (int) ($profilData['orderLancarCount'] ?? 0),
                'inModal'           => true,
            ]) ?>
        </div>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('profilModal');
        if (!modal) return;

        let closeTimer = null;

        const closeUserMenu = () => {
            if (typeof window.closeUserAccountMenu === 'function') {
                window.closeUserAccountMenu();
            }
        };

        const openProfilModal = () => {
            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }

            closeUserMenu();

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(() => {
                modal.classList.add('is-open');
            });
        };

        const closeProfilModal = () => {
            modal.classList.remove('is-open');

            closeTimer = setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                closeTimer = null;
            }, 280);
        };

        window.openProfilModal = openProfilModal;

        document.querySelectorAll('[data-open-profil-modal]').forEach((el) => {
            el.addEventListener('click', (event) => {
                event.preventDefault();
                openProfilModal();
            });
        });

        document.querySelectorAll('[data-close-profil-modal]').forEach((el) => {
            el.addEventListener('click', closeProfilModal);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeProfilModal();
            }
        });

        const shouldOpen = document.body.dataset.openProfil === '1'
            || new URLSearchParams(window.location.search).get('profil') === '1';

        if (shouldOpen) {
            openProfilModal();
            if (window.history.replaceState) {
                const url = new URL(window.location.href);
                url.searchParams.delete('profil');
                window.history.replaceState({}, '', url.pathname + url.search + url.hash);
            }
        }

        const formatNpwpValue = (raw) => {
            const digits = String(raw || '').replace(/\D/g, '').slice(0, 15);
            if (digits.length <= 2) return digits;
            if (digits.length <= 5) return digits.slice(0, 2) + '.' + digits.slice(2);
            if (digits.length <= 8) return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5);
            if (digits.length <= 9) {
                return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5, 8) + '.' + digits.slice(8);
            }
            if (digits.length <= 12) {
                return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5, 8) + '.'
                    + digits.slice(8, 9) + '-' + digits.slice(9);
            }

            return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5, 8) + '.'
                + digits.slice(8, 9) + '-' + digits.slice(9, 12) + '.' + digits.slice(12);
        };

        document.querySelectorAll('[data-npwp-input]').forEach((input) => {
            input.addEventListener('input', () => {
                const formatted = formatNpwpValue(input.value);
                input.value = formatted;
            });
        });

        const profilForm = document.getElementById('profilAkunForm');
        const profilAlert = document.getElementById('profilAkunAlert');
        const profilPhoneInput = document.getElementById('profil_no_telp');
        const phonePattern = /^(\+62|08|022)[0-9]{8,13}$/;
        const namaLengkapPattern = /^[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*$/u;

        const showProfilAlert = (message, isSuccess = false) => {
            if (!profilAlert) return;
            profilAlert.textContent = message;
            profilAlert.classList.remove('hidden', 'bg-[#FEE2E2]', 'text-[#991B1B]', 'bg-emerald-50', 'text-emerald-800');
            profilAlert.classList.add(isSuccess ? 'bg-emerald-50' : 'bg-[#FEE2E2]', isSuccess ? 'text-emerald-800' : 'text-[#991B1B]');
        };

        if (profilPhoneInput) {
            profilPhoneInput.addEventListener('input', () => {
                let value = profilPhoneInput.value.replace(/[^\d+]/g, '');
                if (value.includes('+')) {
                    value = '+' + value.replace(/\+/g, '');
                }
                profilPhoneInput.value = value.slice(0, 20);
            });
        }

        if (profilForm) {
            profilForm.addEventListener('submit', (event) => {
                const nama = document.getElementById('profil_nama')?.value.trim() || '';
                const phone = profilPhoneInput?.value.trim() || '';
                const alamat = document.getElementById('profil_alamat')?.value.trim() || '';

                if (nama.length < 3 || nama.length > 100 || !namaLengkapPattern.test(nama)) {
                    event.preventDefault();
                    showProfilAlert('Nama lengkap hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter).');
                    return;
                }

                if (!phonePattern.test(phone)) {
                    event.preventDefault();
                    showProfilAlert('Format no. telepon harus berupa angka dan diawali dengan 08, +62, atau 022 (Contoh: 087778965442) (8–13 digit setelah awalan).');
                    return;
                }

                if (alamat.length < 10 || alamat.length > 150) {
                    event.preventDefault();
                    showProfilAlert('Alamat wajib diisi (minimal 10 karakter, maks. 150 karakter).');
                }
            });
        }

        const passwordForm = document.getElementById('profilPasswordForm');
        const passwordAlert = document.getElementById('profilPasswordAlert');

        const showPasswordAlert = (message) => {
            if (!passwordAlert) return;
            passwordAlert.textContent = message;
            passwordAlert.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800');
            passwordAlert.classList.add('bg-[#FEE2E2]', 'text-[#991B1B]');
        };

        document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const inputId = btn.getAttribute('data-toggle-password');
                const input = inputId ? document.getElementById(inputId) : null;
                if (!input) return;
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });
        });

        if (passwordForm) {
            passwordForm.addEventListener('submit', (event) => {
                const lama = document.getElementById('profil_password_lama')?.value || '';
                const baru = document.getElementById('profil_password_baru')?.value || '';
                const confirm = document.getElementById('profil_password_confirm')?.value || '';

                if (!lama) {
                    event.preventDefault();
                    showPasswordAlert('Password saat ini wajib diisi.');
                    return;
                }

                if (baru.length < 8) {
                    event.preventDefault();
                    showPasswordAlert('Password baru minimal 8 karakter.');
                    return;
                }

                if (baru !== confirm) {
                    event.preventDefault();
                    showPasswordAlert('Konfirmasi password baru tidak sama.');
                    return;
                }

                if (baru === lama) {
                    event.preventDefault();
                    showPasswordAlert('Password baru harus berbeda dari password saat ini.');
                }
            });
        }
    })();
</script>
