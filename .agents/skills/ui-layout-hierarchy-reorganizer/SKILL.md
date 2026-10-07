---
name: ui-layout-hierarchy-reorganizer
description: Menganalisa tampilan antarmuka yang sudah ada dan menyusun ulang hierarki layout, posisi elemen, dan alur informasi visual (Visual & Information Architecture Reordering) secara murni dan non-destruktif tanpa mengubah business logic, variabel, atau event handler. Aktifkan saat user meminta analisa tampilan, merapikan urutan layout, menyusun ulang posisi kartu/widget/tombol, menata hierarki informasi dashboard/form/halaman, "susun ulang tampilan", "rapikan posisi layout", atau "atur ulang hierarki tampilan".
---

# UI LAYOUT & VISUAL HIERARCHY REORGANIZER SKILL (NON-DESTRUCTIVE INFORMATION REORDERING)

Skill spesialis ini memandu AI Agent dalam **menganalisa tampilan visual antarmuka yang sudah ada dan menyusun ulang hierarki tata letak (layout), posisi kartu/widget/tombol, serta alur penyajian informasi (Information Architecture & Cognitive Flow)** agar lebih logis, berkelas (Bento Apple HIG v2.0), dan nyaman dipindai mata manusia — **MURNI menyempurnakan susunan posisi tanpa merusak logika bisnis, variabel backend, atau event frontend**.

---

## 🧭 Berkas Referensi Pendukung

