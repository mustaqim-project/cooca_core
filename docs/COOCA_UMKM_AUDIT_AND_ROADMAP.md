# MASTER BLUEPRINT: AUDIT, GAP ANALYSIS & ROADMAP COOCA UMKM

**Document Status:** Master Architecture & Engineering Blueprint  
**System Name:** COOCA UMKM — Business Operating System  
**Framework:** Laravel 13.17 (PHP 8.3+) | Database: MySQL 8.0  
**Target User:** Pemilik & Pengelola UMKM (Solopreneur s.d. Tim Terdelegasi)  
**Filosofi:** *"Bukan ERP corporate yang rumit, melainkan Business OS yang sederhana di antarmuka namun memiliki fondasi data transaksi, stok, HPP, dan akuntansi yang presisi dan tidak dapat dikompromikan."*

---

## 1. Executive Summary

Aplikasi `cooca_core` telah diaudit secara menyeluruh dari kode sumber aktual (61 migration, 101 model, 29 direktori domain service). 

### Kondisi Realita Saat Ini:
* **Kekuatan:**
  1. Arsitektur multi-tenancy berbasis `business_id` (UUID) sangat solid dengan penerapan global scope `BelongsToBusiness` dan resolver `Context::businessId()`.
  2. Modul **Kalkulasi HPP Dinamis** dan **BOM (Bill of Materials)** sangat matang (3 pilar: bahan baku, tenaga kerja langsung, mesin/overhead).
  3. Modul **POS Kasir** (`PosOrderService`) sudah menerapkan transaksi atomik, pemotongan stok berdasar gudang, penyimpanan snapshot harga/HPP, dan auto-journal akuntansi double-entry seimbang.
* **Kesenjangan Kritis (Gaps):**
  1. **Stok Bocor pada Invoice:** Modul Faktur Penjualan (`invoices`) saat ini **sama sekali tidak memotong stok inventori** dan tidak memicu jurnal piutang.
  2. **Ketiadaan Modul Retur:** Tidak ada tabel, model, maupun logika untuk **Retur Penjualan** dan **Retur Pembelian**.
  3. **Penghapusan Transaksi (Destructive Delete):** Masih ada endpoint `DELETE` fisik untuk Purchase Order dan Invoice yang sudah aktif.
  4. **Pemisahan Menu Berorientasi Teknis:** Menu saat ini terpecah-pecah (misal: "Upah Kerja", "Mesin", "Simulasi What-If" terpencar) dan belum mencerminkan alur kerja alami pemilik UMKM.

---

## 2. Struktur Menu Baru: Berorientasi Alur Kerja UMKM

Navigasi disusun berdasarkan alur mental pemilik bisnis:  
**"Saya punya bisnis → saya jual → saya beli → saya kelola stok → saya hitung modal → saya kelola uang → saya lihat hasil bisnis."**

