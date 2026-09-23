@extends('public.partials.subpage_layout', [
    'title' => 'Live Demo & Simulasi',
    'category' => 'COOCA',
    'badge' => 'Uji Coba Langsung',
    'icon' => 'play',
    'headline' => 'Rasakan Kemudahan COOCA Secara Langsung Tanpa Instalasi',
    'subtitle' => 'Coba antarmuka kasir kasir kilat, buat order penjualan tiruan, cek laporan laba rugi otomatis, dan rasakan kepuasan desain Apple Bento HIG.',
    'features' => [
        ['icon' => 'shopping-cart', 'title' => 'Simulasi Kasir POS Interaktif', 'desc' => 'Klik produk, pilih varian topping, dan selesaikan transaksi checkout secepat kilat.'],
        ['icon' => 'qr-code', 'title' => 'Coba Pemesanan QR Meja', 'desc' => 'Rasakan pengalaman pelanggan kafe memesan langsung dari meja tanpa unduh aplikasi.'],
        ['icon' => 'layout-dashboard', 'title' => 'Eksplorasi Dasbor Owner Bisnis', 'desc' => 'Lihat bagaimana grafik penjualan, tren jam ramai, dan sisa kas toko tersaji elegan.'],
        ['icon' => 'smartphone', 'title' => 'Tes di Smartphone Anda', 'desc' => 'Buka di HP Anda dan buktikan betapa ringannya aplikasi tanpa membebani memori penyimpanan.'],
        ['icon' => 'sparkles', 'title' => 'Uji Coba AI Assistant', 'desc' => 'Tanyakan proyeksi penjualan hari ini atau analisis menu paling laris ke asisten AI.'],
        ['icon' => 'user-plus', 'title' => 'Siap Pakai untuk Bisnis Nyata?', 'desc' => 'Daftar akun gratis kapan saja dan data simulasi dapat di-reset bersih dengan 1 klik.'],
    ]
])
