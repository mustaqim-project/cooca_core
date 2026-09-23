@extends('public.partials.subpage_layout', [
    'title' => 'Produk Pilihan UMKM',
    'category' => 'Marketplace',
    'badge' => 'Katalog Produk',
    'icon' => 'shopping-bag',
    'headline' => 'Katalog Produk Pilihan Langsung dari Produsen & Penjual',
    'subtitle' => 'Temukan produk makanan, minuman, kerajinan tangan, pakaian, suku cadang, dan jasa dengan harga terbaik tanpa perantara rantai pasok panjang.',
    'features' => [
        ['icon' => 'search', 'title' => 'Pencarian Cepat & Filter Presisi', 'desc' => 'Cari produk berdasarkan kata kunci, rentang harga, lokasi pengiriman, dan ketersediaan stok.'],
        ['icon' => 'shield-check', 'title' => 'Jaminan Stok Real-Time', 'desc' => 'Katalog terhubung langsung ke persediaan gudang penjual sehingga pesanan pasti dapat dikirim.'],
        ['icon' => 'credit-card', 'title' => 'Pembayaran Aman QRIS & Transfer', 'desc' => 'Dukungan berbagai metode pembayaran resmi yang mudah dan terverifikasi otomatis.'],
        ['icon' => 'truck', 'title' => 'Pilihan Kurir & Estimasi Ongkir', 'desc' => 'Cek ongkos kirim berbagai ekspedisi reguler, kargo, atau kurir instan sebelum checkout.'],
        ['icon' => 'percent', 'title' => 'Harga Spesial & Promo Diskon', 'desc' => 'Dapatkan potongan harga langsung dan voucher promo dari toko-toko terdaftar di COOCA.'],
        ['icon' => 'repeat', 'title' => 'Pemesanan Berulang Mudah', 'desc' => 'Simpan produk favorit untuk memesan kembali dengan cepat dalam 1 ketukan layar.'],
    ]
])
