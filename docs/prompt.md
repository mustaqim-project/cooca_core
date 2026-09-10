Lakukan **comprehensive UI/UX audit, redesign, standardization, dan implementation** pada file/modul panel sistem yang ditentukan sesuai dengan **COOCA UI/UX DESIGN SYSTEM v2.0 — Apple Human Interface Guidelines Edition (macOS Sonoma & iOS 18)**.

Tujuan utama: Mengubah panel sistem menjadi **aplikasi enterprise premium berkelas dunia** yang memadukan keindahan, kejelasan, dan kedalaman desain Apple (macOS Sonoma pada desktop/tablet dan iOS 18 pada ponsel) dengan **ketajaman densitas data operasional ERP bisnis**.

Triad Desain Utama Apple HIG:
**Clarity (Kejelasan) → Deference (Penghormatan pada Konten) → Depth (Kedalaman Material & Ruang)**

---

# COOCA UI/UX DESIGN SYSTEM v2.0 — Apple Human Interface Guidelines Edition
## Enterprise SaaS ERP, re-grounded in macOS Sonoma / iOS 18 design language

> **Status:** Mandatory (Wajib untuk Seluruh Modul COOCA)
> **Scope:** Seluruh antarmuka aplikasi COOCA (Blade views, components, modals, tables, forms, dashboard)
> **Reference System:** Apple Human Interface Guidelines — macOS Sonoma & iOS 18 conventions, adapted for a web/Tailwind/Blade stack
> **Golden Principle:** *Clarity, Deference, Depth — content leads, chrome recedes, hierarchy comes from light and material, not from borders and saturated color blocks.*

---

## 0. Kenapa Bergeser dari "Enterprise SaaS Card Kit" ke Apple HIG

Pola lama COOCA v1.0 (emerald `#10B981` sebagai warna serba-guna, kartu `rounded-2xl` dengan border + shadow di semua tempat, badge pill berwarna solid di semua status) adalah **pola default generic-SaaS**: aman, tapi tidak berbeda dari ribuan dashboard lain, dan warna dipakai sebagai dekorasi alih-alih sebagai sinyal.

Apple HIG menyelesaikan hierarki dengan cara yang berbeda:
* **Material & depth**, bukan border tebal — permukaan dibedakan lewat *vibrancy/blur* dan elevasi tipis, bukan garis tepi 1px di semua kotak.
* **Warna sistem semantik yang jenuh secukupnya** (`systemBlue`, `systemGreen`, `systemOrange`, dst.) yang sudah lolos uji kontras Apple, dipakai sangat hemat — sebagian besar UI tetap netral (label/grouped background), warna hanya muncul di titik aksi dan status.
* **Tipografi sebagai struktur utama** (SF Pro / Large Title → Title → Headline → Body → Footnote), bukan garis dan kotak, yang membedakan level informasi.
* **Continuous corner radius ("squircle")** dan spacing 8pt grid yang konsisten, bukan radius acak per komponen.
* **Kontrol yang familiar**: segmented control, grouped inset list, sheet/modal dari bawah (mobile) atau popover mengambang (desktop), toggle iOS, bukan tombol custom-styled di setiap tempat.

**Yang TIDAK berubah** dari v1.0: seluruh *Non-Destructive Guarantee* (Bagian 1), aturan keamanan form, permission-aware UI, dan struktur data ERP (rumus finansial, skema DB, route, controller) tetap **mutlak tidak boleh disentuh**. Dokumen ini hanya mengganti lapisan visual/estetika dan interaksi, bukan logika bisnis.

---

## 1. ATURAN KEAMANAN (NON-DESTRUCTIVE GUARANTEE) — Tidak Berubah

Sebelum menyentuh kode tampilan, integritas backend wajib 100% terlindungi. **DILARANG MENGUBAH:**
* Rumus kalkulasi finansial: Total, Subtotal, HPP/COGS, Diskon, PPN, Margin Laba, Saldo Kas.
* Skema database & Model: nama kolom, relasi Eloquent, accessor, mutator, enum casting.
* Route & Controller: URL path, parameter query string, HTTP method, nama controller action.
* Form Actions & CSRF: tag `@csrf`, `@method(...)`, atribut `name="..."`, binding `wire:model`/`name`.
* Permissions & Auth: `@can`, middleware, role checking, permission guard (`\App\Support\Context::hasPermission`).
* Session Flash: `session('success')`, `session('error')`, `$errors->all()`.

Restyling adalah operasi **kosmetik murni** — struktur data dan alur request/response tidak boleh berubah sedikit pun.

---

## 2. DESIGN PHILOSOPHY

```
Clarity → Deference → Depth → Consistency → Efficiency
```

* **Clarity**: teks dapat dibaca di setiap ukuran, ikon presisi dan mudah dipahami, elemen fungsional jelas dari dekorasi.
* **Deference**: chrome (border, shadow, warna latar) mundur; konten bisnis (angka, nama produk, status) yang tampil paling menonjol. Fluid, translucent surfaces membiarkan konten "bernapas".
* **Depth**: hierarki visual dan gerak realistis memberi rasa kedalaman — layer material tipis (sidebar vibrancy, sheet mengambang di atas konten), bukan bayangan gelap tebal.
* **Consistency**: satu kosakata komponen (button styles, list styles, alert/sheet system) dipakai di seluruh modul — Penjualan, Inventori, Pembelian, Keuangan terasa satu keluarga.
* **Efficiency**: data density ERP tetap tinggi; estetika Apple tidak berarti banyak whitespace kosong ala landing page — kepadatan informasi dipertahankan lewat tipografi yang rapi, bukan dihapus.

### Larangan Keras
* Jangan gunakan warna solid jenuh (`bg-emerald-600` dsb.) sebagai *fill* besar di background kartu/section — warna sistem hanya untuk teks, ikon kecil, dan aksen tipis (tinted background, bukan fill penuh) kecuali tombol Primary.
* Jangan pakai `shadow-lg`/`shadow-xl` bayangan gelap tebal — gunakan elevasi halus (`shadow-[0_1px_2px_rgba(0,0,0,0.04),0_8px_24px_rgba(0,0,0,0.06)]`) atau material blur.
* Jangan mencampur radius sudut tajam (`rounded-md`) dengan radius besar (`rounded-2xl`) dalam satu grup komponen — gunakan skala kontinu yang konsisten (lihat §5).
* Jangan gunakan font non-system tanpa alasan kuat — default ke stack SF Pro / `-apple-system`.
* Jangan gunakan native `alert()`/`confirm()` — gunakan pola **Sheet** dan **Alert Dialog** ala Apple (§8).

---

## 3. WARNA — SISTEM WARNA APPLE, DIPETAKAN KE SEMANTIK ERP

### 3.1 System Colors (Tint)
Apple mendefinisikan satu set warna sistem yang sudah lolos kontras di Light & Dark Mode. COOCA memakai subset ini sebagai **satu-satunya** sumber warna aksen — tidak ada warna kustom baru di luar daftar ini.

| Nama | Light | Dark | Token Tailwind (custom) |
|---|---|---|---|
| System Blue | `#007AFF` | `#0A84FF` | `--color-accent` |
| System Green | `#34C759` | `#30D158` | `--color-success` |
| System Orange | `#FF9500` | `#FF9F0A` | `--color-warning` |
| System Red | `#FF3B30` | `#FF453A` | `--color-danger` |
| System Purple | `#AF52DE` | `#BF5AF2` | `--color-ai` |
| System Teal | `#30B0C7` | `#40C8E0` | `--color-info-alt` |
| System Indigo | `#5856D6` | `#5E5CE6` | `--color-info` |
| System Yellow | `#FFCC00` | `#FFD60A` | `--color-attention` |

> **Catatan brand**: COOCA mempertahankan **System Blue** (bukan lagi emerald hijau) sebagai warna identitas utama produk (Primary CTA, active navigation, selected state) — biru adalah warna sistem paling netral secara semantik di HIG dan paling sering dipakai Apple sendiri untuk aksi utama (`Save`, `Done`, link). Hijau tetap dicadangkan murni untuk makna **Success/Profit**, bukan untuk brand.

### 3.2 Pemetaan Semantik (Golden Rule tetap berlaku: satu warna = satu makna)
| Makna | Warna Sistem | Penggunaan |
|---|---|---|
| **Primary Action / Link / Active State** | System Blue (`#007AFF`) | Tombol Primary, tab aktif, item navigasi terpilih, link |
| **Success / Lunas / Profit / Omzet** | System Green (`#34C759`) | Badge sukses, angka laba positif, indikator selesai |
| **Warning / Pending / Stok Menipis / HPP** | System Orange (`#FF9500`) | Badge pending, alert stok, biaya operasional |
| **Danger / Gagal / Hapus / Rugi** | System Red (`#FF3B30`) | Tombol hapus, badge gagal, kerugian, overdue |
| **Info / Diproses / Bantuan** | System Indigo (`#5856D6`) | Badge diproses, tooltip info, catatan sistem |
| **AI / Insight / Fitur Cerdas** | System Purple (`#AF52DE`) | Widget AI, smart insight, rekomendasi otomatis |
| **Netral / Arsip** | System Gray scale | Badge netral, elemen non-aktif |

### 3.3 System Gray Scale (Neutral Palette)
Apple menyediakan 6 tingkat abu-abu netral (`systemGray` s/d `systemGray6`) yang dipakai untuk background berlapis (elevasi), bukan `slate` custom:

