# COOCA - Saran Fitur, Optimasi Performa, & Penataan Arsitektur End-to-End (Referensi Lengkap)

Dokumen ini memuat cetak biru (*blueprint*) penataan fitur, optimasi performa sistem, serta arsitektur alur kerja hulu-ke-hilir (*end-to-end*) agar ekosistem COOCA ERP & POS bekerja secara terstruktur, berkinerja tinggi, aman, dan mudah dioperasikan.

---

## 1. Taksonomi & Penataan 6-Hub Modul Utama (End-to-End Modular Architecture)

Untuk menghindari fragmentasi menu dan kebingungan navigasi pengguna UMKM, seluruh fitur COOCA dikonsolidasikan ke dalam **6 Hub Operasional Terpadu**:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           COOCA ALL-IN-ONE BUSINESS OS                      │
├──────────────────────┬──────────────────────────────┬───────────────────────┤
│ 1. OPERASIONAL KASIR │ 2. KATALOG & LOGISTIK        │ 3. PENGADAAN & VENDOR │
│ • Terminal Kasir POS │ • Katalog & Varian/Modifiers │ • Direktori Supplier  │
│ • Manajemen Meja/Dine│ • Bahan Baku & Resep BOM     │ • Purchase Order (PO) │
│ • Tiket Dapur/Bar KOT│ • Multi-Gudang & Cabang      │ • Penerimaan GRN (3-Way)│
│ • Hardware & Laci Kas│ • Mutasi & Penyesuaian Stok  │ • Retur Pembelian     │
│ • Tutup Shift (Blind)│ • Transfer Stok In-Transit   │ • Tagihan Vendor (AP) │
├──────────────────────┼──────────────────────────────┼───────────────────────┤
│ 4. PELANGGAN & TOKO  │ 5. KEUANGAN, PAJAK & SDM     │ 6. EKOSISTEM & OMNI   │
│ • CRM, Member & Poin │ • Kas & Rekening Bank        │ • WhatsApp Cloud API  │
│ • Toko Online Store  │ • Auto-Journal Double-Entry  │ • Medsos (Meta/TikTok)│
│ • Pre-Order & Batch  │ • Piutang (AR) & Auto-Remind │ • Landing Page Studio │
│ • Ekspedisi Biteship │ • Pajak (PPh 21/Final/Badan) │ • Billing SaaS Tier   │
│ • Voucher & Diskon   │ • Payroll, BPJS, THR, Komisi │ • Pengaturan Platform │
└──────────────────────┴──────────────────────────────┴───────────────────────┘
```

### 1.1 Hub 1: Operasional Kasir & Front-Desk (POS Hub)
* **Terminal Kasir POS Cepat:** Antarmuka responsif ramah sentuh (Tablet/Desktop/Mobile), pencarian instan via foto katalog, barcode scanner hardware, atau shortcut keyboard.
* **Manajemen Meja & Pesanan Tempat (*Table & Dine-In*):** Pemetaan denah meja visual, status meja (*Kosong, Terisi, Billing, Kotor*), dan penggabungan/pemisahan tagihan (*Split/Merge Bill*).
* **Cetak ESC/POS Hardware & Tiket Dapur/Bar (*Kitchen Order Ticket - KOT*):** Pencetakan binary stream instan ke printer thermal 58mm/80mm via LAN/Wi-Fi/USB/Bluetooth dengan perutean otomatis item makanan ke dapur dan minuman ke bar.
* **Proteksi Laci Kas (*Cash Drawer Safety*):** RJ-11/RJ-12 solenoid pulse hanya aktif saat pembayaran tunai `COMPLETED`. Dilarang membuka saat transaksi non-tunai atau cetak ulang struk (*reprint*). Pembukaan manual *No-Sale* wajib PIN Supervisor dan terekam di `audit_logs`.
* **Tutup Shift & *Blind Cash Count*:** Kasir menginput uang fisik riil tanpa melihat angka ekspektasi sistem; sistem otomatis mencatat selisih kas (*over/short*), mengunci shift, dan menerbitkan jurnal penyesuaian kas.

### 1.2 Hub 2: Katalog Produk, Stok, & Logistik Multi-Cabang
* **Katalog Produk & Varian Dinamis:** Dukungan produk barang fisik (dengan stok) vs jasa/servis (bebas stok), modifier topping/level pedas, dan barcode SKU unik.
* **Bahan Baku & Resep (*Bill of Materials - BOM*):** Pemotongan otomatis stok bahan baku mentah per porsi penjualan (*Auto-BOM Deduction*) dan kalkulasi HPP ilmiah berbasis bahan baku riil.
* **Multi-Gudang & Multi-Cabang:** Pengelolaan stok terisolasi per lokasi, minimum stock alert (*Reorder Point*), dan pelacakan batch/kadaluarsa.
* **Transfer Stok Antar-Cabang (*Two-Step In-Transit*):** Status `In-Transit` saat dikirim dari Cabang A, dan baru masuk stok Cabang B setelah diverifikasi fisik (`Received`) oleh staf Cabang B guna mencegah penggelapan barang di perjalanan.
* **Penyesuaian Stok (*Stock Adjustment*):** Penyesuaian stok minus wajib menyertakan foto Berita Acara, alasan baku, dan otorisasi Supervisor/Owner (Maker-Checker).

### 1.3 Hub 3: Pengadaan, Pemasok, & Hutang Usaha (Procurement & AP)
* **Direktori Supplier Terintegrasi:** Rekam jejak performa pemasok, nomor rekening bank resmi, termin pembayaran, dan riwayat pasokan.
* **Purchase Order (PO) Berjenjang:** Alur persetujuan dokumen bertingkat (*Maker-Approver-Releaser MAR*) untuk pembelian bernilai besar.
* **Penerimaan Barang (*Three-Way Matching GRN*):** Pencairan tagihan pembelian wajib mencocokkan Purchase Order (PO) $\leftrightarrow$ Bukti Penerimaan Barang (GRN) $\leftrightarrow$ Invoice Supplier untuk mencegah tagihan siluman.
* **Retur Pembelian:** Pencatatan pengembalian barang cacat/rusak ke supplier dengan pemotongan hutang usaha atau pengembalian dana kas/bank.

### 1.4 Hub 4: Pelanggan, Loyalitas, & Kanal Digital (Commerce & CRM)
* **Pusat Pelanggan & Utang Piutang:** Profil pelanggan, riwayat transaksi seumur hidup (*LTV*), batas plafon kasbon/piutang, dan kontak WhatsApp terverifikasi.
* **Program Loyalitas & Poin Member:** Akumulasi poin belanja otomatis, tier membership (*Silver, Gold, Platinum*), dan voucher diskon digital.
* **Toko Online Publik (*Storefront Hub*):** Kanal e-commerce instan terhubung katalog kasir, keranjang belanja terisolasi per tenant, dan identitas pembeli global (*Global Customer*).
* **Pre-Order Dinamis & Batch Scheduling:** Pengaturan pesanan PO berjangka dengan batas kuota harian/mingguan dan jadwal produksi otomatis.
* **Integrasi Kurir Ekspedisi Otomatis (*Biteship Multi-Courier*):** Kalkulasi ongkos kirim real-time (JNE, J&T, SiCepat, GoSend, GrabExpress), cetak label resi otomatis, dan pelacakan paket live.

### 1.5 Hub 5: Keuangan, Akuntansi, Pajak, & SDM (Finance, Tax & HRM)
* **Pusat Kas & Bank Terpadu:** Monitoring saldo kas kecil laci kasir, rekening bank operasional, dan mutasi ledger dalam satu layar bento.
* **Pembukuan Akuntansi Ganda Otomatis (*Auto-Journal Double-Entry*):** Seluruh transaksi operasional otomatis menghasilkan jurnal berimbang (Debit = Kredit) tanpa menuntut user memahami kode akun akuntansi.
* **Manajemen Piutang & Pengingat Otomatis (*Auto-Reminder*):** Pengingat tagihan santun terjadwal via WhatsApp/Email (H-3, Hari H, H+3) dilengkapi link kuitansi dan tombol bayar online.
* **Kepatuhan Pajak UMKM Komprehensif:** Simulasi dan pelaporan PPh Final UMKM 0.5% (PP 55/2022), PPh 21 Karyawan metode TER (PP 58/2023), PPh Badan UU HPP Pasal 31E/17, serta Pajak Restoran (PB1 10%) / PPN (11%/12%).
* **Penggajian & Kesejahteraan Karyawan (*HRM Suite*):** Perhitungan gaji pokok, lembur, komisi teknisi/SPK, cicilan kasbon, BPJS Ketenagakerjaan/Kesehatan, THR resmi (Permenaker 6/2016), dan slip gaji digital.

### 1.6 Hub 6: Ekosistem, Pemasaran Omnichannel, & Administrasi Platform
* **Saluran WhatsApp Cloud API Meta Multi-Tenant:** Nomor WhatsApp resmi bisnis untuk pengiriman struk digital, update order, OTP, dan broadcast promosi legal.
* **Pemasaran Media Sosial Omnichannel:** Integrasi posting konten otomatis ke Instagram, Facebook Page, TikTok, dan LinkedIn via UGC Post API.
* **Landing Page Studio:** Generator website profil bisnis dengan editor visual bento dan custom domain.
* **Manajemen Paket Billing & Langganan SaaS:** Integrasi pembayaran langganan tier platform (Free, Standard, Premium, Prestige) via TriPay Payment Gateway.

---

## 2. Saran Fitur Optimasi Kinerja & Skalabilitas (Performance & Resilience)

Untuk menjamin sistem tetap cepat (*sub-100ms response time*), stabil saat lonjakan transaksi, dan hemat sumber daya server:

### 2.1 Caching Layer Cerdas Berbasis Tag (Redis / Cache Tags)
1. **Cache Master Data yang Jarang Berubah:**
   - Katalog produk aktif, kategori, varian, satuan, dan konfigurasi pajak di-cache dengan key: `tenant:{business_id}:products:active`.
   - Konfigurasi toko dan storefront di-cache dengan TTL 1 jam.
2. **Invalidasi Otomatis via Eloquent Observers:**
   - Saat produk, kategori, atau harga diubah/dihapus, `ProductObserver` otomatis membersihkan tag cache terkait (`Cache::tags(["tenant_{$businessId}"])->flush()`).
3. **Pemisahan Cache Sesi & Antrean:**
   - Gunakan driver Redis untuk session, cache, dan queue worker guna mencegah locking pada basis data utama.

### 2.2 Offline-First POS Resilience (PWA & IndexedDB Local Storage)
1. **Penyimpanan Katalog Lokal di Browser Kasir:**
   - Saat terminal POS dibuka, aplikasi menyalin snapshot katalog produk dan harga ke storage lokal browser (*IndexedDB*).
2. **Offline Checkout Mode:**
   - Jika koneksi internet kasir putus saat jam sibuk, kasir tetap dapat memindai barang, menghitung total belanja, menerima pembayaran tunai, dan mencetak struk ESC/POS lokal.
3. **Automatic Resilient Sync:**
   - Saat internet kembali terhubung, antrean transaksi lokal (*Sync Queue*) otomatis dikirimkan ke server secara berurutan dengan verifikasi idempotensi (*anti-duplicate order*).

### 2.3 Optimasi Basis Data & Indeks Komposit
1. **Indeks Komposit pada Tabel Transaksi Besar:**
   ```sql
   -- orders table
   INDEX idx_orders_business_created (business_id, created_at DESC)
   INDEX idx_orders_business_status (business_id, status, created_at DESC)
   INDEX idx_orders_branch (business_id, branch_id, created_at DESC)
   
   -- stock_movements table
   INDEX idx_stock_movements_lookup (business_id, product_id, location_id, created_at DESC)
   ```
2. **Zero N+1 Query Mandate:**
   - Seluruh query daftar (list/table) wajib eager-loading relasi: `Order::with(['items.product', 'customer', 'branch', 'cashier'])->where(...)`.
   - Gunakan tools deteksi seperti Laravel Debugbar / Telescope di lingkungan pengembangan untuk memastikan query count minimal.

### 2.4 Asynchronous Background Task Queue (Laravel Queue & Horizon)
1. **Offloading Bebas Hambatan (Zero Blocking on UI):**
   - Pembuatan file PDF invoice / laporan keuangan bulanan.
   - Pengiriman pesan WhatsApp Cloud API dan Email HTML.
   - Sinkronisasi posting media sosial multi-platform.
   - Pemrosesan batch auto-journaling dan rekonsiliasi bank.
2. **Retry Mechanism & Dead-Letter Buffer:**
   - Setiap job antrean wajib memiliki batas retry (`tries = 3`) dan interval jeda eksponensial (`backoff = [10, 60, 300]`).
   - Job yang gagal permanen tersimpan di `failed_jobs` dan memunculkan notifikasi di dashboard Administrator.

---

## 3. Saran Fitur Optimasi UX & Otomasi Alur Kerja (Zero-Manual Workflow)

### 3.1 Integrasi Smart Barcode & QR Hardware Scanner
* Dukungan penuh scanner barcode USB/Bluetooth (mode HID Keyboard Emulation) pada terminal POS dan halaman Stok Opname Gudang.
* Input textfield kasir otomatis menangkap input barcode scanner secara global tanpa kasir perlu mengklik kotak input terlebih dahulu (*Global Barcode Listener*).

### 3.2 Self-Service QR Table Ordering (Restoran & Kafe)
* Setiap meja makan dilengkapi QR Code unik (`/order/table/{token}`).
* Pengunjung memindai QR menggunakan smartphone mereka, memilih menu dari buku menu digital bento yang estetik, dan melakukan pemesanan langsung.
* Pesanan otomatis masuk ke terminal POS kasir sebagai pesanan aktif dan mencetak tiket pesanan dapur (*KOT*) secara otomatis.

### 3.3 Smart Auto-Reorder & Safety Stock Advisor
* Sistem menghitung kecepatan penjualan harian (*Daily Sales Velocity*) dan sisa stok bahan baku.
* Saat stok mendekati batas aman (*Reorder Point*), sistem otomatis memunculkan notifikasi peringatan di dashboard dan menyiapkan tombol 1-klik: **`[ Buat Draf PO ke Supplier ]`** dengan jumlah pemesanan yang disarankan.

### 3.4 Interactive Customer WhatsApp Self-Service Bot
* Pelanggan dapat berinteraksi langsung dengan nomor WhatsApp resmi toko:
  - Ketik `1` atau `NOTA`: Mengirimkan tautan struk/invoice transaksi terakhir.
  - Ketik `2` atau `STATUS`: Mengecek status pesanan online dan nomor resi pengiriman kurir.
  - Ketik `3` atau `POIN`: Mengecek jumlah poin loyalitas dan voucher yang tersedia.

### 3.5 Smart Anomaly Insight & Executive Assistant untuk Owner
* Kartu analitik bento cerdas di halaman dashboard yang menerjemahkan angka rumit menjadi wawasan bisnis bahasa Indonesia yang mudah dipahami:
  - *“💡 Wawasan: Produk 'Kopi Susu Gula Aren' menyumbang 42% omzet minggu ini. Margin bahan baku stabil di 65%.”*
  - *“⚠️ Perhatian: Terjadi 4x pembatalan nota (void) pada Shift Malam Cabang Barat. Disarankan periksa rekaman audit kasir.”*

---

## 4. Panduan Penataan Kontrol Akses & Keamanan End-to-End

| Tingkatan Peran | Lingkup Akses (Scope) | Batasan & Proteksi Wajib |
| :--- | :--- | :--- |
| **Superadmin (Platform)** | Seluruh Tenant Platform | • Hanya akses pengawasan, billing platform, dan diagnostik sistem.<br>• Dilarang memodifikasi saldo kas atau stok fisik tenant tanpa otorisasi tertulis.<br>• Setiap aksi mutasi wajib mencatat `admin_id` ke audit log. |
| **Business Owner** | Seluruh Cabang & Modul Bisnis Miliknya | • Akses penuh laporan laba rugi, konfigurasi sistem, dan manajemen hak akses staf.<br>• Penerima utama notifikasi anomali fraud (WhatsApp & Email).<br>• Otoritas tertinggi penguncian buku akuntansi (*Period Lock*). |
| **Branch / Store Manager (Supervisor)** | Cabang Tertentu (*Assigned Branch*) | • Memiliki `supervisor_pin` untuk otorisasi aksi berisiko di POS (Void, Refund, No-Sale Drawer Pop, Diskon Khusus).<br>• Menyetujui Berita Acara penyesuaian stok dan transfer antar-cabang. |
| **Cashier / Front-Desk Staff** | Terminal POS Cabang Aktif | • Akses terbatas pada terminal penjualan kasir, buka/tutup shift kasir (*Blind Cash Count*), dan riwayat nota shift aktif.<br>• Dilarang mengakses laporan laba rugi, HPP bahan baku, atau jurnal akuntansi. |
| **Warehouse / Logistics Staff** | Gudang & Stok Cabang Aktif | • Akses terbatas pada penerimaan barang (GRN), pencatatan mutasi stok, dan pengepakan pesanan.<br>• Penyesuaian stok minus bernilai besar wajib persetujuan Supervisor. |
| **Finance / Accounting Staff** | Modul Keuangan & Pembukuan | • Mengelola kas & bank, rekonsiliasi, penagihan piutang, dan pelaporan pajak.<br>• Dilarang menghapus jurnal akuntansi terposting (hanya jurnal pembalik). |
| **Customer / Pembeli** | Storefront Publik & Portal Pesanan | • Terisolasi mutlak via IDOR Shield (`auth:customer` + nomor WhatsApp terverifikasi OTP).<br>• Hanya dapat melihat pesanan dan riwayat transaksi miliknya sendiri. |

---

## 5. Sistem Limitasi Langganan, Mitigasi Downgrade (Auto-Gating), & Auto-Reactivation

### 5.1 Transparansi Limitasi & Indikator Kuota pada UI
Setiap batasan kuota fitur dan kapasitas (produk, bahan baku, resep BOM, cabang outlet, staf, storage, kuota transaksi bulanan, kuota WhatsApp, medsos blast) wajib ditampilkan secara jelas, transparan, dan tidak ambigu pada antarmuka terkait:
- **Visual Progress Bar & Meter Kuota**: Menampilkan perbandingan pemakaian riil vs kuota paket aktif (misal: `10 / 50 Produk Digunakan (20%)`).
- **Color-Coded Status**: Hijau (0–74%), Amber/Warning (75–89%), Merah/Alert (90–100%) dengan tombol pemicu cepat `[ Upgrade Paket ]`.
- **Informasi Sederhana & Bernas**: Teks status kuota disajikan ringkas tanpa basa-basi (*glanceable*).

### 5.2 Mitigasi Downgrade Plan & Masa Langganan Berakhir (Exceeded Quota Graceful Degradation)
Skenario operasional yang sering terjadi: Pengguna saat berlangganan plan **Prestige** (*Unlimited Products*) mengimpor 1.000 produk. Pada bulan berikutnya, pengguna tidak memperpanjang langganan atau beralih ke plan **Standard** (kuota maks. 50 produk) atau **Free** (kuota maks. 10 produk).

Sistem menerapkan arsitektur mitigasi non-destruktif:

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                             SIKLUS MITIGASI DOWNGRADE PRODUK                                │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. PRINSIP NO DATA PUNISHMENT                                                               │
│    • Data produk ke-11 s/d 1.000 TIDAK PERNAH DIHAPUS oleh database.                       │
│    • HPP, resep BOM, harga modal, histori penjualan, dan barcode tetap utuh 100%.          │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 2. AUTO-GATING AKSES PENJUALAN (POS & STOREFRONT)                                           │
│    • Hanya N produk terawal (sesuai kuota plan aktif, misal 50 produk pertama / prioritas)  │
│      yang berstatus AKTIF dan dapat dijual di Terminal Kasir POS & Toko Online Storefront.  │
│    • Produk selebihnya (produk ke-51 s/d 1.000) otomatis berstatus SUSPENDED BY PLAN        │
│      (Terkunci Limitasi Paket) dan disembunyikan otomatis dari katalog POS & Toko Online.   │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 3. PENGALAMAN PENGGUNA PADA UI DASHBOARD PRODUK (/products)                                 │
│    • Produk terkunci tetap muncul di tabel produk merchant dengan tanda gembok Lucide lock  │
│      dan badge abu-abu/amber: "Terkunci (Limitasi Plan Standar)".                           │
│    • Tombol Tambah Produk Baru di-disable dengan tooltip/banner penjelasan lugas.           │
│    • Merchant dapat mengatur prioritas produk aktif (menukar produk aktif dalam batas kuota)│
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 4. AUTO-REACTIVATION SAAT BERLANGGANAN KEMBALI                                              │
│    • Begitu merchant memperpanjang/upgrade langganan (Webhook TriPay status = 'PAID'),       │
│      sistem otomatis mengeksekusi Auto-Unlock: seluruh produk yang suspended seketika       │
│      aktif kembali di POS & Toko Online tanpa perlu konfigurasi manual satu per satu.       │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

### 5.3 Implementasi Teknis Auto-Gating & Reactivation
1. **Query Scoping di POS & Storefront Service**:
   ```php
   // Katalog POS & Storefront hanya mengambil produk yang aktif dan dalam batas kuota paket
   $activeLimit = EntitlementService::getProductLimit($business);
   $products = Product::where('business_id', $business->id)
       ->where('is_active', true)
       ->orderBy('priority_order', 'asc')
       ->orderBy('created_at', 'asc')
       ->limit($activeLimit === -1 ? null : $activeLimit)
       ->get();
   ```
2. **Event-Driven Auto-Unlock**:
   - Event `SubscriptionUpgraded` / `SubscriptionRenewed` memicu `UnlockSuspendedResourcesJob` untuk membersihkan flag pembatasan dan me-refresh cache katalog POS di Redis.

---

## 6. Pusat Pelacakan Storage & Database Footprint, Data Pruning & Preview Sebelum Hapus

Untuk menjaga performa basis data tetap kencang dan memberikan transparansi kuota penyimpanan per akun Owner:

### 6.1 Pelacakan Komprehensif (Storage & DB Footprint Tracking)
Sistem melacak penggunaan ruang penyimpanan secara real-time yang bersumber dari:
1. **File & Media Penyimpanan Cloud**:
   - Foto katalog produk dan gambar varian.
   - Lampiran bukti pembayaran transfer bank & kuitansi supplier.
   - Foto identitas/KTP karyawan, foto Berita Acara opname stok, dan logo bisnis.
   - File dokumen laporan bulanan PDF / cetak struk tersimpan.
2. **Log Audit & Aktivitas Pengguna**:
   - Tabel `audit_logs` (rekaman kronologis mutasi, void kasir, login, perubahan role).
3. **Log Komunikasi & Webhook**:
   - Tabel `whatsapp_logs`, `notification_logs`, log dispatch media sosial.
4. **Data Riwayat Transaksi Lama**:
   - Rekam jejak draf transaksi kedaluwarsa dan keranjang belanja usang.

### 6.2 Antarmuka Dasbor Manajemen Penyimpanan (`/settings/storage`)
- **Bento Meter Kapasitas**: Menampilkan visual kuota per Owner (misal: `1.2 GB / 10 GB terpakai`).
- **Breakdown Kategori Interaktif**:
  - *Media & Foto:* 850 MB (70%)
  - *Log Audit & Keamanan:* 210 MB (17%)
  - *Log Notifikasi & WhatsApp:* 95 MB (8%)
  - *Data Histori Usang:* 45 MB (5%)

### 6.3 Pusat Pembersihan Data Mandiri (*Data Pruning Hub*) dengan Preview Transparan
Pemilik bisnis (Owner) dapat membersihkan data log lama dan file media yatim (*orphan media*) untuk menghemat kuota penyimpanan:

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                           ALUR DATA PRUNING & PREVIEW SEBELUM HAPUS                         │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. FILTER PILIHAN PRUNING                                                                   │
│    Owner memilih jenis pembersihan:                                                         │
│    • Log Audit Aktivitas > 90 hari                                                          │
│    • Log Pengiriman WhatsApp / Notifikasi > 60 hari                                         │
│    • File Gambar Produk yang tidak lagi terhubung ke katalog (Orphan Images)                │
│    • Draf keranjang belanja kedaluwarsa > 30 hari                                           │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 2. MODAL SHEET PREVIEW SEBELUM EKSEKUSI (DATA PRUNING PREVIEWER)                            │
│    Sistem menampilkan Modal Sheet Full-Size XXL berisi:                                     │
│    • Total baris data / file yang akan dihapus (misal: 14.250 record & 120 file).           │
│    • Estimasi ruang penyimpanan yang berhasil dihemat (misal: 145 MB dibebaskan).           │
│    • Rentang tanggal data yang masuk cakupan pembersihan (misal: 01 Jan 2025 - 31 Des 2025).│
│    • Tabel sampel 10 data teratas yang akan dibersihkan untuk verifikasi visual.            │
│    • Pesan Penenang Jiwa (No-Panic Microcopy):                                              │
│      "Tenang: Pembersihan log lama tidak akan pernah menghapus data transaksi penjualan,    │
│       nota kasir, faktur invoice, atau laporan keuangan pembukuan Anda."                    │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 3. KONFIRMASI DUA LANGKAH & EKSEKUSI AMAN                                                   │
│    • Tombol konfirmasi [ Bersihkan Data Sekarang ] dengan otorisasi PIN/Password Owner.     │
│    • Eksekusi pembersihan berjalan via Background Queue Job (Chunking 500 rows/batch)       │
│      agar tidak mengunci tabel operasional database.                                        │
│    • Kapasitas storage ter-refresh otomatis seketika setelah job selesai.                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 7. Ringkasan Prinsip Implementasi Rekayasa
1. **Konsolidasi, Bukan Fragmentasi:** Gabungkan halaman yang mengelola entitas sama ke dalam antarmuka berbasis Tab / Segmented Control.
2. **Asinkron untuk Layanan Eksternal:** Seluruh integrasi WhatsApp, Email, Ekspedisi, dan PDF wajib melalui antrean latar belakang (*Queue*).
3. **Aman Secara Bawaan (*Secure by Default*):** Terapkan proteksi fraud internal di level domain logic, bukan hanya menyembunyikan tombol di antarmuka pengguna.
4. **Resilience & Fallback:** Sediakan tombol manual 1-klik ramah pengguna setiap kali otomasi jaringan eksternal mengalami kendala.
5. **No Data Punishment & Graceful Degradation:** Jangan pernah menghapus data tenant saat masa langganan berakhir; terapkan auto-gating pada kanal penjualan dan auto-reactivation saat pembayaran diperpanjang.
6. **Transparansi Storage & Pruning Preview:** Sediakan tracking penyimpanan menyeluruh dan wajibkan dialog preview transparan sebelum eksekusi pembersihan data log/media.
