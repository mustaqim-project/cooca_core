<?php

declare(strict_types=1);

return [
    'created' => ':entity berhasil ditambahkan ke dalam sistem.',
    'updated' => ':entity berhasil diperbarui.',
    'deleted' => ':entity berhasil dihapus. Riwayat masa lalu tetap tersimpan aman.',
    'restored' => ':entity berhasil dipulihkan kembali.',

    'billing' => [
        'upgrade_success' => 'Selamat! Bisnis Anda kini aktif pada paket :cycle. Seluruh kuota transaksi, produk, dan token AI telah terbuka penuh.',
        'storage_recalculated_success' => "Kalkulasi storage bisnis ':business' selesai. File dipindai: :scanned | Baru ditambah: :added | Orphan dibersihkan: :cleaned | Digunakan bisnis ini: :used MB / :limit GB.",
        'storage_file_deleted_named' => "Berkas ':name' (:size MB) berhasil dihapus. Kapasitas penyimpanan bisnis Anda telah diperbarui.",
        'free_package_activated_success' => "Selamat! Paket promo ':name' (:days Hari Trial Pro) berhasil diaktifkan secara instan tanpa perlu transfer pembayaran.",
        'order_created_success' => 'Pesanan #:order berhasil dibuat. Silakan selesaikan pembayaran.',
        'upload_proof_unnecessary' => 'Pembayaran langganan diverifikasi otomatis secara instan oleh TriPay Payment Gateway. Anda tidak perlu mengunggah bukti bayar.',
        'order_cancelled_success' => 'Pesanan langganan berhasil dibatalkan.',
    ],

    'pos' => [
        'order_placed' => 'Pesanan nota #:number berhasil disimpan dan dicetak.',
        'shift_closed' => 'Shift kasir berhasil ditutup. Laporan ringkasan kas telah dibuat.',
        'drawer_opened' => 'Laci kas berhasil dibuka secara manual (Tercatat di Audit Log).',
        'table_status_reset' => 'Sesi meja #:number telah diselesaikan dan meja siap digunakan kembali.',
    ],

    'inventory' => [
        'stock_adjusted' => 'Penyesuaian stok untuk :count barang berhasil dibukukan.',
        'transfer_sent' => 'Surat jalan pengiriman antar-gudang #:number berhasil diterbitkan.',
        'transfer_received' => 'Penerimaan barang dari gudang :origin berhasil diverifikasi.',
    ],
];
