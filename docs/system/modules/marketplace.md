# Modul Integrasi Marketplace Omnichannel (Marketplace Hub)

> **Status:** AUDITED & SPECIFIED (Telah Diaudit & Dispesifikasikan)  
> **Domain Terkait:** `app/Domain/Marketplace/`, `app/Domain/Inventory/`, `app/Domain/Accounting/`  
> **Tabel Basis Data:** `marketplace_accounts`, `marketplace_product_mappings`, `marketplace_orders`, `marketplace_sync_logs`  
> **Controller Terkait:** `MarketplaceWebController`, `MarketplaceWebhookController`, `AdminMarketplaceSettingController`

---

## 1. Tujuan & Nilai Bisnis

Modul **Marketplace Hub** mengintegrasikan toko online resmi merchant Cooca di tiga marketplace terdepan di Indonesia: **Shopee, TikTok Shop, dan Tokopedia**.

Nilai bisnis utama yang dihadirkan meliputi:
1. **Pencegahan Overselling & Sinkronisasi Stok Satu Pintu:** Saat produk terjual di kasir POS fisik maupun etalase Storefront Cooca, sistem secara otomatis memperbarui sisa stok yang tersedia di Shopee, TikTok Shop, dan Tokopedia, sehingga mencegah penalti penolakan pesanan akibat stok fisik habis.
2. **Fleksibilitas Multi-Harga Per Channel (*Dynamic Fee Absorption*):** Merchant dapat menetapkan margin harga berbeda di setiap saluran (misal: menaikkan harga 8% di Shopee untuk menyerap biaya admin platform) secara otomatis atau menetapkan harga jual manual per kanal.
3. **Sentra Transaksi Terpadu (*Unified Inbound Order Feed*):** Mengeliminasi kebutuhan membuka tiga aplikasi seller center terpisah. Seluruh pesanan yang masuk dikonsolidasikan dalam satu antarmuka Cooca.

---

## 2. Arsitektur Driver & Adapter Platform

Sistem menerapkan pola desain **Manager-Driver Architecture** dengan kontrak `MarketplaceAdapterInterface`:

```text
                     ┌───────────────────────────────┐
                     │   MarketplaceManagerService   │
                     └───────────────┬───────────────┘
                                     │
           ┌─────────────────────────┼─────────────────────────┐
           ▼                         ▼                         ▼
┌─────────────────────┐   ┌─────────────────────┐   ┌─────────────────────┐
│    ShopeeAdapter    │   │  TikTokShopAdapter  │   │   TokopediaAdapter  │
│  (Shopee Open V2)   │   │(TTS Partner Center) │   │ (Tokopedia Open API)│
└─────────────────────┘   └─────────────────────┘   └─────────────────────┘
```

Setiap driver mengimplementasikan metode standar:
- `getAuthUrl(Business $business, string $redirectUri, string $state): string`
- `handleAuthCallback(Business $business, array $params, string $redirectUri): array`
- `refreshToken(MarketplaceAccount $account): array`
- `pushPrice(MarketplaceAccount $account, MarketplaceProductMapping $mapping, float $price): bool`
- `pushStock(MarketplaceAccount $account, MarketplaceProductMapping $mapping, int $stock): bool`
- `pullOrders(MarketplaceAccount $account): array`
- `handleWebhook(Request $request, string $rawBody, array $headers): array`

---

## 3. Komponen Teknis & Skema Data

### 3.1 Model Basis Data
1. **`MarketplaceAccount`**: Menyimpan kredensial otorisasi multi-tenant toko. Access token dan refresh token terenkripsi menggunakan cast `encrypted`. Dilengkapi kolom `is_active` (boolean) untuk mengontrol saklar aktif/nonaktif penjualan dan sinkronisasi otomatis per saluran tanpa memutuskan otorisasi OAuth.
2. **`MarketplaceProductMapping`**: Pemetaan 1-ke-Banyak dari `Product` Cooca ke SKU marketplace eksternal dengan konfigurasi `price_multiplier`, `stock_buffer`, dan `custom_stock`.
3. **`MarketplaceOrder`**: Pencatatan riwayat transaksi pesanan eksternal dengan status siklus hidup (`UNPAID`, `PAID`, `SHIPPED`, `COMPLETED`, `CANCELLED`).
4. **`MarketplaceSyncLog`**: Catatan audit trail immutable untuk setiap aktivitas API keluar (*outbound*) maupun webhook masuk (*inbound*).

### 3.2 Lapisan Layanan (Service Layer)
- **`MarketplaceManagerService`**: Resolusi driver adapter, pembuatan & validasi token state OAuth terenkripsi (`Crypt::encryptString`).
- **`MarketplaceSyncService`**: Logika bisnis penghitungan stok efektif dengan safety buffer (`resolveEffectiveStock`), serta orkestrasi pembaruan harga.
- **`MarketplaceOrderService`**: Eksekusi upserting pesanan dan penarikan order berkala.

### 3.3 Antrean Asinkron (Queue Jobs)
- **`ProcessMarketplaceWebhookJob`**: Pemrosesan muatan webhook di latar belakang untuk menjaga respon HTTP sub-100ms.
- **`SyncMarketplacePriceJob`**: Antrean push perubahan harga secara asinkron.
- **`SyncMarketplaceStockJob`**: Antrean push perubahan stok fisik secara asinkron.

