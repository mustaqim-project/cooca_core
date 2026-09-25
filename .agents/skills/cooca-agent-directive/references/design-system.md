# COOCA - Bento Apple HIG Design System (Referensi Lengkap)

Baca file ini sebelum menyentuh view/Blade/CSS apa pun di COOCA. Ini adalah gabungan penuh dari dua draft `AGENT.md`, tanpa duplikasi.

## 1. Tiga Pilar Apple HIG

1. **Clarity (Kejelasan Mutlak)** - _3-Second Glanceability_: setiap halaman harus dipahami maksud, status kunci, dan aksi utamanya dalam 3 detik pertama. Kontras teks minimal 4.5:1 (WCAG 2.1 AA). Ikon Lucide selalu berpasangan dengan label jelas pada aksi kritis.
2. **Deference (Kerendahan Hati Antarmuka)** - UI adalah pelayan konten, bukan pencuri perhatian. Dilarang gradien neon, drop-shadow pekat, atau border tebal gelap. Kanvas abu-abu netral (`#F2F2F7` light / `#000000` dark), kartu putih bersih (`#FFFFFF` light / `#1C1C1E` dark).
3. **Depth (Kedalaman & Layering Halus)** - 4 level elevasi:
    - Level 0 (Canvas): `#F2F2F7` light / `#000000` dark
    - Level 1 (Card Surface): `#FFFFFF` light / `#1C1C1E` dark
    - Level 2 (Elevated Hover Tile): `#F9F9FB` light / `#2C2C2E` dark
    - Level 3 (Modal/Floating Sheet): Frosted Glass - `backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08]`
    - Hairline border: `border-black/[0.06] dark:border-white/[0.08]` (1px), bukan garis pekat tebal.

## 2. Brand Soul & Persona Cooca

Cooca **bukan** template AI generik dan bukan tiruan Silicon Valley - Cooca adalah **Platform Sistem Operasi Bisnis UMKM Nusantara yang Berjiwa, Jujur, Tangguh, dan Presisi**.

1. **Tenang & Berwibawa** - tidak berteriak dengan ornamen/stiker warna-warni. White space dibiarkan lapang sebagai "udara bernapas", bukan ruang yang harus dijejali badge.
2. **Kejujuran & Presisi Fungsional** - setiap piksel/garis/angka punya alasan operasional nyata. Angka penjualan, HPP, dan stok disajikan dengan kepastian matematis (`tabular-nums`, tipografi tebal).
3. **Wibawa Tanpa Gimmick** - hierarki tipografi murni (ukuran, ketebalan, warna) lebih diutamakan daripada membungkus teks ke kapsul.
4. **Kehangatan Manusiawi** - bahasa Indonesia santun, bersahaja, lugas. **DILARANG KERAS** slogan klise AI (_AI-Powered Synergy, Next-Gen Modular Ecosystem, Ultimate Solution_).

## 3. Mandat Anti-AI-Template & Anti-Pill-Abuse

"Pill & Badge Inflation" (membungkus setiap kata/angka ke kapsul `rounded-full`, dot berkedip palsu, "alis kapsul" eyebrow di atas judul) merusak wibawa brand. Aturan mutlak:

