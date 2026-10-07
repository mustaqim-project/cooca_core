# Panduan Induk Sistem COOCA ID & POS (System Guide)

> **Dokumentasi Tingkat Tertinggi (Layer 3: Curated Master System Manual)**  
> **Target Pembaca:** Pemilik Usaha (Business Owner), Tim Produk, Pengembang Perangkat Lunak (Developer), dan AI Development Agent.  
> **Versi Sistem:** Cooca Enterprise ERP & POS v2.0 (Laravel 11 + DDD 33 Packages + Apple HIG Bento UI)  
> **Prinsip Utama:** `Clarity → Deference → Depth → Empathy → Simplicity`

---

## 📑 Daftar Isi Cepat

1. [Ikhtisar Sistem & Filosofi Desain](#1-ikhtisar-sistem--filosofi-desain)
2. [Konsep Inti & Arsitektur Multi-Tenancy](#2-konsep-inti--arsitektur-multi-tenancy)
3. [Panduan Pemilik Usaha (Business Owner Operations Manual)](#3-panduan-pemilik-usaha-business-owner-operations-manual)
   - [3.1 Menentukan Modal & Harga Jual Ilmiah (Costing & HPP)](#31-menentukan-modal--harga-jual-ilmiah-costing--hpp)
   - [3.2 Operasional Kasir Harian (Terminal Kasir POS)](#32-operasional-kasir-harian-terminal-kasir-pos)
   - [3.3 Manajemen Stok & Penerimaan Bahan (Gudang & GR)](#33-manajemen-stok--penerimaan-bahan-gudang--gr)
   - [3.4 Mengembangkan Kanal Penjualan Online (Storefront)](#34-mengembangkan-kanal-penjualan-online-storefront)
   - [3.5 Pembukuan Otomatis Tanpa Pusing Akuntansi (Keuangan)](#35-pembukuan-otomatis-tanpa-pusing-akuntansi-keuangan)
   - [3.6 Manajemen Katalog Produk, Resep (BOM), & Modifiers (Varian)](#36-manajemen-katalog-produk-resep-bom--modifiers-varian)
   - [3.7 Manajemen Bahan Baku, Pemasok, & Layanan Jasa Bebas Stok](#37-manajemen-bahan-baku-pemasok--layanan-jasa-bebas-stok)
   - [3.8 Toko Online (Storefront Hub) & Website Landing Page Studio](#38-toko-online-storefront-hub--website-landing-page-studio)
   - [3.9 Saluran WhatsApp Resmi (WhatsApp Cloud API Meta)](#39-saluran-whatsapp-resmi-whatsapp-cloud-api-meta)
   - [3.10 Pengelolaan Media Sosial Terpadu (Meta, TikTok & LinkedIn)](#310-pengelolaan-media-sosial-terpadu-meta-tiktok--linkedin)
   - [3.11 Kepatuhan Pajak UMKM & Penggajian Karyawan (HRM & Tax Compliance)](#311-kepatuhan-pajak-umkm--penggajian-karyawan-hrm--tax-compliance)
   - [3.12 Analitik Bisnis & Tren Pertumbuhan (Analytics Suite)](#312-analitik-bisnis--tren-pertumbuhan-analytics-suite)
   - [3.13 Pusat Otorisasi Dokumen (MAR Engine)](#313-pusat-otorisasi-dokumen-mar---maker-approver-releaser)
   - [3.14 Portal Karyawan & Presensi Mandiri (Staff Personal Attendance & Workstation Hub)](#314-portal-karyawan--presensi-mandiri-staff-personal-attendance--workstation-hub)
   - [3.15 Integrasi Model Context Protocol & AI Gateway (MCP Server)](#315-integrasi-model-context-protocol--ai-gateway-mcp-server)
4. [Panduan Rekayasa Developer & AI Agent (Engineering Blueprint)](#4-panduan-rekayasa-developer--ai-agent-engineering-blueprint)
   - [4.1 Struktur 33 Domain Packages DDD](#41-struktur-33-domain-packages-ddd)
   - [4.2 Aturan Scoping Tenant & Proteksi Keamanan](#42-aturan-scoping-tenant--proteksi-keamanan)
   - [4.3 Mesin Otomasi Latar Belakang (Auto-Journal & Auto-Stock)](#43-mesin-otomasi-latar-belakang-auto-journal--auto-stock)
   - [4.4 Protokol Verifikasi & Kesiapan Produksi (100% Zero-Error Mandate)](#44-protokol-verifikasi--kesiapan-produksi-100-zero-error-mandate)
   - [4.5 Protokol Dokumentasi Berkelanjutan Simultan (AiWorkHistory.md + SYSTEM_GUIDE.md)](#45-protokol-dokumentasi-berkelanjutan-simultan-aiworkhistorymd--system_guidemd)
   - [4.6 Arsitektur Multi-Tenant WhatsApp Cloud API](#46-arsitektur-multi-tenant-whatsapp-cloud-api)
   - [4.7 Arsitektur Media Sosial Omnichannel (Meta, TikTok & LinkedIn UGC Post API)](#47-arsitektur-media-sosial-omnichannel-meta-tiktok--linkedin-ugc-post-api)
   - [4.8 Arsitektur Pre-Order & Batch Scheduling Dinamis Terparameterisasi](#48-arsitektur-pre-order--batch-scheduling-dinamis-terparameterisasi)
   - [4.9 Pusat Verifikasi Pemulihan Akun Administrator (Account Recovery Desk)](#49-pusat-verifikasi-pemulihan-akun-administrator-account-recovery-desk)
   - [4.10 Pusat Pengelolaan Katalog Paket Billing Platform (Billing Packages CMS)](#410-pusat-pengelolaan-katalog-paket-billing-platform-billing-packages-cms)
   - [4.11 Pusat Pengelolaan CMS Artikel, Taksonomi Kategori, & Cluster Konten (Admin Posts CMS)](#411-pusat-pengelolaan-cms-artikel-taksonomi-kategori--cluster-konten-admin-posts-cms)
   - [4.12 Pusat Pengaturan Platform & Integrasi Layanan Terpusat (Admin Settings & 5-Service Hub)](#412-pusat-pengaturan-platform--integrasi-layanan-terpusat-admin-settings--5-service-hub)
   - [4.13 Arsitektur Blueprint Tier Pricing v2.3, Multi-Branch & Tax Compliance Engine](#413-arsitektur-blueprint-tier-pricing-v23-multi-branch--tax-compliance-engine)
   - [4.14 Arsitektur Audit & Proteksi Fraud Internal serta Notifikasi Sistem Terpadu (UI, Email, WhatsApp)](#414-arsitektur-audit--proteksi-fraud-internal-serta-notifikasi-sistem-terpadu-ui-email-whatsapp)
   - [4.15 Arsitektur POS Hardware, ESC/POS Thermal Printer, Cash Drawer Safety & Local Agent Bridge](#415-arsitektur-pos-hardware-escpos-thermal-printer-cash-drawer-safety--local-agent-bridge)
   - [4.16 Cetak Biru Penataan 6-Hub Modul & Rekomendasi Optimasi Performa End-to-End](#416-cetak-biru-penataan-6-hub-modul--rekomendasi-optimasi-performa-end-to-end)
   - [4.17 Arsitektur Limitasi Subscription, Downgrade Auto-Gating, Pelacakan Storage & Data Pruning Previewer](#417-arsitektur-limitasi-subscription-downgrade-auto-gating-pelacakan-storage--data-pruning-previewer)
   - [4.18 Hardening Keamanan Siber POS Hardware, SSRF Guardrail & Otorisasi PIN (PRD-17)](#418-hardening-keamanan-siber-pos-hardware-ssrf-guardrail--otorisasi-pin-prd-17)
   - [4.19 Hardening Komprehensif 7 Modul View & Remediasi 20 Sektor Industri (PRD-18)](#419-hardening-komprehensif-7-modul-view--remediasi-20-sektor-industri-prd-18)
   - [4.20 Arsitektur Shell Navigasi Sidebar Bento Apple HIG, Invisible Hover Bridge & RBAC Paritas](#420-arsitektur-shell-navigasi-sidebar-bento-apple-hig-invisible-hover-bridge--rbac-paritas)
   - [4.21 Arsitektur Otomasi Pengingat Termin Pembayaran Pelanggan (WhatsApp & Email Tri-Channel, Scheduler 08:30 WIB & Anti-Spam Guard)](#421-arsitektur-otomasi-pengingat-termin-pembayaran-pelanggan-whatsapp--email-tri-channel-scheduler-0830-wib--anti-spam-guard)
   - [4.22 Arsitektur COOCA AI Digital Company (Autonomous Workforce, Executive Hierarchy C-Level, Multi-Provider BYOAI, Maker-Checker Gate & Bento AI Office)](#422-arsitektur-cooca-ai-digital-company-autonomous-workforce-executive-hierarchy-c-level-multi-provider-byoai-maker-checker-gate--bento-ai-office)
   - [4.23 Arsitektur Hardening Modul Gudang & Pemasok (Bento Apple HIG, IDOR Precedence Shield, N-Tier Cycle Traversal, & 100% i18n Parity - PRD-32)](#423-arsitektur-hardening-modul-gudang--pemasok-bento-apple-hig-idor-precedence-shield-n-tier-cycle-traversal--100-i18n-parity---prd-32)
   - [4.24 Arsitektur COOCA Online Store & Marketplace Production-Grade (Shopee-Standard Workflow, TriPay Auto-Journaling, AWB Tracking & Verified Reviews)](#424-arsitektur-cooca-online-store--marketplace-production-grade-shopee-standard-workflow-tripay-auto-journaling-awb-tracking--verified-reviews)
   - [4.25 Arsitektur Universal Multi-Tenant Model Context Protocol (MCP) Server & Multi-Provider AI Adapter Hub](#425-arsitektur-universal-multi-tenant-model-context-protocol-mcp-server--multi-provider-ai-adapter-hub)

---

## 1. Ikhtisar Sistem & Filosofi Desain

Cooca adalah sistem operasi bisnis terpadu (*All-in-One Business OS*) yang dirancang khusus untuk memadukan kekuatan ERP kelas enterprise dengan kesederhanaan antarmuka yang sangat ramah pengguna (*ultra user-friendly*).

### Filosofi Desain "Apple Human Interface Guidelines & Bento Grid UI"
Aplikasi ini dirancang untuk dapat dioperasikan secara percaya diri oleh **generasi Boomers (usia 50–65+ tahun) dan milenial akhir yang gaptek (tidak paham teknis)**:
* **Antarmuka Tanpa Panduan (*Zero-Manual UI*):** Saat pengguna membuka aplikasi, mereka langsung paham apa yang harus dilakukan tanpa perlu membaca buku manual panjang.
* **Penyajian Sederhana, Padat, dan Jelas (*Anti-Clutter & Extreme Simplicity*):**
  - **Prinsip 3-Detik (*3-Second Glanceability*):** Maksud halaman, metrik utama, dan aksi prioritas langsung dipahami tanpa membebani pikiran pengguna.
  - **Bebas Dinding Teks (*Zero Wall-of-Text*):** Menghilangkan paragraf bertele-tele, helper text berulang, atau kartu penuh teks panduan yang tidak esensial.
  - **Progressive Disclosure:** Rincian teknis kompleks disimpan rapi di dalam modal sheet / drawer rincian (*Master-Detail*), menjaga layar operasional utama tetap bersih, lapang, dan menenangkan.
* **Integritas Faktual & Anti-Hiperbola (*Anti-Hyperbole Data Integrity*):**
  - Dilarang keras menyajikan teks, label, metrik, atau slogan yang dilebih-lebihkan yang tidak sesuai dengan spesifikasi teknis atau data riil sistem (larangan klaim fiktif seperti *"AI Quantum 99.999%"*, *"Algoritma Kecepatan Cahaya"*, atau metrik estimasi palsu).
  - Seluruh angka penjualan, sisa stok, status perangkat keras, dan waktu proses mencerminkan kalkulasi database aktual dengan format angka presisi (`tabular-nums`).
* **Perlindungan Kredensial Sensitif di Antarmuka (*Zero Plaintext Credential Exposure*):**
  - Seluruh kredensial rahasia (API Keys, Secret Tokens, Private Keys, Password, PIN Kasir, Webhook Secrets) dilarang tampil polos (*plain text*) di antarmuka publik/operasional.
  - Form pengaturan integrasi wajib menerapkan masking keamanan (`••••••••••••••••` atau `sk-live-••••••••1234`) dan properti model Eloquent wajib menyertakan `$hidden`.
* **Ergonomi Jempol, Tata Letak Anti-Pecah & Aksesibilitas Visual:**
  - **Zero Horizontal Overflow:** Arsitektur fluid container (`w-full max-w-full min-w-0 truncate`) yang menjamin tidak ada pergeseran layar ke samping pada smartphone 360px–430px.
  - **Matriks Tipografi Dinamis Lintas Perangkat:** Skala font terkalibrasi presisi untuk Mobile, Tablet, dan Desktop (acuan resmi di `docs/prompt.md` dan `AGENTS.md`).
  - Touch targets tombol aksi utama berukuran minimal **48px hingga 52px** agar tidak meleset saat ditekan di layar ponsel.
  - Ukuran font kolom input minimal **16px** (`text-[16px] sm:text-[14px]`) untuk mencegah auto-zoom browser iOS Safari yang merusak tampilan.
  - Sudut membulat organik (*squircle* `rounded-[20px]`) dan border hairline lembut yang memanjakan mata.
  - **Aksesibilitas Kontras Dropdown Form Controls pada Dark Mode:** Seluruh elemen `<select>`, `<option>`, dan `<optgroup>` wajib menerapkan `color-scheme: dark;` dan latar belakang gelap Apple (`#1C1C1E`) dengan teks putih (`#FFFFFF`) saat Dark Mode aktif, mengeliminasi cacat kontras *white-on-white* di mana opsi tidak terlihat pada peramban Chromium/Windows/Firefox/Safari.
* **Format Ribuan Otomatis:** Mengetik nominal uang otomatis menghasilkan tanda pemisah ribuan (`Rp 150.000`), mencegah kekeliruan mengetik nol berlebih.
* **Standar Modal Pop-Up & Form Full-Size Lintas Multi-Device:**
  - Operasi Create, Show (Detail), Edit, dan Form Input Transaksi pada seluruh halaman index mengadopsi arsitektur **Modal-First Full-Size Canvas** tanpa berpindah halaman (*Zero Navigation Jumps*), menjaga filter dan posisi pagination tetap utuh. Dilarang modal form sempit (`max-w-md`/`max-w-lg`).
  - **Desktop (>= 1024px):** Menggunakan **Full Layout XXL Centered Bento Dialog** (`w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] mx-auto rounded-[24px] max-h-[92vh]`) yang lapang, mampu menampung grid bento multi-kolom (8 kolom form/tabel utama + 4 kolom metrik ringkasan live) tanpa berdesakan.
  - **Tablet (640px – 1023px):** Centered Responsive Bento Modal (`max-w-3xl` s/d `max-w-4xl rounded-[22px] max-h-[92vh]`) dengan tata letak 2 kolom seimbang dan touch target 44px–48px.
  - **Mobile (< 640px):** Apple Full-Responsive Bottom Sheet (`w-full inset-x-0 bottom-0 rounded-t-[28px] h-full max-h-[95vh]`) meluncur dari bawah dengan lebar penuh (100% viewport), indikator grab bar Apple, input font minimal 16px anti-auto-zoom iOS, dan sticky bottom action bar menempel jempol (48px–52px).
* **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*):** Di setiap aksi penting atau dialog konfirmasi, sistem selalu menyertakan pesan penenang menggunakan font icon Lucide `info` (bebas emoji):  
  *“Tenang: Riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan.”*

---

## 2. Konsep Inti & Arsitektur Multi-Tenancy

* **Multi-Tenancy Berbasis Shared Database:** Seluruh bisnis berbagi basis data yang sama namun terisolasi secara mutlak di tingkat query database menggunakan `Context::requireBusiness()` dan foreign key `business_id`.
* **Identitas Global Customer:** Pelanggan storefront publik memiliki satu akun global (`GlobalCustomer`) yang dapat digunakan untuk berbelanja di berbagai toko UMKM berbeda, dengan keranjang belanja (`CustomerCart`) yang tetap terisolasi penuh per tenant.
* **Jaminan Integritas Finansial Non-Destruktif:** Rumus subtotal, pajak PPN, diskon, HPP, saldo kas berjalan, dan keseimbangan jurnal akuntansi bersifat mutlak dan tidak boleh diubah secara destruktif.

---

## 3. Panduan Pemilik Usaha (Business Owner Operations Manual)

### 3.1 Menentukan Modal & Harga Jual Ilmiah (Costing & HPP)
* **Kapan Digunakan?** Saat Anda ingin meluncurkan menu baru, membuat produk kemasan, atau mengevaluasi apakah harga jual saat ini masih memberikan keuntungan layak di tengah kenaikan harga pasar.
* **Cara Kerjanya:**
  1. Buka menu **Kalkulator HPP**.
  2. Masukkan bahan baku yang digunakan beserta takarannya (misal: *Kopi 18 gram, Susu 120 ml, Cup 1 pcs*).
  3. Masukkan biaya upah kerja karyawan per porsi dan estimasi biaya listrik/alat.
  4. Tentukan target keuntungan (misal: *Margin Kotor 60%*).
  5. Sistem langsung menyajikan rekomendasi harga jual resmi dan titik impas (**BEP**).
* **Dampak ke Bisnis:** Anda dapat langsung menekan tombol `[ Terapkan ke Produk ]` agar kasir dan toko online langsung menggunakan harga tersebut secara otomatis.

### 3.2 Operasional Kasir Harian (Terminal Kasir POS)
* **Kapan Digunakan?** Setiap hari selama jam operasional toko berlangsung untuk melayani antrean pembeli di kasir.
* **Alur Standar Kasir:**
  1. **Buka Shift:** Masukkan modal awal kas kecil di laci kasir (*Float Cash*).
  2. **Pilihan Saluran Penjualan F&B:** Kasir memilih saluran via Segmented Pill Bar (`Dine In`, `Takeaway`, `GoFood`, `GrabFood`, `ShopeeFood`). Jika saluran online delivery dipilih, kasir memasukkan nomor referensi pesanan pengemudi aplikasi luar (misal: `GF-8849201`). Harga produk langsung menyesuaikan saluran terpilih.
  3. **Transaksi Cepat & Paket Kombo:** Sentuh foto produk atau paket kombo di katalog bento, pilih topping/level pedas (modifier), dan pilih metode bayar (Tunai, QRIS, atau Kasbon).
  4. **Cetak Struk & Kirim WA:** Tekan `[ 📄 Simpan & Cetak Struk ]`. Printer thermal mencetak struk fisik seketika (mencantumkan saluran & nomor referensi aplikasi), pesanan dapur KDS menampilkan lencana warna kontras, dan WhatsApp pelanggan menerima tautan struk digital resmi.
  5. **Tutup Shift:** Hitung uang fisik di laci kasir di akhir hari. Sistem membandingkannya dengan catatan sistem dan mencatat selisih kas secara transparan.
* **Isolasi Konteks 25 Sektor Industri (*Zero Feature Leakage*):**
  - **Retail & Non-Food:** Antarmuka kasir secara otomatis mengeliminasi pemilih saluran ojol, nomor meja, order QR, dan KDS dapur. Tombol detail item menggunakan label "Detail & Catatan Item" (bukan dosis obat).
  - **F&B Restoran:** Menampilkan badge nomor meja tamu (`Meja {{ table_or_reference }}`) pada kolom transaksi kasir, detail pesanan modal sheet, dan struk kasir.
  - **Bengkel / Otomotif:** Menyajikan pemicu kartu dan modal SPK Kendaraan (nomor polisi, odometer, model, dan mekanik bertugas).
  - **Laundry Kiloan:** Menyajikan pemicu timbangan cucian (berat kg, nomor rak/loker, dan estimasi waktu selesai).
  - **Farmasi / Apotek:** Mengaktifkan mode apotek dengan pelacakan nomor batch dan tanggal kadaluarsa (ED) pada item obat.
* **Dampak ke Bisnis:** Kasir tidak bisa membatalkan transaksi (void) atau mengambil uang secara diam-diam karena tindakan berisiko dilindungi **PIN Supervisor**. Pemotongan stok paket kombo merekursi seluruh komponen anak secara atomik tanpa duplikasi. Tampilan antarmuka 100% fokus pada proses bisnis spesifik UMKM tanpa kebocoran fitur industri lain.

### 3.3 Manajemen Jaringan Cabang (Settings Hub) & Gudang Logistik (Warehouse Hub)
* **Kapan Digunakan?** 
  - **Manajemen Cabang & Toko (`/settings?tab=branches`):** Saat Anda ingin menambah, mengubah, atau mengelola titik fisik cabang/outlet, menentukan Cabang Utama (Primary Branch) untuk basis profil usaha dan toko online, menyetel koordinat GPS satelit & radius geofence presensi staf (10–10.000 meter), serta memetakan zona waktu dan area kurir Biteship.
  - **Manajemen Gudang Logistik (`/warehouse`):** Saat Anda mengelola lokasi fisik penyimpanan barang persediaan (Gudang Utama, Gudang Belakang, Gudang Dingin, Rak Dapur) yang dinaungi oleh cabang induk masing-masing, menerima pasokan PO supplier via Goods Receipt (GR), atau melakukan penyesuaian stok fisik (Stock Adjustment).
* **Fitur Utama & Keunggulan Operasional:**
  1. **Pemisahan Tegas Cabang vs Gudang (Arsitektur 1-Cabang Banyak-Gudang):** 
     - **Cabang/Outlet** adalah entitas bisnis fisik tempat transaksi kasir, presensi karyawan, dan titik penjemputan pesanan. Dikelola terpusat di **Settings Hub (`/settings?tab=branches`)**.
     - **Gudang (`type = 'warehouse'`)** adalah entitas logistik persediaan barang. Satu cabang fisik dapat menaungi beberapa gudang anak (`parent_id = $branch->id`), misalnya Gudang Display Toko, Gudang Belakang, dan Gudang Dingin.
     - Halaman `/warehouse` terfokus 100% untuk penambahan dan pengelolaan gudang logistik, dengan tautan cepat langsung ke Settings Hub jika ingin menambah cabang baru.
  2. **Deteksi GPS & Integrasi Kurir Ekspedisi Otomatis:** Tombol `[📍 Deteksi Lokasi Saya]` mengambil koordinat satelit instan, melakukan reverse-geocoding alamat Indonesia otomatis, dan menyambungkan kode area kurir Biteship tanpa salah ketik manual.
  3. **Absensi Berpagar Geofence Presisi:** Menentukan radius toleransi absensi (10 s/d 10.000 meter) agar presensi karyawan di portal staf terkunci pada titik fisik cabang terkait.
  4. **Penerimaan Barang (*Goods Receipt* / GR) dari PO:** Mencocokkan surat jalan supplier dengan Purchase Order (PO). Stok fisik langsung bertambah, HPP modal diperbarui via Moving Average, dan hutang supplier (AP) tercatat otomatis di menu keuangan.
  5. **Penyesuaian Stok Fisik Cepat & Analisis Dampak:** Mengoreksi selisih fisik riil langsung dari kartu bento gudang dengan live kalkulasi delta unit dan selisih valuasi rupiah HPP.
  6. **Proteksi Integritas Relasional:** Cabang utama (`is_primary`) dan cabang yang masih menaungi anak gudang aktif dilindungi dari penghapusan tidak sengaja. Cabang dengan jejak transaksi historis dinonaktifkan secara aman (*non-destructive deactivation*).
* **Dampak ke Bisnis:** Struktur organisasi fisik usaha tertata rapi, tim gudang tidak dibebani pengaturan entitas cabang, relasi stok per cabang transparan, dan pengiriman kurir online berjalan presisi dari cabang utama.

### 3.4 Mengembangkan Kanal Penjualan Online (Storefront)
* **Kapan Digunakan?** Membagikan link toko online Anda (`cooca.id/nama-toko-anda` atau alias `cooca.id/b/nama-toko-anda`) ke media sosial, Instagram Bio, atau status WhatsApp.
* **Cara Kerjanya:**
  - Pembeli memilih produk, memasukkan ke keranjang, dan melakukan checkout mandiri.
  - Pembeli mentransfer dana dan mengunggah foto bukti bayar.
  - Anda menerima notifikasi di handphone dan cukup menekan `[ ✅ Verifikasi & Proses Pesanan ]`.
* **Dampak ke Bisnis:** Toko Anda melayani pesanan 24 jam non-stop tanpa membuat pembeli menunggu balasan chat yang lama.

### 3.5 Pembukuan Otomatis Tanpa Pusing Akuntansi (Keuangan)
* **Kapan Digunakan?** Setiap saat Anda ingin melihat posisi kesehatan uang toko (Laba Bersih, Uang di Kasir, Saldo di Bank, Piutang Pembeli, dan Mutasi Gateway).
* **Keunggulan Cooca:** Anda **tidak perlu mengerti debit, kredit, atau kode akun**. Sistem secara otonom menjalankan `AutoJournalService` setiap kali transaksi kasir atau pembayaran hutang terjadi.
* **Fitur Utama Pembukuan & Rekonsiliasi Terpadu:**
  - **Konsolidasi Omnichannel Laba Rugi:** Laporan Laba Rugi secara otomatis menggabungkan seluruh saluran penjualan: Kasir POS, Faktur Penjualan (Invoicing), dan Toko Online Storefront (`CommerceOrder`). Pendapatan kotor, ongkos kirim, diskon, dan HPP aktual berbasis snapshot modal tersaji transparan.
  - **Arus Kas Akurat Bebas Hitung Ganda (*Zero Double-Counting*):** Penerimaan kas dari pesanan online dan POS diisolasi dari transaksi kas langsung generik, menjamin saldo kas akhir di laporan arus kas 100% konsisten dengan fisik riil.
  - **Pemisahan Fisik Laci Kasir POS vs Digital Gateway:** Pada saat kasir menutup shift, sistem secara ketat membedakan omzet tunai (`cash_sales`) dengan penjualan non-tunai/gateway (`gateway_sales`). Jumlah uang fisik yang diharapkan di laci kasir (*expected cash*) murni dihitung dari uang modal awal ditambah penjualan tunai riil, sehingga kasir tidak panik mencari fisik uang transaksi digital QRIS.
  - **Rekonsiliasi Pencairan Gateway (*Gateway Settlement Hub*):** Modul rekonsiliasi dana pencairan TriPay di menu Keuangan. Saat dana dari TriPay cair ke rekening bank operasional UMKM, sistem mencatat jurnal ganda secara otomatis: mendebit Rekening Bank (`1-1002`), mendebit Beban MDR Gateway (`6-6003`), dan mengkredit Akun Kliring Gateway (`1-1005`), sekaligus menandai transaksi POS dan toko online terkait sebagai *reconciled*.
  - **Audit Trail Callback Webhook & Tombol Failover Sinkronisasi:** Jejak seluruh webhook gateway tercatat pada `payment_gateway_callback_logs` dan jika ada notifikasi gateway terlambat, kasir/pemilik dapat menekan tombol *"Cek & Sinkronkan Status TriPay"* untuk verifikasi instan.
 
### 3.6 Manajemen Katalog Produk, Resep (BOM), & Modifiers (Varian)
* **Kapan Digunakan?** Mengelola seluruh daftar dagangan toko, baik barang jadi (retail/F&B), bahan mentah, jasa/layanan, formula resep produksi (BOM), paket kombo bundling, hingga pilihan varian/topping.
* **Fitur & Keunggulan Alur Kerja:**
  - **Apple Segmented Control:** Berpindah seketika antara *Barang Fisik (Katalog)*, *Jasa & Layanan*, dan *Varian & Modifiers* melalui tab tersegmentasi yang bersih dan intuitif.
  - **Full Layout XXL Modal (Zero Navigation Jump):** Menambah atau mengedit produk dilakukan langsung di jendela pop-up XXL 12-kolom terpadu tanpa pernah meninggalkan daftar katalog atau mereset filter.
  - **Paket Kombo / Bundling F&B & Retail:** Konfigurasi paket kombo (`is_bundle = true`) dengan dynamic child item repeater, penentuan kuantitas anak, proteksi circular reference, live kalkulasi estimasi total modal HPP, dan estimasi nilai normal.
  - **Multi-Harga Saluran POS (F&B):** Kolom input penetapan harga khusus per kanal penjualan (Dine In, Takeaway, GoFood, GrabFood, ShopeeFood) untuk mengkompensasi komisi agregator.
  - **Inline Quick-Add AJAX `[ + ]`:** Menambah kategori atau satuan baru langsung dari samping dropdown tanpa reload halaman atau kehilangan data yang sedang diketik.
  - **Manajemen Kanal Terpadu:** Pengaturan visibilitas kasir POS, Surat Pesanan (Sales Order), Toko Online Storefront, dan Pre-Order dapat disesuaikan per produk dengan saklar instan.
  - **Resep Produksi (BOM) & Modifiers:** Setiap produk jadi dapat dihubungkan ke resep bahan baku sehingga stok gudang terpotong otomatis saat kasir memproses pesanan.
  - **Penenang Jiwa Saat Menghapus:** Dialog konfirmasi hapus selalu memberikan jaminan kepastian: riwayat nota kasir dan jurnal pembukuan masa lalu yang menggunakan produk tersebut tetap aman tersimpan.

### 3.7 Manajemen Bahan Baku, Pemasok, & Layanan Jasa Bebas Stok
* **Kapan Digunakan?** Mengelola seluruh rantai pasok bahan baku mentah (untuk usaha manufaktur/kuliner) serta tarif layanan jasa bebas stok (untuk usaha salon, bengkel, klinik, servis, dsb.).
* **Fitur & Keunggulan Alur Kerja:**
  - **Efisiensi Rendemen & Susut (Yield & Waste):** Menghitung harga pokok akuisisi riil per gram/ml secara matematis dengan memperhitungkan faktor susut bahan dan ongkos kirim.
  - **Triple Inline Quick-Add AJAX `[ + ]`:** Menambahkan Kategori Bahan, Satuan, dan Supplier Pemasok secara instan dari form bahan baku tanpa memuat ulang layar atau mereset draf isian.
  - **Layanan Jasa Bebas Stok (Selalu Siap Jual):** Layanan jasa tidak memerlukan stok gudang fisik dan dapat langsung dipanggil pada transaksi kasir POS atau faktur penjualan.
  - **Navigasi Terpadu Apple Segmented Control:** Berpindah mulus antara *Barang Fisik*, *Jasa & Layanan*, dan *Varian & Modifiers* dalam 1 ketukan.
  - **Zero-Navigation Jump pada Edit Layanan:** Pengeditan jasa menggunakan instant client-side modal tanpa reload parameter URL `?edit=<id>`.
  - **Penenang Jiwa Saat Menghapus:** Menjamin keamanan data historis masa lalu pada setiap dialog konfirmasi hapus bahan maupun jasa.

### 3.8 Toko Online (Storefront Hub) & Website Landing Page Studio
* **Kapan Digunakan?** Saat Anda ingin mempublikasikan profil bisnis digital, menampilkan etalase produk & layanan ke publik, menerima pesanan mandiri pelanggan via web, menetapkan ongkir kurir toko, serta mengelola reservasi meja/jadwal.
* **Fitur Utama & Keunggulan Operasional:**
  - **Bento Cockpit Pengaturan Toko:** Memantau visibilitas etalase, status direktori `/jelajah`, rekening aktif, serta mode transaksi (Langsung, Terjadwal, PO B2B, Reservasi).
  - **Integritas Saklar Non-Destruktif:** Setiap switch etalase dikawal input hidden deterministik agar status penonaktifan tersimpan sempurna tanpa merusak pengaturan lainnya.
  - **Dialog Konfirmasi Penenang Jiwa:** Hapus rekening pembayaran, hapus aturan ongkir, dan pembatalan reservasi tamu dilindungi dialog konfirmasi Apple Alert yang menjamin transaksi dan riwayat masa lalu tetap aman tercatat.
  - **Verifikasi Pembayaran 1-Klik:** Verifikasi bukti transfer pelanggan secara visual di modal Apple Alert dengan alokasi pemotongan stok otomatis yang sah di pembukuan.
  - **Aturan Ongkir Adaptif:** Menghitung ongkos kirim berbasis tarif flat, radius jarak (KM), dan promo bebas ongkir otomatis berdasarkan ambang belanja minimum.
  - **CMS Studio Landing Page 11 Bagian:** Mengatur identitas brand, warna aksen kustom, banner hero, ulasan pelanggan, galeri foto suasana, jam operasional, dan optimasi SEO Google dengan dukungan preset instan 25 industri.
  - **Pengalaman Halaman Publik Pelanggan (`/b/{slug}` & `business_landing.blade.php`):** 
    - **Apple HIG Bento Modal Architecture:** Modal Checkout (`max-w-5xl`), Reservasi (`max-w-4xl`), Request Order (`max-w-4xl`), dan Customer PO (`max-w-4xl`) mengadopsi tata letak responsif: Full-layout 2 kolom Bento Dialog di desktop (kolom kiri untuk input formulir/jadwal & kolom kanan untuk ringkasan biaya/metode bayar/submit) dan native Apple Bottom Sheet meluncur dari bawah di mobile (`items-end`, `rounded-t-[28px]`, grab bar halus).
    - **Anti-AI-Template & Tipografi Murni:** Menggunakan *Pure Typographic Overline* tanpa badge pill dekoratif, eliminasi ikon clutters (seperti sparkles), dan penggunaan kata kerja aksi padat (*Pesan*, *Reservasi*, *WhatsApp*).
    - **Ergonomi Aksesibilitas:** Seluruh input formulir berukuran minimal 16px di mobile anti-auto-zoom iOS, touch target 48px–52px, tombol mengambang WhatsApp terbebas dari fake unread dots, dan safe-area padding bawah (`pb-28 sm:pb-32 md:pb-28`).
    - **Anti-FOUC Theme Synchronizer:** Inisialisasi tema instan berbasis database CMS toko (`var isLandingDark`) yang mencegah kedipan kontras saat halaman dimuat.
    - **Model Referensi FnB & Katering (Dapur Sedap Rasa):** Melalui `DapurSedapRasaSeeder.php` (`/dapur-sedap-rasa`), platform mendemonstrasikan kapabilitas ganda operasional: Order Offline (Kasir POS, 12 Meja Dine-in Indoor/Outdoor/VIP, Takeaway) dan Pre-Order (PO) Online Katering (Nasi Box, Tumpeng, Prasmanan, kuota pesanan, batch schedule delivery, transfer bank BCA/Mandiri & QRIS).

### 3.9 Saluran WhatsApp Resmi (WhatsApp Cloud API Meta & Broadcast Hub)
* **Kapan Digunakan?** Saat Anda ingin mengirimkan struk kasir digital otomatis, faktur tagihan (invoice), kode OTP, notifikasi pesanan resmi, serta blast promosi massal langsung ke nomor WhatsApp pelanggan tanpa risiko blokir nomor.
* **Fitur Utama & Keunggulan Operasional:**
  - **Onboarding Mandiri 1-Klik (Meta Embedded Signup):** Merchant cukup menghubungkan akun WhatsApp Business Facebook mereka melalui jendela pop-up resmi Meta tanpa perlu konfigurasi token manual yang rumit.
  - **Identitas Bisnis Resmi & Verified Badge:** Menampilkan nama bisnis resmi (`verified_name`) dan centang hijau Meta di chat pelanggan, meningkatkan kepercayaan dan kredibilitas UMKM.
  - **Pengiriman Struk POS & Invoice Terstruktur:** Menggunakan template pesan resmi Meta yang disetujui, dilengkapi media gambar struk, rincian biaya, serta tombol tautan langsung ke struk digital interaktif publik (`/receipt/{order}`) tanpa login merchant.
  - **Penyusunan Broadcast Promosi Sadar Konteks (Context-Aware 20 Industri):** Modul komposer broadcast Bento XXL secara cerdas mendeteksi template sektor industri merchant (`fnb_*`, `service_workshop`, `service_laundry`, `mfg_*`, `service_contractor`, `retail_pharmacy`) dan menyajikan chip tag personal adaptif (`{meja}`, `{nopol}`, `{servis_terakhir}`, `{no_rak}`, `{berat_kg}`, `{no_spk}`, `{produk}`, `{proyek}`, `{termin}`, `{no_resep}`, `{poin}`, `{tier}`, `{bisnis}`) dengan live smartphone simulator WYSIWYG.
  - **Peringatan Kepatuhan Regulasi Farmasi (Meta Health Policy & BPOM):** Banner proteksi proaktif khusus apotek (`retail_pharmacy`) yang mencegah pemblokiran nomor akun WABA akibat promosi obat keras / antibiotik / obat resep.
  - **Peringatan Jam Istirahat Pelanggan (Quiet Hours 21:00–08:00 WIB):** Edukasi real-time waktu lokal merchant untuk mencegah pengiriman pesan di luar jam operasional wajar, menjaga skor kualitas nomor (*Meta Quality Rating*), dan mencegah laporan spam.
  - **Perlindungan Privasi Pelanggan (PII Masking):** Penyamaran nomor telepon pelanggan (`0812••••7890`) bagi staf non-owner pada tabel log pesan dan detail penerima broadcast, serta pembatasan tautan eksternal `wa.me` khusus untuk Owner.
  - **Notifikasi Otomatis & Pemantauan Status Transparan:** Status pengiriman pesan terperinci secara live (`terkirim`, `diterima`, `dibaca`, `gagal`) dengan pemantauan rating kualitas nomor (GREEN/YELLOW/RED) dan batas kuota pesan bulanan.

### 3.10 Pengelolaan Media Sosial Terpadu (Meta, TikTok & LinkedIn)
* **Kapan Digunakan?** Saat pemilik toko ingin mengelola dan mempublikasikan materi promosi, video produk, dan foto katalog ke Facebook Page, Instagram Bisnis, Threads, TikTok, dan LinkedIn secara serentak dari satu dashboard COOCA.
* **Fitur Utama & Keunggulan Operasional:**
  - **Koneksi Akun 1-Klik Resmi:** Menghubungkan akun Facebook, Instagram, Threads, TikTok, dan LinkedIn melalui dialog otorisasi OAuth 2.0 resmi (Meta Login for Business, TikTok Developer Platform, dan LinkedIn OpenID Connect) dengan pembaruan token otomatis (*auto-refresh*).
  - **Composer Omnichannel Terpadu:** Membuat 1 konten promosi dan mendistribusikannya ke berbagai akun media sosial sekaligus, dengan pratinjau langsung (*live preview*), kustomisasi caption spesifik per kanal, serta pemenuhan panduan Do's & Don'ts untuk 20 sektor industri bisnis.
  - **Instagram Carousel 2–10 Media dengan Reordering Tray:** Pengunggahan korsel foto/video Instagram multi-item dengan fitur penyusunan ulang urutan slide (*reordering tray*) yang mulus.
  - **Aturan Bisnis COOCA (Maksimal 5 Tagar Unik):** Menegakkan batas maksimal 5 tagar per postingan/kanal secara otomatis dengan deduplikasi case-insensitive dan indikator badge live (`Tagar: X / 5`) demi memaksimalkan jangkauan algoritma dan estetika feed.
  - **Penyimpanan Server Bebas Beban (*Storage Auto-Purge*):** Berkas video/foto yang diunggah langsung dibersihkan permanen dari server lokal COOCA setelah 1x24 jam publikasi berhasil untuk menjaga kapasitas storage disk server.
  - **Kalender Konten & Analitik:** Tampilan kalender jadwal tayang bulanan dan pemantauan metrik impresi, jangkauan (*reach*), interaksi, dan komentar dengan tombol *Tarik Live*.

### 3.11 Hub Integrasi Marketplace Omnichannel (Shopee, TikTok Shop & Tokopedia)
* **Kapan Digunakan?** Saat pemilik usaha ingin mengintegrasikan inventori gudang, perbedaan harga jual (*channel pricing*), dan pesanan masuk dari toko resmi di Shopee, TikTok Shop, dan Tokopedia ke dalam satu pintu operasional COOCA.
* **Fitur Utama & Keunggulan Operasional:**
  - **Otorisasi Resmi 1-Pintu:** Menghubungkan akun toko resmi via OAuth 2.0 (Shopee Open V2 dan TikTok Shop Partner Center yang mengelola TikTok Shop + Tokopedia sekaligus) dengan penyimpanan token terenkripsi (`encrypted`).
  - **Multi-Harga Per Channel & Faktor Pengali:** Menetapkan margin harga dinamis (misal: pengali `1.08` untuk menyerap biaya admin marketplace 8%) atau harga tetap manual per channel dari harga dasar COOCA.
  - **Alokasi Stok Pengaman (Safety Buffer Stock):** Menyisihkan stok fisik di gudang utama agar tidak terpublikasikan ke marketplace, mencegah risiko kehabisan stok (*overselling*) saat kasir offline POS sedang melayani pelanggan toko fisik.
  - **Anti-Margin Bleed Guard:** Peringatan visual proaktif saat harga jual saluran yang dimasukkan berada di bawah modal dasar produk (HPP) untuk mencegah kerugian finansial akibat salah ketik staf (*human error*).
  - **Saklar Penjualan & Sinkronisasi Aktif Per Saluran:** Mengontrol keaktifan jalur penjualan dan pembaruan stok/harga otomatis per channel secara instan via toggle switch Apple HIG tanpa memutus koneksi otorisasi toko.
  - **Inbound Order Feed & Real-Time Sync:** Menarik pesanan masuk secara real-time via webhook HMAC SHA-256 terverifikasi atau penarikan massal terjadwal, lengkap dengan kurir ekspedisi dan nomor resi pelacakan.
  - **Penegakan Regulasi 20 Sektor Industri:** Hard-lock pencegahan penjualan obat keras BPOM RI untuk sektor apotek, serta pemisahan produk barang fisik (*goods*) dari jasa (*service*) untuk sektor bengkel, salon, dan laundry.

### 3.12 Kepatuhan Pajak UMKM & Penggajian Karyawan (HRM & Tax Compliance Hub)
* **Kapan Digunakan?** Saat Anda ingin memantau kewajiban perpajakan bisnis (PPh Final UMKM 0.5% PP 55/2022, PB1 Restoran / PPN) atau mengelola seluruh operasional SDM & penggajian staf (profil data karyawan, struktur upah, BPJS Ketenagakerjaan & Kesehatan, pinjaman kasbon, pekerja harian lepas, penggajian bulanan batch PPh 21 TER A/B/C, dan slip gaji digital).
* **Fitur Utama & Keunggulan Operasional:**
  - **HRM Hub Terpadu (`/hrm`):** 
    1. *Profil Karyawan Lengkap:* Menyimpan data jabatan, jenis status kerja (tetap/kontrak/harian lepas), tanggal bergabung, gaji pokok, tunjangan tetap & variabel, status PTKP (TK/0 s/d K/3), keikutsertaan BPJS TK & Kes, rekening bank penerima, dan nomor WhatsApp.
    2. *Pengelolaan Kasbon & Pinjaman (`employee_loans`):* Pencatatan pinjaman darurat karyawan lengkap dengan tenor, jadwal cicilan, dan auto-pemotongan otomatis saat penggajian bulanan dibayarkan.
    3. *Mesin Penggajian Bulanan Batch (`/hrm/payrolls`):* Kalkulasi penggajian massal 1-klik untuk seluruh staf dengan penghitungan presisi PPh 21 TER (PP 58/2023 & PMK 168/2023), iuran BPJS TK & Kesehatan, upah lembur, komisi SPK, dan THR prorata.
    4. *Siklus Draf-Setujui-Bayar:* Alur kerja aman multi-langkah (`draft` -> `approved` -> `paid`) yang secara otonom membukukan beban upah perusahaan ke modul pengeluaran (`finance.expenses`) dan memperbarui saldo kasbon staf.
    5. *Slip Gaji Digital Apple HIG (`/hrm/payslips/{item}` & `/payslip/{token}`):* Dokumen slip gaji elegan berstandar Apple Bento HIG dengan opsi cetak printer A4/thermal 80mm, download PDF instan, tombol bagikan WhatsApp, serta link token publik aman yang dapat diakses langsung oleh karyawan tanpa login dashboard.
  - **Smart 500M Threshold Meter (PP 55/2022):** Pelacak visual omzet kumulatif tahunan khusus Wajib Pajak Orang Pribadi. Menghitung omzet bebas pajak hingga Rp 500.000.000 secara otomatis, dan hanya memotong PPh Final 0.5% atas kelebihan omzet di atas ambang batas.
  - **Rekapitulasi 12 Bulan Omzet Riil:** Mengonsolidasi seluruh invoice penjualan dan transaksi kasir POS secara otomatis ke kartu pajak bulanan tanpa input manual.
  - **Kalkulator Interaktif 4-in-1:**
    1. *Simulasi PPh Final UMKM:* Menghitung tarif 0.5% berdasarkan omzet bulanan dan status wajib pajak.
    2. *Simulasi PPh 21 TER & Daily Worker:* Menghitung pemotongan bulanan TER Kategori A/B/C (PP 58/2023) dan upah harian lepas.
### 3.13 Analitik Bisnis & Tren Pertumbuhan (Analytics Suite)
* **Kapan Digunakan?** Saat pemilik usaha atau tim manajemen ingin meninjau performa penjualan, laba kotor riil, perbandingan antar-periode, dan pola belanja pelanggan.
* **Fitur Utama & Keunggulan Operasional:**
  - **Konsolidasi Multi-Channel:** Menghitung omzet gabungan dari transaksi kasir POS, faktur B2B, dan toko online secara real-time.
  - **Laba Kotor & Estimasi Laba Bersih:** Menghitung margin laba kotor terhadap HPP secara otomatis, serta mengestimasi laba bersih setelah dikurangi seluruh beban operasional.
  - **Grafik Tren & Jam Sibuk Kasir:** Visualisasi pergerakan omzet harian serta peta panas antrean transaksi kasir (07:00–23:00) untuk optimalisasi jadwal shift staf.
  - **Distribusi Pembayaran:** Komposisi transaksi metode QRIS, Tunai, Transfer Bank, Kartu EDC, dan Kasbon.
  - **Top 10 Menu/Produk Terlaris:** Menampilkan kontribusi kuantitas dan margin produk terlaris dengan antarmuka dual-mode (Tabel Desktop & Kartu Mobile).

### 3.14 Pusat Otorisasi Dokumen (MAR - Maker, Approver, Releaser)
* **Kapan Digunakan?** Saat bisnis menerapkan tata kelola bertingkat untuk pengeluaran biaya operasional, tagihan supplier, dan permohonan pengadaan barang (Purchase Order) di atas ambang nominal tertentu.
* **Fitur Utama & Keunggulan Operasional:**
  - **Pemisahan Wewenang (Segregation of Duties):** Staf pembuat draf (*Maker*) mengajukan dokumen, sementara penyetujuan diotorisasi berjenjang (*Level 1 Supervisor -> Level 2 Manager -> Level 3 Owner*).
  - **Audit Trail Permanen:** Seluruh aksi persetujuan, penolakan, dan catatan tertulis direkam abadi untuk kepatuhan tata kelola bisnis.
  - **Pencegahan Fraud & Auto-Journaling:** Dokumen yang ditolak otomatis diblokir dari pencairan kas; dokumen yang disetujui penuh secara otomatis terhubung ke pemotongan stok bahan baku (BOM) dan jurnal akuntansi berimbang.
  - **Manajemen Plafon Mandiri:** Pemilik usaha dapat mengatur batas nominal minimal dan tingkatan penyetuju langsung melalui modal sheet in-place.

### 3.15 Portal Karyawan & Presensi Mandiri (Staff Personal Attendance & Workstation Hub)
* **Kapan Digunakan?** Setiap hari saat staf toko (kasir, barista, pelayan, staf gudang) mulai bertugas atau mengakhiri shift.
* **Fitur & Keamanan Alur Kerja:**
  - **Pengalihan Otomatis Non-Eksekutif:** Staf tanpa hak akses `dashboard.view` otomatis dialihkan ke `/portal` alih-alih menemui error 403 Forbidden.
  - **Penyaringan Ketat Hak Akses Operasional:** Kartu modul cepat hanya menampilkan modul yang sah dimiliki staf (kasir hanya melihat POS Kasir, staf logistik hanya melihat Gudang & Stok).
  - **Bento Empty-State Card:** Jika staf belum diberikan izin operasional apa pun, layar menyajikan kartu empty state ramah pengguna yang menjelaskan bahwa akses difokuskan untuk presensi mandiri.
  - **Presensi Berbasis Shift & Multi-Timezone:** Dilengkapi jam live interaktif dengan badge timezone outlet (`WIB`, `WITA`, `WIT`) dan kartu *Active Shift Today* (jam kerja, toleransi grace period, dan jam istirahat).
  - **Pemisahan Tegas Operating Hours vs Shift:** Operating hours toko hanya mengatur jendela buka gerai, sementara absensi staf dihitung murni terhadap shift atau roster individu (`WorkShift` / `EmployeeSchedule`). Staf bebas jadwal tidak dipaksa keterlambatan fiktif.
  - **Dukungan Shift Overnight (Lintas Tengah Malam):** Shift malam (misal 22:00 - 06:00) dievaluasi sebagai satu sesi absensi tunggal utuh.
  - **Umpan Balik Instan Keterlambatan (Modal Feedback Apple HIG):** Saat clock-in berhasil, modal sheet langsung menyajikan status kehadiran (`TEPAT WAKTU`, `TERLAMBAT`, `LEBIH AWAL`), jam masuk aktual, jam jadwal masuk, dan durasi keterlambatan presisi dalam menit.
  - **Imutabilitas Riwayat Absensi:** Rekam jejak absensi mengunci snapshot jadwal secara permanen, sehingga pemindahan shift di masa depan tidak mengubah historis data kehadiran staf.

### 3.15 Integrasi Model Context Protocol & AI Gateway (MCP Server)
* **Kapan Digunakan?** Saat pemilik usaha ingin menghubungkan asisten kecerdasan buatan favoritnya (Claude Desktop, Cursor IDE, ChatGPT, Gemini, Ollama, n8n) dengan data operasional COOCA ID.
* **Fitur & Keamanan Alur Kerja:**
  - **Pusat Integrasi AI Terpadu:** Dapat diakses melalui `/settings/integrations/mcp` dengan antarmuka Bento Apple HIG v2.0 yang modern dan bebas emoji.
  - **Manajemen Token Mandiri:** Pemilik usaha dapat membuat token akses aman, mengatur batas kedaluwarsa, membatasi hak akses (*abilities* seperti hanya baca produk atau pencatatan pengeluaran), serta mencabut (*revoke*) token kapan saja.
  - **Panduan Konfigurasi Interaktif 6 Provider:** Menyajikan petunjuk instalasi copy-paste instan untuk Claude Desktop, Cursor IDE, ChatGPT Custom Actions, Google Gemini Python SDK, Ollama, dan n8n/Dify.
  - **Pemantauan Audit Trail Real-time:** Setiap interaksi AI tercatat transparan di tabel log aktivitas, mencakup nama tool, status eksekusi, provider yang digunakan, latency eksekusi (ms), dan IP address.

---

## 4. Panduan Rekayasa Developer & AI Agent (Engineering Blueprint)

### 4.1 Struktur 33 Domain Packages DDD
Logika bisnis utama tidak ditempatkan di Controller, melainkan pada domain package di `app/Domain/`:
* Controller bertindak sebagai *HTTP transport layer* (validasi request, otorisasi, dan format respon).
* Domain Service mengeksekusi logika bisnis inti dalam transaksi database atomik (*DB::transaction*).
* Model Eloquent menangani relasi data, mutator, casting, dan event lifecycle.

### 4.2 Aturan Scoping Tenant, Proteksi Keamanan, & Zero Plaintext Credential Exposure
* **Aturan Scoping Mutlak:** Setiap query entitas tenant WAJIB terikat pada `$business->id` atau `Context::requireBusiness()`. Dilarang melakukan query un-scoped seperti `Product::all()`.
* **Proteksi IDOR Portal Pelanggan:** Akses `/customer/orders/{id}` WAJIB memverifikasi bahwa ID customer pada pesanan identik dengan identitas yang diautentikasi oleh guard `auth:customer`.
* **Proteksi PIN Kasir:** Verifikasi PIN supervisor menggunakan hash Bcrypt dan dibatasi rate limit (*throttle: 5, 1 menit*).
* **Perlindungan Kredensial Sensitif di UI/API:** Seluruh API keys, secret tokens, private keys, password akun, PIN kasir, dan secrets dilarang diekspos dalam teks terbuka (*plain text*). Wajib menerapkan masking (`••••••••`) pada form integrasi dan menyembunyikan atribut rahasia via `$hidden` pada model Eloquent agar tidak bocor via respons JSON/AJAX.

### 4.3 Mesin Otomasi Latar Belakang (Auto-Journal & Auto-Stock)
* **AutoJournalService:** Mengkonversi transaksi kasir, pelunasan AP/AR, dan mutasi kas menjadi jurnal memorial berimbang ($\sum \text{Debit} = \sum \text{Kredit}$).
* **Auto-BOM Engine:** Mengurangi saldo stok bahan baku mentah secara atomik (`decrement`) saat pesanan kasir berstatus `paid`.

### 4.4 Protokol Rekayasa 6-Tahap & Kesiapan Produksi (The 6-Stage Engineering Lifecycle)
Seluruh aktivitas pengembangan dan perbaikan sistem oleh AI Agent atau Engineer **WAJIB** mematuhi siklus kerja terstruktur:
1. **Audit Sistem & Analisis End-to-End:** Analisa hulu-ke-hilir (`User → UI → Route → Controller → Validation → Service → Model → DB → Event/Job → Notification Tri-Channel → Response`) untuk memahami konteks dan fiturnya secara mendalam sebelum menyentuh kode.
2. **Dokumen Rencana Perbaikan:** Menyusun rencana memuat 8 elemen standar (Temuan, Akar Penyebab, Dampak, Solusi, File Terdampak, Risiko, Prioritas, Urutan Implementasi).
3. **Minta Persetujuan (Confirmation Gate):** Menyajikan rencana perbaikan dan menunggu persetujuan pengguna sebelum melakukan perubahan apa pun.
4. **Implementasi Surgical & Pengujian Nyata:** Eksekusi terarah tepat sasaran tanpa scope creep; pengujian sintaks (`php -l`), routing (`php artisan route:list`), unit/feature test (`php artisan test`) wajib 100% lolos (0 error, 0 failure).
5. **Pembersihan Residue & Hardening:** Bebas mock data, bebas debug console (`dd()`, `dump()`, `console.log()`), kompilasi aset produksi (`npm run build`), dan pembersihan data testing dari database operasional.
6. **Pencatatan & Pembaruan Pengetahuan:** Catat history di `docs/AiWorkHistory.md` dan mutakhirkan `docs/system/` serta `docs/SYSTEM_GUIDE.md`.

### 4.5 Protokol Dokumentasi Berkelanjutan Simultan (AiWorkHistory.md + docs/system/ + SYSTEM_GUIDE.md)
* **Mandat Mutlak Pembaruan Bersamaan:** Setiap kali AI Agent atau engineer menyelesaikan tugas rekayasa dan mencatatkan riwayat di `docs/AiWorkHistory.md`, **WAJIB secara simultan memperbarui `docs/SYSTEM_GUIDE.md` dan direktori relevan di `docs/system/`**.
* **7 Komponen Wajib `docs/AiWorkHistory.md`:** (1) Tanggal/waktu, (2) Tujuan pekerjaan, (3) Hasil audit, (4) Perbaikan yang dilakukan, (5) File/module yang diubah, (6) Hasil pengujian, (7) Catatan/risiko tersisa.
* **10 Aspek Pemicu Pembaruan `docs/system/`:** Pembaruan Layer 2 `docs/system/` wajib dilakukan jika perubahan menyentuh salah satu dari: (1) Arsitektur sistem, (2) Struktur database, (3) Modul/fitur, (4) Alur bisnis, (5) Integrasi, (6) Konfigurasi, (7) API/Endpoint, (8) Permission/role, (9) Workflow operasional, (10) Struktur file/komponen.
* **Status Penyelesaian:** Tugas yang hanya mencatatkan history di `AiWorkHistory.md` tanpa menyelaraskan `SYSTEM_GUIDE.md` dan `docs/system/` diklasifikasikan sebagai **BELUM SELESAI (INCOMPLETE / PARTIAL)** dan tidak dapat dinyatakan `VERIFIED` atau `COMPLETED`.

### 4.6 Arsitektur Multi-Tenant WhatsApp Cloud API & Hardening Terpadu
* **Pemisahan Kredensial Multi-Tenant:** Setiap merchant memiliki satu rekaman data pada tabel `whatsapp_accounts` yang menyimpan `waba_id`, `phone_number_id`, `phone_number`, dan `access_token`.
* **Enkripsi Otomatis Atribut Sensitif:** Kolom `access_token` dienkripsi secara otomatis pada level basis data menggunakan native Laravel cast `'access_token' => 'encrypted'` (AES-256-CBC dengan `APP_KEY`), mencegah kebocoran kredensial saat backup basis data.
* **Asynchronous Webhook Processing via Redis:** Endpoint webhook Meta (`/api/v1/wa/meta/webhook`) segera memvalidasi tanda tangan kriptografis HMAC-SHA256 (`X-Hub-Signature-256`) menggunakan `hash_equals()` dan mendispatch event ke antrean Redis Job (`ProcessWhatsAppWebhookJob`), mengembalikan HTTP 200 dalam waktu <200ms untuk memenuhi SLA Meta.
* **Isolasi Pemrosesan Event:** `WhatsAppWebhookService` memetakan `phone_number_id` yang tertera pada metadata webhook ke `business_id` tenant merchant secara presisi, menjamin pesan masuk dan pembaruan status tidak pernah tertukar lintas tenant.
* **Client HTTP Andal dengan Retry Logic:** `WhatsAppClient` membungkus pemanggilan Graph API v26.0 dengan retry otomatis (hingga 3 kali percobaan) pada galat jaringan transien (HTTP 429 Rate Limit dan HTTP 5xx Server Error Meta) dan audit logging terstruktur per-tenant.
* **Proteksi Anti-Fraud & Audit Struk Kasir (`receipt.phone_override`):** Saat kasir mengubah nomor telepon penerima struk digital di luar data pelanggan terdaftar, `PosTerminalWebController@sendReceipt` mencatat audit log permanen (`audit_logs`) memuat nomor awal, nomor pengalihan, ID kasir, IP address, dan User Agent.
* **Idempotency Key Lock 300 Detik:** `WhatsAppBroadcastWebController@store` mengamankan penjadwalan broadcast dengan kunci atomik cache (`Cache::add("broadcast_lock_{$hash}", true, 300)`) berbasis hash `business_id + title + message`, mencegah pengiriman ganda akibat lag koneksi atau double-click.
* **Mitigasi Anti-SSRF Banner Media:** Validasi URL banner pada broadcast memverifikasi protokol `https://` dan menolak seluruh IP privat (RFC 1918), IP loopback (127.0.0.1/localhost), dan metadata cloud providers (169.254.169.254).
* **Rate Limiting & E.164 Normalization:** Endpoint konsol uji coba `POST /whatsapp/test` dilindungi pembatasan ketat `throttle:5,1` dan konversi deterministik format nomor lokal `08xx` &rarr; `628xx`.
* **Masking PII Nomor Telepon & Proteksi wa.me:** Nomor pelanggan pada log pesan dan daftar penerima kampanye dimasking (`0812••••7890`) bagi pengguna non-owner via `\App\Support\Context::isOwner()`, dan tombol eksternal `wa.me` hanya diizinkan untuk Owner guna mencegah pembajakan database kontak pelanggan oleh staf.
* **Arsitektur Modal-First XXL & Anti Double-Submit:** Penyatuan halaman create ke modal sheet Bento XXL di halaman index via pengalihan anggun (`whatsapp.broadcast.create` &rarr; `whatsapp.broadcast.index?open_composer=1`), dilengkapi proteksi `submitting: false` dengan tombol terkunci dan SVG spinner animasi.
* **Sadar Konteks 20 Industri, Meta Health Policy & Quiet Hours:** Komposer broadcast secara otomatis menyajikan kamus tag personal adaptif per klaster industri (`fnb_*`, `service_workshop`, `service_laundry`, `mfg_*`, `service_contractor`, `retail_pharmacy`), menyematkan banner peringatan hukum Meta Health Policy & BPOM khusus apotek, serta deteksi jam istirahat pelanggan (*Quiet Hours* 21:00–08:00 WIB) berbasis waktu lokal.

### 4.7 Arsitektur Media Sosial Omnichannel (Meta, TikTok & LinkedIn UGC Post API)
* **Zero .env Architecture & Database-Driven Settings:** Seluruh konfigurasi platform (`social_media_app_id`, `social_media_app_secret`, `instagram_app_id`, `instagram_app_name`, `instagram_app_secret`, `instagram_access_token`, `tiktok_client_key`, `tiktok_client_secret`, `linkedin_client_id`, `linkedin_client_secret`) dikelola eksklusif melalui antarmuka Superadmin `resources/views/admin/settings` (Tab "Media Sosial") dan tersimpan di tabel `system_settings` dengan flag rahasia `is_secret = true`, tanpa menyentuh file `.env`.
* **Official Instagram Platform API & Cerdas Dual-Host Routing:** Integrasi resmi Instagram Graph API v26.0 (`Cooca-IG`) mendukung akun Bisnis/Kreator (`@cooca.indonesia`). `MetaSocialMediaClient` mengimplementasikan deteksi prefiks token (`IGAA...`) untuk otomatis mengalihkan host ke `https://graph.instagram.com/{version}/...` alih-alih `https://graph.facebook.com/{version}/...`, mencegah kegagalan OAuth 190 sambil tetap memelihara kompatibilitas dengan Facebook Page token.
* **LinkedIn Developer Platform (OAuth 2.0 OpenID Connect & UGC Posts v2):** `LinkedInClient` dan `LinkedInProvider` mengintegrasikan alur otorisasi OpenID (`openid profile email w_member_social`), Digital Media Asset upload (`/v2/assets?action=registerUpload`), dan publikasi konten teks / gambar publik (`/v2/ugcPosts`) dengan URN person author (`urn:li:person:...`).
* **Enkripsi Kredensial & Auto-Refresh Token:** `access_token` dan `refresh_token` pada `social_media_accounts` dienkripsi simetris menggunakan Eloquent encrypted cast (AES-256 via APP_KEY). Token TikTok dan LinkedIn diperiksa dan dikelola secara aman.
* **Pola Desain Strategy & Kontrak Provider:** `SocialMediaProviderInterface` diimplementasikan oleh `MetaProvider` (Facebook, Instagram, Threads), `TikTokProvider` (Direct Post Video & Photo Mode), dan `LinkedInProvider` (UGC Post API). Resolusi provider bersifat decoupled melalui `SocialMediaManager`.
* **Validasi Konten Sentral & Aturan 5 Tagar:** `SocialMediaContentValidator` memvalidasi batas karakter per platform (FB 63.206, IG 2.200, Threads 500, TikTok 2.200, LinkedIn 3.000), aturan korsel Instagram (2–10 media), serta aturan wajib COOCA maksimal 5 unique hashtags dengan normalisasi case-insensitive.
* **Queue-First Distributed Publishing & Multi-Channel Scheduling:** Publikasi multi-target didelegasikan ke antrean `PublishSocialMediaTargetJob` dengan retry berjenjang (*exponential backoff* 10s, 30s, 60s). Engine scheduler cron `social-media:publish-scheduled` berjalan setiap menit mengeksekusi target terjadwal (`scheduled_at <= now()`) secara otonom per-saluran tanpa memblokir antarmuka pengguna.

### 4.8 Arsitektur Pre-Order & Batch Scheduling Dinamis Terparameterisasi
* **Strict Batch vs Flexible Scheduling:** Parameter `allow_custom_date` (boolean) pada `commerce_store_settings` memungkinkan merchant mengunci pemesanan hanya pada tanggal batch yang ditentukan (menyembunyikan datepicker bebas pada checkout modal dan memvalidasi pesanan di server-side). Jika aktif, pembeli tetap diberikan keleluasaan memilih tanggal kalender mandiri.
* **Dual Quota Metric (`quantity` vs `orders`):** Kapasitas kuota (`daily_order_quota`) mendukung dua mode kalkulasi melalui kolom `quota_metric`:
  - `quantity`: Kuota dihitung berdasarkan total kuantitas unit produk yang dipesan (`SUM(commerce_order_items.quantity)`), cocok untuk kapasitas dapur/produksi (misal: 150 PCS/Porsi/Box).
  - `orders`: Kuota dihitung berdasarkan total transaksi (`COUNT(commerce_orders.id)`), cocok untuk kapasitas antrean pengantaran.
* **Dynamic Quota Unit Labeling:** Kolom `preorder_quota_unit` (default `PCS`) menyajikan label satuan dinamis pada antarmuka checkout ("Sisa 150 PCS", "Sisa 149 Box", dll.).
* **Mode Batch Rutin vs Kalender Spesifik:** Melalui `batch_dates_mode`:
  - `operating_days`: Otomatis menghasilkan batch mingguan berulang sesuai hari operasional yang aktif (misal: Jumat saja).
  - `custom_dates`: Merchant mendefinisikan daftar tanggal kalender spesifik beserta kuota per-batch melalui kolom JSON `custom_batch_dates`, ideal untuk PO hari raya, seasonal batch, atau catering kustom.
* **Server-Side Guardrail:** `CommerceOrderService::createScheduledOrder()` menolak secara tegas tanggal pesanan di luar batch resmi saat `allow_custom_date = false`, serta memvalidasi ketersediaan sisa kuota PCS/kuantitas sebelum transaksi dicatat.
* **Join Pre-Order & Office Group Buying (Bento Apple HIG):**
  - **Shareable Batch Link:** Merchant atau koordinator kantor dapat menyalin link langsung `/b/{slug}?batch=YYYY-MM-DD` (opsional dengan grup/kantor `&group=NamaKantor`).
  - **Auto Pre-Selection:** Halaman etalase publik langsung mengunci tanggal batch yang diminta dan menampilkan Bento Hub Pre-Order dengan aksi satu klik *Salin Link* & *Share WhatsApp*.
  - **Label Pemesan per Item:** Setiap item di keranjang dapat disematkan nama pemesan / catatan (`item.notes`), mencegah kekeliruan menu antar rekan kerja kantor.
  - **Lokalisasi 100% Bahasa Indonesia:** Hari (`SENIN`, `SELASA`, `RABU`, `KAMIS`, `JUMAT`, `SABTU`, `MINGGU`) dan bulan diformat secara deterministik independen dari setting locale server OS.

### 4.9 Pusat Verifikasi Pemulihan Akun Administrator (Account Recovery Desk)
Pusat Verifikasi Pemulihan Akun (`/admin/account-recoveries`) berfungsi sebagai instrumen audit dan proteksi identitas level platform untuk mencegah pengambilalihan akun sepihak (*account takeover*) dan melindungi data historis UMKM:
* **Arsitektur Modal-First XXL Inspection Desk:** Halaman antrean index (`index.blade.php`) dilengkapi dengan modal pop-up berukuran Full Layout XXL (`max-w-6xl`) bergaya Apple HIG untuk memeriksa komparasi kontak lama vs baru dan meninjau berkas bukti otentik (KTP, legalitas usaha, selfie).
* **Dukungan Berkas PDF & Multi-Format Dokumen:** Meja inspeksi dokumen secara dinamis mendeteksi berkas PDF legalitas usaha (`.pdf`) dan merender kartu dokumen PDF berstandar Apple lengkap dengan tombol pratinjau tab baru dan unduh langsung.
* **Kepatuhan Bento Apple HIG v2.0 & Anti-Pill-Abuse:** Membersihkan fake pulse dots (`animate-ping`/`animate-pulse`), menerapkan fluid container (`max-w-[1250px] w-full min-w-0 pb-28 lg:pb-10`), font input anti auto-zoom iOS (`text-[16px] sm:text-xs`), dan touch target nyaman 44px–52px.

### 4.10 Pusat Pengelolaan Katalog Paket Billing Platform (Billing Packages CMS)
Pusat Pengelolaan Paket Billing (`/admin/billing-packages/{type?}`) adalah kontrol panel terpadu bagi Superadmin untuk mengelola monetisasi platform Cooca:
* **Struktur Tiga Tab Terpadu (Apple Pill Segmented Control):** Paket & Durasi Subscription, Paket Top Up Token AI, dan Paket Top Up Storage.
* **Single Source of Truth Default Pricing Panel:** Panel konfigurasi terpusat untuk menentukan tarif fallback bawaan platform (`subscription_price_monthly`, `subscription_price_annual`, dll.).
* **Kepatuhan Desain Apple HIG v2.0:** Desain Bento squircle `rounded-[20px]`/`rounded-[22px]`, font minimal 16px di mobile anti auto-zoom, dan Zero Unicode Emoji.

### 4.11 Pusat Pengelolaan CMS Artikel, Taksonomi Kategori, & Cluster Konten (Admin Posts CMS)
Pusat manajemen konten publikasi blog dan edukasi bisnis Cooca (`/admin/posts`) mengintegrasikan strategi konten modern berbasis taksonomi terstruktur dan editor visual:
* **Tabel Taksonomi Database Terstruktur:** Tabel `post_clusters` dan `post_categories` dengan Dual-Sync Backward Compatibility ke rute publik blog.
* **Integrasi Text Editor Kaya TinyMCE Free:** CDN resmi TinyMCE Free pada target `#post-content` dengan sinkronisasi `tinymce.triggerSave()`.
* **Apple HIG v2.0 & Ergonomi Navigasi:** 3-Tab navigasi segmented control, Inset Dialog Modal, tombol inline `[ + Kategori Baru ]`, dan Zero Unicode Emoji.

### 4.12 Pusat Pengaturan Platform & Integrasi Layanan Terpusat (Admin Settings & 5-Service Hub)
Pusat kontrol terpadu Superadmin (`/admin/settings`) mengadopsi **Model B (Platform Centralized Integration)** dengan arsitektur 6 tab modular Bento Apple HIG v2.0:
* **Zero .env Mandate & Dynamic Database Resolution:** Seluruh kredensial dan parameter 5 layanan eksternal (TriPay, WhatsApp Cloud API, Meta Social, TikTok Open API, Biteship Logistics) dikonfigurasi melalui Web UI dan dipersistensikan langsung ke basis data `system_settings`.
* **Integrasi Logistik & Ekspedisi Agregator (Biteship Multi-Courier API):** Mengelola API Secret Key (`biteship_api_key`), Base URL (`biteship_base_url`), Mode Lingkungan (`biteship_environment`: Sandbox vs Production), dan Platform Handling Fee (`biteship_service_fee`) dengan webhook tracking resi otomatis dan live diagnostic tester.
* **Pengujian Konektivitas Real-Time (Live Diagnostic Testers):** Seluruh kanal dilengkapi tombol uji koneksi instan AJAX dengan diagnostic alert box terpadu.
* **Kepatuhan Apple HIG v2.0 & Ergonomi Jempol:** Desain bento squircle `rounded-[20px]`/`rounded-[24px]`, fluid layout `max-w-[1250px] w-full min-w-0 pb-28 lg:pb-10`, Zero Unicode Emoji (100% Lucide SVG Icons), toggle intip kredensial sensitif, dan input anti auto-zoom iOS Safari (`text-[16px] sm:text-[13px]`).

### 4.13 Arsitektur Blueprint Tier Pricing v2.3, Multi-Branch & Tax Compliance Engine
Berdasarkan dokumen arsitektur `docs/BLUEPRINT_TIER_PRICING_DAN_LIMITASI_COOCA.md` (Versi 2.3):
* **4-Tier Pricing Model:** Free (Rp 0), Standard (Rp 29.000/bln), Premium (Rp 89.000/bln), Prestige (Rp 199.000/bln).
* **Storage Melekat pada Owner:** Kapasitas penyimpanan media (gambar produk, nota, berkas) melekat pada akun Owner (1 GB Free, 3 GB Standard, 10 GB Premium, 30 GB Prestige) dan berlaku gabungan untuk seluruh bisnis miliknya.
* **Multi-Branch & Central Kitchen (Premium & Prestige):** Transfer stok antar gudang/cabang dan penetapan harga produk spesifik per cabang (`branch_product_prices`).
* **HRM Comprehensive Suite:**
  - `BPJSCalculationService`: JHT (3.7%/2%), JKK (0.24%-1.74%), JKM (0.3%), JP (2%/1% cap Rp 10.042.300), BPJS Kesehatan (4%/1% cap Rp 12.000.000).
  - `THRCalculationService`: Sesuai Permenaker 6/2016 (>= 12 bln = 1 bln, 1 <= bln < 12 = prorata, < 1 bln = Rp 0; daily worker avg wage 12 bln).
  - `PayrollCalculationService`: Mengintegrasikan seluruh komponen gaji, lembur, komisi SPK (`spk_commissions`), cicilan kasbon (`employee_loans`), BPJS, THR, dan PPh 21 TER.
* **Tax Compliance Engine:**
  - `PPh21CalculationService`: Kategori TER A/B/C (PP 58/2023 & PMK 168/2023), rekonsiliasi Desember Pasal 17, dan upah harian lepas.
  - `PPhFinalUMKMService`: PP 55/2022 tarif 0.5% dengan batas bebas pajak Rp 500.000.000 untuk Orang Pribadi.
  - `SalesTaxService`: PB1 10%, PPN 11%/12%, Service Charge, mode inklusif vs eksklusif.
* **Payment Gateway Eksklusif TriPay:** Pembayaran langganan SaaS 100% dialirkan melalui TriPay resmi (Virtual Account, QRIS, E-Wallet, Retail Mart).

### 4.14 Arsitektur Audit & Proteksi Fraud Internal serta Notifikasi Sistem Terpadu (UI, Email, WhatsApp)
* **Pondasi Anti-Fraud Internal Bawaan (*Built-in Internal Fraud Guardrails*):**
  - **POS & Kasir:** Aksi pembatalan (*Void*) dan pengembalian uang (*Refund*) pasca cetak struk wajib `supervisor_pin` ter-hash Bcrypt, pencatatan alasan, auto-restock, dan notifikasi instan. Tutup kasir wajib menerapkan **Blind Cash Count (Tutup Kasir Buta)** untuk mencegah manipulasi selisih kas.
  - **Gudang & Pengadaan:** Stock write-off bernilai besar wajib Maker-Checker / Approval Owner dengan bukti Berita Acara. Pengadaan menerapkan **Three-Way Matching** (PO ↔ GRN ↔ Invoice AP). Transfer antar cabang wajib **Two-Step Transfer** (`In-Transit` → `Received`).
  - **Keuangan & Piutang:** Pelunasan piutang otomatis menerbitkan kuitansi digital via WhatsApp/Email ke pelanggan untuk mencegah *lapping scheme*. Periode buku terlindungi **Accounting Period Lock** guna mencegah manipulasi tanggal mundur (*anti-backdating*). Jurnal akuntansi terposting bersifat permanen (*Double-Entry Immutability*).
  - **Audit Trail Immutable:** Tabel `audit_logs` merekam seluruh mutasi berisiko secara terstruktur (`user_id`, `business_id`, `branch_id`, `action`, IP, User Agent, snapshot JSON `payload_before` & `payload_after`, `reason_notes`).
* **Arsitektur Notifikasi Multi-Saluran (*Tri-Channel Notification Engine*):**
  - **Saluran UI In-App:** Header Bell Dropdown dengan unread badge counter `tabular-nums`, filter kategori (Transaksi, Fraud, Stok, Otorisasi, Sistem), Floating Frosted Glass Toast (auto-dismiss 3-5 detik), dan Modal Sheet Maker-Checker Action Cards.
  - **Saluran Email (Apple HIG Responsive HTML):** Daily/Weekly Executive Business Digest untuk Owner, Critical Security & Fraud Alerts seketika, Faktur & Invoice B2B PDF terlampir.
  - **Saluran WhatsApp (Meta Cloud API & Gateway):** Nota/struk digital instan ke pembeli, update status pesanan, Auto-Reminder piutang jatuh tempo (H-3, Hari H, H+3), dan Peringatan Kritis Langsung ke WhatsApp Owner.
  - **Prinsip Asinkron & Fail-Safe Fallback:** Seluruh pengiriman Email/WhatsApp berjalan asinkron di antrean (`ShouldQueue`) tanpa membebani response time POS (sub-100ms). Jika gateway eksternal offline, transaksi tetap sukses dan UI menyediakan tombol manual 1-klik `[ Kirim via WhatsApp Web / HP ]` (`wa.me`).

### 4.15 Arsitektur POS Hardware, ESC/POS Thermal Printer, Cash Drawer Safety & Local Agent Bridge
* **Integrasi Raw Binary ESC/POS Berkecepatan Tinggi:** Menggunakan library `mike42/escpos-php` dan abstraksi domain `App\Domain\Printer` untuk menghasilkan binary stream ESC/POS native yang langsung dikirimkan ke hardware thermal printer (58mm / 80mm) tanpa bergantung pada driver print modal browser.
* **Topologi Multi-Konektor & Abstraksi Jaringan:**
  - **LAN / Wi-Fi Ethernet:** Koneksi TCP socket langsung ke port 9100 (`NetworkConnector`) dengan uji diagnostik ping latency terintegrasi.
  - **Windows Spooler Print Queue:** Koneksi antrean Windows (`WindowsConnector`) untuk printer kasir yang terpasang melalui driver USB Windows.
  - **Direct Device File:** Koneksi port lokal Linux/macOS `/dev/usb/lp0` atau COM Serial (`FileConnector`).
  - **Local POS Agent (Bluetooth / USB):** Daemon Node.js ringan (`hardware-agent/agent.js`) untuk bridge browser/cloud server ke printer Bluetooth nirkabel (`AgentPayloadConnector`) dengan polling antrean asinkron (`pos_print_jobs`).
* **Proteksi Keamanan Laci Kas Fisik (*Cash Drawer Safety & Anti-Fraud*):**
  - **Pemicu Otomatis:** Laci kas (RJ-11/RJ-12 solenoid pulse) **HANYA** terbuka saat pembayaran pesanan telah berstatus `COMPLETED` dan metode pembayaran mengandung `CASH`.
  - **Larangan Pembukaan pada Reprint / Non-Tunai:** Cetak ulang struk (*reprint*) dan pembayaran non-tunai (QRIS, Kartu, Transfer) dilarang memicu pembukaan laci kas secara otomatis.
  - **No-Sale Manual Pop:** Pembukaan laci kas manual tanpa transaksi wajib diverifikasi menggunakan **PIN Supervisor** dan mencatat alasan tertulis ke dalam `audit_logs`.
* **Perutean Pesanan Dapur & Bar Otomatis (*Kitchen Order Ticket - KOT*):**
  - `KitchenRoutingService` memilah item pesanan berdasarkan kategori produk dan mengarahkannya ke printer stasiun yang sesuai (Dapur Makanan Panas vs Bar Minuman) dengan tiket khusus berhuruf tebal, modifikasi topping, dan catatan koki.
* **Kompatibilitas Penuh (100% Backward Compatibility):** Menyediakan tombol cetak ganda di terminal dan struk kasir: tombol utama **Cetak ESC/POS Hardware** berkecepatan tinggi dan tombol cadangan **Cetak Bill (Browser Print)**.

### 4.16 Cetak Biru Penataan 6-Hub Modul & Rekomendasi Optimasi Performa End-to-End
* **Konsolidasi 6-Hub Modul Terpadu:**
  1. **Hub Operasional Kasir (POS):** Kasir Cepat (Touch/Barcode/Shortcut), Manajemen Meja & Dine-In, Tiket Dapur/Bar KOT, Laci Kas RJ-11 Safety Controller, Tutup Shift Blind Cash Count.
  2. **Hub Katalog & Logistik:** Katalog Produk & Varian, Resep Bahan Baku BOM (*Auto-BOM Deduction*), Multi-Gudang & Cabang, Mutasi & Penyesuaian Stok (Berita Acara & Approval), Two-Step In-Transit Transfer.
  3. **Hub Pengadaan & Pemasok (AP):** Direktori Supplier & Rekening Bank Resmi, Purchase Order MAR (*Maker-Approver-Releaser*), Three-Way Matching Penerimaan GRN, Retur Pembelian.
  4. **Hub Pelanggan & Kanal Digital (Commerce & CRM):** CRM Pelanggan & Plafon Kasbon, Poin Loyalitas & Voucher, Toko Online Storefront Publik, Pre-Order Dinamis, Ekspedisi Otomatis (Biteship).
  5. **Hub Keuangan, Pajak & SDM (Finance, Tax & HRM):** Kas & Rekening Bank Terpadu, Auto-Journaling Double-Entry, Auto-Reminder Piutang, Pajak UMKM (PP 55/PPh 21 TER/PPh Badan/PPN), Penggajian (Payroll, BPJS, THR, Komisi SPK).
  6. **Hub Ekosistem & Administrasi Platform:** WhatsApp Cloud API Meta Hub, Omnichannel Social Media (Meta/TikTok/LinkedIn UGC API), Landing Page Studio, Billing SaaS Tier (TriPay Gateway).
* **Rekomendasi Optimasi Performa & Ketahanan Sistem:**
  - **Tag-Based Caching Layer (Redis):** Cache master data aktif (`tenant_{id}:products`) dengan invalidasi otomatis via Eloquent Model Observers.
  - **Offline-First POS Resilience (IndexedDB / PWA):** Cache katalog lokal di browser kasir memungkinkan checkout tunai dan cetak ESC/POS lokal tetap berjalan saat koneksi internet toko terputus, dilengkapi antrean auto-sync saat online.
  - **Database Indexing & Zero N+1 Queries:** Indeks komposit pada tabel transaksi besar (`business_id, branch_id, status, created_at`) dan kewajiban Eager Loading (`with(['items.product', ...])`).
  - **Asynchronous Task Offloading:** Proses berat (PDF invoice, email digest, WhatsApp API, rekapitulasi data besar) dialirkan ke antrean worker latar belakang (*Laravel Queue*).
  - **Smart Workflows:** Global Barcode Scanner listener, Self-Service QR Table Ordering dengan Customer CRM Auto-Connect & akumulasi poin loyalitas otomatis, validasi nomor HP standar Indonesia (10–15 digit), dinamisasi pemicu layanan POS adaptif untuk 20 sektor industri (`service_workshop`, `service_laundry`, `retail_pharmacy`), Auto-Reorder PO saat stok menyentuh Reorder Point (ROP), dan Interactive Customer WhatsApp Bot.

### 4.16 Arsitektur AI Digital Company, Bring Your Own AI (BYOAI), dan Eksekutor Multi-Aksi Maker-Checker
Pusat Komando AI Digital Company (`/cooca-ai`) mengoperasikan 17 agen AI spesialis dalam struktur hirarki terkelola (AI CEO, COO, CFO, CMO) dengan kantor virtual 3D Three.js dan ruang komando Bento Apple HIG:
* **Multi-Provider BYOAI & Dynamic Model Discovery:**
  - Mendukung kunci API Anthropic Claude, OpenAI, Google Gemini, OpenRouter, serta Rule-Based Fallback Engine 100% Offline.
  - Model Anthropic terintegrasi langsung dengan Discovery API (`GET /v1/models`) untuk mendeteksi katalog model aktif (Claude 4.5/5 series) secara dinamis dengan pelacakan latensi real-time (~1.280ms).
  - Keamanan AES-256-CBC pada penyimpanan kunci API (`api_key` encrypted cast) dan penyamaran (*masking*) `••••••••` di UI.
* **Pipeline Eksekusi Multi-Aksi Terpadu (`AiActionExecutor`):**
  - **Prinsip Maker-Checker Mutlak:** AI tidak dapat langsung memutasi database operasional tanpa otorisasi manusia. Seluruh proposal dicatat dengan status `PENDING` pada tabel `ai_action_proposals` dan diverifikasi sebelum eksekusi.
  - **Dukungan Spektrum Aksi Nyata:**
    1. *Sales & Billing:* `create_invoice`, `create_quotation` otomatis menerbitkan dokumen penjualan di tabel `invoices` / `quotations`.
    2. *Supply Chain:* `create_purchase_order` menyiapkan draf pengadaan bahan baku ke tabel `purchase_orders`.
    3. *Finance & Cost Control:* `cost_structure_audit`, `cost_audit_and_optimization`, `financial_audit` menghitung beban OPEX aktual dari tabel `expenses`, mengkalkulasi rasio efisiensi beban terhadap pendapatan POS, menyusun target omzet break-even minimum, dan mencatat laporan audit berkode `AUD-COST-xxxx`.
    4. *Growth & Marketing:* `revenue_acceleration`, `revenue_growth_initiative`, `launch_marketing_campaign` mengaktifkan inisiatif kampanye percepatan omzet dan materi promosi WhatsApp/Medsos.
    5. *System Maintenance:* `system_maintenance`, `data_integrity_check` menjalankan diagnostik performa query dan integritas tabel POS.
    6. *Strategic Directive Fallback:* Setiap usulan aksi baru yang dirumuskan LLM ditangani secara aman dengan mencatat direktif resmi ke dalam `audit_logs` dan snapshot `result` tanpa melempar fatal exception.
  - **Idempotensi & Transaksi Atomik:** Setiap proposal memegang `idempotency_key` dan dieksekusi di dalam `DB::transaction` ber-lock pesimistik (`lockForUpdate`). Upaya eksekusi berulang mengembalikan status sukses secara idempotent tanpa duplikasi aksi.
* **Kepatuhan Bento Apple HIG & Zero Native Dialogs:**
  - Halaman Action Center (`/cooca-ai/actions`) dan UI AI Office bebas dari dialog browser `alert()`, `confirm()`, dan `prompt()`.
  - Mengadopsi suite modal dan frosted-glass toast terpusat `AppAlert` (`AppAlert.confirm`, `AppAlert.success`, `AppAlert.error`).
  - Modal Review Sheet menyajikan 6 pilar analisis lengkap: *WHY*, *WHAT*, *IMPACT & RISK*, *CHANGES/PAYLOAD*, *EXECUTION RESULT*, dan *ERROR FEEDBACK*.

### 4.17 Arsitektur Limitasi Subscription, Downgrade Auto-Gating, Pelacakan Storage & Data Pruning Previewer
* **Transparansi Limitasi Paket pada UI:** Setiap batas kuota fitur (produk, staf, cabang, storage, transaksi bulanan, kuota WhatsApp) disajikan secara jelas dan transparan melalui meter progress bar dan badge status berwarna semantik.
* **Mitigasi Downgrade Non-Destruktif (*No Data Punishment*):**
  - Jika masa aktif langganan habis atau pengguna downgrade paket (misal: memiliki 1.000 produk saat di plan Prestige, lalu beralih ke plan Standard dengan kuota 50 produk), sistem **TIDAK PERNAH menghapus data produk ke-51 s/d 1.000**.
  - Produk over-quota secara otomatis di-suspend dari katalog penjualan (POS & Toko Online), sementara 50 produk pertama tetap aktif normal.
  - Pada dashboard produk merchant (`/products`), produk yang terkunci ditandai dengan badge gembok Lucide `lock` bertuliskan *"Terkunci (Limitasi Plan)"*.
* **Auto-Reactivation Instan:** Begitu pembayaran perpanjangan paket berhasil diproses (misal via Webhook TriPay status `'PAID'`), sistem secara otomatis membuka kunci (*auto-unlock*) seluruh produk yang tersuspend tanpa perlu pengaturan ulang satu per satu.
* **Pelacakan Kapasitas Storage & Database:** Melacak konsumsi penyimpanan media upload (foto produk, bukti transfer), log aktivitas (`audit_logs`), dan log komunikasi (`whatsapp_logs`, `notification_logs`) per akun Owner secara real-time.
* **Pusat Pembersihan Data Mandiri (*Data Pruning Hub*) dengan Preview Transparan:**
  - Owner dapat membersihkan log lama (> 90 hari) dan file media yatim (*orphan media*) secara mandiri.
  - **Wajib Dialog Preview (Modal Sheet Full-Size XXL):** Menampilkan rincian jumlah baris data yang akan dihapus, rentang tanggal data, sampel data teratas, dan estimasi megabyte (MB) yang berhasil dihemat.
  - **No-Panic Microcopy:** *"Tenang: Pembersihan log aktivitas lama tidak akan pernah menghapus data transaksi penjualan, nota kasir, faktur invoice, atau laporan keuangan pembukuan Anda."*
  - Eksekusi pembersihan dijalankan aman di latar belakang via *Chunked Queue Job* setelah konfirmasi dua langkah Owner.

### 4.18 Arsitektur F&B Channel Multi-Pricing, Online Delivery Tags & Product Bundling Engine (Phases 1-4)
* **Mesin Multi-Harga Saluran Penjualan F&B (`product_channel_prices`):**
  - Mendukung 5 kanal penjualan: `dine_in`, `takeaway`, `gofood`, `grabfood`, `shopeefood`.
  - `PosTerminalWebController::index()` meng-eager load relasi `channelPrices` dan merender segmented channel selector pill bar bergaya Apple HIG.
  - State Alpine.js `salesChannel` mere-kalkulasi harga item keranjang belanja secara instan (`getProductPrice()`) dengan fallback ke harga dasar produk jika harga saluran belum diset.
  - Dialog modal sheet menangani input nomor referensi pesanan eksternal (`external_order_ref`) untuk order online delivery.
  - Lencana kontras tinggi pada Kanban Dapur (`kitchen.blade.php`) dan penandaan baris saluran pada struk thermal ESC/POS (`receipt.blade.php` & `PosReceiptImageService.php`).
* **Mesin Paket Kombo / Bundling Produk (`product_bundle_items`):**
  - Kolom `is_bundle = true` pada tabel `products` dan tabel anak `product_bundle_items`.
  - **Kalkulasi Akumulasi HPP (`Product::getBundleHpp()`):** Menjumlahkan seluruh `base_cost` atau HPP aktif dari costing run masing-masing produk anak dikali kuantitasnya, menjaga akurasi `total_hpp_cost` dan `total_gross_profit` pada `pos_orders`.
  - **Aturan Stok Efektif Bottleneck:** Stok paket kombo dihitung dinamis dari stok fisik item anak: $\min_{i} \lfloor \text{child\_stock}_i / \text{qty}_i \rfloor$. Jika salah satu komponen anak habis, stok paket kombo otomatis bernilai `0.0`.
  - **Pemotongan & Pengembalian Stok Rekursif:** `StockService::deductForProductSale()` memotong stok produk anak fisik pada `inventory_stocks` dan merekursi `getMaterialDeductions()` (Case 0) untuk produk anak bertipe resep BOM secara atomik. Pada aksi refund/void kasir, `StockService::restoreForPosRefund()` memulihkan kembali seluruh komponen anak secara otomatis.
  - **Antarmuka Master Produk (Bento Apple HIG):** Dynamic child item repeater pada modal tambah/ubah produk dengan live estimasi total HPP modal dan harga normal.

### 4.19 Arsitektur Manajemen Gudang & Multi-Cabang Terpadu, Geocoding GPS, dan Mitigasi Fraud Internal
* **Arsitektur Multi-Hierarki Cabang & Titik Simpan Fisik (`locations`):**
  - Struktur self-referencing `parent_id` membedakan Gudang Pusat (DC) penampung kontainer supplier dari Sub-Gudang Cabang (Gudang Belakang, Etalase Depan, Dapur/Workshop Produksi).
  - Agregasi ketersediaan stok fisik kasir POS dihitung dinamis via `Location::resolveLocationIds()` tanpa membuat saldo cabang fiktif.
  - Pemotongan stok otomatis (`StockService::deductForProductSale`) mendahulukan saldo cabang lalu merekursi ke sub-gudang fisik yang menyimpan barang secara atomik.
* **Geocoding Otomatis & Geofencing Absensi Staf:**
  - Integrasi Geolocation API HTML5 dengan backend reverse-geocoding (`GeoLocationService`) yang memetakan koordinat lat/lng satelit ke nama jalan, kelurahan, kecamatan, dan kode pos secara instan.
  - Pencarian area kurir Biteship terintegrasi otomatis untuk menetapkan titik penjemputan (*origin location*) kurir toko online storefront (`CommerceStoreSetting`).
  - Radius geofence presisi (`geofence_radius_meters`) menjadi pagar virtual absensi karyawan di portal staf (`/portal`).
* **Audit Proteksi Fraud & Kepatuhan Integritas Buku Besar (Defensive Controls):**
  - Penyesuaian stok cepat (`quickAdjust`) wajib divalidasi kepemilikan tenant (`business_id`) untuk mencegah kerentanan IDOR.
  - Penyesuaian stok minus (write-off) bernilai tinggi wajib dilindungi otorisasi **Supervisor PIN**, pemilihan kode Berita Acara baku (rusak, kadaluarsa, selisih opname, hilang), serta memicu pencatatan jurnal akuntansi otomatis (*Auto-Journal Beban Kerugian Selisih Persediaan* vs *Persediaan Barang Dagang*) agar Neraca Keuangan tetap seimbang dengan stok fisik riil.
  - Penghapusan lokasi fisik dilarang menggunakan hard-delete jika telah memiliki jejak transaksi historis (`stock_movements`, `goods_receipts`, `pos_orders`) guna mencegah kerusakan integritas foreign key dan kehilangan audit trail.
* **Penegakan Antarmuka Sadar Konteks (*Context-Aware UI*) untuk 20 Sektor Industri:**
  - Opsi dropdown `central_kitchen` ("Dapur Pusat") dikawal ketat oleh `template_code` agar hanya tampil untuk industri kuliner/F&B, dan bertransformasi menjadi *"Pabrik / Workshop Produksi"* pada manufaktur atau *"Basecamp / Workshop Proyek"* pada kontraktor.
  - Tombol aksi *"Pengiriman Storefront"* dan *"Katalog Bahan"* disembunyikan otomatis jika modul `merchant_shipping` atau `recipe_bom` dinonaktifkan oleh preset industri pengguna.

---

### 4.20 Arsitektur Shell Navigasi Sidebar Bento Apple HIG, Invisible Hover Bridge & RBAC Paritas
* **Arsitektur Shell Navigasi Dua Mode (Expanded 272px & Collapsed Rail 76px):**
  - Mengatur navigasi 71 rute modul bisnis UMKM terdistribusi dalam 8 grup terpadu (Overview, POS Kasir, Penjualan B2B, Produk & Logistik, Pembelian & Supplier, Pelanggan & Pemasaran, Keuangan & Biaya, Laporan & Analitik, dan Pengaturan Usaha).
  - Mengimplementasikan standar ergonomi sentuh Apple HIG dengan tinggi target klik minimal 44px (`--sidebar-row: 2.75rem`), radius squircle `rounded-[8px]`, dan micro-copy ramah pengguna senior (40–65 tahun).
* **Rekayasa Jembatan Hover Anti-Flicker (*Invisible Hover Bridge*):**
  - Mengatasi celah fisik 8px antara rel sidebar (76px) dan popover flyout (`left-[84px]`).
  - Seluruh 9 kontainer flyout (`div[x-show*="sidebarCollapsed && activeFlyout === ..."]`) dilengkapi pseudo-elemen CSS tak kasat mata selebar 16px ke kiri:
    `before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50`.
  - Mencegah *mouse-leave* / penutupan popover mendadak saat pengguna menggerakkan kursor diagonal menggunakan trackpad atau mouse.
* **Penegakan Paritas Tautan 100% Antar-Mode:**
  - Menghilangkan diskrepansi 12 menu yang sebelumnya hilang saat mode collapsed, memastikan seluruh menu Master Data Logistik (Kategori Produk, Bahan Baku, Satuan Ukur, Impor Excel), Omnichannel (WhatsApp Broadcast, Jadwal Medsos), Kalkulasi (Simulator HPP, Biaya Mesin), dan HR (Data Karyawan & Slip Gaji) dapat diakses identik pada kedua mode.
* **Palet Warna Aksen Semantik Apple HIG Resmi:**
  - Ikon grup pada rel collapsed dan akordeon aktif menggunakan palet semantik Apple HIG terstandarisasi untuk memori visual instan:
    - POS & Dashboard: `#007AFF` (Apple System Blue)
    - Penjualan B2B: `#5856D6` (Apple Indigo)
    - Logistik & Stok: `#FF9500` (Apple Amber)
    - Pengadaan & Supplier: `#30B0C7` (Apple Cyan)
    - Pemasaran & Saluran: `#FF2D55` (Apple Rose)
    - Kas & Keuangan: `#34C759` (Apple Emerald)
    - Laporan & Pajak: `#AF52DE` (Apple Purple)
    - Pengaturan Usaha: `#8E8E93` (Apple Slate)
* **Penegakan Keamanan RBAC & Isolasi Status Aktif (Zero Active Collision):**
  - Rute bantuan dan pelaporan bug (`feedback.bugs.index`) dilindungi eksklusif oleh `@if (\App\Support\Context::isOwner())` di kedua mode, mencegah HTTP 403 bagi staf non-owner.
  - Rute impor massal (`/import`) diselaraskan dengan backend middleware `require.permission:materials.view,products.view`.
  - Rute pajak `tax.index` dikonsolidasikan tunggal ke Grup 7 (Laporan & Analitik) dengan label resmi *"Laporan Pajak & Kepatuhan"* dan guard `reports.view || isOwner() || canAccessFinance`.
  - **Isolasi Penuh State Aktif (`settings.index`):** Wildcard `settings.*` diisolasi ketat dengan mengecualikan sub-modul audit logs, rules, roles, dan pos-printers, menjamin 0 false-positive active highlight di seluruh navigasi.
  - **Unifikasi Audit Log & Peringatan Risiko (Opsi B):** Mengeliminasi duplikasi tautan "Peringatan Audit" dari Ringkasan & Dashboard, serta memusatkan badge indikator insiden risiko tinggi (`$recentHighRiskCount`) langsung menempel di samping label *"Jejak Audit & Anti-Fraud"* di menu Pengaturan Usaha pada mode Expanded maupun Flyout.

### 4.18 Hardening Keamanan Siber POS Hardware, Proteksi Fraud Kasir & Otorisasi PIN (PRD-17)

Sistem mengimplementasikan penguatan keamanan siber dan proteksi anti-fraud kasir menyeluruh pada modul Point of Sale:
1. **SSRF Guardrail & Sanitizer IP Printer LAN (`PrinterIpValidator`):**
   - Seluruh input alamat IP printer LAN/WiFi divalidasi ketat dan memblokir alamat IP loopback (`127.0.0.0/8`, `::1`, `localhost`), link-local dan metadata cloud (`169.254.0.0/16`, `169.254.169.254`, `metadata.google.internal`), broadcast, dan hostname internal.
   - Pengecekan diterapkan ganda pada lapisan controller (`PosPrinterWebController@store` & `@update`) dan lapisan socket connector (`NetworkConnector@send` & `@testConnection`).
   - Timeout koneksi socket TCP dibatasi maksimal 2.0 detik untuk mencegah ancaman *connection hang* dan *resource exhaustion*.
2. **Eliminasi Fallback Insecure PIN Default:**
   - Menghapus fallback PIN hardcoded `'1234'`. Jika pemilik usaha belum menyetel PIN Supervisor pada menu Pengaturan, seluruh tindakan sensitif (pembatalan/void, pengembalian dana/refund, dan pembukaan laci kas manual tanpa transaksi/no-sale) ditolak otomatis dengan pesan jelas yang mewajibkan Owner mengatur PIN 6 digit ter-hash Bcrypt terlebih dahulu.
3. **Server-Authoritative Pricing (`PosOrderService@checkout`):**
   - Seluruh harga produk dalam keranjang belanja diverifikasi dan dihitung ulang secara ketat di sisi server berdasarkan harga katalog master database (`Product::selling_price`) dan harga channel penjualan aktif (`ProductChannelPrice`). Payload `unit_price` yang dikirim dari browser klien diabaikan untuk mencegah manipulasi harga via skrip JavaScript atau tampering HTTP.
4. **Endpoint Asinkron Validasi Voucher Real-Time (`POST /pos/validate-voucher`):**
   - Setiap penggunaan kode voucher divalidasi secara real-time ke backend untuk memeriksa kepemilikan tenant (`business_id`), rentang masa aktif, kuota penggunaan, dan batas minimum belanja (`min_order_amount`), menggantikan kalkulasi mock statis di frontend.
5. **Strict Blind Cash Count (`PosShiftWebController@summary`, `shifts.blade.php`, `terminal.blade.php`):**
   - Untuk mencegah tindak penggelapan kas laci (*cash skimming*), kasir biasa diwajibkan menghitung dan memasukkan uang fisik di laci tanpa melihat nilai ekspektasi sistem (`expected_cash` di-mask menjadi `null` pada API response).
   - Nilai ekspektasi kas dan selisih kas (*variance*) hanya dirender untuk pengguna berstatus `Owner` atau `Supervisor`. Prefill otomatis kas dihilangkan dari inisialisasi Alpine.js modal tutup shift.

### 4.19 Hardening Komprehensif 7 Modul View & Remediasi 20 Sektor Industri (PRD-18)

Sistem mengimplementasikan cetak biru pembersihan menyeluruh pada 7 folder antarmuka utama (`pos`, `products`, `social_media`, `warehouse`, `tax`, `marketplace`, `finance`):
1. **Zero Full Page Reload (`window.location.reload()` Ban):** Seluruh aksi sinkronisasi, rekonsiliasi, dan pembaruan metrik beralih ke arsitektur reaktif Smart Fetch + Alpine.js dengan notifikasi toast frosted glass sub-100ms.
2. **Dynamic Auto-Hiding 20 Industri:** Bento Box Multi-Harga Saluran POS (Ojol) dan menu Layar Dapur (KDS)/Meja Resto dilindungi oleh kondisi `$activeBiz->isModuleEnabled('pos_dinein')`, mencegah kebocoran antarmuka ke sektor bengkel, apotek, kontraktor, dan garmen.
3. **Modal-First Bento XXL Canvas:** Modal formulir pengeluaran beban (`expenses.blade.php`) dan akun kas/bank diperlebar ke standar `max-w-[95vw] lg:max-w-5xl 2xl:max-w-[1250px]` dengan 12-kolom grid ergonomis untuk pengguna usia 40–65 tahun.
4. **Deep-Linking URL `?tab=` pada Simulator Pajak:** Tab kalkulasi pajak tersinkronisasi persisten ke query URL dan menggunakan token warna semantik Apple HIG Bento.
5. **Maker-Checker Ambang Batas Stok Gudang:** Penyesuaian stok bernilai moneter tinggi diwajibkan melalui persetujuan Owner sebelum memotong kartu stok dan jurnal HPP.
6. **Hardening Otorisasi Supervisor PIN (Strict Bcrypt & Audit Trail):** Seluruh tindakan sensitif kasir (Void, Refund, Buka Laci Kas Manual No-Sale, Quick Adjustment) diwajibkan melewati verifikasi hash Bcrypt (tanpa fallback plaintext atau default `1234`), rate limiting 5 percobaan salah dengan masa kunci 10 menit, dan pencatatan otomatis insiden `POS_SUPERVISOR_PIN_LOCKED` ke tabel `audit_logs`.
7. **Arsitektur Multi-Bahasa Full-Stack (i18n & l10n ID & EN):** Seluruh respon backend (flash alerts, validasi request, domain exceptions, response JSON payload) dan teks frontend dilokalisasi secara dwibahasa (Bahasa Indonesia `id` & English `en`) melalui dictionary terstruktur di `lang/id/` dan `lang/en/`.

*Rencana Implementasi 9 Fase Terstruktur:* [`docs/IMPLEMENTATION_PLAN_COMPREHENSIVE_7_MODULES_REMEDIATION.md`](file:///c:/laragon/www/cooca_core/docs/IMPLEMENTATION_PLAN_COMPREHENSIVE_7_MODULES_REMEDIATION.md)

### 4.21 Arsitektur Otomasi Pengingat Termin Pembayaran Pelanggan (WhatsApp & Email Tri-Channel, Scheduler 08:30 WIB & Anti-Spam Guard)

Sistem mengimplementasikan mesin otomasi pengingat piutang dagang dan termin invoice pelanggan (*Customer Payment Term Reminder Engine*) yang beroperasi secara terjadwal dan manual:
1. **Tri-Channel Dispatcher Terpadu (`CustomerPaymentTermReminderService`):**
   - Mendukung pengiriman melalui 3 kanal: WhatsApp, Email HTML responsif Apple HIG, atau keduanya secara simultan (`channel: both`).
   - Saluran WhatsApp memanfaatkan WhatsApp Cloud API terverifikasi Meta dengan fallback ke gateway/tautan interaktif `https://wa.me/...`.
   - Saluran Email menggunakan mailable terisolasi (`CustomerPaymentTermReminderMail`) dengan template Blade bertema Bento Apple HIG, kartu ringkasan piutang, status lencana dinamis, rincian rekening bank transfer usaha, dan tombol aksi bayar instan.
2. **Interval Waktu Cerdas (Upcoming H-3, Hari H, & Overdue Cadence):**
   - **H-3 Mendekati Jatuh Tempo (`upcoming_h3`):** Pengingat santun untuk membantu pelanggan mempersiapkan kas tanpa merasa tertekan.
   - **Hari H Jatuh Tempo (`due_date`):** Notifikasi formal pada tanggal jatuh tempo.
   - **Terlambat / Overdue (`overdue`):** Eskalasi berkala bertahap pada Hari ke-1 terlambat, Hari ke-3, Hari ke-7, dan setiap kelipatan 7 hari berikutnya untuk menjaga penagihan konsisten tanpa spam harian yang mengganggu.
3. **Guard Anti-Spam & Deduplikasi Harian Deterministik:**
   - Tabel audit log terisolasi `customer_term_reminders` mencatat riwayat pengiriman per `(business_id, invoice_id, reminder_type, sent_date)`.
   - Menggunakan query dialek aman `whereDate('sent_date', $today->toDateString())` untuk mencegah pengiriman ganda pada hari yang sama meskipun scheduler cron dieksekusi berkali-kali.
4. **Artisan Console Scheduler (`customers:send-term-reminders`):**
   - Dijadwalkan otomatis setiap hari kerja pukul 08:30 WIB (`routes/console.php`) menggunakan opsi `--business=` opsional untuk batching per tenant atau menyeluruh.
5. **Aksi Cepat Interaktif pada Antarmuka Pemilik (Web UI Trigger):**
   - **Halaman Detail Invoice (`invoices.show`):** Tombol aksi *"Kirim Pengingat"* memunculkan Sheet Modal Bento dengan pratinjau teks WhatsApp, pilihan kanal (WhatsApp / Email / Keduanya), catatan khusus pengirim, serta tautan langsung klik `wa.me`.
   - **Tabel Pelanggan (`customers.index`):** Aksi cepat *"Tagih via WhatsApp"* langsung membuka modal atau tautan wa.me untuk pelanggan yang memiliki saldo piutang tertunggak (`unpaid_invoices_count > 0`).

*Dokumen Audit & Rencana Kerja:* [`docs/system/audits/customer-payment-terms-auto-reminder-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/customer-payment-terms-auto-reminder-plan.md)

### 4.22 Arsitektur COOCA AI Digital Company (Autonomous Workforce, Executive Hierarchy C-Level, Multi-Provider BYOAI, Maker-Checker Gate & Bento AI Office)

COOCA AI Digital Company mentransformasikan AI dari sekadar asisten percakapan teks pasif menjadi **angkatan kerja digital otonom (*Digital Workforce Layer*)** yang bekerja terstruktur mendampingi pemilik usaha:
1. **Prinsip Utama: "LLM May Reason, But Laravel Remains The Authority":**
   - AI dilarang melakukan eksekusi raw SQL liar atau mutasi database langsung. Seluruh aksi bisnis (pembuatan faktur, PO belanja supplier, penyesuaian stok, broadcast kampanye) diperlakukan sebagai **Proposal Aksi (`ai_action_proposals`)** dengan status `PENDING`.
   - Manusia (Owner/Supervisor) memegang kendali penuh di **Action Center (`/ai/actions`)** untuk meninjau *WHY, WHAT, IMPACT, COST, dan CHANGES* sebelum menyetujui atau menolak.
2. **Dewan Eksekutif (C-Level AI Suite) & 5 Departemen Fungsional:**
   - **AI CEO:** Evaluasi kesehatan makro bisnis, health-check harian otomatis pukul 07:00 WIB (`cooca:ai-daily-business-check`), rekomendasi strategis.
   - **AI COO:** Pengawas rantai pasok stok pergudangan, purchase order, dan deteksi anomali transaksi kasir/fraud.
   - **AI CFO:** Pengendali keuangan, likuiditas kas & bank, rasio piutang tertunggak (*AR aging*).
   - **AI CMO:** Perancang strategi promosi, efektivitas broadcast WhatsApp, dan keterlibatan pelanggan.
   - **AI HR Lead:** Pengawas kehadiran karyawan, kepatuhan jam kerja, dan beban shift.
   - **12 Agen Spesialis:** Terdistribusi di 5 squad fungsional (Executive, Sales, Operations, Finance, Marketing, People) dengan routing otomatis berbasis kata kunci intensi.
3. **Multi-Provider BYOAI (Bring Your Own AI):**
   - Mendukung multi-vendor terkemuka: OpenAI (`gpt-4o`, `gpt-4o-mini`), Google Gemini (`gemini-1.5-pro`, `gemini-1.5-flash`), Anthropic Claude (`claude-3-5-sonnet`), dan OpenRouter.
   - **Offline Deterministic Fallback:** Mesin inferensi bisnis deterministik berbasis data lokal yang dapat beroperasi 100% tanpa internet atau saat kuota API habis.
   - Kunci API dienkripsi di basis data menggunakan AES-256-CBC, disembunyikan (`$hidden`), dan hanya ditampilkan bertopeng (`••••••••`).
4. **Idempotensi & Transaksi Database Atomik:**
   - Setiap proposal dilengkapi `idempotency_key` (`ACT-xxxx`). Proposal yang telah dieksekusi terlindungi dari re-eksekusi tidak disengaja (*idempotency guard*).
   - Eksekusi diselimuti `DB::transaction` atomik dengan verifikasi keanggotaan pengguna dan pencatatan jejak audit permanen ke `audit_logs` dan `ai_work_histories`.
5. **Antarmuka Bento Apple HIG AI Office:**
   - Dashboard modular di `/ai` dengan Executive Suites, Department Pods, status tim, dan modal konsultasi interaktif multi-agen.
   - Kepatuhan total terhadap standar desain Apple HIG: sudut membulat proporsional, border hairline halus, dan **Zero Emoji Policy** (100% menggunakan ikon vektor Lucide SVG).
6. **Arsitektur RAG Multi-Tenant & Zero-Hallucination Guardrails:**
   - 4 Sumber Pengetahuan: `TenantMasterDataKnowledgeSource` (produk, stok fisik via `stocks`, supplier, pelanggan, rekening kas), `TenantOperationalHistoryKnowledgeSource` (faktur piutang, POS orders, PO, mutasi kas, audit log), `UmkmRegulationsKnowledgeSource` (Pajak PP 55 0,5%, PPh 21 TER PP 58/2023, PPN 11%, SOP Anti-Fraud kasir toleransi Rp 10.000, rumus Safety Stock & ROP), dan `AiSystemCapabilitiesKnowledgeSource`.
   - Mengharuskan sitasi faktual (misal `[Katalog Produk: #KGA-001]`) dan kewajiban menjawab *"Data tidak ditemukan dalam sistem"* jika fakta tidak terverifikasi.
7. **Context Engineering Pipeline 5-Layer & Dual-Monitor Real Data di 3D Virtual Office:**
   - Menyusun prompt sistem dinamis: Identity & Meta-Directives, Role Directives untuk 17 peran agen, Profil Tenant, Injeksi Pengetahuan RAG, serta Anti-Hallucination & Token Budgeting.
   - Tiap meja kerja di 3D Virtual Office memiliki **dua monitor real-time**: Monitor 1 (Strategic KPI & Bar/Line Chart 7 hari) dan Monitor 2 (Live Operational Feed tabel 3 kolom faktual) dengan pembaruan otomatis latar belakang (*background polling 30s*) tanpa reload halaman.

*Dokumentasi Lengkap:* [`docs/system/modules/ai-digital-company.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/ai-digital-company.md) | Folder Arsitektur AI: [`docs/ai/`](file:///c:/laragon/www/cooca_core/docs/ai/)

### 4.23 Arsitektur Hardening Modul Gudang & Pemasok (Bento Apple HIG, IDOR Precedence Shield, N-Tier Cycle Traversal, & 100% i18n Parity - PRD-32)

1. **Proteksi IDOR & SQL Precedence Shield:**
   - Method `resolveRouteBinding()` pada model Eloquent `Supplier`, `Product`, `ProductCategory`, `Material`, dan `MaterialCategory` mengisolasi klausa `where` dan `orWhere` dalam closure callback terpisah:
     ```php
     public function resolveRouteBinding($value, $field = null)
     {
         return $this->where(function ($query) use ($value): void {
             $query->where('id', $value)
                   ->orWhere('slug', $value);
         })->firstOrFail();
     }
     ```
   - Menghasilkan query SQL deterministik: `WHERE business_id = ? AND (id = ? OR slug = ?)`, mencegah eskalasi akses data lintas tenant secara mutlak.
2. **Algoritma Pencegahan Siklus Hirarki Gudang Bertingkat (N-Tier Ancestor Traversal):**
   - Penataan cabang/gudang (`parent_id`) pada `WarehouseWebController::update` dilengkapi penelusuran rantai leluhur (*ancestor chain traversal*) ke atas:
     ```php
     $curr = Location::where('business_id', $business->id)->find($validated['parent_id']);
     while ($curr && ! empty($curr->parent_id)) {
         if ((string) $curr->parent_id === (string) $location->id) {
             throw ValidationException::withMessages([
                 'parent_id' => [__('warehouse.validation.parent_descendant')],
             ]);
         }
         $curr = Location::where('business_id', $business->id)->find($curr->parent_id);
     }
     ```
   - Mencegah infinite loop dan kerusakan pohon struktur organisasi cabang pada kedalaman bertingkat bebas.
3. **Arsitektur Antarmuka Bento Apple HIG & Deep-Linking Tabs:**
   - **Warehouse Index (`/warehouse`):** Segmented Control Filter (`filterTab`) tersinkronisasi dua arah dengan parameter URL `?type=all|outlet|warehouse` via `window.history.replaceState()`.
   - **Warehouse Show (`/warehouse/{location}`):** 4 Underline Tabs Bento HIG (`stocks`, `receipts`, `movements`, `approvals`) terhubung URL `?tab=...`.
   - **Suppliers Index (`/suppliers`):** Bento XXL 2-Kolom Modal Sheet (`max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl`) dengan pemisahan Profil/Kontak PIC (kiri) dan Rekening Bank/Ketentuan TOP (kanan).
   - **Eliminasi Native Confirm:** Mengganti 100% dialog native browser `confirm()` dengan Bento Confirmation Modal Sheet yang elegan.
4. **Adaptasi 20 Template Industri & Auto-Hiding BOM:**
   - Label Central Kitchen beradaptasi otomatis berdasarkan `$business->template_code`: *"Dapur Pusat"* (F&B), *"Pabrik / Workshop Produksi"* (Manufaktur), *"Basecamp / Workshop Proyek"* (Kontraktor).
   - Tombol "Katalog Bahan Baku" disembunyikan otomatis pada industri non-BOM dengan evaluasi `isModuleEnabled(ModuleRegistry::MODULE_RECIPE_BOM)`.
5. **Kamus Bahasa Multi-Locale & Dual-Mode Controller:**
   - Paritas 1-to-1 antara `lang/id/` dan `lang/en/` untuk kamus `warehouse.php`, `purchasing.php`, dan `inventory.php`.
   - Controller menyediakan dual response: Web standard redirect dengan flash message terlocalisasi dan AJAX JSON payload `{ success: true, message: string, location/supplier: model }`.
6. **Non-Destructive Archival Guard:**
   - Penghapusan cabang/gudang yang telah memiliki histori mutasi/transaksi (`StockMovement`, `GoodsReceipt`, `PosOrder`, dll.) secara otomatis dialihkan ke penonaktifan aman (`is_active = false`) dengan payload `deactivated: true`.

*Dokumentasi Lengkap:* [`docs/prd/PRD-32-WAREHOUSE-AND-SUPPLIERS-HARDENING-MULTI-INDUSTRY-I18N.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-32-WAREHOUSE-AND-SUPPLIERS-HARDENING-MULTI-INDUSTRY-I18N.md) | Audit Plan: [`docs/system/audits/warehouse-and-suppliers-master-implementation-plan.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/warehouse-and-suppliers-master-implementation-plan.md)

### 4.24 Arsitektur COOCA Online Store & Marketplace Production-Grade (Shopee-Standard Workflow, TriPay Auto-Journaling, AWB Tracking & Verified Reviews)

Sistem mengimplementasikan standarisasi menyeluruh pada modul Storefront & Marketplace agar setara dengan standar operasional e-commerce nasional:
1. **Cart to Dedicated Checkout Handoff Sync:**
   - Alur keranjang belanja multi-toko terisolasi (`CustomerCart`) tersinkronisasi mulus ke halaman Checkout Toko dedikasi (`public.storefront.checkout.page`).
   - Penegakan wajib login akun pelanggan (`GlobalCustomer`) dengan profil lengkap dan verifikasi WhatsApp OTP seumur hidup sebelum menyelesaikan pesanan, tanpa menghilangkan isi keranjang belanja.
2. **TriPay Gateway Auto-Journaling & Idempotensi Finansial:**
   - Webhook callback TriPay (`TripayCallbackController::handlePaymentSuccess`) membukukan jurnal akuntansi double-entry secara otomatis melalui `AutoJournalService::recordCommerceOrderJournal`:
     - Debit: Kas/Bank Payment Gateway (Akun `1-1002`) sebesar penerimaan bersih (`net_amount`).
     - Debit: Beban Administrasi Gateway / MDR (Akun `6-6003`) sebesar biaya admin (`fee`).
     - Kredit: Pendapatan Penjualan Toko Online (Akun `4-4003`) sebesar total transaksi (`amount`).
   - Transaksi dibungkus dalam `DB::transaction` atomik dengan pengecekan idempotensi ketat pada referensi pesanan dan nomor transaksi TriPay.
3. **Standardisasi Lifecycle & Transisi Status Pesanan:**
   - Model `CommerceOrder` mendukung status standar: `pending`/`pending_payment` ➔ `paid` ➔ `processing` ➔ `packed` ➔ `shipped` ➔ `delivered` ➔ `completed`.
   - Helper `isPaid()` mengenali status pengiriman (`shipped`, `delivered`, `completed`) sebagai transaksi lunas.
   - Merchant hanya dapat memajukan status pesanan sesuai urutan logis dan tidak dapat memanipulasi status pembayaran secara manual.
4. **Pusat Logistik & Tracking Resi AWB (Shopee-Grade):**
   - Halaman detail pesanan pelanggan menyajikan widget Bento AWB modern dengan fitur 1-klik salin nomor resi ke clipboard dan tautan pelacakan live ekspedisi resmi.
   - Visualisasi timeline 4-langkah progresif (*Dibuat ➔ Dikonfirmasi ➔ Dikirim ➔ Selesai*) memberikan kepastian status pengiriman kepada pembeli.
5. **Customer Actions & Pelepasan Stok Otomatis:**
   - Pembeli dapat membatalkan pesanan yang belum dibayar secara mandiri via `POST /customer/orders/{id}/cancel`, yang secara atomik melepaskan reservasi kuantitas stok melalui `StockService::releaseReservation`.
   - Pembeli dapat mengonfirmasi pesanan telah sampai via `POST /customer/orders/{id}/complete`.
6. **Sistem Ulasan Produk Terverifikasi (Verified Purchase Reviews):**
   - Menggunakan tabel `commerce_product_reviews` dengan validasi kepemilikan pesanan (`auth:customer`) dan syarat status pesanan `completed` atau `delivered`.
   - Mencegah manipulasi ulasan fiktif dan serangan IDOR lintas pelanggan.
   - Agregasi skor bintang (`rating_average`) dan jumlah ulasan (`reviews_count`) ditampilkan secara elegan pada Product Detail Page (PDP).
7. **Katalog Publik Marketplace Terpadu:**
   - Endpoint eksplorasi publik terhubung dengan query database teroptimasi: `/marketplace/businesses`, `/marketplace/products`, `/marketplace/categories`, dan `/marketplace/locations`.

*Dokumentasi Lengkap Modul:* [`docs/system/modules/commerce.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/commerce.md)

### 4.25 Arsitektur Universal Multi-Tenant Model Context Protocol (MCP) Server & Multi-Provider AI Adapter Hub
1. **Prinsip Isolasi Tenant Nol-Kebocoran (*Strict Zero-Leak Boundary*):**
   - Parameter `business_id` secara ketat dilarang untuk diekspos sebagai argumen MCP tool.
   - Identitas tenant murni ditentukan dari MCP Bearer Token (`mcp_access_tokens`) melalui `McpTokenAuthenticator` dan diikat ke `Context::requireBusiness()`.
2. **Katalog 10 Domain Tools Terpadu:**
   - Finansial: `finance_record_expense` (pencatatan struk OCR, auto-journal, & kas keluar), `finance_get_cash_and_bank_balances` (likuiditas kas & bank).
   - Inventori: `inventory_create_product` (katalog produk & stok awal), `inventory_check_stock` (stok real-time & peringatan stok menipis).
   - Pemasaran: `social_schedule_post` (penjadwalan omnichannel Meta/TikTok/X), `social_get_insights` (analisis engagement).
   - Laporan & AI: `report_get_profit_loss` (omzet, HPP, margin & laba bersih), `analytics_get_sales_forecast` (prediksi AI forecasting).
   - CRM & WhatsApp: `crm_search_customer` (profil pelanggan setia & riwayat order), `whatsapp_send_notification` (pengiriman pesan resmi WhatsApp Cloud API).
3. **Multi-Transport & Multi-Provider Adapter:**
   - **Local Stdio Transport:** `php artisan mcp:serve` untuk eksekusi CLI desktop (Claude Desktop, Cursor, Antigravity).
   - **Remote SSE Transport:** `GET /api/v1/mcp/sse` + `POST /api/v1/mcp/message` JSON-RPC 2.0.
   - **OpenAPI 3.1 Dynamic Spec:** `GET /api/v1/mcp/openapi.json` untuk integrasi 1-klik ChatGPT Custom Actions, LangChain, n8n, dan Dify.
   - **REST Direct Bridge:** `POST /api/v1/mcp/tools/{tool}/execute` untuk Google Gemini dan workflow webhook.
4. **Audit Trail & Sanitasi Payload:**
   - Seluruh pemanggilan tool otomatis terekam pada `mcp_activity_logs` dengan sanitasi data sensitif seperti gambar base64 yang dipotong ringkas demi efisiensi storage.

*Dokumentasi Lengkap Modul:* [`docs/system/modules/mcp-server-and-ai-integrations.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/mcp-server-and-ai-integrations.md)

---

## 5. Matriks Penelusuran Pengetahuan (Traceability Matrix)

Dokumentasi Cooca saling terhubung secara dua arah untuk memudahkan penelusuran asal-usul keputusan arsitektur:

```
[ LAYER 3: SYSTEM GUIDE (docs/SYSTEM_GUIDE.md) ]
   │
   ├──► Modul Costing & HPP ────────► docs/system/modules/costing.md ────► app/Domain/Costing/
   │                                                                        └──► Work History #001
   │
   ├──► Modul POS Kasir ───────────► docs/system/modules/pos.md ────────► app/Domain/Pos/
   │                                                                        └──► Work History #001
   │
   ├──► Modul POS Hardware & Printer► docs/system/modules/pos-hardware-and-printers.md ──► app/Domain/Printer/
   │                                                                                        └──► WORK-2026-09-25-151
   │
   ├──► Modul Gudang & Inventori ──► docs/system/modules/inventory.md ──► app/Domain/Inventory/
   │                                                                        └──► Work History #001
   │
   ├──► Modul Keuangan & Jurnal ───► docs/system/modules/finance.md ────► app/Domain/Finance/
   │                                                                        └──► Work History #001
   │
   ├──► Modul Toko Storefront ─────► docs/system/modules/commerce.md ───► app/Domain/Commerce/
   │                                                                        └──► WORK-2026-09-15-001
   │
   ├──► Arsitektur Multi-Tenant ───► docs/system/architecture/multi-tenancy.md ──► App\Support\Context
   │                                                                                └──► WORK-2026-09-15-002
   │
   │
   ├──► UI/UX Design System (v2) ──► docs/system/architecture/ui-ux-design-system.md ──► resources/views/layouts/
   │                                                                                             └──► WORK-2026-09-16-010
   │
   ├──► Diagnostik & Error Logs ───► docs/system/modules/system-diagnostics.md ────────► AdminErrorLogController
   │                                                                                            └──► WORK-2026-09-16-014
   │
   ├──► Admin Console & Analytics ─► docs/system/architecture/ui-ux-design-system.md ──► AdminDashboardController
   │                                                                                            └──► WORK-2026-09-16-020
   │
   ├──► Modul Katalog & Produk ─────► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/products/
   │                                                                                            └──► WORK-2026-09-17-044
   │
   ├──► Modul Bahan Baku & Pemasok ──► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/materials/
   │                                                                                            └──► WORK-2026-09-17-045
   │
   ├──► Modul Jasa & Layanan ────────► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/services/
   │                                                                                            └──► WORK-2026-09-17-045
   │
   ├──► Modul Storefront Hub ────────► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/storefront/
   │                                                                                            └──► WORK-2026-09-17-046
   │
   ├──► Modul Landing Page Studio ───► docs/system/architecture/ui-ux-design-system.md ──► resources/views/app/landing_page/
   │                                                                                            └──► WORK-2026-09-17-046
   │
   ├──► WhatsApp Cloud API ──────────► docs/system/architecture/multi-tenancy.md ─────────► app/Domain/WhatsApp/CloudApi/
   │                                                                                            └──► WORK-2026-09-17-048
   │
   ├──► Media Sosial (Meta & TikTok) ─► docs/social-media/architecture.md ─────────────────► app/Domain/SocialMedia/
   │                                                                                            └──► WORK-2026-09-17-053
   │
   ├──► Unified Settings & SMTP ───► docs/system/modules/settings.md ───────────────► AdminSettingController
   │                                                                                    └──► WORK-2026-09-18-082
   │
   ├──► Admin Recovery Desk ───────► docs/system/business-rules/security-rules.md ──► AdminAccountRecoveryController
   │                                                                                    └──► WORK-2026-09-18-079
   │
   ├──► Admin Billing Packages CMS ─► docs/system/modules/saas-billing.md ──────────► AdminBillingPackageController
   │                                                                                    └──► WORK-2026-09-18-080
   │
   ├──► Admin Posts & Cluster CMS ──► docs/system/modules/cms.md ───────────────────► AdminPostController
   │                                                                                    └──► WORK-2026-09-18-081
   │
   ├──► Modul HRM & Tax Engine ────► docs/system/modules/hrm-and-tax.md ────────────► app/Domain/Tax/ & app/Domain/HRM/
   │                                                                                    └──► WORK-2026-09-19-085
   │
   ├──► Modul Gudang & Cabang ─────► docs/system/workflows/warehouse-logistics-and-multi-branch-flow.md ──► resources/views/app/warehouse/
   │                                                                                                          └──► WORK-2026-09-27-187
   │
   ├──► Modul Produk & BOM ────────► docs/system/modules/costing.md ────────────────► resources/views/app/products/
   │                                                                                    └──► WORK-2026-09-26-171
   │
   ├──► POS Hardware & Shifts ─────► docs/system/modules/pos-hardware-and-printers.md ──► app/Domain/Printer/ & PosShiftService
   │                                                                                         └──► WORK-2026-09-25-154
   │
   ├──► Staff Portal & RBAC ────────► docs/system/workflows/staff-portal-and-attendance-flow.md ──► PortalWebController
   │                                                                                              └──► WORK-2026-09-26-180
   │
   ├──► POS Channel Multi-Pricing ──► docs/system/workflows/pos-channel-pricing-and-delivery-flow.md ──► PosTerminalWebController
   │                                                                                                   └──► WORK-2026-09-26-181
   │
   ├──► Product Bundling Engine ────► docs/system/workflows/product-bundling-and-combo-flow.md ───────► Product & StockService
   │                                                                                                    └──► WORK-2026-09-26-182
   │
   ├──► Shell Navigasi Sidebar ────► docs/prd/PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY.md ──► resources/views/layouts/partials/sidebar.blade.php
   │                                                                                                     └──► WORK-2026-09-28-206
   │
   ├──► Hardening 7 Modul View ────► docs/prd/PRD-18-COMPREHENSIVE-SYSTEM-HARDENING-7-MODULES-AND-MULTI-INDUSTRY-REMEDIATION.md ──► 7 Core View Modules
   │                                                                                                                                        └──► AUDIT-2026-09-29-001
   │
   ├──► Audit & Remediasi POS ─────► docs/system/audits/pos-master-implementation-plan.md ──► resources/views/app/pos/ & app/Domain/Pos/
   │                                                                                               └──► WORK-2026-09-30-256 / WORK-2026-10-04-292 (Fase 1 - 10 Remediasi Penuh)
   │
   ├──► Notifikasi Termin Pelanggan ──► docs/system/audits/customer-payment-terms-auto-reminder-plan.md ──► app/Domain/Crm/ & routes/console.php
                                                                                                           └──► WORK-2026-10-01-273
   │
   ├──► AI Digital Company ────────► docs/system/modules/ai-digital-company.md ─────────► app/Domain/Ai/ & resources/views/app/ai/
   │                                                                                                └──► WORK-2026-10-01-274 / WORK-2026-10-01-275 / WORK-2026-10-01-277 / WORK-2026-10-01-278 / WORK-2026-10-02-280 (BYOAI Providers, Claude 4.5/5 & Dynamic Discovery)
   │
   └──► Hardening Gudang & Pemasok ──► docs/prd/PRD-32-WAREHOUSE-AND-SUPPLIERS-HARDENING-MULTI-INDUSTRY-I18N.md ──► resources/views/app/warehouse/ & suppliers/
                                                                                                                   └──► WORK-2026-10-04-289 / WORK-2026-10-04-290 / WORK-2026-10-04-291 / WORK-2026-10-05-307 (Route warehouse.edit & Bento Modal Sheet Navigation)
    │
    ├──► MCP Server & AI Gateway ───► docs/system/modules/mcp-server-and-ai-integrations.md ──► app/Domain/Mcp/ & resources/views/app/settings/integrations/mcp.blade.php
    │                                                                                                   └──► WORK-2026-10-07-319 (Universal MCP Server, 10 Tools, Multi-Provider Support)
    │
    └──► Billing Packages & Promos ─► docs/system/modules/saas-billing.md ─────────────────────► AdminPromoController & SubscriptionCheckoutWebController
                                                                                                        └──► WORK-2026-10-07-320 (Subscription Promo Codes, Checkout Voucher Validation, Audit Usages)
```
