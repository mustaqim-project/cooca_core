# BLUEPRINT: SISTEM TIER LANGGANAN, LIMITASI & MONETISASI COOCA
**Versi:** 2.3 (Multi-Branch, Comprehensive HRM, BPJS, Loans, Daily Worker, Join-Date THR & Tax Compliance Engine)  
**Nomenklatur Tier:** Free, Standard, Premium, Prestige  
**Target Pasar:** Ekosistem 20 Sektor Industri UMKM & Korporasi Indonesia  
**Prinsip Utama:** *Storage & Entitas Melekat pada Owner*, *No Data Punishment*, *Graceful Degradation*, *Bento Apple HIG*, *Tax & Labor Compliance*


---

## 1. Eksekutif & Filosofi Fondasi Produk

Sistem monetisasi COOCA dibangun di atas tiga filosofi arsitektur inti:

### A. Storage Melekat pada Level Akun Owner (Bukan Toko/Bisnis)
- Seorang **Owner** (akun pengguna utama) dapat memiliki banyak entitas bisnis di bawah satu akun terpusat.
- Kuota Cloud Storage (1 GB s.d 30 GB + Top-Up) dihitung secara **kumulatif per Owner ID**.
- Seluruh berkas (foto produk, nota pengeluaran, QRIS, logo toko) dari **seluruh bisnis milik owner tersebut menyedot kapasitas penyimpanan yang sama**.
- **Efek Monetisasi:** Begitu owner mengekspansi bisnisnya (membuat Bisnis B atau Bisnis C), kapasitas storage terpakai lebih cepat, menciptakan kebutuhan organik untuk membeli *Top-Up Storage Permanen* atau *Upgrade Tier*.

### B. Komitmen "No Data Punishment"
- Jika masa aktif langganan habis atau kuota bulanan tercapai:
  - Sistem **TIDAK PERNAH menghapus data historis**.
  - Sistem **TIDAK PERNAH mengunci hak baca (*read-access*)**.
  - Rekam jejak pembukuan kas, histori struk kasir, data stok historis, dan kontak pelanggan tetap bisa diakses dan diekspor.
  - Pembatasan hanya berlaku pada **pembuatan data baru (*write-block*)** hingga kuota di-reset awal bulan atau dilakukan upgrade paket.

### C. Tangga Konversi Organik (*Natural Friction Ladder*)
- Paket Free bukan sekadar uji coba 14 hari, melainkan paket gratis selamanya yang sangat fungsional bagi solo-owner perorangan.
- Begitu bisnis mulai tumbuh (merekrut kasir, transaksi harian ramai, butuh resep HPP & layar dapur, atau membuka cabang kedua), limitasi sistem secara elegan mengarahkan owner untuk naik kelas.

---

## 2. Nomenklatur, Harga & Target Persona

| Tier | Harga Bulanan | Harga Tahunan (Diskon 2 Bln) | Target Persona UMKM | Nilai Utama (*Core Value Proposition*) |
| :--- | :---: | :---: | :--- | :--- |
| **FREE** | **Rp 0** | **Rp 0** | Usaha mikro rumahan, pedagang solo, warung pemula tanpa karyawan. | *Zero-friction onboarding*, langsung jualan via kasir HP/tablet tanpa kartu kredit. |
| **STANDARD** | **Rp 29.000** | **Rp 290.000** | Kedai kopi rintisan, booth minuman, laundry, toko retail kecil dengan 1–3 staf/kasir. | *"Seharga 1 cangkir kopi"*, bebas kuota transaksi POS, otorisasi PIN kasir & laci kas. |
| **PREMIUM**<br>*(Sweet Spot)* | **Rp 89.000** | **Rp 890.000** | Cafe F&B, resto dengan dapur, toko multi-cabang (s.d 3), produsen makanan, bengkel. | **Full ERP Operasional:** Layar dapur (KDS), resep BOM HPP, multi-gudang, transfer stok, HRM komisi, auto-jurnal. |
| **PRESTIGE**<br>*(Enterprise)* | **Rp 199.000** | **Rp 1.990.000** | Pengusaha multi-brand (*serial entrepreneur*), pemilik franchise, jaringan ritel modern. | **Skalabilitas Tanpa Batas:** Multi-bisnis & cabang unlimited, katalog digital publik, 30 GB storage, HRM payroll slip WA, PPh 21 TER. |

---

## 3. Matriks Kapasitas & Kuota (Resource Quota Matrix)

| Sumber Daya / Limitasi | FREE (Rp 0) | STANDARD (Rp 29k) | PREMIUM (Rp 89k) | PRESTIGE (Rp 199k) | Lokasi Penegakan Kode |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Level Akun Owner** | | | | | |
| 🏢 **Maksimal Entitas Bisnis** | **1 Bisnis** | **1 Bisnis** | **Hingga 3 Bisnis** | **∞ Unlimited Bisnis** | `EntitlementService::canCreateBusiness` |
| 👥 **Karyawan / Staf Terdaftar** | **1 (Solo Owner)** | **3 Karyawan** | **10 Karyawan** | **∞ Unlimited Karyawan** | `EntitlementService::canAddMember` |
| 💾 **Kapasitas Cloud Storage** | **1 GB** | **3 GB** | **10 GB** | **30 GB** | `OwnerStorageQuotaService::getBaseLimitBytes` |
| **Fasilitas Multi-Cabang Fisik** | | | | | |
| 🏪 **Cabang Outlet Toko** | 1 Outlet | 1 Outlet | **Hingga 3 Cabang** | **∞ Unlimited Cabang** | `EntitlementService::canCreateLocation('outlet')` |
| 🏭 **Gudang / Dapur Pusat** | 1 Gudang | 1 Gudang | **Hingga 3 Gudang** | **∞ Unlimited Gudang** | `EntitlementService::canCreateLocation('warehouse')`|
| 🚚 **Surat Jalan Transfer Stok** | ❌ Terkunci | ❌ Terkunci | **∞ Unlimited** | **∞ Unlimited** | `canTransferStockBetweenBranches` |
| 🏷️ **Multi-Pricing per Cabang** | ❌ (1 Harga) | ❌ (1 Harga) | ✅ Tersedia | ✅ Bebas per Cabang | `canSetBranchSpecificPrices` |
| **Fasilitas HRM & Ketenagakerjaan** | | | | | |
| 🔐 **Otorisasi PIN & Role Kasir**| ❌ | ✅ (PIN Kasir) | ✅ (Custom Role)| ✅ (Granular RBAC) | `canAssignCustomRoles` |
| ⏱️ **Presensi & Absensi Karyawan**| ❌ | Presensi POS | Presensi GPS/POS | Presensi Multi-Cabang | `canTrackAttendance` |
| 💰 **Sistem Komisi Kinerja Staf** | ❌ | ❌ | ✅ (Komisi Struk) | ✅ (Komisi Bertingkat) | `canCalculateCommissions` |
| 💳 **Pinjaman & Kasbon Karyawan** | ❌ | Catat Kasbon Sederhana | ✅ (Buku Cicilan Kasbon) | ✅ (Auto-Deduct & Plafon) | `canManageEmployeeLoans` |
| 👷 **Pekerja Harian (Daily Worker)**| ❌ | ❌ | ✅ (Upah Harian/Shift) | ✅ (Daily Worker + Borongan) | `canManageDailyWorkers` |
| 🏥 **BPJS TK & BPJS Kesehatan** | ❌ | ❌ | Hitung Persentase Riil | **Modul BPJS Terpadu** | `canCalculateBPJS` |
| 🎁 **THR Otomatis Berbasis Join Date**| ❌ | ❌ | Kalkulator THR Manual | **Auto-THR Proporsional WA**| `canCalculateTHR` |
| 📑 **Penggajian / Slip Gaji WA**| ❌ | ❌ | Rekap Gaji Sederhana | **Slip Gaji WA Otomatis** | `canGeneratePayroll` |
| **Fasilitas Perpajakan & Kepatuhan Fiskal (Tax Engine)** | | | | | |
| 🧾 **Pajak POS (PB1 10% / PPN)** | ✅ (Tarif Flat) | ✅ (Tarif Flat) | ✅ Multi-Tarif Cabang | ✅ Multi-Tarif Cabang | `canConfigureBranchTaxes` |
| 🏢 **PPh Final UMKM 0.5% (PP 55)** | ❌ | Rekap Omzet | ✅ + Tracker Threshold Rp500jt| ✅ Laporan SPT Masa PPh Final | `canTrackPPhFinalUMKM` |
| 💼 **PPh 21 TER (PP 58/2023)** | ❌ | ❌ | Estimasi TER Bulanan | **TER A/B/C + Pasal 17 Des** | `canCalculatePPh21` |
| 📊 **Ekspor Laporan Pajak DJP** | ❌ | ❌ | Ekspor Rekap Pajak | **Format Siap Lapor DJP** | `canExportTaxReports` |
| **Master Data Operasional** | | | | | |
| 📦 **Katalog Produk & SKU** | 10 Produk | 50 Produk | **∞ Unlimited** | **∞ Unlimited** | `EntitlementService::canCreateProduct` |
| 🥣 **Bahan Baku (Raw Materials)** | 10 Bahan | 30 Bahan | **∞ Unlimited** | **∞ Unlimited** | `EntitlementService::canCreateMaterial` |
| 📋 **Resep HPP (BOM)** | 3 Resep | 10 Resep | **∞ Unlimited** | **∞ Unlimited** | `EntitlementService::canCreateRecipe` |
| 👥 **Kontak Pelanggan CRM** | 15 Kontak | 100 Kontak | 1.000 Kontak | **∞ Unlimited** | `EntitlementService::canCreateCustomer` |
| 🚚 **Pemasok / Vendor** | 2 Vendor | 5 Vendor | **∞ Unlimited** | **∞ Unlimited** | `EntitlementService::canCreateSupplier` |
| **Transaksi Bulanan (Reset Tgl 1)** | | | | | |
| 🛒 **Transaksi Kasir POS** | 30 struk/bln | **∞ Unlimited** | **∞ Unlimited** | **∞ Unlimited** | `EntitlementService::canCreatePosTransactionThisMonth` |
| 📄 **Faktur Penjualan B2B** | 3 faktur/bln | 15 faktur/bln | **∞ Unlimited** | **∞ Unlimited** | `EntitlementService::canCreateInvoiceThisMonth` |
| 📑 **Purchase Order (PO)** | 3 PO/bln | 15 PO/bln | **∞ Unlimited** | **∞ Unlimited** | `EntitlementService::canCreatePurchaseOrderThisMonth` |
| 💬 **Struk WhatsApp Kasir** | 10 pesan/bln | 50 pesan/bln | 300 pesan/bln | 1.000 pesan/bln | `EntitlementService::canSendWhatsAppThisMonth` |
| 📱 **Posting Media Sosial** | 3 post/bln | 10 post/bln | 30 post/bln | **∞ Unlimited** | `EntitlementService::canScheduleSocialPostThisMonth` |

