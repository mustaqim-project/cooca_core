# Laporan Analisis Arsitektur & Rencana Perbaikan: Integrasi Produk POS vs Marketplace COOCA

> **Status Dokumen:** COMPREHENSIVE ARCHITECTURAL AUDIT & IMPLEMENTATION PLAN  
> **Master Rujukan:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md) & [`docs/system/modules/marketplace.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/marketplace.md)  
> **Domain Terkait:** `Product Catalog`, `Point of Sale (POS)`, `Marketplace Hub (Shopee, TikTok, Tokopedia)`, `Inventory & BOM`, `Financial Engine`  
> **Tanggal Audit:** 30 September 2026  

---

## 1. Executive Summary & Putusan Arsitektur

Berdasarkan audit mendalam terhadap seluruh lapisan sistem (Database Schema, Domain Service, Controller, Request Validation, hingga Blade & Alpine.js UI), disimpulkan bahwa:

> ### 🛑 KESIMPULAN UTAMA: DILARANG MEMISAHKAN PRODUK POS DAN MARKETPLACE MENJADI ENTITAS/TABEL BERBEDA.
> 
> Produk fisik di rak toko dan produk yang dijual di Shopee/TikTok/Tokopedia adalah **barang fisik yang sama**, menggunakan inventori/stok yang sama, HPP yang sama, dan supplier/BOM yang sama.  
> Memisahkan entitas produk menjadi 2 master data (`pos_products` vs `marketplace_products`) adalah **anti-pattern fatal** dalam arsitektur ERP/Omnichannel karena akan merusak sinkronisasi stok, memicu *overselling*, menduplikasi beban input data pengguna, dan merusak akurasi laporan keuangan/akuntansi.

### Solusi Standar Industri: *Single Master Product with 3-Tier Taxonomy Mapping Layer*
Untuk mengakomodasi kasus di mana **Kategori POS dapat disetting secara bebas oleh merchant**, sedangkan **Kategori Marketplace harus mengikuti standar taksonomi resmi Shopee, TikTok Shop, dan Tokopedia**, sistem menerapkan arsitektur **3-Tier Taxonomy Mapping**:

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ TIER 1: Kategori Bebas Internal (POS & Operasional Toko)                   │
│ [ProductCategory] -> Nama: "Kopi Signature & Mocktail" (Bebas Dibuat User)   │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │ MAPPING
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ TIER 2: Mapping Kategori Standar Marketplace (Level Kategori Induk)          │
│ marketplace_category_id: "601100" (Makanan & Minuman / FnB)                │
│ -> Otomatis diwariskan (inherited) ke seluruh produk di dalam kategori ini   │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │ OVERRIDE (OPSIONAL)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ TIER 3: Mapping Khusus Per-Produk / Per-Saluran (Jika Ada Pengecualian)     │
│ [MarketplaceProductMapping] -> raw_metadata['category_id']                  │
│ -> Digunakan jika 1 produk spesifik butuh kategori marketplace berbeda      │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Audit Sistem Hulu-ke-Hilir (Existing State vs Ideal State)

### 2.1 Lapisan Basis Data & Model Eloquent
| Komponen | Status Saat Ini | Analisis & Evaluasi |
| :--- | :---: | :--- |
| **`Product` (`products`)** | ✅ Kuat | Memiliki flag visibilitas (`show_in_pos`, `show_in_website`, `show_in_sales_order`), berat/dimensi pengiriman, relasi BOM, dan relasi `marketplaceMappings()`. |
| **`ProductCategory` (`product_categories`)** | ✅ Kuat | Sudah memiliki kolom `marketplace_category_id` dan `marketplace_category_name` untuk menautkan kategori internal ke taksonomi resmi. |
| **`MarketplaceProductMapping`** | ✅ Kuat | Sudah memiliki `channel`, `external_product_id`, `channel_price`, `price_multiplier`, `custom_stock`, `stock_buffer`, dan `raw_metadata['category_id']`. |
| **`MarketplaceCategoryRegistry`** | ✅ Kuat | Memuat taksonomi resmi 22+ kategori utama marketplace Indonesia dilengkapi dengan sistem *Smart AI Suggestion* berbasis kata kunci. |

### 2.2 Lapisan Bisnis & Domain Service
| Komponen | Status Saat Ini | Analisis & Evaluasi |
| :--- | :---: | :--- |
| **`MarketplaceSyncService::publishProductToChannel()`** | ✅ Sangat Kuat | Mengimplementasikan resolusi kategori bertingkat: Explicit Options → Kategori Induk (`category->marketplace_category_id`) → Smart Auto-Suggestion. |
| **Pencegahan Jual Rugi (*Anti-Margin Bleed Guard*)** | ✅ Aktif | Memblokir penerbitan produk jika harga saluran berada di bawah HPP (`effectivePrice < baseCost`). |
| **Restriksi Regulasi BPOM & Sektor Jasa** | ✅ Aktif | Otomatis menolak penerbitan item Jasa (`isService()`) dan Obat Keras Apotek (`isRestrictedPharmacyProduct()`). |
| **Sinkronisasi Stok Satu Pintu (*Atomic Stock Sync*)** | ✅ Aktif | Menggunakan `calculateEffectiveAvailableStock()` dan `stock_buffer` agar pesanan online tidak bentrok dengan transaksi kasir offline. |

### 2.3 Lapisan Antarmuka Pengguna (UI / Blade / Alpine.js)
| Halaman / Antarmuka | Status Saat Ini | Gap / Temuan Masalah |
| :--- | :---: | :--- |
| **Master Data Kategori (`master-data/index.blade.php`)** | ⚠️ Cukup | Sudah ada dropdown pemetaan kategori marketplace, namun belum ada badge status yang jelas apakah kategori ini sudah terpetakan atau belum di tabel indeks. |
| **Katalog Produk (`products/index.blade.php`)** | ⚠️ Perlu Penyempurnaan | Form Tambah/Edit Produk belum memiliki kartu konfigurasi marketplace yang adaptif (*progressive disclosure*). Belum ada toggle eksplisit "Aktifkan untuk Marketplace" yang otomatis menyembunyikan/menampilkan field terkait marketplace. |
| **Terminal Kasir POS (`pos/index.blade.php`)** | ✅ Sempurna | Kasir POS hanya melihat kategori internal yang bersih dan cepat, tanpa terganggu oleh istilah teknis kategori Shopee/TikTok. |
| **Marketplace Hub (`marketplace/products.blade.php`)** | ✅ Sangat Kuat | Cockpit pemetaan multi-harga, margin live calculator, alokasi stok, dan pencarian taksonomi marketplace sudah berjalan optimal. |

---

## 3. Matriks Gap & Rencana Mitigasi Human Error & Fraud

Berikut adalah 5 skema proteksi utama untuk mencegah kesalahan manusia (*human error*) dan kecurangan (*system fraud*):

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│                   5 PILAR PROTEKSI HUMAN ERROR & FRAUD                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ 1. Contextual Auto-Hide & Progressive Disclosure (Hide jika non-aktif)     │
│ 2. Automatic Taxonomy Inheritance & Validation Gate (Kategori Mismatch)     │
│ 3. Anti-Margin Bleed Guard & Live Fee Calculator (Cegah Rugi Biaya Admin)   │
│ 4. Regulatory & Sector Compliance Lock (BPOM Obat Keras & Filter Jasa)      │
│ 5. Atomic Stock Deduction & Safety Buffer (Cegah Overselling Kasir vs Toko) │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 3.1 Mitigasi 1: Penjagaan & Auto-Hide Konfigurasi Marketplace (*Progressive Disclosure*)
* **Masalah:** Jika seluruh opsi marketplace dibuka begitu saja di form produk, kasir/staf toko akan bingung dan salah mengisi kategori atau harga.
* **Solusi:**  
  1. Pada form Tambah/Edit Produk, sediakan saklar kontrol: `[ Jual di Marketplace ]`.
  2. **Jika Dimatikan (Default untuk produk kasir murni / FnB meja):** Seluruh input taksonomi marketplace, dimensi paket, dan mapping channel otomatis tersembunyi (*hidden*). Kasir hanya perlu memilih Kategori Bebas POS.
  3. **Jika Dihidupkan:** Sistem secara cerdas memunculkan kartu konfigurasi marketplace (*Bento Card Apple HIG*) dan memvalidasi syarat wajib marketplace (Berat Gramasi > 0, Kategori Marketplace Valid, Minimal 1 Foto Produk).

### 3.2 Mitigasi 2: Pencegahan Kategori Kosong / Mismatch (*Taxonomy Fallback Gate*)
* **Masalah:** User memilih kategori bebas di POS (misal: "Promo Kopi"), tetapi kategori tersebut belum ditautkan ke kategori resmi Shopee/TikTok.
* **Solusi:**  
  1. Sistem membaca `product->category->marketplace_category_id`.
  2. Jika kategori induk sudah memiliki mapping, form menampilkan badge hijau: `✓ Terpetakan ke: Makanan & Minuman (601100)`.
  3. Jika kategori induk belum memiliki mapping, sistem menampilkan status `⚠️ Belum Dipetakan` disertai tombol 1-klik `[ Pilih Kategori Marketplace ]` atau tombol `[ Gunakan Rekomendasi Cerdas: Makanan & Minuman ]`.

### 3.3 Mitigasi 3: Anti-Margin Bleed Guard (Jual Rugi Akibat Potongan Fee Admin Platform)
* **Masalah:** Admin mengeset harga jual produk di marketplace sama dengan harga toko offline, tanpa menyadari adanya potongan komisi/fee Shopee & TikTok (~6% s/d 10%), sehingga bisnis mengalami kerugian margin (*margin bleed*).
* **Solusi:**  
  1. Di form produk & modal mapping, kalkulator Alpine.js menghitung estimasi biaya platform secara live:  
     $$\text{Estimasi Payout Bersih} = \text{Harga Saluran} - (\text{Harga Saluran} \times \text{Fee 8\%})$$
  2. Jika $\text{Estimasi Payout Bersih} < \text{HPP (Base Cost)}$, sistem menampilkan peringatan visual merah mencolok dan mengunci tombol simpan kecuali user mengonfirmasi override.

### 3.4 Mitigasi 4: Kepatuhan Regulasi Hukum (BPOM RI & Sektor Servis)
* **Masalah:** Karyawan apotek tidak sengaja mempublish obat keras atau jasa pemasangan bengkel ke marketplace umum, yang berisiko pencabutan izin apotek atau komplain resi fiktif.
* **Solusi:**  
  1. Sistem secara otomatis mendeteksi kata kunci obat keras (antibiotik, psikotropika, resep dokter, daftar G).
  2. Opsi penerbitan marketplace otomatis **di-disable permanen (Hard-Lock)** dengan pesan edukasi regulasi hukum BPOM RI No. 8 Tahun 2020.
  3. Produk bertipe `service` (Jasa) otomatis dinonaktifkan dari modul ekspedisi marketplace.

### 3.5 Mitigasi 5: Pencegahan Overselling & Discrepancy Stok (Kasir Fisik vs Marketplace)
* **Masalah:** Sisa stok barang di toko tinggal 1 pcs. Kasir sedang melayani pembeli di meja kasir, namun di saat bersamaan pembeli online di Shopee melakukan checkout, menyebabkan penolakan pesanan dan penalti performa toko.
* **Solusi:**  
  1. Penerapan **Stock Safety Buffer** (misal: buffer 2 unit). Jika stok fisik toko $\le 2$, stok yang dikirim ke Shopee/TikTok otomatis bernilai `0`.
  2. Saat kasir POS mencetak struk, sistem secara atomik memotong stok inventori dan menembakkan *Queue Job* `SyncMarketplaceStockJob` di latar belakang.

---

## 4. Rekomendasi Perubahan Teknis & File Terdampak

### 4.1 File yang Terdampak & Perlu Dioptimalkan:
1. [`resources/views/app/products/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/products/index.blade.php):
   - Tambahkan Bento Card *"Status & Kategori Marketplace"* pada Modal Create & Edit Produk.
   - Tambahkan indikator visual pewarisan kategori (`category.marketplace_category_name`).
   - Terapkan prinsip *Progressive Disclosure* (Auto-Hide opsi marketplace jika produk hanya untuk POS).
