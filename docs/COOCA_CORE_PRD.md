# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## COOCA CORE — BUSINESS OPERATING SYSTEM
**Document Version:** 2.1  
**Status:** Approved Master Blueprint Specification  
**Product Category:** SaaS Business Operating System (BOS)  
**Target Market:** Seluruh Klaster Industri UMKM & Bisnis Berkembang (F&B/Resto, Bakery, Produksi Makanan, Konveksi/Fashion, Percetakan, Furnitur, Kosmetik, Kerajinan, Retail, Distribusi)  
**Operating Capability:** **Dual Operating Modes** (Dapat berjalan untuk **1 Orang Owner Solo** maupun **Owner dengan Tim Karyawan**)  
**Core UX Philosophy:** **Simple, Mudah, & Otomatis (Automation as Core Experience)**  
**Commercial Model:** Tiered Subscription (Free Plan Rp0 vs. Core Monthly Rp129.000 / Core Annual Rp1.290.000)  

---

## 1. Executive Summary & Vision

### 1.1 Product Definition
**Cooca Core** adalah *Business Operating System (BOS)* berbasis Cloud/SaaS terpadu yang dirancang khusus untuk membebaskan pelaku **Usaha Mikro, Kecil, dan Menengah (UMKM)** dari kerumitan administrasi manual (pencatatan buku kas kertas, spreadsheet terpisah, aplikasi kasir terisolasi, dan ketidaktahuan atas biaya modal sebenarnya).

Cooca Core dirancang fleksibel untuk melayani dua profil operasional UMKM yang sangat berbeda:
1. **Solo-Owner Mode (Operasional Mandiri 1 Orang):** Pengusaha mandiri yang memproduksi, melayani pelanggan, dan mengelola keuangan sendirian tanpa staf. Sistem memberikan alur kerja instan tanpa birokrasi, tanpa approval berbelit, dan serba otomatis.
2. **Team / Delegated Mode (Operasional dengan Karyawan):** Bisnis yang telah berkembang dan memiliki karyawan (kasir, staf gudang, bagian dapur/produksi, admin keuangan). Sistem memberikan pembatasan hak akses (*least privilege*), perlindungan kerahasiaan resep/margin modal dari kasir, pelacakan shift laci kasir, dan persetujuan supervisor.

Cooca Core menghubungkan seluruh rantai operasional bisnis ke dalam satu siklus tertutup (*closed-loop business system*):
```
[Produk & Bahan Baku] 
       ↓
[Kalkulasi HPP & Resep BOM] 
       ↓
[Pembelian / PO & Penerimaan Barang] 
       ↓
[Inventori & Saldo Multi-Gudang] 
       ↓
[Penjualan, POS, Quotation, Sales Order] 
       ↓
[Faktur / Invoice & Pelacakan Piutang] 
       ↓
[Pembayaran & Rekonsiliasi Kas] 
       ↓
[Keuangan, Biaya Operasional & Buku Besar] 
       ↓
[Laba Bersih & Profitabilitas Riil] 
       ↓
[Intelijensi Buatan / AI Insight & Tindakan] 
       ↓
[Keputusan Bisnis yang Terukur & Akurat]
```

### 1.2 Core Positioning & Mantra
- **Tagline:** *"Know your cost. Run your business. Understand your money."*
- **Product Motto:** *"Run your business from one core system."*
- **Product Differentiator:** Cooca Core **bukan ERP korporat yang rumit**, **bukan software akuntansi yang menakutkan dengan istilah teknis**, dan **bukan sekadar software kasir (POS) minim fungsi**. Cooca Core menempatkan **Kalkulasi Biaya Pokok Penjualan (HPP)** berbasis Bill of Materials (BOM) riil sebagai fondasi otomatis yang menggerakkan persediaan, transaksi penjualan, pembukuan jurnal, hingga rekomendasi AI.

---

## 2. Product Principles: Simple, Mudah, & Otomasi Penuh

1. **Owner-First & Solo-Ready:** Pemilik usaha dapat menjalankan seluruh sistem seorang diri dalam hitungan menit tanpa konsultan IT dan tanpa staf akuntansi.
2. **Adaptive Scalability (Solo vs. Team):** 
   - Ketika bisnis hanya memiliki **1 pengguna (Owner)**: Sistem secara otomatis menyembunyikan (*progressive disclosure*) kerumitan approval, meniadakan keharusan PIN supervisor untuk pembatalan transaksi, dan mengaktifkan pintasan *"Beli & Terima Sekaligus"* dalam 1 klik.
   - Ketika Owner **mengundang staf/karyawan**: Sistem secara otomatis mengaktifkan fitur kontrol delegasi (pembatasan akses kasir agar tidak melihat HPP/margin rahasia, shift kasir, audit trail, dan otorisasi void).
