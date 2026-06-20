<?php
static $actionConfirmModalRendered = false;
if ($actionConfirmModalRendered) {
    return;
}
$actionConfirmModalRendered = true;
?>
<style>
    #actionConfirmModal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    #actionConfirmModal.is-open {
        opacity: 1;
    }

    #actionConfirmModal .action-confirm-panel {
        transform: scale(.95);
        opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
    }

    #actionConfirmModal.is-open .action-confirm-panel {
        transform: scale(1);
        opacity: 1;
    }

    #actionConfirmModal .action-confirm-card:hover {
        transform: none;
    }

    .btn-action-confirm-cancel {
        background: #fff;
        color: #64748B;
        border: 1.5px solid #E2E8F0;
        border-radius: 14px;
        font-weight: 600;
        transition: background-color .2s ease, border-color .2s ease, color .2s ease;
    }

    .btn-action-confirm-cancel:hover {
        background: #F8FAFC;
        border-color: #CBD5E1;
        color: #475569;
    }

    .btn-action-confirm-submit {
        color: #fff;
        border-radius: 14px;
        font-weight: 700;
        transition: background-color .2s ease;
    }

    .btn-action-confirm-submit.variant-offer {
        background: #051747;
    }

    .btn-action-confirm-submit.variant-offer:hover {
        background: #2E5CE6;
    }

    .btn-action-confirm-submit.variant-accept,
    .btn-action-confirm-submit.variant-payment-accept {
        background: #22C55E;
    }

    .btn-action-confirm-submit.variant-accept:hover,
    .btn-action-confirm-submit.variant-payment-accept:hover {
        background: #16A34A;
    }

    .btn-action-confirm-submit.variant-reject,
    .btn-action-confirm-submit.variant-payment-reject {
        background: #EF4444;
    }

    .btn-action-confirm-submit.variant-reject:hover,
    .btn-action-confirm-submit.variant-payment-reject:hover {
        background: #DC2626;
    }

    .btn-action-confirm-submit.variant-status-finishing {
        background: #4F46E5;
    }

    .btn-action-confirm-submit.variant-status-finishing:hover {
        background: #4338CA;
    }
</style>

