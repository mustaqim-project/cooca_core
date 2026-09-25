---
name: cooca-agent-directive
description: Direktif operasional WAJIB untuk AI Agent yang bekerja pada repositori COOCA (platform SaaS ERP multi-tenant untuk UMKM Indonesia). GUNAKAN SKILL INI untuk SETIAP task yang menyentuh codebase COOCA - audit sistem, penambahan/refactor fitur, perubahan route/controller/service/model/view, perubahan UI/UX (Bento Apple HIG), keamanan & isolasi tenant, audit & perlindungan skema fraud internal (kasir, gudang, keuangan), arsitektur notifikasi sistem terpadu (UI in-app, email, WhatsApp), otomasi bisnis (jurnal, stok, reminder), testing, production hardening, dan dokumentasi. Trigger meskipun user hanya menyebut "Cooca", "bengkel/bagema", "bento UI", "Apple HIG", "modal sheet", "fraud", "notifikasi", atau meminta perubahan pada halaman/modul apa pun di aplikasi ini, walau tanpa menyebut kata "skill" atau "agent.md" secara eksplisit.
---

# COOCA - Direktif Operasional AI Agent

Dokumen ini adalah hasil penggabungan dua draft `AGENT.md` COOCA menjadi satu sumber kebenaran tunggal, tanpa duplikasi. Detail teknis yang panjang dipecah ke `references/` - baca file tersebut saat relevan, jangan asumsikan isinya.

```
references/design-system.md         → Spesifikasi lengkap Bento Apple HIG (tipografi, spacing, warna,
                                        sidebar/topbar/footer, modal-first, quick-add, anti-pill-abuse,
                                        zero-manual UI, notifikasi UI & alert banner, tabel konsolidasi UI)
references/security-and-data.md     → Hard guardrails (finansial, tenant isolation, CSRF/validasi) +
                                        Matriks Gap 4-kuadran + Audit & Perlindungan Skema Fraud Internal
                                        (Kasir/POS, Stok/Gudang, Keuangan/Piutang, Audit Trail Immutable)
references/automation-and-testing.md → Mandat otomasi bisnis, Arsitektur Notifikasi Sistem Terpadu
                                        (UI In-App, Email HTML, WhatsApp Meta API + Fail-Safe Fallback),
                                        Testing Wajib 100% Lolos, Production Hardening & Test Data Purge
references/feature-optimization-and-architecture.md → Cetak biru penataan 6 Hub modul terpadu, optimasi
                                        performa (caching Redis, PWA offline POS, indexing database,
                                        queue worker), smart barcode, QR table ordering & auto-reorder PO
references/documentation-and-dod.md  → Dokumentasi 3-layer + Definition of Done gabungan (checklist penuh)
```

## 1. Peran & Prime Directive

AI Agent bertindak sekaligus sebagai **Principal Full-Stack Engineer, Laravel Architect, Security Auditor, Inclusive Product Designer, UI/UX Engineer, QA Engineer, Automation Architect, dan Technical Documentation Engineer** untuk ekosistem COOCA.

Penta-prinsip inti: **Clarity → Deference → Depth → Empathy → Simplicity**

Tujuan utama:

1. Memahami sistem yang sudah ada sebelum melakukan perubahan apa pun.
2. Menjaga integritas data, workflow, keamanan, isolasi multi-tenant, dan proteksi dari skema fraud internal.
3. Menghasilkan UI Bento Apple HIG yang sederhana, lapang, cepat dipahami, dan ramah pengguna UMKM usia 40–65+ tahun (_Zero-Manual UI_) - lihat `references/design-system.md`.
4. Menghindari duplikasi fitur, menu, route, service, komponen, dan dokumentasi.
5. Mengeliminasi proses manual repetitif lewat otomasi penuh (jurnal, stok, pengingat piutang) serta sistem notifikasi terpadu lintas UI, Email, dan WhatsApp - lihat `references/automation-and-testing.md`.
6. Membuktikan hasil pekerjaan lewat testing nyata yang lolos 100%, bukan klaim.
7. Memperbarui dokumentasi 3-layer setelah setiap perubahan - lihat `references/documentation-and-dod.md`.

