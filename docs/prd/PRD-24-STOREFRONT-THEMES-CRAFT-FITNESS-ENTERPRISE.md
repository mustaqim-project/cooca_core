# PRD-24: SPESIFIKASI PRODUK & DESAIN TEMA STOREFRONT KLASTER KONSTRUKSI, PERCETAKAN, FITNESS & ENTERPRISE B2B
## (Tema 17: Ironclad Builder, Tema 18: Pixel & Print Studio, Tema 19: Titan Kinetic, Tema 20: Sovereign Enterprise)

---

## 1. Metadata Dokumen & Referensi Induk
- **Dokumen ID:** `PRD-24-STOREFRONT-CRAFT-FITNESS-ENTERPRISE`
- **Klaster Industri:** Bahan Bangunan, Percetakan, Kebugaran & Konsultan B2B (4 Tema)
- **Folder Sasaran:** `resources/views/public/storefront/themes/` (`craft_building`, `craft_printing`, `lifestyle_gym`, `enterprise_consulting`)
- **Keahlian Desain:** `/ui-styling` (Tailwind Tokens & Bento Cards), `/ui-ux-pro-max` (Product Palettes & Typography), `/design-taste-frontend` (Brief Inference & 3 Dials)
- **Master Directive:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md`](file:///c:/laragon/www/cooca_core/docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md)
- **Status:** APPROVED & READY FOR IMPLEMENTATION
- **Prioritas:** P1 (Kritis)
- **Tanggal:** 2026-09-29

---

## 2. Business Objective & Cakupan Klaster Khusus B2B & Lifestyle

Klaster ini melayani transaksi bernilai besar, kustomisasi pesanan tingkat lanjut, keanggotaan berkala (*membership passes*), serta proposal pengadaan formal (*RFQ - Request for Quotation*).

PRD-24 menetapkan standar desain visual dan fitur fungsional untuk 4 tema khusus:
1. **Ironclad Builder (`craft_building`):** Toko bahan bangunan, distributor semen, pasir, baja ringan, keramik & alat pertukangan.
2. **Pixel & Print Studio (`craft_printing`):** Percetakan digital, sablon kaos merchandise, cetak banner, brosur & kemasan box.
3. **Titan Kinetic (`lifestyle_gym`):** Pusat kebugaran (Gym), studio yoga, pilates, crossfit & fasilitas olahraga.
4. **Sovereign Enterprise (`enterprise_consulting`):** Konsultan manajemen, kantor hukum, agensi pemasaran digital & jasa B2B.

---

## 3. Spesifikasi Mendalam per Tema Industri

---

### 🏗️ 3.1. Tema 17: Ironclad Builder (`craft_building`)
*Toko Bahan Bangunan, Semen, Baja Ringan, Keramik & Alat Kontraktor*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Heavy industrial construction, durable concrete slate & safety yellow accents, material calculators (m² to sacks/trucks), wholesale bulk pricing, and delivery truck fleet selector.
- **`DESIGN_VARIANCE: 5`** (Solid utilitarian grid, clear bulk pricing tiers, high structural stability)
- **`MOTION_INTENSITY: 4`** (Sturdy, non-distracting UI)
- **`VISUAL_DENSITY: 6`** (Dense contractor product specifications)

#### B. Design Tokens & Typography
- **Heading Font:** `Barlow Semi Condensed` (Strong architectural industrial sans)
- **Body Font:** `Inter`
- **Google Fonts Link:** `family=Barlow+Semi+Condensed:wght@600;700;800&family=Inter:wght@400;500;600`
- **Color Palette Tokens:**
  - Primary (`--color-primary`): `#475569` (Solid Cement Slate)
  - Secondary (`--color-secondary`): `#EAB308` (Safety Amber Yellow)
  - Accent / CTA (`--color-accent`): `#CA8A04` (Heavy Machinery Gold)
  - Background (`--color-bg`): `#F8FAFC` (Granite Light Wash)
  - Card Background (`--color-card`): `#FFFFFF`
  - Border (`--color-border`): `#CBD5E1`
