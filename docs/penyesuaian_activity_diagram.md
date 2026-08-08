# PENYESUAIAN ACTIVITY DIAGRAM MENGIKUTI KODE AKTUAL

## 📋 PERUBAHAN YANG DIPERLUKAN

Berdasarkan analisis kode aktual, berikut penyesuaian yang perlu dilakukan pada Activity Diagram Pemesanan (To-Be):

---

### 🔄 **PERUBAHAN 1: Alur Pembayaran DP**

#### **SEBELUM (Activity Diagram Saat Ini):**

```
[Perseorangan / Kerja Sama order > Rp5jt]
↓
Melakukan pembayaran DP melalui transfer
↓
Mengunggah bukti pembayaran
↓
Menekan tombol "Kirim Bukti Pembayaran"
↓
[Sistem] Status: "Menunggu Verifikasi DP"
          Simpan bukti DP & Notif Email
```

**Interpretasi:** Sistem menyimpan bukti DP (artinya Payment record sudah ada sebelumnya)

---

#### **SESUDAH (Sesuai Kode Aktual):**

```
[Perseorangan / Kerja Sama order > Rp5jt]
↓
[Sistem] Status: "Menunggu Verifikasi DP"
         Tampilkan form upload DP + batas waktu 24 jam
↓
Melakukan pembayaran DP melalui transfer
↓
Mengunggah bukti pembayaran
↓
Menekan tombol "Kirim Bukti Pembayaran"
↓
[Sistem] Buat record Payment + Simpan bukti DP
         Notif Email ke Keuangan
```

**Interpretasi:** Payment record dibuat SAAT pelanggan upload bukti (bukan sebelumnya)

---

### 📝 **DETAIL PERUBAHAN:**

| Elemen | Sebelum | Sesudah |
|---|---|---|
| **Timing Payment Record** | Dibuat saat order disimpan | Dibuat saat pelanggan upload bukti |
| **Status Order** | Set SETELAH Payment dibuat | Set SAAT order disimpan |
| **Activity setelah Submit** | Sistem simpan Order → Sistem buat Payment → Tampil halaman detail | Sistem simpan Order dengan status 'menunggu_verifikasi_dp' → Tampil halaman detail dengan form upload |

---

### 🎨 **CARA UPDATE ACTIVITY DIAGRAM:**

1. **Hapus activity "Sistem buat Payment record"** dari jalur setelah simpan order
2. **Pindahkan "Sistem buat Payment record"** ke dalam activity "Mengunggah bukti pembayaran"
3. **Update label activity** dari:
   - ❌ "Simpan bukti DP & Notif Email"
   - ✅ "Buat Payment + Simpan bukti DP + Notif Email"

---

## ⚠️ **CATATAN UNTUK DOKUMENTASI SKRIPSI:**

Dalam pembahasan skripsi, jelaskan bahwa:

> "Payment record dibuat **on-demand** saat pelanggan upload bukti pertama kali, bukan saat order dibuat. Pendekatan ini dipilih untuk **menghindari record kosong** di database dan **memastikan setiap Payment record selalu memiliki bukti transfer** sejak dibuat."

Ini adalah **valid design choice** yang bisa diargumentasikan ke dosen:
- ✅ Database lebih bersih (tidak ada Payment record tanpa bukti)
- ✅ Lebih simpel (1 transaksi untuk create Payment + upload bukti)
- ✅ Tetap memenuhi business logic (payment tracking tetap akurat)

---

## 🔍 **VERIFIKASI:**

Pastikan di Activity Diagram yang sudah diupdate:
- ✅ Tidak ada activity "Buat Payment record" sebelum pelanggan upload bukti
- ✅ Ada activity "Buat Payment + Simpan bukti" saat pelanggan upload
- ✅ Status order "Menunggu Verifikasi DP" sudah diset SEBELUM pelanggan upload (saat order dibuat)
