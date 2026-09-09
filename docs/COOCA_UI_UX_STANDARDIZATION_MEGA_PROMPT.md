# COOCA UMKM — UI/UX STANDARDIZATION MEGA PROMPT

> **Master Specification & Prompt Standardisasi Seluruh Panel Sistem Cooca UMKM**  
> File ini berisi template prompt komprehensif untuk memodernisasi setiap halaman Blade agar memiliki visual, layout lebar penuh (*Full-Width Fluid* seukuran Dashboard), hierarki, dan komponen yang 100% seragam, konsisten, dan berkelas enterprise SaaS.

---

## Panduan Penggunaan Cepat

Untuk mengaudit dan mendesain ulang halaman manapun di sistem COOCA:
1. Salin seluruh isi teks di dalam kotak **[MASTER COPY-PASTE PROMPT]** di bawah ini.
2. Letakkan path file target di baris paling atas, contoh:
   ```text
   @[c:\laragon\www\cooca_core\resources\views\app\suppliers\index.blade.php]
   ```
3. Kirimkan ke AI assistant.

---

# [MASTER COPY-PASTE PROMPT]

````markdown
Lakukan **comprehensive UI/UX audit, redesign, standardization, dan implementation** pada file/modul panel sistem yang ditentukan.

Tujuan utama: Mengubah panel menjadi **professional-grade business system / SaaS dashboard** dengan standar visual, komponen, dan layout yang 100% KONSISTEN dan IDENTIK dengan **Dashboard Utama Cooca UMKM**.

Fokus utama:
**Usability → Information Architecture → Consistency → Efficiency → Responsiveness → Accessibility → Visual Quality**

Hasil akhir harus terasa seperti **satu unified business application**, bukan halaman terpisah yang berbeda-beda karakter.

---

# 1. ATURAN LEBAR LAYOUT & STRUKTUR HALAMAN (STRICT FULL-WIDTH)

## 1.1 Wajib Full-Width Fluid (Seukuran Dashboard)
* **DILARANG KERAS** membungkus halaman dengan container sempit seperti `max-w-4xl mx-auto`, `max-w-5xl mx-auto`, `max-w-6xl mx-auto`, atau `max-w-7xl mx-auto` yang menyebabkan tampilan mepet ke tengah dengan margin/ruang kosong yang terlalu besar di sisi kiri dan kanan!
* **WAJIB** menggunakan pembungkus lebar penuh (*fluid edge-to-edge*):
  ```html
  <div class="space-y-6 pb-12">
      <!-- Seluruh konten halaman di sini -->
  </div>
  ```
  *(Lebar halaman harus mengalir bebas memanfaatkan 100% bentang horizontal workspace layar monitor, karena padding luar telah diatur secara rapi dan konsisten oleh tag `<main class="flex-1 p-3.5 sm:p-5 md:p-6 lg:p-8 xl:p-10 ...">` di `resources/views/layouts/app.blade.php`).*

## 1.2 Standar Breadcrumb Ber-Icon Menarik
Setiap halaman WAJIB diawali oleh breadcrumb modern dengan Lucide icon di depan setiap segmen navigasi:
```html
<!-- Breadcrumb Bar -->
<nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
        <span>Dashboard</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
    <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
        <i data-lucide="[icon-kategori]" class="w-3.5 h-3.5"></i>
        <span>[Nama Kategori / Modul]</span>
    </span>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
    <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
        <span>[Judul Halaman / Sub-Modul]</span>
    </span>
</nav>
```

---

# 2. ATURAN UTAMA (NON-DESTRUCTIVE GUARANTEE)

## 2.1 Jangan Merusak Sistem Existing
DILARANG KERAS mengubah atau merusak:
* **Business Logic & Calculation**: Rumus perhitungan harga, stok, diskon, pajak, grand total, COGS, dll.
* **Database & Model**: Struktur tabel, kolom, migration, model relationship, casting.
* **Routing & Controllers**: Route name, URL pattern, HTTP method (GET/POST/PUT/DELETE), Controller action & method signatures.
* **Security & Permissions**: Middleware, otentikasi, authorization policy, permission check (`require.permission`), role checking.
* **Form Integrity**: Form action URL, token CSRF (`@csrf`), method spoofing (`@method('PUT')`), input `name="..."` attributes, query string filters.
* **Session & Feedback**: Flash messages (`session('success')`, `session('error')`), validation errors (`$errors->all()`, `@error`).

## 2.2 Audit Kode Existing Sebelum Mengubah
Pahami seluruh state Alpine.js (`x-data`), paginator query string (`$items->withQueryString()->links()`), modal, serta parameter filter sebelum melakukan refactoring tampilan.

---

# 3. KAMUS DESIGN TOKENS & KELAS TAILWIND (DUAL-THEME KONSISTEN)

Setiap file UI WAJIB menggunakan kelas utility Tailwind yang terkunci berikut:

