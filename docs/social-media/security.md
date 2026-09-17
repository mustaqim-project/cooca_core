# Keamanan, Isolasi Tenant, & Perlindungan Kredensial

## 1. Isolasi Tenant Ketat (Multi-Tenant Isolation)
Aplikasi COOCA melayani banyak toko UMKM dalam satu instalasi multi-tenant:
- Setiap akun media sosial terikat secara wajib ke `business_id` (kunci asing ke tabel `businesses` dengan `cascadeOnDelete`).
- Setiap akses web controller diautentikasi dengan `Context::requireBusiness()`.
- Pengambilan akun, postingan, target, komentar, dan metrik analitik **wajib menyertakan filter `business_id = $business->id`**.
- Upaya merchant mengakses atau melakukan retry target milik toko lain diblokir dengan kode status `403 Forbidden` / `404 Not Found`.

## 2. Enkripsi Token Sensitif di Database
Token otorisasi API pihak ketiga (`access_token` dan `refresh_token`) merupakan data sensitif:
- Disimpan dalam bentuk terenkripsi menggunakan Eloquent attribute casting `'encrypted'`:
  - Enkripsi AES-256-CBC menggunakan kunci aplikasi `APP_KEY`.
  - Terlindungi dari kebocoran jika basis data ter-dump atau log query diaktifkan.
  - Secara otomatis disembunyikan dari serialisasi JSON / array dengan `$hidden = ['access_token', 'refresh_token']`.
- Tidak pernah diekspos ke antarmuka pengguna frontend, respon API, atau atribut HTML.

## 3. Mitigasi Serangan CSRF pada OAuth
- **Meta Login**: Menggunakan CSRF token session Laravel.
- **TikTok OAuth**: Menggunakan token acak 40 karakter (`Str::random(40)`) yang disimpan dalam session pengguna (`tiktok_oauth_state`) dan divalidasi menggunakan fungsi tahan timing-attack `hash_equals()` saat callback diterima. Jika state tidak cocok atau kosong, otorisasi langsung dibatalkan.

## 4. Perlindungan Logging & Masking Data
- Log aplikasi tidak pernah mencatat `client_secret`, `access_token`, `refresh_token`, atau `authorization_code`.
- Hanya informasi non-sensitif seperti `target_id`, `channel`, `platform_post_id`, dan pesan galat ringkas yang dicatat dalam log produksi.
