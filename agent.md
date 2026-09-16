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

# 8. UI/UX MASTER DIRECTIVE

## 8.1 Prinsip Utama UI

UI COOCA harus:

* Sederhana.
* Jelas.
* Lapang.
* Konsisten.
* Mudah dipahami tanpa tutorial panjang.
* Ramah pengguna nonteknis.
* Ramah pengguna usia 40 tahun ke atas.
* Cepat dipindai.
* Tidak membuat pengguna merasa penuh atau kewalahan.
* Tidak menampilkan informasi yang tidak relevan.
* Tidak menggunakan teks berlebihan.

> **Clarity over Decoration.**
> Informasi yang penting harus terlihat. Informasi tambahan harus disembunyikan secara kontekstual.

---

# 9. MANDAT ANTI-EXCESSIVE-TEXT

## 9.1 Dilarang Memenuhi UI dengan Teks yang Tidak Perlu

AI Agent **DILARANG** menambahkan:

* Paragraf penjelasan panjang tanpa kebutuhan.
* Deskripsi berulang.
* Label yang menjelaskan sesuatu yang sudah jelas.
* Subtitle pada setiap kartu tanpa fungsi.
* Helper text yang tidak membantu keputusan pengguna.
* Teks promosi pada halaman operasional.
* Jargon teknis yang tidak dipahami pengguna.
* Judul panjang yang dapat diringkas.
* Empty state yang terlalu banyak kalimat.
* Instruksi berulang di setiap komponen.

## 9.2 Prioritas Konten UI

Setiap teks UI harus masuk ke salah satu kategori:

1. **Wajib:** diperlukan agar pengguna dapat memahami atau menyelesaikan tugas.
2. **Membantu:** memberikan konteks penting atau mencegah kesalahan.
3. **Opsional:** hanya ditampilkan jika pengguna memerlukan detail tambahan.
4. **Tidak perlu:** harus dihapus.

Jika teks tidak membantu pengguna mengambil keputusan atau menyelesaikan tugas, hapus.

## 9.3 Aturan Microcopy

Gunakan:

* Kalimat pendek.
* Bahasa Indonesia yang umum.
* Kata kerja yang jelas.
* Label langsung.
* Istilah yang konsisten.
* Satu pesan untuk satu tujuan.

Contoh:

```text
Kurang baik:
"Silakan melakukan proses penyimpanan data produk yang telah Anda masukkan."

Lebih baik:
"Simpan Produk"
```

```text
Kurang baik:
"Anda belum memiliki data produk yang dapat ditampilkan pada halaman ini."

Lebih baik:
"Belum ada produk."
```

```text
Kurang baik:
"Apakah Anda benar-benar yakin ingin melanjutkan proses penghapusan data ini?"

Lebih baik:
"Hapus produk ini?"
```

## 9.4 Aturan Judul dan Subtitle

Tidak semua halaman membutuhkan subtitle.

Gunakan subtitle hanya jika:

* Memberikan konteks yang benar-benar diperlukan.
* Menjelaskan status penting.
* Membantu pengguna memahami tindakan berikutnya.

Jika judul sudah jelas, jangan menambahkan subtitle.

## 9.5 Aturan Kartu Bento

Setiap kartu sebaiknya memiliki:

* Satu tujuan.
* Satu judul singkat.
* Satu nilai utama atau aksi.
* Detail tambahan hanya jika relevan.

Hindari:

* Judul panjang.
* Tiga sampai lima baris deskripsi.
* Banyak badge yang tidak bermakna.
* Ikon dekoratif berlebihan.
* Informasi yang sama pada banyak kartu.

---

# 10. MANDAT WHITE SPACE DAN BREATHING ROOM

UI COOCA **DILARANG PADAT, SESAK, ATAU TERLALU BERDEMPETAN**.

## 10.1 Sistem Spacing

Gunakan prinsip 8pt grid:

| Area                                |    Standar |
| ----------------------------------- | ---------: |
| Jarak antar elemen kecil            |        8px |
| Jarak antar kontrol                 |    12–16px |
| Jarak antar kartu                   |    16–24px |
| Padding kartu                       |    16–24px |
| Jarak antar section                 |    24–32px |
| Jarak tombol positif dan destruktif |    12–16px |
| Padding bawah mobile                |    `pb-28` |
| Padding bawah desktop               | `lg:pb-12` |

## 10.2 Aturan Anti-Padat

AI Agent wajib memeriksa:

* Apakah teks terlalu dekat dengan border?
* Apakah tombol saling menempel?
* Apakah kartu memiliki ruang bernapas?
* Apakah tabel terlalu rapat?
* Apakah form terlihat menakutkan?
* Apakah terlalu banyak elemen tampil dalam satu viewport?
* Apakah pengguna dapat membedakan satu kelompok informasi dari kelompok lainnya?
* Apakah layout masih nyaman pada layar 360px?

Jika jawabannya tidak, perbaiki spacing sebelum menambah dekorasi.

## 10.3 Jangan Berlebihan

White space bukan berarti semua elemen harus dibuat sangat besar.

Hindari:

* Padding berlebihan yang menghabiskan ruang.
* Kartu terlalu tinggi.
* Jarak kosong yang tidak memiliki fungsi.
* Tombol terlalu besar untuk aksi sederhana.
* Font besar pada seluruh elemen.
* Layout yang terlihat kosong tetapi tetap sulit dipahami.

Gunakan **ruang secukupnya berdasarkan hierarki informasi**.

---

# 11. MANDAT FONT SIZE DAN TYPOGRAPHY

## 11.1 Prinsip

Ukuran font harus:

