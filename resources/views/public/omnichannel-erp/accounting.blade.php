@extends('public.partials.subpage_layout', [
    'title' => 'Accounting & Jurnal Otomatis',
    'category' => 'Omnichannel ERP',
    'badge' => 'Pembukuan Standar SAK EMKM',
    'icon' => 'book-open',
    'headline' => 'Pembukuan Standar Tanpa Butuh Ahli Akuntansi',
    'subtitle' => 'Setiap transaksi penjualan, pembelian stok, dan pengeluaran beban otomatis membentuk jurnal debit-kredit ganda. Laporan Laba Rugi dan Neraca tersaji instan.',
    'features' => [
        ['icon' => 'zap', 'title' => 'Auto-Posting Jurnal Transaksi', 'desc' => 'Tidak perlu input manual debit kredit. Sistem otomatis menjurnal setiap kali kasir menyelesaikan transaksi.'],
        ['icon' => 'file-spreadsheet', 'title' => 'Laporan Laba Rugi (P&L) Real-Time', 'desc' => 'Ketahui laba kotor, beban operasional, dan laba bersih usaha Anda setiap hari, minggu, atau bulan.'],
        ['icon' => 'scale', 'title' => 'Neraca Keuangan Seimbang (Balance Sheet)', 'desc' => 'Pantau total aktiva aset lancar/tetap, kewajiban hutang, dan modal ekuitas pemilik usaha secara seimbang.'],
        ['icon' => 'list-tree', 'title' => 'Bagan Akun (Chart of Accounts - COA)', 'desc' => 'Struktur bagan akun standar Indonesia yang siap pakai atau disesuaikan dengan kebutuhan akuntan Anda.'],
        ['icon' => 'landmark', 'title' => 'Buku Besar & Neraca Saldo', 'desc' => 'Rincian pergerakan setiap akun kas, bank, persediaan, piutang, dan modal dalam format rapi.'],
        ['icon' => 'file-check', 'title' => 'Kesiapan Pajak & Audit Finansial', 'desc' => 'Data pembukuan terstruktur memudahkan pelaporan SPT tahunan dan perhitungan PPh final UMKM.'],
    ]
])
