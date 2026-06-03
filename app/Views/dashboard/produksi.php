<?php
/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard Produksi<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-[#051747]">
        Selamat Datang, Produksi 👋
    </h2>
    <p class="mt-1 text-sm text-slate-500">
        Antrian desain dan cetak hari ini.
    </p>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h3 class="text-base font-bold text-[#051747]">Antrian Pengerjaan</h3>
        <input
            type="search"
            id="searchInput"
            placeholder="Cari kode atau produk..."
            class="border border-slate-200 rounded-lg px-3 py-2 text-sm w-full sm:w-64 focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
    </div>

    <div class="overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold">No</th>
                    <th class="px-4 py-3 text-left font-semibold">Kode Order</th>
                    <th class="px-4 py-3 text-left font-semibold">Produk</th>
                    <th class="px-4 py-3 text-left font-semibold">Sisa Kuota</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentOrders === []): ?>
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <p class="text-sm font-medium text-slate-500">Tidak ada pesanan dalam antrian 🎉</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $index => $order): ?>
                        <?php
                        $kodeOrder    = (string) ($order['kode_order'] ?? '');
                        $namaProduk   = !empty($order['is_custom'])
                            ? 'Pesanan Custom'
                            : (string) ($order['nama_produk'] ?? '-');
                        $status       = (string) ($order['status'] ?? '');
                        $sisaKuota    = (int) ($order['sisa_kuota'] ?? 0);
                        $kuotaRevisi  = (int) ($order['kuota_revisi'] ?? 0);
                        $searchText   = mb_strtolower(trim($kodeOrder . ' ' . $namaProduk . ' ' . $status));
                        $kuotaBadge   = $sisaKuota > 0
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800';
                        $kuotaLabel   = $sisaKuota > 0
                            ? 'Sisa ' . $sisaKuota . ' Revisi'
                            : 'Kuota Habis';
                        $uploadUrl    = site_url('revisi/upload/' . $kodeOrder);
                        $historyUrl   = site_url('revisi/history/' . $kodeOrder);
                        ?>
                        <tr
                            class="border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>">
                            <td class="px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodeOrder ?: '-') ?>
                            </td>
                            <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc($kuotaBadge) ?>">
                                    <?= esc($sisaKuota . '/' . $kuotaRevisi) ?> · <?= esc($kuotaLabel) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getStatusLabel($status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-wrap gap-2">
                                    <a href="<?= esc($uploadUrl) ?>" class="inline-flex bg-[#051747] text-white text-xs px-3 py-1.5 rounded-lg font-semibold transition-colors hover:bg-[#2E5CE6]">
                                        Upload Draft
                                    </a>
                                    <a href="<?= esc($historyUrl) ?>" class="inline-flex text-xs px-3 py-1.5 rounded-lg border border-slate-300 text-slate-600 font-semibold transition-colors hover:bg-slate-50">
                                        History
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const searchInput = document.getElementById('searchInput');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('tbody tr[data-search]').forEach(row => {
                row.style.display = row.dataset.search.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
</script>
<?= $this->endSection() ?>
