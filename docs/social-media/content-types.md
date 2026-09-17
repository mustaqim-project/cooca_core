# Format Konten (Content Types) & Aturan Carousel

## 1. Matriks Tipe Konten Resmi per Saluran

| Saluran | Content Type | Batas Media | Format Didukung | Keterangan API Resmi |
| :--- | :--- | :--- | :--- | :--- |
| **Facebook** | `feed` | 1 Foto / Teks | JPEG, PNG, WEBP | Facebook Graph API Page Photos/Feed |
| | `video` | 1 Video | MP4, MOV | Facebook Graph API Videos |
| | `reel` | 1 Video Vertikal | MP4, MOV (9:16) | Facebook Reels Publishing API |
| **Instagram** | `photo` | 1 Foto | JPEG, PNG (4:5, 1:1, 1.91:1) | Instagram Content Publishing Single Media |
| | `carousel` | **2 s.d. 10 Berkas** | Foto & Video campuran | Instagram Multi-Item Media Container |
| | `reel` | 1 Video Vertikal | MP4, MOV (9:16) | Instagram Reels API |
| | `story` | 1 Foto / Video | JPEG, PNG, MP4 (9:16) | Instagram Story Container |
| **Threads** | `text` | Teks murni | - | Threads Graph API Text Container |
| | `image` | 1 Foto | JPEG, PNG | Threads Graph API Media Container |
| | `video` | 1 Video | MP4, MOV | Threads Graph API Video Container |
| **TikTok** | `video` | 1 Video | MP4, MOV | TikTok Direct Post Video API |
| | `photo` | **2 s.d. 35 Foto** | JPEG, PNG, WEBP | TikTok Direct Post Photo Mode |

## 2. Instagram Carousel Architecture
Dalam arsitektur COOCA, Carousel dikelola sebagai **SATU entitas postingan tunggal** (`SocialMediaPost`), bukan beberapa post terpisah.
- Berkas media diunggah ke storage lokal sementara dan direkam pada relasi `SocialPostMedia` dengan atribut `sort_order` eksplisit (1, 2, 3, ... N).
- Urutan berkas dapat diatur dan ditata ulang oleh merchant melalui tombol reordering di tray pratinjau modal composer.
- Validasi menjamin jumlah berkas minimal 2 dan maksimal 10 sebelum dikirim ke Meta Graph API.
- Proses penerbitan membuat kontainer anak untuk setiap media, mengaitkannya ke kontainer induk CAROUSEL, dan menerbitkan secara atomic.

## 3. TikTok Photo Mode Architecture
- TikTok Photo Mode memungkinkan pembuat konten mempublikasikan album foto musik/slide.
- Sesuai dokumentasi resmi TikTok Developer Platform, Photo Mode membutuhkan minimal 2 gambar.
- Caption dan maksimal 5 hashtag unik disematkan ke dalam metadata post.
