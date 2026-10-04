# Struktur Standar Kamus Modular Multi-Bahasa (`lang/id/` & `lang/en/`)

Dokumen ini memuat arsitektur, konvensi penamaan key, dan contoh isi kamus dwibahasa untuk sistem COOCA ERP & POS v2.0 yang mencakup **100% seluruh spektrum teks aplikasi secara menyeluruh (Page Titles, Breadcrumbs, Headers, Forms, Dev Notices, Validation Headings, Buttons, Modals, Tables, Backend Messages, Domain Exceptions, Notifikasi WhatsApp/Email, dan Audit Logs)**.

---

## 📂 1. Peta 12 Berkas Kamus Terjemahan (Full-Spectrum Coverage)

Semua berkas kamus disimpan di direktori `lang/id/` (Bahasa Indonesia) dan `lang/en/` (English):

| Nama File | Domain & Ruang Lingkup | Cakupan Elemen Teks | Contoh Key |
|---|---|---|---|
| `common.php` | Tombol aksi global, status badge, pagination, dialog konfirmasi | Tombol Simpan, Batal, Hapus, Edit, Cari, Status Aktif/Draf, Pagination | `save`, `cancel`, `delete`, `edit`, `search`, `status_active`, `showing_records` |
| `nav.php` | Navigasi, Breadcrumb, Overline Menu, Sidebar | Breadcrumb trail, Category kicker, Mobile bottom bar items, Menu grouping | `dashboard`, `sales`, `inventory_hub`, `pos_history`, `settings_group` |
| `auth.php` | Autentikasi, Login, Register, OTP WA, Password, Dev Notice | Form OTP, Judul "Verifikasi WhatsApp", Notice "Bypass / Pengujian", Resend Button | `verify_whatsapp_title`, `otp_sent_to`, `testing_bypass_label`, `testing_bypass_message` |
| `validation.php` | Pesan validasi form & summary heading | Header "Verifikasi Belum Berhasil:", custom attribute name, format error | `errors_heading`, `required`, `numeric`, `custom.table_number.required` |
| `messages.php` | Flash session alerts (success, error, warning, info) pada Controller | Notifikasi toast "Data berhasil disimpan", "Nota berhasil dibatalkan" | `created`, `updated`, `deleted`, `pos.order_placed`, `pos.shift_closed` |
| `exceptions.php` | Domain business exceptions, rule violations | Pesan error `DomainException`: Stok kurang, Akses ditolak, PIN terkunci | `stock.insufficient`, `pos.pin_locked`, `tenant.unauthorized_access` |
| `notifications.php` | Template pesan WhatsApp Meta API & Email HTML | Isi template WA nota struk digital, pengingat piutang, subject email | `whatsapp.pos_receipt`, `whatsapp.debt_reminder`, `email.invoice_subject` |
| `audit.php` | Deskripsi aksi & peristiwa pada tabel `audit_logs` | Deskripsi "Kasir membuka laci kas manual", "Perubahan harga produk" | `pos.drawer_opened_no_sale`, `products.price_changed`, `users.role_modified` |
| `pos.php` | Terminal kasir, pesanan, meja resto, KDS dapur, shift, struk | Judul halaman POS, Manajemen Meja, Agregasi BOM Dapur, Split Bill | `orders_title`, `tables_title`, `prep_sheet_title`, `blind_cash_prompt` |
| `inventory.php` | Produk, varian, resep BOM, stok gudang, penerimaan GR, opname | Judul Master Produk, Kolom Stok Fisik, Peringatan Stok Menipis | `products_title`, `warehouse_title`, `recipe_bom`, `low_stock_warning` |
| `finance.php` | Buku kas & bank, auto-journal, laba rugi, pajak, pengeluaran | Buku Kas Harian, Simulator Pajak, Modal Beban Pengeluaran, Neraca | `cash_book_title`, `tax_simulator`, `income_statement`, `unbalanced_journal` |
| `settings.php` | Profil bisnis, printer thermal, format nota, integrasi, role | Konfigurasi Usaha, Kertas Printer 58/80mm, Koneksi WhatsApp Meta | `business_profile`, `printer_settings`, `receipt_format`, `whatsapp_gateway` |
| `storefront.php` | Toko online publik, keranjang belanja, checkout, tracking resi | Etalase Publik, Tombol Bayar Sekarang, Estimasi Ongkir Biteship | `cart_title`, `checkout_button`, `shipping_estimate`, `order_tracking` |

---

## 📝 2. Contoh Kamus Pasangan: `auth.php`, `nav.php`, dan `validation.php`