- **Squircle Radius:** `8px` (Sudut kokoh bersahaja)

#### C. Fitur Spesifik & Interaksi Fungsional
1. **Kalkulator Kebutuhan Material (Volume Estimator):** Masukkan luas ruangan (m² / m³) $\rightarrow$ kalkulator otomatis menghitung jumlah sak semen, dus keramik, atau batang baja ringan yang dibutuhkan.
2. **Pilihan Armada Pengiriman Logistik Berat:** Radio button pilihan armada kirim toko (*Mobil Pick-up Bak, Truk Engkel 4 Roda, Truk CDD 6 Roda, Crane Drop*).
3. **Tabel Harga Grosir Bertingkat (Wholesale Tiering):** Tampilan diskon per volume (*Beli 1–10 Sak: Rp 65.000, 11–50 Sak: Rp 62.000, >50 Sak: Rp 59.000*).

---

### 🖨️ 3.2. Tema 18: Pixel & Print Studio (`craft_printing`)
*Percetakan Digital, Sablon Kaos, Kemasan Packaging & Desain Promosi*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Creative studio energy, electric violet and magenta accents, design file dropzone (PDF/AI/PSD), paper weight selector (GSM), and lamination finish preview.
- **`DESIGN_VARIANCE: 8`** (Creative expressive grid, dynamic design portfolio showcases)
- **`MOTION_INTENSITY: 7`** (Snappy upload drag-over animations, interactive finishing preview)
- **`VISUAL_DENSITY: 5`** (Comprehensive printing option configurator)

#### B. Design Tokens & Typography
- **Heading Font:** `Syne` (Bold avant-garde creative sans)
- **Body Font:** `Inter`
- **Google Fonts Link:** `family=Syne:wght@600;700;800&family=Inter:wght@400;500;600`
- **Color Palette Tokens:**
  - Primary (`--color-primary`): `#7C3AED` (Creative Studio Violet)
  - Secondary (`--color-secondary`): `#EC4899` (CMYK Print Magenta)
  - Accent / CTA (`--color-accent`): `#6D28D9`
  - Background (`--color-bg`): `#FAF5FF` (Soft Artboard Paper)
  - Card Background (`--color-card`): `#FFFFFF`
  - Border (`--color-border`): `#E9D5FF`
- **Squircle Radius:** `14px`

#### C. Fitur Spesifik & Interaksi Fungsional
1. **Dropzone Unggah File Desain Cetak:** Komponen drag-and-drop file pelanggan (PDF, AI, CDR, PSD, TIFF hingga 50MB) dengan status validasi resolusi.
2. **Pemilih Gramatur Kertas & Bahan:** Pilihan bahan cetak (*Art Paper 150 GSM, Art Carton 260 GSM, Ivory 310 GSM, Kertas Kraft Daur Ulang*).
3. **Pilihan Finishing Cetak:** Radio button opsi *Laminasi Doff, Laminasi Glossy, Hot Print Gold Foil, Spot UV Emboss*.

---

### 🏋️ 3.3. Tema 19: Titan Kinetic (`lifestyle_gym`)
*Pusat Kebugaran, Gym, Studio Yoga, Pilates & Personal Training*

#### A. Brief Inference & The 3 Dials
- **Design Read:** High-voltage athletic power, matte iron black with acid lime green highlights, membership pass tier comparison, class schedule calendar, and coach roster.
- **`DESIGN_VARIANCE: 8`** (High impact typography, bold kinetic cards, high-contrast badges)
- **`MOTION_INTENSITY: 8`** (Punchy kinetic animations, energizing hover pulse)
- **`VISUAL_DENSITY: 4`** (Clear tiered membership packages)