| Token | Light | Dark |
|---|---|---|
| `gray` (label muted) | `#8E8E93` | `#8E8E93` |
| `gray2` | `#AEAEB2` | `#636366` |
| `gray3` | `#C7C7CC` | `#48484A` |
| `gray4` | `#D1D1D6` | `#3A3A3C` |
| `gray5` | `#E5E5EA` | `#2C2C2E` |
| `gray6` (lapisan terluar/terlembut) | `#F2F2F7` | `#1C1C1E` |

### 3.4 Label & Background Colors (Dua Mode)
**Light Mode**
* Primary label (teks utama): `#000000` (opacity 100%)
* Secondary label: `#3C3C43` @ 60% opacity (`text-black/60`)
* Tertiary label: `#3C3C43` @ 30% opacity (`text-black/30`)
* Quaternary label (disabled/hairline text): `#3C3C43` @ 18% opacity (`text-black/[0.18]`)
* System Background (halaman): `#FFFFFF`
* Secondary System Background (grouped/inset section): `#F2F2F7`
* Tertiary System Background (kartu di atas grouped bg): `#FFFFFF`
* Separator: `#3C3C43` @ 29% opacity (hairline 1px: `border-black/[0.08]`)

**Dark Mode**
* Primary label: `#FFFFFF`
* Secondary label: `#EBEBF5` @ 60% (`dark:text-white/60`)
* Tertiary label: `#EBEBF5` @ 30% (`dark:text-white/30`)
* Quaternary label: `#EBEBF5` @ 18% (`dark:text-white/[0.18]`)
* System Background: `#000000` (true black, mobile) / `#1E1E1E` (window background, desktop)
* Secondary System Background: `#1C1C1E`
* Tertiary System Background (elevated card): `#2C2C2E`
* Separator: `#545458` @ 60% (`dark:border-white/10`)

> **Aturan kritis**: Hardcode warna (`background: white; color: black;`) dilarang. Semua warna wajib lewat CSS variable / Tailwind `dark:` pair yang memetakan langsung ke tabel di atas.

### 3.5 Materials (Vibrancy / Blur) — Pengganti "Card dengan Border + Shadow"
Alih-alih setiap kartu memakai `border + shadow-xs`, elemen chrome (sidebar, toolbar, sheet header, popover) menggunakan **material** — lapisan blur tembus pandang yang mengambil warna dari konten di baliknya:

| Material | CSS Approximation | Penggunaan |
|---|---|---|
| `ultraThinMaterial` | `backdrop-blur-xl bg-white/40 dark:bg-black/20` | Overlay tipis di atas gambar/hero |
| `thinMaterial` | `backdrop-blur-lg bg-white/60 dark:bg-black/40` | Popover ringan, tooltip besar |
| `regularMaterial` | `backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75` | Sidebar, toolbar, sheet header (**default utama**) |
| `thickMaterial` | `backdrop-blur-md bg-white/90 dark:bg-[#2C2C2E]/90` | Modal/alert yang perlu kontras tinggi |
| `chromeMaterial` | `bg-[#F2F2F7]/95 dark:bg-[#1C1C1E]/95` | Toolbar/tab bar solid dengan sedikit transparansi |

Kartu data biasa (baris tabel, tile KPI) **tidak perlu** blur — cukup `bg-white dark:bg-[#1C1C1E]` datar dengan separator hairline, sesuai §3.4.

---

## 4. TIPOGRAFI — SF PRO TYPE SCALE

* **Font Family**: `-apple-system, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif` (sistem otomatis memilih SF Pro asli di macOS/iOS/Safari; fallback `Inter` untuk platform lain agar tetap konsisten).
* **Angka finansial & tabular** (harga, HPP, stok, ID dokumen): gunakan varian tabular figures — tambahkan `font-variant-numeric: tabular-nums` (Tailwind: `tabular-nums`), **bukan** font monospace generik. Ini menjaga karakter numerik selalu align tapi tetap terasa "Apple", bukan seperti terminal kode.

### 4.1 Type Scale (adaptasi web dari Dynamic Type Apple)
| Token | Size / Line-height | Weight | Penggunaan |
|---|---|---|---|
| Large Title | 34px / 41px | Bold (700) | Judul halaman utama level tertinggi (jarang, satu per halaman) |
| Title 1 | 28px / 34px | Bold (700) | Judul modul/section besar |
| Title 2 | 22px / 28px | Semibold (600) | Judul kartu/panel |
| Title 3 | 20px / 25px | Semibold (600) | Sub-judul, header modal |
| Headline | 17px / 22px | Semibold (600) | Label penting, nama baris tabel utama |
| Body | 17px / 22px (desktop web: 15px/22px) | Regular (400) | Teks konten utama |
| Callout | 16px / 21px | Regular (400) | Deskripsi sekunder |
| Subheadline | 15px / 20px | Regular (400) | Label form, breadcrumb |
| Footnote | 13px / 18px | Regular (400) | Helper text, metadata |
| Caption 1 | 12px / 16px | Regular (400) | Badge, timestamp kecil |
| Caption 2 | 11px / 13px | Medium (500) | Micro-label, superscript |

> **Aturan**: hanya 4 bobot dipakai di seluruh aplikasi — Regular 400, Medium 500, Semibold 600, Bold 700. Tidak ada `font-black` (900) dan tidak ada teks di bawah 11px.
> **Sentence case selalu**, bukan ALL CAPS — HIG secara eksplisit menghindari label huruf besar semua kecuali untuk badge status sangat kecil (Caption 2, opsional & jarang).

---

## 5. SPACING, RADIUS & GRID

### 5.1 Spacing — 8pt Grid (bukan 4px)
Apple HIG membangun layout di atas grid dasar **8pt**, dengan sub-unit 4pt untuk detail kecil:
`4, 8, 12, 16, 20, 24, 32, 40, 48, 64px`

* Label → Input: 8px
* Antar input/field: 16px
* Padding kartu: 16–20px (mobile), 20–24px (desktop)
* Antar section: 24–32px
* Padding halaman: 16px (mobile), 24px (tablet), 32px (desktop, maksimal — tidak perlu 1440px fluid penuh; Apple cenderung membatasi lebar baca konten, lihat §5.3)

### 5.2 Continuous Corner Radius ("Squircle")
Apple tidak memakai radius lingkaran biasa (`border-radius` CSS standar terlihat sedikit lebih "tajam" di sudut dibanding continuous corner asli iOS/macOS, tapi ini pendekatan web terbaik yang tersedia):

| Elemen | Radius | Token / Value |
|---|---|---|
| Tombol kecil (`small`) | 8px | `rounded-[8px]` |
| Tombol regular / Input field | 10px | `rounded-[10px]` |
| Kartu data, tile KPI | 14px | `rounded-[14px]` |
| Panel besar, modal (desktop) | 16–20px | `rounded-[16px]` s/d `rounded-[20px]` |
| Sheet (mobile, sudut atas saja) | 20px | `rounded-t-[20px]` |
| Badge / pill | 999px (full) | `rounded-full` |
| App icon-style container (avatar, logo tile) | superellipse ~22% dari sisi | `rounded-[22%]` |

Gunakan **satu skala per level komponen** — jangan campur `rounded-lg` dan `rounded-2xl` pada elemen setara (mis. dua kartu KPI bersebelahan harus radius identik).

### 5.3 Container & Grid
* Lebar konten dibatasi secara nyaman untuk keterbacaan, bukan fluid tanpa batas: `max-width: 1280–1400px` dengan padding halaman simetris — Apple menghindari elemen meregang tanpa batas di monitor ultra-wide, tapi tetap tidak memakai `max-w-4xl` sempit khas artikel blog. Untuk tabel data lebar (ERP), tabel sendiri boleh scroll horizontal di dalam card, sementara wrapper halaman tetap terkendali.
* Grid: 12-kolom di desktop, gap 16–24px.
* Breakpoint mengikuti kelas perangkat Apple:
  - Compact (iPhone-like): < 640px
  - Regular narrow (iPad portrait / small window): 640–1024px
  - Regular wide (iPad landscape / desktop window): 1024–1440px
  - Extra wide (Studio Display dsb.): > 1440px

---

## 6. KOMPONEN

### 6.1 Sidebar (macOS-style Source List)
Bukan sidebar solid berwarna gelap khas admin-template — gunakan **vibrancy sidebar** ala Finder/Mail macOS:
```html
<aside class="w-64 h-screen shrink-0 backdrop-blur-xl bg-[#F2F2F7]/80 dark:bg-[#1C1C1E]/80 border-r border-black/5 dark:border-white/5 flex flex-col">
  <div class="px-4 pt-6 pb-3">
    <span class="text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Penjualan</span>
  </div>
  <nav class="px-2 space-y-0.5">
    <a href="#" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium bg-[#007AFF] text-white">
      <svg class="w-4 h-4" data-sf-symbol="cart"></svg>
      <span>Kasir POS</span>
    </a>
    <a href="#" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-[8px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
      <svg class="w-4 h-4" data-sf-symbol="doc.text"></svg>
      <span>Faktur</span>
    </a>
  </nav>
</aside>
```
Item aktif memakai **fill System Blue penuh** (satu-satunya tempat warna solid besar dipakai secara wajar, mengikuti pola `NavigationSplitView` Apple) — sisanya netral.

