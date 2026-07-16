<?php
/**
 * @var list<array<string, mixed>> $orders
 * @var string                     $emptyMessage
 * @var bool                       $showPelanggan
 */
$showPelanggan = $showPelanggan ?? false;
$emptyMessage  = $emptyMessage ?? 'Belum ada pesanan.';
?>
<div class="admin-data-table-wrap">
    <div class="px-6 py-4 border-b" style="border-color:var(--border);">
        <h3 class="text-base font-bold" style="color:var(--navy);">Pesanan Terbaru</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-white text-xs uppercase tracking-wider" style="background:var(--navy);">
                    <th class="px-6 py-3 font-semibold w-12">No</th>
                    <th class="px-6 py-3 font-semibold">Kode</th>
                    <?php if ($showPelanggan): ?>
                        <th class="px-6 py-3 font-semibold">Pelanggan</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 font-semibold">Produk</th>
                    <th class="px-6 py-3 font-semibold">Total</th>
                    <th class="px-6 py-3 font-semibold">Status</th>
                    <th class="px-6 py-3 font-semibold">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders === []): ?>
                    <tr>
                        <td colspan="<?= $showPelanggan ? 7 : 6 ?>" class="px-6 py-8 text-center" style="color:var(--text-muted);">
                            <?= esc($emptyMessage) ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <?php
                        $produk = !empty($order['is_custom'])
                            ? 'Pesanan Custom'
                            : ($order['nama_produk'] ?? '-');
                        $status = (string) ($order['status'] ?? '');
                        ?>
                        <tr class="border-b transition-colors hover:bg-[#F8FAFF]" style="border-color:var(--border);">
                            <td class="px-6 py-3.5 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-6 py-3.5 font-semibold" style="color:var(--navy);">
                                <?= esc($order['kode_order'] ?? '-') ?>
                            </td>
                            <?php if ($showPelanggan): ?>
                                <td class="px-6 py-3.5">
                                    <?= view('partials/pelanggan_kontak_cell', [
                                        'nama'   => (string) ($order['nama_pelanggan'] ?? '-'),
                                        'noTelp' => (string) ($order['no_telp'] ?? ''),
                                    ]) ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-3.5"><?= esc($produk) ?></td>
                            <td class="px-6 py-3.5 font-medium">
                                Rp <?= esc(number_format((float) ($order['total_harga'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc(getOrderStatusLabel($order)) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5" style="color:var(--text-muted);">
                                <?= esc(date('d M Y', strtotime($order['created_at'] ?? 'now'))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
