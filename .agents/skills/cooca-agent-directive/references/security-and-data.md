# COOCA — Keamanan, Data, & Matriks Audit Kesenjangan (Referensi Lengkap)

## 1. Jaminan Integritas Finansial (Non-Destruktif)

**Dilarang tanpa persetujuan eksplisit**:
- Mengubah rumus Subtotal, Diskon, Pajak/PPN, Biaya Kirim, Total Akhir.
- Mengubah rumus HPP/COGS (Moving Average / Weighted Average).
- Mengubah kalkulasi Margin Laba Kotor & Laba Bersih.
- Mengubah logika Saldo Kas, Rekonsiliasi Bank, Jurnal Akuntansi Otomatis (*double-entry*).
- Memodifikasi nilai transaksi pada nota/invoice/PO/penerimaan barang yang berstatus **selesai/paid**.
- Menghapus data produksi atau mengubah status transaksi selesai.
- Mengubah struktur database yang berisiko.

Semua perubahan finansial wajib dianalisis dan diuji secara khusus sebelum implementasi.

## 2. Isolasi Multi-Tenant (Strict Tenant Isolation)

**Dilarang** melakukan query database tanpa scoping tenant. Setiap query Eloquent/Query Builder pada entitas milik tenant wajib menyertakan scope bisnis aktif:

```php
// BENAR
$business = \App\Support\Context::requireBusiness();
$products = Product::where('business_id', $business->id)->get();

// SALAH — Kebocoran data lintas tenant!
$products = Product::all();
```

**Dilarang** membypass middleware keamanan inti: `auth:web`, `auth:admin`, `auth:customer`, `wa.otp`, `business.active`, `verified`, `require.permission:*`, `require.role:*`, `entitlement:*`.

## 3. Integritas Formulir & Proteksi Eksploitasi

- Setiap `<form>` wajib mempertahankan `@csrf`. Form `PUT`/`PATCH`/`DELETE` wajib `@method('PUT')` dst.
- Dilarang melemahkan validasi input (`required`, `numeric`, `min`, `max`, `exists`, `unique`).
- Dilarang memasukkan input mentah ke `DB::raw` tanpa parameter binding aman (cegah SQL Injection).
- Dilarang merender output HTML belum di-escape — gunakan `{{ $var }}` default Blade, hindari `{!! !!}` kecuali HTML yang sudah tersanitasi.

## 4. Larangan Penghapusan Sepihak (Zero Silent Deletions)

Penghapusan/penggabungan route lama **wajib** menyediakan redirect atau alias rute untuk menjamin backward-compatibility dan mencegah broken links pada bookmark pengguna. Setiap perombakan alur kerja wajib melewati **Interactive Confirmation Gate** terlebih dahulu.

## 5. Checklist Audit Keamanan Umum

Setiap perubahan wajib diperiksa terhadap: Authentication, Authorization, Role & permission, IDOR, CSRF, Mass assignment, Validasi request, SQL Injection, XSS, Upload berbahaya, Route exposure, Privilege escalation, Webhook security, API security, Rate limiting, Audit log, Isolasi perusahaan & cabang, Kebocoran data antar pengguna/cabang.

> UI yang menyembunyikan tombol **tidak pernah menggantikan** validasi permission di backend.

---

## 6. Matriks Audit Kesenjangan (Gap Analysis) 4-Dimensi

Setiap modul/fitur yang ditinjau wajib dianalisis batas keamanan (*security boundary*) dan kesenjangan pengalaman (*experience gap*) antar 4 kuadran:

```
┌─────────────────────────────────────────────────────────────┐
│                    SUPERADMIN (Backoffice)                  │
│   • Pengawasan Platform   • Manajemen Tenant   • Billing    │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Isolasi Ketat / Audit Trail)
┌──────────────────────────────▼──────────────────────────────┐
│                  BUSINESS OWNER & TIM KASIR                 │
│   • POS Kasir   • Stok/Gudang   • Keuangan   • Pengaturan   │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Gated Checkout / IDOR Shield)
┌──────────────────────────────▼──────────────────────────────┐
│                    CUSTOMER / PEMBELI AKHIR                 │
│   • Toko Online (Storefront)   • Portal Pesanan   • Lacak   │
└──────────────────────────────▲──────────────────────────────┘
                               │ (Webhook / Fail-Safe Messaging)
┌──────────────────────────────┴──────────────────────────────┐
│                SUBSISTEM OTOMASI & BACKGROUND               │
│   • WhatsApp Gateway   • Auto-Journal   • Cron Scheduler    │
└─────────────────────────────────────────────────────────────┘
```

### Peta Kesenjangan & Mitigasi

1. **Admin vs Owner** — *Risiko*: Superadmin tidak sengaja memodifikasi stok/kas tenant saat troubleshooting. *Mandat*: setiap aksi mutasi data oleh admin wajib audit log (`admin_id` tercatat), tidak boleh memotong validasi integritas finansial.
2. **Owner vs Customer (IDOR Shield)** — *Risiko*: Customer A melihat pesanan/nota Customer B lewat tebak ID (`/customer/orders/{id}`) atau manipulasi parameter URL. *Mandat*: akses portal customer wajib verifikasi ganda — identitas global customer (`auth:customer`) + nomor WhatsApp terverifikasi OTP. Dilarang query pesanan customer jika parameter identitas `null`.
3. **Owner vs POS Staff (Privilege & Fraud Prevention)** — *Risiko*: kasir melakukan void/refund sepihak untuk penggelapan dana. *Mandat*: aksi sensitif POS (Void, Refund, Buka Laci Kas Manual) wajib verifikasi `supervisor_pin` yang di-hash (Bcrypt) dan dibatasi frekuensi (`throttle:5,1`).
4. **Otomasi vs Kegagalan Jaringan (Fail-Safe Automation)** — *Risiko*: server WhatsApp terputus sehingga invoice/struk tidak terkirim, user panik mengira transaksi gagal. *Mandat*: otomasi wajib punya fallback ramah pengguna — tombol instan "Kirim Manual via WhatsApp Web/Aplikasi HP" (ikon Lucide, tanpa emoji) dengan teks nota yang sudah terformat rapi.
