@extends('public.partials.subpage_layout', [
    'title' => 'Centralized Orders',
    'category' => 'Omnichannel',
    'badge' => 'Manajemen Pesanan Terpadu',
    'icon' => 'clipboard-list',
    'headline' => 'Kelola Seluruh Pesanan Masuk Tanpa Ada yang Terlewat',
    'subtitle' => 'Alur pesanan terstandarisasi mulai dari order dibuat, konfirmasi pembayaran, penyiapan gudang, pengemasan, hingga pengiriman kurir.',
    'features' => [
        ['icon' => 'check-square', 'title' => 'Status Pesanan Transparan', 'desc' => 'Status bertahap: Menunggu Bayar, Diproses, Dikirim, Selesai, atau Dibatalkan terpantau rapi.'],
        ['icon' => 'upload-cloud', 'title' => 'Verifikasi Bukti Transfer Cepat', 'desc' => 'Pelanggan dapat mengunggah bukti bayar secara mandiri dengan deteksi verifikasi admin yang mudah.'],
        ['icon' => 'truck', 'title' => 'Hitung Ongkir Ekspedisi Otomatis', 'desc' => 'Dukungan integrasi cek ongkir kurir reguler, kargo, instan motor, hingga kurir internal toko.'],
        ['icon' => 'box', 'title' => 'Picking & Packing Checklist', 'desc' => 'Daftar barang yang harus diambil staf gudang untuk meminimalisir kesalahan kirim barang.'],
        ['icon' => 'history', 'title' => 'Jejak Audit Pesanan & Pengembalian', 'desc' => 'Kelola retur dan penukaran barang secara akuntabel dengan pencatatan alasan yang valid.'],
        ['icon' => 'bell', 'title' => 'Peringatan Pesanan Prioritas', 'desc' => 'Tanda visual untuk pesanan mendesak (same day/instant delivery) agar diproses lebih awal.'],
    ]
])
