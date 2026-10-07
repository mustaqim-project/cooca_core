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

## 6. Mandat Anti-Excessive-Text, Anti-Hyperbole & Penyajian Sederhana, Padat, dan Jelas

### A. Larangan Keras Informasi Berlebih, Rumit, & Kompleks (Anti-Clutter & Extreme Simplicity)
UI COOCA dirancang agar **sederhana, padat, dan jelas** (*clarity & high glanceability*). Dilarang menyajikan informasi yang berlebihan, terlalu banyak data sekunder yang tidak relevan, atau tata letak berbelit-belit yang membingungkan pengguna:
- **Dilarang**: Paragraf penjelasan panjang tanpa kebutuhan operasional, deskripsi berulang yang menjelaskan hal yang sudah jelas dari judulnya, subtitle pada tiap kartu tanpa fungsi pembeda status, helper text yang tidak membantu keputusan, teks promosi di halaman transaksi/operasional, jargon teknis (_SKU, BOM, COGS, Void, Tenant Context_), judul panjang yang bisa diringkas 2–3 kata, empty state berkalimat panjang (cukup: _"Belum ada produk"_ + tombol aksi).
- **Prinsip Essential-First & 3-Second Glanceability**: Pengguna wajib dapat memahami status kunci dan aksi prioritas dalam 3 detik pertama.
- **Progressive Disclosure**: Sembunyikan rincian teknis yang kompleks atau jarang diakses ke dalam modal sheet / drawer detail (*Master-Detail*), sehingga antarmuka utama tetap bersih, lapang, dan bernafas.

### B. Mandat Anti-Hyperbole & Integritas Faktual Sistem (Larangan Klaim Dilebih-lebihkan)
Dilarang keras menyajikan teks, label, metrik, atau slogan yang **dilebih-lebihkan (*overstated / marketing slop / fake claims*)** yang tidak sesuai dengan spesifikasi teknis riil sistem atau data aktual di database:
- **Dilarang Klaim Teknologi/Fitur Fiktif**: Dilarang menggunakan istilah bombastis seperti *"Mesin AI Quantum 99.999% Akurasi"*, *"Algoritma Otomatis Berkecepatan Cahaya"*, *"Super AI Engine Terintegrasi"*, *"Zero Error Guaranteed"*, atau sebutan fiktif lain yang tidak mencerminkan kapabilitas sistem nyata.
- **Dilarang Metrik / Estimasi Palsu**: Dilarang menampilkan angka klaim fiktif pada UI operasional (misal: *"Meningkatkan Penjualan 300%"*, *"Dipercaya oleh 100.000 Bisnis"*, *"Penghematan Biaya 100%"*).
- **Wajib Data Riil & Matematis**: Seluruh angka KPI, nominal moneter, persentase pertumbuhan, sisa stok, status perangkat keras, dan waktu proses wajib bersumber dari kalkulasi database aktual dengan format angka presisi (`tabular-nums`).

### C. Mandat Perlindungan Kredensial di UI (Zero Plaintext Credential Exposure)
Dilarang keras menampilkan informasi kredensial sensitif atau rahasia sistem secara terbuka (*plain text*) di antarmuka publik maupun dashboard operasional:
- **Kredensial yang Wajib Dilindungi**: API Keys, Secret Tokens, Private Keys, Password akun/staf, PIN Kasir, Supervisor PIN, Webhook Secrets, SMTP Password, dan detail kartu perbankan pelanggan.
- **Standar Masking Keamanan**: Kredensial pada form pengaturan integrasi wajib tertutup secara default menggunakan masking titik tebal (`••••••••••••••••` atau `sk-live-••••••••1234`).
- **Toggle Visibility Terkontrol**: Tombol intip (*eye icon* / Show-Hide) hanya boleh tersedia pada form konfigurasi untuk peran berotorisasi tinggi (Superadmin/Owner), dan tidak boleh merender token rahasia ke HTML publik atau atribut dataset JavaScript tanpa enkripsi/masking. Struk POS dan log publik dilarang memuat data sensitif.

Prioritas konten UI: **Wajib** (agar user paham data/selesaikan tugas) → **Membantu** (konteks penting/cegah kesalahan) → **Opsional** (hanya di modal sheet saat diminta) → **Tidak perlu** (hapus seketika).

Contoh microcopy lugas & padat:
| Kurang baik (Bertele-tele / Hiperbola) | Lebih baik (Sederhana, Padat, Jelas) |
|---|---|
| "Silakan melakukan proses penyimpanan data produk canggih yang telah Anda masukkan ke dalam sistem kami." | **"Simpan Produk"** |
| "Anda saat ini belum memiliki data produk yang dapat ditampilkan pada tabel ringkasan halaman ini." | **"Belum ada produk."** |
| "Apakah Anda benar-benar yakin 100% ingin melanjutkan proses penghapusan permanen data ini dari server?" | **"Hapus produk ini?"** |
| "Mesin Otomasi AI Mutakhir memproses sinkronisasi data secara instan tanpa batas." | **"Sinkronisasi Data Otomatis"** |

**Tombol Aksi Lugas & Standardisasi CTA**: tombol adalah pemicu aksi, bukan tempat mengulang judul kartu/halaman atau menjejali fitur sekunder.

- Di dalam form/modal → satu kata kerja murni: **Simpan · Hapus · Edit/Ubah · Lihat · Batal · Kirim · Salin**.
- Pengecualian terbatas (maks. 2–4 kata) hanya untuk CTA utama modul di luar form/tabel: _"Tambah Produk"_, _"Ekspor Excel"_, _"Cetak Struk"_, _"Hubungkan WhatsApp Resmi"_.
- **Larangan Keras Kata Yatim (*No Orphan Word Wrapping*)**: Dilarang label tombol melipat menghasilkan 1 kata terisolasi di baris kedua seperti `Connect with Official WhatsApp (1-Click \n Meta)`. Jika tombol 1 baris, terapkan `whitespace-nowrap`. Jika butuh keterangan sekunder (misal `1-Klik Meta`), taruh di micro-badge atau helper text di luar tombol!
- **Action Proximity Rule**: Tombol CTA utama dalam kartu harus diletakkan langsung menyusul kalimat ajakan atau sejajar dengan header kartu. DILARANG menyisipkan 3 kartu langkah atau blok panduan besar di antara pengantar dan tombol aksi (*Action Proximity Inversion*).
- **Quiet Stepper vs Action Button (Anti False-Affordance)**: Elemen panduan onboarding/langkah (1. Klik, 2. Masuk, 3. Selesai) WAJIB berupa *quiet stepper* (nomor bundar kecil `w-5 h-5 rounded-full bg-black/5 dark:bg-white/10`, teks subtil). DILARANG membungkus langkah ke dalam kotak berborder tebal/kontras tinggi yang menyerupai tombol interaktif.
- **Proporsi Lebar Responsif**: Di desktop, tombol dalam kartu wajib proporsional (`sm:w-auto px-6 min-h-[44px]` atau `max-w-sm`), bukan balok raksasa yang membentang 100% kaku dari tepi ke tepi. Ikon di dalam tombol wajib `shrink-0` dan sejajar rapi (`inline-flex items-center justify-center gap-2.5`).
- Contoh salah: _"Simpan Pengaturan Google & Sistem"_ → benar: _"Simpan"_. _"Hubungkan dengan WhatsApp Resmi (1-Klik Meta)"_ → benar: _"Hubungkan WhatsApp Resmi"_.

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

