# COOCA Unified Social Media Architecture

## 1. Executive Summary & Vision
Modul **COOCA Unified Social Media Management** mengintegrasikan pengelolaan berbagai platform media sosial resmi ke dalam satu antarmuka terpadu (Unified Cockpit) untuk Business Owner / Merchant UMKM Indonesia.

Sebelumnya, COOCA memiliki integrasi Meta (Facebook Page, Instagram, Threads). Arsitektur ini diperluas menjadi **Omnichannel Multi-Provider** dengan menambahkan **TikTok Developer Platform Official API (Content Posting API)** secara *first-class*.

```text
                               COOCA System
                                     │
                                     ▼
                        Social Media Layer (Manager)
                                     │
                 ┌───────────────────┴───────────────────┐
                 ▼                                       ▼
           Meta Provider                          TikTok Provider
                 │                                       │
        ┌────────┼────────┐                              ▼
        ▼        ▼        ▼                            TikTok
    Facebook Instagram Threads
```

## 2. Layer & Component Separation

### A. Provider Layer (`app/Domain/SocialMedia/Providers/`)
- Mengimplementasikan `App\Domain\SocialMedia\Contracts\SocialMediaProviderInterface`.
- Bertanggung jawab atas:
  - Pembentukan OAuth Authorization URL.
  - Penanganan OAuth callback & pertukaran kode otorisasi.
  - Rotasi & penyegaran token (access token & refresh token).
  - Publikasi konten ke channel yang didukung melalui client resmi.
- Provider yang terdaftar:
  - `MetaProvider` -> Mengelola Facebook, Instagram, Threads.
  - `TikTokProvider` -> Mengelola TikTok.

### B. Client Layer (`app/Domain/SocialMedia/Clients/`)
- Komunikasi HTTP murni ke API resmi platform dengan cURL / Guzzle:
  - `MetaSocialMediaClient` -> Meta Graph API v21.0.
  - `TikTokClient` -> TikTok Open API v2.

### C. Validation Layer (`app/Domain/SocialMedia/Validation/`)
- `SocialMediaContentValidator`:
  - Aturan Bisnis COOCA: **Maksimal 5 tagar/hashtag unik** per postingan/saluran (deduplikasi case-insensitive).
  - Validasi panjang karakter caption multibyte UTF-8 (`mb_strlen`).
  - Validasi batas media: Instagram Carousel (2-10 berkas), TikTok Photo Mode (2-35 foto), Video/Reels (format & ekstensi).

### D. Data Persistence & Multi-Tenant Model
- `SocialMediaAccount`: Kredensial akun terisolasi per tenant (`business_id`). Token dienkripsi menggunakan Laravel `encrypted` attribute casting.
- `SocialMediaPost`: Entitas induk postingan kampanye (caption, format utama, status jadwal, tenant).
- `SocialPostTarget`: Target spesifik per saluran (channel, provider, content_type, custom_caption override, platform_post_id, error_message, retry_count).
- `SocialPostMedia`: Berkas media terurut (`sort_order`, `media_url`, `local_path`, `media_type`).

### E. Queue & Asynchronous Processing (`app/Jobs/SocialMedia/`)
- `PublishSocialMediaTargetJob`: Mengelola pemrosesan asinkron per target dengan idempotency guard, exponential backoff, penanganan partial failure, dan auto-purge storage saat seluruh target selesai.

---
Dokumen ini disusun sebagai panduan teknis resmi arsitektur COOCA Social Media.
