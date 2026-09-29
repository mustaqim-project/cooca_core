# PRD-24: SPESIFIKASI PRODUK & DESAIN TEMA STOREFRONT KLASTER MANUFAKTUR, KONSTRUKSI, AGRIBISNIS & PRODUKSI
## (17. Konveksi & Garment Mfg, 18. Penjahit Jas & Kebaya, 19. Furniture & Woodworking, 20. Percetakan & Digital Printing, 21. Kosmetik & Skincare Mfg, 22. Pabrik Plastik & Metal, 23. Frozen Food Mfg, 24. Kontraktor & Renovasi, 25. Peternakan & Pertanian)

---

## 1. Metadata Dokumen & Referensi Induk
- **Dokumen ID:** `PRD-24-STOREFRONT-MANUFACTURING-CRAFT-AGRI`
- **Klaster Industri:** Manufaktur, Konstruksi, Agribisnis & Produksi Pabrikasi (9 Sektor Resmi)
- **Folder Sasaran:** `resources/views/public/storefront/themes/` (`mfg_garment`, `mfg_custom_tailor`, `mfg_furniture`, `mfg_printing`, `mfg_skincare`, `mfg_plastic_metal`, `fnb_frozen_food`, `service_contractor`, `agri_farming`)
- **Keahlian Desain:** `/ui-styling` (Tailwind Tokens & Bento Cards), `/ui-ux-pro-max` (Product Palettes & Typography), `/design-taste-frontend` (Brief Inference & 3 Dials)
- **Master Directive:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md`](file:///c:/laragon/www/cooca_core/docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md)
- **Status:** APPROVED & READY FOR IMPLEMENTATION
- **Prioritas:** P1 (Kritis)
- **Tanggal:** 2026-09-29

---

## 2. Business Objective & Cakupan Klaster Manufaktur & Produksi

Klaster Manufaktur dan Produksi melayani transaksi job-order kustom satuan, pesanan massal pabrik, produksi pangan beku, hingga hasil panen agribisnis.

PRD-24 menetapkan standar desain visual dan fitur fungsional untuk 9 model bisnis manufaktur & produksi resmi COOCA:

1. **Konveksi & Garment Manufacturing (`mfg_garment`):** Produksi kaos komunitas, kemeja seragam kantor, jaket & sablon (BOM 4 komponen biaya: Kain roll meter/kg, benang/kancing, cutting/jahit per potong, sablon/bordir).
2. **Penjahit Jas & Kebaya Custom (`mfg_custom_tailor`):** Adibusana satuan, jas pengantin, kebaya wisuda & batik sutra (Job costing 3 komponen biaya: Kain/furing sutra, upah pola & fitting tailor, kemasan cover bag).
3. **Furniture & Woodworking (`mfg_furniture`):** Mebel kayu jati, lemari plywood, meja resin & kitchen set (Job costing 3 komponen biaya: Kayu solid/plywood/lem, upah tukang kayu, finishing melamine/duco).
4. **Percetakan & Digital Printing (`mfg_printing`):** Cetak banner, brosur, box packaging, buku majalah (Process costing 4 komponen biaya: Kertas rim/karton, tinta CMYK, plat film, machine run hours).
5. **Kosmetik & Skincare Production (`mfg_skincare`):** Produksi sabun kecantikan, serum wajah, toner, body lotion (Process costing 4 komponen biaya: Bahan aktif/ekstrak, botol pump/jar, stiker BPOM, uji lab batch).
6. **Pabrik Plastik & Metal Presisi (`mfg_plastic_metal`):** Injeksi plastik, cetakan mold, stamping komponen mesin CNC (Process costing 3 komponen biaya: Biji plastik resin/plat metal, cycle time mesin CNC, mold depreciation).
7. **Frozen Food Manufacturing (`fnb_frozen_food`):** Olahan daging beku, nugget, sosis, dimsum vacuum pack (Process costing 3 komponen biaya: Daging/bumbu campuran, plastik vacuum/nitrogen, listrik cold storage).
8. **Kontraktor & Renovasi Bangunan (`service_contractor`):** Bangun rumah baru, renovasi kantor, instalasi atap baja ringan (Job costing 3 komponen biaya: Semen/pasir/bata, mandor/tukang borongan, sewa scaffolding).
9. **Peternakan & Pertanian (`agri_farming`):** Bibit ayam/DOC, telur, hidroponik, sayur organik, pakan ternak (Process costing 3 komponen biaya: Bibit/DOC, pakan harian FCR/vitamin, panen yield mortality buffer).

---

## 3. Spesifikasi Mendalam per Sektor Industri

---

### 🧵 3.1. Konveksi & Garment Manufacturing (`mfg_garment`)
*Metode HPP: `recipe_bom` (4 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Industrial garment production workshop, wholesale minimum order quantity (MOQ) tiering, fabric & stitching options, custom embroidery notes.
- **`DESIGN_VARIANCE: 6`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 5`**

#### B. Design Tokens & Typography
- **Heading Font:** `Barlow Semi Condensed` | **Body Font:** `Inter`
- **Color Palette:** Primary `#1E293B` (Industrial Navy), Accent `#F97316` (Worker Orange), Background `#F8FAFC`.
- **Squircle Radius:** `10px`

