<?php if (!session()->get('isLoggedIn')) {
    return;
} ?>
<style>
    #logoutModal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    #logoutModal.is-open {
        opacity: 1;
    }

    #logoutModal .logout-modal-panel {
        transform: scale(.95);
        opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
    }

    #logoutModal.is-open .logout-modal-panel {
        transform: scale(1);
        opacity: 1;
    }

    #logoutModal .logout-modal-card:hover {
        transform: none;
    }

    .btn-logout-cancel {
        background: #fff;
        color: #64748B;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-weight: 600;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-logout-cancel:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #475569;
    }

    .btn-logout-confirm {
        background: #EF4444;
        color: #fff;
        border-radius: 14px;
        font-weight: 700;
        transition: background-color .2s ease;
    }

    .btn-logout-confirm:hover {
        background: #DC2626;
    }
</style>

<form id="logoutForm" action="<?= site_url('logout') ?>" method="post" class="hidden">
    <?= csrf_field() ?>
</form>

<div id="logoutModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-logout-modal></div>
    <div class="logout-modal-panel logout-modal-card relative w-full max-w-md bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg p-6 md:p-8 z-10">
        <div class="text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 border border-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
            <h4 id="logoutModalTitle" class="text-xl md:text-2xl font-extrabold text-[#051747] leading-tight">Logout dari Portal?</h4>
            <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                Apakah Anda yakin ingin keluar dari akun SIMENAK saat ini?
            </p>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="button" data-close-logout-modal class="btn-logout-cancel flex-1 h-11 text-sm">
                Cancel
            </button>
            <button type="button" id="logoutConfirmBtn" class="btn-logout-confirm flex-1 h-11 text-sm">
                Yes, Logout
            </button>
        </div>
    </div>
</div>

<script>
    (() => {
        const logoutModal = document.getElementById('logoutModal');
        const logoutForm = document.getElementById('logoutForm');
        const logoutConfirmBtn = document.getElementById('logoutConfirmBtn');

        if (!logoutModal || !logoutForm) {
            return;
        }

        let closeTimer = null;

        const openLogoutModal = () => {
            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }

            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(() => {
                logoutModal.classList.add('is-open');
            });
        };

        const closeLogoutModal = () => {
            logoutModal.classList.remove('is-open');

            closeTimer = setTimeout(() => {
                logoutModal.classList.add('hidden');
                logoutModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                closeTimer = null;
            }, 250);
        };

        document.querySelectorAll('[data-open-logout-modal]').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openLogoutModal();
            });
        });

        document.querySelectorAll('[data-close-logout-modal]').forEach((btn) => {
            btn.addEventListener('click', closeLogoutModal);
        });

        if (logoutConfirmBtn) {
            logoutConfirmBtn.addEventListener('click', () => {
                logoutForm.submit();
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && logoutModal.classList.contains('is-open')) {
                closeLogoutModal();
            }
        });
    })();
</script>