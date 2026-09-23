@extends('public.partials.subpage_layout', [
    'title' => 'CRM & Loyalty',
    'category' => 'Omnichannel ERP',
    'badge' => 'Hubungan Pelanggan',
    'icon' => 'users',
    'headline' => 'Ubah Pembeli Pertama Menjadi Pelanggan Setia yang Loyal',
    'subtitle' => 'Rekam riwayat belanja, terapkan program poin loyalitas, kelompokkan segmen pelanggan, dan kirim pesan personalisasi lewat WhatsApp.',
    'features' => [
        ['icon' => 'user-check', 'title' => 'Database Pelanggan Terpusat', 'desc' => 'Simpan nomor WhatsApp, tanggal lahir, preferensi produk, dan total belanja setiap pelanggan.'],
        ['icon' => 'award', 'title' => 'Poin Loyalitas & Peringkat Member', 'desc' => 'Atur level membership (Silver, Gold, Platinum) dengan cashback poin dan diskon eksklusif.'],
        ['icon' => 'send', 'title' => 'WhatsApp Broadcast Tersegmentasi', 'desc' => 'Kirim promo gajian, voucher ulang tahun, atau pengingat servis ke segmen pelanggan tertentu.'],
        ['icon' => 'heart', 'title' => 'Analisis RFM (Recency, Frequency, Value)', 'desc' => 'Ketahui pelanggan VIP yang paling sering belanja serta pelanggan yang berisiko kabur (churn).'],
        ['icon' => 'message-square', 'title' => 'Riwayat Komunikasi & Catatan Khusus', 'desc' => 'Catat alergi makanan pelanggan F&B atau histori suku cadang kendaraan di bengkel Anda.'],
        ['icon' => 'gift', 'title' => 'Voucher Promo & Kupon Diskon', 'desc' => 'Buat kupon potongan harga dengan batas pemakaian, tanggal aktif, dan minimal pembelanjaan.'],
    ]
])
