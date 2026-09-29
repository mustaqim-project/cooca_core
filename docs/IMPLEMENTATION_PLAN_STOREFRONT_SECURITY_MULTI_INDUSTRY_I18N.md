# RENCANA IMPLEMENTASI BERTAHAP (ROADMAP)
## Penataan Arsitektur Komprehensif Storefront Publik COOCA
### (PRD-20: Multi-Tenant Security, Dynamic Context-Aware Auto-Hiding & Multi-Language i18n/l10n)

---

## 1. Ringkasan Eksekusi
Dokumen ini memuat panduan teknis langkah-demi-langkah (Fase 1 s/d Fase 4) untuk mengeksekusi perbaikan komprehensif pada ekosistem Storefront Publik COOCA (`resources/views/public/storefront/`), backend controller, kamus bahasa, dan pengujian otomatis.

---

## 2. Roadmap Implementasi 4 Fase

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: Standarisasi Kamus Multi-Bahasa i18n (`lang/id/` & `lang/en/`)       │
│ • Membuat lang/id/storefront.php (Kamus Bahasa Indonesia lengkap)            │
│ • Membuat lang/en/storefront.php (Kamus English lengkap)                    │
│ • Menyiapkan helper injeksi window.COOCA_I18N untuk Alpine.js                │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 2: Backend Controller & Service Hardening                               │
│ • Perbaiki query Post scoping di PublicStorefrontController@home            │
│ • Lokalisasikan seluruh response JSON & validation message di Controller    │
│ • Terapkan context-aware filter industri pada query data terkait             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 3: Refactoring 11 Berkas Blade Storefront Bento Apple HIG              │
│ • layouts/app.blade.php        • contact.blade.php                           │
│ • home.blade.php               • about.blade.php                             │
│ • catalog.blade.php            • articles.blade.php                          │
│ • product_detail.blade.php     • article_detail.blade.php                    │
│ • checkout.blade.php           • reservation.blade.php                       │
│ • order_tracking.blade.php                                                   │
│ (Substitusi seluruh hardcoded string ke {{ __('storefront....') }} & @js())  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 4: Automated Testing & Layer 2 Documentation Update                     │
│ • Jalankan php artisan test --filter=Storefront                             │
│ • Tulis Feature Test baru: tests/Feature/PublicStorefrontComprehensiveSuiteTest│
│ • Buat docs/system/workflows/customer-storefront-flow.md                     │
│ • Update docs/system/INDEX.md dan docs/AiWorkHistory.md                      │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Pekerjaan per Fase

### 🟦 FASE 1: Standarisasi Kamus Multi-Bahasa i18n
1. **Buat Berkas `lang/id/storefront.php`:**
   - Menyusun 120+ translation keys terstruktur mencakup seluruh namespace: `nav`, `hero`, `catalog`, `product_detail`, `checkout`, `tracking`, `reservation`, `contact`, `about`, `blog`, `messages`.
2. **Buat Berkas `lang/en/storefront.php`:**
   - Menyusun versi terjemahan bahasa Inggris yang akurat, profesional, dan simetris 1-to-1 dengan versi bahasa Indonesia.

---

### 🟩 FASE 2: Backend Controller & Service Hardening
1. **`app/Http/Controllers/Web/Storefront/PublicStorefrontController.php`:**
   - **Fix IDOR Scoping:** Pada method `home()`, ubah query `Post::where('is_published', true)` menjadi `Post::when($hasBusinessId, fn ($q) => $q->where('business_id', $business->id))->where('is_published', true)`.
   - **Multi-Industry Data Preparation:** Kirim flag klaster industri `$isDiningIndustry` ke view checkout dan home.
2. **`app/Http/Controllers/Web/Commerce/PublicOrderTrackingController.php`:**
   - Lokalisasikan seluruh pesan validasi request pada method `submitCheckout`, `submitRequestOrder`, `submitCustomerPo`, `uploadProof`.
   - Lokalisasikan pesan flash message dan respon JSON dengan `__('storefront.messages....')`.
3. **`app/Http/Controllers/Web/Commerce/PublicReservationController.php`:**
   - Lokalisasikan pesan validasi request `submitReservation` dan pesan respon JSON.

---

### 🟨 FASE 3: Refactoring 11 Berkas Blade Storefront Bento Apple HIG
1. **`resources/views/public/storefront/layouts/app.blade.php`:**
   - Mengganti teks navigasi, cart bar, WhatsApp bubble, footer, dan login button dengan `{{ __('storefront....') }}`.
   - Menginjeksi `window.COOCA_I18N = @js(__('storefront.messages'));` ke dalam header/footer untuk Alpine.js.
2. **`resources/views/public/storefront/home.blade.php`:**
   - Mengganti teks Hero, badge verifikasi, CTA button, katalog teaser, layanan, jam operasional, dan pop-up modal.
   - Mengamankan tombol Add to Cart dengan `@js([...])`.
