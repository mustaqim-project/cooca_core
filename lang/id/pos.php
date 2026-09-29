<?php

declare(strict_types=1);

return [
    'supervisor_pin_not_configured' => 'PIN Supervisor belum diatur oleh pemilik bisnis. Silakan atur PIN 6-digit di Pengaturan Usaha terlebih dahulu.',
    'supervisor_pin_invalid' => 'PIN Supervisor salah atau otorisasi tidak valid.',
    'supervisor_pin_required' => 'PIN Supervisor wajib diisi.',
    'supervisor_pin_invalid_attempts' => 'PIN Supervisor salah. Sisa :remaining kesempatan sebelum terkunci.',
    'supervisor_auth_verified' => 'Otorisasi Supervisor Terverifikasi.',
    'order_voided_successfully' => 'Transaksi #:order_number berhasil dibatalkan (void).',
    'order_refunded_successfully' => 'Transaksi #:order_number berhasil direfund / diretur.',
    'partial_refund_successful' => 'Refund parsial #:order_number berhasil.',
    'void_reason_mandatory' => 'Alasan pembatalan (void) transaksi wajib diisi.',
    'refund_reason_mandatory' => 'Alasan pengembalian (refund) transaksi wajib diisi.',
    'order_already_paid' => 'Pesanan ini sudah berstatus lunas.',
    'missing_tripay_reference' => 'Pesanan tidak memiliki referensi pembayaran TriPay.',
    'tripay_sync_success' => 'Status pembayaran TriPay berhasil disinkronkan (:status).',
    'shift_opened' => 'Sesi shift kasir berhasil dibuka dengan modal awal :amount.',
    'shift_closed' => 'Sesi shift kasir berhasil ditutup. Laporan ringkasan kas siap dicetak.',
    'shift_already_open' => 'Anda masih memiliki sesi shift yang aktif di register ini.',
    'shift_not_open' => 'Anda belum membuka shift kasir. Silakan buka shift terlebih dahulu untuk bertransaksi.',
    'table_created' => 'Meja / Area resto berhasil ditambahkan.',
    'table_updated' => 'Informasi meja / area resto berhasil diperbarui.',
    'table_deleted' => 'Meja resto berhasil dihapus.',
    'table_status_changed' => 'Status ketersediaan meja berhasil diperbarui.',
    'kitchen_item_status_updated' => 'Status antrean dapur item :item diperbarui menjadi :status.',
    'kitchen_order_completed' => 'Seluruh pesanan dapur untuk meja/nota #:number telah selesai dimasak.',
];
