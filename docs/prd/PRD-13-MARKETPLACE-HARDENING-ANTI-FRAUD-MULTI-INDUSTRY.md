# PRD-13: Hardening Keamanan Cyber, Proteksi Fraud Internal & Panduan Sadar Konteks 20 Sektor Industri pada Modul Integrasi Marketplace Omnichannel

> **ID Dokumen:** `PRD-13-MARKETPLACE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY`  
> **Status Dokumen:** IMPLEMENTED & VERIFIED (Seluruh 5 Fase Selesai & Lolos 100%)  
> **Domain Terkait:** `app/Domain/Marketplace/`, `app/Domain/Inventory/`, `app/Domain/Accounting/`  
> **Target Pengguna:** Business Owner (Pemilik Usaha), Store Manager, Admin Marketplace, Staf Gudang & Logistik  
> **Standar Arsitektur:** `docs/agent.md`, `docs/SYSTEM_GUIDE.md`, Bento Apple HIG v2.0, Zero .env, Zero Plaintext Credential Exposure

---

## 1. Latar Belakang & Pernyataan Masalah (Problem Statement)

Modul **Integrasi Marketplace (`resources/views/app/marketplace`)** merupakan komponen strategis omnichannel Cooca untuk menghubungkan etalase toko UMKM dengan ekosistem e-commerce nasional: **Shopee, TikTok Shop, dan Tokopedia**.

Berdasarkan audit komprehensif hulu-ke-hilir (*end-to-end*) terhadap 4 berkas tampilan Blade (`index.blade.php`, `products.blade.php`, `orders.blade.php`, `logs.blade.php`), controller, domain adapter, dan skema migrasi basis data, ditemukan **8 kesenjangan kritis (*critical gaps*)** yang mencakup aspek keamanan siber, skema fraud internal, cacat inkonsistensi properti model, serta ketidakpatuhan terhadap standar desain Bento Apple HIG v2.0:

1. **Bypass Kritis Tanda Tangan Webhook Publik (Zero-Authentication Spoofing Vulnerability):**
   - Pada `ShopeeAdapter.php` dan `TikTokShopAdapter.php`, logika verifikasi tanda tangan HMAC dilonggarkan dengan klausul OR: `|| ! empty($payload['code'])` dan `|| ! empty($payload['event'])`.
   - Pada `TokopediaAdapter.php`, metode `handleWebhook` langsung mengembalikan `'is_valid' => true` tanpa melakukan kalkulasi HMAC sama sekali.
   - *Dampak:* Siapa pun dari internet publik dapat menembakkan HTTP POST ke `/webhooks/marketplace/{provider}` dengan payload JSON tiruan untuk menyuntikkan pesanan fiktif atau memanipulasi status pesanan menjadi "PAID".
2. **Ketiadaan Pembatasan Hak Akses Berbasis Peran (Missing RBAC Middleware):**
   - Grup rute `marketplace-hub.*` di `routes/owner.php` hanya dibungkus oleh middleware autentikasi dasar (`auth:web`, `business.active`, `verified`) tanpa middleware `require.permission`.
   - *Dampak:* Seluruh staf toko (termasuk kasir magang atau pramusaji) dapat mengakses modul, mengubah harga jual saluran, memutuskan akun toko resmi, atau menjalankan sinkronisasi massal.
3. **Ketiadaan Pembatas Laju Panggilan (Unthrottled Sync Endpoints & API Exhaustion Risk):**
   - Endpoint sensitif berbobot tinggi seperti `POST /marketplace-hub/products/sync-all` dan `POST /marketplace-hub/orders/pull` belum memiliki pembatas laju (`throttle`).
   - *Dampak:* Panggilan berulang-ulang dapat membebani antrean background worker, menghabiskan kuota API platform (Shopee/TikTok/Tokopedia), dan memicu penalti pemblokiran IP toko (*API Rate Limit Ban*).
4. **Kerentanan Fraud Predatory Underpricing (Manipulasi Harga Bawah Modal oleh Rogue Staff):**
   - Staf toko yang nakal dapat menyetel faktor pengali harga `price_multiplier` ke angka `0.10` atau memasukkan `channel_price` Rp 1.000 untuk barang bernilai jutaan rupiah, lalu memborongnya secara pribadi di Shopee/TikTok.
   - *Dampak:* Kerugian finansial fatal bagi merchant tanpa peringatan atau intervensi sistem.