```text
🏠 Dashboard

💼 BISNIS
   ├── Bisnis               (Profil usaha, mata uang, pajak, pembulatan)
   ├── Outlet               (Cabang toko / kasir fisik)
   └── Gudang               (Pusat penyimpanan & logistik)

🛍️ PENJUALAN
   ├── POS / Kasir          (Penjualan langsung layar sentuh & shift)
   ├── Invoice              (Faktur penjualan B2B / pesanan bertahap)
   ├── Pelanggan            (CRM, riwayat belanja, saldo kredit)
   └── Retur Penjualan      (Pengembalian barang rusak/salah dari klien)

📦 PEMBELIAN
   ├── Supplier             (Database pemasok & kontak vendor)
   ├── Purchase Order       (Order pengadaan barang & bahan)
   ├── Pembelian            (Penerimaan fisik barang masuk / Goods Receipt)
   └── Retur Pembelian      (Pengembalian barang rusak ke supplier)

📦 PERSEDIAAN
   ├── Produk               (Master barang, SKU, barcode, unit, kategori)
   ├── Stok                 (Saldo stok real-time per outlet & gudang)
   ├── Mutasi Stok          (Kartu stok lengkap seluruh pergerakan barang)
   ├── Transfer Stok        (Pemindahan barang antar gudang/cabang)
   └── Stock Opname         (Pencocokan fisik berkala & rekonsiliasi)

🧮 HPP & PRODUKSI
   ├── Recipe / BOM         (Komposisi resep bahan baku produk jadi)
   ├── HPP / Costing        (Kalkulator modal 3 pilar: Bahan, Upah, Mesin)
   └── Riwayat Harga        (Histori perubahan harga beli & modal HPP)

💰 KEUANGAN
   ├── Kas & Bank           (Buku rekening, petty cash, saldo real)
   ├── Pemasukan            (Pemasukan operasional di luar transaksi jual)
   ├── Pengeluaran          (Beban operasional toko, gaji, listrik, sewa)
   ├── Piutang              (Daftar tagihan customer belum lunas & aging)
   └── Hutang               (Daftar tagihan supplier jatuh tempo)

📊 LAPORAN
   ├── Penjualan            (Laporan omzet harian/bulanan, per kasir, per produk)
   ├── Pembelian            (Rekap belanja, rata-rata harga beli supplier)
   ├── Persediaan           (Valuasi aset stok, barang fast/slow moving)
   ├── Laba & Margin        (Laba kotor riil berbasis snapshot HPP)
   ├── Keuangan             (Arus kas masuk-keluar cash flow)
   └── Akuntansi            (Jurnal transaksi otomatis, Buku Besar, Neraca, L/R)

🤖 AI ASSISTANT             (Konsultasi bisnis, anomali biaya, rekomendasi harga)

⚙️ PENGATURAN
   ├── Profil Bisnis        (Identitas, logo, format nomor nota)
   ├── User & Role          (Hak akses: Owner, Admin, Kasir, Gudang, Finance)
   ├── Metode Pembayaran    (Tunai, Transfer Bank, QRIS, EDC)
   ├── Pajak                (Konfigurasi PPN / PB1)
   ├── Workflow             (Pengaturan approval PO & Invoice)
   └── Pengaturan Sistem    (Backup data, lisensi SaaS, preferensi)
```

---

## 3. Technology Stack & Fondasi Sistem

| Komponen | Spesifikasi Existing | Catatan Evaluasi |
| :--- | :--- | :--- |
| **Framework** | Laravel 13.17 (Skeleton 11/12/13) | Bersih, cepat, routing modern di `routes/web.php` dan `routes/api.php` |
| **PHP Runtime** | PHP 8.3+ (Strict Types enforced) | Menggunakan typed properties, constructor promotion, match expression |
| **Database** | MySQL 8.0 / MariaDB | Relasi InnoDB dengan `CASCADE` dan `RESTRICT` yang terstruktur |
| **Multi-Tenancy** | Shared-Database Multi-Tenant | Pemisahan via `business_id` UUID pada seluruh tabel tenant |
| **Frontend UI** | Blade + TailwindCSS + Alpine.js | Ringan, responsif, glassmorphism dark-mode tanpa overhead SPA |
| **Icons & Visual** | Lucide Icons + Chart.js | Visual modern dengan hierarki visual kontras tinggi |
| **Auth & Guard** | Multi-guard: `web`, `admin`, `sanctum` | Pemisahan tegas SaaS Operator vs Tenant UMKM |
| **Authorization** | Custom RBAC (`Context::hasPermission`) | Terpasang rapi di `BusinessMembership`, `Role`, `Permission` |
| **Storage & File** | Local disk (`storage/app/public`) | Bukti transfer, receipt attachment, logo bisnis |

---

## 4. Analisis Alur Transaksi & Data Integrity

### A. Konsep Stock (Single Source of Truth)
Seluruh mutasi stok wajib melalui satu gerbang: **`StockService`** yang mencatat buku besar **`stock_movements`**.

```text
[Transaksi Bisnis: POS / Invoice / Goods Receipt / Transfer / Opname / Retur]
                                │
                                ▼
                       [ StockService ]
                 (Pessimistic Lock: lockForUpdate)
                                │
        ┌───────────────────────┴───────────────────────┐
        ▼                                               ▼
[ inventory_stocks ]                           [ stock_movements ]
(Saldo riil per lokasi)                 (Audit trail kartu stok immutable)
```

* **Aturan Mutasi Stok:**
  1. Wajib memiliki referensi (`reference_id` dan `reference_number`).
  2. Wajib atomic di dalam `DB::transaction`.
  3. Mengunci baris stok dengan `lockForUpdate()` untuk mencegah race condition checkout simultan.
  4. Mencegah stok minus kecuali diizinkan oleh pengaturan bisnis.

