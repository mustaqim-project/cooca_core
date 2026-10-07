---
name: ui-simplify-layout
description: Menyederhanakan informasi pada UI, merapikan tombol (button/btn), menata posisi tombol, dan memperbaiki layout agar tidak tumpang tindih (overlap). Gunakan skill ini setiap kali user menyebut UI terlalu ramai/penuh/padat, tombol terlalu banyak, tombol menumpuk atau menutupi elemen lain, posisi tombol berantakan, layout berantakan/bertabrakan/overflow/terpotong, "sederhanakan tampilan", "rapikan layout", "tombol tumpang tindih", "redesign halaman biar clean", atau minta review/audit halaman, form, tabel, dashboard, modal, card di Blade/HTML/CSS/Tailwind/Bootstrap/React — walaupun user tidak menyebut kata "skill". Also use when the user says a UI is cluttered, buttons overlap, has too many actions, or wants decluttering, button hierarchy, and a no-overlap layout fix.
---

# UI Simplify & Layout (tanpa tumpang tindih)

Skill ini membuat antarmuka lebih **sederhana**, **jelas**, dan **bebas tumpang tindih**. Fokusnya empat hal:

1. Menyederhanakan **informasi** yang tampil
2. Menyederhanakan **tombol** (jumlah, hierarki, label)
3. Menentukan **posisi tombol** yang konsisten
4. Menjamin **layout tidak tumpang tindih** di semua ukuran layar

Alasan di balik semuanya: pengguna hanya punya perhatian terbatas. Setiap elemen tambahan bersaing dengan elemen yang benar-benar penting, dan setiap elemen yang saling menutupi membuat UI terasa rusak. Jadi tugasnya bukan sekadar "merapikan", tetapi memutuskan **apa yang pantas ditampilkan, di mana, dan dengan bobot visual berapa**.

---

## Alur kerja

Ikuti urutan ini. Jangan langsung menulis kode sebelum langkah 1–3 selesai.

### 1. Pahami konteks (singkat)
Tentukan dari file/screenshot/kode yang diberikan:
- Halaman ini untuk siapa dan **tugas utama pengguna** apa? (contoh: kasir menyelesaikan transaksi, admin menyetujui pengajuan)
- Platform utama: mobile, desktop, atau keduanya? Jika tidak jelas, anggap **mobile-first**.
- Stack: Blade + Bootstrap, Tailwind, React, HTML murni, dll. Pakai stack yang sudah ada, jangan menambah library baru.

Jika tugas utama tidak bisa disimpulkan, ajukan **satu** pertanyaan singkat. Selain itu, buat asumsi, tulis di awal jawaban, lalu kerjakan.

### 2. Audit (temukan masalah)
Jalankan checklist di bagian **Checklist audit** di bawah. Catat temuan sebagai daftar singkat: *apa masalahnya → kenapa mengganggu → solusi*.

### 3. Putuskan (sederhanakan)
Terapkan aturan di bagian **Menyederhanakan informasi** dan **Hierarki & posisi tombol**. Tulis keputusan eksplisit: apa yang **dihapus**, **digabung**, **disembunyikan** (progressive disclosure), dan **dipertahankan**.

### 4. Bangun ulang layout
Terapkan aturan **Layout bebas tumpang tindih**. Hasilkan kode lengkap yang bisa dipakai, bukan potongan abstrak.

### 5. Verifikasi
Jalankan **Uji overlap** sebelum menyerahkan hasil. Laporkan hasilnya secara jujur, termasuk hal yang tidak bisa Anda uji (misalnya tidak ada browser untuk render).

---

## Menyederhanakan informasi

Untuk setiap elemen informasi, tanyakan: *"Apakah pengguna butuh ini untuk menyelesaikan tugas utama di layar ini?"*

| Jawaban | Tindakan |
|---|---|
| Ya, selalu | Tampilkan, beri bobot visual sesuai prioritas |
| Kadang-kadang | Sembunyikan di balik aksi (accordion, "Lihat detail", tab, tooltip, drawer) |
| Jarang / hanya untuk admin | Pindahkan ke halaman/menu lain |
| Duplikat atau dekoratif | Hapus |

Aturan praktis:

- **Satu layar, satu tujuan.** Jika ada dua tujuan yang sama kuat, pisahkan jadi dua tab atau dua langkah.
- **Maksimal 1 elemen paling menonjol** per area (judul halaman atau angka kunci atau aksi utama, bukan semuanya sekaligus).
- **Kurangi label berulang.** Kolom tabel yang isinya sama di semua baris, ikon + teks yang artinya identik, dan judul yang mengulang breadcrumb bisa dihapus.
- **Gabungkan data terkait.** Contoh: `Nama`, `Telepon`, `Email` di tiga kolom → satu sel dengan nama tebal dan kontak di baris kedua yang lebih redup.
- **Batasi kolom tabel di mobile** ke 2–3 kolom paling penting; sisanya dipindah ke baris yang bisa diperluas atau halaman detail.
- **Teks pendek dan konkret.** Ganti paragraf penjelasan dengan satu kalimat atau helper text di bawah field. Hindari kalimat yang menjelaskan hal yang sudah jelas dari label.
- **Angka dan status mendahului penjelasan.** Tampilkan `Rp 2.450.000 · Lunas`, bukan kalimat panjang tentang status pembayaran.
- **Whitespace itu informasi.** Jarak antar-grup lebih besar daripada jarak di dalam grup, sehingga hubungan antar elemen terbaca tanpa garis dan kotak tambahan.
- **Jangan menyembunyikan hal kritis.** Error, peringatan yang menghalangi, dan aksi destruktif harus tetap terlihat jelas.

---

## Hierarki & posisi tombol

### Hierarki (berapa banyak, seberapa menonjol)

| Level | Tampilan | Jumlah per konteks |
|---|---|---|
| **Primary** | Solid, warna brand | **Maksimal 1** |
| **Secondary** | Outline atau tonal | 1–2 |
| **Tertiary** | Teks/link tanpa kotak | sesukanya, tetapi dikelompokkan |
| **Destructive** | Merah; solid hanya di dalam dialog konfirmasi | terpisah dari primary |

Aturan:

- **Satu primary per layar/kartu/modal.** Dua tombol solid yang bersaing membuat pengguna ragu.
- **DILARANG DUPLIKASI MAKNA "ADD" / SIMBOL `+`**: Jika tombol sudah menggunakan ikon plus (`<i data-lucide="plus"></i>`), DILARANG menambahkan karakter `+` pada teks label.
  - ❌ *Salah:* `[ + + Tambah Produk ]`, `[ <i data-lucide="plus"></i> + Tambah Produk ]`, `[ + Tambah ]` (jika ada icon).
  - ✅ *Benar:* `[ <i data-lucide="plus"></i> Tambah Produk ]` (karakter `+` berasal dari ikon murni, bukan teks).
- **SATU ACTION = SATU VISUAL CUE**: Jangan pernah menggandakan icon atau mengulang simbol visual yang sama (`[ plus-icon + Tambah ]` ❌).
- **SEMANTIC ICON REGISTRY (DILARANG PAKAI ICON PLUS UNTUK SEMUA AKSI)**:
  - Tambah $\rightarrow$ `plus`
  - Edit/Ubah $\rightarrow$ `pencil` / `edit-3`
  - Hapus $\rightarrow$ `trash-2`
  - Lihat $\rightarrow$ `eye`
  - Cari $\rightarrow$ `search`
  - Filter $\rightarrow$ `filter`
  - Sort $\rightarrow$ `arrow-up-down` / `sliders-horizontal`
  - Import $\rightarrow$ `upload` (❌ DILARANG icon `plus`)
  - Export $\rightarrow$ `download` (❌ DILARANG icon `plus`)
  - Simpan $\rightarrow$ `check` / `save`
  - Batal/Tutup $\rightarrow$ `x`
- **Lebih dari 3 aksi sejajar → kelompokkan.** Tampilkan 1 primary + 1 secondary, lalu masukkan sisanya ke menu overflow (`⋯` atau "Lainnya").
- **Aksi per baris tabel**: tampilkan maksimal 1–2 ikon langsung, sisanya masuk menu `⋯`. Jangan memasang 4–5 tombol kecil berdampingan.
- **Label berupa kata kerja spesifik**: "Simpan perubahan", "Kirim pesanan", bukan "OK" atau "Submit". Maksimal 1–3 kata.
- **Ikon-saja hanya untuk ikon yang universal** (tutup, hapus, cari, edit) dan wajib punya `aria-label` + tooltip. Selain itu pakai teks, atau ikon + teks.
- **Hapus tombol yang duplikat fungsinya.** Contoh: "Batal" dan tombol ✕ di modal yang sama → cukup salah satu.
- **Hindari tombol yang selalu nonaktif.** Jelaskan kenapa nonaktif (helper text) atau sembunyikan.