### A. Berkas `lang/id/auth.php` vs `lang/en/auth.php` (Termasuk Dev Alert Banner)

#### `lang/id/auth.php`:
```php
<?php

return [
    'verify_whatsapp_title'   => 'Verifikasi WhatsApp',
    'otp_sent_to'             => 'Masukkan kode 6 digit yang dikirim ke :phone',
    'testing_bypass_label'    => 'Bypass / Pengujian',
    'testing_bypass_message'  => 'Gunakan kode OTP :code untuk verifikasi instan.',
    'resend_otp_button'       => 'Kirim Ulang Kode OTP',
    'resend_countdown'        => 'Kirim ulang dalam :seconds detik',
    'change_phone_number'     => 'Ubah Nomor Telepon',
    'didnt_receive_otp'       => 'Belum menerima pesan WhatsApp?',
    'otp_invalid_or_expired'  => 'Kode verifikasi OTP salah atau telah kadaluarsa.',
];
```

#### `lang/en/auth.php`:
```php
<?php

return [
    'verify_whatsapp_title'   => 'WhatsApp Verification',
    'otp_sent_to'             => 'Enter the 6-digit code sent to :phone',
    'testing_bypass_label'    => 'Testing / Bypass',
    'testing_bypass_message'  => 'Use OTP code :code for instant verification.',
    'resend_otp_button'       => 'Resend OTP Code',
    'resend_countdown'        => 'Resend in :seconds seconds',
    'change_phone_number'     => 'Change Phone Number',
    'didnt_receive_otp'       => 'Didn’t receive the WhatsApp message?',
    'otp_invalid_or_expired'  => 'Verification code is invalid or has expired.',
];
```

---

### B. Berkas `lang/id/nav.php` vs `lang/en/nav.php` (Breadcrumb & Overline)

#### `lang/id/nav.php`:
```php
<?php

return [
    'dashboard'             => 'Dashboard',
    'sales'                 => 'Penjualan',
    'sales_and_transactions'=> 'Penjualan & Transaksi',
    'pos_terminal'          => 'Kasir POS',
    'order_history'         => 'Riwayat Transaksi',
    'table_management'      => 'Manajemen Meja',
    'kitchen_kds'           => 'Layar Dapur KDS',
    'catalog_and_inventory' => 'Katalog & Inventori',
    'products'              => 'Daftar Produk',
    'warehouses'            => 'Gudang & Cabang',
    'finance_and_reports'   => 'Keuangan & Laporan',
    'settings'              => 'Pengaturan Bisnis',
];
```

#### `lang/en/nav.php`:
```php
<?php

return [
    'dashboard'             => 'Dashboard',
    'sales'                 => 'Sales',
    'sales_and_transactions'=> 'Sales & Transactions',
    'pos_terminal'          => 'POS Terminal',
    'order_history'         => 'Order History',
    'table_management'      => 'Table Management',
    'kitchen_kds'           => 'Kitchen KDS',
    'catalog_and_inventory' => 'Catalog & Inventory',
    'products'              => 'Product List',
    'warehouses'            => 'Warehouses & Branches',
    'finance_and_reports'   => 'Finance & Reports',
    'settings'              => 'Business Settings',
];
```

---

### C. Berkas `lang/id/validation.php` vs `lang/en/validation.php` (Error Summary Heading)

#### `lang/id/validation.php`:
```php
<?php

return [
    'errors_heading' => 'Verifikasi Belum Berhasil:',
    'required'       => 'Kolom :attribute wajib diisi.',
    'numeric'        => 'Kolom :attribute harus berupa angka.',
    'min' => [
        'numeric' => 'Kolom :attribute minimal bernilai :min.',
    ],
    'custom' => [
        'otp' => [
            'required' => 'Kode OTP 6 digit wajib dimasukkan.',
            'digits'   => 'Kode OTP harus tepat 6 digit angka.',
        ],
        'phone' => [
            'required' => 'Nomor WhatsApp wajib diisi.',
        ],
    ],
];
```

#### `lang/en/validation.php`:
```php
<?php

return [
    'errors_heading' => 'Verification Incomplete:',
    'required'       => 'The :attribute field is required.',
    'numeric'        => 'The :attribute must be a number.',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
    ],
    'custom' => [
        'otp' => [
            'required' => 'The 6-digit OTP code is required.',
            'digits'   => 'The OTP code must be exactly 6 digits.',
        ],
        'phone' => [
            'required' => 'The WhatsApp phone number is required.',
        ],
    ],
];
```
