# COOCA - Dokumentasi 3-Layer & Definition of Done Gabungan (Referensi Lengkap)

## 1. Dokumentasi Berkelanjutan 3-Layer

Setiap pekerjaan yang mengubah sistem wajib menghasilkan dua hal: perubahan pada sistem **dan** pembaruan pengetahuan sistem. Pastikan ketiga layer selaras - tidak boleh ada sumber kebenaran yang saling bertentangan.

### Layer 1 - `docs/AiWorkHistory.md`

Rekam jejak historis terstruktur. Setiap entri wajib memuat **7 komponen minimal**:
1. **Tanggal/waktu**: Tanggal eksekusi pekerjaan (YYYY-MM-DD).
2. **Tujuan pekerjaan**: Konteks bisnis dan target hulu-ke-hilir yang ingin dicapai.
3. **Hasil audit**: Ringkasan temuan audit sistem & analisis end-to-end sebelum perbaikan.
4. **Perbaikan yang dilakukan**: Rincian teknis perubahan surgical yang telah diimplementasikan.
5. **File/module yang diubah**: Daftar lengkap path berkas controller, model, view, route, migration, dll.
6. **Hasil pengujian**: Bukti konkret verifikasi pengujian otomatis (`php artisan test`, `php -l`, `php artisan route:list`, `npm run build`) dengan status 100% lolos.
7. **Catatan atau risiko yang masih tersisa**: Catatan teknis lanjutan, mitigasi, atau rekomendasi perbaikan tahap berikutnya.

### Layer 2 - `docs/system/`

Pengetahuan kondisi sistem terkini (_Current State Knowledge_), diekstrak ke direktori sesuai: `modules/`, `features/`, `workflows/`, `business-rules/`, `permissions/`, `architecture/`.

**Pemicu Wajib Pembaruan `docs/system/` (10 Aspek Sistem)**:
Pembaruan dokumentasi pada Layer 2 ini **WAJIB** dilakukan apabila pekerjaan memengaruhi salah satu dari 10 aspek berikut:
1. **Arsitektur Sistem**: Perubahan pola service, event/job, queue worker, caching layer, middleware.
2. **Struktur Database**: Migrasi tabel baru, penambahan kolom, foreign key, composite index, enum status.
3. **Modul atau Fitur**: Penambahan, refactoring, atau pembaruan fungsionalitas modul.
4. **Business Flow / Alur Bisnis**: Perubahan tahapan checkout, alur approval, pemotongan stok, siklus piutang.
5. **Integrasi**: Konektor WhatsApp Meta Cloud API, gateway pembayaran TriPay/Midtrans, kurir Biteship, printer hardware.
6. **Konfigurasi**: Penambahan atau penyesuaian file config Laravel, runtime settings, environment variable.
7. **API / Endpoint**: Signature endpoint HTTP, payload request/response JSON, rate limiting.
8. **Permission / Role**: Hak akses baru pada Superadmin, Business Owner, Kasir, Staff Gudang, Customer.
9. **Workflow Operasional**: Prosedur kasir, KDS dapur, blind cash count, approval MAR, stock opname.
10. **Struktur File / Komponen Penting**: Pembuatan Blade component baru, Alpine store, layout shell.

### Layer 3 - `docs/SYSTEM_GUIDE.md`

Panduan induk kurasi tertinggi bagi Business Owner, Developer, QA, dan AI Agent - **WAJIB diperbarui secara simultan bersama `docs/AiWorkHistory.md`** pada setiap pekerjaan rekayasa. Dilarang keras hanya mencatat riwayat pada history log tanpa menyelaraskan System Guide.

### Hierarki Kebenaran

```
Actual Code > Database Schema > Tests > Existing Docs > AiWorkHistory > AI Assumption (DILARANG)
```

---

## 2. Definition of Done (Checklist Gabungan)

Pekerjaan hanya dapat dinyatakan selesai - dan status akhir `VERIFIED` - jika **seluruh** item berikut tercentang dengan bukti nyata (bukan asumsi):

### Siklus Kerja Wajib (Mandatory Lifecycle)

