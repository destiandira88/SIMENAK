<?php
/**
 * @var array<string, mixed>         $user
 * @var array<string, mixed>         $pelanggan
 * @var array<string, mixed>|null    $verifikasiTerbaru
 * @var bool                         $inModal
 */
$inModal         = $inModal ?? false;
$isVerified      = (int) ($pelanggan['is_verified'] ?? 0) === 1;
$verifStatus     = (string) ($verifikasiTerbaru['status'] ?? '');
$canAjukanVerif  = !$isVerified && $verifStatus !== 'pending';
$orderLancarCount = (int) ($orderLancarCount ?? 0);
$tierPerusahaan  = (string) ($pelanggan['tier_perusahaan'] ?? '');
$isSuspended     = (int) ($pelanggan['is_suspended'] ?? 0) === 1;
helper('notification');
$oldInput        = static fn(string $key, string $fallback = '') => esc(old($key, $fallback));
$sectionClass    = $inModal
    ? 'rounded-2xl border border-[#E2E8F0] bg-[#F8FAFF] p-5'
    : 'bg-white rounded-[20px] shadow-sm border border-[#E2E8F0] p-6';
?>

<div class="<?= $inModal ? 'space-y-5' : 'grid grid-cols-1 xl:grid-cols-2 gap-6' ?>">
    <div class="<?= esc($sectionClass) ?>">
        <h3 class="text-base font-bold text-[#051747] mb-4">Data Akun</h3>

        <form method="post" action="<?= esc(site_url('profil')) ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama" required maxlength="100"
                    value="<?= $oldInput('nama', (string) ($user['nama'] ?? '')) ?>"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Email</label>
                <input type="email" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 text-slate-500 cursor-not-allowed">
                <p class="text-xs text-slate-400 mt-1">Email tidak dapat diubah dari halaman ini.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. Telepon</label>
                <input type="text" name="no_telp" required pattern="[0-9]{10,13}"
                    value="<?= $oldInput('no_telp', (string) ($pelanggan['no_telp'] ?? '')) ?>"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Alamat</label>
                <textarea name="alamat" rows="3" maxlength="150"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"><?= $oldInput('alamat', (string) ($pelanggan['alamat'] ?? '')) ?></textarea>
            </div>

            <button type="submit"
                class="bg-[#051747] text-white rounded-full px-5 py-2.5 text-sm font-bold uppercase hover:bg-[#2E5CE6] transition-colors">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <div class="<?= esc($sectionClass) ?>">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-[#051747]">Verifikasi Perusahaan</h3>
                <p class="text-sm text-slate-500 mt-1">Tier Pemula: wajib DP, pelunasan sebelum kirim. Tier Terpercaya: tanpa DP (≤ Rp 5 jt), pelunasan setelah diterima.</p>
            </div>
            <?php if ($isVerified): ?>
                <span class="inline-flex shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    ✓ Terverifikasi
                </span>
            <?php elseif ($verifStatus === 'pending'): ?>
                <span class="inline-flex shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                    Menunggu Review
                </span>
            <?php elseif ($verifStatus === 'rejected'): ?>
                <span class="inline-flex shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                    Ditolak
                </span>
            <?php endif; ?>
        </div>

        <?php if ($isVerified): ?>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 space-y-3">
                <p>
                    Akun terverifikasi sebagai perusahaan
                    <strong><?= esc((string) ($pelanggan['nama_perusahaan'] ?? '')) ?></strong>.
                </p>
                <?php if ($tierPerusahaan !== ''): ?>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold <?= esc(getTierPerusahaanBadgeClass($tierPerusahaan)) ?>">
                            Tier <?= esc(getTierPerusahaanLabel($tierPerusahaan)) ?>
                        </span>
                        <?php if ($isSuspended): ?>
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                Disuspend
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($tierPerusahaan === 'pemula' && !$isSuspended): ?>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 mb-1">Progress ke Tier Terpercaya</p>
                        <div class="h-2 rounded-full bg-emerald-200 overflow-hidden">
                            <div class="h-full bg-emerald-600 rounded-full transition-all" style="width: <?= esc((string) min(100, (int) round(($orderLancarCount / 3) * 100))) ?>%"></div>
                        </div>
                        <p class="text-xs mt-1 text-emerald-700"><?= esc((string) $orderLancarCount) ?>/3 order lancar selesai</p>
                    </div>
                    <p class="text-xs text-emerald-700">Saat membuat pesanan, pilih <strong>Kerja Sama Perusahaan</strong>. Wajib DP setiap order; pelunasan sebelum pengiriman.</p>
                <?php elseif ($tierPerusahaan === 'terpercaya' && !$isSuspended): ?>
                    <p class="text-xs text-emerald-700">Order ≤ Rp 5.000.000 tanpa DP. Order di atas Rp 5.000.000 wajib DP dulu; sisa pelunasan setelah barang diterima.</p>
                <?php elseif ($isSuspended): ?>
                    <p class="text-xs text-red-700">Akun perusahaan disuspend. Pesanan akan diproses sebagai perseorangan hingga suspend dicabut admin.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($verifStatus === 'pending'): ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 space-y-2">
                <p>Pengajuan verifikasi untuk <strong><?= esc((string) ($verifikasiTerbaru['nama_perusahaan'] ?? '')) ?></strong> sedang ditinjau admin.</p>
                <p class="text-xs text-amber-700">
                    Diajukan: <?= !empty($verifikasiTerbaru['tgl_pengajuan'])
                        ? esc(date('d M Y H:i', strtotime((string) $verifikasiTerbaru['tgl_pengajuan'])))
                        : '-' ?>
                </p>
            </div>

        <?php else: ?>
            <?php if ($verifStatus === 'rejected'): ?>
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 mb-4">
                    <p class="font-semibold">Pengajuan terakhir ditolak.</p>
                    <?php if (!empty($verifikasiTerbaru['catatan_admin'])): ?>
                        <p class="mt-1"><strong>Alasan:</strong> <?= esc((string) $verifikasiTerbaru['catatan_admin']) ?></p>
                    <?php endif; ?>
                    <p class="mt-2 text-xs">Perbaiki dokumen lalu ajukan ulang di bawah.</p>
                </div>
            <?php endif; ?>

            <?php if ($canAjukanVerif): ?>
                <form method="post"
                    action="<?= esc(site_url('profil/verifikasi-perusahaan')) ?>"
                    enctype="multipart/form-data"
                    class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Nama Perusahaan</label>
                        <input type="text" name="nama_perusahaan" required maxlength="150"
                            value="<?= $oldInput('nama_perusahaan', (string) ($verifikasiTerbaru['nama_perusahaan'] ?? $pelanggan['nama_perusahaan'] ?? '')) ?>"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Jabatan PIC</label>
                        <input type="text" name="jabatan_pic" required maxlength="100"
                            value="<?= $oldInput('jabatan_pic', (string) ($verifikasiTerbaru['jabatan_pic'] ?? '')) ?>"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. HP / WA Perusahaan</label>
                        <input type="text" name="wa_perusahaan" required pattern="[0-9]{10,13}" maxlength="13"
                            value="<?= $oldInput('wa_perusahaan', (string) ($verifikasiTerbaru['wa_perusahaan'] ?? '')) ?>"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Alamat Kantor</label>
                        <textarea name="alamat_kantor" required rows="2" maxlength="255"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"><?= $oldInput('alamat_kantor', (string) ($verifikasiTerbaru['alamat_kantor'] ?? '')) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. NPWP (opsional)</label>
                        <input type="text"
                            name="no_npwp"
                            data-npwp-input
                            maxlength="20"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="12.345.678.9-012.345"
                            value="<?= $oldInput('no_npwp', (string) ($verifikasiTerbaru['no_npwp'] ?? '')) ?>"
                            class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                        <p class="text-xs text-slate-400 mt-1">15 digit angka · format otomatis XX.XXX.XXX.X-XXX.XXX</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Dokumen NPWP</label>
                        <input type="file" name="dokumen_npwp" required accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#2E5CE6]">
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, atau PDF · maks. 2MB</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">KTP PIC / Penanggung Jawab</label>
                        <input type="file" name="dokumen_ktp_pic" required accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#2E5CE6]">
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, atau PDF · maks. 2MB</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">MOU / Surat Kerjasama / SPK (PDF)</label>
                        <input type="file" name="dokumen_mou" required accept=".pdf"
                            class="w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-[#051747] file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#2E5CE6]">
                        <p class="text-xs text-slate-400 mt-1">PDF bermaterai · maks. 2MB</p>
                    </div>

                    <button type="submit"
                        class="bg-[#051747] text-white rounded-full px-5 py-2.5 text-sm font-bold uppercase hover:bg-[#2E5CE6] transition-colors">
                        Ajukan Verifikasi
                    </button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