---

## 4. Hak Akses & Matriks Peran (RBAC)

| Peran Pengguna | Akses Cockpit & Feed | Ubah Multi-Harga & Stok | Sambung / Putus Akun | Eksekusi Sync Massal |
| :--- | :---: | :---: | :---: | :---: |
| **Super Admin Platform** | Full System View | Non-Tenant Only | Configuration Only | Admin Testing |
| **Business Owner** | Ya | Ya | Ya | Ya |
| **Store Manager** | Ya | Ya | Tidak (Perlu Approval) | Ya |
| **Kasir / POS Staff** | Hanya Lihat Order | Tidak | Tidak | Tidak |
| **Gudang & Logistik** | Lihat Order & Resi | Tidak | Tidak | Tidak |

---

## 5. Ringkasan Endpoint Rute

| Metode | URI | Nama Rute | Middleware | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/marketplace-hub` | `marketplace-hub.index` | `auth, business.active, require.permission:marketplace.view` | Cockpit ringkasan status toko & metrik |
| `POST` | `/marketplace-hub/connect/{provider}` | `marketplace-hub.connect` | `auth, business.active, require.permission:marketplace.manage` | Inisiasi OAuth redirect ke Shopee/TikTok |
| `POST` | `/marketplace-hub/disconnect/{provider}` | `marketplace-hub.disconnect` | `auth, business.active, require.permission:marketplace.manage` | Pemutusan hubungan akun toko |
| `POST` | `/marketplace-hub/toggle/{provider}` | `marketplace-hub.toggle` | `auth, business.active, require.permission:marketplace.manage` | Mengaktifkan/menonaktifkan sync aktif |
| `GET` | `/marketplace-hub/products` | `marketplace-hub.products` | `auth, business.active, require.permission:marketplace.view` | Tabel komparasi multi-harga & pemetaan |
| `POST` | `/marketplace-hub/products/map` | `marketplace-hub.products.map` | `auth, business.active, require.permission:marketplace.manage` | Simpan pengaturan harga & alokasi stok SKU |
| `POST` | `/marketplace-hub/products/sync-all` | `marketplace-hub.products.sync-all` | `auth, require.permission:marketplace.manage, throttle:10,1` | Sinkronisasi massal seluruh produk aktif |
| `GET` | `/marketplace-hub/orders` | `marketplace-hub.orders` | `auth, business.active, require.permission:marketplace.view` | Feed daftar pesanan transaksi marketplace |
| `POST` | `/marketplace-hub/orders/pull` | `marketplace-hub.orders.pull` | `auth, require.permission:marketplace.manage, throttle:10,1` | Penarikan pesanan terbaru dari API |
| `GET` | `/marketplace-hub/logs` | `marketplace-hub.logs` | `auth, business.active, require.permission:marketplace.view` | Riwayat log & audit sinkronisasi |
| `GET` | `/integrations/{provider}/callback` | `integrations.marketplace.callback` | `web` | OAuth callback penerima auth code |
| `POST` | `/webhooks/marketplace/{provider}` | `webhooks.marketplace` | `api, throttle:120,1` | Inbound webhook penerima pesanan/status |

---

## 6. Standar Antarmuka Bento Apple HIG & Modal Sheet XXL

Sesuai direktif Bento Apple HIG v2.0 (`docs/agent.md` & `references/design-system.md`), modul Marketplace Hub menerapkan:
1. **Modal Sheet XXL 2-Kolom (`max-w-5xl xl:max-w-6xl`) & Arsitektur Alpine.js Terisolasi:**
   - Komponen logika dikelola secara terisolasi via `Alpine.data('marketplaceProductManager', ...)` di skrip khusus `@push('scripts')`, dengan injeksi data server aman menggunakan `Js::from` dan pemetaan `$productsMap` berbasis ID produk untuk mencegah tabrakan quote escaping pada atribut HTML.
   - Kolom Kiri: Form pemetaan interaktif dengan tab segmented (Shopee, TikTok Shop, Tokopedia), input squircle Apple, kartu bento pricing auto-multiplier vs manual, serta alokasi stok gudang vs buffer pengaman.
   - Kolom Kanan: Kalkulator finansial reaktif live Alpine.js (Modal Dasar HPP, Harga Toko COOCA, Harga Saluran, Biaya Fee Platform ~8%, Estimasi Payout & Margin Bersih %) yang otomatis memberikan alert visual jika margin bernilai negatif.
2. **Eliminasi Dialog Native Browser (`Zero Native Popups`):**
   - Menggunakan komponen dialog `AppAlert.confirmSubmit` untuk aksi konfirmasi sinkronisasi massal seluruh harga & stok ke saluran marketplace.
3. **Proteksi Double-Submit Form:**
   - Seluruh modal mutasi data (`products.map`, `disconnect`, `orders.pull`) dilengkapi state Alpine.js `submitting`, tombol aksi `:disabled="submitting"`, dan animasi spinner SVG.
4. **Penyamaran Kredensial Sensitif (Zero Plaintext Exposure):**
   - Modal audit log (`max-w-4xl`) menerapkan helper sanitasi `maskSensitiveData()` untuk menyamarkan access token, refresh token, password, dan secret signature sebelum dirender ke DOM.

