# MASTER IMPLEMENTATION PLAN: OTOMASI PENGINGAT TERMIN PEMBAYARAN PELANGGAN (WHATSAPP & EMAIL)

> **Dokumen Rencana Aksi Rekayasa Sistem (Master Plan)**  
> **Target:** Otomasi Pengiriman Pengingat Termin Pembayaran Pelanggan ke WhatsApp & Email jika Belum Bayar  
> **Tanggal:** 2026-10-01  
> **Status:** PROPOSED & WAITING CONFIRMATION GATE  

---

## 1. Rencana Fase Implementasi Bertahap

### Fase 1: Fondasi Skema Database & Model Pelacak Reminder
- **Tujuan:** Menyimpan riwayat pengiriman reminder termin agar tidak terjadi spamming ke pelanggan (Anti-Spam Dedup Guard).
- **Aksi Teknis:**
  - Buat migrasi: `database/migrations/2026_10_01_000003_create_customer_term_reminders_table.php`.
  - Kolom:
    - `id` (uuid primary)
    - `business_id` (foreignUuid -> `businesses`)
    - `customer_id` (foreignUuid -> `customers`)
    - `invoice_id` (foreignUuid nullable -> `invoices`)
    - `reminder_type` (`upcoming_h3`, `due_date`, `overdue`, `manual`)
    - `channel` (`whatsapp`, `email`, `both`)
    - `recipient_phone`, `recipient_email`
    - `status` (`sent`, `failed`, `skipped`)
    - `wa_status`, `email_status`, `error_message`
    - `sent_at` (timestamp)
    - Index unik komposit: `['business_id', 'invoice_id', 'reminder_type', 'sent_date']` untuk dedup harian.
  - Buat Model: `App\Models\CustomerTermReminder`.

### Fase 2: Domain Service Otomasi Pengingat Termin (`CustomerPaymentTermReminderService`)
- **Tujuan:** Logika bisnis terpusat untuk mendeteksi tagihan jatuh tempo, memfilter anti-spam, dan mendispatch ke WhatsApp & Email.
- **Aksi Teknis:**
  - Lokasi: `app/Domain/Crm/CustomerPaymentTermReminderService.php`.
  - Fitur:
    - `sendScheduledDueReminders(Business $business, ?Carbon $date = null)`:
      - Menemukan seluruh invoice `status` in `[sent, unpaid, partially_paid, overdue]` dengan `balance_due > 0`.
      - Menentukan klasifikasi:
        - `upcoming_h3`: Jatuh tempo tepat 3 hari ke depan (`$dueDate->diffInDays($today) === 3` dan belum jatuh tempo).
        - `due_date`: Jatuh tempo tepat hari ini (`$dueDate->isToday()`).
        - `overdue`: Lewat jatuh tempo (`$dueDate->isPast()` dan bukan hari ini).
      - Menemukan pelanggan dengan `current_credit_balance > 0` (kasbon belanja tempo) yang belum lunas.
      - Mengecek log `CustomerTermReminder` agar tidak dikirim ganda pada hari yang sama.
    - `dispatchReminder(Invoice $invoice, string $reminderType, string $channel = 'both')`:
      - Kirim Email via `CustomerPaymentTermReminderMail`.
      - Kirim WhatsApp via `WhatsAppGatewayService` / `WhatsAppService` (dengan pesan santun, rincian nomor faktur, sisa tagihan, due date, dan rekening transfer resmi toko).
      - Catat hasil ke `CustomerTermReminder`.

### Fase 3: Template Email Responsif Apple HIG & Mailable
- **Tujuan:** Menghasilkan email tagihan resmi yang elegan, tepercaya, dan bebas dari kesan spam.
- **Aksi Teknis:**
  - Buat Mailable: `app/Mail/CustomerPaymentTermReminderMail.php`.
  - Buat Blade View Email: `resources/views/emails/customer-payment-term-reminder.blade.php`.
  - Konten Email:
    - Header Nama & Logo Toko Resmi.
    - Box Sorotan Jatuh Tempo & Sisa Tagihan (format mata uang rapi).
    - Status Badge (Mendekati Jatuh Tempo / Jatuh Tempo Hari Ini / Melewati Jatuh Tempo).
    - Rincian Tabel Faktur (Nomor, Tanggal, Termin Tempo, Total, Sisa).
    - Rekening Resmi Pembayaran Toko (BCA/Mandiri/BRI/BNI dari `bank_details_snapshot` bisnis).
    - Tombol Call-to-Action: *"Lihat Dokumen Faktur"* & *"Konfirmasi Pembayaran via WhatsApp"*.

### Fase 4: Template Teks WhatsApp & Fallback Link Mandiri
- **Tujuan:** Pesan WhatsApp santun, terformat tebal/miring rapi, dan tautan langsung ke faktur.
- **Aksi Teknis:**
  - Format pesan WhatsApp:
    ```text
    Yth. Bapak/Ibu [Nama Pelanggan],

    Kami dari [Nama Toko] menginformasikan status tagihan faktur Anda:
    • No. Faktur: #[Nomor Faktur]
    • Termin Tempo: [Net 30 / dll]
    • Tanggal Jatuh Tempo: [Tanggal]
    • Sisa Tagihan: Rp [Nominal]

    Pembayaran dapat ditransfer ke rekening resmi kami:
    Bank [Nama Bank] - [No Rekening] a.n [Nama Pemilik]

    Lihat dokumen faktur Anda:
    [Tautan Faktur]

    Terima kasih atas kerja sama Anda.
    _[Nama Toko]_
    ```
  - Menghasilkan URL fallback manual `https://wa.me/{phone}?text={encoded_message}` untuk kenyamanan kasir jika gateway API tidak aktif.

