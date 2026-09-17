# Panduan Pemecahan Masalah (Troubleshooting Guide)

## 1. Masalah Umum & Solusi

### A. Galat Otorisasi TikTok (State Tidak Valid)
- **Gejala**: Notifikasi galat *"Validasi keamanan OAuth TikTok gagal (state tidak valid)"*.
- **Penyebab**: Session browser kedaluwarsa, merchant menekan tombol kembali (*back*), atau terjadi cross-site domain mismatch.
- **Solusi**: Minta merchant mengklik kembali tombol "Hubungkan Akun TikTok Resmi" dan menyelesaikan proses otorisasi dalam satu sesi browser aktif tanpa reload.

### B. Galat Token Kedaluwarsa (Token Expired / 401 Unauthorized)
- **TikTok**: Sistem otomatis memperbarui access token jika masa berlaku kurang dari 15 menit menggunakan `refresh_token` (berlaku 1 tahun). Jika refresh token telah kedaluwarsa atau merchant mencabut izin dari aplikasi TikTok, status akun akan berubah menjadi `expired` dan merchant cukup mengklik "Perbarui Akun TikTok".
- **Meta (Facebook/Instagram)**: Long-lived token berlaku 60 hari sedangkan Page token bersifat permanen. Jika admin Facebook mengubah password akun Facebook utamanya, Page token mungkin menjadi tidak valid. Solusinya adalah melakukan re-koneksi 1-klik melalui tombol "Perbarui Akun Meta".

### C. Rate Limit API Platform (HTTP 429)
- **Gejala**: Penerbitan konten gagal dengan pesan galat rate limit.
- **Solusi**: Queue Job secara otomatis melakukan backoff bertahap (10 detik, 30 detik, 60 detik). Jika masih gagal, target berstatus `failed` dan merchant dapat mengklik tombol "Coba Lagi" beberapa jam kemudian tanpa membuat postingan baru.

### D. Media Tidak Dapat Diakses oleh Server TikTok (PULL_FROM_URL Failed)
- **Gejala**: TikTok API mengembalikan galat saat mengunduh berkas video atau foto dari URL.
- **Penyebab**: Server COOCA berada di jaringan lokal (localhost/privat tanpa ngrok/domain publik), atau URL media terlindungi autentikasi.
- **Solusi**: Pastikan environment produksi memiliki `APP_URL` dengan protokol HTTPS yang dapat diakses publik oleh server CDN TikTok dan Meta.

### E. Postingan Gagal karena Melebihi Batas Tagar COOCA
- **Gejala**: Muncul pesan galat *"Jumlah hashtag (X) melebihi aturan maksimal COOCA (5 hashtag per postingan)"*.
- **Solusi**: Sunting caption agar hanya menyertakan maksimal 5 tagar unik.