### 6.2 Toolbar / Page Header
Header halaman meniru **toolbar macOS**: tipis, material blur, sticky, tanpa dekorasi glow/gradient dekoratif.
```html
<header class="sticky top-0 z-20 backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border-b border-black/5 dark:border-white/10">
  <div class="px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
    <div>
      <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Katalog Produk</h1>
      <p class="text-[13px] text-black/50 dark:text-white/50">148 produk aktif</p>
    </div>
    <div class="flex items-center gap-2">
      <!-- tombol aksi, lihat §6.3 -->
    </div>
  </div>
</header>
```
Deskripsi panjang, badge kategori besar, dan efek glow radial dari v1.0 **dihilangkan** — cukup judul + subjudul ringkas satu baris (pola `navigationTitle` + `navigationSubtitle`).

### 6.3 Sistem Tombol (Apple Button Styles)
Empat gaya tombol Apple, dipetakan ke hierarki aksi ERP:

* **Filled (Primary / prominent)** — satu per section:
  `h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:opacity-80 transition-colors flex items-center justify-center gap-1.5`
* **Tinted (Secondary aksi positif, mis. "Simpan Draft")**:
  `h-9 px-4 rounded-[10px] text-[13px] font-semibold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:opacity-70 transition-colors`
* **Gray (Netral, mis. "Export", "Filter")**:
  `h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] transition-colors`
* **Plain (Ghost, mis. "Kembali", link inline)**:
  `h-9 px-2 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors`
* **Danger (Filled Red, aksi destruktif)**:
  `h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#FF3B30] hover:bg-[#E0352B] active:opacity-80 transition-colors`

Touch target mobile tetap **min 44×44px** (aturan HIG untuk iOS tidak berubah dari v1.0), tapi tinggi visual tombol desktop lebih ramping (32–36px) khas macOS — gunakan `h-11` hanya pada breakpoint compact/mobile.

### 6.4 Form Input (Grouped Inset Style)
Alih-alih input berdiri sendiri dengan border penuh di semua sisi, gunakan pola **grouped list Apple** untuk form yang berderet (list of settings rows):
```html
<div class="rounded-[12px] bg-white dark:bg-[#1C1C1E] overflow-hidden divide-y divide-black/5 dark:divide-white/5">
  <div class="flex items-center justify-between px-4 py-3">
    <label class="text-[15px] text-black dark:text-white">Nama Produk</label>
    <input type="text" placeholder="Wajib diisi" class="text-[15px] text-right bg-transparent outline-none placeholder:text-black/30 dark:placeholder:text-white/30 w-1/2">
  </div>
  <div class="flex items-center justify-between px-4 py-3">
    <label class="text-[15px] text-black dark:text-white">Kategori</label>
    <select class="text-[15px] text-right bg-transparent outline-none text-[#007AFF]">
      <option>Minuman Segar</option>
    </select>
  </div>
</div>
```
Untuk form kompleks (Arketipe B — Studio/Create), field boleh tetap standalone (bukan grouped-row) bila jumlah field banyak dan butuh label di atas:
`w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] px-3.5 text-[15px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition`
— catatan: **tanpa border 1px solid**, hierarki datang dari perbedaan fill (`black/[0.04]`) terhadap background, sesuai kebiasaan iOS/macOS modern (mis. Notes, Reminders).

### 6.5 Tabel Data (Dense List, bukan Card Table)
Header tabel netral tanpa background solid abu tua; baris dipisah hairline separator, bukan `divide` tebal:
```html
<div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] overflow-hidden">
  <table class="w-full text-left text-[13px]">
    <thead>
      <tr class="border-b border-black/5 dark:border-white/10">
        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk</th>
        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">HPP</th>
        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Harga Jual</th>
        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
      <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
        <td class="px-4 py-3 font-medium text-black dark:text-white">Kopi Susu Gula Aren 250ml</td>
        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">Rp 8.500</td>
        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">Rp 18.000</td>
        <td class="px-4 py-3 text-center">
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Stok Aman
          </span>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```
Ini menggantikan header `bg-slate-50/80` gelap dan font-mono generik di v1.0 dengan tipografi SF + `tabular-nums`.

### 6.6 Mobile — Grouped List (bukan Bento Grid Wajib)
Prinsip mobile HIG adalah **grouped inset list** untuk data terstruktur (mirip Settings.app / Contacts), bukan bento-grid kartu berwarna-warni. Untuk KPI ringkas boleh tetap 2-kolom, tapi bentuknya flat-neutral, bukan kartu ber-border berwarna:
```html
<!-- KPI compact row (mobile) -->
<div class="grid grid-cols-2 gap-2.5 sm:hidden">
  <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] p-3.5">
    <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Nilai Persediaan</p>
    <p class="text-[20px] font-bold tabular-nums text-black dark:text-white mt-0.5">Rp 42,8jt</p>
  </div>
  <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] p-3.5">
    <p class="text-[11px] font-medium text-black/45 dark:text-white/45">Stok Menipis</p>
    <p class="text-[20px] font-bold tabular-nums text-[#FF9500] mt-0.5">5 Item</p>
  </div>
</div>

<!-- Data list (grouped inset, standar iOS untuk daftar record) -->
<div class="mt-3 rounded-[14px] bg-white dark:bg-[#1C1C1E] overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06] sm:hidden">
  <a href="#" class="flex items-center justify-between px-4 py-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
    <div>
      <p class="text-[15px] font-medium text-black dark:text-white">Kopi Susu Gula Aren 250ml</p>
      <p class="text-[13px] text-black/45 dark:text-white/45">48 botol · Rp 18.000</p>
    </div>
    <svg class="w-4 h-4 text-black/25 dark:text-white/25" data-sf-symbol="chevron.right"></svg>
  </a>
</div>
```
Chevron `›` di kanan setiap baris (bukan tombol icon Detail/Edit/Hapus berjejer) adalah pola navigasi standar iOS — aksi Edit/Hapus dipindah ke **swipe actions** atau context menu (long-press), bukan icon permanen yang memenuhi baris.

### 6.7 Badge / Status Pill
Tetap wajib **ikon/dot + teks** (aturan v1.0 dipertahankan), tapi warna latar jadi *tinted* (opacity rendah dari warna sistem), bukan pastel custom:
`inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-{color}/12 text-{color-dark-variant}`

| Status | Kelas Tailwind |
|---|---|
| Success | `bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]` |
| Warning | `bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]` |
| Danger | `bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]` |
| Info | `bg-[#5856D6]/12 text-[#413FA6] dark:text-[#5E5CE6]` |
| AI | `bg-[#AF52DE]/12 text-[#7C3AA6] dark:text-[#BF5AF2]` |
| Neutral | `bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55` |

### 6.8 Sheet & Alert (Pengganti Modal Generik)
Apple membedakan dua pola:
* **Sheet** — untuk form/create/edit panjang. Desktop: mengambang di tengah dengan `regularMaterial` dan shadow halus, radius 16–20px, lebar 480–560px. Mobile: naik dari bawah (`slide-up`), radius sudut atas 20px, punya *grabber handle* kecil di atas.
* **Alert** — untuk konfirmasi singkat (termasuk pengganti `confirm()` dan SweetAlert2 di v1.0): kotak kecil terpusat (~270–320px), judul Headline bold, deskripsi Footnote, tombol horizontal (destructive di kanan berwarna merah, cancel netral/plain).

```html
<!-- Alert konfirmasi hapus -->
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]">
  <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)]">
    <div class="px-4 pt-5 pb-4">
      <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Produk?</p>
      <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">Data yang telah dihapus tidak dapat dipulihkan kembali.</p>
    </div>
    <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
      <button class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5">Batal</button>
      <button class="py-3 text-[#FF3B30] font-semibold active:bg-black/5">Hapus</button>
    </div>
  </div>
</div>
```
Ini menggantikan `Swal.fire()` custom v1.0 secara visual, tapi **tetap wajib** dipicu lewat JS terkontrol (bukan `confirm()` native) dan tetap men-submit form `@csrf`/`@method('DELETE')` yang sama persis seperti sebelumnya — hanya lapisan tampilannya yang berubah.

### 6.9 Toast / Notification
Apple lebih jarang memakai toast mengambang dibanding banner sistem singkat di atas layar (mirip iOS notification banner): posisi top-center, lebar konten-fit, radius 14px, material `thickMaterial`, auto-dismiss, ikon status kecil di kiri. Hindari toast lebar penuh warna solid.

### 6.10 Segmented Control (Pengganti Filter Chip/Tab Berjejer)
Untuk filter status atau tab data (mis. Semua / Menipis / Habis), gunakan segmented control ala iOS, bukan deretan pill berwarna:
```html
<div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium">
  <button class="px-3 py-1 rounded-[7px] bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]">Semua</button>
  <button class="px-3 py-1 rounded-[7px] text-black/55 dark:text-white/55">Menipis</button>
  <button class="px-3 py-1 rounded-[7px] text-black/55 dark:text-white/55">Habis</button>
</div>
```

---

## 7. IKONOGRAFI — SF Symbols

