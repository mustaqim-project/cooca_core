@extends('public.partials.subpage_layout', [
    'title' => 'HRM & Payroll',
    'category' => 'Omnichannel ERP',
    'badge' => 'Manajemen Tim & Gaji',
    'icon' => 'user-cog',
    'headline' => 'Kelola Absensi Karyawan, Shift Kerja, & Slip Gaji Digital',
    'subtitle' => 'Otomasi perhitungan gaji (payroll), uang lembur, tunjangan, komisi kasir/teknisi, dan kirim slip gaji ber-token aman langsung ke WhatsApp staf.',
    'features' => [
        ['icon' => 'calendar-clock', 'title' => 'Jadwal Shift & Jam Kerja Toko', 'desc' => 'Atur rotasi shift kasir, barista, teknisi, dan staf gudang dengan pembagian jam kerja fleksibel.'],
        ['icon' => 'fingerprint', 'title' => 'Absensi GPS & Kamera Smartphone', 'desc' => 'Karyawan absen masuk dan pulang melalui smartphone dengan verifikasi geolokasi toko.'],
        ['icon' => 'receipt-cent', 'title' => 'Perhitungan Gaji & Lembur Otomatis', 'desc' => 'Perhitungan gaji pokok, tunjangan kehadiran, potongan keterlambatan, dan lembur otomatis terakumulasi.'],
        ['icon' => 'percent', 'title' => 'Komisi Penjualan & Insentif Kerja', 'desc' => 'Hitung bagi hasil atau komisi per porsi, per servis motor, atau per transaksi secara transparan.'],
        ['icon' => 'file-lock', 'title' => 'Slip Gaji Digital Token WhatsApp', 'desc' => 'Staf dapat melihat dan mengunduh rincian gaji (take home pay) melalui tautan enkripsi unik.'],
        ['icon' => 'users-round', 'title' => 'Data Karyawan & Kontrak Kerja', 'desc' => 'Penyimpanan arsip identitas KTP, nomor rekening bank, dan tanggal mulai bekerja yang rapi.'],
    ]
])
