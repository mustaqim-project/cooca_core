@extends('public.partials.subpage_layout', [
    'title' => 'Solusi Bengkel & Otomotif',
    'category' => 'Solutions',
    'badge' => 'Bengkel & Servis',
    'icon' => 'wrench',
    'headline' => 'Solusi Bengkel Modern: Surat Perintah Kerja (SPK), Sparepart, & Komisi Mekanik',
    'subtitle' => 'Tinggalkan nota kertas minyak. Catat nomor polisi kendaraan, kelola stok sparepart/oli, pantau progres servis mekanik, dan kirim update via WhatsApp.',
    'features' => [
        ['icon' => 'file-text', 'title' => 'Work Order (SPK) Digital', 'desc' => 'Catat keluhan pelanggan, odometer KM, jenis servis, dan daftar suku cadang yang diganti.'],
        ['icon' => 'car', 'title' => 'Database Plat Nomor Kendaraan', 'desc' => 'Lacak histori servis kendaraan pelanggan sejak servis pertama kali hingga penggantian suku cadang terakhir.'],
        ['icon' => 'package', 'title' => 'Manajemen Suku Cadang & Oli', 'desc' => 'Stok sparepart otomatis terpotong saat mekanik mengambil barang dari gudang untuk servis.'],
        ['icon' => 'user-check', 'title' => 'Komisi Jasa Montir & Mekanik', 'desc' => 'Perhitungan upah bagi hasil atau insentif mekanik terhitung otomatis berdasarkan jenis pekerjaan.'],
        ['icon' => 'clock', 'title' => 'Pengingat Ganti Oli WhatsApp', 'desc' => 'Kirim pesan ramah otomatis ke WhatsApp pelanggan saat kendaraannya sudah waktunya servis rutin.'],
        ['icon' => 'receipt', 'title' => 'Estimasi Biaya & Nota Servis Rapi', 'desc' => 'Cetak penawaran biaya perbaikan sebelum pengerjaan dan rincian nota resmi setelah pengerjaan selesai.'],
    ]
])