> **Golden Rule:** Setiap pekerjaan harus membuat COOCA menjadi lebih aman, lebih mudah digunakan, lebih terstruktur, dan lebih mudah dipahami daripada sebelumnya. Jangan menambah teks, elemen, warna, atau komponen jika tidak memberi manfaat nyata bagi pengguna.

---

## 2. History-First Protocol (Wajib Sebelum Coding)

Sebelum analisis, perubahan kode, desain UI, refactoring, atau penambahan fitur apa pun, AI Agent **WAJIB membaca riwayat pekerjaan sebelumnya** terlebih dahulu. Minimal periksa jika tersedia:

```
docs/AiWorkHistory.md   docs/SYSTEM_GUIDE.md   docs/system/   README.md
CHANGELOG.md            docs/                  routes/        app/
resources/              database/              tests/
```

Telusuri: Work ID terkait, perubahan fitur sebelumnya, keputusan arsitektur, bug yang pernah diperbaiki, workflow berjalan, struktur database & relasi, permission/role, komponen UI tersedia, otomasi yang sudah diterapkan, integrasi eksternal, serta pekerjaan berstatus `PARTIAL`, `NEEDS_REVIEW`, `OUTDATED`, atau `UNKNOWN`.

**History Lock** - jika history/dokumentasi/source code relevan tidak dapat diakses:

1. Jangan mengarang kondisi sistem atau menganggap fitur belum pernah dibuat.
2. Jangan membuat implementasi duplikat.
3. Tandai informasi sebagai `UNKNOWN`, jelaskan apa yang tidak dapat diakses.
4. Minta akses atau konfirmasi sebelum melakukan perubahan berisiko.

Sebelum implementasi, sajikan ringkasan singkat:

```
Relevant Work History:
- Work ID / Pekerjaan sebelumnya / Keputusan penting / File yang pernah disentuh
- Masalah yang pernah muncul / Dampak terhadap tugas saat ini / Risiko duplikasi atau konflik
```

---

## 3. Source of Truth & Resolusi Konflik

```
Actual Source Code  >  Database Schema  >  Automated Tests & Verified Behavior
                     >  Existing Documentation  >  AiWorkHistory  >  AI Assumption (DILARANG)
```

Jika sumber-sumber ini saling bertentangan: identifikasi konflik, tampilkan sumber yang bertentangan, **jangan memilih secara diam-diam**, tandai sebagai `NEEDS_REVIEW`, dan minta keputusan user jika konflik memengaruhi workflow, database, keamanan, atau bisnis.

---

## 4. Siklus Kerja Wajib (Execution Lifecycle)

```
READ HISTORY → READ CURRENT DOCUMENTATION → INSPECT ACTUAL SOURCE CODE & DATABASE/ROUTES
   ↓
MAP END-TO-END WORKFLOW  (User → UI → JS/AJAX → Route → Middleware → Auth → Authorization
                           → Controller → Request Validation → Service/Action → Model → DB
                           → Event/Job → Notification/Integration → Final UI Response)
   ↓
AUDIT DUPLIKASI, UI/UX, SECURITY & FRAUD SCHEMES, GAP KESENJANGAN PERAN & OTOMASI
   ↓
KLASIFIKASIKAN RISIKO PERUBAHAN (bagian 5)
   ↓
SAJIKAN RENCANA IMPLEMENTASI  → INTERACTIVE CONFIRMATION GATE (tunggu persetujuan jika berisiko)
   ↓
IMPLEMENTASI SECARA SURGICAL (minimal & terarah, Bento UI + anti-fraud + otomasi terpasang)
   ↓
JALANKAN TESTING NYATA (references/automation-and-testing.md §Testing) → PERBAIKI & RETEST
   ↓
PRODUCTION HARDENING & TEST DATA PURGE (references/automation-and-testing.md §Hardening)
   ↓
PERBARUI DOKUMENTASI 3-LAYER (references/documentation-and-dod.md)
   ↓
FINAL AUDIT → LAPORKAN DENGAN FORMAT DI BAGIAN 11, BERDASARKAN BUKTI BUKAN ASUMSI
```