### Card Usage Hierarchy & Anti-Nesting ("Not Everything Needs a Card")
- **DILARANG Card di dalam Card di dalam Card**: Hindari `Card > Card > Card > Table`.
- Gunakan Card hanya untuk pengelompokan entitas mandiri.
- Prioritas Pembatas: **Whitespace $\rightarrow$ Section Title $\rightarrow$ Hairline Divider $\rightarrow$ Content**.
- Jika tampilan dapat dipisah dengan rapi menggunakan whitespace lapang dan divider garis tipis, **jangan gunakan card baru**.

### Posisi (di mana)

Gunakan posisi yang **konsisten di seluruh aplikasi**, karena pengguna belajar sekali lalu mengandalkan kebiasaan.

| Konteks | Posisi standar |
|---|---|
| Form | Di bawah form, rata kiri (desktop) atau lebar penuh (mobile). Primary paling kiri/atas, secondary setelahnya |
| Modal / dialog | Footer, rata kanan di desktop: `[Batal] [Primary]`. Di mobile ditumpuk vertikal, primary di atas |
| Header halaman | Aksi utama halaman di kanan atas, sejajar dengan judul |
| Kartu | Aksi di footer kartu atau pojok kanan atas (satu tempat saja) |
| Tabel | Kolom aksi paling kanan; toolbar (cari, filter, tambah) di atas tabel |
| Mobile, aksi utama yang sering dipakai | Bar bawah (sticky bottom) dalam jangkauan jempol |
| Aksi destruktif | **Jauh dari primary** (jarak minimal 16px) dan butuh konfirmasi |

Ukuran dan jarak:

- **Target sentuh minimal 44×44px** (mobile), jarak antar tombol **minimal 8px**.
- Tombol dalam satu grup memakai tinggi dan padding yang sama.
- Tombol jangan lebih dari satu baris teks. Jika label terlalu panjang, perpendek labelnya, bukan memperkecil font.

---

### Audit Khusus: Deteksi 6 Anomali Tombol & CTA (Button Flaws & Placement)

Wajib diperiksa di setiap audit antarmuka (terutama kartu integrasi, modal, dan formulir):

| # | Anomali Visual & Layout | Gejala yang Terlihat | Akar Masalah | Solusi Standar |
|---|---|---|---|---|
| 1 | **Teks Melipat Canggung & Kata Yatim (*Orphan Word Wrap*)** | Kata tunggal jatuh sendirian di baris kedua (mis. `Connect with Official WhatsApp (1-Click` di baris 1, `Meta)` di baris 2). | Label terlalu panjang (> 4 kata), menyertakan tanda kurung keterangan fitur, atau kontainer tidak proporsional. | Perpendek label ke kata kerja inti (maks 2–4 kata: mis. *"Hubungkan WhatsApp Resmi"*). Tambahkan `whitespace-nowrap`. Pindahkan info sekunder ke micro-badge/helper text di luar tombol. |
| 2 | **Posisi Tombol Terputus dari Ajakannya (*Action Proximity Inversion*)** | Teks pengantar mengajak *"Klik tombol di bawah..."*, namun di bawahnya disisipkan 3 kartu langkah panjang, dan tombol asli terlempar ke dasar kartu. | Urutan tata letak terbalik. Blok panduan langkah memisahkan teks ajakan dari tombol eksekusi. | **Action Proximity Rule**: Tombol CTA utama harus langsung menyusul teks ajakan. Urutan logis: `Header` → `Deskripsi` → `Tombol CTA Utama` → `Quiet Stepper (Langkah Berikutnya)`. |
| 3 | **Persaingan Affordance (*Step Cards Menyamar Jadi Tombol*)** | Kotak petunjuk langkah (*1. Klik Connect, 2. Masuk Facebook*) dibungkus kartu putih berborder kontras tinggi mirip tombol klik. | Gaya visual panduan terlalu berat (*clickable affordance* palsu), membuat pengguna bingung mana tombol yang sebenarnya. | Ganti kartu bertingkat dengan **Quiet Stepper Apple HIG**: nomor bulat kecil subtil (`w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[11px] font-bold`), teks abu-abu redup tanpa border tebal atau background hover. |
| 4 | **Lebar Tombol Tidak Proporsional (*Bloated Full-Width Button*)** | Tombol CTA membentang 100% (`w-full`) di kartu desktop yang lebar, membentuk balok raksasa yang kaku dan melelahkan mata. | Memakai `w-full` tanpa pembatas breakpoint responsif atau `max-w-*`. | Gunakan `w-full sm:w-auto px-6 min-h-[44px]` (full di mobile, lebar alami proporsional di tablet/desktop) atau batasi dengan `max-w-sm`. |
| 5 | **Ikon Terhimpit atau Menempel di Tepi (*Squished / Edge-Pinned Icon*)** | Ikon di tombol terhimpit di tepi kiri, gepeng, atau terisolasi aneh saat teks membungkus. | Flexbox tanpa `shrink-0` atau pemisahan wadah ikon yang salah. | Ikon wajib `shrink-0 w-4 h-4`, posisikan rapi di tengah mendampingi teks dengan `inline-flex items-center justify-center gap-2.5`. |
| 6 | **Boundary Clipping pada Banner / Header Kartu** | Teks banner informasi atau header kartu terpotong di tepi atas/bawah kontainer. | Padding vertikal terlalu tipis, overflow tersembunyi yang salah, atau minus margin. | Beri padding vertikal aman (`p-4` / `py-3.5 px-4`), pastikan kontainer memiliki ruang bernapas tanpa memotong baris teks teratas/terbawah. |

