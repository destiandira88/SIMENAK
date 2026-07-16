<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentPayments
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Beranda<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Beranda Keuangan<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Selamat Datang, Keuangan 👋<?= $this->endSection() ?>
<?= $this->section('banner_subtitle_allow_html') ?>1<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>
Daftar pembayaran yang perlu diverifikasi hari ini. Rekap tersedia pada menu
<a href="<?= esc(site_url('laporan-keuangan')) ?>" class="font-semibold text-[#2E5CE6] hover:text-[#051747]">Laporan Transaksi</a>.
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<style>
    #paymentRejectInputModal {
        opacity: 0;
        transition: opacity .25s ease;
    }

    #paymentRejectInputModal.is-open {
        opacity: 1;
    }

    #paymentRejectInputModal .action-confirm-panel {
        transform: scale(.95);
        opacity: 0;
        transition: transform .25s ease, opacity .25s ease;
    }

    #paymentRejectInputModal.is-open .action-confirm-panel {
        transform: scale(1);
        opacity: 1;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-4 mt-6">
    <h3 class="text-base font-bold text-[#051747]">Pembayaran Menunggu Verifikasi</h3>
    <label for="searchInput" class="sr-only">Cari pembayaran</label>
    <div class="search-control w-full sm:w-auto">
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <input
            type="search"
            id="searchInput"
            placeholder="Cari kode, pelanggan, atau jenis..."
            autocomplete="off">
    </div>
</div>

