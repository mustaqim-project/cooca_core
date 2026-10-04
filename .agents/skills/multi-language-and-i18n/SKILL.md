---
name: multi-language-and-i18n
description: Audit dan implementasi multi-bahasa komprehensif (Internationalization i18n & Localization l10n) untuk seluruh sistem COOCA (Bahasa Indonesia 'id' sebagai default & English 'en'). Menegakkan ZERO HARDCODED TEXT pada 100% elemen: Document Title, Page Header, Breadcrumb, Overline, Subtitle, Form Input, Placeholder, Helper Text, Dev/Testing Notice Banners, Validation Error Summary, Dropdowns, Action Buttons, Modals, Empty States, Tables, Badges, Pagination, JavaScript/Alpine.js Microcopy, Controller Flash Alerts, Domain Exceptions, JSON API, Notifikasi WA/Email, dan Audit Logs.
---

# MULTI-LANGUAGE (i18n & l10n) COMPREHENSIVE AUDIT & IMPLEMENTATION SKILL

Skill operasional ini memandu AI Agent dalam mengeksekusi **audit teks 100% menyeluruh (*Zero Hardcoded Text Exhaustive Mandate*), deteksi seluruh string mentah, ekstraksi kamus terjemahan modular, dan standardisasi arsitektur multi-bahasa dwibahasa (Bahasa Indonesia `id` default & English `en`)** di seluruh ekosistem COOCA.

> 🎯 **MANDAT ZERO HARDCODED TEXT (100% EXHAUSTIVE COVERAGE)**:  
> **TIDAK BOLEH ADA SATUPUN TEKS BAHASA INDONESIA MENTAH YANG TERTINGGAL** di kode sumber.  
> Seluruh informasi teks—mulai dari Document Title `<title>`, Header H1/H2, Breadcrumb & Overline, Form Input/Placeholder/Helper, Dev Testing Alert, Validation Error Heading, Tombol Aksi, Modal Sheet, Empty State, Kolom Tabel, Badge Status, Pagination, Toast JavaScript, Flash Message, Domain Exception, Notifikasi WhatsApp/Email, hingga Audit Log—**WAJIB MENGGUNAKAN HELPER TRANSLATABLE `{{ __('group.key') }}` ATAU `__('group.key')`**.

---

## 🧭 File Referensi Pendukung

