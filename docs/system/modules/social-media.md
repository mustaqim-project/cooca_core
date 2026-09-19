# Modul Media Sosial & Integrasi Platform Meta / TikTok

> **Layer 2: System Knowledge Document**  
> **Ruang Lingkup:** Integrasi Meta Graph API (Instagram Professional & Facebook Pages), Threads API, dan TikTok Open API untuk Superadmin Platform Cooca dan Tenant UMKM.

---

## 1. Arsitektur & Peran Modul

Modul Media Sosial melayani dua domain utama:
1. **Platform Center (Superadmin Cockpit)**: Publikasi konten resmi Cooca Indonesia (`@cooca.indonesia` dan Facebook Page `Cooca Indonesia`), manajemen kotak masuk interaksi/komentar, serta dasbor analitik & pertumbuhan organik.
2. **Merchant Hub (Tenant UMKM)**: Penautan akun bisnis merchant, penerbitan katalog produk & promo, serta moderasi komentar.

---

## 2. Format Konten yang Didukung

Sistem mendukung tiga format utama penerbitan konten ke Meta Graph API v21.0:
1. **Postingan Feed (`IMAGE` / `CAROUSEL` / `TEXT`)**:
   - Foto tunggal atau album carousel (2 hingga 10 berkas).
   - Teks caption lengkap (hingga 2.200 karakter) dan tagar.
2. **Reels Video (`REELS`)**:
   - Video vertikal aspek rasio 9:16 (format `.mp4` atau `.mov`).
   - Parameter Meta: `media_type=REELS`, `share_to_feed=true`, dan `caption`.
   - Waktu pemrosesan kontainer asynchronous transkoding otomatis dipantau (`waitForMediaContainerReady`) hingga status `FINISHED`.
   - Pada Facebook Page, video vertikal otomatis terintegrasi ke ekosistem video/reels halaman.
3. **Instagram Story (`STORIES`)**:
   - Konten tayang 24 jam berupa foto atau video pendek.
   - Parameter Meta: `media_type=STORIES`, `image_url` atau `video_url`.
   - **Catatan Spesifikasi API:** Meta Graph API melarang pengiriman parameter `caption` untuk format Story. Sistem secara otomatis mensterilkan caption saat mempublikasikan ke Stories.

---

## 3. Kebijakan Privasi & Pengecualian Meta Ads

> [!IMPORTANT]
> **Kebijakan Nol Meta Ads:**  
> Sistem Cooca secara eksplisit **tidak mengelola Meta Ads berbayar**, kampanye iklan sponsor, maupun anggaran ads (`ads_management`, `ads_read`).  
> Seluruh metrik, dasbor, dan kapabilitas API difokuskan 100% pada **Pertumbuhan Organik**, kualitas konten, jangkauan murni, dan percakapan komunitas.

---

## 4. Analitik & Metrik Live

Dasbor Analitik Platform Admin Center (`tab=analytics`) menyediakan metrik real-time:
- **Profil Instagram**: Followers count, follows count, total media count, dan info profil terverifikasi.
- **Batas Kuota Publikasi API**: Pemantauan kuota harian dari endpoint Meta `/content_publishing_limit` (maksimum 25 pos per 24 jam).
- **Interaksi & Engagement Rate**: Kalkulasi otomatis persentase interaksi dari total likes dan komentar pada 15 postingan/reels terbaru terhadap basis pengikut.
- **Halaman Facebook**: Kategori halaman, jumlah fans/pengikut, serta talking about count.
- **Caching Cerdas**: Hasil analitik dicache selama 10 menit dengan tombol **"Segarkan Data Live"** (`?refresh_analytics=1`) untuk update instan tanpa menunggu kedaluwarsa cache.

---

## 5. Kredensial & Autentikasi Meta

- `social_media_app_token`: User access token berjangka panjang.
- `social_media_page_token`: Page access token permanen Halaman Facebook `Cooca Indonesia` (`1340316975827711`).
- `instagram_access_token`: Token akses Instagram yang ditautkan ke akun profesional `@cooca.indonesia` (`17841439846162016`).
- Command utilitas penyegaran konfigurasi: `php artisan instagram:configure`.
