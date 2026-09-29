# RENCANA IMPLEMENTASI TEKNIS SUPER LENGKAP: 22 FASE PENGEMBANGAN
## Penataan Arsitektur Storefront Publik COOCA, Keamanan Multi-Tenant, i18n & 25 Tema Industri Otentik
### (PRD-20, PRD-21, PRD-22, PRD-23, PRD-24)

---

## 📑 DAFTAR ISI

1. [Eksekutif Ringkasan & Prinsip Rekayasa](#1-eksekutif-ringkasan--prinsip-rekayasa)
2. [Matriks Ikhtisar 22 Fase Implementasi](#2-matriks-ikhtisar-22-fase-implementasi)
3. [Rincian Kerja Detail 22 Fase Implementasi](#3-rincian-kerja-detail-22-fase-implementasi)
   - [Fase 1: Fondasi Keamanan Multi-Tenant & Controller Hardening](#fase-1-fondasi-keamanan-multi-tenant--controller-hardening)
   - [Fase 2: Standarisasi Kamus Multi-Bahasa Full-Stack (`lang/id/` & `lang/en/`)](#fase-2-standarisasi-kamus-multi-bahasa-full-stack-langid--langen)
   - [Fase 3: Refactoring Shell Layout Storefront (`layouts/app.blade.php` & Partials)](#fase-3-refactoring-shell-layout-storefront-layoutsappbladephp--partials)
   - [Fase 4: Ekosistem Komponen Bento Reusable & Shared Widgets](#fase-4-ekosistem-komponen-bento-reusable--shared-widgets)
   - [Fase 5: Tema 1 – Coffee Shop & Cafe (`fnb_cafe` / `artisan_brew`)](#fase-5-tema-1--coffee-shop--cafe-fnb_cafe--artisan_brew)
   - [Fase 6: Tema 2 – Restoran / Rumah Makan (`fnb_resto` / `nusantara_feast`)](#fase-6-tema-2--restoran--rumah-makan-fnb_resto--nusantara_feast)
   - [Fase 7: Tema 3 – Cloud Kitchen & Delivery Only (`fnb_cloud_kitchen` / `neon_crunch`)](#fase-7-tema-3--cloud-kitchen--delivery-only-fnb_cloud_kitchen--neon_crunch)
   - [Fase 8: Tema 4 – Bakery & Cake Shop (`fnb_bakery` / `velvet_patisserie`)](#fase-8-tema-4--bakery--cake-shop-fnb_bakery--velvet_patisserie)
   - [Fase 9: Tema 5 & 6 – Catering Prasmanan + Diet & Healthy Catering (`fnb_catering` & `fnb_diet_catering`)](#fase-9-tema-5--6--catering-prasmanan--diet--healthy-catering-fnb_catering--fnb_diet_catering)
   - [Fase 10: Tema 7 & 8 – Reseller Retail & Distributor FMCG (`retail_reseller` & `trading_fmcg`)](#fase-10-tema-7--8--reseller-retail--distributor-fmcg-retail_reseller--trading_fmcg)
   - [Fase 11: Tema 9 & 10 – Apotek & Toko Obat + Kerajinan Tangan (`retail_pharmacy` & `creative_craft`)](#fase-11-tema-9--10--apotek--toko-obat--kerajinan-tangan-retail_pharmacy--creative_craft)
   - [Fase 12: Tema 11 – Bengkel Mobil & Motor (`service_workshop` / `apex_velocity`)](#fase-12-tema-11--bengkel-mobil--motor-service_workshop--apex_velocity)
   - [Fase 13: Tema 13 – Barbershop & Salon Kecantikan (`service_salon` / `aura_glamour`)](#fase-13-tema-13--barbershop--salon-kecantikan-service_salon--aura_glamour)
   - [Fase 14: Tema 12 & 15 – Auto Detailing & Laundry Kiloan (`service_autowash` & `service_laundry`)](#fase-14-tema-12--15--auto-detailing--laundry-kiloan-service_autowash--service_laundry)
   - [Fase 15: Tema 14 & 16 – Event Organizer + Digital Agency (`service_event_organizer` & `service_digital_agency`)](#fase-15-tema-14--16--event-organizer--digital-agency-service_event_organizer--service_digital_agency)
   - [Fase 16: Tema 17 & 18 – Konveksi Garment & Penjahit Jas Custom (`mfg_garment` & `mfg_custom_tailor`)](#fase-16-tema-17--18--konveksi-garment--penjahit-jas-custom-mfg_garment--mfg_custom_tailor)
   - [Fase 17: Tema 19 & 20 – Furniture Woodworking & Percetakan (`mfg_furniture` & `mfg_printing`)](#fase-17-tema-19--20--furniture-woodworking--percetakan-mfg_furniture--mfg_printing)
   - [Fase 18: Tema 21 & 22 – Kosmetik Skincare & Pabrik Plastik/Metal (`mfg_skincare` & `mfg_plastic_metal`)](#fase-18-tema-21--22--kosmetik-skincare--pabrik-plastikmetal-mfg_skincare--mfg_plastic_metal)
   - [Fase 19: Tema 23, 24, 25 – Frozen Food, Kontraktor Bangunan & Peternakan (`fnb_frozen_food`, `service_contractor`, `agri_farming`)](#fase-19-tema-23-24-25--frozen-food-kontraktor-bangunan--peternakan-fnb_frozen_food-service_contractor-agri_farming)
   - [Fase 20: Engine Dynamic Context-Aware Auto-Hiding & Graceful Routing](#fase-20-engine-dynamic-context-aware-auto-hiding--graceful-routing)
   - [Fase 21: Automated Testing Suite & Validasi Regresi 100%](#fase-21-automated-testing-suite--validasi-regresi-100)
   - [Fase 22: Production Hardening, Benchmark Performa (<80ms) & Dokumentasi Layer 2](#fase-22-production-hardening-benchmark-performa-80ms--dokumentasi-layer-2)
4. [Matriks Berkas Terdampak (Affected Files Matrix)](#4-matriks-berkas-terdampak-affected-files-matrix)
5. [Protokol Keselamatan Kerja & Definition of Done (DoD)](#5-protokol-keselamatan-kerja--definition-of-done-dod)

---

## 1. Eksekutif Ringkasan & Prinsip Rekayasa

Dokumen ini memuat rencana kerja teknis yang dirinci dalam **22 fase eksekusi bertahap** untuk mentransformasikan ekosistem Storefront Publik COOCA (`resources/views/public/storefront/`). Setiap fase dirancang dengan batasan tugas yang jelas, bebas asumsi (*code-first factuality*), serta mengintegrasikan:
- **Keamanan Siber:** Isolasi multi-tenant mutlak (`where('business_id', $business->id)`), anti-IDOR shield, XSS protection via directive `@js()`, dan rate limiting.
- **Internasionalisasi (i18n):** 100% zero hardcoded strings dengan kamus modular `lang/id/storefront.php` dan `lang/en/storefront.php` serta injeksi JavaScript `window.COOCA_I18N`.
- **Adaptasi 25 Industri Resmi:** 25 tema otentik dengan palet warna HSL, Google Fonts berkarakter, dan formulir interaktif spesifik (resep obat, fitting tailor, kalkulator semen, antrean cuci mobil, dll.).
- **Desain Bento Apple HIG v2.0:** Geometri squircle kontinu, touch targets 44px–52px, tabular-nums untuk angka, dan nol emoji pada elemen operasional.

---

## 2. Matriks Ikhtisar 22 Fase Implementasi

```
┌─────────────────────────────────────────────────────────────────────────────────────────────────┐
│ ROADMAP 22 FASE IMPLEMENTASI STOREFRONT PUBLIK COOCA                                            │
├──────┬─────────────────────────────────────────────────┬──────────────────────────┬─────────────┤
│ Fase │ Nama Fase & Fokus Pekerjaan                     │ Target Berkas / Domain   │ Status      │
├──────┼─────────────────────────────────────────────────┼──────────────────────────┼─────────────┤
│ 01   │ Multi-Tenant Security & Controller Hardening    │ Controllers & Middleware │ READY       │
│ 02   │ Standarisasi Kamus Multi-Bahasa (i18n/l10n)     │ lang/id/ & lang/en/      │ READY       │
│ 03   │ Refactoring Shell Layout & Navigation Partials  │ layouts/app, navbar      │ READY       │
│ 04   │ Shared Bento Components & Reusable Widgets      │ components/*             │ READY       │
│ 05   │ Tema 1: Coffee Shop & Cafe                      │ themes/fnb_cafe/         │ READY       │
│ 06   │ Tema 2: Restoran / Rumah Makan                  │ themes/fnb_resto/        │ READY       │
│ 07   │ Tema 3: Cloud Kitchen & Delivery Only           │ themes/fnb_cloud_kitchen/│ READY       │
│ 08   │ Tema 4: Bakery & Cake Shop                      │ themes/fnb_bakery/       │ READY       │
│ 09   │ Tema 5 & 6: Catering Prasmanan & Diet Catering  │ themes/fnb_catering/ dll │ READY       │
│ 10   │ Tema 7 & 8: Reseller Retail & Distributor FMCG  │ themes/retail_reseller/  │ READY       │
│ 11   │ Tema 9 & 10: Apotek Medis & Kerajinan Seni      │ themes/retail_pharmacy/  │ READY       │
│ 12   │ Tema 11: Bengkel Mobil & Motor                  │ themes/service_workshop/ │ READY       │
│ 13   │ Tema 13: Barbershop & Salon Kecantikan          │ themes/service_salon/    │ READY       │
│ 14   │ Tema 12 & 15: Auto Detailing & Laundry Kiloan   │ themes/service_autowash/ │ READY       │
│ 15   │ Tema 14 & 16: Event Organizer & Digital Agency  │ themes/service_event_org/│ READY       │
│ 16   │ Tema 17 & 18: Garment Mfg & Penjahit Custom     │ themes/mfg_garment/ dll  │ READY       │
│ 17   │ Tema 19 & 20: Furniture & Percetakan Digital    │ themes/mfg_furniture/ dll│ READY       │
│ 18   │ Tema 21 & 22: Kosmetik Skincare & Pabrik Logam  │ themes/mfg_skincare/ dll │ READY       │
│ 19   │ Tema 23, 24, 25: Frozen, Kontraktor & Agribisnis│ themes/fnb_frozen/ dll   │ READY       │
│ 20   │ Engine Auto-Hiding & Context-Aware Routing      │ StorefrontThemeService   │ READY       │
│ 21   │ Automated Testing Suite (Feature & Unit Tests)  │ tests/Feature/Storefront │ READY       │
│ 22   │ Production Hardening, Benchmark & Layer 2 Docs  │ docs/ & AiWorkHistory    │ READY       │
└──────┴─────────────────────────────────────────────────┴──────────────────────────┴─────────────┘
```

---

## 3. Rincian Kerja Detail 22 Fase Implementasi

---

### 🛡️ Fase 1: Fondasi Keamanan Multi-Tenant & Controller Hardening
1. **Perbaikan Query IDOR Scoping:**
   - Pada [`PublicStorefrontController.php:71-75`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Storefront/PublicStorefrontController.php#L71-L75), ubah query `Post::where('is_published', true)` menjadi `Post::when($hasBusinessId, fn ($q) => $q->where('business_id', $business->id))->where('is_published', true)`.
   - Pastikan seluruh query relasi (`Product`, `ProductCategory`, `PosTable`, `CommercePaymentMethod`, `CommerceShippingRule`) memiliki scope `business_id`.
2. **Context Resolution & Injeksi Data Industri:**
   - Injeksi flag `$industryCategory` dan `$isDiningIndustry` dari controller ke view untuk mengontrol rendering komponen.
3. **Standarisasi Respon JSON & Throttling:**
   - Lokalisasikan pesan validasi dan respon JSON pada `PublicOrderTrackingController` dan `PublicReservationController`.

---

### 🌐 Fase 2: Standarisasi Kamus Multi-Bahasa Full-Stack (`lang/id/` & `lang/en/`)
1. **Penyusunan Berkas `lang/id/storefront.php`:**
   - Membuat 150+ translation keys terstruktur mencakup grup: `nav`, `hero`, `catalog`, `product_detail`, `checkout`, `tracking`, `reservation`, `contact`, `about`, `blog`, `industries`, `forms`, `messages`.
2. **Penyusunan Berkas `lang/en/storefront.php`:**
   - Menyusun versi terjemahan bahasa Inggris profesional yang simetris 1-to-1 dengan kunci bahasa Indonesia.
3. **Jembatan Injeksi JavaScript Global:**
   - Injeksi `window.COOCA_I18N = @js(__('storefront.messages'));` di layout utama untuk reaktivitas Alpine.js.

---

### 🖥️ Fase 3: Refactoring Shell Layout Storefront (`layouts/app.blade.php` & Partials)
1. **`layouts/app.blade.php`:**
   - Integrasi dynamic Google Fonts loader sesuai tema aktif.
   - Pemasangan meta tag OpenGraph dinamis, Favicon tenant, dan tag Apple Mobile Web App.
   - Penataan floating cart pill yang menghormati safe-area-inset pada smartphone iOS.
2. **`layouts/navbar.blade.php`:**
   - Bento navbar dengan mekanisme *active-page auto-hiding* (hanya menampilkan menu yang diaktifkan merchant).
   - Language switcher Bento (ID / EN) yang mempertahankan parameter URL aktif.
3. **`layouts/footer.blade.php`:**
   - Footer responsif memuat direktori multi-cabang, jam operasional, tautan WhatsApp resmi, dan legalitas usaha.

---

### 🧱 Fase 4: Ekosistem Komponen Bento Reusable & Shared Widgets
1. **`components/product_card.blade.php`:**
   - Kartu produk Bento responsif dengan gambar aspect-ratio konsisten, badge ketersediaan stok, harga `tabular-nums`, dan tombol tambah keranjang aman (`@js()`).
2. **`components/cart_floating_pill.blade.php`:**
   - Pill melayang di bawah layar dengan penghitung jumlah item real-time dan subtotal dinamis.
3. **`components/verified_badge.blade.php`:**
   - Badge verifikasi legalitas toko (BPOM, Kemenkes, Halal MUI, Hak Cipta).
4. **`components/whatsapp_inquiry.blade.php`:**
   - Tombol konsultasi 1-klik WhatsApp dengan template pesan pre-filled memuat nama produk dan URL.

---

### ☕ Fase 5: Tema 1 – Coffee Shop & Cafe (`fnb_cafe` / `artisan_brew`)
- **Tipografi & Warna:** `Playfair Display` / `Inter`, Palet Roasted Espresso `#8B5A2B` & Crema `#C88A58`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `product_detail.blade.php`.
- **Fitur Khusus:** Bean tasting notes pill (Fruity, Nutty), roast level visualizer, grind size selector stepper (Biji Utuh, Kasar, Sedang, Halus), dan form order meja via scan QR.

---

### 🍛 Fase 6: Tema 2 – Restoran / Rumah Makan (`fnb_resto` / `nusantara_feast`)
- **Tipografi & Warna:** `DM Serif Display` / `Plus Jakarta Sans`, Palet Chili Red `#991B1B` & Turmeric `#D97706`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `reservation.blade.php`.
- **Fitur Khusus:** Tingkat kepedasan interaktif (Level 0–5) dengan ikon cabai semantik, checkbox tambahan lauk sambal/lalap, dan form reservasi meja VIP & Lesehan.

---

### 📦 Fase 7: Tema 3 – Cloud Kitchen & Delivery Only (`fnb_cloud_kitchen` / `neon_crunch`)
- **Tipografi & Warna:** `Outfit` / `Inter`, Palet Sriracha Red `#DC2626` & Cheddar Gold `#F59E0B`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `checkout.blade.php`.
- **Fitur Khusus:** Combo meal upsize builder (Ala Carte $\rightarrow$ Paket Combo + Fries + Soda), countdown flash deal bar, dan switcher cepat Takeaway / Delivery.

---

### 🎂 Fase 8: Tema 4 – Bakery & Cake Shop (`fnb_bakery` / `velvet_patisserie`)
- **Tipografi & Warna:** `Cormorant Garamond` / `Poppins`, Palet Velvet Berry `#BE185D` & Champagne Gold `#D4AF37`.
- **Implementasi Views:** `home.blade.php`, `product_detail.blade.php`, `checkout.blade.php`.
- **Fitur Khusus:** Pemilih diameter kue (16/20/24 cm), form tulisan ucapan kustom di atas plat cokelat kue, kalender pre-order H-3, dan add-on lilin angka.

---

### 🍱 Fase 9: Tema 5 & 6 – Catering Prasmanan + Diet & Healthy Catering (`fnb_catering` & `fnb_diet_catering`)
- **Tipografi & Warna:** `DM Serif` & `Plus Jakarta Sans` / `Inter`, Palet Emerald `#059669` & Amber `#D97706`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `reservation.blade.php`.
- **Fitur Khusus:** Kalkulator jumlah porsi prasmanan (50–500 Porsi), kalender booking hari-H resepsi, weekly meal schedule matrix, dan badge rincian kalori makronutrisi.

---

### 🛒 Fase 10: Tema 7 & 8 – Reseller Retail & Distributor FMCG (`retail_reseller` & `trading_fmcg`)
- **Tipografi & Warna:** `Plus Jakarta Sans` / `Inter`, Palet Retail Green `#16A34A` & Wholesale Blue `#1D4ED8`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `checkout.blade.php`.
- **Fitur Khusus:** Super-dense catalog grid, instant fast-stepper `+ / -` tanpa membuka PDP, progress bar gratis ongkir, tabel diskon grosir per karton/dus, dan selector armada truk box.

---

### 💊 Fase 11: Tema 9 & 10 – Apotek & Toko Obat + Kerajinan Tangan (`retail_pharmacy` & `creative_craft`)
- **Tipografi & Warna:** `Inter` & `Quicksand`, Palet Clinical Teal `#0D9488` & Terracotta Clay `#C2410C`.
- **Implementasi Views:** `home.blade.php`, `product_detail.blade.php`, `contact.blade.php`.
- **Fitur Khusus:** Encrypted prescription upload dropzone, badge resmi BPOM & Kemenkes, penanda obat bebas vs keras (K Merah), form grafir ukir nama kustom, dan narasi cerita pengrajin lokal.

---

### 🏍️ Fase 12: Tema 11 – Bengkel Mobil & Motor (`service_workshop` / `apex_velocity`)
- **Tipografi & Warna:** `Chakra Petch` / `Inter`, Palet Racing Orange `#EA580C` & Dark Garage Slate `#0F172A`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `reservation.blade.php`.
- **Fitur Khusus:** Vehicle model compatibility selector (Merek, Model, Tahun), booking jadwal antrean servis dengan input nomor polisi dan keluhan mesin, serta switcher opsi pasang di bengkel.

---

### 💇 Fase 13: Tema 13 – Barbershop & Salon Kecantikan (`service_salon` / `aura_glamour`)
- **Tipografi & Warna:** `Playfair Display` / `Plus Jakarta Sans`, Palet Rose Berry `#9D174D` & Rose Gold `#B76E79`.
- **Implementasi Views:** `home.blade.php`, `product_detail.blade.php`, `reservation.blade.php`.
- **Fitur Khusus:** Pemilih kapster/stylist/beautician, badge estimasi durasi treatment (*Durasi: 45 Menit*), before-after transformation slider, dan appointment calendar scheduler.

---

### 🧼 Fase 14: Tema 12 & 15 – Auto Detailing & Laundry Kiloan (`service_autowash` & `service_laundry`)
- **Tipografi & Warna:** `Orbitron` & `Plus Jakarta Sans` / `Inter`, Palet Hydro Blue `#2563EB` & Ocean Blue `#0284C7`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `order_tracking.blade.php`.
- **Fitur Khusus:** Tabel komparasi paket 9H ceramic coating (Bronze/Silver/Gold/Diamond), indikator live antrean cuci bay, slider estimasi berat timbangan cucian kiloan, pemilih aroma parfum, dan sakelar layanan kilat 3 jam.

---

### 💍 Fase 15: Tema 14 & 16 – Event Organizer + Digital Agency (`service_event_organizer` & `service_digital_agency`)
- **Tipografi & Warna:** `Cormorant Garamond` & `Space Grotesk`, Palet Royal Rose `#BE185D` & Digital Indigo `#4F46E5`.
- **Implementasi Views:** `home.blade.php`, `about.blade.php`, `reservation.blade.php`.
- **Fitur Khusus:** Tabel komparasi paket pernikahan/event (Silver, Gold, Platinum), booking kalender tanggal hari-H, kartu paket retainer bulanan agensi, dan form brief proyek proposal.

---

### 🧵 Fase 16: Tema 17 & 18 – Konveksi Garment & Penjahit Jas Custom (`mfg_garment` & `mfg_custom_tailor`)
- **Tipografi & Warna:** `Barlow Semi Condensed` & `Cormorant Garamond` / `Inter`, Palet Industrial Navy `#1E293B` & Bespoke Noir `#18181B`.
- **Implementasi Views:** `home.blade.php`, `product_detail.blade.php`, `checkout.blade.php`.
- **Fitur Khusus:** Tabel MOQ lusinan/kodi, dropzone file mockup sablon/bordir, formulir input ukuran tubuh (lingkar dada, pinggang, lengan), opsi furing sutra, dan booking sesi fitting.

---

### 🪑 Fase 17: Tema 19 & 20 – Furniture Woodworking & Percetakan (`mfg_furniture` & `mfg_printing`)
- **Tipografi & Warna:** `Cormorant Garamond` & `Syne` / `Inter`, Palet Warm Teak `#78350F` & Studio Violet `#7C3AED`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `product_detail.blade.php`.
- **Fitur Khusus:** Kustomisasi dimensi furnitur (PxLxT), radio selector finishing duco/melamine, dropzone file desain cetak hingga 50MB (AI/PDF/PSD), pemilih gramatur kertas GSM, dan opsi laminasi doff/glossy.

---

### 🧴 Fase 18: Tema 21 & 22 – Kosmetik Skincare & Pabrik Plastik/Metal (`mfg_skincare` & `mfg_plastic_metal`)
- **Tipografi & Warna:** `Playfair Display` & `Space Grotesk` / `Inter`, Palet Rose Pearl `#BE185D` & Machined Steel `#334155`.
- **Implementasi Views:** `home.blade.php`, `product_detail.blade.php`, `checkout.blade.php`.
- **Fitur Khusus:** Tab kandungan bahan aktif (Niacinamide, Ceramide), nomor BPOM resmi, formulir RFQ cetakan mold injeksi plastik, dan tabel toleransi presisi mesin CNC.

---

### 🌾 Fase 19: Tema 23, 24, 25 – Frozen Food, Kontraktor Bangunan & Peternakan (`fnb_frozen_food`, `service_contractor`, `agri_farming`)
- **Tipografi & Warna:** `Plus Jakarta Sans` & `Barlow Semi Condensed`, Palet Icy Blue `#0284C7`, Solid Cement `#475569`, Harvest Green `#15803D`.
- **Implementasi Views:** `home.blade.php`, `catalog.blade.php`, `reservation.blade.php`.
- **Fitur Khusus:** Badge kemasan vacuum & instruksi cara memasak beku, kalkulator volume semen/bata per m², form survei lokasi proyek fisik, informasi batch panen segar, dan sertifikasi organik.

---

### 🔄 Fase 20: Engine Dynamic Context-Aware Auto-Hiding & Graceful Routing
1. **Otomasi Eliminasi Elemen Non-Relevan:**
   - Menyembunyikan opsi "Makan di Tempat (Dine-In)" dan pemilihan meja dari formulir checkout untuk 19 sektor non-F&B.
2. **Graceful Redirect:**
   - Jika pengunjung mengakses URL halaman yang dinonaktifkan merchant (misal: `/reservasi` padahal diset `false`), sistem secara cerdas mengalihkan ke `/` tanpa error 404.
3. **Penyesuaian Terminologi Otomatis:**
   - Label reservasi otomatis beralih (*"Reservasi Meja"* untuk Resto, *"Booking Servis"* untuk Bengkel, *"Booking Treatment"* untuk Salon, *"Survei Proyek"* untuk Kontraktor).

---

### 🧪 Fase 21: Automated Testing Suite & Validasi Regresi 100%
1. **Pembuatan Suite Pengujian Terpadu:**
   - Buat berkas test baru: `tests/Feature/PublicStorefrontComprehensiveSuiteTest.php`.
   - Menguji isolasi multi-tenant query artikel dan produk (IDOR prevention).
   - Menguji persistensi bahasa i18n (ID vs EN) dan integritas kamus terjemahan.
   - Menguji fungsionalitas auto-hiding opsi dine-in pada berbagai klaster industri.
2. **Eksekusi Pengujian Otomatis:**
   - Jalankan `php -l` pada seluruh berkas template Blade dan PHP.
   - Jalankan `php artisan test --filter=Storefront` (Wajib lolos 100%).

---

### 📊 Fase 22: Production Hardening, Benchmark Performa (<80ms) & Dokumentasi Layer 2
1. **Optimasi Aset & Latensi Render:**
   - Verifikasi TTFB render Storefront < 80ms pada koneksi 4G standar.
   - Zero N+1 query melalui eager loading pada relasi `Product` dan `ProductCategory`.
2. **Pembaruan Dokumentasi Sistem (Layer 2):**
   - Perbarui [`docs/system/workflows/customer-storefront-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/customer-storefront-flow.md).
   - Perbarui [`docs/system/INDEX.md`](file:///c:/laragon/www/cooca_core/docs/system/INDEX.md).
   - Catat seluruh riwayat pekerjaan pada [`docs/AiWorkHistory.md`](file:///c:/laragon/www/cooca_core/docs/AiWorkHistory.md).

---

## 4. Matriks Berkas Terdampak (Affected Files Matrix)

```
┌─────────────────────────────────────────────────────────────┬─────────────────────────────────┐
│ Jalur Berkas (File Path)                                    │ Jenis Perubahan & Tanggung Jawab│
├─────────────────────────────────────────────────────────────┼─────────────────────────────────┤
│ app/Http/Controllers/Web/Storefront/                        │                                 │
│ ├── PublicStorefrontController.php                          │ Scoping IDOR Post & Flag Ind.   │
│ app/Http/Controllers/Web/Commerce/                          │                                 │
│ ├── PublicOrderTrackingController.php                       │ Lokalisasi Pesan JSON & Request │
│ ├── PublicReservationController.php                         │ Lokalisasi Pesan Form Booking   │
│ lang/id/storefront.php                                      │ Kamus Bahasa Indonesia Lengkap  │
│ lang/en/storefront.php                                      │ Kamus English Lengkap           │
│ resources/views/public/storefront/                          │                                 │
│ ├── layouts/app.blade.php                                   │ i18n Bridge & Fonts Loader      │
│ ├── layouts/navbar.blade.php                                │ Auto-Hide Nav & Lang Switcher   │
│ ├── layouts/footer.blade.php                                │ Dynamic Branches & Geolocations │
│ ├── components/product_card.blade.php                       │ Bento Adaptive Product Card     │
│ ├── components/cart_floating_pill.blade.php                 │ Floating Cart Pill (iOS Safe)   │
│ ├── components/verified_badge.blade.php                     │ Legal Trust Pills               │
│ ├── themes/fnb_cafe/*                                       │ 1. Coffee Shop & Cafe           │
│ ├── themes/fnb_resto/*                                      │ 2. Restoran / Rumah Makan       │
│ ├── themes/fnb_cloud_kitchen/*                              │ 3. Cloud Kitchen & Delivery     │
│ ├── themes/fnb_bakery/*                                     │ 4. Bakery & Cake Shop           │
│ ├── themes/fnb_catering/*                                   │ 5. Catering & Prasmanan         │
│ ├── themes/fnb_diet_catering/*                              │ 6. Diet & Healthy Catering      │
│ ├── themes/retail_reseller/*                                │ 7. Reseller & Toko Retail       │
│ ├── themes/trading_fmcg/*                                   │ 8. Distributor FMCG Trading     │
│ ├── themes/retail_pharmacy/*                                │ 9. Apotek & Toko Obat           │
│ ├── themes/creative_craft/*                                 │ 10. Kerajinan Tangan Craft      │
│ ├── themes/service_workshop/*                               │ 11. Bengkel Mobil & Motor       │
│ ├── themes/service_autowash/*                               │ 12. Auto Detailing & Cuci Mobil │
│ ├── themes/service_salon/*                                  │ 13. Barbershop & Salon          │
│ ├── themes/service_laundry/*                                │ 14. Laundry Kiloan & Satuan     │
│ ├── themes/service_event_organizer/*                        │ 15. Event & Wedding Organizer   │
│ ├── themes/service_digital_agency/*                         │ 16. Digital Creative Agency     │
│ ├── themes/mfg_garment/*                                    │ 17. Konveksi & Garment Mfg      │
│ ├── themes/mfg_custom_tailor/*                              │ 18. Penjahit Jas & Kebaya       │
│ ├── themes/mfg_furniture/*                                  │ 19. Furniture & Woodworking     │
│ ├── themes/mfg_printing/*                                   │ 20. Percetakan & Digital Print  │
│ ├── themes/mfg_skincare/*                                   │ 21. Kosmetik & Skincare Mfg     │
│ ├── themes/mfg_plastic_metal/*                              │ 22. Pabrik Plastik & Metal      │
│ ├── themes/fnb_frozen_food/*                                │ 23. Frozen Food Manufacturing   │
│ ├── themes/service_contractor/*                             │ 24. Kontraktor & Renovasi       │
│ ├── themes/agri_farming/*                                   │ 25. Peternakan & Pertanian      │
│ tests/Feature/PublicStorefrontComprehensiveSuiteTest.php    │ Suite Pengujian Otomatis        │
│ docs/system/workflows/customer-storefront-flow.md           │ Dokumentasi Layer 2 Workflow    │
│ docs/system/INDEX.md                                        │ Indeks Pengetahuan Sistem       │
│ docs/AiWorkHistory.md                                       │ Log Riwayat Rekayasa Sistem     │
└─────────────────────────────────────────────────────────────┴─────────────────────────────────┘
```

---

## 5. Protokol Keselamatan Kerja & Definition of Done (DoD)

1. **Safety Gate:** Sesuai direktif [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), AI Agent tidak memodifikasi file aplikasi sebelum rencana 22 fase ini disetujui oleh pengguna.
2. **Definition of Done (DoD):**
   - 100% dari 25 tema industri terimplementasi dengan palet, font, dan form spesifik yang berfungsi reaktif.
   - Seluruh hardcoded string terhubung ke `lang/id/storefront.php` dan `lang/en/storefront.php`.
   - Tidak ada celah IDOR multi-tenant pada query controller.
   - Seluruh automated tests lolos 100% tanpa error atau peringatan linting.
   - Dokumentasi Layer 2 diperbarui secara komprehensif.