### Fase 5: Artisan Console Command & Penjadwalan Cron
- **Tujuan:** Menjalankan pengingat termin secara otomatis setiap pagi tanpa intervensi manual kasir.
- **Aksi Teknis:**
  - Buat Command: `app/Console/Commands/SendCustomerPaymentTermRemindersCommand.php`.
  - Signature: `customers:send-term-reminders {--business= : ID bisnis spesifik} {--dry-run : Simulasi tanpa kirim}`.
  - Daftarkan ke `routes/console.php`:
    ```php
    Schedule::command('customers:send-term-reminders')
        ->dailyAt('08:30')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/cron-term-reminders.log'));
    ```

### Fase 6: Antarmuka UI (Tombol Trigger Cepat Manual 1-Klik)
- **Tujuan:** Memberikan kendali kepada merchant untuk mengirim pengingat langsung kapan saja dari layar web.
- **Aksi Teknis:**
  - Endpoint Controller:
    - Tambahkan metode `sendTermReminder(Request $request, Invoice $invoice)` di `app/Http/Controllers/Web/InvoiceWebController.php`.
    - Route: `POST /invoices/{invoice}/remind` (name: `invoices.remind`).
  - Update View:
    - Di [`resources/views/app/invoices/show.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/invoices/show.blade.php): Tombol *"Kirim Pengingat Termin"* dengan pilihan (WhatsApp, Email, atau Keduanya), modal konfirmasi, dan tautan cepat ke WhatsApp Web jika gateway offline.
    - Di [`resources/views/app/customers/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/customers/index.blade.php): Pada Modal Detail Pelanggan, tampilkan status tagihan aktif dan tombol *"Kirim Pengingat Tagihan"* jika pelanggan memiliki sisa kasbon/faktur.
  - Kamus Bahasa: Tambahkan keys terjemahan dwibahasa di `lang/id/` dan `lang/en/` untuk pesan sukses, dialog konfirmasi, dan status terkirim.

### Fase 7: Pengujian Otomatis Komprehensif (100% Lolos)
- **Tujuan:** Memvalidasi seluruh skenario pengiriman, anti-spam dedup, isolasi multi-tenant, dan format pesan.
- **Aksi Teknis:**
  - Buat Test Suite: `tests/Feature/CustomerPaymentTermReminderTest.php`.
  - Skenario Uji:
    1. Pengiriman otomatis mendeteksi faktur H-3, Hari H, dan Overdue secara akurat.
    2. Dedup Anti-Spam: Tidak mengirim reminder ganda pada hari yang sama untuk faktur yang sama.
    3. Konten Email: Memuat nomor faktur, sisa tagihan, due date, dan rekening bank resmi.
    4. Konten WhatsApp: Memformat nomor telepon internasional `628...` dan pesan santun.
    5. Otorisasi & Multi-Tenant: Tenant B dilarang menembak atau melihat reminder Tenant A.
    6. Manual Trigger via UI/Web Route menghasilkan respons sukses dan log tercatat.

---

## 2. Matriks File Terdampak

| No | Path Berkas | Peran / Deskripsi |
| :---: | :--- | :--- |
| **01** | `database/migrations/2026_10_01_000003_create_customer_term_reminders_table.php` | Migrasi tabel log riwayat reminder & anti-spam |
| **02** | `app/Models/CustomerTermReminder.php` | Eloquent Model pelacak reminder termin |
| **03** | `app/Domain/Crm/CustomerPaymentTermReminderService.php` | Domain Service logika deteksi & dispatch dwisaluran |
| **04** | `app/Mail/CustomerPaymentTermReminderMail.php` | Mailable resmi pengingat termin pelanggan |
| **05** | `resources/views/emails/customer-payment-term-reminder.blade.php` | Template HTML Email Apple HIG responsif |
| **06** | `app/Console/Commands/SendCustomerPaymentTermRemindersCommand.php` | Artisan Console Command untuk Cron harian |
| **07** | `routes/console.php` | Pendaftaran jadwal harian (pukul 08:30 WIB) |
| **08** | `app/Http/Controllers/Web/InvoiceWebController.php` | Controller action pengiriman pengingat manual |
| **09** | `routes/owner.php` | Route endpoint `POST /invoices/{invoice}/remind` |
| **10** | `resources/views/app/invoices/show.blade.php` | Tombol dan modal trigger pengingat termin |
| **11** | `resources/views/app/customers/index.blade.php` | Integrasi status tagihan & tombol aksi pengingat di modal detail |
| **12** | `lang/id/invoices.php` & `lang/en/invoices.php` (serta customers/crm) | Kamus dwibahasa pesan notifikasi pengingat |
| **13** | `tests/Feature/CustomerPaymentTermReminderTest.php` | Test Suite otomatis komprehensif |
| **14** | `docs/AiWorkHistory.md` & `docs/SYSTEM_GUIDE.md` | Dokumentasi rekam jejak historis & panduan sistem |
