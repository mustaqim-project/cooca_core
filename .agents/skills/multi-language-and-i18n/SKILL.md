---
name: multi-language-and-i18n
description: Audit dan implementasi multi-bahasa (Internationalization i18n & Localization l10n) untuk sistem COOCA (Bahasa Indonesia 'id' sebagai default & English 'en'). Digunakan untuk mendeteksi teks mentah (hardcoded string), ekstraksi kamus terjemahan modular ke lang/id/ dan lang/en/, standarisasi helper Blade {{ __('group.key') }}, injeksi JavaScript window.COOCA_I18N di Alpine.js, middleware SetLocaleMiddleware, Language Switcher Bento Apple HIG, serta pemformatan moneter/tanggal adaptif (Rp 250.000 vs IDR 250,000). Aktifkan saat user meminta audit teks, lokalisasi, terjemahan bahasa inggris, i18n, l10n, atau multi-language.
---

# MULTI-LANGUAGE (i18n & l10n) AUDIT & IMPLEMENTATION SKILL

Skill operasional ini memandu AI Agent dalam mengeksekusi **audit teks, deteksi string mentah (*hardcoded text*), ekstraksi kamus terjemahan modular, dan standardisasi arsitektur multi-bahasa dwibahasa (Bahasa Indonesia `id` default & English `en`)** di seluruh ekosistem COOCA (Admin Panel, Owner Backoffice, POS Terminal, dan Customer Storefront).

---

## 🧭 File Referensi Pendukung

