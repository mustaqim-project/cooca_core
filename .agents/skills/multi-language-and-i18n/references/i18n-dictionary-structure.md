# Struktur Standar Kamus Modular Multi-Bahasa (`lang/id/` & `lang/en/`)

Dokumen ini memuat arsitektur, konvensi penamaan key, dan contoh isi kamus dwibahasa untuk sistem COOCA ERP & POS v2.0 yang mencakup **seluruh spektrum aplikasi secara Full-Stack (Frontend UI, Backend Flash Messages, Domain Exceptions, Validasi Request, Respon JSON API, Notifikasi WhatsApp/Email, dan Audit Logs)**.

---

## 📂 1. Peta Lengkap Berkas Kamus Terjemahan (Frontend & Backend)

Semua berkas kamus disimpan di direktori `lang/id/` (Bahasa Indonesia) dan `lang/en/` (English):

| Nama File | Domain & Ruang Lingkup | Area Penggunaan | Contoh Isi Key |
|---|---|---|---|
| `common.php` | Tombol aksi global, status badge, pesan dialog konfirmasi, pagination, label form universal | **Frontend & Backend** | `save`, `cancel`, `delete`, `edit`, `search`, `status_active`, `confirm_delete_title` |
| `messages.php` | Flash session alerts (success, error, warning, info) pada Controller & Action | **Backend** (Redirect / Web) | `saved_successfully`, `deleted_successfully`, `status_updated`, `import_completed` |
| `exceptions.php` | Domain business exceptions, kegagalan logic bisnis, pelanggaran rule/guardrails | **Backend** (Domain & Service) | `stock.insufficient`, `tenant.unauthorized_access`, `auth.pin_locked`, `plan.quota_exceeded` |
| `notifications.php` | Template pesan WhatsApp Cloud API, Email HTML subject & body, In-App Notification Center | **Backend** (Jobs, Events, Queues) | `wa.order_invoice`, `wa.debt_reminder`, `email.welcome_owner`, `email.low_stock_digest` |
| `validation.php` | Pesan error validasi form request & custom attributes | **Backend** (FormRequest) | `required`, `numeric`, `min`, `max`, `custom.table_number.required` |
| `audit.php` | Deskripsi aksi & peristiwa pada tabel `audit_logs` | **Backend** (Audit Trail) | `pos.drawer_opened_no_sale`, `products.price_changed`, `users.role_modified` |
| `pos.php` | Terminal kasir, riwayat pesanan, meja resto, KDS dapur, tutup shift kasir, laci kas | **Frontend & Backend** | `terminal_title`, `open_register`, `blind_cash_count`, `table_management`, `fire_ticket`, `split_bill` |
| `inventory.php` | Katalog produk, varian, resep BOM, stok gudang, surat penerimaan barang (GR), supplier, opname | **Frontend & Backend** | `product_list`, `recipe_bom`, `warehouse_transfer`, `stock_opname`, `low_stock_alert` |
| `finance.php` | Buku kas & bank, auto-journal, laba rugi, neraca, pengeluaran beban, pajak PPN/PB1 | **Frontend & Backend** | `cash_book`, `general_ledger`, `income_statement`, `expense_modal`, `tax_simulator` |
| `settings.php` | Profil bisnis, printer thermal, format nota, integrasi WA Meta API, kurir, hak akses | **Frontend & Backend** | `business_profile`, `printer_settings`, `receipt_format`, `whatsapp_gateway`, `role_permissions` |
| `storefront.php` | Toko online publik, keranjang belanja, tracking resi, verifikasi bukti transfer | **Frontend & Backend** | `cart_title`, `checkout_button`, `shipping_estimate`, `payment_proof_upload`, `order_tracking` |
| `auth.php` | Autentikasi, login, OTP WhatsApp, reset password, otorisasi supervisor PIN kasir | **Frontend & Backend** | `login_title`, `supervisor_pin_prompt`, `invalid_pin`, `account_locked`, `otp_sent` |

---

## 🛡️ 2. Standar Backend: Domain Exceptions & Flash Messages

### A. Berkas `exceptions.php` (Penanganan Kegagalan Logika Bisnis)

Seluruh Exception class di `app/Domain/*/Exceptions/` **DILARANG** melempar string mentah. Wajib menggunakan helper `__('exceptions.key', [...])`.