- [ ] **Audit Sistem & Analisis End-to-End**: History & dokumen sistem telah dibaca; source code aktual diperiksa; rantai alur dipetakan penuh (`User → UI → Route → Controller → Validation → Service → Model → DB → Event/Job → Notifikasi → Response`) agar memahami konteks secara mendalam dan tepat sasaran.
- [ ] **Dokumen Rencana Perbaikan**: Rencana lengkap telah disusun memuat 8 elemen standar (Temuan, Penyebab/Root Cause, Dampak, Solusi, File Terdampak, Risiko, Prioritas, Urutan Implementasi).
- [ ] **Persetujuan (Confirmation Gate)**: Persetujuan eksplisit pengguna telah diperoleh sebelum modifikasi kode dilakukan.
- [ ] **Implementasi Surgical**: Perbaikan dilakukan sesuai rencana, tepat sasaran, bebas dari scope creep, dan tidak merusak fitur yang sudah ada.
- [ ] **Testing Nyata 100% Lolos**: Pengujian nyata dieksekusi dengan hasil 0 error dan 0 failure.
- [ ] **Catat History**: Entri lengkap dengan 7 komponen wajib dicatat di `docs/AiWorkHistory.md`.
- [ ] **Update Dokumentasi Sistem**: Seluruh perubahan yang menyentuh 10 aspek sistem telah disinkronkan ke `docs/system/` dan `docs/SYSTEM_GUIDE.md`.

### Keamanan, Proteksi Fraud, & Data

- [ ] Gap keamanan 4-kuadran (Admin/Owner/Customer/Otomasi) sudah terproteksi, termasuk IDOR shield pada order customer.
- [ ] Keamanan multi-tenant terjaga - query terikat `Context::requireBusiness()`/`business_id`.
- [ ] Integritas kalkulasi finansial 100% utuh - rumus subtotal, pajak, diskon, HPP, margin, jurnal akuntansi tidak berubah tanpa persetujuan.
- [ ] Guardrail anti-fraud internal aktif: `supervisor_pin` pada Void/Refund POS, Blind Cash Count pada tutup kasir, Three-Way Matching pada pengadaan, Two-Step Transfer stok antar-cabang, dan Accounting Period Lock.
- [ ] Zero Plaintext Credential Exposure: Kredensial sensitif (API key, token, secret, password, PIN) terlindungi masking (`••••••••`) di UI, disembunyikan via `$hidden` pada model, dan tidak bocor ke output publik/struk POS.
- [ ] No Data Punishment & Subscription Limit Auto-Gating: Saat downgrade atau masa aktif berakhir, data produk/master tidak dihapus dari basis data. Produk over-quota di-suspend secara otomatis dari POS & Toko Online, dan ter-unlock otomatis saat pembayaran diperpanjang (*Auto-Reactivation*).
- [ ] Storage Tracking & Data Pruning Previewer: Seluruh konsumsi storage/database ditracking transparan. Fitur pembersihan log/media wajib menyertakan modal dialog preview (rincian jumlah data, rentang tanggal, estimasi MB dihemat) dan konfirmasi dua langkah tanpa menyentuh transaksi finansial.
- [ ] Permission frontend dan backend sesuai; UI yang sembunyikan tombol tidak menggantikan validasi backend.

### Otomasi & Notifikasi Sistem Terpadu (UI, Email, WhatsApp)

- [ ] Proses manual repetitif (jurnal, potong stok BOM, kirim nota WA, transisi status) telah berjalan otomatis sesuai kebutuhan modul.
- [ ] Notifikasi Tri-Channel (UI In-App Notification Center, Email HTML responsif, WhatsApp dispatch) berjalan asinkron via Laravel Queue (`ShouldQueue`) tanpa memblokir UI/POS.
- [ ] Fail-Safe & Manual Fallback tersedia: jika gateway WhatsApp/Email offline/timeout, transaksi tetap sukses dan tersedia tombol manual 1-klik `[ Kirim via WhatsApp ]` (`wa.me`).
- [ ] Anomali fraud & operasional kritis terhubung ke notifikasi instan WhatsApp/Email Owner.

### UI/UX (Bento Apple HIG)