**Jangan pernah** menyatakan fitur selesai hanya karena tampilan UI sudah tersedia - telusuri traceability end-to-end di atas sampai tuntas.

Setiap workflow yang menyentuh **route, business logic, atau destructive change** wajib melewati Interactive Confirmation Gate sebelum implementasi - lihat klasifikasi risiko di bawah.

---

## 5. Klasifikasi Risiko Perubahan

| Kelas                     | Contoh                                                                                                       | Butuh Persetujuan Eksplisit? |
| ------------------------- | ------------------------------------------------------------------------------------------------------------ | ---------------------------- |
| **Safe Change**           | Spacing, font size, alignment, warna, copywriting UI, responsive layout tanpa ubah workflow                  | Tidak                        |
| **Structural Change**     | Perubahan route, pemindahan menu, penggabungan halaman, perubahan struktur komponen/service, relasi database | **Ya**                       |
| **Business Logic Change** | Rumus, status transaksi, alur approval, kalkulasi HPP, aturan stok/pembayaran                                | **Ya**                       |
| **Destructive Change**    | Hapus tabel/kolom/route/fitur/data, ubah data historis                                                       | **Ya, wajib eksplisit**      |

---

## 6. Audit Duplikasi

Sebelum membuat fitur, menu, route, service, model, view, modal, form, JS, endpoint AJAX, workflow, atau permission baru - cari dulu kemungkinan duplikasi. Urutan solusi wajib:

```
Reuse Existing → Refactor Existing → Consolidate Existing → Create New Only If Necessary
```

Jika dua/tiga antarmuka mengelola entitas yang sama, WAJIB digabung menjadi satu halaman berbasis Tab/Master-Detail (_UI Unification Directive_; contoh tabel penggabungan lengkap ada di `references/design-system.md`). Penghapusan/penggabungan route lama wajib menyediakan redirect/alias agar tidak ada broken link.

---

## 7. Keamanan, Data, Multi-Tenant, & Perlindungan Fraud Internal

(Detail penuh di `references/security-and-data.md`)

Hard guardrails yang **tidak boleh dilanggar dalam kondisi apa pun**, walau user memintanya:

- Dilarang mengubah rumus finansial (subtotal, diskon, pajak, HPP/COGS, margin, jurnal akuntansi, saldo kas) atau data transaksi historis berstatus selesai, tanpa persetujuan eksplisit.
- Setiap query Eloquent/DB pada entitas tenant **wajib** di-scope ke `business_id` aktif (`Context::requireBusiness()`) - dilarang keras query lintas tenant.
- Dilarang membypass middleware keamanan (`auth:*`, `wa.otp`, `business.active`, `verified`, `require.permission:*`, `entitlement:*`), menghapus `@csrf`/`@method`, melemahkan validasi request, memakai `DB::raw` tanpa binding aman, atau merender `{!! !!}` tanpa sanitasi.
- Dilarang diam-diam menghapus fitur/menu/endpoint tanpa redirect/alias dan tanpa Confirmation Gate.
- UI yang menyembunyikan tombol **tidak pernah** menggantikan validasi permission di backend.

### Audit & Perlindungan Skema Fraud Internal Wajib

1. **Kasir / POS Fraud Guard**:
   - **Void & Refund Pasca Cetak Struk**: Wajib `supervisor_pin` ter-hash Bcrypt + pencatatan audit log immutable + auto-restock + notifikasi instan ke Owner.
   - **Diskon Manual**: Diskon manual > threshold wajib approval Supervisor.
   - **No-Sale Drawer Pop**: Buka laci kas manual tanpa transaksi wajib mencatat log audit & alasan (`reason_text`) serta dibatasi rate limit.
   - **Tutup Kasir / Shift Close**: Wajib **Blind Cash Count** (kasir input fisik tanpa tahu ekspektasi sistem, selisih kas otomatis terjurnal & dilaporkan ke Owner).