## 3.1 Top Header Banner (Seukuran Dashboard Penuh)
```html
<div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 transition-colors">
    <div class="space-y-1.5 max-w-3xl">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                <i data-lucide="[icon-kategori]" class="w-3.5 h-3.5"></i>
                <span>[Tag / Kategori Modul]</span>
            </span>
        </div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
            [Judul Halaman / Modul]
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
            [Deskripsi singkat fungsi operasional halaman bisnis ini.]
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
        <!-- Tombol Aksi Utama / Secondary Actions -->
    </div>
</div>
```

## 3.2 4 Command Pillars KPI Cards (Grid Penuh 4 Kolom)
```html
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">[Label Metrik]</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="[icon]" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight truncate">
                [Nilai / Angka Metrik]
            </div>
        </div>
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
            <span>[Keterangan tambahan]</span>
        </div>
    </div>
</div>
```

## 3.3 Kartu Standar & Permukaan Sekunder
* **Standard Container Card**: `bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs p-5 sm:p-6`
* **Card Header**: `flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-3 mb-4`
* **Secondary / Inset Surface**: `bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/80 dark:border-slate-700/60 p-4`

## 3.4 Standar Input Form & Kontrol
* **Input Text / Number / Date / Select**:
  `w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-hidden focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 transition`
* **Field Label**:
  `block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5 uppercase tracking-wider`
* **Input Readonly / Disabled**:
  `bg-slate-100 dark:bg-slate-900 text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-800 cursor-not-allowed select-none`

## 3.5 Hierarki Tombol (Buttons)
* **Primary Emerald / Success**:
  `px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm shadow-emerald-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5`
* **Primary Blue / Submit**:
  `px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 shadow-sm shadow-blue-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5`
* **Secondary Neutral**:
  `px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition cursor-pointer flex items-center justify-center gap-1.5`
* **Outline / Filter / Print**:
  `px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition cursor-pointer shadow-2xs flex items-center justify-center gap-1.5`
* **Danger**:
  `px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-sm shadow-rose-600/20 active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-1.5`

## 3.6 Semantic Status Badges (Pill)
Format: `rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1`:
* **Success (Selesai/Lunas/Aktif)**: `bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90`
* **Warning (Pending/Draft/Menipis)**: `bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border-amber-200/90 dark:border-amber-800/90`
* **Info (Proses/Approved/B2B)**: `bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border-blue-200/90 dark:border-blue-800/90`
* **Danger (Batal/Void/Macet)**: `bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border-rose-200/90 dark:border-rose-800/90`
* **Neutral (Reguler/Tamu)**: `bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700`

## 3.7 High-Density Data Table
* **Table Wrapper**: `bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 overflow-hidden shadow-xs`
* **Thead**: `bg-slate-50/80 dark:bg-slate-950/60 text-slate-500 dark:text-slate-400 uppercase text-[10px] font-bold border-b border-slate-200 dark:border-slate-800 whitespace-nowrap`
* **Tbody**: `divide-y divide-slate-100 dark:divide-slate-800/60 font-mono text-xs`
* **Tr Hover**: `hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors`
* **Nama Entitas / Teks**: `font-sans font-bold text-slate-900 dark:text-white`
* **Angka & Finansial**: `font-mono text-right font-bold text-slate-900 dark:text-white` (Format `Rp {{ number_format($nominal, 0, ',', '.') }}`)

---

# 4. POLA STRUKTUR LAYOUT HALAMAN (4 ARKETIPE BAKU)

Setiap file tampilan HARUS diklasifikasikan dan mengikuti salah satu arketipe layout berikut:

## ARKETIPE A: INDEX / DIRECTORY VIEW (Daftar Data)
Gunakan urutan struktur hierarki berikut:
1. **Breadcrumb Bar Ber-Icon** (Paling Atas)
2. **Top Header Banner & Action Cockpit** (Lebar Penuh)
3. **4-Pillar Command KPI Cards** (Grid 4 kolom penuh)
4. **Toolbar Filter & Search Toolbar** (Container card dengan input search & filter chips)
5. **High-Density Data Table** (Kolom terstruktur, font-mono untuk angka/kode, badge semantik)
6. **Empty State Component** (Tampil otomatis bila koleksi data kosong)
7. **Pagination Bar** (Link navigasi data)

## ARKETIPE B: FORM / STUDIO VIEW (Create & Edit)
Gunakan urutan struktur hierarki berikut:
1. **Breadcrumb Bar Ber-Icon**
2. **Top Header Banner**: Judul Dokumen, status badge, tombol Batal / Kembali
3. **Numbered Step / Section Cards**:
   - Format: `1. Informasi Utama`, `2. Rincian Item / Konfigurasi`, `3. Pengaturan Tambahan`
   - Grid 2 kolom responsif (`grid grid-cols-1 md:grid-cols-2 gap-4`)
