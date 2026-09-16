# System Overview (Arsitektur Ekosistem Cooca)

> **Status:** VERIFIED  
> **Modul Terkait:** Seluruh Modul Core, Multi-Tenant, & Domain Packages  
> **Pilar:** Laravel 11, PHP 8.3+, Domain-Driven Design (33 Packages), Tailwind CSS + Apple HIG v2.0, Multi-Tenant SaaS.

---

## 1. Visi & Tujuan Sistem

**Cooca** adalah sistem operasi bisnis terpadu (*Enterprise SaaS ERP & UMKM Operating System*) yang dirancang untuk mengotomasi operasional bisnis dari hulu ke hilir: mulai dari kalkulasi biaya dan HPP presisi, pengadaan bahan baku, manajemen inventori multi-gudang, terminal kasir Point of Sale (POS), toko online mandiri (*storefront*), hingga pembukuan akuntansi berpasangan (*double-entry*) otomatis dan notifikasi real-time via WhatsApp.

Sistem secara khusus dioptimalkan dengan prinsip **Antarmuka Tanpa Panduan (*Zero-Manual / Self-Explanatory UI*)** menggunakan standar desain **Apple Human Interface Guidelines (macOS Sonoma & iOS 18) dan Bento Grid UI**, memberikan kenyamanan maksimal bagi generasi Boomers (50–65+ tahun) dan milenial akhir yang *gaptek* (tidak cakap teknologi).

---

## 2. Diagram Arsitektur Tingkat Tinggi

