# COOCA — Bento Apple HIG Design System (Referensi Lengkap)

Baca file ini sebelum menyentuh view/Blade/CSS apa pun di COOCA. Ini adalah gabungan penuh dari dua draft `AGENT.md`, tanpa duplikasi.

## 1. Tiga Pilar Apple HIG

1. **Clarity (Kejelasan Mutlak)** — *3-Second Glanceability*: setiap halaman harus dipahami maksud, status kunci, dan aksi utamanya dalam 3 detik pertama. Kontras teks minimal 4.5:1 (WCAG 2.1 AA). Ikon Lucide selalu berpasangan dengan label jelas pada aksi kritis.
2. **Deference (Kerendahan Hati Antarmuka)** — UI adalah pelayan konten, bukan pencuri perhatian. Dilarang gradien neon, drop-shadow pekat, atau border tebal gelap. Kanvas abu-abu netral (`#F2F2F7` light / `#000000` dark), kartu putih bersih (`#FFFFFF` light / `#1C1C1E` dark).
3. **Depth (Kedalaman & Layering Halus)** — 4 level elevasi:
   - Level 0 (Canvas): `#F2F2F7` light / `#000000` dark
   - Level 1 (Card Surface): `#FFFFFF` light / `#1C1C1E` dark
   - Level 2 (Elevated Hover Tile): `#F9F9FB` light / `#2C2C2E` dark
   - Level 3 (Modal/Floating Sheet): Frosted Glass — `backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08]`
   - Hairline border: `border-black/[0.06] dark:border-white/[0.08]` (1px), bukan garis pekat tebal.

## 2. Brand Soul & Persona Cooca

Cooca **bukan** template AI generik dan bukan tiruan Silicon Valley — Cooca adalah **Platform Sistem Operasi Bisnis UMKM Nusantara yang Berjiwa, Jujur, Tangguh, dan Presisi**.

1. **Tenang & Berwibawa** — tidak berteriak dengan ornamen/stiker warna-warni. White space dibiarkan lapang sebagai "udara bernapas", bukan ruang yang harus dijejali badge.
2. **Kejujuran & Presisi Fungsional** — setiap piksel/garis/angka punya alasan operasional nyata. Angka penjualan, HPP, dan stok disajikan dengan kepastian matematis (`tabular-nums`, tipografi tebal).
3. **Wibawa Tanpa Gimmick** — hierarki tipografi murni (ukuran, ketebalan, warna) lebih diutamakan daripada membungkus teks ke kapsul.
4. **Kehangatan Manusiawi** — bahasa Indonesia santun, bersahaja, lugas. **DILARANG KERAS** slogan klise AI (*AI-Powered Synergy, Next-Gen Modular Ecosystem, Ultimate Solution*).

## 3. Mandat Anti-AI-Template & Anti-Pill-Abuse

"Pill & Badge Inflation" (membungkus setiap kata/angka ke kapsul `rounded-full`, dot berkedip palsu, "alis kapsul" eyebrow di atas judul) merusak wibawa brand. Aturan mutlak:

