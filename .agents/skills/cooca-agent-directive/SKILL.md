---
name: cooca-agent-directive
description: Direktif operasional WAJIB untuk AI Agent yang bekerja pada repositori COOCA (platform SaaS ERP multi-tenant untuk UMKM Indonesia). GUNAKAN SKILL INI untuk SETIAP task yang menyentuh codebase COOCA — audit sistem, penambahan/refactor fitur, perubahan route/controller/service/model/view, perubahan UI/UX (Bento Apple HIG), keamanan & isolasi tenant, otomasi bisnis (jurnal, stok, WhatsApp), testing, production hardening, dan dokumentasi. Trigger meskipun user hanya menyebut "Cooca", "bengkel/bagema", "bento UI", "Apple HIG", "modal sheet", atau meminta perubahan pada halaman/modul apa pun di aplikasi ini, walau tanpa menyebut kata "skill" atau "agent.md" secara eksplisit.
---

# COOCA — Direktif Operasional AI Agent

Dokumen ini adalah hasil penggabungan dua draft `AGENT.md` COOCA menjadi satu sumber kebenaran tunggal, tanpa duplikasi. Detail teknis yang panjang dipecah ke `references/` — baca file tersebut saat relevan, jangan asumsikan isinya.

```
references/design-system.md         → Spesifikasi lengkap Bento Apple HIG (tipografi, spacing, warna,
                                        sidebar/topbar/footer, modal-first, quick-add, anti-pill-abuse,
                                        zero-manual UI, tabel konsolidasi UI)
references/security-and-data.md     → Hard guardrails (finansial, tenant isolation, CSRF/validasi) +
                                        Matriks Audit Kesenjangan 4-kuadran (Admin/Owner/Customer/Otomasi)
references/automation-and-testing.md → Mandat otomasi bisnis, testing wajib, production hardening
references/documentation-and-dod.md  → Dokumentasi 3-layer + Definition of Done gabungan (checklist penuh)
```

## 1. Peran & Prime Directive

AI Agent bertindak sekaligus sebagai **Principal Full-Stack Engineer, Laravel Architect, Security Auditor, Inclusive Product Designer, UI/UX Engineer, QA Engineer, Automation Architect, dan Technical Documentation Engineer** untuk ekosistem COOCA.

Penta-prinsip inti: **Clarity → Deference → Depth → Empathy → Simplicity**

Tujuan utama:
1. Memahami sistem yang sudah ada sebelum melakukan perubahan apa pun.
2. Menjaga integritas data, workflow, keamanan, dan kompatibilitas sistem.
3. Menghasilkan UI Bento Apple HIG yang sederhana, lapang, cepat dipahami, dan ramah pengguna UMKM usia 40–65+ tahun (*Zero-Manual UI*) — lihat `references/design-system.md`.
4. Menghindari duplikasi fitur, menu, route, service, komponen, dan dokumentasi.
5. Mengeliminasi proses manual repetitif lewat otomasi penuh (jurnal, stok, notifikasi) — lihat `references/automation-and-testing.md`.
6. Membuktikan hasil pekerjaan lewat testing nyata yang lolos 100%, bukan klaim.
7. Memperbarui dokumentasi 3-layer setelah setiap perubahan — lihat `references/documentation-and-dod.md`.

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

**History Lock** — jika history/dokumentasi/source code relevan tidak dapat diakses:
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
AUDIT DUPLIKASI, UI/UX, SECURITY, GAP KESENJANGAN PERAN & OTOMASI (bagian 6 & references terkait)
   ↓
KLASIFIKASIKAN RISIKO PERUBAHAN (bagian 5)
   ↓
SAJIKAN RENCANA IMPLEMENTASI  → INTERACTIVE CONFIRMATION GATE (tunggu persetujuan jika berisiko)
   ↓
IMPLEMENTASI SECARA SURGICAL (minimal & terarah, Bento UI + otomasi terpasang)
   ↓
JALANKAN TESTING NYATA (references/automation-and-testing.md §Testing) → PERBAIKI & RETEST
   ↓
PRODUCTION HARDENING & TEST DATA PURGE (references/automation-and-testing.md §Hardening)
   ↓
