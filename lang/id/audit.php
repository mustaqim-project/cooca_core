<?php

declare(strict_types=1);

return [
    'billing' => [
        'plan_upgraded' => 'Langganan bisnis ditingkatkan ke paket :plan (:cycle).',
        'payment_approved' => 'Pembayaran tagihan order #:order diverifikasi lunas.',
        'payment_rejected' => 'Pembayaran tagihan order #:order ditolak atau kedaluwarsa.',
        'storage_recalculated' => 'Kalkulasi ulang kapasitas penyimpanan storage disk dilakukan.',
        'storage_file_deleted' => 'Berkas storage :file (:size MB) dihapus dari server.',
        'free_promo_activated' => 'Paket promo trial gratis :package diaktifkan.',
    ],
    'pos' => [
        'drawer_opened_no_sale' => 'Laci kas dibuka manual tanpa transaksi penjualan.',
        'order_voided' => 'Nota kasir #:number dibatalkan oleh supervisor.',
        'shift_opened' => 'Shift kasir dibuka dengan modal awal :amount.',
        'shift_closed' => 'Shift kasir ditutup dengan total penjualan :amount.',
    ],
    'products' => [
        'price_changed' => 'Harga jual produk :product diubah dari :old menjadi :new.',
    ],
    'users' => [
        'role_modified' => 'Hak akses peran pengguna :user diubah menjadi :role.',
    ],
];