- **Larangan Eyebrow Pills**: dilarang keras kapsul/badge di atas H1/H2 (mis. `🟢 AI-Powered Management System • 100% Gratis`, `Ekosistem Modular Terpadu`). Jika konteks section perlu, gunakan **Pure Typographic Overline/Kicker**: teks murni tanpa kapsul (`text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`).
- **Larangan Metric Cluttering**: dilarang menempel pill kecil di samping angka besar (`Rp 0` + pill `📈 ARR Rp 0.0 Juta/thn`). Angka utama berdiri gagah (`text-3xl font-bold tabular-nums`); keterangan sekunder jadi footnote teks polos di bawahnya (`text-[12px] text-black/50 dark:text-white/50`).
- **Larangan Fake Pulse Dots**: `animate-pulse` HANYA untuk status perangkat keras fisik yang benar-benar tersambung (printer thermal Bluetooth, timbangan digital, barcode scanner, koneksi socket server kritis) — bukan pada teks biasa atau rentang waktu.
- **Batasan Pill/Badge**: hanya untuk **status siklus hidup entitas bisnis yang berubah** — status transaksi (`Menunggu Pembayaran` amber, `Lunas` green, `Dibatalkan` gray, `Ditolak` red), status inventori (`Stok Aman` green, `Menipis` amber, `Habis` red), status akun (`Aktif` green, `Ditangguhkan` red, `Superadmin` blue). **Maksimal 1 badge per entitas/baris.** Dilarang untuk teks statis, slogan promosi, rentang waktu, atau kategori tanpa siklus status.
- **Eliminasi Total Elemen Fluff**: menghapus bungkus pill tapi membiarkan teks sampahnya melayang (`Live 6 Bulan Terakhir`, `Organik`, `Terverifikasi`, `Platform-Wide`, `AI-Powered Management System`, `INSIGHT`) **tetap merusak UI**. Jika teks tidak punya nilai fungsional nyata atau sudah tersirat dari konteks — **hapus total elemen dan teksnya**, jangan tinggalkan teks polos.
- **Hierarki via Tipografi Murni**: kontras skala (`text-2xl`/`text-3xl` + `text-[14px]`), kontras bobot (`font-bold` data penting, `font-medium` label, `font-normal` keterangan), kontras warna semantik (`text-black dark:text-white` primer, `/60` sekunder, `/40` label kecil), ruang bernapas 16–24px tanpa dekorasi stiker.
- **Larangan Emoji Mutlak**: TIDAK ADA emoji Unicode (🚀✨💡👥🏆🎟️📦⚡🔥🟢📈💬🏢 dll.) di tombol, judul (H1/H2/H3), bento card/widget KPI, tab bar, dialog konfirmasi, alert banner, badge status, atau kolom tabel — di seluruh UI, termasuk contoh microcopy di dokumen lama yang menyertakan emoji (`💡 Tenang...`, `📲 Kirim Manual...`) harus diterapkan tanpa emoji, hanya dengan Lucide icon (`<i data-lucide="...">`) atau SVG inline fungsional.

## 4. Geometri Squircle & Continuous Corner Radius

- Outer Bento Card: `rounded-[20px]`/`rounded-[24px]` desktop, `rounded-[16px]` mobile
- Inner Tile/Sub-Widget: `rounded-[14px]`/`rounded-[16px]`
- Tombol & Input: `rounded-[12px]`/`rounded-[14px]`
- Status Badge/Avatar/Pills: `rounded-full`
- Mobile Bottom Sheet: `rounded-t-[28px]` (varian 26px juga dipakai untuk action sheet — pilih konsisten per konteks)
- **Dilarang keras**: `rounded-none`, `rounded-sm`, sudut kaku 2–4px.

## 5. Mikro-Interaksi Taktil

```html
class="transition-all duration-150 ease-out active:scale-[0.98] hover:opacity-95"
```
- Touch target minimum **44×44px**, wajib **48–52px** untuk tombol aksi utama di layar sentuh mobile kasir.
- Jarak antar tombol penting minimal **12–16px** (`gap-2.5`–`gap-3`).

## 6. Mandat Anti-Excessive-Text & Microcopy

**Dilarang** menambahkan: paragraf penjelasan panjang tanpa kebutuhan operasional, deskripsi berulang yang menjelaskan hal yang sudah jelas dari judul, subtitle pada tiap kartu tanpa fungsi pembeda status, helper text yang tidak membantu keputusan, teks promosi di halaman transaksi/operasional, jargon teknis (*SKU, BOM, COGS, Void, Tenant Context*), judul panjang yang bisa diringkas 2–3 kata, empty state berkalimat panjang (cukup: *"Belum ada produk"* + tombol aksi).

Prioritas konten UI: **Wajib** (agar user paham data/selesaikan tugas) → **Membantu** (konteks penting/cegah kesalahan) → **Opsional** (hanya di modal sheet saat diminta) → **Tidak perlu** (hapus seketika).

Contoh microcopy lugas:
| Kurang baik | Lebih baik |
|---|---|
| "Silakan melakukan proses penyimpanan data produk yang telah Anda masukkan." | **"Simpan Produk"** |
| "Anda belum memiliki data produk yang dapat ditampilkan pada halaman ini." | **"Belum ada produk."** |
| "Apakah Anda benar-benar yakin ingin melanjutkan proses penghapusan data ini?" | **"Hapus produk ini?"** |

