@extends('public.partials.subpage_layout', [
    'title' => 'Customer Portal & Directory',
    'category' => 'Omnichannel',
    'badge' => 'Pengalaman Pelanggan Modern',
    'icon' => 'user-check',
    'headline' => 'Portal Mandiri Pelanggan: Belanja, Reservasi, & Lacak Resi',
    'subtitle' => 'Berikan pengalaman berkelas untuk pembeli Anda dengan portal online mandiri: pesan menu via QR meja, booking layanan, dan pantau status paket kapan saja.',
    'features' => [
        ['icon' => 'qr-code', 'title' => 'Self-Ordering QR Meja Restoran', 'desc' => 'Tamu kafe/resto scan QR di meja, langsung lihat menu digital, pesan makanan, dan bayar tanpa antre.'],
        ['icon' => 'calendar', 'title' => 'Sistem Reservasi & Booking Jadwal', 'desc' => 'Pelanggan bengkel, salon, atau klinik dapat memilih jam kunjungan dengan konfirmasi ketersediaan live.'],
        ['icon' => 'compass', 'title' => 'Lacak Status Pesanan Mandiri (Live Tracking)', 'desc' => 'Cukup masukkan nomor token order, pelanggan dapat melihat progres paket mereka tanpa tanya admin.'],
        ['icon' => 'user', 'title' => 'Akun Profil & Riwayat Transaksi', 'desc' => 'Pelanggan dapat melihat nota pembelanjaan terdahulu, alamat pengiriman tersimpan, dan poin loyalitas.'],
        ['icon' => 'shield-check', 'title' => 'Keamanan Data IDOR Shield', 'desc' => 'Setiap tautan akses publik diamankan dengan token kriptografi acak yang menjamin privasi belanja.'],
        ['icon' => 'star', 'title' => 'Ulasan & Testimoni Pelanggan', 'desc' => 'Kumpulkan penilaian kepuasan bintang 5 untuk membangun reputasi merek bisnis Anda.'],
    ]
])