<div class="admin-data-table-wrap">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kodebayar">
                        Kode Pembayaran<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kodeorder">
                        Kode Pesanan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="pelanggan">
                        Pelanggan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="jenis">
                        Jenis<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">Nominal</th>
                    <th class="px-4 py-3 text-left font-semibold">Status Order</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="deadline">
                        Deadline Pengerjaan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="upload">
                        Tgl Unggah<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold">Bukti</th>
                    <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody id="keuanganPaymentBody">
                <?php if ($recentPayments === []): ?>
                    <tr>
                        <td colspan="11" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pembayaran yang menunggu verifikasi 🎉
                        </td>
                    </tr>
                <?php else: ?>
                    <?php helper('deadline'); ?>
                    <?php foreach ($recentPayments as $index => $payment): ?>
                        <?php
                        $idPayment    = (int) ($payment['id_payment'] ?? 0);
                        $kodePayment  = (string) ($payment['kode_payment'] ?? ('PAY-' . str_pad((string) $idPayment, 4, '0', STR_PAD_LEFT)));
                        $kodeOrder    = (string) ($payment['kode_order'] ?? '-');
                        $namaPelanggan = (string) ($payment['nama_pelanggan'] ?? '-');
                        $noTelp        = trim((string) ($payment['no_telp'] ?? ''));
                        $jenis        = (string) ($payment['jenis'] ?? '');
                        $tglUpload    = (string) ($payment['tgl_upload'] ?? $payment['created_at'] ?? 'now');
                        $buktiFile    = (string) ($payment['bukti_tf'] ?? '');
                        $deadlineRaw  = trim((string) ($payment['deadline_produksi'] ?? ''));
                        $tsDeadline   = $deadlineRaw !== '' ? strtotime($deadlineRaw) : 0;
                        $uploadTs     = $tglUpload !== '' ? strtotime($tglUpload) : 0;
                        $searchText   = mb_strtolower(trim($kodePayment . ' ' . $kodeOrder . ' ' . $namaPelanggan . ' ' . $noTelp . ' ' . $jenis));
                        $jenisBadge   = $jenis === 'pelunasan'
                            ? 'bg-purple-100 text-purple-800'
                            : 'bg-blue-100 text-blue-800';
                        $jenisLabel   = $jenis === 'pelunasan' ? 'Pelunasan' : 'DP';
                        $orderStatus  = (string) ($payment['order_status'] ?? '');
                        $orderStatusRow = [
                            'status'             => $orderStatus,
                            'order_status'       => $orderStatus,
                            'jenis'              => $jenis,
                            'payment_status'     => (string) ($payment['payment_status'] ?? ''),
                            'dp_status'          => $jenis === 'dp' ? ($payment['payment_status'] ?? '') : null,
                            'dp_bukti_tf'        => $jenis === 'dp' ? ($payment['bukti_tf'] ?? '') : null,
                            'pelunasan_status'   => $jenis === 'pelunasan' ? ($payment['payment_status'] ?? '') : null,
                            'pelunasan_bukti_tf' => $jenis === 'pelunasan' ? ($payment['bukti_tf'] ?? '') : null,
                        ];
                        ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-kodebayar="<?= esc(mb_strtolower($kodePayment)) ?>"
                            data-kodeorder="<?= esc(mb_strtolower($kodeOrder)) ?>"
                            data-pelanggan="<?= esc(mb_strtolower($namaPelanggan)) ?>"
                            data-jenis="<?= esc($jenis) ?>"
                            data-deadline="<?= esc((string) $tsDeadline) ?>"
                            data-upload="<?= esc((string) $uploadTs) ?>">
                            <td class="row-num px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodePayment) ?>
                            </td>
                            <td class="px-4 py-3 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodeOrder) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => $namaPelanggan,
                                    'noTelp' => $noTelp,
                                ]) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($jenisBadge) ?>">
                                    <?= esc($jenisLabel) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-[#051747]">
                                Rp <?= esc(number_format((float) ($payment['nominal'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($orderStatus)) ?>">
                                    <?= esc(getOrderStatusLabel($orderStatusRow, 'keuangan')) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= $deadlineRaw !== '' ? esc(formatTanggalId($deadlineRaw)) : '-' ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= esc(date('d M Y H:i', strtotime($tglUpload))) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($buktiFile !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/bukti_bayar/' . $buktiFile)) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-[#2E5CE6] underline text-xs font-semibold hover:text-[#051747]">
                                        Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="js-payment-accept-btn shrink-0 bg-emerald-500 text-white rounded-full text-xs font-bold px-3 py-1.5 hover:bg-emerald-600 transition-colors whitespace-nowrap"
                                        data-id-payment="<?= esc((string) $idPayment) ?>"
                                        data-kode-order="<?= esc($kodeOrder, 'attr') ?>"
                                        data-jenis="<?= esc($jenis, 'attr') ?>"
                                        data-nominal="<?= esc((string) (int) ($payment['nominal'] ?? 0)) ?>">
                                        ACC
                                    </button>
                                    <button
                                        type="button"
                                        class="js-payment-reject-btn shrink-0 bg-red-500 text-white rounded-full text-xs font-bold px-3 py-1.5 hover:bg-red-600 transition-colors whitespace-nowrap"
                                        data-id-payment="<?= esc((string) $idPayment) ?>"
                                        data-kode-order="<?= esc($kodeOrder, 'attr') ?>"
                                        data-jenis="<?= esc($jenis, 'attr') ?>">
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<form id="setujuiForm" method="post" action="" class="hidden js-action-confirm-form"
    data-confirm-variant="payment-accept"
    data-confirm-kode=""
    data-confirm-nominal=""
    data-confirm-jenis="">
    <?= csrf_field() ?>
</form>

<div id="paymentRejectInputModal" class="fixed inset-0 z-[85] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="paymentRejectInputTitle">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-payment-reject-input></div>
    <div class="action-confirm-panel relative w-full max-w-md bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg p-6 md:p-8 z-10">
        <div class="text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 border border-red-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h4 id="paymentRejectInputTitle" class="text-xl md:text-2xl font-extrabold text-[#051747] leading-tight">Alasan Penolakan</h4>
            <p id="paymentRejectInputMessage" class="mt-2 text-sm text-slate-500 leading-relaxed"></p>
        </div>
        <form id="tolakForm" method="post" action="" class="mt-5 js-action-confirm-form"
            data-confirm-variant="payment-reject"
            data-confirm-kode=""
            data-confirm-jenis="">
            <?= csrf_field() ?>
            <textarea
                name="catatan_tolak"
                rows="3"
                required
                placeholder="Jelaskan alasan penolakan bukti pembayaran..."
                class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10 resize-none"></textarea>
            <div class="mt-6 flex gap-3">
                <button type="button" data-close-payment-reject-input class="btn-action-confirm-cancel flex-1 h-11 text-sm">
                    Batal
                </button>
                <button type="submit" class="btn-action-confirm-submit variant-payment-reject flex-1 h-11 text-sm">
                    Lanjutkan
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'searchInput',
        tbodyId: 'keuanganPaymentBody',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<script>
    const setujuiForm = document.getElementById('setujuiForm');
    const tolakForm = document.getElementById('tolakForm');
    const rejectInputModal = document.getElementById('paymentRejectInputModal');
    const rejectInputMessage = document.getElementById('paymentRejectInputMessage');

    const verifikasiBaseUrl = {
        dp: '<?= site_url('verifikasi-dp') ?>',
        pelunasan: '<?= site_url('verifikasi-pelunasan') ?>',
    };

    function getVerifikasiBase(jenis) {
        return jenis === 'pelunasan' ? verifikasiBaseUrl.pelunasan : verifikasiBaseUrl.dp;
    }

    function getJenisLabel(jenis) {
        return jenis === 'pelunasan' ? 'Pelunasan' : 'DP';
    }

    function openRejectInputModal() {
        if (!rejectInputModal) {
            return;
        }

        rejectInputModal.classList.remove('hidden');
        rejectInputModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            rejectInputModal.classList.add('is-open');
        });
    }

    function closeRejectInputModal() {
        if (!rejectInputModal) {
            return;
        }

        rejectInputModal.classList.remove('is-open');
        setTimeout(() => {
            rejectInputModal.classList.add('hidden');
            rejectInputModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }, 250);
    }

    function openSetujuiModal(idPayment, kodeOrder, jenis, nominal) {
        if (!setujuiForm || typeof window.openActionConfirmForm !== 'function') {
            return;
        }

        setujuiForm.action = getVerifikasiBase(jenis) + '/' + idPayment + '/acc';
        setujuiForm.dataset.confirmKode = kodeOrder;
        setujuiForm.dataset.confirmNominal = String(nominal);
        setujuiForm.dataset.confirmJenis = getJenisLabel(jenis);
        window.openActionConfirmForm(setujuiForm);
    }

    document.querySelectorAll('.js-payment-accept-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            openSetujuiModal(
                parseInt(btn.dataset.idPayment || '0', 10),
                btn.dataset.kodeOrder || '-',
                btn.dataset.jenis || 'dp',
                parseInt(btn.dataset.nominal || '0', 10)
            );
        });
    });

    document.querySelectorAll('.js-payment-reject-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            openTolakModal(
                parseInt(btn.dataset.idPayment || '0', 10),
                btn.dataset.kodeOrder || '-',
                btn.dataset.jenis || 'dp'
            );
        });
    });

    function openTolakModal(idPayment, kodeOrder, jenis) {
        if (!tolakForm) {
            return;
        }

        tolakForm.action = getVerifikasiBase(jenis) + '/' + idPayment + '/tolak';
        tolakForm.dataset.confirmKode = kodeOrder;
        tolakForm.dataset.confirmJenis = getJenisLabel(jenis);

        const textarea = tolakForm.querySelector('textarea[name="catatan_tolak"]');
        if (textarea) {
            textarea.value = '';
        }

        if (rejectInputMessage) {
            rejectInputMessage.textContent = 'Masukkan alasan penolakan bukti ' + getJenisLabel(jenis) + ' untuk pesanan ' + kodeOrder + '.';
        }

        openRejectInputModal();
    }

    document.querySelectorAll('[data-close-payment-reject-input]').forEach((btn) => {
        btn.addEventListener('click', closeRejectInputModal);
    });

    if (tolakForm) {
        tolakForm.addEventListener('submit', () => {
            closeRejectInputModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && rejectInputModal && rejectInputModal.classList.contains('is-open')) {
            closeRejectInputModal();
        }
    });
</script>
<?= $this->endSection() ?>