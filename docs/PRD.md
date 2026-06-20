# SIMENAK — Product Requirement Document

**Sistem Informasi Pemesanan Percetakan Z'Plack Berbasis Web**
Versi: 2.1 | Update terakhir: Juni 2026
Mahasiswa: Destiandira Rakhadian | 10522088 | UNIKOM

---

## 1. STACK TEKNIS

| Komponen  | Teknologi                                  |
| --------- | ------------------------------------------ |
| Framework | CodeIgniter 4 (PHP 8.2, MVC)               |
| Database  | MySQL 8                                    |
| Frontend  | HTML murni + Tailwind CSS CDN + Vanilla JS |
| Font      | Plus Jakarta Sans (Google Fonts CDN)       |
| Email     | CI4 Email Library + Gmail SMTP             |
| Upload    | FCPATH . 'public/uploads/[folder]/'        |

---

## 2. DESIGN SYSTEM

### Warna

```css
--navy: #051747;
--navy-mid: #0a2860;
--blue-accent: #2e5ce6;
--bg-page: #f0f2f8;
--text-body: #4a5568;
--text-muted: #83a2cd;
--border: #e2e8f0;
```

### Font

```html

```

### Komponen Standar

- **Card**: `bg-white border border-[#E2E8F0] rounded-xl shadow-sm p-6`
- **Tombol Primary**: `bg-[#051747] text-white px-5 py-2.5 rounded-full font-bold uppercase hover:bg-[#2E5CE6] transition`
- **Tombol Outline**: `border-[1.5px] border-[#051747] rounded-full hover:bg-[#051747] hover:text-white`
- **Tombol Danger**: `bg-red-500 text-white rounded-full hover:bg-red-600`
- **Input**: `border-[1.5px] border-[#E2E8F0] rounded-2xl px-3.5 py-2.5 focus:border-[#2E5CE6] focus:ring-3 focus:ring-[#2E5CE6]/10`
- **Tabel thead**: `bg-[#051747] text-white text-xs uppercase tracking-wide`
- **Tabel tbody hover**: `hover:bg-[#F8FAFF]`
- **Sidebar**: `bg-[#051747] w-64 fixed left-0 top-0 min-h-screen`
- **Menu aktif**: `bg-[#2E5CE6] rounded-lg`
- **Menu hover**: `bg-white/8 rounded-lg`

### Badge Status

