# Automation Black-Box QA — SIMENAK

## Cara menjalankan

Pastikan Apache + MySQL XAMPP aktif, lalu:

```bash
php spark qa:blackbox --baseURL=http://localhost/SIMENAK/public
```

Laporan Markdown tersimpan di:

```
writable/qa/report_YYYYMMDDHHmmss.md
```

## Apa yang diuji

Suite HTTP black-box (`app/Commands/QaBlackbox.php`) meniru pengguna sungguhan per role:

1. Pelanggan — registrasi/validasi, login, order standar & custom, skema DP (perseorangan vs kerja sama), auto-cancel DP, upload bukti, kuota revisi, tracking
2. Admin — katalog + `kode_katalog`, konfirmasi harga custom, tetapkan/cabut kerja sama, resi + `kode_kirim`, (staf hanya Owner)
3. Keuangan — ACC/tolak DP & pelunasan, validasi catatan wajib
4. Produksi — upload draft (tipe/ukuran 1MB), `kode_revisi`
5. Owner — laporan + riwayat aktivitas
6. Lintas role — akses URL role lain
7. Kodifikasi — NULL/duplikat `kode_*`
8. Lupa password staf — entry UI portal

## Komponen

| File | Fungsi |
|---|---|
| `app/Commands/QaBlackbox.php` | Orchestrator skenario |
| `app/Libraries/Qa/BlackBoxHttpClient.php` | HTTP client + CSRF + cookie |
| `app/Libraries/Qa/BlackBoxReport.php` | Formatter laporan |
| `writable/qa/fixtures/` | File uji upload |

Akun uji memakai email `qa.*@simenak.test` (dibuat otomatis tiap run).