#### C. Fitur Spesifik Interaktif
1. **Tabel MOQ Lusinan & Kodi (Volume Tiering):** Diskon harga untuk pesanan 24 pcs, 100 pcs, 500 pcs.
2. **Pilihan Jenis Kain & Gramatur:** Pilihan Cotton Combed 24s/30s, Fleece, Lacoste CVC, American Drill.
3. **Dropzone File Desain Sablon / Bordir:** Pengunggahan mockup logo sablon depan/belakang.

---

### ✂️ 3.2. Penjahit Jas & Kebaya Custom (`mfg_custom_tailor`)
*Metode HPP: `job` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Bespoke haute couture tailor, high-contrast monochrome elegance, detailed body measurements form, and fitting session booking.
- **`DESIGN_VARIANCE: 9`** | **`MOTION_INTENSITY: 6`** | **`VISUAL_DENSITY: 3`**

#### B. Design Tokens & Typography
- **Heading Font:** `Cormorant Garamond` (Haute couture serif) | **Body Font:** `Inter`
- **Color Palette:** Primary `#18181B` (Bespoke Noir), Accent `#A1A1AA` (Silver Stitch), Background `#FAFAFA`.
- **Squircle Radius:** `8px`

#### C. Fitur Spesifik Interaktif
1. **Formulir Input Ukuran Tubuh Detail:** Form lingkar dada, lingkar pinggang, panjang lengan, dan bahu.
2. **Pilihan Furing Sutra & Kancing Custom:** Opsi kancing tanduk, kancing emas, atau furing sutra jacquard.
3. **Jadwal Booking Sesi Fitting (Fitting 1 & Final Fitting):** Kalender janji temu pencocokan baju di butik.

---

### 🪑 3.3. Furniture & Woodworking (`mfg_furniture`)
*Metode HPP: `job` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Artisan teak woodworking & bespoke furniture, warm timber tones, custom dimensions estimator, and heavy freight delivery options.
- **`DESIGN_VARIANCE: 7`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 4`**

#### B. Design Tokens & Typography
- **Heading Font:** `Cormorant Garamond` | **Body Font:** `Inter`
- **Color Palette:** Primary `#78350F` (Warm Teak Brown), Accent `#D97706` (Amber Grain), Background `#FDFBF7`.
- **Squircle Radius:** `12px`

#### C. Fitur Spesifik Interaktif
1. **Kustomisasi Dimensi Ukuran (Panjang x Lebar x Tinggi):** Input ukuran custom untuk meja makan & lemari.
2. **Pilihan Finishing Kayu:** Radio selector *Natural Doff Melamine, Glossy Duco, Walnut Dark, Teak Oil*.
3. **Opsi Pengiriman Kargo & Jasa Rakit di Tempat:** Checkbox pengiriman truk ekspedisi furnitur.

---

