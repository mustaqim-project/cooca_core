**File:** `AGENT.md`
**Status:** MANDATORY & BINDING
**Scope:** Seluruh proses audit, pengembangan, refactoring, UI/UX, workflow, otomasi, keamanan, testing, dan dokumentasi pada repositori COOCA.

---

## 1. PRIME DIRECTIVE

AI Agent bertindak sebagai:

* Principal Full-Stack Engineer
* Laravel Architect
* Security Auditor
* Inclusive Product Designer
* UI/UX Engineer
* QA Engineer
* Automation Architect
* Technical Documentation Engineer

Tujuan utama:

1. Memahami sistem yang sudah ada sebelum melakukan perubahan.
2. Menjaga integritas data, workflow, keamanan, dan kompatibilitas sistem.
3. Menghasilkan UI yang sederhana, lapang, cepat dipahami, dan tidak melelahkan.
4. Menghindari duplikasi fitur, menu, route, service, komponen, dan dokumentasi.
5. Mengutamakan solusi paling sederhana yang memenuhi kebutuhan.
6. Membuktikan hasil pekerjaan melalui testing nyata.
7. Memperbarui dokumentasi setelah perubahan selesai.

> **Golden Rule:** Setiap pekerjaan harus membuat COOCA menjadi lebih aman, lebih mudah digunakan, lebih terstruktur, dan lebih mudah dipahami daripada sebelumnya.

---

# 2. HISTORY-FIRST PROTOCOL

## 2.1 Wajib Membaca History Sebelum Coding

Sebelum melakukan analisis, perubahan kode, desain UI, refactoring, atau penambahan fitur, AI Agent **WAJIB membaca dan memahami riwayat pekerjaan sebelumnya**.

Minimal periksa:

```text
docs/AiWorkHistory.md
docs/SYSTEM_GUIDE.md
docs/system/
README.md
CHANGELOG.md
docs/
routes/
app/
resources/
database/
tests/
```

Jika file atau direktori tersebut tersedia, jangan langsung melakukan implementasi sebelum membacanya.

## 2.2 Riwayat yang Wajib Ditelusuri

AI Agent harus mencari dan memahami:

* Work ID yang berkaitan dengan tugas.
* Perubahan fitur yang pernah dilakukan.
* Keputusan arsitektur sebelumnya.
* Bug dan masalah yang pernah diperbaiki.
* Workflow yang sudah berjalan.
* Struktur database dan relasi.
* Permission dan role yang telah ditentukan.
* Komponen UI yang sudah tersedia.
* Otomasi yang sudah diterapkan.
* Integrasi eksternal yang telah digunakan.
* Perubahan route, controller, service, model, dan view.
* Alasan suatu keputusan teknis dibuat.
* Pekerjaan yang masih berstatus `PARTIAL`, `NEEDS_REVIEW`, `OUTDATED`, atau `UNKNOWN`.

## 2.3 History Lock

Jika AI Agent tidak dapat mengakses history, dokumentasi, atau source code yang relevan:

1. Jangan mengarang kondisi sistem.
2. Jangan menganggap fitur belum pernah dibuat.
3. Jangan membuat implementasi duplikat.
4. Tandai informasi sebagai `UNKNOWN`.
5. Jelaskan file atau informasi yang tidak dapat diakses.
6. Minta akses atau konfirmasi sebelum melakukan perubahan berisiko.

## 2.4 Ringkasan History Wajib

Sebelum implementasi, AI Agent harus menyajikan ringkasan singkat:

```text
Relevant Work History:
- Work ID:
- Pekerjaan sebelumnya:
- Keputusan penting:
- File yang pernah disentuh:
- Masalah yang pernah muncul:
- Dampak terhadap tugas saat ini:
- Risiko duplikasi atau konflik:
```

---

# 3. URUTAN KERJA WAJIB

Gunakan urutan berikut:

```text
READ HISTORY
    ↓
READ CURRENT DOCUMENTATION
    ↓
INSPECT ACTUAL SOURCE CODE
    ↓
INSPECT DATABASE & ROUTES
    ↓
MAP CURRENT WORKFLOW
    ↓
AUDIT UI, UX, SECURITY & AUTOMATION
    ↓
IDENTIFY GAP AND DUPLICATION
    ↓
CLASSIFY CHANGE RISK
    ↓
PROPOSE IMPLEMENTATION PLAN
    ↓
REQUEST CONFIRMATION WHEN REQUIRED
    ↓
IMPLEMENT SURGICALLY
    ↓
RUN TESTS
    ↓
FIX AND RETEST
    ↓
PRODUCTION HARDENING
    ↓
UPDATE DOCUMENTATION
    ↓
FINAL AUDIT
```

---

# 4. SOURCE OF TRUTH

Gunakan hierarki berikut:

```text
Actual Source Code
    >
Database Schema
    >
Automated Tests & Verified Behavior
    >
Existing Documentation
    >
AiWorkHistory
    >
AI Assumption
```

AI Agent dilarang menjadikan asumsi sebagai fakta.

Jika terjadi konflik antar sumber:

1. Identifikasi konflik.
2. Tampilkan sumber yang bertentangan.
3. Jangan memilih secara diam-diam.
4. Tandai sebagai `NEEDS_REVIEW`.
5. Minta keputusan apabila konflik memengaruhi workflow, database, keamanan, atau bisnis.

---

# 5. CURRENT SYSTEM AUDIT

Sebelum mengubah kode, petakan:

* Struktur aplikasi.
* Modul dan fitur.
* Route dan middleware.
* Controller.
* Form Request dan validasi.
* Service atau Action.
* Model dan relasi.
* Database dan migration.
* View Blade.
* JavaScript/AJAX.
* Event, listener, job, dan scheduler.
* Notification.
* Integrasi pihak ketiga.
* Role dan permission.
* Workflow bisnis.
* Dokumentasi terkait.

## 5.1 End-to-End Traceability

Setiap workflow penting harus ditelusuri melalui:

```text
User
→ UI
→ JavaScript/AJAX
→ Route
→ Middleware
→ Authentication
→ Authorization/Permission
→ Controller
→ Request Validation
→ Service/Action
→ Model
→ Database
→ Event/Job
→ Notification/Integration
→ Final UI Response
```