**Tombol Aksi Lugas**: tombol adalah pemicu aksi, bukan tempat mengulang judul kartu/halaman.
- Di dalam form/modal → satu kata kerja murni: **Simpan · Hapus · Edit/Ubah · Lihat · Batal · Kirim · Salin**.
- Pengecualian terbatas (maks. 2 kata) hanya untuk CTA utama index di luar form/tabel: *"Tambah Produk"*, *"Ekspor Excel"*, *"Cetak Struk"*.
- Contoh salah: *"Simpan Pengaturan Google & Sistem"* → benar: *"Simpan"*. *"Lakukan Proses Penghapusan Akun"* → *"Hapus"*.

## 7. Filosofi Zero-Manual / Self-Explanatory UI

Target pengguna: Boomer (50–65+) & Milenial Akhir (40+), gaptek, mudah cemas dengan istilah teknis, rentan salah sentuh.

1. **Tombol aksi utama mencolok & berkata kerja spesifik** — Primary System Blue (`bg-[#007AFF] text-white`) dengan teks jelas (*"+ Tambah Barang Baru"*, bukan hanya ikon `+`). Dilarang tombol aksi kritis hanya ikon tanpa teks.
2. **Kaidah 3 Kolom Pokok (Anti-Intimidasi Form)** — form utama (mis. Tambah Produk) hanya menampilkan **3 input inti**: Nama Barang/Jasa, Kategori, Harga Jual (Rp). Seluruh opsi teknis (Barcode, Resep BOM, Modal HPP, Min Stok Gudang) disembunyikan di akordeon opsional (`⚙️ Atur Modal Beli, Stok Gudang & Resep (Opsional) ▾` — tanpa emoji pada implementasi nyata, gunakan ikon Lucide).
3. **Microcopy Penenang Jiwa (No-Panic Feedback)** — di setiap dialog konfirmasi/hapus, sertakan kalimat penenang menggunakan ikon Lucide `info`, tanpa emoji: *"Tenang, riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan."*, *"Anda dapat mengubah kembali pilihan ini kapan saja."* Tombol destruktif wajib konfirmasi dua langkah.
4. **Format Ribuan Otomatis** — input nominal wajib format pemisah ribuan real-time (`Rp 250.000`).
5. **Bahasa Indonesia lugas, bebas jargon Inggris**: *HPP/COGS* → Modal Pokok/Biaya Bahan; *Stock Reversal* → Pengembalian Bahan; *Void Transaction* → Pembatalan Transaksi; *Tenant Context* → Pemisahan Toko.

## 8. Matriks Tipografi Lintas Perangkat

Font stack: `-apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif`

| Peran | Mobile (<640px) | Tablet (640–1023px) | Desktop (1024px+) | Weight | Line Height |
|---|---|---|---|---|---|
| Large Title | 22–24px | 28px | 32–34px | 700 | 1.2 |
| Title 1/Section | 20px | 24px | 24px | 600 | 1.25 |
| Title 2/Card | 17–18px | 18px | 20px | 600 | 1.3 |
| Headline | 16px | 16px | 16px | 600 | 1.35 |
| Body | 15–16px | 15–16px | 14–15px | 400 | 1.5 |
| Form Input/Select | **16px (MUTLAK)** | 14–15px | 14px | 400 | 1.4 |
| Subheadline | 14px | 14px | 13–14px | 500 | 1.4 |
| Footnote/Helper | 13px | 13px | 12–13px | 400 | 1.35 |
| Caption/Badge | 12px | 12px | 11–12px | 600 | 1.2 |
| Angka/Moneter | `tabular-nums` semua breakpoint | | | 600–700 | leading-none |

- **Anti Auto-Zoom iOS**: `<input>`/`<select>`/`<textarea>` mobile wajib `text-[16px] sm:text-[14px]` atau lebih besar — di bawah 16px memicu auto-zoom Safari yang merusak layout.
- **Tabular Figures wajib** (`tabular-nums`) untuk: Rupiah, jumlah stok, nomor nota/faktur, tanggal & jam transaksi, persentase & diskon.
- Hanya 4 bobot: 400/500/600/700. **Dilarang** `font-black`/900. Dilarang all-bold dalam satu kartu — kontraskan label (400/500, abu-abu) vs nilai utama (600/700, hitam/putih).