## 11. Blueprint Responsivitas Adaptif: Beyond Bento (Anti Bento-Dogmatism)

### A. Filosofi Anti-Bento-Dogmatism (Tidak Semua Harus Bento!)

Bento Grid adalah pola komposisi asimetris yang sangat elegan untuk **Desktop & Tablet Landscape (layar lebar horizontal)**. Namun, **DILARANG KERAS memaksakan Bento Grid secara dogmatis ke setiap elemen di mobile**!

Memaksakan semua komponen menjadi tumpukan kotak Bento di smartphone (360px–430px) memicu **"Infinite Card Bloat"**:
- Halaman menjadi sangat panjang ke bawah karena setiap metrik/langkah/opsi dibungkus kotak kartu ber-border dengan padding 20–24px.
- Pengguna ponsel kelelahan scrolling vertikal (*scroll fatigue*) hanya untuk melihat 3–4 angka atau 3 langkah ringkas.
- Ruang layar sempit terbuang sia-sia untuk bingkai kotak (*card framing waste*), bukan untuk konten fungsional.

**Aturan Emas:** Gunakan pola tata letak yang paling **simpel, hemat ruang, dan ergonomis dengan satu jempol** di mobile:
1. **Desktop:** Bebas memakai Bento Grid asimetris 12-kolom (8 vs 4, 3 vs 3 vs 3).
2. **Mobile:** BERALIH KE POLA ADAPTIF: **Horizontal Snap Slider**, **Grouped Inset List**, **Segmented Control**, atau **Compact Stepper**.

---

### B. 4 Pola Alternatif Ramah Mobile (Mobile-First Ergonomic Patterns)

#### 1. Horizontal Snap Slider / Carousel (`snap-x snap-mandatory overflow-x-auto no-scrollbar`)
- **Cocok Untuk**: 3–4 Kartu Metrik KPI, panduan langkah/onboarding, shortcut aksi cepat, kartu mutasi saldo, promo highlight.
- **Kelebihan**: Menghemat hingga 75% tinggi layar! Pengguna dapat menggeser (*flick*) kartu secara horizontal dengan ibu jari tanpa membuat halaman molor ke bawah.
- **Implementasi (Tailwind)**:
  ```html
  {{-- Mobile: Horizontal Snap Slider | Desktop: Grid 4-Kolom Sejajar --}}
  <div class="flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0">
      <div class="snap-start shrink-0 w-[220px] sm:w-auto p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm">
          {{-- Konten KPI / Step Card --}}
      </div>
  </div>
  ```

#### 2. Grouped Inset List (Apple iOS Settings / Health Style)
- **Cocok Untuk**: Modul pengaturan, opsi form bertingkat, formulir switch/toggle, detail akun, daftar transaksi mini.
- **Kelebihan**: Menggantikan 4–5 kartu Bento terpisah menjadi **1 kartu kontainer tunggal** dengan baris pemisah halus (`divide-y divide-black/5 dark:divide-white/5`).
- **Implementasi**:
  ```html
  <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 divide-y divide-black/5 dark:divide-white/5 overflow-hidden shadow-sm">
      <div class="p-3.5 sm:p-4 flex items-center justify-between min-h-[48px]">
          <div class="flex items-center gap-3 min-w-0">
              <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/12 text-[#007AFF] flex items-center justify-center shrink-0">
                  <i data-lucide="bell" class="w-4 h-4"></i>
              </div>
              <div class="min-w-0 truncate">
                  <p class="text-[13.5px] font-semibold text-black dark:text-white truncate">Notifikasi WhatsApp</p>
                  <p class="text-[11.5px] text-black/50 dark:text-white/50 truncate">Kirim struk otomatis</p>
              </div>
          </div>
          <input type="checkbox" class="shrink-0 ...">
      </div>
  </div>
  ```

#### 3. Segmented Control & Single-Active Chart
- **Cocok Untuk**: Visualisasi grafik tren & perbandingan data.
- **Kelebihan**: Di mobile, DILARANG menumpuk 3 grafik vertikal. Tampilkan 1 grafik aktif dengan tombol segmen (*Hari Ini · 7 Hari · 30 Hari*) atau tab switch (*Omzet vs Laba*) agar layar tetap lapang.

#### 4. Compact Inline Stepper (Anti False-Affordance Step Cards)
- **Cocok Untuk**: Alur verifikasi, onboarding 1-klik, panduan integrasi.
- **Kelebihan**: Ganti kartu langkah tebal bertingkat dengan titik/nomor progres minimalis dalam 1 baris (`1 ── 2 ── 3`) atau swipeable card. Menjaga fokus utama pengguna langsung ke tombol aksi (CTA).

---

### C. Matriks Pemilihan Pola Berbasis Perangkat (Form-Factor Matrix)