---

## 4. Matriks Hak Akses Fitur (Feature Gating Matrix)

| Modul & Fitur (`resources/views/app`) | FREE | STANDARD | PREMIUM | PRESTIGE | Rationale & Dampak Bisnis |
| :--- | :---: | :---: | :---: | :---: | :--- |
| **Operasional Kasir & POS (`app/pos`)** | | | | | |
| Kasir Penjualan Cepat POS | ✅ | ✅ | ✅ | ✅ | Fungsi dasar kasir tetap gratis. |
| Otorisasi PIN Kasir & Lock Screen | ❌ | ✅ | ✅ | ✅ | Kasir tidak bisa buka menu pengaturan/laporan. |
| Manajemen Shift & Selisih Kas Fisik | ❌ | ✅ | ✅ | ✅ | Kontrol uang laci kasir saat pergantian jam kerja. |
| Manajemen Denah Meja & Dine-In | Maks. 3 Meja | Maks. 8 Meja | **Unlimited** | **Unlimited** | Restoran/cafe butuh denah meja tak terbatas. |
| Kitchen Display System (KDS Layar Dapur)| ❌ | ❌ | ✅ | ✅ | Pesanan kasir langsung muncul di layar juru masak. |
| Self-Order QR Meja Pelanggan | ❌ | ❌ | ✅ | ✅ | Pelanggan pesan & bayar mandiri dari scan meja. |
| **Multi-Cabang & Gudang (`app/warehouse`, `inventory`)** | | | | | |
| Transfer Stok Antar Gudang/Cabang | ❌ | ❌ | ✅ | ✅ | Mutasi barang antar cabang ruko/gudang pusat. |
| Dapur Pusat / Central Kitchen | ❌ | ❌ | ✅ | ✅ | Produksi bumbu/adonan massal untuk suplai outlet. |
| Multi-Pricing (Beda Harga Antar Cabang)| ❌ | ❌ | ✅ | ✅ | Harga jual di Bandara/Mall vs Ruko pinggir jalan. |
| Stock Opname Digital & Penyesuaian | 1x / bulan | 2x / bulan | **Unlimited** | **Unlimited** | Audit fisik persediaan langsung buku jurnal selisih. |
| Peringatan Stok Menipis (*Low Stock Alert*)| ❌ | ❌ | ✅ (Email/WA) | ✅ (Email/WA) | Mencegah kehabisan bahan saat jam ramai. |
| **HRM, Penggajian & Ketenagakerjaan (`app/hrm`, `roles`)** | | | | | |
| Biodata & Penempatan Cabang Karyawan| ❌ | ✅ (3 Karyawan)| ✅ (10 Karyawan)| **Unlimited** | Pengelompokan karyawan per outlet/posisi. |
| Presensi Absensi Shift POS / GPS | ❌ | Presensi POS | Presensi GPS/POS | Multi-Cabang | Pencatatan jam kerja & keterlambatan akurat. |
| Perhitungan Komisi Mekanik/Kapster | ❌ | ❌ | ✅ Otomatis | ✅ Bertingkat | Komisi langsung terhitung dari struk pengerjaan. |
| Pinjaman & Kasbon Karyawan | ❌ | Catat Manual | ✅ Buku Kasbon | ✅ Auto-Potong Gaji | Kelola kasbon terstruktur tanpa lupa potong. |
| Skema Pekerja Harian (Daily Worker)| ❌ | ❌ | ✅ Upah Harian | ✅ Harian + Borongan | Sesuai aturan ketenagakerjaan kru paruh waktu. |
| BPJS TK (JHT, JKK, JKM, JP) & BPJS Kes| ❌ | ❌ | Rumus Otomatis | **Modul BPJS Lengkap**| Kepatuhan jaminan sosial ketenagakerjaan RI. |
| Kalkulasi THR Berbasis Tanggal Masuk | ❌ | ❌ | Rumus Manual | **Otomatis Join Date**| Proporsional masa kerja < 12 bulan & penuh $\ge 12$ bln. |
| Payroll & Slip Gaji Otomatis via WA | ❌ | ❌ | Rekap Sederhana | **Otomatis WA** | Kirim slip gaji digital ke HP karyawan 1-klik. |
| Audit Trail Pembatalan & Void Kasir | ❌ | ❌ | ✅ Lengkap | ✅ Lengkap | Cegah kasir nakal hapus transaksi kas. |
| **Perpajakan & Fiskal (`app/tax`, `finance`)** | | | | | |
| Pajak Restoran (PB1 10%) & PPN Kasir | ✅ (Flat) | ✅ (Flat) | ✅ Pengaturan Luwes | ✅ Pengaturan Luwes | Kepatuhan retribusi daerah & PPN faktur. |
| PPh Final UMKM 0.5% (PP 55/2022) | ❌ | Rekap Kasar | ✅ Threshold 500 Jt | ✅ Rekonsiliasi SPT | Otomatis hitung bebas pajak 500 juta OP. |
| PPh 21 TER (PP 58/2023) Karyawan | ❌ | ❌ | Estimasi Bruto | **TER A/B/C + Des** | Metode Gross / Gross-Up / Net resmi. |
| Ekspor File Pajak Siap Lapor DJP | ❌ | ❌ | Rekap PDF | **Format e-Bupot DJP** | Memudahkan akuntan/konsultan pajak klien. |
| **Keuangan & Akuntansi (`app/finance`)** | | | | | |
| Multi Akun Kas & Rekening Bank | 1 Akun Tunai | 2 Akun | **Multi-Rekening** | **Multi-Rekening** | Pisahkan Kasir, Kasbon, BCA, Mandiri, QRIS. |
| Jurnal Akuntansi Otomatis (*Auto-Journal*)| ❌ | ❌ | ✅ | ✅ | POS, PO, dan Biaya langsung posting buku besar. |
| Laporan Umur Piutang (*Aging AR/AP*) | ❌ | Basic | ✅ Lengkap | ✅ Lengkap | Monitoring jatuh tempo tagihan pelanggan & vendor. |
| Laba Rugi per Cabang vs Konsolidasian | ❌ | ❌ | ✅ per Cabang | ✅ Konsolidasi | Evaluasi performa cabang untung vs rugi. |
| **Toko Online Publik (`app/storefront`)** | | | | | |
| Katalog Digital Resmi (`cooca.id/{slug-bisnis}`) | ✅ | ✅ | ✅ | ✅ | Etalase digital kanonikal terverifikasi (alias `/b/{slug}`) untuk share di bio IG/WA. |
| Watermark Cooca (*"Powered by Cooca"*)| Ada | Ada | **Dihapus** | **Dihapus** | Tampilan bersih untuk menjaga citra brand toko. |
| Cek Ongkir Otomatis (JNE, SiCepat, dll)| ❌ | Tarif Flat | ✅ Otomatis | ✅ Otomatis | Integrasi API logistik kurir nasional. |
| Payment Gateway Otomatis (VA & QRIS) | ❌ | ❌ | ✅ | ✅ | Pembayaran online diverifikasi otomatis tanpa cek manual.|
| **Pelaporan & Analitik (`app/reports`)** | | | | | |
| Rentang Waktu Laporan Penjualan | 7 Hari Terakhir| Bulan Berjalan| **Kustom Bebas** | **Kustom Bebas** | Fleksibilitas audit pembukuan tahunan. |
| Ekspor Laporan Excel Multi-Sheet | ❌ | ❌ | ✅ | ✅ | Siap setor akuntan, kantor pajak & pengajuan KUR. |
| Impor Massal Excel (Produk & Bahan) | ❌ | ✅ | ✅ | ✅ | Cepat onboarding data barang ribuan SKU. |
| Konsolidasi Laba Rugi Multi-Bisnis | ❌ | ❌ | ❌ | ✅ | 1 laporan konsolidasian untuk seluruh gurita bisnis. |
| **Customer Support & SLA** | Komunitas | Email Support | WhatsApp CS | Dedicated Manager | Penanganan kendala prioritas sesuai nilai akun. |

---

## 5. Arsitektur Mitigasi Jika Pengguna Tidak Bayar (Graceful Degradation)

Sistem **TIDAK PERNAH memblokir UI secara total (No 100% Hostile Lockout)**. Penyanderaan data akan membuat pengguna panik, frustrasi, dan *churn*. Mitigasi dilakukan melalui siklus 3-fase:

```
   H-7 s/d H-1                    Hari 1 s/d Hari 3                      Hari 4+
[ Periode Pengingat ]  ──►  [ Masa Tenggang (Grace Period) ]  ──►  [ Kedaluwarsa (Expired) ]
   Status: ACTIVE                   Status: PAST_DUE                     Status: EXPIRED
• UI 100% Normal             • UI Berjalan Normal                 • Otomatis Turun ke FREE
• Banner Halus Pengingat     • Amber Banner Peringatan            • Fitur Canggih Terkunci
• Email & WA Otomatis        • Transaksi Kasir Diberi Toleransi   • Data Lama READ-ONLY
```

### A. Tiga Siklus Waktu Mitigasi
1. **Fase 1: Masa Pengingat Menjelang Jatuh Tempo (H-7 s/d H-1)**
   - Akses: 100% Normal.
   - UI: Toast banner lembut di dashboard (`bg-emerald-50 text-emerald-800`): *"Masa aktif paket Premium tersisa 3 hari. Perpanjang sekarang agar operasional kasir & dapur tidak terganggu."*
   - Backend: Dispatch email & WhatsApp notification via `cooca:process-subscriptions`.
2. **Fase 2: Masa Tenggang Toleransi / *Grace Period* (Hari 1 s/d Hari 3)**
   - Status di database: `past_due`.
   - Akses: **Kasir dan dapur tetap bisa beroperasi normal selama 3 hari** untuk mencegah toko lumpuh saat jam ramai.
   - UI: Amber warning banner di navbar atas: *"Masa aktif telah berakhir. Anda berada dalam masa tenggang 3 hari. [Perpanjang Sekarang]"*.
3. **Fase 3: Masa Kedaluwarsa & Turun ke Free (Hari 4+)**
   - Status di database: `expired`.
   - Akun bisnis secara otomatis diturunkan (*downgrade*) ke aturan **Paket FREE**.