* [`references/visual-hierarchy-and-reading-patterns.md`](file:///c:/laragon/www/cooca_core/.agents/skills/ui-layout-hierarchy-reorganizer/references/visual-hierarchy-and-reading-patterns.md) — Pola baca mata manusia (F-Pattern, Z-Pattern, Layer-Cake), pembagian grid 12-kolom asimetris, dan kontras bobot visual.
* [`references/non-destructive-blade-reordering-guide.md`](file:///c:/laragon/www/cooca_core/.agents/skills/ui-layout-hierarchy-reorganizer/references/non-destructive-blade-reordering-guide.md) — Aturan wajib non-destruktif: menjaga variabel Blade, Alpine.js reactivity, ID modal, form inputs, dan event handlers tetap 100% utuh saat menyusun ulang elemen.
* [`c:/laragon/www/cooca_core/.agents/skills/cooca-agent-directive/references/design-system.md`](file:///c:/laragon/www/cooca_core/.agents/skills/cooca-agent-directive/references/design-system.md) — Master Design System Bento Apple HIG v2.0.

---

## 🏛️ 3 Mandat Utama Skill

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│               3 MANDAT UTAMA PENATAAN ULANG HIERARKI TAMPILAN                          │
├─────────────────────────┬──────────────────────────┬───────────────────────────────────┤
│ 1. ANALISA TAMPILAN     │ 2. REORDERING HIERARKI   │ 3. 100% NON-DESTRUKTIF            │
│ • Petakan alur visual   │ • Susun ulang tata letak │ • DILARANG merusak variabel Blade │
│   eksisting (Z & F flow)│   berbasis 3 Zona Logis  │ • DILARANG merusak Alpine.js state│
│ • Identifikasi informasi│ • Bento Grid 12-Kolom    │ • DILARANG mengubah action route  │
│   tenggelam / clutter   │ • Prioritaskan aksi & KPI│ • MURNI memposisikan ulang elemen │
└─────────────────────────┴──────────────────────────┴───────────────────────────────────┘
```

---

## 📐 1. Standar 3 Zona Hierarki Informasi Logis & Anatomi Halaman

### Standard Page Anatomy:
```text
Page
│
├── 1. Breadcrumb (Konteks & Posisi User)
├── 2. Page Header (H1 Title + 1-Line Description + Max 1 Primary Action)
├── 3. Summary / KPI Metrics (Bento Grid Desktop / Snap Slider Mobile)
├── 4. Toolbar (Search, Filter, Sort, View, Secondary Actions)
├── 5. Main Content (Table, Form, Chart, Detail, Empty State)
└── 6. Pagination / Secondary Footer
```

### 6 Core Questions in Cognitive Flow:
1. **WHERE AM I?** (Breadcrumb & active menu)
2. **WHAT IS THIS PAGE?** (Page Title H1)
3. **WHAT IS IMPORTANT?** (Status Siklus / KPI kunci)
4. **WHAT CAN I DO?** (Primary Action & Toolbar)
5. **WHAT DATA SHOULD I READ?** (Clean scannable data interface)
6. **WHAT SHOULD I DO NEXT?** (Submit / Pagination / Next step)

### Standar 3 Zona Vertikal Terpadu:
```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ 📍 ZONA 1: ANCHOR & EXECUTIVE SNAPSHOT (Puncak Layar - 1st Fold)                       │
│ 1. Unified 3-Baris Page Header:                                                        │
│    • Overline Breadcrumb / Domain Identifier (teks abu-abu netral 11pt, tanpa pill).   │
│    • Baris Utama: H1 Title tebal + Status Badge + Primary Action Button kanan atas.    │
│    • Subtitle Padat: 1 baris penjelasan tujuan halaman (maks 10-15 kata).              │
│ 2. Metrik Kunci KPI (Adaptif Lintas Perangkat):                                         │
│    • Desktop: Bento Grid 4-Kolom sejajar.                                              │
│    • Mobile: Horizontal Snap Slider (swipe jempol, hemat 75% scroll) ATAU Grid 2x2.   │
│      ⚠️ DILARANG menumpuk 4 kartu vertikal satu per satu di mobile (Infinite Bloat)!   │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 📊 ZONA 2: ANALYTICAL & TACTICAL WORKFLOWS (Tengah Layar - 2nd Fold)                   │
│ 1. Filter & Period Selector Toolbar: Berada tepat di atas grafik/tabel yang dipengaruhi│
│ 2. Presentasi Analitikal & Alur Kerja:                                                 │
│    • Desktop: Asymmetric Bento Grid (8-Kolom Tren vs 4-Kolom Komposisi/Quick Actions). │
│    • Mobile: 1 Grafik Aktif + Segmented Control Tab (BUKAN multi-grafik bertumpuk).    │
│    • Alur Langkah/Onboarding di Mobile: Horizontal Snap Slider / Compact Inline Stepper│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 📑 ZONA 3: DETAILED LEDGER & OPERATIONAL FEED (Bawah Layar - 3rd Fold)                 │
│ 1. Tabel Data Interaktif Lengkap (Desktop) / Grouped Card List Ringkas (Mobile).       │
│ 2. Pagination & Sorting Toolbar.                                                       │
│ 3. Audit Log / Timeline Aktivitas Terakhir (Secondary Supporting Data).               │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

### Visual Priority Testing:
- **3-Second Glanceability Test**: Dalam 3 detik, user wajib tahu: 1. Halaman apa? 2. Konteksnya apa? 3. Aksi utamanya apa?
- **10-Second Scannability Test**: Dalam 10 detik, user wajib tahu: 1. Di mana data penting? 2. Di mana search/filter? 3. Status item? 4. Aksi berikutnya?


---

## 🔍 2. Protokol Analisa Tampilan (7 Langkah Evaluasi)

Sebelum melakukan modifikasi susunan HTML/Blade, lakukan audit visual terhadap tampilan yang ada:

1. **Evaluasi F-Pattern & Scannability:** Apakah mata pengguna langsung menangkap angka/status paling penting dalam 3 detik pertama tanpa harus menggulir ke bawah?
2. **Evaluasi Posisi Tombol & CTA:** Apakah tombol aksi utama (*Primary Action*) berada di lokasi alami (Kanan atas pada desktop, Thumb Zone bawah pada mobile) atau justru tercecer di tengah-tengah kartu?
3. **Evaluasi Card-Level Micro-Hierarchy & Action Proximity:** Apakah tombol aksi di dalam kartu langsung menyusul teks ajakannya (*Action Proximity*), atau terputus/terlempar ke bawah karena terhalang kartu-kartu panduan langkah?
4. **Evaluasi Anti-Bento-Dogmatism di Mobile:** **TIDAK SEMUA HARUS BENTO!** Apakah di mobile terdapat tumpukan kartu Bento raksasa yang membuat halaman terlalu panjang (*Infinite Card Bloat*)? Jika ada deretan KPI, alur onboarding, atau pengaturan, gunakan **Horizontal Snap Slider** (`snap-x snap-mandatory`), **Grouped Inset List**, atau **Compact Stepper**.
5. **Evaluasi Kedekatan Kontrol dengan Target (*Proximity*):** Apakah filter tanggal dan dropdown cabang berada tepat di atas grafik/tabel yang dikendalikannya, atau terisolasi jauh di tempat lain?
6. **Evaluasi Kepadatan Visual & Affordance (*Density & False Affordance*):** Apakah ada kartu kecil yang terpisah-pisah tanpa alasan? Apakah ada elemen panduan langkah (*stepper/onboarding*) yang memakai style tombol interaktif sehingga membingungkan pengguna?
7. **Evaluasi Proporsi Grid Desktop vs Mobile:** Apakah tata letak desktop memanfaatkan lebar layar 12-kolom dengan seimbang, dan apakah tampilan otomatis runtuh (*collapse*) secara anggun di mobile tanpa overflow horizontal?

---

### 📱 2.2 Panduan Pola Alternatif Mobile (Kapan Pakai Bento vs Slider vs Grouped List)

```
┌─────────────────────────────────────────────────────────────────────────────────────────┐
│                       PILIH POLA SESUAI PERANGKAT & KEBUTUHAN DATA                      │
├──────────────────────────┬─────────────────────────────┬────────────────────────────────┤
│ Pola Tampilan            │ Kapan Digunakan             │ Keunggulan di Mobile           │
├──────────────────────────┼─────────────────────────────┼────────────────────────────────┤
│ 1. Horizontal Slider     │ • Kartu KPI (3-4 metrik)    │ • Hemat 75% scroll vertikal    │
│    (Snap Carousel)       │ • Panduan Onboarding        │ • Cukup digeser dengan jempol  │
│    `snap-x snap-mandatory`│ • Shortcut aksi cepat      │ • Terlihat rapi & modern       │
├──────────────────────────┼─────────────────────────────┼────────────────────────────────┤
│ 2. Grouped Inset List    │ • Menu Pengaturan / Form    │ • Menggabungkan 5 kartu jadi 1 │
│    (iOS Settings Style)  │ • Konfigurasi Integrasi     │ • Baris sentuh 48px divide-y   │
│    `divide-y` 1 kontainer│ • Detail Master-Data        │ • Tanpa padding berulang boros │
├──────────────────────────┼─────────────────────────────┼────────────────────────────────┤
│ 3. Compact Stepper Line  │ • Alur 1-Klik / Verifikasi  │ • 1 baris titik/angka minimalis│
│    `1 ── 2 ── 3`         │ • Panduan ringkas wizard    │ • Tidak mengalihkan fokus CTA  │
├──────────────────────────┼─────────────────────────────┼────────────────────────────────┤
│ 4. Segmented Control Tab │ • Filter periode (7d/30d)   │ • Menghindari multi-grafik     │
│    Pill Switcher         │ • Switch Omzet vs Laba      │ • Hanya 1 grafik aktif tampil  │
└──────────────────────────┴─────────────────────────────┴────────────────────────────────┘
```

---

### 🎛️ 2.1 Standar Alur Mikro Kartu Aksi & Integrasi (Card-Level Action Flow)

Untuk kartu dengan aksi tunggal (seperti Onboarding 1-Klik, Integrasi WABA/Marketplace, atau Pengaturan Modul), terapkan hierarki vertikal ketat:

```
┌────────────────────────────────────────────────────────────────────────┐
│ [Icon Squircle]  Judul Kartu + Subtitle Ringkas        [Status Badge]  │
├────────────────────────────────────────────────────────────────────────┤
│ 💡 Banner Ringkas Kuota/Benefit (Padding aman py-3.5 px-4)             │
├────────────────────────────────────────────────────────────────────────┤
│ Teks Pengantar Aksi (Lugas, padat, tanpa kata kaku "klik tombol di bawah")│
│                                                                        │
│ [Primary CTA Button: min-h-[44px] px-6 rounded-[14px] sm:w-auto]       │
│  • Label padat (2-4 kata, whitespace-nowrap, TANPA kata yatim)         │
│  • Ikon shrink-0 sejajar di tengah (inline-flex gap-2.5)               │
├────────────────────────────────────────────────────────────────────────┤
│ Quiet Informational Stepper (Bukan kartu tombol palsu!):               │
│  ① Langkah 1 (Teks abu-abu redup)  ② Langkah 2   ③ Selesai (Hijau)     │
├────────────────────────────────────────────────────────────────────────┤
│ Status Operasional / Toggle Sekunder (Di footer kartu)                 │
└────────────────────────────────────────────────────────────────────────┘
```

> **DILARANG:** Menaruh kartu langkah 1-2-3 di antara teks ajakan dan tombol aksi. Posisi ini memutus alur kognitif (*Action Proximity Inversion*) dan membuat tombol terasing di dasar kartu.

---

### 🪟 2.3 Standar Pop-Up Modal Form Full-Size Lintas Device & Dual Light/Dark Mode

Setiap pop-up modal formulir (Create, Edit, Show, Setting, Detail) **DILARANG KERAS** menggunakan ukuran sempit (`max-w-md` / `max-w-lg`) yang memicu sesak visual dan scroll sempit:

```
┌────────────────────────────────────────────────────────────────────────┐
│ [Overline]  Judul Modal (H3) + Deskripsi Singkat               [ (X) ] │
├────────────────────────────────────────────────────────────────────────┤
│ ┌───────────────────────────┐ ┌──────────────────────────────────────┐ │
│ │ 🔲 KARTU KIRI (7-KOLOM)    │ │ 🔲 KARTU KANAN (5-KOLOM)              │ │
│ │ • Overline seksi 1 baris  │ │ • Overline seksi 1 baris             │ │
│ │   (DILARANG duplikasi H3!)│ │ • Input harga/biaya, status switch,   │ │
│ │ • Input nama, kode, unit  │ │   kanal tampil & konfigurasi          │ │
│ │ • Deskripsi / catatan     │ │                                      │ │
│ └───────────────────────────┘ └──────────────────────────────────────┘ │
├────────────────────────────────────────────────────────────────────────┤
│ [Sticky Footer Bar]                       [ Batal ] [ Simpan / Update ]│
└────────────────────────────────────────────────────────────────────────┘
```

1. **Ukuran Maksimal (Full-Size Canvas Lintas Device)**:
   - **Desktop (≥ 1024px, 1280px, 1440px, 1920px)**: `w-full max-w-[96vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] max-h-[92vh] sm:rounded-[24px] flex flex-col overflow-hidden` (Bento 2-Kolom 7:5 atau 6:6).
   - **Tablet (640px – 1023px)**: `max-w-[94vw] max-h-[90vh] rounded-[20px]`.
   - **Mobile (< 640px)**: `fixed inset-x-0 bottom-0 max-h-[96vh] w-full rounded-t-[28px] rounded-b-none flex flex-col overflow-hidden` + handle pill + scroll safe-area `pb-28`.
2. **Arsitektur Backdrop Zero-Gap Edge-to-Edge ($y=0$ Full Viewport Overlay)**:
   - **Dilarang menaruh background gelap langsung pada wrapper flex** (`class="fixed inset-0 flex ... bg-black/40"`), karena memicu kebocoran bilah header/topbar di belakang modal.
   - **Wajib gunakan elemen backdrop mandiri**: `<div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md" @click="..."></div>`.
   - **Kontainer luar wajib `z-[200]`** (atau CSS global `z-index: 99999 !important`), sub-modal `z-[210]`, alert confirm `z-[220]`, dan card dialog `relative z-10`.
   - Background transparan gelap wajib menyelimuti 100% layar dari batas paling atas ($y=0$ di bawah address bar browser) sampai dasar layar tanpa celah putih header.
3. **Anti-Whitespace Atas & Larangan Duplikasi Header**:
   - Dilarang menduplikasi subtitle/deskripsi header modal sebagai `H4 uppercase` di dalam kotak kartu form.
   - Sub-card form menggunakan overline tipografis 1 baris yang padat (`text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]`), dengan kolom kiri dan kanan rata atas (*flush top-aligned*).
4. **100% Kompatibel Light Mode & Dark Mode**:
   - Modal Shell: `bg-white/98 dark:bg-[#1C1C1E]/98 border border-black/[0.08] dark:border-white/[0.12] backdrop-blur-2xl text-[#1C1C1E] dark:text-[#F2F2F7]`
   - Header Bar: `bg-[#F2F2F7]/50 dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08]`
   - Sub-Cards: `bg-black/[0.02] dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.06] rounded-[20px] p-4 sm:p-5`
   - Inputs/Selects: `bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.1] text-[#1C1C1E] dark:text-[#F2F2F7] placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50`
   - Sticky Action Footer: `bg-white dark:bg-[#1C1C1E] border-t border-black/[0.06] dark:border-white/[0.08]`
   - Action Buttons: Primary `bg-[#007AFF] hover:bg-[#0071E3] text-white min-h-[44px]`, Secondary `bg-black/[0.05] dark:bg-white/[0.08] text-[#1C1C1E] dark:text-[#F2F2F7] min-h-[44px]`

---

## 🛡️ 3. Aturan Ketat Non-Destruktif (Preservation Rule)

Pekerjaan skill ini adalah **MURNI MERAPIKAN POSISI DAN HIERARKI**, sehingga wajib mematuhi aturan perlindungan berikut:

1. **Semua Variabel Blade Tetap Utuh:** Jangan pernah menghapus atau mengganti variabel `$orders`, `$business`, `$kpi`, `$summary`, dll.
2. **Semua Direktif Blade Tetap Terjaga:** Seluruh `@if`, `@foreach`, `@forelse`, `@can`, `@auth`, dan `@csrf` harus tetap melingkupi elemennya masing-masing secara benar.
3. **Alpine.js State & Methods Tidak Boleh Berubah:** Seluruh atribut `x-data`, `x-show`, `x-if`, `@click`, `x-model`, dan panggilan fungsi AJAX `fetch(...)` harus dipindahkan utuh bersama elemennya.
4. **Form Inputs & Route Action Tetap 100% Valid:** Seluruh atribut `name="..."`, `id="..."`, `action="{{ route(...) }}"`, `method="POST"`, `@method('PUT')`, dan input tersembunyi (*hidden inputs*) tidak boleh hilang.
5. **ID & Anchor Selector Tetap Sinkron:** ID elemen modal (`#modalCreateCustomer`, `#dropdownFilter`) harus tetap identik agar JavaScript pemanggil tidak *error*.

---

## 📝 4. Format Sajian Hasil Analisa & Reordering

Saat menyajikan rekomendasi penataan ulang kepada pengguna:

### 1. Diagram Perbandingan Struktur (Before vs After Wireframe):
```
SEBELUM (Bercampur & Tidak Teratur):
[Filter Toolbar] -> [Tabel Rinci] -> [Grafik Tren] -> [Kartu KPI Kecil di Bawah]

SESUDAH (Hierarki Logis Bento Apple HIG):
[Zona 1: Header 3-Baris + 4 Kartu KPI]
[Zona 2: Filter Toolbar -> Grafik Tren (8-col) + Komposisi (4-col)]
[Zona 3: Tabel Data Rinci dengan Drill-Down]
```

### 2. Matriks Pemindahan Posisi Elemen:
| Elemen / Komponen | Posisi Semula | Posisi Baru yang Dioptimalkan | Alasan / Nilai UX |
|---|---|---|---|
| **Kartu KPI Omzet & Laba** | Baris 4 (Tenggelam di bawah tabel) | Baris 1 (Tepat di bawah Header) | Memberikan *executive glance* instan tanpa perlu scroll. |
| **Preset Filter Tanggal** | Header Halaman paling atas | Di atas Bento Grafik & Tabel | Menyatukan kontrol (*context proximity*) dengan data target. |
| **Tombol Tambah Transaksi**| Di dalam tabel baris data | Kanan Atas Sejajar H1 Title | Standar F-Pattern: aksi utama halaman selalu di kanan atas. |

### 3. Kode Blade Lengkap yang Sudah Disusun Ulang:
Sajikan kode lengkap yang rapi, terstruktur dengan komentar pemisah zona (`<!-- ZONA 1: HEADER & KPI -->`, `<!-- ZONA 2: ANALYTICS -->`, `<!-- ZONA 3: DATA LEDGER -->`), dan bebas potongan abstrak.
