# Cooca System Knowledge Base (Layer 2)

> **Status:** CURRENT STATE (Kondisi Sistem Berjalan)  
> **Mandat:** Merepresentasikan bagaimana sistem Cooca bekerja **saat ini** secara faktual, mendalam, dan terverifikasi terhadap source code, skema basis data, dan pengujian.  
> **Hierarki Kebenaran:** Aktual Implementasi > Database Schema > Tests > Dokumentasi Lama > AiWorkHistory > Asumsi (DILARANG).

---

## 🗺️ Peta Navigasi Pengetahuan Sistem (Knowledge Map)

### 1. Ikhtisar Sistem (Overview)
* [`docs/system/overview/system-overview.md`](file:///c:/laragon/www/cooca_core/docs/system/overview/system-overview.md) - Arsitektur Platform, Pilar Teknis, 33 Domain Packages DDD, dan 4 Kuadran Aktor (Superadmin, Owner/POS, Customer, Otomasi). `[VERIFIED]`

### 2. Modul Sistem (Modules)
* [`docs/system/modules/costing.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/costing.md) - Mesin Kalkulasi Biaya HPP Multi-Tier, Resep BOM, Biaya Mesin/Tenaga Kerja, Skenario "What-If", & BEP. `[COMPLETE]`
* [`docs/system/modules/pos.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/pos.md) - Terminal Kasir POS, Manajemen Shift, Sesi Meja FnB, Thermal Printer ESC/POS, & Otorisasi Supervisor. `[COMPLETE]`
* [`docs/system/modules/inventory.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/inventory.md) - Manajemen Bahan Baku vs Produk Jadi, Konversi Multi-Satuan, Goods Receipt (GR), & Auto-Deduction BOM. `[COMPLETE]`
* [`docs/system/modules/finance.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/finance.md) - Akun Kas & Bank, AutoJournalService, Pembukuan Berpasangan (Debit=Kredit), AP/AR, & Rekonsiliasi. `[COMPLETE]`
* [`docs/system/modules/commerce.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/commerce.md) - Toko Online Storefront, Keranjang Belanja Multi-Tenant, Batch Pesanan Terjadwal, Reservasi, & Anti-IDOR Shield. `[VERIFIED]`
* [`docs/system/modules/crm.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/crm.md) - Pusat Pelanggan & Loyalitas Terpadu (Apple HIG Bento UI), Member Tiers, Poin Belanja, Kupon Voucher, & Pelunasan Piutang. `[COMPLETE]`
* [`docs/system/modules/saas-billing.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/saas-billing.md) - Paket Langganan, Entitlement Fitur, Batasan Kuota Bulanan, & Pembayaran Subscription. `[VERIFIED]`
* [`docs/system/modules/whatsapp.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/whatsapp.md) - WhatsApp Gateway Platform, Admin Center Bento UI, Pengingat Langganan H-7 s/d Hari H, & Broadcast Owner. `[COMPLETE]`
* [`docs/system/modules/social-media.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/social-media.md) - Modul Media Sosial & Integrasi Platform Meta, TikTok & LinkedIn, Provider Matrix, Zero Meta Ads Policy. `[COMPLETE]`
* [`docs/system/modules/marketplace.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/marketplace.md) - Modul Integrasi Marketplace Omnichannel (Shopee, TikTok Shop, Tokopedia), Multi-Harga, Safety Buffer & Feed Pesanan. `[COMPLETE]`
* [`docs/system/modules/system-diagnostics.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/system-diagnostics.md) - Pemantauan Error Log & Diagnostik Sistem Real-Time (SplFileObject Streaming, IDOR Shield, & Apple Bento UI). `[COMPLETE]`

### 3. Alur Kerja End-to-End (Workflows)
* [`docs/system/workflows/pos-sales-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-sales-flow.md) - Alur Lengkap Penjualan Kasir ➔ Potong Stok BOM ➔ Kas Ledger ➔ Auto-Journal ➔ Nota WhatsApp. `[COMPLETE]`
* [`docs/system/workflows/pos-channel-pricing-and-delivery-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-channel-pricing-and-delivery-flow.md) - Alur Multi-Harga Saluran POS & Online Delivery (Dine In, Takeaway, GoFood, GrabFood, ShopeeFood) ➔ Nomor Order Ref Eksternal ➔ Lencana KDS Dapur ➔ Struk Thermal ESC/POS. `[COMPLETE]`
* [`docs/system/workflows/product-bundling-and-combo-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/product-bundling-and-combo-flow.md) - Alur Paket Kombo & Bundling Produk ➔ Konfigurasi Item Anak ➔ Aturan Stok Bottleneck ➔ Akumulasi HPP ➔ Pemotongan & Pengembalian Rekursif. `[COMPLETE]`
* [`docs/system/workflows/staff-portal-and-attendance-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/staff-portal-and-attendance-flow.md) - Alur Portal Karyawan & Presensi Mandiri ➔ Pengalihan Aman Dashboard ➔ Penyaringan Ketat Modul Cepat RBAC ➔ Bento Empty-State. `[COMPLETE]`
* [`docs/system/workflows/purchasing-goods-receipt-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/purchasing-goods-receipt-flow.md) - Alur Pengadaan PO ➔ Penerimaan Fisik Barang (GR) ➔ Update Stok & HPP ➔ Tagihan Vendor (AP) ➔ Jurnal Akuntansi. `[COMPLETE]`
* [`docs/system/workflows/customer-storefront-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/customer-storefront-flow.md) - Alur Pembelian Pelanggan ➔ Gated Checkout ➔ Upload Bukti Bayar ➔ Konfirmasi Merchant ➔ Pelacakan Pesanan. `[VERIFIED]`
* [`docs/system/workflows/branch-assortment-and-pricing-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/branch-assortment-and-pricing-flow.md) - Alur Ketersediaan Produk & Multi-Harga Cabang (Rest Area / Bandara vs Kota) ➔ Pemisahan Toggle Mandiri ➔ Penyaringan Etalase POS ➔ Hierarki Resolusi Harga Kasir. `[COMPLETE]`
* [`docs/system/workflows/multi-hierarchy-branch-and-warehouse-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/multi-hierarchy-branch-and-warehouse-flow.md) - Alur Hubungan Cabang, Outlet, dan Gudang Multi-Hierarki ➔ Model Self-Referencing `parent_id` ➔ Gudang Pusat vs Sub-Gudang Cabang ➔ Agregasi Stok Efektif POS Terminal ➔ Auto-Routing Pemotongan Stok. `[COMPLETE]`
* [`docs/system/workflows/warehouse-logistics-and-multi-branch-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/warehouse-logistics-and-multi-branch-flow.md) - Alur Manajemen Cabang, Outlet & Gudang Logistik Terpadu (Warehouse Management Hub) ➔ Registrasi Geocoding GPS ➔ Origin Penjemputan Storefront ➔ Kartu Stok & Valuasi HPP ➔ Penyesuaian Stok Cepat & Audit Fraud. `[COMPLETE]`
* [`docs/system/workflows/whatsapp-gateway-and-broadcast-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/whatsapp-gateway-and-broadcast-flow.md) - Alur Kerja WhatsApp Gateway, Struk Kasir POS & Broadcast Promosi ➔ Onboarding Meta WABA ➔ Simulator WYSIWYG ➔ Queue Worker Asinkron ➔ Do's & Don'ts 20 Sektor Industri. `[COMPLETE]`
* [`docs/system/workflows/social-media-omnichannel-and-content-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/social-media-omnichannel-and-content-flow.md) - Alur Kerja Media Sosial Omnichannel, Penjadwalan Konten & Moderasi ➔ Meta/TikTok/LinkedIn 1-Click ➔ Unified Composer ➔ Hashtag Guard ➔ Do's & Don'ts 20 Sektor Industri. `[COMPLETE]`
* [`docs/system/workflows/marketplace-omnichannel-and-sync-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/marketplace-omnichannel-and-sync-flow.md) - Alur Kerja Marketplace Omnichannel, Sinkronisasi Multi-Harga, Stok & Pesanan ➔ Shopee/TikTok/Tokopedia ➔ Safety Buffer ➔ Anti-Fraud Webhook ➔ Do's & Don'ts 20 Sektor Industri. `[COMPLETE]`

### 4. Aturan Bisnis (Business Rules)
* [`docs/system/business-rules/finance-rules.md`](file:///c:/laragon/www/cooca_core/docs/system/business-rules/finance-rules.md) - Integritas Finansial, Ketetapan Transaksi Final (Immutability), & Keseimbangan Jurnal. `[COMPLETE]`
* [`docs/system/business-rules/inventory-rules.md`](file:///c:/laragon/www/cooca_core/docs/system/business-rules/inventory-rules.md) - Aturan Pengurangan Bahan Baku, Kebijakan Stok Minus, Bottleneck Stok Paket Kombo, & Pemotongan Rekursif. `[COMPLETE]`
* [`docs/system/business-rules/security-rules.md`](file:///c:/laragon/www/cooca_core/docs/system/business-rules/security-rules.md) - Scoping Multi-Tenant, Proteksi IDOR, PIN Supervisor, Proteksi Akun, Staff Portal RBAC Guard, & Proteksi Circular Bundle. `[COMPLETE]`
* [`docs/system/business-rules/multi-branch-pricing-and-inventory-cases.md`](file:///c:/laragon/www/cooca_core/docs/system/business-rules/multi-branch-pricing-and-inventory-cases.md) - Kompilasi Studi Kasus Bisnis: Paket Bundling, Multi-Harga Saluran Ojol, Portal Karyawan, Do's & Don'ts Lintas Industri, Hierarki Cabang-Gudang, & Diferensiasi Rest Area. `[COMPLETE]`

### 5. Hak Akses & Peran (Permissions)
* [`docs/system/permissions/permission-matrix.md`](file:///c:/laragon/www/cooca_core/docs/system/permissions/permission-matrix.md) - Matriks Wewenang Lintas Peran: Superadmin, Business Owner, Manajer Toko, Kasir, Staf Dapur/Gudang, Pelanggan, & Otomasi Sistem. `[COMPLETE]`

### 6. Arsitektur Teknis (Architecture)
* [`docs/system/architecture/multi-tenancy.md`](file:///c:/laragon/www/cooca_core/docs/system/architecture/multi-tenancy.md) - Isolasi Basis Data Multi-Tenant, Context Resolver (`Context::requireBusiness()`), dan Tenant Lifecycle. `[COMPLETE]`
* [`docs/system/architecture/ui-ux-design-system.md`](file:///c:/laragon/www/cooca_core/docs/system/architecture/ui-ux-design-system.md) - Desain Sistem Bento Apple HIG v2.0, Keseragaman Konsep Mobile/Tablet, Floating Bottom Navbar, Pop-Up First Index, & Inline Quick-Add. `[COMPLETE]`
* [`docs/prd/PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY.md`](file:///c:/laragon/www/cooca_core/docs/prd/PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY.md) - Remediasi Navigasi Sidebar, Invisible Hover Bridge (Anti-Flicker), Paritas Flyout 100%, & RBAC Zero-Error. `[COMPLETE]`

---

## 📊 Matriks Status Dokumentasi Sistem (Documentation Maturity Status)

| Modul / Domain | Status | Referensi Source Code / Migrasi | Terakhir Diverifikasi |
| :--- | :---: | :--- | :---: |
| **Costing & HPP Engine** | `COMPLETE` | `app/Domain/Costing/`, `app/Domain/Calculation/`, Migrasi `000014`-`000029` | 2026-09-26 |
| **Point of Sale (POS)** | `COMPLETE` | `app/Domain/Pos/`, Migrasi `000040`-`000045`, `2026_09_26_110000` (Channel Pricing) | 2026-09-27 |
| **Branch Assortment & Pricing** | `COMPLETE` | `branch_product_prices`, `ProductWebController`, `PosTerminalWebController`, Migrasi `2026_09_27_103000` | 2026-09-27 |
| **Multi-Hierarchy Branch & Warehouse** | `COMPLETE` | `locations.parent_id`, `Location`, `WarehouseWebController`, `StockService`, Migrasi `2026_09_27_123000` | 2026-09-27 |
| **Warehouse Management Hub** | `COMPLETE` | `resources/views/app/warehouse/`, `WarehouseWebController`, `InventoryWebController`, `Location` | 2026-09-27 |
| **Inventory & Materials** | `COMPLETE` | `app/Domain/Inventory/`, `ProductBundleItem`, Migrasi `2026_09_26_100000` (Bundling Engine) | 2026-09-26 |
| **Staff Portal & RBAC** | `COMPLETE` | `PortalWebController`, `portal/index.blade.php`, RBAC Permission Guard | 2026-09-26 |
| **Finance & Accounting** | `COMPLETE` | `app/Domain/Finance/`, `app/Domain/Accounting/`, Migrasi `000044`, `000080`-`000081` | 2026-09-15 |
| **Customer & CRM Loyalty** | `COMPLETE` | `app/Domain/Customer/`, `app/Domain/Crm/`, `routes/web.php`, Views `customers/` & `crm/` | 2026-09-15 |
| **Commerce & Storefront** | `VERIFIED` | `app/Domain/Commerce/`, Migrasi `2026_09_15_000001`-`063500`, `routes/customer.php` | 2026-09-15 |
| **SaaS Billing & Quotas** | `VERIFIED` | `app/Domain/Billing/`, Migrasi `000047`-`000048`, `000002` (2026-09-02) | 2026-09-15 |
| **WhatsApp Gateway & Broadcast** | `COMPLETE` | `app/Domain/WhatsApp/`, `WhatsAppWebController`, `WhatsAppBroadcastWebController`, `MetaWhatsAppOnboardingController`, Views `resources/views/app/whatsapp/` | 2026-09-27 |
| **Social Media Omnichannel Hub** | `COMPLETE` | `app/Domain/SocialMedia/`, `SocialMediaWebController`, Views `resources/views/app/social_media/` | 2026-09-28 |
| **Multi-Tenant & Security** | `COMPLETE` | `app/Support/Context.php`, Middleware, Migrasi `000001`-`000004` | 2026-09-26 |
| **Sidebar Navigation & Flyout System** | `COMPLETE` | `resources/views/layouts/partials/sidebar.blade.php`, `routes/owner.php`, PRD-14 | 2026-09-28 |
| **UI/UX Bento Apple HIG** | `COMPLETE` | `docs/prompt.md`, `AGENTS.md`, `layouts/app.blade.php`, `layouts/admin.blade.php` | 2026-09-26 |
| **System Diagnostics & Logs** | `COMPLETE` | `AdminErrorLogController`, `resources/views/admin/error-logs/`, `AdminErrorLogTest` | 2026-09-16 |

*Keterangan Status:*
- `DISCOVERED`: Fitur/komponen ditemukan di kode tetapi belum dianalisis tuntas.
- `PARTIAL`: Dokumentasi parsial dan masih ada gap workflow.
- `DOCUMENTED`: Struktur terdokumentasi lengkap.
- `VERIFIED`: Telah diverifikasi terhadap logika kode aktual dan database.
- `COMPLETE`: Telah diverifikasi penuh, bebas kontradiksi, dan tervalidasi dengan pengujian otomatis.
- `NEEDS_REVIEW`: Ada perubahan kode terbaru yang memerlukan peninjauan ulang dokumentasi.
- `OUTDATED`: Implementasi telah berubah dan dokumentasi harus segera diperbarui.