### B. Konsep Price Snapshot (Integritas Riwayat Finansial)
Perubahan master data produk atau harga supplier **TIDAK BOLEH** mengubah angka pada laporan transaksi historis.

```text
MASTER DATA (Dapat Berubah):
- Product Selling Price = Rp 120.000
- Product Base Cost     = Rp  75.000

TRANSAKSI HARI INI (Snapshot Disimpan Permanen):
- Unit Price Snapshot   = Rp 100.000
- Unit HPP Snapshot     = Rp  65.000
- Qty                   = 2
- Total Penjualan       = Rp 200.000
- Total HPP             = Rp 130.000
- Laba Kotor            = Rp  70.000

JIKA BESOK HARGA MASTER NAIK KE Rp 150.000:
Transaksi lama TETAP mencatat Laba Kotor Rp 70.000.
```

### C. Konsep State Machine Dokumen

| Modul | Alur State Machine yang Disahkan | Kapan Stok Berubah? | Kapan Jurnal Terbentuk? |
| :--- | :--- | :--- | :--- |
| **POS Kasir** | `draft` → `completed` (atau `void` / `refund`) | Saat `completed` | Saat `completed` |
| **Sales Invoice** | `draft` → `sent` → `approved/released` → `paid` | Saat status `released` / `paid` | Saat `released` (Piutang) & `paid` (Kas) |
| **Purchase Order**| `draft` → `confirmed` → `received` → `completed` | Saat Goods Receipt diterima fisik | Saat penerimaan barang fisik di gudang |
| **Retur Jual** | `draft` → `approved` → `completed` | Saat `completed` (Stok masuk kembali) | Saat `completed` (Reversal HPP & Pendapatan)|
| **Retur Beli** | `draft` → `approved` → `completed` | Saat `completed` (Stok keluar) | Saat `completed` (Potong Hutang & Stok) |

---

## 5. Gap Analysis Komprehensif

| Area / Modul | Kondisi Repository Saat Ini | Kebutuhan Target Cooca UMKM | Klasifikasi Gap | Prioritas |
| :--- | :--- | :--- | :--- | :---: |
| **Sales Invoice & Stock** | Invoice hanya mencatat piutang dan pembayaran; **tidak ada kode yang memotong stok** | Invoice yang disahkan/dibayar wajib memotong stok gudang asal | **Kritis (Data Inconsistency)** | **P0** |
| **Sales Invoice & Accounting** | Pembayaran invoice belum memicu pembuatan jurnal otomatis kas/piutang | Menghasilkan jurnal Piutang Usaha saat terbit & Kas masuk saat bayar | **Kritis (Accounting Gap)** | **P0** |
| **Retur Penjualan** | Belum ada model, migration, controller, maupun tampilan retur penjualan | Alur retur barang dari customer, koreksi stok masuk kembali, kredit nota | **Missing Feature** | **P1** |
| **Retur Pembelian** | Belum ada model, migration, controller, maupun tampilan retur pembelian | Alur retur barang cacat ke supplier, potong stok gudang, potong hutang | **Missing Feature** | **P1** |
| **Pencegahan Concurrency Stok** | `StockService::deductForPosSale` belum memakai `lockForUpdate()` | Mengunci baris stok selama transaksi checkout kasir simultan | **Data Integrity / Race Condition** | **P0** |
| **Buku Kas & Bank** | Ada tabel `payment_accounts` dan `expenses`, namun belum ada mutasi buku kas terpusat | Pengelolaan kas kasir, rekening bank, mutasi transfer antar akun | **Usability & Control** | **P1** |
| **Purchase Cost History** | PO mencatat harga beli per item, namun belum ada kalkulator weighted average cost | Menghitung harga beli rata-rata tertimbang (WAC) per supplier & produk | **Costing Precision** | **P2** |
| **Pemisahan Menu UMKM** | Menu sidebar masih mengelompokkan HPP & kalkulator secara terpisah | Restrukturisasi navigasi sidebar sesuai 10 pilar pekerjaan bisnis UMKM | **UX Alignment** | **P1** |
| **Penghapusan Data Transaksi** | Controller Invoice & PO menyediakan fungsi `destroy()` fisik | Larangan `DELETE` fisik transaksi final; wajib memakai `void` atau `cancel` | **Compliance & Security** | **P0** |

---