#### `lang/id/exceptions.php`:
```php
<?php

return [
    'stock' => [
        'insufficient'       => 'Stok untuk barang ":item" tidak mencukupi di gudang :warehouse. Sisa: :available, diminta: :requested.',
        'locked_in_transit'  => 'Stok barang ":item" sedang terkunci dalam proses pengiriman antar-gudang (Surat Jalan #:do_number).',
        'negative_not_allowed' => 'Penyesuaian stok tidak dapat menyebabkan kuantitas akhir menjadi minus (:qty).',
    ],
    'pos' => [
        'shift_not_opened'   => 'Terminal kasir belum dapat memproses transaksi karena shift kasir belum dibuka.',
        'already_closed'     => 'Shift kasir ini telah ditutup sebelumnya pada :time.',
        'pin_locked'         => 'Otorisasi Supervisor terkunci akibat 5 kali salah PIN. Silakan tunggu :minutes menit.',
        'invalid_pin'        => 'PIN Supervisor yang Anda masukkan salah. Sisa percobaan: :remaining_attempts.',
        'cannot_void_settled'=> 'Transaksi nota #:number tidak dapat dibatalkan karena pembukuan kas telah diselesaikan (*settled*).',
    ],
    'tenant' => [
        'unauthorized_access'=> 'Anda tidak memiliki hak akses untuk melihat atau memodifikasi data milik tenant lain (IDOR Shield).',
        'quota_exceeded'     => 'Batas kuota produk untuk paket langganan Anda (:plan) telah tercapai (:max produk). Silakan upgrade paket untuk menambah produk baru.',
    ],
    'finance' => [
        'unbalanced_journal' => 'Jurnal akuntansi tidak seimbang (*unbalanced*). Total Debit (Rp :debit) harus sama dengan Total Kredit (Rp :credit).',
        'account_locked'     => 'Akun kas/bank ":account" sedang ditutup untuk periode audit keuangan.',
    ],
];
```

#### `lang/en/exceptions.php`:
```php
<?php

return [
    'stock' => [
        'insufficient'       => 'Insufficient stock for ":item" at :warehouse. Available: :available, requested: :requested.',
        'locked_in_transit'  => 'Stock for ":item" is currently locked in inter-warehouse transit (Transfer Note #:do_number).',
        'negative_not_allowed' => 'Stock adjustment cannot result in negative final quantity (:qty).',
    ],
    'pos' => [
        'shift_not_opened'   => 'POS terminal cannot process transactions because cashier shift is not opened yet.',
        'already_closed'     => 'This cashier shift was already closed at :time.',
        'pin_locked'         => 'Supervisor authorization is locked due to 5 failed attempts. Please wait :minutes minutes.',
        'invalid_pin'        => 'Incorrect Supervisor PIN. Remaining attempts: :remaining_attempts.',
        'cannot_void_settled'=> 'Transaction receipt #:number cannot be voided because cash settlement is already finalized.',
    ],
    'tenant' => [
        'unauthorized_access'=> 'You do not have permission to access or modify other tenant data (IDOR Shield).',
        'quota_exceeded'     => 'Product limit for your subscription plan (:plan) has been reached (:max items). Please upgrade to add more products.',
    ],
    'finance' => [
        'unbalanced_journal' => 'Accounting journal entry is unbalanced. Total Debit (IDR :debit) must equal Total Credit (IDR :credit).',
        'account_locked'     => 'Cash/bank account ":account" is locked for financial audit period.',
    ],
];
```

---

### B. Berkas `messages.php` (Flash Alerts & JSON API Responses)

#### `lang/id/messages.php`:
```php
<?php

return [
    'created' => ':entity berhasil ditambahkan ke dalam sistem.',
    'updated' => ':entity berhasil diperbarui.',
    'deleted' => ':entity berhasil dihapus. Riwayat masa lalu tetap tersimpan aman.',
    'restored'=> ':entity berhasil dipulihkan kembali.',
    
    'pos' => [
        'order_placed'       => 'Pesanan nota #:number berhasil disimpan dan dicetak.',
        'shift_closed'       => 'Shift kasir berhasil ditutup. Laporan ringkasan kas telah dibuat.',
        'drawer_opened'      => 'Laci kas berhasil dibuka secara manual (Tercatat di Audit Log).',
        'table_status_reset' => 'Sesi meja #:number telah diselesaikan dan meja siap digunakan kembali.',
    ],
    
    'inventory' => [
        'stock_adjusted'     => 'Penyesuaian stok untuk :count barang berhasil dibukukan.',
        'transfer_sent'      => 'Surat jalan pengiriman antar-gudang #:number berhasil diterbitkan.',
        'transfer_received'  => 'Penerimaan barang dari gudang :origin berhasil diverifikasi.',
    ],
];
```