- **Larangan Eyebrow Pills**: dilarang keras kapsul/badge di atas H1/H2 (mis. `🟢 AI-Powered Management System • 100% Gratis`, `Ekosistem Modular Terpadu`). Jika konteks section perlu, gunakan **Pure Typographic Overline/Kicker**: teks murni tanpa kapsul (`text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`).
- **Larangan Metric Cluttering**: dilarang menempel pill kecil di samping angka besar (`Rp 0` + pill `📈 ARR Rp 0.0 Juta/thn`). Angka utama berdiri gagah (`text-3xl font-bold tabular-nums`); keterangan sekunder jadi footnote teks polos di bawahnya (`text-[12px] text-black/50 dark:text-white/50`).
- **Larangan Fake Pulse Dots**: `animate-pulse` HANYA untuk status perangkat keras fisik yang benar-benar tersambung (printer thermal Bluetooth, timbangan digital, barcode scanner, koneksi socket server kritis) - bukan pada teks biasa atau rentang waktu.
- **Batasan Pill/Badge**: hanya untuk **status siklus hidup entitas bisnis yang berubah** - status transaksi (`Menunggu Pembayaran` amber, `Lunas` green, `Dibatalkan` gray, `Ditolak` red), status inventori (`Stok Aman` green, `Menipis` amber, `Habis` red), status akun (`Aktif` green, `Ditangguhkan` red, `Superadmin` blue). **Maksimal 1 badge per entitas/baris.** Dilarang untuk teks statis, slogan promosi, rentang waktu, atau kategori tanpa siklus status.
- **Eliminasi Total Elemen Fluff**: menghapus bungkus pill tapi membiarkan teks sampahnya melayang (`Live 6 Bulan Terakhir`, `Organik`, `Terverifikasi`, `Platform-Wide`, `AI-Powered Management System`, `INSIGHT`) **tetap merusak UI**. Jika teks tidak punya nilai fungsional nyata atau sudah tersirat dari konteks - **hapus total elemen dan teksnya**, jangan tinggalkan teks polos.
- **Hierarki via Tipografi Murni**: kontras skala (`text-2xl`/`text-3xl` + `text-[14px]`), kontras bobot (`font-bold` data penting, `font-medium` label, `font-normal` keterangan), kontras warna semantik (`text-black dark:text-white` primer, `/60` sekunder, `/40` label kecil), ruang bernapas 16–24px tanpa dekorasi stiker.
- **Larangan Emoji Mutlak**: TIDAK ADA emoji Unicode (🚀✨💡👥🏆🎟️📦⚡🔥🟢📈💬🏢 dll.) di tombol, judul (H1/H2/H3), bento card/widget KPI, tab bar, dialog konfirmasi, alert banner, badge status, atau kolom tabel - di seluruh UI, termasuk contoh microcopy di dokumen lama yang menyertakan emoji (`💡 Tenang...`, `📲 Kirim Manual...`) harus diterapkan tanpa emoji, hanya dengan Lucide icon (`<i data-lucide="...">`) atau SVG inline fungsional.

## 4. Geometri Squircle & Continuous Corner Radius

- Outer Bento Card: `rounded-[20px]`/`rounded-[24px]` desktop, `rounded-[16px]` mobile
- Inner Tile/Sub-Widget: `rounded-[14px]`/`rounded-[16px]`
- Tombol & Input: `rounded-[12px]`/`rounded-[14px]`
- Status Badge/Avatar/Pills: `rounded-full`
- Mobile Bottom Sheet: `rounded-t-[28px]` (varian 26px juga dipakai untuk action sheet - pilih konsisten per konteks)
- **Dilarang keras**: `rounded-none`, `rounded-sm`, sudut kaku 2–4px.

## 5. Mikro-Interaksi Taktil

```html
class="transition-all duration-150 ease-out active:scale-[0.98]
hover:opacity-95"
```

- Touch target minimum **44×44px**, wajib **48–52px** untuk tombol aksi utama di layar sentuh mobile kasir.
- Jarak antar tombol penting minimal **12–16px** (`gap-2.5`–`gap-3`).

## 6. Mandat Anti-Excessive-Text & Microcopy

**Dilarang** menambahkan: paragraf penjelasan panjang tanpa kebutuhan operasional, deskripsi berulang yang menjelaskan hal yang sudah jelas dari judul, subtitle pada tiap kartu tanpa fungsi pembeda status, helper text yang tidak membantu keputusan, teks promosi di halaman transaksi/operasional, jargon teknis (_SKU, BOM, COGS, Void, Tenant Context_), judul panjang yang bisa diringkas 2–3 kata, empty state berkalimat panjang (cukup: _"Belum ada produk"_ + tombol aksi).

