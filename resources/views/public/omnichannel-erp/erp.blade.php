@extends('public.partials.subpage_layout', [
    'title' => 'ERP Core',
    'category' => 'Omnichannel ERP',
    'badge' => 'Enterprise Resource Planning',
    'icon' => 'box',
    'headline' => 'ERP Modern untuk Skala UMKM Hingga Multi-Cabang',
    'subtitle' => 'Kelola seluruh sumber daya bisnis, rantai pasok, pengadaan, dan pergerakan aset dalam satu ekosistem terpadu tanpa kompleksitas ERP tradisional.',
    'features' => [
        ['icon' => 'building-2', 'title' => 'Manajemen Multi-Cabang', 'desc' => 'Kelola puluhan gerai atau gudang terpisah dalam satu akun induk dengan konsolidasi laporan otomatis.'],
        ['icon' => 'truck', 'title' => 'Supply Chain & Procurement', 'desc' => 'Otomasi Purchase Order (PO), approval bertingkat, dan pencatatan penerimaan barang dari vendor.'],
        ['icon' => 'git-pull-request', 'title' => 'Mutasi Stok Antar Cabang', 'desc' => 'Kirim dan terima barang antar gerai dengan verifikasi barcode dan status pengiriman real-time.'],
        ['icon' => 'shield', 'title' => 'Audit Trail & Log Aktivitas', 'desc' => 'Rekam setiap perubahan harga, void transaksi, dan mutasi barang dengan detail pelaku dan stempel waktu.'],
        ['icon' => 'file-text', 'title' => 'Manajemen Dokumen Bisnis', 'desc' => 'Cetak surat jalan, faktur pajak sederhana, delivery order, dan invoice profesional bertanda tangan digital.'],
        ['icon' => 'sliders', 'title' => 'Konfigurasi Alur Fleksibel', 'desc' => 'Sesuaikan alur otorisasi approval belanja barang sesuai batasan nominal kewenangan manajer.'],
    ]
])