### B. Mitigasi di Sisi Antarmuka (UI Rules)
1. **Akses *Read-Only* Tanpa Hapus Data**:
   - Jika saat Premium owner membuat 120 produk, saat turun ke Free (kuota 10): **120 produk TIDAK DIHAPUS**. Data historis penjualan, resep, dan laporan kas tetap bisa dilihat dan dicari (*View & Search OK*).
2. **Write-Block dengan Bento Paywall Modal**:
   - Tombol aksi penambahan baru (misal `+ Tambah Produk`) dicegat:
     > **Katalog Anda Berisi 120 Produk (Batas Free: 10 Produk)**  
     > Produk lama Anda tetap tersimpan aman. Untuk menambah produk baru, silakan aktifkan kembali paket Standard atau Premium Anda.  
     > `[ Perpanjang Langganan ]` `[ Batal ]`
   - Di Kasir POS: Jika transaksi bulan berjalan sudah tembus 30 struk, kasir memunculkan modal: *"Batas Transaksi Kasir Free Bulan Ini Tercapai (30 Struk). Upgrade ke Standard (Rp29.000/bln) untuk kasir tanpa batas."*
3. **Feature Locking (*Blurred Preview & Gated State*)**:
   - Menu KDS Dapur, Transfer Stok, dan Auto-Journal menampilkan ikon gembok elegan 🔒 dengan ajakan upgrade yang jelas.
4. **Multi-Bisnis Suspension (Level Owner)**:
   - Bisnis pertama (primer) tetap aktif dalam mode Free.
   - Bisnis ke-2 dan ke-3 diberi label *"Diarsipkan Sementara"* di dropdown switcher. Tidak ada data yang hilang; begitu langganan dibayar, bisnis langsung aktif kembali.
5. **Sticky Free Mode Notice Banner**:
   - Banner diskrit di bagian atas aplikasi: *"Mode Free Aktif — Beberapa fitur lanjutan dinonaktifkan."*

### C. Mitigasi di Sisi Sistem Backend (Enforcement Rules)
1. **Middleware & Policy Guard**:
   - Backend memvalidasi setiap mutasi data (`POST`, `PUT`, `DELETE`). Request mutasi yang melanggar batas tier ditolak dengan `403 Forbidden` atau redirect flash notice ramah ke `billing.limits`.
2. **Storage Quota Protection**:
   - Jika berkas owner berjumlah 8 GB saat Premium, lalu turun ke Free (1 GB): berkas lama tidak dihapus, namun `canUpload($owner)` mengembalikan `false`. Upload foto produk baru atau nota biaya baru diblokir hingga storage berada di bawah kuota atau dilakukan top-up.
3. **Storefront Fallback**:
   - Watermark *"Powered by Cooca"* muncul kembali di katalog online toko publik (`cooca.id/{slug-bisnis}`).
   - Hitung ongkir otomatis kurir kembali ke tarif flat/manual.

---

## 6. Adaptasi & Fokus Modul untuk 20 Sektor Industri

COOCA dirancang kompatibel untuk **20 sektor industri Indonesia** (didefinisikan di `IndustryPresets.php` & `TwentyIndustriesShowcaseSeeder.php`). Setiap industri memiliki **alur kerja (*workflow*) unik**, sehingga limitasi modul di bawah ini menjadi pemicu konversi paling efektif:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        PETA 6 KLASTER DARI 20 INDUSTRI COOCA                           │
├─────────────────────────┬──────────────────────────┬───────────────────────────────────┤
│ 1. Kuliner & F&B (5)    │ 2. Manufaktur & HPP (5)  │ 3. Ritel & Apotek (2)             │
│ • Restoran Dine-in      │ • Konveksi & Garment     │ • Minimarket / Kelontong          │
│ • Coffee Shop & Cafe    │ • Percetakan & Offset    │ • Apotek & Toko Obat              │
│ • Bakery & Roti         │ • Mebel & Furniture Kayu ├───────────────────────────────────┤
│ • Cloud Kitchen         │ • Bubut & Logam Presisi  │ 4. Jasa Operasional Harian (4)    │
│ • Katering Prasmanan    │ • Kerajinan & Craft      │ • Bengkel Mobil & Motor           │
├─────────────────────────┼──────────────────────────┤ • Barbershop & Salon              │
│ 5. Jasa Proyek (3)      │ 6. Distribusi & Agro (2) │ • Laundry Kiloan                  │
│ • Kontraktor Bangunan   │ • Distributor Grosir     │ • Cuci Mobil & Detailing          │
│ • Event / Wedding Org   │ • Pertanian & Peternakan │                                   │
│ • Digital IT Agency     │                          │                                   │
└─────────────────────────┴──────────────────────────┴───────────────────────────────────┘
```

---

### Klaster 1: Kuliner, Cafe & Restoran F&B (5 Industri)
*Cakupan: `fnb_resto`, `fnb_cafe`, `fnb_bakery`, `fnb_cloud_kitchen`, `fnb_catering`*

| Modul Kunci yang Digunakan | Peran Modul bagi Industri F&B | Titik Chokepoint & Pemicu Konversi Berbayar |
| :--- | :--- | :--- |
| **Kasir POS (`app/pos/terminal`)** | Input pesanan kilat, split bill, cetak struk dapur & bar. | **Free 30 struk/bln:** Hari ke-10 kedai kopi ramai langsung tembus kuota -> **Upgrade ke Standard (Rp29k)**. |
| **Kitchen Display System (`app/pos/kitchen`)** | Layar pesanan real-time di dapur koki tanpa kertas struk. | Terkunci di Free/Standard -> Cafe butuh koki cepat saji -> **Upgrade ke Premium (Rp89k)**. |
| **Denah Meja Dine-in (`app/pos/tables`)** | Kelola status meja (kosong, terisi, reservasi), pindah meja. | Free hanya 3 meja -> Resto punya 15 meja -> **Upgrade ke Premium**. |
| **Resep HPP & Bahan Baku (`materials`)** | Resep BOM potong gramasi sirup/kopi/terigu otomatis per cup. | Free dibatasi 3 resep -> Menu cafe puluhan varian -> **Upgrade ke Premium**. |
| **Shift Kasir (`app/pos/shifts`)** | Rekonsiliasi modal kas laci pergantian kasir siang-malam. | Kunci blind-cash closing ada di Standard & Premium. |

---

### Klaster 2: Manufaktur, Konveksi & Percetakan (5 Industri)
*Cakupan: `mfg_garment`, `mfg_printing`, `mfg_furniture`, `mfg_precision`, `mfg_craft`*

| Modul Kunci yang Digunakan | Peran Modul bagi Industri Manufaktur | Titik Chokepoint & Pemicu Konversi Berbayar |
| :--- | :--- | :--- |
| **Biaya Mesin & Buruh (`labor-machines`)** | Hitung biaya listrik oven/mesin jahit & upah operator per menit. | **Eksklusif Premium:** Tanpa ini, pabrik salah hitung HPP dan jual rugi -> **Pemicu Upgrade Utama**. |
| **Purchase Order (`purchase-orders`)** | Pembelian kain, kertas rim, kayu, pelat besi ke supplier pabrik. | Free hanya 3 PO/bln -> Produksi mingguan butuh restock rutin -> **Upgrade ke Premium**. |
| **Sales Order & DP (`sales-orders`)** | Kontrak pesanan seragam/cetakan dengan termin DP 50%. | Free dibatasi 3 faktur/bln -> Pesanan grosir menumpuk -> **Upgrade ke Premium**. |
| **Kalkulator & Simulator BEP (`simulator`)** | Hitung titik impas minimal order kustom (MOQ) cetak sablon. | Simulasi lanjutan target margin hanya terbuka di Premium. |

---

### Klaster 3: Ritel Toko Kelontong & Apotek (2 Industri)
*Cakupan: `retail_reseller`, `retail_pharmacy`*

| Modul Kunci yang Digunakan | Peran Modul bagi Industri Ritel | Titik Chokepoint & Pemicu Konversi Berbayar |
| :--- | :--- | :--- |
| **Barcode Scanner POS (`app/pos`)** | Kasir kilat scan barcode ribuan SKU barang minimarket & obat. | Free hanya 10 SKU -> Minimarket punya 500+ SKU -> **Wajib Standard / Premium**. |
| **Multi-Gudang (`app/warehouse`)** | Pisahkan stok etalase toko depan dengan gudang dus belakang. | Free hanya 1 gudang -> Butuh mutasi stok -> **Upgrade ke Premium**. |
| **Stock Opname Digital (`inventory/opname`)** | Audit fisik persediaan mingguan untuk cegah pencurian barang. | Free hanya 1x/bln -> Toko ritel butuh opname berkala -> **Upgrade ke Premium**. |
| **Peringatan Stok Menipis (*Low Stock*)** | Notifikasi WA/Email saat stok obat atau minyak goreng < 5 pcs. | Otomasi alert hanya aktif di Premium. |

---

### Klaster 4: Jasa Operasional Harian (4 Industri)
*Cakupan: `service_workshop`, `service_barbershop`, `service_laundry`, `service_autodetailing`*

| Modul Kunci yang Digunakan | Peran Modul bagi Industri Jasa Harian | Titik Chokepoint & Pemicu Konversi Berbayar |
| :--- | :--- | :--- |
| **Manajemen Layanan Jasa (`services`)** | Menu jasa ganti oli, pangkas rambut, cuci kiloan, salon mobil. | Penggabungan jasa servis + sparepart dalam 1 struk kasir. |
| **Booking & Reservasi Online (`storefront`)** | Pelanggan booking jam slot cuci mobil atau servis motor via HP. | Form reservasi online tanpa antrean -> **Upgrade ke Premium**. |
| **PIN Kasir & Hak Staf (`app/roles`)** | Kasir front-office tidak boleh melihat total laba bersih bengkel. | Free hanya solo-owner -> Bengkel punya kasir & 4 mekanik -> **Upgrade ke Standard**. |
| **Struk WhatsApp Digital (`app/whatsapp`)** | Kirim nota digital & nomor antrean pengerjaan ke nomor WA mobil. | Free 10 pesan -> Laundry/bengkel cuci ratusan nota -> **Upgrade ke Standard/Premium**. |

---

### Klaster 5: Jasa Profesional, Proyek & Acara (3 Industri)
*Cakupan: `service_contractor`, `service_event`, `service_agency`*

| Modul Kunci yang Digunakan | Peran Modul bagi Industri Proyek | Titik Chokepoint & Pemicu Konversi Berbayar |
| :--- | :--- | :--- |
| **Surat Penawaran Harga (`quotations`)** | Susun proposal RAB proyek renovasi atau paket wedding elegan. | Free dibatasi 3 penawaran -> EO butuh kirim belasan proposal klien -> **Upgrade ke Standard**. |
| **Faktur B2B Termin (`invoices`)** | Tagihan bertahap: DP 30%, Progress Fisik 50%, Pelunasan 20%. | Fitur konversi 1-klik Quotation ke Faktur Termin -> **Wajib Premium**. |
| **Catatan Biaya & Nota Lapangan (`expenses`)** | Upload bukti nota material semen/catering lapangan via HP owner. | Menyedot kuota storage owner -> Butuh 10 GB storage -> **Upgrade ke Premium**. |
| **Ekspor Laporan Keuangan (`reports`)** | Laporan laba rugi per proyek untuk pembagian dividen mitra kerja. | Ekspor Excel komprehensif terkunci di Free -> **Upgrade ke Premium**. |

---

### Klaster 6: Distribusi Grosir & Agro Peternakan (2 Industri)
*Cakupan: `distributor_fmcg`, `agri_farming`*

| Modul Kunci yang Digunakan | Peran Modul bagi Industri Distribusi & Agro | Titik Chokepoint & Pemicu Konversi Berbayar |
| :--- | :--- | :--- |
| **Manajemen Piutang Tempo (`receivables`)** | Penjualan karton telur/FMCG dengan tempo pembayaran Net 14/30. | Laporan umur piutang (*Aging AR*) & notifikasi jatuh tempo otomatis -> **Wajib Premium**. |
| **Multi-Cabang & Titik Kirim (`warehouse`)** | Titik kumpul hasil panen, gudang pendingin, dan agen distributor. | Free hanya 1 lokasi -> Butuh 3 titik gudang -> **Upgrade ke Premium/Prestige**. |
| **Konsolidasi Lintas Bisnis (Prestige)** | Pemilik ternak yang juga punya rumah potong ayam dan resto geprek. | 3 bisnis berbeda dalam 1 akun terpusat -> **Wajib Prestige (Rp199k)**. |
| **Ekspor Data SPT Pajak (`reports`)** | Data rekonsiliasi faktur pajak keluaran/masukan untuk akuntan. | Ekspor multi-sheet tak terbatas di Premium & Prestige. |

---

## 7. Arsitektur Multi-Cabang, Multi-Gudang & Central Kitchen

Ketika UMKM berkembang dari satu toko rintisan menjadi jaringan bisnis multi-cabang (ruko kedua, booth mall, kemitraan franchise, atau gudang distribusi), sistem COOCA menyediakan kontrol rantai pasok terpadu dan tata kelola terpusat:

```
                                ┌─────────────────────────────────────────┐
                                │          AKUN OWNER TERPUSAT            │
                                │   (Pusat Kendali Bisnis & Konsolidasi)  │
                                └────────────────────┬────────────────────┘
                                                     │
                                ┌────────────────────┴────────────────────┐
                                │     DAPUR PUSAT / GUDANG INDUK          │
                                │ (Central Kitchen / Central Distribution)│
                                └────────────┬───────────────┬────────────┘
                                             │               │
                         Surat Jalan Transfer│               │Surat Jalan Transfer
                         Stok (In-Transit)   │               │Stok (In-Transit)
                                             ▼               ▼
                                ┌─────────────────┐     ┌─────────────────┐
                                │ CABANG OUTLET A │     │ CABANG OUTLET B │
                                │   (Mall Plaza)  │     │   (Ruko Pasar)  │
                                ├─────────────────┤     ├─────────────────┤
                                │ • POS Kasir #1  │     │ • POS Kasir #1  │
                                │ • Harga: Rp35rb │     │ • Harga: Rp28rb │
                                │ • Stok Lokal    │     │ • Stok Lokal    │
                                │ • Shift Kasir   │     │ • Shift Kasir   │
                                └─────────────────┘     └─────────────────┘