3. **Radical Zero-Touch Automation:**
   - **Otomasi HPP:** Update harga bahan baku supplier langsung memperbarui kalkulasi HPP produk terkait secara *real-time*.
   - **Otomasi Inventori:** Stok bahan/produk otomatis berkurang saat kasir POS checkout atau saat faktur penjualan diterbitkan.
   - **Otomasi Akuntansi (Zero Accounting Knowledge):** Pemilik tidak perlu tahu istilah Debit atau Kredit. Setiap faktur, nota pembelian, pembayaran kas, dan penjualan kasir langsung menjurnal buku besar secara otomatis di latar belakang.
   - **Otomasi Intelijensi AI:** Sistem otomatis mendeteksi anomali fraud kasir, memproyeksikan omzet 14 hari ke depan, dan memperingatkan stok yang akan habis tanpa perlu diminta.
4. **Clean, Fast & Mobile-Optimized UX:** Antarmuka responsif, modern, dan dirancang khusus agar nyaman digunakan di layar sentuh kasir (POS Touchscreen), tablet iPad/Android, maupun layar smartphone pemilik usaha saat sedang bepergian.
5. **Connected Source of Truth:** Seluruh modul (HPP, Stok, Kasir POS, Invoice, PO, Keuangan) berbagi satu database yang sama. Tidak ada sinkronisasi manual atau impor-ekspor antarmuka internal.
6. **No Data Punishment:** Jika masa aktif paket langganan habis, data bisnis tidak akan pernah dihapus secara sepihak. Pengguna tetap dapat melihat, mencari, dan mengekspor seluruh data historis miliknya.
7. **Security by Default:** Isolasi tenant multi-bisnis (`business_id`) dan enkripsi data sensitif ditegakkan secara mutlak di sisi server (*server-side boundary*).

---

## 3. Profil Pengguna & Dual Operating Modes

```
                        COOCA OPERATING MODES
                                  │
         ┌────────────────────────┴────────────────────────┐
         ▼                                                 ▼
   MODE 1: SOLO OWNER                               MODE 2: GROWING TEAM
(1 Orang Pemilik Mandiri)                        (Owner + Karyawan/Staf)
────────────────────────────                     ────────────────────────────
• Tanpa birokrasi & approval                     • Pemisahan hak akses (RBAC)
• Direct Action (1-Klik Transaksi)               • Kasir: POS only (HPP tersembunyi)
• Shift kasir opsional                           • Wajib buka/tutup shift kasir
• Instant Receive PO ke Stok                     • Alur PO -> Gudang -> Kasir
• Otomasi penuh tanpa admin                      • Audit log & PIN otorisasi supervisor
```

### 3.1 Mode 1: Solo UMKM Owner (Solo-preneur)
- **Karakteristik:** Bisnis dijalankan sendiri oleh pemilik (misal: *baker rumahan*, *kedai kopi mandiri*, *konveksi rumahan*, *toko online artisan*).
- **Kebutuhan Utama:** Kecepatan, kemudahan, dan ketiadaan birokrasi. Pemilik tidak ingin terbebani alur approval bertingkat atau keharusan memasukkan PIN otorisasi saat ada koreksi di kasir.
- **Perilaku Sistem Cooca Core:**
  - *Instant Purchasing:* Tombol *"Beli & Tambah ke Stok"* langsung menambah inventori tanpa harus membuat PO terpisah lalu membuat formulir penerimaan barang.
  - *Instant Cashier:* Terminal POS dapat langsung dipakai tanpa keharusan input saldo modal awal shift laci kas jika pemilik tidak menginginkannya.
  - *Direct Price Adjustment:* Ubah harga atau diskon kasir langsung tanpa memerlukan approval supervisor.

