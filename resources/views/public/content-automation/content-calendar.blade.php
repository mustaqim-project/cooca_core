@extends('public.partials.subpage_layout', [
    'title' => 'Content Calendar',
    'category' => 'Content Automation',
    'badge' => 'Perencanaan Jadwal',
    'icon' => 'calendar',
    'headline' => 'Rencanakan Konten Sebulan Penuh Tanpa Panik',
    'subtitle' => 'Kelola jadwal posting media sosial dalam kalender visual interaktif: drag-and-drop jadwal, tandai tanggal promosi gajian, dan pantau status kesiapan materi.',
    'features' => [
        ['icon' => 'calendar-days', 'title' => 'Tampilan Kalender Visual Bulanan', 'desc' => 'Lihat seluruh agenda postingan di Instagram, TikTok, dan Facebook secara teratur dalam satu grid.'],
        ['icon' => 'move', 'title' => 'Drag and Drop Reschedule', 'desc' => 'Geser jadwal postingan ke tanggal atau jam lain dengan mudah tanpa harus mengedit ulang dari awal.'],
        ['icon' => 'flag', 'title' => 'Penanda Tanggal Promosi & Hari Besar', 'desc' => 'Pengingat otomatis untuk momen kampanye diskon tanggal kembar (Double Dates) dan libur nasional.'],
        ['icon' => 'tag', 'title' => 'Kategori & Tema Konten (Content Pillar)', 'desc' => 'Kelompokkan konten ke pilar Edukasi, Hiburan, Soft-selling, Hard-selling, dan Testimoni pelanggan.'],
        ['icon' => 'users', 'title' => 'Kolaborasi Tim & Persetujuan (Approval)', 'desc' => 'Staf desainer/admin menyusun draf, pemilik bisnis cukup meninjau dan menyetujui sebelum tayang.'],
        ['icon' => 'check-circle', 'title' => 'Pelacak Kesiapan Aset Konten', 'desc' => 'Ketahui postingan mana yang gambar/videonya belum siap agar tidak terlambat tayang.'],
    ]
])