## 9. Information Hierarchy

```
Primary Purpose → Primary Information → Primary Action → Secondary Information → Optional Detail
```
Pertanyaan panduan: apa yang harus diketahui user dalam 3 detik pertama? Apa yang harus dilakukan sekarang? Apa risiko jika salah pilih? Info apa yang cukup di modal sheet saat diminta? Jangan menampilkan semua info dengan bobot visual yang sama.

## 10. Sistem Spacing Adaptif (8pt Grid)

| Area | Mobile (<640px) | Tablet (640–1023px) | Desktop (1024px+) |
|---|---|---|---|
| Jarak elemen kecil | 6–8px | 8px | 8px |
| Jarak antar kontrol/tombol | 10–12px | 12–16px | 12–16px |
| Grid gap antar kartu | `gap-3` (12px) | `gap-4` (16px) | `gap-4 sm:gap-5`/`gap-6` (16–24px) |
| Padding dalam kartu | `p-3.5`–`p-4` (14–16px) | `p-5` (20px) | `p-6` (24px) |
| Jarak antar section | 16–20px | 24px | 24–32px |
| Margin horizontal halaman | `px-3` | `px-6` | `px-8 max-w-[1440px] mx-auto` |
| Safe-area bawah | **`pb-28`–`pb-32` (MUTLAK)** | `pb-16` | `pb-10`/`pb-12` |

Dilarang `p-6`/`p-8` pada kartu mobile (memotong 48–64px dari layar 360–390px). Checklist anti-padat: teks mepet border? tambah padding. Tombol saling menempel? beri `gap-2.5`–`gap-3`. Terlalu banyak elemen berjejal di satu layar? gunakan progressive disclosure/modal sheet. Bebas scroll horizontal di 360px?

## 11. Blueprint Responsivitas Bento & Anti-Overflow

### Smartphone 360–639px (Zero-Breakage Rules)
- Dilarang `w-[...]`/`min-w-[...]` statis > 300px — pakai `w-full max-w-full`.
- Semua teks dalam flex container wajib `min-w-0` + `truncate`/`break-words`.
- Grid: form/detail/tabel kompleks → `grid-cols-1`. Stat/KPI ringkas → maksimal `grid-cols-2` (jangan tumpuk `grid-cols-1` monoton untuk kartu metrik — berpasangan 2 kartu per baris dengan `col-span-2` untuk hero card).
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
- **Perbaikan mobile DILARANG merusak tampilan desktop yang sudah baik — preservasi 100%.**

### Mandat Anti-Kerusakan Dimensi SVG
- Setiap `<svg>` wajib atribut eksplisit `width`/`height` DAN kelas presisi (`w-[18px] h-[18px]` atau `w-4 h-4`/`w-5 h-5`). Dilarang pecahan non-standar (`w-4.5`) tanpa daftar di `tailwind.config`. Mencegah SVG "meledak" menutupi sidebar.

### Mandat Kontensi Sidebar
- Sidebar wajib `overflow-y-auto overflow-x-hidden`. Dilarang scrollbar horizontal atau floating widget yang menutupi navigasi.

## 12. Layout Bagian Per Bagian (Sidebar, Topbar, Body, Footer)

### A. Sidebar (macOS Sonoma Source List)
- **Lebar baku desktop `w-72` (288px)** — dilarang sempit `w-64`. Offset kanvas utama `lg:pl-72`.
- **Mandat Satu Baris Mutlak**: item navigasi `h-10 px-3 rounded-[12px] flex items-center gap-2.5`; label wajib `whitespace-nowrap truncate min-w-0 flex-1`. Dilarang keras teks menu terbungkus 2–3 baris. Nomenklatur ringkas (*"Langganan & Billing"*, *"Rekening Bank"*, *"Token AI"*, *"WhatsApp Gateway"*, *"Google OAuth"*, *"Server SMTP"*).
- **Scrollbar ramping anti-Windows**: `.sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.15) transparent; }` (dark: `rgba(255,255,255,0.18)`), webkit lebar 4px, thumb `rounded-full` semi-transparan tanpa tombol panah. Dilarang scrollbar native Windows 17px.
- Material: `backdrop-blur-2xl bg-[#F2F2F7]/80 dark:bg-[#1C1C1E]/80 border-r border-black/[0.06] dark:border-white/[0.08]`. Dilarang `bg-gray-900`/`bg-slate-800` solid.
- Header brand: squircle `w-9 h-9 rounded-[11px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6]`.
- Grup nav: label `px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]`.
- State aktif: `bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold`. State inaktif: `text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]`.
- Badge counter: pill bulat `px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 ml-2` dengan warna semantik.
- Footer profil: kartu bento `p-2.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]`, nama user `min-w-0 flex-1 truncate`.