Prioritas konten UI: **Wajib** (agar user paham data/selesaikan tugas) → **Membantu** (konteks penting/cegah kesalahan) → **Opsional** (hanya di modal sheet saat diminta) → **Tidak perlu** (hapus seketika).

Contoh microcopy lugas:
| Kurang baik | Lebih baik |
|---|---|
| "Silakan melakukan proses penyimpanan data produk yang telah Anda masukkan." | **"Simpan Produk"** |
| "Anda belum memiliki data produk yang dapat ditampilkan pada halaman ini." | **"Belum ada produk."** |
| "Apakah Anda benar-benar yakin ingin melanjutkan proses penghapusan data ini?" | **"Hapus produk ini?"** |

**Tombol Aksi Lugas**: tombol adalah pemicu aksi, bukan tempat mengulang judul kartu/halaman.

- Di dalam form/modal → satu kata kerja murni: **Simpan · Hapus · Edit/Ubah · Lihat · Batal · Kirim · Salin**.
- Pengecualian terbatas (maks. 2 kata) hanya untuk CTA utama index di luar form/tabel: _"Tambah Produk"_, _"Ekspor Excel"_, _"Cetak Struk"_.
- Contoh salah: _"Simpan Pengaturan Google & Sistem"_ → benar: _"Simpan"_. _"Lakukan Proses Penghapusan Akun"_ → _"Hapus"_.

## 7. Filosofi Zero-Manual / Self-Explanatory UI

Target pengguna: Boomer (50–65+) & Milenial Akhir (40+), gaptek, mudah cemas dengan istilah teknis, rentan salah sentuh.

1. **Tombol aksi utama mencolok & berkata kerja spesifik** - Primary System Blue (`bg-[#007AFF] text-white`) dengan teks jelas (_"+ Tambah Barang Baru"_, bukan hanya ikon `+`). Dilarang tombol aksi kritis hanya ikon tanpa teks.
2. **Kaidah 3 Kolom Pokok (Anti-Intimidasi Form)** - form utama (mis. Tambah Produk) hanya menampilkan **3 input inti**: Nama Barang/Jasa, Kategori, Harga Jual (Rp). Seluruh opsi teknis (Barcode, Resep BOM, Modal HPP, Min Stok Gudang) disembunyikan di akordeon opsional (`⚙️ Atur Modal Beli, Stok Gudang & Resep (Opsional) ▾` - tanpa emoji pada implementasi nyata, gunakan ikon Lucide).
3. **Microcopy Penenang Jiwa (No-Panic Feedback)** - di setiap dialog konfirmasi/hapus, sertakan kalimat penenang menggunakan ikon Lucide `info`, tanpa emoji: _"Tenang, riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan."_, _"Anda dapat mengubah kembali pilihan ini kapan saja."_ Tombol destruktif wajib konfirmasi dua langkah.
4. **Format Ribuan Otomatis** - input nominal wajib format pemisah ribuan real-time (`Rp 250.000`).
5. **Bahasa Indonesia lugas, bebas jargon Inggris**: _HPP/COGS_ → Modal Pokok/Biaya Bahan; _Stock Reversal_ → Pengembalian Bahan; _Void Transaction_ → Pembatalan Transaksi; _Tenant Context_ → Pemisahan Toko.

## 8. Matriks Tipografi Lintas Perangkat

Font stack: `-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif`

| Peran             | Mobile (<640px)                 | Tablet (640–1023px) | Desktop (1024px+) | Weight  | Line Height  |
| ----------------- | ------------------------------- | ------------------- | ----------------- | ------- | ------------ |
| Large Title       | 22–24px                         | 28px                | 32–34px           | 700     | 1.2          |
| Title 1/Section   | 20px                            | 24px                | 24px              | 600     | 1.25         |
| Title 2/Card      | 17–18px                         | 18px                | 20px              | 600     | 1.3          |
| Headline          | 16px                            | 16px                | 16px              | 600     | 1.35         |
| Body              | 15–16px                         | 15–16px             | 14–15px           | 400     | 1.5          |
| Form Input/Select | **16px (MUTLAK)**               | 14–15px             | 14px              | 400     | 1.4          |
| Subheadline       | 14px                            | 14px                | 13–14px           | 500     | 1.4          |
| Footnote/Helper   | 13px                            | 13px                | 12–13px           | 400     | 1.35         |
| Caption/Badge     | 12px                            | 12px                | 11–12px           | 600     | 1.2          |
| Angka/Moneter     | `tabular-nums` semua breakpoint |                     |                   | 600–700 | leading-none |