* [`references/i18n-dictionary-structure.md`](file:///c:/laragon/www/cooca_core/.agents/skills/multi-language-and-i18n/references/i18n-dictionary-structure.md) — Struktur folder kamus modular (`lang/id/` & `lang/en/`), tata nama key domain, dan checklist kelengkapan kata.
* [`references/text-audit-and-extraction-protocol.md`](file:///c:/laragon/www/cooca_core/.agents/skills/multi-language-and-i18n/references/text-audit-and-extraction-protocol.md) — Protokol audit 5 layer untuk mendeteksi hardcoded string di Blade, Alpine.js, Controller, Notifikasi, dan Flash Alerts.

---

## 🏛️ Arsitektur Multi-Bahasa COOCA (ID $\leftrightarrow$ EN)

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                       ARSITEKTUR MULTI-BAHASA (i18n & l10n) COOCA                           │
├──────────────────────────┬──────────────────────────┬───────────────────────────────────────┤
│ 1. RESOLUSI LOCALE       │ 2. STRUKTUR BERKAS MODULAR│ 3. INTEGRASI FRONTEND & BLADE         │
│ • URL Query (?lang=en)   │ • lang/id/*.php          │ • {{ __('group.key') }} / @lang       │
│ • Sesi session('locale') │ • lang/en/*.php          │ • window.COOCA_I18N di Alpine.js      │
│ • Profil User / Tenant DB│ • common, pos, inventory,│ • Helper dinamis t('key')             │
│ • Fallback: 'id'         │   finance, crm, validasi │ • Language Switcher Bento Apple HIG   │
└──────────────────────────┴──────────────────────────┴───────────────────────────────────────┘
```

---

## 🔍 Protokol Audit Teks 5 Layer (The 5-Layer Text Audit)

Setiap kali melakukan review atau modifikasi berkas kode, AI Agent **WAJIB** memeriksa 5 layer berikut:

### Layer 1: Blade Views & Layouts (UI Markup)
- **DILARANG:** Teks bahasa Indonesia statis mentah langsung di tag HTML:
  ```html
  <!-- ❌ SALAH (Hardcoded String) -->
  <h1 class="text-2xl font-bold">Riwayat Transaksi Kasir</h1>
  <p>Daftar transaksi kasir, margin HPP, dan cetak ulang nota.</p>
  ```
- **WAJIB:** Menggunakan helper translatable Laravel:
  ```blade
  <!-- ✅ BENAR -->
  <h1 class="text-2xl font-bold">{{ __('pos.orders.title') }}</h1>
  <p>{{ __('pos.orders.subtitle') }}</p>
  ```
- **Interpolasi Parameter Dinamis:**
  ```blade
  {{ __('pos.shift.opened_by', ['name' => $user->name, 'time' => $time]) }}
  ```

### Layer 2: JavaScript & Alpine.js Microcopy (Dialog & Toasts)
- Seluruh alert toast, modal konfirmasi, dan dynamic string di Alpine.js dilarang keras di-hardcode.
- **Injeksi Kamus ke Window State:**
  ```blade
  <script>
      window.COOCA_I18N = window.COOCA_I18N || {};
      window.COOCA_I18N.pos = @json(__('pos'));
      window.COOCA_I18N.common = @json(__('common'));
  </script>
  ```
- **Pemanggilan di Script / Alpine.js:**
  ```javascript
  const msg = (window.COOCA_I18N?.pos?.delete_confirm || 'Hapus meja ini?')
      .replace(':number', this.tableNumber);
  AppAlert.confirm({ title: window.COOCA_I18N?.common?.confirm || 'Konfirmasi', message: msg });
  ```

### Layer 3: Controller & Service Responses (Flash Alerts & JSON API)
- Dilarang hardcoded flash message atau JSON response di Controller / Action:
  ```php
  // ❌ SALAH
  return back()->with('success', 'Transaksi kasir berhasil disimpan.');
  
  // ✅ BENAR
  return back()->with('success', __('messages.pos.order_placed', ['number' => $order->order_number]));
  return response()->json(['message' => __('common.saved_successfully')]);
  ```

### Layer 4: Domain Exceptions & Business Logic Errors
- Seluruh Exception di `app/Domain/*/Exceptions/` wajib translatable (dilarang string mentah):
  ```php
  // ❌ SALAH
  throw new DomainException("Stok barang {$item} tidak cukup di gudang!");

  // ✅ BENAR
  throw new DomainException(__('exceptions.stock.insufficient', [
      'item'      => $item->name,
      'warehouse' => $warehouse->name,
      'available' => $stock->quantity,
      'requested' => $requestedQty,
  ]));
  ```

### Layer 5: Form Request Validation Messages
- Seluruh validasi request menggunakan key `validation.php` atau method `messages()` yang translatable:
  ```php
  public function messages(): array
  {
      return [
          'table_number.required' => __('validation.custom.table_number.required'),
          'capacity.min'          => __('validation.custom.capacity.min'),
      ];
  }
  ```

### Layer 6: Format Moneter, Tanggal, Notifikasi & Audit Logs
- **Format Rupiah / Currency:**
  - `id` (Bahasa Indonesia): `Rp 250.000` (Pemisah ribuan titik `.`, desimal koma `,`)
  - `en` (English): `IDR 250,000` atau `Rp 250,000` (Pemisah ribuan koma `,`, desimal titik `.`)
- **Format Tanggal & Waktu:**
  - `id`: `29 September 2026, 14:30 WIB` (`d F Y`)
  - `en`: `September 29, 2026, 02:30 PM` (`F d, Y`)
- **Notifikasi WhatsApp & Email:** Template pesan di-render dari `notifications.php`.
- **Audit Logs:** Deskripsi aksi di `audit_logs` di-record dari `audit.php`.
- **Tipografi Angka:** Wajib menambahkan class Tailwind `tabular-nums` untuk perataan angka moneter.

---

## 🗂️ Struktur Standar Kamus Modular (`lang/`)

```
lang/
├── id/                                 lang/en/
│   ├── common.php        ───────────►  ├── common.php        (Aksi universal, modal, pagination)
│   ├── messages.php      ───────────►  ├── messages.php      (Flash alerts: created, updated, deleted)
│   ├── exceptions.php    ───────────►  ├── exceptions.php    (Domain business rule exceptions)
│   ├── notifications.php ───────────►  ├── notifications.php (Template pesan WA Meta & Email HTML)
│   ├── audit.php         ───────────►  ├── audit.php         (Deskripsi audit trail & system logs)
│   ├── pos.php           ───────────►  ├── pos.php           (Terminal, Meja, KDS, Shift, Struk, Void)
│   ├── inventory.php     ───────────►  ├── inventory.php     (Produk, Stok, Gudang, Resep BOM, Supplier)
│   ├── finance.php       ───────────►  ├── finance.php       (Buku Kas, Akun Bank, Laba Rugi, Pajak)
│   ├── settings.php      ───────────►  ├── settings.php      (Profil Usaha, Printer, Integrasi, Role)
│   ├── storefront.php    ───────────►  ├── storefront.php    (Keranjang, Checkout, Resi, Ongkir)
│   ├── auth.php          ───────────►  ├── auth.php          (Login, OTP, Password, PIN Supervisor)
│   └── validation.php    ───────────►  └── validation.php    (Pesan Error Validasi Form)
```

---

## 🌐 Komponen Language Switcher Bento Apple HIG

Komponen pengalih bahasa terpasang di Topbar dan Footer Storefront:
* **Visual:** Tombol frosted glass elegan dengan ikon Lucide `globe` (tanpa emoji bendera yang rawan masalah rendering OS).
* **Interaksi:** Pilihan dropdown atau segmented toggle `[ ID | EN ]` yang langsung menyimpan preferensi ke session dan database user.

```html
<div class="relative inline-flex items-center" x-data="{ open: false }">
    <button @click="open = !open" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg border border-black/[0.08] dark:border-white/[0.1] bg-white/70 dark:bg-[#1C1C1E]/70 text-xs font-semibold text-black dark:text-white hover:bg-black/[0.04] transition">
        <i data-lucide="globe" class="w-3.5 h-3.5 text-black/60 dark:text-white/60"></i>
        <span>{{ strtoupper(app()->getLocale()) }}</span>
        <i data-lucide="chevron-down" class="w-3 h-3 text-black/40"></i>
    </button>
    <div x-show="open" @click.away="open = false" class="absolute right-0 top-full mt-1.5 w-28 p-1 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-lg z-50">
        <a href="?lang=id" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-black dark:text-white hover:bg-black/[0.05]">
            <span>Bahasa (ID)</span>
            @if(app()->getLocale() === 'id')<i data-lucide="check" class="w-3 h-3 text-[#007AFF]"></i>@endif
        </a>
        <a href="?lang=en" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium text-black dark:text-white hover:bg-black/[0.05]">
            <span>English (EN)</span>
            @if(app()->getLocale() === 'en')<i data-lucide="check" class="w-3 h-3 text-[#007AFF]"></i>@endif
        </a>
    </div>
</div>
```

---

## 🚀 Cara Menjalankan Audit Multi-Bahasa pada Folder

Jalankan audit teks menggunakan prompt berikut:

```text
Jalankan AUDIT TEKS MULTI-BAHASA (i18n & l10n) pada folder @[c:\laragon\www\cooca_core\resources\views\app\pos] menggunakan skill multi-language-and-i18n:
1. Pindai seluruh string bahasa Indonesia mentah (hardcoded text) di Blade dan Alpine.js script.
2. Ekstraksi daftar kamus key ke berkas lang/id/pos.php dan siapkan padanan bahasa Inggrisnya di lang/en/pos.php.
3. Ganti teks mentah dengan helper {{ __('pos.key') }} dan window.COOCA_I18N.
4. Pastikan pemformatan moneter, jam, dan tanggal sudah mendukung switch ID dan EN.
Sajikan Laporan Temuan Teks dan Rekomendasi Ekstraksi Kamusnya.
```
