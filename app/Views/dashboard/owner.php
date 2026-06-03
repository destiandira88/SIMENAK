<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 * @var list<string>                 $chartLabels
 * @var list<int>                    $chartValues
 */
$ownerTooltip = 'Owner hanya dapat melihat data';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard Owner<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-[#051747]">
            Selamat Datang, Owner 👋
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            Ringkasan performa bisnis Z'Plack.
        </p>
    </div>
    <button
        type="button"
        disabled
        title="Fitur ekspor ada di halaman Laporan"
        class="inline-flex shrink-0 items-center justify-center opacity-50 cursor-not-allowed bg-[#051747] text-white px-5 py-2.5 rounded-full text-sm font-semibold">
        Export Excel
    </button>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
    <h3 class="text-base font-bold text-[#051747] mb-4">Pesanan 7 Hari Terakhir</h3>
    <canvas id="ordersChart" height="100"></canvas>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h3 class="text-base font-bold text-[#051747]">Pesanan Terbaru</h3>
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
                    <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                    <th class="px-4 py-3 text-left font-semibold">Total</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recentOrders === []): ?>
                    <tr>
                        <td colspan="7" class="py-16 text-center">
                            <div class="text-4xl mb-3">📦</div>
                            <p class="text-sm font-medium text-slate-500">Belum ada data pesanan</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $index => $order): ?>
                        <?php
                        $kodeOrder  = (string) ($order['kode_order'] ?? '');
                        $namaProduk = !empty($order['is_custom'])
                            ? 'Pesanan Custom'
                            : (string) ($order['nama_produk'] ?? '-');
                        $status     = (string) ($order['status'] ?? '');
                        $searchText = mb_strtolower(trim($kodeOrder . ' ' . $namaProduk));
                        ?>
                        <tr
                            class="border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>">
                            <td class="px-4 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3.5 font-mono text-sm font-semibold text-[#051747]">
                                <?= esc($kodeOrder ?: '-') ?>
                            </td>
                            <td class="px-4 py-3.5"><?= esc($namaProduk) ?></td>
                            <td class="px-4 py-3.5 text-slate-500">
                                <?= esc(date('d M Y', strtotime($order['created_at'] ?? 'now'))) ?>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-[#051747]">
                                Rp <?= esc(number_format((float) ($order['total_harga'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getStatusLabel($status)) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <button
                                    type="button"
                                    disabled
                                    title="<?= esc($ownerTooltip) ?>"
                                    class="inline-flex opacity-50 cursor-not-allowed text-xs px-3 py-1.5 bg-[#051747] text-white rounded-lg">
                                    Detail
                                </button>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    const ordersChartEl = document.getElementById('ordersChart');
    if (ordersChartEl) {
        const ctx = ordersChartEl.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels ?? []) ?>,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: <?= json_encode($chartValues ?? []) ?>,
                    backgroundColor: '#2E5CE6',
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>