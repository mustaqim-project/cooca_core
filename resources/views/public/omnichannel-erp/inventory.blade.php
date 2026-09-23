@extends('public.partials.subpage_layout', [
    'title' => 'Inventory & Warehouse',
    'category' => 'Omnichannel ERP',
    'badge' => 'Manajemen Persediaan',
    'icon' => 'archive',
    'headline' => 'Stok Akurat, HPP Presisi, & Bebas Selisih Gudang',
    'subtitle' => 'Pantau persediaan bahan baku dan barang jadi secara real-time. Hitung HPP dengan metode Moving Average atau FIFO otomatis tanpa kalkulasi manual.',
    'features' => [
        ['icon' => 'package-check', 'title' => 'Stok Opname Digital Cepat', 'desc' => 'Lakukan stok opname periodik menggunakan pemindai barcode HP tanpa harus menutup gerai usaha.'],
        ['icon' => 'calculator', 'title' => 'Kalkulasi HPP Presisi Otomatis', 'desc' => 'HPP (Harga Pokok Penjualan) dihitung dinamis setiap ada pembelian baru dari pemasok.'],
        ['icon' => 'bell-ring', 'title' => 'Peringatan Stok Menipis & Kadaluarsa', 'desc' => 'Notifikasi otomatis saat stok mencapai batas minimum reorder point atau mendekati tanggal expired.'],
        ['icon' => 'layers', 'title' => 'Multi-Satuan & Konversi Otomatis', 'desc' => 'Beli dalam satuan dus/karton/karung, jual dalam renteng/pak/bungkus/gram dengan konversi otomatis.'],
        ['icon' => 'utensils', 'title' => 'Bill of Materials (Resep & Komposisi)', 'desc' => 'Cocok untuk F&B dan manufaktur. Penjualan 1 porsi otomatis memotong bahan baku sesuai resep.'],
        ['icon' => 'history', 'title' => 'Kartu Stok & Jejak Mutasi', 'desc' => 'Lacak riwayat keluar masuk setiap barang hingga satuan terkecil lengkap dengan nomor batch referensi.'],
    ]
])