<div id="actionConfirmModal" class="fixed inset-0 z-[90] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="actionConfirmModalTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-action-confirm></div>
    <div class="action-confirm-panel action-confirm-card relative w-full max-w-md bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg p-6 md:p-8 z-10">
        <div class="text-center">
            <div id="actionConfirmIconWrap" class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 border border-blue-100">
                <span id="actionConfirmIcon" class="text-[#2E5CE6]" aria-hidden="true"></span>
            </div>
            <h4 id="actionConfirmModalTitle" class="text-xl md:text-2xl font-extrabold text-[#051747] leading-tight"></h4>
            <p id="actionConfirmModalMessage" class="mt-2 text-sm text-slate-500 leading-relaxed"></p>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="button" data-close-action-confirm class="btn-action-confirm-cancel flex-1 h-11 text-sm">
                Batal
            </button>
            <button type="button" id="actionConfirmSubmitBtn" class="btn-action-confirm-submit flex-1 h-11 text-sm">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
    (() => {
        if (window.__simenakActionConfirmInit) {
            return;
        }
        window.__simenakActionConfirmInit = true;

        const modal = document.getElementById('actionConfirmModal');
        const iconWrap = document.getElementById('actionConfirmIconWrap');
        const iconEl = document.getElementById('actionConfirmIcon');
        const titleEl = document.getElementById('actionConfirmModalTitle');
        const messageEl = document.getElementById('actionConfirmModalMessage');
        const submitBtn = document.getElementById('actionConfirmSubmitBtn');

        if (!modal || !submitBtn || !iconWrap || !iconEl || !titleEl || !messageEl) {
            return;
        }

        let closeTimer = null;
        let pendingForm = null;
        let skipConfirm = false;

        const icons = {
            offer: {
                wrap: 'bg-blue-50 border-blue-100',
                color: 'text-[#2E5CE6]',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            },
            accept: {
                wrap: 'bg-green-50 border-green-100',
                color: 'text-green-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            },
            reject: {
                wrap: 'bg-red-50 border-red-100',
                color: 'text-red-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            },
            'payment-accept': {
                wrap: 'bg-emerald-50 border-emerald-100',
                color: 'text-emerald-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            },
            'payment-reject': {
                wrap: 'bg-red-50 border-red-100',
                color: 'text-red-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>',
            },
            'status-finishing': {
                wrap: 'bg-indigo-50 border-indigo-100',
                color: 'text-indigo-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" /></svg>',
            },
        };

        const copy = {
            offer: {
                title: 'Kirim Penawaran Harga?',
                confirm: 'Ya, Kirim Penawaran',
            },
            accept: {
                title: 'Terima Penawaran?',
                confirm: 'Ya, Saya Setuju',
            },
            reject: {
                title: 'Tolak Penawaran?',
                confirm: 'Ya, Tolak',
            },
            'payment-accept': {
                title: 'Setujui Bukti Pembayaran?',
                confirm: 'Ya, Setujui',
            },
            'payment-reject': {
                title: 'Tolak Bukti Pembayaran?',
                confirm: 'Ya, Tolak',
            },
            'status-finishing': {
                title: 'Selesai Cetak?',
                confirm: 'Ya, Masuk Finishing',
            },
        };

        const formatRupiah = (value) => {
            const num = parseInt(String(value).replace(/\D/g, ''), 10) || 0;
            return 'Rp ' + num.toLocaleString('id-ID');
        };

        const openModal = (variant, message, titleOverride) => {
            const icon = icons[variant] || icons.offer;
            const text = copy[variant] || copy.offer;

            iconWrap.className = 'mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full border ' + icon.wrap;
            iconEl.className = icon.color;
            iconEl.innerHTML = icon.svg;
            titleEl.textContent = titleOverride || text.title;
            messageEl.textContent = message;

            submitBtn.textContent = text.confirm;
            submitBtn.className = 'btn-action-confirm-submit flex-1 h-11 text-sm variant-' + variant;

            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            requestAnimationFrame(() => {
                modal.classList.add('is-open');
            });
        };

        const closeModal = () => {
            modal.classList.remove('is-open');

            closeTimer = setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                pendingForm = null;
                closeTimer = null;
            }, 250);
        };

        const buildMessage = (form) => {
            if (form.dataset.confirmMessage) {
                return form.dataset.confirmMessage;
            }

            const variant = form.dataset.confirmVariant || 'offer';
            const kode = form.dataset.confirmKode || '-';
            const hargaInput = form.querySelector('[name="harga_custom"]');
            const harga = form.dataset.confirmHarga || (hargaInput ? hargaInput.value : '0');
            const estimasiInput = form.querySelector('[name="estimasi_custom"]');
            const deadlineInput = form.querySelector('[name="deadline"]');
            const estimasi = form.dataset.confirmEstimasi
                || (estimasiInput ? estimasiInput.value : '-');
            const deadline = form.dataset.confirmDeadline
                || (deadlineInput ? deadlineInput.value : '-');
            const jenis = form.dataset.confirmJenis || 'pembayaran';
            const nominal = form.dataset.confirmNominal || harga;

            if (variant === 'offer') {
                return 'Kirim penawaran untuk ' + kode + '? Harga ' + formatRupiah(harga)
                    + ', estimasi ' + estimasi + ' (setelah ACC desain), deadline produksi ' + deadline + '.';
            }

            if (variant === 'accept') {
                return 'Setujui penawaran ' + kode + '? Harga ' + formatRupiah(harga)
                    + ', estimasi ' + estimasi + ' (setelah ACC desain), deadline produksi ' + deadline + '.';
            }

            if (variant === 'reject') {
                return 'Apakah Anda yakin ingin menolak penawaran untuk pesanan ' + kode + '? Pesanan akan dibatalkan.';
            }

            if (variant === 'payment-accept') {
                return 'Apakah Anda yakin ingin menyetujui bukti ' + jenis + ' sebesar ' + formatRupiah(nominal) + ' untuk pesanan ' + kode + '?';
            }

            if (variant === 'payment-reject') {
                return 'Apakah Anda yakin ingin menolak bukti ' + jenis + ' untuk pesanan ' + kode + '? Pelanggan akan diminta mengunggah ulang.';
            }

            if (variant === 'status-finishing') {
                return 'Apakah Anda yakin ingin menandai pesanan ' + kode + ' selesai cetak dan masuk tahap Finishing?';
            }

            return 'Apakah Anda yakin ingin melanjutkan?';
        };

        const handleConfirmSubmit = (form) => {
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            pendingForm = form;
            const variant = form.dataset.confirmVariant || 'offer';
            const text = copy[variant] || copy.offer;
            openModal(variant, buildMessage(form), form.dataset.confirmTitle || null);
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement) || !form.classList.contains('js-action-confirm-form')) {
                return;
            }

            if (skipConfirm) {
                skipConfirm = false;
                return;
            }

            event.preventDefault();
            handleConfirmSubmit(form);
        }, true);

        window.openActionConfirmForm = (form) => {
            if (!form) {
                return;
            }

            handleConfirmSubmit(form);
        };

        document.querySelectorAll('[data-close-action-confirm]').forEach((btn) => {
            btn.addEventListener('click', closeModal);
        });

        submitBtn.addEventListener('click', () => {
            if (!pendingForm) {
                closeModal();
                return;
            }

            skipConfirm = true;
            pendingForm.submit();
            closeModal();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });
    })();
</script>
