 <?php
    /**
     * @var array<string, mixed>         $user
     * @var array<string, mixed>         $pelanggan
     * @var array<string, mixed>|null    $verifikasiTerbaru
     * @var bool                         $inModal
     */
    $inModal        = $inModal ?? false;
    helper('notification');
    $isKerjasama    = pelangganIsKerjasamaPerusahaan($pelanggan);
    $oldInput       = static fn(string $key, string $fallback = '') => esc(old($key, $fallback));
    $sectionClass   = $inModal
        ? 'rounded-2xl border border-[#E2E8F0] bg-[#F8FAFF] p-5'
        : 'bg-white rounded-[20px] shadow-sm border border-[#E2E8F0] p-6';
    $batasTanpaDp   = number_format(BATAS_ORDER_TANPA_DP, 0, ',', '.');
    ?>

 <div class="<?= $inModal ? 'space-y-5' : 'grid grid-cols-1 xl:grid-cols-2 gap-6' ?>">
     <div class="<?= esc($sectionClass) ?>">
         <h3 class="text-base font-bold text-[#051747] mb-4">Data Akun</h3>

         <form id="profilAkunForm" method="post" action="<?= esc(site_url('profil')) ?>" class="space-y-4" novalidate>
             <?= csrf_field() ?>

             <div id="profilAkunAlert" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

             <div>
                 <label for="profil_nama" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Nama Lengkap</label>
                 <input id="profil_nama" type="text" name="nama" required minlength="3" maxlength="100"
                     pattern="[A-Za-zÀ-ÿ][A-Za-zÀ-ÿ\s.'\-]*"
                     title="Hanya boleh berisi huruf, spasi, tanda kutip, atau titik (3–100 karakter)"
                     value="<?= $oldInput('nama', (string) ($user['nama'] ?? '')) ?>"
                     class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                 <p class="text-xs text-slate-400 mt-1">Hanya huruf, spasi, tanda kutip, atau titik (3–100 karakter).</p>
             </div>

             <div>
                 <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Email</label>
                 <input type="email" value="<?= esc((string) ($user['email'] ?? '')) ?>" disabled
                     class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-slate-50 text-slate-500 cursor-not-allowed">
                 <p class="text-xs text-slate-400 mt-1">Email tidak dapat diubah dari halaman ini.</p>
             </div>

             <div>
                 <label for="profil_no_telp" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">No. Telepon</label>
                 <input id="profil_no_telp" type="tel" name="no_telp" required inputmode="tel"
                     pattern="^(\+62|08|022)[0-9]{8,13}$" maxlength="20"
                     title="Format harus berupa angka dan diawali dengan 08, +62, atau 022"
                     placeholder="087778965442"
                     value="<?= $oldInput('no_telp', (string) ($pelanggan['no_telp'] ?? '')) ?>"
                     class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10">
                 <p class="text-xs text-slate-400 mt-1">Format harus berupa angka dan diawali dengan 08, +62, atau 022 (Contoh: 087778965442) (8–13 digit setelah awalan).</p>
             </div>

             <div>
                 <label for="profil_alamat" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Alamat</label>
                 <textarea id="profil_alamat" name="alamat" rows="3" required minlength="10" maxlength="150"
                     class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"><?= $oldInput('alamat', (string) ($pelanggan['alamat'] ?? '')) ?></textarea>
                 <p class="text-xs text-slate-400 mt-1">Minimal 10 karakter · maks. 150 karakter.</p>
             </div>

             <button type="submit" id="profilAkunSubmitBtn"
                 class="bg-[#051747] text-white rounded-full px-5 py-2.5 text-sm font-bold uppercase hover:bg-[#2E5CE6] transition-colors">
                 Simpan Perubahan
             </button>
         </form>
     </div>

     <?php
        $isGoogleAkun = ! empty($user['google_id']);
        ?>
     <div class="<?= esc($sectionClass) ?>">
         <h3 class="text-base font-bold text-[#051747] mb-1">Keamanan</h3>
         <p class="text-sm text-slate-500 mb-4">Kelola keamanan akun Anda.</p>

         <?php if ($isGoogleAkun): ?>
             <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 space-y-2">
                 <p>Akun ini terhubung dengan Google. Untuk mengubah password, gunakan fitur Lupa Password pada halaman utama.</p>
                 <a href="<?= esc(site_url('lupa-sandi')) ?>"
                     class="inline-flex font-semibold text-[#2E5CE6] hover:underline">
                     Buka Lupa Password
                 </a>
             </div>
         <?php else: ?>
             <form id="profilPasswordForm" method="post" action="<?= esc(site_url('profil/ubah-password')) ?>" class="space-y-4" novalidate>
                 <?= csrf_field() ?>

                 <div id="profilPasswordAlert" class="hidden rounded-xl px-4 py-3 text-sm font-medium"></div>

                 <div>
                     <label for="profil_password_lama" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Password Saat Ini</label>
                     <div class="relative">
                         <input id="profil_password_lama" type="password" name="password_lama" required autocomplete="current-password"
                             class="w-full border border-slate-200 rounded-xl px-3 py-2.5 pr-11 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                             placeholder="Masukkan password saat ini">
                         <button type="button" data-toggle-password="profil_password_lama"
                             class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-[#2E5CE6] transition-colors"
                             aria-label="Tampilkan password">
                             <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                             </svg>
                         </button>
                     </div>
                 </div>

                 <div>
                     <label for="profil_password_baru" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Password Baru</label>
                     <div class="relative">
                         <input id="profil_password_baru" type="password" name="password" required minlength="8" autocomplete="new-password"
                             class="w-full border border-slate-200 rounded-xl px-3 py-2.5 pr-11 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                             placeholder="Minimal 8 karakter">
                         <button type="button" data-toggle-password="profil_password_baru"
                             class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-[#2E5CE6] transition-colors"
                             aria-label="Tampilkan password">
                             <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                             </svg>
                         </button>
                     </div>
                     <p class="text-xs text-slate-400 mt-1">Minimal 8 karakter.</p>
                 </div>

                 <div>
                     <label for="profil_password_confirm" class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Konfirmasi Password Baru</label>
                     <div class="relative">
                         <input id="profil_password_confirm" type="password" name="password_confirm" required minlength="8" autocomplete="new-password"
                             class="w-full border border-slate-200 rounded-xl px-3 py-2.5 pr-11 text-sm bg-white focus:border-[#2E5CE6] focus:outline-none focus:ring-2 focus:ring-[#2E5CE6]/10"
                             placeholder="Ulangi password baru">
                         <button type="button" data-toggle-password="profil_password_confirm"
                             class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-[#2E5CE6] transition-colors"
                             aria-label="Tampilkan password">
                             <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                 <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                             </svg>
                         </button>
                     </div>
                 </div>

                 <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                     <button type="submit" id="profilPasswordSubmitBtn"
                         class="bg-[#051747] text-white rounded-full px-5 py-2.5 text-sm font-bold uppercase hover:bg-[#2E5CE6] transition-colors">
                         Ubah Password
                     </button>
                     <a href="<?= esc(site_url('lupa-sandi')) ?>"
                         class="text-sm font-semibold text-[#2E5CE6] hover:underline">
                         Lupa password saat ini?
                     </a>
                 </div>
             </form>
         <?php endif; ?>
     </div>

     <?php if ($isKerjasama): ?>
         <div class="<?= esc($sectionClass) ?>">
             <div class="flex items-start justify-between gap-3 mb-4">
                 <h3 class="text-base font-bold text-[#051747]">Skema Pembayaran</h3>
                 <span class="inline-flex shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                     Kerja Sama Perusahaan
                 </span>
             </div>

             <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 space-y-3 mb-4">
                 <p>
                     Akun terdaftar sebagai kerja sama perusahaan
                     <strong><?= esc((string) ($pelanggan['nama_perusahaan'] ?? '')) ?></strong>.
                 </p>
                 <?php if ($verifikasiTerbaru !== null): ?>
                     <dl class="grid grid-cols-1 gap-2 text-xs">
                         <?php if (!empty($verifikasiTerbaru['jabatan_pic'])): ?>
                             <div>
                                 <dt class="font-semibold text-emerald-800">Jabatan PIC</dt>
                                 <dd><?= esc((string) $verifikasiTerbaru['jabatan_pic']) ?></dd>
                             </div>
                         <?php endif; ?>
                         <?php if (!empty($verifikasiTerbaru['wa_perusahaan'])): ?>
                             <div>
                                 <dt class="font-semibold text-emerald-800">WA Perusahaan</dt>
                                 <dd><?= esc((string) $verifikasiTerbaru['wa_perusahaan']) ?></dd>
                             </div>
                         <?php endif; ?>
                         <?php if (!empty($verifikasiTerbaru['alamat_kantor'])): ?>
                             <div>
                                 <dt class="font-semibold text-emerald-800">Alamat Kantor</dt>
                                 <dd><?= esc((string) $verifikasiTerbaru['alamat_kantor']) ?></dd>
                             </div>
                         <?php endif; ?>
                     </dl>
                 <?php endif; ?>
             </div>

             <div class="rounded-xl border border-[#E2E8F0] bg-[#F8FAFF] px-4 py-4 text-sm text-slate-600 space-y-3">
                 <p class="text-xs font-bold uppercase tracking-wide text-[#051747]">Ketentuan Kerja Sama Perusahaan</p>
                 <ul class="list-disc pl-5 space-y-2 text-sm leading-relaxed">
                     <li>Order hingga Rp <?= esc($batasTanpaDp) ?> tanpa DP; pelunasan setelah barang diterima (nota tagihan).</li>
                     <li>Order di atas Rp <?= esc($batasTanpaDp) ?> wajib DP 50%; sisa pelunasan tetap setelah barang diterima.</li>
                 </ul>
                 <div class="rounded-lg border border-amber-200 bg-amber-50 px-3.5 py-3 text-xs leading-relaxed text-amber-900">
                     Apabila kerja sama berakhir atau terdapat kendala pembayaran, Admin dapat mencabut status kerja sama.
                     Setelah dicabut, pesanan baru mengikuti skema perseorangan (DP 50%, pelunasan sebelum pengiriman).
                 </div>
                 <p class="text-xs text-slate-500 leading-relaxed">
                     Status kerja sama perusahaan ditetapkan oleh Admin Z'Plack berdasarkan kebijakan internal.
                 </p>
             </div>
         </div>
     <?php endif; ?>
 </div>