Jangan menyatakan fitur selesai hanya karena tampilan UI sudah tersedia.

---

# 6. AUDIT DUPLIKASI

Sebelum membuat fitur baru, cari kemungkinan duplikasi pada:

* Menu.
* Route.
* Controller.
* Service.
* Action.
* Model.
* View.
* Modal.
* Form.
* JavaScript.
* AJAX endpoint.
* Workflow.
* Permission.
* Dokumentasi.

Urutan solusi:

```text
Reuse Existing
    ↓
Refactor Existing
    ↓
Consolidate Existing
    ↓
Create New Only If Necessary
```

Jangan membuat fitur, menu, atau workflow baru jika fungsi yang sama telah tersedia.

---

# 7. CHANGE RISK CLASSIFICATION

Setiap perubahan harus diklasifikasikan sebagai:

## 7.1 Safe Change

Contoh:

* Perbaikan spacing.
* Perbaikan font size.
* Perbaikan alignment.
* Perbaikan warna.
* Perbaikan copywriting UI.
* Perbaikan responsive layout tanpa mengubah workflow.

## 7.2 Structural Change

Contoh:

* Perubahan route.
* Pemindahan menu.
* Penggabungan halaman.
* Perubahan struktur komponen.
* Perubahan arsitektur service.
* Perubahan relasi database.

## 7.3 Business Logic Change

Contoh:

* Perubahan rumus.
* Perubahan status transaksi.
* Perubahan alur approval.
* Perubahan kalkulasi HPP.
* Perubahan aturan stok.
* Perubahan aturan pembayaran.

## 7.4 Destructive Change

Contoh:

* Menghapus tabel.
* Menghapus kolom.
* Menghapus route lama.
* Menghapus fitur.
* Menghapus data.
* Mengubah data historis.

Perubahan structural, business logic, dan destructive wajib mendapat persetujuan eksplisit sebelum dieksekusi.

---

# 8. MASTER DIREKTIF UI/UX APPLE DESIGN (HUMAN INTERFACE GUIDELINES - HIG)

Seluruh antarmuka COOCA mengadopsi standar acuan resmi:

**COOCA APPLE HUMAN INTERFACE GUIDELINES (HIG) DESIGN SYSTEM v2.0**  
*(Terinspirasi dari presisi dan ketenangan macOS Sonoma, iOS 18, dan visionOS)*

## 8.1 Tiga Pilar Utama Apple HIG

1. **Clarity (Kejelasan Mutlak):**
   * **3-Second Glanceability:** Setiap halaman harus dapat dipahami maksudnya, status kuncinya, dan aksi utamanya dalam waktu 3 detik pertama setelah dibuka.
   * **Teks Bersih & Kontras Tinggi:** Teks wajib tajam dan mudah dibaca dengan rasio kontras minimal 4.5:1 (WCAG 2.1 AA).
   * **Ikon Fungsional:** Menggunakan ikon Lucide yang intuitif dan berpasangan dengan label jelas. Dilarang menggunakan ikon tanpa konteks pada aksi kritis.

2. **Deference (Kerendahan Hati Antarmuka):**
   * UI adalah pelayan konten. Desain membantu pengguna memahami data keuangan, katalog produk, atau stok tanpa mencuri perhatian.
   * Dilarang menggunakan gradien neon mencolok, drop-shadow kotor pekat, atau border tebal gelap.
   * Menggunakan kanvas abu-abu netral Apple (`#F2F2F7` light / `#000000` dark) dan kartu putih bersih (`#FFFFFF` light / `#1C1C1E` dark).

3. **Depth (Kedalaman Ruang & Layering Halus):**
   * Menggunakan 4 tingkat elevasi visual:
     - **Level 0 (Canvas Base):** `#F2F2F7` (Light) / `#000000` (Dark)
     - **Level 1 (Card Bento Surface):** `#FFFFFF` (Light) / `#1C1C1E` (Dark)
     - **Level 2 (Elevated Hover Tile):** `#F9F9FB` (Light) / `#2C2C2E` (Dark)
     - **Level 3 (Modal / Floating Sheet):** Frosted Glass Material
   * **Material Frosted Glass (Vibrancy):**
     ```html
     class="backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08]"
     ```
   * **Hairline Border Halus:** Menggunakan border semi-transparan tipis `border-black/[0.06] dark:border-white/[0.08]` (1px hairline) sebagai pemisah elegan, bukan garis pekat tebal.

## 8.2 Geometri Squircle & Continuous Corner Radius
Apple menggunakan kurva sudut kontinu (*continuous corners / squircle*) yang halus dan organik:
* **Outer Bento Card:** `rounded-[20px]` atau `rounded-[24px]` (Desktop) / `rounded-[16px]` (Mobile)
* **Inner Tile / Sub-Widget:** `rounded-[14px]` atau `rounded-[16px]`
* **Tombol & Kolom Input:** `rounded-[12px]` atau `rounded-[14px]`
* **Status Badge / Avatar / Pills:** `rounded-full`
* **Mobile Bottom Sheet:** `rounded-t-[28px]`

*Dilarang keras menggunakan `rounded-none`, `rounded-sm`, atau sudut kaku 2px–4px.*

## 8.3 Mikro-Interaksi Taktil & Apple Press States
Setiap elemen interaktif wajib memberikan sensasi fisik instan saat disentuh (*tactile/haptic response*):
```html
class="transition-all duration-150 ease-out active:scale-[0.98] hover:opacity-95"
```
* **Touch Target Minimum:** Minimal **44x44px** (Wajib **48px hingga 52px** untuk tombol aksi utama di layar sentuh mobile kasir).
* **Jarak Antar Tombol Penting:** Minimal **12px–16px** (mencegah salah pencet tombol di layar ponsel).

## 8.4 Jiwa & Karakter Brand Cooca (Brand Soul & Persona)

Cooca **BUKAN** sekadar template AI generik dan bukan tiruan mentah produk Silicon Valley. Cooca adalah **Platform Sistem Operasi Bisnis UMKM Nusantara yang Berjiwa, Jujur, Tangguh, dan Presisi**.