5. **Inkonsistensi Kritis Antara Properti Kolom Basis Data dan Tampilan Blade (Field Mismatch Bugs):**
   - Pada `orders.blade.php`, view memanggil atribut yang tidak ada di skema migrasi:
     - `$order->marketplace_order_sn` (seharusnya `external_order_sn`).
     - `$order->customer_name` & `$order->customer_phone` (seharusnya `buyer_name` & `buyer_phone`).
     - `$order->items_payload` (seharusnya `items_summary`).
     - `$order->order_created_at` (seharusnya `placed_at`).
   - Pada `logs.blade.php`, view memanggil atribut yang tidak ada di skema migrasi:
     - `$log->event_type` (seharusnya `action`).
     - `$log->reference_id` (seharusnya `entity_id`).
     - `$log->execution_time_ms` (kolom fiktif tidak ada di tabel `marketplace_sync_logs`).
     - `selectedLog.request_payload` & `selectedLog.response_payload` (seharusnya `payload` & `response`).
   - *Dampak:* Kolom-kolom data pada tabel pesanan dan log audit menjadi kosong atau bernilai null saat dirender di antarmuka pengguna.
6. **Pelanggaran Mandat Anti-Hyperbole & Integritas Faktual (False Stock-Deduction Claim):**
   - Header pada `orders.blade.php` menyatakan: *"Pesanan akan otomatis memotong stok produk di COOCA."*
   - Pada implementasi kode aktual di `MarketplaceOrderService.php`, pesanan masuk hanya di-upsert ke tabel `marketplace_orders` tanpa pernah memotong inventori gudang atau memicu jurnal akuntansi.
   - *Dampak:* Terjadi selisih stok fisik vs catatan sistem (*stock discrepancy fraud*), memicu *overselling* di kasir offline POS.
7. **Pelanggaran Desain Sistem Bento Apple HIG v2.0 & Penggunaan Dialog Browser Native:**
   - Formulir sinkronisasi massal di `products.blade.php` masih menggunakan dialog native `onsubmit="return confirm(...)"` yang memblokir UI thread dan melanggar standar estetika Cooca.
   - Modal pemetaan produk di `products.blade.php` berukuran sempit `max-w-xl` (melanggar standar *Modal-First XXL* `max-w-5xl`/`xl:max-w-6xl`).
   - Seluruh formulir modal belum memiliki perlindungan double-submit (`:disabled="submitting"`).
8. **Ketiadaan Adaptasi Kontekstual & Guardrail Kepatuhan 20 Sektor Industri:**
   - Sektor Apotek/Farmasi belum memiliki filter pencegahan penjualan Obat Keras (Daftar G / Lingkaran Merah) ke marketplace terbuka, padahal hal ini melanggar regulasi BPOM RI dan syarat ketentuan Shopee/Tokopedia dengan ancaman pencabutan izin edar dan pidana.
   - Sektor jasa (Bengkel, Salon, Laundry, Kontraktor) masih menampilkan opsi pemetaan untuk produk yang berjenis `service` (jasa tenaga kerja), yang secara fisik tidak dapat dikirim via kurir ekspedisi.

---

## 2. Sasaran Produk & Metrik Keberhasilan (OKRs)

| Sasaran | Metrik Keberhasilan | Target |
| :--- | :--- | :---: |
| **Keamanan Siber** | Eliminasi celah bypass webhook signature (HMAC-SHA256 valid 100%) dan sanitasi DOM XSS via `@js()` | 100% Bebas Celah |
| **Isolasi RBAC** | Penegakan middleware izin `marketplace.view` dan `marketplace.manage` | Terisolasi Sempurna |
| **Perlindungan Throttling** | Penerapan rate limiting pada `sync-all`, `orders.pull`, dan webhook endpoint | Terlindungi dari Spam |
| **Proteksi Fraud Finansial** | Penegakan Anti-Margin Bleed Guard (deteksi harga di bawah HPP modal produk) | 0 Transaksi di Bawah Modal |
| **Integritas Data** | Penyelarasan 100% kolom migrasi basis data pada `orders.blade.php` dan `logs.blade.php` | 0 Nilai Null/Kosong |
| **Kepatuhan Apple HIG** | Konversi modal sheet ke XXL (`max-w-5xl`/`xl:max-w-6xl`), migrasi native `confirm()` ke `AppAlert` | 100% Bento HIG |
| **Kepatuhan 20 Sektor Industri** | Hard-lock obat keras BPOM untuk apotek & pemisahan produk goods vs service | Aktif 20 Sektor |
| **Kesiapan Pengujian** | Seluruh test suite `tests/Feature/Marketplace` lolos 100% | 100% Lolos (0 Failure) |

---

## 3. Spesifikasi Fungsional (Functional Requirements)

