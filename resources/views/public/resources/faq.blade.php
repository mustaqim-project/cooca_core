@extends('public.partials.subpage_layout', [
    'title' => 'Tanya Jawab (FAQ)',
    'category' => 'Resources',
    'badge' => 'Pertanyaan Populer',
    'icon' => 'help-circle',
    'headline' => 'Pertanyaan yang Sering Diajukan Seputar COOCA',
    'subtitle' => 'Temukan jawaban cepat mengenai model lisensi, keamanan data, perangkat keras yang didukung, dan panduan migrasi bisnis Anda.',
    'features' => [
        ['icon' => 'dollar-sign', 'title' => 'Apakah COOCA benar-benar gratis?', 'desc' => 'Ya, seluruh modul operasional esensial (POS kasir, inventori, laporan laba rugi dasar) gratis digunakan.'],
        ['icon' => 'smartphone', 'title' => 'Perangkat apa saja yang didukung?', 'desc' => 'COOCA berjalan berbasis web modern di smartphone Android/iOS, tablet kasir, iPad, laptop, dan komputer PC.'],
        ['icon' => 'printer', 'title' => 'Apakah bisa pakai printer kasir lama saya?', 'desc' => 'Bisa. COOCA mendukung hampir semua printer thermal struk Bluetooth 58mm & 80mm standar pasar.'],
        ['icon' => 'database', 'title' => 'Bagaimana keamanan data bisnis saya?', 'desc' => 'Data Anda terisolasi dengan proteksi tenant ketat, dienkripsi saat transit dan di server cloud bersertifikasi.'],
        ['icon' => 'wifi-off', 'title' => 'Apakah bisa jualan saat internet mati?', 'desc' => 'Ya, modul kasir POS dirancang memiliki kapabilitas offline ringan untuk tetap mencetak struk belanjaan.'],
        ['icon' => 'file-up', 'title' => 'Bisakah memindahkan data dari Excel lama?', 'desc' => 'Bisa! Kami menyediakan template impor Excel untuk produk, stok, dan data pelanggan hanya dalam sekali klik.'],
    ]
])
