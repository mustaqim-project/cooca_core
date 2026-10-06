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

## 📐 1. Standar 3 Zona Hierarki Informasi Logis

Setiap antarmuka (Dashboard, Halaman Index, Form Input, Detail Entity, atau Modal) wajib disusun ulang mengikuti **3 Zona Vertikal Terpadu**:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ 📍 ZONA 1: ANCHOR & EXECUTIVE SNAPSHOT (Puncak Layar - 1st Fold)                       │
│ 1. Unified 3-Baris Page Header:                                                        │
│    • Overline Breadcrumb / Domain Identifier (teks abu-abu netral 11pt, tanpa pill).   │
│    • Baris Utama: H1 Title tebal + Status Badge + Primary Action Button kanan atas.    │
│    • Subtitle Padat: 1 baris penjelasan tujuan halaman (maks 10-15 kata).              │
│ 2. Bento KPI Cards Row:                                                                │
│    • 4 Kartu Bento Metrik Kunci (Angka besar tabular-nums, label semantik, delta growth)│
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 📊 ZONA 2: ANALYTICAL & TACTICAL WORKFLOWS (Tengah Layar - 2nd Fold)                   │
│ 1. Filter & Period Selector Toolbar: Berada tepat di atas grafik/tabel yang dipengaruhi│
│ 2. Asymmetric Bento Grid (Desktop: 8-Kolom vs 4-Kolom / Mobile: Full Width Stack):     │
│    • Kiri (8 Kolom): Visualisasi Tren Utama (Line / Bar Chart) atau Data Utama.        │
│    • Kanan (4 Kolom): Komposisi Donut Chart / Ringkasan Kategori / Quick Action Tiles. │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ 📑 ZONA 3: DETAILED LEDGER & OPERATIONAL FEED (Bawah Layar - 3rd Fold)                 │
│ 1. Tabel Data Interaktif Lengkap / List Card View dengan fitur Drill-Down Modal.       │
│ 2. Pagination & Sorting Toolbar.                                                       │
│ 3. Audit Log / Timeline Aktivitas Terakhir (Secondary Supporting Data).               │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔍 2. Protokol Analisa Tampilan (5 Langkah Evaluasi)

Sebelum melakukan modifikasi susunan HTML/Blade, lakukan audit visual terhadap tampilan yang ada:

1. **Evaluasi F-Pattern & Scannability:** Apakah mata pengguna langsung menangkap angka/status paling penting dalam 3 detik pertama tanpa harus menggulir ke bawah?
2. **Evaluasi Posisi Tombol & CTA:** Apakah tombol aksi utama (*Primary Action*) berada di lokasi alami (Kanan atas pada desktop, Thumb Zone bawah pada mobile) atau justru tercecer di tengah-tengah kartu?
3. **Evaluasi Kedekatan Kontrol dengan Target (*Proximity*):** Apakah filter tanggal dan dropdown cabang berada tepat di atas grafik/tabel yang dikendalikannya, atau terisolasi jauh di tempat lain?
4. **Evaluasi Kepadatan Visual (*Density & Clutter*):** Apakah ada kartu kecil yang terpisah-pisah tanpa alasan yang seharusnya dapat digabungkan menjadi satu Bento Box yang kohesif?
5. **Evaluasi Proporsi Grid Desktop vs Mobile:** Apakah tata letak desktop memanfaatkan lebar layar 12-kolom dengan seimbang (tidak ada ruang kosong raksasa yang mubazir), dan apakah tampilan otomatis runtuh (*collapse*) secara anggun di mobile tanpa overflow horizontal?

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
