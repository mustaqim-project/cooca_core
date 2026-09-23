@extends('public.partials.subpage_layout', [
    'title' => 'Lokasi Bisnis & Kota',
    'category' => 'Marketplace',
    'badge' => 'Cakupan Wilayah',
    'icon' => 'map-pin',
    'headline' => 'Temukan Bisnis & Toko Lokal di Kota Anda',
    'subtitle' => 'Dukung ekosistem wirausaha daerah. Jelajahi UMKM terdaftar dari Jakarta, Bandung, Surabaya, Yogyakarta, Medan, Bali, hingga seluruh pelosok nusantara.',
    'features' => [
        ['icon' => 'compass', 'title' => 'Pencarian Berbasis Titik GPS', 'desc' => 'Aktifkan izin lokasi untuk melihat kafe, bengkel, atau toko terdekat dalam jarak beberapa kilometer.'],
        ['icon' => 'building', 'title' => 'Direktori Kota-Kota Besar', 'desc' => 'Daftar gerai terpopuler di kota-kota pusat bisnis dengan opsi pengiriman kurir instan kilat.'],
        ['icon' => 'truck', 'title' => 'Dukungan Pengiriman Seluruh Nusantara', 'desc' => 'Banyak produsen lokal yang melayani pengiriman paket ke seluruh 38 provinsi di Indonesia.'],
        ['icon' => 'store', 'title' => 'Opsi Ambil di Tempat (Self Pick-up)', 'desc' => 'Pesan online terlebih dahulu melalui sistem COOCA dan ambil pesanan langsung di toko.'],
        ['icon' => 'award', 'title' => 'Sentra Oleh-Oleh Khas Daerah', 'desc' => 'Temukan kuliner khas dan cinderamata otentik langsung dari pengrajin daerah asalnya.'],
        ['icon' => 'map', 'title' => 'Peta Digital & Petunjuk Arah', 'desc' => 'Tautan langsung ke Google Maps untuk memudahkan Anda mengunjungi outlet toko fisik.'],
    ]
])