3. **`resources/views/public/storefront/catalog.blade.php`:**
   - Mengganti teks search placeholder, label filter, option sorting, tombol terapkan/reset, dan empty state.
   - Mengamankan tombol Add to Cart dengan `@js([...])`.
4. **`resources/views/public/storefront/product_detail.blade.php`:**
   - Mengganti teks stepper quantity, tombol Add to Cart & Beli Sekarang, badge ketersediaan stok, toast pemberitahuan, share links, dan produk serupa.
5. **`resources/views/public/storefront/checkout.blade.php`:**
   - Menerapkan *Dynamic Context-Aware Auto-Hiding* pada tombol "Makan di Tempat" dan pilihan meja hanya untuk klaster F&B.
   - Mengganti seluruh teks judul langkah (Step 1, 2, 3), label form pengiriman, opsi kurir, rincian biaya, dan catatan keamanan.
6. **`resources/views/public/storefront/order_tracking.blade.php`:**
   - Mengganti seluruh string status pesanan, banner instruksi TriPay, upload form bukti transfer, jadwal multi-drop batches, dan modal split bill.
7. **`resources/views/public/storefront/reservation.blade.php`:**
   - Menyesuaikan label dan form reservasi agar adaptif terhadap industri F&B vs Layanan/Jasa/Bengkel.
   - Mengganti seluruh hardcoded string ke kamus terjemahan.
8. **`resources/views/public/storefront/contact.blade.php`, `about.blade.php`, `articles.blade.php`, `article_detail.blade.php`:**
   - Mengganti seluruh teks profil usaha, nilai mutu, jam operasional, galeri, metadata artikel, author, dan share button.

---

### 🟧 FASE 4: Automated Testing & Layer 2 Documentation Update
1. **Eksekusi Pengujian Otomatis:**
   - Jalankan `php -l` pada seluruh berkas yang dimodifikasi.
   - Jalankan `php artisan test --filter=PublicStorefront`.
   - Buat suite pengujian baru `tests/Feature/PublicStorefrontComprehensiveSuiteTest.php` untuk memvalidasi isolasi tenant IDOR, multi-bahasa ID/EN, dan dynamic auto-hiding.
2. **Pembaruan Dokumentasi:**
   - Perbarui `docs/system/workflows/customer-storefront-flow.md`.
   - Perbarui indeks dokumentasi di `docs/system/INDEX.md`.
   - Catat riwayat pekerjaan di `docs/AiWorkHistory.md`.

---

## 4. Matriks Berkas Terdampak (Affected Files Matrix)

| Berkas (File Path) | Tipe | Deskripsi Pekerjaan |
|---|---|---|
| `lang/id/storefront.php` | Baru | Kamus Bahasa Indonesia lengkap (120+ keys) |
| `lang/en/storefront.php` | Baru | Kamus English lengkap (120+ keys) |
| `app/Http/Controllers/Web/Storefront/PublicStorefrontController.php` | Refactor | Scoping IDOR Post di `home()`, context flags |
| `app/Http/Controllers/Web/Commerce/PublicOrderTrackingController.php` | Refactor | Lokalisasi validasi & JSON response pesan |
| `app/Http/Controllers/Web/Commerce/PublicReservationController.php` | Refactor | Lokalisasi validasi & JSON response pesan |
| `resources/views/public/storefront/layouts/app.blade.php` | Refactor | Zero hardcoded string, injeksi `window.COOCA_I18N` |
| `resources/views/public/storefront/home.blade.php` | Refactor | Zero hardcoded string, `@js()` escaping |
| `resources/views/public/storefront/catalog.blade.php` | Refactor | Zero hardcoded string, localized filter & sorting |
| `resources/views/public/storefront/product_detail.blade.php` | Refactor | Zero hardcoded string, localized stock badges |
| `resources/views/public/storefront/checkout.blade.php` | Refactor | Zero hardcoded string, dine-in auto-hiding non-F&B |
| `resources/views/public/storefront/order_tracking.blade.php` | Refactor | Zero hardcoded string, localized status machine |
| `resources/views/public/storefront/reservation.blade.php` | Refactor | Zero hardcoded string, adaptive terminology |
| `resources/views/public/storefront/contact.blade.php` | Refactor | Zero hardcoded string, localized contact info |
| `resources/views/public/storefront/about.blade.php` | Refactor | Zero hardcoded string, localized story & badges |
| `resources/views/public/storefront/articles.blade.php` | Refactor | Zero hardcoded string, localized blog teasers |
| `resources/views/public/storefront/article_detail.blade.php` | Refactor | Zero hardcoded string, localized article view & share |
| `tests/Feature/PublicStorefrontComprehensiveSuiteTest.php` | Baru | Suite pengujian otomatis IDOR, i18n, auto-hiding |
| `docs/system/workflows/customer-storefront-flow.md` | Refactor | Dokumentasi Layer 2 alur transaksi storefront lengkap |
