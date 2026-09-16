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
* [`docs/system/modules/system-diagnostics.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/system-diagnostics.md) - Pemantauan Error Log & Diagnostik Sistem Real-Time (SplFileObject Streaming, IDOR Shield, & Apple Bento UI). `[COMPLETE]`

### 3. Alur Kerja End-to-End (Workflows)
* [`docs/system/workflows/pos-sales-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/pos-sales-flow.md) - Alur Lengkap Penjualan Kasir ➔ Potong Stok BOM ➔ Kas Ledger ➔ Auto-Journal ➔ Nota WhatsApp. `[COMPLETE]`
* [`docs/system/workflows/purchasing-goods-receipt-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/purchasing-goods-receipt-flow.md) - Alur Pengadaan PO ➔ Penerimaan Fisik Barang (GR) ➔ Update Stok & HPP ➔ Tagihan Vendor (AP) ➔ Jurnal Akuntansi. `[COMPLETE]`
* [`docs/system/workflows/customer-storefront-flow.md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/customer-storefront-flow.md) - Alur Pembelian Pelanggan ➔ Gated Checkout ➔ Upload Bukti Bayar ➔ Konfirmasi Merchant ➔ Pelacakan Pesanan. `[VERIFIED]`

### 4. Aturan Bisnis (Business Rules)
* [`docs/system/business-rules/finance-rules.md`](file:///c:/laragon/www/cooca_core/docs/system/business-rules/finance-rules.md) - Integritas Finansial, Ketetapan Transaksi Final (Immutability), & Keseimbangan Jurnal. `[COMPLETE]`
* [`docs/system/business-rules/inventory-rules.md`](file:///c:/laragon/www/cooca_core/docs/system/business-rules/inventory-rules.md) - Aturan Pengurangan Bahan Baku, Kebijakan Stok Minus, & Valuasi Biaya Rata-Rata. `[COMPLETE]`
* [`docs/system/business-rules/security-rules.md`](file:///c:/laragon/www/cooca_core/docs/system/business-rules/security-rules.md) - Scoping Isolasi Multi-Tenant, Proteksi IDOR Pelanggan, Otorisasi PIN Kasir, & Rate Limiting. `[COMPLETE]`

### 5. Hak Akses & Peran (Permissions)
* [`docs/system/permissions/permission-matrix.md`](file:///c:/laragon/www/cooca_core/docs/system/permissions/permission-matrix.md) - Matriks Wewenang Lintas Peran: Superadmin, Business Owner, Manajer Toko, Kasir, Staf Dapur/Gudang, Pelanggan, & Otomasi Sistem. `[COMPLETE]`

### 6. Arsitektur Teknis (Architecture)
* [`docs/system/architecture/multi-tenancy.md`](file:///c:/laragon/www/cooca_core/docs/system/architecture/multi-tenancy.md) - Isolasi Basis Data Multi-Tenant, Context Resolver (`Context::requireBusiness()`), dan Tenant Lifecycle. `[COMPLETE]`
* [`docs/system/architecture/ui-ux-design-system.md`](file:///c:/laragon/www/cooca_core/docs/system/architecture/ui-ux-design-system.md) - Desain Sistem Bento Apple HIG v2.0, Keseragaman Konsep Mobile/Tablet, Floating Bottom Navbar, Pop-Up First Index, & Inline Quick-Add. `[COMPLETE]`

---

## 📊 Matriks Status Dokumentasi Sistem (Documentation Maturity Status)

| Modul / Domain | Status | Referensi Source Code / Migrasi | Terakhir Diverifikasi |
| :--- | :---: | :--- | :---: |
| **Costing & HPP Engine** | `COMPLETE` | `app/Domain/Costing/`, `app/Domain/Calculation/`, Migrasi `000014`-`000029` | 2026-09-15 |
| **Point of Sale (POS)** | `COMPLETE` | `app/Domain/Pos/`, Migrasi `000040`-`000045`, `000001`-`000004` (2026-09-10) | 2026-09-15 |
| **Inventory & Materials** | `COMPLETE` | `app/Domain/Inventory/`, `app/Domain/Material/`, Migrasi `000008`-`000013`, `000042` | 2026-09-15 |
| **Finance & Accounting** | `COMPLETE` | `app/Domain/Finance/`, `app/Domain/Accounting/`, Migrasi `000044`, `000080`-`000081` | 2026-09-15 |
| **Customer & CRM Loyalty** | `COMPLETE` | `app/Domain/Customer/`, `app/Domain/Crm/`, `routes/web.php`, Views `customers/` & `crm/` | 2026-09-15 |
| **Commerce & Storefront** | `VERIFIED` | `app/Domain/Commerce/`, Migrasi `2026_09_15_000001`-`063500`, `routes/customer.php` | 2026-09-15 |
| **SaaS Billing & Quotas** | `VERIFIED` | `app/Domain/Billing/`, Migrasi `000047`-`000048`, `000002` (2026-09-02) | 2026-09-15 |
| **WhatsApp Gateway** | `COMPLETE` | `app/Domain/WhatsApp/`, `wa-server/`, `AdminWhatsAppController`, Views `admin/whatsapp/` | 2026-09-15 |
| **Multi-Tenant & Security** | `COMPLETE` | `app/Support/Context.php`, Middleware, Migrasi `000001`-`000004` | 2026-09-15 |
| **UI/UX Bento Apple HIG** | `COMPLETE` | `docs/prompt.md`, `AGENTS.md`, `layouts/app.blade.php`, `layouts/admin.blade.php` | 2026-09-16 |
| **System Diagnostics & Logs** | `COMPLETE` | `AdminErrorLogController`, `resources/views/admin/error-logs/`, `AdminErrorLogTest` | 2026-09-16 |

*Keterangan Status:*
- `DISCOVERED`: Fitur/komponen ditemukan di kode tetapi belum dianalisis tuntas.
- `PARTIAL`: Dokumentasi parsial dan masih ada gap workflow.
- `DOCUMENTED`: Struktur terdokumentasi lengkap.
- `VERIFIED`: Telah diverifikasi terhadap logika kode aktual dan database.
- `COMPLETE`: Telah diverifikasi penuh, bebas kontradiksi, dan tervalidasi dengan pengujian otomatis.
- `NEEDS_REVIEW`: Ada perubahan kode terbaru yang memerlukan peninjauan ulang dokumentasi.
- `OUTDATED`: Implementasi telah berubah dan dokumentasi harus segera diperbarui.