### 3.2 Mode 2: Growing UMKM (Owner + Tim Karyawan)
- **Karakteristik:** Bisnis telah berkembang dan memiliki 1 hingga puluhan staf (kasir toko, barista, tukang potong konveksi, staf gudang, admin invoice, staf keuangan).
- **Kebutuhan Utama:** Keamanan data, pencegahan kecurangan (*fraud prevention*), pembagian tugas (*separation of duties*), dan rekonsiliasi uang fisik kasir.
- **Perilaku Sistem Cooca Core:**
  - *Role Protection:* Kasir hanya dapat melihat antarmuka kasir POS dan riwayat pesanannya sendiri; kasir **dilarang keras melihat biaya modal HPP riil dan margin keuntungan toko**.
  - *Cash Drawer Reconciliation:* Kasir wajib melakukan buka shift (input modal kas awal) dan tutup shift (penghitungan uang fisik laci kas) saat pergantian giliran kerja.
  - *Supervisor Authorization:* Pembatalan transaksi (*void*), pengembalian dana (*refund*), atau diskon manual di atas batas wajar wajib diverifikasi dengan PIN Pemilik/Supervisor.
  - *Goods Receipt Separation:* Staf gudang menerima barang fisik dan mencocokkannya dengan PO tanpa melihat informasi finansial margin penjualan.

---

## 4. Cakupan Fitur Bisnis Lengkap

### 4.1 Master Data & Mesin HPP (Costing Engine)
- **Multi-Satuan & Konversi Otomatis:** Konversi satuan takaran resep secara cerdas (misal: beli terigu per sak 25 kg, resep menggunakan 250 gram $\rightarrow$ sistem otomatis mengonversi biaya bahan secara akurat).
- **Katalog Bahan Baku & Pemasok:** Pelacakan harga beli terakhir, rendemen bersih (*yield*), dan penyusutan bahan (*waste percentage*).
- **Resep Dinamis (Bill of Materials):** Komposisi bahan baku per takaran saji/produksi, dilengkapi alokasi upah tenaga kerja langsung per jam/batch dan beban operasional overhead dapur/mesin.
- **Kalkulasi HPP Presisi Otomatis:** Menghitung biaya pokok per unit secara otomatis dan merekomendasikan harga jual ideal berbasis target margin laba kotor.
- **20 Template Industri Siap Pakai:** Preset instan untuk Bakery, Kedai Kopi, Resto Padang, Konveksi Kaos, Sablon & Digital Printing, Furnitur Kayu, Frozen Food, Laundry, Kosmetik, Kerajinan, Retail Toko Kelontong, dll.

### 4.2 Siklus Penjualan & Kasir POS (Sales Pipeline)
- **Quotation (Penawaran Harga Formal):** Pembuatan surat penawaran harga dengan nomor unik untuk transaksi B2B/katering dengan status: `DRAFT`, `SENT`, `ACCEPTED`, `REJECTED`.
- **Sales Order (Pesanan Penjualan):** Konfirmasi pesanan yang mengunci ketersediaan stok sebelum dikirim dengan status: `CONFIRMED`, `PARTIALLY_FULFILLED`, `FULFILLED`.
- **Faktur Penjualan (Invoicing):** Penagihan resmi dengan termin jatuh tempo (Net 7, Net 14, Net 30, COD), pajak PPN, diskon, dan QR digital.
- **Terminal Kasir POS Cepat:**
  - Dukungan layar sentuh & scanner barcode/QR super responsif ($< 100\text{ ms}$).
  - Pembayaran multi-tender: Tunai (dengan kalkulator kembalian instan), QRIS, Transfer Bank, Kartu Debit/Kredit, dan Piutang Member.
  - Fitur simpan/tahan pesanan (*Hold & Resume*) untuk antrean ramai.
  - Cetak struk termal 58mm & 80mm otomatis, serta opsi struk digital instan via WhatsApp.

### 4.3 Pengadaan & Manajemen Pemasok (Purchasing)
- **Purchase Order (PO):** Dokumen pesanan pembelian resmi ke pemasok bahan baku.
- **Penerimaan Barang (Goods Receipt):** Pencatatan fisik barang masuk di gudang (mendukung penerimaan penuh maupun bertahap / *partial receiving*) yang otomatis menambah stok riil dan mencatat utang dagang.
- **Mode 1-Klik Solo Owner:** Fitur *"Beli Langsung ke Stok"* bagi pemilik mandiri tanpa perlu membuat alur PO formal.

