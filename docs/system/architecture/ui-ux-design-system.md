# Arsitektur UI/UX Design System: Apple HIG & Bento Grid (v2.0)

> **Status:** VERIFIED & COMPLETE  
> **Komponen Kunci:** Tailwind CSS, Alpine.js, Apple SF Pro typography, Lucide/SF-style SVG icons  
> **Rujukan Utama:** [`docs/prompt.md`](file:///c:/laragon/www/cooca_core/docs/prompt.md) & [`AGENTS.md`](file:///c:/laragon/www/cooca_core/AGENTS.md)

---

## 1. Filosofi & Pilar Arsitektur Antarmuka

Arsitektur antarmuka Cooca v2.0 dibangun di atas estetika **Apple Human Interface Guidelines (macOS Sonoma & iOS 18)** yang dipadukan dengan tata letak modular **Bento Grid** dan mandat **Ergonomi Ramah Boomer & Pengguna Gaptek** (usia 40–65+ tahun).

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       APPLE HIG DESIGN SYSTEM (v2.0)                        │
├─────────────────────────────────────────────────────────────────────────────┤
│  • Clarity over Decoration       • Materials & Vibrancy (Frosted Glass)     │
│  • Continuous Squircle Radius   • Dynamic Type Scale & Tabular Numbers      │
│  • Semantic Color Governance    • Zero-Manual & Background Automation       │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Keseragaman Konsep UI Multi-Device (Unified Bento Philosophy)

Antarmuka pada **Smartphone Layar Kecil (360px–430px)**, **Tablet Kasir POS (768px–1024px)**, dan **Desktop/Laptop (1280px+)** **WAJIB MENGGUNAKAN KONSEP UI YANG SAMA PERSIS**:
- **Bukan Sistem Kelas Dua**: Antarmuka mobile dan tablet bukan versi yang dikurangi fiturnya atau layout berlainan konsep, melainkan adaptasi responsif luwes dari satu bahasa desain terpadu.
- **Konsistensi Token**: Sudut squircle kontinu (`rounded-[18px]`–`rounded-[24px]`), material *frosted glass* (`backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08]`), dan warna semantik Apple (System Blue `#007AFF`, System Green `#34C759`, System Orange `#FF9500`, System Red `#FF3B30`).

---

## 3. Komponen Full-Style Floating Bottom Navigation Bar (iOS 18)

Pada perangkat mobile dan tablet ringkas (`md:hidden`), navigasi bawah disajikan dalam bentuk bilah mengambang (*floating bar*) bergaya Apple iOS 18:
- **Spesifikasi CSS**: `fixed bottom-3 inset-x-3 sm:inset-x-6 z-40 pb-[env(safe-area-inset-bottom)] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.08] dark:border-white/[0.1] shadow-[0_12px_36px_rgba(0,0,0,0.14)] rounded-[24px] px-3 py-2`.
- **Target Sentuh**: Minimal 48px–52px untuk kenyamanan jempol.
- **Elevated Center Quick Action**: Tombol tengah menonjol bulat dengan gradasi biru Apple (`w-12 h-12 rounded-full bg-gradient-to-tr from-[#007AFF] to-[#0051D5] -mt-6 ring-4 ring-white dark:ring-[#1C1C1E]`) untuk akses cepat ke POS kasir atau tambah transaksi baru.

---

## 4. Mandat Master-Detail Pop-Up / Modal Sheet First pada Index

> **Aturan Wajib:** *"Jika suatu halaman Index memiliki aksi Show (Detail), Create (Tambah Baru), atau Edit (Ubah), seluruh aksi tersebut wajib disajikan dalam bentuk pop-up / modal sheet langsung di halaman index tanpa pernah me-redirect ke URL terpisah."*

### Rationale & Manfaat:
1. **Zero Navigation Jumps**: Pengguna Boomer tidak tersesat atau panik kehilangan konteks tabel.
2. **Preservasi State 100%**: Kata kunci pencarian, filter status, filter kategori, dan nomor pagination tetap aktif di latar belakang tanpa reload.
3. **Adaptasi Bentuk Modal (Wajib Full Layout XXL & Responsif Multi-Device)**:
   - **Desktop (>= 1024px)**: Mengambang di tengah layar (**Full Layout XXL Centered Bento Dialog** `max-w-5xl` s/d `max-w-7xl` / `max-w-[95vw] rounded-[24px] max-h-[90vh]`) dengan backdrop blur lembut `bg-black/40 backdrop-blur-sm`. Memberikan ruang lapang bagi arsitektur Bento multi-kolom (8 kolom input/detail + 4 kolom metrik ringkasan).
   - **Tablet (640px – 1023px)**: Mengambang di tengah layar (**Centered Responsive Bento Modal** `max-w-3xl` s/d `max-w-4xl rounded-[22px] max-h-[90vh]`) dengan layout 2-kolom modular seimbang, pas untuk navigasi sentuh kasir/owner.
   - **Mobile (< 640px)**: Berubah dinamis menjadi **Apple Full-Responsive Bottom Sheet** (`w-full inset-x-0 bottom-0 rounded-t-[28px] max-h-[94vh] flex flex-col overflow-hidden`) lengkap dengan indikator handle bar geser (`w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5`), input minimal 16px (anti-auto-zoom), dan sticky bottom action bar dengan safe-area padding.

---

## 5. Mandat Inline Quick-Add Trigger `[ + ]` pada Dropdown / Select

> **Aturan Wajib:** *"Setiap dropdown relasi data master (Kategori, Satuan, Supplier, Pelanggan, Rekening Bank, dsb.) wajib memiliki tombol `[ + ]` tepat di samping dropdown untuk menambah data baru secara instan tanpa me-reload form utama."*

### Mekanisme Eksekusi (Alpine.js & AJAX):
1. Pengguna menekan tombol squircle `[ + ]` (`w-11 h-11 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF]`).
2. Muncul **pop-up mini-modal** di atas form utama tanpa menghapus isian data form utama yang sudah diketik.
3. Form baru disimpan via AJAX (`fetch('/.../quick-store')`).
4. Setelah respon sukses, sistem secara otomatis:
   - Menginjeksi opsi baru ke dalam data list array kategori.
   - Memilih opsi baru tersebut (`x-model="activeItem.category_id"`).
   - Menutup pop-up mini-modal.
5. Pengguna melanjutkan sisa pengisian form utama tanpa jeda.

---

## 6. Standar Ergonomi Boomer & Inklusivitas (Usia 40–65+ Tahun)

1. **Anti Auto-Zoom Browser**: Seluruh input teks, select, dan textarea pada mobile wajib menggunakan font minimal 16px (`text-[16px] sm:text-[14px]`).
2. **Tombol Ekstra Lega**: Tombol aksi utama tinggi minimal 48px–52px dengan feedback taktil `active:scale-[0.97] active:opacity-80`.
3. **Kaidah 3 Input Pokok**: Form awal hanya menampilkan 3 isian wajib (Nama, Kategori, Harga); opsi lanjutan dilipat di akordeon opsional.
4. **Pemisah Ribuan Otomatis**: Input nominal memformat pemisah ribuan secara real-time (`Rp 100.000`).
5. **Bahasa Tanpa Jargon**: Penggunaan istilah ramah toko UMKM (Resep Bahan Baku, Modal Beli, Batalkan Pesanan).
6. **Pesan Penenang Jiwa**: Dialog hapus menyertakan kalimat kepastian bahwa riwayat masa lalu aman tersimpan.

---

## 7. Ketentuan Styling Apple untuk Sidebar Menu & Topbar Header (macOS Sonoma & iOS 18)

### 7.1 Sidebar Menu (macOS Sonoma Source List & iPadOS Split View)
* **Struktur & Material**: Bilah samping lebar `w-72` menggunakan material translucent frosted glass (`backdrop-blur-2xl bg-[#F2F2F7]/80 dark:bg-[#1C1C1E]/80 border-r border-black/[0.06] dark:border-white/[0.08]`). Dilarang keras menggunakan sidebar hitam solid (`bg-gray-900`/`bg-slate-800`).
* **Header Brand**: Dilengkapi logo ber-squircle continuous (`w-9 h-9 rounded-[11px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white shadow-sm shadow-[#007AFF]/25`) serta badge status semantik (`bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20`).
* **Grup Inset & Tipografi Sub-menu**: Judul seksi menu menggunakan huruf kapital berukuran 11px tebal (`text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] px-3 pt-4 pb-1`).
* **Link Navigasi Squircle Continuous**:
  - **State Aktif**: `bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold rounded-[12px] h-[40px] px-3`. Ikon SVG aktif berwarna putih solid.
  - **State Inaktif**: `text-[#3C3C43]/80 dark:text-[#EBEBF5]/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] rounded-[12px] h-[40px] px-3 font-medium text-[13.5px]`.
* **Badge Counter Semantik**: Angka notifikasi disajikan dalam badge pill bulat (`px-2 py-0.5 text-[11px] font-semibold rounded-full bg-[#FF3B30] text-white` untuk approval/alert mendesak).
* **Footer Kartu Pengguna Bento**: Profil pengguna dan tombol logout cepat disematkan di dasar sidebar dalam kartu bento ber-squircle (`p-2.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]`).

### 7.2 Topbar / Page Header (macOS Sonoma Toolbar & iOS 18 Navigation Bar)
* **Struktur & Material**: Bar melekat mengambang (`sticky top-0 z-30 backdrop-blur-xl bg-[#F2F2F7]/75 dark:bg-[#000000]/75 border-b border-black/[0.06] dark:border-white/[0.08] h-16`). Dilarang keras menggunakan banner raksasa berwarna gelap atau gradasi mencolok yang menyita viewport kerja.
* **Hierarki Tipografi (Apple Dynamic Type Scale)**:
  - *Eyebrow context*: `text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]`.
  - *Navigation Title*: `text-[20px] sm:text-[22px] font-bold tracking-tight text-[#1C1C1E] dark:text-[#F2F2F7]`.
  - *Navigation Subtitle*: `text-[13px] text-[#3C3C43]/70 dark:text-[#EBEBF5]/70 font-normal`.
* **Theme Pill Controller**: Pengalih tema Light/Dark/System disajikan dalam bentuk pill terpadu (`p-1 rounded-full bg-black/[0.05] dark:bg-white/[0.08]`).
* **Pemisah Hairline Vertikal**: Garis tipis 1px tinggi 20px (`w-[1px] h-5 bg-black/[0.08] dark:bg-white/[0.1] mx-1`).
* **Tombol Aksi Utama System Blue**: Primary CTA menggunakan System Blue Apple (`bg-[#007AFF] text-white rounded-[14px] px-4 py-2 text-[13.5px] font-semibold shadow-sm hover:brightness-105 active:scale-[0.98]`).

### 7.3 Content Body Canvas (Bento Grid Architecture)
* **Kanvas & Max-Width Containment**: Kanvas utama dibatasi pada `max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8` agar keterbacaan data tetap optimal pada monitor ultra-wide (1440px–1920px+).
* **Modular Squircle Cards**: Elemen antarmuka dikemas dalam kartu modular squircle (`rounded-[20px]`–`rounded-[24px]` dengan material `backdrop-blur-xl bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] shadow-sm`).
* **Horizontal Table Containment**: Setiap tabel transaksi dibungkus dalam kartu bento ber-overflow horizontal terisolasi (`overflow-x-auto scrollbar-thin`) dengan header kolom sticky atau styling subtil (`bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-[#8E8E93]`), mencegah tabel merusak lebar viewport.
* **Bottom Safe Clearance**: Kanvas body wajib memiliki padding bawah aman `pb-28 lg:pb-12` agar kartu, pagination, atau form aksi terbawah tidak tertutup Floating Bottom Bar pada mobile/tablet.

### 7.4 Dual-Mode Footer Architecture
Arsitektur layout Cooca mengadopsi footer adaptif dua mode:
1. **Mode Mobile & Tablet Ringkas (`md:hidden`)**: Menggunakan **Full-Style Floating Bottom Navigation Bar (iOS 18)** mengambang di bawah layar (`fixed bottom-3 inset-x-3 sm:inset-x-6 z-40`), frosted glass `backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 rounded-[24px]`, target sentuh jempol 48–52px, dan tombol tengah menonjol *Elevated Center Action Button* bergradasi System Blue.
2. **Mode Desktop & Laptop (`hidden md:block`)**: Menggunakan **Clean Minimalist Hairline Footer** (`mt-auto border-t border-black/[0.06] dark:border-white/[0.08] py-4 px-6 lg:px-8 text-[12px] text-[#8E8E93] dark:text-[#98989D]`) yang memuat:
   - Status koneksi platform real-time (`🟢 Sistem Normal & Terhubung`).
   - Indikator versi aktif dan hak cipta platform.
   - Tautan navigasi utilitas (Bantuan Operasional, Dokumentasi Sistem).

---

## 8. Standar Tipografi SF Pro & Skala Ukuran Font (Dynamic Type Scale)

Tipografi Cooca mengadopsi standar Apple Typography untuk keterbacaan instan tanpa kelelahan visual:
- **Font Stack**: `-apple-system, "SF Pro Text", "SF Pro Display", "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`.
- **Tabular Figures Wajib (`tabular-nums`)**: Seluruh nilai uang (Rp), jumlah kuantitas stok, persentase diskon/pajak, tanggal/jam, dan nomor nota transaksi WAJIB menyertakan `tabular-nums` agar angka sejajar rapi vertikal.
- **Batasan 4 Bobot Font**: Hanya diperbolehkan memakai Regular (400), Medium (500), Semibold (600), dan Bold (700). Dilarang keras menggunakan font-black (900) yang intimidatif.
- **Anti Auto-Zoom Browser Mobile**: Seluruh `<input>`, `<select>`, dan `<textarea>` pada mobile WAJIB berukuran minimal **16px** (`text-[16px] sm:text-[14px]`).

### Matriks Skala Ukuran Font Apple HIG Lintas Perangkat:
| Kategori Tipografi | Mobile (<640px) | Tablet (640–1023px) | Desktop (1024px+) | Weight | Line Height | Penggunaan Utama |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Large Title** | `text-[22px]–text-2xl (24px)` | `text-3xl (28px)` | `text-3xl–text-4xl (32–34px)` | Bold (700) | `leading-tight (1.2)` | Hero visual & dashboard metrics |
| **Title 1** | `text-xl (20px)` | `text-2xl (24px)` | `text-2xl (24px)` | Semibold (600) | `leading-snug (1.25)` | Judul halaman indeks utama |
| **Title 2** | `text-[17px]–text-lg (18px)` | `text-lg (18px)` | `text-xl (20px)` | Semibold (600) | `leading-snug (1.3)` | Navigation Title Topbar & Header Modal |
| **Title 3 / Card** | `text-[16px]–text-[17px]` | `text-base–text-lg (18px)` | `text-lg (18px)` | Semibold (600) | `leading-snug (1.3)` | Judul kartu Bento & Card Header |
| **Headline** | `text-[16px]` | `text-[16px]` | `text-[16px]` | Semibold (600) | `leading-normal (1.35)` | Item list penting, nama produk, tombol CTA |
| **Body** | `text-[15px]–text-[16px]` | `text-[15px]–text-[16px]` | `text-[14px]–text-[15px]` | Regular (400) | `leading-relaxed (1.5)` | Teks deskripsi, paragraf bacaan nyaman |
| **Form Input / Select** | **`text-[16px]` (MUTLAK)** | `text-[14px]–text-[15px]` | `text-[14px]` | Regular (400) | `leading-normal (1.4)` | **Wajib 16px di mobile (anti auto-zoom iOS)** |
| **Subheadline** | `text-sm (14px)` | `text-sm (14px)` | `text-[13px]–text-sm (14px)` | Medium (500) | `leading-normal (1.4)` | Keterangan sekunder di bawah nama produk |
| **Footnote / Helper** | `text-[13px]` | `text-[13px]` | `text-[12px]–text-[13px]` | Regular (400) | `leading-normal (1.35)` | Petunjuk form, keterangan tambahan |
| **Caption / Badge** | `text-xs (12px)` | `text-xs (12px)` | `text-[11px]–text-xs (12px)` | Semibold (600) | `leading-none (1.2)` | Status pills, label kolom tabel |
| **Angka / Moneter** | **`tabular-nums`** | **`tabular-nums`** | **`tabular-nums`** | 600–700 | `leading-none` | **Rupiah, kuantitas stok, diskon, tanggal** |


---

## 9. Sistem Harmoni Warna Semantik & Dual-Mode (Light Mode vs Dark Mode)

Cooca menggunakan warna fungsional terstandardisasi Apple Human Interface Guidelines:
- **System Blue** (`#007AFF` Light / `#0A84FF` Dark): Primary Action, tombol Simpan/Tambah, link navigasi aktif, fokus border form.
- **System Green** (`#34C759` Light / `#30D158` Dark): Status sukses, transaksi lunas, stok aman, kas masuk.
- **System Orange** (`#FF9500` Light / `#FF9F0A` Dark): Peringatan stok menipis, status pending, bayar tempo.
- **System Red** (`#FF3B30` Light / `#FF453A` Dark): Status kritis, stok habis, tombol hapus/batal, utang jatuh tempo.
- **System Indigo** (`#5856D6` Light / `#5E5CE6` Dark): Laporan analitik, grafik performa, kategori khusus.
- **System Purple** (`#AF52DE` Light / `#BF5AF2` Dark): Program loyalitas, voucher diskon, pelanggan VIP.

### Harmoni Light Mode vs Dark Mode:
| Token Antarmuka | Spesifikasi Light Mode | Spesifikasi Dark Mode | Catatan Estetika |
| :--- | :--- | :--- | :--- |
| **Canvas Background** | `#F2F2F7` (Apple Light Gray Canvas) | `#000000` / `#1C1C1E` | Menghilangkan silau mata |
| **Bento Card Surface** | `#FFFFFF` / `bg-white/80 backdrop-blur-xl` | `#1C1C1E` / `bg-[#1C1C1E]/80 backdrop-blur-xl` | Frosted glass material |
| **Border / Hairline** | `border-black/[0.06]` (1px halus) | `border-white/[0.08]` (1px halus) | Pemisah tajam tanpa kontras tajam |
| **Teks Primer** | `#000000` / `#1C1C1E` (Opaque) | `#FFFFFF` / `#F2F2F7` (Opaque) | Kontras teks maksimal |
| **Teks Sekunder** | `rgba(60,60,67,0.70)` | `rgba(235,235,245,0.70)` | Keterangan / metadata |
| **Teks Tersier / Placeholder** | `rgba(60,60,67,0.40)` | `rgba(235,235,245,0.40)` | Hint & placeholder form |
| **Badge Tinted Background** | `bg-{color}/10` atau `bg-{color}/12` | `bg-{color}/15` atau `bg-{color}/20` | Dilarang background solid jenuh |

---

## 10. Sistem Spacing & White Space 8pt Grid (Ruang Bernapas Bebas Padat)

Penerapan ritme spasi 8pt grid konsisten menjamin tampilan tetap santai dan bebas dari kepadatan (*clutter-free*):
1. **Jarak Antar Kartu Bento**: `gap-4 sm:gap-5 lg:gap-6` (16px–24px) memberikan batas visual tanpa garis tebal kaku.
2. **Padding Internal Kartu Bento**: `p-4 sm:p-5 lg:p-6` (16px–24px) memastikan teks atau grafik memiliki margin lega dari tepi kartu.
3. **Pemisah Vertikal Antar-Seksi**: `space-y-6 sm:space-y-8` (24px–32px) memberikan ritme baca yang tidak melelahkan mata.
4. **Jarak Aman Tombol Sentuh**: Jarak antar tombol interaktif minimal 12px–16px untuk mencegah kesalahan penekanan pada layar sentuh.
5. **Bottom Clearance Mobile**: Ruang kosong bawah `pb-28 lg:pb-12` menjamin seluruh interaksi di dasar halaman tetap terjangkau.

---

## 11. Matriks Adaptabilitas Multi-Device (Smartphone, Tablet, Desktop)

| Aspek Layout | Smartphone (360px – 430px) | Tablet POS & iPad (768px – 1024px) | Desktop & Laptop (1280px – 1920px+) |
| :--- | :--- | :--- | :--- |
| **Kolom Bento Grid** | **Bento Adaptif 2-Kolom (`grid-cols-2 gap-3`)**, Hero Card `col-span-2`, metrics `col-span-1` | 2–3 Kolom (`md:grid-cols-2 lg:grid-cols-3`) | 3–4 Kolom modular dinamis (12-kolom) |
| **Sidebar Menu** | Sheet Drawer samping (off-canvas) + `overflow-x-hidden` | Split-view collapsible atau icon-rail | Fixed Left Sidebar `w-72` frosted glass, `overflow-x-hidden` mutlak |
| **Topbar Header** | Sticky bar ringkas, burger toggle, profil mini | Sticky bar, breadcrumb, quick search, segmented theme | Sticky bar lengkap, eyebrow context, primary CTA |
| **Footer** | Floating Bottom Navbar (iOS 18) `fixed bottom-3` | Floating Bottom Navbar (mode potret/POS ringkas) | Minimalist Hairline Footer di dasar kanvas |
| **Bentuk Dialog / Form** | Apple Full-Responsive Bottom Sheet (`rounded-t-[28px] max-h-[94vh]`) | Centered Responsive Bento Modal (`max-w-3xl` s/d `max-w-4xl rounded-[22px]`) | Full Layout XXL Centered Bento Dialog (`max-w-5xl` s/d `max-w-7xl rounded-[24px]`) |
| **Input Form Font Size** | Wajib `16px` (mencegah auto-zoom) | `14px` – `15px` | `14px` – `15px` |
| **Touch Target Utama** | 48px – 52px (ramah jempol) | 44px – 48px (ramah stylus/jemari) | 38px – 42px (mouse click precision) |

---

## 12. Standarisasi Layout Backoffice Admin Platform (`layouts/admin.blade.php` & `admin/dashboard.blade.php`)

Standarisasi antarmuka pengelola platform Cooca (Backoffice Superadmin) mengadopsi penuh arsitektur Apple macOS Sonoma & iOS 18 dengan penyesuaian khusus untuk tugas pengawasan multi-tenant:

### 12.1 Sidebar macOS Sonoma Source List
- **Lebar Baku Desktop 288px (`w-72`) & Offset Kanvas (`lg:pl-72`)**: Sidebar desktop berukuran lebar standar Apple Source List `w-72` (288px / 18rem) guna menjamin ruang horizontal yang lega. Kontainer kanvas utama mengimbangi dengan offset `lg:pl-72`.
- **Mandat Satu Baris Mutlak (*Strict Single-Line Directive*)**: Seluruh item navigasi wajib berupa baris tunggal (`h-10 px-3 rounded-[12px] flex items-center gap-2.5`). Label teks dibungkus `whitespace-nowrap truncate min-w-0 flex-1`. Dilarang keras teks menu terputus dan membungkus ke 2–3 baris (*no multi-line wrapping*). Nomenklatur menu ringkas (*Langganan & Billing*, *Paket & Harga*, *Rekening Bank*, *Token AI*, *WhatsApp Gateway*, *Google OAuth*, *Server SMTP*).
- **Mandat Sleek Custom Scrollbar Anti-Windows (`.sidebar-scroll`)**: Kontainer menu menerapkan custom scrollbar Apple 4px ramping transparan (`scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.15) transparent;` & `::-webkit-scrollbar { width: 4px; }`). Dilarang membiarkan scrollbar native default Windows yang tebal (17px) muncul dan mengikis ruang tombol menu.
- **Header Status Operasional**: Menyertakan indikator status real-time platform (`🟢 Sistem Normal` dengan badge semantik `bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20`).
- **Grup Navigasi Inset**: Label uppercase 11px tebal memisahkan 5 seksi berimbang (*Ringkasan Utama*, *Operasional & Layanan*, *Monetisasi & Billing*, *Konten & Pemasaran*, dan *Konfigurasi Sistem*).
- **Item Navigasi Squircle Continuous**: Tinggi 40px, state aktif System Blue Apple berelevasi lembut (`bg-[#007AFF] text-white shadow-sm shadow-[#007AFF]/25 font-semibold rounded-[12px]`).
- **Badge Counter Semantik**: Angka verifikasi pending (`bg-[#FF9500] text-white`) atau alert kritis (`bg-[#FF3B30] text-white`).
- **Footer Profil Bento**: Profil administrator disematkan dalam squircle card di dasar sidebar dengan proteksi teks `min-w-0 flex-1 truncate` dan tombol logout cepat.
- **Isolasi Scrollbar Sidebar (`overflow-x-hidden`)**: Kontainer menu sidebar wajib memiliki `overflow-y-auto overflow-x-hidden` sehingga tidak ada scrollbar horizontal yang merusak estetika.

### 12.2 Topbar Toolbar & Zero Top-Edge Clipping Directive
- **Bilah Melekat Berjarak Vertikal Aman**: Ketinggian header aman dan proporsional `h-[68px] sm:h-[72px] py-2 px-4 sm:px-8 sticky top-0 z-30 backdrop-blur-xl bg-[#F2F2F7]/75 dark:bg-[#000000]/75 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-4`.
- **Mandat Bebas Terpotong di Batas Atas (*Zero Top-Edge Clipping*)**: Baris teks teratas (`eyebrow` sistem 10.5–11px) wajib memiliki jarak vertikal terlindungi (`leading-none mb-1` dan padding atas aman) sehingga teks tidak pernah terbelah atau terpotong di tepi atas layar.
- **Dynamic Type Scale**: `eyebrow` 10.5px uppercase (`text-[#8E8E93] dark:text-[#98989D]`), `navigationTitle` 19–21px font-extrabold `leading-snug`, dan `navigationSubtitle` 12–13px reguler `leading-none mt-0.5`.
- **Segmented Theme Control**: Pill switcher terpadu (Light / Dark / Auto).
- **Hairline Vertical Separator**: Pemisah vertikal 1px tinggi 20px sebelum profil admin.

### 12.3 Desktop Minimalist Hairline Footer
- **Pemisah Hairline**: `border-t border-black/[0.06] dark:border-white/[0.08] py-4 px-6 lg:px-8 text-[12px] text-[#8E8E93] dark:text-[#98989D]`.
- **Indikator Keamanan & Versi**: Memuat status `Multi-Tenant Shield Active • Cooca Platform Operations v2.0` dan hak cipta platform.

### 12.4 Standardisasi Modal Dialog SweetAlert2 Apple HIG
- Pop-up alert dan dialog konfirmasi SweetAlert2 disesuaikan dengan estetika Apple HIG:
  - Container squircle continuous `rounded-[24px]`.
  - Frosted background `bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-xl`.
  - Border hairline `border border-black/[0.08] dark:border-white/[0.1]`.
  - Tipografi SF Pro / Inter dengan tombol aksi System Blue (`#007AFF`) dan tombol batal halus (`bg-black/[0.05] dark:bg-white/[0.08]`).

### 12.5 Admin Dashboard Bento Grid Architecture
1. **Bento Hero Pulse**:
   - Memantau metrik finansial platform: **MRR** (Monthly Recurring Revenue) dan **ARR** (Annual Recurring Revenue) menggunakan tipografi angka tabular (`tabular-nums`).
   - Indikator persentase konversi tenant berbayar dengan bilah progres Apple HIG (`h-2 rounded-full bg-gradient-to-r from-[#007AFF] to-[#34C759]`).
   - Kartu status kesehatan ekosistem: Status Database Multi-Tenant, AI Engine Gateway, dan WhatsApp Dispatcher.
2. **Bento KPI Grid (Adaptive 2-Column Mobile, 4-Column Desktop)**:
   - Di mobile: Ditata rapi dalam 2 kolom berpasangan (`grid grid-cols-2 gap-3 sm:gap-4`) dengan padding proporsional (`p-3.5 sm:p-5`), badge ikon mini, dan angka tabular tebal (`text-[18px]`–`text-[26px] font-extrabold`).
   - Di desktop: Tetap 4 kolom (`lg:grid-cols-4`) modular elegan.
3. **Bento Quick-Action Tray**:
   - Tombol sentuh 48–52px (`h-12`) ramah jempol dengan label tindakan bahasa Indonesia gamblang: *Kelola Langganan*, *Audit Pengguna*, *Pengaturan Email SMTP*, *Status WhatsApp*.
4. **Tabel Operasional Terisolasi**:
   - Pembungkus kartu dengan scroll horizontal aman (`overflow-x-auto scrollbar-thin`), baris interaktif dengan status semantik Apple, dan trigger klik langsung membuka modal detail.
5. **Mandat 4 Pop-Up Modal Sheets (Zero Navigation Jumps)**:
   - **Modal Sheet 1 (Detail Tenant)**: Menampilkan profil bisnis, owner, paket langganan aktif, kuota AI, dan status verifikasi.
   - **Modal Sheet 2 (Detail Akun Pengguna)**: Menampilkan informasi login, peran akses, bisnis terasosiasi, dan audit timestamp.
   - **Modal Sheet 3 (Quick Approval Langganan)**: Persetujuan pembayaran instan dengan input catatan admin (`admin_notes`) berukuran font minimal 16px (anti-auto-zoom) dan opsi aksi Setujui / Tolak tanpa reload halaman.
   - **Modal Sheet 4 (Bukti Transfer Lightbox)**: Preview gambar bukti pembayaran beresolusi penuh langsung di dalam dashboard dengan tombol zoom, informasi nomor referensi, dan aksi cepat verifikasi.

### 12.6 Mandat Perlindungan Dimensi SVG & Preservasi Desktop
- **Dimensi SVG Wajib**: Setiap elemen `<svg>` wajib menyertakan atribut eksplisit `width` & `height` serta arbitrary class (misal: `width="18" height="18" class="w-[18px] h-[18px]"`), mencegah SVG meledak menjadi bola raksasa yang merusak layout.
- **Preservasi Desktop & Tabel 100%**: Layout desktop 4-kolom (`lg:grid-cols-4`), kanvas 1440px, dan tabel data dense dengan horizontal containment wajib dipertahankan seutuhnya saat mengoptimasi tampilan responsif mobile.

### 12.7 Visual Analytics Bento Grid & Dynamic Dark-Mode Charting
- **Chart.js CDN Injection**: Diinjeksikan pada `<head>` `layouts/admin.blade.php` agar setiap panel administrasi dapat merender visualisasi performa tanpa ketergantungan bundler eksternal.
- **4 Pilar Visual Analytics Dashboard**:
  1. *Tren Pertumbuhan Registrasi*: Dual Area Line Chart (System Blue `#007AFF` & System Green `#34C759`) dengan kurva halus (`tension: 0.35`) memvisualisasikan tren pendaftaran bisnis dan pengguna 6 bulan terakhir.
  2. *Komposisi Langganan Platform*: Donut Chart (`cutout: '76%'`) dengan palet Apple Semantik (`#34C759`, `#007AFF`, `#5856D6`) membedakan distribusi tier Free vs Core Monthly vs Core Annual beserta legend persentase ringkas.
  3. *Arus Pendapatan Billing Bulanan*: Bar Chart rounded (`borderRadius: 8`) memproyeksikan riwayat pendapatan langganan dalam format mata uang IDR Rupiah tabular.
  4. *Distribusi Aktivitas Ekosistem*: Horizontal Bar Chart mengukur volume keterlibatan modul utama platform (Katalog Produk, Kalkulasi HPP BOM, Token AI, Tenant Bisnis, dan Total Pengguna).
- **Reaktivitas Dark Mode Dinamis (`MutationObserver`)**: Mengamati mutasi atribut `class` pada `document.documentElement` (`dark`). Ketika tema berubah (Light ⇄ Dark), warna garis grid (`rgba(0,0,0,0.06)` vs `rgba(255,255,255,0.08)`), warna label sumbu (`#8E8E93` vs `#98989D`), dan border irisan donut otomatis diperbarui tanpa perlu memuat ulang (*reload*) halaman.

### 12.8 Standar Formulir Konfigurasi & Profil Admin (Apple HIG)
- **Pusat Konfigurasi Terpadu (*Unified Settings & SMTP Hub*)**:
  - Mengonsolidasikan pengaturan platform Google OAuth (`admin/settings`) dan Server SMTP (`admin/smtp`) ke dalam satu antarmuka terpadu bergaya Apple macOS Sonoma System Settings.
  - Dilengkapi Segmented Pill Tab Bar responsif (`Google OAuth & Sistem` dan `Pengaturan SMTP Email`) yang interaktif via Alpine.js dengan sinkronisasi URL parameter (`?tab=system` vs `?tab=smtp`) tanpa reload halaman.
  - Mempertahankan backward compatibility 100% terhadap rute backend (`admin.settings.index`, `admin.settings.update`, `admin.smtp.index`, `admin.smtp.update`, `admin.smtp.test`) dan asersi pengujian otomatis.
- **Preset Konfigurasi Instan 1-Klik**: Formulir teknis rumit (seperti SMTP) dilengkapi tombol preset instan (Gmail, Mailtrap, cPanel/Hosting, Log Driver) yang otomatis mengisi host, port, dan tipe enkripsi dengan aman.
- **Show/Hide Password Toggles**: Input kata sandi dan credential sensitif (SMTP Password, Kata Sandi Administrator, Konfirmasi Sandi) dilengkapi tombol intip (*eye icon toggle*) berukuran sentuh 44px–48px.
- **Status Indikator Layanan Terintegrasi**: Pengaturan OAuth Google Cloud dan webhook dilengkapi pill badge status real-time (*Terkonfigurasi* 🟢 vs *Belum Dikonfigurasi* 🟡) serta tombol 1-klik salin URI Callback resmi.
- **Ergonomi Input & Sentuh Ramah Mobile**:
  - Seluruh field input teks dan select memiliki font minimal 16px pada viewport mobile (`text-[16px] sm:text-[13px]`) untuk menonaktifkan auto-zoom iOS Safari.
  - Tombol aksi simpan memiliki tinggi minimal 48px (`h-12 sm:h-10 px-6 rounded-[12px]`) dengan feedback visual fokus dan state disabled/loading saat proses submit.

### 12.9 Standar Manajemen Katalog Produk, Resep (BOM), & Modifiers (Apple HIG)
- **Segmented Control Navigasi Terpadu (Section 16)**:
  - Menggantikan banner raksasa bergradasi dengan tab bar tersegmentasi bergaya Apple macOS Sonoma / iOS 18 (`[ Barang Fisik (Katalog) ] [ Jasa & Layanan ] [ Varian & Modifiers ]`).
  - Efek transisi halus dan kontras tajam antar tab aktif (`bg-white dark:bg-[#2C2C2E] shadow-sm font-semibold text-[#1C1C1E] dark:text-[#F2F2F7]`) dan inaktif.
- **Modal Pop-Up Full Layout XXL & Multi-Device Responsiveness**:
  - Modal Form Tambah/Ubah Produk (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl max-h-[90vh]`) mengadopsi tata letak Bento Grid 12 kolom (7 kolom informasi identitas produk & inventori + 5 kolom penetapan harga, kanal penjualan, foto, & pre-order).
  - Menggantikan form sempit 1 kolom yang memaksa pengguna scrolling berlebih, menjadi 2 kolom komprehensif pada desktop & tablet landscape.
  - Pada mobile (< 640px), modal bertransformasi dinamis menjadi Apple Bottom Sheet (`rounded-t-[28px] max-h-[94vh]`) dengan grab bar dan sticky action bar di dasar layar.
- **Inline Quick-Add Trigger `[ + ]` via AJAX (Zero Page Reload)**:
  - Kategori Produk dan Satuan Pengukuran dilengkapi tombol quick-add `[ + ]` di samping dropdown.
  - Sub-modal pop-up menyimpan data secara asynchronous (`fetch()`) dengan header `Accept: application/json` dan CSRF token.
  - Opsi baru diinjeksi ke dropdown dan dipilih secara otomatis (`x-model`) tanpa memicu reload halaman atau menghapus draf form produk yang sedang diketik.
- **Apple Grouped Inset Cards untuk Mobile (< sm)**:
  - Pada layar smartphone, tabel data produk, BOM, dan varian bertransformasi menjadi kartu inset Apple (`rounded-[20px] p-4 bg-white/90 dark:bg-[#1C1C1E]/90 border border-black/[0.06]`) dengan baris key-value yang mudah dipindai jempol.
- **Pesan Penenang Jiwa (*No-Panic Microcopy*)**:
  - Seluruh aksi penghapusan produk, komponen BOM, kelompok modifier, atau opsi varian dilengkapi Apple Alert Dialog dengan jaminan microcopy Lucide icon `info`:  
    *“Tenang: Riwayat transaksi kasir, nota pesanan, dan pembukuan masa lalu yang menggunakan produk ini tetap aman tersimpan.”*
- **Safe Area Bottom Padding Ergonomics**:
  - Seluruh layout halaman katalog menyertakan kontainer padding dasar `pb-28 sm:pb-32 lg:pb-10` untuk mencegah elemen aksi tertutup bilah navigasi mengambang iOS 18.
- **Font Input Minimal 16px Anti-Auto-Zoom**:
  - Seluruh input form menggunakan kelas `text-[16px] sm:text-[14px]` untuk mencegah browser Safari iOS melakukan zoom otomatis saat pengguna fokus ke kolom isian.

### 12.10 Standar Manajemen Bahan Baku (Materials) & Jasa Layanan (Services) Apple HIG
- **Bahan Baku (Materials Management)**:
  - **Full Layout XXL Centered Bento Dialog**: Modal Tambah Bahan Baku (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-7xl max-h-[90vh]`) mengadopsi 12 kolom Bento Grid (7 kolom identitas bahan + 5 kolom rendemen yield/waste, harga awal, dan kartu live cost estimation).
  - **Triple Inline Quick-Add AJAX `[ + ]`**: Kategori Bahan (`material-categories.store`), Satuan Beli (`units.store`), dan Supplier Pemasok (`suppliers.store`) dapat ditambahkan seketika melalui sub-modal asynchronous tanpa memuat ulang halaman utama.
  - **Apple Grouped Inset Cards**: Tabel bahan baku otomatis bertransformasi menjadi kartu inset Apple di smartphone (`< sm`).
  - **Penenang Jiwa Dialog**: Konfirmasi hapus bahan baku menyertakan jaminan bahwa resep BOM dan riwayat penerimaan barang (GR) masa lalu tetap aman tersimpan.
- **Jasa & Layanan (Services Management)**:
  - **Penyatuan Navigasi Segmented Control (Section 16)**: Menghilangkan banner gradasi dan menyelaraskan bilah navigasi dengan katalog produk (`[ Barang Fisik ] [ Jasa & Layanan ] [ Varian & Modifiers ]`).
  - **Zero-Navigation Jump pada Edit Jasa**: Menggantikan alur edit berbasis redirect parameter URL (`?edit=<id>`) menjadi instant client-side Alpine.js modal (`openEdit(service)`), mempertahankan state filter dan nomor pagination.
  - **Full Layout XXL Dialog**: Form tambah/ubah layanan mengadopsi Bento 12 kolom (7 kolom identitas, kategori, dan deskripsi + 5 kolom tarif, biaya teknisi, switches kanal POS/Web/SO dengan fallback value `0`).
  - **Strict No-Emoji Mandate**: Seluruh emoji dekoratif (seperti 🛠️, ✏️, 🗑️) dibersihkan dan digantikan dengan ikon Lucide SVG murni.

### 12.11 Standar Storefront Hub, Pengaturan Toko, Aturan Ongkir, Reservasi & Landing Page Studio
- **Storefront Settings (Pengaturan Etalase Toko)**:
  - **Bento KPI Overview Cards**: Menampilkan 4 kartu ringkasan status operasional etalase: Status Etalase Publik, Metode Bayar Aktif (QRIS & Transfer), Opsi Pengiriman (Pickup & Kurir), dan Mode Transaksi Toko Aktif.
  - **Integritas Hidden Fallback Switch Boolean**: Seluruh kontrol saklar boolean (`is_storefront_enabled`, `is_discoverable`, `allow_pickup`, `allow_delivery`, `allow_request_order`, `allow_scheduled_order`, `allow_customer_po`, `allow_reservation`) wajib didahului oleh `<input type="hidden" name="[field]" value="0">`. Hal ini mencegah kegagalan pengiriman nilai `false` saat pemilik toko menonaktifkan fitur.
  - **Apple Alert Penenang Jiwa**: Dialog konfirmasi hapus rekening pembayaran menggunakan Apple Alert Dialog squircle lengkap dengan microcopy penenang jiwa (*"Tenang: Riwayat pesanan dan bukti transfer pelanggan masa lalu yang pernah menggunakan rekening ini tetap aman tercatat di pembukuan."*).
  - **Responsive Modal Bottom Sheet**: Form tambah metode pembayaran mengadopsi format Apple Bottom Sheet pada mobile (`rounded-t-[28px]`) dengan grab bar dan font input minimal 16px.
- **Aturan Ongkir & Kurir Toko (Shipping Rules)**:
  - **Penenang Jiwa Dialog**: Aksi hapus aturan ongkir pada tabel desktop maupun kartu mobile dilengkapi dialog konfirmasi Apple dengan jaminan bahwa transaksi dan ongkir pesanan masa lalu tidak akan berubah.
  - **Apple Bento Dialog Tambah/Edit**: Modal aturan ongkir mendukung tarif flat, radius jarak (KM), dan ambang bebas ongkir belanja gratis dengan tata letak modal responsif.
- **Reservasi & Booking Jadwal (Reservations)**:
  - **Apple Alert Konfirmasi Pembatalan**: Menggantikan aksi pembatalan langsung dengan dialog konfirmasi Apple Alert terpadu dan pesan penenang jiwa.
  - **Modal Alokasi Meja Restoran**: Dilengkapi grab bar mobile dan integrasi pemilihan meja yang ramah layar sentuh.
- **Verifikasi & Rincian Pesanan (Orders Show)**:
  - **Apple Alert Konfirmasi Verifikasi**: Menggantikan `confirm()` native dengan dialog konfirmasi visual bernuansa hijau yang merincikan pemotongan stok otomatis dan status LUNAS.
- **Studio Landing Page (CMS Editor)**:
  - **Strict No-Emoji Mandate**: Mengeliminasi seluruh karakter unicode mentah (seperti bintang `★` pada form ulasan testimoni) dan menggantikannya dengan ikon vektor Lucide SVG.
  - **Ergonomi Layar Sentuh**: Menjamin seluruh input memiliki ukuran minimal 16px pada viewport mobile dan padding dasar aman `pb-28 sm:pb-32 lg:pb-10`.

### 12.12 Standar Halaman Publik Bisnis (Public Storefront & Landing Page Experience)
Halaman publik bisnis (`resources/views/public/business_landing.blade.php`) merupakan representasi digital terdepan bagi pelanggan umum:
- **Dismissible Announcement Marquee**:
  - Banner pengumuman bergerak di posisi teratas menggunakan pemisah bersih bullet dot `•` (`&bull;`) tanpa simbol unicode bunga/bintang `✦`.
  - Dilengkapi kontrol Alpine.js (`x-data="{ bannerDismissed: false }"`) dan tombol tutup bundar frosted glass (`w-5 h-5 rounded-full bg-white/20 hover:bg-white/30`) agar pelanggan dapat menyembunyikan pengumuman secara mandiri.
- **Strict No-Emoji Mandate pada Status Verifikasi**:
  - Seluruh status akun terverifikasi pada Checkout Modal, Customer PO Modal, dan Reservasi wajib menggunakan ikon SVG murni `<i data-lucide="check" class="w-3.5 h-3.5"></i> Terverifikasi` dan dilarang keras menggunakan karakter unicode checklist `✓`.
- **Responsive Modal Architecture (Mobile Bottom-Sheet & Desktop XXL 2-Column Bento Dialog)**:
  - Seluruh modal interaksi publik (Checkout Modal, Request Order, Reservasi, dan Customer PO) wajib mengadopsi format hybrid:
    - **Mobile (< 640px)**: Tampil sebagai **Apple Full-Responsive Bottom Sheet** (`items-end justify-center`, `rounded-t-[28px]`, `max-h-[92vh]`) dengan drag/swipe indicator handle bar (`w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5`), font input minimal 16px (mencegah auto-zoom iOS), dan sticky action footer dengan safe-area padding.
    - **Desktop (>= 1024px)**: Mengambang di tengah sebagai **Full Layout XXL 2-Column Bento Dialog** (`lg:max-w-5xl` untuk Checkout; `lg:max-w-4xl` untuk Reservasi & Request Order; `lg:max-w-4xl` untuk Customer PO). Kolom kiri memuat identitas pelanggan, jadwal, dan opsi pengiriman; kolom kanan memuat rincian transaksi, pemilihan metode pembayaran, catatan, dan ringkasan subtotal/ongkir/grand total.
- **Pure Typographic Overline pada Hero Section (Anti-AI-Template)**:
  - Dilarang membungkus tag pengenal atau promo hero dengan kapsul pil dekoratif berikon sparkle (`rounded-full` AI template).
  - Gunakan **Pure Typographic Overline** (`text-[11px] font-bold uppercase tracking-widest text-black/50 dark:text-white/50`) dengan pemisah dot murni `&bull;` yang menyatu tenang dengan tipografi SF Pro Display.
- **Floating WhatsApp Widget Tanpa Fake Unread Pulse Dots**:
  - Tombol aksi WhatsApp mengambang (`fixed bottom-20 right-4 ...`) wajib diproteksi dengan kondisi `@if ($hasWhatsapp)`.
  - Dilarang memasang titik merah berkedip palsu (*fake unread badge* `animate-ping`) yang memanipulasi perhatian pengguna. Gunakan label aksi yang jujur, santun, dan profesional: `Hubungi via WhatsApp`.
- **Standarisasi Microcopy & Pure Action Verbs**:
  - Hindari singkatan kasar seperti `"WA"` pada header, navigasi mobile, maupun modal. Gunakan nama layanan utuh `"WhatsApp"`.
  - Gunakan kata kerja aksi tegas dan ramah: *Pesan*, *Reservasi*, *Kirim Request Order*, *Hubungi via WhatsApp*.
- **Preservasi Kontrak Anti-FOUC Script**:
  - Script inisialisasi dark mode pada `<head>` wajib mempertahankan deklarasi eksplisit `var isLandingDark = {{ $initialDarkMode ? 'true' : 'false' }};` sebelum manipulasi `document.documentElement.classList`, guna mencegah *Flash of Unstyled Content* (FOUC) saat SSR dan menjamin integritas kontrak pengujian otomatis (`PublicBusinessDiscoveryTest`).
- **Penanganan Adaptif Opsi Pemenuhan (Fulfillment)**:
  - Formulir checkout wajib menangani 4 permutasi pengaturan pemenuhan toko: (1) Keduanya aktif (Ambil Sendiri & Kurir Toko); (2) Hanya Kurir Toko; (3) Hanya Ambil Sendiri di Outlet; dan (4) Keduanya nonaktif dengan banner informatif ramah (*Metode pengiriman disesuaikan saat konfirmasi pesanan*).
- **Visualisasi Batas Minimum Belanja**:
  - Drawer keranjang belanja menyajikan peringatan visual batas minimum belanja (`minOrderAmount`) berformat mata uang Rupiah lengkap dengan ikon Lucide `alert-circle` saat subtotal belum mencukupi batas checkout.
- **Mobile Safe Area Bottom Padding Ergonomics**:
  - Bagian dasar footer wajib menggunakan kelas padding `pb-28 sm:pb-32 lg:pb-12` agar bilah navigasi pulau mengambang mobile iOS 18 (`fixed bottom-3 ... z-50`) tidak menghalangi keterbacaan hak cipta, merek dagang, dan tautan sosial media footer.

---

## 13. Jiwa & Karakter Brand Cooca (Brand Soul & Anti-AI-Template Mandate)

### 13.1 Karakter Brand Cooca
Cooca BUKAN template AI generik. Cooca adalah **Platform Sistem Operasi Bisnis UMKM Nusantara yang Berjiwa, Jujur, Tangguh, dan Presisi**:
1. **Tenang & Berwibawa (Calm Confidence):** Menghadirkan ketenangan visual bagi pemilik usaha yang sibuk. Ruang kosong (*whitespace*) dibiarkan lapang dan bersih sebagai "udara bernapas".
2. **Presisi Fungsional:** Angka keuangan, HPP, dan stok disajikan dengan kepastian matematis murni menggunakan tipografi tebal dan `tabular-nums`.
3. **Apple Restraint (Seni Menahan Diri):** UI menahan diri agar data bisnis menjadi fokus utama. Tidak membungkus teks ke dalam kapsul jika hierarki tipografi murni sudah cukup menjelaskannya.
4. **Kehangatan Manusiawi:** Bahasa Indonesia yang santun, lugas, dan membumi tanpa jargon klise AI (*no AI buzzwords*).

### 13.2 Mandat Anti-Pill-Abuse (Pemberantasan Inflasi Kapsul & Stiker)
1. **Larangan Eyebrow Pills:** Dilarang menaruh kapsul `rounded-full` di atas judul utama maupun judul section (seperti `AI-Powered Management System` atau `Ekosistem Modular Terpadu`). Jika konteks section diperlukan, gunakan **Pure Typographic Overline** (`text-[11px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`).
2. **Larangan Metric Cluttering:** Dilarang menempelkan pill kecil di samping angka besar (seperti `Rp 0` ditempeli `ARR Rp 0.0 Juta/thn`). Biarkan angka utama berdiri gagah, keterangan sekunder ditaruh di bawahnya sebagai teks footnote murni yang tenang.
3. **Larangan Fake Pulse Dots:** Dilarang memberi efek berkedip (`animate-pulse`) pada teks atau rentang waktu biasa. Pulsing dot HANYA untuk status perangkat keras fisik (printer thermal, barcode scanner, koneksi socket aktif).
4. **Batasan Ketat Badge/Pill:** Badge kapsul HANYA untuk **Status Siklus Hidup Objek yang Berubah** (Pembayaran: *Menunggu*, *Lunas*; Stok: *Aman*, *Kritis*; Akun: *Aktif*, *Ditangguhkan*). Dilarang untuk teks statis, slogan promosi, atau rentang waktu. Maksimal 1 badge per entitas data.
5. **Tipografi Murni Sebagai Pahlawan:** Membangun hierarki visual melalui kontras skala ukuran font, bobot tebal/sedang, dan saturasi warna teks semantik, bukan dengan membungkus teks ke dalam kapsul.
6. **Mandat Eliminasi Total Elemen Fluff (Hapus Sampahnya, Jangan Cuma Copot Bajunya):** Dilarang keras menghilangkan bungkus kapsul tapi tetap membiarkan teks hiasannya melayang di antarmuka (*"sama aja bohong"*). Jika suatu teks adalah hiasan buatan bot AI (`Live 6 Bulan`, `Organik`, `Terverifikasi`, `Platform-Wide`, `AI-Powered...`, `Live Cloud`, `INSIGHT`, `Bebas Biaya`), **HAPUS TOTAL ELEMEN DAN TEKS TERSEBUT DARI KODE!**

