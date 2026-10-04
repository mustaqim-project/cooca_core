# AUDIT SISTEM END-TO-END: OTOMASI NOTIFIKASI TERMIN PEMBAYARAN PELANGGAN (WHATSAPP & EMAIL)

> **Dokumen Audit Teknis & Bisnis**  
> **Modul Terkait:** Commerce / Sales Invoices, Customers & CRM, WhatsApp Gateway, Email Notification System  
> **Tanggal:** 2026-10-01  
> **Status:** AUDITED & PROPOSED  

---

## 1. Konteks Bisnis & Operasional UMKM

Pada operasional harian UMKM Indonesia (distributor B2B, grosir, toko retail, maupun bengkel otomotif), transaksi tempo (*payment terms*) seperti *Net 14*, *Net 30*, maupun kasbon belanja adalah hal yang sangat lumrah. Namun, kendala terbesar pemilik usaha adalah:
1. **Lupa Menagih Tepat Waktu**: Kasir atau staf keuangan seringkali lupa memeriksa tanggal jatuh tempo faktur secara manual, sehingga piutang menumpuk (*aging receivables* membesar).
2. **Kecanggungan Menagih Manual**: Menagih pelanggan lewat telepon pribadi seringkali dirasa canggung atau tidak profesional.
3. **Pemberitahuan Sepihak**: Pelanggan sering beralasan tidak menerima tagihan fisik atau tidak ingat tanggal jatuh tempo.
4. **Resiko Fraud Internal (Lapping)**: Tanpa notifikasi resmi yang terkirim langsung ke nomor WhatsApp dan email pribadi pelanggan, kasir berpotensi menunda pencatatan pembayaran pelanggan atau menyalahgunakan uang kasbon.

Dengan otomasi pengingat termin pembayaran terjadwal (*Auto-Reminder*) secara dwisaluran (WhatsApp & Email), pelanggan menerima pemberitahuan santun dan profesional menjelang jatuh tempo (H-3), pada hari H, dan saat lewat tempo (Overdue), lengkap dengan nomor faktur, rincian nominal, batas waktu, dan rekening bank resmi toko.

---

## 2. Pemetaan Arsitektur Hulu-ke-Hilir Saat Ini

```
[Cron Scheduler] ──> routes/console.php (Belum ada jadwal reminder pelanggan)
                           │
[Invoices Table] ──> invoices: due_date, balance_due, status (draft/sent/unpaid/partially_paid/paid/overdue/void)
[Customers Table] ──> customers: phone (WhatsApp), email, payment_terms_days, current_credit_balance
                           │
[WhatsApp Gateway] ──> WhatsAppGatewayService / WhatsAppService (Sudah siap Meta WABA & fallback wa.me)
[Email Channel] ──> Mailables & emails views (Sudah ada infrastruktur, belum ada Mailable termin pelanggan)
                           │
[UI / Blade] ──> resources/views/app/invoices/show.blade.php (Belum ada tombol trigger pengingat termin)
             ──> resources/views/app/customers/index.blade.php (Belum ada tombol reminder di modal pelanggan)
```

### Rantai Keterhubungan Sistem:
1. **Entitas Faktur (`invoices`)**:
   - Kolom `due_date`, `balance_due`, `total_amount`, `paid_amount`, `payment_terms`, `bank_details_snapshot`.
   - Status belum lunas: `sent`, `unpaid`, `partially_paid`, `overdue`.
2. **Entitas Pelanggan (`customers`)**:
   - Kolom `phone` (nomor WA seluler), `email`, `name`, `company_name`, `payment_terms_days`, `current_credit_balance`.
3. **Ketiadaan Tabel Anti-Spam / Log Pengingat**:
   - Saat ini belum ada tabel dedicated untuk mencatat riwayat pengiriman pengingat termin per pelanggan/faktur, sehingga jika cron berjalan atau kasir menekan tombol kirim berulang kali, pelanggan berpotensi ter-spam.
4. **Ketiadaan Artisan Command & Mailable**:
   - Belum ada Mailable `CustomerPaymentTermReminderMail` yang memuat layout faktur Apple HIG resmi.
   - Belum ada Artisan Command `customers:send-term-reminders` yang terdaftar di `routes/console.php`.

---

## 3. Matriks Gap 4-Kuadran

| Kuadran | Status Saat Ini | Dampak | Kebutuhan Solusi |
| :--- | :--- | :--- | :--- |
| **Q1: Data & DB** | Ada tabel `invoices` & `customers`, namun belum ada tabel pelacak log reminder termin. | Berisiko spam jika cron dijalankan atau retry. | Tambahkan tabel migrasi `customer_term_reminders`. |
| **Q2: Logic & Service** | Logika tagihan ada di `InvoiceService`, namun belum ada service otomatisasi reminder piutang berkala. | Reminder tidak bisa berjalan otomatis di latar belakang. | Buat `CustomerPaymentTermReminderService` dengan interval H-3, Hari H, dan Overdue. |
| **Q3: Notifikasi & Channel** | `WhatsAppGatewayService` dan `Mail` tersedia tapi belum ada template pesan termin pelanggan. | Pesan termin belum terstandardisasi santun dan profesional. | Buat Mailable HTML premium + template teks WhatsApp santun anti-blokir. |
| **Q4: UI & Ergonomi** | Tampilan detail faktur dan pelanggan belum memiliki tombol kirim pengingat manual 1-klik. | Merchant tidak bisa menagih manual saat dibutuhkan. | Tambahkan tombol kirim pengingat di detail faktur dan modal profil pelanggan. |

---

## 4. Analisis Skema Fraud Internal & Mitigasi

1. **Skema Lapping / Penggelapan Kasbon**:
   - *Risiko:* Kasir menerima uang pelunasan dari pelanggan namun tidak menginputnya ke sistem, mengklaim pelanggan belum bayar.
   - *Mitigasi:* Sistem otomatis mengirim reminder ke WA & Email pelanggan yang memuat sisa saldo riil. Jika pelanggan sudah bayar, pelanggan akan langsung komplain ke Owner membawa bukti transfer, membongkar skema penggelapan kasir seketika.
2. **Skema Rekening Fiktif**:
   - *Risiko:* Kasir memberikan rekening pribadi saat menagih pelanggan.
   - *Mitigasi:* Seluruh notifikasi otomatis WA & Email mengunci rekening tujuan transfer resmi toko yang diambil langsung dari konfigurasi profil bisnis (`bank_details_snapshot`), bukan input bebas kasir.