menunggu\_\* → bg-[#FEF3C7] text-[#92400E]
terverifikasi → bg-[#DBEAFE] text-[#1E40AF]
proses_desain/revisi/cetak → bg-[#EDE9FE] text-[#5B21B6]
finishing → bg-[#CFFAFE] text-[#164E63]
siap_kirim/diambil → bg-[#CCFBF1] text-[#065F46]
dikirim → bg-[#CFFAFE] text-[#164E63]
pesanan_diterima → bg-[#DCFCE7] text-[#166534]
menunggu_verif_lunas → bg-[#FFEDD5] text-[#9A3412]
selesai → bg-[#DCFCE7] text-[#166534]
dibatalkan → bg-[#FEE2E2] text-[#991B1B]

---

## 3. LAYOUT ARCHITECTURE

### Tiga Layout — Jangan Dicampur

**1. `app/Views/layouts/landing.php`**

- Halaman publik (`/`)
- Announcement bar navy di atas
- Navbar transparan + backdrop-blur + sticky
- Login/register via MODAL POPUP (bukan halaman terpisah)
- Footer navy 3 kolom

**2. `app/Views/layouts/pelanggan.php`**

- Semua halaman `/pelanggan/*`
- Navbar atas horizontal (BUKAN sidebar)
- Logo kiri | menu tengah | notif bell + avatar kanan
- Avatar klik → dropdown: Profil, Keluar
- Background `#F0F2F8`

**3. `app/Views/layouts/main.php`** ← SUDAH DIBUAT

- Admin, keuangan, produksi, owner
- Sidebar fixed kiri 256px bg `#051747`
- Topbar sticky bg white glass effect
- Avatar + notif bell di topbar kanan
- Background `#F0F2F8`

- Notif bell: `SELECT COUNT(*) FROM notifications WHERE id_user=X AND is_read=0` (belum)

---

## 4. DATABASE — 11 TABEL FINAL

### Urutan CREATE (ikuti FK dependency):

users → pelanggan → verifikasi_perusahaan →katalog → form_templates → orders → order_attributes →payments → revisi_desain → pengiriman → notifications

### Struktur Tabel

**users**

```sql
id_user INT PK AUTO_INCREMENT
nama VARCHAR(100) NOT NULL
email VARCHAR(100) UNIQUE NOT NULL
password VARCHAR(255) NOT NULL
role ENUM('pelanggan','admin','keuangan','produksi','owner') NOT NULL
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
```

**pelanggan**

```sql
id_pelanggan INT PK AUTO_INCREMENT
id_user INT FK→users
no_telp VARCHAR(20)
alamat TEXT
jenis ENUM('perseorangan','perusahaan') DEFAULT 'perseorangan'
nama_perusahaan VARCHAR(150)
is_verified TINYINT DEFAULT 0
```

**verifikasi_perusahaan**

```sql
id_verify INT PK AUTO_INCREMENT
id_pelanggan INT FK→pelanggan
nama_perusahaan VARCHAR(150) NOT NULL
no_npwp VARCHAR(30)
dokumen_npwp VARCHAR(255)
dokumen_ktp_pic VARCHAR(255)
status ENUM('pending','verified','rejected') DEFAULT 'pending'
catatan_admin TEXT
tgl_pengajuan DATETIME DEFAULT CURRENT_TIMESTAMP
tgl_verifikasi DATETIME
id_admin INT FK→users
```

**katalog** ← SUDAH ADA, KOLOM `gambar` SUDAH DITAMBAHKAN

```sql
id_katalog INT PK AUTO_INCREMENT
nama_produk VARCHAR(150) NOT NULL
kategori ENUM('desain_grafis','cetak_digital','cetak_offset','media_promosi') NOT NULL
harga_dasar DECIMAL(12,2) NOT NULL
min_order INT NOT NULL DEFAULT 1
satuan VARCHAR(30) NOT NULL DEFAULT 'pcs'
kuota_revisi_default INT NOT NULL DEFAULT 2
estimasi_hari VARCHAR(50)
deskripsi TEXT
gambar VARCHAR(255)          ← path: 'uploads/katalog/filename.jpg'
is_active TINYINT DEFAULT 1
```

**form_templates** ← SUDAH ADA

```sql
id_template INT PK AUTO_INCREMENT
id_katalog INT FK→katalog
field_key VARCHAR(50) NOT NULL
field_label VARCHAR(100) NOT NULL
field_type ENUM('text','date','time','textarea','file') NOT NULL
placeholder VARCHAR(150)
is_required TINYINT DEFAULT 1
urutan INT DEFAULT 0
UNIQUE KEY uq_field (id_katalog, field_key)
```

**orders** ← BELUM DIBUAT

```sql
id_order INT PK AUTO_INCREMENT
kode_order VARCHAR(25) UNIQUE NOT NULL   -- format: ORD-YYYYMMDD-XXXX
id_pelanggan INT FK→pelanggan
id_katalog INT FK→katalog
jenis_pelanggan ENUM('perseorangan','perusahaan') NOT NULL
jumlah_order INT NOT NULL
is_custom TINYINT DEFAULT 0
catatan_custom TEXT
harga_custom DECIMAL(12,2)
referensi_desain VARCHAR(255)            -- path: uploads/referensi/
detail_pesanan TEXT                      -- untuk produk tanpa form_templates
deadline DATE
metode_pengiriman ENUM('kurir','ambil_sendiri') NOT NULL
alamat_kirim TEXT
kuota_revisi INT NOT NULL
sisa_kuota INT NOT NULL
total_harga DECIMAL(12,2)
require_dp TINYINT DEFAULT 1
status ENUM(
  'menunggu_konfirmasi_harga',
  'menunggu_konfirmasi_pelanggan',
  'menunggu_verifikasi_dp',
  'terverifikasi',
  'proses_desain',
  'proses_revisi',
  'proses_cetak',
  'finishing',
  'siap_kirim',
  'siap_diambil',
  'dikirim',
  'pesanan_diterima',
  'menunggu_verifikasi_lunas',
  'selesai',
  'dibatalkan'
) NOT NULL DEFAULT 'menunggu_verifikasi_dp'
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
```

**order_attributes** ← EAV, BELUM DIBUAT

```sql
id_attr INT PK AUTO_INCREMENT
id_order INT FK→orders
attribute_key VARCHAR(50) NOT NULL
attribute_val TEXT
UNIQUE KEY uq_order_attr (id_order, attribute_key)
```

**payments** ← BELUM DIBUAT

```sql
id_payment INT PK AUTO_INCREMENT
kode_payment VARCHAR(25) UNIQUE NOT NULL  -- format: PAY-YYYYMMDD-XXXX
id_order INT FK→orders
jenis ENUM('dp','pelunasan') NOT NULL
nominal DECIMAL(12,2) NOT NULL
bukti_tf VARCHAR(255)                     -- path: uploads/bukti_bayar/
status ENUM('menunggu','terverifikasi','ditolak') DEFAULT 'menunggu'
catatan_tolak TEXT
id_verifikator INT FK→users
tgl_upload DATETIME DEFAULT CURRENT_TIMESTAMP
tgl_verifikasi DATETIME
```

**revisi_desain** ← BELUM DIBUAT

```sql
id_revisi INT PK AUTO_INCREMENT
id_order INT FK→orders
id_produksi INT FK→users
versi INT NOT NULL DEFAULT 1
file_draft VARCHAR(255) NOT NULL          -- path: uploads/draft_desain/
catatan_prod TEXT
catatan_revisi TEXT
status ENUM('uploaded','diajukan_revisi','acc','ditolak') DEFAULT 'uploaded'
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
```

**pengiriman** ← BELUM DIBUAT

```sql
id_kirim INT PK AUTO_INCREMENT
id_order INT FK→orders UNIQUE
no_resi VARCHAR(100)
nama_ekspedisi VARCHAR(50)
status_kirim ENUM('siap_kirim','siap_diambil','dikirim','diterima') DEFAULT 'siap_kirim'
tgl_kirim DATETIME
tgl_diterima DATETIME
```

**notifications** ← BELUM DIBUAT

```sql
id_notif INT PK AUTO_INCREMENT
id_user INT FK→users
id_order INT FK→orders NULL
judul VARCHAR(150)
pesan TEXT
is_read TINYINT DEFAULT 0
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
```

---

## 5. FOLDER UPLOAD

public/uploads/
katalog/ ← gambar produk katalog (✅ sudah ada)
referensi/ ← referensi desain dari pelanggan (✅ sudah ada)
bukti_bayar/ ← bukti transfer DP & pelunasan (✅ sudah ada)
draft_desain/ ← draft desain dari bagian produksi (✅ sudah ada)
lampiran_peta/ ← peta lokasi undangan (✅ sudah ada)
dokumen_verifikasi/ ← NPWP & KTP untuk verifikasi perusahaan (✅ sudah ada)

### Aturan Upload (WAJIB ikuti di semua Controller)

```php
// Path upload
$folder = FCPATH . 'public/uploads/katalog/';

// Validasi
$file->isValid()
$file->getMimeType() → hanya jpg, jpeg, png, pdf
$file->getSizeByUnit('mb') → max 2MB (draft_desain max 1MB)

// Naming
$newName = time() . '_' . $file->getName();
$file->move($folder, $newName);

// Simpan ke DB
$gambar = 'uploads/katalog/' . $newName;

// Tampil di View
<img src="<?= base_url($katalog['gambar']) ?>">
```

---

## 6. KODIFIKASI

Kode Order : ORD-[YYYYMMDD]-[XXXX] → ORD-20260529-0001
Kode Payment : PAY-[YYYYMMDD]-[XXXX] → PAY-20260529-0001
Kode Revisi : REV-[ID_ORDER]-[VV] → REV-0001-02

### Generate Kode Order (gunakan di notification_helper.php)

```php
function generateKodeOrder(): string {
    $db = \Config\Database::connect();
    $prefix = 'ORD-' . date('Ymd') . '-';
    $count = $db->table('orders')
                ->like('kode_order', $prefix, 'after')
                ->countAllResults();
    return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}

function generateKodePayment(): string {
    $db = \Config\Database::connect();
    $prefix = 'PAY-' . date('Ymd') . '-';
    $count = $db->table('payments')
                ->like('kode_payment', $prefix, 'after')
                ->countAllResults();
    return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
}
```

---

## 7. BUSINESS RULES (WAJIB diimplementasikan)

### Rule 1 — EAV Pattern

form_templates = definisi field per id_katalog (field_key, field_label,
field_type, is_required, urutan)
order_attributes = jawaban pelanggan per pesanan (id_order, attribute_key,
attribute_val)
UNIQUE KEY (id_order, attribute_key)
SIMPAN:
foreach ($fields as $f) {
    val = $request->getPost(
f['field_key']);
    $db->table('order_attributes')->insert([
        'id_order' => $newOrderId,
        'attribute_key' => $f['field_key'],
        'attribute_val' => $val
    ]);
}
BACA:
$attrs = $db->table('order_attributes')
->where('id_order', $orderId)->get()->getResultArray();
formData=arraycolumn(formData = array_column(
formData=arrayc​olumn(attrs, 'attribute_val', 'attribute_key');
// Akses: $formData['nama_mempelai_pria']
Katalog TANPA form_templates → gunakan kolom detail_pesanan TEXT di orders.

### Rule 2 — Minimum Order Validation

Di OrderController::store():
$jumlah = $request->getPost('jumlah_order');
katalog = $katalogModel->find(
id_katalog);
if ($jumlah < $katalog['min_order']) {
return redirect()->back()->withInput()
->with('error', "Minimum order {katalog['min_order']} {
katalog['satuan']}");
}

### Rule 3 — Customer Type Logic

Di OrderController::store():
$jenis = $request->getPost('jenis_pelanggan');
$pelanggan = $pelangganModel->where('id_user', session('id_user'))->first();
if ($jenis === 'perusahaan' && $pelanggan['is_verified'] == 0) {
$jenis = 'perseorangan'; // paksa
session()->setFlashdata('warning', 'Akun belum terverifikasi perusahaan. Skema diubah ke perseorangan.');
}
requireDp=(requireDp = (
requireDp=(jenis === 'perseorangan') ? 1 : 0;
status=(status = (
status=(jenis === 'perusahaan') ? 'terverifikasi' : 'menunggu_verifikasi_dp';

### Rule 4 — Custom Order Flow

is_custom = 1 → status = 'menunggu_konfirmasi_harga'
→ INSERT notifications (Admin): "Pesanan custom baru: [kode_order]"
→ Admin set harga → status = 'menunggu_konfirmasi_pelanggan'
→ Email ke pelanggan: penawaran harga
→ Pelanggan setuju → lanjut flow normal (Rule 3)
→ Pelanggan tolak → status = 'dibatalkan'
→ INSERT notifications (Admin): "Pesanan custom [kode_order] dibatalkan"
Tombol estimasi harga:
Produk standar → harga_dasar × jumlah_order
Custom → tampil Rp 0 + catatan "Harga ditentukan Admin"

### Rule 5 — Revision Quota System

kuota_revisi = katalog.kuota_revisi_default (set saat pesanan dibuat, TIDAK berubah)
sisa_kuota = kuota_revisi (awal), decrement setiap pelanggan tolak draft
Di RevisiController::tolak():
if ($order['sisa_kuota'] <= 0) {
    return redirect()->back()->with('error', 'Kuota revisi habis. Hanya bisa ACC.');
}
orderModel−>update(orderModel->update(
orderModel−>update(id_order, ['sisa_kuota' => $order['sisa_kuota'] - 1,
                                 'status' => 'proses_revisi']);
Di View (tombol Tolak):
if ($order['sisa_kuota'] <= 0) → disabled + tooltip "Kuota revisi habis"

### Rule 6 — Order Status Flow (15 status, transisi ketat)

menunggu_konfirmasi_harga [is_custom=1]
↓ Admin set harga
menunggu_konfirmasi_pelanggan [is_custom=1]
↓ Pelanggan setuju
menunggu_verifikasi_dp [perseorangan]
↓ Keuangan ACC DP
terverifikasi [perusahaan langsung dari sini]
↓ Produksi mulai
proses_desain
↓ Produksi upload draft
proses_revisi [jika pelanggan tolak, sisa_kuota--]
↓ Produksi upload ulang / Pelanggan ACC
proses_cetak
↓ Produksi update
finishing
↓ Produksi selesai
siap_kirim / siap_diambil
↓ Admin input resi / konfirmasi ambil
dikirim / [konfirmasi ambil langsung]
↓ Pelanggan konfirmasi terima
pesanan_diterima
↓ (perseorangan: upload pelunasan | perusahaan: nota tagihan aktif)
menunggu_verifikasi_lunas
↓ Keuangan ACC
selesai
[bisa dibatalkan sebelum proses_cetak]
dibatalkan

### Rule 7 — Payment Flow

DP (perseorangan):
Aktif setelah pesanan dibuat (status=menunggu_verifikasi_dp)
Pelanggan upload bukti → payments INSERT (jenis=dp, status=menunggu)
Keuangan ACC → orders UPDATE status=terverifikasi
Keuangan Tolak → pelanggan upload ulang
Pelunasan perseorangan:
Aktif saat status = siap_kirim ATAU siap_diambil
Flow sama dengan DP
Nota tagihan perusahaan:
Aktif saat status = pesanan_diterima
jenis = 'pelunasan', tampil sebagai nota tagihan
Kedua jenis:
Upload bukti_tf → status=menunggu → Keuangan ACC/Tolak
File: FCPATH.'public/uploads/bukti_bayar/'

### Rule 8 — Notification System

EMAIL (via CI4 Email + Gmail SMTP) kirim ke:
Pelanggan:

- DP terverifikasi
- Draft desain tersedia (tiap kali Produksi upload)
- Desain di-ACC (lanjut cetak)
- Pesanan siap kirim / siap diambil
- Pesanan dikirim + nomor resi
- Penawaran harga custom dari Admin
- Verifikasi perusahaan disetujui/ditolak
- Pelunasan dikonfirmasi (selesai)
  Keuangan:
- Bukti DP baru diunggah
- Bukti pelunasan baru diunggah
- Reminder jika bukti DP belum diverifikasi >2 jam
  Owner (email + in-app):
- Eskalasi jika bukti DP belum diverifikasi >6 jam
  IN-APP (INSERT ke tabel notifications):
  Admin:
- Pesanan custom baru masuk
- Pengajuan verifikasi perusahaan baru
- Pesanan custom dibatalkan pelanggan
  Produksi:
- Revisi diajukan pelanggan
- Desain di-ACC, siap cetak
  Fungsi helper (notification_helper.php):
  sendNotifEmail($to, $subject, $body)
sendNotifInApp($id_user, $id_order, $judul, $pesan)
generateKodeOrder()
generateKodePayment()
getStatusLabel($status)
  getStatusBadgeClass($status)

---

## 8. LOGIN FLOW

/ (landing page) → tombol LOGIN atau PESAN SEKARANG
→ Modal popup overlay (bukan halaman terpisah)
→ POST /auth/login-ajax via fetch → return JSON
→ JS redirect berdasarkan role:
pelanggan → /pelanggan/dashboard
admin → /admin/dashboard
keuangan → /keuangan/dashboard
produksi → /produksi/dashboard
owner → /owner/dashboard
Tombol "Pesan Sekarang" di katalog:
Belum login → simpan id_katalog ke sessionStorage → buka modal login
Sudah login as pelanggan → /order/create/{id_katalog}
Role lain → tidak tampilkan tombol pesan

---

## 9. STRUKTUR FOLDER CI4

app/
Controllers/
AuthController.php ✅ sudah ada
KatalogController.php ✅ sudah ada
FormTemplateController.php ✅ sudah ada
LandingController.php ← belum dibuat
PelangganController.php ← belum dibuat
OrderController.php ← belum dibuat
PaymentController.php ← belum dibuat
RevisiController.php ← belum dibuat
PengirimanController.php ← belum dibuat
TrackingController.php ← belum dibuat
LaporanController.php ← belum dibuat
ProfilController.php ← belum dibuat
DashboardController.php ← belum dibuat
AdminController.php ← belum dibuat
KeuanganController.php ← belum dibuat
ProduksiController.php ← belum dibuat
OwnerController.php ← belum dibuat
VerifikasiController.php ← belum dibuat
Models/
UserModel.php ✅ sudah ada
PelangganModel.php ← belum dibuat
KatalogModel.php ✅ sudah ada
FormTemplateModel.php ✅ sudah ada
OrderModel.php ← belum dibuat
OrderAttrModel.php ← belum dibuat
PaymentModel.php ← belum dibuat
RevisiDesainModel.php ← belum dibuat
PengirimanModel.php ← belum dibuat
NotificationModel.php ← belum dibuat
VerifikasiPerusahaanModel.php ← belum dibuat
Filters/
AuthFilter.php ✅ sudah ada
RoleFilter.php ✅ sudah ada
Helpers/
notification_helper.php ← belum dibuat
Views/
layouts/
landing.php ← belum dibuat
pelanggan.php ← belum dibuat
main.php ✅ sudah ada
auth.php ✅ sudah ada
landing/
index.php ← belum dibuat
auth/
login.php ✅ sudah ada
register.php ✅ sudah ada
katalog/
index.php ✅ sudah ada
create.php ✅ sudah ada
edit.php ✅ sudah ada
list.php ✅ sudah ada
form_template.php ✅ sudah ada
order/ ← belum dibuat
payment/ ← belum dibuat
revisi/ ← belum dibuat
pengiriman/ ← belum dibuat
tracking/ ← belum dibuat
laporan/ ← belum dibuat
profil/ ← belum dibuat
dashboard/
pelanggan.php ← belum dibuat
admin.php ← belum dibuat
keuangan.php ← belum dibuat
produksi.php ← belum dibuat
owner.php ← belum dibuat
notifikasi/ ← belum dibuat
public/uploads/
katalog/ ✅ sudah ada
referensi/ ✅ sudah ada
bukti_bayar/ ✅ sudah ada
draft_desain/ ✅ sudah ada
lampiran_peta/ ✅ sudah ada
dokumen_verifikasi/ ✅ sudah ada

---

## 10. CODING RULES (WAJIB semua file)

CI4 Query Builder — TIDAK BOLEH raw SQL
esc() di semua output view (XSS prevention)
csrf*field() di semua form
password_hash() simpan | password_verify() login
redirect()->back()->with('success','pesan') setelah POST
Wrap DB operations dalam try/catch
Upload: whitelist jpg,jpeg,png,pdf | max 2MB (draft: 1MB)
File naming: time().'*'.nama_asli, simpan ke FCPATH.'public/uploads/[folder]/'
Session: isLoggedIn + role + id_user + nama + id_pelanggan
Notif in-app: INSERT ke notifications setiap event penting
Email: gunakan CI4 Email library + Config/Email.php SMTP Gmail

---

## 11. NAMING CONVENTIONS

Controllers : PascalCase + Controller (OrderController)
Models : PascalCase + Model (OrderModel)
View folder : snake_case per controller (order/, payment/)
Helper fn : snake_case (sendNotifEmail, generateKodeOrder)
DB columns : snake_case (id_user, kode_order)
Routes : kebab-case (/verifikasi-pembayaran)
PHP vars : camelCase ($orderId, $sisaKuota)

---

## 12. STATUS PEKERJAAN

### Sudah Selesai ✅

- Auth (login, register, logout, filter, role redirect)
- Layout main.php (sidebar, topbar, notif bell)
- KatalogController (CRUD, upload gambar, toggle aktif, hapus pintar)
- FormTemplateController (tambah/edit/hapus field EAV, cegah duplikat)
- KatalogModel + FormTemplateModel
- Views katalog (index, create, edit, list publik, form_template)
- Folder upload semua sudah ada + index.html proteksi

### Sedang Dikerjakan / Belum Dibuat ❌

- LandingController + Views landing (seluruh section)
- Modal login/register di landing (AJAX)
- Dashboard semua role (5 dashboard)
- OrderController + OrderAttrModel (form pesanan 3 step + EAV)
- PaymentController (upload DP, pelunasan, verifikasi)
- RevisiController (upload draft, approval, kuota)
- PengirimanController (input resi, update status)
- TrackingController (timeline publik)
- LaporanController (filter, grafik, ekspor)
- ProfilController (edit profil, verifikasi perusahaan)
- VerifikasiController (Admin ACC/tolak perusahaan)
- notification_helper.php
- Semua Views untuk modul di atas
