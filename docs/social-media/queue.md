# Arsitektur Antrean (Queue) & Pemrosesan Asinkron

## 1. Desain Job Antrean
Penerbitan konten ke platform pihak ketiga (Meta & TikTok) memerlukan latensi jaringan dan pemrosesan media, sehingga **wajib dieksekusi secara asinkron** melalui sistem Queue Laravel (`app/Jobs/SocialMedia/PublishSocialMediaTargetJob.php`).

Konfigurasi Job:
- Antrean: `ShouldQueue`.
- Percobaan Maksimal (`$tries`): 3 kali.
- Waktu Tunggu Eksponensial (`$backoff`): `[10, 30, 60]` detik (10 detik percobaan ke-2, 30 detik ke-3, 60 detik berikutnya).
- Waktu Habis (`$timeout`): 180 detik.

## 2. Jaminan Idempotency
Untuk mencegah penerbitan ganda di akun media sosial merchant jika terjadi kegagalan jaringan atau worker restart:
1. Sebelum memanggil API penyedia, job memeriksa status target di database:
   ```php
   if ($target->status === 'published') {
       return; // Exit early safely without calling API
   }
   ```
2. Target diubah ke status `'processing'` segera setelah job mulai dieksekusi.
3. Setelah API merespons dengan ID postingan resmi, ID tersebut langsung disimpan ke `platform_post_id` dan status diubah ke `'published'`.

## 3. Penanganan Keberhasilan Parsial (Partial Success) & Retry Target
Ketika sebuah postingan ditargetkan ke beberapa saluran sekaligus (misal: Facebook, Instagram, Threads, dan TikTok):
- Jika Facebook, Instagram, dan TikTok berhasil, namun Threads gagal karena *rate limit*, status postingan induk menjadi `'partial_failed'`.
- Merchant dapat mengklik tombol **"Coba Lagi"** khusus pada baris Threads yang gagal.
- Rute `route('social-media.targets.retry', $target)` hanya mendispatch job untuk target Threads tersebut tanpa mengulang postingan yang sudah sukses di Facebook, Instagram, atau TikTok.

## 4. Pembersihan Berkas Otomatis (Storage Auto-Purge)
Sesuai arahan arsitektur, COOCA tidak menyimpan berkas media promosi permanen di server toko guna menghemat kuota penyimpanan disk server:
- Saat seluruh target selesai dipublikasikan (`count(pending) === 0` dan `count(processing) === 0`), sistem secara otomatis memanggil `purgeLocalMedia($post)`.
- Semua berkas yang tercatat pada `local_media_paths` dan `social_post_media.local_path` dihapus secara permanen dari disk publik storage COOCA.