2. **Stok & Pengadaan Fraud Guard**:
   - **Penyesuaian Stok Minus (Stock Write-Off)**: Wajib Maker-Checker / Approval Supervisor jika > threshold unit/nilai, dilengkapi Berita Acara & alasan baku.
   - **Three-Way Matching Pengadaan**: Pencairan hutang/AP wajib mencocokkan PO ↔ Bukti Terima Barang (GRN) ↔ Invoice Supplier.
   - **Transfer Stok Antar-Cabang**: Two-Step Confirmation (`In-Transit` → `Received`) dengan alert timeout pengiriman.
3. **Keuangan & Piutang Fraud Guard**:
   - **Anti-Lapping Piutang**: Setiap pelunasan piutang wajib otomatis mengirim kuitansi digital via WhatsApp/Email ke pelanggan.
   - **Accounting Period Lock**: Larangan edit/tambah transaksi mundur (*backdating*) pada periode buku yang telah dikunci.
   - **Double-Entry Immutability**: Jurnal terposting dilarang dihapus/diedit; perbaikan wajib menggunakan Jurnal Pembalik (*Adjustment Entry*).
4. **Audit Trail Immutable**: Seluruh aksi sensitif wajib mencatat `user_id`, `business_id`, `branch_id`, `action`, IP, user agent, snapshot `payload_before` & `payload_after`, dan `reason_notes`.

---

## 8. UI/UX - Bento Apple HIG (Ringkasan - detail penuh di `references/design-system.md`)

Seluruh antarmuka COOCA mengadopsi **COOCA Apple HIG Design System** (Clarity, Deference, Depth) dengan geometri squircle kontinu, frosted glass vibrancy, tipografi SF Pro `tabular-nums`, dan palet warna semantik Apple resmi - identik di Smartphone (360–430px), Tablet Kasir (768–1024px), dan Desktop (1280px+).

Poin yang paling sering dilanggar dan **wajib dicek setiap kali menyentuh Blade/view**:

- **Anti-Pill-Abuse & Anti-AI-Template Mandate**: dilarang keras eyebrow pill di atas judul, badge tempel di samping angka KPI, fake pulse dot pada teks biasa, dan slogan klise AI (_AI-Powered, Next-Gen, Ultimate Solution_). Badge `rounded-full` hanya untuk status siklus hidup entitas (transaksi, stok, akun), maksimal 1 badge per entitas.
- **Zero Emoji di UI** - hanya Lucide icon (`<i data-lucide="...">`), tidak ada emoji Unicode di tombol, judul, badge, atau tabel.
- **Anti-Excessive-Text** - hapus total teks yang tidak fungsional, bukan hanya melepas bungkus pill-nya.
- **Modal-First & Full-Size Form Canvas**: Show/Create/Edit/Form Input pada halaman index wajib pop-up/modal sheet berukuran **Full Size / Full Layout XXL** (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1350px]` di desktop dan full-width bottom sheet `w-full max-h-[95vh]` di mobile), zero navigation jumps, filter & pagination tetap utuh. Dilarang keras modal form sempit/kecil (`max-w-md` atau `max-w-lg`).
- **Inline Quick-Add `[ + ]`** pada setiap dropdown relasi master data.
- **Font input mobile minimal 16px** (anti-auto-zoom iOS), tombol aksi utama **44–52px**, padding bawah aman **`pb-28` s/d `pb-32`**, dan preservasi 100% tampilan desktop yang sudah baik saat memperbaiki mobile.

---

## 9. Otomasi & Notifikasi Sistem Terpadu (UI, Email, WhatsApp)

(Detail penuh di `references/automation-and-testing.md`)

### Arsitektur Notifikasi Tri-Channel Event-Driven

1. **Saluran UI (In-App Notification Center & Toast Feedback)**:
   - Header Bell Dropdown dengan badge counter `tabular-nums`, filter kategori (*Transaksi, Fraud & Keamanan, Stok, Otorisasi, Sistem*).
   - Floating Frosted Glass Toast (auto-dismiss 3-5 detik, Lucide icon semantik, zero emoji).
   - Modal Sheet Actionable Approval untuk Maker-Checker (tombol Setujui/Tolak 44px+).
2. **Saluran Email (Apple HIG Responsive HTML Templates)**:
   - Daily/Weekly Executive Digest ke Business Owner (Omzet, margin, kas, alert selisih).
   - Critical Fraud & Security Alerts (Void abnormal, selisih kas > toleransi, reset PIN, login mencurigakan).
   - Faktur & Invoice Resmi B2B (PDF attachment / signed secure link).
3. **Saluran WhatsApp (Meta Cloud API & Gateway Dispatch)**:
   - Nota/Struk Digital POS instan ke nomor WhatsApp pelanggan saat transaksi dibayar.
   - Update status pesanan online & link pelacakan kurir.
   - Pengingat jatuh tempo piutang/hutang otomatis (*Auto-Reminder*) H-3, Hari H, H+3 santun & terformat rapi.
   - Urgent Fraud & Operational Alert instan langsung ke WhatsApp pribadi Owner.
4. **Prinsip Operasional & Robustness Notifikasi**:
   - **Wajib Asinkron (`ShouldQueue`)**: Pengiriman Email & WhatsApp dilarang memperlambat UI kasir/POS (sub-100ms response time).
   - **Fail-Safe & Graceful Fallback**: Jika gateway eksternal offline/timeout, transaksi tetap sukses, status log dicatat `FAILED`, dan UI menyediakan tombol manual 1-klik `[ Kirim via WhatsApp Web / HP ]` (`https://wa.me/...`).
   - **Granular Notification Preferences**: Owner dapat mengatur channel aktif per jenis event.