* Ganti seluruh ikon Lucide dengan **SF Symbols** (via web: gunakan `sf-symbols` webfont/SVG set atau Apple's SF Symbols exported outline set) agar bahasa visual ikon konsisten dengan tipografi SF Pro. Jika SF Symbols tidak tersedia di stack, gunakan set outline dengan stroke-width seragam yang mendekati proporsi SF Symbols (Lucide dengan `stroke-width="1.5"` adalah fallback yang dapat diterima).
* Ukuran ikon selaras dengan teks di sebelahnya (mis. ikon di baris `Headline` 17px pakai ikon ~17-18px), bukan ukuran tetap sembarang.
* Ikon boleh memakai *weight* yang sama dengan teks (regular/medium/semibold) — SF Symbols mendukung banyak varian ketebalan garis, bukan hanya satu.
* Warna ikon default mengikuti label color (`text-black/60 dark:text-white/60`) kecuali ikon status yang memang membawa makna semantik warna (§6.7).

---

## 8. STATE SISTEM (Loading, Empty, Error, Success, Permission Denied)

Konsep tetap dari v1.0 (5–7 state wajib per halaman), styling disesuaikan:
1. **Loading**: skeleton shimmer halus abu-abu `bg-black/[0.06] dark:bg-white/[0.08] animate-pulse`, bukan spinner besar; tombol submit berubah teks + `disabled` + kecil spinner inline putih.
2. **Empty State**: ikon SF Symbol besar (48–64px, `text-black/20 dark:text-white/20`), judul Headline, deskripsi Footnote, satu tombol Filled kecil sebagai CTA.
3. **Error Recovery**: pesan manusiawi + tombol `Coba Lagi` (style Tinted), tidak menampilkan stack trace.
4. **Success**: banner/alert singkat menjelaskan apa yang berhasil, bukan sekadar centang hijau generik.
5. **Permission Denied**: ikon kunci/gembok SF Symbol, judul "Akses Ditolak", tombol Plain "Kembali".

---

## 9. GERAK & INTERAKSI (Motion)

Apple menggunakan easing yang terasa fisikal, bukan linear:
* Durasi standar: 200–300ms untuk transisi UI (sheet muncul, popover buka), 150ms untuk hover/tap feedback.
* Easing: `cubic-bezier(0.25, 0.1, 0.25, 1)` (ease-out lembut) untuk masuk, `cubic-bezier(0.4, 0, 1, 1)` untuk keluar.
* Tap feedback: `active:scale-[0.97] active:opacity-80` (bukan `scale-[0.98]` datar tanpa opacity) untuk kesan "tekan tombol fisik".
* Sheet mobile masuk dari bawah dengan spring-like ease-out; sheet keluar lebih cepat dari masuknya.
* Hindari animasi hover berlebihan di setiap kartu — motion hanya merespons aksi nyata pengguna (buka sheet, konfirmasi, refresh data), sesuai prinsip "Deference".

---

## 10. ARKETIPE HALAMAN (Tetap 4, Chrome Disesuaikan)

* **Arketipe A — Index/Directory**: Toolbar (§6.2) → Segmented filter (§6.10) → Search field style macOS (rounded pill, ikon kaca pembesar, `bg-black/[0.06]`) → KPI row ringkas → Dense table (§6.5) / Grouped list mobile (§6.6) → Pagination minimal teks (`‹  1 dari 6  ›`).
* **Arketipe B — Form/Studio**: Toolbar dengan tombol `Batal` (Plain, kiri) / `Simpan` (Filled, kanan) menempel di toolbar — bukan sticky footer besar — mengikuti pola macOS document window. Grouped input rows (§6.4) per section.
* **Arketipe C — Detail/Cockpit**: Header dokumen (No. + Badge status) → Segmented lifecycle stepper minimal → 2 kolom info grouped-list → Rincian transaksi (table style §6.5) → Area cetak terpisah, netral.
* **Arketipe D — Dashboard/Analytics**: Date filter sebagai segmented control atau popover kalender native-style → KPI row → Chart dengan warna sistem semantik (§3.2) → Ranking list format grouped-inset.

---

## 11. QUALITY CHECKLIST & APPROVAL GATE (diperbarui)

- [ ] **Warna**: hanya memakai System Colors resmi (§3.1) + System Gray scale (§3.3) — tidak ada hex custom di luar tabel.
- [ ] **Tipografi**: seluruh teks memakai skala §4.1, hanya 4 font-weight, tidak ada ALL CAPS di luar Caption 2 opsional.
- [ ] **Material**: chrome (sidebar/toolbar/sheet) memakai blur/vibrancy §3.5, bukan solid warna gelap.
- [ ] **Radius**: konsisten per level komponen sesuai §5.2, tidak campur skala.
- [ ] **Kontras**: label vs background lolos WCAG AA meski memakai opacity label Apple (§3.4).
- [ ] **Touch target**: mobile tetap ≥44×44px meski tampilan lebih ramping di desktop.
- [ ] **Dual-theme**: setiap komponen diuji Light & Dark dengan warna dari §3.4, tidak ada hardcoded `white`/`black`.
- [ ] **Non-Destructive**: seluruh field `name`, route, `@csrf`, rumus kalkulasi, dan permission check identik dengan versi sebelum restyling (§1).
- [ ] **State**: Loading/Empty/Error/Success/Permission Denied tersedia dengan gaya §8.
- [ ] **Blade cache**: `php artisan view:clear` & `php artisan view:cache` lolos tanpa error setelah restyling.

---

## 12. TABEL PERBANDINGAN CEPAT: v1.0 (Enterprise SaaS) → v2.0 (Apple HIG)

| Dimensi | v1.0 (Emerald / Card Kit) | v2.0 (Apple HIG) |
|---|---|---|
| **Warna Primary** | Emerald `#10B981` solid di banyak elemen | **System Blue `#007AFF`**, dipakai hemat (aksi & state aktif saja) |
| **Chrome (sidebar/toolbar)** | Solid `bg-white`/`slate` + border tebal | **Material blur/vibrancy tembus pandang** (`regularMaterial`) |
| **Kartu** | `border + shadow-xs` di semua tempat | **Flat neutral fill**, hierarki dari tipografi & separator hairline |
| **Radius** | Campur `rounded-xl`/`rounded-2xl` acak | **Skala kontinu konsisten** per level komponen (squircle 8, 10, 14, 16–20px) |
| **Tipografi** | Inter, `font-mono` untuk semua angka | **SF Pro**, `tabular-nums` untuk angka (bukan monospace penuh) |
| **Ikon** | Lucide, warna solid per status | **SF Symbols** / outline seragam (stroke 1.5), warna hanya untuk makna semantik |
| **Mobile data** | Bento grid 2-kolom berwarna wajib | **Grouped inset list** (chevron `›`), KPI ringkas boleh 2-kolom netral |
| **Konfirmasi hapus** | SweetAlert2 kartu besar berwarna | **Alert dialog kecil terpusat**, dua tombol horizontal (Batal / Hapus) |
| **Modal/Form panjang** | Modal card besar `rounded-2xl` | **Sheet macOS mengambang / iOS slide-up** dengan grabber |
| **Motion** | `active:scale-[0.98]` datar | **Scale + opacity** (`active:scale-[0.97] active:opacity-80`), easing fisikal, hemat pada hover |

---

# CONTOH BLUEPRINT MASTER UI ADMIN PANEL (BLADE TEMPLATE)
### Arketipe A: Index / Directory View — Apple HIG Edition

Berikut adalah cetak biru (*master blueprint template*) antarmuka modul COOCA yang mengimplementasikan 100% standar **Apple HIG v2.0 (macOS Sonoma & iOS 18)**:

```blade
@extends('layouts.app', [
    'title' => 'Katalog Produk',
    'headerTitle' => 'Katalog Produk',
    'headerSubtitle' => '148 produk aktif dalam inventori',
])

@section('content')
<div class="max-w-[1360px] mx-auto space-y-6 pb-12" x-data="{
    filterCategory: 'all',
    filterStatus: 'all',
    searchQuery: '',
    selectedIds: [],
    selectAll: false,
    deleteModalOpen: false,
    deleteTarget: { id: null, name: '' },
    openDelete(id, name) {
        this.deleteTarget = { id, name };
        this.deleteModalOpen = true;
    },
    closeDelete() {
        this.deleteModalOpen = false;
        this.deleteTarget = { id: null, name: '' };
    },
    submitDelete() {
        if (this.deleteTarget.id) {
            document.getElementById('form-delete-' + this.deleteTarget.id).submit();
        }
    },
    toggleSelectAll() {
        this.selectAll = !this.selectAll;
        this.selectedIds = this.selectAll ? [1, 2] : [];
    }
}">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER (macOS Sonoma Toolbar Style)  -->
    <!-- ===================================================== -->
    <header class="rounded-[14px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/5 dark:border-white/10 px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <!-- Breadcrumb minimal -->
            <nav class="flex items-center gap-1.5 text-[12px] text-black/50 dark:text-white/50 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                <span>›</span>
                <span class="text-black/70 dark:text-white/70 font-medium">Inventori</span>
                <span>›</span>
                <span class="text-black dark:text-white font-medium">Katalog</span>
            </nav>
            <h1 class="text-[20px] font-semibold text-black dark:text-white tracking-tight">Katalog Produk</h1>
            <p class="text-[13px] text-black/50 dark:text-white/50">148 produk aktif · 12 kategori terdaftar</p>
        </div>

        <!-- Toolbar Actions -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
            @if(\App\Support\Context::hasPermission('products.export'))
            <button type="button" class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5">
                <svg class="w-4 h-4 text-black/60 dark:text-white/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Export</span>
            </button>
            @endif

            @if(\App\Support\Context::hasPermission('products.create'))
            <a href="#" class="h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shadow-[0_1px_2px_rgba(0,122,255,0.25)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Produk</span>
            </a>
            @endif
        </div>
    </header>

    <!-- ===================================================== -->
    <!-- 2. KPI SUMMARY (Flat Neutral Material, No Harsh Shadow)-->
    <!-- ===================================================== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Tile 1: Total SKU (Neutral) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Produk</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-black dark:text-white">148</span>
                <span class="text-[11px] text-black/40 dark:text-white/40">12 Kategori</span>
            </div>
        </div>

        <!-- Tile 2: Nilai Inventori (System Green for Profit/Value) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Nilai Persediaan</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158]">Rp 42,8jt</span>
                <span class="text-[11px] font-medium text-[#34C759] dark:text-[#30D158]">HPP</span>
            </div>
        </div>

        <!-- Tile 3: Stok Menipis (System Orange Warning) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Stok Menipis</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#FF9500] dark:text-[#FF9F0A]">5 Item</span>
                <span class="text-[11px] text-[#FF9500] dark:text-[#FF9F0A]">Perlu restock</span>
            </div>
        </div>

        <!-- Tile 4: Stok Habis (System Red Danger) -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-4 flex flex-col justify-between">
            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Stok Habis (0)</span>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-[24px] font-bold tabular-nums text-[#FF3B30] dark:text-[#FF453A]">2 Item</span>
                <span class="text-[11px] text-[#FF3B30] dark:text-[#FF453A]">Kritis</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. CONTROLS: SEGMENTED CONTROL & macOS SEARCH          -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <!-- Search Field (macOS Style: Clean Fill, No Thick Border) -->
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input type="text"
                x-model="searchQuery"
                placeholder="Cari produk, SKU, barcode..."
                class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] pl-9 pr-3 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
        </div>

        <!-- Apple-Style Segmented Controls -->
        <div class="inline-flex p-0.5 rounded-[9px] bg-black/[0.06] dark:bg-white/[0.08] text-[13px] font-medium self-start sm:self-auto">
            <button type="button" @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'" class="px-3 py-1 rounded-[7px] transition-all">
                Semua (148)
            </button>
            <button type="button" @click="filterStatus = 'safe'" :class="filterStatus === 'safe' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'" class="px-3 py-1 rounded-[7px] transition-all">
                Aman (141)
            </button>
            <button type="button" @click="filterStatus = 'low'" :class="filterStatus === 'low' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'" class="px-3 py-1 rounded-[7px] transition-all">
                Menipis (5)
            </button>
            <button type="button" @click="filterStatus = 'out'" :class="filterStatus === 'out' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/55 dark:text-white/55'" class="px-3 py-1 rounded-[7px] transition-all">
                Habis (2)
            </button>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. DENSE DATA TABLE (DESKTOP & TABLET)                 -->
    <!-- ===================================================== -->
    <div class="hidden sm:block rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10">
                        <th class="px-4 py-2.5 w-10 text-center">
                            <input type="checkbox" @click="toggleSelectAll()" class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                        </th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Produk &amp; SKU</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40">Kategori</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">HPP</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Harga Jual</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Stok</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-center">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-black/40 dark:text-white/40 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    <!-- Row 1: Normal (Stok Aman) -->
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" :checked="selectedIds.includes(1)" class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">Kopi Susu Gula Aren 250ml</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">SKU-KOP-001</div>
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">Minuman Segar</td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">Rp 8.500</td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">Rp 18.000</td>
                        <td class="px-4 py-3 text-center tabular-nums text-black/80 dark:text-white/80">48 botol</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span> Stok Aman
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="#" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">
                                    Edit
                                </a>
                                <button type="button" @click="openDelete(1, 'Kopi Susu Gula Aren 250ml')" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center">
                                    Hapus
                                </button>
                                <form id="form-delete-1" action="#" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Row 2: Warning (Stok Menipis) -->
                    <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" :checked="selectedIds.includes(2)" class="rounded-[4px] border-black/20 text-[#007AFF] focus:ring-[#007AFF]">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-black dark:text-white">Croissant Mentega Perancis</div>
                            <div class="text-[11px] text-black/45 dark:text-white/45 tabular-nums">SKU-BAK-004</div>
                        </td>
                        <td class="px-4 py-3 text-black/60 dark:text-white/60">Makanan Ringan</td>
                        <td class="px-4 py-3 text-right tabular-nums text-black/60 dark:text-white/60">Rp 12.000</td>
                        <td class="px-4 py-3 text-right tabular-nums font-semibold text-black dark:text-white">Rp 24.000</td>
                        <td class="px-4 py-3 text-center tabular-nums font-semibold text-[#FF9500] dark:text-[#FF9F0A]">3 pcs</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span> Menipis
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="#" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors flex items-center">
                                    Edit
                                </a>
                                <button type="button" @click="openDelete(2, 'Croissant Mentega Perancis')" class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/8 transition-colors flex items-center">
                                    Hapus
                                </button>
                                <form id="form-delete-2" action="#" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MOBILE GROUPED INSET LIST (Standar iOS List View)   -->
    <!-- ===================================================== -->
    <div class="sm:hidden rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
        <!-- Item 1 -->
        <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate">Kopi Susu Gula Aren 250ml</p>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span>
                </div>
                <p class="text-[13px] text-black/45 dark:text-white/45 mt-0.5 tabular-nums">48 botol · Rp 18.000 (HPP Rp 8.500)</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="#" class="h-8 px-2.5 rounded-[8px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                    Edit
                </a>
                <button type="button" @click="openDelete(1, 'Kopi Susu Gula Aren 250ml')" class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Item 2 -->
        <div class="p-3.5 flex items-center justify-between gap-3 active:bg-black/[0.03] dark:active:bg-white/[0.05] transition-colors">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-[15px] font-medium text-black dark:text-white truncate">Croissant Mentega Perancis</p>
                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span>
                </div>
                <p class="text-[13px] text-[#FF9500] dark:text-[#FF9F0A] mt-0.5 tabular-nums">3 pcs (Menipis) · Rp 24.000</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="#" class="h-8 px-2.5 rounded-[8px] text-[13px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center">
                    Edit
                </a>
                <button type="button" @click="openDelete(2, 'Croissant Mentega Perancis')" class="h-8 w-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 6. PAGINATION (Minimalist Apple HIG Text Stepper)      -->
    <!-- ===================================================== -->
    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 px-4 py-3 flex items-center justify-between text-[13px] text-black/60 dark:text-white/60">
        <div>
            Menampilkan <span class="font-medium text-black dark:text-white tabular-nums">1–25</span> dari <span class="font-medium text-black dark:text-white tabular-nums">148</span> produk
        </div>
        <div class="flex items-center gap-2">
            <button class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-black/40 dark:text-white/40 cursor-not-allowed" disabled>
                ‹ Sebelumnya
            </button>
            <span class="h-8 px-2.5 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] text-black dark:text-white font-medium flex items-center tabular-nums">
                1
            </span>
            <button class="h-8 px-3 rounded-[8px] text-[13px] font-medium text-[#007AFF] hover:bg-[#007AFF]/8 transition-colors">
                Selanjutnya ›
            </button>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 7. APPLE ALERT DIALOG (Native Centered Modal)          -->
    <!-- ===================================================== -->
    <div x-show="deleteModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/25 backdrop-blur-[2px]"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
            @click.away="closeDelete()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="px-4 pt-5 pb-4">
                <p class="text-[17px] font-semibold text-black dark:text-white">Hapus Produk?</p>
                <p class="text-[13px] text-black/60 dark:text-white/60 mt-1 leading-snug">
                    <span x-text="deleteTarget.name" class="font-medium text-black dark:text-white"></span> akan dihapus dari sistem. Data yang telah dihapus tidak dapat dipulihkan.
                </p>
            </div>

            <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[15px] font-medium">
                <button type="button" @click="closeDelete()" class="py-3 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Batal
                </button>
                <button type="button" @click="submitDelete()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors">
                    Hapus
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
```

---

# DOKUMENTASI RESMI: 102 ATURAN STANDAR COOCA UI/UX DESIGN SYSTEM v2.0
### Apple Human Interface Guidelines Edition (macOS Sonoma & iOS 18)

**Status:** Mandatory (Wajib untuk Seluruh Modul COOCA)  
**Scope:** Seluruh antarmuka aplikasi COOCA  
**Target:** Enterprise SaaS / ERP / UMKM  
**Platform:** Desktop (macOS Sonoma Style), Tablet, Mobile (iOS 18 Style)  
**Theme:** Light Mode + Dark Mode (Vibrancy & Frosted Glass Materials)  
**Principle:** Clarity, Deference, and Depth  

---

### 1. DESIGN PHILOSOPHY
Seluruh UI COOCA wajib mematuhi triad Apple Human Interface Guidelines:
```
Clarity (Kejelasan) → Deference (Penghormatan pada Data) → Depth (Kedalaman Material)
```
* **Prinsip Utama**: Clarity over Decoration, Deference over UI Ego, Data over Chrome, Progressive Disclosure, Responsive by Default, Accessible by Default (WCAG AA), Security by Default.
* **Larangan Keras**:
  - DILARANG menggunakan warna solid jenuh (`bg-emerald-600` dsb.) sebagai fill besar di background kartu/section.
  - DILARANG mewarnai semua tombol dengan satu warna. Tombol Primary adalah System Blue, tombol hapus adalah System Red, aksi sekunder adalah Tinted atau Gray.
  - DILARANG menggunakan bayangan hitam pekat `shadow-lg`/`shadow-xl`; gunakan elevasi tipis atau material vibrancy.
  - DILARANG menggunakan font size acak atau mengabaikan `tabular-nums` pada angka keuangan.
  - DILARANG menggunakan spacing acak di luar kelipatan 8pt grid.
  - DILARANG menggunakan alert native browser (`alert()`, `confirm()`, `prompt()`).
  - DILARANG menggunakan warna sebagai satu-satunya indikator status tanpa teks atau icon.
  - DILARANG MENGUBAH business logic, route, skema database, atau controller hanya demi tampilan.

---

### 2. DESIGN TOKEN
Semua halaman harus menggunakan design token resmi. Dilarang melakukan styling inline acak (`style="margin: 17px; padding: 23px;"`). Wajib gunakan token Tailwind dan CSS variables sistem yang memetakan ke System Colors Apple.

---

### 3. COLOR SYSTEM (APPLE HIG STANDARD)
#### 3.1 Brand Primary / System Accent (System Blue)
COOCA menggunakan **System Blue** sebagai warna identitas aksi sistem (System Accent):
* **Light Mode**: `#007AFF` | **Dark Mode**: `#0A84FF`
* **Token CSS**: `--color-accent`
* **Penggunaan**: Primary CTA button, active navigation indicator, segmented control tab aktif, tautan teks (links).

#### 3.2 System Green (Success & Profit Saja)
Warna hijau dicadangkan murni untuk makna semantik keberhasilan dan keuntungan finansial:
* **Light Mode**: `#34C759` | **Dark Mode**: `#30D158`
* **Token CSS**: `--color-success`
* **Penggunaan**: Badge sukses, status lunas, angka laba bersih (profit), omzet positif, stok aman.

---

### 4. APPLE SEMANTIC TINTS
* **System Blue (`#007AFF` / `#0A84FF`)**: Primary action, link, tab aktif, filter terpilih.
* **System Green (`#34C759` / `#30D158`)**: Status lunas, profit, selesai, stok aman.
* **System Orange (`#FF9500` / `#FF9F0A`)**: Warning, pending/draft, stok menipis, HPP (COGS), perhatian mendesak.
* **System Red (`#FF3B30` / `#FF453A`)**: Error, kegagalan, void pesanan, tombol hapus destruktif, stok habis, kerugian, overdue.
* **System Indigo (`#5856D6` / `#5E5CE6`)**: Informasi, status pemrosesan, tooltip teknis, bantuan.
* **System Purple (`#AF52DE` / `#BF5AF2`)**: Apple Intelligence / AI Assistant, smart recommendations, analitik prediktif.
* **System Teal (`#30B0C7` / `#40C8E0`)**: Informasi alternatif, tagging sekunder.
* **System Yellow (`#FFCC00` / `#FFD60A`)**: Attention mikro, highlight peringatan dini.

---

### 5. NEUTRAL MATERIALS & VIBRANCY
Apple System Gray scale menggantikan slate generik:
* **Light Mode**:
  - `gray` (label muted): `#8E8E93`
  - `gray2`: `#AEAEB2` | `gray3`: `#C7C7CC` | `gray4`: `#D1D1D6` | `gray5`: `#E5E5EA`
  - `gray6` (lapisan terluar/terlembut): `#F2F2F7`
  - System Background: `#FFFFFF`
  - Secondary System Background: `#F2F2F7`
  - Separator: `#3C3C43` @ 29% (`border-black/[0.08]`)
* **Dark Mode**:
  - `gray` (label muted): `#8E8E93`
  - `gray2`: `#636366` | `gray3`: `#48484A` | `gray4`: `#3A3A3C` | `gray5`: `#2C2C2E`
  - `gray6`: `#1C1C1E`
  - System Background: `#000000` (mobile) / `#1E1E1E` (desktop)
  - Secondary System Background: `#1C1C1E`
  - Tertiary Card Background: `#2C2C2E`
  - Separator: `#545458` @ 60% (`dark:border-white/10`)

---

### 6. COLOR GOVERNANCE
* **Golden Rule**: *One color = one semantic meaning.*
* **Standard Meaning**:
  - Primary CTA / Links / Active Nav → **System Blue (`#007AFF`)**
  - Success & Profit & Lunas → **System Green (`#34C759`)**
  - Warning & Pending & Low Stock & HPP → **System Orange (`#FF9500`)**
  - Error & Failed & Delete & Void & Loss → **System Red (`#FF3B30`)**
  - AI & Smart Intelligence → **System Purple (`#AF52DE`)**
  - Info & Processing → **System Indigo (`#5856D6`)**
  - Neutral & Muted Data → **System Gray scale**
* **Pantangan**: DILARANG Green untuk semua tombol! Tombol destruktif WAJIB System Red, tombol ekspor/filter WAJIB System Gray/Neutral.

---

### 7. TIPOGRAFI
* **Font Family**: Stack sistem asli Apple:
  `-apple-system, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif`
* **Tabular Typography**: Seluruh angka finansial, nilai mata uang Rupiah, kuantitas stok, ID invoice, SKU, dan barcode WAJIB menyertakan `font-variant-numeric: tabular-nums` (Tailwind: `tabular-nums`), bukan monospace terminal generik.

---

### 8. TYPE SCALE (APPLE DYNAMIC TYPE COMPATIBLE)
| Token | Size | Line Height | Weight | Peruntukan |
|---|---|---|---|---|
| **Large Title** | 34px | 41px | Bold (700) | Judul Utama Window / Top Level |
| **Title 1** | 28px | 34px | Bold (700) | Judul Modul / Section Besar |
| **Title 2** | 22px | 28px | Semibold (600) | Judul Kartu / Panel |
| **Title 3** | 20px | 25px | Semibold (600) | Sub-header / Header Modal |
| **Headline** | 17px | 22px | Semibold (600) | Baris Data Utama / Label Penting |
| **Body** | 15–17px | 22px | Regular (400) | Teks Konten Utama |
| **Callout** | 16px | 21px | Regular (400) | Deskripsi Sekunder |
| **Subheadline** | 15px | 20px | Regular (400) | Label Form / Breadcrumb |
| **Footnote** | 13px | 18px | Regular (400) | Helper Text / Metadata |
| **Caption 1** | 12px | 16px | Regular (400) | Badges / Timestamps |
| **Caption 2** | 11px | 13px | Medium (500) | Micro-labels / Superscripts |

---

### 9. RESPONSIVE TYPOGRAPHY
* **Desktop (≥ 1024px)**: Title 20–24px, Body 15px, Caption 11–12px.
* **Mobile (< 640px)**: Title 17–20px, Body 14–15px, Caption 11px.
* **Rule**: Teks data operasional tidak boleh lebih kecil dari **11px**. Gunakan **Sentence case** selalu, hindari ALL CAPS.

---

### 10. FONT WEIGHT
Hanya gunakan 4 bobot Apple standar:
* `400` (Regular) → Paragraf, deskripsi, teks tabel biasa
* `500` (Medium) → Form helper, item navigasi, caption 2
* `600` (Semibold) → Header tabel, title 2/3, label penting, tombol primer/sekunder
* `700` (Bold) → Judul modul besar, angka metrik display utama

---

### 11. SPACING SYSTEM (8PT GRID)
Apple HIG membangun layout di atas grid dasar **8pt**:
`4, 8, 12, 16, 20, 24, 32, 40, 48, 64px`.

---

### 12. SPACING STANDARD
* Label → Input: `8px`
* Antar input/field: `16px`
* Tombol gap: `8px`
* Card Padding: `16–20px` (mobile), `20–24px` (desktop)
* Section to Section: `24–32px`

---

### 13. RESPONSIVE PAGE PADDING
* Desktop: `24–32px`.
* Mobile: `16px`.

---

### 14. BREAKPOINTS
* Compact (iPhone-like): < 640px
* Regular narrow (iPad portrait): 640–1024px
* Regular wide (iPad landscape / desktop window): 1024–1440px
* Extra wide (Studio Display dsb.): > 1440px

---

### 15. CONTAINER BOUNDARY
* Lebar konten dibatasi secara elegan untuk scannability: `max-w-[1360px] mx-auto` dengan padding simetris. Hindari container sempit `max-w-4xl` untuk dashboard data.

---

### 16. GRID SYSTEM
* Desktop: 12-kolom grid.
* KPI Summary: 4 kolom di desktop (`lg:grid-cols-4`).
* Mobile: **2-kolom compact KPI row** (`grid-cols-2`) atau grouped inset list.

---

### 17. PAGE FLOW
Urutan baku halaman operasional:
```
Breadcrumb → Toolbar Header (Judul + Subjudul + Actions) → KPI Summary Row → Segmented Controls & Search Toolbar → High-Density Data Table / Mobile Grouped Inset List → Pagination.
```

---

### 18. BREADCRUMB (MACOS STYLE)
* Minimalist format: `Dashboard › Inventori › Katalog Produk`.
* Warna muted `text-black/50 dark:text-white/50`, font 12–13px.

---

### 19. TOOLBAR / HEADER COCKPIT
* Tipis, material blur (`regularMaterial`: `backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border-b border-black/5 dark:border-white/10`).
* Judul `Title 2` (20–22px), subjudul `Footnote` ringkas (13px), aksi di kanan.

---

### 20. BUTTON SYSTEM (APPLE TOUCH TARGET)
* Visual desktop: `h-9` (36px).
* Visual mobile: `h-11` (44px, minimal touch target iOS).
* Micro action tabel: `h-7 px-2` (28px) atau Plain style.

---

### 21. BUTTON PADDING
* Horizontal: `px-3.5` s/d `px-4`. Gap icon: `6px`.

---

### 22. BUTTON RADIUS & SQUIRCLE
* Small: `rounded-[8px]`.
* Regular / Input field: `rounded-[10px]`.
* Capsule / Pill: `rounded-full`.

---

### 23. BUTTON HIERARCHY
* **Filled (Primary)**: `bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold shadow-[0_1px_2px_rgba(0,122,255,0.25)]`.
* **Tinted (Secondary)**: `bg-[#007AFF]/10 hover:bg-[#007AFF]/15 text-[#007AFF] font-semibold`.
* **Gray (Neutral)**: `bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] text-black/80 dark:text-white/80 font-medium`.
* **Plain (Ghost)**: `text-[#007AFF] hover:bg-[#007AFF]/8 font-medium`.
* **Danger**: `bg-[#FF3B30] hover:bg-[#E0352B] text-white font-semibold`.
* **Tactile Requirement**: Seluruh tombol wajib menyertakan `active:scale-[0.97] active:opacity-80 transition-all duration-150 ease-out`.

---

### 24. ICON SYSTEM
* Gunakan **SF Symbols** atau icon outline seragam (Lucide `stroke-width="1.5"`).
* Ukuran selaras teks: 14–16px untuk body, 18–20px untuk header.

---

### 25. SURFACE MATERIALS
* Menggunakan material translucency sesuai kebutuhan:
  - `regularMaterial`: `backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75` (Sidebar & Toolbar)
  - `thickMaterial`: `backdrop-blur-md bg-white/90 dark:bg-[#2C2C2E]/90` (Modal / Alert)
  - Kartu data biasa: `bg-white dark:bg-[#1C1C1E]` datar dengan hairline separator.

---

### 26. FORM CONTROLS
* Gunakan pola **Grouped Inset Style** untuk formulir berderet: baris putih ber-separator hairline `divide-y divide-black/5 dark:divide-white/5`.
* Untuk form standalone: `bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] h-11 px-3.5 focus:ring-2 focus:ring-[#007AFF]/50`.

---

### 27. FORM TYPOGRAPHY
* Label: 13–15px / Subheadline / Sentence case.
* Input text: 15px.
* Helper & Error: 12–13px.

---

### 28. FORM SECURITY & INTEGRITY
* Proteksi `@csrf`, validasi error `$errors->has('...')`, method spoofing `@method('PUT')` / `@method('DELETE')`.

---

### 29. HIGH-DENSITY TABLE SYSTEM
* Row Height: 40–44px.
* Divider: Hairline `divide-y divide-black/[0.04] dark:divide-white/[0.06]`.
* Hover State: `hover:bg-black/[0.02] dark:hover:bg-white/[0.03]`.

---

### 30. TABLE CAPABILITIES
* Search terintegrasi, filter segmented control, bulk checkbox selection, inline row actions, dan pagination.

---

### 31. RESPONSIVE TABLE (MACOS & IOS)
* Desktop: Dense table dengan hairline separator dan `tabular-nums`.
* Mobile: **Grouped Inset List** (`sm:hidden`) dengan chevron `›` atau tombol aksi kompak di kanan.

---

### 32. STATUS BADGE SYSTEM (APPLE TINTED PILL)
* Format: `inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-{color}/12 text-{color-dark-variant}`.
* Wajib ada Dot / Icon + Teks semantik.

---

### 33. SHEET PRESENTATION
* Desktop: Window sheet mengambang di tengah (480–560px), `thickMaterial`, radius 16–20px.
* Mobile: Slide-up sheet dari bawah dengan grabber handle dan radius atas 20px (`rounded-t-[20px]`).

---

### 34. DESTRUCTIVE CONFIRMATION (APPLE ALERT DIALOG)
* Menggunakan **Alert Dialog Apple** terpusat (290px), judul semibold, deskripsi footnote, dan 2 tombol horizontal: `Batal` (System Blue) dan `Hapus` (System Red).
* DILARANG menggunakan `confirm()` bawaan browser.

---

### 35. NOTIFICATION SYSTEM
* Menggunakan top-center banner tipis auto-dismiss dengan ikon status semantik.

---

### 36. TOAST DIMENSION
* Width: fit-content (min 280px, max 380px), radius `rounded-[14px]`, `thickMaterial`.

---

### 37. ASYNC LOADING FEEDBACK
* Tombol submit berganti teks, menyertakan micro-spinner inline, dan berstatus `disabled`.

---

### 38. SKELETON LOADERS
* Shimmer abu-abu halus: `bg-black/[0.06] dark:bg-white/[0.08] animate-pulse rounded-[8px]`.

---

### 39. EMPTY STATE
* Ikon SF Symbol besar (48–64px, `text-black/20 dark:text-white/20`), judul Headline, deskripsi Footnote, satu tombol Filled kecil sebagai CTA.

---

### 40. ERROR RECOVERY UX
* Pesan ramah manusiawi disertai tombol aksi `[ Coba Lagi ]` bergaya Tinted.

---

### 41. SUCCESS FEEDBACK
* Feedback deskriptif menerangkan apa yang berhasil diproses.

---

### 42. DASHBOARD ARCHITECTURE
* Padukan: Toolbar date filter → Segmented control tabs → KPI row → Chart dengan semantic tints → Grouped inset list.

---

### 43. KPI CARDS (APPLE HIG STYLE)
* Flat-neutral surface (`bg-white dark:bg-[#1C1C1E]`), border hairline `border-black/5 dark:border-white/5`, angka `tabular-nums` tebal, dan label muted.

---

### 44. CHART SYSTEM
* Line Chart: Tren omzet & pertumbuhan.
* Bar Chart: Ranking performa item.
* Donut / Ring: Proporsi pengeluaran & pembayaran.

---

### 45. CHART COLOR HARMONY
* Menggunakan palet Apple System Colors: Blue `#007AFF`, Green `#34C759`, Orange `#FF9500`, Red `#FF3B30`, Purple `#AF52DE`.

---

### 46. FINANCIAL UI
* Omzet & Laba Bersih: **System Green (`#34C759`)**
* Biaya Operasional & HPP: **System Orange (`#FF9500`)**
* Piutang Macet & Kerugian: **System Red (`#FF3B30`)**

---

### 47. NUMBER FORMAT
* Rupiah: `Rp {{ number_format($val, 0, ',', '.') }}` dengan kelas `tabular-nums`.
* Persentase: `12,5%`.
* Kuantitas: `148 botol`.

---

### 48. SIDEBAR (MACOS SOURCE LIST)
* Lebar `256px`, material `regularMaterial` (`backdrop-blur-xl bg-[#F2F2F7]/80 dark:bg-[#1C1C1E]/80`).
* Item aktif menggunakan background System Blue `#007AFF` dengan teks putih.

---

### 49. TOPBAR TOOLBAR
* Tinggi: Desktop 56–64px, Mobile 52px.
* Komponen: Breadcrumb context, Search pill, Notification, Profile.

---

### 50. DOMAIN NAVIGATION
* Menu tersusun logis: Penjualan, Inventori, Pembelian, Keuangan, Pengaturan.

---

### 51. PERMISSION-AWARE UI
* Tombol aksi sensitif dibungkus otorisasi: `@if(\App\Support\Context::hasPermission('...'))`.

---

### 52. ROLE & PERMISSION
* Pengecekan otorisasi berbasis granular permission, bukan hardcoded string role.

---

### 53. APPLE INTELLIGENCE STANDARD
* Fitur AI beraksen **System Purple (`#AF52DE`)** dengan opsi pembatalan transparan.

---

### 54. RESPONSIVE BEHAVIOR
* Desktop: Source list sidebar + Toolbar + Dense table.
* Mobile: Grouped inset list + Compact KPI row + Bottom sheet.

---

### 55. ZERO HORIZONTAL PAGE OVERFLOW
* Halaman tidak boleh mengalami horizontal scroll pada level `<body>` di viewport mana pun.

---

### 56. RESPONSIVE KPI
* Desktop 4 kartu ➔ Tablet 2 kartu ➔ Mobile 2-kolom compact row.

---

### 57. RESPONSIVE FORM
* Desktop: Grouped list rows 2-kolom. Mobile: 1-kolom grouped rows.

---

### 58. RESPONSIVE ACTION
* Desktop: Tombol berjajar rapi di toolbar. Mobile: Full-width atau grid 2-kolom teratur.

---

### 59. ACCESSIBILITY (WCAG 2.1 AA)
* Visual focus ring tegas: `focus:ring-2 focus:ring-[#007AFF]/50`.
* Kontras label vs background memenuhi standar rasio minimal.

---

### 60. CONTRAST RATIO
* Teks normal ≥ 4.5:1, teks besar ≥ 3:1 terhadap background.

---

### 61. DUAL-THEME FULL COVERAGE
* Setiap komponen wajib mendukung Light Mode dan Dark Mode secara native via Tailwind `dark:` pairs.

---

### 62. NO HARDCODED COLOR STYLES
* Dilarang menggunakan inline CSS warna absolut (`style="color: #000;"`).

---

### 63. UX FEEDBACK LOOP
* Alur aksi: Tap ➔ Tactile scale (`active:scale-[0.97] active:opacity-80`) ➔ Shimmer/Spinner ➔ Alert / Toast.

---

### 64. FORM SECURITY
* Proteksi CSRF, validasi sanitasi input, batasan upload file.

---

### 65. SQUIRCLE IMAGE STANDARDS
* Thumbnail produk ber-radius `rounded-[10px]`, aspek rasio `1:1`, lazy loading aktif.

---

### 66. TABLE DATA DENSITY
* Ketinggian baris 40–44px untuk densitas tinggi tanpa sesak.

---

### 67. TYPOGRAPHIC HIERARCHY
* Title ➔ Headline ➔ Body ➔ Footnote / Caption.

---

### 68. CAPSULE SEARCH
* Input search capsule ber-radius `rounded-[10px]`, icon kaca pembesar di kiri, background `bg-black/[0.04] dark:bg-white/[0.06]`.

---

### 69. FILTER CONTROLS
* Kontrol filter disederhanakan melalui Segmented Control.

---

### 70. ACTIVE FILTER CHIPS
* Chip filter minimalis dengan opsi reset yang mudah dijangkau.

---

### 71. PAGINATION
* Navigasi minimalis: `‹ Sebelumnya`, nomor halaman aktif, `Selanjutnya ›`.

---

### 72. TOOLTIP
* Penjelasan singkat pada icon-only buttons via atribut `title` atau popover tooltip.

---

### 73. Z-INDEX SYSTEM
* Base `0`, Sticky Header `20`, Modal Sheet `50`, Alert Dialog `60`, Toast `70`.

---

### 74. BORDER RADIUS HIERARCHY (CONCENTRIC SQUIRCLE)
* Tombol kecil: `rounded-[8px]` (8px)
* Tombol regular / Input: `rounded-[10px]` (10px)
* Kartu data: `rounded-[14px]` (14px)
* Panel / Modal window: `rounded-[16px]`–`rounded-[20px]` (16–20px)
* Sheet mobile: `rounded-t-[20px]` (20px)
* Pill / Badge: `rounded-full` (999px)

---

### 75. ELEVATION SYSTEM
* Hairline separator first (`border-black/5 dark:border-white/10`), subtle diffused ambient shadow second. Dilarang `shadow-lg` tebal.

---

### 76. MOTION TIMING
* Durasi transisi 150–250ms dengan fisikal easing: `cubic-bezier(0.25, 0.1, 0.25, 1)`.

---

### 77. MICRO INTERACTIONS
* Sentuhan taktil pada elemen interaktif: `active:scale-[0.97] active:opacity-80`.

---

### 78. CONSISTENT TERMINOLOGY
* Bahasa Indonesia baku: Gunakan **Tambah**, **Simpan**, **Batal**, **Hapus**, **Terapkan**.

---

### 79. LOCALIZATION
* Format Rupiah: `Rp 18.000` dengan `tabular-nums`. Tanggal format Indonesia.

---

### 80. ACCESSIBLE ERROR MESSAGES
* Pesan validasi ramah manusiawi di bawah input field.

---

### 81. PAGE STATES HANDLING
* Seluruh 5 state (Normal, Loading, Empty, Success, Error) tertangani rapi.

---

### 82. PERMISSION DENIED SCREEN
* Tampilan ramah dengan ikon gembok SF Symbol dan tombol Plain "Kembali ke Dashboard".

---

### 83. ERROR RECOVERY UX
* Tombol `Coba Lagi` langsung tersedia saat terjadi kegagalan jaringan atau query.

---

### 84. INFORMATION DENSITY FOR ERP
* Memprioritaskan keterbacaan data bisnis yang tinggi melalui tipografi rapi, bukan ruang kosong mubazir.

---

### 85. VISUAL SCAN PATTERN
* Hirarki tipografi SF Pro memandu pengguna membaca data kunci dalam 3 detik pertama.

---

### 86. COMPONENT REUSABILITY
* Memaksimalkan komponen Blade dan CSS yang sudah ada di layout aplikasi.

---

### 87. CROSS-MODULE CONSISTENCY
* Seluruh modul (Penjualan, Inventori, Pembelian, Keuangan) berbagi kosakata visual yang identik.

---

### 88. DASHBOARD RESPONSIVE DYNAMICS
* Desktop: Source list + Toolbar + Multi-column widgets + Tables.
* Mobile: Compact KPI row + Grouped inset lists.

---

### 89. MOBILE NAVIGATION
* Topbar ringkas dengan drawer menu atau tab bar iOS.

---

### 90. PERFORMANCE OPTIMIZATION
* Asset ringan, Alpine.js reaktif, lazy loading gambar, server-side pagination.

---

### 91. SCALABLE ERP DATA UX
* Pagination server-side untuk menangani ribuan baris data tanpa membebani browser.

---

### 92. AUDIT TRAIL LOGGING
* Transaksi penting mencatat user and timestamp di backend.

---

### 93. CONFIRMATION LEVELS
* Aksi baca: Langsung tanpa konfirmasi.
* Aksi pembatalan draft: Konfirmasi sheet standar.
* Aksi hapus data: Konfirmasi Apple Alert Dialog.

---

### 94. DATA SECURITY AT BACKEND
* Proteksi data sensitif ditegakkan di level Controller & Eloquent Policy.

---

### 95. RESPONSIVE MODAL SHEETS
* Desktop: Mengambang di tengah (centered floating window).
* Mobile: Bottom-sheet slide-up dengan grabber.

---

### 96. RESPONSIVE CARDS
* Flat neutral card ber-radius kontinu sesuai level hierarki.

---

### 97. MOBILE DATA TILES
* Grouped inset list dengan baris teratur dan penanda status dot semantik.

---

### 98. QUALITY VERIFICATION GATES
* Lolos verifikasi Warna, Tipografi, Material, Radius, Kontras, Touch Target, Dual-Theme, dan Non-Destructive Guarantee.

---

### 99. APPROVAL RULE
* Modul hanya berstatus **READY** jika memenuhi seluruh kriteria checklist Bagian 11.

---

### 100. 10 RANGKUMAN UTAMA APPLE HIG COOCA v2.0
1. **Clarity, Deference, and Depth** — Konten bisnis memimpin, chrome dekoratif mundur.
2. **System Blue (`#007AFF`) sebagai Primary Accent** — Emerald hijau dicadangkan murni untuk Success / Profit.
3. **Materials & Vibrancy** — Permukaan dibedakan lewat material blur (`regularMaterial`) dan hairline separator, bukan border tebal.
4. **One Color, One Semantic Meaning** — Satu warna hanya membawa satu arti semantik.
5. **Continuous Curvature (Squircle)** — Skala radius kontinu: 8px, 10px, 14px, 16–20px, 999px.
6. **SF Pro Type Scale & Tabular Figures** — Skala Dynamic Type Apple dengan `tabular-nums` untuk angka finansial.
7. **Apple Button Styles** — Filled, Tinted, Gray, Plain, Danger dengan feedback taktil `active:scale-[0.97] active:opacity-80`.
8. **Grouped Inset Lists & Segmented Controls** — Kontrol familiar khas macOS & iOS menggantikan border card berulang.
9. **Apple Sheet & Alert Dialog** — Dialog konfirmasi terpusat kecil dengan tombol horizontal menggantikan popup pihak ketiga.
10. **Strict Non-Destructive Business Logic Guarantee** — Seluruh rumus keuangan, routes, CSRF, database schema, dan permissions 100% terjaga utuh.

---

### 101. QUICK REFERENCE SPECIFICATION (APPLE HIG v2.0)
* **Primary Accent**: System Blue `#007AFF` (Light) / `#0A84FF` (Dark)
* **Success / Profit**: System Green `#34C759` (Light) / `#30D158` (Dark)
* **Warning / HPP**: System Orange `#FF9500` (Light) / `#FF9F0A` (Dark)
* **Danger / Delete**: System Red `#FF3B30` (Light) / `#FF453A` (Dark)
* **Materials**: `regularMaterial` (`backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75`)
* **Typography**: Stack SF Pro / Inter, angka dengan kelas `tabular-nums`
* **Border Radius**: Small button 8px, Regular button/Input 10px, Card 14px, Sheet 20px, Badge full pill
* **Button Height**: Visual desktop 36px (`h-9`), Mobile touch target 44px (`h-11`)
* **Touch Target**: Minimal 44×44px pada layar sentuh ponsel

---

### 102. MASTER GOVERNANCE RULE
Setiap halaman, modul, komponen Blade, modal, form, tabel, dashboard, notifikasi, dan interaksi di dalam sistem COOCA harus mematuhi COOCA UI/UX Design System v2.0 berstandar Apple HIG ini. Tidak ada keputusan visual yang dibuat secara arbitrer tanpa merujuk pada standar resmi ini.
