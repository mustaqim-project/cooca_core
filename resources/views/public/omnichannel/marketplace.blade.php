@extends('public.partials.subpage_layout', [
    'title' => 'Marketplace Integration',
    'category' => 'Omnichannel',
    'badge' => 'Sinkronisasi Multi-Channel',
    'icon' => 'shopping-bag',
    'headline' => 'Sinkronisasi Stok & Pesanan dari Berbagai Marketplace',
    'subtitle' => 'Hubungkan toko Anda di berbagai marketplace terkemuka. Cegah pembatalan pesanan akibat selisih stok fisik dan digital.',
    'features' => [
        ['icon' => 'refresh-cw', 'title' => 'Sinkronisasi Stok Real-Time', 'desc' => 'Ketika 1 produk terjual di toko fisik atau Shopee, stok di Tokopedia langsung berkurang otomatis.'],
        ['icon' => 'inbox', 'title' => 'Pusat Order Terpadu (Single Order Hub)', 'desc' => 'Proses seluruh pesanan yang masuk dari berbagai platform dalam satu layar pemrosesan terpadu.'],
        ['icon' => 'printer', 'title' => 'Cetak Massal Label Pengiriman (Shipping Label)', 'desc' => 'Cetak resi dan label paket dari banyak marketplace secara serentak hanya dalam 1 klik.'],
        ['icon' => 'tag', 'title' => 'Manajemen Harga per Saluran', 'desc' => 'Tentukan perbedaan harga jual untuk memperhitungkan biaya admin marketplace yang berbeda-beda.'],
        ['icon' => 'alert-triangle', 'title' => 'Pencegahan Overselling & Penalti', 'desc' => 'Fitur safety stock otomatis mengunci sisa stok barang agar tidak terjadi penalti pembatalan.'],
        ['icon' => 'bar-chart-2', 'title' => 'Komparasi Laba Bersih per Marketplace', 'desc' => 'Ketahui marketplace mana yang memberikan margin profit paling sehat setelah dipotong fee platform.'],
    ]
])
