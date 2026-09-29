# PRD-21: SPESIFIKASI PRODUK & DESAIN TEMA STOREFRONT KLASTER F&B & KULINER
## (1. Coffee Shop & Cafe, 2. Restoran / Rumah Makan, 3. Cloud Kitchen & Delivery, 4. Bakery & Cake Shop, 5. Catering & Prasmanan, 6. Diet & Healthy Catering)

---

## 1. Metadata Dokumen & Referensi Induk
- **Dokumen ID:** `PRD-21-STOREFRONT-FNB-CULINARY`
- **Klaster Industri:** F&B, Kuliner, Minuman, Bakery & Katering (6 Sektor Resmi)
- **Folder Sasaran:** `resources/views/public/storefront/themes/` (`fnb_cafe`, `fnb_resto`, `fnb_cloud_kitchen`, `fnb_bakery`, `fnb_catering`, `fnb_diet_catering`)
- **Keahlian Desain:** `/ui-styling` (Tailwind Tokens & Bento Cards), `/ui-ux-pro-max` (Product Palettes & Typography), `/design-taste-frontend` (Brief Inference & 3 Dials)
- **Master Directive:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md`](file:///c:/laragon/www/cooca_core/docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md)
- **Status:** APPROVED & READY FOR IMPLEMENTATION
- **Prioritas:** P1 (Kritis)
- **Tanggal:** 2026-09-29

---

## 2. Business Objective & Cakupan Klaster F&B

Klaster F&B melayani penjualan makanan, minuman, hidangan pesta, hingga langganan diet. PRD-21 menetapkan standar desain visual dan fitur fungsional untuk 6 model bisnis kuliner resmi COOCA:

1. **Coffee Shop & Cafe (`fnb_cafe`):** Kafe artisan, kedai kopi, roastery manual brew (BOM 5 komponen biaya: Biji kopi, susu/sirup, cup/sedotan, upah barista, depresiasi espresso).
2. **Restoran / Rumah Makan (`fnb_resto`):** Rumah makan tradisional, masakan nusantara, seafood & family dining (BOM 6 komponen biaya: Bahan makanan, bumbu, kemasan, upah koki, gas/listrik, sewa ruko).
3. **Cloud Kitchen & Delivery Only (`fnb_cloud_kitchen`):** Gerai delivery-only, burger, ayam goreng & snack cepat saji (BOM 3 komponen biaya: Bahan porsi, box packaging premium, jasa chef).
4. **Bakery & Cake Shop (`fnb_bakery`):** Toko roti, kue tart ulang tahun, pastry & donat (BOM 4 komponen biaya: Tepung adonan, topping cokelat/keju, baker hours, listrik oven deck).
5. **Catering & Prasmanan (`fnb_catering`):** Katering prasmanan pernikahan, gathering kantor, tumpeng & nasi kotak (Job costing 3 komponen biaya: Bahan menu prasmanan, upah masak/pelayan harian, sewa alat saji).
6. **Diet & Healthy Catering (`fnb_diet_catering`):** Katering harian berbasis hitungan kalori, diet mayo & meal prep (BOM 4 komponen biaya: Bahan organik makronutrisi, kemasan microwave-safe, koki gizi, kurir harian).

---

## 3. Spesifikasi Mendalam per Sektor Industri

---

### ☕ 3.1. Coffee Shop & Cafe (`fnb_cafe`)
*Metode HPP: `recipe_bom` (5 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Specialty coffee roastery with earthy warm aesthetic, aroma tasting notes, grind size selector, and table QR dine-in ordering.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 6`** | **`VISUAL_DENSITY: 3`**

#### B. Design Tokens & Typography
- **Heading Font:** `Playfair Display` (Serif elegan artisan)
- **Body Font:** `Inter`
- **Google Fonts:** `family=Playfair+Display:ital,wght@0,400..800;1,400..800&family=Inter:wght@300;400;500;600;700`
- **Color Palette:** Primary `#8B5A2B` (Roasted Espresso), Accent `#C88A58` (Crema), Background `#FAF7F2` (Warm Paper).
- **Squircle Radius:** `16px`

#### C. Fitur Spesifik Interaktif
1. **Bean Tasting Notes & Origin Badges:** Catatan rasa (Floral, Citrus, Nutty, Brown Sugar) dan asal kebun (Aceh Gayo, Kerinci).
2. **Grind Size Selector:** Pilihan gilingan: Biji Utuh, Kasar (Cold Brew), Sedang (V60), Halus (Espresso).
3. **QR Meja & Dine-In Ordering:** Form pesanan otomatis mendeteksi nomor meja saat discan di cafe.

---

