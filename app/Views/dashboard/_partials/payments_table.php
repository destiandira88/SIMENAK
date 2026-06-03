<?php
/**
 * @var list<array<string, mixed>> $payments
 */
?>
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b" style="border-color:var(--border);">
        <h3 class="text-base font-bold" style="color:var(--navy);">Pembayaran Menunggu Verifikasi</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-white text-xs uppercase tracking-wider" style="background:var(--navy);">
                    <th class="px-6 py-3 font-semibold">Kode Order</th>
                    <th class="px-6 py-3 font-semibold">Pelanggan</th>
                    <th class="px-6 py-3 font-semibold">Jenis</th>
                    <th class="px-6 py-3 font-semibold">Nominal</th>
                    <th class="px-6 py-3 font-semibold">Status Order</th>
                    <th class="px-6 py-3 font-semibold">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments === []): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center" style="color:var(--text-muted);">
                            Tidak ada pembayaran menunggu verifikasi.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                        <?php $orderStatus = (string) ($payment['order_status'] ?? ''); ?>
                        <tr class="border-b transition-colors hover:bg-[#F8FAFF]" style="border-color:var(--border);">
                            <td class="px-6 py-3.5 font-semibold" style="color:var(--navy);">
                                <?= esc($payment['kode_order'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-3.5"><?= esc($payment['nama_pelanggan'] ?? '-') ?></td>
                            <td class="px-6 py-3.5 uppercase text-xs font-semibold">
                                <?= esc($payment['jenis'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-3.5 font-medium">
                                Rp <?= esc(number_format((float) ($payment['jumlah'] ?? 0), 0, ',', '.')) ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($orderStatus)) ?>">
                                    <?= esc(getStatusLabel($orderStatus)) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5" style="color:var(--text-muted);">
                                <?= esc(date('d M Y', strtotime($payment['created_at'] ?? 'now'))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