- **Anti Auto-Zoom iOS**: `<input>`/`<select>`/`<textarea>` mobile wajib `text-[16px] sm:text-[14px]` atau lebih besar - di bawah 16px memicu auto-zoom Safari yang merusak layout.
- **Tabular Figures wajib** (`tabular-nums`) untuk: Rupiah, jumlah stok, nomor nota/faktur, tanggal & jam transaksi, persentase & diskon.
- Hanya 4 bobot: 400/500/600/700. **Dilarang** `font-black`/900. Dilarang all-bold dalam satu kartu - kontraskan label (400/500, abu-abu) vs nilai utama (600/700, hitam/putih).

## 9. Information Hierarchy

```
Primary Purpose → Primary Information → Primary Action → Secondary Information → Optional Detail
```

Pertanyaan panduan: apa yang harus diketahui user dalam 3 detik pertama? Apa yang harus dilakukan sekarang? Apa risiko jika salah pilih? Info apa yang cukup di modal sheet saat diminta? Jangan menampilkan semua info dengan bobot visual yang sama.

## 10. Sistem Spacing Adaptif (8pt Grid)

| Area                       | Mobile (<640px)              | Tablet (640–1023px) | Desktop (1024px+)                  |
| -------------------------- | ---------------------------- | ------------------- | ---------------------------------- |
| Jarak elemen kecil         | 6–8px                        | 8px                 | 8px                                |
| Jarak antar kontrol/tombol | 10–12px                      | 12–16px             | 12–16px                            |
| Grid gap antar kartu       | `gap-3` (12px)               | `gap-4` (16px)      | `gap-4 sm:gap-5`/`gap-6` (16–24px) |
| Padding dalam kartu        | `p-3.5`–`p-4` (14–16px)      | `p-5` (20px)        | `p-6` (24px)                       |
| Jarak antar section        | 16–20px                      | 24px                | 24–32px                            |
| Margin horizontal halaman  | `px-3`                       | `px-6`              | `px-8 max-w-[1440px] mx-auto`      |
| Safe-area bawah            | **`pb-28`–`pb-32` (MUTLAK)** | `pb-16`             | `pb-10`/`pb-12`                    |

Dilarang `p-6`/`p-8` pada kartu mobile (memotong 48–64px dari layar 360–390px). Checklist anti-padat: teks mepet border? tambah padding. Tombol saling menempel? beri `gap-2.5`–`gap-3`. Terlalu banyak elemen berjejal di satu layar? gunakan progressive disclosure/modal sheet. Bebas scroll horizontal di 360px?

## 11. Blueprint Responsivitas Bento & Anti-Overflow

### Smartphone 360–639px (Zero-Breakage Rules)

- Dilarang `w-[...]`/`min-w-[...]` statis > 300px - pakai `w-full max-w-full`.
- Semua teks dalam flex container wajib `min-w-0` + `truncate`/`break-words`.
- Grid: form/detail/tabel kompleks → `grid-cols-1`. Stat/KPI ringkas → maksimal `grid-cols-2` (jangan tumpuk `grid-cols-1` monoton untuk kartu metrik - berpasangan 2 kartu per baris dengan `col-span-2` untuk hero card).
- Tabel 6–10 kolom: sembunyikan di mobile (`hidden md:block`), ganti Card List View (`block md:hidden`). Jika tabel wajib tampil: `overflow-x-auto` dengan padding sentuh aman.
- Toolbar search/filter: input `w-full` baris atas, filter kategori scroll horizontal (`flex overflow-x-auto no-scrollbar space-x-2 py-1`).
- Safe area bawah **wajib `pb-28`–`pb-32`**.
- Transaksi POS/checkout: tombol utama di floating bottom action bar menempel jempol.