---

## Layout bebas tumpang tindih

Penyebab overlap hampir selalu salah satu dari daftar ini. Perbaiki **akar masalahnya**, bukan menambal dengan `z-index` yang makin besar.

### Prinsip

1. **Gunakan alur normal (flow) dulu.** Pakai `flex`/`grid` dengan `gap`. Elemen di dalam flow tidak bisa menimpa tetangganya.
2. **`position: absolute/fixed` hanya untuk elemen yang memang melayang** (dropdown, tooltip, toast, FAB, modal), dan setiap elemen melayang harus punya **ruang cadangan** (padding) di konten di bawahnya.
3. **Satu zona, satu elemen melayang.** Jangan taruh FAB, toast, dan bar bawah di pojok yang sama.
4. **Teks panjang tidak boleh mendorong atau menimpa tombol.** Beri aturan overflow yang eksplisit.
5. **Uji pada ukuran terkecil** (320–360px) dan teks terpanjang, bukan hanya data contoh yang pendek.

### Masalah umum → perbaikan

| Gejala | Penyebab umum | Perbaikan |
|---|---|---|
| Tombol menutupi konten paling bawah | Elemen `fixed` bawah tanpa padding di konten | Beri `padding-bottom` ≥ tinggi bar + safe-area pada kontainer scroll |
| FAB menimpa bar navigasi bawah/toast | Offset `bottom` tidak memperhitungkan elemen lain | Hitung `bottom: calc(tinggi-bar + 16px + env(safe-area-inset-bottom))`, atau hapus FAB dan jadikan tombol di header |
| Header sticky menutupi judul saat scroll ke anchor | Tidak ada kompensasi tinggi header | `scroll-margin-top` pada target anchor |
| Teks panjang menimpa tombol di sampingnya | Flex child tanpa `min-width: 0` | `min-width: 0` + `overflow: hidden; text-overflow: ellipsis; white-space: nowrap` (atau `line-clamp`) pada teks |
| Tombol keluar dari kartu/terpotong di mobile | Lebar tetap (`width: 200px`) atau `nowrap` tanpa wrap | `flex-wrap: wrap`, ganti lebar tetap dengan `min-width` + `flex: 1` |
| Dropdown/tooltip terpotong | Induk memakai `overflow: hidden` | Pindahkan overlay ke portal/`body`, atau buang `overflow: hidden` dari induk |
| Modal tertimpa elemen lain / celah putih topbar | `z-index` acak / background ditaruh di wrapper flex | Gunakan elemen backdrop mandiri (`fixed inset-0 bg-black/60 backdrop-blur-md`), container `z-[200]`, card dialog `relative z-10`, dan pastikan CSS layout mengangkat overlay ke `z-index: 99999 !important` |
| Dua tombol saling menempel/bertumpuk di mobile | Layout baris yang tidak turun jadi kolom | `flex-direction: column` di bawah breakpoint, atau grid 1 kolom |
| Elemen bergeser/menimpa saat konten dimuat | Gambar/skeleton tanpa dimensi | Tentukan `aspect-ratio` atau tinggi minimum |
| Margin negatif menarik elemen ke atas elemen lain | Hack spasi | Ganti dengan `gap`/padding |

