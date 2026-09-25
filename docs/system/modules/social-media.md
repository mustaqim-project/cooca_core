# Modul Media Sosial & Integrasi Platform Meta / TikTok / LinkedIn

> **Layer 2: System Knowledge Document**  
> **Ruang Lingkup:** Integrasi Meta Graph API (Instagram Professional, Facebook Pages, Threads), TikTok Content Posting API, dan LinkedIn Developer Platform (OAuth 2.0 OpenID Connect & UGC Post Publishing) untuk Superadmin Platform Cooca dan Tenant UMKM.

---

## 1. Arsitektur & Peran Modul

Modul Media Sosial melayani dua domain utama:
1. **Platform Center (Superadmin Cockpit)**: Publikasi konten resmi Cooca Indonesia (`@cooca.indonesia`, Facebook Page `Cooca Indonesia`, TikTok, dan LinkedIn), manajemen kotak masuk interaksi/komentar, konfigurasi kredensial platform dinamis, serta dasbor analitik & pertumbuhan organik.
2. **Merchant Hub (Tenant UMKM)**: Penautan akun bisnis merchant (Meta 1-Klik, TikTok 1-Klik, LinkedIn 1-Klik), Unified Omnichannel Composer, penjadwalan konten multi-saluran, auto-refresh token, dan moderasi komentar.

---

## 2. Platform & Provider Matrix

| Platform | Provider Key | Protokol Autentikasi | Scopes / Izin Resmi | Fitur yang Didukung |
| :--- | :--- | :--- | :--- | :--- |
| **Facebook Pages** | `meta` | OAuth 2.0 (Long-lived Page Token) | `pages_show_list`, `pages_read_engagement`, `pages_manage_posts` | Feed text, image, video, auto-purge storage |
| **Instagram Business** | `meta` | Meta Graph API (Instagram Login) | `instagram_basic`, `instagram_content_publish`, `instagram_manage_comments`, `instagram_manage_insights` | Feed photo, carousel (2-10 items), Reels (9:16), live analytics |
| **Threads** | `meta` | Threads API (`graph.threads.net`) | `threads_basic`, `threads_content_publish` | Text, image, video |
| **TikTok** | `tiktok` | Open API v2 Auth Code (PKCE/State) | `user.info.basic`, `video.publish`, `video.upload` | Direct video posting, photo carousel, auto token refresh |
| **LinkedIn** | `linkedin` | OAuth 2.0 OpenID Connect | `openid`, `profile`, `email`, `w_member_social` | UGC Post feed text, image attachment via Digital Media Asset API (`/v2/assets?action=registerUpload`), OpenID UserInfo (`/v2/userinfo`) |

---

## 3. Konfigurasi LinkedIn Developer Portal

### Kredensial Resmi
- **Client ID:** `868wurbnxke9xg`
- **Primary Client Secret:** `WPL_AP1.T6CrhB0PHBB6XA3T.ddwN2A==`
- **Penyimpanan:** `system_settings` (Terenkripsi AES-256 via Laravel APP_KEY) dengan fallback ke `config('services.linkedin')` & `.env`.

### Authorized Redirect URLs (LinkedIn & TikTok Developer Portals)
1. **Merchant Callback (Produksi):** `https://cooca.id/social-media/linkedin/callback`
2. **Merchant Callback (Pengembangan Lokal):** `http://127.0.0.1:9871/social-media/linkedin/callback`
3. **Super Admin Callback (Produksi):** `https://cooca.id/admin/social-media/linkedin/callback`
4. **Super Admin Callback (Pengembangan Lokal):** `http://127.0.0.1:9871/admin/social-media/linkedin/callback`

---

## 4. Platform Official Accounts vs Merchant Accounts

Tabel `social_media_accounts` mengelola akun dengan pembagian hak akses dan isolasi yang ketat:
- **Merchant Account (`is_platform = false`, `business_id = <UUID>`)**: Terikat pada satu tenant UMKM dan hanya dapat diakses/dikelola oleh merchant yang bersangkutan.
- **Platform Official Account (`is_platform = true`, `business_id = null`)**: Akun resmi platform Cooca Indonesia yang dihubungkan langsung oleh Super Admin melalui Admin Social Media Center (`/admin/social-media?tab=posts`). Akun ini digunakan untuk mempublikasikan pengumuman resmi platform Cooca ke LinkedIn, TikTok, Instagram, dan Facebook. Super Admin dapat menghubungkan akun via 1-Click Connect (`admin.social-media.linkedin.connect`, `admin.social-media.tiktok.connect`) dan memutuskan koneksi kapan saja.

---

## 5. Engine Penjadwalan Konten Multi-Saluran (Omnichannel)

1. **Alur Kerja Pembuatan Konten:**
   - Merchant memilih satu atau lebih target akun (`target_accounts[]`).
   - Format: `photo`, `carousel`, `video`, `reels`, atau `text`.
   - Waktu Publikasi:
     - `all_now`: Eksekusi serentak saat tombol submit ditekan.
     - `all_same`: Seluruh saluran terpilih dijadwalkan pada waktu global yang sama (`scheduled_at`).
     - `per_channel`: Setiap saluran memiliki waktu dan mode independen (`channel_schedule_modes`, `channel_scheduled_at`).
2. **Eksekusi Penjadwalan Otomatis:**
   - Entri dibuat pada tabel `social_post_targets` dengan `status = 'scheduled'` dan timestamp `scheduled_at`.
   - Scheduler Laravel mengeksekusi `social-media:publish-scheduled` setiap menit:
     ```php
     Schedule::command('social-media:publish-scheduled')->everyMinute()->withoutOverlapping();
     ```
   - Perintah ini mencari target berstatus `scheduled` dengan `scheduled_at <= now()`, mengubah status ke `processing`, dan mendispatch `PublishSocialMediaTargetJob::dispatch($target->id)`.
   - Job mengeksekusi API provider spesifik (`MetaProvider`, `TikTokProvider`, `LinkedInProvider`), menyimpan ID eksternal pada `platform_post_id`, mengisi `published_at`, dan menyinkronkan status pos induk via `post->syncStatusFromTargets()`.

---

## 5. Kebijakan Privasi & Nol Meta Ads

> [!IMPORTANT]
> **Kebijakan Nol Meta Ads:**  
> Sistem Cooca secara eksplisit **tidak mengelola Meta Ads berbayar**, kampanye iklan sponsor, maupun anggaran ads (`ads_management`, `ads_read`).  
> Seluruh metrik, dasbor, dan kapabilitas API difokuskan 100% pada **Pertumbuhan Organik**, kualitas konten, jangkauan murni, dan percakapan komunitas.

---

## 6. Analitik & Metrik Live

Dasbor Analitik Platform Admin Center (`tab=analytics`) dan Merchant Insights menyediakan metrik real-time:
- **Profil & Pengikut**: Followers count, follows count, total media count, dan info profil terverifikasi.
- **Batas Kuota Publikasi API**: Pemantauan kuota harian dari endpoint Meta `/content_publishing_limit` (maksimum 25 pos per 24 jam).
- **Interaksi & Engagement Rate**: Kalkulasi otomatis persentase interaksi dari total likes dan komentar terhadap basis pengikut.
- **Caching Cerdas**: Hasil analitik dicache selama 10 menit dengan tombol **"Segarkan Data Live"** (`?refresh_analytics=1`).