### B. Topbar (Zero Top-Edge Clipping Directive)
- Tinggi aman: `h-[68px] sm:h-[72px]` atau `min-h-[64px] py-2 sm:py-2.5 px-4 sm:px-8 sticky top-0 z-30 backdrop-blur-xl bg-[#F2F2F7]/75 dark:bg-[#000000]/75 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4`.
- **Dilarang keras** baris eyebrow terpotong di tepi atas viewport — wajib `<div class="min-w-0 flex flex-col justify-center">` dengan eyebrow `leading-none mb-1`, `navigationTitle` `leading-snug`, `navigationSubtitle` `leading-none mt-0.5`.
- Skala: eyebrow 10.5–11px uppercase (`text-[#8E8E93] dark:text-[#98989D]`); title 19–21px font-extrabold (`tracking-tight truncate leading-snug`); subtitle 12–13px (`text-black/50 dark:text-white/50 hidden md:block`).
- Segmented switcher tema: pill `p-1 rounded-full bg-black/[0.05] dark:bg-white/[0.08]`. Pemisah vertikal hairline `w-[1px] h-5 bg-black/[0.08] dark:bg-white/[0.1]`.
- Primary CTA: `bg-[#007AFF] text-white rounded-[14px] px-4 py-2 font-semibold shadow-sm hover:brightness-105 active:scale-[0.98]`.

### C. Content Body
- `max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8`, kartu `rounded-[20px]`/`rounded-[24px]`.
- Tabel dibungkus kartu bento dengan `overflow-x-auto scrollbar-thin`.

### D. Footer (Dual-Mode Architecture)
- **Mobile/Tablet (`md:hidden`)** — **Full-Style Floating Bottom Navigation Bar (iOS 18)**: `fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 pb-[env(safe-area-inset-bottom)]`, material `backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 rounded-[24px]`, target sentuh 48–52px, tombol tengah menonjol (*Elevated Center Action Trigger*) gradasi System Blue untuk aksi kasir tercepat.
- **Desktop** — Clean Minimalist Hairline Footer: `border-t border-black/[0.06] dark:border-white/[0.08] py-4 px-6 lg:px-8 text-[12px]`, menampilkan status koneksi sistem & versi aplikasi.

## 13. Harmoni Warna Semantik & Dual-Mode

- System Blue `#007AFF`/`#0A84FF` · Green `#34C759`/`#30D158` · Orange `#FF9500`/`#FF9F0A` · Red `#FF3B30`/`#FF453A` · Indigo `#5856D6`/`#5E5CE6` · Purple `#AF52DE`/`#BF5AF2`.
- Light: background `#F2F2F7`, kartu `#FFFFFF`/`bg-white/80 backdrop-blur-xl`, teks utama `#000000`, sekunder `text-black/60`, hairline `border-black/[0.06]`.
- Dark: background `#000000`/`#1C1C1E`, kartu `#1C1C1E`/`#2C2C2E`, teks utama `#FFFFFF`, sekunder `dark:text-white/60`, hairline `border-white/[0.08]`.
- Dilarang fill solid jenuh di kartu/banner — gunakan tinted badge pill (`bg-{color}/12 text-{color}`) hanya untuk status siklus hidup (lihat §3).

## 14. Modal-First Standar Full Layout XXL & Responsif Multi-Device

