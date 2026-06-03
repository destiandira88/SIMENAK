<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 */
$pendingCustom = (int) ($cards[2]['value'] ?? 0);
$pendingKirim  = (int) ($cards[3]['value'] ?? 0);
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Dashboard Admin<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-[#051747]">
        Selamat Datang, Admin 👋
    </h2>
    <p class="mt-1 text-sm text-slate-500">
        Pantau seluruh aktivitas pemesanan.
    </p>
</div>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<?php if ($pendingCustom > 0 || $pendingKirim > 0): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <h3 class="font-semibold text-amber-800 mb-3">⚡ Perlu Tindakan</h3>
        <div class="space-y-3">
            <?php if ($pendingCustom > 0): ?>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-amber-900">
                        <span class="mr-1">⭐</span>
                        <?= esc((string) $pendingCustom) ?> pemesanan custom menunggu konfirmasi harga
                    </p>
                    <a href="<?= site_url('list-pemesanan?tab=menunggu-harga') ?>" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-800 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-amber-900">
                        Tinjau →
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($pendingKirim > 0): ?>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-amber-900">
                        <span class="mr-1">🚚</span>
                        <?= esc((string) $pendingKirim) ?> pesanan siap untuk dikirim
                    </p>
                    <a href="<?= site_url('pengiriman') ?>" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-800 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-amber-900">
                        Kelola →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?= view('dashboard/_partials/recent_orders_table', [
    'orders'            => $recentOrders,
    'variant'           => 'admin',
    'sectionTitle'      => 'Pesanan Terbaru',
    'searchId'          => 'adminDashboardSearch',
    'tbodyId'           => 'adminDashboardBody',
    'searchPlaceholder' => 'Cari kode, pelanggan, produk...',
]) ?>

<div id="deleteModal" class="fixed inset-0 z-[80] hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative w-full max-w-md bg-white border border-[#E2E8F0] rounded-[20px] shadow-lg p-6 z-10">
        <h4 class="text-lg font-bold text-[#051747]">Konfirmasi Hapus</h4>
        <p class="mt-2 text-sm text-slate-500">
            Yakin ingin menghapus pesanan <span id="deleteModalKode" class="font-mono font-semibold text-[#051747]"></span>?
        </p>
        <form id="deleteForm" method="post" action="" class="mt-6 flex gap-3">
            <?= csrf_field() ?>
            <button type="button" onclick="closeDeleteModal()" class="flex-1 rounded-full border border-[#051747] px-4 py-2.5 text-sm font-bold uppercase tracking-wide text-[#051747] transition-colors hover:bg-[#051747] hover:text-white">
                Batal
            </button>
            <button type="submit" class="flex-1 rounded-full bg-red-500 px-4 py-2.5 text-sm font-bold uppercase tracking-wide text-white transition-colors hover:bg-red-600">
                Hapus
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'adminDashboardSearch',
        filterId: '',
        tbodyId: 'adminDashboardBody',
        emptyFilterRowId: 'emptyFilterRow',
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<script>
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    const deleteModalKode = document.getElementById('deleteModalKode');

    function confirmDelete(kode) {
        if (!deleteModal || !deleteForm || !deleteModalKode) return;

        deleteModalKode.textContent = kode;
        deleteForm.action = '<?= site_url('pesanan/delete/') ?>' + encodeURIComponent(kode);
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeDeleteModal() {
        if (!deleteModal) return;

        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('[data-dashboard-delete]').forEach((btn) => {
        btn.addEventListener('click', () => {
            confirmDelete(btn.getAttribute('data-dashboard-delete') || '');
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
<?= $this->endSection() ?>