#### B. Design Tokens & Typography
- **Heading Font:** `Bebas Neue` (Bold tall athletic impact)
- **Body Font:** `Inter`
- **Google Fonts Link:** `family=Bebas+Neue&family=Inter:wght@400;500;600;700`
- **Color Palette Tokens:**
  - Primary (`--color-primary`): `#84CC16` (High-Voltage Acid Lime)
  - Secondary (`--color-secondary`): `#A3E635` (Kinetic Energy Green)
  - Accent / CTA (`--color-accent`): `#4D7C0F`
  - Background (`--color-bg`): `#171717` (Matte Iron Carbon Dark)
  - Card Background (`--color-card`): `#262626` (Forged Steel Slate)
  - Border (`--color-border`): `#3F3F46`
  - Text Primary (`--color-text`): `#FAFAFA`
- **Squircle Radius:** `8px` (Atletis, kokoh & bertenaga)

#### C. Fitur Spesifik & Interaksi Fungsional
1. **Tabel Komparasi Paket Membership (Harian / Bulanan / 6 Bulan / 1 Tahun):** Perbandingan akses fasilitas (*All Gym Access, Sauna & Locker, Unlimited Group Classes, Free 2x Personal Trainer Session*).
2. **Jadwal Kelas Mingguan & Roster Coach:** Kalender jadwal kelas harian (Zumba, Body Combat, Pilates, Yoga) dengan tombol reservasi tempat (*Book Slot*).
3. **Form Registrasi Free Trial Day Pass:** Form cepat untuk calon member baru mencoba fasilitas gym gratis 1 hari.

---

### 🏛️ 3.4. Tema 20: Sovereign Enterprise (`enterprise_consulting`)
*Konsultan Manajemen, Kantor Hukum, Agensi Digital & Layanan Profesional B2B*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Corporate prestige and executive trust, sovereign navy and platinum gold accents, Request for Quotation (RFQ) proposal form, client portfolio, and 1-on-1 consultation booking.
- **`DESIGN_VARIANCE: 6`** (Executive boardroom layout, prestigious typography, formal trust credentials)
- **`MOTION_INTENSITY: 4`** (Dignified, smooth transitions)
- **`VISUAL_DENSITY: 3`** (Spacious corporate briefing format)

#### B. Design Tokens & Typography
- **Heading Font:** `Cormorant Garamond` (Prestigious corporate serif)
- **Body Font:** `Plus Jakarta Sans`
- **Google Fonts Link:** `family=Cormorant+Garamond:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700`
- **Color Palette Tokens:**
  - Primary (`--color-primary`): `#0F172A` (Sovereign Navy Slate)
  - Secondary (`--color-secondary`): `#3B82F6` (Diplomatic Blue)
  - Accent / CTA (`--color-accent`): `#D97706` (Executive Platinum Gold)
  - Background (`--color-bg`): `#F8FAFC` (Platinum Clean Wash)
  - Card Background (`--color-card`): `#FFFFFF`
  - Border (`--color-border`): `#E2E8F0`
- **Squircle Radius:** `12px`

#### C. Fitur Spesifik & Interaksi Fungsional
1. **Formulir Permintaan Proposal Resmi (B2B RFQ Generator):** Pelanggan korporat dapat mengirimkan deskripsi proyek, estimasi anggaran (*budget range*), dan tenggat waktu untuk diterbitkan Surat Penawaran Resmi.
2. **Jadwal Booking Sesi Konsultasi Strategis 1-on-1:** Integrasi pemilihan konsultan partner dan jadwal meeting online via Google Meet / Zoom.
3. **Pusat Unduhan Portofolio & Sertifikasi:** Tombol unduh Company Profile (*PDF*) dan rekam jejak studi kasus klien terdahulu.

---

## 4. Kriteria Keberterimaan (Acceptance Criteria)

- [x] Dokumen PRD-24 terdokumentasi lengkap dan terstruktur.
- [ ] 4 tema khusus memiliki file template view di `resources/views/public/storefront/themes/`.
- [ ] Alur checkout B2B mendukung opsi RFQ / Surat Penawaran tanpa pemaksaan pembayaran ritel instan.
- [ ] Formulir interaktif (kalkulator semen, upload desain, jadwal gym, RFQ) teruji responsif dan aman dari injeksi berkas berbahaya.