### Skala z-index terpusat

Definisikan sekali, pakai di mana saja. Jangan memakai angka di luar skala ini.

```css
:root {
  --z-base: 0;
  --z-sticky: 30;       /* header/bar sticky */
  --z-sidebar: 50;      /* sidebar navigation drawer */
  --z-dropdown: 60;
  --z-fab: 70;
  --z-modal: 200;       /* modal backdrop & canvas (z-index: 99999) */
  --z-submodal: 210;    /* nested sub-modals / quick add */
  --z-alert: 220;       /* critical confirmation dialogs */
  --z-toast: 99999;     /* system toasts */
}
```

### Pola siap pakai

**Baris aksi yang tidak pernah bertumpuk (CSS murni)**

```css
.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
}
.actions > .btn { min-height: 44px; }

@media (max-width: 480px) {
  .actions { flex-direction: column-reverse; }   /* primary di atas */
  .actions > .btn { width: 100%; }
}
```

**Baris dengan teks panjang + tombol (tidak saling menimpa)**

```css
.row { display: flex; align-items: center; gap: 12px; }
.row__text { flex: 1; min-width: 0; }            /* kunci: min-width: 0 */
.row__title {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.row__action { flex: none; }
```

**Bar aksi sticky bawah (mobile) tanpa menutupi konten**

```css
.page-content {
  padding-bottom: calc(72px + env(safe-area-inset-bottom));  /* 72px = tinggi bar */
}
.bottom-bar {
  position: fixed;
  inset: auto 0 0 0;
  z-index: var(--z-sticky);
  display: flex;
  gap: 8px;
  padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
  background: var(--surface, #fff);
  border-top: 1px solid var(--border, #e5e7eb);
}
```

**Menu overflow untuk aksi tabel (Bootstrap 5)**

```html
<div class="dropdown">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-label="Aksi lainnya">⋯</button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="#">Lihat detail</a></li>
    <li><a class="dropdown-item" href="#">Duplikat</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item text-danger" href="#">Hapus</a></li>
  </ul>
</div>
```

Padanan Tailwind: `flex flex-wrap gap-2 justify-end`, `min-w-0 truncate`, `pb-[calc(72px+env(safe-area-inset-bottom))]`.

**Horizontal Snap Slider (Hemat 75% scroll di mobile, tanpa tumpukan kartu raksasa)**

```html
<!-- Mobile: Geser Horizontal (Swipe) | Desktop: Grid 4-Kolom -->
<div class="flex gap-3 overflow-x-auto snap-x snap-mandatory no-scrollbar -mx-4 px-4 pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:overflow-visible sm:mx-0 sm:px-0">
  <div class="snap-start shrink-0 w-[240px] sm:w-auto p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm">
    <p class="text-[12px] text-black/50 dark:text-white/50">Total Penjualan</p>
    <p class="text-[20px] font-bold tabular-nums mt-1">Rp 12.450.000</p>
  </div>
</div>
```

**Grouped Inset List (Opsi Pengaturan/Form 1 Kotak, iOS Settings Style)**

```html
<!-- Menggabungkan 5 opsi menjadi 1 kartu padat, bukan 5 kartu terpisah -->
<div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 divide-y divide-black/5 dark:divide-white/5 overflow-hidden shadow-sm">
  <div class="p-3.5 sm:p-4 flex items-center justify-between min-h-[48px]">
    <span class="text-[13.5px] font-semibold text-black dark:text-white">Auto Kirim Struk</span>
    <input type="checkbox" class="shrink-0">
  </div>
  <div class="p-3.5 sm:p-4 flex items-center justify-between min-h-[48px]">
    <span class="text-[13.5px] font-semibold text-black dark:text-white">Pemberitahuan Stok Menipis</span>
    <input type="checkbox" class="shrink-0">
  </div>
</div>
```

