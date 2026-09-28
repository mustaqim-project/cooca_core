---
name: cooca-system-guide
description: Panduan induk sistem Cooca ERP & POS v2.0 (Layer 3: Curated Master System Manual). Aktifkan saat user meminta penjelasan cara kerja sistem, konsultasi modul bisnis (Costing HPP, POS Kasir, Gudang & GR, Toko Online, Keuangan & Jurnal, CRM, WA, Billing, Pajak, HRM, MAR, Portal Karyawan), blueprint arsitektur rekayasa developer (33 DDD domain packages, scoping tenant, WhatsApp Cloud API, Tier pricing, internal fraud protection, POS hardware ESC/POS), atau penelusuran sistem (Traceability Matrix) berdasarkan docs/SYSTEM_GUIDE.md.
---

# COOCA - PANDUAN INDUK SISTEM (SYSTEM GUIDE SKILL)

Skill ini berfungsi sebagai **Master System Consultant & Architecture Reference** resmi untuk ekosistem Cooca ERP & POS v2.0, berakar langsung pada dokumen kurasi tertinggi:
[`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md).

Skill ini memandu AI Agent dalam menjelaskan cara kerja sistem kepada pemilik bisnis (dalam bahasa non-teknis yang hangat dan mudah dipahami pengguna usia 40–65+ tahun), serta memandu para pengembang perangkat lunak dalam menerapkan arsitektur teknis 33 Domain Packages DDD, sistem keamanan multi-tenant, dan otomasi latar belakang.

---

## 🧭 File Referensi Pendukung

* [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md) — Dokumen induk Layer 3 (Curated Master System Manual).
* [`references/business-owner-manual.md`](file:///c:/laragon/www/cooca_core/.agents/skills/cooca-system-guide/references/business-owner-manual.md) — Intisari panduan operasional 14 sub-modul untuk Pemilik Usaha.
* [`references/engineering-blueprint.md`](file:///c:/laragon/www/cooca_core/.agents/skills/cooca-system-guide/references/engineering-blueprint.md) — Intisari 18 blueprint teknis arsitektur rekayasa pengembang.
* [`docs/system/INDEX.md`](file:///c:/laragon/www/cooca_core/docs/system/INDEX.md) — Indeks navigasi Layer 2 (Current State).

---

## 🏛️ Hierarki Sumber Kebenaran

```text
Aktual Kode Sumber (Actual Source Code)
    >
Skema Basis Data & Migrasi (Database Schema)
    >
Pengujian Otomatis Terverifikasi (Tests)
    >
Dokumentasi Layer 2 (docs/system/)
    >
Panduan Induk Layer 3 (docs/SYSTEM_GUIDE.md)
    >
Riwayat Historis Layer 1 (docs/AiWorkHistory.md)
    >
Asumsi AI (DILARANG KERAS MENJADIKAN ASUMSI SEBAGAI FAKTA)
```

---

## 📑 5 Bab Utama `docs/SYSTEM_GUIDE.md`

### 1. Ikhtisar Sistem & Filosofi Desain
- **All-in-One Business OS**: Memadukan ERP enterprise dengan kesederhanaan Bento Grid Apple HIG v2.0.
- **Filosofi Zero-Manual UI**: Sekali lihat langsung paham dalam 3 detik tanpa perlu buku manual tebal.
- **Integritas Faktual & Anti-Hiperbola**: Bebas klaim fiktif ("AI Quantum 99.999%"), angka keuangan riil `tabular-nums`.
- **Zero Plaintext Credential Exposure**: Seluruh kunci API, token, dan PIN di-masking (`••••••••`) di UI.
- **Ergonomi Ramah Boomer**: Font input minimal 16px (anti-zoom Safari), touch target minimal 48–52px, format ribuan otomatis (`Rp 150.000`), dan pesan penenang jiwa (*No-Panic Microcopy*).
- **Modal-First Full-Size Canvas**: Pop-up XXL di Desktop (`max-w-[95vw] lg:max-w-5xl xl:max-w-6xl`) dan Bottom Sheet di Mobile (`w-full max-h-[95vh]`).

### 2. Konsep Inti & Arsitektur Multi-Tenancy
- **Shared Database Isolation**: Terisolasi mutlak di tingkat query via `Context::requireBusiness()` dan foreign key `business_id`.
- **Global Customer Identity**: 1 akun pembeli publik untuk berbelanja di berbagai toko, dengan keranjang belanja (`CustomerCart`) terisolasi per tenant.
- **Integritas Finansial Non-Destruktif**: Rumus subtotal, pajak, diskon, HPP, dan buku besar tidak boleh diubah secara destruktif.

### 3. Panduan Pemilik Usaha (Business Owner Operations Manual - 14 Sub-Modul)
1. **3.1 Costing & HPP Ilmiah**: Kalkulasi modal akurat, resep BOM, biaya mesin/buruh, simulasi BEP.
2. **3.2 Operasional Kasir POS**: Input transaksi kilat, denah meja, multi-harga saluran (GoFood/GrabFood), shift tutup kasir *Blind Cash Count*.
3. **3.3 Manajemen Stok & Gudang**: Gudang pusat vs sub-gudang cabang (`parent_id`), mutasi barang, penerimaan surat jalan (GR).
4. **3.4 Toko Online (Storefront)**: Etalase online 24/7, verifikasi bukti transfer anti-IDOR, estimasi ongkir otomatis.
5. **3.5 Keuangan & Buku Kas**: Pembukuan otomatis berpasangan (Auto-Journal), buku kas/bank, rekonsiliasi.
6. **3.6 Katalog Produk & Kombo**: Paket bundling kombo (aturan stok bottleneck & akumulasi HPP), varian modifiers.
7. **3.7 Bahan Baku & Jasa Bebas Stok**: Master bahan mentah, supplier, dan pemisahan produk fisik vs jasa servis.
8. **3.8 Toko Online & Landing Page Studio**: Editor website publik bento tanpa koding, preset 25 industri.
9. **3.9 Saluran WhatsApp Resmi**: WhatsApp Cloud API Meta, nota digital otomatis, broadcast promo, pengingat piutang.
10. **3.10 Media Sosial Terpadu**: Integrasi posting konten otomatis ke Meta (IG/FB), TikTok & LinkedIn.
11. **3.11 Pajak UMKM & Penggajian (HRM)**: PPh 21 TER, PP 55 (0.5%), slip gaji karyawan, komisi, kasbon, THR.
12. **3.12 Analitik Bisnis**: Tren omzet, laba kotor, produk terlaris, retensi pelanggan.
13. **3.13 Otorisasi Dokumen (MAR Engine)**: Maker-Approver-Releaser untuk transaksi berisiko tinggi.
14. **3.14 Portal Karyawan & Presensi**: Absensi GPS geofence + selfie kamera, slip gaji mandiri, RBAC guard.

### 4. Panduan Rekayasa Developer & AI Agent (18 Blueprint Arsitektur)
1. **4.1 33 Domain Packages DDD**: Struktur Domain-Driven Design di `app/Domain/`.
2. **4.2 Scoping Tenant & Proteksi Keamanan**: Aturan `business_id`, IDOR shield, CSRF, mass assignment.
3. **4.3 Mesin Otomasi Latar Belakang**: `AutoJournalService`, pemotongan stok otomatis saat POS checkout.
4. **4.4 Protokol Verifikasi 100% Zero-Error**: Larangan menyatakan selesai tanpa bukti lolos `php artisan test`.
5. **4.5 Dokumentasi Berkelanjutan Simultan**: Wajib memperbarui `AiWorkHistory.md` dan `SYSTEM_GUIDE.md` secara sinkron.
6. **4.6 Arsitektur WhatsApp Cloud API Multi-Tenant**: Webhook router, token isolation, queue dispatcher.
7. **4.7 Arsitektur Omnichannel Media Sosial**: UGC Post API, refresh token lifecycles.
8. **4.8 Arsitektur Pre-Order Dinamis**: Batch scheduling, lead-time cut-off harian, kuota kapasitas.
9. **4.9 Account Recovery Desk**: Otorisasi pemulihan akun admin via verifikasi aman.
10. **4.10 Billing Packages CMS**: Pengelolaan katalog harga paket langganan SaaS.
11. **4.11 Admin Posts CMS**: Manajemen artikel, kategori, dan SEO cluster konten.
12. **4.12 Admin Settings 5-Service Hub**: Pusat integrasi WhatsApp, Payment Gateway, Courier, Mail, Maps.
13. **4.13 Blueprint Tier Pricing v2.3 & Pajak**: Limitasi kuota tier paket, multi-cabang, kalkulator PPh/PPN.
14. **4.14 Audit & Proteksi Fraud Internal**: Supervisor PIN, blind cash count, three-way matching, two-step transfer, immutable audit logs.
15. **4.15 POS Hardware & ESC/POS Thermal**: Driver thermal printer 58/80mm, cash drawer kicker, local agent bridge.
16. **4.16 Penataan 6-Hub Modul & Optimasi Kinerja**: Redis caching, offline-first PWA, composite indexing, queue worker.
17. **4.17 Limitasi Kuota, No Data Punishment & Data Pruning Previewer**: Auto-gating suspend over-quota saat downgrade paket tanpa hapus data, previewer pembersihan log.
18. **4.18 F&B Multi-Pricing, Delivery Tags & Product Bundling Engine**: Arsitektur harga saluran ojol, paket kombo rekursif, hierarki cabang-gudang `parent_id`.

### 5. Matriks Penelusuran Pengetahuan (Traceability Matrix)
Pemetaan silang antara modul bisnis, berkas kode sumber terkait, file migrasi basis data, dan dokumentasi Layer 2 di `docs/system/`.

---

## 💬 Protokol Konsultasi & Penggunaan Skill

Ketika user bertanya tentang fitur, alur operasional, atau arsitektur Cooca:

1. **Kenali Persona Penanya**:
   - Jika penanya adalah **Pemilik Usaha / Non-Teknis**: Jawab menggunakan pendekatan **Bab 3 (Panduan Pemilik Usaha)**. Gunakan bahasa Indonesia yang bersahabat, analogi bisnis praktis, dan panduan langkah 1-2-3 tanpa istilah teknis membingungkan.
   - Jika penanya adalah **Pengembang / AI Agent**: Jawab menggunakan pendekatan **Bab 4 (Panduan Rekayasa Developer)**. Cantumkan nama class Controller/Service, skema tabel, middleware, relasi Eloquent, dan guardrails keamanan.
2. **Kebenaran Faktual Berbasis Kode**:
   Verifikasi jawaban terhadap kondisi kode terkini di `app/Domain/`, `routes/`, dan `docs/system/`.
3. **Pembaruan Berkelanjutan**:
   Jika ada fitur baru yang diimplementasikan pada sistem, pastikan Bab 3, Bab 4, dan Bab 5 pada [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md) diperbarui secara simultan!