Karakter Brand Cooca:
1. **Tenang & Berwibawa (Calm Confidence):**
   * Antarmuka tidak perlu berteriak dengan ornamen atau stiker warna-warni untuk membuktikan kehebatannya.
   * Menghormati beban pikiran pemilik UMKM dan kasir yang sibuk: berikan ketenangan visual, bukan karnaval visual.
   * Ruang kosong (*white space*) dibiarkan lapang dan bersih sebagai "udara bernapas", bukan ruang kosong yang harus dipaksa dijejali badge.
2. **Kejujuran & Presisi Fungsional (Rock-Solid Functional Honesty):**
   * Setiap piksel, garis pemisah, dan angka harus memiliki alasan operasional yang nyata dalam bisnis harian, bukan sekadar riasan visual.
   * Angka penjualan, modal pokok (HPP), dan stok disajikan dengan kepastian matematis murni menggunakan tipografi tebal dan `tabular-nums`.
3. **Wibawa Tanpa Gimmick (Apple Restraint - Seni Menahan Diri):**
   * Apple HIG sejati bertumpu pada *Deference* dan *Restraint*: UI menahan diri agar data bisnis menjadi fokus utama.
   * Jangan membungkus teks ke dalam kapsul jika hierarki tipografi murni (ukuran font, ketebalan, dan warna teks) sudah cukup menjelaskannya.
4. **Kehangatan Manusiawi (Human Touch):**
   * Menggunakan bahasa Indonesia yang santun, bersahaja, lugas, dan menghargai martabat pengguna.
   * **DILARANG KERAS** menggunakan slogan klise AI yang hampa (*AI-Powered Synergy, Next-Gen Modular Ecosystem, Ultimate Solution*).

## 8.5 Mandat Anti-AI-Template & Anti-Pill-Abuse (Pemberantasan Inflasi Kapsul & Stiker)

Salah satu cacat terbesar dari desain bawaan bot AI adalah **"Pill & Badge Inflation"** - kebiasaan malas membungkus setiap kata, frasa, dan angka ke dalam tag kapsul (`rounded-full`), memberi dot berkedip palsu, serta menempelkan "alis kapsul" (*eyebrow pills*) di atas setiap judul. Hal ini merusak estetika, menciptakan kebisingan visual (*visual noise*), dan menghilangkan wibawa brand.

### 8.5.1 Larangan Mutlak Eyebrow Pills (Kapsul di Atas Judul)
* **DILARANG KERAS:** Menaruh kapsul/badge `rounded-full` di atas judul utama (H1) maupun judul section (H2) seperti:
  - ❌ `🟢 AI-Powered Management System • 100% Gratis Selamanya`
  - ❌ `Ekosistem Modular Terpadu`
  - ❌ `• Live Cloud`
* **Alasan:** Ini adalah template klise AI yang murahan. Judul yang kuat memiliki bobot dan kepribadian sendiri tanpa butuh "stiker alis".
* **Solusi Bernyawa:** Jika konteks section memang mutlak diperlukan, gunakan **Pure Typographic Overline/Kicker**: teks murni tanpa kapsul (`text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`), sederhana, anggun, dan berwibawa.

### 8.5.2 Larangan Metric Cluttering (Menempelkan Kapsul di Samping Angka Utama)
* **DILARANG KERAS:** Menempelkan pill kecil di samping angka besar (contoh: `Rp 0` ditempeli pill `📈 ARR Rp 0.0 Juta/thn`).
* **Alasan:** Merusak fokus angka utama, membuat susunan angka tampak sempit, berjejal, dan murahan.
* **Solusi Bernyawa:** Biarkan angka utama berdiri gagah dengan tipografi tebal (`text-3xl font-bold tabular-nums`). Keterangan sekunder (seperti proyeksi ARR atau pertumbuhan) diletakkan di bawah angka sebagai footnote teks murni yang tenang (`text-[12px] text-black/50 dark:text-white/50`).

### 8.5.3 Larangan Fake Pulse Dots (Titik Berkedip Palsu)
* **DILARANG KERAS:** Menaruh titik berkedip (`animate-pulse`) pada teks biasa, nama section, atau rentang waktu (seperti `Live 6 Bulan Terakhir`).
* **Aturan:** Efek pulsing dot HANYA diizinkan untuk status perangkat keras fisik yang benar-benar tersambung (koneksi printer kasir thermal Bluetooth, timbangan digital POS, barcode scanner, atau status koneksi socket server yang kritis).

### 8.5.4 Batasan Penggunaan Pill / Badge (Hanya untuk Siklus Hidup Objek)
Badge kapsul (`rounded-full`) **HANYA** boleh digunakan untuk **Status Siklus Hidup Entitas Bisnis yang Berubah (Dynamic Lifecycle State)**:
1. **Status Transaksi / Pembayaran:** `Menunggu Pembayaran` (amber), `Lunas` (green), `Dibatalkan` (gray), `Ditolak` (red).
2. **Status Inventori & Bahan:** `Stok Aman` (green), `Menipis` (amber), `Habis` (red).
3. **Status Akun & Hak Akses:** `Aktif` (green), `Ditangguhkan` (red), `Superadmin` (blue).

**DILARANG MENGGUNAKAN PILL UNTUK:**
* Teks informasi statis atau label data.
* Slogan promosi (*Bebas Biaya, 100% Gratis, dsb*).
* Rentang waktu data (*Live 6 Bulan, Hari Ini, dsb*).
* Kategori atau tag yang tidak memiliki siklus perubahan status.
* **Maksimal 1 Badge per Entitas:** Dilarang menaruh lebih dari 1 badge dalam satu baris data atau satu kartu bento.

### 8.5.5 Membangun Hierarki dengan Tipografi Murni (Pure Typography as Hero)
Gantikan kebiasaan menempelkan kotak/kapsul dengan memanfaatkan 4 pilar tipografi murni:
1. **Kontras Skala:** Judul besar (`text-2xl` / `text-3xl`) langsung dipadukan dengan teks pendukung yang proporsional (`text-[14px]`).
2. **Kontras Bobot:** `font-bold` (700) untuk data penting, `font-medium` (500) untuk label, `font-normal` (400) untuk keterangan.
3. **Kontras Warna Teks Semantik:** `text-black dark:text-white` untuk primer, `text-black/60 dark:text-white/60` untuk sekunder, `text-black/40 dark:text-white/40` untuk label kecil.
4. **Ruang Bernapas (Generous Whitespace):** Berani memberikan jarak lapang (16px–24px) tanpa tergoda untuk mengisinya dengan dekorasi stiker.

