# PENYESUAIAN CLASS DIAGRAM MENGIKUTI KODE AKTUAL

## 📋 PERUBAHAN YANG DIPERLUKAN

Berdasarkan analisis kode aktual, berikut penyesuaian yang perlu dilakukan pada Class Diagram:

---

## 1️⃣ **PERUBAHAN METHOD PAYMENT**

### **Class: Payment**

#### **SEBELUM (Class Diagram Saat Ini):**

```
Payment
-----------
+id_payment: int
+kode_payment: varchar
+id_order: int
+jenis: enum
+nominal: decimal
+bukti_tf: varchar
+status: enum
...
-----------
+create(): bool
+uploadBukti(): bool         ← METHOD INI DIHAPUS
+verifikasi(): bool
+getStatus(): string
+hitungNominalKeuangan(): decimal
```

**Interpretasi:** Method `uploadBukti()` mengindikasikan Payment record sudah ada, lalu bukti di-upload kemudian.

---

#### **SESUDAH (Sesuai Kode Aktual):**

```
Payment
-----------
+id_payment: int
+kode_payment: varchar
+id_order: int
+jenis: enum
+nominal: decimal
+bukti_tf: varchar
+status: enum
...
-----------
+create(idOrder, jenis, nominal, namaFile): bool    ← PARAMETER namaFile DITAMBAHKAN
+verifikasi(idPayment, keputusan): bool
+getStatus(idPayment): string
+hitungNominalKeuangan(totalHarga): decimal
```

**Perubahan:**
- ❌ **HAPUS** method `uploadBukti()` (tidak digunakan di kode aktual)
- ✅ **UPDATE** signature method `create()`:
  - Tambah parameter `namaFile` (bukti file)
  - Payment record dibuat SEKALIGUS dengan bukti file

---

## 2️⃣ **TIDAK ADA PERUBAHAN METHOD LAIN**

Method yang **TETAP SAMA** dan sudah sesuai kode aktual:

### **Class: Order**
- ✅ `create()` - sudah sesuai
- ✅ `updateStatus()` - sudah sesuai
- ✅ `hitungEstimasi()` - sudah sesuai
- ✅ `validateInput()` - sudah sesuai
- ✅ `getDetailPesanan()` - sudah sesuai
- ✅ `updateHargaCustom()` - usulan baru, tetap valid
- ✅ `konfirmasiHargaCustom()` - usulan baru, tetap valid

### **Class: Notification**
- ✅ `kirim()` - sudah sesuai (hasil rename dari `create()`)

### **Class: Pelanggan**
- ✅ `canCreateOrder()` - sudah sesuai
- ✅ `getPaymentScheme()` - sudah sesuai
- ✅ `isPelunasanSebelumKirim()` - usulan baru, tetap valid

### **Class: Katalog**
- ✅ Semua method sudah sesuai

---

## 📝 **RINGKASAN PERUBAHAN CLASS DIAGRAM**

| Class | Method | Perubahan | Alasan |
|---|---|---|---|
| **Payment** | `uploadBukti()` | ❌ **DIHAPUS** | Tidak digunakan di kode aktual |
| **Payment** | `create()` | ✅ **UPDATE signature** | Tambah parameter `namaFile: string` agar bisa create Payment sekaligus dengan bukti |

---

## 🔧 **SIGNATURE METHOD YANG DIPERBAIKI:**

### **SEBELUM:**
```php
// Payment class
+create(idOrder: int, jenis: string, nominal: decimal): bool
+uploadBukti(idPayment: int, namaFile: string): bool
```

### **SESUDAH:**
```php
// Payment class
+create(idOrder: int, jenis: string, nominal: decimal, namaFile: string): bool
// uploadBukti() dihapus
```

---

## 🎨 **CARA UPDATE CLASS DIAGRAM:**

1. **Di Class Payment:**
   - ❌ Hapus baris method `+uploadBukti(idPayment: int, namaFile: string): bool`
   - ✅ Update baris method `+create()` jadi:
     ```
     +create(idOrder: int, jenis: string, nominal: decimal, namaFile: string): bool
     ```

2. **Tidak ada perubahan di class lain**

---

## ⚠️ **CATATAN UNTUK DOKUMENTASI SKRIPSI:**

Dalam pembahasan skripsi, jelaskan bahwa:

> "Method `Payment.create()` dirancang untuk **menerima parameter file bukti transfer** sehingga pembuatan record Payment dan penyimpanan bukti dapat dilakukan dalam **satu transaksi atomic**. Pendekatan ini lebih **efisien** dibanding membuat Payment record terlebih dahulu lalu update bukti kemudian, karena menghindari state tidak konsisten (Payment tanpa bukti)."

Ini adalah **valid design choice** yang bisa diargumentasikan ke dosen:
- ✅ **Atomic operation:** Create + upload dalam 1 transaksi
- ✅ **Data consistency:** Payment record selalu punya bukti sejak dibuat
- ✅ **Simpler API:** 1 method call instead of 2

---

## 🔍 **VERIFIKASI:**

Pastikan di Class Diagram yang sudah diupdate:
- ✅ Method `Payment.create()` punya 4 parameter (termasuk `namaFile`)
- ✅ Method `Payment.uploadBukti()` sudah dihapus
- ✅ Method lain tetap sama