* [`references/i18n-dictionary-structure.md`](file:///c:/laragon/www/cooca_core/.agents/skills/multi-language-and-i18n/references/i18n-dictionary-structure.md) — Matriks struktur kamus Full-Stack (`lang/id/` & `lang/en/`), 12 kategori file kamus, konvensi key, dan checklist kelengkapan.
* [`references/text-audit-and-extraction-protocol.md`](file:///c:/laragon/www/cooca_core/.agents/skills/multi-language-and-i18n/references/text-audit-and-extraction-protocol.md) — Protokol audit teks 10 domain & katalog pola deteksi Regex untuk membersihkan string mentah hulu-ke-hilir.

---

## 🏛️ Arsitektur Multi-Bahasa COOCA (ID $\leftrightarrow$ EN)

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                       ARSITEKTUR MULTI-BAHASA (i18n & l10n) COOCA                           │
├──────────────────────────┬──────────────────────────┬───────────────────────────────────────┤
│ 1. RESOLUSI LOCALE       │ 2. STRUKTUR BERKAS MODULAR│ 3. INTEGRASI FRONTEND & BLADE         │
│ • URL Query (?lang=en)   │ • lang/id/*.php          │ • {{ __('group.key') }} / @lang       │
│ • Sesi session('locale') │ • lang/en/*.php          │ • window.COOCA_I18N di Alpine.js      │
│ • Profil User / Tenant DB│ • 12 Domain Modular      │ • Helper dinamis t('key')             │
│ • Fallback: 'id'         │   (Frontend & Backend)   │ • Language Switcher Bento Apple HIG   │
└──────────────────────────┴──────────────────────────┴───────────────────────────────────────┘
```

---

## 🔍 10 Domain Sasaran Audit Teks Menyeluruh (Zero Text Left Behind)

Setiap kali melakukan audit atau refactoring berkas kode, AI Agent **WAJIB MENYISIR SELURUH 10 DOMAIN BERIKUT**:

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                        10 DOMAIN SASARAN AUDIT TEKS MULTI-BAHASA                            │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. Page Title & Meta Tags   │ @extends('layouts.app', ['title' => __('auth.otp_title')])    │
│ 2. Breadcrumbs & Overlines  │ Overline Kategori, Breadcrumb items, Active Route label       │
│ 3. Headers, H1, & Subtitles │ <x-module-header :title="__('...')" :subtitle="__('...')">    │
│ 4. Forms, Labels, & Hints   │ <label>, placeholder="", helper hint text, dropzone upload    │
│ 5. Dev / Test Alerts        │ Banner bypass dev/testing, sandbox notice, warning alerts     │
│ 6. Error Lists & Summary    │ Header "Verifikasi Belum Berhasil:", daftar pesan validasi    │
│ 7. Buttons & Action Links   │ Primary CTA, Ghost buttons, dropdown menu items, tooltips     │
│ 8. Modals, Sheets, Dialogs  │ Modal title, body text, "Hapus meja ini?", tombol Batal/Simpan│
│ 9. Tables, Badges, & Empty  │ Thead columns, Status badges, Empty state "Belum ada produk"  │
│ 10. Backend Exceptions & Log│ DomainException, Flash messages, WA & Email templates, Audit  │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

### Rincian Standar & Contoh Penerapan per Domain:

#### 1. Page Title & Meta Tags (Document Head)
```blade
<!-- ❌ SALAH: Hardcoded Page Title -->
@extends('layouts.app', ['title' => 'Verifikasi WhatsApp'])

<!-- ✅ BENAR: Translatable Page Title -->
@extends('layouts.app', ['title' => __('auth.verify_whatsapp_title')])
```

#### 2. Breadcrumbs, Category Overlines & Navigation
```blade
<!-- ❌ SALAH: Hardcoded Breadcrumb -->
<nav aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span>Penjualan</span>
    <span>Riwayat Transaksi</span>
</nav>

<!-- ✅ BENAR: Translatable Breadcrumb -->
<nav aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">{{ __('nav.dashboard') }}</a>
    <span>{{ __('nav.sales') }}</span>
    <span>{{ __('pos.orders.title') }}</span>
</nav>
```

#### 3. Module Headers, Titles, Subtitles, & Kickers
```blade
<!-- ❌ SALAH: Hardcoded Header -->
<h1 class="text-2xl font-bold">Verifikasi WhatsApp</h1>
<p class="text-sm text-slate-500">Masukkan kode 6 digit yang dikirim ke {{ $phone }}</p>

<!-- ✅ BENAR: Translatable Header dengan Parameter Dinamis -->
<h1 class="text-2xl font-bold">{{ __('auth.verify_whatsapp_title') }}</h1>
<p class="text-sm text-slate-500">{{ __('auth.otp_sent_to', ['phone' => $phone]) }}</p>
```

#### 4. Form Inputs, Labels, Placeholders, & Helper Text
```blade
<!-- ❌ SALAH -->
<label>Nomor Telepon WhatsApp</label>
<input type="text" placeholder="Misal: 081234567890">
<p class="text-xs">Kode OTP akan dikirimkan melalui pesan WhatsApp resmi.</p>

<!-- ✅ BENAR -->
<label>{{ __('auth.phone_label') }}</label>
<input type="text" placeholder="{{ __('auth.phone_placeholder') }}">
<p class="text-xs">{{ __('auth.otp_whatsapp_hint') }}</p>
```

#### 5. Dev Testing / Sandbox Alert Banners
```blade
<!-- ❌ SALAH: Hardcoded Dev Testing Notice -->
@if (app()->environment('local', 'testing'))
    <div>
        <span><strong>Bypass / Pengujian:</strong> Gunakan kode OTP <code>123456</code> untuk verifikasi instan.</span>
    </div>
@endif

<!-- ✅ BENAR: Translatable Testing Notice -->
@if (app()->environment('local', 'testing'))
    <div>
        <span><strong>{{ __('auth.testing_bypass_label') }}:</strong> {{ __('auth.testing_bypass_message', ['code' => '123456']) }}</span>
    </div>
@endif
```

#### 6. Validation Error Summaries & Feedback
```blade
<!-- ❌ SALAH -->
<div class="alert-danger">
    <span>Verifikasi Belum Berhasil:</span>
</div>

<!-- ✅ BENAR -->
<div class="alert-danger">
    <span>{{ __('validation.errors_heading') }}</span>
</div>
```

#### 7. Buttons, Dropdown Menus, & Action Links
```blade
<!-- ❌ SALAH -->
<button type="submit">Kirim Ulang Kode OTP</button>
<button type="button">Batal</button>

<!-- ✅ BENAR -->
<button type="submit">{{ __('auth.resend_otp_button') }}</button>
<button type="button">{{ __('common.cancel') }}</button>
```

#### 8. Modals, Bottom Sheets, & Dialog Confirmation
```blade
<!-- ❌ SALAH -->
<h3 class="font-bold">Konfirmasi Hapus Meja</h3>
<p>Apakah Anda yakin ingin menghapus Meja #{{ $table->number }}? Riwayat pesanan tetap aman.</p>

<!-- ✅ BENAR -->
<h3 class="font-bold">{{ __('pos.tables.delete_modal_title') }}</h3>
<p>{{ __('pos.tables.delete_confirm', ['number' => $table->number]) }}</p>
```

#### 9. Data Tables, Status Badges, & Empty States
```blade
<!-- ❌ SALAH -->
<th>Nama Produk</th>
<th>Status Stok</th>
<span>Stok Aman</span>
<div>Belum ada data transaksi kasir.</div>

<!-- ✅ BENAR -->
<th>{{ __('inventory.product_name') }}</th>
<th>{{ __('inventory.stock_status') }}</th>
<span>{{ __('common.status_safe') }}</span>
<div>{{ __('pos.orders.empty_state') }}</div>
```

#### 10. Backend Domain Exceptions, Flash Messages, & Notifications
```php
// ❌ SALAH: Hardcoded Backend Strings
throw new DomainException("Stok barang {$item} tidak mencukupi!");
return back()->with('success', 'Meja baru berhasil ditambahkan.');

// ✅ BENAR: Translatable Backend Helpers
throw new DomainException(__('exceptions.stock.insufficient', ['item' => $item->name]));
return back()->with('success', __('messages.created', ['entity' => __('pos.tables.entity_name')]));
```

---

## 🗂️ 12 Berkas Kamus Modular Terstandar (`lang/`)

```
lang/
├── id/                                 lang/en/
│   ├── common.php        ───────────►  ├── common.php        (Simpan, Batal, Hapus, Edit, Cari, Status, Pagination)
│   ├── nav.php           ───────────►  ├── nav.php           (Sidebar, Topbar, Breadcrumb, Overline Menu)
│   ├── auth.php          ───────────►  ├── auth.php          (Login, Register, OTP WA, Reset Password, Dev Bypass)
│   ├── messages.php      ───────────►  ├── messages.php      (Flash session alerts: created, updated, deleted)
│   ├── exceptions.php    ───────────►  ├── exceptions.php    (Domain business rule exceptions: stock, auth, tenant)
│   ├── notifications.php ───────────►  ├── notifications.php (Template pesan WhatsApp Meta API & Email HTML)
│   ├── validation.php    ───────────►  ├── validation.php    (Error messages & Heading "Verifikasi Belum Berhasil")
│   ├── audit.php         ───────────►  ├── audit.php         (Deskripsi audit trail & system event logs)
│   ├── pos.php           ───────────►  ├── pos.php           (Terminal, Meja, KDS, Shift, Struk, Void, Prep Sheet)
│   ├── inventory.php     ───────────►  ├── inventory.php     (Produk, Stok, Gudang, Resep BOM, Supplier, Opname)
│   ├── finance.php       ───────────►  ├── finance.php       (Buku Kas, Akun Bank, Laba Rugi, Pajak, Beban)
│   ├── settings.php      ───────────►  ├── settings.php      (Profil Usaha, Printer, Integrasi, Role, Langganan)
│   └── storefront.php    ───────────►  └── storefront.php    (Katalog Online, Keranjang, Checkout, Resi, Tracking)
```

---

## 📋 Checklist Validasi 100% Multi-Bahasa

Sebelum pekerjaan dinyatakan selesai, AI Agent **WAJIB MEMVERIFIKASI**:
- [ ] **Page Title (`title`):** Parameter layout `@extends('...', ['title' => __('...')])` translatable.
- [ ] **Breadcrumbs & Overlines:** Seluruh tautan navigasi dan overline kategori menggunakan `__('nav....')`.
- [ ] **Headers & Subtitles:** Semua `<h1>`, `<h2>`, `<x-module-header>` translatable.
- [ ] **Inputs & Placeholders:** Semua `<label>`, `placeholder="..."`, dan teks hint translatable.
- [ ] **Dev Testing Notices:** Banner bypass pengujian sandbox menggunakan `__('auth.testing_bypass_...')`.
- [ ] **Error Lists:** Judul error summary menggunakan `__('validation.errors_heading')`.
- [ ] **Action Buttons:** Seluruh tombol aksi, dropdown, dan pagination translatable.
- [ ] **JavaScript & Alpine.js:** Injeksi `window.COOCA_I18N` atau `@json(__('...'))` terpasang pada dialog/toast.
- [ ] **Backend Flash & Exceptions:** Controller dan Domain Exception 100% bebas string mentah.
- [ ] **Sinkronisasi Dwibahasa:** Setiap key baru di `lang/id/*.php` memiliki padanan resmi di `lang/en/*.php`.
- [ ] **Format Locale:** Angka moneter diformat dengan class Tailwind `tabular-nums` dan pemformatan angka/tanggal mengikuti `app()->getLocale()`.
