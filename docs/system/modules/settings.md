# Modul Pengaturan Sistem & Integrasi Terpusat (Platform Settings & External Integrations)

> **Status:** VERIFIED  
> **Domain Terkait:** `app/Domain/Payment/`, `app/Domain/Shipping/`, `app/Domain/SocialMedia/`, `app/Domain/Mail/`, `app/Domain/WhatsApp/`, `app/Models/SystemSetting.php`  
> **Tabel Basis Data:** `system_settings`  
> **Rute Controller:** `AdminSettingController` (`/admin/settings`)

---

## 1. Tujuan & Nilai Bisnis

Modul Pengaturan Sistem & Integrasi Terpusat (`/admin/settings`) adalah kokpit administrasi Superadmin untuk mengendalikan seluruh parameter global platform Cooca dan kredensial konektivitas pihak ketiga.

Mengusung **Model B (Platform Centralized Architecture)**, seluruh integrasi pihak ketiga dikelola di level platform Cooca. Hal ini memberikan keuntungan strategis:
1. **Zero Friction Onboarding:** Tenant UMKM tidak perlu mendaftar akun developer atau mengurus verifikasi KYC rumit ke TriPay, Meta WhatsApp, Meta Developer, TikTok Open API, atau Biteship Logistics.
2. **Kemandirian Operasional:** Seluruh kredensial dapat dikonfigurasi langsung dari antarmuka Web Admin (`/admin/settings`) dan tersimpan secara dinamis pada tabel basis data `system_settings`. Tidak memerlukan pengeditan berkas `.env` ataupun restart server.
3. **Pengujian Real-Time:** Tersedia tombol uji koneksi instan (*Live Connectivity Tester*) dengan umpan balik visual untuk seluruh kanal integrasi.

---

## 2. Arsitektur 6 Tab Pengaturan Terpadu (Unified Hub)

Antarmuka Pengaturan dirancang dengan panduan desain **Bento Apple HIG v2.0** yang terdiri atas 6 segmented tab modular:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        PUSAT PENGATURAN PLATFORM & INTEGRASI                          │
├─────────────┬─────────────┬─────────────┬──────────────┬──────────────┬────────────────┤
│ 1. OAuth &  │ 2. Payment  │ 3. WhatsApp │ 4. Media     │ 5. Logistik  │ 6. Server SMTP │
│    Sistem   │   (TriPay)  │   Cloud API │   Sosial     │   (Biteship) │    Email       │
└─────────────┴─────────────┴─────────────┴──────────────┴──────────────┴────────────────┘
```

### 2.1 Tab 1: Google Cloud OAuth & Parameter Sistem (Canonical Production URL)
* **Fungsi:** Mengelola identitas platform, canonical production base URL, serta kredensial Client ID & Client Secret Google OAuth.
* **Canonical Base URL (`app_url`):** Diatur ke domain utama produksi `https://cooca.id`. Seluruh callback URL dan webhook eksternal diturunkan secara kanonikal dari basis data ini. Sistem secara otomatis menolak dan menormalkan rujukan lokal (`127.0.0.1:9082`, `localhost`) ataupun subdomain warisan (`umkm.cooca.id`) menjadi `https://cooca.id`.
* **Canonical URL & `/public` Immunity:** Menerapkan sistem pertahanan 4-lapis (root `.htaccess`, `public/.htaccess`, middleware `EnsureCleanUrl`, dan `URL::forceRootUrl`) yang secara mutlak mengeliminasi kontaminasi prefix `/public` pada URL dan me-redirect (301) setiap permintaan browser berawalan `/public/` kembali ke URL kanonikal bersih `https://cooca.id/...`.
* **Dual Callback Architecture:**
  - *Owner / Kasir Callback:* `https://cooca.id/auth/google/callback` (guard: `web`)
  - *Customer Storefront Callback:* `https://cooca.id/customer/auth/google/callback` (guard: `customer`)

### 2.2 Tab 2: Payment Gateway TriPay (Model B Terpusat)
* **Fungsi:** Gerbang pembayaran digital terpusat untuk QRIS Dinamis meja kasir, checkout pesanan online, dan tagihan langganan SaaS.
* **Parameter Dikelola:**
  - `tripay_merchant_code` (Contoh: `T38171`)
  - `tripay_api_key`
  - `tripay_private_key` (Signature HMAC-SHA256)
  - `tripay_is_production` (Sandbox vs Production toggle)
  - `tripay_sandbox_url` & `tripay_prod_url`
* **Webhook Callback:** `/api/v1/payment/tripay/callback`
* **Live Connectivity Tester:** Rute `POST /admin/settings/test-tripay` memeriksa validitas API Key dengan meminta kanal pembayaran aktif.

