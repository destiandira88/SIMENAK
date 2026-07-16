<?php helper('notification'); ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Manajemen Pengiriman<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Manajemen Pengiriman<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Manajemen Pengiriman<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Pantau pesanan yang siap dikirim atau menunggu konfirmasi pengambilan.<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end mb-4">
    <label for="pengirimanSearch" class="sr-only">Cari pengiriman</label>
    <div class="search-control w-full sm:w-auto">
        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
        </svg>
        <input
            id="pengirimanSearch"
            type="search"
            placeholder="Cari kode, pelanggan, jenis, status, alamat..."
            autocomplete="off">
    </div>
</div>

<div class="admin-data-table-wrap">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#051747] text-white text-xs uppercase">
                    <th class="px-4 py-3 text-left font-semibold w-12">No</th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="kode">
                        Kode Pesanan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="pelanggan">
                        Pelanggan<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="jenis">
                        Jenis<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="status">
                        Status<span class="sort-icon">↕</span>
                    </th>
                    <th class="sortable-th px-4 py-3 text-left font-semibold" data-sort="metode">
                        Metode<span class="sort-icon">↕</span>
                    </th>
                    <th class="px-4 py-3 text-left font-semibold min-w-[180px]">Alamat Tujuan</th>
                    <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody id="pengirimanBody">
                <?php if (empty($orders)): ?>
                    <tr id="emptyDataRow">
                        <td colspan="8" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada pesanan dalam antrian pengiriman.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $index => $o):
                        $idOrder      = (int) ($o['id_order'] ?? 0);
                        $kodeOrder    = (string) ($o['kode_order'] ?? '');
                        $namaPelanggan = (string) ($o['nama_pelanggan'] ?? '');
                        $status       = (string) ($o['status'] ?? '');
                        $jenis        = (string) ($o['jenis_pelanggan'] ?? 'perseorangan');
                        $metode       = (string) ($o['metode_pengiriman'] ?? 'kurir');
                        $pg           = $pengirimanByOrder[$idOrder] ?? null;
                        $beforeShip   = isPelunasanSebelumKirim($o);
                        $ambilSendiri = $metode === 'ambil_sendiri';
                        $alamatKirim  = trim((string) ($o['alamat_kirim'] ?? ''));
                        $jenisLabel   = $jenis === 'perusahaan' ? 'perusahaan' : 'perseorangan';
                        $metodeLabel  = str_replace('_', ' ', $metode);
                        $statusLabel  = getOrderStatusLabel($o);
                        $searchText   = mb_strtolower(trim(
                            $kodeOrder . ' ' . $namaPelanggan . ' ' . $jenisLabel . ' ' . $jenis
                            . ' ' . $status . ' ' . $statusLabel . ' ' . $metodeLabel . ' ' . $metode . ' ' . $alamatKirim
                        ));
                        $alamatSingkat = '';
                        if (!$ambilSendiri && $alamatKirim !== '') {
                            $alamatSingkat = mb_strlen($alamatKirim) > 55
                                ? mb_substr($alamatKirim, 0, 55) . '…'
                                : $alamatKirim;
                        }
                    ?>
                        <tr
                            class="data-table-row border-b border-slate-100 hover:bg-[#F8FAFF] transition-colors"
                            data-search="<?= esc($searchText) ?>"
                            data-kode="<?= esc(mb_strtolower($kodeOrder)) ?>"
                            data-pelanggan="<?= esc(mb_strtolower($namaPelanggan)) ?>"
                            data-jenis="<?= esc($jenisLabel) ?>"
                            data-status="<?= esc(mb_strtolower($status)) ?>"
                            data-metode="<?= esc(mb_strtolower($metode)) ?>">
                            <td class="row-num px-4 py-3 text-slate-600"><?= esc((string) ($index + 1)) ?></td>
                            <td class="px-4 py-3 font-semibold text-[#051747]"><?= esc($kodeOrder) ?></td>
                            <td class="px-4 py-3 text-slate-600"><?= esc($namaPelanggan) ?></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $jenis === 'perusahaan' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700' ?>">
                                    <?= $jenis === 'perusahaan' ? 'Perusahaan' : 'Perseorangan' ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-3 py-1 rounded-full text-[11px] font-semibold <?= esc(getStatusBadgeClass($status)) ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs capitalize">
                                <?= esc($metodeLabel) ?>
                            </td>
                            <td class="px-4 py-3 text-xs max-w-[220px]">
                                <?php if ($ambilSendiri): ?>
                                    <span class="text-slate-400 italic">Ambil di toko</span>
                                <?php elseif ($alamatSingkat !== ''): ?>
                                    <p
                                        class="text-slate-600 leading-snug"
                                        title="<?= esc($alamatKirim) ?>">
                                        <?= esc($alamatSingkat) ?>
                                    </p>
                                    <?php if (mb_strlen($alamatKirim) > 55): ?>
                                        <a
                                            href="<?= esc(site_url('order/detail/' . $kodeOrder)) ?>"
                                            class="inline-block mt-1 text-[10px] font-semibold text-[#2E5CE6] hover:underline">
                                            Lihat lengkap
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-amber-700 bg-amber-50 border border-amber-100 rounded px-2 py-0.5">
                                        Belum diisi
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 min-w-[220px]">
                                <?php if ($status === 'finishing'): ?>
                                    <form method="post" action="<?= esc(site_url('pengiriman/' . $idOrder)) ?>" class="flex gap-2 flex-wrap items-center">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="set_siap">
                                        <span class="text-xs text-slate-500 italic">
                                            <?= $ambilSendiri ? 'Ambil Sendiri' : 'Kurir' ?>
                                        </span>
                                        <button type="submit" class="bg-[#051747] text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-[#2E5CE6] transition-colors">
                                            Set Siap
                                        </button>
                                    </form>

                                <?php elseif (!$ambilSendiri && $status === 'pelunasan_terverifikasi' && $beforeShip): ?>
                                    <form method="post" action="<?= esc(site_url('pengiriman/' . $idOrder)) ?>" class="space-y-1.5">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="set_dikirim">
                                        <input type="text" name="no_resi" placeholder="No. Resi" required
                                            class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 w-full focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        <input type="text" name="nama_ekspedisi" placeholder="Ekspedisi (JNE, JNT…)"
                                            class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 w-full focus:outline-none">
                                        <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-emerald-700 transition-colors w-full">
                                            Kirim
                                        </button>
                                    </form>

                                <?php elseif (!$ambilSendiri && $status === 'siap_kirim' && !$beforeShip): ?>
                                    <form method="post" action="<?= esc(site_url('pengiriman/' . $idOrder)) ?>" class="space-y-1.5">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="set_dikirim">
                                        <input type="text" name="no_resi" placeholder="No. Resi" required
                                            class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 w-full focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        <input type="text" name="nama_ekspedisi" placeholder="Ekspedisi (JNE, JNT…)"
                                            class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 w-full focus:outline-none">
                                        <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-emerald-700 transition-colors w-full">
                                            Kirim
                                        </button>
                                    </form>

                                <?php elseif ($ambilSendiri && $status === 'pelunasan_terverifikasi' && $beforeShip): ?>
                                    <form method="post" action="<?= esc(site_url('pengiriman/' . $idOrder)) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="konfirmasi_diambil">
                                        <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-emerald-700 transition-colors">
                                            Konfirmasi Diambil
                                        </button>
                                    </form>

                                <?php elseif ($ambilSendiri && $status === 'siap_diambil' && !$beforeShip): ?>
                                    <form method="post" action="<?= esc(site_url('pengiriman/' . $idOrder)) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="aksi" value="konfirmasi_diambil">
                                        <button type="submit" class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-emerald-700 transition-colors">
                                            Konfirmasi Diambil
                                        </button>
                                    </form>

                                <?php elseif (in_array($status, ['siap_kirim', 'siap_diambil', 'menunggu_verifikasi_lunas'], true) && $beforeShip): ?>
                                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                                        Menunggu pelunasan &amp; verifikasi keuangan pelanggan.
                                    </p>

                                <?php elseif ($status === 'dikirim'): ?>
                                    <div class="text-xs text-slate-500 space-y-0.5">
                                        <?php if ($pg && !empty($pg['no_resi'])): ?>
                                            <p>Resi: <strong class="text-slate-700"><?= esc($pg['no_resi']) ?></strong>
                                                <?php if (!empty($pg['nama_ekspedisi'])): ?>
                                                    <span class="text-slate-400">via <?= esc($pg['nama_ekspedisi']) ?></span>
                                                <?php endif; ?>
                                            </p>
                                            <?php if (!empty($pg['tgl_kirim'])): ?>
                                                <p class="text-slate-400">Dikirim: <?= esc(date('d M Y', strtotime($pg['tgl_kirim']))) ?></p>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p>Sedang dikirim-menunggu konfirmasi pelanggan.</p>
                                        <?php endif; ?>
                                    </div>

                                <?php else: ?>
                                    <span class="text-xs text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="emptyFilterRow" class="hidden">
                        <td colspan="8" class="py-12 text-center text-slate-500 text-sm">
                            Tidak ada data yang cocok dengan pencarian.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($orders)): ?>
        <?= view('partials/admin_data_table_footer', [
            'entriesId'     => 'pengirimanEntries',
            'entriesInfoId' => 'pengirimanEntriesInfo',
            'paginationId'  => 'pengirimanPagination',
            'prevPageId'    => 'pengirimanPrevPage',
            'nextPageId'    => 'pengirimanNextPage',
            'pageInfoId'    => 'pengirimanPageInfo',
        ]) ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (!empty($orders)): ?>
<script>
    window.adminDataTableConfig = {
        searchId: 'pengirimanSearch',
        tbodyId: 'pengirimanBody',
        emptyFilterRowId: 'emptyFilterRow',
        entriesId: 'pengirimanEntries',
        entriesInfoId: 'pengirimanEntriesInfo',
        paginationId: 'pengirimanPagination',
        prevPageId: 'pengirimanPrevPage',
        nextPageId: 'pengirimanNextPage',
        pageInfoId: 'pengirimanPageInfo',
        rowSelector: 'tr.data-table-row',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<?php endif; ?>
<?= $this->endSection() ?>