### Tablet Kasir 640–1023px / 768–1024px

- Layout 2–3 kolom proporsional; katalog & keranjang POS berdampingan.
- Tombol aksi minimal 48px, informasi penting terlihat tanpa scroll berlebihan.

### Desktop 1024–1920px+

- Kanvas `max-w-[1440px] mx-auto`, layout bento 3–4 kolom lapang.
- Tabel dibungkus `overflow-x-auto` + hairline border lembut.
- **Perbaikan mobile DILARANG merusak tampilan desktop yang sudah baik - preservasi 100%.**

### Mandat Anti-Kerusakan Dimensi SVG

- Setiap `<svg>` wajib atribut eksplisit `width`/`height` DAN kelas presisi (`w-[18px] h-[18px]` atau `w-4 h-4`/`w-5 h-5`). Dilarang pecahan non-standar (`w-4.5`) tanpa daftar di `tailwind.config`. Mencegah SVG "meledak" menutupi sidebar.

### Mandat Kontensi Sidebar

- Sidebar wajib `overflow-y-auto overflow-x-hidden`. Dilarang scrollbar horizontal atau floating widget yang menutupi navigasi.

## 12. Layout Bagian Per Bagian (Sidebar, Topbar, Body, Footer)

### A. Sidebar (macOS Sonoma Source List)

- **Lebar baku desktop `w-72` (288px)** - dilarang sempit `w-64`. Offset kanvas utama `lg:pl-72`.
- **Mandat Satu Baris Mutlak**: item navigasi `h-10 px-3 rounded-[12px] flex items-center gap-2.5`; label wajib `whitespace-nowrap truncate min-w-0 flex-1`. Dilarang keras teks menu terbungkus 2–3 baris. Nomenklatur ringkas (_"Langganan & Billing"_, _"Rekening Bank"_, _"Token AI"_, _"WhatsApp Gateway"_, _"Google OAuth"_, _"Server SMTP"_).
- **Scrollbar ramping anti-Windows**: `.sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.15) transparent; }` (dark: `rgba(255,255,255,0.18)`), webkit lebar 4px, thumb `rounded-full` semi-transparan tanpa tombol panah. Dilarang scrollbar native Windows 17px.
- Material: `backdrop-blur-2xl bg-[#F2F2F7]/80 dark:bg-[#1C1C1E]/80 border-r border-black/[0.06] dark:border-white/[0.08]`. Dilarang `bg-gray-900`/`bg-slate-800` solid.
- Header brand: squircle `w-9 h-9 rounded-[11px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6]`.
- Grup nav: label `px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]`.
- State aktif: `bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold`. State inaktif: `text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]`.
- Badge counter: pill bulat `px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 ml-2` dengan warna semantik.
- Footer profil: kartu bento `p-2.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]`, nama user `min-w-0 flex-1 truncate`.

### B. Topbar (Zero Top-Edge Clipping Directive)

- Tinggi aman: `h-[68px] sm:h-[72px]` atau `min-h-[64px] py-2 sm:py-2.5 px-4 sm:px-8 sticky top-0 z-30 backdrop-blur-xl bg-[#F2F2F7]/75 dark:bg-[#000000]/75 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4`.
- **Dilarang keras** baris eyebrow terpotong di tepi atas viewport - wajib `<div class="min-w-0 flex flex-col justify-center">` dengan eyebrow `leading-none mb-1`, `navigationTitle` `leading-snug`, `navigationSubtitle` `leading-none mt-0.5`.
- Skala: eyebrow 10.5–11px uppercase (`text-[#8E8E93] dark:text-[#98989D]`); title 19–21px font-extrabold (`tracking-tight truncate leading-snug`); subtitle 12–13px (`text-black/50 dark:text-white/50 hidden md:block`).
- Segmented switcher tema: pill `p-1 rounded-full bg-black/[0.05] dark:bg-white/[0.08]`. Pemisah vertikal hairline `w-[1px] h-5 bg-black/[0.08] dark:bg-white/[0.1]`.
- Primary CTA: `bg-[#007AFF] text-white rounded-[14px] px-4 py-2 font-semibold shadow-sm hover:brightness-105 active:scale-[0.98]`.

