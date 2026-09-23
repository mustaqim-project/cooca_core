@extends('public.partials.subpage_layout', [
    'title' => 'Solusi Laundry & Dry Cleaning',
    'category' => 'Solutions',
    'badge' => 'Laundry Kiloan & Satuan',
    'icon' => 'droplets',
    'headline' => 'Solusi Usaha Laundry: Timbangan Digital, Nota WA, & Status Cuci',
    'subtitle' => 'Kelola cucian kiloan dan satuan dengan penomoran rak teratur. Kirim notifikasi WhatsApp otomatis saat cucian selesai dicuci atau siap diambil.',
    'features' => [
        ['icon' => 'scale', 'title' => 'Dukungan Timbangan Digital & Kiloan', 'desc' => 'Input berat cucian dengan desimal presisi, sistem menghitung total tagihan dalam sekejap.'],
        ['icon' => 'tag', 'title' => 'Manajemen Rak & Nomor Hanger', 'desc' => 'Ketahui lokasi penyimpanan baju pelanggan di rak nomor berapa untuk menghindari tertukar.'],
        ['icon' => 'message-circle', 'title' => 'Notifikasi "Cucian Selesai" via WA', 'desc' => 'Pelanggan langsung tahu saat pakaian mereka sudah bersih, wangi, dan siap dijemput atau diantar.'],
        ['icon' => 'package-check', 'title' => 'Layanan Satuan & Dry Cleaning', 'desc' => 'Pencatatan khusus untuk jas, gaun, sepatu, selimut, karpet, dan helm dengan instruksi cuci.'],
        ['icon' => 'truck', 'title' => 'Antar Jemput (Pick-up & Delivery)', 'desc' => 'Catat rute kurir jemput cucian pelanggan dan biaya tambahan ongkos antar.'],
        ['icon' => 'flask-conical', 'title' => 'Monitoring Stok Parfum & Deterjen', 'desc' => 'Ketahui pemakaian bahan cuci dan estimasi biaya operasional per kilogram pakaian.'],
    ]
])