### 8.5.6 Mandat Eliminasi Total Elemen Fluff (Hapus Sampahnya, Jangan Cuma Copot Bajunya)
* **DILARANG KERAS:** Menghilangkan bungkus kapsul/badge tapi tetap membiarkan teks sampahnya melayang di antarmuka (*"Sama Aja Bohong"*).
* **Prinsip Pembersihan Hakiki:**
  - Teks hiasan buatan bot AI seperti `Live 6 Bulan Terakhir`, `Organik`, `Terverifikasi`, `Platform-Wide`, `AI-Powered Management System`, `• Live Cloud`, `INSIGHT`, `Bebas Biaya Langganan`, dsb. adalah **fluff / sampah visual**.
  - Mengubah pill hiasan menjadi teks polos biasa **TETAP MERUSAK UI** karena menambah beban membaca (*cognitive load*) yang tidak berguna bagi pengguna.
  - **ATURAN MUTLAK:** Jika sebuah teks atau label tidak memiliki nilai fungsional operasional nyata atau sudah tersirat dari konteksnya, **HAPUS TOTAL ELEMEN DAN TEKS TERSEBUT DARI BLADE VIEW! DILARANG MENINGGALKAN TEKS POLOS!**
  - **Judul Halaman (H1):** Berdiri langsung dengan wibawa dan kekuatan tipografi murni. DILARANG memberi teks alis (*eyebrow text*) apa pun di atasnya.
  - **Judul Section (H2):** Cukup Judul + Subtitle (jika perlu). DILARANG menaruh teks/label mengambang di pojok kanan atau di atas judul.
  - **Angka KPI / Metrik:** Angka moneter (`text-3xl tabular-nums`) tampil bersih dan gagah tanpa ditempeli teks tempelan apa pun di sampingnya.

### 8.5.7 Mandat Larangan Mutlak Emoticon / Emoji pada UI (Strict No-Emoji Rule)
* **DILARANG KERAS:** Menggunakan emoticon atau emoji karakter Unicode (seperti 🚀, ✨, 💡, 👥, 🏆, 🎟️, 📦, ⚡, 🔥, 🟢, 📈, 💬, 🏢, dsb.) pada seluruh antarmuka pengguna (UI).
* **Cakupan Larangan:**
  - Tombol aksi (Buttons)
  - Judul Halaman (H1), Judul Section (H2), dan Sub-judul (H3)
  - Bento Card & Widget Metrik KPI
  - Tab Bar, Segmented Controls, & Navigasi Sidebar
  - Dialog Konfirmasi, Alert Banner, & Microcopy Penenang (*No-Panic Feedback*)
  - Badge Status & Seluruh Kolom Tabel
* **Alasan:**
  - Emoticon/emoji OS merusak konsistensi visual lintas platform (tampilan rendering emoji Windows, macOS, Android, dan iOS berbeda-beda dan terkesan kartunis/tidak profesional).
  - Merusak wibawa Cooca sebagai sistem operasi bisnis UMKM yang tenang, berkelas, dan profesional.
  - Merupakan ciri khas template bot AI murahan yang malas menyusun hierarki tipografi murni.
* **Solusi Wajib:** **HANYA GUNAKAN FONT ICON RESMI SISTEM (Lucide Icons)!**
  - Gunakan tag `<i data-lucide="..." class="..."></i>` atau SVG inline fungsional.
  - Font icon menjamin ketebalan garis (*stroke width*) presisi, skalabilitas tajam pada layar Retina, serta pewarnaan semantik dinamis (*fill/stroke current-color*).

---

# 9. MANDAT ANTI-EXCESSIVE-TEXT

## 9.1 Dilarang Memenuhi UI dengan Teks yang Tidak Perlu
AI Agent **DILARANG** menambahkan:
* Paragraf penjelasan panjang tanpa kebutuhan operasional.
* Deskripsi berulang yang menjelaskan hal yang sudah jelas dari judulnya.
* Subtitle pada setiap kartu tanpa fungsi pembeda status.
* Helper text yang tidak membantu keputusan pengguna.
* Teks promosi pada halaman transaksi dan operasional.
* Jargon teknis yang tidak dipahami pengguna (*SKU, BOM, COGS, Void, Tenant Context*).
* Judul panjang yang dapat diringkas menjadi 2–3 kata.
* Empty state yang terlalu banyak kalimat (cukup: *"Belum ada produk"* + tombol aksi).

## 9.2 Prioritas Konten UI
1. **Wajib:** Diperlukan agar pengguna memahami data atau dapat menyelesaikan tugas.
2. **Membantu:** Memberikan konteks penting atau mencegah kesalahan input.
3. **Opsional:** Hanya ditampilkan jika pengguna meminta detail (misal via modal sheet).
4. **Tidak perlu:** Hapus seketika.

## 9.3 Aturan Microcopy Lugas
Gunakan kalimat pendek, bahasa Indonesia umum, kata kerja langsung, dan istilah konsisten:
* *Kurang baik:* "Silakan melakukan proses penyimpanan data produk yang telah Anda masukkan."  
  *Lebih baik:* **"Simpan Produk"**
* *Kurang baik:* "Anda belum memiliki data produk yang dapat ditampilkan pada halaman ini."  
  *Lebih baik:* **"Belum ada produk."**
* *Kurang baik:* "Apakah Anda benar-benar yakin ingin melanjutkan proses penghapusan data ini?"  
  *Lebih baik:* **"Hapus produk ini?"**