| Komponen / Tipe Data | Di Desktop (≥ 1024px) | Di Mobile (< 640px) | Alasan UX Mobile |
|---|---|---|---|
| **Metrik KPI (3–4 angka)** | Bento Grid 4-kolom sejajar | **Horizontal Snap Slider** ATAU **Grid 2x2 Kompak** | Mencegah 4 kartu menumpuk ke bawah (hemat 75% tinggi scroll). |
| **Panduan Onboarding (3 langkah)** | Grid 3-kolom horizontal | **Compact Inline Stepper** ATAU **Horizontal Swipe Slider** | Tombol CTA tidak tenggelam di bawah kartu langkah raksasa. |
| **Pengaturan & Opsi Modul** | Bento Card 2-kolom | **Grouped Inset List** (1 kartu dengan `divide-y`) | Rapi, padat, mudah disentuh jempol ala iOS Settings. |
| **Grafik Analisis Tren** | Asymmetric Bento 8-col vs 4-col | **1 Grafik Aktif** + Segmented Control Tab | Layar ponsel tidak sesak oleh multi-chart bertumpuk. |
| **Tabel Data Transaksi** | Tabel komprehensif | **Card List Ringkas** (2–3 field penting) + Bottom Sheet Detail | Menghindari tabel melebar rusak dan horizontal scroll macet. |
| **Aksi Transaksi Kritis** | Tombol kanan atas / footer | **Floating Bottom Bar** (`fixed bottom-3`) | Berada di jangkauan alami jempol (*thumb zone*). |

---

### D. Smartphone 360–639px (Zero-Breakage Rules)

- Dilarang `w-[...]`/`min-w-[...]` statis > 300px - pakai `w-full max-w-full`.
- Semua teks dalam flex container wajib `min-w-0` + `truncate`/`break-words`.
- Grid: form/detail kompleks → `grid-cols-1`. Stat/KPI ringkas → Horizontal Slider atau maksimal `grid-cols-2`.
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

---

## 18. Spesifikasi UI Indikator Kuota Subscription, Entitas Terkunci (Gembok), & Storage Pruning Previewer

### A. Indikator Kuota & Badge Entitas Terkunci Limitasi Plan (*Resource Gating UI*)
Ketika kuota plan tercapai atau merchant melakukan downgrade (misal: produk ke-51 s/d 1.000 pada plan Standard):
1. **Banner Peringatan Kuota Lapang**:
   - Di atas tabel katalog produk/karyawan/cabang:
     ```html
     <div class="rounded-[18px] bg-amber-500/10 border border-amber-500/20 p-4 flex items-center justify-between gap-4">
         <div class="flex items-center gap-3 min-w-0">
             <div class="w-9 h-9 rounded-[10px] bg-amber-500/15 text-amber-600 flex items-center justify-center shrink-0">
                 <i data-lucide="lock" class="w-4 h-4"></i>
             </div>
             <div class="min-w-0">
                 <p class="text-[13px] sm:text-[14px] font-semibold text-amber-950 dark:text-amber-200">
                     Batas Kuota Paket Standar: 50 Produk Aktif
                 </p>
                 <p class="text-[12px] text-amber-800/80 dark:text-amber-300/70 truncate">
                     950 produk lainnya sementara dinonaktifkan dari POS & Toko Online. Data Anda tetap aman.
                 </p>
             </div>
         </div>
         <a href="/billing/packages" class="px-3.5 py-2 rounded-[10px] bg-[#007AFF] text-white text-[12px] font-semibold hover:brightness-105 active:scale-95 transition-all shrink-0">
             Upgrade Paket
         </a>
     </div>
     ```
2. **Badge Status Entitas Terkunci (Tabel Produk/Master)**:
   - Produk yang melebihi kuota ditandai dengan badge: `inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-zinc-500/10 text-zinc-600 dark:text-zinc-300 border border-zinc-500/20`.
   - Menggunakan ikon Lucide `lock` (`w-3 h-3`) + label *"Terkunci (Limitasi Plan)"*.