### 4.4 Inventori & Multi-Gudang (Inventory Management)
- **Stok Real-Time Multi-Lokasi:** Pantau ketersediaan barang di gudang pusat, outlet toko, maupun dapur produksi.
- **Buku Besar Mutasi Stok (*Stock Movement Ledger*):** Rekam jejak keluar-masuk barang yang tidak dapat dimanipulasi (*immutable*) dengan referensi dokumen transaksi asal.
- **Stock Opname Digital:** Penghitungan fisik berkala dengan pemindaian barcode yang otomatis menghitung selisih dan membuat jurnal penyesuaian.
- **Peringatan Stok Menipis (*Low Stock Alert*):** Notifikasi otomatis ketika barang mencapai titik pemesanan ulang (*reorder point*).

### 4.5 Keuangan & Akuntansi Tanpa Ribet (Zero-Touch Finance)
- **Pencatatan Biaya Operasional (Expense):** Catat pengeluaran harian (gaji, listrik, sewa, transport, pemasaran) dengan lampiran foto nota/struk.
- **Buku Piutang & Utang (AR & AP Aging):** Pantau pelanggan yang belum bayar dan tagihan supplier yang akan jatuh tempo untuk menjaga arus kas.
- **Penjurnalan Otomatis (Auto-Journaling):** Seluruh transaksi operasional otomatis mendebit dan mengkredit akun buku besar tanpa perlu input akuntansi manual.
- **Laporan Finansial Real-Time:** Laporan Laba Rugi (*Profit & Loss*), Arus Kas (*Cash Flow*), dan Neraca Keuangan yang selalu ter-update setiap detik.

### 4.6 CRM & Loyalitas Pelanggan
- **Database Pelanggan:** Profil member, riwayat belanja, dan saldo poin loyalitas.
- **Tingkatan Keanggotaan (Membership Tiers):** Bronze, Silver, Gold, Platinum dengan diskon otomatis saat belanja di kasir.
- **Voucher Promo & Diskon:** Pembuatan kode kupon diskon nominal atau persentase dengan kuota pemakaian.

### 4.7 Intelijensi Buatan (*AI POS & Business Assistant*)
- **Peramalan Penjualan 14 Hari (*Sales Forecasting*):** Proyeksi omzet harian menggabungkan regresi tren linear dan pola musiman mingguan (Senin–Minggu).
- **Prediksi Stok Habis (*Runout Days*):** Menghitung sisa hari sebelum stok habis berdasarkan kecepatan laku barang, lengkap dengan rekomendasi jumlah order aman.
- **Deteksi Transaksi Abnormal & Fraud:** Mendeteksi transaksi bernilai aneh, tingkat void kasir di atas batas wajar (>15%), dan peringatan selisih kas fisik laci uang.
- **Matriks Menu Engineering (BCG Matrix):** Pemetaan produk ke dalam 4 kuadran (*Stars, Plowhorses, Puzzles, Dogs*) untuk memaksimalkan profit resep.
- **Rekomendasi Harga Cerdas (*Smart Pricing*):** Saran kenaikan harga jual ideal jika HPP bahan baku mengalami lonjakan.
- **Paket Promo Bundling (*Market Basket Analysis*):** Saran paket hemat untuk produk-produk yang terbukti sering dibeli bersamaan oleh pelanggan.
- **Asisten Natural Language ("Tanya AI"):** Tanya jawab cerdas dalam Bahasa Indonesia tentang kondisi bisnis.
- **AI Action Confirmation (*Human-in-the-Loop*):** Pembuatan draf transaksi otomatis oleh AI dengan kartu pratinjau dan konfirmasi tombol pengguna sebelum dieksekusi ke database.

---

## 5. Paket Langganan, Entitlement & Mode Over-Limit

### 5.1 Matriks Paket Berlangganan