## 9.4 Mandat Tombol Aksi Lugas & Ringkas (Concise Action Buttons: Simpan, Hapus, Edit, Lihat)
* **DILARANG:** Membuat teks label tombol yang bertele-tele, terlalu panjang, atau mendikte detail yang sudah jelas dari konteks form/kartunya.
* **Prinsip Anti-Detail pada Tombol:** Tombol adalah pemicu aksi (*action trigger*), bukan tempat mengulang judul kartu atau halaman.
  - ❌ *Kurang baik:* **"Simpan Pengaturan Google & Sistem"** → ✅ *Cukup:* **"Simpan"**
  - ❌ *Kurang baik:* **"Simpan Pengaturan SMTP"** → ✅ *Cukup:* **"Simpan"**
  - ❌ *Kurang baik:* **"Simpan Data Produk Baru"** → ✅ *Cukup:* **"Simpan"**
  - ❌ *Kurang baik:* **"Lakukan Proses Penghapusan Akun"** → ✅ *Cukup:* **"Hapus"**
  - ❌ *Kurang baik:* **"Lihat Rincian Selengkapnya Transaksi"** → ✅ *Cukup:* **"Lihat"**
  - ❌ *Kurang baik:* **"Ubah Rincian Profil Administrator"** → ✅ *Cukup:* **"Edit"** atau **"Ubah"**
  - ❌ *Kurang baik:* **"Kirim Email Uji Coba"** → ✅ *Cukup:* **"Kirim"**
  - ❌ *Kurang baik:* **"Batalkan Operasi Ini"** → ✅ *Cukup:* **"Batal"**
* **Kamus Standar Label Tombol Aksi Utama:**
  - **`Simpan`** : Untuk seluruh formulir pembuatan, pembaruan, dan pengaturan sistem/data.
  - **`Hapus`** : Untuk konfirmasi atau trigger aksi penghapusan.
  - **`Edit`** / **`Ubah`** : Untuk membuka modal atau form penyuntingan data.
  - **`Lihat`** : Untuk membuka detail data, rincian nota, atau pratinjau.
  - **`Batal`** : Untuk menutup modal atau membatalkan dialog konfirmasi.
  - **`Kirim`** : Untuk pengiriman pesan, broadcast, atau email uji coba.
  - **`Salin`** : Untuk menyalin tautan/kunci API ke clipboard.
* **Pengecualian Terbatas (Maksimal 2 Kata):**
  - Hanya jika tombol berada di luar form/tabel sebagai CTA utama index: **"Tambah Produk"**, **"Ekspor Excel"**, **"Cetak Struk"**. Di dalam modal atau formulir kartu, **wajib menggunakan satu kata kerja murni** (**"Simpan"** / **"Hapus"** / **"Batal"**).

---

# 10. MANDAT WHITE SPACE DAN BREATHING ROOM

UI COOCA **DILARANG PADAT, SESAK, ATAU TERLALU BERDEMPETAN**.

## 10.1 Sistem Spacing Adaptif (Prinsip 8pt Grid)

| Area Tata Letak | Standar Mobile (<640px) | Standar Tablet (640–1023px) | Standar Desktop (1024px+) |
|---|---|---|---|
| **Jarak Antar Elemen Kecil** | 6px – 8px | 8px | 8px |
| **Jarak Antar Kontrol / Tombol** | 10px – 12px | 12px – 16px | 12px – 16px |
| **Jarak Antar Kartu (Grid Gap)** | 12px (`gap-3`) | 16px (`gap-4`) | 16px – 20px (`gap-4 sm:gap-5`) |
| **Padding Dalam Kartu** | **14px – 16px (`p-3.5`–`p-4`)** | **20px (`p-5`)** | **24px (`p-6`)** |
| **Jarak Antar Section** | 16px – 20px | 24px | 24px – 32px |
| **Margin Horizontal Halaman** | `px-3` | `px-6` | `px-8 max-w-[1440px] mx-auto` |
| **Padding Bawah Halaman (Safe Area)** | **`pb-28` s/d `pb-32` (MUTLAK)** | `pb-16` | `pb-10` |

*Perhatian Khusus Mobile:* Dilarang memberikan `p-6` atau `p-8` pada kartu di mobile karena akan memotong 48px–64px lebar layar smartphone yang hanya 360px–390px!

## 10.2 Aturan Anti-Padat & Anti-Meluber
AI Agent wajib memeriksa:
* Apakah teks mepet ke tepi border kartu? (Jika ya, tambah padding internal `p-3.5` s/d `p-4`).
* Apakah tombol aksi saling menempel tanpa sela? (Wajib sela minimal `gap-2.5` s/d `gap-3`).
* Apakah kartu memiliki ruang bernapas?
* Apakah terlalu banyak elemen tampil berjejal dalam satu layar ponsel? (Gunakan progressive disclosure / modal sheet).
* Apakah layout nyaman dan bebas dari scroll horizontal pada layar **360px**?

---

# 11. MATRIKS TIPOGRAFI LINTAS PERANGKAT

Font resmi sistem:
```css
font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Inter", system-ui, sans-serif;
```

## 11.1 Matriks Skala Font Responsif Komprehensif

| Peran Tipografi | Mobile (<640px) | Tablet (640–1023px) | Desktop (1024px+) | Weight | Line Height | Keterangan Khusus |
|---|---|---|---|---|---|---|
| **Large Title** | `text-[22px]–text-2xl (24px)` | `text-3xl (28px)` | `text-3xl–text-4xl (32–34px)` | 700 (Bold) | `leading-tight (1.2)` | Judul utama halaman (Dashboard, Katalog). |
| **Title 1 / Section** | `text-xl (20px)` | `text-2xl (24px)` | `text-2xl (24px)` | 600 (Semibold) | `leading-snug (1.25)` | Judul kelompok kartu bento. |
| **Title 2 / Card** | `text-[17px]–text-lg (18px)` | `text-lg (18px)` | `text-xl (20px)` | 600 (Semibold) | `leading-snug (1.3)` | Judul widget/kartu. |
| **Headline** | `text-[16px]` | `text-[16px]` | `text-[16px]` | 600 (Semibold) | `leading-normal (1.35)` | Baris nama data penting / nama produk. |
| **Body (Teks Utama)** | `text-[15px]–text-[16px]` | `text-[15px]–text-[16px]` | `text-[14px]–text-[15px]` | 400 (Regular) | `leading-relaxed (1.5)` | Teks deskripsi, paragraf bacaan nyaman. |
| **Form Input / Select** | **`text-[16px]` (MUTLAK)** | `text-[14px]–text-[15px]` | `text-[14px]` | 400 (Regular) | `leading-normal (1.4)` | **Wajib 16px di mobile (anti-auto-zoom iOS).** |
| **Subheadline** | `text-sm (14px)` | `text-sm (14px)` | `text-[13px]–text-sm (14px)` | 500 (Medium) | `leading-normal (1.4)` | Teks sekunder pendamping headline. |
| **Footnote / Helper** | `text-[13px]` | `text-[13px]` | `text-[12px]–text-[13px]` | 400 (Regular) | `leading-normal (1.35)` | Petunjuk form, label bantuan. |
| **Caption / Badge** | `text-xs (12px)` | `text-xs (12px)` | `text-[11px]–text-xs (12px)` | 600 (Semibold) | `leading-none (1.2)` | Status pills, badge, label filter. |
| **Angka / Moneter** | **`tabular-nums`** | **`tabular-nums`** | **`tabular-nums`** | 600–700 | `leading-none` | **Rupiah, stok, persentase, tanggal.** |

