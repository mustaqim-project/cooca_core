@extends('public.partials.subpage_layout', [
    'title' => 'Solusi Jasa & Bisnis Servis',
    'category' => 'Solutions',
    'badge' => 'Jasa & Konsultasi',
    'icon' => 'briefcase',
    'headline' => 'Solusi Bisnis Jasa: Reservasi Waktu, Penugasan Staf, & Invoice Bertahap',
    'subtitle' => 'Untuk barbershop, salon kecantikan, klinik, jasa servis AC, konsultan, dan studio foto. Kelola kalender booking janji temu dan penagihan proyek secara profesional.',
    'features' => [
        ['icon' => 'calendar-check', 'title' => 'Booking Jadwal & Kalender Reservasi', 'desc' => 'Klien memilih jam kunjungan atau kedatangan teknisi secara online tanpa bentrok jadwal.'],
        ['icon' => 'user-plus', 'title' => 'Penugasan Staf & Kapasitas Jam Kerja', 'desc' => 'Distribusikan orderan jasa ke terapis, tukang cukur, atau teknisi yang sedang tersedia.'],
        ['icon' => 'file-invoice', 'title' => 'Invoice Bertahap (DP & Pelunasan)', 'desc' => 'Terbitkan tagihan uang muka (Down Payment) dan invoice pelunasan setelah pekerjaan selesai.'],
        ['icon' => 'clock', 'title' => 'Pengingat Janji Temu WhatsApp Otomatis', 'desc' => 'Kirim reminder 1 hari atau beberapa jam sebelum waktu janji temu untuk mencegah no-show.'],
        ['icon' => 'receipt', 'title' => 'Kuitansi & Bukti Layanan Digital', 'desc' => 'Kirim laporan hasil inspeksi servis atau lembar garansi kerja langsung ke email/WhatsApp klien.'],
        ['icon' => 'percent', 'title' => 'Bagi Hasil & Komisi Tenaga Kerja', 'desc' => 'Perhitungan komisi jasa terapis/teknisi transparan sesuai persentase kesepakatan usaha.'],
    ]
])