### 2.3 Tab 3: Meta WhatsApp Cloud API (Tech Provider Resmi)
* **Fungsi:** Pengiriman pesan transaksional (kode OTP registrasi/login, nota digital pesanan kasir, dan pengingat tagihan langganan tenant).
* **Parameter Dikelola:**
  - `meta_wa_app_id`
  - `meta_wa_phone_number_id`
  - `meta_wa_waba_id`
  - `meta_wa_token` (Permanent System User Token)
  - `meta_wa_webhook_verify_token`
  - `meta_wa_graph_version` (Default: `v25.0`)
  - `meta_wa_graph_url` (`https://graph.facebook.com`)
* **Webhook Callback:** `/api/v1/wa/meta/webhook`
* **Live Connectivity Tester:** Rute `POST /admin/settings/test-whatsapp` memvalidasi status nomor, tier limit, dan rating kualitas nomor.

### 2.4 Tab 4: Media Sosial Terpadu (Meta Facebook/Instagram & TikTok)
* **Fungsi:** Otomasi posting konten promosi, penjadwalan konten omnichannel, dan integrasi social commerce.
* **Platform Meta (Facebook Pages & Instagram API):**
  - `social_media_app_id`, `social_media_app_secret`, `social_media_webhook_verify_token`
  - `social_media_graph_version` (`v21.0`), `social_media_graph_url` (`https://graph.facebook.com`)
  - Kredensial khusus Instagram: `instagram_app_id`, `instagram_app_secret`, `instagram_account_id`, `instagram_access_token`
* **Platform TikTok Developer:**
  - `tiktok_client_key`, `tiktok_client_secret`
  - `tiktok_api_url` (`https://open.tiktokapis.com/v2/`)
  - `tiktok_auth_url` (`https://www.tiktok.com/v2/auth/authorize/`)
* **Live Connectivity Tester:** Rute `POST /admin/settings/test-social` dan `POST /admin/settings/test-instagram`.

### 2.5 Tab 5: Logistik & Ekspedisi Agregator (Biteship Multi-Courier API)
* **Fungsi:** Agregator kurir pengiriman terintegrasi (JNE, J&T, SiCepat, Anteraja, GoSend, Grab, POS, Ninja, Lion Parcel) untuk penjemputan paket, cetak waybill thermal, dan webhook pelacakan resi real-time.
* **Parameter Dikelola:**
  - `biteship_api_key` (JWT Token Otentikasi `biteship_live.*` atau `biteship_test.*`)
  - `biteship_base_url` (`https://api.biteship.com`)
  - `biteship_environment` (`production` atau `sandbox`)
  - `biteship_service_fee` (Platform handling fee per pesanan)
* **Webhook Tracking URL:** `/api/v1/shipping/biteship/webhook`
* **Live Connectivity Tester:** Rute `POST /admin/settings/test-biteship` memvalidasi API Key dengan menguji endpoint kurir aktif `/v1/couriers`.

### 2.6 Tab 6: Server SMTP Email
* **Fungsi:** Pengiriman email notifikasi verifikasi akun, kuitansi billing, dan reset kata sandi.
* **Parameter Dikelola:** Mailer (`smtp`, `sendmail`, `log`), Host, Port, Enkripsi (`tls`, `ssl`), Username, Password, From Address, From Name.
* **Preset Cepat 1-Klik:** Gmail SMTP (587 TLS), Mailtrap Sandbox, cPanel Webmail (465 SSL), Driver Log.

---

## 3. Resolusi Runtime Dinamis (`BiteshipService` & Client Domain)

Setiap layanan eksternal mengutamakan resolusi parameter dari tabel `system_settings`:

```php
// Contoh resolusi parameter di BiteshipService
$dbBaseUrl = SystemSetting::get('biteship_base_url');
$this->baseUrl = rtrim($baseUrl ?? ($dbBaseUrl ?: (string) config('services.biteship.base_url', 'https://api.biteship.com')), '/');
$this->apiKey = $apiKey ?? (string) (SystemSetting::get('biteship_api_key') ?: config('services.biteship.api_key', ''));
```

Dengan pola ini, jika entri di database ada, konfigurasi runtime langsung memakai data database tanpa perlu menyentuh environment server `.env`.

---

## 4. Keamanan & Standar UI (Apple HIG)
1. **Sensitivitas Data:** Kunci rahasia (private key, access token) dapat dilihat/disembunyikan dengan toggle mata bertenaga Alpine.js dan dienkripsi saat persistensi database.
2. **Zero Unicode Emoji:** Menggunakan 100% Lucide SVG Icons (`credit-card`, `truck`, `message-square`, `share-2`, `server`, `zap`, `webhook`, `copy`, `check`).
3. **Anti Auto-Zoom iOS:** Seluruh input field diformat `text-[16px] sm:text-[13px]`.
4. **Touch Ergonomics:** Tombol aksi memenuhi standar touch target Apple minimum 44px dengan feedback sentuhan haptic `active:scale-[0.98]`.
