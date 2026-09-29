# PRD-23: SPESIFIKASI PRODUK & DESAIN TEMA STOREFRONT KLASTER JASA, OTOMOTIF, EVENT & KREATIF
## (11. Bengkel Mobil & Motor, 12. Auto Detailing & Cuci Mobil, 13. Barbershop & Salon Kecantikan, 14. Laundry Kiloan & Satuan, 15. Event Organizer & Wedding Organizer, 16. Digital Creative Agency / IT Software)

---

## 1. Metadata Dokumen & Referensi Induk
- **Dokumen ID:** `PRD-23-STOREFRONT-SERVICES-HEALTH-BEAUTY`
- **Klaster Industri:** Jasa Otomotif, Salon, Laundry, Event Organizer & IT Agency (6 Sektor Resmi)
- **Folder Sasaran:** `resources/views/public/storefront/themes/` (`service_workshop`, `service_autowash`, `service_salon`, `service_laundry`, `service_event_organizer`, `service_digital_agency`)
- **Keahlian Desain:** `/ui-styling` (Tailwind Tokens & Bento Cards), `/ui-ux-pro-max` (Product Palettes & Typography), `/design-taste-frontend` (Brief Inference & 3 Dials)
- **Master Directive:** [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md), [`docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md`](file:///c:/laragon/www/cooca_core/docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md)
- **Status:** APPROVED & READY FOR IMPLEMENTATION
- **Prioritas:** P1 (Kritis)
- **Tanggal:** 2026-09-29

---

## 2. Business Objective & Cakupan Klaster Jasa & Event

Klaster Jasa berfokus pada keahlian profesional, penjadwalan antrean waktu, penyewaan alat, serta paket pengerjaan terpadu.

PRD-23 menetapkan standar desain visual dan fitur fungsional untuk 6 model bisnis jasa resmi COOCA:

1. **Bengkel Mobil & Motor (`service_workshop`):** Bengkel servis, ganti oli, tune-up & suku cadang (Costing: `job` 3 komponen biaya: Sparepart retail/material, upah mekanik per jam, overhead bengkel).
2. **Auto Detailing & Cuci Mobil (`service_autowash`):** Cuci hidrolik, poles salon mobil, nano ceramic coating (Costing: `service` 3 komponen biaya: Obat poles compound/shampoo pH, upah teknisi poles, listrik/air).
3. **Barbershop & Salon Kecantikan (`service_salon`):** Barbershop pria, salon potong rambut, creambath, nail art & spa (Costing: `service` 3 komponen biaya: Shampoo/pomade/obat, upah stylist/kapster per kepala, listrik/sewa).
4. **Laundry Kiloan & Satuan (`service_laundry`):** Jasa cuci kiloan, dry clean satuan jas, cuci sepatu/helm (Costing: `per_unit` 3 komponen biaya: Detergen/softener/plastik, gas dryer, operator cuci/setrika).
5. **Event Organizer & Wedding Organizer (`service_event_organizer`):** Paket wedding planner, dekorasi lamaran, sound system, MC & crew event (Costing: `job` 3 komponen biaya: Dekorasi/bunga, sewa sound/lighting, honor crew event harian).
6. **Digital Creative Agency / IT Software (`service_digital_agency`):** Jasa pembuatan website, software development, UI/UX design & digital marketing (Costing: `service` 3 komponen biaya: Developer/Designer hourly rate, software subscription/server, PM overhead).

---

## 3. Spesifikasi Mendalam per Sektor Industri

---

### 🏍️ 3.1. Bengkel Mobil & Motor (`service_workshop`)
*Metode HPP: `job` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** High-octane motorsport garage, dark carbon textures, racing orange highlights, vehicle model compatibility filter, and service booking with vehicle plate input.
- **`DESIGN_VARIANCE: 7`** | **`MOTION_INTENSITY: 6`** | **`VISUAL_DENSITY: 5`**

#### B. Design Tokens & Typography
- **Heading Font:** `Chakra Petch` (Technical motorsport sans)
- **Body Font:** `Inter`
- **Google Fonts:** `family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;500;600`
- **Color Palette:** Primary `#EA580C` (Tuned Racing Orange), Accent `#F97316`, Background `#0F172A` (Dark Garage Slate).
- **Squircle Radius:** `10px`

#### C. Fitur Spesifik Interaktif
1. **Vehicle Compatibility Selector:** Filter merek motor/mobil (Honda Vario, Yamaha NMAX, Toyota Avanza).
2. **Booking Jadwal Servis & Input Plat Nomor:** Form booking jam kedatangan dengan nomor polisi dan keluhan mesin.
3. **Opsi Pasang di Bengkel vs Bawa Pulang:** Switcher biaya pasang mekanik langsung pada kartu sparepart.

---

### 🏎️ 3.2. Auto Detailing & Cuci Mobil (`service_autowash`)
*Metode HPP: `service` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Wet-gloss obsidian dark theme, electric blue neon, ceramic coating tier matrix, vehicle size pricing selector, and live wash bay status.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 7`** | **`VISUAL_DENSITY: 5`**

#### B. Design Tokens & Typography
- **Heading Font:** `Orbitron` (Futuristic high-gloss automotive)
- **Body Font:** `Inter`
- **Google Fonts:** `family=Orbitron:wght@600;700;800&family=Inter:wght@400;500;600`
- **Color Palette:** Primary `#2563EB` (Hydro Ceramic Blue), Accent `#06B6D4` (Gloss Cyan), Background `#0B0F19`.
- **Squircle Radius:** `14px`

#### C. Fitur Spesifik Interaktif
1. **Tabel Komparasi Paket Coating (Bronze/Silver/Gold/Diamond 9H):** Lapisan mikron dan garansi 1–5 tahun.
2. **Pemilih Ukuran Mobil (Small/Med/Large):** Penyesuaian tarif otomatis sesuai tipe dimensi mobil.
3. **Indikator Live Antrean Cuci:** Status kuota bay cuci (*cth: "Bay 2/3 Tersedia - Estimasi Masuk 5 Menit"*).

---

### 💇 3.3. Barbershop & Salon Kecantikan (`service_salon`)
*Metode HPP: `service` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Boutique beauty and gentlemen grooming, rose berry luxury, stylist selector, treatment duration badges, and appointment calendar.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 6`** | **`VISUAL_DENSITY: 3`**

#### B. Design Tokens & Typography
- **Heading Font:** `Playfair Display` (Luxury boutique serif)
- **Body Font:** `Plus Jakarta Sans`
- **Google Fonts:** `family=Playfair+Display:ital,wght@0,500;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600`
- **Color Palette:** Primary `#9D174D` (Rose Berry), Accent `#B76E79` (Rose Gold), Background `#FFF5F7`.
- **Squircle Radius:** `20px`

#### C. Fitur Spesifik Interaktif
1. **Pemilih Kapster / Beautician:** Pilihan foto dan profil stylist saat melakukan booking potong rambut.
2. **Badge Estimasi Durasi Pengerjaan:** Indikator waktu (*Durasi: 45 Menit*).
3. **Kalender Slot Jam Kedatangan:** Pemilihan jam yang menonaktifkan slot yang sudah terisi.

---

### 🧼 3.4. Laundry Kiloan & Satuan (`service_laundry`)
*Metode HPP: `per_unit` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Ocean breeze clean laundry, kilo estimation slider, perfume aroma selector, and express 3-hour switch.
- **`DESIGN_VARIANCE: 6`** | **`MOTION_INTENSITY: 5`** | **`VISUAL_DENSITY: 5`**

#### B. Design Tokens & Typography
- **Heading Font:** `Plus Jakarta Sans`
- **Body Font:** `Inter`
- **Google Fonts:** `family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600`
- **Color Palette:** Primary `#0284C7` (Ocean Blue), Accent `#38BDF8` (Foam Sky), Background `#F0F9FF`.
- **Squircle Radius:** `16px`

#### C. Fitur Spesifik Interaktif
1. **Kalkulator Estimasi Timbangan Kiloan:** Slider berat cucian (1–20 Kg) dengan harga live.
2. **Pemilih Aroma Parfum Pakaian:** Pilihan wangi (*Lavender, Ocean Breeze, Cherry Blossom, Downy*).
3. **Sakelar Layanan Kilat (Express 3 Jam vs Reguler 2 Hari):** Pilihan durasi pengerjaan.

---

### 💍 3.5. Event Organizer & Wedding Organizer (`service_event_organizer`)
*Metode HPP: `job` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Grand romantic wedding & event planning, rose gold and champagne prestige, package comparison, and event date booking.
- **`DESIGN_VARIANCE: 9`** | **`MOTION_INTENSITY: 6`** | **`VISUAL_DENSITY: 3`**

#### B. Design Tokens & Typography
- **Heading Font:** `Cormorant Garamond` (Grand celebration serif)
- **Body Font:** `Plus Jakarta Sans`
- **Google Fonts:** `family=Cormorant+Garamond:ital,wght@0,600;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600`
- **Color Palette:** Primary `#BE185D` (Royal Rose), Accent `#D4AF37` (Champagne Gold), Background `#FDF2F8`.
- **Squircle Radius:** `18px`

#### C. Fitur Spesifik Interaktif
1. **Tabel Komparasi Paket Pernikahan / Event (Silver, Gold, Platinum):** Rincian dekorasi panggung, dokumentasi foto/video, MC, sound system, dan tim WO hari-H.
2. **Kalender Booking Tanggal Hari-H:** Form pemilihan tanggal acara dengan pengecekan ketersediaan jadwal.
3. **Galeri Portofolio & Lookbook Acara:** Showcase dokumentasi event yang pernah ditangani.

---

### 💻 3.6. Digital Creative Agency / IT Software (`service_digital_agency`)
*Metode HPP: `service` (3 Komponen Biaya)*

#### A. Brief Inference & The 3 Dials
- **Design Read:** Modern tech agency & software studio, indigo & cyan glow, structured scope of work cards, monthly retainer packages, and 1-on-1 consultation booking.
- **`DESIGN_VARIANCE: 8`** | **`MOTION_INTENSITY: 7`** | **`VISUAL_DENSITY: 4`**

#### B. Design Tokens & Typography
- **Heading Font:** `Space Grotesk` (Modern tech studio sans)
- **Body Font:** `Plus Jakarta Sans`
- **Google Fonts:** `family=Space+Grotesk:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600`
- **Color Palette:** Primary `#4F46E5` (Digital Indigo), Accent `#06B6D4` (Tech Cyan), Background `#F8FAFC`.
- **Squircle Radius:** `14px`

#### C. Fitur Spesifik Interaktif
1. **Pilihan Paket Retainer / Scope of Work:** Paket Website Development, UI/UX Design, SEO & Social Media Marketing.
2. **Formulir Permintaan Brief Proyek:** Input link referensi, estimasi timeline pengerjaan, dan budget range.
3. **Jadwal Booking Konsultasi Online:** Integrasi pemilihan jadwal video call meeting.

---

## 4. Kriteria Keberterimaan (Acceptance Criteria)

- [x] Dokumen PRD-23 mencakup 6 sektor resmi Jasa & Event COOCA.
- [ ] Opsi "Makan di Tempat" disembunyikan 100% pada seluruh form checkout 6 sektor jasa.
- [ ] Kalender booking, input plat kendaraan, dan estimasi timbangan berfungsi secara reaktif.