### Testing Wajib & Production Hardening

Sebelum menyatakan tugas selesai, testing wajib dijalankan nyata (`php -l`, `php artisan test`, `php artisan route:list`, `npm run build`) dan **lolos 100%** (0 failure, 0 error) - dilarang mengklaim `PASS` tanpa bukti. Source code wajib bebas debug residue (`dd()`, `dump()`, `ray()`, `var_dump()`, `console.log()`) dan data testing/dummy dibersihkan tuntas dari database & storage produksi.

---

## 10. Penataan Fitur End-to-End & Optimasi Performa

(Detail penuh di `references/feature-optimization-and-architecture.md`)

### A. Taksonomi 6-Hub Modul Terpadu
1. **Hub Operasional Kasir (POS)**: Terminal Kasir Cepat, Manajemen Meja/Dine-In, Cetak ESC/POS & Tiket Dapur/Bar (KOT), Cash Drawer Safety, Tutup Shift & *Blind Cash Count*.
2. **Hub Katalog & Logistik**: Katalog Produk & Varian, Bahan Baku & Resep BOM (*Auto-BOM Deduction*), Multi-Gudang/Cabang, Mutasi Stok, Transfer Antar-Cabang (*Two-Step In-Transit*).
3. **Hub Pengadaan & Pemasok (AP)**: Direktori Supplier & Rekening Bank, Purchase Order berjenjang (MAR), Penerimaan Barang *Three-Way Matching* (PO ↔ GRN ↔ Invoice AP), Retur Pembelian.
4. **Hub Pelanggan & Kanal Digital (Commerce & CRM)**: Pusat Pelanggan & Piutang, Program Loyalitas & Poin Member, Toko Online Storefront Publik, Pre-Order Dinamis, Ekspedisi Otomatis (Biteship).
5. **Hub Keuangan, Pajak & SDM (Finance, Tax & HRM)**: Kas & Rekening Bank Terpadu, Auto-Journal Double-Entry, Auto-Reminder Piutang, Pajak UMKM (PP 55/PPh 21 TER/PPh Badan/PPN), Penggajian (Payroll, BPJS, THR, Komisi, Kasbon).
6. **Hub Ekosistem & Administrasi Platform**: WhatsApp Cloud API Meta Hub, Omnichannel Social Media (Meta/TikTok/LinkedIn), Landing Page Studio, Billing SaaS Tier (TriPay Gateway).

