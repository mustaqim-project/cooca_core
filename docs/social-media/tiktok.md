# Integrasi TikTok Developer Platform (Content Posting API)

## 1. Ikhtisar Integrasi
Integrasi TikTok di COOCA menggunakan **TikTok Open API v2** dan **Content Posting API** resmi untuk memungkinkan Business Owner mempublikasikan video dan foto (Photo Mode) langsung dari dashboard COOCA.

## 2. Parameter Kredensial & Lingkungan
Dikonfigurasi melalui environment variables (`.env`) atau Admin Center:
- `TIKTOK_CLIENT_KEY`: Client Key dari TikTok Developer App.
- `TIKTOK_CLIENT_SECRET`: Client Secret dari TikTok Developer App.
- `TIKTOK_REDIRECT_URI`: URI Callback terdaftar (contoh: `https://umkm.cooca.id/social-media/tiktok/callback`).

Cakupan Izin (*Scopes*):
- `user.info.basic`: Mengambil OpenID dan informasi profil dasar pembuat konten.
- `user.info.profile`: Informasi profil publik.
- `user.info.stats`: Statistik pengikut dan keterlibatan.
- `video.publish`: Otorisasi publikasi video langsung (*Direct Post*).
- `video.upload`: Izin pengunggahan aset video ke server TikTok.

## 3. Alur OAuth 2.0 & Token Refresh
1. **Otorisasi**:
   - URL otorisasi resmi: `https://www.tiktok.com/v2/auth/authorize/`.
   - Menggunakan state token acak sepanjang 40 karakter yang diverifikasi via session (`hash_equals`) untuk mencegah serangan CSRF.
2. **Pertukaran Kode**:
   - Menghubungi `https://open.tiktokapis.com/v2/oauth/token/` dengan `grant_type=authorization_code`.
   - Mengembalikan `open_id`, `access_token` (berlaku 86400 detik / 1 hari), dan `refresh_token` (berlaku 365 hari).
3. **Penyimpanan**:
   - Disimpan di `social_media_accounts` dengan `provider = 'tiktok'`, `platform = 'tiktok'`.
   - `access_token` dan `refresh_token` dienkripsi otomatis oleh Eloquent.
4. **Penyegaran Token Otomatis (*Auto-Refresh*)**:
   - Sebelum job queue melakukan posting konten, `TikTokProvider::checkAndRefreshToken()` memeriksa apakah access token kedaluwarsa dalam 15 menit ke depan (`needsTokenRefresh(15)`).
   - Jika ya, sistem memanggil `https://open.tiktokapis.com/v2/oauth/token/` dengan `grant_type=refresh_token`, lalu memperbarui token dan masa kedaluwarsa baru di database tanpa memerlukan login ulang dari merchant.

## 4. Publikasi Konten TikTok (Direct Post API)
### A. Direct Video Post
1. Memanggil `/v2/post/publish/video/init/` dengan:
   - `post_mode = 'DIRECT_POST'`.
   - `source_info.source = 'PULL_FROM_URL'` dengan `video_url` publik.
   - `post_info`: `title` (caption + hashtag maksimal 5), `privacy_level`, dan izin interaksi (`disable_duet`, `disable_stitch`, `disable_comment`).
2. TikTok mengembalikan `publish_id`.
3. Worker memantau status publikasi via `/v2/post/publish/status/fetch/` hingga berstatus `SUCCESS` atau gagal.

### B. Direct Photo Post (Photo Mode)
1. Memanggil `/v2/post/publish/content/init/` dengan:
   - `media_type = 'PHOTO'`.
   - `post_mode = 'DIRECT_POST'`.
   - `source_info.photo_images`: minimal 2 tautan gambar publik.
   - `post_info.title`: caption dengan maksimal 5 tagar unik.
2. Memantau `publish_id` sampai selesai.
