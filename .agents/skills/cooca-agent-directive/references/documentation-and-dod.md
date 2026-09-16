# COOCA — Dokumentasi 3-Layer & Definition of Done Gabungan (Referensi Lengkap)

## 1. Dokumentasi Berkelanjutan 3-Layer

Setiap pekerjaan yang mengubah sistem wajib menghasilkan dua hal: perubahan pada sistem **dan** pembaruan pengetahuan sistem. Pastikan ketiga layer selaras — tidak boleh ada sumber kebenaran yang saling bertentangan.

### Layer 1 — `docs/AiWorkHistory.md`
Rekam jejak historis terstruktur. Catat:
- Work ID · Tanggal · Tujuan · Masalah
- File yang diubah · Workflow yang terdampak
- Keputusan teknis · Testing yang dijalankan · Risiko
- Status verifikasi

### Layer 2 — `docs/system/`
Pengetahuan kondisi sistem terkini (*Current State Knowledge*), diekstrak ke direktori sesuai: `modules/`, `features/`, `workflows/`, `business-rules/`, `permissions/`, `architecture/`. Mencakup: Modul, Fitur, Workflow, Business rules, Permission, Arsitektur, Integrasi, Current state.

### Layer 3 — `docs/SYSTEM_GUIDE.md`
Panduan induk kurasi tertinggi — perbarui jika perubahan memengaruhi cara sistem dipahami oleh Business Owner, Developer, QA, AI Agent, atau Administrator.

### Hierarki Kebenaran
```
Actual Code > Database Schema > Tests > Existing Docs > AiWorkHistory > AI Assumption (DILARANG)
```

---

## 2. Definition of Done (Checklist Gabungan)

Pekerjaan hanya dapat dinyatakan selesai — dan status akhir `VERIFIED` — jika **seluruh** item berikut tercentang dengan bukti nyata (bukan asumsi):

### Proses & Riwayat
- [ ] History telah dibaca dan dokumentasi relevan telah dibaca.
- [ ] Source code aktual telah diperiksa; workflow end-to-end telah dipetakan (Route → Controller → Service → Model → View, dan rantai penuh User→...→UI Response).
- [ ] Duplikasi telah diperiksa (Reuse → Refactor → Consolidate → Create New).
- [ ] Risiko perubahan telah diklasifikasikan (Safe/Structural/Business Logic/Destructive).
- [ ] Persetujuan telah diperoleh jika diperlukan (structural/business logic/destructive) — tidak ada perombakan alur/penggabungan menu tanpa persetujuan eksplisit.

### Keamanan & Data
- [ ] Gap keamanan 4-kuadran (Admin/Owner/Customer/Otomasi) sudah terproteksi, termasuk IDOR shield pada order customer.
- [ ] Keamanan multi-tenant terjaga — query terikat `Context::requireBusiness()`/`business_id`.
- [ ] Integritas kalkulasi finansial 100% utuh — rumus subtotal, pajak, diskon, HPP, margin, jurnal akuntansi tidak berubah tanpa persetujuan.
- [ ] Permission frontend dan backend sesuai; UI yang sembunyikan tombol tidak menggantikan validasi backend.

### Otomasi
- [ ] Proses manual repetitif (jurnal, potong stok BOM, kirim nota WA, transisi status) telah berjalan otomatis sesuai kebutuhan modul.

### UI/UX (Bento Apple HIG)
- [ ] UI tidak memiliki teks berlebihan; anti-pill-abuse & anti-emoji ditegakkan.
- [ ] Spacing cukup, tidak padat (Spacing Adaptif 8pt); font size sesuai Matriks Tipografi Apple HIG.
- [ ] Input form mobile minimal 16px; touch target tombol utama minimal 44–52px.
- [ ] Padding bawah mobile memiliki safe-area (`pb-28`–`pb-32`).
- [ ] Angka moneter & kuantitas memakai `tabular-nums`.
- [ ] Komponen menerapkan estetika Apple HIG (squircle, hairline border, frosted glass, tactile press).
- [ ] Bento UI konsisten di Smartphone (360–430px), Tablet Kasir (768–1024px), Desktop (1024px+) — konsep sama persis, bukan disunat.
- [ ] Floating Bottom Navbar iOS 18 tersedia di mobile/tablet dengan center action trigger.
- [ ] Modal-First berlaku untuk Show/Create/Edit di index (zero navigation jumps, filter/pencarian/pagination utuh).
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
- [ ] `docs/AiWorkHistory.md` telah mencatat entri lengkap & terstruktur.
- [ ] `docs/system/` telah diperbarui merefleksikan kondisi sistem berjalan.
- [ ] `docs/SYSTEM_GUIDE.md` telah diperbarui jika ada perubahan pemahaman alur/modul/aturan bisnis.
- [ ] Final audit telah dilakukan.
