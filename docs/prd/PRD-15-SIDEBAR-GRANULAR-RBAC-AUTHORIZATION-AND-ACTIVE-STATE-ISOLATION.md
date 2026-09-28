# PRD-15: Remediasi Otorisasi Granular RBAC, Eliminasi Jebakan HTTP 403 & Isolasi Status Aktif Deterministik pada Navigasi Sidebar

> **ID Dokumen:** `PRD-15-SIDEBAR-GRANULAR-RBAC-AUTHORIZATION-AND-ACTIVE-STATE-ISOLATION`  
> **Status Dokumen:** READY FOR IMPLEMENTATION / COMPLETED IN CODEBASE  
> **Modul Terkait:** Navigasi Sidebar (Bento Apple HIG), RBAC Authorization Engine, Active State Detection, Security Guardrails  
> **Berkas Utama:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php), [`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php), [`database/seeders/RbacSeeder.php`](file:///c:/laragon/www/cooca_core/database/seeders/RbacSeeder.php)  
> **Target Pengguna:** Seluruh Peran Pengguna UMKM (Owner, Manajer Operasional, Kasir POS, Admin Gudang, Admin E-Commerce/CMS, Akuntan, Staf Pemasaran)  
> **Standar Desain & Keamanan:** `docs/agent.md`, Zero 403 Traps, Granular Least Privilege, Bento Apple HIG, WCAG AAA  

---

## 1. Latar Belakang & Pernyataan Masalah (Problem Statement)

Sidebar navigasi ([`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php)) adalah portal kontrol operasional utama bagi seluruh peran pengguna di ekosistem COOCA. Setelah penyelesaian PRD-14 yang menuntaskan paritas tata letak, jembatan hover *anti-flicker*, dan estetika Apple HIG, audit forensik mendalam terhadap **723 rute Laravel**, middleware backend ([`routes/owner.php`](file:///c:/laragon/www/cooca_core/routes/owner.php)), dan matriks hak akses ([`RbacSeeder.php`](file:///c:/laragon/www/cooca_core/database/seeders/RbacSeeder.php)) mengungkap **2 kelemahan arsitektur fundamental**:

### 1.1 Jebakan Otorisasi Komposit & Error HTTP 403 Forbidden (*Over-Permissive Composite Guards*)
Blade template sidebar banyak menggunakan variabel *composite* di tingkat grup (misal: `$canAccessReports`, `$canAccessChannels`, `$canAccessStorefront`, `$canAccessSettings`) yang menggabungkan beberapa izin menjadi satu ekspresi boolean `OR`. Ketika variabel ini dipakai untuk membungkus sub-menu individual yang sesungguhnya mewajibkan hak akses spesifik di backend, timbul celah keamanan dan UX buruk:
1. **Grup 7 (Laporan & Analitik):** Kasir POS yang hanya memiliki izin `pos.reports` (untuk cetak rekap shift harian) diberikan tampilan seluruh menu laporan eksekutif (Laba Rugi, Arus Kas, Neraca, Valuasi Stok) karena `$canAccessReports = hasPermission('reports.view') || hasPermission('pos.reports')`. Saat kasir mengklik menu tersebut, sistem menolak dengan **HTTP 403 Forbidden** karena backend mewajibkan `reports.view`.
2. **Grup 5 (Saluran & Pemasaran):** `$canAccessChannels` hanya menghitung izin WhatsApp (`whatsapp.view`, `whatsapp.manage`) dan mengabaikan `social_media.view`. Akibatnya:
   - Staf marketing dengan izin `social_media.view` tidak dapat melihat menu Media Sosial sama sekali (omission).
   - Staf CS dengan izin `whatsapp.view` dapat melihat menu Broadcast WA (`whatsapp.manage`) dan seluruh menu Medsos, yang memicu **HTTP 403 Forbidden** saat diklik.
3. **Grup 5 (Storefront Toko Online vs CMS Landing Page):** Variabel `$canAccessStorefront` menyatukan izin CMS (`cms.manage`) dengan pesanan toko online (`storefront.orders.view`) dan pengiriman (`storefront.shipping.manage`). Staf desainer/penulis konten melihat menu transaksi toko online (➔ 403), sedangkan staf logistik pengiriman melihat menu edit landing page (➔ 403).
4. **Grup 8 (Pengaturan Usaha):** Menu *Aturan Persetujuan Transaksi (MAR)* (`approval-rules.index`) dibungkus oleh `$canAccessSettings` (`settings.view`), padahal backend mewajibkan `approvals.manage`. Staf admin cabang terkena **HTTP 403 Forbidden**.
5. **Grup 2A (POS Kasir):** Menu *Riwayat Transaksi Kasir* (`pos.orders.index`) dibungkus oleh `hasPermission('pos.orders') || hasPermission('pos.terminal')`, padahal backend secara ketat mewajibkan `pos.orders`. Staf magang kasir yang hanya boleh melakukan kasir (`pos.terminal`) terkena **HTTP 403 Forbidden**.

### 1.2 Tabrakan Status Aktif Wildcard (*Active State Collisions*) & Ambiguitas Menu Audit
1. **Tabrakan Wildcard `settings.*`:** Menu *Pengaturan Usaha & Cabang* (`settings.index`) menggunakan pengecekan `request()->routeIs('settings.*')`. Karena rute Jejak Audit adalah `settings.audit-logs.index` dan Aturan Persetujuan adalah `settings.approval-rules.index`, saat pengguna membuka halaman Jejak Audit, menu *Pengaturan Usaha & Cabang* ikut menyala aktif secara keliru bersamaan (*dual active highlight*).
2. **Ambiguitas Navigasi Audit Log:** Terdapat dua tautan yang mengarah ke log audit: "Peringatan Audit" di Overview (`?risk=high`) dan "Jejak Audit & Anti-Fraud" di Pengaturan. Keduanya saling memicu status aktif karena tidak memvalidasi query string secara deterministik.

---

## 2. Sasaran Produk & Metrik Keberhasilan (OKRs)

| Sasaran (Objectives) | Indikator Kunci (Key Results) | Target |
| :--- | :--- | :---: |
| **Keamanan Hak Akses (Zero 403 Traps)** | Tidak ada satupun staf dengan peran terbatas yang melihat menu terlarang di sidebar | 0 Kasus HTTP 403 |
| **Prinsip Hak Akses Terkecil (Least Privilege)** | Seluruh link individual dibungkus oleh guard granular spesifik sesuai middleware backend | 100% Granular Guarding |
| **Isolasi Status Aktif (Zero Active Collision)** | Tidak ada dua menu yang menyala aktif bersamaan akibat wildcard prefix URL | 0 Tabrakan Status Aktif |
| **Unifikasi Struktur Informasi (IA)** | Eliminasi tautan duplikat log audit dengan memusatkan badge risiko ke menu utama | 1 Menu Tunggal Ber-badge |
| **Paritas Expanded vs Flyout** | Seluruh guard dan visual badge diterapkan identik pada mode terbuka (272px) dan melayang (76px) | 100% Paritas |

---

## 3. Matriks Penyelarasan Hak Akses Frontend vs Backend (RBAC Mapping)

Tabel berikut menjadi standar acuan tunggal (*single source of truth*) antara middleware backend di `routes/owner.php` dan guard Blade di `sidebar.blade.php`:

| Grup Menu | Tautan Menu (Route) | Middleware Backend Wajib | Guard Frontend Blade Baru (Expanded & Flyout) |
| :--- | :--- | :--- | :--- |
| **Grup 1 (Overview)** | `dashboard` | `require.permission:dashboard.view` / Owner | `@if (\App\Support\Context::isOwner() \|\| \App\Support\Context::hasPermission('dashboard.view'))` |
| | `portal` | Bebas (Seluruh Karyawan) | Tanpa Guard (Akses Publik Karyawan) |
| | `pos.ai.index` | `require.permission:ai.access` | `@if (\App\Support\Context::hasPermission('ai.access'))` |
| | `approvals.inbox` | `require.permission:approvals.view,approvals.manage` | `@if ($pendingApprovalCount > 0)` |
| **Grup 2A (POS)** | `pos.terminal` | `require.permission:pos.terminal` | `@if (\App\Support\Context::hasPermission('pos.terminal'))` |
| | `pos.orders.index` | `require.permission:pos.orders` | `@if (\App\Support\Context::hasPermission('pos.orders') \|\| \App\Support\Context::isOwner())` *(Hapus pos.terminal)* |
| | `pos.kitchen.index` | `require.permission:pos.kitchen` | `@if (\App\Support\Context::hasPermission('pos.kitchen'))` |
| | `pos.tables.index` | `require.permission:pos.tables` | `@if (\App\Support\Context::hasPermission('pos.tables'))` |
| **Grup 2B (B2B Sales)** | `sales.orders.index` | `require.permission:sales.view` | `@if (\App\Support\Context::hasPermission('sales.view'))` |
| | `sales.quotations.index` | `require.permission:sales.view` | `@if (\App\Support\Context::hasPermission('sales.view'))` |
| | `invoices.index` | `require.permission:invoices.view` | `@if (\App\Support\Context::hasPermission('invoices.view'))` |
| | `sales.returns.index` | `require.permission:sales.returns` | `@if (\App\Support\Context::hasPermission('sales.returns'))` |
| **Grup 3 (Logistik)** | `products.index` | `require.permission:products.view` | `@if (\App\Support\Context::hasPermission('products.view'))` |
| | `marketplace-hub.index` | `require.permission:marketplace.view` | `@if (\App\Support\Context::hasPermission('marketplace.view'))` |
| | `materials.index` | `require.permission:materials.view` | `@if (\App\Support\Context::hasPermission('materials.view'))` |
| | `inventory.stocks` | `require.permission:inventory.stocks` | `@if (\App\Support\Context::hasPermission('inventory.stocks'))` |
| | `inventory.transfers.index` | `require.permission:inventory.transfers` | `@if (\App\Support\Context::hasPermission('inventory.transfers'))` |
| | `inventory.opnames.index` | `require.permission:inventory.opnames` | `@if (\App\Support\Context::hasPermission('inventory.opnames'))` |
| | `inventory.movements` | `require.permission:inventory.movements` | `@if (\App\Support\Context::hasPermission('inventory.movements'))` |
| | `product-categories.index` | `require.permission:master_data.product_categories.view` | `@if (\App\Support\Context::hasPermission('master_data.product_categories.view'))` |
| | `material-categories.index` | `require.permission:master_data.material_categories.view` | `@if (\App\Support\Context::hasPermission('master_data.material_categories.view'))` |
| | `units.index` | `require.permission:master_data.units.view` | `@if (\App\Support\Context::hasPermission('master_data.units.view'))` |
| | `import.index` | `require.permission:materials.view,products.view` | `@if (\App\Support\Context::hasPermission('materials.view') \|\| \App\Support\Context::hasPermission('products.view') \|\| \App\Support\Context::isOwner())` |
| **Grup 4 (Pembelian)** | `purchase-orders.index` | `require.permission:purchasing.view` | `@if (\App\Support\Context::hasPermission('purchasing.view'))` |
| | `purchasing.bills.index` | `require.permission:purchasing.bills.view` | `@if (\App\Support\Context::hasPermission('purchasing.bills.view'))` |
| | `suppliers.index` | `require.permission:master_data.suppliers.view` | `@if (\App\Support\Context::hasPermission('master_data.suppliers.view'))` |
| | `purchase.returns.index` | `require.permission:purchasing.returns` | `@if (\App\Support\Context::hasPermission('purchasing.returns'))` |
| **Grup 5 (Pemasaran)** | `customers.index` | `require.permission:crm.customers.view` | `@if (\App\Support\Context::hasPermission('crm.customers.view'))` |
| | `crm.members.index` | `require.permission:crm.members.view` | `@if (\App\Support\Context::hasPermission('crm.members.view'))` |
| | `crm.vouchers.index` | `require.permission:crm.vouchers.view` | `@if (\App\Support\Context::hasPermission('crm.vouchers.view'))` |
| | `storefront.orders.index` | `require.permission:storefront.orders.view` | `@if (\App\Support\Context::hasPermission('storefront.orders.view'))` |
| | `landing-page.edit` | `require.permission:cms.manage,storefront.popup.manage` | `@if (\App\Support\Context::hasPermission('cms.manage') \|\| \App\Support\Context::isOwner())` |
| | `landing-page.popup.edit` | `require.permission:storefront.popup.manage` | `@if (\App\Support\Context::hasPermission('storefront.popup.manage') \|\| \App\Support\Context::isOwner())` |
| | `storefront.reservations.index` | `require.permission:storefront.reservations.manage` | `@if ($sidebarShowReservation && (\App\Support\Context::hasPermission('storefront.reservations.manage') \|\| \App\Support\Context::isOwner()))` |
| | `storefront.shipping.index` | `require.permission:storefront.shipping.manage` | `@if ($sidebarShowShipping && (\App\Support\Context::hasPermission('storefront.shipping.manage') \|\| \App\Support\Context::isOwner()))` |
| | `storefront.settings.index` | `require.permission:storefront.manage` | `@if (\App\Support\Context::hasPermission('storefront.manage') \|\| \App\Support\Context::isOwner())` |
| | `whatsapp.index` | `require.permission:whatsapp.view` | `@if (\App\Support\Context::hasPermission('whatsapp.view'))` |
| | `whatsapp.broadcast.index` | `require.permission:whatsapp.manage` | `@if (\App\Support\Context::hasPermission('whatsapp.manage'))` |
| | `whatsapp.logs.index` | `require.permission:whatsapp.view` | `@if (\App\Support\Context::hasPermission('whatsapp.view'))` |
| | `social-media.index` | `require.permission:social_media.view` | `@if (\App\Support\Context::hasPermission('social_media.view'))` |
| | `social-media.posts.index` | `require.permission:social_media.view` | `@if (\App\Support\Context::hasPermission('social_media.view'))` |
| | `social-media.calendar` | `require.permission:social_media.view` | `@if (\App\Support\Context::hasPermission('social_media.view'))` |
| | `social-media.inbox.index` | `require.permission:social_media.view` | `@if (\App\Support\Context::hasPermission('social_media.view'))` |
| **Grup 6 (Keuangan)** | `finance.cash-bank.index` | `require.permission:finance.cash_bank` | `@if (\App\Support\Context::hasPermission('finance.cash_bank'))` |
| | `finance.expenses.index` | `require.permission:expenses.view` | `@if (\App\Support\Context::hasPermission('expenses.view'))` |
| | `finance.receivables` | `require.permission:finance.receivables` | `@if (\App\Support\Context::hasPermission('finance.receivables'))` |
| | `finance.payables` | `require.permission:finance.payables` | `@if (\App\Support\Context::hasPermission('finance.payables'))` |
| | `finance.settlements.index` | `require.permission:finance.cash_bank` | `@if (\App\Support\Context::hasPermission('finance.cash_bank'))` |
| | `finance.coa.index` | `require.permission:finance.coa.view` | `@if (\App\Support\Context::hasPermission('finance.coa.view'))` |
| | `finance.journals.index` | `require.permission:finance.journals.view` | `@if (\App\Support\Context::hasPermission('finance.journals.view'))` |
| | `finance.general-ledger` | `require.permission:finance.general_ledger.view` | `@if (\App\Support\Context::hasPermission('finance.general_ledger.view'))` |
| | `finance.balance-sheet` | `require.permission:finance.balance_sheet.view` | `@if (\App\Support\Context::hasPermission('finance.balance_sheet.view'))` |
| | `finance.trial-balance` | `require.permission:finance.trial_balance.view` | `@if (\App\Support\Context::hasPermission('finance.trial_balance.view'))` |
| | `finance.reconciliations.index` | `require.permission:finance.reconcile` | `@if (\App\Support\Context::hasPermission('finance.reconcile'))` |
| | `calculator.index` | `require.permission:costing.calculator` | `@if (\App\Support\Context::hasPermission('costing.calculator'))` |
| | `simulator.index` | `require.permission:costing.calculator` | `@if (\App\Support\Context::hasPermission('costing.calculator'))` |
| | `labor-machines.index` | `require.permission:labor_machines.view` | `@if (\App\Support\Context::hasPermission('labor_machines.view'))` |
| | `profitability.index` | `require.permission:costing.view_margin` | `@if (\App\Support\Context::hasPermission('costing.view_margin'))` |
| | `hrm.index` | `require.permission:users.manage` | `@if (\App\Support\Context::hasPermission('users.manage'))` |
| | `hrm.payrolls.index` | `require.permission:users.manage` | `@if (\App\Support\Context::hasPermission('users.manage'))` |
| **Grup 7 (Laporan)** | `reports.index` | `require.permission:reports.view` | `@if (\App\Support\Context::hasPermission('reports.view') \|\| \App\Support\Context::isOwner())` |
| | `analytics.index` | `require.permission:reports.view` | `@if (\App\Support\Context::hasPermission('reports.view') \|\| \App\Support\Context::isOwner())` |
| | `pos.reports.index` | `require.permission:pos.reports` | `@if (\App\Support\Context::hasPermission('pos.reports'))` |
| | `tax.index` | `require.permission:reports.view` | `@if (\App\Support\Context::hasPermission('reports.view') \|\| \App\Support\Context::isOwner() \|\| $canAccessFinance)` |
| **Grup 8 (Pengaturan)** | `profile.edit` | Bebas (Seluruh Pengguna) | Tanpa Guard (Akses Profil Mandiri) |
| | `settings.index` | `require.permission:settings.view` | `@if ($canAccessSettings)` |
| | `approval-rules.index` | `require.permission:approvals.manage` | `@if (\App\Support\Context::hasPermission('approvals.manage') \|\| \App\Support\Context::isOwner())` |
| | `settings.audit-logs.index` | `require.permission:audit_logs.view` | `@if ($canAccessAuditLogs)` *(Menampilkan badge $recentHighRiskCount)* |
| | `roles.index` | `require.permission:roles.view` | `@if ($canAccessRoles)` |
| | `billing.limits` | `require.permission:billing.view` | `@if ($canAccessBilling)` |
| | `feedback.bugs.index` | `require.role:owner` | `@if (\App\Support\Context::isOwner())` |

---

## 4. Spesifikasi Remediasi Arsitektur (Architectural Solutions)

### 4.1 Isolasi Status Aktif Deterministik (*Zero Active Collision*)
Menu `settings.index` pada baris 2311 menggunakan kondisi eksklusi eksplisit terhadap sub-modul anak:
```blade
{{ (request()->routeIs('settings.*') && !request()->routeIs('settings.audit-logs.*') && !request()->routeIs('settings.approval-rules.*') && !request()->routeIs('settings.roles.*') && !request()->routeIs('settings.pos.printers.*')) ? 'aria-current="page"' : '' }}
```
**Efek:** Memastikan bahwa membuka halaman Audit Log, Rules MAR, atau Roles tidak akan pernah lagi mengaktifkan highlight biru pada "Pengaturan Usaha & Cabang".

### 4.2 Unifikasi Navigasi Audit Log & Indikator Risiko Tinggi (Opsi B)
1. **Pembersihan Ringkasan (Grup 1):** Tautan `settings.audit-logs.index?risk=high` ("Peringatan Audit") dihapus total dari Overview. Bagian Overview hanya menampilkan `approvals.inbox` jika ada persetujuan transaksi yang tertunda.
2. **Penyematan Badge Risiko:** Pada menu `Jejak Audit & Anti-Fraud` (Grup 8), disematkan badge pill merah dinamis jika `$recentHighRiskCount > 0`:
   ```blade
   @if (isset($recentHighRiskCount) && $recentHighRiskCount > 0)
       <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ request()->routeIs('settings.audit-logs.*') || request()->routeIs('audit-logs.*') ? 'bg-white/20 text-white border-white/30' : 'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30' }} border shrink-0" title="{{ $recentHighRiskCount }} Aktivitas Risiko Tinggi Terdeteksi">
           {{ $recentHighRiskCount }}
       </span>
   @endif
   ```

---

## 5. Rencana Pelaksanaan & Verifikasi (Verification Framework)

1. **Pengujian Deteksi Tabrakan Status Aktif:**
   Eksekusi script `scratch/detect_active_collisions.py` untuk mensimulasikan seluruh 71 rute terhadap logika active state. Target: **0 Collisions**.
2. **Pengujian Paritas Tautan Antar-Mode:**
   Eksekusi script `scratch/inspect_nav_duplicates.py`. Target: **71 rute Expanded = 71 rute Flyout**.
3. **Pengujian Bebas Error Sintaks:**
   Eksekusi `php -l resources/views/layouts/partials/sidebar.blade.php`. Target: **Pass**.
4. **Dokumentasi 3-Layer:**
   - Layer 1: Catat di `docs/AiWorkHistory.md` ([WORK-2026-09-28-207]).
   - Layer 2: Sinkronkan ke `docs/system/permissions/permission-matrix.md`.
   - Layer 3: Rangkum dalam `docs/SYSTEM_GUIDE.md` (Seksi 4.20).