### 🍛 3.2. Restoran / Rumah Makan (`fnb_resto`)
*Metode HPP: `recipe_bom` (6 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Traditional heritage dining, terracotta warmth, banquet feast layouts, spice level indicators, and VIP room reservations.
- **`DESIGN_VARIANCE: 7`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 4`**

#### B. Design Tokens & Typography
- **Heading Font:** `DM Serif Display` (Serif hangat masakan nusantara)
- **Body Font:** `Plus Jakarta Sans`
- **Google Fonts:** `family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800`
- **Color Palette:** Primary `#991B1B` (Chili Red), Accent `#D97706` (Turmeric), Background `#FFFBEB` (Warm Jasmine Rice).
- **Squircle Radius:** `14px`

#### C. Fitur Spesifik Interaktif
1. **Tingkat Kepedasan Interaktif:** Pilihan level pedas (Level 0 s/d Level 5) dengan ikon cabai semantik.
2. **Opsi Tambahan Lauk & Sambal:** Checkbox cepat Nasi Uduk, Sambal Terasi, Lalapan Segar.
3. **Reservasi Meja VIP & Lesehan:** Pemilihan area meja (Lesehan Saung, Indoor AC, VIP Room Meeting).

---

### 📦 3.3. Cloud Kitchen & Delivery Only (`fnb_cloud_kitchen`)
*Metode HPP: `recipe_bom` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** High-energy quick-service delivery brand, bold red accents, combo meal builders, and fast add-to-cart.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 8`** | **`VISUAL_DENSITY: 6`**

#### B. Design Tokens & Typography
- **Heading Font:** `Outfit` (Bold geometric punchy)
- **Body Font:** `Inter`
- **Google Fonts:** `family=Outfit:wght@600;700;800;900&family=Inter:wght@400;500;600;700`
- **Color Palette:** Primary `#DC2626` (Vibrant Sriracha), Accent `#F59E0B` (Cheddar Gold), Background `#FFF1F2`.
- **Squircle Radius:** `20px`

#### C. Fitur Spesifik Interaktif
1. **Combo Meal Upsize Builder:** Drawer cepat untuk upgrade paket (*Ala Carte $\rightarrow$ Paket Combo + Fries + Drink*).
2. **Delivery / Pickup Switcher:** Sakelar pemenuhan cepat di bagian atas katalog.
3. **Flash Deals Countdown Bar:** Bar hitung mundur promo kilat dengan progress sisa stok porsi.

---

### 🎂 3.4. Bakery & Cake Shop (`fnb_bakery`)
*Metode HPP: `recipe_bom` (4 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** French patisserie & celebration cakes, soft berry tones, pre-order calendar slots, and cake inscription message box.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 6`** | **`VISUAL_DENSITY: 3`**

#### B. Design Tokens & Typography
- **Heading Font:** `Cormorant Garamond` (Serif anggun kue tart)
- **Body Font:** `Poppins`
- **Google Fonts:** `family=Cormorant+Garamond:ital,wght@0,500;0,700;1,500&family=Poppins:wght@300;400;500;600;700`
- **Color Palette:** Primary `#BE185D` (Velvet Rose Berry), Accent `#D4AF37` (Gold), Background `#FDF2F8`.
- **Squircle Radius:** `18px`

#### C. Fitur Spesifik Interaktif
1. **Pemilih Diameter Kue:** Pilihan diameter kue (*16 cm, 20 cm, 24 cm*).
2. **Tulisan Ucapan di Atas Kue:** Input teks ucapan plat cokelat (*cth: "Happy Birthday Amanda"*) dengan preview live.
3. **Kalender Pre-Order H-3:** Datepicker yang mengunci slot H-1 secara otomatis.

---

### 🍲 3.5. Catering & Prasmanan (`fnb_catering`)
*Metode HPP: `job` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Grand wedding & corporate buffet catering, banquet packages, portion calculation, and event calendar booking.
- **`DESIGN_VARIANCE: 7`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 4`**

#### B. Design Tokens & Typography
- **Heading Font:** `DM Serif Display`
- **Body Font:** `Plus Jakarta Sans`
- **Google Fonts:** `family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800`
- **Color Palette:** Primary `#D97706` (Royal Amber Gold), Accent `#B45309`, Background `#FFFBEB`.
- **Squircle Radius:** `16px`

#### C. Fitur Spesifik Interaktif
1. **Kalkulator Jumlah Porsi Prasmanan:** Pilihan paket buffet (50 / 100 / 250 / 500 Porsi) dengan rincian menu gubukan.
2. **Kalender Booking Tanggal Acara:** Pemilihan tanggal resepsi/gathering dengan opsi sewa meja & pemanas saji.
3. **Pilihan Menu Custom:** Checkbox ganti menu utama, sop, sayuran, dan hidangan penutup.

---

### 🥗 3.6. Diet & Healthy Catering (`fnb_diet_catering`)
*Metode HPP: `recipe_bom` (4 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Fresh nutrition & organic wellness catering, weekly meal matrix, calorie and macro nutrition badges, and dietary preference options.
- **`DESIGN_VARIANCE: 6`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 4`**

#### B. Design Tokens & Typography
- **Heading Font:** `Plus Jakarta Sans`
- **Body Font:** `Inter`
- **Google Fonts:** `family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600`
- **Color Palette:** Primary `#059669` (Organic Emerald), Accent `#10B981` (Fresh Mint), Background `#F0FDF4`.
- **Squircle Radius:** `16px`

#### C. Fitur Spesifik Interaktif
1. **Weekly Menu Schedule Matrix:** Pengunjung dapat melihat menu Senin–Jumat beserta foto hidangan.
2. **Badge Informasi Nutrisi & Kalori:** Indikator Kkal, Protein (g), Karbohidrat (g), dan Lemak (g).
3. **Preferensi Alergi & Diet:** Pilihan program diet (Keto, Rendah Garam, High Protein, Halal).

---

## 4. Kriteria Keberterimaan (Acceptance Criteria)

- [x] Dokumen PRD-21 mencakup 6 sektor resmi F&B COOCA.
- [ ] Berkas template view untuk 6 sektor F&B tersedia di `resources/views/public/storefront/themes/`.
- [ ] Fitur kalkulator porsi, gilingan kopi, level pedas, tulisan kue, dan jadwal menu terintegrasi ke cart state.