```

### A. Hierarki & Topologi Jaringan Lokasi
1. **Level Akun Owner**: Memegang kepemilikan bisnis, alokasi total kuota cloud storage, dan konsolidasi finansial seluruh cabang.
2. **Entitas Bisnis (`Business`)**: Badan usaha atau merek dagang (misal: "Kopi Kita Sejahtera").
3. **Lokasi Fisik Operasional (`Location`)**:
   - **`outlet` (Cabang Penjualan Fisik):** Memiliki kasir POS, laci kas fisik, printer struk, staf operasional, dan melayani pelanggan langsung (Dine-In, Takeaway, Retail).
   - **`warehouse` (Gudang Logistik):** Tempat penyimpanan stok bahan mentah dus-dusan atau cadangan produk jadi. Tidak melayani transaksi ritel POS langsung, melainkan menangani *Goods Receipt* dari vendor dan distribusi ke outlet.
   - **`central_kitchen` (Dapur Pusat / Pabrik Bahan):** Fasilitas produksi khusus (BOM Produksi Dapur) untuk mengolah bahan mentah menjadi bahan setengah jadi (misal: bumbu pasta 10 kg, sirup racikan, adonan roti, daging marinasi) yang kemudian disuplai ke seluruh outlet.

### B. Arsitektur Skema Database Multi-Cabang
Diimplementasikan secara efisien di `cooca_core`:

```
┌─────────────────┐        1:N        ┌──────────────────────┐        N:1        ┌─────────────────┐
│    locations    │◄─────────────────┤   inventory_stocks   ├─────────────────►│    products     │
│ (outlet/wh/ck)  │                   │ (stok riil per cab)  │                  │  / materials    │
└────────┬────────┘                   └──────────────────────┘                  └─────────────────┘
         │
         │ 1:N
         ├───────────────────────────┐
         ▼                           ▼