### FR-01: Verifikasi Kriptografis Tanda Tangan Webhook & Anti-Spoofing
- Menghapus seluruh kelonggaran bypass signature (`|| ! empty($payload['code'])` dan `|| ! empty($payload['event'])`).
- Mengimplementasikan kalkulasi HMAC SHA-256 murni sesuai dokumentasi resmi:
  - **Shopee:** Validasi header `X-Shopee-Sign` terhadap `hash_hmac('sha256', $request->fullUrl() . '|' . $rawBody, $partnerKey)`.
  - **TikTok Shop:** Validasi header `Authorization` terhadap `hash_hmac('sha256', $rawBody, $appSecret)`.
  - **Tokopedia:** Validasi secret key webhook atau validasi bearer token resmi Tokopedia Seller API.
- Webhook dengan tanda tangan tidak valid wajib ditolak seketika dengan HTTP 401 Unauthorized dan dicatat sebagai potensi insiden keamanan di log.

### FR-02: Otorisasi RBAC Khusus Marketplace
- Menambahkan izin baru ke dalam sistem permission Cooca:
  - `marketplace.view`: Mengakses cockpit ringkasan, melihat daftar produk terpetakan, melihat feed pesanan, dan membaca log audit.
  - `marketplace.manage`: Menghubungkan/memutuskan akun toko, mengaktifkan/menonaktifkan saluran, mengubah pemetaan harga & stok, memicu sinkronisasi massal, dan menarik pesanan.
- Menerapkan middleware pada seluruh rute di `routes/owner.php`.

### FR-03: Pembatasan Laju Akses (Rate Limiting / Throttling)
- Rute `marketplace-hub.products.sync-all` dibatasi `throttle:10,1` (maksimal 10 kali per menit per merchant).
- Rute `marketplace-hub.orders.pull` dibatasi `throttle:10,1`.
- Rute webhook publik `/webhooks/marketplace/{provider}` dibatasi `throttle:120,1`.

### FR-04: Anti-Margin Bleed Guard (Proteksi Harga di Bawah Modal)
- Pada modal pemetaan produk, sistem membaca HPP/Modal Dasar (`$product->base_cost`).
- Jika pengguna memasukkan `channel_price` atau kalkulasi pengali `price_multiplier` menghasilkan harga jual yang **lebih rendah dari HPP**, sistem menampilkan banner peringatan merah:
  *"Peringatan Risiko Finansial: Harga jual saluran (Rp X) berada di bawah modal dasar produk (Rp Y). Potensi kerugian Rp Z per transaksi."*
- Formulir mewajibkan centang konfirmasi eksplisit sebelum dapat disimpan: `[x] Saya memahami risiko penjualan di bawah modal ini`.

### FR-05: Penyelarasan Kolom Basis Data & Eliminasi Bug Properti Blade
- Pada `orders.blade.php`:
  - Ganti `$order->marketplace_order_sn` dengan `$order->external_order_sn`.
  - Ganti `$order->customer_name` dengan `$order->buyer_name`.
  - Ganti `$order->customer_phone` dengan `$order->buyer_phone`.
  - Ganti `$order->items_payload` dengan `$order->items_summary`.
  - Ganti `$order->order_created_at` dengan `$order->placed_at`.
- Pada `logs.blade.php`:
  - Ganti `$log->event_type` dengan `$log->action`.
  - Ganti `$log->reference_id` dengan `$log->entity_id`.
  - Ganti `selectedLog.request_payload` dengan `selectedLog.payload`.
  - Ganti `selectedLog.response_payload` dengan `selectedLog.response`.
  - Eliminasi referensi kolom fiktif `execution_time_ms`.
- Ganti seluruh pemanggilan `json_encode()` di atribut Blade menjadi `@js()`.

### FR-06: Modernisasi Bento Apple HIG v2.0 & Modal Sheet XXL
- Memperluas modal pemetaan produk `products.blade.php` menjadi **Bento Full Size XXL (`max-w-5xl xl:max-w-6xl`)** dengan arsitektur 2 kolom:
  - **Kolom Kiri (Input Controls):** Segmented channel selector (Shopee / TikTok Shop / Tokopedia), input external Item ID & SKU, skema penetapan harga (otomatis multiplier vs manual), dan skema alokasi stok buffer.
  - **Kolom Kanan (Live Comparison & Intelligence):** Kartu ringkasan finansial interaktif menampilkan:
    - Harga Modal (HPP): `Rp X`
    - Harga Toko Utama (COOCA): `Rp Y`
    - Harga Saluran Marketplace: `Rp Z`
    - Estimasi Margin Bersih setelah Estimasi Biaya Admin Marketplace (~8%): `Rp W`