4. **Interactive Items Table (Jika form multi-item)**:
   - Kalkulasi Alpine.js reaktif tanpa reload (subtotal, diskon, pajak, grand total)
5. **Sticky Action Bar / Form Footer**:
   - Tombol Batal dan Tombol Simpan dengan loading state & konfirmasi SweetAlert2

## ARKETIPE C: DETAIL / COCKPIT VIEW (Show)
Gunakan urutan struktur hierarki berikut:
1. **Breadcrumb Bar Ber-Icon**: `Dashboard / Modul / [Nomor Dokumen]`
2. **Top Action Bar**:
   - Nomor dokumen dengan status badge semantik
   - Tombol Kembali, Tombol Cetak Nota (`window.print()`), dan Tombol Status (Approval/Complete)
3. **Lifecycle Workflow Stepper (3-Phase Banner)**:
   - Visualisasi alur: `Draft` ➔ `Disetujui` ➔ `Selesai / Terpenuhi` lengkap dengan tanggal & timestamp
4. **Informasi Dua Kolom**:
   - Kartu 1: Metadata Entitas Asal / Pelanggan / Vendor
   - Kartu 2: Ringkasan Nilai Finansial / Kompensasi / Akun
5. **Rincian Item & Ringkasan Perhitungan**:
   - Tabel barang berdensitas tinggi dengan baris Grand Total
6. **Print Layout Support**:
   - Blok tanda tangan resmi yang hanya muncul saat dicetak (`hidden print:grid grid-cols-3 gap-6 pt-12 text-center text-xs`)

## ARKETIPE D: DASHBOARD & ANALYTICS VIEW
Gunakan urutan struktur hierarki berikut:
1. **Breadcrumb Bar Ber-Icon**
2. **Toolbar Filter Tanggal**: Quick date preset chips (*Hari Ini*, *7 Hari*, *Bulan Ini*, *Tahun Ini*) & input kustom
3. **Sub-Navigation Tabs Bergaris Bawah**: Switcher tab analitik Alpine.js
4. **Widget Grid & Chart.js Theme Adaptive**: Chart canvas yang otomatis menyesuaikan warna legend dengan mode Light/Dark

---

# 5. UX FEEDBACK & INTERAKSI

* **Lucide Icons**: Wajib panggil `lucide.createIcons()` saat Alpine.js init atau mutasi DOM, gunakan icon ukuran proporsional (`w-4 h-4` atau `w-3.5 h-3.5`).
* **SweetAlert2**: Semua aksi hapus/void/approval wajib menggunakan dialog konfirmasi SweetAlert2 dengan copy bahasa Indonesia yang sopan dan jelas.
* **Double-Submit Protection**: Tombol form wajib memiliki state disabled/spinner saat diklik untuk mencegah duplikasi data transaksi bisnis.
* **Empty State**: Sediakan visual empty state (icon besar di rounded circle + teks panduan ramah + tombol aksi CTA) jika koleksi data kosong.

---

# 6. VERIFIKASI & DEFINITION OF DONE

* [ ] Layout membentang luas (*full-width fluid* seperti Dashboard), TIDAK menyempit di tengah dengan `max-w-... mx-auto`.
* [ ] Breadcrumb memiliki Lucide icon di depan tautan.
* [ ] Tampilan Light Mode jernih, profesional, dan kontras tajam.
* [ ] Tampilan Dark Mode serasi menggunakan warna netral slate (`slate-900/950`).
* [ ] Sintaks Blade aman: `php artisan view:clear` & `php artisan view:cache` lolos tanpa error.
* [ ] 100% route, permission, dan logika bisnis existing berfungsi normal.
````

---

## Ringkasan Perubahan Penting & Best Practice

| Bagian | Standardisasi Lama (Bermasalah) | Standardisasi Baru (Mega Prompt) |
|---|---|---|
| **Lebar Container** | `max-w-7xl mx-auto` (mepet di tengah dengan ruang kosong besar di desktop) | `<div class="space-y-6 pb-12">` (lebar penuh fluid seukuran Dashboard) |
| **Breadcrumb** | Teks biasa tanpa icon: `Dashboard > Modul` | Navigasi modern ber-icon: `<i data-lucide="layout-dashboard"></i>` + chevron |
| **Header Banner** | Sederhana tanpa kategori badge | Card banner lengkap dengan badge kategori, judul tebal, dan action bar |
| **Kartu KPI** | Tidak seragam, ada yang 3 kolom, ada yang 5 | Standar 4 Pilar Command KPI Cards ber-icon dengan font-mono |
| **Tabel Data** | Font default, angka rata kiri | High-density font-mono, angka uang rata kanan (`text-right font-bold`) |
| **Status Badge** | Warna acak | Pill semantik seragam (*Success, Warning, Info, Danger, Neutral*) |
| **Validasi Blade** | Kadang menyisakan sintaks usang | Wajib verifikasi via `php artisan view:cache` |