#### `lang/en/messages.php`:
```php
<?php

return [
    'created' => ':entity has been successfully added to the system.',
    'updated' => ':entity has been successfully updated.',
    'deleted' => ':entity has been successfully deleted. Past transaction records remain safely archived.',
    'restored'=> ':entity has been successfully restored.',
    
    'pos' => [
        'order_placed'       => 'Order receipt #:number has been successfully saved and printed.',
        'shift_closed'       => 'Cashier shift has been closed. Cash summary report generated.',
        'drawer_opened'      => 'Cash drawer manually opened (Recorded in Audit Log).',
        'table_status_reset' => 'Table #:number session completed and table is ready for next guests.',
    ],
    
    'inventory' => [
        'stock_adjusted'     => 'Stock adjustment for :count items has been successfully posted.',
        'transfer_sent'      => 'Inter-warehouse transfer note #:number has been issued.',
        'transfer_received'  => 'Goods receipt from warehouse :origin has been verified.',
    ],
];
```

---

## 📲 3. Standar Notifikasi: WhatsApp Cloud API & Email HTML

### Berkas `notifications.php`

Pesan notifikasi otomatis ke pelanggan dan pemilik usaha wajib terisolasi dalam kamus:

#### `lang/id/notifications.php`:
```php
<?php

return [
    'whatsapp' => [
        'pos_receipt' => "Halo *{{name}}*,\nTerima kasih telah berbelanja di *{{business}}*!\n\nNota: #{{order_number}}\nTotal: {{total}}\nMetode: {{payment_method}}\n\nUnduh nota digital: {{link}}\nSemoga hari Anda menyenangkan!",
        'debt_reminder' => "Yth. *{{customer}}*,\nKami menginformasikan bahwa tagihan faktur *#{{invoice_number}}* sebesar *{{amount}}* akan jatuh tempo pada *{{due_date}}*.\n\nDetail pembayaran: {{link}}\nTerima kasih atas kerja samanya.",
        'low_stock_owner' => "⚠️ *Peringatan Stok Menipis*\nOutlet: {{outlet}}\nBarang: {{item}}\nSisa stok fisik: *{{remaining}} {{unit}}* (Batas minimum: {{threshold}} {{unit}}).\nSegera lakukan PO pengadaan!",
    ],
    'email' => [
        'daily_digest_subject' => 'Ringkasan Laporan Harian - :date (:business)',
        'invoice_subject'      => 'Faktur Penagihan #:number dari :business',
    ],
];
```

#### `lang/en/notifications.php`:
```php
<?php

return [
    'whatsapp' => [
        'pos_receipt' => "Hello *{{name}}*,\nThank you for shopping at *{{business}}*!\n\nReceipt: #{{order_number}}\nTotal: {{total}}\nPayment: {{payment_method}}\n\nDownload e-receipt: {{link}}\nHave a great day!",
        'debt_reminder' => "Dear *{{customer}}*,\nThis is a friendly reminder that invoice *#{{invoice_number}}* for *{{amount}}* is due on *{{due_date}}*.\n\nPayment details: {{link}}\nThank you for your business.",
        'low_stock_owner' => "⚠️ *Low Stock Warning*\nOutlet: {{outlet}}\nItem: {{item}}\nRemaining stock: *{{remaining}} {{unit}}* (Minimum threshold: {{threshold}} {{unit}}).\nPlease issue a purchase order soon!",
    ],
    'email' => [
        'daily_digest_subject' => 'Daily Business Summary - :date (:business)',
        'invoice_subject'      => 'Invoice #:number from :business',
    ],
];
```

---

## 🔤 4. Konvensi Penamaan Key & Interpolasi Parameter

1. **Struktur Parameter Dinamis (`:param`):**  
   Selalu gunakan placeholder bertanda titik dua: `:item`, `:count`, `:number`, `:amount`, `:plan`, `:time`, `:warehouse`.  
   *Alasan:* Struktur tata bahasa Inggris dan Indonesia sering memiliki urutan kata terbalik (SPOK vs Modifier-Noun). Placeholder memungkinkan susunan kalimat tetap alami di kedua bahasa.
2. **Kategori Domain Backend yang Wajib Ada:**
   * `exceptions.*` → Untuk seluruh pesan `throw new Exception(...)`
   * `messages.*` → Untuk seluruh pesan `->with('success'|'error', ...)`
   * `notifications.*` → Untuk seluruh template WA & Email
   * `validation.*` → Untuk seluruh form request validation
   * `audit.*` → Untuk seluruh aksi log sistem
