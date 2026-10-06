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
- **Lebih dari 3 aksi sejajar → kelompokkan.** Tampilkan 1 primary + 1 secondary, lalu masukkan sisanya ke menu overflow (`⋯` atau "Lainnya").
- **Aksi per baris tabel**: tampilkan maksimal 1–2 ikon langsung, sisanya masuk menu `⋯`. Jangan memasang 4–5 tombol kecil berdampingan.
- **Label berupa kata kerja spesifik**: "Simpan perubahan", "Kirim pesanan", bukan "OK" atau "Submit". Maksimal 1–3 kata.
- **Ikon-saja hanya untuk ikon yang universal** (tutup, hapus, cari, edit) dan wajib punya `aria-label` + tooltip. Selain itu pakai teks, atau ikon + teks.
- **Hapus tombol yang duplikat fungsinya.** Contoh: "Batal" dan tombol ✕ di modal yang sama → cukup salah satu.
- **Hindari tombol yang selalu nonaktif.** Jelaskan kenapa nonaktif (helper text) atau sembunyikan.

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
| Modal tertimpa elemen lain | `z-index` acak | Pakai skala z-index terpusat (lihat di bawah) |
| Dua tombol saling menempel/bertumpuk di mobile | Layout baris yang tidak turun jadi kolom | `flex-direction: column` di bawah breakpoint, atau grid 1 kolom |
| Elemen bergeser/menimpa saat konten dimuat | Gambar/skeleton tanpa dimensi | Tentukan `aspect-ratio` atau tinggi minimum |
| Margin negatif menarik elemen ke atas elemen lain | Hack spasi | Ganti dengan `gap`/padding |

### Skala z-index terpusat

Definisikan sekali, pakai di mana saja. Jangan memakai angka di luar skala ini.

```css
:root {
  --z-base: 0;
  --z-sticky: 100;      /* header/bar sticky */
  --z-dropdown: 200;
  --z-fab: 300;
  --z-overlay: 400;     /* backdrop */
  --z-modal: 500;
  --z-toast: 600;
  --z-tooltip: 700;
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

---

## Checklist audit

Periksa tiap poin, lalu catat yang gagal.

**Informasi**
- [ ] Apakah tugas utama halaman bisa dikenali dalam 3 detik?
- [ ] Adakah informasi duplikat, dekoratif, atau yang jarang dipakai tetapi tampil permanen?
- [ ] Adakah lebih dari 1 elemen yang berebut menjadi paling menonjol?
- [ ] Adakah teks penjelas yang bisa dipersingkat atau dihapus?

**Tombol**
- [ ] Hanya ada 1 primary per konteks?
- [ ] Adakah lebih dari 3 aksi sejajar tanpa pengelompokan?
- [ ] Apakah label berupa kata kerja yang spesifik?
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