**Standar Pop-Up Modal Form (Full-Size Canvas XXL & Dual Light/Dark Mode)**

Dilarang keras menggunakan modal sempit (`max-w-md`/`max-w-lg`) untuk form input data:
1. **Desktop (≥1024px)**: `max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px] w-full max-h-[92vh] sm:rounded-[24px] flex flex-col overflow-hidden` (Bento 2-Kolom 7:5/6:6).
2. **Tablet (640–1023px)**: `max-w-[94vw] max-h-[90vh] rounded-[20px]`.
3. **Mobile (<640px)**: `fixed inset-x-0 bottom-0 max-h-[96vh] w-full rounded-t-[28px] rounded-b-none flex flex-col` (Adaptive Bottom Sheet, safe-area `pb-28`).
4. **Anti-Whitespace Atas**: Dilarang menduplikasi subtitle modal di dalam sub-card form sebagai `H4 uppercase`. Gunakan label seksi 1 baris yang padat dan pastikan kolom kiri/kanan rata atas (*flush top-aligned*).
5. **Kompatibilitas Light/Dark**: `bg-white dark:bg-[#1C1C1E]` modal shell, `bg-[#F2F2F7]/50 dark:bg-white/[0.02]` header bar, `bg-black/[0.02] dark:bg-white/[0.02]` sub-cards, `bg-white dark:bg-[#2C2C2E]` inputs, border `border-black/[0.08] dark:border-white/[0.1]`, teks `text-[#1C1C1E] dark:text-[#F2F2F7]`.

---

## Checklist audit

Periksa tiap poin, lalu catat yang gagal.

**Informasi & Tata Letak Mobile (Beyond Bento)**
- [ ] Apakah tugas utama halaman bisa dikenali dalam 3 detik?
- [ ] **Anti Infinite Card Bloat (Mobile)**: Apakah deretan metrik (KPI) atau alur langkah menggunakan **Horizontal Snap Slider** / **Compact Stepper** daripada menumpuk 4–5 kartu Bento vertikal yang membuat halaman molor panjang?
- [ ] **Grouped Inset List (Mobile)**: Apakah opsi formulir/pengaturan disatukan ke dalam 1 kartu dengan pembatas `divide-y`, bukan dipisah jadi 5 kartu berat yang boros ruang?
- [ ] **Modal Form Full-Size Lintas Device**: Apakah semua pop-up modal form menggunakan ukuran maksimal (**XXL 2-kolom di Desktop**, **Adaptive Bottom Sheet di Mobile**), bebas dari duplikasi subtitle header, dan 100% kompatibel Light Mode & Dark Mode?
- [ ] Adakah informasi duplikat, dekoratif, atau yang jarang dipakai tetapi tampil permanen?
- [ ] Adakah lebih dari 1 elemen yang berebut menjadi paling menonjol?
- [ ] Adakah teks penjelas yang bisa dipersingkat atau dihapus?

**Tombol & CTA (Button & Placement)**
- [ ] Hanya ada 1 primary per konteks?
- [ ] Adakah lebih dari 3 aksi sejajar tanpa pengelompokan?
- [ ] Apakah label berupa kata kerja yang spesifik dan padat (maksimal 2–4 kata)?
- [ ] **Bebas kata yatim (*no orphan words*)**: apakah teks tombol tidak melipat canggung meninggalkan 1 kata terisolasi di baris kedua?
- [ ] **Action Proximity Rule**: jika copy mengajak "klik tombol di bawah", apakah tombol CTA langsung menyusul teks ajakan (tidak terhalang oleh blok kartu langkah)?
- [ ] **Affordance Clarity**: apakah panduan langkah (stepper) berwujud indikator alur subtil (*quiet stepper*), bukan kartu tebal yang menyerupai tombol klik?
- [ ] **Proporsi Lebar**: apakah tombol CTA di desktop proporsional (`sm:w-auto` / `max-w-sm`), bukan balok raksasa `w-full` yang membentang kaku?
- [ ] **Keseimbangan Ikon**: apakah ikon di tombol memiliki `shrink-0` dan sejajar rapi (`inline-flex items-center justify-center gap-2.5`), tidak terhimpit di tepi kiri?
- [ ] Apakah aksi destruktif terpisah dari primary dan dikonfirmasi?
- [ ] Apakah posisi tombol sama dengan halaman lain di aplikasi?
- [ ] Apakah target sentuh ≥ 44px dengan jarak ≥ 8px?

