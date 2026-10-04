<?php

declare(strict_types=1);

return [
    'title' => 'Inventori & Stok',
    'header_title' => 'Manajemen Persediaan & Stok',
    'header_subtitle' => 'Kelola stok fisik produk, bahan baku, penyesuaian opname, dan histori mutasi kartu stok.',

    'breadcrumbs' => [
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventori',
        'stocks' => 'Stok Gudang',
        'movements' => 'Mutasi Stok',
        'adjustments' => 'Penyesuaian Stok',
        'transfers' => 'Transfer Antar-Gudang',
        'opname' => 'Stok Opname',
    ],

    'kpis' => [
        'total_sku' => 'Total SKU / Komoditas',
        'total_valuation' => 'Total Nilai Aset Stok',
        'low_stock_items' => 'Item Perlu Restock',
        'pending_approvals' => 'Menunggu Otorisasi',
    ],

    'tabs' => [
        'stocks' => 'Saldo Stok Aktual',
        'receipts' => 'Penerimaan Barang (GRN)',
        'movements' => 'Riwayat Mutasi Stok',
        'adjustments' => 'Penyesuaian & Opname',
        'transfers' => 'Transfer Antar-Gudang',
    ],

    'movement_types' => [
        'po_receipt' => 'Penerimaan PO (GRN)',
        'pos_sale' => 'Penjualan Kasir (POS)',
        'sales_order' => 'Pengiriman Pesanan B2B',
        'adjustment_in' => 'Koreksi Tambah Stok',
        'adjustment_out' => 'Koreksi Kurang Stok',
        'transfer_in' => 'Transfer Masuk',
        'transfer_out' => 'Transfer Keluar',
        'opname_variance' => 'Penyesuaian Stok Opname',
        'bom_production_in' => 'Hasil Produksi Jadi (BOM)',
        'bom_production_out' => 'Pemakaian Bahan Baku (BOM)',
        'return_vendor' => 'Retur Pembelian ke Vendor',
        'return_customer' => 'Retur Masuk dari Pelanggan',
    ],

    'reasons' => [
        'variance' => 'Selisih Opname Fisik Rutin',
        'damaged' => 'Barang Rusak / Cacat',
        'expired' => 'Barang Kadaluwarsa',
        'shrinkage' => 'Penyusutan / Tumpah',
        'sample' => 'Pemakaian Sampel / Promosi',
        'opening' => 'Saldo Awal Pembukuan',
        'production_loss' => 'Susut / Rusak Proses Produksi',
        'other' => 'Lainnya (Wajib Catatan)',
    ],

    'supervisor_pin_not_configured' => 'PIN Supervisor belum diatur oleh pemilik bisnis. Silakan atur PIN di Pengaturan Bisnis terlebih dahulu.',
    'supervisor_pin_shrinkage_required' => 'Penyesuaian pengurangan stok melebihi batas toleransi (kuantitas > 10 unit atau nilai > Rp 100.000). PIN Supervisor 6-digit wajib diisi dengan benar.',
    'quick_adjustment_success' => 'Penyesuaian stok cepat berhasil disimpan.',
    'quick_adjustment_pending' => 'Penyesuaian stok bernilai tinggi (Rp :amount) telah diajukan dan menunggu persetujuan Pemilik Usaha / Supervisor.',
    'adjustment_pending_approval' => 'Penyesuaian stok bernilai tinggi (Rp :amount) telah diajukan dan menunggu persetujuan Pemilik Usaha / Supervisor.',
    'adjustment_success' => 'Stok berhasil disesuaikan dan kartu stok telah diperbarui.',
    'adjustment_approved' => 'Pengajuan penyesuaian stok berhasil disetujui dan kartu stok telah diperbarui.',
    'adjustment_rejected' => 'Pengajuan penyesuaian stok telah ditolak.',
    'adjustment_not_found_or_processed' => 'Pengajuan penyesuaian stok tidak ditemukan atau sudah diproses sebelumnya.',
    'unauthorized_approval' => 'Hanya Pemilik Usaha atau Supervisor yang berwenang menyetujui penyesuaian stok bernilai tinggi.',
    'notes_other_min_length' => 'Untuk alasan "Lainnya", penjelasan catatan wajib diisi minimal 10 karakter.',
    'stock_transfer_success' => 'Transfer mutasi stok berhasil diproses.',
];
