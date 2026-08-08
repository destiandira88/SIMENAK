# SEQUENCE DIAGRAM PEMESANAN - DIAGRAM UTAMA
**Use Case:** Pemesanan (Submit Pesanan + Custom Price + DP Payment)

**Actors:** Pelanggan, Admin, Keuangan

**Boundary Objects:**
- Halaman Pemesanan
- Halaman Detail Pesanan

**Control Objects:**
- Kontrol Pemesanan

**Entity Objects:**
- Pelanggan «entity»
- Katalog
- Order
- Payment
- Notification

---

## DIAGRAM UTAMA — Detail Messages

| No | From | To | Label Pesan | Keterangan |
|---|---|---|---|---|
| 1 | Pelanggan | Halaman Pemesanan | aksesHalaman(idKatalog) | Buka form pemesanan untuk katalog tertentu |
| 1r | Halaman Pemesanan | Pelanggan | tampilkanFormPemesanan | (return) form tampil di layar |
| 2 | Pelanggan | Halaman Pemesanan | submitPesanan(dataPesanan) | Submit form dengan data lengkap (jumlah, detail, deadline, pengiriman, referensi) |
| 3 | Halaman Pemesanan | Kontrol Pemesanan | prosesPemesanan(dataPesanan) | Teruskan ke lapisan proses |
| 4 | Kontrol Pemesanan | Katalog | getById(idKatalog) | Ambil data katalog untuk validasi min_order dan hitung estimasi |
| 4r | Katalog | Kontrol Pemesanan | dataKatalog | (return) detail katalog {nama_produk, min_order, satuan, harga_satuan, estimasi_hari} |
| 5 | Kontrol Pemesanan | Pelanggan «entity» | canCreateOrder() | Cek kelayakan pelanggan untuk memesan |
| 5r | Pelanggan «entity» | Kontrol Pemesanan | statusKelayakan | (return) boolean (true = boleh pesan, false = tidak boleh) |
| 6a | - | - | [statusKelayakan = false] | Percabangan 1 |
| 7a | Kontrol Pemesanan | Halaman Pemesanan | infoGagal(alasan) | Kirim alasan penolakan (akun belum aktif, dll) |
| 8a | Halaman Pemesanan | Pelanggan | tampilkanPesanGagal | (return) pesan error tampil di form |
| 6b | - | - | [statusKelayakan = true] | Percabangan 1 alternatif — lanjut pemesanan |
| 7b | Kontrol Pemesanan | Pelanggan «entity» | getPaymentScheme(totalHarga) | Tentukan skema pembayaran berdasarkan akun + total harga |
| 8b | Pelanggan «entity» | Kontrol Pemesanan | schemeData | (return) {requireDp, jenisPelanggan, statusAwal} |
| **ref** | **Kontrol Pemesanan** | - | **ref: DETAIL-1 Percabangan Jenis Pesanan** | Jika is_custom=1 → konfirmasi harga Admin, jika standard → langsung simpan |
| **ref** | **Kontrol Pemesanan** | - | **ref: DETAIL-2 Percabangan Pembayaran DP** | Jika requireDp=1 → upload + verifikasi DP, jika requireDp=0 → langsung terverifikasi |
| 9 | Kontrol Pemesanan | Halaman Detail Pesanan | infoSukses(kodeOrder, statusAkhir) | Kirim info pesanan berhasil disimpan + status akhir |
| 10 | Halaman Detail Pesanan | Pelanggan | redirectDetailPesanan | (return) pindah ke halaman detail pesanan dengan status akhir sesuai jalur yang diambil |

---

## CATATAN AKADEMIS

1. **Scope Diagram Utama:** Diagram ini menunjukkan alur keseluruhan dari submit pesanan sampai pesanan masuk antrian (status terverifikasi) atau menunggu verifikasi DP. Detail percabangan untuk Custom Order dan DP Payment dipecah ke diagram detail terpisah untuk readability.

2. **ref Fragment:** Menggunakan UML ref fragment untuk merujuk ke 2 diagram detail:
   - DETAIL-1: Menangani percabangan standard vs custom order
   - DETAIL-2: Menangani percabangan pembayaran DP (upload + verifikasi)

3. **Boundary Objects:** Setelah submit pesanan, flow bisa berakhir di 3 halaman berbeda tergantung jalur:
   - Halaman Detail Pesanan (standard tanpa DP) → status terverifikasi
   - Halaman Detail Pesanan (standard/custom dengan DP) → status menunggu_verifikasi_dp
   - Halaman Detail Pesanan (custom menunggu konfirmasi harga) → status menunggu_konfirmasi_pelanggan

   Untuk akademis, kita generalisasi sebagai "Halaman Detail Pesanan" saja di message terakhir.

---

**NEXT:** Lihat `sequence_diagram_pemesanan_DETAIL1.md` dan `sequence_diagram_pemesanan_DETAIL2.md` untuk detail penuh kedua percabangan.
