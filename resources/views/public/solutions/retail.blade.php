@extends('public.partials.subpage_layout', [
    'title' => 'Solusi Retail & Toko',
    'category' => 'Solutions',
    'badge' => 'Toko & Swalayan',
    'icon' => 'store',
    'headline' => 'Solusi Toko Retail: Kasir Cepat, Barcode Scanner, & Multi-Satuan',
    'subtitle' => 'Cocok untuk minimarket, toko kelontong, butik fashion, toko kosmetik, dan stationery. Kelola ribuan SKU barang dengan pencarian cepat dan stok opname barcode mudah.',
    'features' => [
        ['icon' => 'barcode', 'title' => 'Barcode Scanner Kamera & USB', 'desc' => 'Scan barcode pabrikan atau cetak barcode label toko sendiri langsung dari sistem.'],
        ['icon' => 'layers', 'title' => 'Multi-Satuan (Dus, Pak, Pcs)', 'desc' => 'Beli dari distributor 1 karton/dus, jual eceran per bungkus dengan pemotongan stok otomatis.'],
        ['icon' => 'tag', 'title' => 'Harga Bertingkat & Grosir', 'desc' => 'Terapkan harga berbeda untuk pembelian 1 pcs, 3 pcs, atau 1 lusin secara instan di layar kasir.'],
        ['icon' => 'book-open', 'title' => 'Pencatatan Bon Kasbon Pelanggan', 'desc' => 'Catat hutang belanja tetangga dan kirim rincian nota belanja langsung ke WhatsApp pelanggan.'],
        ['icon' => 'bell', 'title' => 'Peringatan Stok Menipis', 'desc' => 'Sistem memberi tahu produk-produk yang harus segera di-order ulang ke supplier distributor.'],
        ['icon' => 'refresh-cw', 'title' => 'Stok Opname Tanpa Tutup Toko', 'desc' => 'Hitung fisik barang di rak secara bertahap saat jam sepi tanpa menghentikan kasir jualan.'],
    ]
])