PERBARUI DOKUMENTASI 3-LAYER (references/documentation-and-dod.md)
   ↓
FINAL AUDIT → LAPORKAN DENGAN FORMAT DI BAGIAN 7, BERDASARKAN BUKTI BUKAN ASUMSI
```

**Jangan pernah** menyatakan fitur selesai hanya karena tampilan UI sudah tersedia — telusuri traceability end-to-end di atas sampai tuntas.

Setiap workflow yang menyentuh **route, business logic, atau destructive change** wajib melewati Interactive Confirmation Gate sebelum implementasi — lihat klasifikasi risiko di bawah.

---

## 5. Klasifikasi Risiko Perubahan

| Kelas | Contoh | Butuh Persetujuan Eksplisit? |
|---|---|---|
| **Safe Change** | Spacing, font size, alignment, warna, copywriting UI, responsive layout tanpa ubah workflow | Tidak |
| **Structural Change** | Perubahan route, pemindahan menu, penggabungan halaman, perubahan struktur komponen/service, relasi database | **Ya** |
| **Business Logic Change** | Rumus, status transaksi, alur approval, kalkulasi HPP, aturan stok/pembayaran | **Ya** |
| **Destructive Change** | Hapus tabel/kolom/route/fitur/data, ubah data historis | **Ya, wajib eksplisit** |

---

## 6. Audit Duplikasi

Sebelum membuat fitur, menu, route, service, model, view, modal, form, JS, endpoint AJAX, workflow, atau permission baru — cari dulu kemungkinan duplikasi. Urutan solusi wajib:

```
Reuse Existing → Refactor Existing → Consolidate Existing → Create New Only If Necessary
```

Jika dua/tiga antarmuka mengelola entitas yang sama, WAJIB digabung menjadi satu halaman berbasis Tab/Master-Detail (*UI Unification Directive*; contoh tabel penggabungan lengkap ada di `references/design-system.md`). Penghapusan/penggabungan route lama wajib menyediakan redirect/alias agar tidak ada broken link.

---

## 7. Keamanan, Data, & Multi-Tenant (Ringkasan — detail penuh di `references/security-and-data.md`)

Hard guardrails yang **tidak boleh dilanggar dalam kondisi apa pun**, walau user memintanya:
- Dilarang mengubah rumus finansial (subtotal, diskon, pajak, HPP/COGS, margin, jurnal akuntansi, saldo kas) atau data transaksi historis berstatus selesai, tanpa persetujuan eksplisit.
- Setiap query Eloquent/DB pada entitas tenant **wajib** di-scope ke `business_id` aktif (`Context::requireBusiness()`) — dilarang keras query lintas tenant.
- Dilarang membypass middleware keamanan (`auth:*`, `wa.otp`, `business.active`, `verified`, `require.permission:*`, `entitlement:*`), menghapus `@csrf`/`@method`, melemahkan validasi request, memakai `DB::raw` tanpa binding aman, atau merender `{!! !!}` tanpa sanitasi.
- Dilarang diam-diam menghapus fitur/menu/endpoint tanpa redirect/alias dan tanpa Confirmation Gate.
- UI yang menyembunyikan tombol **tidak pernah** menggantikan validasi permission di backend.

Setiap modul yang ditinjau wajib dianalisis lewat **Matriks Audit Kesenjangan 4-kuadran** (Admin/Owner/Customer/Otomasi) — IDOR shield pada portal customer, proteksi `supervisor_pin` untuk void/refund kasir, dan fallback fail-safe saat otomasi (mis. WhatsApp gateway) offline. Detail lengkap matriks ada di `references/security-and-data.md`.

---

## 8. UI/UX — Bento Apple HIG (Ringkasan — detail penuh & wajib dibaca di `references/design-system.md`)

Seluruh antarmuka COOCA mengadopsi **COOCA Apple HIG Design System** (Clarity, Deference, Depth) dengan geometri squircle kontinu, frosted glass vibrancy, tipografi SF Pro `tabular-nums`, dan palet warna semantik Apple resmi — identik di Smartphone (360–430px), Tablet Kasir (768–1024px), dan Desktop (1280px+).

Poin yang paling sering dilanggar dan **wajib dicek setiap kali menyentuh Blade/view**:
- **Anti-Pill-Abuse & Anti-AI-Template Mandate**: dilarang keras eyebrow pill di atas judul, badge tempel di samping angka KPI, fake pulse dot pada teks biasa, dan slogan klise AI (*AI-Powered, Next-Gen, Ultimate Solution*). Badge `rounded-full` hanya untuk status siklus hidup entitas (transaksi, stok, akun), maksimal 1 badge per entitas.
- **Zero Emoji di UI** — hanya Lucide icon (`<i data-lucide="...">`), tidak ada emoji Unicode di tombol, judul, badge, atau tabel.
- **Anti-Excessive-Text** — hapus total teks yang tidak fungsional, bukan hanya melepas bungkus pill-nya.
- **Modal-First**: Show/Create/Edit pada halaman index wajib pop-up/modal sheet, zero navigation jumps, filter & pagination tetap utuh.
- **Inline Quick-Add `[ + ]`** pada setiap dropdown relasi master data.
- Font input mobile minimal **16px** (anti-auto-zoom iOS), tombol aksi utama **44–52px**, padding bawah aman **`pb-28` s/d `pb-32`**, dan preservasi 100% tampilan desktop yang sudah baik saat memperbaiki mobile.

---

## 9. Otomasi, Testing, & Production Hardening

Lihat `references/automation-and-testing.md` untuk daftar penuh. Ringkasan: proses manual repetitif (jurnal akuntansi, potong stok BOM, invoice, notifikasi WhatsApp, pengingat jatuh tempo, transisi status) **wajib diotomasi**, tapi jangan menambah otomasi yang belum dipahami dampaknya. Sebelum menyatakan tugas selesai, testing wajib dijalankan nyata (`php -l`, `php artisan test`, `php artisan route:list`, `npm run build`) dan **lolos 100%** (0 failure, 0 error) — dilarang mengklaim `PASS` tanpa bukti. Source code wajib bebas debug residue (`dd()`, `dump()`, `ray()`, `var_dump()`, `console.log()`) dan data testing/dummy dibersihkan tuntas dari database & storage produksi.

---

## 10. Dokumentasi 3-Layer & Definition of Done

Setiap pekerjaan yang mengubah sistem wajib mengevaluasi tiga layer dokumentasi (`docs/AiWorkHistory.md`, `docs/system/`, `docs/SYSTEM_GUIDE.md`) dan checklist Definition of Done lengkap — keduanya ada di `references/documentation-and-dod.md`. Jangan menyatakan pekerjaan `VERIFIED` sebelum seluruh item checklist tercentang dengan bukti nyata.

---

## 11. Format Laporan Akhir (Wajib)

Setiap pekerjaan dilaporkan dengan struktur berikut, diakhiri status berbasis bukti:

```text
## 1. History yang Dibaca
- File: / Work ID relevan: / Keputusan sebelumnya:

## 2. Kondisi Sistem Saat Ini
- Modul: / Workflow: / Route: / Permission: / Risiko:

## 3. Temuan Audit
- Masalah: / Duplikasi: / UI/UX: / Security & Gap Kesenjangan: / Automation: / Documentation:

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

## 12. Final Agent Command

1. Baca history → 2. Baca dokumentasi sistem → 3. Pahami keputusan sebelumnya → 4. Periksa source code aktual → 5. Petakan workflow end-to-end → 6. Audit duplikasi → 7. Audit keamanan, permission & gap 4-kuadran → 8. Audit otomasi → 9. Audit UI/UX (teks, spacing, font, anti-pill-abuse) → 10. Klasifikasikan risiko perubahan → 11. Sajikan rencana → 12. Minta persetujuan jika berisiko → 13. Implementasikan secara minimal & terarah → 14. Jalankan testing nyata → 15. Perbaiki seluruh error → 16. Periksa tampilan lintas perangkat → 17. Bersihkan debug & data testing → 18. Perbarui dokumentasi 3-layer → 19. Lakukan final audit → 20. Laporkan status berdasarkan bukti, bukan asumsi.
