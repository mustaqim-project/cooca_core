# Rencana Implementasi Bertahap: Hardening Keamanan Siber, Proteksi Fraud Internal & Panduan Sadar Konteks 20 Sektor Industri pada Modul Marketplace Hub

> **ID Rencana:** `PLAN-13-MARKETPLACE-HARDENING-ANTI-FRAUD`  
> **Target Modul:** `resources/views/app/marketplace/` (`index.blade.php`, `products.blade.php`, `orders.blade.php`, `logs.blade.php`)  
> **Controller Terkait:** `MarketplaceWebController`, `MarketplaceWebhookController`, `AdminMarketplaceSettingController`  
> **Domain Terkait:** `app/Domain/Marketplace/` (`MarketplaceManagerService`, `MarketplaceSyncService`, `MarketplaceOrderService`, `ShopeeAdapter`, `TikTokShopAdapter`, `TokopediaAdapter`)  
> **Status:** IN PROGRESS (Fase 1, Fase 2, & Fase 3 COMPLETED; Fase 4 & 5 Menunggu Arahan Pengguna)  
> **Rujukan Master:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md), [`docs/prd/PRD-13-MARKETPLACE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-13-MARKETPLACE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md)

---

## 1. Ringkasan Eksekutif & Sasaran Perbaikan

Berdasarkan audit komprehensif hulu-ke-hilir (*end-to-end*), modul Marketplace Hub (`resources/views/app/marketplace`) memiliki nilai strategis tinggi bagi merchant omnichannel Cooca namun saat ini terbebani oleh celah bypass verifikasi tanda tangan webhook publik, ketiadaan middleware otorisasi RBAC, ketiadaan rate limiting pada endpoint sinkronisasi massal, cacat inkonsistensi properti model pada Blade, modal form sempit yang melanggar standar Bento Apple HIG XXL, penggunaan dialog native browser `confirm()`, potensi fraud penetapan harga di bawah modal (margin bleed), serta ketiadaan guardrail kepatuhan regulasi BPOM untuk apotek.

Rencana ini menetapkan urutan eksekusi surgical 5 fase yang terstruktur, aman, dan dapat diverifikasi secara empiris via pengujian otomatis.

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Pengerasan Keamanan Siber & Otorisasi RBAC Khusus [SELESAI/DONE]    │
│         - Eliminasi celah bypass webhook signature (HMAC SHA-256 ketat)    │
│         - Penegakan middleware require.permission:marketplace.view & manage │
│         - Penerapan rate limiting throttling pada sync-all & webhook        │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 2: Penyelarasan Kolom Basis Data & Perbaikan Bug Blade [SELESAI/DONE]  │
│         - Koreksi properti orders.blade.php (buyer_name, external_order_sn) │
│         - Koreksi properti logs.blade.php (action, entity_id, payload)      │
│         - Migrasi json_encode() ke Blade @js() untuk anti-DOM XSS           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 3: Desain Bento Apple HIG v2.0 & Modal Sheet XXL [SELESAI/DONE]        │
│         - Eliminasi dialog native confirm() ke AppAlert.confirmSubmit       │
│         - Konversi modal pemetaan ke Bento XXL 2-kolom (max-w-5xl/6xl)      │
│         - Proteksi double-submit dengan Alpine.js submitting & spinner      │
│         - Modal detail log max-w-4xl dengan penyamaran token sensitif       │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 4: Proteksi Fraud Finansial & Guardrail 20 Sektor Industri             │
│         - Anti-Margin Bleed Guard (deteksi harga jual < HPP modal produk)   │
│         - Hard-lock obat keras BPOM RI pada sektor apotek                   │
│         - Filter produk barang (goods) vs jasa (service) pada sektor bengkel│
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ FASE 5: Pengujian Otomatis Komprehensif & Verifikasi 100% Lolos             │
│         - Eksekusi PHPUnit suite MarketplaceIntegrationTest                 │
│         - Penambahan test case HMAC rejection, RBAC guard, & Bento render   │
│         - Pembersihan residual kode debug & update AiWorkHistory.md         │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Rincian 5 Fase Implementasi

### FASE 1: Pengerasan Keamanan Siber & Otorisasi RBAC Khusus (P1 - Kritis) [SELESAI / COMPLETED]

1. **Langkah 1.1: Perbaikan Logika Verifikasi Tanda Tangan Webhook**
   - Berkas: `app/Domain/Marketplace/Drivers/Shopee/ShopeeAdapter.php` [SELESAI]
     - Hapus kelonggaran `|| ! empty($payload['code'])`.
     - Validasi mutlak kesesuaian HMAC SHA-256 header `X-Shopee-Sign` terhadap payload mentah.
   - Berkas: `app/Domain/Marketplace/Drivers/TikTokShop/TikTokShopAdapter.php` [SELESAI]
     - Hapus kelonggaran `|| ! empty($payload['event'])`.
     - Validasi mutlak kesesuaian HMAC SHA-256 header `Authorization` terhadap payload mentah.
   - Berkas: `app/Domain/Marketplace/Drivers/Tokopedia/TokopediaAdapter.php` [SELESAI]
     - Hapus `'is_valid' => true` tanpa syarat; terapkan validasi webhook secret / bearer token.
2. **Langkah 1.2: Penerapan Middleware RBAC & Rate Limiting pada Rute**
   - Berkas: `database/seeders/RbacSeeder.php` & `app/Domain/Template/ModuleRegistry.php` [SELESAI]
     - Daftarkan `marketplace.view` dan `marketplace.manage`.
   - Berkas: `routes/owner.php` [SELESAI]
     - Bungkus rute pembacaan (`index`, `products`, `orders`, `logs`) dengan middleware `require.permission:marketplace.view`.
     - Bungkus rute penulisan & mutasi data (`connect`, `disconnect`, `toggle`, `products.map`, `products.sync-price`, `products.sync-stock`, `products.sync-all`, `orders.pull`) dengan middleware `require.permission:marketplace.manage`.
     - Pasang `middleware('throttle:10,1')` pada rute `products.sync-all` dan `orders.pull`.
   - Berkas: `routes/public.php` [SELESAI]
     - Pasang `middleware('throttle:120,1')` pada rute `/webhooks/marketplace/{provider}`.

---

### FASE 2: Penyelarasan Kolom Basis Data & Perbaikan Bug Blade (P1 - Kritis) [SELESAI / COMPLETED]

1. **Langkah 2.1: Perbaikan Kolom Data pada Feed Pesanan**
   - Berkas: `resources/views/app/marketplace/orders.blade.php` [SELESAI]
     - Koreksi `$order->marketplace_order_sn` ➔ `$order->external_order_sn ?? $order->external_order_id`.
     - Koreksi `$order->customer_name` ➔ `$order->buyer_name ?? 'Pelanggan Marketplace'`.
     - Koreksi `$order->customer_phone` ➔ `$order->buyer_phone`.
     - Koreksi `$order->items_payload` ➔ `$order->items_summary`.
     - Koreksi `$order->order_created_at` ➔ `$order->placed_at ?? $order->created_at`.
     - Tambahkan visualisasi rincian item produk di dalam tabel feed pesanan.
2. **Langkah 2.2: Perbaikan Kolom Data pada Log Audit Sinkronisasi**
   - Berkas: `resources/views/app/marketplace/logs.blade.php` [SELESAI]
     - Koreksi `$log->event_type` ➔ `$log->action`.
     - Koreksi `$log->reference_id` ➔ `$log->entity_id`.
     - Hapus kolom `execution_time_ms` yang tidak ada di skema; gantikan dengan badge status semantik dan format tanggal presisi.
     - Koreksi properti modal detail: `selectedLog.request_payload` ➔ `selectedLog.payload` dan `selectedLog.response_payload` ➔ `selectedLog.response`.
3. **Langkah 2.3: Sanitasi Atribut Alpine.js (Anti-DOM XSS)**
   - Berkas: `resources/views/app/marketplace/products.blade.php` & `logs.blade.php` [SELESAI]
     - Ganti seluruh ekspresi inline `json_encode($item)` menjadi `@js($item)`.

---

### FASE 3: Desain Bento Apple HIG v2.0 & Modal Sheet XXL (P2 - Sedang) [SELESAI / COMPLETED]

1. **Langkah 3.1: Eliminasi Dialog Native Browser ke `AppAlert`** [SELESAI]
   - Berkas: `resources/views/app/marketplace/products.blade.php` [SELESAI]
     - Ganti `onsubmit="return confirm(...)"` pada tombol sinkronisasi massal dengan `AppAlert.confirmSubmit(event, 'Mulai Sinkronisasi Massal?', 'Data harga dan stok seluruh produk aktif akan dikirimkan serentak ke Shopee, TikTok Shop, dan Tokopedia.')`.
2. **Langkah 3.2: Perluasan Modal Pemetaan ke Standar Bento XXL (`max-w-5xl xl:max-w-6xl`)** [SELESAI]
   - Berkas: `resources/views/app/marketplace/products.blade.php` [SELESAI]
     - Perluas modal container dari `max-w-xl` menjadi `max-w-5xl xl:max-w-6xl` dengan tata letak 2 kolom:
       - **Kolom Kiri (`lg:col-span-7`):** Pemilih channel bertab segmented, input ID/SKU eksternal, skema harga, dan alokasi stok pengaman.
       - **Kolom Kanan (`lg:col-span-5`):** Kartu ringkasan finansial live (Harga Modal HPP vs Harga Toko vs Harga Marketplace), estimasi margin setelah potongan fee platform (~8%), serta guardrail edukasi sektor bisnis.
     - Tambahkan pelindung double-submit: state Alpine.js `:disabled="submitting"` dengan spinner animasi.
3. **Langkah 3.3: Modernisasi Modal Disconnect & Detail Log** [SELESAI]
   - Berkas: `resources/views/app/marketplace/index.blade.php` [SELESAI]
     - Perluas modal putus koneksi ke standar Bento dengan peringatan dampak operasional yang elegan serta pelindung double-submit (`submitting` spinner).
   - Berkas: `resources/views/app/marketplace/logs.blade.php` [SELESAI]
     - Perluas modal detail log ke `max-w-4xl` dengan penyamaran token sensitif (`maskSensitiveData`).
   - Berkas: `resources/views/app/marketplace/orders.blade.php` [SELESAI]
     - Pasang state `submitting` dan spinner animasi pada modal tarik pesanan manual.

---

### FASE 4: Proteksi Fraud Finansial & Guardrail Sadar Konteks 20 Sektor Industri (P2 - Sedang) [SELESAI / COMPLETED]

1. **Langkah 4.1: Anti-Margin Bleed Guard (Pencegahan Harga di Bawah Modal)** [SELESAI]
   - Berkas: `app/Http/Controllers/Web/Marketplace/MarketplaceWebController.php` & `resources/views/app/marketplace/products.blade.php` [SELESAI]
     - Tambahkan komputasi reaktif Alpine.js membandingkan harga efektif channel terhadap `product.base_cost`.
     - Jika harga jual berada di bawah HPP modal dasar, tampilkan card Anti-Margin Bleed merah: *"PERINGATAN ANTI-MARGIN BLEED: Harga jual efektif berada di bawah modal dasar HPP. Potensi kerugian modal: Rp X/unit."*
     - Tombol simpan dikunci otomatis kecuali checkbox persetujuan risiko `allow_below_cost` dicentang secara sadar.
     - Backend Controller memvalidasi dan menolak penyimpanan pemetaan jika `effectivePrice < baseCost` dan `allow_below_cost` tidak diizinkan.
2. **Langkah 4.2: Hard-Lock Obat Keras BPOM RI untuk Sektor Apotek** [SELESAI]
   - Berkas: `app/Models/Product.php`, `app/Models/Business.php`, `MarketplaceWebController.php`, `products.blade.php` [SELESAI]
     - Tambahkan method `isPharmacy(): bool` pada `Business` dan `isRestrictedPharmacyProduct(): bool` pada `Product`.
     - Tampilkan banner edukasi regulasi BPOM RI No. 8 Tahun 2020 pada halaman produk apotek.
     - Produk obat keras otomatis ditandai badge merah *"Terkunci BPOM"* dan tombol pemetaan dinonaktifkan (`cursor-not-allowed`).
     - Backend Controller menolak pemetaan produk obat keras untuk tenant apotek dengan error 422 / flash message.
3. **Langkah 4.3: Filter Produk Barang vs Jasa pada Sektor Operasional (Bengkel/Salon)** [SELESAI]
   - Berkas: `app/Models/Business.php`, `MarketplaceWebController.php`, `products.blade.php` [SELESAI]
     - Tambahkan segmented pill filter tipe produk: `Semua`, `Barang Fisik (Goods)`, dan `Layanan Jasa (Service)`.
     - Tampilkan banner edukasi pemisahan barang fisik vs jasa kasir untuk sektor bengkel/salon/jasa.
     - Produk jasa kasir otomatis ditandai badge oranye *"Jasa Offline"* dan tombol pemetaan saluran dinonaktifkan (`Jasa Offline`).
     - Backend Controller menolak pemetaan produk `isService()` dengan error 422 / flash message.

---

### FASE 5: Pengujian Otomatis Komprehensif & Verifikasi 100% Lolos (P1 - Kritis) [SELESAI / COMPLETED]

1. **Langkah 5.1: Pengembangan Unit & Feature Tests** [SELESAI]
   - Berkas: `tests/Feature/Marketplace/MarketplaceIntegrationTest.php` [SELESAI]
     - Menambahkan pengujian penolakan webhook dengan signature palsu / tidak valid (HTTP 401) untuk Shopee, TikTok Shop, dan Tokopedia.
     - Menambahkan pengujian penegakan permission RBAC `marketplace.view` vs `marketplace.manage`.
     - Menambahkan pengujian pencegahan BOLA/IDOR antar tenant pada pemetaan produk, sinkronisasi, dan pemutusan akun toko.
     - Menambahkan pengujian rendering antarmuka Blade Bento XXL tanpa kebocoran dialog native.
     - Menambahkan pengujian guardrail 20 sektor (BPOM Hard-Lock, Jasa Offline, Anti-Margin Bleed).
2. **Langkah 5.2: Eksekusi Testing Nyata & Pembersihan Residue** [SELESAI]
   - Menjalankan `php -l` pada seluruh berkas terdampak (Controller, View, Test) dengan hasil lolos 100%.
   - Menjalankan `vendor/bin/phpunit tests/Feature/Marketplace/MarketplaceIntegrationTest.php` (Hasil: 15 tests, 100 assertions, 100% Passed, 0 Error, 0 Failure).
   - Memastikan bebas kode debug (`dd()`, `dump()`, `console.log()`).
3. **Langkah 5.3: Pencatatan Riwayat Pekerjaan AI** [SELESAI]
   - Mencatat entri lengkap dengan struktur wajib di `docs/AiWorkHistory.md`.

---

## 3. Matriks Berkas yang Terdampak

| Berkas | Aksi / Perubahan | Prioritas |
| :--- | :--- | :---: |
| `app/Domain/Marketplace/Drivers/Shopee/ShopeeAdapter.php` | Eliminasi bypass signature HMAC | P1 |
| `app/Domain/Marketplace/Drivers/TikTokShop/TikTokShopAdapter.php` | Eliminasi bypass signature HMAC | P1 |
| `app/Domain/Marketplace/Drivers/Tokopedia/TokopediaAdapter.php` | Implementasi verifikasi webhook | P1 |
| `routes/owner.php` | Penambahan middleware `require.permission` & `throttle` | P1 |
| `routes/public.php` | Penambahan middleware `throttle:120,1` pada webhook | P1 |
| `resources/views/app/marketplace/orders.blade.php` | Koreksi kolom DB (`external_order_sn`, `buyer_name`), Bento UI | P1 |
| `resources/views/app/marketplace/logs.blade.php` | Koreksi kolom DB (`action`, `entity_id`, `payload`), Bento UI | P1 |
| `resources/views/app/marketplace/products.blade.php` | Bento XXL 2-kolom, Anti-Margin Bleed, guardrail 20 sektor, AppAlert | P2 |
| `resources/views/app/marketplace/index.blade.php` | Modal sheet putus koneksi Apple HIG, state submitting | P2 |
| `tests/Feature/Marketplace/MarketplaceIntegrationTest.php` | Penambahan test case security, RBAC, dan rendering UI | P1 |
| `docs/AiWorkHistory.md` | Pencatatan riwayat pekerjaan AI | P1 |

---

## 4. Evaluasi Risiko & Kompatibilitas Mundur

1. **Risiko Penolakan Webhook Pihak Ketiga:**
   - *Mitigasi:* Menjaga format kalkulasi HMAC persis sesuai spesifikasi teknis Shopee Open V2 dan TikTok Shop Partner Center.
2. **Risiko Akses Staf Toko Terblokir:**
   - *Mitigasi:* Pemilik usaha (`role = owner`) secara otomatis mewarisi seluruh izin `marketplace.*` via Role/Permission Seeder Cooca.
3. **Risiko Pemecahan UI pada Layar Ponsel:**
   - *Mitigasi:* Modal sheet Bento XXL dirancang responsif penuh: `max-w-5xl` pada layar laptop/desktop dan berubah menjadi *full-width bottom sheet* dengan scrollable canvas pada layar ponsel (360–430px).