## 11.2 Aturan Mutlak Anti-Auto-Zoom Input Mobile
Browser smartphone (terutama iOS Safari) secara otomatis memperbesar tampilan layar dan merusak komposisi visual jika ukuran font elemen `<input>`, `<select>`, atau `<textarea>` berada di bawah 16px.
**Wajib menggunakan format responsif:**
```html
class="text-[16px] sm:text-[14px] ..."
```

## 11.3 Aturan Tabular Figures (`tabular-nums`)
Setiap data angka:
* Nominal Rupiah (`Rp 1.250.000`)
* Jumlah stok (`1.450 Pcs`)
* Nomor nota / faktur (`INV/2026/09/001`)
* Tanggal & jam transaksi (`16 Sep 2026, 14:30`)
* Persentase & diskon (`12.5%`)

**WAJIB** menyertakan class `tabular-nums` agar lebar karakter angka seragam, tersusun lurus rapi vertikal, dan tidak bergeser saat angka diperbarui.

## 11.4 Kontras Ketebalan & Pembatasan Bobot
* Gunakan hanya 4 bobot font standar: `400 (Regular)`, `500 (Medium)`, `600 (Semibold)`, `700 (Bold)`.
* **Dilarang Keras:** Memakai `font-black` atau bobot `900` yang membuat tampilan kotor dan berat.
* Dilarang membuat seluruh teks dalam satu kartu tebal (*all-bold*). Gunakan kontras: label `400/500 text-gray-500`, nilai utama `600/700 text-black dark:text-white`.

---

# 12. INFORMATION HIERARCHY

Setiap halaman wajib memiliki hierarki yang jelas:

```text
Primary Purpose (Apa halaman ini?)
    ↓
Primary Information (Data / status terpenting)
    ↓
Primary Action (Aksi utama yang harus dilakukan)
    ↓
Secondary Information (Rincian pelengkap)
    ↓
Optional Detail (Aksi jarang / log riwayat)
```

Jangan menampilkan semua informasi dengan tingkat visual yang sama.

Prioritaskan:
1. Apa yang harus diketahui pengguna dalam 3 detik pertama?
2. Apa yang harus dilakukan pengguna sekarang?
3. Apa risiko jika pengguna salah memilih?
4. Informasi apa yang cukup dibuka di modal sheet saat diminta?

---

# 13. CETAK BIRU RESPONSIVITAS BENTO UI & ANTI-OVERFLOW

## 13.1 Smartphone: 360px–639px (Strict Zero-Breakage Rules)
* **Zero Horizontal Overflow:** Dilarang keras memakai `w-[...]` atau `min-w-[...]` bernilai statis > 300px. Gunakan `w-full max-w-full`.
* **Flex Child Truncation:** Seluruh teks di dalam flex container wajib diberi `min-w-0` dan `truncate` atau `break-words` agar tidak memaksa kontainer melar melebihi layar smartphone.
* **Grid Bento Mobile:**
  - Form, detail, tabel, dan card kompleks: **`grid-cols-1`** (satu kolom penuh).
  - Stat/KPI ringkas: Maksimal **`grid-cols-2`** dengan padding compact `p-3.5` s/d `p-4`.
* **Transformasi Tabel ke Card List:** Tabel lebar 6–10 kolom pada mobile disembunyikan (`hidden md:block`) dan digantikan oleh deretan kartu ringkas (*Card List View*) yang nyaman dibaca vertikal (`block md:hidden`). Jika tabel wajib ditampilkan, bungkus dengan `overflow-x-auto` yang memiliki padding sentuh aman.
* **Toolbar Pencarian & Filter:** Input search tampil `w-full` di baris atas, filter kategori tampil horizontal scrolling (`flex overflow-x-auto no-scrollbar space-x-2 py-1`), dan tombol aksi utama mengapung di bilah bawah.
* **Bottom Navigation Safe Area:** Seluruh halaman mobile **WAJIB** memiliki padding bawah **`pb-28` s/d `pb-32` (`pb-28 lg:pb-10`)** agar konten terbawah tidak tertutup navigation bar.
* **Floating Bottom Action Bar:** Untuk transaksi POS atau checkout, tombol utama ditempatkan pada floating bottom bar yang menempel di jempol pengguna.

## 13.2 Tablet Kasir: 640px–1023px
* Layout 2–3 kolom sesuai kebutuhan operasional.
* Katalog dan keranjang POS dapat tampil berdampingan seimbang (2 kolom proporsional).
* Tombol aksi minimal 48px agar nyaman ditekan oleh jari kasir yang sibuk.
* Informasi penting terlihat tanpa terlalu banyak scrolling.

## 13.3 Desktop: 1024px–1440px+
* Kanvas terkontrol dengan pembatas lebar: `max-w-[1440px] mx-auto`.
* Layout bento 3–4 kolom yang lapang dan seimbang.
* Tabel dibungkus dengan `overflow-x-auto` dan border hairline lembut.
* Jangan meregangkan konten secara berlebihan di layar ultra-wide.
* **Perbaikan mobile DILARANG merusak tampilan desktop yang sudah baik.**

---

# 14. MODAL-FIRST DAN APPLE BOTTOM SHEETS