### C. Content Body

- `max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8`, kartu `rounded-[20px]`/`rounded-[24px]`.
- Tabel dibungkus kartu bento dengan `overflow-x-auto scrollbar-thin`.

### D. Footer (Dual-Mode Architecture)

- **Mobile/Tablet (`md:hidden`)** - **Full-Style Floating Bottom Navigation Bar (iOS 18)**: `fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 pb-[env(safe-area-inset-bottom)]`, material `backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 rounded-[24px]`, target sentuh 48–52px, tombol tengah menonjol (_Elevated Center Action Trigger_) gradasi System Blue untuk aksi kasir tercepat.
- **Desktop** - Clean Minimalist Hairline Footer: `border-t border-black/[0.06] dark:border-white/[0.08] py-4 px-6 lg:px-8 text-[12px]`, menampilkan status koneksi sistem & versi aplikasi.

## 13. Harmoni Warna Semantik & Dual-Mode

- System Blue `#007AFF`/`#0A84FF` · Green `#34C759`/`#30D158` · Orange `#FF9500`/`#FF9F0A` · Red `#FF3B30`/`#FF453A` · Indigo `#5856D6`/`#5E5CE6` · Purple `#AF52DE`/`#BF5AF2`.
- Light: background `#F2F2F7`, kartu `#FFFFFF`/`bg-white/80 backdrop-blur-xl`, teks utama `#000000`, sekunder `text-black/60`, hairline `border-black/[0.06]`.
- Dark: background `#000000`/`#1C1C1E`, kartu `#1C1C1E`/`#2C2C2E`, teks utama `#FFFFFF`, sekunder `dark:text-white/60`, hairline `border-white/[0.08]`.
- Dilarang fill solid jenuh di kartu/banner - gunakan tinted badge pill (`bg-{color}/12 text-{color}`) hanya untuk status siklus hidup (lihat §3).

## 14. Mandat Mutlak: Form Pop-Up / Modal Sheet Berukuran Penuh (Full Size) di Desktop & Mobile

Setiap kali antarmuka memuat **formulir (Form Create, Edit, Input Transaksi, Form Penyesuaian, Show/Detail, maupun Dialog Interaktif)** di dalam pop-up / modal, wajib mengadopsi standar **Full-Size Canvas** lintas perangkat:

> **Aturan Emas**: Dilarang keras membuat modal form berukuran kecil/sempit (`max-w-sm`, `max-w-md`, `max-w-lg`, atau `max-w-xl`) yang menyebabkan isian form terlihat berdesakan, terpotong, atau mengharuskan scrolling sempit. Seluruh form dalam pop-up wajib lapang, membentang penuh, dan bernafas lega.

### A. Desktop (>= 1024px) - Full Layout XXL Centered Bento Dialog
- **Dimensi Kontainer**: `w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] mx-auto rounded-[24px] max-h-[92vh] flex flex-col overflow-hidden`.
- **Struktur Multi-Kolom Bento**: Memanfaatkan lebar layar secara maksimal dengan tata letak multi-kolom yang lapang (misal: 8 kolom untuk field input & tabel item transaksi + 4 kolom untuk kartu ringkasan, metrik kalkulasi live, dan instruksi bantuan).
- **Elemen Form**: Seluruh kolom input, dropdown, textarea, dan tabel item wajib membentang penuh (`w-full`) di dalam kolom Bento masing-masing.
- **Sticky Navigation**: Sticky modal header di atas dan sticky footer action bar di bawah (`[ Batal ]` dan `[ Simpan Data ]` 44px–52px).

