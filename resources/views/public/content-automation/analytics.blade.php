@extends('public.partials.subpage_layout', [
    'title' => 'Content Analytics',
    'category' => 'Content Automation',
    'badge' => 'Evaluasi Kinerja Konten',
    'icon' => 'bar-chart',
    'headline' => 'Ukur Efektivitas Konten Promosi Terhadap Pertumbuhan Penjualan',
    'subtitle' => 'Ketahui jenis konten yang paling disukai audiens, grafik impresi postingan, dan korelasinya langsung terhadap kenaikan omzet toko Anda.',
    'features' => [
        ['icon' => 'eye', 'title' => 'Metrik Jangkauan & Impresi', 'desc' => 'Lihat berapa banyak pasang mata yang melihat postingan Anda di berbagai platform media sosial.'],
        ['icon' => 'heart', 'title' => 'Tingkat Keterlibatan (Engagement Rate)', 'desc' => 'Pantau jumlah like, komentar, share, dan save untuk mengukur seberapa menarik konten yang disajikan.'],
        ['icon' => 'mouse-pointer-click', 'title' => 'Klik Tautan Bio & Konversi Checkout', 'desc' => 'Lacak perjalanan audiens dari melihat postingan hingga mengklik tautan belanja dan membayar produk.'],
        ['icon' => 'trophy', 'title' => 'Peringkat Konten Terbaik (Top Performers)', 'desc' => 'Daftar konten dengan performa tertinggi sebagai acuan pembuatan materi promosi berikutnya.'],
        ['icon' => 'clock', 'title' => 'Analisis Waktu Terbaik (Best Time to Post)', 'desc' => 'Rekomendasi jam dan hari di mana pengikut Anda paling banyak memberikan respon aktif.'],
        ['icon' => 'file-bar-chart', 'title' => 'Laporan Rekap Mingguan Otomatis', 'desc' => 'Ringkasan performa media sosial mingguan yang dikirim langsung ke dasbor Anda.'],
    ]
])