* Mudah dibaca.
* Konsisten.
* Responsif.
* Tidak terlalu kecil.
* Tidak terlalu besar.
* Disesuaikan dengan tingkat kepentingan informasi.
* Tidak membuat kartu atau tabel menjadi padat.

## 11.2 Skala Font yang Disarankan

| Elemen        |   Desktop |  Mobile |
| ------------- | --------: | ------: |
| Large Title   |   32–34px | 28–32px |
| Page Title    |   24–28px | 22–26px |
| Section Title |   20–22px | 18–20px |
| Card Title    |   16–18px | 16–17px |
| Headline      |   16–17px | 16–17px |
| Body          |   14–15px | 15–16px |
| Form Label    |   13–14px | 14–16px |
| Helper Text   |   12–13px | 13–14px |
| Caption       |      12px | 12–13px |
| Eyebrow       | 10.5–11px |    11px |

## 11.3 Aturan Tipografi

* Gunakan maksimal empat bobot: `400`, `500`, `600`, `700`.
* Dilarang menggunakan `font-black` atau bobot `900`.
* Gunakan `tabular-nums` untuk uang, stok, jumlah, tanggal, dan nomor transaksi.
* Jangan menggunakan uppercase untuk teks panjang.
* Jangan menggunakan ukuran font kecil hanya untuk memuat lebih banyak informasi.
* Gunakan `leading-relaxed` atau line-height yang sesuai untuk teks penjelasan.
* Jangan membuat seluruh halaman menggunakan font bold.
* Gunakan kontras tipografi untuk membedakan prioritas informasi.

## 11.4 Input Mobile

Input berikut wajib memiliki ukuran minimal 16px pada mobile:

```text
<input>
<select>
<textarea>
```

Contoh:

```html
class="text-[16px] sm:text-[14px]"
```

Tujuannya untuk menghindari auto-zoom browser dan menjaga kenyamanan input.

---

# 12. INFORMATION HIERARCHY

Setiap halaman wajib memiliki hierarki yang jelas:

```text
Primary Purpose
    ↓
Primary Information
    ↓
Primary Action
    ↓
Secondary Information
    ↓
Optional Detail
```

Jangan menampilkan semua informasi dengan tingkat visual yang sama.

Prioritaskan:

1. Apa yang harus diketahui pengguna?
2. Apa yang harus dilakukan pengguna?
3. Apa risiko jika pengguna salah?
4. Informasi apa yang dapat ditampilkan setelah pengguna meminta detail?

---

# 13. RESPONSIVE BENTO UI

## 13.1 Smartphone: 360px–430px

Wajib:

* Layout adaptif.
* Spacing nyaman.
* Tidak ada horizontal overflow.
* Input minimal 16px.
* Tombol utama minimal 48–52px.
* Kartu tidak terlalu padat.
* Teks tidak terpotong.
* Informasi sekunder dapat disembunyikan atau dipindahkan ke modal.
* Bottom navigation tidak menutupi konten.

Jangan memaksakan semua informasi desktop tampil sekaligus di mobile.

## 13.2 Tablet Kasir: 768px–1024px

Wajib:

* Layout 2–3 kolom sesuai kebutuhan.
* Katalog dan keranjang POS dapat tampil berdampingan.
* Tombol mudah disentuh.
* Informasi penting terlihat tanpa terlalu banyak scrolling.
* Tidak menggunakan layout desktop yang diperkecil secara paksa.

## 13.3 Desktop: 1280px+

Wajib:

* Kanvas terkontrol.
* Gunakan `max-w-[1440px] mx-auto`.
* Tabel dibungkus dengan `overflow-x-auto`.
* Jangan meregangkan konten secara berlebihan.
* Jangan merusak tampilan desktop ketika memperbaiki mobile.
* Informasi utama dapat dipahami dalam 3 detik pertama.

---

# 14. MODAL-FIRST DAN QUICK-ADD

## 14.1 Modal-First

Pada halaman index:

* Show/detail menggunakan modal atau modal sheet.
* Create menggunakan modal atau modal sheet.
* Edit menggunakan modal atau modal sheet.
* Filter, pencarian, dan pagination tetap dipertahankan.
* Hindari redirect yang tidak diperlukan.

## 14.2 Inline Quick-Add

Dropdown master yang membutuhkan data relasi dapat menyediakan tombol `+` di sampingnya.

Contoh:

```text
Kategori [ Pilih kategori ▼ ] [ + ]
```

Quick-add harus:

* Membuka modal kecil.
* Tidak menghapus isian form utama.
* Menyimpan melalui endpoint yang aman.
* Memasukkan opsi baru ke dropdown.
* Memilih opsi baru secara otomatis.
* Menampilkan feedback yang jelas.

---

# 15. ACCESSIBILITY DAN HUMAN-FRIENDLY UX

Wajib memperhatikan:

* Kontras warna.
* Ukuran teks.
* Touch target minimal 48px.
* Fokus keyboard.
* Label form yang jelas.
* Error message yang mudah dipahami.
* Tombol destruktif yang tidak mudah salah tekan.
* Navigasi tanpa bergantung pada warna saja.
* Tidak menggunakan ikon tanpa konteks pada aksi kritis.
* Bahasa Indonesia yang sederhana.
* Hindari jargon seperti `SKU`, `BOM`, `COGS`, atau `Void` tanpa penjelasan yang sesuai.

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

Selain itu periksa:

* Tidak ada error 500.
* Tidak ada route bentrok.
* Tidak ada view rusak.
* Tidak ada JavaScript error.
* Tidak ada console debug.
* Tidak ada broken link.
* Tidak ada horizontal overflow.
* Tidak ada teks terpotong.
* Tidak ada tombol yang tidak berfungsi.
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
* [ ] Spacing cukup dan tidak padat.
* [ ] Font size sesuai perangkat dan hierarki.
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