## 6. Master Roadmap Perbaikan (Module-by-Module)

Pengerjaan wajib dilakukan berurutan berdasarkan dependensi fondasi data:

```text
FASE 1: Fondasi Inventori & Stock Service Centralization (P0)
   ├── 1.1 Atomic Lock & Single Gateway StockService
   ├── 1.2 Proteksi Transaksi Final (Nonaktifkan Hard Delete)
   └── 1.3 Verifikasi Saldo & Pencegahan Stok Minus

FASE 2: Integrasi Sales Invoice ke Inventori & Keuangan (P0)
   ├── 2.1 State Machine Invoice (Draft → Released → Paid)
   ├── 2.2 Otomasi Pemotongan Stok dari Invoice Released
   └── 2.3 Auto-Journal Piutang & Penerimaan Kas Invoice

FASE 3: Purchasing, Receiving & Manajemen Hutang Supplier (P1)
   ├── 3.1 Integrasi Goods Receipt ke Pencatatan Hutang Usaha
   ├── 3.2 Pelunasan Tagihan Supplier (AP Payment Flow)
   └── 3.3 Riwayat Harga Beli Rata-Rata (Weighted Average Purchase Price)

FASE 4: Modul Retur (Retur Penjualan & Retur Pembelian) (P1)
   ├── 4.1 Modul Retur Penjualan (Restock + Nota Kredit / Refund)
   ├── 4.2 Modul Retur Pembelian (Pengurangan Stok + Nota Debet Hutang)
   └── 4.3 Jurnal Akuntansi Khusus Retur

FASE 5: Buku Kas, Bank & Manajemen Arus Kas (P1)
   ├── 5.1 Buku Kas & Bank Terpusat (Cash & Bank Ledger)
   ├── 5.2 Pencatatan Pemasukan Non-Penjualan & Pengeluaran Operasional
   └── 5.3 Transfer Antar Kas/Bank dengan Validasi Saldo

FASE 6: Restrukturisasi Sidebar Navigasi Berbasis Pekerjaan UMKM (P1)
   ├── 6.1 Penyusunan Ulang Grup Menu Sesuai Standar Cooca UMKM
   ├── 6.2 Relokasi Sub-Fitur (Riwayat Harga masuk Produk; Akuntansi masuk Laporan)
   └── 6.3 Pengaturan Hak Akses Menu Dinamis Berdasarkan Permission

FASE 7: Engine Pelaporan Komprehensif Berbasis Snapshot (P2)
   ├── 7.1 Laporan Laba Rugi Riil (Gabungan POS + Invoice - Beban)
   ├── 7.2 Laporan Arus Kas Masuk-Keluar (Cash Flow)
   ├── 7.3 Laporan Umur Piutang (AR Aging) & Umur Hutang (AP Aging)
   └── 7.4 Laporan Valuasi & Perputaran Stok (Fast/Slow Moving)

FASE 8: AI Assistant Guardrails & Analytics Tools (P3)
   ├── 8.1 Fine-Tuning Tool Registry (Batasi Hanya Hak Akses Role Terkait)
   ├── 8.2 Anomaly Detector: Margin Turun & Lonjakan Harga Bahan
   └── 8.3 Rekomendasi Restock Cerdas Berdasarkan Lead Time Supplier
```

---

## 7. Rekomendasi Pengerjaan Modul Pertama

### **MODUL FASE 1: FONDASI INVENTORI & STOCK SERVICE CENTRALIZATION (P0)**

#### Mengapa Modul Ini Wajib Dikerjakan Pertama Kali?
1. **Pondasi dari Segala Transaksi:** Penjualan (POS & Invoice), Pembelian (Goods Receipt), Transfer, Opname, dan Retur semuanya bermuara pada stok. Jika gerbang stok belum memiliki penguncian konkurensi (`lockForUpdate`), penolakan stok minus, dan integritas referensi, maka perbaikan pada modul lain akan tetap menghasilkan data inventori yang korup.
2. **Kunci Pembuka untuk Invoice:** Kita tidak bisa memperbaiki modul Invoice (Fase 2) untuk memotong stok sebelum `StockService` memiliki method baku yang siap menerima pemotongan dari invoice dengan aman.
3. **Mengamankan Transaksi Fisik:** Menutup lubang keamanan hard-delete pada dokumen transaksi yang sudah terbit.

