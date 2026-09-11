# Analisa Komprehensif Modul & Fitur Sistem COOCA Core ERP

Dokumen ini merupakan laporan audit, inventarisasi modul, pemetaan fitur, serta analisis kemampuan teknis dan fungsional dari seluruh sistem **COOCA Core ERP & POS Ecosystem**.

---

## 📑 Daftar Isi

1. [Peta Arsitektur Ekosistem Sistem](#1-peta-arsitektur-ekosistem-sistem)
2. [Modul Kalkulasi Biaya & Costing Engine](#2-modul-kalkulasi-biaya--costing-engine)
3. [Modul Master Data, Bahan Baku & Resep Produk](#3-modul-master-data-bahan-baku--resep-produk)
4. [Modul Point of Sale (POS) & Terminal Kasir](#4-modul-point-of-sale-pos--terminal-kasir)
5. [Modul Gudang & Inventori Multi-Lokasi](#5-modul-gudang--inventori-multi-lokasi)
6. [Modul Pengadaan & Pembelian (Purchasing & AP)](#6-modul-pengadaan--pembelian-purchasing--ap)
7. [Modul Penjualan Komersial & B2B (Sales & Invoicing)](#7-modul-penjualan-komersial--b2b-sales--invoicing)
8. [Modul Keuangan & Akuntansi (Finance & Double-Entry Accounting)](#8-modul-keuangan--akuntansi-finance--double-entry-accounting)
9. [Modul CRM, Loyalitas Pelanggan & Promosi](#9-modul-crm-loyalitas-pelanggan--promosi)
10. [Modul WhatsApp Gateway & Marketing Engine](#10-modul-whatsapp-gateway--marketing-engine)
11. [Modul AI Assistant & Analitik Prediktif](#11-modul-ai-assistant--analitik-prediktif)
12. [Modul CMS Website & Landing Page Bisnis](#12-modul-cms-website--landing-page-bisnis)
13. [Modul Ekosistem Publik, SEO & Lead Generation](#13-modul-ekosistem-publik-seo--lead-generation)
14. [Modul Multi-Tenant, Keanggotaan & RBAC](#14-modul-multi-tenant-keanggotaan--rbac)
15. [Modul SaaS Billing, Langganan & Batas Kuota](#15-modul-saas-billing-langganan--batas-kuota)
16. [Modul Super Admin Control Center](#16-modul-super-admin-control-center)
17. [Modul Aplikasi Mobile POS (Flutter)](#17-modul-aplikasi-mobile-pos-flutter)
18. [Matriks Ringkasan Modul & Hak Akses](#18-matriks-ringkasan-modul--hak-akses)
19. [Kesimpulan & Keunggulan Kompetitif](#19-kesimpulan--keunggulan-kompetitif)

---

## 1. Peta Arsitektur Ekosistem Sistem

```
+---------------------------------------------------------------------------------------------------+
|                                  COOCA CORE ENTERPRISE ECOSYSTEM                                  |
+---------------------------------------------------------------------------------------------------+
| [FRONTEND WEB & CLIENTS]                                                                          |
|   ├── Enterprise Web Panel (Blade + Tailwind CSS + Alpine.js + Apple HIG v2.0)                    |
|   ├── Native Mobile POS App (Flutter / Dart - Android & iOS Clean Architecture)                   |
|   ├── Customer Self-Service QR Ordering System (Mobile Web Touch-Optimized)                       |
|   └── Public Web, SEO Growth Engine & Lead Magnets (/kalkulator, /blog, /template, /b/{slug})     |
+---------------------------------------------------------------------------------------------------+
| [APPLICATION & DOMAIN CORE] (Laravel 11 / PHP 8.3+)                                               |
|   ├── 33 Domain Packages (DDD Structure: Costing, Inventory, Accounting, Sales, POS, CRM, etc.)  |
|   ├── Automated Double-Entry Journal Engine (AutoJournalService)                                  |
|   ├── Real-Time Multi-Channel Cash Ledger & Running Balance System                                |
|   ├── Dynamic HPP & Bill of Materials (BOM) Mathematical Engine                                   |
|   └── Multi-Tenant Engine (Business Isolation, Resource Quotas & Feature Entitlements)           |
+---------------------------------------------------------------------------------------------------+
| [MICROSERVICES & EXTERNAL INTEGRATIONS]                                                           |
|   ├── Node.js WhatsApp Gateway (Baileys / Puppeteer headless microservice di wa-server/)          |
|   ├── AI Assistant Engine (Gemini / OpenAI API untuk Prediksi Penjualan & Chat Bisnis)            |
|   ├── Payment & Bank Settlement Trackers (Tunai, QRIS, Virtual Account, EDC, Kasbon)             |
|   └── Thermal Receipt Engine (ESC/POS 58mm & 80mm Direct USB/Bluetooth/Network & Raster Canvas)   |
+---------------------------------------------------------------------------------------------------+
```

---

## 2. Modul Kalkulasi Biaya & Costing Engine

### 2.1 Mesin Kalkulator HPP Interaktif (Interactive HPP Calculator)
* **Tujuan Utama:** Menghitung Harga Pokok Penjualan (HPP) per unit produk secara presisi, dinamis, dan ilmiah untuk berbagai jenis industri (FnB, Manufaktur, Ritel, Jasa, Konveksi).
* **Kemampuan Fitur:**
  - **Live Multi-Tier Costing Breakdown:** Memisahkan biaya ke dalam 4 komponen dasar: *Biaya Bahan Baku (Raw Materials)*, *Tenaga Kerja Langsung (Direct Labor)*, *Biaya Mesin/Depresiasi (Machine Overhead)*, dan *Overhead Pabrik/Toko (General Overhead)*.
  - **Simulasi Margin & Markup:** Menghitung rekomendasi harga jual berdasarkan target margin kotor (Gross Margin %) atau target keuntungan nominal secara instan tanpa reload halaman (*reactive calculation*).
  - **One-Click Apply to Product:** Menerapkan hasil kalkulasi HPP langsung menjadi HPP standar dan harga jual acuan pada katalog master produk.
  - **Quick Create Product:** Membuat master produk baru langsung dari draf perhitungan kalkulator.
  - **Ekspor Excel:** Mengunduh lembar kerja kalkulasi HPP ke file `.xlsx` lengkap dengan rincian rumusnya.

### 2.2 Tenaga Kerja & Biaya Mesin (Labor & Machine Costing)
* **Tujuan Utama:** Menstandarisasi tarif upah kerja dan biaya operasional mesin per menit/jam ke dalam komponen HPP produk.
* **Kemampuan Fitur:**
  - **Labor Rate Matrix:** Pengaturan tarif tenaga kerja per jam, harian, atau borongan per unit output.
  - **Machine Depreciation & Operating Cost:** Kalkulasi otomatis biaya listrik (kWh), bahan bakar, penyusutan alat, dan servis berkala per durasi produksi.
  - **Time-Study Allocation:** Memasukkan durasi menit pengerjaan ke dalam resep BOM produk untuk membebankan biaya mesin dan tenaga kerja secara proporsional.

### 2.3 Simulator Skenario "What-If" (Scenario Simulator)
* **Tujuan Utama:** Uji ketahanan margin bisnis terhadap inflasi dan fluktuasi harga pasar.
* **Kemampuan Fitur:**
  - **Simulasi Kenaikan Bahan Baku:** Menganalisis dampak jika harga bahan baku naik $X\%$ terhadap laba kotor.
  - **Simulasi Efisiensi Tenaga Kerja/Mesin:** Mengukur dampak peningkatan kecepatan produksi terhadap penurunan HPP.
  - **Sensitivitas Harga Jual:** Mengukur toleransi diskon maksimum sebelum bisnis mengalami kerugian kotor (*negative margin alert*).

### 2.4 Analisis BEP & Profitabilitas (BEP & Profitability Analyzer)
* **Tujuan Utama:** Menentukan titik impas usaha (*Break-Even Point*).
* **Kemampuan Fitur:**
  - **BEP Unit & BEP Rupiah:** Menghitung berapa unit atau berapa omzet yang harus dicapai per bulan untuk menutup seluruh biaya tetap (*Fixed Costs*) dan biaya variabel (*Variable Costs*).
  - **Target Profit Planning:** Menghitung volume penjualan minimal yang diperlukan untuk mencapai target laba bersih tertentu.
  - **Margin of Safety (MoS):** Indikator batas toleransi penurunan penjualan sebelum usaha mulai menderita rugi.

---

## 3. Modul Master Data, Bahan Baku & Resep Produk

### 3.1 Manajemen Bahan Baku (Materials & Pricing)
* **Tujuan Utama:** Mengelola database material mentah, bumbu, kain, kemasan, atau suku cadang.
* **Kemampuan Fitur:**
  - **Multi-Satuan & Konversi Dinamis:** Mendukung satuan beli (misal: *Karung 25 Kg*) dan satuan pakai resep (misal: *gram* atau *ml*) dengan rasio konversi otomatis.
  - **Histori Perubahan Harga Beli:** Mencatat jejak perubahan harga beli dari waktu ke waktu untuk memantau tren inflasi supplier.
  - **Stok Minimum & Reorder Point (ROP):** Peringatan otomatis saat stok bahan baku mendekati batas kritis pengadaan ulang.
  - **Kategorisasi Material:** Pengelompokan material berdasarkan jenis (Bahan Pokok, Bumbu, Packaging, Utilitas).

### 3.2 Katalog Produk & Bill of Materials (BOM / Recipe Engine)
* **Tujuan Utama:** Mengelola produk jadi/jasa dan formula komposisi pembuatannya.
* **Kemampuan Fitur:**
  - **Multi-Level BOM:** Menyusun resep produk dari berbagai kombinasi bahan baku, biaya tenaga kerja, dan mesin.
  - **Auto-Deduction pada POS:** Ketika produk terjual di kasir, sistem secara otomatis memotong stok bahan baku penyusunnya sesuai resep BOM (bukan hanya memotong stok produk jadi).
  - **Yield & Wastage Factor:** Memperhitungkan persentase penyusutan atau bahan terbuang (*waste %*) selama proses pengolahan.
  - **Dukungan Varian Produk & Modifiers:** Mendukung varian rasa, ukuran, level gula/pedas dengan penyesuaian biaya tambahan.

### 3.3 Mesin Impor Massal (Mass Import Engine)
* **Tujuan Utama:** Onboarding data bisnis secara cepat melalui file spreadsheet.
* **Kemampuan Fitur:**
  - **Dukungan Format Excel (.xlsx) & CSV:** Tersedia template resmi yang dapat diunduh langsung.
  - **Preview & Validasi Sebelum Eksekusi:** Menampilkan tabel pratinjau data dengan deteksi error (duplikasi SKU, satuan tidak valid, kolom kosong) sebelum data dimasukkan ke database aktif.
  - **4 Jalur Impor:** Impor Bahan Baku, Impor Produk Jadi, Impor Resep/BOM, dan Impor Saldo Awal Stok Gudang.

---

## 4. Modul Point of Sale (POS) & Terminal Kasir

### 4.1 Terminal Kasir Modern (POS Terminal)
* **Tujuan Utama:** Transaksi penjualan ritel dan resto dengan responsivitas tinggi (<100ms) dan UI berbasis Apple Human Interface Guidelines.
* **Kemampuan Fitur:**
  - **Katalog Grid & Barcode Scanner:** Pencarian instan berdasarkan nama produk, SKU, kategori, atau tembakan barcode scanner USB/Bluetooth.
  - **Parkir Pesanan (Hold & Resume Order):** Menahan transaksi sementara (misal: pelanggan menambah pesanan atau berpindah meja) dan melanjutkannya kapan saja.
  - **Multi-Payment Support:** Tunai (*Cash with Change Calculator*), QRIS Statis/Dinamis, Mesin EDC Debit/Kredit, Transfer Bank, dan Kasbon Pelanggan (*Customer Store Credit*).
  - **Split Payment (Pembayaran Campuran):** Pelanggan dapat membayar dengan kombinasi 2 atau lebih metode (contoh: 50% Tunai + 50% QRIS) dalam satu faktur transaksi.
  - **Otorisasi Supervisor via PIN:** Tindakan berisiko tinggi seperti *Void* struk, *Refund*, atau pemberian diskon manual wajib memasukkan PIN supervisor yang tervalidasi secara aman.

### 4.2 Manajemen Shift & Laci Kasir (Cash Drawer & Shifts)
* **Tujuan Utama:** Menjaga akuntabilitas fisik uang kasir antara pergantian jam kerja karyawan.
* **Kemampuan Fitur:**
  - **Buka Shift (Opening Cash):** Input modal awal laci kasir sebelum transaksi dimulai.
  - **Pencatatan Kas Masuk/Keluar Kasir (Cash Movement):** Mencatat pengeluaran darurat kasir (misal: beli es batu) langsung dari terminal.
  - **Tutup Shift & Rekonsiliasi Otomatis (Blind Close):** Kasir menginput uang fisik yang dihitung di laci; sistem membandingkannya dengan catatan sistem dan menampilkan status: *Cocok*, *Kelebihan Kas*, atau *Selisih Kurang*.

### 4.3 Sistem Pesan Mandiri Meja via QR (QR Table Ordering)
* **Tujuan Utama:** Memungkinkan tamu restoran memesan langsung dari ponsel mereka tanpa antre di kasir.
* **Kemampuan Fitur:**
  - **QR Token Dinamis per Meja:** Meja memiliki QR unik yang dapat diunduh dalam format kartu cetak atau file vektor SVG.
  - **Katalog Menu Digital Responsif:** Menu mobile-friendly lengkap dengan foto produk, deskripsi, pilihan modifier, dan catatan koki.
  - **Incoming Order Queue di Kasir:** Pesanan dari meja masuk ke kasir dengan notifikasi suara dan opsi: *Terima (Accept)* atau *Tolak (Reject)*.
  - **Pelacakan Status Pesanan Realtime:** Pelanggan dapat memantau status pesanan (*Diterima*, *Sedang Dimasak*, *Siap Disajikan*).

### 4.4 Kitchen Display System (KDS / Dapur & Bar)
* **Tujuan Utama:** Menggantikan kertas cetak dapur dengan layar monitor antrean pesanan di area dapur/bar.
* **Kemampuan Fitur:**
  - **Auto-Refresh Tanpa Reload:** Pesanan baru otomatis muncul di layar koki secara realtime.
  - **Pemisahan Jalur Dapur vs Bar:** Memisahkan item makanan ke layar Kitchen dan item minuman ke layar Bar.
  - **Pembaruan Status 1-Klik:** Mengubah status pengerjaan dari *Menunggu* $\rightarrow$ *Dimasak* $\rightarrow$ *Selesai*.

---

## 5. Modul Gudang & Inventori Multi-Lokasi

### 5.1 Manajemen Multi-Gudang (Warehouse Hub)
* **Tujuan Utama:** Memantau persediaan stok di lebih dari satu lokasi fisik (misal: *Gudang Pusat*, *Toko Cabang A*, *Dapur Utama*).
* **Kemampuan Fitur:**
  - Pembuatan dan pengelolaan banyak lokasi gudang tanpa batas.
  - Penunjukan gudang default untuk transaksi POS dan pengadaan.

### 5.2 Pergerakan Stok & Kartu Stok (Stock Movements & Ledger)
* **Tujuan Utama:** Menjaga transparansi setiap unit barang yang bertambah atau berkurang.
* **Kemampuan Fitur:**
  - **Kartu Stok Digital:** Riwayat lengkap arus barang mencakup: Penjualan POS, Penerimaan Pembelian, Penyesuaian Manual (*Adjustment*), dan Pemakaian Resep.
  - **Pelacakan Saldo Berjalan (Running Balance):** Mengetahui sisa stok di setiap detik transaksi.

### 5.3 Stock Opname & Rekonsiliasi Fisik
* **Tujuan Utama:** Pemeriksaan fisik persediaan secara berkala untuk mencocokkan stok nyata dengan pembukuan.
* **Kemampuan Fitur:**
  - Pembuatan sesi opname per kategori atau seluruh gudang.
  - **Auto Reconcile:** Sistem otomatis menghitung selisih (*variance*) dan membuat penyesuaian stok otomatis disertai pencatatan beban selisih stok ke akuntansi.

### 5.4 Mutasi Antar Gudang (Stock Transfers)
* **Tujuan Utama:** Pengiriman barang atau bahan baku dari satu cabang/gudang ke cabang lain.
* **Kemampuan Fitur:**
  - Alur dua arah: *Pengiriman (Sent)* $\rightarrow$ *Penerimaan (Received)*.
  - Mengurangi stok di gudang asal dan menambah stok di gudang tujuan saat penerimaan dikonfirmasi.

---

## 6. Modul Pengadaan & Pembelian (Purchasing & AP)

### 6.1 Surat Pesanan Pembelian (Purchase Orders / PO)
* **Tujuan Utama:** Pengadaan stok formal ke vendor dan pemasok.
* **Kemampuan Fitur:**
  - Pembuatan PO lengkap dengan daftar item, kuantitas, harga kesepakatan, dan estimasi tanggal kirim.
  - Status Lifecycle PO: *Draf* $\rightarrow$ *Dikonfirmasi* $\rightarrow$ *Diterima Sebagian* $\rightarrow$ *Selesai* $\rightarrow$ *Dibatalkan*.
  - Cetak dokumen PO standar PDF/Print siap kirim via email/WhatsApp ke supplier.

### 6.2 Penerimaan Barang Fisik (Goods Receipt)
* **Tujuan Utama:** Verifikasi barang yang tiba di gudang dari supplier.
* **Kemampuan Fitur:**
  - Pencocokan jumlah barang datang dengan jumlah di PO (mencegah kekurangan atau kelebihan kirim).
  - Penambahan kuantitas stok gudang secara otomatis saat verifikasi penerimaan disimpan.
  - **Otomatisasi Tagihan Supplier (AP):** Langsung membentuk catatan hutang supplier dan jurnal akuntansi persediaan.
  - **1-Klik Beli ke Stok (Instant Stock-In):** Jalur kilat untuk belanja pasar tanpa PO formal yang langsung menambah stok dan memotong uang kas toko.

### 6.3 Manajemen Hutang Usaha & Pembayaran Supplier (Accounts Payable / AP)
* **Tujuan Utama:** Mengontrol kewajiban pembayaran tagihan supplier agar terhindar dari denda dan menjaga reputasi bisnis.
* **Kemampuan Fitur:**
  - **AP Aging Analysis:** Mengelompokkan tagihan ke dalam kategori: *Lancar (Belum Jatuh Tempo)*, *Lewat 1–30 Hari*, dan *Kritis (>30 Hari)*.
  - Pencatatan pembayaran tagihan bertahap (*partial payment*) atau pelunasan penuh.
  - Integrasi pemotongan saldo rekening bank/kas pembayar secara atomik.

---

## 7. Modul Penjualan Komersial & B2B (Sales & Invoicing)

### 7.1 Penawaran Harga (Quotations)
* **Tujuan Utama:** Pembuatan dokumen proposal penawaran harga untuk klien korporat atau transaksi partai besar.
* **Kemampuan Fitur:**
  - Perhitungan subtotal, diskon volume, PPN, dan syarat pembayaran (*terms of payment*).
  - **One-Click Convert:** Mengonversi penawaran yang disetujui klien langsung menjadi Sales Order (SO) tanpa input ulang data.

### 7.2 Surat Perintah Penjualan (Sales Orders / SO)
* **Tujuan Utama:** Konfirmasi resmi pesanan pelanggan sebelum dilakukan pengiriman barang atau penerbitan invoice penagihan.
* **Kemampuan Fitur:**
  - Memvalidasi ketersediaan stok sebelum pesanan diproses.
  - Generate Invoice resmi dengan satu klik.

### 7.3 Faktur Penjualan B2B (Invoices & AR Management)
* **Tujuan Utama:** Penagihan pembayaran resmi kepada pelanggan.
* **Kemampuan Fitur:**
  - Penomoran faktur otomatis dengan format profesional.
  - Template cetak invoice modern (Apple HIG minimalist) siap cetak atau kirim PDF.
  - **AR Aging Analysis:** Memantau umur piutang pelanggan untuk mencegah kredit macet.
  - Pencatatan pembayaran invoice via transfer/kas dengan penjurnalan otomatis.

### 7.4 Manajemen Retur Penjualan & Pembelian (Returns Management)
* **Tujuan Utama:** Mengelola pengembalian barang cacat/rusak dari pelanggan atau ke supplier.
* **Kemampuan Fitur:**
  - Pengembalian unit barang ke gudang stok atau status barang rusak (*damaged write-off*).
  - Rekonsiliasi pengembalian dana (*refund cash*) atau pemotongan saldo piutang/hutang.

---

## 8. Modul Keuangan & Akuntansi (Finance & Double-Entry Accounting)

### 8.1 Kas & Rekening Bank (Cash & Bank Management)
* **Tujuan Utama:** Pengelolaan rekening bank dan brankas kas fisik perusahaan.
* **Kemampuan Fitur:**
  - **Multi-Account Dashboard:** Memantau saldo riil di Kas Utama, Kas Operasional, Rekening Bank BCA, Mandiri, e-Wallet, dll.
  - **Pencatatan Kas Masuk & Kas Keluar Manual:** Untuk transaksi non-dagang seperti setoran modal pemilik atau penarikan dividen.
  - **Transfer Saldo Antar Rekening:** Pemindahan dana internal (misal: setor tunai kasir ke rekening bank) yang otomatis membentuk dua mutasi dan jurnal transfer berpasangan.
  - **Pelacakan Multi-Kanal POS:** Memecah penerimaan kasir POS menjadi jalur Tunai, QRIS, Transfer, EDC, dan Kasbon secara realtime.

### 8.2 Buku Kas & Ledger Berjalan
* **Tujuan Utama:** Audit trail mutasi debit dan kredit seluruh akun keuangan.
* **Kemampuan Fitur:**
  - Menampilkan tanggal, nomor rekening, arah arus (*In/Out/Transfer*), keterangan, nominal, dan saldo akhir berjalan (*balance after*).
  - Filter lengkap berdasarkan rekening, jenis arus, metode bayar POS, rentang tanggal, dan pencarian kata kunci.

### 8.3 Manajemen Beban Operasional (Expenses)
* **Tujuan Utama:** Pengendalian biaya operasional rutin (gaji, listrik, sewa, internet, transportasi).
* **Kemampuan Fitur:**
  - Klasifikasi Bagan Akun Beban (COA Kelas 5/6).
  - Pemilihan Akun Kas/Bank pembayar dengan proteksi saldo tidak mencukupi.
  - Unggah lampiran foto bukti struk/kwitansi pembelian.
  - Transaksi dibungkus `DB::transaction` untuk mencegah jurnal yatim jika terjadi kegagalan sistem.

### 8.4 Buku Jurnal Umum & Otomatisasi Akuntansi (Auto-Journal Engine)
* **Tujuan Utama:** Menyediakan pembukuan standar akuntansi tanpa memerlukan akuntan manual untuk setiap transaksi harian.
* **Kemampuan Fitur:**
  - **Otomatisasi 9 Jalur Transaksi:** Penjualan POS, Beban Operasional, Penerbitan Invoice B2B, Pembayaran Invoice, Penerimaan Barang Gudang, Pembayaran Supplier, Retur Penjualan, Retur Pembelian, dan Transfer Kas/Bank.
  - **Balance Integrity Audit:** Widget indikator status keseimbangan total debit vs total kredit di bagian atas layar jurnal (*Balanced ✓* vs *Imbalanced ⚠*).
  - Format voucher jurnal rapi dengan penomoran unik kronologis.

### 8.5 Laporan Keuangan & Analitika Eksekutif (Financial Reporting)
* **Tujuan Utama:** Menyajikan performa finansial bisnis kepada pemilik modal dan manajemen.
* **Kemampuan Fitur:**
  - **Laporan Laba Rugi (Income Statement):** Pendapatan Bersih $-$ HPP $=$ Laba Kotor $-$ Total Beban Operasional $=$ Laba Bersih Periode.
  - **Laporan Arus Kas (Cash Flow Statement):** Rincian arus kas dari aktivitas Operasi, Investasi, dan Pendanaan.
  - **Laporan Penilaian Stok (Stock Valuation):** Total nilai aset persediaan barang yang ada di seluruh gudang.
  - **Ekspor CSV / Excel:** Seluruh laporan keuangan dapat diunduh untuk kebutuhan perpajakan dan pelaporan investor.

---

## 9. Modul CRM, Loyalitas Pelanggan & Promosi

### 9.1 Database Pelanggan & Segmentasi
* **Tujuan Utama:** Manajemen data kontak pelanggan, histori belanja, dan preferensi produk.
* **Kemampuan Fitur:**
  - Database nomor WhatsApp, alamat, email, dan tanggal ulang tahun.
  - Riwayat total akumulasi transaksi belanja (Customer Lifetime Value / LTV).

### 9.2 Sistem Poin Loyalitas & Membership (Loyalty Engine)
* **Tujuan Utama:** Meningkatkan retensi pelanggan dengan skema reward belanja.
* **Kemampuan Fitur:**
  - **Perolehan Poin Otomatis:** Menghasilkan poin berdasarkan kelipatan nilai transaksi belanja di kasir POS.
  - **Penukaran Poin di Kasir:** Pelanggan dapat memotong total tagihan belanja dengan menukarkan poin yang terkumpul.
  - **Riwayat Mutasi Poin:** Catatan perolehan dan penggunaan poin pelanggan yang transparan.

### 9.3 Kasbon & Limit Kredit Pelanggan (Customer Store Credit)
* **Tujuan Utama:** Mengelola penjualan tempo/kasbon khusus pelanggan langganan di kasir POS.
* **Kemampuan Fitur:**
  - Pengaturan limit plafon piutang maksimal per pelanggan.
  - Pencatatan pembayaran cicilan atau pelunasan kasbon langsung dari menu CRM.

### 9.4 Kupon Diskon & Voucher Promosi
* **Tujuan Utama:** Kampanye pemasaran untuk meningkatkan volume penjualan.
* **Kemampuan Fitur:**
  - Pengaturan kode voucher, tipe potongan (Persentase % atau Nominal Rp), dan minimal belanja.
  - Batas kuota penggunaan voucher dan masa berlaku tanggal aktif.

---

## 10. Modul WhatsApp Gateway & Marketing Engine

### 10.1 Konektivitas WhatsApp Microservice (wa-server)
* **Tujuan Utama:** Menghubungkan nomor WhatsApp bisnis ke sistem COOCA ERP secara langsung.
* **Kemampuan Fitur:**
  - **Scan QR Code Pairing:** Menghubungkan nomor WhatsApp toko via scan QR di panel web (menggunakan Baileys protocol).
  - **Multi-Device Support:** Tetap aktif tanpa perlu ponsel selalu menyala di dekat komputer.
  - **Webhook Notifikasi Masuk:** Menerima respon chat pelanggan kembali ke server ERP.

### 10.2 Pengiriman Struk Belanja Otomatis (E-Receipt via WA)
* **Tujuan Utama:** Menghemat kertas struk dan memberikan pengalaman modern bagi pelanggan.
* **Kemampuan Fitur:**
  - Pengiriman pesan tanda terima struk belanja langsung ke nomor WhatsApp pelanggan setelah kasir menyelesaikan transaksi.
  - Menyertakan link e-receipt interaktif dan preview gambar struk belanja.

### 10.3 Mesin Broadcast Promosi (WhatsApp Broadcast Campaign)
* **Tujuan Utama:** Mengirimkan pesan promosi, menu baru, atau ucapan selamat secara massal kepada database pelanggan.
* **Kemampuan Fitur:**
  - **Estimasi Penerima:** Menghitung jumlah target nomor penerima sebelum pesan dikirim.
  - **Anti-Banned Delay Protection:** Mengatur jeda interval antar pengiriman pesan untuk melindungi nomor bisnis dari pemblokiran oleh WhatsApp.
  - **Log Status Pengiriman:** Memantau pesan yang berhasil terkirim, pending, atau gagal.

---

## 11. Modul AI Assistant & Analitik Prediktif

### 11.1 Asisten Bisnis AI (AI Business Copilot)
* **Tujuan Utama:** Memberikan saran strategi operasional, penetapan harga, dan efisiensi biaya berbasis kecerdasan buatan.
* **Kemampuan Fitur:**
  - **Tanya Data Penjualan:** Bertanya dalam bahasa alami (contoh: *"Produk apa yang paling laris minggu ini dan cabang mana yang marginnya paling tipis?"*).
  - **Rekomendasi Penyesuaian Harga:** Analisis elastisitas harga produk terhadap HPP bahan baku terkini.

### 11.2 Prediksi Penjualan & Estimasi Kebutuhan Bahan (AI Demand Forecasting)
* **Tujuan Utama:** Mencegah kehabisan stok (*stockout*) atau bahan basi terbuang (*spoilage*).
* **Kemampuan Fitur:**
  - Memprediksi volume penjualan produk untuk 7 hari ke depan berdasarkan tren historis hari kerja vs akhir pekan.
  - Menghitung rekomendasi kuantitas bahan baku yang harus dibeli ke supplier minggu ini.

---

## 12. Modul CMS Website & Landing Page Bisnis

### 12.1 Pembuat Landing Page Usaha (Business Mini Website)
* **Tujuan Utama:** Memberikan setiap outlet bisnis sebuah website profil satu halaman (*single-page website*) instan yang siap diakses publik.
* **Kemampuan Fitur:**
  - **URL Publik Unik:** Dapat diakses langsung pada alamat domain `/b/{slug-bisnis}`.
  - **CMS Kustomisasi Visual:** Pengaturan logo, banner hero, teks deskripsi usaha, jam operasional, tautan media sosial, Google Maps, dan tombol direct WhatsApp.
  - **Katalog Menu Terintegrasi:** Menampilkan daftar produk unggulan langsung dari database katalog aktif.
  - **Preset Industri:** Pilihan tema desain siap pakai (Cafe, Resto, Butik/Fashion, Toko Kelontong, Jasa Servis).
  - **Saklar Publikasi (Publish/Unpublish):** Mengaktifkan atau menonaktifkan website dalam satu klik.

---

## 13. Modul Ekosistem Publik, SEO & Lead Generation

### 13.1 Kalkulator Bisnis Publik Gratis (/kalkulator)
* **Tujuan Utama:** Magnet pengunjung organik (SEO) yang dapat digunakan pelaku usaha umum secara gratis.
* **Kemampuan Fitur:**
  - Kalkulator HPP Kuliner & Manufaktur.
  - Kalkulator Titik Impas (BEP).
  - Kalkulator Penetapan Harga Jual & Margin.
  - Kalkulator PPh Final UMKM 0.5%.
  - Kalkulator Gaji Karyawan & Omzet Harian.

### 13.2 Pusat Unduhan Template & Lead Capture (/template-pembukuan-gratis)
* **Tujuan Utama:** Mengumpulkan prospek leads calon pelanggan SaaS dengan memberikan template spreadsheet pembukuan gratis.
* **Kemampuan Fitur:**
  - Formulir pendaftaran nama, email, dan WhatsApp sebelum file Excel dapat diunduh.
  - Data prospek otomatis tersimpan ke modul *Admin Leads* untuk di-follow up oleh tim sales.

### 13.3 Blog Bisnis & Optimasi Mesin Pencari (SEO Engine)
* **Tujuan Utama:** Publikasi artikel edukasi bisnis dan optimasi ranking Google.
* **Kemampuan Fitur:**
  - Generator otomatis `sitemap.xml` dan `sitemap.html`.
  - Struktur Schema.org, OpenGraph Meta Tags, dan canonical URL otomatis.

---

## 14. Modul Multi-Tenant, Keanggotaan & RBAC

### 14.1 Multi-Tenancy & Tenant Switcher
* **Tujuan Utama:** Satu akun user dapat memiliki atau mengelola beberapa bisnis/cabang yang berbeda secara terisolasi.
* **Kemampuan Fitur:**
  - Pemisahan data absolut antar tenant (`business_id`).
  - Menu *Switch Business* instan untuk berpindah antar cabang tanpa perlu logout.

### 14.2 Manajemen Hak Akses Terperinci (Role-Based Access Control / RBAC)
* **Tujuan Utama:** Membatasi akses menu dan tombol sensitif berdasarkan jabatan karyawan.
* **Kemampuan Fitur:**
  - **Role Bawaan:** *Owner*, *Manager*, *Kasir*, *Staff Gudang*, *Finance/Akuntan*.
  - **Custom Roles:** Kemampuan membuat role baru dengan konfigurasi granular (contoh: Kasir hanya boleh buka POS dan shift, tidak boleh melihat menu HPP, biaya beban, atau laporan laba rugi).
  - **Manajemen Anggota Tim:** Mengundang staf baru via email dan menentukan jabatan mereka.

---

## 15. Modul SaaS Billing, Langganan & Batas Kuota

### 15.1 Manajemen Paket Berlangganan (Subscription Management)
* **Tujuan Utama:** Pengelolaan model monetisasi SaaS (Freemium, Starter, Pro, Enterprise).
* **Kemampuan Fitur:**
  - **Checkout Pembayaran Langganan:** Pilihan paket bulanan/tahunan dengan metode pembayaran transfer bank dan unggah bukti transfer.
  - **Fitur Patungan Bisnis:** Skema langganan kolaboratif.
  - **Pemberian Masa Aktif Otomatis:** Setelah diverifikasi admin, masa aktif langganan akun diperpanjang otomatis.

### 15.2 Pengendalian Kuota Sumber Daya (Resource Quota Enforcer)
* **Tujuan Utama:** Memastikan tenant tidak menggunakan kapasitas melebihi paket langganannya.
* **Kemampuan Fitur:**
  - Limit jumlah produk maksimal.
  - Limit jumlah staf/user maksimal.
  - Limit kapasitas penyimpanan lampiran gambar/nota (*storage quota*).
  - Limit kuota token AI per bulan.

---

## 16. Modul Super Admin Control Center

### 16.1 Manajemen Tenant & Pengguna Global
* **Tujuan Utama:** Panel kendali pusat untuk pemilik platform COOCA Core.
* **Kemampuan Fitur:**
  - Melihat daftar seluruh bisnis yang terdaftar, status masa aktif, dan paket yang digunakan.
  - Fitur *Suspend / Activate* akun bisnis yang melanggar ketentuan atau menunggak pembayaran.
  - Ekspor seluruh database pengguna ke file CSV.

### 16.2 Manajemen Pembayaran & Konfirmasi Langganan
* **Tujuan Utama:** Memvalidasi bukti transfer langganan dari pengguna.
* **Kemampuan Fitur:**
  - Menampilkan foto struk transfer pembayaran langganan.
  - Tombol aksi 1-klik: **Approve** (mengaktifkan paket pengguna) atau **Reject** (menolak dengan alasan).

### 16.3 WhatsApp Admin Bot (Billing Reminders & System Blast)
* **Tujuan Utama:** Otomatisasi penagihan perpanjangan langganan via WhatsApp.
* **Kemampuan Fitur:**
  - Kirim pengingat otomatis jatuh tempo langganan: H-7, H-3, H-1, dan Hari H.
  - Fitur kirim pesan siaran massal (*System Blast*) ke seluruh pemilik bisnis terdaftar.

### 16.4 Pusat Laporan Masukan & Bug (Feedback Center)
* **Tujuan Utama:** Menampung aspirasi dan laporan kendala dari pengguna.
* **Kemampuan Fitur:**
  - Manajemen tiket laporan bug (*Bug Reports*).
  - Manajemen permintaan fitur baru (*Feature Requests*) dengan status pengerjaan (*Pending*, *In Progress*, *Resolved*).

---

## 17. Modul Aplikasi Mobile POS (Flutter)

### 17.1 Native Mobile Cashier App (Android & iOS)
* **Tujuan Utama:** Memberikan fleksibilitas bagi kasir dan staf untuk bertransaksi menggunakan tablet atau smartphone di lantai penjualan.
* **Kemampuan Fitur:**
  - **Clean Architecture & State Management:** Dibangun dengan Flutter dan Provider untuk performa rendering 60 FPS yang mulus.
  - **Autentikasi Aman:** Login menggunakan Laravel Sanctum Bearer Token dengan multi-tenant selector.
  - **Katalog Cepat & Keranjang Belanja:** Dilengkapi stepper jumlah barang, diskon per item, dan catatan pesanan koki (*kitchen notes*).
  - **Konektivitas Printer Thermal:** Mencetak langsung struk kasir ke printer thermal Bluetooth (format 58mm / 80mm).
  - **Sinkronisasi Buka/Tutup Shift:** Sinkron penuh dengan backend server untuk rekonsiliasi kas laci secara realtime.

---

## 18. Matriks Ringkasan Modul & Hak Akses

| No | Modul Sistem | Guard / Akses | Pengguna Utama | Integrasi Utama |
| :---: | :--- | :--- | :--- | :--- |
| **1** | **Kalkulator HPP & Biaya** | Tenant Auth (`costing.*`) | Owner, Produksi, Chef | Produk, Resep, Inventori |
| **2** | **Master Data & Bahan Baku** | Tenant Auth (`materials.*`) | Purchasing, Gudang | Resep BOM, Supplier, PO |
| **3** | **Katalog Produk & Resep** | Tenant Auth (`products.*`) | Owner, Kitchen, Kasir | POS, HPP, Inventori |
| **4** | **Terminal POS & Shift** | Tenant Auth (`pos.*`) | Kasir, Store Manager | Kas & Bank, Jurnal, CRM |
| **5** | **Meja & QR Self-Order** | Tenant Auth + Public | Tamu Resto, Waiter | POS Terminal, Kitchen Display |
| **6** | **Kitchen Display (KDS)** | Tenant Auth (`pos.kitchen`) | Koki, Barista | Terminal Kasir, Meja |
| **7** | **Gudang & Stok Opname** | Tenant Auth (`inventory.*`) | Staff Gudang, Logistik | PO, Goods Receipt, POS |
| **8** | **Pembelian & PO** | Tenant Auth (`purchasing.*`)| Purchasing, Supplier | Goods Receipt, Hutang (AP) |
| **9** | **Penjualan B2B & Invoice**| Tenant Auth (`invoices.*`) | Sales, Marketing | Piutang (AR), Kas/Bank |
| **10**| **Kas & Rekening Bank** | Tenant Auth (`finance.*`) | Finance, Owner | POS, Beban, Jurnal Umum |
| **11**| **Buku Jurnal Umum (GL)** | Tenant Auth (`accounting.*`)| Akuntan, Owner | Seluruh Transaksi Finansial |
| **12**| **CRM & Poin Loyalitas** | Tenant Auth (`crm.*`) | Kasir, Marketing | POS Terminal, WhatsApp |
| **13**| **WhatsApp Gateway** | Tenant Auth (`whatsapp.*`)| Admin, CS, Kasir | POS Receipts, Marketing |
| **14**| **AI Business Assistant** | Tenant Auth (`ai.*`) | Owner, Manajer | Analitik Penjualan, HPP |
| **15**| **CMS Mini Landing Page** | Tenant Auth (`cms.*`) | Owner, Marketing | Katalog Publik, SEO |
| **16**| **Tools & Blog Publik** | Public Guest (No Auth) | Publik, Calon Leads | Lead Capture, Sitemap SEO |
| **17**| **Super Admin Platform** | Admin Guard (`auth:admin`) | Developer, Super Admin | Tenant, Billing Packages |
| **18**| **Mobile App (Flutter)** | Mobile API (`Sanctum`) | Kasir Lapangan | POS Backend, Printer Thermal |

---

## 19. Kesimpulan & Keunggulan Kompetitif

Ekosistem **COOCA Core** bukan sekadar aplikasi kasir (POS) biasa ataupun software akuntansi terpisah, melainkan **Full-Suite Enterprise ERP** yang memadukan:
1. **Ketelitian HPP Ilmiah:** Mengetahui biaya riil produk hingga ke butir gram bahan dan menit kerja mesin.
2. **Otomatisasi Akuntansi Penuh:** Tanpa perlu menjurnal manual, setiap struk kasir, nota belanja pasar, atau penerimaan barang langsung membentuk jurnal double-entry yang seimbang.
3. **Pengalaman Pengguna Kelas Dunia:** Mengadopsi standar Apple Human Interface Guidelines (macOS Sonoma & iOS 18) untuk kecepatan, densitas informasi tinggi, dan estetika visual premium.
4. **Kesiapan Multi-Channel & Multi-Outlet:** Mendukung operasional terpusat dari smartphone kasir, monitor dapur, hingga kontrol eksekutif pemilik bisnis.

---
*Dokumen ini diperbarui secara berkala sebagai standar referensi teknis dan fungsional COOCA Core ERP.*
