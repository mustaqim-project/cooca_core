@extends('public.partials.subpage_layout', [
    'title' => 'Panduan & Dokumentasi Sistem',
    'category' => 'Resources',
    'badge' => 'Pusat Panduan Praktis',
    'icon' => 'file-text',
    'headline' => 'Panduan Langkah Demi Langkah Penggunaan Sistem COOCA',
    'subtitle' => 'Tutorial visual, panduan setup awal gerai, konfigurasi printer Bluetooth, dan tips praktis memaksimalkan seluruh fitur aplikasi.',
    'features' => [
        ['icon' => 'play-circle', 'title' => 'Video Tutorial Interaktif', 'desc' => 'Tonton video singkat berdurasi 2-3 menit yang mengajarkan alur kasir, input stok, hingga cetak laporan.'],
        ['icon' => 'bluetooth', 'title' => 'Setup Printer Struk Thermal', 'desc' => 'Panduan pairing koneksi Bluetooth printer mini 58mm/80mm di Android, iOS, Windows, dan MacOS.'],
        ['icon' => 'qr-code', 'title' => 'Panduan QR Table Ordering', 'desc' => 'Cara mengunduh dan mencetak stand barcode QR meja kafe untuk dipasang di meja pelanggan.'],
        ['icon' => 'upload', 'title' => 'Impor Data Massal Excel', 'desc' => 'Tutorial memindahkan ribuan data produk, harga jual, dan stok awal dari spreadsheet Excel lama Anda.'],
        ['icon' => 'users', 'title' => 'Pengaturan Hak Akses Karyawan', 'desc' => 'Langkah membatasi wewenang staf: kasir hanya bisa jualan, gudang hanya bisa terima stok.'],
        ['icon' => 'shield', 'title' => 'Pencadangan & Keamanan Akun', 'desc' => 'Tips menjaga keamanan sandi, verifikasi nomor WhatsApp bisnis, dan ekspor berkala database toko.'],
    ]
])