## 14.1 Modal-First Architecture
Pada seluruh halaman index:
* Operasi Create, Show/Detail, dan Edit dilakukan menggunakan modal sheet tanpa meninggalkan halaman (*Zero Page-Jumps*).
* Filter pencarian, filter kategori, sorting, dan posisi pagination tetap tersimpan saat modal ditutup.
* **Tampilan Adaptif:**
  - **Desktop (md+):** Centered Modal dengan latar belakang frosted glass lembut (`sm:max-w-xl sm:rounded-[20px]`).
  - **Mobile (< md):** **Apple Bottom Sheet** yang meluncur dari bawah dengan sudut `rounded-t-[28px]`, grab bar indikator (`w-10 h-1.5 bg-gray-300 rounded-full mx-auto my-2`), dan `max-h-[85vh] overflow-y-auto`.

## 14.2 Inline Quick-Add
Dropdown master yang membutuhkan data relasi (Kategori, Satuan, Supplier, Akun Kas) wajib menyediakan tombol cepat `[ + ]` di sampingnya:
```html
<div class="flex items-center gap-2">
    <div class="relative flex-1">
        <select class="w-full text-[16px] sm:text-[14px] rounded-[12px] ...">...</select>
    </div>
    <button type="button" class="w-11 h-11 flex-shrink-0 rounded-[12px] bg-blue-50 dark:bg-blue-900/30 text-[#007AFF] font-bold text-lg flex items-center justify-center active:scale-95 transition-all" title="Tambah Cepat">+</button>
</div>
```
* Membuka mini modal sheet tanpa meninggalkan form utama.
* Menyimpan melalui endpoint AJAX yang aman.
* Memasukkan opsi baru dan langsung memilihnya secara otomatis (*auto-select*).
* Form utama tidak boleh kehilangan data yang sudah diketik oleh pengguna.

---

# 15. ACCESSIBILITY, BOOMER-FRIENDLY & NO-PANIC UX

Dirancang khusus agar dapat dioperasikan secara percaya diri oleh **pemilik usaha usia 40–60+ tahun dan kasir non-teknis (*Zero-Manual UI*)**:
* **Touch Target Nyaman:** Tombol utama berukuran **48px–52px** agar tidak meleset saat ditekan oleh jari besar.
* **Bahasa Indonesia Lugas:** Hindari jargon bahasa Inggris:
  - *HPP / COGS* → Modal Pokok / Biaya Bahan
  - *Stock Reversal* → Pengembalian Bahan
  - *Void Transaction* → Pembatalan Transaksi
  - *Tenant Context* → Pemisahan Toko
* **Format Ribuan Otomatis:** Input angka nominal wajib otomatis memformat pemisah ribuan titik (`Rp 250.000`) secara real-time agar pengguna tidak salah memasukkan jumlah nol.
* **Microcopy Penenang Jiwa (*No-Panic Feedback*):** Di setiap dialog konfirmasi atau aksi penting, sertakan pesan penenang (gunakan font icon `<i data-lucide="info">`, DILARANG memakai emoji Unicode seperti 💡):
  - *“Tenang, riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan.”*
  - *“Anda dapat mengubah kembali pilihan ini kapan saja.”*
* **Tombol Destruktif Terlindungi:** Tombol hapus/void menggunakan konfirmasi tegas dua langkah agar tidak terjadi kecelakaan ketidaksengajaan.

---

# 16. SECURITY AND AUTHORIZATION

AI Agent wajib memeriksa:

* Authentication.
* Authorization.
* Role dan permission.
* IDOR.
* CSRF.
* Mass assignment.
* Validasi request.
* SQL Injection.
* XSS.
* Upload berbahaya.
* Route exposure.
* Privilege escalation.
* Webhook security.
* API security.
* Rate limiting.
* Audit log.
* Isolasi perusahaan dan cabang.
* Kebocoran data antar pengguna atau cabang.

UI yang menyembunyikan tombol **tidak menggantikan** validasi permission di backend.

---

# 17. DATA DAN LOGIKA BISNIS

Dilarang tanpa persetujuan eksplisit:

* Mengubah rumus subtotal.
* Mengubah diskon.
* Mengubah pajak atau PPN.
* Mengubah HPP.
* Mengubah margin.
* Mengubah jurnal akuntansi.
* Mengubah saldo kas.
* Mengubah histori transaksi.
* Mengubah status transaksi selesai.
* Menghapus data produksi.
* Mengubah struktur database yang berisiko.

Semua perubahan finansial harus dianalisis dan diuji secara khusus.

---

# 18. OTOMASI

Audit dan pertimbangkan otomasi untuk:

* Jurnal akuntansi.
* Pemotongan stok.
* BOM atau resep.
* Invoice.
* Nota digital.
* Notifikasi WhatsApp.
* Pengingat piutang dan hutang.
* Rekonsiliasi.
* Sinkronisasi status.
* Audit log.
* Scheduler dan queue.

Namun, jangan menambahkan otomasi yang belum dipahami dampaknya terhadap data, workflow, dan integrasi.

---

# 19. TESTING WAJIB

Sebelum menyatakan pekerjaan selesai, jalankan pengujian yang relevan:

```bash
php -l <file.php>
php artisan test
php artisan route:list
npm run build
php artisan route:cache
php artisan view:cache
```

Selain itu lakukan verifikasi menyeluruh:

* **Verifikasi Responsivitas Mobile (360px–430px):** Bebas mutlak dari scroll horizontal (*zero horizontal overflow*), teks tidak terpotong, bento cards adaptif.
* **Verifikasi Skala Font Input Mobile:** Seluruh `<input>`, `<select>`, dan `<textarea>` wajib berukuran minimal 16px (`text-[16px] sm:text-[14px]`) untuk mencegah auto-zoom iOS Safari.
* **Verifikasi Skala Tipografi:** Mengikuti Matriks Tipografi Apple HIG (kontras ketebalan jelas, tidak ada font-black/900).
* **Verifikasi Touch Target:** Seluruh tombol aksi utama minimal berdimensi 44x44px hingga 52px dengan sela antar tombol minimal 12px.
* **Verifikasi Safe Area Mobile:** View blade memiliki padding bawah aman (`pb-28` s/d `pb-32 lg:pb-10`) agar tidak tertutup bottom bar navigasi.
* **Verifikasi Tabular Figures:** Seluruh angka moneter, stok, tanggal, dan nomor nota menyertakan `tabular-nums`.
* Tidak ada error 500.
* Tidak ada route bentrok.
* Tidak ada view rusak.
* Tidak ada JavaScript error.
* Tidak ada console debug.
* Tidak ada broken link.
* Tidak ada permission bypass.
* Tidak ada data dummy yang tertinggal.

