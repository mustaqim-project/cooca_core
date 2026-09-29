# ANALISIS KOMPREHENSIF KEBUTUHAN, DESAIN & FITUR 25 TEMA STOREFRONT RESMI COOCA
**Target Modul:** Public Storefront Multi-Theme Engine (`resources/views/public/storefront/`)  
**Basis Data Industri:** 25 Template Resmi COOCA (`BusinessTypeTemplate` & `BusinessTemplateSeeder`)  
**Desain & Metodologi:** Apple HIG Bento UI v2.0, Zero-Slop Visual Direction, Adaptive Multi-Industry Form Ergonomics  
**Keahlian Desain:** `/ui-styling` (Tailwind CSS Tokens), `/ui-ux-pro-max` (Design Systems & UX Guidelines), `/design-taste-frontend` (Anti-Default Brief Inference & 3 Dials)  
**Dokumen Induk:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)  
**Tanggal Rilis:** 2026-09-29  

---

## 📑 DAFTAR ISI

1. [Eksekutif Ringkasan & Sinkronisasi 25 Industri Resmi](#1-eksekutif-ringkasan--sinkronisasi-25-industri-resmi)
2. [Arsitektur Direktori Blade & Engine Resolusi Tema](#2-arsitektur-direktori-blade--engine-resolusi-tema)
3. [Kerangka Kerja Desain (Framework The 3 Dials & Tokens)](#3-kerangka-kerja-desain-framework-the-3-dials--tokens)
4. [Master Matriks 25 Sektor Industri COOCA](#4-master-matriks-25-sektor-industri-cooca)
5. [Analisis Kebutuhan Mendalam 4 Klaster Industri](#5-analisis-kebutuhan-mendalam-4-klaster-industri)
   - [Klaster A: Food & Beverage / Kuliner (6 Industri)](#klaster-a-food--beverage--kuliner-6-industri)
   - [Klaster B: Ritel, Perdagangan & Kesehatan (4 Industri)](#klaster-b-ritel-perdagangan--kesehatan-4-industri)
   - [Klaster C: Jasa Otomotif, Perawatan, Event & Kreatif (6 Industri)](#klaster-c-jasa-otomotif-perawatan-event--kreatif-6-industri)
   - [Klaster D: Manufaktur, Konstruksi, Agribisnis & Produksi (9 Industri)](#klaster-d-manufaktur-konstruksi-agribisnis--produksi-9-industri)
6. [Matriks Komponen Spesifik & Form Input Adaptif](#6-matriks-komponen-spesifik--form-input-adaptif)
7. [Pedoman UX Anti-Default & Kepatuhan Apple HIG](#7-pedoman-ux-anti-default--kepatuhan-apple-hig)
8. [Peta Dokumen PRD Terpadu](#8-peta-dokumen-prd-terpadu)

---

## 1. Eksekutif Ringkasan & Sinkronisasi 25 Industri Resmi

Sistem COOCA dibangun di atas 25 model bisnis presisi yang memiliki metode kalkulasi HPP (*Costing Method*), struktur biaya (*Cost Components*), dan perilaku operasional yang berbeda. 

Storefront publik COOCA (`cooca.id/{slug}`) menyelaraskan 100% pengalaman antarmuka pelanggan dengan 25 model bisnis resmi ini:
- **Zero Generic Slop:** Tidak ada lagi tampilan monolitik generik di mana sebuah bengkel motor terlihat sama persis dengan toko kue tart.
- **Karakteristik Unik Sektor:** Setiap tema memuat palet warna, tipografi berkarakter, form input adaptif (cth: ukuran lingkar dada untuk penjahit jas, nomor polisi untuk bengkel, tanggal acara untuk event organizer, gramatur kertas untuk percetakan, dan estimasi luas ruangan untuk kontraktor).
- **Kepatuhan Bento Apple HIG v2.0:** Geometri squircle kontinu, touch target 44px–52px ramah sentuhan, tipografi angka `tabular-nums`, nol emoji pada tombol transaksi, dan kontras warna teks standar WCAG 2.1 AA.

---

## 2. Arsitektur Direktori Blade & Engine Resolusi Tema

Struktur view toko publik dirancang modular untuk mendukung 25 tema industri:

```
resources/views/public/storefront/
├── layouts/
│   ├── app.blade.php                 # Core shell (i18n injection, Alpine $store.cart, Google Fonts loader)
│   ├── navbar.blade.php              # Bento navbar with active-page auto-hiding
│   └── footer.blade.php              # Multi-branch geolocations & operational hours footer
│
├── themes/                           # 25 Dedicated Industry Themes
│   │
│   ├── [KLASTER 1: F&B & KULINER]
│   ├── fnb_cafe/                     # 1. Coffee Shop & Cafe (recipe_bom)
│   ├── fnb_resto/                    # 2. Restoran / Rumah Makan (recipe_bom)
│   ├── fnb_cloud_kitchen/            # 3. Cloud Kitchen & Delivery Only (recipe_bom)
│   ├── fnb_bakery/                   # 4. Bakery & Cake Shop (recipe_bom)
│   ├── fnb_catering/                 # 5. Catering & Prasmanan (job)
│   ├── fnb_diet_catering/            # 6. Diet & Healthy Catering (recipe_bom)
│   │
│   ├── [KLASTER 2: RETAIL, TRADING & KESEHATAN]
│   ├── retail_reseller/              # 7. Reseller & Toko Retail (retail)
│   ├── trading_fmcg/                 # 8. Distributor & Trading FMCG (retail)
│   ├── retail_pharmacy/              # 9. Apotek & Toko Obat (retail)
│   ├── creative_craft/               # 10. Kerajinan Tangan & Handmade Craft (simple)
│   │
│   ├── [KLASTER 3: JASA, OTOMOTIF, EVENT & KREATIF]
│   ├── service_workshop/             # 11. Bengkel Mobil & Motor (job)
│   ├── service_autowash/             # 12. Auto Detailing & Cuci Mobil (service)
│   ├── service_salon/                # 13. Barbershop & Salon Kecantikan (service)
│   ├── service_laundry/              # 14. Laundry Kiloan & Satuan (per_unit)
│   ├── service_event_organizer/      # 15. Event Organizer & Wedding Organizer (job)
│   ├── service_digital_agency/       # 16. Digital Creative Agency / IT Software (service)
│   │
│   └── [KLASTER 4: MANUFAKTUR, KONSTRUKSI, AGRI & PRODUKSI]
│       ├── mfg_garment/              # 17. Konveksi & Garment Manufacturing (recipe_bom)
│       ├── mfg_custom_tailor/        # 18. Penjahit Jas & Kebaya Custom (job)
│       ├── mfg_furniture/            # 19. Furniture & Woodworking (job)
│       ├── mfg_printing/             # 20. Percetakan & Digital Printing (process)
│       ├── mfg_skincare/             # 21. Kosmetik & Skincare Production (process)
│       ├── mfg_plastic_metal/        # 22. Pabrik Plastik & Metal Presisi (process)
│       ├── fnb_frozen_food/          # 23. Frozen Food Manufacturing (process)
│       ├── service_contractor/       # 24. Kontraktor & Renovasi Bangunan (job)
│       └── agri_farming/             # 25. Peternakan & Pertanian (process)
│
└── [Core Universal Fallback Views: home, catalog, product_detail, checkout, order_tracking, reservation, contact, about, articles]
```

---

## 3. Kerangka Kerja Desain (Framework The 3 Dials & Tokens)

Setiap tema dikalibrasikan menggunakan **The 3 Dials** (`/design-taste-frontend`):
- **`DESIGN_VARIANCE` (1–10):** Tingkat variasi asimetri layout (`1` = Kisi kaku simetris; `10` = Asimetris dinamis, kartu bento tumpang tindih artistik).
- **`MOTION_INTENSITY` (1–10):** Intensitas transisi mikro (`1` = Statis murni; `10` = Spring physics, scroll-reveal terpadu).
- **`VISUAL_DENSITY` (1–10):** Kepadatan data pada antarmuka (`1` = Galeri lapang; `10` = Grid katalog rapat cepat).

---

## 4. Master Matriks 25 Sektor Industri COOCA

| No | Kategori | Metode HPP | Nama Industri | Tipografi (Head / Body) | Palet Utama | Dials (V/M/D) | Fitur Spesifik Interaktif |
| :---: | :--- | :--- | :--- | :--- | :--- | :---: | :--- |
| **1** | `fnb` | `job` | **Catering & Prasmanan** | DM Serif / Plus Jakarta | Amber `#D97706` / `#B45309` | `7 / 5 / 4` | Paket Buffet/Kotak, Jumlah Porsi, Jadwal Acara |
| **2** | `service` | `job` | **Event & Wedding Organizer** | Cormorant / Plus Jakarta | Rose Gold `#BE185D` / `#D4AF37`| `9 / 6 / 3` | Paket WO/Dekor, Kalender Tanggal Hari-H |
| **3** | `service` | `service` | **Auto Detailing & Cuci Mobil** | Orbitron / Inter | Hydro Blue `#2563EB` / `#06B6D4`| `8 / 7 / 5` | Paket Ceramic Coating, Antrean Live Bay |
| **4** | `manufacturing`| `recipe_bom` | **Konveksi & Garment Mfg** | Barlow Semi / Inter | Navy `#1E293B` / `#F97316` | `6 / 5 / 5` | Tabel MOQ Lusinan, Custom Sablon/Bordir |
| **5** | `trading` | `retail` | **Distributor & Trading FMCG** | Plus Jakarta / Inter | Blue Royal `#1D4ED8` / `#EA580C`| `5 / 4 / 7` | Harga Kartonan Grosir, Armada Truk Box |
| **6** | `service` | `job` | **Kontraktor & Renovasi** | Barlow Semi / Inter | Cement `#475569` / `#EAB308` | `5 / 4 / 5` | Kalkulator Proyek Fisik, Form Survei Lokasi |
| **7** | `manufacturing`| `process` | **Pabrik Plastik & Metal** | Space Grotesk / Inter | Steel `#334155` / `#06B6D4` | `6 / 5 / 6` | Cetak Mold Injeksi, Toleransi Presisi CNC |
| **8** | `manufacturing`| `process` | **Percetakan & Digital Print**| Syne / Inter | Violet `#7C3AED` / `#EC4899` | `8 / 7 / 5` | Dropzone File (AI/PDF), Gramatur GSM |
| **9** | `agriculture` | `process` | **Peternakan & Pertanian** | Plus Jakarta / Inter | Agro Green `#15803D` / `#CA8A04`| `6 / 4 / 4` | Panen Batch, Bibit Unggul, Berat Rata-rata |
| **10**| `fnb` | `recipe_bom` | **Diet & Healthy Catering** | Plus Jakarta / Inter | Emerald `#059669` / `#10B981` | `6 / 5 / 4` | Jadwal Menu Mingguan, Kalori Makronutrisi |
| **11**| `service` | `service` | **Barbershop & Salon** | Playfair / Plus Jakarta | Rose Berry `#9D174D` / `#B76E79`| `8 / 6 / 3` | Booking Stylist/Kapster, Durasi Treatment |
| **12**| `manufacturing`| `job` | **Penjahit Jas & Kebaya** | Cormorant / Inter | Noir `#18181B` / `#A1A1AA` | `9 / 6 / 3` | Form Ukuran Tubuh (Fitting), Bahan Sutra |
| **13**| `creative` | `simple` | **Handmade Craft & Seni** | Quicksand / Inter | Terracotta `#C2410C` / `#10B981`| `8 / 7 / 3` | Cerita Pengrajin, Kustomisasi Ukiran/Nama |
| **14**| `service` | `service` | **Digital Agency / Software** | Space Grotesk / Jakarta | Indigo `#4F46E5` / `#06B6D4` | `8 / 7 / 4` | Scope of Work, Paket Retainer Bulanan |
| **15**| `service` | `per_unit` | **Laundry Kiloan & Satuan** | Plus Jakarta / Inter | Ocean `#0284C7` / `#38BDF8` | `6 / 5 / 5` | Kalkulator Timbangan Kg, Aroma Parfum |
| **16**| `service` | `job` | **Bengkel Mobil & Motor** | Chakra Petch / Inter | Orange `#EA580C` / `#F97316` | `7 / 6 / 5` | Filter Tipe Kendaraan, Booking Servis |
| **17**| `fnb` | `recipe_bom` | **Cloud Kitchen & Delivery** | Outfit / Inter | Red `#DC2626` / `#F59E0B` | `8 / 8 / 6` | Combo Deal Kilat, Switcher Delivery/Pickup |
| **18**| `fnb` | `recipe_bom` | **Restoran / Rumah Makan** | DM Serif / Plus Jakarta | Chili Red `#991B1B` / `#D97706`| `7 / 5 / 4` | Level Pedas, Meja Makan Lesehan / VIP |
| **19**| `manufacturing`| `job` | **Furniture & Woodworking** | Cormorant / Inter | Warm Teak `#78350F` / `#D97706` | `7 / 5 / 4` | Dimensi Custom PxLxT, Finishing Kayu |
| **20**| `fnb` | `recipe_bom` | **Bakery & Cake Shop** | Cormorant / Poppins | Velvet `#BE185D` / `#D4AF37` | `8 / 6 / 3` | Diameter Kue, Tulisan Ucapan Cokelat |
| **21**| `manufacturing`| `process` | **Kosmetik & Skincare Mfg** | Playfair / Inter | Pearl `#BE185D` / `#0D9488` | `8 / 6 / 3` | Formula Khasiat, Izin BPOM, Botol Pump |
| **22**| `retail` | `retail` | **Reseller & Toko Retail** | Plus Jakarta / Inter | Green `#16A34A` / `#EA580C` | `5 / 4 / 8` | Super-Dense Grid, Tombol Beli Cepat (+/-)|
| **23**| `fnb` | `recipe_bom` | **Coffee Shop & Cafe** | Playfair / Inter | Espresso `#8B5A2B` / `#C88A58` | `8 / 6 / 3` | Tasting Notes, Level Gilingan Biji Kopi |
| **24**| `fnb` | `process` | **Frozen Food Mfg** | Plus Jakarta / Inter | Cold Cyan `#0284C7` / `#059669`| `6 / 5 / 5` | Kemasan Vacuum, Suhu Simpan Beku |
| **25**| `retail` | `retail` | **Apotek & Toko Obat** | Inter / Inter | Teal `#0D9488` / `#0284C7` | `4 / 3 / 4` | Unggah Foto Resep Dokter, Label BPOM |

---

## 5. Analisis Kebutuhan Mendalam 4 Klaster Industri

### Klaster A: Food & Beverage / Kuliner (6 Industri)
1. **Coffee Shop & Cafe (`fnb_cafe`):** Catatan rasa aroma, asal biji kopi, selector gilingan, dan QR meja dine-in.
2. **Restoran / Rumah Makan (`fnb_resto`):** Level pedas masakan, lauk pauk tambahan, reservasi ruang VIP/lesehan.
3. **Cloud Kitchen & Delivery Only (`fnb_cloud_kitchen`):** Combo meal kilat, box delivery tahan panas, promo bundle.
4. **Bakery & Cake Shop (`fnb_bakery`):** Diameter kue, tulisan kustom plat cokelat, slot pre-order H-3, lilin angka.
5. **Catering & Prasmanan (`fnb_catering`):** Paket porsi besar buffet/box, sewa alat saji, kalender tanggal prasmanan pesta.
6. **Diet & Healthy Catering (`fnb_diet_catering`):** Rincian kalori/protein makronutrisi, preferensi pantangan diet (keto/halal), jadwal menu mingguan.

### Klaster B: Ritel, Perdagangan & Kesehatan (4 Industri)
7. **Reseller & Toko Retail (`retail_reseller`):** Etalase belanja kilat dengan tombol stepper `+ / -` instan, bar gratis ongkir.
8. **Distributor & Trading FMCG (`trading_fmcg`):** Skema harga kartonan grosir, minimum pembelian karton, armada truk box.
9. **Apotek & Toko Obat (`retail_pharmacy`):** Dropzone resep dokter terenkripsi, lingkaran penanda obat bebas/keras, konsultasi apoteker.
10. **Kerajinan Tangan & Handmade Craft (`creative_craft`):** Narasi pengrajin lokal, opsi ukir nama/kado khusus, galeri kerajinan.

### Klaster C: Jasa Otomotif, Perawatan, Event & Kreatif (6 Industri)
11. **Bengkel Mobil & Motor (`service_workshop`):** Filter kecocokan sparepart motor/mobil, booking antrean servis, opsi pasang di bengkel.
12. **Auto Detailing & Cuci Mobil (`service_autowash`):** Komparasi paket coating 9H, pemilih ukuran mobil, indikator antrean cuci live.
13. **Barbershop & Salon Kecantikan (`service_salon`):** Pemilih kapster/beautician, estimasi durasi potong/treatment, kalender booking.
14. **Laundry Kiloan & Satuan (`service_laundry`):** Slider estimasi berat kiloan cuci, pilihan wangi parfum, sakelar express 3 jam.
15. **Event Organizer & Wedding Organizer (`service_event_organizer`):** Paket WO komprehensif, galeri dokumentasi portfolio, booking tanggal hari-H.
16. **Digital Creative Agency / IT Software (`service_digital_agency`):** Scope of work jasa profesional, paket retainer bulanan, booking meeting online.

### Klaster D: Manufaktur, Konstruksi, Agribisnis & Produksi (9 Industri)
17. **Konveksi & Garment Manufacturing (`mfg_garment`):** Tiering harga lusinan/kodi, pilihan jenis kain katun/fleece, opsi sablon/bordir.
18. **Penjahit Jas & Kebaya Custom (`mfg_custom_tailor`):** Formulir detail ukuran tubuh (lingkar dada/pinggang), pilihan furing sutra, jadwal fitting.
19. **Furniture & Woodworking (`mfg_furniture`):** Dimensi kustom kayu jati/plywood, pilihan finishing duco/melamine, pengiriman kargo berat.
20. **Percetakan & Digital Printing (`mfg_printing`):** Dropzone file desain (AI/PDF), gramatur kertas GSM, opsi laminasi doff/glossy.
21. **Kosmetik & Skincare Production (`mfg_skincare`):** Formula khasiat dermatologis, nomor BPOM, pilihan kemasan pump/jar.
22. **Pabrik Plastik & Metal Presisi (`mfg_plastic_metal`):** Pembuatan cetakan mold injeksi, spesifikasi toleransi presisi mesin CNC, RFQ B2B.
23. **Frozen Food Manufacturing (`fnb_frozen_food`):** Ketahanan kemasan vacuum, panduan cara memasak beku, kurir instan berpendingin.
24. **Kontraktor & Renovasi Bangunan (`service_contractor`):** Kalkulator volume semen/bata/cat, form permintaan survei lokasi proyek.
25. **Peternakan & Pertanian (`agri_farming`):** Penjualan bibit/DOC, hasil panen per kg/ekor, rincian sertifikasi pakan organik.

---

## 6. Matriks Komponen Spesifik & Form Input Adaptif

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ MATRIKS KOMPONEN ADAPTIF 25 INDUSTRI STOREFRONT COOCA                       │
├────────────────────────────┬────────────────────────────────────────────────┤
│ Komponen Khusus            │ Sektor Industri Terkait                        │
├────────────────────────────┼────────────────────────────────────────────────┤
│ 1. Dine-In Table Selector  │ Cafe, Restoran, Cloud Kitchen                  │
│ 2. Pre-Order Event Date    │ Bakery, Catering Prasmanan, Event Organizer    │
│ 3. Instant Stepper (+/-)   │ Reseller Retail, FMCG Trading, Frozen Food     │
│ 4. Vehicle Model Picker    │ Bengkel Motor/Mobil, Auto Detailing            │
│ 5. File Upload Dropzone    │ Apotek (Resep), Percetakan (Desain), Agency    │
│ 6. Body Measurements Form  │ Penjahit Jas & Kebaya Custom                   │
│ 7. Material Calculator     │ Kontraktor Bangunan, Furniture Kayu            │
│ 8. Nutrition / Calorie Bar │ Diet Catering, Frozen Food                     │
│ 9. Scent & Treatment Select│ Laundry Kiloan, Salon Kecantikan               │
│ 10. B2B RFQ & Wholesale    │ Garment Mfg, Pabrik Plastik/Metal, FMCG        │
└────────────────────────────┴────────────────────────────────────────────────┘
```

---

## 7. Pedoman UX Anti-Default & Kepatuhan Apple HIG

1. **Tipografi Berkarakter & Google Fonts Terkurasi:** Dilarang menggunakan font generic sans-serif tanpa identitas. Setiap tema memiliki font heading dan body berkarakter (misal: *Chakra Petch* untuk Bengkel, *Space Grotesk* untuk Gadget & Plastik CNC, *Orbitron* untuk Detailing Mobil, *Syne* untuk Percetakan).
2. **Kerapian Angka (Tabular Numbers):** Seluruh angka harga, stok, dan estimasi wajib menerapkan CSS `font-variant-numeric: tabular-nums`.
3. **Touch Targets & Form Input Ergonomics:** Tombol aksi utama memiliki tinggi minimal 48px–52px. Input teks diset `font-size: 16px` pada perangkat mobile untuk mencegah browser iOS Safari melakukan zoom paksa.
4. **Nol Emoji pada Elemen Operasional:** Seluruh tombol navigasi, checkout, status pesanan, dan verifikasi legal hanya menggunakan ikon SVG semantik Lucide.

---

## 8. Peta Dokumen PRD Terpadu

Spesifikasi fungsional dan teknis mendalam terbagi ke dalam 4 berkas PRD terstruktur:

1. ☕ [`docs/prd/PRD-21-STOREFRONT-THEMES-FNB-CULINARY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-21-STOREFRONT-THEMES-FNB-CULINARY.md) — **PRD Sektor 1–6: F&B & Kuliner** (Cafe, Resto, Cloud Kitchen, Bakery, Catering Prasmanan, Diet Catering).
2. 🛍️ [`docs/prd/PRD-22-STOREFRONT-THEMES-RETAIL-COMMERCE.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-22-STOREFRONT-THEMES-RETAIL-COMMERCE.md) — **PRD Sektor 7–10: Ritel, Trading & Kesehatan** (Reseller Retail, FMCG Trading, Apotek, Handmade Craft).
3. 🔧 [`docs/prd/PRD-23-STOREFRONT-THEMES-SERVICES-HEALTH-BEAUTY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-23-STOREFRONT-THEMES-SERVICES-HEALTH-BEAUTY.md) — **PRD Sektor 11–16: Jasa, Otomotif, Event & Kreatif** (Bengkel, Auto Detailing, Barbershop/Salon, Laundry, Event Organizer, Digital Agency).
4. 🏗️ [`docs/prd/PRD-24-STOREFRONT-THEMES-MANUFACTURING-CRAFT-AGRI.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-24-STOREFRONT-THEMES-MANUFACTURING-CRAFT-AGRI.md) — **PRD Sektor 17–25: Manufaktur, Konstruksi, Agribisnis & Produksi** (Konveksi, Penjahit Jas, Furniture, Percetakan, Kosmetik Skincare, Pabrik Plastik/Metal, Frozen Food, Kontraktor, Peternakan/Pertanian).
