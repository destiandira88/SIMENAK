Ringkasan lengkap:
File|Halaman|Isi Utama|
simenak_public.html |4 halaman|Landing page, tracking publik, profil pelanggan, modal login/register
simenak_dashboard.html |9 halaman|Role selector, 5 dashboard, form pesanan, detail pesanan, profil
simenak_internal.html1 |0 halaman|Katalog EAV, verif DP/pelunasan, laporan, invoice, email preview, approval history, notifikasi

======================simenak_publik===============================================
Landing Page + Modal Login/Register + Tracking Publik + Profil Pelanggan
Halaman yang ada:

    page-landing — landing page lengkap
    page-tracking — tracking publik
    page-profil — profil + verifikasi perusahaan
    modalLogin + modalRegister — popup overlay

    STRUKTUR HTML (urut dari atas):

    <head>
      - Plus Jakarta Sans Google Fonts CDN
      - Tailwind CSS CDN
      - tailwind.config: extend navy=#051747, accent=#2E5CE6
      - <style> berisi CSS variables dan semua custom CSS
    </head>
    <body>
      <!-- FILE SWITCHER pojok kanan bawah fixed -->
      <!-- PAGE: LANDING -->
      <!-- PAGE: TRACKING -->
      <!-- PAGE: PROFIL -->
      <!-- MODAL LOGIN -->
      <!-- MODAL REGISTER -->
      <!-- <script> semua JS -->
    </body>

    CSS VARIABLES:
    css:root {
    --navy: #051747;
    --navy-mid: #0a2860;
    --blue-accent: #2E5CE6;
    --bg-page: #F0F2F8;
    --text-body: #4A5568;
    --text-muted: #83A2CD;
    --border: #E2E8F0;
    --shadow-sm: 0 1px 3px rgba(5,23,71,.06);
    --shadow-md: 0 4px 16px rgba(5,23,71,.10);
    --shadow-lg: 0 12px 40px rgba(5,23,71,.14);
    }

    - { box-sizing:border-box; margin:0; padding:0; }
      body { font-family:'Plus Jakarta Sans',sans-serif; }

    KOMPONEN CSS YANG PERLU DIDEFINISIKAN:
    .ann-bar
    bg var(--navy), text white center, 11px uppercase letter-spacing .1em, py-2

    .navbar
    sticky top-0 z-50, bg rgba(255,255,255,.96) backdrop-blur,
    border-bottom var(--border), height 64px, flex items-center px-10 gap-8

    .nav-logo (flex gap-2.5)
    .nav-logo-icon: w-9 h-9 rounded-full bg-navy text-white flex center text-base
    .nav-logo-text .main: 14px font-800 navy, span=accent color
    .nav-logo-text .sub: 9px font-600 muted uppercase letter-spacing .12em

    .nav-links (flex gap-7 flex-1 justify-center)
    a: 11px font-600 uppercase letter-spacing .07em muted, hover navy

    .btn-pill (font-700 uppercase letter-spacing .08em rounded-full px-5 py-2.5 border-none cursor-pointer transition-all)
    .btn-navy: bg navy text-white hover:bg-accent hover:shadow
    .btn-outline: border 1.5px navy bg-transparent hover:bg-navy hover:text-white
    .btn-white: bg-white text-navy hover:bg-blue-50

    HERO:
    .hero: bg var(--bg-page) py-24 px-10
    .hero-inner: max-w-7xl mx-auto grid 2-cols gap-16 items-center
    .hero-badge: inline-flex items-center gap-2 bg-white border rounded-full
    py-1.5 px-3.5 text-10px font-700 uppercase letter-spacing .09em navy
    ::before dot biru 7px rounded-full
    .hero-h1: 52px font-800 navy letter-spacing -.02em line-height 1.08
    .hero-h1-accent: 52px font-800 gradient-text
    background: linear-gradient(to right, #172554, #1e3a8a, #1e1b4b)
    -webkit-background-clip:text; -webkit-text-fill-color:transparent
    .hero-sub: 15px text-body line-height 1.7 max-w-lg mb-8
    .hero-stats: flex gap-9 (stat-num 24px font-800 navy, stat-label 10px uppercase muted)
    .hero-card: bg-white rounded-3xl border shadow-lg h-80 relative overflow-hidden
    .hero-float: absolute bottom-[-12px] right-4 bg-navy text-white rounded-2xl
    px-5 py-3.5 flex items-center gap-3 shadow-lg

    SECTIONS:
    .section: py-22 px-10
    .section-inner: max-w-7xl mx-auto
    .section-label: 11px font-700 uppercase letter-spacing .12em text-accent mb-2.5
    .section-h2: 34px font-800 navy letter-spacing -.02em line-height 1.15 mb-3
    .section-sub: 15px text-body line-height 1.7 max-w-xl

    WHO WE ARE:
    .wwa-grid: grid 2-cols gap-16 items-center
    .wwa-h3: 22px font-700 gradient-text (same as hero-h1-accent) mb-3.5
    .highlight-grid: grid 2-cols gap-3
    .highlight-card: bg-white border rounded-2xl p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition
    .hc-icon: 18px accent font-700 mb-1.5
    .hc-title: 12px font-700 navy mb-1
    .hc-body: 11px muted line-height 1.6
    .wwa-visual: bg-page rounded-3xl h-96 flex-center relative border overflow-hidden
    .wwa-badge: absolute bottom-5 left-5 bg-navy text-white rounded-xl px-4 py-2.5 12px font-700

    SERVICES:
    .services-layout: grid cols(1fr 2fr) gap-16 items-start
    .services-grid: grid 2-cols gap-4
    .service-card: bg-white border rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition
    .sc-badge: 10px font-700 pill bg-navy text-white mb-3.5 uppercase letter-spacing .05em
    .sc-icon: 28px mb-2.5
    .sc-title: 15px font-700 navy mb-1.5
    .sc-body: 12px muted line-height 1.65

    WORK PROCESS:
    .process-steps: flex relative mt-12
    ::before connector line: absolute top-7 left-[10%] right-[10%] h-px border-top-2 dashed border-color
    .step: flex-1 text-center px-3 relative z-1
    .step-num: 30px font-800 accent line-height 1 mb-1.5
    .step-circle: w-14 h-14 bg-white border-2 border-border rounded-full flex-center text-xl mx-auto mb-3.5 shadow-sm
    .step-title: 13px font-700 navy mb-1
    .step-body: 11px muted line-height 1.6

    PORTFOLIO:
    .portfolio-grid: grid 4-cols gap-5 mt-10
    .portfolio-card: rounded-2xl overflow-hidden border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition
    .portfolio-img: h-44 bg-gradient flex-center text-4xl relative
    .portfolio-badge: absolute top-2.5 left-2.5 bg-navy text-white 9px font-700 pill uppercase letter-spacing .06em px-2 py-0.5
    .portfolio-label: p-3 bg-white 12px font-600 navy

    KATALOG:
    .filter-tabs: flex gap-2 mb-8 flex-wrap
    .filter-tab: 12px font-600 pill py-2 px-5 border-1.5 border-border bg-transparent muted cursor-pointer transition
    .active / hover: bg-navy text-white border-navy
    .katalog-grid: grid 4-cols gap-5
    .katalog-card: bg-white border rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5 transition
    .kc-img: h-40 bg-page flex-center text-4xl relative
    .kc-tag: absolute top-2.5 left-2.5 10px font-700 pill uppercase letter-spacing .05em
    offset=bg-blue-100 text-blue-800 | digital=bg-green-100 text-green-800 | grafis=bg-purple-100 text-purple-800
    .kc-body: p-4
    .kc-name: 14px font-700 navy mb-1
    .kc-min: 11px muted mb-2
    .kc-price-label: 10px font-600 uppercase letter-spacing .06em muted
    .kc-price: 16px font-800 navy mb-3.5

    CTA SECTION:
    bg-navy py-20 px-10 text-center
    h2: 36px font-800 white mb-2.5
    sub: 15px rgba(255,255,255,.65) mb-8

    FOOTER:
    bg-navy border-top rgba(255,255,255,.08) py-16 px-10 pb-7
    .footer-grid: grid(2fr 1fr 1fr) gap-12 mb-10
    .footer-brand: .name 18px font-800 white (span=6b9fff), .desc 13px line-height 1.65
    .footer-col: h4 12px font-700 white uppercase letter-spacing .1em mb-3.5
    a: 13px rgba-white-.6 no-underline block mb-2 hover:white transition
    .footer-copy: text-center 12px rgba-white-.35 pt-6 border-top rgba-white-.08

    MODAL:
    .modal-overlay: fixed inset-0 bg rgba(5,23,71,.55) backdrop-blur z-[100]
    flex items-start justify-center pt-16
    opacity-0 pointer-events-none transition-opacity .25s
    .open: opacity-1 pointer-events-all
    .modal-card: bg-white rounded-[28px] shadow-lg w-[440px] p-10 relative
    transform scale(.95) translateY(8px) transition-transform .25s
    .open .modal-card: scale(1) translateY(0)
    max-h-[90vh] overflow-y-auto
    .modal-close: absolute top-4 right-4 w-8 h-8 rounded-full border-none bg-page muted cursor-pointer hover:bg-border
    .modal-logo: flex items-center gap-2 mb-5
    .modal-logo-icon: w-8 h-8 rounded-full bg-navy text-white flex-center 14px
    .modal-logo-text: 13px font-800 navy (span=accent)
    .modal-title: 24px font-800 navy mb-1
    .modal-sub: 13px muted mb-6
    .form-group: mb-4
    .form-label: 11px font-700 navy mb-1.5 uppercase letter-spacing .03em block
    .form-input: w-full 13px py-2.5 px-3.5 border-1.5 border-border rounded-2xl navy
    outline-none focus:border-accent focus:ring-3 focus:ring-accent/10 transition
    .input-wrap: relative (icon absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer muted)
    .error-box: bg-red-100 text-red-800 12px p-2.5 rounded-xl mb-4 hidden
    .modal-link: text-center 13px muted mt-4 (a=accent font-600 cursor-pointer hover:underline)
    .demo-note: mt-5 pt-4 border-top text-center
    p: 11px muted mb-2.5 font-500
    .demo-roles: flex gap-1.5 flex-wrap justify-center
    .demo-btn: 10px font-700 px-3 py-1 rounded-full border-1.5 border-border bg-white navy cursor-pointer
    hover:bg-navy hover:text-white hover:border-navy transition

    NAVBAR AFTER LOGIN:
    #nav-loggedin: display:none (ditampilkan setelah login)
    .dd-dashboard: 11px font-700 uppercase letter-spacing .07em pill border-1.5 navy
    .nav-bell: relative w-9 h-9 rounded-full bg-page flex-center text-base border cursor-pointer
    .bell-badge: absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full 9px font-700 white flex-center border-2 border-white
    .nav-avatar: w-9 h-9 bg-navy rounded-full flex-center white 13px font-700 cursor-pointer relative
    .avatar-dropdown: absolute top-[calc(100%+10px)] right-0 bg-white rounded-2xl shadow-lg border w-56 p-2 hidden z-[200]
    .open: display:block
    .dd-user: px-3 py-2.5 border-bottom mb-1.5
    .dd-name: 13px font-700 navy
    .dd-email: 11px muted
    .dd-item: w-full text-left 13px font-500 px-3 py-2 rounded-xl border-none bg-none cursor-pointer navy hover:bg-page transition
    .danger: text-red-600

    FILE SWITCHER:
    .file-switcher: fixed bottom-6 right-6 z-[999] flex flex-col gap-2
    .fs-btn: 11px font-700 px-4 py-2.5 rounded-full border-none cursor-pointer shadow-md transition hover:-translate-y-0.5
    .active: bg-navy text-white
    .inactive: bg-white text-navy border-1.5 border-border

    TRACKING PAGE:
    .tracking-hero: bg-page py-20 px-10 text-center
    .tracking-card: bg-white rounded-2xl border shadow-md p-8 max-w-xl mx-auto
    .tracking-search: flex gap-2.5 mt-6
    .timeline: pl-5 relative
    ::before: absolute left-1.5 top-0 bottom-0 w-0.5 bg-border
    .tl-item: relative pb-5 pl-6
    .tl-dot: absolute left-[-14px] top-1 w-3.5 h-3.5 rounded-full border-2
    .done: bg-accent border-accent
    .active: bg-white border-accent animation-pulse
    .pending: bg-white border-border
    @keyframes pulse: 0%,100%{box-shadow:0 0 0 0 rgba(46,92,230,.4)} 50%{box-shadow:0 0 0 6px rgba(46,92,230,0)}

    OTHER PAGE NAV:
    bg-navy text-white py-3.5 px-10 flex items-center gap-3
    .back-btn: 12px font-600 rgba-white-.7 bg-none border border-rgba-white-.2 px-3.5 py-1.5 rounded-full cursor-pointer hover:bg-rgba-white-.1
    .page-title-nav: 14px font-700

    DATA KATALOG (8 produk):

    1. Undangan Hardcover | offset | Min 50 pcs | Rp 2.500/pcs
    2. Kalender Meja Spiral | digital | Min 10 pcs | Rp 25.000/pcs
    3. Stiker Vinyl Matte A3+ | digital | Min 20 lembar | Rp 15.000/lembar
    4. Brosur A5 Full Color | offset | Min 100 lembar | Rp 1.500/lembar
    5. Map Dossier | offset | Min 50 pcs | Rp 8.000/pcs
    6. Banner Flexi Korea | digital | Min 1 m² | Rp 35.000/m²
    7. Kartu Nama Premium | offset | Min 100 pcs | Rp 2.000/pcs
    8. Desain & Media Promosi | grafis | Custom | Harga custom

    JAVASCRIPT FUNCTIONS:
    javascript// STATE
    let currentUser = null; // {role, nama, email}
    let pendingKatalogId = null;

    // PAGE NAVIGATION
    function showPage(id)

    - querySelectorAll('.page').forEach remove 'active'
    - getElementById(id).classList.add('active')
    - window.scrollTo(0,0)

    // MODAL
    function openModal(id)

    - getElementById(id).classList.add('open')
    - document.body.style.overflow = 'hidden'
      function closeModal(id)
    - remove 'open', restore overflow
      function switchModal(closeId, openId)
    - closeModal → setTimeout 200ms → openModal

    // Overlay click tutup modal
    // Escape key tutup semua modal

    // PASSWORD TOGGLE
    function togglePass(inputId, iconEl)

    - toggle type password/text
    - toggle icon 👁/🙈

    // LOGIN
    function doLoginForm()

    - validasi email + pass tidak kosong
    - detect role dari email (admin/keuangan/produksi/owner → role tersebut, default pelanggan)
    - panggil doLogin(role)

    function doLogin(role)

    - set currentUser = {role, nama: getRoleName(role), email}
    - closeModal
    - if role === pelanggan:
      show #nav-loggedin, hide #nav-guest
      if pendingKatalogId → alert redirect ke form pesanan
    - else:
      alert "Login sebagai [role]. Buka simenak_dashboard.html"

    function getRoleName(role)

    - map: pelanggan, admin, keuangan, produksi, owner → nama tampil

    function doLogout()

    - currentUser = null
    - show #nav-guest, hide #nav-loggedin
    - showPage('page-landing')

    // REGISTER
    function doRegister()

    - alert sukses dummy
    - switchModal ke modalLogin

    // AVATAR DROPDOWN
    function toggleDropdown() → toggle 'open' pada #avatarDropdown
    function closeDropdown() → remove 'open'
    document.addEventListener click → close if click outside

    // KATALOG FILTER
    function filterKatalog(cat, btn)

    - update active tab
    - katalog-card: tampilkan jika cat === 'all' || data-cat === cat

    // CHECK SESSION BEFORE ORDER
    function checkSessionAndOrder(katalogId)

    - if !currentUser → simpan pendingKatalogId, openModal('modalLogin')
    - if role pelanggan → alert redirect form pesanan
    - if role lain → alert "role ini tidak dapat memesan"

    // TRACKING
    function doTracking()

    - validasi input tidak kosong
    - tampilkan #trackingResult (data dummy statis)

    DATA DUMMY TRACKING:
    Kode: ORD-20260529-0001
    Produk: Undangan Pernikahan
    Status: Proses Desain
    Timeline:
    ✅ Pesanan Masuk — 29 Mei 2026 • 08:30
    ✅ DP Terverifikasi — 29 Mei 2026 • 10:15
    🔵 Proses Desain — Sedang dikerjakan... (dot pulse)
    ⬜ Proses Cetak — Menunggu ACC desain
    ⬜ Pengiriman — —
    ⬜ Selesai — —

======================simenak_dashboard===============================================

    .revisi-card { border: 1px solid var(--border); border-radius: 16px; padding: 16px; margin-bottom: 12px; }
    .rc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .rc-version { font-size: 12px; font-weight: 700; color: var(--navy); }
    .rc-preview { width: 80px; height: 64px; background: var(--bg-page); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0; }
    .rc-body { display: flex; gap: 12px; align-items: flex-start; }
    .rc-info { flex: 1; }
    .rc-note { font-size: 12px; color: var(--text-muted); line-height: 1.6; margin-bottom: 8px; }
    .rc-actions { display: flex; gap: 8px; margin-top: 10px; }
    .quota-info { background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 12px; padding: 10px 16px; margin-bottom: 16px; font-size: 12px; color: #92400E; font-weight: 600; }
    .quota-info.danger { background: #FEE2E2; border-color: #FECACA; color: #991B1B; }

    /* PAYMENT CARD */
    .payment-card { border: 1px solid var(--border); border-radius: 16px; padding: 20px; margin-bottom: 12px; }
    .pc-title { font-size: 13px; font-weight: 700; color: var(--navy); margin-bottom: 4px; }
    .pc-amount { font-size: 22px; font-weight: 800; color: var(--blue-accent); margin-bottom: 12px; }
    .pc-upload { border: 2px dashed var(--border); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: border-color .2s; }
    .pc-upload:hover { border-color: var(--blue-accent); }
    .pc-upload-icon { font-size: 28px; margin-bottom: 6px; }
    .pc-upload-text { font-size: 12px; color: var(--text-muted); }

    /* GRAFIK OWNER */
    .chart-bar-wrap { display: flex; align-items: flex-end; gap: 10px; height: 140px; padding: 0 8px; }
    .chart-bar-col { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; }
    .chart-bar { width: 100%; background: var(--blue-accent); border-radius: 6px 6px 0 0; transition: background .2s; min-width: 24px; }
    .chart-bar:hover { background: var(--navy); }
    .chart-label { font-size: 10px; color: var(--text-muted); font-weight: 500; white-space: nowrap; }
    .chart-val { font-size: 10px; font-weight: 700; color: var(--navy); }

    PAGE SELECTOR (halaman pertama yang tampil):
    htmlid="page-selector"
    bg var(--bg-page) min-h-screen flex-center flex-col gap-6

    Heading: "SIMENAK — Pilih Role Dashboard"
    Sub: "Prototype interaktif — pilih role untuk masuk dashboard"

    Grid 5 kolom role cards (bg-white border rounded-2xl p-6 text-center
      cursor-pointer hover:shadow-md hover:-translate-y-1 transition):

    Card Pelanggan:
      ikon 👤, judul "Pelanggan", sub "Overview pesanan & tracking"
      onclick: setRole('pelanggan')

    Card Admin:
      ikon 🛠, judul "Admin", sub "Kelola katalog, pesanan, pengiriman"
      onclick: setRole('admin')

    Card Keuangan:
      ikon 💳, judul "Keuangan", sub "Verifikasi pembayaran DP & pelunasan"
          onclick: setRole('keuangan')Dashboard Semua Role + Form Pesanan + Detail Pesanan + Profil

    HALAMAN YANG ADA:
    page-selector          → pilih role masuk dashboard
    page-dash-pelanggan    → dashboard pelanggan (navbar atas)
    page-dash-admin        → dashboard admin (sidebar)
    page-dash-keuangan     → dashboard keuangan (sidebar)
    page-dash-produksi     → dashboard produksi (sidebar)
    page-dash-owner        → dashboard owner (sidebar)
    page-form-pesanan      → form pemesanan 3 step
    page-detail-pesanan    → detail pesanan + revisi + pembayaran
    page-profil-pelanggan  → profil + verifikasi perusahaan

    HEAD — sama persis File 1:
    Plus Jakarta Sans CDN
    Tailwind CDN
    tailwind.config: navy=#051747, accent=#2E5CE6
    CSS variables sama

    TAMBAHAN CSS FILE 2:
    css/* SIDEBAR LAYOUT */
    .sidebar {
      position: fixed; left: 0; top: 0; bottom: 0;
      width: 256px; background: var(--navy); z-index: 40;
      display: flex; flex-direction: column; padding: 0;
      overflow-y: auto;
    }
    .sidebar-logo {
      padding: 24px 20px 20px;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-logo .brand { font-size: 15px; font-weight: 800; color: #fff; line-height: 1; }
    .sidebar-logo .brand span { color: #6b9fff; }
    .sidebar-logo .sub { font-size: 9px; font-weight: 600; color: rgba(255,255,255,.4); letter-spacing: .12em; text-transform: uppercase; margin-top: 3px; }
    .sidebar-menu { padding: 16px 12px; flex: 1; }
    .menu-section { font-size: 9px; font-weight: 700; color: rgba(255,255,255,.3); text-transform: uppercase; letter-spacing: .12em; padding: 0 8px; margin: 14px 0 6px; }
    .menu-item {
      display: flex; align-items: center; gap: 10px;
      padding: 9px 12px; border-radius: 10px;
      font-size: 13px; font-weight: 500; color: rgba(255,255,255,.65);
      cursor: pointer; transition: all .15s; border: none; background: none;
      width: 100%; text-align: left; font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .menu-item:hover { background: rgba(255,255,255,.08); color: #fff; }
    .menu-item.active { background: var(--blue-accent); color: #fff; font-weight: 600; }
    .menu-item .mi-icon { font-size: 16px; flex-shrink: 0; }
    .sidebar-footer {
      padding: 16px 12px;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-user { display: flex; align-items: center; gap: 10px; padding: 8px 12px; }
    .su-avatar { width: 32px; height: 32px; background: rgba(255,255,255,.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 700; flex-shrink: 0; }
    .su-name { font-size: 12px; font-weight: 600; color: #fff; line-height: 1.2; }
    .su-role { font-size: 10px; color: rgba(255,255,255,.4); }
    .btn-logout { width: 100%; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 600; padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,.15); background: transparent; color: rgba(255,255,255,.6); cursor: pointer; margin-top: 8px; transition: all .2s; }
    .btn-logout:hover { background: rgba(239,68,68,.15); border-color: rgba(239,68,68,.4); color: #fca5a5; }

    /* TOPBAR */
    .topbar {
      position: fixed; top: 0; right: 0; left: 256px;
      height: 64px; background: #fff;
      border-bottom: 1px solid var(--border);
      z-index: 30; display: flex; align-items: center;
      padding: 0 24px; gap: 16px;
    }
    .topbar-title { font-size: 16px; font-weight: 700; color: var(--navy); flex: 1; }
    .topbar-bell { position: relative; cursor: pointer; width: 36px; height: 36px; border-radius: 50%; background: var(--bg-page); display: flex; align-items: center; justify-content: center; font-size: 16px; border: 1px solid var(--border); }
    .topbar-badge { position: absolute; top: -2px; right: -2px; width: 16px; height: 16px; background: #EF4444; border-radius: 50%; font-size: 9px; font-weight: 700; color: #fff; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; }
    .topbar-avatar { width: 36px; height: 36px; background: var(--navy); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; position: relative; }
    .topbar-dropdown { position: absolute; top: calc(100% + 10px); right: 0; background: #fff; border-radius: 16px; box-shadow: var(--shadow-lg); border: 1px solid var(--border); width: 220px; padding: 8px; display: none; z-index: 200; }
    .topbar-dropdown.open { display: block; }

    /* MAIN CONTENT */
    .main-content { margin-left: 256px; padding-top: 64px; min-height: 100vh; background: var(--bg-page); }
    .content-inner { padding: 28px 24px; }

    /* NAVBAR PELANGGAN */
    .navbar-pelanggan {
      position: sticky; top: 0; z-index: 50;
      background: rgba(255,255,255,.96); backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border); height: 64px;
      display: flex; align-items: center; padding: 0 32px; gap: 24px;
    }
    .npel-logo { font-size: 14px; font-weight: 800; flex-shrink: 0; }
    .npel-logo .w { color: var(--navy); }
    .npel-logo .b { color: var(--blue-accent); }
    .npel-menu { display: flex; gap: 4px; flex: 1; justify-content: center; }
    .npel-item { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; font-weight: 600; padding: 7px 16px; border-radius: 8px; border: none; background: none; color: var(--text-muted); cursor: pointer; transition: all .15s; }
    .npel-item:hover { background: var(--bg-page); color: var(--navy); }
    .npel-item.active { background: var(--bg-page); color: var(--navy); }
    .npel-right { display: flex; align-items: center; gap: 10px; }
    .pel-content { padding: 28px 32px; background: var(--bg-page); min-height: calc(100vh - 64px); }

    /* SUMMARY CARDS */
    .summary-grid { display: grid; gap: 16px; margin-bottom: 24px; }
    .grid-4 { grid-template-columns: repeat(4, 1fr); }
    .grid-3 { grid-template-columns: repeat(3, 1fr); }
    .grid-2 { grid-template-columns: repeat(2, 1fr); }
    .sum-card { background: #fff; border: 1px solid var(--border); border-radius: 20px; padding: 22px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 16px; }
    .sum-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .sum-icon-blue { background: #DBEAFE; }
    .sum-icon-green { background: #DCFCE7; }
    .sum-icon-amber { background: #FEF3C7; }
    .sum-icon-purple { background: #EDE9FE; }
    .sum-icon-red { background: #FEE2E2; }
    .sum-icon-teal { background: #CCFBF1; }
    .sum-num { font-size: 28px; font-weight: 800; color: var(--navy); line-height: 1; }
    .sum-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); margin-top: 3px; }

    /* TABLE */
    .tbl-wrap { background: #fff; border: 1px solid var(--border); border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 24px; }
    .tbl-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px 14px; border-bottom: 1px solid var(--border); gap: 12px; flex-wrap: wrap; }
    .tbl-title { font-size: 15px; font-weight: 700; color: var(--navy); }
    .search-input { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; padding: 8px 14px 8px 36px; border: 1.5px solid var(--border); border-radius: 999px; outline: none; color: var(--navy); transition: border-color .2s; width: 220px; }
    .search-input:focus { border-color: var(--blue-accent); }
    .search-wrap { position: relative; }
    .search-wrap::before { content: '🔍'; position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: var(--navy); color: #fff; font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; padding: 11px 16px; text-align: left; white-space: nowrap; }
    thead th:first-child { border-radius: 0; }
    tbody td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 13px; color: var(--navy); vertical-align: middle; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: #f8faff; }

    /* BADGE */
    .badge { font-size: 11px; font-weight: 600; padding: 4px 12px; border-radius: 999px; white-space: nowrap; display: inline-block; }
    .badge-menunggu { background: #FEF3C7; color: #92400E; }
    .badge-terverif { background: #DBEAFE; color: #1E40AF; }
    .badge-proses { background: #EDE9FE; color: #5B21B6; }
    .badge-siap { background: #CCFBF1; color: #065F46; }
    .badge-dikirim { background: #CFFAFE; color: #164E63; }
    .badge-diterima { background: #DCFCE7; color: #166534; }
    .badge-lunas { background: #FFEDD5; color: #9A3412; }
    .badge-selesai { background: #DCFCE7; color: #166534; }
    .badge-batal { background: #FEE2E2; color: #991B1B; }

    /* ACTION BUTTONS */
    .btn-xs { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 999px; border: none; cursor: pointer; transition: all .15s; }
    .btn-xs-navy { background: var(--navy); color: #fff; }
    .btn-xs-navy:hover { background: var(--blue-accent); }
    .btn-xs-outline { background: transparent; color: var(--navy); border: 1.5px solid var(--border); }
    .btn-xs-outline:hover { border-color: var(--navy); }
    .btn-xs-green { background: #DCFCE7; color: #166534; }
    .btn-xs-green:hover { background: #10B981; color: #fff; }
    .btn-xs-red { background: #FEE2E2; color: #991B1B; }
    .btn-xs-red:hover { background: #EF4444; color: #fff; }

    /* THREE DOTS MENU */
    .dots-menu { position: relative; }
    .dots-btn { font-size: 18px; cursor: pointer; padding: 4px 8px; border-radius: 8px; border: none; background: none; color: var(--text-muted); transition: background .15s; }
    .dots-btn:hover { background: var(--bg-page); }
    .dots-dropdown { position: absolute; right: 0; top: calc(100% + 4px); background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-md); padding: 6px; min-width: 140px; display: none; z-index: 100; }
    .dots-dropdown.open { display: block; }
    .dots-item { display: flex; align-items: center; gap: 8px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; font-weight: 500; padding: 8px 10px; border-radius: 8px; border: none; background: none; cursor: pointer; color: var(--navy); width: 100%; text-align: left; transition: background .15s; }
    .dots-item:hover { background: var(--bg-page); }
    .dots-item.danger { color: #DC2626; }

    /* FORM STEPS */
    .step-indicator { display: flex; align-items: center; margin-bottom: 32px; }
    .si-step { display: flex; align-items: center; gap: 8px; }
    .si-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0; }
    .si-active .si-circle { background: var(--blue-accent); color: #fff; }
    .si-done .si-circle { background: #DCFCE7; color: #166534; }
    .si-pending .si-circle { background: var(--bg-page); color: var(--text-muted); border: 2px solid var(--border); }
    .si-label { font-size: 12px; font-weight: 600; }
    .si-active .si-label { color: var(--navy); }
    .si-done .si-label { color: #166534; }
    .si-pending .si-label { color: var(--text-muted); }
    .si-connector { flex: 1; height: 2px; background: var(--border); margin: 0 12px; }

    /* DETAIL PESANAN */
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .detail-card { background: #fff; border: 1px solid var(--border); border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); }
    .detail-card-title { font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
    .detail-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 8px 0; border-bottom: 1px solid var(--bg-page); gap: 12px; }
    .detail-row:last-child { border-bottom: none; }
    .dr-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; flex-shrink: 0; }
    .dr-val { font-size: 13px; font-weight: 600; color: var(--navy); text-align: right; }

    /* REVISI CARDS */

    Card Produksi:
      ikon 🎨, judul "Produksi", sub "Antrian desain & revisi"
      onclick: setRole('produksi')

    Card Owner:
      ikon 👑, judul "Owner", sub "Laporan & monitoring"
      onclick: setRole('owner')

    Link bawah: "← Kembali ke Landing Page (buka simenak_public.html)"

    PAGE DASHBOARD PELANGGAN:
    htmlid="page-dash-pelanggan"

    NAVBAR PELANGGAN:
      Logo kiri: Z'PLACK navy + SIMENAK accent
      Menu tengah (5 item dengan showPelSection()):
        Dashboard | Katalog | Pesanan Saya | Tracking | —
      Kanan: bell badge "3" + avatar "DS" + dropdown (Profil, Keluar)

    SECTION: OVERVIEW (default)
      id="pel-overview"

      Greeting: "Halo, Destiandira 👋" 24px font-800 navy
      Sub: "Selamat datang kembali di SIMENAK Z'Plack" muted mb-6

      Summary grid-4:
        📦 Total Pesanan: 12
        🔄 Pesanan Aktif: 3
        ⏳ Menunggu DP: 1
        ✅ Selesai: 8

      Quick actions row:
        "+ Buat Pesanan Baru" btn-navy onclick:showPage('page-form-pesanan')
        "Lihat Katalog" btn-outline onclick:showPelSection('pel-katalog')

      Tabel pesanan terbaru (tbl-wrap):
        Header: "Pesanan Terbaru" + search-input id="searchOverview"
        Kolom: Kode Order | Produk | Tanggal | Total | Status | Aksi

        5 baris data:
        ORD-20260529-0001 | Undangan Pernikahan | 29 Mei 2026 | Rp 900.000 | badge-proses "Proses Desain" | btn Detail
        ORD-20260528-0003 | Brosur A5 | 28 Mei 2026 | Rp 375.000 | badge-menunggu "Menunggu DP" | btn Detail
        ORD-20260527-0007 | Kalender Meja | 27 Mei 2026 | Rp 750.000 | badge-dikirim "Dikirim" | btn Detail
        ORD-20260520-0012 | Kartu Nama Premium | 20 Mei 2026 | Rp 400.000 | badge-selesai "Selesai" | btn Detail
        ORD-20260515-0008 | Undangan Khitanan | 15 Mei 2026 | Rp 600.000 | badge-selesai "Selesai" | btn Detail

        Setiap "btn Detail" onclick: showPage('page-detail-pesanan')

    SECTION: PESANAN SAYA
      id="pel-pesanan" (hidden default)

      Header row: "Semua Pesanan" + filter status select + search input

      Tabel lebih lengkap, 8 baris, kolom:
      Kode Order | Produk | Jenis Pelanggan | Metode Kirim | Tanggal | Total | Status | Aksi

      Filter select options: Semua | Menunggu DP | Proses | Dikirim | Selesai | Dibatalkan

      Row 1: ORD-20260529-0001 | Undangan Pernikahan | Perseorangan | Kurir | 29 Mei | Rp 900.000 | badge-proses | Detail
      Row 2: ORD-20260528-0003 | Brosur A5 | Perseorangan | Ambil Sendiri | 28 Mei | Rp 375.000 | badge-menunggu | Detail
      Row 3: ORD-20260527-0007 | Kalender Meja | Perusahaan | Kurir | 27 Mei | Rp 750.000 | badge-dikirim | Detail
      Row 4: ORD-20260520-0012 | Kartu Nama | Perseorangan | Ambil Sendiri | 20 Mei | Rp 400.000 | badge-selesai | Detail
      Row 5: ORD-20260515-0008 | Undangan Khitanan | Perseorangan | Kurir | 15 Mei | Rp 600.000 | badge-selesai | Detail
      Row 6: ORD-20260510-0005 | Map Dossier | Perusahaan | Kurir | 10 Mei | Rp 1.200.000 | badge-selesai | Detail
      Row 7: ORD-20260505-0003 | Stiker Vinyl | Perseorangan | Ambil Sendiri | 5 Mei | Rp 300.000 | badge-batal "Dibatalkan" | Detail
      Row 8: ORD-20260501-0001 | Banner Flexi | Perseorangan | Kurir | 1 Mei | Rp 525.000 | badge-selesai | Detail

    SECTION: TRACKING
      id="pel-tracking" (hidden default)

      Sama persis dengan page-tracking di file 1
      tapi tanpa navbar terpisah — sudah pakai navbar pelanggan

    PAGE DASHBOARD ADMIN:
    htmlid="page-dash-admin"
    Layout: sidebar + topbar + main-content

    SIDEBAR menu (onclick showAdminSection()):
      📊 Dashboard [default aktif]
      📋 Semua Pesanan
      🏷 Katalog
      👥 Pengguna
      🏢 Verifikasi Perusahaan
      ⭐ Pesanan Custom
      🚚 Pengiriman
      📈 Laporan

    TOPBAR: title = nama section aktif, badge "5", avatar "AD"

    SECTION: DASHBOARD (id="adm-dashboard")
      Greeting + tanggal hari ini

      Summary grid-4:
        📦 Pesanan Hari Ini: 7
        ⭐ Pending Custom: 2
        🚚 Pending Pengiriman: 4
        💰 Total Bulan Ini: Rp 12.750.000

      Tabel pesanan terbaru:
        Header "Pesanan Masuk Hari Ini" + search
        Kolom: Kode Order | Pelanggan | Produk | Jenis | Tgl | Total | Status | Aksi
        6 baris dengan status berbeda
        Aksi: kolom dots-menu → Detail, Update Status, Batalkan

      Grid 2 kolom pending actions (card masing-masing):
        KIRI — Verifikasi Perusahaan (2 item):
          CV Maju Jaya | Diajukan 2 jam lalu | btn Review
          PT Berkah Abadi | Diajukan 1 hari lalu | btn Review
        KANAN — Pesanan Custom Pending (2 item):
          ORD-20260529-0006 | Banner outdoor 2x3m | btn Set Harga
          ORD-20260528-0004 | Packaging custom | btn Set Harga

    SECTION: SEMUA PESANAN (id="adm-pesanan")
      Filter row: status select + jenis pelanggan select + search
      Tabel 10 baris semua status berbeda
      Kolom: Kode | Pelanggan | Produk | Jenis | Metode Kirim | Total | Status | Aksi
      Aksi: dots-menu (Detail, Update Status, Batalkan)

    SECTION: KATALOG (id="adm-katalog")
      Header: "Manajemen Katalog" + btn "+ Tambah Produk" navy
      Tabel 8 produk:
      Kolom: Nama Produk | Kategori | Harga Dasar | Min Order | Satuan | Kuota Revisi | Status | Aksi
      Data:
        Undangan Hardcover | Offset | Rp 2.500 | 50 | pcs | 3 | Aktif | dots (Edit, Nonaktifkan)
        Kalender Meja Spiral | Digital | Rp 25.000 | 10 | pcs | 2 | Aktif | dots
        Stiker Vinyl Matte | Digital | Rp 15.000 | 20 | lembar | 1 | Aktif | dots
        Brosur A5 Full Color | Offset | Rp 1.500 | 100 | lembar | 2 | Aktif | dots
        Map Dossier | Offset | Rp 8.000 | 50 | pcs | 1 | Aktif | dots
        Banner Flexi Korea | Digital | Rp 35.000 | 1 | m² | 1 | Aktif | dots
        Kartu Nama Premium | Offset | Rp 2.000 | 100 | pcs | 2 | Aktif | dots
        Desain & Media Promosi | Grafis | Custom | 1 | project | 3 | Aktif | dots

    SECTION: PENGGUNA (id="adm-pengguna")
      Header: "Manajemen Pengguna" + btn "+ Tambah Akun Internal"
      Tabel 6 user:
      Kolom: Nama | Email | Role | Tgl Dibuat | Status | Aksi
      Data:
        Owner Z'Plack | owner@zplack.com | Owner | 1 Jan 2026 | Aktif | dots
        Admin Z'Plack | admin@zplack.com | Admin | 1 Jan 2026 | Aktif | dots
        Bagian Keuangan | keuangan@zplack.com | Keuangan | 1 Jan 2026 | Aktif | dots
        Bagian Produksi | produksi@zplack.com | Produksi | 1 Jan 2026 | Aktif | dots
        Destiandira R. | desti@email.com | Pelanggan | 15 Mei 2026 | Aktif | dots (Edit, Nonaktifkan)
        Budi Santoso | budi@email.com | Pelanggan | 20 Mei 2026 | Aktif | dots

    SECTION: VERIFIKASI PERUSAHAAN (id="adm-verifikasi")
      Tabel 3 baris:
      Kolom: Nama Perusahaan | Nama Pelanggan | NPWP | Dokumen | Tgl Ajukan | Status | Aksi
      Data:
        CV Maju Jaya | Andi Saputra | 12.345.678.9 | 2 file | 29 Mei 2026 | badge-menunggu "Pending" | ACC, Tolak
        PT Berkah Abadi | Siti Rahmah | 98.765.432.1 | 2 file | 28 Mei 2026 | badge-menunggu "Pending" | ACC, Tolak
        UD Santoso Print | Budi Santoso | 11.222.333.4 | 2 file | 1 Mei 2026 | badge-terverif "Verified" | —
      ACC btn: btn-xs-green
      Tolak btn: btn-xs-red

    SECTION: PESANAN CUSTOM (id="adm-custom")
      Info box: "Pesanan custom memerlukan konfirmasi harga dari Admin sebelum diproses."
      Tabel 3 baris:
      Kolom: Kode Order | Pelanggan | Deskripsi | Tgl Masuk | Status | Harga Custom | Aksi
      Data:
        ORD-20260529-0006 | Andi Saputra | Banner outdoor 2x3m flexi korea | 29 Mei | Menunggu Konfirmasi Harga | — | btn "Set Harga"
        ORD-20260528-0004 | CV Maju Jaya | Packaging custom box produk | 28 Mei | Menunggu Konfirmasi Harga | — | btn "Set Harga"
        ORD-20260520-0002 | Siti Rahmah | Undangan custom foil emas | 20 Mei | Menunggu Konfirmasi Pelanggan | Rp 3.500.000 | btn "Lihat"
      "Set Harga" onclick: showModal('modalSetHarga')

    SECTION: PENGIRIMAN (id="adm-pengiriman")
      Filter: status kirim select
      Tabel 5 baris:
      Kolom: Kode Order | Pelanggan | Produk | Metode | Status Kirim | Resi | Aksi
      Data:
        ORD-20260527-0007 | Andi Saputra | Kalender Meja | Kurir | badge-dikirim "Dikirim" | JNE-1234567890 | btn "Update"
        ORD-20260526-0009 | Budi Santoso | Brosur A5 | Kurir | badge-siap "Siap Kirim" | — | btn "Input Resi"
        ORD-20260525-0011 | CV Maju Jaya | Map Dossier | Ambil Sendiri | badge-siap "Siap Diambil" | — | btn "Konfirmasi"
        ORD-20260524-0008 | Siti Rahmah | Stiker Vinyl | Kurir | badge-diterima "Diterima" | JNT-9876543210 | —
        ORD-20260523-0006 | Andi Saputra | Kartu Nama | Ambil Sendiri | badge-diterima "Diterima" | — | —
      "Input Resi" onclick: showModal('modalInputResi')

    SECTION: LAPORAN (id="adm-laporan")
      → Sama dengan section laporan di FILE 3 tapi versi admin
      Filter: tanggal dari + tanggal sampai + status select + kategori select
      Summary row 3 cards: Total Pesanan | Total Transaksi | Rata-rata/Pesanan
      Tabel 10 baris laporan
      Btn "Ekspor Excel" kanan atas (outline pill, onclick alert "Fitur ekspor berjalan di CI4")

    PAGE DASHBOARD KEUANGAN:
    htmlid="page-dash-keuangan"
    Sidebar menu:
      📊 Dashboard | 💳 Verifikasi DP | 💰 Verifikasi Pelunasan | 📋 Riwayat

    SECTION: DASHBOARD (id="keu-dashboard")
      Summary grid-3:
        ⏳ Pending DP: 4
        💰 Pending Pelunasan: 2
        ✅ Terverifikasi Hari Ini: 6

      Tabel antrian verifikasi prioritas:
      Header: "Antrian Verifikasi Pembayaran" + search
      Kolom: Kode Bayar | Kode Order | Pelanggan | Jenis | Nominal | Bukti | Waktu Upload | Aksi
      5 baris:
        PAY-20260529-0003 | ORD-20260529-0001 | Andi Saputra | DP | Rp 450.000 | 📎 Lihat | 2 jam lalu | ACC / Tolak
        PAY-20260529-0004 | ORD-20260528-0003 | Siti Rahmah | DP | Rp 187.500 | 📎 Lihat | 3 jam lalu | ACC / Tolak
        PAY-20260528-0007 | ORD-20260527-0007 | CV Maju Jaya | Pelunasan | Rp 750.000 | 📎 Lihat | 1 hari lalu | ACC / Tolak
        PAY-20260528-0005 | ORD-20260525-0005 | Budi Santoso | DP | Rp 300.000 | 📎 Lihat | 1 hari lalu | ACC / Tolak
        PAY-20260527-0002 | ORD-20260524-0002 | Andi Saputra | Pelunasan | Rp 600.000 | 📎 Lihat | 2 hari lalu | ACC / Tolak
      ACC: btn-xs-green | Tolak: btn-xs-red
      Klik ACC → showModal('modalKonfirmasiVerif')
      Klik Tolak → showModal('modalTolakBayar')

    SECTION: VERIFIKASI DP (id="keu-dp")
      Filter: status select + tanggal
      Tabel semua record jenis=dp dengan expand row untuk lihat bukti

    SECTION: VERIFIKASI PELUNASAN (id="keu-pelunasan")
      Sama struktur dengan DP, jenis=pelunasan
      Info: "Pelunasan perseorangan aktif setelah status siap_kirim/siap_diambil.
            Pelunasan perusahaan aktif setelah pesanan_diterima."

    SECTION: RIWAYAT (id="keu-riwayat")
      Tabel semua transaksi terverifikasi
      Footer row: Total DP Masuk | Total Pelunasan | Grand Total Bulan Ini

    PAGE DASHBOARD PRODUKSI:
    htmlid="page-dash-produksi"
    Sidebar menu:
      📊 Dashboard | 🎨 Antrian Desain | 🖨 Manajemen Desain | 📅 Kalender

    SECTION: DASHBOARD (id="prod-dashboard")
      Summary grid-3:
        🎨 Antrian Desain: 5
        🔄 Proses Revisi: 2
        ✅ Selesai Hari Ini: 3

      Tabel antrian prioritas deadline:
      Header: "Antrian Pesanan Aktif" + search
      Kolom: Kode Order | Pelanggan | Produk | Deadline | Status | Kuota Revisi | Aksi
      5 baris:
        ORD-20260529-0001 | Andi Saputra | Undangan Pernikahan | 5 Jun 2026 | badge-proses "Proses Desain" | 3/3 (hijau) | Upload Draft
        ORD-20260528-0003 | Siti Rahmah | Undangan Khitanan | 3 Jun 2026 | badge-proses "Proses Revisi" | 1/3 (merah) | Upload Draft
        ORD-20260527-0005 | Budi Santoso | Brosur A5 | 2 Jun 2026 | badge-proses "Proses Desain" | 2/2 (hijau) | Upload Draft
        ORD-20260526-0009 | CV Maju Jaya | Map Dossier | 1 Jun 2026 | badge-proses "Proses Desain" | 3/3 (hijau) | Upload Draft
        ORD-20260525-0011 | Andi Saputra | Kartu Nama | 30 Mei 2026 | badge-lunas "Finishing" | 2/3 (hijau) | Update Status
      Kuota: sisa/total — warna merah jika sisa ≤ 1
      "Upload Draft" onclick: showModal('modalUploadDraft')

    SECTION: ANTRIAN DESAIN (id="prod-antrian")
      Filter: status (proses_desain / proses_revisi)
      Tabel expanded + expand row accordion:
        Klik baris → tampil catatan revisi pelanggan di bawahnya

    SECTION: MANAJEMEN DESAIN (id="prod-desain")
      Header: "Approval History Revisi Desain" + search
      Kolom: Kode Revisi | Kode Order | Versi | Status | Catatan Pelanggan | Upload | Aksi

      Data:
        REV-0001-03 | ORD-20260529-0001 | v3 | badge-terverif "ACC" | — | 29 Mei 09:00 | Lihat
        REV-0001-02 | ORD-20260529-0001 | v2 | badge-proses "Diajukan Revisi" | Font terlalu kecil | 28 Mei 14:00 | Upload Baru
        REV-0001-01 | ORD-20260529-0001 | v1 | badge-proses "Diajukan Revisi" | Ganti warna background | 28 Mei 09:00 | —
        REV-0003-01 | ORD-20260528-0003 | v1 | badge-menunggu "Uploaded" | — | 29 Mei 13:00 | —

    SECTION: KALENDER (id="prod-kalender")
      Header: "Kalender Deadline Pesanan"
      Info note: "Data deadline bersumber dari tabel orders — hanya tampilan monitoring."

      Kalender sederhana bulan Juni 2026:
        Grid 7 kolom (Sen–Min), baris per minggu
        Sel tanggal yang ada deadline: highlight dengan badge kecil navy
        Contoh highlight:
          2 Jun: "Brosur A5" (dot biru kecil)
          3 Jun: "Undangan Khitanan" (dot merah — deadline dekat)
          5 Jun: "Undangan Pernikahan" (dot biru)
        Klik tanggal highlight → tampil tooltip/card kecil: kode order, pelanggan, produk

    PAGE DASHBOARD OWNER:
    htmlid="page-dash-owner"
    Sidebar menu:
      📊 Dashboard | 📈 Laporan | 📅 Kalender

    SECTION: DASHBOARD (id="own-dashboard")
      Greeting + tanggal

      Summary grid-4:
        📦 Pesanan Aktif: 18
        ✅ Selesai Bulan Ini: 47
        💰 Total Transaksi: Rp 28.450.000
        ❌ Dibatalkan: 2

      Grid 2 kolom:
        KIRI — Grafik Batang (card putih rounded-2xl p-6):
          Header "Pesanan 7 Hari Terakhir"
          SVG atau CSS chart:
            Data: 23Mei=5, 24Mei=8, 25Mei=6, 26Mei=11, 27Mei=7, 28Mei=9, 29Mei=12
            Bar: bg accent, hover bg navy, border-radius 6px 6px 0 0
            Angka di atas bar, label hari di bawah
            Max height 140px, bar width proporsional
          Implementasi CSS:
            .chart-bar-wrap: flex items-end gap-2.5 h-36 px-2
            .chart-bar: bg-accent rounded-t-md min-w-6 hover:bg-navy transition
              height = (nilai/max)*100% → inline style
            .chart-label: 10px muted font-500
            .chart-val: 10px font-700 navy

        KANAN — Distribusi Status (grid 2x3 mini cards):
          Proses Desain: 5 | badge-proses
          Proses Cetak: 4 | badge-proses
          Dikirim: 3 | badge-dikirim
          Menunggu DP: 3 | badge-menunggu
          Finishing: 1 | badge-proses
          Selesai Bulan Ini: 47 | badge-selesai

      Tabel pesanan terbaru (read-only):
        Header "Pesanan Terbaru" + btn "Ekspor Excel" outline
        Kolom: Kode Order | Pelanggan | Produk | Total | Status
        5 baris (tidak ada kolom Aksi)

    SECTION: LAPORAN (id="own-laporan")
      Filter row: bulan/tahun select + kategori produk select
      Tombol "Terapkan Filter" + "Ekspor Excel" navy

      Summary row 3 cards:
        Total Pesanan Periode Ini: 47
        Total Pendapatan: Rp 28.450.000
        Kategori Terlaris: Offset

      Grafik Line Sederhana — Pendapatan Harian (CSS/SVG):
        7 titik data: nilai pendapatan per hari
        Garis SVG polyline stroke accent, fill none
        Titik dot kecil di setiap nilai
        Label hari di bawah, nilai di atas titik
        Card wrapper putih rounded-2xl p-6 mb-6

      Tabel Rekap Per Kategori:
      Kolom: Kategori | Jumlah Pesanan | Total Pendapatan | % dari Total
      Data:
        Offset | 22 | Rp 12.500.000 | 43.9%
        Digital | 15 | Rp 8.750.000 | 30.7%
        Grafis | 10 | Rp 7.200.000 | 25.3%

    SECTION: KALENDER (id="own-kalender")
      Sama dengan kalender produksi — bersumber dari data orders

    PAGE FORM PESANAN (3 STEP):
    htmlid="page-form-pesanan"
    Layout: navbar pelanggan + content

    Tombol "← Kembali ke Dashboard" kiri atas

    Step indicator (3 step):
      Step 1: Pilih Produk [active]
      connector
      Step 2: Detail & Spesifikasi [pending]
      connector
      Step 3: Konfirmasi [pending]

    ═══ STEP 1: PILIH PRODUK (id="step1") ═══

      Heading "Pilih Produk yang Ingin Dipesan"
      Filter tabs: Semua | Offset | Digital | Desain Grafis
      Grid 4 kolom katalog cards (sama style kc-card):
        Setiap card: onclick selectProduct(id, nama)
        Selected state: border-2 border-accent shadow-md ring-2 ring-accent/20

      Btn "Lanjut →" navy (disabled sampai produk dipilih) di kanan bawah
      onclick: goStep(2)

    ═══ STEP 2: DETAIL PESANAN (id="step2", hidden) ═══

      Grid 2 kolom:

      KIRI — Form fields:

        Card info produk terpilih:
          Tag kategori + nama produk + harga dasar
          Min order: "Min. X pcs/lembar/m²"

        Form standar:
          Jumlah Order (input number, min=min_order, placeholder "Min. X")
            onchange: updateEstimasi()
          Jenis Pelanggan: radio
            ⚪ Perseorangan — Wajib DP 50%
            ⚪ Perusahaan — Tanpa DP (hanya tersedia jika terverifikasi)
              Note kecil muted kalau belum terverifikasi: "Akun belum terverifikasi perusahaan"
          Metode Pengiriman: radio
            ⚪ Kurir
            ⚪ Ambil Sendiri
          Alamat Kirim (textarea, muncul jika Kurir dipilih)
          Deadline (date input, min = hari ini + 3)
          Upload Referensi Desain (file, .jpg .jpeg .png .pdf, max 1MB)
          Catatan Tambahan (textarea optional)

        Kalau produk = Undangan Pernikahan:
        Card "Detail Undangan Pernikahan" (bg blue-50 border-blue-100 rounded-xl p-5):
          Nama Mempelai Pria *
          Nama Mempelai Wanita *
          Nama Keluarga Mempelai Pria *
          Nama Keluarga Mempelai Wanita *
          Akad Nikah: Hari* | Tanggal* | Waktu* | Tempat* (textarea)
          Resepsi: Hari* | Tanggal* | Waktu* | Tempat* (textarea)
          Turut Mengundang (textarea, optional)
          Hiburan (input, optional)
          Upload Peta Lokasi (file, optional)

        Kalau produk = Undangan Khitanan:
        Card "Detail Undangan Khitanan" (bg green-50):
          Nama Anak *
          Nama Bapak *
          Nama Ibu *
          Resepsi: Hari* | Tanggal* | Waktu* | Tempat* (textarea)
          Turut Mengundang (textarea, optional)
          Hiburan (input, optional)
          Upload Peta Lokasi (file, optional)

      KANAN — Estimasi Harga Card (sticky top-4):
        Card putih rounded-xl shadow-sm p-5:
          Label "Estimasi Harga"
          Harga satuan: Rp X.XXX
          × Jumlah: X pcs
          ─────────────────
          Subtotal: Rp X.XXX.XXX
          Note kecil muted: "Harga final dikonfirmasi setelah pesanan diproses"
          Kalau custom: tampil "Rp 0 — Harga ditentukan Admin"

      Btn row:
        "← Kembali" outline onclick: goStep(1)
        "Lanjut ke Konfirmasi →" navy onclick: goStep(3)

    ═══ STEP 3: KONFIRMASI (id="step3", hidden) ═══

      Card summary pesanan:
        Nama produk, kategori, jumlah, harga estimasi
        Jenis pelanggan, metode pengiriman, deadline
        Referensi file: nama file yang diupload

      Info box jenis pelanggan:
        Perseorangan: bg-amber-50 border-amber-200
          "DP 50% = Rp X.XXX.XXX wajib dibayar setelah pesanan dikonfirmasi."
        Perusahaan: bg-blue-50 border-blue-200
          "Skema perusahaan — tanpa DP. Nota tagihan aktif setelah pesanan diterima."

      Btn row:
        "← Kembali" outline onclick: goStep(2)
        "✓ Konfirmasi & Buat Pesanan" navy onclick: submitPesanan()

    PAGE DETAIL PESANAN:
    htmlid="page-detail-pesanan"
    Layout: navbar pelanggan + content

    Btn "← Pesanan Saya" kiri atas

    Header row:
      Kode Order: ORD-20260529-0001 (font-mono font-800 18px)
      Badge status besar: badge-proses "Proses Desain"

    Grid 2 kolom atas:
      KIRI — Info Pesanan (detail-card):
        Produk: Undangan Pernikahan
        Jenis Pelanggan: Perseorangan
        Jumlah: 200 pcs | Harga Satuan: Rp 4.500
        Total Harga: Rp 900.000
        Metode Pengiriman: Kurir
        Deadline: 5 Juni 2026
        Tanggal Pesan: 29 Mei 2026

        Jika ada detail EAV (undangan pernikahan):
        Section "Detail Undangan" (border-top mt-4 pt-4):
          Mempelai Pria: Ahmad Fauzi
          Mempelai Wanita: Siti Rahmah
          Akad: Sabtu, 15 Juni 2026 • 08:00 • Masjid Al-Ikhlas Bandung
          Resepsi: Sabtu, 15 Juni 2026 • 11:00 • Gedung Serbaguna Cimahi

      KANAN — Timeline Status (detail-card):
        Vertical timeline 6 titik:
          ✅ Pesanan Masuk — 29 Mei 2026 08:30
          ✅ DP Terverifikasi — 29 Mei 2026 10:15
          🔵 Proses Desain — Sedang dikerjakan... (dot pulse)
          ⬜ Proses Cetak — Menunggu ACC desain
          ⬜ Pengiriman — —
          ⬜ Selesai — —

    Grid 2 kolom bawah:
      KIRI — Section Revisi Desain (detail-card):
        Header: "Revisi Desain" + info kuota "Sisa Kuota: 1 dari 3"
        quota-info (amber): "Sisa kuota revisi: 1. Gunakan dengan bijak."

        revisi-card v1:
          rc-header: "Draft v1 — REV-0001-01" + badge-proses "Diajukan Revisi" + tanggal
          rc-body: preview 🖼 + info:
            Catatan Produksi: "Draft pertama, font disesuaikan"
            Catatan Revisi Kamu: "Tolong ganti warna background ke merah maroon"
            (tidak ada action button — sudah lewat)

        revisi-card v2:
          rc-header: "Draft v2 — REV-0001-02" + badge-proses "Diajukan Revisi" + tanggal
          rc-body: preview + info:
            Catatan Produksi: "Warna background sudah diganti"
            Catatan Revisi Kamu: "Ukuran font nama mempelai kurang besar"
            (tidak ada button)

        revisi-card v3 (terbaru):
          rc-header: "Draft v3 — REV-0001-03" + badge-menunggu "Menunggu Review" + tanggal
          rc-body: preview + info:
            Catatan Produksi: "Font nama sudah diperbesar, layout disesuaikan"
            rc-actions:
              btn "✓ ACC Desain" btn-xs-green onclick: showModal('modalAccDesain')
              btn "↺ Ajukan Revisi" btn-xs-red onclick: showModal('modalAjukanRevisi')
              (btn revisi disabled + tooltip jika sisa_kuota = 0)

      KANAN — Section Pembayaran (detail-card):
        Header "Pembayaran"

        payment-card (DP):
          pc-title: "Uang Muka (DP 50%)"
          pc-amount: "Rp 450.000"
          Badge terverifikasi + tanggal verifikasi (sudah dibayar)
          Status: ✅ Terverifikasi — 29 Mei 2026

        payment-card (Pelunasan — disabled/locked):
          pc-title: "Pelunasan (50%)"
          pc-amount: "Rp 450.000"
          Info note: "Pelunasan aktif setelah pesanan siap kirim/diambil."
          pc-upload locked (opacity-50 cursor-not-allowed)

    MODALS FILE 2:
    modalSetHarga:
      Title: "Set Harga Pesanan Custom"
      Info: kode order + deskripsi custom
      Input: Harga yang Ditawarkan (Rp)
      Textarea: Catatan untuk Pelanggan
      Btn: "Kirim Penawaran" navy + Batal

    modalInputResi:
      Title: "Input Nomor Resi"
      Info: kode order + nama pelanggan
      Input: Nama Ekspedisi
      Input: Nomor Resi
      Btn: "Simpan & Kirim Notifikasi" navy + Batal

    modalUploadDraft:
      Title: "Upload Draft Desain"
      Info card: kode order + pelanggan + produk
      File input (jpg/jpeg, max 1MB)
      Textarea: Catatan untuk Pelanggan
      Btn: "Upload Draft" navy + Batal

    modalKonfirmasiVerif:
      Title: "Konfirmasi Verifikasi Pembayaran"
      Text: "Yakin memverifikasi pembayaran PAY-20260529-0003 senilai Rp 450.000?"
      Btn: "Ya, Verifikasi" green + Batal

    modalTolakBayar:
      Title: "Tolak Bukti Pembayaran"
      Text: "Masukkan alasan penolakan:"
      Textarea: alasan
      Btn: "Tolak Pembayaran" red + Batal

    modalAccDesain:
      Title: "ACC Desain"
      Text: "Desain draft v3 akan di-ACC dan pesanan lanjut ke proses cetak."
      Btn: "Ya, ACC Desain" green + Batal

    modalAjukanRevisi:
      Title: "Ajukan Revisi Desain"
      Text: "Sisa kuota: 1 revisi lagi."
      Textarea: Catatan revisi (apa yang perlu diubah)
      Btn: "Kirim Revisi" red + Batal

    JAVASCRIPT FILE 2:
    javascript// STATE
    let currentRole = 'pelanggan';
    let selectedProduct = null;
    let currentStep = 1;
    let selectedKatalog = null;

    // ROLE SETUP
    function setRole(role)
      - currentRole = role
      - showPage('page-dash-' + role)
      - (khusus pelanggan: showPage('page-dash-pelanggan'))

    // PAGE NAVIGATION
    function showPage(id) — sama dengan file 1

    // SECTION SWITCHING PER ROLE
    function showPelSection(id)
      - hide semua .pel-section
      - show getElementById(id)
      - update .npel-item active state

    function showAdminSection(id)
      - hide semua .adm-section
      - show id
      - update .menu-item active

    function showKeuSection(id) — sama pola
    function showProdSection(id) — sama pola
    function showOwnerSection(id) — sama pola

    // FORM PESANAN
    function selectProduct(id, nama, kategori, hargaDasar, minOrder, satuan)
      - selectedProduct = {id, nama, kategori, hargaDasar, minOrder, satuan}
      - update selected visual (border accent + ring)
      - enable btn Lanjut
      - if id === 1 (Undangan Pernikahan): showEavSection('eav-pernikahan')
        else if id === 2 (Undangan Khitanan): showEavSection('eav-khitanan')
        else: hideAllEav()

    function goStep(n)
      - hide step[1,2,3] div
      - show step[n] div
      - update step indicator UI
      - currentStep = n
      - window.scrollTo(0,0)

    function updateEstimasi()
      - ambil nilai input jumlah
      - jika < minOrder: tampilkan error merah "Minimum order X satuan"
      - kalkulasi subtotal = hargaDasar × jumlah
      - update DOM estimasi

    function showEavSection(id)
      - hide semua .eav-section
      - show id

    function submitPesanan()
      - alert 'Pesanan berhasil dibuat! Kode: ORD-20260529-000X\nSilakan lakukan pembayaran DP.'
      - showPage('page-dash-pelanggan')
      - showPelSection('pel-pesanan')

    // MODAL
    function showModal(id) — same as openModal
    function hideModal(id) — same as closeModal

    // SEARCH TABLES
    function searchTable(inputId, tableId)
      - get value toLowerCase
      - querySelectorAll('#'+tableId+' tbody tr')
      - forEach tr: hide jika tidak ada td yang mengandung value

    // DOTS MENU
    function toggleDots(id)
      - close semua .dots-dropdown
      - toggle id

    // CHARTS OWNER
    function renderBarChart()
      - data = [5,8,6,11,7,9,12]
      - labels = ['23/5','24/5','25/5','26/5','27/5','28/5','29/5']
      - max = Math.max(...data)
      - build HTML bars dengan height = (val/max)*100%

    // KALENDER PRODUKSI/OWNER
    function renderKalender(month, year)
      - build grid kalender
      - highlight tanggal yang ada deadline dari data dummy
      - onclick show tooltip

    // FILTER KATALOG FORM STEP 1
    function filterKatalogForm(cat, btn)
      - sama dengan filterKatalog di file 1

    // TOPBAR TITLE UPDATE
    function updateTopbarTitle(title)
      - getElementById('topbarTitle').textContent = title

    // DOTS CLICK OUTSIDE
    document.addEventListener('click', e =>
      if not inside .dots-menu → close all .dots-dropdown)

    // ESC KEY
    document.addEventListener('keydown', e =>
      if Escape → close all modals + dots dropdowns)

======================simenak_internal===============================================
Halaman Internal: Katalog Detail, Verifikasi Pembayaran, Laporan, Invoice, Contoh Email, Kalender

    HALAMAN YANG ADA:
    page-selector-internal   → pilih fitur yang mau dibuka
    page-katalog-detail      → detail produk + form template EAV
    page-verif-dp            → verifikasi DP full (keuangan)
    page-verif-pelunasan     → verifikasi pelunasan full
    page-laporan-admin       → laporan + filter + grafik (admin)
    page-laporan-owner       → laporan owner lengkap
    page-invoice             → contoh invoice/nota tagihan
    page-email-preview       → contoh tampilan email notifikasi
    page-approval-history    → approval history revisi per pesanan
    page-notif-panel         → panel notifikasi in-app

    HEAD — sama persis File 1 & 2:
    Plus Jakarta Sans CDN
    Tailwind CDN
    tailwind.config + CSS variables sama

    TAMBAHAN CSS FILE 3:
    css/* INVOICE */
    .invoice-wrap {
      max-width: 680px; margin: 0 auto;
      background: #fff; border: 1px solid var(--border);
      border-radius: 20px; overflow: hidden;
      box-shadow: var(--shadow-lg);
    }
    .invoice-header {
      background: var(--navy); color: #fff;
      padding: 32px 40px; display: flex;
      justify-content: space-between; align-items: flex-start;
    }
    .invoice-logo { font-size: 20px; font-weight: 800; }
    .invoice-logo span { color: #6b9fff; }
    .invoice-logo-sub { font-size: 10px; opacity: .5; letter-spacing: .12em; text-transform: uppercase; margin-top: 3px; }
    .invoice-title { font-size: 13px; font-weight: 700; opacity: .7; text-transform: uppercase; letter-spacing: .1em; }
    .invoice-num { font-size: 22px; font-weight: 800; margin-top: 4px; }
    .invoice-body { padding: 32px 40px; }
    .invoice-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid var(--border); }
    .inv-meta-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); margin-bottom: 4px; }
    .inv-meta-val { font-size: 13px; font-weight: 600; color: var(--navy); }
    .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .invoice-table th { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted); padding: 8px 0; border-bottom: 2px solid var(--border); text-align: left; }
    .invoice-table td { padding: 12px 0; border-bottom: 1px solid var(--bg-page); font-size: 13px; color: var(--navy); }
    .invoice-total { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }
    .inv-total-row { display: flex; gap: 40px; }
    .inv-total-label { font-size: 12px; color: var(--text-muted); font-weight: 500; }
    .inv-total-val { font-size: 12px; font-weight: 700; color: var(--navy); min-width: 120px; text-align: right; }
    .inv-grand { display: flex; gap: 40px; padding-top: 12px; border-top: 2px solid var(--navy); margin-top: 8px; }
    .inv-grand-label { font-size: 14px; font-weight: 800; color: var(--navy); }
    .inv-grand-val { font-size: 14px; font-weight: 800; color: var(--blue-accent); min-width: 120px; text-align: right; }
    .invoice-footer { background: var(--bg-page); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
    .inv-footer-note { font-size: 11px; color: var(--text-muted); }
    .inv-status-badge { font-size: 11px; font-weight: 700; padding: 6px 16px; border-radius: 999px; }

    /* EMAIL PREVIEW */
    .email-wrap {
      max-width: 600px; margin: 0 auto;
      background: #fff; border: 1px solid var(--border);
      border-radius: 16px; overflow: hidden;
      box-shadow: var(--shadow-md);
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .email-preheader {
      background: var(--bg-page); padding: 10px 24px;
      font-size: 11px; color: var(--text-muted);
      border-bottom: 1px solid var(--border);
    }
    .email-header { background: var(--navy); padding: 28px 32px; text-align: center; }
    .email-header-logo { font-size: 18px; font-weight: 800; color: #fff; }
    .email-header-logo span { color: #6b9fff; }
    .email-header-sub { font-size: 10px; color: rgba(255,255,255,.5); letter-spacing: .12em; text-transform: uppercase; margin-top: 4px; }
    .email-body { padding: 32px; }
    .email-greeting { font-size: 20px; font-weight: 800; color: var(--navy); margin-bottom: 12px; }
    .email-text { font-size: 14px; color: var(--text-body); line-height: 1.7; margin-bottom: 20px; }
    .email-info-card { background: var(--bg-page); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .eic-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); }
    .eic-row:last-child { border-bottom: none; }
    .eic-label { font-size: 12px; color: var(--text-muted); font-weight: 500; }
    .eic-val { font-size: 12px; font-weight: 700; color: var(--navy); }
    .email-cta { text-align: center; margin: 24px 0; }
    .email-cta-btn { display: inline-block; background: var(--navy); color: #fff; font-size: 13px; font-weight: 700; padding: 12px 32px; border-radius: 999px; text-decoration: none; letter-spacing: .06em; text-transform: uppercase; }
    .email-footer { background: var(--navy); padding: 20px 32px; text-align: center; }
    .email-footer-text { font-size: 11px; color: rgba(255,255,255,.45); line-height: 1.6; }

    /* APPROVAL HISTORY */
    .ah-timeline { position: relative; padding-left: 32px; }
    .ah-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: var(--border); }
    .ah-item { position: relative; margin-bottom: 20px; }
    .ah-dot { position: absolute; left: -26px; top: 4px; width: 16px; height: 16px; border-radius: 50%; border: 2px solid; display: flex; align-items: center; justify-content: center; font-size: 8px; }
    .ah-dot-acc { background: #DCFCE7; border-color: #10B981; color: #166534; }
    .ah-dot-rev { background: #FEE2E2; border-color: #EF4444; color: #991B1B; }
    .ah-dot-up { background: #DBEAFE; border-color: #2563EB; color: #1E40AF; }
    .ah-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 16px 20px; box-shadow: var(--shadow-sm); }
    .ah-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .ah-version { font-size: 13px; font-weight: 700; color: var(--navy); }
    .ah-time { font-size: 11px; color: var(--text-muted); }
    .ah-preview-row { display: flex; gap: 14px; align-items: flex-start; }
    .ah-thumb { width: 72px; height: 56px; background: var(--bg-page); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 26px; flex-shrink: 0; border: 1px solid var(--border); }
    .ah-notes { flex: 1; }
    .ah-note-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); margin-bottom: 3px; }
    .ah-note-text { font-size: 12px; color: var(--navy); line-height: 1.6; }

    /* NOTIF PANEL */
    .notif-list { display: flex; flex-direction: column; gap: 10px; }
    .notif-item { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-start; box-shadow: var(--shadow-sm); transition: all .15s; cursor: pointer; }
    .notif-item:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
    .notif-item.unread { border-left: 3px solid var(--blue-accent); background: #fafcff; }
    .notif-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .ni-blue { background: #DBEAFE; }
    .ni-green { background: #DCFCE7; }
    .ni-amber { background: #FEF3C7; }
    .ni-red { background: #FEE2E2; }
    .notif-content { flex: 1; }
    .notif-title { font-size: 13px; font-weight: 600; color: var(--navy); margin-bottom: 2px; }
    .notif-sub { font-size: 12px; color: var(--text-muted); line-height: 1.5; }
    .notif-time { font-size: 10px; color: var(--text-muted); margin-top: 4px; }
    .notif-dot { width: 8px; height: 8px; background: var(--blue-accent); border-radius: 50%; flex-shrink: 0; margin-top: 6px; }

    /* FILTER FORM */
    .filter-row { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; }
    .filter-group { display: flex; flex-direction: column; gap: 5px; }
    .filter-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); }
    .filter-select { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; padding: 8px 14px; border: 1.5px solid var(--border); border-radius: 10px; outline: none; color: var(--navy); transition: border-color .2s; background: #fff; }
    .filter-select:focus { border-color: var(--blue-accent); }
    .filter-date { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 12px; padding: 8px 14px; border: 1.5px solid var(--border); border-radius: 10px; outline: none; color: var(--navy); }

    /* GRAFIK LINE SVG */
    .line-chart-wrap { background: #fff; border: 1px solid var(--border); border-radius: 20px; padding: 24px; margin-bottom: 24px; box-shadow: var(--shadow-sm); }
    .lc-header { font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 20px; }
    .lc-svg { width: 100%; height: 160px; overflow: visible; }

    /* FORM TEMPLATE PREVIEW */
    .ftpl-card { background: var(--bg-page); border: 1px dashed var(--border); border-radius: 14px; padding: 20px; }
    .ftpl-row { display: flex; align-items: center; gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--border); }
    .ftpl-row:last-child { border-bottom: none; }
    .ftpl-num { width: 22px; height: 22px; background: var(--navy); color: #fff; border-radius: 50%; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ftpl-key { font-size: 11px; font-family: monospace; background: #fff; border: 1px solid var(--border); border-radius: 6px; padding: 2px 8px; color: var(--blue-accent); flex-shrink: 0; }
    .ftpl-label { font-size: 12px; font-weight: 600; color: var(--navy); flex: 1; }
    .ftpl-type { font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 999px; }
    .ftpl-type-text { background: #DBEAFE; color: #1E40AF; }
    .ftpl-type-date { background: #DCFCE7; color: #166534; }
    .ftpl-type-time { background: #EDE9FE; color: #5B21B6; }
    .ftpl-type-textarea { background: #FEF3C7; color: #92400E; }
    .ftpl-type-file { background: #FEE2E2; color: #991B1B; }
    .ftpl-required { font-size: 10px; font-weight: 700; color: #991B1B; }

    PAGE SELECTOR INTERNAL:
    htmlid="page-selector-internal" (default aktif)
    bg var(--bg-page) min-h-screen

    HEADER:
      bg-navy py-5 px-10 flex justify-between items-center
      Logo Z'PLACK SIMENAK
      Sub "Prototype Halaman Internal"
      Link "← File Sebelumnya"

    CONTENT px-10 py-10:
      Heading "Pilih Halaman yang Ingin Dilihat"
      Sub "File 3 dari 3 — Halaman internal, laporan, invoice, dan dokumentasi"

      Grid 3 kolom link cards:

      Card 1 — Katalog & Form Template:
        ikon 🏷, judul "Detail Katalog + EAV"
        sub "Preview form template undangan pernikahan & khitanan"
        onclick: showPage('page-katalog-detail')

      Card 2 — Verifikasi DP:
        ikon 💳, judul "Verifikasi Pembayaran DP"
        sub "Halaman full verifikasi DP oleh Bagian Keuangan"
        onclick: showPage('page-verif-dp')

      Card 3 — Verifikasi Pelunasan:
        ikon 💰, judul "Verifikasi Pelunasan"
        sub "Halaman full verifikasi pelunasan + nota tagihan"
        onclick: showPage('page-verif-pelunasan')

      Card 4 — Laporan Admin:
        ikon 📈, judul "Laporan Admin"
        sub "Filter, grafik, tabel rekap, ekspor"
        onclick: showPage('page-laporan-admin')

      Card 5 — Laporan Owner:
        ikon 👑, judul "Laporan Owner"
        sub "Dashboard laporan owner lengkap dengan grafik"
        onclick: showPage('page-laporan-owner')

      Card 6 — Invoice:
        ikon 📄, judul "Invoice / Nota Tagihan"
        sub "Contoh invoice pesanan perusahaan (biru)"
        onclick: showPage('page-invoice')

      Card 7 — Email Preview:
        ikon 📧, judul "Contoh Email Notifikasi"
        sub "Preview tampilan email ke pelanggan & keuangan"
        onclick: showPage('page-email-preview')

      Card 8 — Approval History:
        ikon ✅, judul "Approval History Revisi"
        sub "Riwayat lengkap revisi per pesanan"
        onclick: showPage('page-approval-history')

      Card 9 — Panel Notifikasi:
        ikon 🔔, judul "Panel Notifikasi In-App"
        sub "Contoh notifikasi untuk Admin dan Produksi"
        onclick: showPage('page-notif-panel')

    PAGE KATALOG DETAIL:
    htmlid="page-katalog-detail"
    Layout: topbar sederhana navy + content

    Topbar navy:
      back-btn + "Detail Katalog & Form Template"

    Content (bg var(--bg-page) p-8):
      Grid 2 kolom:

      KIRI — Katalog card (detail-card):
        Header "Undangan Pernikahan"
        Badge pill: "Offset" bg-blue-100 text-blue-800

        Detail rows:
          Kategori: Cetak Offset
          Harga Dasar: Rp 4.500 / pcs
          Min. Order: 50 pcs
          Satuan: pcs
          Kuota Revisi Default: 3 kali
          Estimasi Pengerjaan: 3-5 hari kerja
          Status: Aktif ✓

        Tombol row:
          "Edit Katalog" outline btn
          "Nonaktifkan" btn-xs-red

      KANAN — Form Template (detail-card):
        Header "Form Template — Field Dinamis"
        Sub: "Field berikut akan tampil saat pelanggan memesan produk ini"

        ftpl-card untuk Undangan Pernikahan (16 field):
          1 | nama_mempelai_pria | Nama Mempelai Pria | text | *wajib
          2 | nama_mempelai_wanita | Nama Mempelai Wanita | text | *wajib
          3 | nama_keluarga_pria | Nama Keluarga Mempelai Pria | text | *wajib
          4 | nama_keluarga_wanita | Nama Keluarga Mempelai Wanita | text | *wajib
          5 | akad_hari | Hari Akad Nikah | text | *wajib
          6 | akad_tanggal | Tanggal Akad Nikah | date | *wajib
          7 | akad_waktu | Waktu Akad Nikah | time | *wajib
          8 | akad_tempat | Tempat Akad Nikah | textarea | *wajib
          9 | resepsi_hari | Hari Resepsi | text | *wajib
          10 | resepsi_tanggal | Tanggal Resepsi | date | *wajib
          11 | resepsi_waktu | Waktu Resepsi | time | *wajib
          12 | resepsi_tempat | Tempat Resepsi | textarea | *wajib
          13 | turut_mengundang | Turut Mengundang | textarea | opsional
          14 | hiburan | Hiburan (jika ada) | text | opsional
          15 | lampiran_peta | Lampiran Peta Lokasi | file | opsional
          16 | keterangan | Keterangan Tambahan | textarea | opsional

        Tombol "+ Tambah Field" outline kecil di bawah

      BAWAH — Katalog Kedua (Undangan Khitanan):
      Grid 2 kolom:

      KIRI — Card info katalog:
        Nama: Undangan Khitanan
        Harga Dasar: Rp 4.000/pcs
        Min. Order: 50 pcs
        Kuota Revisi: 3 kali
        dst.

      KANAN — Form Template Khitanan (11 field):
        1 | nama_anak | Nama Anak yang Dikhitan | text | *wajib
        2 | nama_bapak | Nama Bapak | text | *wajib
        3 | nama_ibu | Nama Ibu | text | *wajib
        4 | resepsi_hari | Hari Resepsi | text | *wajib
        5 | resepsi_tanggal | Tanggal Resepsi | date | *wajib
        6 | resepsi_waktu | Waktu Resepsi | time | *wajib
        7 | resepsi_tempat | Tempat Resepsi | textarea | *wajib
        8 | turut_mengundang | Turut Mengundang | textarea | opsional
        9 | hiburan | Hiburan (jika ada) | text | opsional
        10 | lampiran_peta | Lampiran Peta Lokasi | file | opsional
        11 | keterangan | Keterangan Tambahan | textarea | opsional

      Note box bawah (bg-blue-50 border-blue-100 rounded-xl p-4):
        "Katalog tanpa form_templates (misal: Brosur A5, Banner) menggunakan
        kolom detail_pesanan TEXT pada tabel orders untuk spesifikasi bebas."

    PAGE VERIFIKASI DP:
    htmlid="page-verif-dp"
    Layout: topbar navy sederhana + content full

    Topbar: back-btn + "Verifikasi Pembayaran DP — Bagian Keuangan"

    Content (bg var(--bg-page) p-8):

      Summary row 3 cards (grid-3 mb-6):
        ⏳ Menunggu Verifikasi: 4
        ✅ Terverifikasi Hari Ini: 6
        ❌ Ditolak Hari Ini: 1

      Filter row:
        Status select: Semua | Menunggu | Terverifikasi | Ditolak
        Tanggal dari: date input
        Tanggal sampai: date input
        Btn "Filter" navy
        Search input kanan

      Tabel tbl-wrap:
      Header: "Daftar Pembayaran DP" + info muted "Data diperbarui real-time"
      Kolom: Kode Bayar | Kode Order | Pelanggan | Nominal | Bukti Transfer | Waktu Upload | Status | Aksi

      8 baris data dummy:
      PAY-20260529-0003 | ORD-20260529-0001 | Andi Saputra | Rp 450.000 | [btn 📎 Lihat Bukti] | 29 Mei 09:45 | badge-menunggu | ACC, Tolak
      PAY-20260529-0004 | ORD-20260528-0003 | Siti Rahmah | Rp 187.500 | [btn 📎 Lihat Bukti] | 29 Mei 11:20 | badge-menunggu | ACC, Tolak
      PAY-20260528-0007 | ORD-20260527-0007 | Budi Santoso | Rp 375.000 | [btn 📎 Lihat Bukti] | 28 Mei 14:05 | badge-menunggu | ACC, Tolak
      PAY-20260528-0005 | ORD-20260525-0005 | CV Maju Jaya | Rp 600.000 | [btn 📎 Lihat Bukti] | 28 Mei 16:30 | badge-menunggu | ACC, Tolak
      PAY-20260527-0009 | ORD-20260524-0009 | Andi Saputra | Rp 200.000 | [btn 📎 Lihat Bukti] | 27 Mei 10:00 | badge-terverif | — (sudah diproses)
      PAY-20260527-0008 | ORD-20260523-0008 | Siti Rahmah | Rp 300.000 | [btn 📎 Lihat Bukti] | 27 Mei 09:15 | badge-terverif | —
      PAY-20260526-0006 | ORD-20260522-0006 | Budi Santoso | Rp 450.000 | [btn 📎 Lihat Bukti] | 26 Mei 13:40 | badge-terverif | —
      PAY-20260526-0004 | ORD-20260521-0004 | CV Maju Jaya | Rp 250.000 | [btn 📎 Lihat Bukti] | 26 Mei 11:55 | badge-batal "Ditolak" | btn "Alasan"

      Aksi:
        "ACC" → btn-xs-green onclick: showModal('modalAccDp', rowData)
        "Tolak" → btn-xs-red onclick: showModal('modalTolakDp')
        "Lihat Bukti" → showModal('modalBuktiBayar')

      Modal ACC DP:
        Title: "Verifikasi Pembayaran DP"
        Info card bg-blue-50: Kode Bayar, Kode Order, Pelanggan, Nominal
        Text: "Pembayaran DP ini akan diverifikasi dan pesanan dilanjutkan ke proses produksi."
        Textarea: Catatan (opsional)
        Btn: "✓ Verifikasi Sekarang" green + Batal

      Modal Tolak DP:
        Title: "Tolak Bukti Pembayaran"
        Info: kode bayar + pelanggan
        Text: "Pelanggan akan menerima notifikasi dan diminta mengupload ulang bukti yang valid."
        Textarea: Alasan penolakan (wajib)
        Btn: "Tolak Pembayaran" red + Batal

      Modal Lihat Bukti:
        Title: "Bukti Transfer"
        Placeholder image besar (bg abu, icon 🧾, text "Bukti Transfer.jpg")
        Info row: Pelanggan, Nominal, Waktu Upload
        Btn: "Download" outline + Tutup

    PAGE VERIFIKASI PELUNASAN:
    htmlid="page-verif-pelunasan"
    Struktur sama persis dengan verif-dp

    Perbedaan:
      Title: "Verifikasi Pelunasan & Nota Tagihan"

      Summary cards:
        ⏳ Menunggu Pelunasan: 2
        📋 Nota Tagihan Aktif: 1
        ✅ Lunas Hari Ini: 3

      Info note box (bg-amber-50 border-amber-200 rounded-xl p-4 mb-6):
        "Pelunasan perseorangan aktif setelah status siap_kirim atau siap_diambil.
        Pelunasan perusahaan menggunakan nota tagihan yang aktif setelah pesanan_diterima."

      Tabel kolom tambahan: Jenis (Pelunasan / Nota Tagihan)

      5 baris data:
      PAY-20260529-0010 | ORD-20260520-0012 | Andi Saputra | Pelunasan | Rp 450.000 | Lihat | 29 Mei 08:20 | badge-menunggu | ACC, Tolak
      PAY-20260529-0011 | ORD-20260519-0010 | Siti Rahmah | Pelunasan | Rp 375.000 | Lihat | 29 Mei 10:45 | badge-menunggu | ACC, Tolak
      NOT-20260528-0001 | ORD-20260515-0008 | PT Berkah Abadi | Nota Tagihan | Rp 2.400.000 | Lihat | 28 Mei 15:00 | badge-terverif | —
      PAY-20260527-0012 | ORD-20260518-0009 | Budi Santoso | Pelunasan | Rp 300.000 | Lihat | 27 Mei 09:30 | badge-terverif | —
      PAY-20260526-0008 | ORD-20260517-0007 | CV Maju Jaya | Nota Tagihan | Rp 1.800.000 | Lihat | 26 Mei 14:20 | badge-terverif | —

    PAGE LAPORAN ADMIN:
    htmlid="page-laporan-admin"
    Layout: topbar navy + content

    Content (bg var(--bg-page) p-8):

      Filter row:
        Dari: date input (default 1 Mei 2026)
        Sampai: date input (default 29 Mei 2026)
        Status: select Semua | Selesai | Proses | Dibatalkan
        Kategori: select Semua | Offset | Digital | Grafis
        Btn "Terapkan Filter" navy
        Btn "Ekspor Excel" outline kanan

      Summary grid-3 mt-6:
        📦 Total Pesanan: 47
        💰 Total Transaksi: Rp 28.450.000
        📊 Rata-rata/Pesanan: Rp 605.320

      Grid 2 kolom:
        KIRI — Grafik Batang Pendapatan Per Minggu (line-chart-wrap):
          Label: "Pendapatan Mingguan — Mei 2026"
          SVG bar chart sederhana:
            Minggu 1 (1-7 Mei): Rp 4.200.000
            Minggu 2 (8-14 Mei): Rp 6.800.000
            Minggu 3 (15-21 Mei): Rp 7.500.000
            Minggu 4 (22-29 Mei): Rp 9.950.000
          Bar width proporsional, tinggi = nilai/max*120px
          Label di bawah bar, nilai di atas

        KANAN — Grafik Pie CSS (donut sederhana):
          Label: "Distribusi Kategori Pesanan"
          CSS conic-gradient donut chart:
            Offset: 45% (#2E5CE6)
            Digital: 32% (#10B981)
            Grafis: 23% (#F59E0B)
          Legend di kanan: warna + label + %

      Tabel Rekap Per Kategori (tbl-wrap mt-6):
      Header: "Rekap Per Kategori"
      Kolom: Kategori | Jumlah Pesanan | Total Pendapatan | % Total | Rata-rata/Pesanan
      Data:
        Offset | 21 | Rp 12.500.000 | 43.9% | Rp 595.238
        Digital | 15 | Rp 8.750.000 | 30.7% | Rp 583.333
        Grafis | 11 | Rp 7.200.000 | 25.3% | Rp 654.545
        Total | 47 | Rp 28.450.000 | 100% | Rp 605.319
      (baris total: font-weight 800, border-top 2px navy)

      Tabel Detail Pesanan (tbl-wrap):
      Header: "Daftar Pesanan" + search
      Kolom: No | Kode Order | Pelanggan | Produk | Kategori | Total | Status | Tgl Selesai
      10 baris data dummy semua status selesai
      Pagination: Halaman 1 dari 5 | Prev | 1 2 3 ... 5 | Next

    PAGE LAPORAN OWNER:
    htmlid="page-laporan-owner"
    Layout: topbar navy + content

    Content (bg var(--bg-page) p-8):

      Filter row:
        Bulan: select Jan-Des 2026 (default Mei)
        Tahun: select 2026
        Btn "Tampilkan" navy
        Btn "Ekspor Excel" outline

      Summary grid-4 mt-6:
        📦 Total Pesanan: 47
        💰 Total Pendapatan: Rp 28.450.000
        📈 Pesanan Tumbuh: +12% vs bulan lalu
        ⏱ Rata-rata Selesai: 4.2 hari

      Grafik Line — Pendapatan Harian (line-chart-wrap):
        Label: "Tren Pendapatan Harian — Mei 2026"
        SVG line chart:
          28 titik data (1-28 Mei), nilai fluktuasi realistis
          polyline stroke accent width 2, no fill
          dot circle kecil di setiap titik
          area bawah garis: fill gradient accent 10% opacity
          grid lines horizontal tipis (5 garis)
          label tanggal setiap 7 hari di x-axis
          label nilai di y-axis kiri (Rp 0, 500K, 1M, 1.5M, 2M)
        Implementasi SVG:
          viewBox="0 0 700 160"
          x-axis: 28 titik → spasi 700/27 ≈ 25.9px per titik
          y-axis: nilai max=2000000, scale = val/max*140
          polyline points="x1,y1 x2,y2 ..."
          path d="M x1,y1 L x2,y2 ... L x28,y28 V 160 H x1 Z" fill gradient

      Grid 2 kolom:
        KIRI — Tabel Rekap Per Kategori:
          Sama dengan laporan admin + kolom Pertumbuhan vs Bulan Lalu

        KANAN — Top 5 Produk Terlaris:
          List ranked cards:
            1. Undangan Pernikahan — 18 pesanan — Rp 8.100.000
            2. Brosur A5 — 12 pesanan — Rp 4.500.000
            3. Kartu Nama Premium — 8 pesanan — Rp 3.200.000
            4. Kalender Meja — 5 pesanan — Rp 3.750.000
            5. Stiker Vinyl — 4 pesanan — Rp 1.800.000
          Setiap item: nomor bold accent + nama + jumlah + total
          Progress bar relatif terhadap #1

      Catatan bawah (italic muted):
        "Data laporan bersumber dari tabel orders, payments yang sudah
        berstatus selesai/terverifikasi pada periode yang dipilih."

    PAGE INVOICE:
    htmlid="page-invoice"
    Layout: topbar navy + content bg abu

    Topbar: back + "Invoice / Nota Tagihan" + Btn "Print / Download" outline

    Content (bg var(--bg-page) p-8):

      Tab selector atas:
        [Perseorangan - Lunas] [Perusahaan - Nota Tagihan]
        Klik tab → toggle tampilan

      TAB PERSEORANGAN:
      invoice-wrap:

        invoice-header:
          KIRI: logo Z'PLACK SIMENAK + sub "Ecosystem Cetak"
          KANAN: invoice-title "KWITANSI PEMBAYARAN" + invoice-num "INV-20260529-0001"

        invoice-body:

          invoice-meta (grid 2 kolom):
            KIRI:
              Kepada: Andi Saputra
              Email: andi@email.com
              Telepon: 081234567890
            KANAN:
              Tanggal Invoice: 29 Mei 2026
              Kode Order: ORD-20260529-0001
              Status: [badge selesai "Lunas"]

          invoice-table:
            Header: Deskripsi | Qty | Harga Satuan | Total
            Row 1: Undangan Pernikahan (Cetak Offset) | 200 pcs | Rp 4.500 | Rp 900.000
            Row 2: (spasi, tidak ada row)

          invoice-total:
            Subtotal: Rp 900.000
            Pembayaran DP (50%): -Rp 450.000
            ───────────────────────────────
            Sisa Pelunasan: Rp 450.000
            Sudah Dilunasi: Rp 450.000
            ═══════════════════════════════
            TOTAL TERBAYAR: Rp 900.000

          Info pembayaran (grid 2 kolom, font kecil):
            Metode: Transfer Bank | Pengiriman: Kurir JNE
            Resi: JNE-1234567890 | Tanggal Lunas: 3 Juni 2026

        invoice-footer:
          KIRI: "Terima kasih telah mempercayakan kebutuhan cetak kepada Z'Plack."
          KANAN: badge-selesai "LUNAS"

      TAB PERUSAHAAN (hidden default):
      invoice-wrap:

        invoice-header:
          KIRI: logo Z'PLACK SIMENAK
          KANAN: invoice-title "NOTA TAGIHAN" + invoice-num "NOT-20260528-0001"

        invoice-body:

          Note box (bg-blue-50 border-blue-200 rounded-xl p-4 mb-6):
            "Nota tagihan ini merupakan permohonan transfer untuk pelunasan
            pesanan atas nama perusahaan. Mohon melakukan transfer ke rekening
            Z'Plack dalam 3 hari kerja."

          invoice-meta:
            KIRI: Tagihan Kepada: PT Berkah Abadi, Nama PIC: Siti Rahmah
            KANAN: Tgl Tagihan: 28 Mei 2026, Kode Order: ORD-20260515-0008

          invoice-table:
            Deskripsi | Qty | Satuan | Harga | Total
            Desain & Cetak Map Dossier Korporat | 500 | pcs | Rp 4.800 | Rp 2.400.000

          invoice-total:
            Subtotal: Rp 2.400.000
            Diskon (0%): Rp 0
            ───────────────────────
            TOTAL TAGIHAN: Rp 2.400.000

          Info rekening (card bg-navy text-white rounded-xl p-5 mt-4):
            "Transfer ke:"
            Bank: BCA | No. Rekening: 1234567890 | Atas Nama: Z'Plack Printing
            Batas Pembayaran: 31 Mei 2026

        invoice-footer:
          KIRI: note "Simpan nota ini sebagai bukti transaksi"
          KANAN: badge-menunggu "MENUNGGU TRANSFER"

    PAGE EMAIL PREVIEW:
    htmlid="page-email-preview"
    Layout: topbar navy + content

    Topbar: back + "Contoh Email Notifikasi"

    Content (bg var(--bg-page) p-8):

      Tab selector 4 tab:
        [DP Terverifikasi] [Draft Tersedia] [Siap Kirim] [Keuangan - DP Baru]

      ══ TAB 1: DP TERVERIFIKASI (ke Pelanggan) ══
      email-wrap:

        email-preheader:
          "Pembayaran DP kamu sudah dikonfirmasi — pesanan dilanjutkan ke produksi"

        email-header:
          logo Z'PLACK SIMENAK + sub "Ecosystem Cetak"

        email-body:
          email-greeting: "Halo, Andi 👋"
          email-text:
            "Kabar baik! Pembayaran DP (uang muka) untuk pesanan kamu
            sudah berhasil diverifikasi oleh Bagian Keuangan Z'Plack.
            Pesanan kamu sekarang masuk ke antrian produksi."

          email-info-card:
            Kode Order: ORD-20260529-0001
            Produk: Undangan Pernikahan
            Nominal DP: Rp 450.000
            Status: Terverifikasi ✓
            Tgl Verifikasi: 29 Mei 2026 10:15

          email-text:
            "Bagian Produksi akan segera mengerjakan desain. Kamu akan
            mendapat notifikasi lagi saat draft desain pertama sudah siap
            untuk direview."

          email-cta:
            btn "Pantau Status Pesanan" → link ke tracking

        email-footer:
          "© 2026 SIMENAK Z'Plack — Ecosystem Cetak
          Bandung, Jawa Barat | info@zplack.com
          Email ini dikirim otomatis, mohon tidak membalas."

      ══ TAB 2: DRAFT TERSEDIA (ke Pelanggan) ══
      email-wrap (hidden):

        preheader: "Draft desain pertama sudah siap — silakan review"

        body:
          greeting: "Halo, Andi 👋"
          text: "Tim Produksi Z'Plack sudah selesai mengerjakan draft desain
                pertama untuk pesanan kamu. Silakan review dan berikan feedback."

          info-card:
            Kode Order: ORD-20260529-0001
            Produk: Undangan Pernikahan
            Versi Draft: v1
            Sisa Kuota Revisi: 3 kali
            Diunggah oleh: Bagian Produksi

          note box (bg-amber-50 rounded-xl p-4):
            "⚠ Penting: Kamu memiliki kuota 3 kali revisi untuk pesanan ini.
            Gunakan dengan bijak dan berikan catatan revisi yang detail."

          cta: "Review Draft Sekarang"

      ══ TAB 3: SIAP KIRIM + RESI (ke Pelanggan) ══
      email-wrap (hidden):

        preheader: "Pesanan kamu sudah dikirim — resi: JNE-1234567890"

        body:
          greeting: "Halo, Andi 👋 Pesanan sudah dikirim!"
          text: "Pesanan kamu sudah selesai diproduksi dan telah dikirim
                melalui JNE Express. Berikut informasi pengiriman kamu."

          info-card:
            Kode Order: ORD-20260529-0001
            Produk: Undangan Pernikahan (200 pcs)
            Ekspedisi: JNE Express
            Nomor Resi: JNE-1234567890
            Tgl Kirim: 4 Juni 2026
            Estimasi Tiba: 5-6 Juni 2026

          text:
            "Lacak pengirimanmu di website JNE atau klik tombol di bawah
            untuk konfirmasi penerimaan setelah barang tiba."

          cta: "Konfirmasi Penerimaan"

          note kecil: "Setelah konfirmasi penerimaan, kamu akan diminta
                      melakukan pembayaran pelunasan 50%."

      ══ TAB 4: NOTIF KE KEUANGAN — DP BARU ══
      email-wrap (hidden):

        email-header: bg berbeda — bg-amber-700 (warna internal)

        preheader: "Bukti DP baru dari Andi Saputra menunggu verifikasi"

        body:
          greeting: "Halo, Bagian Keuangan"
          text: "Ada bukti pembayaran DP baru yang diunggah dan menunggu
                verifikasi kamu."

          info-card:
            Kode Pembayaran: PAY-20260529-0003
            Dari: Andi Saputra
            Untuk Order: ORD-20260529-0001 (Undangan Pernikahan)
            Nominal: Rp 450.000
            Diunggah: 29 Mei 2026 09:45

          note box (bg-red-50 border-red-200):
            "⏰ Verifikasi dalam 2 jam untuk menghindari notifikasi eskalasi."

          cta: "Verifikasi Sekarang"

    PAGE APPROVAL HISTORY:
    htmlid="page-approval-history"
    Layout: topbar navy + content

    Content (bg var(--bg-page) p-8):
      max-width 800px mx-auto

      Header card (bg-white rounded-2xl border p-6 mb-6):
        Row info pesanan:
          Kode: ORD-20260529-0001
          Produk: Undangan Pernikahan
          Pelanggan: Andi Saputra
        Kuota row:
          "Kuota Revisi: 3 kali"
          Progress bar visual: [■■□] — 2 digunakan, 1 tersisa
          Text merah: "Sisa 1 kuota revisi"

      Label "Riwayat Revisi Desain" 14px font-700 navy mb-4

      ah-timeline:

        ah-item (v1 — Ditolak):
          ah-dot-rev (merah)
          ah-card:
            ah-header: "Draft v1 — REV-0001-01" + badge-batal "Ditolak" + "28 Mei 2026 09:00"
            ah-preview-row:
              ah-thumb: 🖼
              ah-notes:
                "Catatan Produksi:" — Draft pertama, font disesuaikan dengan referensi
                "Catatan Revisimu:" — Tolong ganti warna background ke merah maroon.
                                      Font nama mempelai juga kurang tebal.

        ah-item (v2 — Ditolak):
          ah-dot-rev
          ah-card:
            ah-header: "Draft v2 — REV-0001-02" + badge-batal "Ditolak" + "28 Mei 2026 14:00"
            ah-preview-row:
              ah-thumb: 🖼
              ah-notes:
                "Catatan Produksi:" — Background sudah merah maroon, font nama dipertebal
                "Catatan Revisimu:" — Ukuran font nama mempelai wanita masih kurang besar.
                                      Tolong perbesar sedikit.

        ah-item (v3 — ACC):
          ah-dot-acc (hijau)
          ah-card:
            ah-header: "Draft v3 — REV-0001-03" + badge-selesai "ACC ✓" + "29 Mei 2026 09:30"
            ah-preview-row:
              ah-thumb: 🖼
              ah-notes:
                "Catatan Produksi:" — Font nama sudah diperbesar, layout disesuaikan
                "Catatan Revisimu:" — (tidak ada — desain di-ACC)
                note hijau kecil: "✓ Desain disetujui — pesanan lanjut ke proses cetak"

      Note bawah (italic muted text-center):
        "Approval history lengkap tersimpan di tabel revisi_desain dengan
        kode format REV-[ID_ORDER]-[VV]"

    PAGE NOTIF PANEL:
    htmlid="page-notif-panel"
    Layout: topbar navy + content

    Topbar: back + "Panel Notifikasi In-App"

    Content (bg var(--bg-page) p-8):
      max-width 680px mx-auto

      Tab 2 role:
        [Admin] [Bagian Produksi]
        Klik → toggle list notifikasi

      Tab header: judul + badge count unread total

      ══ NOTIF ADMIN ══
      notif-list:

        notif-item unread (ni-amber):
          ikon ⭐
          title: "Pesanan Custom Baru Masuk"
          sub: "ORD-20260529-0006 dari Andi Saputra memerlukan konfirmasi harga dari Admin."
          time: "5 menit yang lalu"
          dot biru kanan

        notif-item unread (ni-amber):
          ikon 🏢
          title: "Pengajuan Verifikasi Perusahaan"
          sub: "CV Maju Jaya mengajukan verifikasi perusahaan. Dokumen NPWP dan KTP PIC tersedia."
          time: "1 jam yang lalu"
          dot biru

        notif-item unread (ni-red):
          ikon ❌
          title: "Pesanan Custom Dibatalkan"
          sub: "ORD-20260528-0004 (Packaging Custom) dibatalkan oleh CV Maju Jaya."
          time: "3 jam yang lalu"
          dot biru

        notif-item (sudah dibaca, ni-blue):
          ikon 📋
          title: "Pesanan Custom Baru Masuk"
          sub: "ORD-20260527-0003 dari Siti Rahmah memerlukan konfirmasi harga."
          time: "2 hari yang lalu"

        notif-item (ni-blue):
          ikon 🏢
          title: "Pengajuan Verifikasi Perusahaan"
          sub: "PT Berkah Abadi mengajukan verifikasi. Sudah direview."
          time: "3 hari yang lalu"

      ══ NOTIF PRODUKSI (hidden) ══
      notif-list:

        notif-item unread (ni-red):
          ikon ↺
          title: "Revisi Desain Diajukan"
          sub: "Pelanggan Andi Saputra mengajukan revisi untuk ORD-20260529-0001.
              Catatan: 'Ukuran font nama mempelai wanita kurang besar.'"
          time: "2 jam yang lalu"
          dot biru

        notif-item unread (ni-green):
          ikon ✅
          title: "Desain Di-ACC — Siap Cetak"
          sub: "Desain ORD-20260527-0007 (Kalender Meja) telah di-ACC pelanggan.
              Lanjutkan ke proses cetak."
          time: "4 jam yang lalu"
          dot biru

        notif-item (ni-green):
          ikon ✅
          title: "Desain Di-ACC — Siap Cetak"
          sub: "ORD-20260526-0009 (Map Dossier) di-ACC. Lanjutkan proses cetak."
          time: "1 hari yang lalu"

        notif-item (ni-red):
          ikon ↺
          title: "Revisi Desain Diajukan"
          sub: "Siti Rahmah mengajukan revisi untuk ORD-20260528-0003.
              Catatan: 'Ganti warna background ke merah maroon.'"
          time: "2 hari yang lalu"

      Mark all as read btn (outline pill kecil, kanan atas list)

    JAVASCRIPT FILE 3:
    javascript// PAGE NAVIGATION
    function showPage(id)
      - querySelectorAll('.page').forEach remove active
      - getElementById(id).classList.add('active')
      - scrollTo(0,0)

    // TAB SWITCHING (general)
    function switchTab(groupId, activeId)
      - hide semua [data-tabgroup=groupId]
      - show activeId
      - update tab button active state

    // INVOICE TAB
    function showInvoiceTab(type)
      - if type === 'perseorangan': show invoice-perseorangan, hide invoice-perusahaan
      - else: show invoice-perusahaan, hide invoice-perseorangan

    // EMAIL TAB
    function showEmailTab(id)
      - hide semua .email-tab-content
      - show id

    // NOTIF TAB
    function showNotifTab(role)
      - hide semua .notif-tab-content
      - show notif-{role}
      - update tab buttons

    // FILTER LAPORAN
    function applyFilter()
      - get filter values
      - update summary cards (demo — angka berubah dummy)
      - show/hide tabel rows based on filter

    // CHART BAR RENDER
    function renderBarChartAdmin()
      - data = [{label:'Mg1',val:4200000},{label:'Mg2',val:6800000}...]
      - max = Math.max(...data.map(d=>d.val))
      - forEach: create bar col with height = val/max*120 + 'px'
      - append to chart container

    // CHART LINE SVG OWNER
    function renderLineChart()
      - data = 28 nilai harian Mei 2026 (array angka)
      - maxVal = 2000000, svgW = 700, svgH = 160
      - xStep = svgW / (data.length - 1)
      - points = data.map((v,i) => `${i*xStep},${svgH - v/maxVal*140}`)
      - set polyline points attribute
      - area path: M x0,y0 L x1,y1 ... L xlast,ylast V svgH H 0 Z

    // DONUT CHART CSS
    function renderDonutChart()
      - offset=45%, digital=32%, grafis=23%
      - conic-gradient(accent 0% 45%, #10B981 45% 77%, #F59E0B 77% 100%)
      - set style.background pada .donut-el

    // MODAL
    function showModal(id) — openModal
    function hideModal(id) — closeModal
    Overlay click + ESC key tutup modal

    // DOTS MENU
    function toggleDots(id) — toggle .dots-dropdown open
    Click outside → close all

    // MARK ALL READ
    function markAllRead()
      - querySelectorAll('.notif-item.unread').forEach remove unread + remove dot
      - update badge count ke 0

    // SEARCH FILTER TABEL
    function filterTable(searchId, tbodyId)
      - oninput event
      - toLowerCase query
      - show/hide rows yang match

    // PRINT INVOICE
    function printInvoice()
      - window.print() dengan CSS @media print yang hide semua kecuali .invoice-wrap

    DATA DUMMY LENGKAP (referensi untuk semua tabel):
    ORDERS:
    ORD-20260529-0001 | Andi Saputra | Undangan Pernikahan | perseorangan | kurir | 200pcs | Rp900.000 | proses_desain
    ORD-20260528-0003 | Siti Rahmah | Undangan Khitanan | perseorangan | kurir | 100pcs | Rp400.000 | menunggu_verifikasi_dp
    ORD-20260527-0007 | Budi Santoso | Kalender Meja | perusahaan | kurir | 50pcs | Rp1.250.000 | dikirim
    ORD-20260526-0009 | CV Maju Jaya | Map Dossier | perusahaan | kurir | 500pcs | Rp4.000.000 | proses_cetak
    ORD-20260525-0011 | Andi Saputra | Kartu Nama | perseorangan | ambil_sendiri | 200pcs | Rp400.000 | finishing
    ORD-20260524-0008 | Siti Rahmah | Stiker Vinyl | perseorangan | kurir | 50lembar | Rp750.000 | pesanan_diterima
    ORD-20260523-0006 | Budi Santoso | Brosur A5 | perseorangan | ambil_sendiri | 500lembar | Rp750.000 | selesai
    ORD-20260522-0005 | CV Maju Jaya | Banner Flexi | perusahaan | kurir | 10m² | Rp350.000 | selesai
    ORD-20260521-0004 | Andi Saputra | Map Dossier | perseorangan | kurir | 100pcs | Rp800.000 | selesai
    ORD-20260520-0002 | PT Berkah | Desain Promosi | perusahaan | ambil_sendiri | 1project | Rp2.400.000 | selesai

    PAYMENTS:
    PAY-20260529-0003 | ORD-0001 | dp | Rp450.000 | menunggu
    PAY-20260529-0004 | ORD-0003 | dp | Rp187.500 | menunggu
    PAY-20260528-0007 | ORD-0007 | pelunasan | Rp750.000 | menunggu
    PAY-20260527-0009 | ORD-0008 | dp | Rp200.000 | terverifikasi
    PAY-20260526-0006 | ORD-0009 | dp | Rp225.000 | terverifikasi
    PAY-20260529-0010 | ORD-0024 | pelunasan | Rp450.000 | menunggu
    NOT-20260528-0001 | ORD-0020 | nota_tagihan | Rp2.400.000 | terverifikasi

    REVISI DESAIN:
    REV-0001-01 | ORD-0001 | v1 | diajukan_revisi | Ganti warna background ke merah maroon
    REV-0001-02 | ORD-0001 | v2 | diajukan_revisi | Ukuran font nama mempelai kurang besar
    REV-0001-03 | ORD-0001 | v3 | acc | —
    REV-0003-01 | ORD-0003 | v1 | uploaded | —

    ORDER ATTRIBUTES (EAV contoh untuk ORD-0001):
    nama_mempelai_pria: Ahmad Fauzi
    nama_mempelai_wanita: Siti Rahmah
    nama_keluarga_pria: Bpk. H. Suparman & Ibu Hj. Siti
    nama_keluarga_wanita: Bpk. H. Sumarno & Ibu Hj. Rahmah
    akad_hari: Sabtu
    akad_tanggal: 2026-06-15
    akad_waktu: 08:00
    akad_tempat: Masjid Al-Ikhlas, Jl. Merdeka No.12, Bandung
    resepsi_hari: Sabtu
    resepsi_tanggal: 2026-06-15
    resepsi_waktu: 11:00
    resepsi_tempat: Gedung Serbaguna Cimahi, Jl. Gatot Subroto No.5
    turut_mengundang: Kol. (Purn.) H. Budi Santoso & Keluarga
    hiburan: Organ Tunggal
    lampiran_peta: peta_masjid_al_ikhlas.jpg
