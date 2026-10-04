<?php

declare(strict_types=1);

return [
    'billing' => [
        'payment_approved_wa' => "Halo *{{owner}}*,\nPembayaran langganan Cooca Anda untuk paket *{{plan}}* senilai *{{amount}}* telah diverifikasi otomatis!\n\nMasa aktif: s/d {{expires_at}}\nFaktur resmi: {{invoice_url}}\n\nTerima kasih atas kepercayaannya!",
        'payment_expired_wa' => "Yth. *{{owner}}*,\nPesanan langganan #{{order_number}} sebesar {{amount}} telah kedaluwarsa. Silakan lakukan pemesanan ulang di Cooca jika masih membutuhkan upgrade kuota.",
        'grace_period_wa' => "⚠️ *Pemberitahuan Masa Tenggang*\nBisnis: {{business}}\nLangganan Anda telah melewati tempo. Kasir POS tetap aktif normal hingga 3 hari ke depan. Mohon selesaikan tagihan untuk mencegah pembatasan fitur.",
    ],
    'whatsapp' => [
        'pos_receipt' => "Halo *{{name}}*,\nTerima kasih telah berbelanja di *{{business}}*!\n\nNota: #{{order_number}}\nTotal: {{total}}\nMetode: {{payment_method}}\n\nUnduh nota digital: {{link}}\nSemoga hari Anda menyenangkan!",
        'debt_reminder' => "Yth. *{{customer}}*,\nKami menginformasikan bahwa tagihan faktur *#{{invoice_number}}* sebesar *{{amount}}* akan jatuh tempo pada *{{due_date}}*.\n\nDetail pembayaran: {{link}}\nTerima kasih atas kerja samanya.",
        'low_stock_owner' => "⚠️ *Peringatan Stok Menipis*\nOutlet: {{outlet}}\nBarang: {{item}}\nSisa stok fisik: *{{remaining}} {{unit}}* (Batas minimum: {{threshold}} {{unit}}).\nSegera lakukan PO pengadaan!",
    ],
    'email' => [
        'daily_digest_subject' => 'Ringkasan Laporan Harian - :date (:business)',
        'invoice_subject' => 'Faktur Penagihan #:number dari :business',
    ],
];