### B. Tablet (640px – 1023px) - Centered Responsive Bento Modal
- **Dimensi Kontainer**: `w-full max-w-[94vw] md:max-w-3xl lg:max-w-4xl mx-auto rounded-[22px] max-h-[92vh] flex flex-col overflow-hidden`.
- **Tata Letak**: Grid 2-kolom seimbang dengan touch target tombol 44px–48px dan input `text-[15px] sm:text-[14px]`.

### C. Mobile (< 640px) - Full-Width Apple Responsive Bottom Sheet
- **Dimensi Kontainer**: `w-full inset-x-0 bottom-0 rounded-t-[28px] h-full max-h-[95vh] sm:max-h-[96vh] flex flex-col overflow-hidden`.
- **Pemanfaatan Layar**: Memanfaatkan 100% lebar viewport ponsel (`w-full`), dengan grab bar di puncak (`w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 shrink-0`).
- **Skala Input & Anti-Auto-Zoom**: Seluruh `<input>`, `<select>`, dan `<textarea>` berukuran font minimal **16px** (`text-[16px] sm:text-[14px]`) untuk mencegah auto-zoom iOS Safari.
- **Sticky Bottom Action Bar**: Tombol submit/aksi utama berukuran penuh `w-full` dengan tinggi **48px–52px** dan padding bawah aman (*Safe Area Padding*) `pb-[max(1.25rem,env(safe-area-inset-bottom))]` / `pb-8`.

---

## 15. Inline Quick-Add `[ + ]`

Dropdown master relasi (Kategori, Satuan, Supplier, Pelanggan, Rekening Bank, Akun Kas) wajib tombol `[ + ]` di samping:

```html
<div class="flex items-center gap-2">
    <div class="relative flex-1">
        <select class="w-full text-[16px] sm:text-[14px] rounded-[12px] ...">
            ...
        </select>
    </div>
    <button
        type="button"
        class="w-11 h-11 flex-shrink-0 rounded-[12px] bg-blue-50 dark:bg-blue-900/30 text-[#007AFF] font-bold text-lg flex items-center justify-center active:scale-95 transition-all"
        title="Tambah Cepat"
    >
        +
    </button>
</div>
```

Perilaku wajib: buka mini modal sheet tanpa menghilangkan isian form utama → simpan via AJAX (`fetch()`) → injeksi opsi baru ke `<select>` → pilih otomatis (_auto-select_) → tutup mini modal, lanjutkan form utama tanpa reset data yang sudah diketik.

## 16. Mandat Konsolidasi UI (UI Unification Directive)

> **Aturan Emas**: Jika dua/tiga antarmuka saling melengkapi dan mengelola entitas yang sama, WAJIB digabung menjadi satu halaman berbasis Tab atau Master-Detail.

| Halaman Terpisah (Pola Lama)                                                                     | Penggabungan (Pola Baru)                                                                                               | Manfaat                                                             |
| ------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------- |
| `Pelanggan` (`/customers`) + `CRM & Member` (`/crm/members`)                                     | **Pusat Pelanggan & Loyalitas** (`/customers`) dengan tab: Semua Pelanggan · Member & Poin · Voucher Diskon            | Satu tempat untuk utang piutang, kontak WhatsApp, poin hadiah       |
| `Katalog Produk` (`/products`) + `Jasa & Layanan` (`/services`)                                  | **Katalog Usaha** (`/products`) dengan segmented control: Semua · Barang Fisik (Ada Stok) · Jasa/Servis (Bebas Stok)   | Tidak bingung antara menu jasa dan barang; input menyesuaikan tab   |
| `Kas & Rekening Bank` (`/finance/cash-bank`) + `Buku Kas & Ledger` (`/finance/cash-bank/ledger`) | **Pusat Kas & Bank** (`/finance/cash-bank`) - atas: saldo & tombol cepat kas masuk/keluar; bawah: tabel mutasi terpadu | Owner langsung lihat kas laci, saldo bank, dan mutasi di satu layar |
| `Master Data Supplier` (`/suppliers`) berdiri sendiri                                            | Pindahkan ke grup navigasi **Pembelian & Vendor** (berdampingan PO, Tagihan, Retur)                                    | Tidak perlu cari menu Supplier di bawah dashboard                   |