### 🖨️ 3.4. Percetakan & Digital Printing (`mfg_printing`)
*Metode HPP: `process` (4 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Creative digital printing studio, violet and magenta tones, print file dropzone (PDF/AI/PSD), paper weight selector (GSM), and lamination finish options.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 7`** | **`VISUAL_DENSITY: 5`**

#### B. Design Tokens & Typography
- **Heading Font:** `Syne` (Creative avant-garde sans) | **Body Font:** `Inter`
- **Color Palette:** Primary `#7C3AED` (Print Violet), Accent `#EC4899` (CMYK Magenta), Background `#FAF5FF`.
- **Squircle Radius:** `14px`

#### C. Fitur Spesifik Interaktif
1. **Dropzone File Desain Siap Cetak:** Validasi upload format PDF/AI/CDR/PSD hingga 50MB.
2. **Pemilih Gramatur Kertas (GSM):** Pilihan Art Paper 150 GSM, Art Carton 260 GSM, Ivory 310 GSM.
3. **Pilihan Finishing Cetak:** Opsi Laminasi Doff, Glossy, Poly Emas / Hot Print Foil, Spot UV.

---

### 🧴 3.5. Kosmetik & Skincare Production (`mfg_skincare`)
*Metode HPP: `process` (4 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Dermatological clinical skincare elegance, rose quartz & pearl tones, BPOM safety certification badges, and formula benefit breakdown.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 6`** | **`VISUAL_DENSITY: 3`**

#### B. Design Tokens & Typography
- **Heading Font:** `Playfair Display` | **Body Font:** `Inter`
- **Color Palette:** Primary `#BE185D` (Rose Pearl), Accent `#0D9488` (Clinical Mint), Background `#FFF5F7`.
- **Squircle Radius:** `20px`

#### C. Fitur Spesifik Interaktif
1. **Badge Izin BPOM & Uji Dermatologi:** Nomor registrasi NA resmi BPOM pada setiap kartu produk.
2. **Tab Khasiat Kandungan Aktif (Active Ingredients):** Rincian Niacinamide, Salicylic Acid, Ceramide, Hyaluronic Acid.
3. **Pilihan Kemasan & Varian Botol:** Pilihan Botol Pipet Dropper, Airless Pump, atau Jar Krim.

---

### ⚙️ 3.6. Pabrik Plastik & Metal Presisi (`mfg_plastic_metal`)
*Metode HPP: `process` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Precision CNC engineering & plastic injection mold manufacturing, dark steel aesthetic, tolerance spec matrix, and B2B RFQ generator.
- **`DESIGN_VARIANCE: 6`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 6`**

#### B. Design Tokens & Typography
- **Heading Font:** `Space Grotesk` (Technical precision sans) | **Body Font:** `Inter`
- **Color Palette:** Primary `#334155` (Machined Steel), Accent `#06B6D4` (CNC Cyan), Background `#0F172A`.
- **Squircle Radius:** `8px`

#### C. Fitur Spesifik Interaktif
1. **Formulir RFQ Cetakan Mold Injeksi:** Input jenis bahan (PP/ABS/Nylon/Aluminium) dan kuantitas produksi.
2. **Spesifikasi Toleransi Presisi Mesin CNC:** Tabel mikron toleransi pengerjaan komponen presisi.
3. **Unduh Lembar Data Material (MSDS / Data Sheet):** Tombol download spesifikasi teknis material.

---

### ❄️ 3.7. Frozen Food Manufacturing (`fnb_frozen_food`)
*Metode HPP: `process` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Clean cold-chain frozen food manufacturing, icy blue freshness, vacuum packaging badges, and cooking instruction cards.
- **`DESIGN_VARIANCE: 6`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 5`**

#### B. Design Tokens & Typography
- **Heading Font:** `Plus Jakarta Sans` | **Body Font:** `Inter`
- **Color Palette:** Primary `#0284C7` (Icy Cold Blue), Accent `#059669` (Fresh Pack Green), Background `#F0FDF4`.
- **Squircle Radius:** `14px`

#### C. Fitur Spesifik Interaktif
1. **Badge Kemasan Vacuum & Suhu Simpan:** Indikator *Simpan pada Suhu -18°C / Tahan 6 Bulan*.
2. **Panduan Cara Memasak (Airfryer / Goreng / Rebus):** Tab instruksi praktis penyajian makanan beku.
3. **Pilihan Kurir Instant Berpendingin (Cold Delivery):** Pilihan kurir instan dengan ice gel pack.

---

### 🏗️ 3.8. Kontraktor & Renovasi Bangunan (`service_contractor`)
*Metode HPP: `job` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Heavy civil construction contractor, cement slate with safety amber yellow accents, material volume calculator, and site survey request form.
- **`DESIGN_VARIANCE: 5`** | **`MOTION_INTENSITY: 4`** | **`VISUAL_DENSITY: 5`**

#### B. Design Tokens & Typography
- **Heading Font:** `Barlow Semi Condensed` | **Body Font:** `Inter`
- **Color Palette:** Primary `#475569` (Solid Cement), Accent `#EAB308` (Safety Amber), Background `#F8FAFC`.
- **Squircle Radius:** `8px`

#### C. Fitur Spesifik Interaktif
1. **Kalkulator Estimasi Biaya Renovasi per m²:** Input luas bangunan $\rightarrow$ estimasi kisaran biaya standar vs premium.
2. **Formulir Permintaan Survei Lokasi Proyek:** Form alamat proyek, jadwal kunjungan mandor, dan jenis renovasi.
3. **Galeri Portofolio Proyek Sebelum vs Sesudah:** Komparasi renovasi gedung & rumah tinggal.

---

### 🌾 3.9. Peternakan & Pertanian (`agri_farming`)
*Metode HPP: `process` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Organic agri-farming & livestock, rich harvest green & amber, harvest batch information, average weight per unit, and bulk harvest ordering.
- **`DESIGN_VARIANCE: 6`** | **`MOTION_INTENSITY: 4`** | **`VISUAL_DENSITY: 4`**

#### B. Design Tokens & Typography
- **Heading Font:** `Plus Jakarta Sans` | **Body Font:** `Inter`
- **Color Palette:** Primary `#15803D` (Agri Harvest Green), Accent `#CA8A04` (Golden Wheat), Background `#FEFCE8`.
- **Squircle Radius:** `16px`

#### C. Fitur Spesifik Interaktif
1. **Informasi Batch Panen & Tanggal Petik:** Indikator kesegaran hasil panen (*Panen Baru Hari Ini*).
2. **Pemilih Berat / Satuan (Kg / Ekor / Kuintal / Sak):** Selector kuantitas belanja hasil bumi / DOC bibit ternak.
3. **Sertifikasi Organik & Bebas Pestisida:** Badge jaminan mutu pangan sehat alami.

---

## 4. Kriteria Keberterimaan (Acceptance Criteria)

- [x] Dokumen PRD-24 mencakup 9 sektor resmi Manufaktur, Konstruksi, Agribisnis & Produksi COOCA.
- [ ] Berkas template view untuk 9 sektor tersedia di `resources/views/public/storefront/themes/`.
- [ ] Formulir interaktif (ukuran tailor, upload desain print, kalkulator renovasi, RFQ CNC, panduan frozen) berfungsi secara reaktif.
