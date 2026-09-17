# Integrasi Meta Platform (Facebook, Instagram, Threads)

## 1. Ikhtisar Integrasi
Integrasi Meta di COOCA memanfaatkan **Meta Graph API v21.0** resmi dengan model otorisasi tunggal (*Single Authorization Flow*). Sekali otorisasi dari akun Facebook merchant, sistem secara otomatis menemukan dan menghubungkan:
1. **Facebook Pages** yang dikelola.
2. **Instagram Business Accounts** yang terhubung ke Facebook Page.
3. **Threads Profiles** yang terhubung.

## 2. Alur OAuth & Token Lifecycle
1. **Merchant Login**: Merchant mengklik "Hubungkan Meta", sistem membuka popup Facebook Login for Business dengan cakupan izin (*scopes*):
   - `pages_show_list`, `pages_read_engagement`, `pages_manage_posts`, `instagram_basic`, `instagram_content_publish`, `threads_basic`, `threads_content_publish`.
2. **Token Exchange**:
   - Kode otorisasi ditukar menjadi Short-Lived User Access Token.
   - Ditukar kembali menjadi **Long-Lived User Access Token** (masa berlaku 60 hari).
   - Memanggil endpoint `/me/accounts` untuk memperoleh **Permanent Page Access Token** yang tidak pernah kedaluwarsa selama izin tidak dicabut oleh merchant.
3. **Penyimpanan Kredensial**:
   - Disimpan di tabel `social_media_accounts` dengan `provider = 'meta'`.
   - `access_token` dienkripsi secara simetris AES-256-CBC via Eloquent `encrypted` cast.

## 3. Publikasi Konten & Carousel
- **Facebook Feed / Video / Reel**:
  - Feed foto: Mengunggah gambar via `/{page-id}/photos` dengan parameter `published=true`.
  - Video / Reels: Mengunggah via `/{page-id}/videos`.
- **Instagram Photo / Reel / Carousel**:
  - Instagram API menggunakan pendekatan kontainer media 2 langkah:
    1. Membuat Media Container item individual (`/{ig-user-id}/media?image_url=...&is_carousel_item=true`).
    2. Membuat Media Container Carousel (`/{ig-user-id}/media?media_type=CAROUSEL&children=id1,id2...`).
    3. Menerbitkan kontainer via `/{ig-user-id}/media_publish?creation_id={carousel-container-id}`.
- **Threads Text / Image / Video**:
  - Diterbitkan melalui Threads Graph API container creation & publish endpoint.

## 4. Keamanan & Tenant Isolation
Semua interaksi Meta Graph API diisolasi ketat menggunakan `business_id`. Merchant A tidak dapat melihat, mengubah, atau menerbitkan konten ke Facebook Page / Akun IG milik Merchant B.
