# Pola Baca Visual & Hierarki Tata Letak Antarmuka (Reading Patterns & Visual Weight)

Dokumen ini memuat panduan mendalam tentang bagaimana mata manusia memindai layar (*eye-tracking & cognitive processing*), pembagian proporsi Bento Grid 12-kolom, dan penataan bobot visual untuk antarmuka bisnis di ekosistem COOCA ID & POS v2.0.

---

## 👁️ 1. Tiga Pola Pindai Mata Manusia (*Reading Patterns*)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        POLA PINDAI MATA PADA APLIKASI WEB                              │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 1. POLA F (F-PATTERN) - Halaman Padat Data & Laporan                                   │
│    • Pengguna memindai baris pertama dari kiri ke kanan (Judul + Aksi Utama).         │
│    • Mata turun ke baris kedua, memindai lebih pendek (Kartu KPI Ringkasan).           │
│    • Mata bergerak vertikal ke bawah di sepanjang sisi kiri (Navigasi / Kolom Utama).  │
│    ➡️ IMPLIKASI: Letakkan status dan metrik kunci di sisi atas dan kiri!              │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 2. POLA Z (Z-PATTERN) - Dashboard & Halaman Landing/Overview                           │
│    • Kiri Atas: Logo / Context Title (Awal Pindai).                                    │
│    • Kanan Atas: Primary CTA / Filter Tanggal (Target Pindai Horizontal).              │
│    • Diagonal ke Kiri Bawah: Grafik Utama / Ringkasan Tren.                            │
│    • Kanan Bawah: Tombol Konfirmasi / Aksi Lanjutan.                                   │
│    ➡️ IMPLIKASI: Aksi primer di kanan atas atau kanan bawah!                           │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 3. POLA LAPISAN KUE (LAYER-CAKE PATTERN) - Form & Halaman Terstruktur                  │
│    • Pengguna membaca header seksi dan melompati body teks jika tidak relevan.         │
│    ➡️ IMPLIKASI: Gunakan Bento Card Header yang tegas dengan tipografi kontras!        │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🍱 2. Pembagian Proporsi Asimetris Bento Grid 12-Kolom

Antarmuka modern Bento Apple HIG v2.0 menggunakan pembagian grid 12-kolom yang dinamis dan berimbang:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                       STRUKTUR BENTO GRID 12-KOLOM STANDAR                             │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ BARIS 1: 4 KARTU KPI EQUAL METRICS (4 x col-span-3 di desktop / 2 x col-span-6 tablet) │
│ [ KPI 1: col-span-3 ]  [ KPI 2: col-span-3 ]  [ KPI 3: col-span-3 ]  [ KPI 4: col-span-3│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ BARIS 2: ASYMMETRIC ANALYTICS HERO (Rasio 8:4 di desktop / Full width 12 di mobile)   │
│ [ KIRI: GRAFIK TREN UTAMA - col-span-12 lg:col-span-8 ] │ [ KANAN: KOMPOSISI - 4-col ] │
│ • Multi-Bar / Line Chart Dinamis                        │ • Donut Chart Kategori / PO   │
│ • Toolbar Filter Periode Terintegrasi                   │ • Top 3 Quick Action Links    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ BARIS 3: COMPREHENSIVE DATA LEDGER (12 Kolom Penuh)                                    │
│ [ TABEL TRANSAKSI / AKTIVITAS TERKINI - col-span-12 ]                                  │
│ • Pencarian Cepat + Filter Status + Tombol Drill-Down Detail Drawer + Pagination       │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## ⚖️ 3. Skala Bobot Visual (*Visual Weight & Contrast Rules*)

Untuk mencegah antarmuka terlihat datar atau sebaliknya terlalu ramai (*visual noise*), terapkan hierarki bobot 3 tingkat:

| Tingkat Hierarki | Elemen UI | Tipografi & Ukuran | Warna & Kontras | Spacing / Margin |
|---|---|---|---|---|
| **Level 1 (Dominan / Anchor)** | H1 Page Title, Nilai Angka KPI Utama, Primary Action Button | `text-2xl sm:text-3xl font-bold`, `tabular-nums` | Hitam Pekat `#1C1C1E` / Dark Mode `#FFFFFF`, Brand Solid `#007AFF` | Margin Bawah `mb-6` |
| **Level 2 (Struktural / Navigasi)** | Subtitle, Judul Bento Card, Filter Presets, Badge Status | `text-sm sm:text-base font-semibold` | Abu-abu Gelap `#3A3A3C`, Badge Soft Tinted (15% opacity) | Padding Card `p-5 sm:p-6` |
| **Level 3 (Pendukung / Detail)** | Overline, Label KPI, Helper text, Timestamp, Pagination | `text-xs font-medium` | Abu-abu Netral `#8E8E93` | Gap elemen `gap-2` atau `gap-3` |

---

## 📱 4. Aturan Transformasi Mobile (Responsive Collapse)

1. **Stacking Vertikal Logis:** Elemen sebelah kiri (8-kolom) selalu berada di atas elemen sebelah kanan (4-kolom) saat dibuka di layar HP.
2. **KPI Card Grid:** 4 kartu metrik horizontal di desktop otomatis berubah menjadi grid $2 \times 2$ di tablet atau slider horizontal / stack $1 \times 4$ di layar HP sempit ($\le 390\text{px}$).
3. **Sticky Action Bar:** Tombol aksi utama halaman di mobile dapat diposisikan sebagai sticky bottom bar agar selalu dalam jangkauan ibu jari (*Thumb Zone*).