**Layout**
- [ ] Adakah elemen `absolute`/`fixed` tanpa ruang cadangan di konten?
- [ ] Adakah elemen melayang yang menempati zona yang sama (FAB, toast, bar bawah)?
- [ ] Adakah teks panjang tanpa aturan overflow di dekat tombol?
- [ ] Adakah lebar tetap (px) yang rusak di layar sempit?
- [ ] Adakah `overflow: hidden` yang memotong dropdown/tooltip?
- [ ] Adakah `z-index` di luar skala terpusat?
- [ ] Apakah halaman bisa scroll horizontal secara tidak sengaja?

---

## Uji overlap (sebelum menyerahkan hasil)

Jika ada browser/alat render, tes pada lebar **320, 375, 768, 1280px**. Jika tidak ada, lakukan penelusuran kode dan nyatakan bahwa verifikasi visual belum dilakukan.

Untuk setiap lebar, pastikan:

1. Tidak ada elemen yang saling menimpa (tombol vs teks, FAB vs bar bawah, header vs konten).
2. Tidak ada scroll horizontal.
3. Semua tombol terlihat penuh dan bisa disentuh.
4. Dengan teks terpanjang yang masuk akal (nama 60 karakter, angka 12 digit), layout tetap utuh.
5. Konten paling bawah masih bisa digulir sampai terlihat sepenuhnya di balik bar sticky.
6. Dropdown, tooltip, dan modal tampil utuh tanpa terpotong.

Snippet cepat untuk mendeteksi overflow di console browser:

```js
[...document.querySelectorAll('*')].filter(e => e.scrollWidth > e.clientWidth + 1 && getComputedStyle(e).overflowX === 'visible')
  .forEach(e => console.log('overflow:', e));
```

---

## Format jawaban

Gunakan struktur ini (bahasa mengikuti bahasa user):

```markdown
## Ringkasan
1–2 kalimat: apa masalah utamanya dan pendekatan yang dipilih. Sebutkan asumsi jika ada.

## Temuan
- Masalah → alasan → solusi (singkat, urut dari dampak terbesar)

## Keputusan penyederhanaan
- Dihapus: …
- Digabung: …
- Disembunyikan (progressive disclosure): …
- Dipertahankan: …

## Kode hasil
(kode lengkap yang bisa langsung dipakai, sesuai stack)

## Verifikasi
- Yang sudah diuji / yang belum bisa diuji
```

Jika user hanya meminta saran atau review (tanpa kode), lewati bagian **Kode hasil** dan beri rekomendasi konkret yang berurutan.

---

## Contoh singkat

**Masalah:** Setiap baris tabel punya 5 tombol (Lihat, Edit, Duplikat, Arsip, Hapus) berjajar. Di mobile tombol saling menempel dan menutupi nama pelanggan.

**Keputusan:**
- Tampilkan 1 aksi langsung: **Lihat** (ikon + `aria-label`)
- Pindahkan Edit, Duplikat, Arsip, Hapus ke menu `⋯` (Hapus dipisah divider dan berwarna merah)
- Kolom nama diberi `min-width: 0` + ellipsis, kolom aksi `flex: none`
- Di mobile, kolom `Email` dan `Tanggal` dipindah ke baris kedua yang lebih redup di bawah nama

**Hasil:** 5 elemen menjadi 2, tidak ada yang bisa saling menimpa, dan nama tetap terbaca.

---

## Hal yang dihindari

- Menambah elemen baru (banner, ikon, penjelasan) padahal tugasnya menyederhanakan.
- Menyelesaikan overlap dengan menaikkan `z-index` tanpa mencari penyebabnya.
- Menghapus informasi yang dibutuhkan untuk tugas utama demi tampilan yang bersih. Jika ragu, sembunyikan di balik aksi, jangan hapus.
- Mengubah warna brand, font, atau gaya keseluruhan jika tidak diminta. Fokus pada struktur, hierarki, dan posisi.
- Mengubah logika bisnis atau nama field/route saat merapikan tampilan.
- Mengklaim "sudah diuji di semua ukuran layar" jika hanya membaca kode.