| Dimensi Sumber Daya | **FREE PLAN (Rp0)** | **CORE MONTHLY (Rp129.000/bln)** | **CORE ANNUAL (Rp1.290.000/thn)** |
| :--- | :---: | :---: | :---: |
| **Pengguna Aktif (User)** | 1 Pengguna (Solo Owner) | 1 Pengguna (Bisa tambah via add-on) | 1 Pengguna (Bisa tambah via add-on) |
| **Unit Bisnis (Business)** | 1 Bisnis | **Unlimited Bisnis** | **Unlimited Bisnis** |
| **Kapasitas Penyimpanan** | 5 GB | 5 GB | 5 GB |
| **Katalog Produk** | Maksimal 50 Produk | **Unlimited Produk** | **Unlimited Produk** |
| **Resep / BOM** | Maksimal 20 Resep | **Unlimited Resep** | **Unlimited Resep** |
| **Database Pelanggan** | Maksimal 50 Kontak | **Unlimited Kontak** | **Unlimited Kontak** |
| **Database Pemasok** | Maksimal 50 Vendor | **Unlimited Vendor** | **Unlimited Vendor** |
| **Gudang / Lokasi** | 1 Lokasi Toko/Gudang | **Multi-Gudang Unlimited** | **Multi-Gudang Unlimited** |
| **Batas Kuota Transaksi** | 10 Quotation / bln<br>10 PO / bln<br>10 Invoice / bln<br>30 Beban / bln | **Transaksi Unlimited** | **Transaksi Unlimited** |
| **Terminal Kasir POS** | Termasuk (1 Kasir) | **Multi-Kasir Unlimited** | **Multi-Kasir Unlimited** |
| **Integrasi WhatsApp** | Tidak Tersedia | 1 Nomor WA Terhubung | 1 Nomor WA Terhubung |
| **AI Token Allowance** | Tidak Tersedia | **10 Juta Token / bulan** | **10 Juta Token / bulan** *(Reset tiap siklus bulanan)* |
| **Template HPP Siap Pakai**| 3 Template Dasar | **20+ Template Industri Lengkap** | **20+ Template Industri Lengkap** |

### 5.2 Kebijakan Over-Limit (*No Data Punishment*)
Ketika masa aktif langganan CORE berakhir dan bisnis kembali ke paket FREE:
- **Keamanan Data 100%:** Tidak ada data produk, resep, atau riwayat transaksi yang dihapus.
- **Mode Read-Only Protektif:** Jika bisnis memiliki 150 produk (melebihi batas 50 Free), seluruh 150 produk tetap dapat dilihat, dicari, dijual di kasir, dan diekspor. Pembuatan produk baru ke-151 diblokir sementara dengan ajakan upgrade yang ramah.
- **Suspensi Akun Karyawan:** Akun Owner tetap aktif secara penuh, sementara akun staf tambahan dialihkan ke status nonaktif sementara (`SUSPENDED_BY_PLAN`) hingga paket diperbarui.

---

## 6. Standar Desain Antarmuka (UX) & Otomasi

1. **Onboarding Cepat 3 Menit:**
   - Daftar akun $\rightarrow$ Masukkan nama bisnis $\rightarrow$ Pilih 1 dari 20 template industri $\rightarrow$ Sistem langsung menyiapkan satuan, bahan baku contoh, resep dasar, dan bagan akun $\rightarrow$ Siap transaksi.
2. **Prinsip "Maksimal 3 Klik":**
   - Transaksi kasir POS dapat diselesaikan dalam 2-3 sentuhan layar.
   - Pembuatan faktur penjualan memiliki opsi pengisian otomatis dari riwayat HPP produk.
3. **Pemberitahuan Proaktif (*Proactive Alerts*):**
   - Kartu peringatan visual saat stok bahan di bawah batas aman.
   - Peringatan selisih kas fisik laci kasir saat penutupan shift.
4. **Pencegahan Kesalahan yang Menenangkan (*Forgiving UI*):**
   - Tombol konfirmasi pembatalan atau hapus data selalu dilengkapi dialog konfirmasi yang jelas.
   - Fitur pratinjau (*preview*) sebelum struk atau PDF faktur dicetak.

---

## 7. Indikator Keberhasilan (Success Metrics & KPI)

1. **North Star Metric:** *Active Businesses Processing Real Transactions per Month (ABPT)* — Jumlah UMKM aktif yang memproses $\ge 20$ transaksi riil per bulan.
2. **Waktu Transaksi Kasir (POS Speed):** Rata-rata waktu pelayanan kasir per pelanggan $< 20$ detik.
3. **Aktivasi Pengguna Mandiri (Solo Activation):** Lebih dari 40% pendaftar baru berhasil menyelesaikan 1 transaksi penjualan dan menghitung 1 HPP dalam 24 jam pertama.
4. **Tingkat Retensi Bulanan (Net Revenue Retention):** NRR $> 105\%$ melalui kombinasi langganan berulang dan pembelian add-on token AI / staf tambahan.
5. **Kualitas Sistem Finansial:** Nol insiden ketidakseimbangan jurnal (*zero unbalanced ledger events*) dan uptime server $\ge 99.9\%$.
