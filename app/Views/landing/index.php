<?= $this->extend('layouts/landing') ?>

<?= $this->section('title') ?>SIMENAK Z'Plack<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$isLoggedIn = (bool) session()->get('isLoggedIn');
$katalogAktif = isset($katalogAktif) && is_array($katalogAktif) ? $katalogAktif : [];
$tabLabels = [
    'all' => 'Semua',
    'desain_grafis' => 'Desain',
    'cetak_digital' => 'Digital',
    'cetak_offset' => 'Offset',
    'media_promosi' => 'Promosi',
];
?>

<section class="relative overflow-visible">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 grid lg:grid-cols-2 gap-10 items-center">
        <div>
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white text-[10px] uppercase tracking-[0.2em] text-[#2E5CE6] font-bold shadow-sm border border-slate-100">Cetak Presisi Tinggi Selaras Kebutuhan</span>
            <h1 class="mt-6 text-4xl md:text-6xl leading-tight font-extrabold text-[#051747]">
                Layanan Cetak
                <span class="block bg-gradient-to-r from-blue-950 via-blue-900 to-indigo-950 bg-clip-text text-transparent">Profesional &amp; Estetik.</span>
            </h1>
            <p class="mt-5 text-sm md:text-base text-slate-600 max-w-xl">Gunakan sistem pesanan pintar <strong>SIMENAK Z'Plack</strong> untuk melayani cetak kemasan eksklusif, digital printing, offset printing, hingga undangan mewah dengan material premium.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <button type="button" data-open-modal="<?= $isLoggedIn ? '' : 'loginModal' ?>" onclick="<?= $isLoggedIn ? "window.location.href='" . site_url('katalog') . "'" : '' ?>" class="btn-primary px-6 py-3 text-xs">Pesan Sekarang</button>
                <a href="#services" class="btn-outline px-6 py-3 text-xs">Pelajari Layanan</a>
            </div>
            <div class="mt-10 grid grid-cols-3 gap-4 max-w-md">
                <div class="rounded-xl border border-[#E2E8F0] bg-white px-3 py-4 shadow-sm">
                    <p class="text-3xl font-extrabold text-[#051747]">0.01mm</p>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500 font-semibold">Presisi Ultra</p>
                </div>
                <div class="rounded-xl border border-[#E2E8F0] bg-white px-3 py-4 shadow-sm">
                    <p class="text-3xl font-extrabold text-[#051747]">Bintang 5</p>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500 font-semibold">Kontrol Kualitas</p>
                </div>
                <div class="rounded-xl border border-[#E2E8F0] bg-white px-3 py-4 shadow-sm">
                    <p class="text-3xl font-extrabold text-[#051747]">100%</p>
                    <p class="text-[10px] uppercase tracking-[0.14em] text-slate-500 font-semibold">Kepuasan Desain</p>
                </div>
            </div>
        </div>
        <div class="relative overflow-visible pb-8 sm:pb-10 flex items-center justify-center lg:justify-end">
            <div class="relative w-full max-w-lg">
                <div class="relative overflow-hidden rounded-2xl border border-[#E2E8F0] bg-gradient-to-br from-[#F0F2F8] via-white to-[#E8EEF8] shadow-[0_12px_40px_rgba(5,23,71,0.08)] ring-1 ring-white/70 h-72 sm:h-80">
                <?php
                $heroFiles = [
                    'illustration.png',
                    'ilustrastion.png',
                    'illustration2.png',
                    'ilustrastion2.png',
                    '3.png',
                    'illustration.jpg',
                    'illustration.svg',
                ];
                $heroUrl = null;

                foreach ($heroFiles as $heroFile) {
                    if (is_file(FCPATH . 'assets/' . $heroFile)) {
                        $heroUrl = base_url('assets/' . $heroFile);
                        break;
                    }
                }
                ?>
                <?php if ($heroUrl !== null): ?>
                    <img
                        src="<?= esc($heroUrl) ?>"
                        alt="Ilustrasi Percetakan Kustom"
                        class="w-full h-full object-cover object-center" />
                <?php else: ?>
                    <div class="flex h-full flex-col items-center justify-center border border-dashed border-slate-200 bg-white/60 text-center text-slate-400 px-4">
                        <span class="text-sm">Gambar ilustrasi tidak ditemukan.</span>
                        <span class="mt-1 text-xs text-slate-400">Simpan sebagai <code class="font-mono">public/assets/illustration.png</code></span>
                    </div>
                <?php endif; ?>
                </div>

                <div class="absolute z-20 -bottom-4 -left-3 sm:-bottom-5 sm:-left-5 max-w-[260px] flex items-start gap-3 rounded-2xl bg-[#051747] px-4 py-3.5 text-white shadow-[0_12px_32px_rgba(5,23,71,0.28)] ring-1 ring-white/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-[#6b9fff]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold leading-snug tracking-wide">Garansi Cetak Ulang</p>
                        <p class="mt-1 text-[10px] leading-relaxed text-white/75">Jika hasil tidak sesuai mockup yang disetujui</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="about" class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-10 items-center">
            <!-- LEFT: TEKSTUAL -->
            <div class="max-w-2xl">
                <p class="uppercase text-[11px] tracking-[0.2em] font-bold" style="color:#2E5CE6;">SIAPA KAMI</p>
                <h2 class="mt-2 text-4xl font-extrabold text-[#051747]">SIMENAK Z'Plack</h2>
                <h3 class="mt-2 text-2xl md:text-4xl font-extrabold bg-gradient-to-r from-[#051747] via-[#2E5CE6] via-60% to-[#83A2CD] bg-clip-text text-transparent" style="text-shadow: 0 2px 12px #83A2CD40;">
                    <span style="background: linear-gradient(90deg, #051747 0%, #2E5CE6 30%, #83A2CD 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        Mitra Cetak Terpercaya Sejak 2010.
                    </span>
                </h3>

                <p class="mt-5 text-[14px] text-[#4A5568] leading-relaxed">
                    Z'Plack melayani percetakan digital, offset, desain grafis, dan
                    media promosi dalam satu platform pesanan yang terstruktur.
                    Setiap pesanan dikelola dari awal hingga selesai-terpusat,
                    terdokumentasi, dan dapat dipantau secara langsung oleh pelanggan.
                </p>
                <!-- 2x2 GRID HIGHLIGHT CARDS -->
                <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Card 1 -->
                    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm p-4 flex flex-col h-full">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[#2E5CE6] text-lg font-bold leading-none">✦</span>
                            <span class="text-[15px] font-bold" style="color:#051747;">Pesanan Terpusat</span>
                        </div>
                        <p class="text-[13px] text-slate-500 mt-1">
                            Semua pesanan masuk dalam satu platform, tidak lagi tersebar di WhatsApp atau email.
                        </p>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm p-4 flex flex-col h-full">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[#2E5CE6] text-lg font-bold leading-none">✦</span>
                            <span class="text-[15px] font-bold" style="color:#051747;">Pelacakan Status Real-Time</span>
                        </div>
                        <p class="text-[13px] text-slate-500 mt-1">
                            Pelanggan dapat memantau progres pesanan kapan saja tanpa perlu menghubungi admin.
                        </p>
                    </div>
                    <!-- Card 3 -->
                    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm p-4 flex flex-col h-full">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[#2E5CE6] text-lg font-bold leading-none">✦</span>
                            <span class="text-[15px] font-bold" style="color:#051747;">Revisi Desain Terstruktur</span>
                        </div>
                        <p class="text-[13px] text-slate-500 mt-1">
                            Proses revisi dikelola dengan approval history dan kuota yang jelas per pesanan.
                        </p>
                    </div>
                    <!-- Card 4 -->
                    <div class="bg-white border border-[#E2E8F0] rounded-xl shadow-sm p-4 flex flex-col h-full">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[#2E5CE6] text-lg font-bold leading-none">✦</span>
                            <span class="text-[15px] font-bold" style="color:#051747;">Pembayaran Terpadu</span>
                        </div>
                        <p class="text-[13px] text-slate-500 mt-1">
                            Bukti bayar diunggah langsung di sistem, verifikasi tercatat dan terhubung dengan data pesanan.
                        </p>
                    </div>
                </div>
            </div>
            <!-- RIGHT: VISUAL MOCKUP -->
            <div class="relative flex items-center justify-center">
                <div class="relative bg-[#F0F2F8] rounded-2xl border border-[#E2E8F0] shadow-lg p-6 md:p-8 flex flex-col items-center justify-center w-full h-[360px] min-w-[290px] ring-1 ring-white/80">
                    <div class="flex flex-col items-center justify-center h-full">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-200 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <rect x="3.5" y="4.5" width="17" height="12" rx="2" stroke="currentColor" fill="none" />
                                <rect x="7.5" y="18" width="9" height="1.7" rx="0.85" fill="#e5e7eb" />
                                <rect x="10.5" y="15" width="3" height="1.3" rx="0.65" fill="#cbd5e1" />
                            </svg>
                        </span>
                        <span class="block text-center font-bold text-lg md:text-xl text-slate-400 tracking-wide">
                            Beranda SIMENAK
                        </span>
                    </div>
                    <div class="absolute left-5 bottom-5 bg-[#051747] rounded-xl px-4 py-3 flex items-center shadow-md">
                        <span class="text-white text-[13px] font-bold tracking-wide leading-none flex items-center">
                            ✦ <span class="ml-2">200+ Pesanan Terkelola</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="bg-[#f8fafc] px-2 md:px-0 py-20">
    <div class="max-w-7xl mx-auto">
        <!-- Heading Area -->
        <div class="text-center mb-12">
            <p class="text-[10px] md:text-[11px] uppercase tracking-[0.17em] text-[#2E5CE6] font-bold mb-1" style="letter-spacing:0.2em;">LAYANAN KAMI</p>
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-[#162044] mb-2 tracking-wide"
                style="letter-spacing:.03em;">CETAK UNTUK BISNIS & KEBUTUHAN PRIBADI</h2>
            <p class="text-center text-[13px] md:text-[14px] text-[#71809e] font-normal max-w-2xl mx-auto mb-0"
                style="line-height:1.75">
                Didukung proses kerja yang terstruktur untuk menghasilkan kualitas cetak yang optimal.
            </p>
        </div>
        <!-- Service Cards Modern Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <div class="service-card group bg-white/80 border border-[#eaf0fa] rounded-2xl shadow-none hover:shadow-md hover:border-[#4A5568] transition-all duration-300 px-8 py-8 flex flex-col justify-between min-h-[260px] cursor-pointer relative overflow-hidden">
                <span class="absolute top-6 right-6 bg-[#eaf0fa] rounded-full h-8 w-8 flex items-center justify-center">
                    <svg width="18" height="18" fill="none">
                        <circle cx="9" cy="9" r="8" fill="#324da8" /><text x="9" y="12.5" text-anchor="middle" fill="#fff" font-size="13" font-weight="bold">🎴</text>
                    </svg>
                </span>
                <span class="inline-block bg-[#f5f8fd] text-[#767e95] rounded-full text-[11px] tracking-wide uppercase font-semibold px-3 py-1 mb-3"
                    style="font-size:11px; letter-spacing:0.1em;">Paling Populer</span>
                <h4 class="text-[#081735] font-bold text-[15px] mb-2">Undangan & Kartu Cetak</h4>
                <p class="text-[#757da5] text-[13px]">
                    Cetak undangan pernikahan, khitanan, dan kartu nama dengan pilihan material dan finishing sesuai kebutuhan.
                </p>
                <div class="service-card-track flex items-center gap-1 mt-5 text-xs text-[#9ba6be] font-semibold tracking-wide transition-all duration-300 ease-out group-hover:translate-x-2 group-hover:tracking-wider">
                    <span>KARTU EKSKLUSIF</span>
                    <span class="ml-auto transition-transform duration-300 ease-out group-hover:translate-x-0.5">&rsaquo;</span>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="service-card group bg-white/80 border border-[#eaf0fa] rounded-2xl shadow-none hover:shadow-md hover:border-[#4A5568] transition-all duration-300 px-8 py-8 flex flex-col justify-between min-h-[260px] cursor-pointer relative overflow-hidden">
                <span class="absolute top-6 right-6 bg-[#eaf0fa] rounded-full h-8 w-8 flex items-center justify-center">
                    <svg width="18" height="18" fill="none">
                        <circle cx="9" cy="9" r="8" fill="#324da8" /><text x="9" y="12.5" text-anchor="middle" fill="#fff" font-size="13" font-weight="bold">🖨</text>
                    </svg>
                </span>
                <span class="inline-block bg-[#f5f8fd] text-[#767e95] rounded-full text-[11px] tracking-wide uppercase font-semibold px-3 py-1 mb-3"
                    style="font-size:11px; letter-spacing:0.1em;">Produksi Volume Tinggi</span>
                <h4 class="text-[#081735] font-bold text-[15px] mb-2">Cetak Offset</h4>
                <p class="text-[#757da5] text-[13px]">
                    Produksi massal untuk brosur, map, kop surat, kalender, dan kebutuhan cetak korporat dengan hasil yang konsisten.
                </p>
                <div class="service-card-track flex items-center gap-1 mt-5 text-xs text-[#9ba6be] font-semibold tracking-wide transition-all duration-300 ease-out group-hover:translate-x-2 group-hover:tracking-wider">
                    <span>CETAK OFFSET</span>
                    <span class="ml-auto transition-transform duration-300 ease-out group-hover:translate-x-0.5">&rsaquo;</span>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="service-card group bg-white/80 border border-[#eaf0fa] rounded-2xl shadow-none hover:shadow-md hover:border-[#4A5568] transition-all duration-300 px-8 py-8 flex flex-col justify-between min-h-[260px] cursor-pointer relative overflow-hidden">
                <span class="absolute top-6 right-6 bg-[#eaf0fa] rounded-full h-8 w-8 flex items-center justify-center">
                    <svg width="18" height="18" fill="none">
                        <circle cx="9" cy="9" r="8" fill="#324da8" /><text x="9" y="12.5" text-anchor="middle" fill="#fff" font-size="13" font-weight="bold">🖼</text>
                    </svg>
                </span>
                <span class="inline-block bg-[#f5f8fd] text-[#767e95] rounded-full text-[11px] tracking-wide uppercase font-semibold px-3 py-1 mb-3"
                    style="font-size:11px; letter-spacing:0.1em;">Pengerjaan Cepat</span>
                <h4 class="text-[#081735] font-bold text-[15px] mb-2">Cetak Digital</h4>
                <p class="text-[#757da5] text-[13px]">
                    Stiker, poster, banner, dan media promosi dengan kualitas detail tinggi serta proses pengerjaan yang cepat.
                </p>
                <div class="service-card-track flex items-center gap-1 mt-5 text-xs text-[#9ba6be] font-semibold tracking-wide transition-all duration-300 ease-out group-hover:translate-x-2 group-hover:tracking-wider">
                    <span>CETAK DIGITAL</span>
                    <span class="ml-auto transition-transform duration-300 ease-out group-hover:translate-x-0.5">&rsaquo;</span>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="service-card group bg-white/80 border border-[#eaf0fa] rounded-2xl shadow-none hover:shadow-md hover:border-[#4A5568] transition-all duration-300 px-8 py-8 flex flex-col justify-between min-h-[260px] cursor-pointer relative overflow-hidden">
                <span class="absolute top-6 right-6 bg-[#eaf0fa] rounded-full h-8 w-8 flex items-center justify-center">
                    <svg width="18" height="18" fill="none">
                        <circle cx="9" cy="9" r="8" fill="#324da8" /><text x="9" y="12.5" text-anchor="middle" fill="#fff" font-size="13" font-weight="bold">🎨</text>
                    </svg>
                </span>
                <span class="inline-block bg-[#f5f8fd] text-[#767e95] rounded-full text-[11px] tracking-wide uppercase font-semibold px-3 py-1 mb-3"
                    style="font-size:11px; letter-spacing:0.1em;">Kustom Kreatif</span>
                <h4 class="text-[#081735] font-bold text-[15px] mb-2">Desain Grafis & Media Promosi</h4>
                <p class="text-[#757da5] text-[13px]">
                    Layanan desain dan produksi media promosi yang disesuaikan dengan identitas visual dan kebutuhan bisnis.
                </p>
                <div class="service-card-track flex items-center gap-1 mt-5 text-xs text-[#9ba6be] font-semibold tracking-wide transition-all duration-300 ease-out group-hover:translate-x-2 group-hover:tracking-wider">
                    <span>DESAIN KREATIF</span>
                    <span class="ml-auto transition-transform duration-300 ease-out group-hover:translate-x-0.5">&rsaquo;</span>
                </div>
            </div>
        </div>
</section>

<section id="process" class="bg-white px-10 py-20 sm:px-12 md:px-20" style="padding:80px 40px;">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-10">
            <p class="uppercase text-[11px] font-bold tracking-[0.2em]" style="color:#4A5568;">CARA KERJANYA</p>
            <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-[#051747]">
                Dari Pemilihan Produk Hingga Pesanan di Tangan
            </h2>
            <p class="mt-3 text-[14px] text-slate-500 text-center leading-relaxed max-w-2xl mx-auto">
                Lima langkah terstruktur yang memastikan setiap pesanan<br />
                tercatat, terpantau, dan selesai tepat waktu.
            </p>
        </div>
        <?php
        // Set icon SVGs sesuai step
        $icons = [
            // Pilih Produk
            '<svg class="w-7 h-7 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" rx="4" stroke="currentColor" fill="none"/><path d="M9 10h6" stroke="currentColor" stroke-linecap="round"/></svg>',
            // Isi Spesifikasi
            '<svg class="w-7 h-7 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3" stroke="currentColor" fill="none"/><path d="M7 8h10M7 12h10M7 16h5" stroke="currentColor" stroke-linecap="round"/></svg>',
            // Verifikasi & DP
            '<svg class="w-7 h-7 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor"/><path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            // Produksi & Revisi
            '<svg class="w-7 h-7 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="3" width="14" height="18" rx="2" stroke="currentColor"/><path d="M9 7h6M9 11h6M9 15h3" stroke="currentColor" stroke-linecap="round"/></svg>',
            // Pengiriman
            '<svg class="w-7 h-7 md:w-8 md:h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="7" width="13" height="10" rx="2" stroke="currentColor"/><path d="M16 7h2a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2" stroke="currentColor"/><circle cx="7.5" cy="17" r="1.5" fill="currentColor"/><circle cx="17.5" cy="17" r="1.5" fill="currentColor"/></svg>',
        ];

        $processSteps = [
            ['num' => '01', 'title' => 'Pilih Produk', 'desc' => 'Telusuri katalog dan pilih layanan yang sesuai kebutuhan.'],
            ['num' => '02', 'title' => 'Isi Spesifikasi', 'desc' => 'Input detail pesanan, unggah referensi desain, dan tentukan deadline.'],
            ['num' => '03', 'title' => 'Verifikasi & DP', 'desc' => 'Unggah bukti pembayaran DP dan tunggu konfirmasi Bagian Keuangan.'],
            ['num' => '04', 'title' => 'Produksi & Revisi', 'desc' => 'Tim produksi mengerjakan desain review dan ajukan revisi sesuai kuota.'],
            ['num' => '05', 'title' => 'Pengiriman', 'desc' => "Pesanan dikirim via kurir atau dapat diambil langsung di Z'Plack."],
        ];
        ?>
        <div class="process-timeline flex flex-col items-center gap-12 md:flex-row md:items-start md:justify-between md:gap-0">
            <?php foreach ($processSteps as $i => $step): ?>
                <div class="flex flex-col items-center text-center relative w-full md:w-1/5 group">
                    <!-- ICON CARD -->
                    <div
                        class="relative flex items-center justify-center mx-auto rounded-xl w-16 h-16 md:w-20 md:h-20 bg-white border border-slate-200 shadow transition-all duration-300 ease-in-out
                        cursor-pointer
                        group/icon
                        hover:bg-[#051747]
                        hover:shadow-[0_8px_28px_0_rgba(30,41,59,0.12)]
                        hover:-translate-y-1.5
                        "
                        style="transition:all 0.3s ease">
                        <span class="icon-card-inner flex items-center justify-center text-[#4A5568] group-hover/icon:text-white transition-colors duration-300 ease-in-out">
                            <?= $icons[$i] ?>
                        </span>
                        <span class="absolute bottom-0 right-0 translate-x-1/4 translate-y-1/4 bg-[#4A5568] text-white text-[11px] font-bold rounded-full px-2 py-0.5 shadow-sm border border-white select-none pointer-events-none
                        transition-all duration-300
                        ">
                            <?= $step['num'] ?>
                        </span>
                    </div>
                    <div class="mt-4 font-bold text-[14px] md:text-[15px] text-[#051747]"><?= esc($step['title']) ?></div>
                    <div class="text-xs text-slate-500 leading-[1.5] max-w-[240px] md:max-w-[210px] mx-auto mt-1">
                        <?= esc($step['desc']) ?>
                    </div>
                    <?php if ($i < count($processSteps) - 1): ?>
                        <!-- Connector (vertical for mobile, horizontal for desktop) -->
                        <div class="md:hidden h-8 w-0.5 bg-[#e2e8f0] mx-auto my-3"></div>
                        <div class="hidden md:block absolute top-1/2 right-0 z-0 h-1 w-8 translate-y-[-50%] bg-gradient-to-r from-[#e2e8f0] to-transparent"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <style>
            /* Extra fallback for icon coloring on hover, targeting only the icon svg */
            #process .group\/icon:hover svg {
                color: #fff !important;
                stroke: #fff !important;
            }

            #process .group\/icon svg {
                color: #cbd5e1;
                stroke: #cbd5e1;
                transition: color 0.3s, stroke 0.3s;
            }
        </style>
    </div>
</section>

<section id="portfolio" class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-[11px] uppercase tracking-[0.2em] text-[#2E5CE6] font-bold">Bukti Karya Klien</p>
            <h3 class="mt-2 text-4xl font-extrabold text-[#051747]">Telah Dipercaya Oleh</h3>
        </div>
        <div class="mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="h-36 rounded-2xl border border-[#E2E8F0] shadow-sm bg-gradient-to-br from-teal-800 to-cyan-600"></div>
            <div class="h-36 rounded-2xl border border-[#E2E8F0] shadow-sm bg-gradient-to-br from-neutral-900 to-neutral-700"></div>
            <div class="h-36 rounded-2xl border border-[#E2E8F0] shadow-sm bg-gradient-to-br from-slate-600 to-slate-400"></div>
            <div class="h-36 rounded-2xl border border-[#E2E8F0] shadow-sm bg-gradient-to-br from-blue-700 to-indigo-500"></div>
        </div>
    </div>
</section>

<section id="catalog" class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div>
            <p class="text-[11px] uppercase tracking-[0.2em] text-[#2E5CE6] font-bold">Toko Digital Kami</p>
            <h3 class="mt-2 text-4xl font-extrabold text-[#051747]">Katalog Percetakan</h3>
            <p class="mt-2 text-sm text-slate-500">Pilih paket produk andalan kami, lalu pesan secara digital.</p>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <?php foreach ($tabLabels as $key => $label): ?>
                    <button type="button" class="catalog-tab <?= $key === 'all' ? 'bg-[#051747] text-white' : 'bg-white text-slate-600 border border-slate-200' ?> px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-[0.12em]" data-tab="<?= esc($key) ?>">
                        <?= esc($label) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="flex w-full flex-col gap-2 sm:ml-auto sm:w-auto sm:flex-row sm:items-center sm:gap-2">
                <label for="catalogSearchInput" class="sr-only">Cari katalog</label>
                <style>
                    /* Custom interaction effect for catalog search bar */
                    #catalogSearchBar {
                        border-color: #83A2CD;
                        transition:
                            border-color 0.3s,
                            box-shadow 0.3s,
                            transform 0.3s;
                        box-shadow: 0 2px 12px 0 rgba(46, 92, 230, 0.05);
                        transform: scale(1);
                    }

                    #catalogSearchBar.focused {
                        border-color: #2E5CE6 !important;
                        box-shadow: 0 4px 20px 0 rgba(46, 92, 230, 0.16), 0 0 7px 0 rgba(64, 175, 253, 0.1);
                        transform: scale(1.025);
                    }
                </style>
                <div
                    id="catalogSearchBar"
                    class="catalog-search-bar flex w-full items-center gap-1.5 rounded-full border border-[#83A2CD] bg-white py-1 pl-3 pr-1 transition-all duration-300 sm:min-w-[200px] sm:max-w-[260px]">
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
                    </svg>
                    <input
                        id="catalogSearchInput"
                        type="search"
                        placeholder="Cari..."
                        autocomplete="off"
                        class="min-w-0 flex-1 border-0 bg-transparent py-1.5 text-xs font-semibold uppercase tracking-[0.08em] text-[#2E5CE6] placeholder:text-slate-400 placeholder:normal-case placeholder:tracking-normal placeholder:font-normal focus:border-[#2E5CE6] focus:outline-none focus:ring-0">
                    <button type="button" id="catalogSearchBtn" class="shrink-0 rounded-full bg-[#051747] px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-[#2E5CE6]">
                        Cari
                    </button>
                </div>
                <script>
                    // Make search bar highlight and zoom when focused
                    (function() {
                        const searchBar = document.getElementById('catalogSearchBar');
                        const input = document.getElementById('catalogSearchInput');
                        // Add .focused when input is focused
                        function setFocused(focused) {
                            if (focused) {
                                searchBar.classList.add('focused');
                            } else {
                                searchBar.classList.remove('focused');
                            }
                        }
                        input.addEventListener('focus', () => setFocused(true));
                        input.addEventListener('blur', () => setFocused(false));
                        // Also allow click anywhere on search bar to focus input
                        searchBar.addEventListener('mousedown', function(e) {
                            if (e.target === searchBar) {
                                e.preventDefault();
                                input.focus();
                            }
                        });
                    })();
                </script>

                <a href="<?= site_url('katalog') ?>" class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#051747] px-6 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-white transition-all hover:bg-[#2E5CE6]">
                    Lihat Seluruh Katalog
                </a>
            </div>
        </div>

        <div class="mt-8 grid md:grid-cols-2 xl:grid-cols-4 gap-5" id="catalogGrid">
            <?php foreach ($katalogAktif as $item): ?>
                <?php
                $category = (string) ($item['kategori'] ?? '');
                $namaProduk = (string) ($item['nama_produk'] ?? '-');
                $categoryLabel = str_replace('_', ' ', $category);
                $gambarRaw = trim((string) ($item['gambar'] ?? ''));
                $gambarRelPath = '';
                $gambarUrl = null;

                if ($gambarRaw !== '') {
                    $normalized = str_replace('\\', '/', $gambarRaw);
                    $normalized = ltrim($normalized, '/');

                    if (str_starts_with($normalized, 'uploads/katalog/')) {
                        $gambarRelPath = substr($normalized, strlen('uploads/katalog/'));
                    } elseif (str_starts_with($normalized, 'katalog/')) {
                        $gambarRelPath = substr($normalized, strlen('katalog/'));
                    } else {
                        $gambarRelPath = $normalized;
                    }

                    $gambarRelPath = ltrim($gambarRelPath, '/');

                    if ($gambarRelPath !== '' && is_file(FCPATH . 'uploads/katalog/' . $gambarRelPath)) {
                        $gambarUrl = base_url('uploads/katalog/' . $gambarRelPath);
                    }
                }
                ?>
                <article
                    class="card p-4 catalog-item"
                    data-category="<?= esc($category) ?>"
                    data-name="<?= esc(mb_strtolower($namaProduk)) ?>"
                    data-category-label="<?= esc(mb_strtolower($categoryLabel)) ?>">
                    <?php if ($gambarUrl !== null): ?>
                        <img
                            src="<?= esc($gambarUrl) ?>"
                            alt="<?= esc($namaProduk) ?>"
                            class="h-36 w-full rounded-2xl object-cover border border-slate-100 bg-slate-50">
                    <?php else: ?>
                        <div class="h-36 rounded-2xl bg-gradient-to-br from-slate-200 to-slate-300"></div>
                    <?php endif; ?>
                    <h4 class="mt-4 text-sm font-extrabold text-[#051747] uppercase"><?= esc($namaProduk) ?></h4>
                    <p class="mt-1 text-xs uppercase tracking-[0.12em] text-[#2E5CE6] font-bold"><?= esc($categoryLabel) ?></p>
                    <p class="mt-1 text-sm text-slate-500">Mulai</p>
                    <p class="text-2xl font-extrabold text-[#051747]">
                        Rp <?= esc(number_format((float) ($item['harga_dasar'] ?? 0), 0, ',', '.')) ?>
                        <span class="text-sm font-semibold text-slate-400">/<?= esc((string) ($item['satuan'] ?? 'pcs')) ?></span>
                    </p>
                    <button type="button" data-open-modal="<?= $isLoggedIn ? '' : 'loginModal' ?>" onclick="<?= $isLoggedIn ? "window.location.href='" . site_url('order/create/' . ((int) ($item['id_katalog'] ?? 0))) . "'" : '' ?>" class="mt-4 w-full btn-outline py-2 text-[10px]">Pilih &amp; Pesan Sekarang</button>
                </article>
            <?php endforeach; ?>
        </div>
        <p id="catalogEmpty" class="hidden mt-8 text-center text-sm text-slate-500">
            Produk tidak ditemukan. Coba kata kunci lain atau pilih kategori berbeda.
        </p>
    </div>
</section>

<section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="card p-8 md:p-10 bg-[#051747] text-white border-[#051747]">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-white/70 font-bold">Pengalaman Cetak Premium</p>
                    <h3 class="mt-2 text-3xl md:text-4xl font-extrabold">Wujudkan Kebutuhan Cetak Anda Bersama Kami</h3>
                    <p class="mt-3 text-sm text-white/80 max-w-2xl">Diskusikan kebutuhan detail desain, material, dan estimasi pesanan. Tim kami siap membantu dari konsep sampai produk jadi.</p>
                </div>
                <div class="flex gap-3 shrink-0">
                    <button type="button" data-open-modal="<?= $isLoggedIn ? '' : 'loginModal' ?>" onclick="<?= $isLoggedIn ? "window.location.href='" . site_url('katalog') . "'" : '' ?>" class="btn-primary px-6 py-3 text-xs bg-white !text-[#051747] hover:!bg-slate-200">Pesan Sekarang</button>
                    <a href="#catalog" class="btn-outline px-6 py-3 text-xs !text-white !border-white hover:!bg-white hover:!text-[#051747]">Lihat Katalog</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (() => {
        const tabs = document.querySelectorAll('.catalog-tab');
        const cards = document.querySelectorAll('.catalog-item');
        const searchInput = document.getElementById('catalogSearchInput');
        const searchBtn = document.getElementById('catalogSearchBtn');
        const catalogEmpty = document.getElementById('catalogEmpty');
        let activeTab = 'all';

        const applyCatalogFilters = () => {
            const query = (searchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            cards.forEach((card) => {
                const category = card.getAttribute('data-category') || '';
                const name = card.getAttribute('data-name') || '';
                const categoryLabel = card.getAttribute('data-category-label') || '';
                const tabMatch = activeTab === 'all' || category === activeTab;
                const searchMatch = query === '' ||
                    name.includes(query) ||
                    category.includes(query) ||
                    categoryLabel.includes(query);
                const visible = tabMatch && searchMatch;

                card.classList.toggle('hidden', !visible);
                if (visible) {
                    visibleCount++;
                }
            });

            if (catalogEmpty) {
                catalogEmpty.classList.toggle('hidden', visibleCount > 0);
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activeTab = tab.getAttribute('data-tab') || 'all';

                tabs.forEach((btn) => {
                    btn.classList.remove('bg-[#051747]', 'text-white');
                    btn.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                });

                tab.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                tab.classList.add('bg-[#051747]', 'text-white');

                applyCatalogFilters();
            });
        });

        const runSearch = () => applyCatalogFilters();

        if (searchBtn) {
            searchBtn.addEventListener('click', runSearch);
        }

        if (searchInput) {
            searchInput.addEventListener('input', runSearch);
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    runSearch();
                }
            });
        }
    })();
</script>
<?= $this->endSection() ?>