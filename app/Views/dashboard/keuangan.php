<?php
/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentPayments
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard Keuangan<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-[#051747]">
        Selamat Datang, Keuangan 👋
    </h2>
    <p class="mt-1 text-sm text-slate-500">
        Pantau dan verifikasi pembayaran DP serta pelunasan pelanggan.
    </p>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h3 class="text-base font-bold text-[#051747]">Pembayaran Menunggu Verifikasi</h3>
        <input
            type="search"
            id="searchInput"
            placeholder="Cari kode, pelanggan, atau jenis..."
            class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-full sm:w-64 focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
    </div>

    <div class="overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Payment</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Order</th>
                    <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                    <th class="px-4 py-3 text-left font-semibold">Jenis</th>
                    <th class="px-4 py-3 text-left font-semibold">Nominal</th>
                    <th class="px-4 py-3 text-left font-semibold">Tgl Upload</th>
                    <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentPayments === []): ?>
                    <tr>
                        <td colspan="8" class="py-16 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pembayaran yang menunggu verifikasi 🎉</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentPayments as $index => $payment): ?>
                        <?php
                        $idPayment    = (int) ($payment['id_payment'] ?? 0);
                        $kodePayment  = (string) ($payment['kode_payment'] ?? ('PAY-' . str_pad((string) $idPayment, 4, '0', STR_PAD_LEFT)));
                        $kodeOrder    = (string) ($payment['kode_order'] ?? '-');
                        $namaPelanggan = (string) ($payment['nama_pelanggan'] ?? '-');
                        $jenis        = (string) ($payment['jenis'] ?? '');
                        $tglUpload    = (string) ($payment['tgl_upload'] ?? $payment['created_at'] ?? 'now');
                        $searchText   = mb_strtolower(trim($kodePayment . ' ' . $kodeOrder . ' ' . $namaPelanggan . ' ' . $jenis));
                        $jenisBadge   = $jenis === 'pelunasan'
                            ? 'bg-purple-100 text-purple-800'
                            : 'bg-blue-100 text-blue-800';
                        $jenisLabel   = $jenis === 'pelunasan' ? 'Pelunasan' : 'DP';
                        ?>
                        <tr
                            class="border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>">
                            <td class="px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodePayment) ?>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-sm text-[#051747]">
                                <?= esc($kodeOrder) ?>
                            </td>
                            <td class="px-4 py-3.5"><?= esc($namaPelanggan) ?></td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($jenisBadge) ?>">
                                    <?= esc($jenisLabel) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-[#051747]">
                                Rp <?= esc(number_format((float) ($payment['nominal'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3.5 text-slate-500">
                                <?= esc(date('d M Y H:i', strtotime($tglUpload))) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="bg-emerald-500 text-white rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors hover:bg-emerald-600"
                                        onclick="openSetujuiModal(<?= esc((string) $idPayment, 'attr') ?>, <?= esc(json_encode($kodeOrder), 'attr') ?>, <?= esc(json_encode($jenis), 'attr') ?>)">
                                        ✓ Setujui
                                    </button>
                                    <button
                                        type="button"
                                        class="bg-red-500 text-white rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors hover:bg-red-600"
                                        onclick="openTolakModal(<?= esc((string) $idPayment, 'attr') ?>, <?= esc(json_encode($jenis), 'attr') ?>)">
                                        ✗ Tolak
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

<div id="setujuiModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-modal="setujui"></div>
    <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-lg border border-slate-100 p-6 z-10">
        <h4 class="text-lg font-bold text-[#051747]">Konfirmasi Verifikasi Pembayaran</h4>
        <p class="mt-2 text-sm text-slate-500">
            Setujui pembayaran untuk pesanan <span id="setujuiModalKode" class="font-mono font-semibold text-[#051747]"></span>?
        </p>
        <form id="setujuiForm" method="post" action="" class="mt-6 flex gap-3">
            <?= csrf_field() ?>
            <button type="button" data-close-modal="setujui" class="flex-1 rounded-full border border-[#051747] px-4 py-2.5 text-sm font-bold uppercase tracking-wide text-[#051747] transition-colors hover:bg-[#051747] hover:text-white">
                Batal
            </button>
            <button type="submit" class="flex-1 rounded-full bg-emerald-500 px-4 py-2.5 text-sm font-bold uppercase tracking-wide text-white transition-colors hover:bg-emerald-600">
                Setujui
            </button>
        </form>
    </div>
</div>

<div id="tolakModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close-modal="tolak"></div>
    <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-lg border border-slate-100 p-6 z-10">
        <h4 class="text-lg font-bold text-[#051747]">Tolak Pembayaran</h4>
        <p class="mt-2 text-sm text-slate-500">
            Berikan alasan penolakan sebelum melanjutkan.
        </p>
        <form id="tolakForm" method="post" action="" class="mt-4 space-y-4">
            <?= csrf_field() ?>
            <textarea
                name="catatan_tolak"
                rows="3"
                required
                placeholder="Alasan penolakan..."
                class="input-field w-full px-3 py-2.5 text-sm resize-none"></textarea>
            <div class="flex gap-3">
                <button type="button" data-close-modal="tolak" class="flex-1 rounded-full border border-[#051747] px-4 py-2.5 text-sm font-bold uppercase tracking-wide text-[#051747] transition-colors hover:bg-[#051747] hover:text-white">
                    Batal
                </button>
                <button type="submit" class="flex-1 rounded-full bg-red-500 px-4 py-2.5 text-sm font-bold uppercase tracking-wide text-white transition-colors hover:bg-red-600">
                    Tolak
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const searchInput = document.getElementById('searchInput');
    const setujuiModal = document.getElementById('setujuiModal');
    const tolakModal = document.getElementById('tolakModal');
    const setujuiForm = document.getElementById('setujuiForm');
    const tolakForm = document.getElementById('tolakForm');
    const setujuiModalKode = document.getElementById('setujuiModalKode');

    const verifikasiBaseUrl = {
        dp: '<?= site_url('verifikasi-dp') ?>',
        pelunasan: '<?= site_url('verifikasi-pelunasan') ?>',
    };

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('tbody tr[data-search]').forEach(row => {
                row.style.display = row.dataset.search.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }

    function getVerifikasiBase(jenis) {
        return jenis === 'pelunasan' ? verifikasiBaseUrl.pelunasan : verifikasiBaseUrl.dp;
    }

    function openModal(modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function openSetujuiModal(idPayment, kodeOrder, jenis) {
        if (!setujuiModal || !setujuiForm || !setujuiModalKode) return;

        setujuiModalKode.textContent = kodeOrder;
        setujuiForm.action = getVerifikasiBase(jenis) + '/' + idPayment + '/acc';
        openModal(setujuiModal);
    }

    function openTolakModal(idPayment, jenis) {
        if (!tolakModal || !tolakForm) return;

        tolakForm.action = getVerifikasiBase(jenis) + '/' + idPayment + '/tolak';
        const textarea = tolakForm.querySelector('textarea[name="catatan_tolak"]');
        if (textarea) {
            textarea.value = '';
        }
        openModal(tolakModal);
    }

    document.querySelectorAll('[data-close-modal]').forEach(el => {
        el.addEventListener('click', () => {
            const target = el.getAttribute('data-close-modal');
            if (target === 'setujui') {
                closeModal(setujuiModal);
            }
            if (target === 'tolak') {
                closeModal(tolakModal);
            }
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(setujuiModal);
            closeModal(tolakModal);
        }
    });
</script>
<?= $this->endSection() ?>
