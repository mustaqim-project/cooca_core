# Ringkasan Blueprint Rekayasa Pengembang (Engineering Blueprint Cooca)

Dokumen ini adalah intisari dari **Bab 4 `docs/SYSTEM_GUIDE.md`** yang memuat spesifikasi arsitektur teknis kelas enterprise bagi Software Engineer, Architect, dan AI Agent.

---

## 1. Arsitektur 33 Domain Packages DDD (`app/Domain/`)
Sistem mengadopsi prinsip Domain-Driven Design (DDD) yang terpecah ke dalam 33 paket domain mandiri:
- `Accounting`: Bagan akun (COA), pembukuan berpasangan, laporan keuangan SAK EMKM.
- `Calculation`: Mesin kalkulator moneter, rounding, dan presisi desimal.
- `Costing`: Mesin kalkulasi HPP bahan baku, jam kerja buruh, depresiasi mesin, dan waste tolerance.
- `Inventory`: Stok gudang, konversi satuan, mutasi stok, dan auto-deduction BOM.
- `Pos`: Terminal kasir, manajemen shift kasir, split bill, channel pricing F&B, dan driver thermal ESC/POS.
- `Finance`: Kas & bank, rekonsiliasi, mutasi kas kecil, piutang (AR), dan hutang (AP).
- `Commerce`: Storefront publik, keranjang belanja, checkout, dan batch scheduling pre-order.
- `Customer` & `Crm`: Manajemen member loyalitas, kupon voucher, dan reward poin belanja.
- `Template`: Manajemen 20 template industri dan module feature gating (`ModuleRegistry.php`).
- `Storage`: Tenant storage isolation, symlink resolution, dan storage quota tracking.
- `WhatsApp`: WhatsApp Meta Cloud API gateway, webhook router, dan template notification dispatcher.
- `SocialMedia`: Integrasi UGC API Meta (IG/FB), TikTok, dan LinkedIn.
- `Billing`: Manajemen paket langganan SaaS, webhook TriPay gateway, dan auto-gating kuota.

---

## 2. Aturan Scoping Tenant & Proteksi Keamanan
- **Isolasi Mutlak**: Setiap query wajib menyertakan `Context::requireBusiness()` dan foreign key `business_id`.
- **Anti-IDOR Shield**: Akses pesanan storefront customer mewajibkan autentikasi global customer + verifikasi nomor WhatsApp OTP.
- **Zero Plaintext Credential Exposure**: Kredensial rahasia (API key, token, PIN supervisor, password) di-masking di UI dan di-exclude via properti `$hidden` pada Model Eloquent.

---

## 3. Mesin Otomasi Latar Belakang (Background Engines)
- **`AutoJournalService`**: Menjurnal transaksi kasir POS secara otomatis ke akun Kas (Debit) dan Penjualan (Kredit), serta HPP (Debit) dan Persediaan (Kredit) tanpa intervensi akuntan manual.
- **`StockService`**: Memotong stok bahan baku resep BOM dan komponen kombo secara atomik dan rekursif saat checkout POS berhasil.
- **Asynchronous Tri-Channel Notifications**: Notifikasi In-App, Email HTML, dan WhatsApp Meta API dikirim via Laravel Queue (`ShouldQueue`) tanpa memblokir respon kasir (latensi sub-100ms).

---

## 4. Hardware POS & Driver ESC/POS Thermal Printer
- Driver printer termal 58mm & 80mm mendukung printer jaringan (Raw Socket 9100), Bluetooth ESC/POS via browser Web Bluetooth API, dan Local Agent Bridge.
- Perintah pembukaan laci kas (`ESC p 0 25 250`) diproteksi audit log immutable dan rate limiting untuk mencegah kecurangan kas kecil.

---

## 5. Kepatuhan Kuota SaaS (No Data Punishment)
- Jika tenant beralih ke paket langganan yang lebih kecil (*downgrade*), sistem **DILARANG MENGHAPUS DATA**.
- Produk atau entitas yang melebihi kuota paket hanya di-suspend sementara dari penjualan POS dan Toko Online, serta otomatis ter-unlock (*Auto-Reactivation*) saat paket diperpanjang kembali.

---

## 6. Protokol Kesiapan Produksi (100% Zero-Error Mandate)
- Pekerjaan rekayasa dilarang dinyatakan selesai sebelum seluruh pengujian otomatis dieksekusi:
  ```bash
  php -l [file]
  php artisan route:list
  php artisan test
  npm run build
  ```
- Seluruh kode debug (`dd()`, `dump()`, `console.log()`) wajib dibersihkan tuntas.