┌─────────────────────────┐ ┌─────────────────────────────────┐
│     stock_transfers     │ │      branch_product_prices      │
│(surat jalan antar cabang│ │ (harga jual berbeda per cabang) │
└────────┬────────────────┘ └─────────────────────────────────┘
         │ 1:N
         ▼
┌─────────────────────────┐
│  stock_transfer_items   │
│(rincian barang transfer)│
└─────────────────────────┘
```

1. **Tabel `locations`**:
   - Kolom: `id`, `business_id`, `name`, `slug`, `type` (`outlet`, `warehouse`, `central_kitchen`), `code`, `phone`, `address`, `latitude`, `longitude`, `radius_meters`, `is_primary`, `is_active`.
2. **Tabel `inventory_stocks`**:
   - Menjaga saldo stok riil per lokasi: `business_id`, `location_id`, `material_id`, `product_id`, `quantity`, `reserved_quantity`, `last_cost`.
3. **Tabel `branch_product_prices` (Multi-Pricing)**:
   - Kolom: `id`, `business_id`, `location_id`, `product_id`, `price`, `cost_price`, `is_available`.
   - Mengizinkan harga jual dan ketersediaan menu berbeda di tiap cabang.
4. **Tabel `stock_transfers` & `stock_transfer_items`**:
   - Mengelola perpindahan stok resmi dengan nomor dokumen surat jalan, verifikasi dua arah (pengirim vs penerima), dan status transisi: `pending` ➔ `in_transit` ➔ `received` / `cancelled`.

### C. Alur Logistik Central Kitchen (Dapur Pusat) & Suplai Bahan
Untuk bisnis kuliner multi-outlet, produksi di dapur pusat menjamin konsistensi rasa dan efisiensi HPP:

```
[ Central Kitchen: Olah Bahan ] ──► [ Batch Production BOM ] ──► [ Stok Bahan Jadi CK Siap ]
                                                                             │
                                                                 Surat Jalan Transfer (SJT)
                                                                             │
                                                                             ▼
[ Rekonsiliasi Otomatis ] ◄── [ Verifikasi Fisik Datang ] ◄── [ Outlet Terima Barang ]
  • Selisih -> Akun Biaya Rugi Ekspedisi
  • Stok Outlet Bertambah Otomatis
```

1. **Produksi Massal di Dapur Pusat (*Batch Production*):**
   - Central Kitchen mengeksekusi Resep/BOM (misal: memasak 50 kg saus rendang instan dari cabai, santan, dan rempah).
   - Bahan mentah di gudang dapur pusat berkurang seketika, dan produk bumbu jadi terbit dalam satuan pack/pouch.
2. **Surat Permintaan Barang Outlet (*Stock Requisition*):**
   - Cabang Outlet membuat PO internal / Requisition saat stok menipis.
3. **Penerbitan Surat Jalan & Pengiriman (*Stock Transfer In-Transit*):**
   - Central Kitchen memverifikasi dokumen pengiriman dan mengemas barang.
   - Status berubah menjadi `in_transit`. Stok di Central Kitchen berkurang, tetapi belum masuk ke outlet (tercatat di akun persediaan *In-Transit Inventory*).
4. **Penerimaan & Rekonsiliasi Selisih (*Receive & Discrepancy Reconciliation*):**
   - Staf outlet menghitung fisik barang yang datang di aplikasi COOCA.
   - **Jika Jumlah Sesuai:** Seluruh kuantitas langsung masuk ke saldo stok outlet dan siap dijual/dipakai di POS.
   - **Jika Terjadi Selisih / Kerusakan di Jalan:** Staf menginput selisih (misal: dikirim 20 pack, pecah 2 pack di jalan, diterima 18 pack). Sistem mencatat selisih tersebut dan otomatis membukukannya ke akun beban kerugian logistik (*Inventory Shrinkage/Loss in Transit*).

### D. Multi-Pricing Dinamis & Ketersediaan Menu per Cabang
- **Strategi Penyesuaian Daya Beli:**
  - Produk yang sama dapat dijual dengan margin berbeda berdasarkan lokasi (Cabang Mall Rp 35.000 vs Cabang Ruko Kampus Rp 22.000).
  - *Harga Modal / Biaya Pengiriman Khusus:* HPP produk dapat disesuaikan jika cabang luar pulau memiliki ongkos kirim bahan lebih tinggi.
- **Fallback Hierarchy:**
  - Saat POS kasir mencari harga produk: Cek tabel `branch_product_prices` berdasarkan `location_id` aktif ➔ Jika tidak ditemukan kustomisasi, *fallback* otomatis ke harga dasar master di tabel `products`.
- **Manajemen Ketersediaan Khusus (*Item Availability*):**
  - Staf cabang dapat menandai produk tertentu sebagai *Out of Stock* / *Unavailable* khusus untuk cabangnya saja tanpa mengganggu cabang lain (misal: mesin espresso cabang B sedang diservis).

### E. Isolasi Data Cabang & Peran Pengguna (*Branch Data Scoping*)
- **Scoping Ketat Kasir & Kru Toko:**
  - Kasir dan juru masak dapur hanya memiliki akses terhadap transaksi, stok laci kasir, antrean KDS, dan pesanan pada cabangnya sendiri (`location_id` aktif). Mereka tidak memiliki akses ke laporan cabang lain.
- **Switch Cabang Fleksibel bagi Owner & Area Manager:**
  - Di bilah navigasi atas (Navbar), Owner dan Area Manager memiliki tombol *Dropdown Branch Switcher*.
  - Dapat beralih sudut pandang operasional cabang A, B, atau C dengan 1-klik, atau memilih mode **"Semua Cabang (Konsolidasi)"**.

### F. Pelaporan Keuangan Cabang: Individual vs Konsolidasi
- **Laba Rugi Cabang (*Profit & Loss per Branch*):**
  - Membedah performa setiap gerai secara terpisah: omzet harian, HPP riil bahan terpakai, beban sewa ruko, gaji karyawan cabang, dan profit margin bersih.
  - Membantu owner mengambil keputusan strategis: cabang mana yang patut diekspansi dan cabang mana yang perlu evaluasi biaya operasional.
- **Neraca & Laporan Konsolidasi Induk (*Consolidated Financials*):**
  - Menyatukan total omzet, perputaran kas, total liabilitas piutang pelanggan, dan valuasi persediaan stok dari seluruh cabang menjadi satu lembar laporan finansial eksekutif bagi Owner.

---

## 8. Arsitektur HRM, Penggajian Komprehensif, BPJS, Pinjaman, Daily Worker & THR

Pengelolaan sumber daya manusia di COOCA dirancang spesifik untuk memecahkan problem fundamental ketenagakerjaan UMKM dan bisnis berkembang di Indonesia: absensi titip teman, kebocoran uang kas laci kasir, salah hitung komisi teknisi, kerumitan potongan pinjaman/kasbon, pekerja harian lepas (*daily worker*), jaminan sosial ketenagakerjaan (BPJS), serta pembagian Tunjangan Hari Raya (THR) yang adil berdasarkan masa kerja.

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                       EKOSISTEM MODUL HRM & PAYROLL COOCA                                            │
├───────────────────────────────┬───────────────────────────────┬───────────────────────────────┬──────────────────────┤
│ 1. Karyawan & Kontrak         │ 2. Presensi & Geofence        │ 3. Shift & Blind Closing      │ 4. Engine Komisi POS │
│ • Tanggal Gabung (Join Date)  │ • PIN di Terminal POS         │ • Shift Pagi / Siang / Malam  │ • Flat fee / SPK     │
│ • Tetap, Kontrak, Daily Worker│ • GPS Geofencing Mobile HP    │ • Kasir buta nominal laci     │ • Persentase jasa    │
│ • Penempatan multi-cabang     │ • Selfie kompresi <200 KB     │ • Auto-jurnal selisih kas     │ • Split komisi tim   │
├───────────────────────────────┼───────────────────────────────┼───────────────────────────────┼──────────────────────┤
│ 5. Pinjaman & Kasbon          │ 6. Kasus Daily Worker         │ 7. BPJS TK & BPJS Kesehatan   │ 8. Engine THR        │
│ • Buku pinjaman & tenor       │ • Upah harian / shift / borong│ • JHT (5.7%), JKK, JKM, JP(3%)│ • Join date cut-off  │
│ • Plafon maks 50% gaji        │ • Rekap <21 hari kerja sebulan│ • BPJS Kesehatan (5%)         │ • Proporsional <12 bln│
│ • Auto-potong di payroll      │ • PPh 21 upah harian          │ • Batas upah resmi Kemnaker   │ • 1 bulan upah >=12 bl│
├───────────────────────────────┴───────────────────────────────┴───────────────────────────────┴──────────────────────┤
│ 9. Payroll Terpadu & Otomasi Slip Gaji via Bot WhatsApp COOCA (Tier Prestige)                                        │
│ • THP = Gaji Pokok + Tunjangan + Lembur + Komisi POS + THR - (Kasbon + BPJS Karyawan + PPh 21 + Denda)               │
│ • Approval 1-Klik Owner ➔ Auto-jurnal Akuntansi ➔ Kirim PDF Slip Gaji Terenkripsi ke WhatsApp Karyawan              │
└──────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘
```

### A. Pilar 1: Database Karyawan, Tanggal Bergabung (*Join Date*), & Klasifikasi Kontrak
- **Profil Terpadu Karyawan (`employees` / `business_members`):**
  - **Identitas Personal**: NIK (KTP), Nama Lengkap, Tempat/Tanggal Lahir, Status Perkawinan & Tanggungan PTKP (`TK/0`, `K/0`, `K/1`, `K/2`, `K/3`), Nomor NPWP/NIK Pajak.
  - **Tanggal Bergabung (*Join Date*)**: Parameter penting yang dicatat presisi hingga tanggal masuk hari pertama. Menjadi basis kalkulasi masa kerja, hak cuti tahunan, dan prorata THR.
  - **Data Pembayaran**: Nomor Rekening Bank, Nama Bank (BCA, Mandiri, BRI, BNI, dll), Nama Pemilik Rekening, dan Nomor WhatsApp aktif karyawan.
- **Tiga Klasifikasi Kepegawaian Sesuai Karakteristik UMKM:**
  1. **Karyawan Tetap (Monthly Salary - PKWTT):** Gaji pokok bulanan tetap + tunjangan tetap (makan/transport) + hak cuti + kepesertaan penuh BPJS.
  2. **Karyawan Kontrak (Fixed Term - PKWT):** Gaji bulanan dengan durasi kontrak tertentu (misal: 6 bulan atau 1 tahun).
  3. **Pekerja Harian Lepas (Daily Worker / Part-time / Freelance):** Bekerja berdasarkan panggilan atau kebutuhan shift fluktuatif (misal: crew banquet katering, kasir pengganti akhir pekan, kuli muat gudang, atau tenaga packing saat promo tanggal kembar).

### B. Pilar 2: Presensi Digital Modern (PIN POS & GPS Geofencing Mobile)
Mencegah kecurangan "titip absen" tanpa perlu membeli mesin *fingerprint* terpisah yang mahal:
1. **Metode 1: Presensi Terminal Kasir POS (In-Store):**
   - Karyawan datang ke outlet, membuka layar terminal kasir, memilih namanya, dan memasukkan 4-6 digit PIN personal.
   - Sistem mencatat waktu presensi secara *real-time* langsung dari server.
2. **Metode 2: Presensi GPS Geofencing & Foto Selfie (Mobile HP Karyawan):**
   - Karyawan mengakses portal presensi COOCA dari smartphone mereka.
   - **Validasi Radius Geofence (Haversine Formula):**
     - Sistem mendeteksi koordinat GPS karyawan dan menghitung jaraknya terhadap koordinat cabang:
       $$d = 2r \arcsin\left(\sqrt{\sin^2\left(\frac{\Delta \phi}{2}\right) + \cos(\phi_1)\cos(\phi_2)\sin^2\left(\frac{\Delta \lambda}{2}\right)}\right)$$
     - Jika jarak $> \text{radius\_meters}$ (misal: 50 meter dari titik ruko), absen **ditolak otomatis** dengan pesan: *"Anda berada di luar area cabang toko"*.
   - **Foto Selfie Kehadiran:** Karyawan mengambil foto selfie di tempat kerja. Gambar dikompres otomatis di sisi browser menjadi ukuran ringan (< 200 KB) agar hemat kuota storage owner.
3. **Kalkulasi Jam Kerja Otomatis:**
   - Menghitung menit keterlambatan terhadap jam masuk shift.
   - Menghitung jam kerja efektif dan akumulasi jam lembur (*overtime*) yang telah diverifikasi manajer.

### C. Pilar 3: Manajemen Shift Kerja & Prosedur "Blind Cash Closing"
Menghilangkan risiko penggelapan uang kasir dan selisih kas tunai yang tidak jelas sumbernya:
1. **Pembukaan Shift Kasir (*Open Shift*):**
   - Kasir login dan menginput uang modal kembalian awal di laci kas (*Opening Cash Float*).
2. **Operasional Shift & Mutasi Kas Laci (*Petty Cash / Drawer Movements*):**
   - Sistem mencatat semua aliran kas: uang masuk transaksi tunai, pembayaran non-tunai (QRIS/debit), setoran kas dadakan (*Cash-In*), dan pengeluaran operasional darurat kasir (*Cash-Out*, misal: beli es batu/gas elpiji Rp 25.000).
3. **Prosedur "Blind Cash Closing" (Penutupan Kasir Buta):**
   - **Prinsip Utama:** Saat tutup kasir, sistem **SENGAJA MENYEMBUNYIKAN** berapa total uang kas yang seharusnya terkumpul menurut komputer.
   - Kasir wajib menghitung uang fisik di laci satu per satu dan mengisi formulir pecahan uang:
     - Berapa lembar Rp 100.000, Rp 50.000, Rp 20.000, Rp 10.000, Rp 5.000, Rp 2.000, Rp 1.000, dan total koin.
   - **Evaluasi Otomatis di Backend:**
     $$\text{Cash Expected} = \text{Opening Cash} + \text{Cash Sales} + \text{Cash In} - \text{Cash Out}$$
     $$\text{Cash Difference} = \text{Closing Cash Actual} - \text{Cash Expected}$$
   - Jika terjadi selisih minus (*Cash Shortage*):
     - Sistem langsung mencatat selisih tersebut ke dalam log shift kasir yang bersangkutan.
     - Terbit jurnal akuntansi otomatis ke akun beban *Kerugian Selisih Kasir*.
     - Owner menerima laporan notifikasi ringkas di aplikasi mengenai selisih tersebut.

### D. Pilar 4: Engine Komisi Karyawan Multi-Sektor (*Commission Engine*)
1. **Atribusi Staf di Keranjang Penjualan (POS Attribution):**
   - Kasir atau teknisi dapat menandai nama staf pengerja pada setiap baris item jasa atau produk di aplikasi kasir POS.
2. **Model Perhitungan Komisi Terkonfigurasi:**
   - **Nominal Tetap per Layanan (*Flat Rate*):** Montir ganti oli dapat Rp 5.000, servis besar dapat Rp 30.000.
   - **Persentase dari Tarif Layanan (*Percentage-Based*):** Kapster salon dapat 35% dari nilai jasa potong rambut.
   - **Komisi Berbagi Tim (*Split Commission*):** Rasio pembagian tim (misal: 70% Mekanik Senior + 30% Asisten).
   - **Insentif Target Penjualan (*Tiered Bonus*):** Bonus jika cabang mencapai target omzet bulanan.
3. **Pencatatan Real-Time:** Staf dapat melihat rekap harian mereka secara transparan di portal staf.

### E. Pilar 5: Manajemen Pinjaman & Kasbon Karyawan (*Employee Loans & Ledger*)
Menertibkan kebiasaan kasbon karyawan tanpa resiko owner lupa memotong saat penggajian:
1. **Pengajuan & Batas Plafon Pinjaman:**
   - Karyawan mengajukan kasbon melalui manajer/owner dengan menyebutkan nominal dan keperluan.
   - **Batas Plafon Aman (*Safety Limit*):** Sistem membatasi pinjaman aktif maksimal 50% dari total gaji pokok bulanan untuk mencegah gagal bayar atau gaji minus.
2. **Jadwal Skema Cicilan (*Amortization Tenor*):**
   - Tenor cicilan fleksibel: 1 bulan (lunas di gajian berikutnya) hingga 12 bulan.
   - Sistem mencatat kartu saldo pinjaman (*Loan Ledger*): Tanggal pinjam, nominal pokok, cicilan terbayar, dan sisa saldo.
3. **Pemotongan Otomatis di Payroll (*Payroll Auto-Deduction*):**
   - Saat proses penggajian bulanan dijalankan, sistem otomatis memasukkan nilai angsuran bulan berjalan ke dalam baris potongan payroll.
   - Begitu payroll disetujui, saldo sisa pinjaman karyawan otomatis berkurang.
4. **Integrasi Jurnal Akuntansi Kasbon:**
   - Saat pencairan kasbon:
     - **Debit:** Piutang Karyawan (`Accounts Receivable - Employee Loans`)
     - **Kredit:** Kas Operasional / Rekening Bank
   - Saat pemotongan gaji bulanan:
     - **Debit:** Beban Gaji & Upah
     - **Kredit:** Piutang Karyawan (pelunasan cicilan kasbon)
     - **Kredit:** Kas/Bank (sisa gaji bersih yang ditransfer)

### F. Pilar 6: Kasus Pekerja Harian Lepas (*Daily Worker / Freelance*)
Diatur spesifik untuk mematuhi regulasi ketenagakerjaan dan fleksibilitas operasional UMKM:
1. **Aturan Hari Kerja (*Maximum Working Days Guard*):**
   - Berdasarkan ketentuan ketenagakerjaan RI, pekerja harian lepas yang bekerja $\ge 21$ hari per bulan selama 3 bulan berturut-turut wajib beralih status menjadi PKWTT (karyawan tetap).
   - Sistem COOCA menyediakan **Early Warning System (EWS)**: Jika pekerja harian telah mencapai 18 hari kerja dalam 1 bulan kalender, sistem memunculkan notifikasi peringatan kepada HR/Owner.
2. **Struktur Upah Fleksibel:**
   - **Upah Harian (*Daily Rate*):** Dibayar tetap per hari kehadiran kerja (misal: Rp 120.000 / hari kerja).
   - **Upah per Jam (*Hourly Rate*):** Dihitung otomatis dari total jam kerja presensi POS/GPS (misal: Rp 15.000 / jam).
   - **Upah Satuan Borongan (*Piece-Rate*):** Dihitung dari output fisik yang diselesaikan (misal: industri konveksi dibayar Rp 2.500 per helai baju yang dijahit).
3. **Siklus Pembayaran Upah:**
   - Dapat diatur mingguan (setiap hari Sabtu), dua mingguan, atau bulanan.
4. **Pajak PPh 21 Upah Sehari (Pasal 16 PMK 168/2023):**
   - Upah harian s.d Rp 450.000/hari dan kumulatif sebulan $\le$ Rp 4.500.000: **Bebas PPh 21 (0%)**.
   - Upah harian > Rp 450.000/hari: Dikenakan pemotongan PPh 21 harian sebesar 0.5% (atau sesuai TER harian/Pasal 17).

### G. Pilar 7: Perhitungan BPJS Ketenagakerjaan & BPJS Kesehatan (Regulasi Resmi RI)
Sistem COOCA mengotomasi pemisahan iuran yang menjadi beban perusahaan (*employer cost*) versus iuran yang dipotong dari gaji karyawan (*employee deduction*):

| Program Jaminan Sosial | Iuran Perusahaan (Pemberi Kerja) | Iuran Karyawan (Potong Gaji) | Total Iuran | Dasar Perhitungan & Batasan Upah |
| :--- | :---: | :---: | :---: | :--- |
| **BPJS Ketenagakerjaan** | | | | *(Upah Pokok + Tunjangan Tetap)* |
| • **JHT (Jaminan Hari Tua)** | **3.70%** | **2.00%** | **5.70%** | Tanpa batas atas upah maksimal. |
| • **JKK (Jaminan Kecelakaan Kerja)**| **0.24% s.d 1.74%** | **0.00%** (Ditanggung Bos) | **0.24% - 1.74%**| Disesuaikan kategori risiko industri: Ritel/Kantor: 0.24%, Resto: 0.54%, Bengkel/Workshop: 0.89%, Manufaktur: 1.27%, Konstruksi: 1.74%. |
| • **JKM (Jaminan Kematian)** | **0.30%** | **0.00%** (Ditanggung Bos) | **0.30%** | Ditanggung penuh oleh pemberi kerja. |
| • **JP (Jaminan Pensiun)** | **2.00%** | **1.00%** | **3.00%** | Berlaku batas plafon upah maksimal resmi (Rp 10.042.300/bln). Kelebihan upah tidak dihitung JP. |
| **BPJS Kesehatan** | | | | *(Upah Pokok + Tunjangan Tetap)* |
| • **Jaminan Pemeliharaan Kesehatan**| **4.00%** | **1.00%** | **5.00%** | Berlaku batas plafon upah maksimal Rp 12.000.000/bln. Menjamin karyawan + 4 anggota keluarga. |

- **Dampak pada Pajak PPh 21:**
  - JKK (0.24%-1.74%), JKM (0.3%), dan BPJS Kesehatan Perusahaan (4%) **menambah penghasilan bruto** karyawan untuk perhitungan pajak PPh 21.
  - Iuran JHT Karyawan (2%) dan Iuran JP Karyawan (1%) **menjadi faktor pengurang** penghasilan bruto dalam perhitungan PPh 21 tahunan.
  - JHT Perusahaan (3.7%) dan JP Perusahaan (2%) bukan merupakan objek pajak saat ini.

### H. Pilar 8: Engine Perhitungan THR (Tunjangan Hari Raya) Berdasarkan Tanggal Bergabung (*Join Date*)
Mengacu pada ketentuan resmi **Permenaker No. 6 Tahun 2016**, sistem COOCA menghitung THR secara otomatis, adil, dan transparan:

```
                                  ┌───────────────────────────┐
                                  │   TANGGAL GABUNG KARYAWAN │
                                  │         (Join Date)       │
                                  └─────────────┬─────────────┘
                                                │
                                    Hitung Selisih Hari/Bulan
                                    terhadap Cut-Off Hari Raya
                                                │
                                ┌───────────────┴───────────────┐
                                ▼                               ▼
                   ┌────────────────────────┐      ┌────────────────────────┐
                   │   MASA KERJA >= 12 BLN │      │  1 BLN <= MASA < 12 BLN│
                   ├────────────────────────┤      ├────────────────────────┤
                   │  THR = 1 BULAN UPAH    │      │    THR PROPORSIONAL    │
                   │ (Gaji Pokok + Tunj Tetap│      │ (Masa Kerja / 12) x Upah│
                   └────────────────────────┘      └────────────────────────┘
```

1. **Formula Perhitungan THR Sesuai Masa Kerja:**
   - **Karyawan dengan Masa Kerja $\ge 12$ Bulan Terus-Menerus:**
     $$\mathbf{\text{THR}} = 1 \times (\text{Gaji Pokok} + \text{Tunjangan Tetap})$$
   - **Karyawan dengan Masa Kerja 1 Bulan s.d $< 12$ Bulan (Prorata):**
     $$\mathbf{\text{THR}} = \frac{\text{Masa Kerja (Bulan, presisi hari)}}{12} \times (\text{Gaji Pokok} + \text{Tunjangan Tetap})$$
     *Contoh:* Budi bergabung tanggal 15 November 2025. Hari Raya Idul Fitri jatuh pada 20 Maret 2026 (Masa kerja = 4 bulan 5 hari $\approx 4.167$ bulan). Jika upah Budi Rp 3.600.000, maka THR yang diterima adalah:
     $$\text{THR} = \frac{4.167}{12} \times \text{Rp } 3.600.000 = \text{Rp } 1.250.100$$
   - **Pekerja Harian Lepas (*Daily Worker*):**
     - Masa kerja $\ge 12$ bulan: Rata-rata upah yang diterima per bulan dalam 12 bulan terakhir.
     - Masa kerja $< 12$ bulan: Rata-rata upah yang diterima per bulan selama masa kerja tersebut.
2. **Pajak atas THR (PPh 21 TER):**
   - Berdasarkan aturan PP 58/2023, THR yang dibayarkan bersamaan atau terpisah pada bulan Hari Raya digabungkan ke dalam Penghasilan Bruto bulan tersebut, lalu dikenakan tarif TER yang sesuai dengan lapisan bruto baru.

### I. Pilar 9: Sistem Penggajian Terpadu & Slip Gaji Otomatis WhatsApp (Tier Prestige)
1. **Formula Lengkap Perhitungan Gaji Bersih (*Take Home Pay - THP*):**
   $$\text{Penghasilan Bruto} = \text{Gaji Pokok} + \text{Tunjangan Tetap} + \text{Tunjangan Hadir} + \text{Upah Lembur} + \text{Komisi POS} + \text{THR}$$
   $$\text{Total Potongan} = \text{Potongan BPJS Karyawan (JHT 2\% + JP 1\% + Kes 1\%)} + \text{PPh 21 TER} + \text{Cicilan Kasbon} + \text{Denda Telat} + \text{Selisih Kasir}$$
   $$\mathbf{\text{Take Home Pay (THP)}} = \text{Penghasilan Bruto} - \text{Total Potongan}$$
2. **Distribusi Slip Gaji Digital via WhatsApp Bot COOCA:**
   - Setelah approval, bot WhatsApp COOCA mengirimkan pesan notifikasi resmi ke nomor WA karyawan:
     > *"Halo [Nama Karyawan], gaji Anda periode [Bulan Tahun] sebesar Rp [Nominal THP] telah disetujui dan ditransfer ke rekening [Bank] [No Rekening]. Unduh slip gaji PDF resmi: [Link Terenkripsi]. Terima kasih atas dedikasi Anda di [Nama Bisnis]!"*
   - Karyawan dapat mengunduh dokumen slip gaji berformat PDF lengkap dengan kop surat bisnis, rincian upah, potongan BPJS, potongan kasbon, PPh 21, dan barcode verifikasi keaslian dokumen.
3. **Penjurnalan Akuntansi Otomatis:**
   - **Debit:** Beban Gaji & Upah Karyawan
   - **Debit:** Beban Komisi Staf
   - **Debit:** Beban BPJS Ketenagakerjaan (Beban Perusahaan)
   - **Debit:** Beban BPJS Kesehatan (Beban Perusahaan)
   - **Debit:** Beban THR Karyawan
   - **Kredit:** Utang BPJS (Ketenagakerjaan & Kesehatan)
   - **Kredit:** Utang PPh 21 Karyawan
   - **Kredit:** Piutang Kasbon Karyawan
   - **Kredit:** Kas Operasional / Bank Penggajian (sebesar THP yang ditransfer)

### J. Pilar 10: Keamanan, Hak Akses Berjenjang (RBAC) & Audit Trail Ketenagakerjaan
1. **Hak Akses Granular**: Kru kasir dan staf dapur tidak dapat melihat rincian gaji, nominal kasbon, atau laporan keuangan pemilik bisnis.
2. **Audit Trail**: Setiap perubahan nominal gaji, persetujuan pinjaman, atau koreksi presensi wajib mencatat ID pengguna pengubah, alasan pengubahan, dan riwayat nilai sebelum/sesudah diubah.

---

## 9. Arsitektur Sistem Perpajakan Terpadu COOCA (Tax Compliance Engine)

Menjamin kepatuhan perpajakan (*tax compliance*) bagi UMKM dan korporasi pengguna COOCA terhadap regulasi Direktorat Jenderal Pajak (DJP) Kementerian Keuangan RI:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                          4 PILAR TAX ENGINE COOCA                                      │
├──────────────────────────┬─────────────────────────────┬───────────────────────────────┤
│ 1. PPh 21 Karyawan (TER) │ 2. PPh Final UMKM 0.5%      │ 3. Pajak POS & Transaksi      │
│ • Skema TER PP 58/2023   │ • PP 55/2022 omzet bruto    │ • PB1 / Pajak Resto 10% F&B   │
│ • Kategori TER A, B, C   │ • Bebas pajak 500 jt (OP)   │ • PPN 11%/12% Faktur PKP      │
│ • Masa Des (Pasal 17 HPP)│ • CV/PT langsung 0.5%       │ • Pisah DPP vs Pajak otomatis │
├──────────────────────────┴─────────────────────────────┴───────────────────────────────┤
│ 4. Dasbor Rekonsiliasi, Jurnal Akuntansi Pajak & Ekspor Siap Lapor DJP (e-Bupot)       │
│ • Format impor CSV/Excel resmi e-Bupot 21/26 DJP & Formulir 1721-A1                    │
│ • Rekapitulasi peredaran bruto bulanan siap setor (Kode Billing DJP)                   │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

### A. Pajak Penghasilan Karyawan: PPh 21 Skema TER (PP 58/2023 & PMK 168/2023)
Sistem COOCA mengadopsi penuh reformasi pemotongan PPh 21 terbaru menggunakan mekanisme Tarif Efektif Rata-Rata (TER):

1. **Pemetaan Kategori TER Berdasarkan Status PTKP:**
   - **Kategori TER A**: Untuk karyawan berstatus PTKP:
     - `TK/0` (Tidak Kawin, 0 Tanggungan - PTKP Rp 54.000.000/thn)
     - `TK/1` (Tidak Kawin, 1 Tanggungan - PTKP Rp 58.500.000/thn)
     - `K/0` (Kawin, 0 Tanggungan - PTKP Rp 58.500.000/thn)
     - *Rentang Tarif TER A:* Mulai dari 0% (bruto s.d Rp 5,4 jt/bln), 0.25%, 0.5%, 0.75%, 1%, ... s.d 34%.
   - **Kategori TER B**: Untuk karyawan berstatus PTKP:
     - `TK/2` & `TK/3` (Tidak Kawin, 2 atau 3 Tanggungan)
     - `K/1` & `K/2` (Kawin, 1 atau 2 Tanggungan)
     - *Rentang Tarif TER B:* Mulai dari 0% (bruto s.d Rp 6,2 jt/bln) s.d 34%.
   - **Kategori TER C**: Untuk karyawan berstatus PTKP:
     - `K/3` (Kawin, 3 Tanggungan - PTKP Rp 72.000.000/thn)
     - *Rentang Tarif TER C:* Mulai dari 0% (bruto s.d Rp 6,6 jt/bln) s.d 34%.

2. **Mekanisme Perhitungan Masa Pajak:**
   - **Masa Pajak Januari s/d November:**
     $$\text{PPh 21 Bulanan} = \text{Penghasilan Bruto Sebulan} \times \text{Tarif TER Sesuai Kategori (A/B/C) \& Lapisan Bruto}$$
     *Contoh:* Staf status TK/0 (Kategori A) menerima bruto Rp 8.000.000 pada bulan April. Tarif TER A lapisan Rp 7.500.001 s.d Rp 8.550.000 adalah 1.5%. Maka potongan PPh 21 April $= \text{Rp } 8.000.000 \times 1.5\% = \text{Rp } 120.000$.
   - **Masa Pajak Terakhir (Desember / Karyawan Resign di Tengah Tahun):**
     Sistem menghitung ulang PPh 21 menggunakan **Tarif Progresif Pasal 17 UU HPP**:
     1. Akumulasi seluruh Bruto 1 tahun (termasuk THR & bonus).
     2. Kurangi Biaya Jabatan (5% dari bruto, maksimal Rp 500.000/bulan atau Rp 6.000.000/tahun).
     3. Kurangi Iuran JHT Karyawan (2%) dan Iuran JP Karyawan (1%).
     4. Menghasilkan **Penghasilan Neto Setahun**.
     5. Kurangi nilai PTKP tahunan karyawan $\rightarrow$ **Penghasilan Kena Pajak (PKP)**.
     6. Terapkan Tarif Pasal 17 ayat (1) huruf a UU HPP:
        - Lapisan I (s.d Rp 60.000.000): **5%**
        - Lapisan II (> Rp 60.000.000 s.d Rp 250.000.000): **15%**
        - Lapisan III (> Rp 250.000.000 s.d Rp 500.000.000): **25%**
        - Lapisan IV (> Rp 500.000.000 s.d Rp 5.000.000.000): **30%**
        - Lapisan V (> Rp 5.000.000.000): **35%**
     7. Hitung selisih pajak untuk masa Desember:
        $$\mathbf{\text{PPh 21 Terutang Desember}} = \text{PPh 21 Pasal 17 Setahun} - \sum_{i=\text{Jan}}^{\text{Nov}} \text{PPh 21 TER yang Telah Dipotong}$$
     8. Jika terjadi lebih bayar (*over-withholding*), sistem otomatis mengembalikan selisih uang ke gaji karyawan di masa Desember.

3. **Metode Pemotongan Pajak:**
   - **Metode Gross:** Pajak PPh 21 dipotong langsung dari upah bruto karyawan.
   - **Metode Gross-Up:** Perusahaan memberikan **Tunjangan Pajak** yang nilainya dihitung secara iteratif sehingga nilai gaji bersih karyawan tidak berkurang.
   - **Metode Net:** Perusahaan menanggung pajak secara langsung tanpa tunjangan pajak.

### B. Pajak Transaksi Bisnis: PPh Final UMKM 0.5% (PP 55/2022)
1. **Ketentuan Tarif:**
   - Tarif PPh Final sebesar **0.5%** dari omzet kotor bulanan bagi pelaku usaha dengan omzet tahunan di bawah Rp 4.8 Miliar.
2. **Smart Threshold Tracker untuk Wajib Pajak Orang Pribadi (OP):**
   - Sesuai UU HPP & PP 55/2022, peredaran bruto kumulatif s.d **Rp 500.000.000 dalam 1 tahun pajak TIDAK DIKENAKAN PAJAK (Bebas PPh Final)**.
   - Sistem COOCA memonitor akumulasi omzet harian POS dan faktur penjualan.
   - Selama omzet kumulatif tahun berjalan $\le \text{Rp } 500.000.000$, nilai PPh Final terutang adalah **Rp 0**.
   - Begitu omzet menembus batas (misal di bulan Agustus mencapai Rp 530.000.000), sistem otomatis menghitung 0.5% hanya atas kelebihannya (0.5% dari Rp 30.000.000 = Rp 150.000). Pada bulan-bulan berikutnya hingga akhir tahun, seluruh omzet dikenakan 0.5%.
3. **Wajib Pajak Badan (CV / PT):**
   - Tidak berlaku batas pembebasan Rp 500 juta. Sistem langsung menghitung 0.5% dari rupiah pertama omzet kotor setiap bulannya.

### C. Pajak Barang & Jasa Tertentu (PB1 / Pajak Restoran 10%) & PPN
1. **PB1 / Pajak Restoran & Hiburan Daerah (10%):**
   - Sangat krusial untuk klaster kuliner (Resto, Cafe, Katering, Bakery).
   - POS kasir memisahkan secara transparan *Subtotal Belanja*, *PB1 10%*, dan *Service Charge* (jika ada).
   - Jurnal akuntansi memisahkan pendapatan toko murni dengan penampungan akun liabilitas lancar **Utang PB1 Restoran** yang harus disetor ke Badan Pendapatan Daerah (Bapenda).
2. **PPN (Pajak Pertambahan Nilai 11% / 12%):**
   - Untuk bisnis yang telah berstatus Pengusaha Kena Pajak (PKP).
   - Pemisahan otomatis DPP (*Dasar Pengenaan Pajak*) dan PPN Keluaran pada faktur penjualan B2B dan struk POS ritel.
   - Perekaman PPN Masukan dari faktur pembelian barang ke pemasok untuk rekonsiliasi SPT Masa PPN.

### D. Dasbor Rekonsiliasi, Jurnal Akuntansi Pajak & Ekspor DJP (e-Bupot)
1. **Ekspor File e-Bupot 21/26 DJP & Formulir 1721-A1:**
   - Menyediakan tombol 1-klik untuk mengunduh template resmi DJP format Excel/CSV yang siap di-upload ke aplikasi web e-Bupot DJP Online.
   - Otomatis mencetak Formulir 1721-A1 tahunan untuk setiap karyawan tetap.
2. **Rekapitulasi SPT Masa PPh Final UMKM:**
   - Rekap omzet bulanan per cabang dan total pajak 0.5% yang harus dibayar, lengkap dengan panduan pembuatan ID Billing DJP (Kode Akun Pajak 411128, KJS 420).

---

## 10. Layanan Tambahan (Add-On & Consumption Top-Up)

Di luar biaya langganan bulanan, Cooca menyediakan 3 jalur monetisasi tambahan (*Pay-as-you-grow*):

| Jenis Add-on / Top-up | Harga Satuan | Mekanisme & Kebijakan |
| :--- | :---: | :--- |
| 💾 **Top-Up Cloud Storage Owner** | **Rp 50.000 / 1 GB** | Kuota permanen melekat pada akun Owner (tidak hangus tiap bulan). |
| 🤖 **Top-Up Token AI Asisten** | **Rp 50.000 / 1 Juta Token** | Dipakai untuk Gemini 2.5 Flash (Forecast omzet & kalkulasi HPP). |
| 📱 **Add-On Media Sosial Multi-CH** | **Rp 89.000 / bulan** | Unlimited posting IG/FB/TikTok (Bagi tier Standard/Premium). |

---

## 11. Arsitektur Pembayaran Langganan SaaS: 100% Otomatis via TriPay Payment Gateway

### A. Kebijakan: *Exclusive Payment Gateway* (Tanpa Transfer Manual)
Untuk seluruh transaksi langganan SaaS (**Standard, Premium, Prestige**), pembelian **Top-Up Storage**, dan pembelian **Top-Up Token AI**, Cooca memberlakukan aturan mutlak:
- **HANYA DAPAT DILAKUKAN MELALUI PAYMENT GATEWAY TRIPAY**.
- **Tidak ada opsi transfer manual konvensional / upload bukti struk transfer**.
- **Alasan & Manfaat:**
  1. **Aktivasi Instan 24/7:** Begitu pelanggan membayar via QRIS atau Virtual Account, sistem langsung aktif detik itu juga tanpa perlu menunggu verifikasi manual admin.
  2. **Nol Risiko Fraud / Pemalsuan:** Mengeliminasi celah manipulasi bukti transfer bank hasil editan/photoshop.
  3. **Efisiensi Operasional Skala Besar:** Tim admin tidak disibukkan dengan pencocokan mutasi rekening m-banking secara manual.

### B. Kanal Pembayaran TriPay yang Didukung
1. **QRIS Dinamis (Rekomendasi Utama):**
   - Mendukung seluruh aplikasi perbankan (BCA, Mandiri Livin, BRImo, BNI, CIMB, Permata) dan e-Wallet nasional (GoPay, OVO, ShopeePay, DANA, LinkAja).
   - Kode QR unik digenerate secara dinamis sesuai nominal tagihan persis.
2. **Virtual Account (VA) Otomatis:**
   - BCA Virtual Account
   - Mandiri Virtual Account
   - BRI Virtual Account (BRIVA)
   - BNI Virtual Account
   - Permata Virtual Account
   - BSI Virtual Account
3. **Gerai Retail (Convenience Store):**
   - Indomaret & Alfamart (untuk pelaku UMKM yang belum memiliki mobile banking).

### C. Alur Teknis & Webhook Event Lifecycle
```
[ User Pilih Paket ] ──► [ Request TriPay Transaction ] ──► [ Tampilkan QRIS / No. VA ]
                                                                      │
                                                           User Melakukan Pembayaran
                                                                      │
                                                                      ▼
[ Aktivasi Instan Detik Itu ] ◄── [ Callback Webhook TriPay ] ◄── [ Server TriPay ]
  • status: approved                • Validasi HMAC-SHA256
  • ends_at bertambah               • Signature Verified
  • Quota & Storage ter-unlock
```

1. **Pembuatan Transaksi:**
   - Frontend memanggil endpoint checkout -> Backend mengeksekusi `TripayService::createSubscriptionTransaction($payment, $channel)`.
   - TriPay mengembalikan `checkout_url`, `qr_string` (untuk QRIS), atau `pay_code` (nomor Virtual Account).
2. **Penanganan Webhook Otomatis:**
   - Server TriPay mengirimkan notifikasi callback ke `POST /api/v1/payment/tripay-callback`.
   - `TripayCallbackController` memverifikasi signature `HMAC-SHA256` menggunakan `tripay_private_key`.
   - Jika signature valid dan status `'PAID'`:
     - `SubscriptionPayment` diubah ke `status = 'approved'`.
     - `BusinessSubscription` diperbarui (`starts_at = now()`, `ends_at` ditambah sesuai durasi 1 bulan / 1 tahun).
     - Jika pembelian Top-Up: Saldo token AI atau bytes storage otomatis ditambahkan ke akun Owner.
     - Cache kuota di-clear seketika via `clearUsageCache()`.
     - Bot WhatsApp otomatis mengirimkan pesan konfirmasi pembayaran sukses & invoice digital ke nomor WhatsApp Owner.

---

## 12. Desain Antarmuka UI (Bento Apple HIG Standard)

Pada halaman **`limits.blade.php`**, tampilan akan mencerminkan hierarki tier:

1. **Badge Status Tier Aktif**:
   - **Free**: Badge Abu-abu Minimalis (`bg-slate-100 text-slate-700 border-slate-200`)
   - **Standard**: Badge Biru Cerah (`bg-blue-50 text-blue-700 border-blue-200`)
   - **Premium**: Badge Emerald Elegan (`bg-emerald-50 text-emerald-700 border-emerald-200`)
   - **Prestige**: Badge Ungu-Emas Eksklusif (`bg-purple-50 text-purple-700 border-purple-200 font-bold`)

2. **Bento Grid Kartu Quota**:
   - Menggunakan meter progress bar dengan transisi halus (`h-2 rounded-full`).
   - Indikator persentase interaktif:
     - 0% - 74%: Emerald / Cyan
     - 75% - 89%: Amber Warning
     - 90% - 100%: Rose Alert + Trigger Tombol Upgrade

3. **Komponen Quota Paywall Modal**:
   - Ketika kasir mencapai transaksi ke-31 di paket Free:
     - Muncul modal popup ramah: *"Kuota Transaksi Free Telah Mencapai Batas (30 Struk). Usaha Anda berkembang luar biasa! Upgrade ke Standard (hanya Rp29.000/bln) untuk menikmati kasir tanpa batas."*
     - Tombol *"Upgrade Sekarang"* langsung membuka pop-up checkout TriPay (pilihan bayar QRIS / VA instan).

---

## 13. Roadmap Arsitektur Kode Backend (`cooca_core`)

### Tahap 1: Model & Enum Definisi Paket
- Perbarui `app/Models/BusinessSubscription.php`:
  ```php
  public const PLAN_FREE              = 'free';
  public const PLAN_STANDARD_MONTHLY  = 'standard_monthly';
  public const PLAN_STANDARD_ANNUAL   = 'standard_annual';
  public const PLAN_PREMIUM_MONTHLY   = 'premium_monthly';
  public const PLAN_PREMIUM_ANNUAL    = 'premium_annual';
  public const PLAN_PRESTIGE_MONTHLY  = 'prestige_monthly';
  public const PLAN_PRESTIGE_ANNUAL   = 'prestige_annual';
  ```
- Seed katalog paket pada tabel `billing_packages`.

### Tahap 2: Hirarki Entitlement Service, Multi-Branch, HRM & Tax Guards
- Refactor `app/Domain/Billing/EntitlementService.php`:
  - Ganti logika biner `isCorePlan()` dengan metode bertingkat:
    - `hasTier(Business $business, string $requiredTier): bool`
    - `canCreateProduct(Business $business): bool` (Maks 10 Free, 50 Standard, Unlimited Premium/Prestige)
    - `canCreateLocation(Business $business, string $type): bool` (1 Free/Standard, 3 Premium, Unlimited Prestige)
    - `canCreateBusiness(User $owner): bool` (1 Free/Standard, 3 Premium, Unlimited Prestige)
    - `canAddMember(Business $business): bool` (Maks 1 Free, 3 Standard, 10 Premium, Unlimited Prestige)
    - `canTransferStock(Business $business): bool` (Minimal Premium)
    - `canCalculateCommissions(Business $business): bool` (Minimal Premium)
    - `canManageEmployeeLoans(Business $business): bool` (Minimal Premium)
    - `canCalculateBPJS(Business $business): bool` (Minimal Premium)
    - `canCalculateTHR(Business $business): bool` (Minimal Prestige untuk otomatis Join Date)
    - `canCalculatePPh21(Business $business): bool` (Minimal Prestige untuk TER A/B/C + Pasal 17)
    - `canAutomatePayrollWhatsApp(Business $business): bool` (Minimal Prestige)

### Tahap 3: Alokasi Storage Berbasis Tier Tertinggi Owner
- Refactor `app/Domain/Storage/OwnerStorageQuotaService.php`:
  - Dapatkan tier tertinggi dari seluruh bisnis yang dimiliki oleh Owner.
  - Alokasikan kuota dasar:
    - Free: `1 * 1024 * 1024 * 1024` (1 GB)
    - Standard: `3 * 1024 * 1024 * 1024` (3 GB)
    - Premium: `10 * 1024 * 1024 * 1024` (10 GB)
    - Prestige: `30 * 1024 * 1024 * 1024` (30 GB)
  - Tambahkan seluruh saldo pembelian `OwnerStorageTopup`.

### Tahap 4: UI & Billing Web Controller Terintegrasi TriPay
- Perbarui `BillingAndLimitWebController.php` untuk memasok data kuota bertingkat ke tampilan `limits.blade.php`.
- Perbarui antarmuka tabel komparasi 4-kolom (*Comparison Matrix*) di halaman langganan.
- Pasang modal checkout **Exclusive TriPay Payment Gateway** (QRIS & Multi-Bank VA) tanpa opsi upload manual.

---

**Status Dokumen:** Diterima, Diperbarui & Disepakati  
**Versi Blueprint:** 2.3 (Multi-Branch, Comprehensive HRM, BPJS, Loans, Daily Worker, Join-Date THR & Tax Compliance Engine)  
**Arsitektur:** Terverifikasi Kompatibel untuk 20 Sektor Industri COOCA

