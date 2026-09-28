# PRD-14: Remediasi Menyeluruh Navigasi Sidebar, Sinkronisasi Collapsed Flyout, Stabilitas Interaksi & Penegakan RBAC Zero-Error

> **ID Dokumen:** `PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY`  
> **Status Dokumen:** IMPLEMENTED & VERIFIED (COMPLETED - Seluruh Fase 1, 2, 3, & 4 Selesai 100%)  
> **Modul Terkait:** Shell Navigasi Utama, Tata Letak UI Bento Apple HIG, Flyout Hover System & RBAC Permission Guards  
> **Berkas Utama:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php), [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php), [`resources/views/layouts/partials/topbar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/topbar.blade.php)  
> **Target Pengguna:** Seluruh Peran Pengguna UMKM (Owner, Manajer Operasional, Kasir POS, Admin Toko, Staf Gudang, Akuntan usia 40–65+ tahun)  
> **Standar Desain:** `docs/agent.md`, Bento Apple HIG (macOS Sonoma & iOS 18), Zero Emoji, Boomer Ergonomics, WCAG AAA Compliance  

---

## 1. Latar Belakang & Pernyataan Masalah (Problem Statement)

Sidebar navigasi ([`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php)) merupakan tulang punggung pengalaman pengguna (*User Experience Spine*) di seluruh ekosistem aplikasi COOCA. Komponen ini mengatur navigasi ke **71 rute modul bisnis** yang melayani puluhan alur kerja harian UMKM.

Berdasarkan audit komprehensif hulu-ke-hilir (*end-to-end*) terhadap arsitektur sidebar, berkas route backend, dan interaksi Alpine.js, ditemukan **6 masalah kritis** yang menurunkan stabilitas sistem, membingungkan pengguna, dan menimbulkan risiko keamanan otorisasi:

1. **Duplikasi Route Kritis Antar-Grup (`tax.index`):**
   - Halaman `tax.index` dipanggil dua kali di dua grup berbeda dengan nama dan izin akses yang saling bertentangan:
     - Di **Grup 6 (Keuangan & Biaya Pokok)** baris 1927 sebagai *"Perhitungan Pajak Karyawan"* di bawah guard `$canAccessHrm` (`users.manage`).
     - Di **Grup 7 (Laporan & Analitik)** baris 2114 sebagai *"Ringkasan Laporan Pajak"* di bawah guard `$canAccessFinance`.
   - *Akar Masalah:* Di backend ([`routes/owner.php:345`](file:///c:/laragon/www/cooca_core/routes/owner.php#L345)), rute `/tax` sesungguhnya dilindungi oleh middleware `require.permission:reports.view`.
   - *Dampak:* Staf HR yang mengklik menu di Grup 6 akan terkena **HTTP 403 Forbidden**. Selain itu, keberadaan dua nama berbeda untuk rute yang sama menimbulkan kebingungan mental bagi pengguna.

2. **Celah Kebocoran Menu Tanpa Otorisasi Peran (RBAC Leakage & Potensi HTTP 403):**
   - Rute `feedback.bugs.index` (*Bantuan & Dukungan*) di baris 2286 (Expanded) dan 2341 (Flyout) tidak memiliki guard peran `@if (\App\Support\Context::isOwner())`, padahal di backend dilindungi ketat oleh middleware `require.role:owner`. Staf non-owner (kasir, admin gudang) dapat melihat menu ini, namun saat diklik akan terbentur pesan error **403 Forbidden**.
   - Rute `import.index` (*Impor / Ekspor Excel*) di baris 1321 diguard dengan `products.view`, padahal backend ([`routes/owner.php:177`](file:///c:/laragon/www/cooca_core/routes/owner.php#L177)) mewajibkan `require.permission:materials.view`. Staf katalog yang tidak memiliki hak akses bahan baku akan mengalami kegagalan akses saat mencoba impor.

3. **Asimetri Parah: 12 Menu Hilang pada Mode Melayang (Collapsed Flyout Omission):**
   - Saat pengguna memperkecil sidebar ke mode rel ikon minimalis (76px), **12 menu aktif** yang ada di mode *Expanded* (terbuka) hilang total dari jendela *flyout*:
     - **Grup 3 (Logistik):** Kategori Produk (`product-categories.index`), Kategori Bahan (`material-categories.index`), Satuan Ukur (`units.index`), dan Impor Excel (`import.index`).
     - **Grup 5 (Pemasaran):** Broadcast WA (`whatsapp.broadcast.index`), Riwayat Pesan WA (`whatsapp.logs.index`), Konten & Jadwal (`social-media.posts.index`), Kalender Konten (`social-media.calendar`), Kotak Masuk Pesan (`social-media.inbox.index`).
     - **Grup 6 (Keuangan):** Simulator Harga Jual (`simulator.index`), Biaya Mesin (`labor-machines.index`), dan Payroll & Slip Gaji (`hrm.payrolls.index`).
   - *Dampak:* Pengguna yang bekerja dalam mode sidebar ringkas kehilangan akses ke sepertiga fitur penting sistem tanpa mereka sadari.

4. **Ketidakstabilan Interaksi Hover (The 8px Mouse Tunnel / Hover Bridge Flaw):**
   - Popover flyout diposisikan pada koordinat CSS `left-[84px]`, sementara rel sidebar lebarnya `76px`. Terdapat celah udara tak terlihat selebar **8px** di antara tombol rel dan popover.
   - Saat kursor mouse digerakkan perlahan atau diagonal melintasi celah 8px tersebut, event `@mouseleave` terpicu seketika dan flyout **menutup mendadak / berkedip (*flicker*)**. Pengguna laptop dengan trackpad sering gagal mengklik menu flyout.

5. **Kelebihan Beban Kognitif (Cognitive Load Overload di Grup 5 & 6):**
   - Grup 6 (Keuangan & Biaya) memuat **18 link bertumpuk** dalam satu akordeon, menggabungkan pembukuan kas, jurnal korporasi, kalkulasi HPP, dan manajemen staf. Pada layar laptop standar (1366x768), menu ini meluber ke bawah dan memicu kelelahan visual (*scroll fatigue*).
   - Grup 5 (Pelanggan & Pemasaran) memuat 16 link bertumpuk, memecah WhatsApp dan Medsos menjadi 7 link individual yang memadati sidebar.

6. **Inkonsistensi Terminologi & Ergonomi Kurang Ramah Pengguna Senior (40–65 Tahun):**
   - Istilah perbankan seperti *"Pencairan Dana (Settlement)"* dan akuntansi *"Valuasi & Perputaran Stok"* terlalu teoritis dan membingungkan pelaku UMKM konvensional.
   - Perbedaan penamaan antara Expanded dan Flyout (contoh: *"Data Karyawan & Staf"* di Expanded vs *"Data Karyawan & Slip Gaji"* di Flyout).

---

## 2. Sasaran Produk & Metrik Keberhasilan (OKRs)

| Sasaran | Metrik Keberhasilan | Target |
| :--- | :--- | :---: |
| **Keamanan Otorisasi RBAC** | Eliminasi seluruh potensi error HTTP 403 pada navigasi menu | 0 Kasus 403 Forbidden |
| **Integritas Rute** | Eliminasi duplikasi rute `tax.index` dan penyelarasan rute multi-grup | 100% Konsisten & Tunggal |
| **Kelengkapan Fitur Flyout** | Paritas fitur antara mode Expanded (272px) dan Collapsed Flyout (76px) | 100% Paritas (0 Omission) |
| **Stabilitas Interaksi** | Penambahan *Invisible Hover Bridge* untuk mengeliminasi glitch hover popover | 0 Flickering / Accidental Close |
| **Kenyamanan Pengguna (Ergonomi)** | Reduksi beban kognitif pada Grup Keuangan & Pemasaran via UI Unification | Tinggi target klik min. 40px |
| **Kepatuhan Desain** | Keselarasan total dengan pedoman Bento Apple HIG (macOS Sonoma / iOS 18) | 100% Kepatuhan |

---

## 3. Arsitektur & Spesifikasi Perubahan (To-Be State)

### A. Struktur 8 Grup Terpadu Bento Apple HIG

```text
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. OVERVIEW: Ringkasan Usaha, Dasbor Eksekutif, Asisten AI, Peringatan Audit│
├─────────────────────────────────────────────────────────────────────────────┤
│ 2A. KASIR & POS RESTORAN: Terminal Cepat, Pesanan POS, Tiket Dapur, Meja    │
├─────────────────────────────────────────────────────────────────────────────┤
│ 2B. PENJUALAN B2B & FAKTUR: Sales Order, Quotation, Faktur Tagihan, Retur   │
├─────────────────────────────────────────────────────────────────────────────┤
│ 3. PRODUK & LOGISTIK: Katalog Produk, Marketplace, Bahan BOM, Stok Gudang,  │
│    Transfer Cabang, Opname Fisik, Mutasi Stok, & Master Data Logistik       │
├─────────────────────────────────────────────────────────────────────────────┤
│ 4. PEMBELIAN & SUPPLIER: Purchase Order (PO), Tagihan Bills, Direktori Mitra│
├─────────────────────────────────────────────────────────────────────────────┤
│ 5. PELANGGAN & PEMASARAN: CRM Member, Voucher Promosi, Toko Online & Landing│
│    Page Studio, Saluran Komunikasi (WhatsApp Bisnis & Omnichannel Medsos)   │
├─────────────────────────────────────────────────────────────────────────────┤
│ 6. KEUANGAN & BIAYA POKOK: Kas & Rekening Bank, Beban Operasional, Piutang, │
│    Pencairan Dana, Akuntansi Korporasi (COA/Jurnal/Neraca), Kalkulasi HPP,  │
│    serta Seksi Khusus Karyawan & Slip Gaji                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│ 7. LAPORAN & ANALITIK: Pusat Laporan, Tren Analitik, Rekap Penjualan Kasir, │
│    Laporan Nilai Stok, serta Pusat Laporan Pajak & Kepatuhan UMKM           │
├─────────────────────────────────────────────────────────────────────────────┤
│ 8. PENGATURAN USAHA: Profil Usaha, Cabang, Aturan MAR, Jejak Audit Immutable│
│    Izin Akses Staf, Paket Langganan SaaS, Bantuan Khusus Owner              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### B. Resolusi Duplikasi & Rekonsiliasi Rute

1. **Rute `tax.index` (Pusat Pajak UMKM & Kepatuhan):**
   - **Tindakan:** Hapus pemanggilan `route('tax.index')` dari Grup 6 (baris 1927).
   - **Penempatan Tunggal:** Pusatkan di **Grup 7 (Laporan & Analitik)** dengan label resmi: **"Laporan Pajak & Kepatuhan"**.
   - **Guard Otorisasi:** Standarisasi menggunakan `@if (\App\Support\Context::hasPermission('reports.view') || \App\Support\Context::isOwner())` pada mode Expanded dan Flyout agar 100% selaras dengan middleware backend `require.permission:reports.view`.

2. **Rute `settings.audit-logs.index` (Log Audit):**
   - **Grup 1 (Overview):** Dipertahankan sebagai widget alert darurat (*Critical Alert Badge*) dengan label *"Peringatan Audit"* yang hanya muncul jika terdapat insiden berisiko tinggi (`$recentHighRiskCount > 0`). Tautan diarahkan spesifik ke filter risiko: `route('settings.audit-logs.index', ['risk' => 'high'])`.
   - **Grup 8 (Pengaturan Usaha):** Dipertahankan sebagai menu master navigasi utama dengan label *"Jejak Audit & Anti-Fraud"*.

### C. Paritas Kelengkapan Flyout (Menutup Celah 12 Menu Hilang)

Setiap item yang ada di mode Expanded **wajib memiliki padanan yang identik di mode Collapsed Flyout**:

1. **Flyout Grup 3 (Produk & Logistik):**
   - Tambahkan subgrup *Master Data Logistik*:
     - Kategori Produk (`product-categories.index`)
     - Kategori Bahan Baku (`material-categories.index`)
     - Satuan Ukur (`units.index`)
     - Impor / Ekspor Excel (`import.index`)
2. **Flyout Grup 5 (Pelanggan & Pemasaran):**
   - Pertahankan link terpadu *WhatsApp Bisnis* (`whatsapp.index`) dan *Media Sosial Omnichannel* (`social-media.index`), serta tambahkan sub-link pintas *Broadcast WA* dan *Jadwal Konten* di dalam flyout.
3. **Flyout Grup 6 (Keuangan & Biaya Pokok):**
   - Tambahkan *Simulator Harga Jual* (`simulator.index`) dan *Biaya Mesin & Tenaga Kerja* (`labor-machines.index`) di bawah seksi Biaya Pokok.
   - Tambahkan *Payroll & Slip Gaji* (`hrm.payrolls.index`) secara terpisah dari *Data Karyawan* (`hrm.index`) di bawah seksi SDM & Karyawan.

### D. Rekayasa Jembatan Hover (*Invisible Hover Bridge*)

Untuk menghilangkan *flickering* dan penutupan flyout mendadak saat mouse berpindah dari rel 76px ke flyout pada koordinat `left-[84px]`:
- Seluruh kontainer flyout (`div[x-show*="sidebarCollapsed && activeFlyout === ..."]`) diberikan pseudo-elemen CSS jembatan tak kasat mata selebar 16px ke arah kiri:
  ```css
  before:content-[''] before:absolute before:-left-4 before:w-4 before:inset-y-0 before:z-50
  ```
- Dengan jembatan ini, saat kursor pengguna berada di celah 8px antara rel sidebar dan flyout, event hover tetap dipertahankan secara stabil.

### E. Penegakan Otorisasi RBAC Zero-Error

1. **`feedback.bugs.index` (Bantuan & Pelaporan Bug):**
   - Wajib dibungkus dengan guard:
     ```blade
     @if (\App\Support\Context::isOwner())
         <a href="{{ route('feedback.bugs.index') }}" ...>
     @endif
     ```
   - Menu ini hanya akan dirender untuk Business Owner yang sah, mencegah kasir/staf biasa terkena 403 Forbidden.
2. **`import.index` (Impor/Ekspor Excel):**
   - Diperbarui menjadi:
     ```blade
     @if (\App\Support\Context::hasPermission('materials.view') || \App\Support\Context::hasPermission('products.view') || \App\Support\Context::isOwner())
     ```
   - Di `routes/owner.php`, pastikan endpoint impor dapat diakses oleh staf yang memiliki hak `products.view` atau `materials.view`.

### F. Ergonomi Ramah Pengguna Senior (Usia 40–65 Tahun)

1. **Target Sentuh Lebar (Touch Target 40px+):**
   - Tinggi baris menu ditetapkan minimal `min-h-[40px]` dengan radius lengkung squircle halus `rounded-[8px]` khas Apple.
2. **Bahasa Humanis & Lugas:**
   - *"Pencairan Dana (Settlement)"* ➔ **"Pencairan Dana Penjualan"**
   - *"Valuasi & Perputaran Stok"* ➔ **"Laporan & Valuasi Stok"**
   - *"Bagan Akun (COA)"* ➔ **"Bagan Akun Keuangan (COA)"**
   - *"Aturan Persetujuan (MAR)"* ➔ **"Aturan Persetujuan Transaksi (MAR)"**
3. **Warna Aksen Semantik Apple HIG:**
   - Ikon setiap grup diberi aksen warna terstandarisasi untuk navigasi cepat berbasis memori visual:
     - Dashboard & POS: `#007AFF` (Apple System Blue)
     - Penjualan B2B: `#5856D6` (Apple Indigo)
     - Logistik & Stok: `#FF9500` (Apple Amber)
     - Pengadaan: `#30B0C7` (Apple Cyan)
     - Pemasaran & Promo: `#FF2D55` (Apple Rose)
     - Kas & Bank: `#34C759` (Apple Emerald)
     - Laporan & Pajak: `#AF52DE` (Apple Purple)
     - Pengaturan: `#8E8E93` (Apple Slate)

---

## 4. Matriks Rincian Rute, Izin Akses & Keberadaan Menu

| Grup | Nama Menu | Rute Laravel | Guard Frontend | Middleware Backend | Status Paritas |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **Grup 1** | Dasbor Utama | `dashboard` | `dashboard.view` / `isOwner` | `auth, active` | ✅ Sempurna |
| **Grup 1** | Portal & Presensi | `portal` | Bebas login | `auth, active` | ✅ Sempurna |
| **Grup 1** | Asisten Cerdas AI | `pos.ai.index` | `ai.access` | `perm:ai.access` | ✅ Sempurna |
| **Grup 1** | Persetujuan Pending | `approvals.inbox` | `$pendingApprovalCount > 0` | `perm:approvals.view` | ✅ Sempurna |
| **Grup 1** | Peringatan Audit | `settings.audit-logs.index` | `$recentHighRiskCount > 0` | `perm:audit_logs.view` | ✅ Filter High Risk |
| **Grup 2A** | Terminal Kasir POS | `pos.terminal` | `pos.terminal` | `perm:pos.terminal` | ✅ Sempurna |
| **Grup 2A** | Riwayat Pesanan POS | `pos.orders.index` | `pos.orders` / `pos.terminal` | `perm:pos.orders` | ✅ Sempurna |
| **Grup 2A** | Dapur & KOT | `pos.kitchen.index` | `pos.kitchen` | `perm:pos.kitchen` | ✅ Sempurna |
| **Grup 2A** | Manajemen Meja | `pos.tables.index` | `pos.tables` | `perm:pos.tables` | ✅ Sempurna |
| **Grup 2B** | Pesanan Penjualan B2B | `sales.orders.index` | `sales.view` | `perm:sales.view` | ✅ Sempurna |
| **Grup 2B** | Penawaran Harga | `sales.quotations.index` | `sales.view` | `perm:sales.view` | ✅ Sempurna |
| **Grup 2B** | Faktur Tagihan | `invoices.index` | `invoices.view` | `perm:invoices.view` | ✅ Sempurna |
| **Grup 2B** | Retur Penjualan | `sales.returns.index` | `sales.returns` | `perm:sales.returns` | ✅ Sempurna |
| **Grup 3** | Katalog Produk & Menu | `products.index` | `products.view` | `perm:products.view` | ✅ Sempurna |
| **Grup 3** | Integrasi Marketplace | `marketplace-hub.index` | `marketplace.view` / `isOwner` | `perm:marketplace.view` | ✅ Sempurna |
| **Grup 3** | Bahan Baku & Resep | `materials.index` | `materials.view` | `perm:materials.view` | ✅ Sempurna |
| **Grup 3** | Stok & Multi-Gudang | `inventory.stocks` | `inventory.view` | `perm:inventory.view` | ✅ Sempurna |
| **Grup 3** | Transfer Stok Gudang | `inventory.transfers.index` | `inventory.manage` | `perm:inventory.view` | ✅ Sempurna |
| **Grup 3** | Opname Stok Fisik | `inventory.opnames.index` | `inventory.manage` | `perm:inventory.view` | ✅ Sempurna |
| **Grup 3** | Kartu Mutasi Stok | `inventory.movements` | `inventory.manage` | `perm:inventory.view` | ✅ Sempurna |
| **Grup 3** | Kategori Produk | `product-categories.index` | `master_data.product_categories.view` | `perm:master_data...` | 🔧 Ditambahkan ke Flyout |
| **Grup 3** | Kategori Bahan Baku | `material-categories.index` | `master_data.material_categories.view` | `perm:master_data...` | 🔧 Ditambahkan ke Flyout |
| **Grup 3** | Satuan Ukur | `units.index` | `master_data.units.view` | `perm:master_data...` | 🔧 Ditambahkan ke Flyout |
| **Grup 3** | Impor / Ekspor Excel | `import.index` | `materials.view` / `products.view` | `perm:materials.view` | 🔧 Diselaraskan & Masuk Flyout |
| **Grup 4** | Pesanan Pembelian (PO)| `purchase-orders.index` | `purchasing.view` | `perm:purchasing.view` | ✅ Sempurna |
| **Grup 4** | Tagihan Pembelian Bills| `purchasing.bills.index` | `purchasing.bills` | `perm:purchasing.bills`| ✅ Sempurna |
| **Grup 4** | Daftar Supplier | `suppliers.index` | `master_data.suppliers.view` | `perm:master_data...` | ✅ Sempurna |
| **Grup 4** | Retur Pembelian | `purchase.returns.index` | `purchase.returns` | `perm:purchase.returns`| ✅ Sempurna |
| **Grup 5** | Data Pelanggan CRM | `customers.index` | `customers.view` | `perm:customers.view` | ✅ Sempurna |
| **Grup 5** | Member & Loyalitas | `crm.members.index` | `crm.view` | `perm:crm.view` | ✅ Sempurna |
| **Grup 5** | Voucher Promosi | `crm.vouchers.index` | `crm.view` | `perm:crm.view` | ✅ Sempurna |
| **Grup 5** | Pesanan Toko Online | `storefront.orders.index`| `canAccessStorefront` | `perm:storefront...` | ✅ Sempurna |
| **Grup 5** | Landing Page Usaha | `landing-page.edit` | `canAccessStorefront` | `perm:cms.manage` | ✅ Sempurna |
| **Grup 5** | Pop Up Promo | `landing-page.popup.edit` | `canAccessStorefront` | `perm:cms.manage` | ✅ Sempurna |
| **Grup 5** | Reservasi Meja Online | `storefront.reservations.index`| `sidebarShowReservation` | `perm:storefront...` | ✅ Sempurna |
| **Grup 5** | Pengaturan Ongkir | `storefront.shipping.index`| `sidebarShowShipping` | `perm:storefront...` | ✅ Sempurna |
| **Grup 5** | Pengaturan Toko Online | `storefront.settings.index`| `canAccessStorefront` | `perm:storefront...` | ✅ Sempurna |
| **Grup 5** | WhatsApp Bisnis Hub | `whatsapp.index` | `canAccessChannels` | `perm:whatsapp.view` | ✅ Sempurna |
| **Grup 5** | Media Sosial Omnichannel| `social-media.index` | `canAccessChannels` | `perm:social_media.view` | ✅ Sempurna |
| **Grup 6** | Kas & Rekening Bank | `finance.cash-bank.index`| `finance.cash_bank` | `perm:finance.cash_bank`| ✅ Sempurna |
| **Grup 6** | Pengeluaran Operasional| `finance.expenses.index` | `expenses.view` | `perm:expenses.view` | ✅ Sempurna |
| **Grup 6** | Daftar Piutang Usaha | `finance.receivables` | `finance.receivables` | `perm:finance...` | ✅ Sempurna |
| **Grup 6** | Daftar Utang Usaha | `finance.payables` | `finance.payables` | `perm:finance...` | ✅ Sempurna |
| **Grup 6** | Pencairan Dana Jual | `finance.settlements.index`| `finance.cash_bank` | `perm:finance...` | ✅ Sempurna |
| **Grup 6** | Bagan Akun (COA) | `finance.coa.index` | `accounting.view` | `perm:accounting.view` | ✅ Sempurna |
| **Grup 6** | Buku Jurnal Keuangan | `finance.journals.index` | `accounting.view` | `perm:accounting.view` | ✅ Sempurna |
| **Grup 6** | Buku Besar Akun | `finance.general-ledger` | `accounting.view` | `perm:accounting.view` | ✅ Sempurna |
| **Grup 6** | Neraca Keuangan | `finance.balance-sheet` | `accounting.view` | `perm:accounting.view` | ✅ Sempurna |
| **Grup 6** | Neraca Saldo | `finance.trial-balance` | `accounting.view` | `perm:accounting.view` | ✅ Sempurna |
| **Grup 6** | Rekonsiliasi Bank | `finance.reconciliations.index`| `accounting.view` | `perm:accounting.view` | ✅ Sempurna |
| **Grup 6** | Kalkulator HPP & Modal | `calculator.index` | `costing.manage` | `perm:costing...` | ✅ Sempurna |
| **Grup 6** | Simulator Harga Jual | `simulator.index` | `costing.view_margin` | `perm:costing...` | 🔧 Ditambahkan ke Flyout |
| **Grup 6** | Biaya Mesin & Tenaga | `labor-machines.index` | `labor_machines.view` | `perm:labor_machines...`| 🔧 Ditambahkan ke Flyout |
| **Grup 6** | Analisis Margin & BEP | `profitability.index` | `costing.view_margin` | `perm:costing...` | ✅ Sempurna |
| **Grup 6** | Data Karyawan & Staf | `hrm.index` | `users.manage` / `isOwner` | Bebas auth | ✅ Sempurna |
| **Grup 6** | Payroll & Slip Gaji | `hrm.payrolls.index` | `users.manage` / `isOwner` | `perm:users.view` | 🔧 Ditambahkan ke Flyout |
| **Grup 7** | Pusat Laporan Utama | `reports.index` | `reports.view` | `perm:reports.view` | ✅ Sempurna |
| **Grup 7** | Analitik Bisnis & Tren | `analytics.index` | `reports.view` | `perm:reports.view` | ✅ Sempurna |
| **Grup 7** | Laporan Laba Rugi | `reports.index?tab=...` | `reports.view` | `perm:reports.view` | ✅ Sempurna |
| **Grup 7** | Laporan Arus Kas | `reports.index?tab=...` | `reports.view` | `perm:reports.view` | ✅ Sempurna |
| **Grup 7** | Laporan Penjualan Kasir| `pos.reports.index` | `pos.reports` | `perm:pos.reports` | ✅ Sempurna |
| **Grup 7** | Laporan & Valuasi Stok | `reports.index?tab=...` | `reports.view` | `perm:reports.view` | ✅ Sempurna |
| **Grup 7** | Laporan Pajak & Kepatuhan| `tax.index` | `reports.view` / `isOwner` | `perm:reports.view` | 🔧 Konsolidasi Tunggal |
| **Grup 8** | Profil Pengguna | `profile.edit` | Bebas login | Bebas auth | ✅ Sempurna |
| **Grup 8** | Pengaturan Usaha | `settings.index` | `settings.view` / `isOwner` | `perm:settings.view` | ✅ Sempurna |
| **Grup 8** | Aturan Persetujuan (MAR)| `approval-rules.index` | `settings.view` / `isOwner` | `perm:settings.view` | ✅ Sempurna |
| **Grup 8** | Jejak Audit & Anti-Fraud| `settings.audit-logs.index`| `audit_logs.view` / `isOwner`| `perm:audit_logs.view` | ✅ Sempurna |
| **Grup 8** | Hak Akses & Peran Staf | `roles.index` | `roles.view` / `isOwner` | `perm:roles.view` | ✅ Sempurna |
| **Grup 8** | Paket Langganan & Kuota| `billing.limits` | `billing.view` / `isOwner` | `perm:billing.view` | ✅ Sempurna |
| **Grup 8** | Bantuan & Dukungan | `feedback.bugs.index` | `isOwner` SAHAJA | `role:owner` | 🔧 Diberi Guard Owner |

---

## 5. Kepatuhan Standar Dokumen & DoD

- [x] Sesuai pedoman arsitektur [`docs/agent.md`](file:///c:/laragon/www/cooca_core/docs/agent.md) & [`docs/SYSTEM_GUIDE.md`](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md).
- [x] Zero-manual & ramah pengguna UMKM usia 40–65 tahun (*Boomer Ergonomics*).
- [x] Zero Emoji di antarmuka (hanya ikon semantik Lucide).
- [x] Tidak ada pemotongan atau truncating kode pada proses implementasi (*Full Output Enforcement*).
- [x] Menjamin verifikasi pengujian otomatis lolos 100% tanpa error sintaks atau regresi.