### B. Modal Sheet Full-Size Data Pruning Previewer (`/settings/storage`)
Ketika Owner ingin memangkas penggunaan storage dan menghapus log audit/komunikasi lama:
1. **Ukuran Modal**: Full-Size XXL (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl mx-auto rounded-[24px] max-h-[92vh]`).
2. **Komponen Header**: Judul "Pratinjau Pembersihan Data Penyimpanan", deskripsi jenis data yang dipilih.
3. **Bento Stat Grid**:
   - Kartu 1: *Total Baris Data Dihapus* (`14.250 Record`, `tabular-nums font-bold text-2xl`).
   - Kartu 2: *Estimasi Ruang Penyimpanan Dihemat* (`145 MB Dibebaskan`, warna hijau emerald `text-emerald-600`).
   - Kartu 3: *Cakupan Tanggal* (`01 Jan 2025 – 31 Des 2025`).
4. **Tabel Sampel Data Teratas**: Menampilkan 10 record teratas yang akan dibersihkan agar user yakin data yang dihapus memang log lawas.
5. **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*)**:
   - `info` Lucide box: *"Tenang: Pembersihan log aktivitas lama tidak akan pernah menghapus data transaksi penjualan, nota kasir, faktur invoice, atau laporan keuangan pembukuan Anda."*
6. **Sticky Action Footer**: Tombol `[ Batal ]` dan tombol destruktif konfirmasi berotorisasi `[ Bersihkan 145 MB Sekarang ]` (merah taktil, 48px).

---

## 19. Protokol & Panduan Audit User Experience (UX) Komprehensif

Setiap kali AI Agent diminta melakukan audit antarmuka (UI/UX) pada modul apa pun di COOCA, evaluasi **WAJIB** mencakup 5 pilar audit UX berikut secara terstruktur:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                 5 PILAR AUDIT UX COOCA                                 │
├──────────────────┬──────────────────┬──────────────────┬──────────────────┬────────────┤
│ 1. Clarity &     │ 2. Ergonomi &    │ 3. Zero-Manual & │ 4. Transparansi  │ 5. Multi-  │
│    Apple HIG     │    Aksesibilitas │    Otomasi Alur  │    Limit & Kuota │ Sektor     │
│  (Bento Canvas,  │   (Touch Target  │ (1-Click Action, │ (SaaS Downgrade, │ (F&B, POS, │
│  Anti-Pill, Typo │   ≥44px, Anti-   │  CRM Auto-Link,  │  Storage Prune & │  Bengkel,  │
│  Overline Murni) │  Zoom iOS 16px)  │  Kamus 1 Kata)   │  No Exaggeration)│  Laundry)  │
└──────────────────┴──────────────────┴──────────────────┴──────────────────┴────────────┘
```

### 19.1 Checklist Pengecekan 5 Pilar UX

1. **Pilar 1: Kepatuhan Bento Apple HIG & Visual Hierarchy**
   - [ ] Apakah kanvas menggunakan rasio Bento Apple HIG yang proporsional dengan *continuous squircle* (`rounded-[20px]`/`[24px]`)?
   - [ ] Apakah halaman bebas dari *eyebrow pill* berlebih? (Gunakan *Pure Typographic Overline* murni).
   - [ ] Apakah seluruh ikon menggunakan Lucide vector icon murni dan 100% bebas dari emoji Unicode?
   - [ ] Apakah kontras teks memenuhi standar WCAG 2.1 AA (minimal 4.5:1 untuk teks biasa)?

2. **Pilar 2: Ergonomi UMKM Senior (Usia 40–65+ Tahun) & Aksesibilitas Sentuh**
   - [ ] Apakah seluruh tombol aksi interaktif memenuhi *touch target* minimum $\ge 44\text{px}$ pada desktop/tablet dan $48\text{px}–52\text{px}$ pada mobile kasir?
   - [ ] Apakah font input form pada perangkat mobile minimal $16\text{px}$ untuk mencegah *auto-zoom* yang mengganggu pada Safari iOS?
   - [ ] Apakah area bawah layar mobile memiliki *safe area padding* (`pb-28` s/d `pb-32` atau `env(safe-area-inset-bottom)`) agar tidak tertutup sticky action bar atau navigation bar browser?
   - [ ] Apakah modal input form menggunakan kanvas lapang **Full-Size XXL** (2-kolom pada desktop dan full bottom sheet pada mobile), bukan modal sempit `max-w-md`?

3. **Pilar 3: Zero-Manual UI, Otomasi Alur Kerja & Multi-Bahasa (i18n / l10n)**
   - [ ] Apakah alur kerja meminimalkan klik berulang (*streamlined workflow*)?
   - [ ] Apakah tombol aksi form menerapkan **Kamus 1 Kata Kerja Tunggal** (`Simpan`, `Batal`, `Hapus`, `Cetak`, `Kirim`, `Void`, `Retur`)?
   - [ ] Apakah dropdown relasi master data dilengkapi tombol *Inline Quick-Add* `[ + ]` untuk input cepat tanpa meninggalkan form utama?
   - [ ] Apakah form dilengkapi *microcopy penenang jiwa* (*No-Panic Feedback*) pada dialog konfirmasi berisiko?
   - [ ] **Audit Teks Multi-Bahasa**: Apakah antarmuka 100% bebas dari string bahasa Indonesia mentah (*hardcoded text*) dan seluruh label/pesan/alert menggunakan helper `{{ __('group.key') }}` atau `window.COOCA_I18N` untuk dukungan dwibahasa penuh (`id` $\leftrightarrow$ `en`)?
   - [ ] Apakah tersedia komponen **Language Switcher Bento Apple HIG** (icon `globe`, opsi `ID` / `EN`, tanpa emoji bendera)?

4. **Pilar 4: Transparansi Kuota SaaS, Storage Footprint & Integritas Data**
   - [ ] Apakah antarmuka menyajikan informasi secara **sederhana, padat, dan jelas** (*Anti-Clutter*, tanpa dinding teks berbelit)?
   - [ ] Apakah antarmuka 100% bebas dari klaim hiperbola/fiktif (*Anti-Hyperbole Mandate*, data wajib matematis `tabular-nums`)?
   - [ ] Apakah kredensial sensitif (API key, token, PIN kasir) dimasking (`••••••••`) dan tidak bocor ke DOM/tabel (*Zero Plaintext Credential Exposure*)?
   - [ ] Apakah batasan kuota langganan (misal saat *downgrade plan*) disajikan secara jelas di UI dengan status badge terkunci tanpa menghapus data historis?
   - [ ] Apakah manajemen pembersihan data (*storage pruning*) menyediakan modal pratinjau (*preview before delete*) yang menampilkan baris data & estimasi MB yang dihemat?
   - [ ] Apakah format angka moneter dan tanggal terformat secara presisi sesuai locale aktif (`Rp 250.000` / `29/09/2026` untuk ID vs `IDR 250,000` / `September 29, 2026` untuk EN)?

5. **Pilar 5: Adaptabilitas Multi-Sektor Bisnis (Context-Aware UI)**
   - [ ] Apakah antarmuka beradaptasi secara cerdas sesuai sektor usaha aktif merchant (misal: SPK & Nopol pada Bengkel, Berat Kg & Loker pada Laundry, No Batch & ED pada Farmasi, Meja & KOT pada Restoran/F&B)?
   - [ ] Apakah terminologi disesuaikan dengan bahasa operasional industri terkait tanpa jargon teknis asing yang membingungkan?

---

### 19.2 Format Standar Laporan Audit UX & Rekomendasi

Saat menyajikan Laporan Audit UX, gunakan format baku berikut:

```markdown
### 1. Ikhtisar & Metodologi Audit UX
- Modul/Halaman yang di-audit: ...
- Perangkat sasaran: Mobile (360–430px) / Tablet Kasir (768–1024px) / Desktop (1280px+)
- Profil Pengguna: Kasir / Owner UMKM / Pelanggan Mandiri

### 2. Temuan Masalah UX (UX Friction Points)
| No | Modul / Elemen | Temuan Masalah | Akar Masalah | Dampak Pengguna | Pilar Terdampak |
|---|---|---|---|---|---|
| 1 | ... | ... | ... | ... | Pilar 1/2/3/4/5 |

### 3. Matriks Perbandingan: Sebelum vs. Rekomendasi Sesudah
| Aspek | Kondisi Saat Ini (Sebelum) | Rekomendasi Solusi (Sesudah) | Standar Acuan |
|---|---|---|---|
| Layout Modal | Modal sempit max-w-md | Modal-First XXL 2-Kolom Lapang | Apple HIG Bento v2.0 |
| Touch Target | Tombol 32px | Tombol sentuh 48–52px | Ergonomi Senior 40–65 th |
| Aksi Form | "Simpan Pengaturan Meja" | "Simpan" (1 kata kerja) | Kamus 1 Kata Tunggal |

### 4. Roadmap Rekomendasi Perbaikan Bertahap
- **P1 (Kritis - Operasional Kasir & Keamanan):** ...
- **P2 (Tinggi - Ergonomi & Alur Pelanggan):** ...
- **P3 (Sedang - Transparansi Kuota & Storage):** ...
```

---

## 20. Standar Konsistensi 3 Panel (Admin, Owner, Customer), Tab Architecture & Information Architecture (IA)

### 20.1 Standar Konsistensi Lintas 3 Panel
1. **Admin Panel (`admin.*`)**: Shell `<x-admin-layout>`, kanvas `#F2F2F7` light / `#000000` dark, kartu bento `rounded-2xl`, header terpadu dengan breadcrumb platform teknis dan metrik multi-tenant.
2. **Owner / User Panel (`app.*` / `owner.*`)**: Shell `<x-app-layout>`, kanvas `#F2F2F7` light / `#000000` dark, kartu bento `rounded-[20px]`–`rounded-[24px]` desktop, touch-first mobile input 16px, tombol ramah sentuhan 48–52px.
3. **Customer Panel (`customer.*` / `storefront.*`)**: Shell `<x-customer-layout>`, kanvas `#FAFAFA` light / `#09090B` dark, kartu bento `rounded-2xl`–`rounded-3xl`, bebas jargon ERP internal, alur belanja cepat tanpa hambatan (*frictionless checkout*).

### 20.2 Struktur Header & Page Title Terpadu (3 Baris Wajib)
Setiap halaman wajib menerapkan susunan 3 baris:
- **Baris 1 (Overline)**: Kategori atau breadcrumb berhuruf kapital murni tanpa kapsul pill (`text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`).
- **Baris 2 (H1 Title + Action)**: Judul halaman (`text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white`) + Badge Status Siklus Entitas + Action Button di pojok kanan atas (`bg-[#007AFF] text-white hover:bg-[#0062CC] rounded-xl px-4 py-2`).
- **Baris 3 (Subtitle / Tabs)**: Deskripsi fungsional 1 baris singkat ATAU Segmented Control Tab Bar.

### 20.3 Arsitektur Tab Navigasi & Pencegahan Desinkronisasi UX
1. **Pill Segmented Control**: Untuk filter kategori / status pada tabel yang sama (`bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-xl`).
2. **Underline Tab Bar**: Untuk membagi section formulir atau master-detail pada entitas yang sama (`border-b border-black/[0.06] dark:border-white/[0.08]`).
3. **Mandat Deep-Linking URL (`?tab=...`)**: Tab internal wajib terhubung dengan query string URL menggunakan watcher Alpine.js agar saat refresh browser atau share link, halaman tetap membuka tab yang aktif.
4. **Larangan Tab untuk Modul Independen**: Fitur yang memiliki alur kerja frekuensi tinggi (seperti Kasir POS) dilarang disembunyikan di dalam tab; wajib memiliki rute dan halaman mandiri.

### 20.4 Information Architecture (IA): Pemisahan Operasional vs Settings Hub
1. **Dilarang Polusi Menu Pengaturan**: Dilarang membuat menu konfigurasi (seperti Pengaturan Printer, Format Nota, Integrasi WA, Setting Pajak) berdiri sendiri di root sidebar.
2. **4 Klaster Menu Baku Sidebar**:
   - **Klaster 1: Operasional Harian** (Dashboard, Kasir POS, Pesanan Masuk, SPK Bengkel / Meja F&B, Surat Jalan).
   - **Klaster 2: Master Data & Katalog** (Produk, Resep BOM, Multi-Gudang & Stok, Pelanggan CRM, Pemasok PO, Karyawan HRM).
   - **Klaster 3: Laporan & Keuangan** (Buku Kas/Bank Auto-Journal, Laporan Penjualan, Laba Rugi).
   - **Klaster 4: Pusat Pengaturan Terpadu (`/settings`)** (Satu menu terpusat yang memuat Sub-Hub: Profil Usaha, Kasir & Nota, Pajak & Bayar, Integrasi, Hak Akses, Plan & Storage).

---

## 21. Standardisasi Mutlak Form Pop-Up Modal Full-Size Lintas Device & Kompatibilitas Light/Dark Mode

### 21.1 Mandat Dimensi Maksimal Modal (Full-Size Canvas Lintas Device)
Dilarang menggunakan modal sempit seperti `max-w-md` atau `max-w-lg` untuk formulir input/edit data operasional atau master data (Produk, Layanan, Pelanggan, Supplier, Stok, dll.). Seluruh modal form wajib memanfaatkan kanvas maksimal secara lapang, lega, dan ergonomis:

1. **Desktop & Widescreen (≥ 1024px, 1280px, 1440px, 1920px)**:
   - **Container Sizing**: `w-full max-w-[96vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] max-h-[92vh] sm:rounded-[24px] flex flex-col overflow-hidden`.
   - **Struktur Bento 2-Kolom / 3-Kolom**: Grid 12-kolom (`grid-cols-1 lg:grid-cols-12 gap-6 items-start`) membagi formulir secara seimbang (misal: 7-kolom untuk data identitas utama dan 5-kolom untuk tarif/kanal/status).
2. **Tablet (640px – 1023px)**:
   - **Container Sizing**: `max-w-[94vw] max-h-[90vh] rounded-[20px]`.
3. **Mobile Smartphone (360px – 639px)**:
   - **Adaptive Full Bottom Sheet**: `fixed inset-x-0 bottom-0 max-h-[96vh] w-full rounded-t-[28px] rounded-b-none flex flex-col overflow-hidden`.
   - **Drag Handle**: Indikator sentuh pill atas (`w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto mt-2.5 mb-1`).
   - **Safe-Area Scroll Padding**: `pb-28` atau `pb-32` pada form scroll container agar elemen input paling bawah tidak tertutup oleh sticky footer action bar.

### 21.2 Aturan Anti-Whitespace Atas & Larangan Duplikasi Header
1. **Dilarang Duplikasi Header di Dalam Kartu Form**:
   - Teks judul atau subtitle modal yang sudah tampil di header modal (seperti deskripsi *"Update service rates, category classification, or sales channel visibility."*) **DILARANG KERAS** ditulis ulang sebagai judul `H4 uppercase` di dalam kartu formulir.
2. **Clean Section Overline**:
   - Setiap sub-kartu form di dalam modal hanya boleh menggunakan header seksi tipografis murni 1 baris yang padat dan presisi (misal: `IDENTITAS & SPESIFIKASI LAYANAN`, `TARIF & BIAYA`, `KANAL PENJUALAN`), dengan styling: `text-[12px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D] pb-1 border-b border-black/[0.04] dark:border-white/[0.06]`.
3. **Flush Top-Alignment**:
   - Kolom kiri dan kolom kanan pada grid desktop wajib rata atas (`items-start`), tanpa margin atau padding berlebih yang menyisakan ruang putih kosong (*empty awkward space*) di bagian atas form.

### 21.3 Token Warna Baku Kompatibilitas 100% Light Mode & Dark Mode
| Elemen Modal | Light Mode Tailwind Token | Dark Mode Tailwind Token |
|---|---|---|
| **Backdrop Blur** | `bg-black/60 backdrop-blur-md` | `bg-black/75 backdrop-blur-md` |
| **Modal Shell / Card** | `bg-white/98 border-black/[0.08] backdrop-blur-2xl` | `dark:bg-[#1C1C1E]/98 dark:border-white/[0.12] dark:backdrop-blur-2xl` |
| **Modal Header Bar** | `bg-[#F2F2F7]/50 border-b border-black/[0.06]` | `dark:bg-white/[0.02] dark:border-b dark:border-white/[0.08]` |
| **Inner Sub-Cards / Bento Box** | `bg-black/[0.02] border-black/[0.04]` | `dark:bg-white/[0.02] dark:border-white/[0.06]` |
| **Input & Select Textfields** | `bg-white border-black/[0.08] text-[#1C1C1E] placeholder:text-black/30` | `dark:bg-[#2C2C2E] dark:border-white/[0.1] dark:text-[#F2F2F7] dark:placeholder:text-white/30` |
| **Focus Ring** | `focus:ring-2 focus:ring-[#007AFF]/50` | `dark:focus:ring-2 dark:focus:ring-[#007AFF]/50` |
| **Sticky Action Footer** | `bg-white border-t border-black/[0.06]` | `dark:bg-[#1C1C1E] dark:border-t dark:border-white/[0.08]` |
| **Primary Action Button** | `bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-sm shadow-[#007AFF]/25` | `bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-sm shadow-[#007AFF]/25` |
| **Cancel / Secondary Button** | `bg-black/[0.05] hover:bg-black/[0.08] text-[#1C1C1E]` | `dark:bg-white/[0.08] dark:hover:bg-white/[0.12] dark:text-[#F2F2F7]` |
| **Checkbox / Switch Box** | `text-[#007AFF] border-black/20 bg-white` | `text-[#007AFF] dark:border-white/20 dark:bg-[#2C2C2E]` |

### 21.4 Standar Arsitektur Backdrop Modal Zero-Gap Edge-to-Edge ($y=0$ Full Viewport Overlay)
1. **Mandat Elemen Backdrop Mandiri (*Standalone Backdrop Overlay*)**:
   - **DILARANG KERAS** meletakkan styling background transparan (`bg-black/40` atau `bg-black/60`) langsung pada pembungkus flex modal (`<div class="fixed inset-0 flex ... bg-black/40">`). Penempatan ini memicu kebocoran header (*topbar stacking leak*) di mana bilah topbar tetap terang dan tidak ter-dim.
   - **WAJIB** memisahkan elemen Backdrop Overlay mandiri di belakang Card Modal:
   ```html
   <!-- 1. Outer Container: z-[200] Full Viewport -->
   <div x-show="showModal" x-cloak
       class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 overflow-y-auto"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       @keydown.escape.window="showModal = false">

       <!-- 2. Standalone Frosted Dark Backdrop (Edge-to-Edge y=0 Full Screen Coverage) -->
       <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"
           @click="showModal = false"></div>

       <!-- 3. Modal Canvas Dialog Card (relative z-10) -->
       <div class="relative z-10 w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] max-h-[95vh] sm:max-h-[92vh] flex flex-col overflow-hidden sm:max-w-[96vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] transition-all"
           @click.outside="showModal = false">
           ...
       </div>
   </div>
   ```
2. **Zero-Gap di Batas Layar Teratas ($y=0$)**:
   - Latar belakang transparan gelap harus menyelimuti 100% viewport dari batas teratas ($y=0$ di bawah address bar browser) hingga ke bawah tanpa celah putih topbar sedikit pun.
3. **Hierarki Z-Index Modal Terstandar**:
   - Sticky Topbar: `z-30`
   - Mobile Sidebar Drawer: `z-40` / `z-50`
   - Modal Utama / Form Dialog: `z-[200]` (CSS Global: `z-index: 99999 !important`)
   - Sub-Modal / Quick-Add Dropdown Dialog: `z-[210]`
   - Alert Confirmation Dialog (Delete/Confirm): `z-[220]`

---

## 22. COOCA OWNER PANEL — COMPREHENSIVE UI/UX HIERARCHY & DESIGN SYSTEM MANIFESTO

Prinsip Utama:
> **"COOCA harus terasa seperti professional business operating system, bukan kumpulan halaman yang dihias."**  
> **"Hierarchy before decoration. Clarity before complexity. Whitespace is part of information architecture."**  
> **"Not everything needs a card. Not every action needs to look important. One action should have one clear visual cue."**

### 22.1 Standar Anatomi Halaman (Standard Page Anatomy)
Setiap halaman di Owner Panel wajib mengikuti alur anatomi terstruktur dari atas ke bawah:
```text
Page
│
├── 1. Breadcrumb (Konteks & Posisi User, mis: Dashboard / Produk)
│
├── 2. Page Header
│   ├── Page Title (H1 Dominan & Jelas)
│   ├── Description (1 Baris Ringkas & Padat)
│   └── Primary Action (Maksimal 1 CTA Utama)
│
├── 3. Summary / KPI (Metrik Kunci — Desktop Grid / Mobile Snap Slider)
│
├── 4. Toolbar
│   ├── Search
│   ├── Primary Filter
│   ├── Secondary Filter / Sort
│   ├── View Switcher
│   └── Secondary Actions (Import, Export, Filter Drawer)
│
├── 5. Main Content
│   ├── Table / Card List
│   ├── Form / Master-Detail
│   ├── Chart / Analytical Visualizer
│   ├── Timeline / Activity Feed
│   └── Empty State (Bila data kosong)
│
└── 6. Pagination / Secondary Footer (Navigasi halaman & status total)
```
*Catatan:* Jangan memaksakan semua elemen jika tidak relevan untuk halaman tertentu. Gunakan hanya yang dibutuhkan.

### 22.2 Hierarchy Pertanyaan Pengguna (6 Core Questions in Cognitive Flow)
Setiap layout halaman harus menjawab secara instan:
1. **WHERE AM I?** (Breadcrumb & Navigasi Aktif)
2. **WHAT IS THIS PAGE?** (Page Title H1 Dominan)
3. **WHAT IS IMPORTANT?** (Status Siklus Entitas / Metrik Kunci / Banner Penting)
4. **WHAT CAN I DO?** (Primary Action & Toolbar Aksi)
5. **WHAT DATA SHOULD I READ?** (Tabel / Form / Chart yang bersih dan scannable)
6. **WHAT SHOULD I DO NEXT?** (Tombol submit, pagination, atau secondary workflow)

### 22.3 Breadcrumb Hierarchy
- Visual weight rendah, font lebih kecil dari Page Title (`text-[11px]`–`text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`).
- Tidak menjadi heading `<h1>` dan tidak bersaing dengan Page Title.
- Memberikan konteks hierarkis: `Dashboard / Penjualan / Pesanan / #INV-00123`.

### 22.4 Page Header Hierarchy
- **Title (H1)**: Paling dominan (`text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white`).
- **Description**: Informatif, padat, singkat (1 baris kalimat, `text-[13px] sm:text-[14px] text-black/60 dark:text-white/60`).
- **Primary Action**: Jelas terlihat, berbobot Primary Blue (`bg-[#007AFF] text-white hover:bg-[#0062CC] rounded-[12px] px-4 py-2 font-semibold`), tetapi tidak mengalahkan dominansi title.

### 22.5 BUTTON & ICON RULE — WAJIB (GLOBAL DESIGN SYSTEM RULE)
Aturan baku tombol dan ikon di seluruh COOCA:

#### A. JANGAN DUPLIKASI MAKNA "ADD" / PLUS
Jika button menggunakan icon plus, **DILARANG KERAS** menambahkan karakter `+` sebagai text label.
- ❌ **SALAH:** `[ + + Tambah Produk ]`
- ❌ **SALAH:** `[ <i data-lucide="plus"></i> + Tambah Produk ]`
- ❌ **SALAH:** `[ + Tambah ]` (jika sudah ada icon plus)
- ✅ **BENAR:** `[ <i data-lucide="plus"></i> Tambah Produk ]`
*Karakter `+` harus berasal dari icon Lucide murni, bukan digandakan dalam text string.*

#### B. SATU ACTION = SATU VISUAL CUE (One Action = One Semantic Visual Cue)
Jangan pernah mengulang makna atau simbol visual yang sama.
- ❌ **SALAH:** `[ <i data-lucide="plus"></i> + Tambah ]`
- ❌ **SALAH:** `[ <i data-lucide="plus"></i> <i data-lucide="plus"></i> Tambah ]`
- ❌ **SALAH:** `[ <i data-lucide="edit"></i> <i data-lucide="pencil"></i> Edit ]`
- ✅ **BENAR:** `[ <i data-lucide="plus"></i> Tambah Produk ]` ATAU `[ Edit ]` ATAU `[ <i data-lucide="pencil"></i> ]` (icon-only dengan aria-label).

#### C. DAFTAR PEMETAAN SEMANTIK IKON RESMI (SEMANTIC ICON REGISTRY)
Ikon wajib memiliki semantic meaning yang tepat. DILARANG menggunakan icon `plus` untuk semua action!
| Action / Maksud | Ikon Lucide Wajib | ❌ Larangan Keras |
|---|---|---|
| **Tambah / Buat Baru** | `plus` | Duplikasi string `+ +` |
| **Edit / Ubah** | `pencil` atau `edit-3` | `plus` |
| **Hapus / Delete** | `trash-2` atau `trash` | `x` (x untuk tutup/batal) |
| **Lihat / Detail** | `eye` | `search` |
| **Cari / Search** | `search` | `eye` |
| **Filter** | `filter` | `sliders` |
| **Sort / Urutkan** | `arrow-up-down` atau `sliders-horizontal` | `plus` |
| **Import / Unggah Data** | `upload` | ❌ `plus` (Dilarang keras `+ Import`) |
| **Export / Unduh Data** | `download` | ❌ `plus` (Dilarang keras `+ Export`) |
| **Pengaturan / Settings** | `settings` | `tool` |
| **Menu Lainnya / More** | `more-horizontal` atau `more-vertical` | `menu` |
| **Kembali / Back** | `arrow-left` | `chevron-left` (kecuali pagination) |
| **Lanjut / Next** | `arrow-right` | `plus` |
| **Simpan / Save** | `check` atau `save` | `plus` |
| **Batal / Tutup / Close**| `x` | `trash` |
| **Segarkan / Refresh** | `refresh-cw` atau `rotate-cw` | `plus` |
| **Salin / Copy** | `copy` | `file` |

#### D. IKON + TEKS vs IKON-SAJA (Icon Recognition Rules)
- **Ikon + Teks**: Digunakan untuk mempercepat recognition aksi utama modul/halaman (`[ <i data-lucide="plus"></i> Tambah Produk ]`, `[ <i data-lucide="upload"></i> Impor Excel ]`, `[ <i data-lucide="download"></i> Ekspor PDF ]`).
- **Tanpa Ikon**: Jika ikon hanya menjadi dekorasi dan tidak meningkatkan pemahaman, **HAPUS IKONNYA**.
- **Ikon-Saja (Icon-Only Buttons)**: Hanya untuk aksi yang sangat familiar dan konteksnya sudah jelas (misal: tombol aksi baris tabel `[edit]` `[trash]` `[eye]`). **WAJIB** memiliki atribut `aria-label="Edit"` / `title="Edit"`, touch-target minimal 44×44px, dan tooltip bila diperlukan.

### 22.6 Action Hierarchy (Hierarki Bobot Aksi)
Gunakan 4 level bobot aksi secara disiplin:
1. **Primary**: Maksimal 1 per area fokus (`bg-[#007AFF] text-white shadow-sm hover:bg-[#0062CC]`).
2. **Secondary**: Tombol pendukung (`bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white hover:bg-black/[0.08]`).
3. **Tertiary**: Tombol teks/link tanpa kotak (`text-[#007AFF] hover:underline px-2 py-1`).
4. **Destructive**: Tombol bahaya (`bg-red-600 text-white hover:bg-red-700` dalam dialog konfirmasi, atau teks merah `text-red-600 hover:bg-red-500/10` di tabel).
*Aturan Emas:* Jangan membuat semua tombol terlihat sebagai primary. Jika semua tombol mencolok, tidak ada tombol yang benar-benar penting.

### 22.7 Card Usage Hierarchy & Anti-Nesting ("Not Everything Needs a Card")
Hindari sindrom **Card-Everything** dan **Card-inside-Card-inside-Card**:
- ❌ **DILARANG:** `Card` $\rightarrow$ `Card` $\rightarrow$ `Card` $\rightarrow$ `Table`.
- Gunakan Card hanya ketika benar-benar diperlukan untuk pengelompokan entitas mandiri.
- Prioritas Pembatas: **Whitespace $\rightarrow$ Section Title $\rightarrow$ Hairline Divider $\rightarrow$ Content**.
- Jika section dapat dipisahkan secara elegan dengan whitespace lapang dan garis tipis (`border-b border-black/[0.06] dark:border-white/[0.08]`), **JANGAN BUNGKUS DENGAN CARD BARU**.

### 22.8 Spacing & Typography Hierarchy
- **Typography Scale**: Page Title (24–32px bold) $\rightarrow$ Section Title (18–20px semibold) $\rightarrow$ Subsection (15–16px medium) $\rightarrow$ Primary Content (14–15px normal/semibold) $\rightarrow$ Secondary Content (13–14px) $\rightarrow$ Metadata (11–12px) $\rightarrow$ Helper Text (12–13px text-black/50).
- **Spacing Scale (8pt Grid)**: Jarak antar-grup lebih besar dari jarak dalam-grup. Breadcrumb $\rightarrow$ Title (4–6px), Title $\rightarrow$ Desc (4–6px), Header $\rightarrow$ Content (20–32px), Section $\rightarrow$ Section (24–36px), Label $\rightarrow$ Input (6–8px), Input $\rightarrow$ Input (14–18px), Row $\rightarrow$ Row (10–14px).

### 22.9 Table Hierarchy (Data Interface Design)
- **Urutan Pemindaian Kolom**: `Primary Information` (Nama Entitas / No. Transaksi tebal) $\rightarrow$ `Secondary Information` (Kategori, Kontak, Tanggal) $\rightarrow$ `Numeric / Financial Data` (`tabular-nums font-semibold` rata kanan) $\rightarrow$ `Status Badge` $\rightarrow$ `Action Column` (rata kanan, subtle).
- Action column tidak boleh menjadi focal point yang mengganggu pembacaan data.
- Gunakan `tabular-nums` untuk semua angka, nominal uang, stok, dan tanggal.
- Hindari tabel yang terlihat seperti spreadsheet padat tanpa ruang bernapas.

### 22.10 Toolbar Hierarchy
- Susunan Toolbar: `Search Input (kiri)` $\rightarrow$ `Primary Filter (Dropdown Status/Kategori)` $\rightarrow$ `Secondary Filter / Sort` $\rightarrow$ `View Switcher` $\rightarrow$ `Secondary Actions (Import/Export/More)`.
- Jika terdapat $>3$ filter, gunakan **Filter Drawer / Popover / Advanced Filter Panel**, jangan memenuhi toolbar dengan puluhan dropdown bertumpuk.

### 22.11 Form Hierarchy
- Form dikelompokkan secara semantik berbasis konsep bisnis:
  - Misal Form Tambah Produk: `Informasi Produk` (Nama, SKU, Kategori) $\rightarrow$ `Harga & Pajak` (Harga Beli, Harga Jual) $\rightarrow$ `Persediaan & Gudang` (Stok, Min. Stok, Gudang).
- Dilarang menyajikan form sebagai rentetan input vertikal tanpa pengelompokan (*no raw endless inputs*).
- Form Pop-Up Modal wajib Full-Size XXL 2-kolom di desktop dan full bottom sheet di mobile.

### 22.12 Detail Page Hierarchy
- Susunan Standar: `Breadcrumb` $\rightarrow$ `Entity Name + Status Badge + Primary Actions Header` $\rightarrow$ `Overview / Key Metrics` $\rightarrow$ `Important Information (Bento Grid 2-Kolom)` $\rightarrow$ `Related Data (Tabel Transaksi / Item)` $\rightarrow$ `Activity / Audit Log`.

### 22.13 Dashboard Hierarchy
- Dashboard **BUKAN** kumpulan kartu KPI acak yang berjejal.
- Susunan Hierarkis: `Page Header` $\rightarrow$ `Business Summary (Omzet, Transaksi, Laba Bersih)` $\rightarrow$ `Operational Highlights (Pesanan Perlu Diproses, Stok Menipis)` $\rightarrow$ `Trend / Chart Visualizer` $\rightarrow$ `Operational Data & Feed Transaksi Terkini`.
- Prioritaskan berdasarkan: 1. Business Importance, 2. Urgency, 3. Frequency, 4. Decision Value.

### 22.14 State Handling Hierarchy (Empty, Loading, Error)
- **Empty State**: Wajib menjawab 3 hal: 1. Apa yang kosong?, 2. Mengapa kosong?, 3. Apa tindakan berikutnya?
  - Contoh: *Judul:* "Belum ada produk" · *Deskripsi:* "Tambahkan produk pertama Anda untuk mulai mengelola stok dan penjualan." · *Aksi:* `[ <i data-lucide="plus"></i> Tambah Produk ]`. DILARANG hanya menulis "No data found".
- **Loading State**: Proportional Skeleton yang merepresentasikan layout asli, progressive loading, zero layout shift (CLS).
- **Error State**: Wajib menjelaskan: 1. Apa yang terjadi, 2. Mengapa, 3. Solusi pengguna (misal: "Data belum dapat dimuat" + keterangan + tombol `[ Coba Lagi ]`).

### 22.15 Visual Priority Testing (3-Second & 10-Second Glanceability Tests)
Setiap halaman wajib lulus 2 pengujian pemindaian visual:
- **3-Second Test**: Dalam 3 detik pertama, pengguna harus langsung tahu:
  1. Halaman apa ini?
  2. Apa konteksnya?
  3. Apa primary action yang dapat dilakukan?
- **10-Second Test**: Dalam 10 detik, pengguna harus langsung tahu:
  1. Di mana data terpenting?
  2. Di mana kontrol filter/pencarian?
  3. Apa status dari item yang dilihat?
  4. Apa tindakan berikutnya yang harus diambil?
*Jika gagal, hierarki visual dan tata letak WAJIB ditata ulang.*

### 22.16 Cognitive Load & Anti-Slop Principles
- **Clarity over Decoration**: UI tenang, tanpa gradien neon, tanpa blob, tanpa glassmorphism berlebihan, tanpa fake pulse dots, tanpa emoji di judul/tombol.
- **Konsolidasi Elemen Duplikat**: Jika dua tombol atau kartu memiliki maksud sama, gabungkan menjadi satu.
- **Zero Business Logic Degradation**: Perbaikan hierarki visual MURNI pada level UI/UX Blade/CSS. Dilarang mengubah alur logika bisnis, controller method, database model, route, maupun hak akses authorization.