- [ ] UI Bebas Hiperbola & Sesuai Spesifikasi Riil: Bebas dari klaim teknologi fiktif ("Mesin AI Quantum 99.999%"), metrik palsu, atau estimasi tidak berdasar. Seluruh data & status sesuai keadaan sistem dan database riil (`tabular-nums`).
- [ ] Penyajian Sederhana, Padat, dan Jelas: Informasi tidak berbelit-belit/berlebihan (*Zero Clutter*), bebas dinding teks (*Zero Wall-of-Text*), dan rincian kompleks tersimpan rapi via progressive disclosure / modal sheet.
- [ ] Indikator Kuota & Gembok Entitas Jelas: Batas kuota paket ditampilkan jelas (meter progress bar), entitas yang melebihi kuota ditandai dengan badge gembok Lucide `lock` bertuliskan *"Terkunci (Limitasi Plan)"* dengan banner upgrade yang tidak mengganggu alur kerja.
- [ ] UI tidak memiliki teks berlebihan; anti-pill-abuse & anti-emoji ditegakkan.
- [ ] Spacing cukup, tidak padat (Spacing Adaptif 8pt); font size sesuai Matriks Tipografi Apple HIG.
- [ ] Input form mobile minimal 16px; touch target tombol utama minimal 44–52px.
- [ ] Padding bawah mobile memiliki safe-area (`pb-28`–`pb-32`).
- [ ] Angka moneter & kuantitas memakai `tabular-nums`.
- [ ] Komponen menerapkan estetika Apple HIG (squircle, hairline border, frosted glass, tactile press).
- [ ] Bento UI konsisten di Smartphone (360–430px), Tablet Kasir (768–1024px), Desktop (1024px+) - konsep sama persis, bukan disunat.
- [ ] Floating Bottom Navbar iOS 18 tersedia di mobile/tablet dengan center action trigger.
- [ ] Modal-First & Full-Size Form Canvas: Seluruh form pop-up (Create/Edit/Show/Input Transaksi/Penyesuaian) wajib berukuran Full Size / Full Layout XXL (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px]`) di Desktop dan Full-Width Bottom Sheet (`w-full max-h-[95vh]`) di Mobile tanpa modal sempit (`max-w-md`/`max-w-lg`).
- [ ] Inline Quick-Add `[ + ]` tersedia di setiap dropdown relasi master data, dengan auto-select tanpa reset form.
- [ ] Ergonomi ramah Boomer lolos uji: bebas jargon teknis Inggris, format ribuan otomatis aktif.
- [ ] Responsivitas terverifikasi 360–430px bebas horizontal overflow; tidak ada teks terpotong.
- [ ] Perbaikan mobile tidak merusak tampilan desktop yang sudah baik.

### Testing & Production Hardening

- [ ] `php artisan test` dieksekusi, status PASS (0 failure, 0 error). `php artisan route:list` valid tanpa exception. Seluruh file PHP lolos lint/sintaks.
- [ ] Source code bebas 100% dari mock/stub testing atau bypass OTP/auth sementara; alur bisnis mengeksekusi domain service riil.
- [ ] Bebas debug residue (`dd()`, `dump()`, `ray()`, `var_dump()`, `console.log()`).
- [ ] Aset frontend terkompilasi produksi (`npm run build`); cache dioptimasi (`route:cache`, `view:cache`).
- [ ] Data testing dibersihkan tuntas dari database & storage operasional (dummy records, user uji coba, file upload percobaan).
- [ ] Tidak ada error yang belum diselesaikan.

### Dokumentasi

- [ ] `docs/AiWorkHistory.md` telah mencatat entri riwayat lengkap & terstruktur dengan 7 komponen wajib (Layer 1).
- [ ] `docs/system/` telah diperbarui merefleksikan kondisi sistem berjalan sesuai 10 aspek pemicu (Layer 2).
- [ ] `docs/SYSTEM_GUIDE.md` WAJIB telah diperbarui secara simultan bersama `AiWorkHistory.md` (Layer 3).
- [ ] Final audit telah dilakukan dan seluruh layer dokumentasi konsisten 100%.