- Memperluas modal log di `logs.blade.php` menjadi `max-w-4xl` dengan penyamaran token sensitif.
- Memperluas modal pemutusan koneksi akun di `index.blade.php` dan modal penarikan pesanan di `orders.blade.php` ke standar modal sheet Apple HIG.

### FR-07: Eliminasi Dialog Native Browser & Double-Submit Guard
- Menggantikan pemanggilan `onsubmit="return confirm(...)"` di `products.blade.php` dengan `AppAlert.confirmSubmit(event, 'Sinkronisasi Seluruh Produk?', 'Data harga dan kuota stok fisik akan dikirimkan serentak ke Shopee, TikTok Shop, dan Tokopedia.')`.
- Menambahkan state reaktif Alpine.js `submitting = false` pada seluruh modal formulir. Tombol aksi utama otomatis menampilkan spinner animasi dan dinonaktifkan (`:disabled="submitting"`) saat proses penyimpanan berjalan.

### FR-08: Penegakan Aturan Kontekstual 20 Sektor Industri
- **Apotek & Toko Obat (`retail_pharmacy`):**
  - Menginspeksi kategori produk atau nama produk yang tergolong Obat Keras / Daftar G / Resep Dokter.
  - Menampilkan banner edukasi regulasi BPOM RI dan menonaktifkan tombol pemetaan untuk produk obat keras guna mencegah sanksi pidana dan penalti pemblokiran toko.
- **Sektor Jasa Operasional (Bengkel, Salon, Laundry, Auto Detailing):**
  - Memfilter daftar produk agar hanya menampilkan produk bertipe `goods` (suku cadang, minyak pelumas, sampo, deterjen).
  - Menyembunyikan produk bertipe `service` (jasa servis, potong rambut, ongkos cuci) dari tabel pemetaan marketplace dengan badge informasi: *"Hanya produk barang fisik yang dapat dijual di marketplace."*
- **Sektor F&B (Resto & Cafe Dine-In):**
  - Menampilkan peringatan logistik sameday/instant dan menyembunyikan pemetaan untuk menu makanan panas cepat basi.
- **Sektor Manufaktur & Konveksi (Garment & Printing):**
  - Menyediakan input `preorder_lead_days` untuk sinkronisasi masa tunggu PO ke Shopee/TikTok.

---

## 4. Kriteria Penerimaan Berbasis Skenario (Gherkin Acceptance Tests)

```gherkin
Feature: Keamanan Siber Webhook & Pencegahan Pemalsuan Tanda Tangan
  Scenario: Menolak webhook masuk dengan tanda tangan HMAC yang salah
    Given penyerang mengirimkan permintaan HTTP POST ke "/webhooks/marketplace/shopee"
    And payload memuat key "code" bernilai 3
    And header "X-Shopee-Sign" memuat tanda tangan palsu atau kosong
    When sistem memverifikasi muatan webhook
    Then sistem menolak permintaan dengan status HTTP 401 Unauthorized
    And sistem tidak memproses pesanan fiktif tersebut ke dalam antrean job

Feature: Otorisasi RBAC Khusus Marketplace
  Scenario: Kasir tanpa izin marketplace.manage dilarang mengubah harga produk channel
    Given pengguna terotentikasi sebagai kasir dengan izin terbatas
    When pengguna mengirimkan permintaan POST ke "/marketplace-hub/products/map"
    Then sistem menolak permintaan dengan kode HTTP 403 Forbidden
    And konfigurasi pemetaan produk tidak mengalami perubahan

Feature: Anti-Margin Bleed Guard
  Scenario: Peringatan saat harga jual marketplace di bawah HPP modal produk
    Given produk memiliki HPP modal sebesar Rp 50.000
    When pengguna memasukkan harga jual channel sebesar Rp 40.000 pada modal pemetaan
    Then antarmuka menampilkan peringatan risiko finansial berwarna merah
    And tombol simpan dinonaktifkan hingga pengguna mencentang persetujuan risiko

Feature: Penyelarasan Kolom Basis Data
  Scenario: Feed pesanan marketplace menampilkan data pelanggan dan nomor resi secara akurat
    Given terdapat pesanan marketplace dengan nomor SN "240928SHP001" dan pembeli "Budi Santoso"
    When pemilik toko membuka halaman "/marketplace-hub/orders"
    Then antarmuka menampilkan nomor pesanan "240928SHP001"
    And antarmuka menampilkan nama pembeli "Budi Santoso"
    And tidak ada teks kosong atau properti null yang tertampil
```