2. [`resources/views/app/master-data/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/master-data/index.blade.php):
   - Perjelas badge taksonomi marketplace pada tabel daftar kategori produk.
   - Tambahkan tombol quick-map kategori marketplace pada baris kategori yang belum terpetakan.
3. [`app/Http/Controllers/Web/ProductWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/ProductWebController.php):
   - Sertakan data `marketplaceMappings` dan `category.marketplace_category_name` pada payload koleksi produk agar form edit produk dapat menampilkan status pemetaan secara instan.
4. [`app/Domain/Marketplace/MarketplaceSyncService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Marketplace/MarketplaceSyncService.php):
   - Pastikan guardrail validasi berat produk, foto produk, dan kepatuhan kategori terverifikasi sebelum request diteruskan ke adapter Shopee/TikTok/Tokopedia.

---

## 5. Rencana Implementasi Bertahap (Step-by-Step Implementation Plan)

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. TAHAP 1: PENYEMPURNAAN PAYLOAD CONTROLLER & DATA BINDING                 │
│    - Eager load relasi kategori & marketplace mappings pada ProductWebCtrl  │
│    - Kirim taksonomi MarketplaceCategoryRegistry ke view produk             │
├─────────────────────────────────────────────────────────────────────────────┤
│ 2. TAHAP 2: PENYEMPURNAAN MODAL CREATE/EDIT PRODUK (BENTO APPLE HIG)        │
│    - Implementasi saklar "Aktifkan Marketplace" dengan Auto-Hide Field      │
│    - Tampilkan status pewarisan kategori marketplace secara reaktif          │
│    - Sediakan Smart Suggestion Pill jika kategori belum terpetakan          │
├─────────────────────────────────────────────────────────────────────────────┤
│ 3. TAHAP 3: ENHANCEMENT MASTER DATA KATEGORI PRODUK                         │
│    - Tambahkan badge & status mapping di tabel Master Data Kategori         │
│    - Sediakan filter kategori: "Semua", "Sudah Dipetakan", "Belum Dipetakan"│
├─────────────────────────────────────────────────────────────────────────────┤
│ 4. TAHAP 4: TESTING & VALIDASI NYATA 100%                                   │
│    - Jalankan unit & feature tests: php artisan test                        │
│    - Verifikasi skenario: POS-only product, Marketplace product, BPOM lock  │
├─────────────────────────────────────────────────────────────────────────────┤
│ 5. TAHAP 5: DOKUMENTASI & HISTORY LOG                                       │
│    - Update docs/AiWorkHistory.md & docs/SYSTEM_GUIDE.md                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 6. Confirmation Gate (Persetujuan Pengguna)

Dokumen ini disusun untuk memberikan transparansi penuh terhadap arsitektur katalog produk COOCA.  
Silakan tinjau laporan dan rencana di atas. Setelah Anda menyetujui rencana ini, perbaikan teknis pada berkas Blade dan Controller akan dieksekusi secara terarah dan teruji.