- Halaman index: Create/Show/Edit **wajib** modal sheet, zero page-jumps. Filter, pencarian, sorting, posisi pagination tetap tersimpan saat modal ditutup.
- **Mandat Full Layout XXL**: Dilarang modal sempit (`max-w-md` atau `max-w-lg`) untuk form operasional ERP/transaksi/master-detail.
- **Desktop (>= 1024px)**: **Full Layout XXL Centered Bento Dialog** (`w-full max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl mx-auto rounded-[24px] max-h-[90vh] flex flex-col`), frosted glass (`backdrop-blur-2xl bg-white/95 dark:bg-[#1C1C1E]/95 border border-black/[0.06] dark:border-white/[0.08] shadow-2xl`), layout multi-kolom Bento (8 kolom utama + 4 kolom ringkasan), sticky header & sticky footer action bar.
- **Tablet (640px – 1023px)**: **Centered Responsive Bento Modal** (`w-full max-w-[92vw] md:max-w-3xl lg:max-w-4xl mx-auto rounded-[22px] max-h-[90vh] flex flex-col`), layout 2-kolom seimbang, touch target tombol 44px–48px.
- **Mobile (< 640px)**: **Apple Full-Responsive Bottom Sheet** dari bawah layar (`w-full inset-x-0 bottom-0 rounded-t-[28px] max-h-[94vh] flex flex-col overflow-hidden`), grab bar (`w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 shrink-0`), input font minimal 16px (`text-[16px] sm:text-[14px]`) anti auto-zoom, sticky bottom action bar dengan safe area padding (`pb-[max(1rem,env(safe-area-inset-bottom))]`).

## 15. Inline Quick-Add `[ + ]`

Dropdown master relasi (Kategori, Satuan, Supplier, Pelanggan, Rekening Bank, Akun Kas) wajib tombol `[ + ]` di samping:

```html
<div class="flex items-center gap-2">
  <div class="relative flex-1">
    <select class="w-full text-[16px] sm:text-[14px] rounded-[12px] ...">...</select>
  </div>
  <button type="button" class="w-11 h-11 flex-shrink-0 rounded-[12px] bg-blue-50 dark:bg-blue-900/30 text-[#007AFF] font-bold text-lg flex items-center justify-center active:scale-95 transition-all" title="Tambah Cepat">+</button>
</div>
```

Perilaku wajib: buka mini modal sheet tanpa menghilangkan isian form utama → simpan via AJAX (`fetch()`) → injeksi opsi baru ke `<select>` → pilih otomatis (*auto-select*) → tutup mini modal, lanjutkan form utama tanpa reset data yang sudah diketik.

## 16. Mandat Konsolidasi UI (UI Unification Directive)

> **Aturan Emas**: Jika dua/tiga antarmuka saling melengkapi dan mengelola entitas yang sama, WAJIB digabung menjadi satu halaman berbasis Tab atau Master-Detail.

| Halaman Terpisah (Pola Lama) | Penggabungan (Pola Baru) | Manfaat |
|---|---|---|
| `Pelanggan` (`/customers`) + `CRM & Member` (`/crm/members`) | **Pusat Pelanggan & Loyalitas** (`/customers`) dengan tab: Semua Pelanggan · Member & Poin · Voucher Diskon | Satu tempat untuk utang piutang, kontak WhatsApp, poin hadiah |
| `Katalog Produk` (`/products`) + `Jasa & Layanan` (`/services`) | **Katalog Usaha** (`/products`) dengan segmented control: Semua · Barang Fisik (Ada Stok) · Jasa/Servis (Bebas Stok) | Tidak bingung antara menu jasa dan barang; input menyesuaikan tab |
| `Kas & Rekening Bank` (`/finance/cash-bank`) + `Buku Kas & Ledger` (`/finance/cash-bank/ledger`) | **Pusat Kas & Bank** (`/finance/cash-bank`) — atas: saldo & tombol cepat kas masuk/keluar; bawah: tabel mutasi terpadu | Owner langsung lihat kas laci, saldo bank, dan mutasi di satu layar |
| `Master Data Supplier` (`/suppliers`) berdiri sendiri | Pindahkan ke grup navigasi **Pembelian & Vendor** (berdampingan PO, Tagihan, Retur) | Tidak perlu cari menu Supplier di bawah dashboard |

Penggabungan wajib disertai rute redirect/alias untuk URL lama dan melewati Interactive Confirmation Gate sebelum eksekusi.
