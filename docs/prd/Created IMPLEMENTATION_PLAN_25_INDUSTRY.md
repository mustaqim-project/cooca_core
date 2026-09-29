Created IMPLEMENTATION_PLAN_25_INDUSTRY_STOREFRONT_THEMES_AND_SECURITY.md
Viewed INDEX.md:55-75
Edited INDEX.md
Edited README.md

Dokumen rencana implementasi teknis super lengkap telah selesai disusun dan dipecah ke dalam **22 fase pengembangan bertahap** yang terstruktur, mencakup penguatan keamanan multi-tenant (IDOR), lokalisasi dwibahasa (i18n/l10n ID/EN), standarisasi Bento Apple HIG, serta implementasi 25 tema industri otentik COOCA.

---

### 🗺️ Rencana Implementasi: 22 Fase Pengembangan Bertahap

Dokumen lengkap: [`docs/IMPLEMENTATION_PLAN_25_INDUSTRY_STOREFRONT_THEMES_AND_SECURITY.md`](file:///c:/laragon/www/cooca_core/docs/IMPLEMENTATION_PLAN_25_INDUSTRY_STOREFRONT_THEMES_AND_SECURITY.md)

| Fase | Nama Fase & Fokus Rekayasa | Cakupan Berkas & Fitur Utama |
| :---: | :--- | :--- |
| **01** | **Fondasi Keamanan Multi-Tenant & Controller Hardening** | Perbaikan query IDOR scoping artikel pada `PublicStorefrontController@home`, isolasi data `business_id`, dan rate limiting. |
| **02** | **Standarisasi Kamus Multi-Bahasa Full-Stack (i18n/l10n)** | Penyusunan 150+ pasang translasi pada `lang/id/storefront.php` & `lang/en/storefront.php`, serta injeksi `window.COOCA_I18N`. |
| **03** | **Refactoring Shell Layout Storefront & Partials** | Layout `layouts/app.blade.php`, Google Fonts loader dinamis, Bento navbar dengan *auto-hiding*, dan floating cart pill aman iOS. |
| **04** | **Ekosistem Komponen Bento Reusable & Shared Widgets** | `product_card`, `verified_badge`, `cart_floating_pill`, `whatsapp_inquiry` 1-klik, dan komponen rating bintang. |
| **05** | **Tema 1: Coffee Shop & Cafe (`fnb_cafe`)** | Tipografi `Playfair/Inter`, palet espresso, tasting notes (floral/nutty), gilingan biji kopi (v60/espresso), dan QR meja dine-in. |
| **06** | **Tema 2: Restoran / Rumah Makan (`fnb_resto`)** | Tipografi `DM Serif/Jakarta`, palet terracotta chili, level pedas (0–5), tambahan sambal/lalap, dan reservasi meja VIP/lesehan. |
| **07** | **Tema 3: Cloud Kitchen & Delivery Only (`fnb_cloud_kitchen`)** | Tipografi `Outfit/Inter`, palet sriracha red, combo upsize builder (+fries/drink), delivery/pickup switcher, flash deal countdown. |
| **08** | **Tema 4: Bakery & Cake Shop (`fnb_bakery`)** | Tipografi `Cormorant/Poppins`, palet berry velvet, diameter kue (16/20/24 cm), form tulisan ucapan plat cokelat, pre-order H-3. |
| **09** | **Tema 5 & 6: Catering Prasmanan + Diet Catering** | Kalkulator porsi prasmanan (50–500 porsi), kalender booking resepsi hari-H, weekly meal matrix, dan badge kalori makronutrisi. |
| **10** | **Tema 7 & 8: Reseller Retail & Distributor FMCG** | Super-dense catalog grid, stepper belanja instan `+ / -` di kartu produk, progress bar gratis ongkir, tabel grosir kartonan, armada truk. |
| **11** | **Tema 9 & 10: Apotek & Kerajinan Tangan Craft** | Dropzone unggah resep dokter privat terenkripsi, badge BPOM/Kemenkes, lingkaran obat bebas/keras, form grafir ukir nama kustom. |
| **12** | **Tema 11: Bengkel Mobil & Motor (`service_workshop`)** | Tipografi `Chakra Petch/Inter`, filter model motor/mobil, booking antrean servis mekanik, input nomor plat polisi, opsi pasang di bengkel. |
| **13** | **Tema 13: Barbershop & Salon Kecantikan (`service_salon`)** | Tipografi `Playfair/Jakarta`, pemilih kapster/beautician, estimasi durasi treatment (*45 menit*), before-after transformation slider. |
| **14** | **Tema 12 & 15: Auto Detailing & Laundry Kiloan** | Komparasi coating nano ceramic 9H, antrean live bay cuci mobil, slider estimasi berat kiloan cuci, pemilih aroma parfum, express 3 jam. |
| **15** | **Tema 14 & 16: Event Organizer & Digital Agency** | Tabel paket pernikahan WO (Silver/Gold/Platinum), booking tanggal acara, kartu scope of work agensi, dan form brief proposal RFQ. |
| **16** | **Tema 17 & 18: Konveksi Garment & Penjahit Jas Custom** | Tabel MOQ lusinan/kodi, dropzone mockup sablon/bordir, formulir detail ukuran tubuh (lingkar dada, pinggang, bahu), furing sutra. |
| **17** | **Tema 19 & 20: Furniture & Percetakan Digital** | Kustomisasi dimensi PxLxT, finishing duco/melamine, dropzone file cetak hingga 50MB (AI/PDF), gramatur kertas GSM, laminasi doff/glossy. |
| **18** | **Tema 21 & 22: Kosmetik Skincare & Pabrik Logam/CNC** | Tab bahan aktif (Ceramide/Niacinamide), nomor izin BPOM, formulir RFQ cetakan mold injeksi plastik, tabel toleransi mikron mesin CNC. |
| **19** | **Tema 23, 24, 25: Frozen Food, Kontraktor & Peternakan** | Badge kemasan vacuum & panduan masak beku, kalkulator volume semen per m², form survei lokasi proyek, info batch panen organik segar. |
| **20** | **Engine Dynamic Context-Aware Auto-Hiding & Routing** | 100% eliminasi otomatis opsi Dine-In pada checkout 19 sektor non-F&B, adaptasi terminologi reservasi, dan pengalihan cerdas non-aktif. |
| **21** | **Automated Testing Suite (Unit & Feature Test Suite)** | Pengujian isolasi multi-tenant query, persistensi bahasa i18n ID/EN, validasi auto-hiding industri, dan pengetesan bebas eror sintaks. |
| **22** | **Production Hardening, Benchmark Performa & Docs Update** | Verifikasi TTFB < 80ms, zero N+1 query, update dokumentasi Layer 2 (`customer-storefront-flow.md`, `system/INDEX.md`), dan log `AiWorkHistory.md`. |

---

### 📂 Struktur Direktori Blade Modular yang Siap Diterapkan

```
resources/views/public/storefront/
├── layouts/
│   ├── app.blade.php                 # Core shell (i18n injection, Alpine $store.cart, Google Fonts loader)
│   ├── navbar.blade.php              # Bento navbar with active-page auto-hiding
│   └── footer.blade.php              # Multi-branch geolocations & operational hours footer
│
├── components/                       # Shared Bento Components
│   ├── product_card.blade.php        # Adaptive Bento Product Card
│   ├── cart_floating_pill.blade.php  # Floating Cart Pill (iOS Safe Area)
│   ├── verified_badge.blade.php      # BPOM, Halal, Kemenkes Trust Pills
│   └── whatsapp_inquiry.blade.php    # 1-Click WhatsApp Direct Consultation
│
├── themes/                           # 25 Dedicated Industry Themes
│   ├── fnb_cafe/                     # 1. Coffee Shop & Cafe
│   ├── fnb_resto/                    # 2. Restoran / Rumah Makan
│   ├── fnb_cloud_kitchen/            # 3. Cloud Kitchen & Delivery Only
│   ├── fnb_bakery/                   # 4. Bakery & Cake Shop
│   ├── fnb_catering/                 # 5. Catering & Prasmanan
│   ├── fnb_diet_catering/            # 6. Diet & Healthy Catering
│   ├── retail_reseller/              # 7. Reseller & Toko Retail
│   ├── trading_fmcg/                 # 8. Distributor & Trading FMCG
│   ├── retail_pharmacy/              # 9. Apotek & Toko Obat
│   ├── creative_craft/               # 10. Kerajinan Tangan & Handmade Craft
│   ├── service_workshop/             # 11. Bengkel Mobil & Motor
│   ├── service_autowash/             # 12. Auto Detailing & Cuci Mobil
│   ├── service_salon/                # 13. Barbershop & Salon Kecantikan
│   ├── service_laundry/              # 14. Laundry Kiloan & Satuan
│   ├── service_event_organizer/      # 15. Event Organizer & Wedding Organizer
│   ├── service_digital_agency/       # 16. Digital Creative Agency / IT Software
│   ├── mfg_garment/                  # 17. Konveksi & Garment Manufacturing
│   ├── mfg_custom_tailor/            # 18. Penjahit Jas & Kebaya Custom
│   ├── mfg_furniture/                # 19. Furniture & Woodworking
│   ├── mfg_printing/                 # 20. Percetakan & Digital Printing
│   ├── mfg_skincare/                 # 21. Kosmetik & Skincare Production
│   ├── mfg_plastic_metal/            # 22. Pabrik Plastik & Metal Presisi
│   ├── fnb_frozen_food/              # 23. Frozen Food Manufacturing
│   ├── service_contractor/           # 24. Kontraktor & Renovasi Bangunan
│   └── agri_farming/                 # 25. Peternakan & Pertanian
│
└── [Core Fallback Views: home, catalog, product_detail, checkout, order_tracking, reservation, contact, about, articles]
```

---

> [!NOTE]
> Seluruh 22 fase implementasi di atas telah terintegrasi dengan dokumen master PRD:
> - [`docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md`](file:///c:/laragon/www/cooca_core/docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md)
> - [`docs/prd/PRD-20-COMPREHENSIVE-STOREFRONT-SECURITY-MULTI-INDUSTRY-I18N.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-20-COMPREHENSIVE-STOREFRONT-SECURITY-MULTI-INDUSTRY-I18N.md)
> - [`docs/prd/PRD-21-STOREFRONT-THEMES-FNB-CULINARY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-21-STOREFRONT-THEMES-FNB-CULINARY.md)
> - [`docs/prd/PRD-22-STOREFRONT-THEMES-RETAIL-COMMERCE.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-22-STOREFRONT-THEMES-RETAIL-COMMERCE.md)
> - [`docs/prd/PRD-23-STOREFRONT-THEMES-SERVICES-HEALTH-BEAUTY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-23-STOREFRONT-THEMES-SERVICES-HEALTH-BEAUTY.md)
> - [`docs/prd/PRD-24-STOREFRONT-THEMES-MANUFACTURING-CRAFT-AGRI.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-24-STOREFRONT-THEMES-MANUFACTURING-CRAFT-AGRI.md)
>
> Sesuai *Safety Gate Protocol*, konfirmasi persetujuan Anda diperlukan untuk memulai eksekusi implementasi teknis (dimulai dari **Fase 1: Keamanan Multi-Tenant & Controller Hardening**).

Viewed IMPLEMENTATION_PLAN_25_INDUSTRY_STOREFRONT_THEMES_AND_SECURITY.md:178-195