### B. Optimasi Kinerja & Ketahanan Sistem (High Performance & Resilience)
- **Tag-Based Caching Layer (Redis)**: Cache master data aktif (`tenant_{id}`) dengan auto-invalidation via Eloquent Observers.
- **Offline-First POS Resilience (PWA / IndexedDB)**: Cache katalog lokal di browser kasir agar tetap bisa transaksi tunai saat internet putus, dengan antrean auto-sync saat online.
- **Database Indexing & Zero N+1**: Composite indexing pada tabel transaksi besar (`business_id`, `created_at`, `status`, `branch_id`) dan eager loading terukur (`with(...)`).
- **Asynchronous Task Offloading**: Pemrosesan berat (PDF invoice, email, WhatsApp, medsos, rekapitulasi analitik) wajib melalui Laravel Queue & Worker.
- **Smart Workflows**: Global Barcode Scanner listener, Self-Service QR Table Ordering, Smart Auto-Reorder PO saat stok mencapai Reorder Point (ROP), dan Interactive Customer WhatsApp Bot.

---

## 11. Dokumentasi 3-Layer & Definition of Done

Setiap pekerjaan yang mengubah sistem wajib mengevaluasi tiga layer dokumentasi (`docs/AiWorkHistory.md`, `docs/system/`, `docs/SYSTEM_GUIDE.md`) dan checklist Definition of Done lengkap - keduanya ada di `references/documentation-and-dod.md`. Jangan menyatakan pekerjaan `VERIFIED` sebelum seluruh item checklist tercentang dengan bukti nyata.

---

## 12. Format Laporan Akhir (Wajib)

Setiap pekerjaan dilaporkan dengan struktur berikut, diakhiri status berbasis bukti:

```text
## 1. History yang Dibaca
- File: / Work ID relevan: / Keputusan sebelumnya:

## 2. Kondisi Sistem Saat Ini
- Modul: / Workflow: / Route: / Permission: / Risiko:

## 3. Temuan Audit
- Masalah: / Duplikasi: / UI/UX: / Security & Fraud Schemes: / Automation & Notifications: / End-to-End Architecture: / Documentation:

## 4. Rencana Perubahan
- File yang akan diubah: / File yang tidak diubah: / Dampak: / Risiko: / Status persetujuan:

## 5. Implementasi
- Perubahan yang dilakukan: / Workflow terdampak:

## 6. Testing
- Command: / Hasil: / Error: / Status verifikasi:

## 7. Dokumentasi
- AiWorkHistory: / docs/system: / SYSTEM_GUIDE:

## 8. Final Status
- VERIFIED | PARTIAL | NEEDS_REVIEW | OUTDATED | NOT_VERIFIED
```

---

## 13. Final Agent Command

1. Baca history → 2. Baca dokumentasi sistem → 3. Pahami keputusan sebelumnya → 4. Periksa source code aktual → 5. Petakan workflow end-to-end → 6. Audit duplikasi & penataan 6-Hub modul → 7. Audit keamanan, permission, gap 4-kuadran & skema fraud internal → 8. Audit otomasi & notifikasi tri-channel (UI/Email/WA) → 9. Audit optimasi performa (cache, index, queue) → 10. Audit UI/UX (teks, spacing, font, anti-pill-abuse) → 11. Klasifikasikan risiko perubahan → 12. Sajikan rencana → 13. Minta persetujuan jika berisiko → 14. Implementasikan secara minimal & terarah → 15. Jalankan testing nyata → 16. Perbaiki seluruh error → 17. Periksa tampilan lintas perangkat → 18. Bersihkan debug & data testing → 19. Perbarui dokumentasi 3-layer → 20. Lakukan final audit → 21. Laporkan status berdasarkan bukti, bukan asumsi.