Penggabungan wajib disertai rute redirect/alias untuk URL lama dan melewati Interactive Confirmation Gate sebelum eksekusi.

---

## 17. Spesifikasi UI Notification Center, Toast Apple HIG, & In-App Fraud Alert

### A. Bell Dropdown / Notification Center (Header)

- **Trigger Button**: Tombol 40×40px atau 44×44px di Topbar dengan ikon Lucide `bell` (`w-5 h-5`), unread counter pill `bg-[#FF3B30] text-white text-[11px] font-bold px-1.5 py-0.5 rounded-full tabular-nums shadow-sm absolute -top-1 -right-1`.
- **Dropdown Panel**: Popover centered/right-aligned berukuran `w-[360px] sm:w-[420px] max-w-[95vw] rounded-[20px] backdrop-blur-2xl bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden`.
- **Header Dropdown**: Judul "Pemberitahuan", tab filter mini (*Semua · Belum Dibaca · Keamanan/Fraud · Otorisasi*), dan tombol "Tandai Semua Dibaca" (warna `#007AFF`, tanpa emoji).
- **Item Notifikasi**: Squircle item berjarak lapang, ikon kategori Lucide dengan background pastel lembut (misal: Fraud = `#FF3B30`/10 text-[#FF3B30], Transaksi = `#34C759`/10 text-[#34C759]), timestamp relatif (`2 menit lalu`, `tabular-nums`), dan penanda unread dot biru muda.

### B. Toast Feedback Melayang (Floating Frosted Glass Toast)

- **Posisi**: Melayang di bagian atas tengah (`fixed top-5 left-1/2 -translate-x-1/2 z-[100]`).
- **Styling**: `inline-flex items-center gap-3 px-4 py-3 rounded-[16px] backdrop-blur-xl bg-white/90 dark:bg-zinc-900/90 border border-black/[0.06] dark:border-white/[0.10] shadow-[0_10px_30px_rgba(0,0,0,0.12)] text-[13px] sm:text-[14px] font-medium text-slate-800 dark:text-zinc-100 animate-slide-down`.
- **Ikon Semantik**: Lucide `check-circle-2` (hijau), `alert-triangle` (kuning/oranye), `shield-alert` (merah), `info` (biru). **Zero Emoji Unicode**.
- **Auto-Dismiss**: Hilang otomatis setelah 3–4 detik dengan transisi fade out halus.

### C. In-App Fraud & Security Alert Banner

- **Tampilan**: Banner bento terisolasi di puncak halaman dashboard/POS:
  ```html
  <div class="rounded-[18px] bg-red-500/10 border border-red-500/20 p-4 sm:p-5 flex items-start gap-4">
      <div class="w-10 h-10 rounded-[12px] bg-red-500/15 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
          <i data-lucide="shield-alert" class="w-5 h-5"></i>
      </div>
      <div class="flex-1 min-w-0">
          <h4 class="text-[14px] sm:text-[15px] font-bold text-red-900 dark:text-red-200">Peringatan Integritas Kasir</h4>
          <p class="text-[12px] sm:text-[13px] text-red-700/90 dark:text-red-300/80 mt-0.5 leading-relaxed">
              Terdeteksi 3x pembatalan nota (void) berturut-turut pada Shift Pagi Cabang Utama.
          </p>
      </div>
      <button type="button" class="px-3.5 py-2 rounded-[10px] bg-red-600 text-white text-[12px] font-semibold hover:bg-red-700 active:scale-95 transition-all shrink-0">
          Periksa Audit Log
      </button>
  </div>
  ```

