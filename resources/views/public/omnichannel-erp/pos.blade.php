@extends('public.partials.subpage_layout', [
    'title' => 'Point of Sale (POS)',
    'category' => 'Omnichannel ERP',
    'badge' => 'Kasir Cepat & Andal',
    'icon' => 'shopping-cart',
    'headline' => 'Aplikasi Kasir POS Kilat: Online & Offline Ready',
    'subtitle' => 'Proses transaksi hitungan detik, terima pembayaran QRIS instan, cetak struk thermal Bluetooth, dan kelola meja kafe/restoran tanpa hambatan.',
    'features' => [
        ['icon' => 'zap', 'title' => 'Checkout Kilat < 3 Detik', 'desc' => 'Pencarian produk instan, pemindaian barcode kamera, dan tombol favorit untuk menu terlaris.'],
        ['icon' => 'qr-code', 'title' => 'Dukungan QRIS & Multi-Payment', 'desc' => 'Terima pembayaran tunai, debit, kartu kredit, transfer bank, hingga QRIS dinamis/statis.'],
        ['icon' => 'printer', 'title' => 'Printer Struk Bluetooth & USB', 'desc' => 'Kompatibel dengan semua jenis printer thermal struk 58mm & 80mm serta kitchen printer tiket pesanan.'],
        ['icon' => 'wifi-off', 'title' => 'Mode Offline Tanpa Jeda', 'desc' => 'Tetap bisa melayani transaksi saat koneksi internet putus, data otomatis tersinkron saat online.'],
        ['icon' => 'layout-grid', 'title' => 'Manajemen Meja & Split Bill', 'desc' => 'Visualisasi denah meja, pindah meja, gabung pesanan, dan pisah tagihan antar pengunjung.'],
        ['icon' => 'lock', 'title' => 'Proteksi Supervisor PIN', 'desc' => 'Cegah kecurangan kasir dengan otorisasi PIN untuk diskon khusus, pembatalan (void), dan refund.'],
    ]
])
