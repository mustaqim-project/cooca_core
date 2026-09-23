@extends('public.partials.subpage_layout', [
    'title' => 'Solusi F&B & Restoran',
    'category' => 'Solutions',
    'badge' => 'Kuliner & Kafe',
    'icon' => 'utensils',
    'headline' => 'Sistem Operasi Kuliner: Dari Resep, Dapur, Kasir, hingga Meja Tamu',
    'subtitle' => 'Solusi lengkap untuk kedai kopi, restoran, warung makan, dan cloud kitchen. Kontrol HPP resep per porsi, cetak tiket dapur (Kitchen Order Ticket), dan integrasikan pemesanan QR di meja.',
    'features' => [
        ['icon' => 'calculator', 'title' => 'HPP Resep (Food Costing)', 'desc' => 'Setiap porsi makanan atau minuman terjual, stok bahan baku (gram, ml, pcs) otomatis terpotong presisi.'],
        ['icon' => 'printer', 'title' => 'Cetak Tiket Dapur & Barista', 'desc' => 'Pisahkan struk pelanggan, tiket masak untuk koki di dapur, dan pesanan minuman untuk barista.'],
        ['icon' => 'layout-grid', 'title' => 'Manajemen Meja & Split Bill', 'desc' => 'Dukung pesan antar meja, simpan pesanan sementara (open tab), dan pisah tagihan antar tamu.'],
        ['icon' => 'qr-code', 'title' => 'Pemesanan Mandiri QR Meja', 'desc' => 'Tamu scan barcode di meja, pilih menu favorit, dan bayar lewat QRIS tanpa memanggil pelayan.'],
        ['icon' => 'alert-triangle', 'title' => 'Pencegahan Bahan Baku Basi (Waste)', 'desc' => 'Pantau masa simpan bahan segar dan minimalkan pemborosan bahan baku yang tidak terpakai.'],
        ['icon' => 'trending-up', 'title' => 'Laporan Jam Sibuk (Rush Hour)', 'desc' => 'Ketahui jam kedai paling ramai pengunjung untuk mengatur stok olahan dan jadwal shift barista.'],
    ]
])
