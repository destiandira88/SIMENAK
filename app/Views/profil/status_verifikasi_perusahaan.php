<?php
/**
 * @var array<string, mixed>      $pelanggan
 * @var array<string, mixed>|null $verifikasiTerbaru
 * @var string                    $verifStatus
 * @var bool                      $showAjukanForm
 */
$oldInput = static fn(string $key, string $fallback = '') => esc(old($key, $fallback));
$namaPerusahaan = (string) ($verifikasiTerbaru['nama_perusahaan'] ?? $pelanggan['nama_perusahaan'] ?? '');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Status Verifikasi') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($page_title ?? 'Status Verifikasi Perusahaan') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= view('partials/flash_toast') ?>

<div class="max-w-3xl mx-auto">
    <?php if ($verifStatus === 'pending'): ?>
        <div class="bg-white rounded-[20px] border border-[#E2E8F0] shadow-sm p-8 text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-2xl font-extrabold text-[#051747]">Menunggu Verifikasi Owner</h2>
            <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                Pengajuan kerjasama untuk <strong><?= esc($namaPerusahaan) ?></strong> sedang ditinjau oleh Owner Z'Plack.
            </p>
            <p class="mt-2 text-xs text-slate-500">
                Diajukan: <?= !empty($verifikasiTerbaru['tgl_pengajuan'])
                    ? esc(date('d M Y H:i', strtotime((string) $verifikasiTerbaru['tgl_pengajuan'])))
                    : '-' ?>
            </p>
            <p class="mt-6 text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 leading-relaxed space-y-2">
                <span class="block">Proses verifikasi dilakukan paling lambat 1–2 hari kerja sejak pengajuan diterima.</span>
                <span class="block">Akses pemesanan akan tersedia setelah pengajuan disetujui.</span>
            </p>
        </div>

    <?php elseif ($verifStatus === 'rejected' && !$showAjukanForm): ?>
        <div class="bg-white rounded-[20px] border border-red-200 shadow-sm p-8">
            <div class="text-center mb-6">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-red-100 text-red-700 text-2xl">✕</div>
                <h2 class="text-2xl font-extrabold text-[#051747]">Pengajuan Ditolak</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Verifikasi perusahaan <strong><?= esc($namaPerusahaan) ?></strong> ditolak oleh Owner.
                </p>
            </div>

            <?php if (!empty($verifikasiTerbaru['catatan_admin'])): ?>
                <div class="notice-danger rounded-xl px-4 py-3 text-sm mb-6">
                    <p class="font-semibold">Alasan penolakan</p>
                    <p class="mt-1"><?= esc((string) $verifikasiTerbaru['catatan_admin']) ?></p>
                </div>
            <?php endif; ?>

            <p class="text-sm text-slate-600 text-center mb-6">
                Anda tetap dapat menggunakan SIMENAK. Pilih langkah berikutnya:
            </p>

            <div class="grid sm:grid-cols-2 gap-4">
                <form method="post" action="<?= esc(site_url('status-verifikasi-perusahaan/lanjut-perseorangan')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit"
                        class="w-full h-full min-h-[120px] rounded-2xl border-2 border-[#051747] bg-white px-5 py-4 text-left transition-colors hover:bg-[#051747] hover:text-white group">
                        <span class="block text-sm font-bold uppercase tracking-wide">Lanjut sebagai akun perseorangan</span>
                        <span class="mt-2 block text-xs opacity-80 group-hover:text-white/90">
                            Ubah jenis akun ke perseorangan dan langsung bisa membuat pesanan.
                        </span>
                    </button>
                </form>

                <a href="<?= esc(site_url('status-verifikasi-perusahaan?ajukan=1')) ?>"
                    class="flex min-h-[120px] flex-col justify-center rounded-2xl border-2 border-[#2E5CE6] bg-[#EEF4FF] px-5 py-4 text-left transition-colors hover:bg-[#2E5CE6] hover:text-white group">
                    <span class="block text-sm font-bold uppercase tracking-wide text-[#051747] group-hover:text-white">Ajukan ulang verifikasi perusahaan</span>
                    <span class="mt-2 block text-xs text-slate-600 group-hover:text-white/90">
                        Kirim dokumen perusahaan yang diperbaiki untuk ditinjau ulang Owner.
                    </span>
                </a>
            </div>
        </div>

    <?php elseif ($verifStatus === 'rejected' && $showAjukanForm): ?>
        <div class="bg-white rounded-[20px] border border-[#E2E8F0] shadow-sm p-6 md:p-8">
            <div class="mb-6">
                <a href="<?= esc(site_url('status-verifikasi-perusahaan')) ?>" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#2E5CE6] hover:underline">
                    <?= view('partials/order_detail_svg_icon', ['icon' => 'arrow-left', 'class' => 'h-4 w-4 shrink-0']) ?>
                    Kembali ke pilihan
                </a>
                <h2 class="mt-3 text-xl font-extrabold text-[#051747]">Ajukan Ulang Verifikasi Perusahaan</h2>
                <p class="mt-1 text-sm text-slate-500">Lengkapi data dan dokumen yang diperbaiki.</p>
            </div>

            <?= view('partials/verifikasi_perusahaan_form', [
                'formAction' => site_url('profil/verifikasi-perusahaan'),
                'pelanggan'  => $pelanggan,
                'verifikasiTerbaru' => $verifikasiTerbaru,
            ]) ?>
        </div>

    <?php else: ?>
        <div class="bg-white rounded-[20px] border border-[#E2E8F0] shadow-sm p-8">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-extrabold text-[#051747]">Belum Ada Pengajuan Verifikasi</h2>
                <p class="mt-2 text-sm text-slate-600">Lengkapi dokumen perusahaan untuk mengajukan verifikasi ke Owner.</p>
            </div>
            <?= view('partials/verifikasi_perusahaan_form', [
                'formAction' => site_url('profil/verifikasi-perusahaan'),
                'pelanggan'  => $pelanggan,
                'verifikasiTerbaru' => $verifikasiTerbaru,
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
