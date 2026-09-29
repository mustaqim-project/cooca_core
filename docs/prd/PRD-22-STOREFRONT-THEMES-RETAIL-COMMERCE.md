# PRD-22: SPESIFIKASI PRODUK & DESAIN TEMA STOREFRONT KLASTER RITEL, TRADING & KESEHATAN
## (7. Reseller & Toko Retail, 8. Distributor & Trading FMCG, 9. Apotek & Toko Obat, 10. Kerajinan Tangan & Handmade Craft)

---

## 1. Metadata Dokumen & Referensi Induk
- **Dokumen ID:** `PRD-22-STOREFRONT-RETAIL-COMMERCE`
- **Klaster Industri:** Ritel, Grosir FMCG, Farmasi & Kerajinan Seni (4 Sektor Resmi)
- **Folder Sasaran:** `resources/views/public/storefront/themes/` (`retail_reseller`, `trading_fmcg`, `retail_pharmacy`, `creative_craft`)
- **Keahlian Desain:** `/ui-styling` (Tailwind Tokens & Bento Cards), `/ui-ux-pro-max` (Product Palettes & Typography), `/design-taste-frontend` (Brief Inference & 3 Dials)
- **Master Directive:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md`](file:///c:/laragon/www/cooca_core/docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md)
- **Status:** APPROVED & READY FOR IMPLEMENTATION
- **Prioritas:** P1 (Kritis)
- **Tanggal:** 2026-09-29

---

## 2. Business Objective & Cakupan Klaster Ritel & Perdagangan

Klaster ini mencakup penjualan produk jadi dari supplier, distribusi kartonan volume besar, obat resep resmi, hingga produk kerajinan tangan bernilai seni tinggi.

PRD-22 menetapkan standar desain visual dan fitur fungsional untuk 4 model bisnis perdagangan resmi COOCA:

1. **Reseller & Toko Retail (`retail_reseller`):** Toko ritel serba ada, busana, kosmetik reseller, sembako harian (Costing: `retail` 3 komponen biaya: Harga beli supplier, ongkir masuk, diskon beli).
2. **Distributor & Trading FMCG (`trading_fmcg`):** Grosir distributor kartonan FMCG, agen logistik toko (Costing: `retail` 4 komponen biaya: Beli karton pabrik, armada truk box, sopir/kernet, handling gudang).
3. **Apotek & Toko Obat (`retail_pharmacy`):** Apotek resmi berizin, obat etikal, suplemen kesehatan, alat medis (Costing: `retail` 3 komponen biaya: Beli obat distributor PBF, shrinkage expired date buffer, embalase racikan).
4. **Kerajinan Tangan & Handmade Craft (`creative_craft`):** Produk kerajinan rotan, batik tulis, ukiran kayu, handmade gift (Costing: `simple` 3 komponen biaya: Bahan baku seni mentah, upah ketelitian pengrajin, kemasan hampers artistik).

---

## 3. Spesifikasi Mendalam per Sektor Industri

---

### 🛍️ 3.1. Reseller & Toko Retail (`retail_reseller`)
*Metode HPP: `retail` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** High-speed, high-density consumer retail catalog with instant +/- cart steppers without opening product detail pages, free shipping progress bar.
- **`DESIGN_VARIANCE: 5`** | **`MOTION_INTENSITY: 4`** | **`VISUAL_DENSITY: 8`**

#### B. Design Tokens & Typography
- **Heading Font:** `Plus Jakarta Sans`
- **Body Font:** `Inter`
- **Google Fonts:** `family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600`
- **Color Palette:** Primary `#16A34A` (Fresh Retail Green), Accent `#EA580C` (Market Orange), Background `#F8FAFC`.
- **Squircle Radius:** `12px`

#### C. Fitur Spesifik Interaktif
1. **Instant Inline Cart Stepper (`+ / -`):** Tambah belanjaan langsung dari kartu etalase katalog.
2. **Bar Gratis Ongkir Interaktif:** Progress bar yang menghitung selisih nominal menuju gratis ongkos kirim.
3. **Penyaring Kategori Cepat (Quick Rail):** Tab filter sticky horizontal untuk kategori terlaris.

---

