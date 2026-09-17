# Penjadwalan Konten & Kalender Visual

## 1. Alur Penjadwalan Postingan
1. **Pembuatan Jadwal**:
   - Merchant memilih waktu tayang di masa depan (`scheduled_at`).
   - Postingan disimpan dengan status `'scheduled'`.
   - Target anak (`social_post_targets`) dibuat dengan status `'pending'`.
   - Media lokal tetap tersimpan di storage publik sementara toko (`storage/app/public/social-media/temp/{business_id}/`).
2. **Eksekusi Penjadwal (Laravel Scheduler & Cron)**:
   - Command `social-media:publish-scheduled` berjalan setiap menit melalui `routes/console.php` (`Schedule::command('social-media:publish-scheduled')->everyMinute()`).
   - Mengambil postingan dengan `scheduled_at <= now()` dan `status = 'scheduled'`.
   - Melakukan transisi status atomik menjadi `'publishing'` untuk mencegah race condition (double dispatch).
   - Menerbitkan postingan target secara paralel via `PublishSocialMediaTargetJob::dispatch($target->id)`.

## 2. Kalender Konten Visual (Monthly Bento View)
Tersedia di rute `route('social-media.calendar')` (`/social-media/calendar`):
- Menampilkan grid kalender bulanan (Senin - Minggu) dengan estetika Bento Apple HIG.
- Mendukung navigasi bulan (`?month=X&year=Y`), tombol kembali ke "Hari Ini", dan shortcut pembuatan postingan baru.
- Kartu postingan di setiap tanggal dilengkapi dengan:
  - Ikon platform target berwarna (Facebook biru, Instagram magenta, Threads hitam/putih, TikTok hitam).
  - Waktu jam penayangan.
  - Cuplikan caption teks.
  - Badge status bertema Apple Status Colors (Hijau: Terbit, Oranye: Terjadwal, Merah: Gagal).
