<?php
/**
 * @var string                       $title
 * @var string                       $page_title
 * @var list<array<string, mixed>>   $payments
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Verifikasi Pelunasan') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Verifikasi Pelunasan') ?><?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Verifikasi Pelunasan<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Daftar bukti pelunasan yang menunggu verifikasi<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php if (empty($payments)): ?>
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-8 text-center">
        <p class="text-slate-500 text-sm">Tidak ada pelunasan yang perlu diverifikasi</p>
    </div>
<?php else: ?>
    <div class="admin-data-table-wrap">
        <div class="table-responsive">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#051747] text-white text-xs uppercase">
                        <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                        <th class="px-4 py-3 text-left font-semibold">Kode Pesanan</th>
                        <th class="px-4 py-3 text-left font-semibold">Pelanggan</th>
                        <th class="px-4 py-3 text-left font-semibold">Nominal</th>
                        <th class="px-4 py-3 text-left font-semibold">Tgl Unggah</th>
                        <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="deadline">
                            Deadline Pengerjaan<span class="sort-icon">↕</span>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold">Bukti</th>
                        <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody id="verifikasiPelunasanBody">
                    <?php helper('deadline'); ?>
                    <?php foreach ($payments as $index => $p): ?>
                        <?php
                        $idPayment = (int) ($p['id_payment'] ?? 0);
                        $buktiFile = (string) ($p['bukti_tf'] ?? '');
                        $kodeOrder = (string) ($p['kode_order'] ?? '-');
                        $nominal   = (int) ($p['nominal'] ?? 0);
                        $deadlineRaw = trim((string) ($p['deadline_produksi'] ?? ''));
                        $tsDeadline  = $deadlineRaw !== '' ? strtotime($deadlineRaw) : 0;
                        ?>
                        <tr class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF]"
                            data-deadline="<?= esc((string) $tsDeadline) ?>">
                            <td class="px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-mono font-semibold text-[#051747]">
                                <?= esc($kodeOrder) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?= view('partials/pelanggan_kontak_cell', [
                                    'nama'   => (string) ($p['nama_pelanggan'] ?? '-'),
                                    'noTelp' => (string) ($p['no_telp'] ?? ''),
                                ]) ?>
                            </td>
                            <td class="px-4 py-3 font-semibold">
                                Rp <?= esc(number_format((float) ($p['nominal'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <?= !empty($p['tgl_upload'])
                                    ? esc(date('d M Y H:i', strtotime((string) $p['tgl_upload'])))
                                    : '-' ?>
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                <?= $deadlineRaw !== '' ? esc(formatTanggalId($deadlineRaw)) : '-' ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($buktiFile !== ''): ?>
                                    <a href="<?= esc(base_url('uploads/bukti_bayar/' . $buktiFile)) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-blue-600 underline text-xs">
                                        Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <span class="text-slate-400 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 min-w-[280px]">
                                    <form method="post"
                                        action="<?= esc(site_url('verifikasi-pelunasan/' . $idPayment . '/acc')) ?>"
                                        class="shrink-0 js-action-confirm-form"
                                        data-confirm-variant="payment-accept"
                                        data-confirm-kode="<?= esc($kodeOrder) ?>"
                                        data-confirm-nominal="<?= esc((string) $nominal) ?>"
                                        data-confirm-jenis="Pelunasan">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                            class="bg-emerald-500 text-white rounded-full text-xs font-bold px-3 py-1.5 hover:bg-emerald-600 transition-colors whitespace-nowrap">
                                            ACC
                                        </button>
                                    </form>
                                    <form method="post"
                                        action="<?= esc(site_url('verifikasi-pelunasan/' . $idPayment . '/tolak')) ?>"
                                        class="flex flex-1 items-center gap-2 min-w-0 js-action-confirm-form"
                                        data-confirm-variant="payment-reject"
                                        data-confirm-kode="<?= esc($kodeOrder) ?>"
                                        data-confirm-jenis="Pelunasan">
                                        <?= csrf_field() ?>
                                        <input type="text" name="catatan_tolak" required
                                            placeholder="Alasan penolakan..."
                                            class="flex-1 min-w-0 border border-slate-200 rounded-lg px-2 py-1.5 text-xs focus:border-[#2E5CE6] focus:outline-none">
                                        <button type="submit"
                                            class="shrink-0 bg-red-500 text-white rounded-full text-xs font-bold px-3 py-1.5 hover:bg-red-600 transition-colors whitespace-nowrap">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        tbodyId: 'verifikasiPelunasanBody',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?= $this->endSection() ?>