### 🚛 3.2. Distributor & Trading FMCG (`trading_fmcg`)
*Metode HPP: `retail` (4 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Heavy B2B wholesale FMCG distributor, carton bulk pricing tiers, truck fleet selection, and minimum order quantity (MOQ) validation.
- **`DESIGN_VARIANCE: 5`** | **`MOTION_INTENSITY: 4`** | **`VISUAL_DENSITY: 7`**

#### B. Design Tokens & Typography
- **Heading Font:** `Plus Jakarta Sans` (Professional B2B)
- **Body Font:** `Inter`
- **Google Fonts:** `family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600`
- **Color Palette:** Primary `#1D4ED8` (Royal Wholesale Blue), Accent `#EA580C`, Background `#F8FAFC`.
- **Squircle Radius:** `10px`

#### C. Fitur Spesifik Interaktif
1. **Tabel Harga Grosir per Karton/Dus:** Diskon bertingkat (*Beli 10 Dus, 50 Dus, 100 Dus/Pallet*).
2. **Pilihan Armada Truk Box Pengiriman:** Radio selector armada (*Pick-up Box, Truk Engkel, Truk Double Fuso*).
3. **Tombol Unduh Faktur Proforma (Quotation):** Opsi checkout PO resmi untuk pembayaran termin invoice.

---

### 💊 3.3. Apotek & Toko Obat (`retail_pharmacy`)
*Metode HPP: `retail` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Sterile medical clinical trust, BPOM/Kemenkes certification badges, encrypted doctor prescription upload dropzone, and clear OTC vs Ethical medicine indicators.
- **`DESIGN_VARIANCE: 4`** | **`MOTION_INTENSITY: 3`** | **`VISUAL_DENSITY: 4`**

#### B. Design Tokens & Typography
- **Heading Font:** `Inter` (Pure clinical clarity)
- **Body Font:** `Inter`
- **Google Fonts:** `family=Inter:wght@400;500;600;700;800`
- **Color Palette:** Primary `#0D9488` (Clinical Hospital Teal), Accent `#0284C7` (Medical Blue), Background `#F0FDFA`.
- **Squircle Radius:** `12px`

#### C. Fitur Spesifik Interaktif
1. **Dropzone Unggah Foto Resep Dokter:** Pengunggahan foto resep fisik dokter dengan enkripsi privat ke apoteker toko.
2. **Badge Klasifikasi Obat (Lingkaran Hijau/Biru/Merah K):** Penanda visual obat Bebas, Terbatas, dan Keras.
3. **Petunjuk Dosis & Aturan Minum:** Kotak informasi penggunaan obat (Sebelum/Sesudah Makan) dan nomor izin BPOM.

---

### 🎨 3.4. Kerajinan Tangan & Handmade Craft (`creative_craft`)
*Metode HPP: `simple` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Artisanal handmade heritage, warm earthy terracotta tones, artisan storytelling cards, and custom engraving options.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 7`** | **`VISUAL_DENSITY: 3`**

#### B. Design Tokens & Typography
- **Heading Font:** `Quicksand` (Artisanal rounded & expressive)
- **Body Font:** `Inter`
- **Google Fonts:** `family=Quicksand:wght@600;700&family=Inter:wght@400;500;600`
- **Color Palette:** Primary `#C2410C` (Terracotta Clay), Accent `#10B981` (Artisan Green), Background `#FFF7ED`.
- **Squircle Radius:** `18px`

#### C. Fitur Spesifik Interaktif
1. **Formulir Kustomisasi Grafir & Ukir Nama:** Input teks kustom untuk ukiran nama pada kerajinan kayu/kulit.
2. **Cerita Pengrajin & Proses Pembuatan (Artisan Bio):** Tab narasi asal daerah seni, teknik menenun/memahat, dan keaslian material.
3. **Pilihan Kemasan Kado Artistik (Gift Wrap Box):** Checkbox penambahan pita rami, kotak kayu estetik, dan kartu ucapan buatan tangan.

---

## 4. Kriteria Keberterimaan (Acceptance Criteria)

- [x] Dokumen PRD-22 mencakup 4 sektor resmi Ritel, FMCG, Farmasi & Kerajinan COOCA.
- [ ] Berkas template view untuk 4 sektor tersedia di `resources/views/public/storefront/themes/`.
- [ ] Pengunggahan resep dokter, kalkulator karton, dan kustomisasi grafir berfungsi lancar pada cart state.
