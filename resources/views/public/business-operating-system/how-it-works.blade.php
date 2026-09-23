@extends('public.partials.subpage_layout', [
    'title' => 'How It Works',
    'category' => 'Business Operating System',
    'badge' => 'Alur & Mekanisme',
    'icon' => 'git-branch',
    'headline' => 'Bagaimana COOCA Bekerja Mengotomasi Bisnis Anda',
    'subtitle' => 'Pelajari bagaimana COOCA menyederhanakan siklus bisnis dari hulu ke hilir hanya dalam beberapa langkah praktis tanpa kurva belajar yang rumit.',
    'features' => [
        [
            'icon' => 'user-plus',
            'title' => '1. Registrasi & Setup Profil Bisnis',
            'desc' => 'Daftar dalam 2 menit, tentukan jenis industri (F&B, Retail, Bengkel, dll), dan sistem akan menyesuaikan modul secara otomatis.'
        ],
        [
            'icon' => 'link-2',
            'title' => '2. Hubungkan Saluran Penjualan',
            'desc' => 'Sinkronisasikan WhatsApp Business, toko online, marketplace, dan terminal kasir fisik ke hub omnichannel COOCA.'
        ],
        [
            'icon' => 'refresh-cw',
            'title' => '3. Transaksi & Pemotongan Stok Otomatis',
            'desc' => 'Setiap order yang terjadi di channel mana pun langsung memotong stok gudang dan memperbarui buku kas secara live.'
        ],
        [
            'icon' => 'book-open',
            'title' => '4. Jurnal Keuangan Otomatis Terbit',
            'desc' => 'Tanpa perlu ahli akuntansi, COOCA otomatis membuat jurnal debit/kredit, laporan laba rugi, dan neraca keuangan.'
        ],
        [
            'icon' => 'send',
            'title' => '5. Notifikasi & Retensi Pelanggan',
            'desc' => 'Kirim struk digital, update status resi pesanan, dan pengingat servis otomatis ke WhatsApp pelanggan.'
        ],
        [
            'icon' => 'trending-up',
            'title' => '6. Analisis & Ambil Keputusan Berbasis Data',
            'desc' => 'Pantau performa cabang, produk terlaris, dan proyeksi kas melalui grafik dasbor visual yang mudah dimengerti.'
        ]
    ]
])
