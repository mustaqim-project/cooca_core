<?php

declare(strict_types=1);

return [
    // Modal 1: Quick Expense
    'expense' => [
        'title' => 'Catat Pengeluaran Cepat',
        'subtitle' => 'Jurnal otomatis operasional bisnis',
        'name_label' => 'Nama / Keterangan Biaya *',
        'name_placeholder' => 'Contoh: Gas Elpiji 3kg, Plastik Kresek',
        'amount_label' => 'Nominal (Rp) *',
        'amount_placeholder' => '25.000',
        'payment_method_label' => 'Metode Bayar',
        'method_cash' => 'Kas Tunai (Laci)',
        'method_bank' => 'Transfer Bank',
        'method_qris' => 'QRIS / e-Wallet',
        'category_label' => 'Kategori Biaya',
        'cat_operational' => 'Operasional Toko',
        'cat_consumables' => 'Bahan Habis Pakai (Plastik/Kemasan)',
        'cat_utilities' => 'Listrik, Air & Gas',
        'cat_logistics' => 'Transportasi & Logistik',
        'cat_other' => 'Lainnya',
        'supervisor_pin_label' => 'PIN Supervisor (Otorisasi Pengeluaran) *',
        'supervisor_pin_hint' => '≥ Rp 500.000',
        'supervisor_pin_placeholder' => 'Masukkan 6-digit PIN',
        'submit_btn' => 'Simpan Pengeluaran',
        'success_msg' => 'Pengeluaran kas berhasil dicatat dan dibukukan.',
        'error_msg' => 'Gagal mencatat pengeluaran.',
        'pin_required_msg' => 'Pengeluaran ≥ Rp 500.000 memerlukan otorisasi PIN Supervisor.',
        'pin_invalid_msg' => 'PIN Supervisor salah atau tidak memiliki wewenang otorisasi.',
    ],

    // Modal 2: Quick Stock In
    'stock_in' => [
        'title' => 'Beli Stok Masuk Cepat',
        'subtitle' => 'Tambah persediaan & valuasi aset',
        'material_label' => 'Bahan Baku / Produk *',
        'select_material' => '-- Pilih Bahan Baku --',
        'loading_materials' => 'Memuat daftar bahan baku...',
        'qty_label' => 'Jumlah Masuk *',
        'qty_placeholder' => '10',
        'unit_cost_label' => 'Harga Beli / Satuan (Rp) *',
        'unit_cost_placeholder' => '15.000',
        'supplier_label' => 'Nama Pemasok / Toko Beli',
        'supplier_placeholder' => 'Contoh: Pasar Induk, Toko Bahan Kue Maju',
        'submit_btn' => 'Tambah Stok Masuk',
        'success_msg' => 'Stok masuk berhasil dicatat dan HPP telah disesuaikan.',
        'error_msg' => 'Gagal mencatat stok masuk.',
    ],

    // Modal 3: Quick Material Creation
    'material' => [
        'title' => 'Tambah Bahan Baku Cepat',
        'subtitle' => 'Daftarkan bahan baku baru tanpa pindah layar',
        'name_label' => 'Nama Bahan Baku *',
        'name_placeholder' => 'Contoh: Tepung Terigu Segitiga Biru',
        'cost_label' => 'Harga Beli Dasar (Rp) *',
        'cost_placeholder' => '12.000',
        'unit_label' => 'Satuan Ukur',
        'select_unit' => 'Pilih Satuan',
        'submit_btn' => 'Tambah Bahan',
        'success_msg' => 'Bahan baku baru berhasil didaftarkan.',
        'error_msg' => 'Gagal mendaftarkan bahan baku.',
    ],

    // Modal 4: Mobile Action Sheet
    'sheet' => [
        'title' => 'Aksi Cepat Instan',
        'expense_title' => 'Catat Beban',
        'expense_desc' => 'Biaya operasional',
        'stock_title' => 'Beli Stok',
        'stock_desc' => 'Tambah persediaan',
        'material_title' => 'Bahan Baku',
        'material_desc' => 'Master bahan resep',
        'hpp_title' => 'Hitung HPP',
        'hpp_desc' => '3-Pilar harga jual',
        'kds_title' => 'Kitchen (KDS)',
        'kds_desc' => 'Pesanan dapur live',
        'tables_title' => 'Meja & QR',
        'tables_desc' => 'Dine-in self order',
        'services_title' => 'Servis & SPK',
        'services_desc' => 'Pekerjaan bengkel',
    ],
];
