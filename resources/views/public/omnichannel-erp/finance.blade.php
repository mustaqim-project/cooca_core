@extends('public.partials.subpage_layout', [
    'title' => 'Finance & Cash Flow',
    'category' => 'Omnichannel ERP',
    'badge' => 'Manajemen Keuangan',
    'icon' => 'wallet',
    'headline' => 'Kendalikan Arus Kas, Bon Hutang, & Piutang Bisnis Anda',
    'subtitle' => 'Pantau mutasi kas masuk dan keluar secara akurat. Jangan biarkan bon hutang pelanggan hilang atau tagihan supplier terlambat dibayar.',
    'features' => [
        ['icon' => 'credit-card', 'title' => 'Multi-Akun Kas & Bank', 'desc' => 'Pisahkan saldo laci kasir, rekening bank operasional, e-wallet, dan petty cash dalam satu dasbor.'],
        ['icon' => 'file-clock', 'title' => 'Monitoring Piutang & Umur Bon', 'desc' => 'Daftar piutang pelanggan lengkap dengan tanggal jatuh tempo dan pengingat tagihan WhatsApp otomatis.'],
        ['icon' => 'receipt', 'title' => 'Manajemen Hutang Dagang (AP)', 'desc' => 'Kelola jadwal pembayaran ke pemasok dan supplier bahan baku untuk menjaga reputasi kredit bisnis.'],
        ['icon' => 'pie-chart', 'title' => 'Kategori Biaya Operasional (OPEX)', 'desc' => 'Klasifikasikan biaya sewa, listrik, air, gaji, kemasan, dan bahan penolong secara teratur.'],
        ['icon' => 'arrow-down-circle', 'title' => 'Pencatatan Biaya Cepat Kasir', 'desc' => 'Kasir dapat mencatat pengeluaran mendadak toko (misal es batu, bensin kurir) dengan validasi struk.'],
        ['icon' => 'scale', 'title' => 'Rekonsiliasi Bank Otomatis', 'desc' => 'Cocokkan mutasi rekening koran bank dengan catatan transaksi sistem secara mudah.'],
    ]
])
