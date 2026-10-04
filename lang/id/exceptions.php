<?php

declare(strict_types=1);

return [
    'billing' => [
        'upgrade_simulation_local_only' => 'Simulasi upgrade paket hanya diizinkan pada lingkungan pengembangan lokal atau pengujian.',
        'only_owner_recalculate_storage' => 'Hanya Owner atau pengelola billing yang dapat menghitung ulang storage.',
        'owner_not_found' => 'Owner akun tidak ditemukan. Tidak dapat menghitung ulang storage.',
        'only_owner_delete_storage' => 'Hanya Owner atau pengelola billing yang dapat menghapus berkas penyimpanan.',
        'storage_file_not_owned' => 'Akses ditolak. Berkas ini bukan milik bisnis yang sedang aktif.',
        'only_owner_order_subscription' => 'Hanya Owner atau pengelola billing yang dapat melakukan pemesanan paket langganan.',
        'order_not_found' => 'Pesanan tagihan tidak ditemukan.',
        'order_cannot_be_cancelled' => 'Pesanan tagihan dengan status saat ini tidak dapat dibatalkan.',
        'quota_exceeded' => 'Batas kuota :resource untuk paket langganan Anda (:plan) telah tercapai (:max :unit). Silakan upgrade paket untuk melanjutkan.',
        'unauthorized_tenant_access' => 'Anda tidak memiliki hak akses untuk melihat atau memodifikasi data langganan milik tenant lain (IDOR Shield).',
    ],
    'stock' => [
        'insufficient' => 'Stok untuk barang ":item" tidak mencukupi di gudang :warehouse. Sisa: :available, diminta: :requested.',
        'locked_in_transit' => 'Stok barang ":item" sedang terkunci dalam proses pengiriman antar-gudang (Surat Jalan #:do_number).',
        'negative_not_allowed' => 'Penyesuaian stok tidak dapat menyebabkan kuantitas akhir menjadi minus (:qty).',
    ],
    'pos' => [
        'shift_not_opened' => 'Terminal kasir belum dapat memproses transaksi karena shift kasir belum dibuka.',
        'already_closed' => 'Shift kasir ini telah ditutup sebelumnya pada :time.',
        'pin_locked' => 'Otorisasi Supervisor terkunci akibat 5 kali salah PIN. Silakan tunggu :minutes menit.',
        'invalid_pin' => 'PIN Supervisor yang Anda masukkan salah. Sisa percobaan: :remaining_attempts.',
        'cannot_void_settled' => 'Transaksi nota #:number tidak dapat dibatalkan karena pembukuan kas telah diselesaikan (*settled*).',
    ],
    'tenant' => [
        'unauthorized_access' => 'Anda tidak memiliki hak akses untuk melihat atau memodifikasi data milik tenant lain (IDOR Shield).',
        'quota_exceeded' => 'Batas kuota produk untuk paket langganan Anda (:plan) telah tercapai (:max produk). Silakan upgrade paket untuk menambah produk baru.',
    ],
    'finance' => [
        'unbalanced_journal' => 'Jurnal akuntansi tidak seimbang (*unbalanced*). Total Debit (Rp :debit) harus sama dengan Total Kredit (Rp :credit).',
        'account_locked' => 'Akun kas/bank ":account" sedang ditutup untuk periode audit keuangan.',
    ],
];
