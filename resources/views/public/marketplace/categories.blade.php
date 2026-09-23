@extends('public.partials.subpage_layout', [
    'title' => 'Kategori Marketplace',
    'category' => 'Marketplace',
    'badge' => 'Klasifikasi Industri',
    'icon' => 'grid',
    'headline' => 'Jelajahi Berbagai Kategori Produk & Layanan Bisnis',
    'subtitle' => 'Temukan produk kebutuhan Anda yang dikelompokkan secara terstruktur: F&B, Retail, Otomotif, Laundry, Kerajinan, Jasa Profesional, dan banyak lagi.',
    'features' => [
        ['icon' => 'coffee', 'title' => 'Kuliner & F&B', 'desc' => 'Kedai kopi, makanan beku (frozen food), camilan khas daerah, bumbu masak, dan katering.'],
        ['icon' => 'shirt', 'title' => 'Fashion & Gaya Hidup', 'desc' => 'Pakaian pria & wanita, kain batik tradisional, sepatu lokal, tas, dan aksesoris estetik.'],
        ['icon' => 'wrench', 'title' => 'Otomotif & Sparepart', 'desc' => 'Suku cadang motor/mobil, oli mesin, aksesoris variasi, dan layanan perawatan kendaraan.'],
        ['icon' => 'home', 'title' => 'Rumah Tangga & Kebutuhan Harian', 'desc' => 'Perlengkapan cuci laundry, perabot kayu mebel, dekorasi rumah, dan sembako.'],
        ['icon' => 'sparkles', 'title' => 'Kecantikan & Perawatan Tubuh', 'desc' => 'Produk skincare lokal ber-BPOM, sabun organik, parfum, dan layanan salon barbershop.'],
        ['icon' => 'briefcase', 'title' => 'Jasa & Servis Profesional', 'desc' => 'Servis elektronik, AC, studio foto, desainer grafis, dan konsultasi legal perizinan usaha.'],
    ]
])