```
+---------------------------------------------------------------------------------------------------+
|                                  COOCA ENTERPRISE ECOSYSTEM                                       |
+---------------------------------------------------------------------------------------------------+
| [APLIKASI FRONTEND & MULTI-DEVICE CLIENTS]                                                        |
|   ├── Web Panel Pemilik Usaha & Kasir (Blade + Tailwind CSS + Alpine.js + Apple HIG v2.0 Bento)   |
|   ├── Toko Online Mandiri / Storefront (/b/{slug} & /customer/* - Mobile Touch Optimized)         |
|   ├── Aplikasi Kasir Native Mobile POS (Flutter / Dart - Clean Architecture Android & iOS)        |
|   ├── Panel Superadmin Platform (/admin/* - SaaS Tenant, Billing, Quotas & Monitoring)            |
|   └── Ekosistem Publik & SEO (/kalkulator/*, /blog/*, /template/*, /solutions/*)                  |
+---------------------------------------------------------------------------------------------------+
| [DOMAIN & APPLICATION CORE (LARAVEL 11 / PHP 8.3+)]                                              |
|   ├── Multi-Tenant Scoping Engine (Strict Isolation via App\Support\Context::requireBusiness())   |
|   ├── 33 Domain Packages (DDD Structure di app/Domain/)                                           |
|   ├── Dynamic HPP & Bill of Materials (BOM) Mathematical Engine                                   |
|   ├── Real-Time Multi-Channel Cash Ledger & Running Balance System                                |
|   ├── Automated Double-Entry Journal Engine (AutoJournalService)                                  |
|   └── Role-Based Access Control (RBAC) & Dynamic Feature Entitlements                             |
+---------------------------------------------------------------------------------------------------+
| [MICROSERVICES, LATAR BELAKANG & INTEGRASI EKSTERNAL]                                             |
|   ├── WhatsApp Gateway (Node.js microservice di wa-server/ dengan Baileys headless)               |
|   ├── Background Job Workers & Automation Scheduler (Cron, Queue Listeners, Auto-Reminder)        |
|   ├── Thermal Receipt Engine (ESC/POS 58mm & 80mm Direct USB, Bluetooth & Network)                |
|   └── Payment Settlement Trackers (Tunai, QRIS, Transfer Bank, Kasbon Piutang)                   |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. Struktur 33 Domain Packages (Domain-Driven Design)

Direktori `app/Domain/` memisahkan logika bisnis kompleks menjadi 33 domain otonom:

1. **Accounting**: Bagan Akun (*Chart of Accounts*), Jurnal Umum (*General Ledger*), Buku Besar, Penutupan Buku, & Laporan Keuangan.
2. **Ai**: Integrasi AI Assistant (rekomendasi stok, analisis margin, asisten cerdas).
3. **Billing**: Paket langganan (*BillingPackage*), faktur tagihan SaaS, dan perpanjangan langganan tenant.
4. **Calculation**: Mesin hitung matematis dasar, konversi nilai, pembulatan harga, dan margin kotor/bersih.
5. **Commerce**: Toko online mandiri, storefront checkout, keranjang belanja pelanggan, aturan pengiriman (*shipping rules*), pesanan terjadwal, & reservasi.
6. **Crm**: Segmentasi pelanggan, histori pembelian, program loyalitas, poin member, dan voucher promosi.
7. **Currency**: Konfigurasi mata uang usaha, nilai tukar (*exchange rates*), dan format pemisah ribuan.
8. **Document**: Generator dokumen PDF (Faktur/Invoice, Surat Jalan/Delivery Note, PO, Kwitansi).
9. **Finance**: Manajemen akun kas & bank, mutasi kas masuk/keluar, pelacakan kasir, AP (Hutang), AR (Piutang).
10. **Formula**: Mesin rumus dinamis untuk komponen biaya kustom.
11. **Import**: Pipeline import data massal Excel/CSV untuk produk, bahan baku, dan kontak dengan validasi ketat.
12. **Inventory**: Stok gudang multi-lokasi, mutasi persediaan, *Goods Receipt*, *Stock Opname*, penyesuaian stok (*Adjustment*).
13. **Labor**: Pengaturan tarif tenaga kerja langsung (per jam, harian, borongan) untuk kalkulasi HPP.
14. **LandingPage**: CMS dinamis untuk profil publik bisnis (`/b/{slug}`), galeri produk, jam buka, dan tautan sosial.
15. **Machine**: Pembebanan biaya mesin, depresiasi alat, dan konsumsi energi (listrik/bahan bakar) ke dalam HPP.
16. **Mail**: Notifikasi email transaksional sistem (verifikasi email, aktivasi paket, faktur pelanggan).
17. **Material**: Master data bahan baku mentah, satuan beli, satuan pakai resep, dan jejak histori harga supplier.
18. **OperatingMode**: Pengaturan mode operasional bisnis (Retail, FnB / Kafe, Jasa/Servis, Manufaktur, Grosir).
19. **Overhead**: Biaya operasional tidak langsung, sewa tempat, internet, utilitas, dan aturan alokasi overhead pabrik.
20. **Pos**: Sesi terminal kasir POS, shift kasir, transaksi cepat, pemotongan stok otomatis, cetak struk thermal, diskon kasir.
21. **Pricing**: Aturan penetapan harga dinamis, harga bertingkat (*tiered pricing*), dan mark-up target.
22. **Product**: Master katalog produk jadi dan jasa, kategori, barcode, SKU, gambar, varian, dan keterkaitan resep.
23. **Profitability**: Analisis margin kotor, margin operasional, margin bersih, dan Margin of Safety (MoS).
24. **Purchasing**: Manajemen pemasok (*Suppliers*), Surat Pesanan (*Purchase Orders / PO*), tagihan vendor (*Vendor Bills*).
25. **Report**: Agregasi data laporan penjualan, perputaran stok, analisa produk terlaris, dan rekapitulasi shift.
26. **Sales**: Penawaran harga (*Quotations*), Sales Orders (SO), Faktur Penjualan komersial B2B, dan Retur Penjualan.
27. **Simulation**: Simulator skenario "What-If" (sensitivitas kenaikan bahan baku, inflasi upah, simulasi diskon).
28. **Storage**: Pengelolaan berkas media, foto produk, logo bukti transfer, dan batas kuota penyimpanan cloud.
29. **System**: Pengaturan global platform, audit log, konfigurasi cache, dan status kesehatan server.
30. **Template**: Template bisnis spesifik industri (FnB, Kafe, Laundry, Bengkel, Fashion, Bakery, dll).
31. **Variance**: Analisis varians biaya standar vs biaya aktual (*standard vs actual costing variance*).
32. **Versioning**: Pelacakan riwayat versi resep produk dan versi kalkulasi HPP dari waktu ke waktu.
33. **WhatsApp**: Integrasi gateway pesan instan WhatsApp (pengiriman struk belanja, notifikasi pesanan, OTP verifikasi).

---

## 4. Matriks 4 Kuadran Aktor (Actor Boundaries)

Sistem Cooca beroperasi melintasi 4 kuadran aktor dengan batasan keamanan ketat:

```
┌─────────────────────────────────────────────────────────────┐
│ 1. SUPERADMIN BACKOFFICE (/admin/*)                         │
│ • Guard: auth:admin                                         │
│ • Fungsi: Pengawasan tenant, kuota SaaS, paket langganan.   │
│ • Batasan: Tidak boleh memutasi transaksi kasir tanpa audit.│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 2. BUSINESS OWNER & TIM KASIR (/app/* atau /owner/*)        │
│ • Guard: auth:web + business.active + RBAC Permissions      │
│ • Fungsi: POS kasir, input stok, pembukuan, laporan laba.   │
│ • Batasan: Wajib terisolasi penuh pada Context bisnis aktif.│
└──────────────────────────────┬──────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────┐
│ 3. CUSTOMER / PEMBELI STOREFRONT (/b/{slug} & /customer/*)  │
│ • Guard: auth:customer + IDOR Shield (No cross-tenant data) │
│ • Fungsi: Belanja toko online, lacak order, upload bukti.   │
│ • Batasan: Tidak dapat mengakses panel manajemen apa pun.   │
└──────────────────────────────▲──────────────────────────────┘
                               │
┌──────────────────────────────┴──────────────────────────────┐
│ 4. SUBSISTEM OTOMASI & BACKGROUND (System Automation)       │
│ • Trigger: Event Listeners, Webhooks, Scheduler Cron        │
│ • Fungsi: Auto-Journal, auto-potong stok BOM, dispatch WA.  │
│ • Batasan: Harus idempotent dan memiliki fail-safe fallback.│
└─────────────────────────────────────────────────────────────┘
```

---

## 5. Prinsip Desain & Aksesibilitas (Apple HIG & Boomer-Friendly)

1. **Material Translucent & Dynamic Squircle**: Seluruh kartu menggunakan sudut membulat organik (`rounded-[20px]` hingga `rounded-[24px]`) dengan border hairline lembut (`border-black/[0.06]`).
2. **Kenyamanan Jempol & Penglihatan**:
   - Touch targets tombol aksi utama berukuran minimal **48px hingga 52px**.
   - Ukuran font input mobile minimal **16px** untuk mengeliminasi gangguan auto-zoom browser iOS/Android.
3. **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*)**: Menampilkan pesan yang menenangkan pengguna usia 50+ tahun bahwa riwayat transaksi mereka selalu aman.
4. **Format Ribuan Otomatis**: Setiap input nominal uang memformat pemisah ribuan otomatis secara instan (`Rp 100.000`).