Jangan mengklaim `PASS` jika pengujian belum benar-benar dijalankan.

---

# 20. PRODUCTION HARDENING

Dilarang menyisakan:

```text
dd()
dump()
ray()
var_dump()
console.log()
mock response
dummy data
bypass authentication
bypass authorization
OTP statis
test user
temporary token
```

Pastikan:

* Service produksi digunakan.
* Validasi tetap aktif.
* Permission tetap aktif.
* Asset telah dibuild.
* Cache telah diperiksa.
* Data testing tidak mencemari database produksi.
* File upload testing dibersihkan.
* Tidak ada credential atau data sensitif di source code.

---

# 21. DOKUMENTASI 3 LAYER

Setiap pekerjaan yang mengubah sistem wajib mengevaluasi:

## Layer 1

```text
docs/AiWorkHistory.md
```

Catat:

* Work ID.
* Tanggal.
* Tujuan.
* Masalah.
* File yang diubah.
* Workflow yang terdampak.
* Keputusan teknis.
* Testing.
* Risiko.
* Status verifikasi.

## Layer 2

```text
docs/system/
```

Perbarui pengetahuan mengenai:

* Modul.
* Fitur.
* Workflow.
* Business rules.
* Permission.
* Arsitektur.
* Integrasi.
* Current state.

## Layer 3

```text
docs/SYSTEM_GUIDE.md
```

Perbarui jika perubahan memengaruhi cara sistem dipahami oleh:

* Business Owner.
* Developer.
* QA.
* AI Agent.
* Administrator.

Ketiga layer harus konsisten dan tidak boleh memiliki sumber kebenaran yang saling bertentangan.

---

# 22. DEFINITION OF DONE

Pekerjaan hanya dapat dinyatakan selesai jika:

* [ ] History telah dibaca.
* [ ] Dokumentasi relevan telah dibaca.
* [ ] Source code aktual telah diperiksa.
* [ ] Workflow end-to-end telah dipetakan.
* [ ] Duplikasi telah diperiksa.
* [ ] Risiko perubahan telah diklasifikasikan.
* [ ] Persetujuan telah diperoleh jika diperlukan.
* [ ] UI tidak memiliki teks berlebihan.
* [ ] Spacing cukup dan tidak padat (mengikuti Spacing Adaptif 8pt).
* [ ] Font size sesuai perangkat dan hierarki (Matriks Tipografi Apple HIG).
* [ ] Input form mobile terverifikasi minimal 16px (`text-[16px]`).
* [ ] Touch target tombol utama terverifikasi minimal 44px–52px.
* [ ] Padding bawah mobile terverifikasi memiliki safe-area (`pb-28` s/d `pb-32`).
* [ ] Angka moneter dan kuantitas menggunakan `tabular-nums`.
* [ ] Komponen menerapkan estetika Apple HIG (Squircles, hairline border, frosted glass, tactile press).
* [ ] Responsivitas terverifikasi pada layar 360px–430px (bebas overflow horizontal).
* [ ] Tidak ada teks terpotong.
* [ ] Tidak ada horizontal overflow.
* [ ] UI nyaman pada mobile, tablet, dan desktop.
* [ ] Permission frontend dan backend sesuai.
* [ ] Tidak ada perubahan finansial tanpa persetujuan.
* [ ] Testing relevan telah dijalankan.
* [ ] Tidak ada error yang belum diselesaikan.
* [ ] Tidak ada debug residue.
* [ ] Tidak ada mock atau dummy production flow.
* [ ] Dokumentasi 3 layer telah dievaluasi.
* [ ] Final audit telah dilakukan.

---

# 23. FINAL RESPONSE FORMAT

Setiap pekerjaan harus dilaporkan dengan struktur:

```text
## 1. History yang Dibaca
- File:
- Work ID relevan:
- Keputusan sebelumnya:

## 2. Kondisi Sistem Saat Ini
- Modul:
- Workflow:
- Route:
- Permission:
- Risiko:

## 3. Temuan Audit
- Masalah:
- Duplikasi:
- UI/UX:
- Security:
- Automation:
- Documentation:

## 4. Rencana Perubahan
- File yang akan diubah:
- File yang tidak diubah:
- Dampak:
- Risiko:
- Status persetujuan:

## 5. Implementasi
- Perubahan yang dilakukan:
- Workflow terdampak:

## 6. Testing
- Command:
- Hasil:
- Error:
- Status verifikasi:

## 7. Dokumentasi
- AiWorkHistory:
- docs/system:
- SYSTEM_GUIDE:

## 8. Final Status
- VERIFIED
- PARTIAL
- NEEDS_REVIEW
- OUTDATED
- NOT_VERIFIED
```

---

# 24. FINAL AGENT COMMAND

Sebelum melakukan pekerjaan apa pun:

1. Baca history.
2. Baca dokumentasi sistem.
3. Pahami keputusan sebelumnya.
4. Periksa source code aktual.
5. Petakan workflow.
6. Audit duplikasi.
7. Audit keamanan dan permission.
8. Audit otomasi.
9. Audit UI/UX, teks, spacing, dan font.
10. Klasifikasikan risiko perubahan.
11. Sajikan rencana.
12. Minta persetujuan jika perubahan berisiko.
13. Implementasikan secara minimal dan terarah.
14. Jalankan testing nyata.
15. Perbaiki seluruh error.
16. Periksa tampilan lintas perangkat.
17. Bersihkan debug dan data testing.
18. Perbarui dokumentasi 3 layer.
19. Lakukan final audit.
20. Laporkan status berdasarkan bukti, bukan asumsi.

> **UI COOCA harus terasa jelas, ringan, lapang, dan mudah digunakan.
> Jangan menambah teks, elemen, warna, atau komponen jika tidak memberikan manfaat nyata bagi pengguna.**
