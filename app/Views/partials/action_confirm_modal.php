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

    .btn-action-confirm-submit.variant-status-pengiriman {
        background: #051747;
    }

    .btn-action-confirm-submit.variant-status-pengiriman:hover {
        background: #2E5CE6;
    }

    .btn-action-confirm-submit.variant-upload-bukti {
        background: #051747;
    }

    .btn-action-confirm-submit.variant-upload-bukti:hover {
        background: #2E5CE6;
    }

    .btn-action-confirm-submit.variant-save-pelanggan {
        background: #051747;
    }

    .btn-action-confirm-submit.variant-save-pelanggan:hover {
        background: #2E5CE6;
    }

    .btn-action-confirm-submit.variant-danger,
    .btn-action-confirm-submit.variant-delete-staff {
        background: #EF4444;
    }

    .btn-action-confirm-submit.variant-danger:hover,
    .btn-action-confirm-submit.variant-delete-staff:hover {
        background: #DC2626;
    }

    .btn-action-confirm-submit.variant-toggle-deactivate {
        background: #D97706;
    }

    .btn-action-confirm-submit.variant-toggle-deactivate:hover {
        background: #B45309;
    }

    .btn-action-confirm-submit.variant-toggle-activate {
        background: #22C55E;
    }

    .btn-action-confirm-submit.variant-toggle-activate:hover {
        background: #16A34A;
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
            <p id="actionConfirmModalDetail" class="hidden mt-3 text-sm text-slate-500 leading-relaxed"></p>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="button" id="actionConfirmCancelBtn" data-close-action-confirm class="btn-action-confirm-cancel flex-1 h-11 text-sm">
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
        const detailEl = document.getElementById('actionConfirmModalDetail');
        const cancelBtn = document.getElementById('actionConfirmCancelBtn');
        const submitBtn = document.getElementById('actionConfirmSubmitBtn');

        if (!modal || !submitBtn || !iconWrap || !iconEl || !titleEl || !messageEl || !cancelBtn) {
            return;
        }

        let closeTimer = null;
        let pendingForm = null;
        let pendingOnConfirm = null;
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
            'status-pengiriman': {
                wrap: 'bg-blue-50 border-blue-100',
                color: 'text-[#2E5CE6]',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6.5h9.5V16H3V6.5zm9.5 3H16l3.5 3.5V16h-7V9.5zm0-3h2.5l2 3H12.5V6.5zM6.5 17.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zm11 0a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" /></svg>',
            },
            'upload-bukti': {
                wrap: 'bg-blue-50 border-blue-100',
                color: 'text-[#2E5CE6]',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6" /></svg>',
            },
            'save-pelanggan': {
                wrap: 'bg-blue-50 border-blue-100',
                color: 'text-[#2E5CE6]',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>',
            },
            danger: {
                wrap: 'bg-red-50 border-red-100',
                color: 'text-red-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>',
            },
            'toggle-deactivate': {
                wrap: 'bg-amber-50 border-amber-100',
                color: 'text-amber-600',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>',
            },
            'toggle-activate': {
                wrap: 'bg-green-50 border-green-100',
                color: 'text-green-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            },
            'delete-staff': {
                wrap: 'bg-red-50 border-red-100',
                color: 'text-red-500',
                svg: '<svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>',
            },
        };

        const copy = {
            offer: {
                title: 'Konfirmasi Harga Pesanan Custom?',
                confirm: 'Ya, Konfirmasi Harga',
                cancel: 'Batal',
            },
            accept: {
                title: 'Setuju dengan Harga Ini?',
                confirm: 'Ya, Saya Setuju',
                cancel: 'Batal',
            },
            reject: {
                title: 'Tolak & Batalkan Pesanan?',
                confirm: 'Ya, Batalkan',
                cancel: 'Batal',
            },
            'payment-accept': {
                title: 'Setujui Bukti Pembayaran?',
                confirm: 'Ya, Setujui',
                cancel: 'Batal',
            },
            'payment-reject': {
                title: 'Tolak Bukti Pembayaran?',
                confirm: 'Ya, Tolak',
                cancel: 'Batal',
            },
            'status-finishing': {
                title: 'Ubah status ke Finishing?',
                confirm: 'Ya, Ubah Status',
                cancel: 'Batal',
            },
            'status-pengiriman': {
                title: 'Ubah status pengiriman?',
                confirm: 'Ya, Ubah Status',
                cancel: 'Batal',
            },
            'upload-bukti': {
                title: 'Konfirmasi Pengiriman Bukti Pembayaran',
                confirm: 'Ya, Kirim',
                cancel: 'Periksa Kembali',
                detail: 'Pastikan bukti pembayaran yang diunggah sudah sesuai. Setelah dikirim, pembayaran akan menunggu proses verifikasi oleh Tim Bagian keuangan Z\'Plack.',
            },
            'save-pelanggan': {
                title: 'Simpan Perubahan?',
                confirm: 'Ya, Simpan',
                confirmHtml: '<span class="inline-flex items-center justify-center gap-1.5"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>Ya, Simpan</span>',
                cancel: 'Batal',
            },
            danger: {
                title: 'Konfirmasi Tindakan?',
                confirm: 'Ya, Lanjutkan',
                cancel: 'Batal',
            },
            'toggle-deactivate': {
                title: 'Nonaktifkan Akun Staff?',
                confirm: 'Ya, Nonaktifkan',
                cancel: 'Batal',
            },
            'toggle-activate': {
                title: 'Aktifkan Akun Staff?',
                confirm: 'Ya, Aktifkan',
                cancel: 'Batal',
            },
            'delete-staff': {
                title: 'Hapus Staff Permanen?',
                confirm: 'Ya, Hapus',
                cancel: 'Batal',
            },
        };

        const formatRupiah = (value) => {
            const num = parseInt(String(value).replace(/\D/g, ''), 10) || 0;
            return 'Rp ' + num.toLocaleString('id-ID');
        };

        const openModal = (variant, message, titleOverride, detailOverride) => {
            const icon = icons[variant] || icons.offer;
            const text = copy[variant] || copy.offer;

            iconWrap.className = 'mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full border ' + icon.wrap;
            iconEl.className = icon.color;
            iconEl.innerHTML = icon.svg;
            titleEl.textContent = titleOverride || text.title;
            messageEl.textContent = message;

            const detailText = detailOverride ?? text.detail ?? '';
            if (detailEl) {
                if (detailText) {
                    detailEl.textContent = detailText;
                    detailEl.classList.remove('hidden');
                } else {
                    detailEl.textContent = '';
                    detailEl.classList.add('hidden');
                }
            }

            cancelBtn.textContent = text.cancel || 'Batal';
            if (text.confirmHtml) {
                submitBtn.innerHTML = text.confirmHtml;
            } else {
                submitBtn.textContent = text.confirm;
            }
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
                pendingOnConfirm = null;
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
            const deadlineInput = form.querySelector('[name="deadline_produksi"]');
            const estimasi = form.dataset.confirmEstimasi
                || (estimasiInput ? estimasiInput.value : '-');
            const deadline = form.dataset.confirmDeadline
                || (deadlineInput ? deadlineInput.value : '-');
            const jenis = form.dataset.confirmJenis || 'pembayaran';
            const nominal = form.dataset.confirmNominal || harga;

            if (variant === 'offer') {
                return 'Konfirmasi harga untuk ' + kode + '? Harga ' + formatRupiah(harga)
                    + ', estimasi ' + estimasi + ' (setelah ACC desain), deadline produksi ' + deadline + '.';
            }

            if (variant === 'accept') {
                return 'Setujui konfirmasi harga ' + kode + '? Harga ' + formatRupiah(harga)
                    + ', estimasi ' + estimasi + ' (setelah ACC desain), deadline produksi ' + deadline + '.';
            }

            if (variant === 'reject') {
                return 'Apakah Anda yakin ingin menolak harga yang dikonfirmasi Admin untuk pesanan ' + kode + '? Pesanan akan dibatalkan.';
            }

            if (variant === 'payment-accept') {
                return 'Apakah Anda yakin ingin menyetujui bukti ' + jenis + ' sebesar ' + formatRupiah(nominal) + ' untuk pesanan ' + kode + '?';
            }

            if (variant === 'payment-reject') {
                return 'Apakah Anda yakin ingin menolak bukti ' + jenis + ' untuk pesanan ' + kode + '? Pelanggan akan diminta mengunggah ulang.';
            }

            if (variant === 'status-finishing') {
                return kode;
            }

            if (variant === 'status-pengiriman') {
                return kode;
            }

            if (variant === 'upload-bukti') {
                return 'Apakah Anda yakin ingin mengirim bukti pembayaran ini?';
            }

            if (variant === 'save-pelanggan') {
                return form.dataset.confirmMessage || 'Apakah Anda yakin ingin menyimpan perubahan pada data pengguna?';
            }

            return 'Apakah Anda yakin ingin melanjutkan?';
        };

        const handleConfirmSubmit = (form) => {
            if (form.classList.contains('js-pelanggan-akun-form') && typeof window.validatePelangganAkunFormFields === 'function') {
                const formatError = window.validatePelangganAkunFormFields(form);
                if (formatError) {
                    alert(formatError);
                    return;
                }
            }

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            pendingForm = form;
            const variant = form.dataset.confirmVariant || 'offer';
            openModal(
                variant,
                buildMessage(form),
                form.dataset.confirmTitle || null,
                form.dataset.confirmDetail || null
            );
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

        window.openActionConfirmCustom = ({ variant, title, message, detail, onConfirm } = {}) => {
            pendingForm = null;
            pendingOnConfirm = typeof onConfirm === 'function' ? onConfirm : null;
            openModal(
                variant || 'offer',
                message || '',
                title || null,
                detail || null
            );
        };

        document.querySelectorAll('[data-close-action-confirm]').forEach((btn) => {
            btn.addEventListener('click', closeModal);
        });

        submitBtn.addEventListener('click', () => {
            if (pendingOnConfirm) {
                const cb = pendingOnConfirm;
                pendingOnConfirm = null;
                closeModal();
                cb();
                return;
            }

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
