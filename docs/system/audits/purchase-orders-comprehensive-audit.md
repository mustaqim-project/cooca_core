# Laporan Audit Komprehensif: Ekosistem Purchase Orders & Pengadaan Multi-Tenant COOCA

**Dokumen Standar Layer 2:** [`docs/system/audits/purchase-orders-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/purchase-orders-comprehensive-audit.md)  
**Target Modul:** [`resources/views/app/purchase-orders/`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders) (`index.blade.php`, `create.blade.php`, `show.blade.php`, `print.blade.php`) beserta Ekosistem Backend Terkait ([`PurchaseOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php), [`PurchaseOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php), [`GoodsReceiptWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Purchasing/GoodsReceiptWebController.php), Model, Rute & i18n)  
**Metodologi:** *Code-First Factuality Audit* (Memadukan 7 Skill Utama COOCA Secara Simultan Berbasis Baris Kode Riil Tanpa Asumsi)  
**Tanggal Audit:** 30 September 2026 | **Status:** `AUDIT COMPLETED — REMEDIATION IN PROGRESS`

---

## 📑 1. Eksekutif Ringkasan & Rekapitulasi Metrik

Audit mendalam berbasis fakta kode nyata (*Code-First Factuality*) pada ekosistem pengadaan barang dan pesanan pembelian (**Purchase Orders**) telah dilaksanakan. Evaluasi menelusuri 11 simpul eksekusi hulu-ke-hilir untuk mendeteksi celah keamanan siber, potensi fraud internal, inkonsistensi arsitektur antarmuka, bias industri, kegagalan ergonomi mobile, dan pelanggaran multi-bahasa.

### Rekapitulasi Metrik Temuan
- **Total Temuan Faktual:** **20 Temuan Terverifikasi** (6 Temuan Kritis P1, 10 Temuan Menengah P2, 4 Temuan Penyempurnaan P3).
- **Cakupan Pengujian:** Keamanan Multi-Tenant (IDOR & Tenant Isolation), Integritas Transaksional Finansial (*Three-Way Matching*), Ergonomi Sentuh Apple HIG, Kompatibilitas 20 Industri, dan Lokalisasi Bahasa (ID/EN).

### Distribusi Tingkat Keparahan (Severity) Lintas 7 Dimensi
| Dimensi Skill | 🔴 P1 (Kritis) | 🟡 P2 (Tinggi/Sedang) | 🟢 P3 (Penyempurnaan) | Total |
| :--- | :---: | :---: | :---: | :---: |
| 1. 🔄 System Workflow & 11 Simpul Eksekusi | 1 | 3 | 0 | 4 |
| 2. 🛡️ Security, Anti-Fraud & Three-Way Matching | 3 | 2 | 0 | 5 |
| 3. 🏢 Multi-Industry Compliance & Dynamic Auto-Hiding | 0 | 2 | 1 | 3 |
| 4. 🎨 UI Panel Consistency & Information Architecture | 0 | 1 | 1 | 2 |
| 5. 📱 Responsive UI/UX & Mobile-First Ergonomics | 0 | 2 | 1 | 3 |
| 6. ⚡ Bento Apple HIG v2.0 & Cooca Directives | 1 | 0 | 1 | 2 |
| 7. 🌐 Multi-Language (i18n & l10n Dwibahasa) | 1 | 0 | 0 | 1 |
| **TOTAL TEMUAN** | **6** | **10** | **4** | **20** |

---

## 📊 2. Tabel Temuan Laporan Audit Komprehensif (7 Kolom Mandatori)

| No | Dimensi Skill | Lokasi File & Baris Kode | Kode Temuan / Root Cause | Severity | Dampak Risiko | Solusi/Rekomendasi |
| :--- | :--- | :--- | :--- | :---: | :--- | :--- |
| **01** | 🛡️ Security & Fraud | [`PurchaseOrderWebController.php:120-135`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L120-L135) | **IDOR Multi-Tenant pada Validasi Request**: Rule validasi `exists:customers,id`, `exists:suppliers,id`, `exists:products,id`, `exists:materials,id` tidak menyertakan klausa `where('business_id', $businessId)`. | **P1** | Tenant A dapat menyuntikkan ID pelanggan/pemasok/bahan milik tenant B ke dalam dokumen PO-nya, memicu kebocoran data rahasia (*cross-tenant data leakage*). | Bungkus seluruh aturan relasi dengan `Rule::exists('table', 'id')->where('business_id', $businessId)`. |
| **02** | 🛡️ Security & Fraud | [`PurchaseOrderWebController.php:171,188,222,232,250`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L171-L250) | **Missing Explicit Tenant Isolation Shield**: Method `show()`, `confirm()`, `cancel()`, `generateInvoice()`, dan `print()` tidak memanggil guardrail `abort_unless($purchaseOrder->business_id === $business->id, 403);`. | **P1** | Jika global scope ter-bypass atau diakses via endpoint sekunder, dokumen transaksi rahasia antar-tenant dapat diakses, dikonfirmasi, dan dicetak oleh pihak tidak berwenang. | Pasang `abort_unless($purchaseOrder->business_id === $business->id, 403);` di baris pertama setiap action controller. |
| **03** | 🛡️ Security & Fraud | [`PurchaseOrderWebController.php:222-227`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L222-L227)<br>[`PurchaseOrderService.php:123-130`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php#L123-L130) | **Zero-Guardrail Cancellation & Missing Supervisor PIN**: Pembatalan PO (`cancel`) dapat dieksekusi sepihak tanpa otorisasi Supervisor PIN (Bcrypt) dan tanpa memeriksa apakah barang fisik sudah diterima di gudang (`goodsReceipts()->exists()`) atau sudah diterbitkan invoice (`invoices()->exists()`). | **P1** | **Potensi Fraud Penggelapan Fisik**: Staf gudang/kasir dapat membatalkan PO yang barang fisiknya sudah masuk gudang, menciptakan *phantom inventory*, merusak *Three-Way Matching*, dan meninggalkan *dangling approval request*. | Wajibkan verifikasi `Hash::check($pin, $business->pos_supervisor_pin)` untuk PO berstatus confirmed, blokir pembatalan jika memiliki relasi penerimaan barang/faktur, dan catat `AuditLog`. |
| **04** | 🔄 System Workflow | [`PurchaseOrderService.php:38-109`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php#L38-L109) | **Missing Database Transaction Atomicity**: Pembuatan header PO (`PurchaseOrder::create`) dan perulangan baris item (`PurchaseOrderItem::create`) tidak dibungkus dalam `DB::transaction(...)`. | **P1** | Jika pembuatan item ke-3 gagal karena database disconnect atau kegagalan relasi, header PO yatim (*orphan header without items*) tertinggal di basis data dengan nomor urut PO terpakai. | Bungkus seluruh alur pembuatan header, baris item, dan kalkulasi total ke dalam satu blok closure `DB::transaction(...)`. |
| **05** | ⚡ Cooca Directive | [`PurchaseOrderWebController.php:143-147`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L143-L147) | **Quota Deduction Pre-Execution Risk (Data Punishment)**: Kuota bulanan dipotong (`incrementMonthlyUsage`) *sebelum* `createPurchaseOrder` berhasil tersimpan di database. | **P1** | Jika terjadi kegagalan database saat pembuatan PO, kuota tenant Free tetap berkurang permanen padahal dokumen gagal terbit (*unjust quota punishment*). | Pindahkan pemanggilan `incrementMonthlyUsage` ke blok akhir setelah transaksi penyimpanan berhasil 100%. |
| **06** | 🌐 Multi-Language | Seluruh berkas Blade (`index`, `create`, `show`, `print`) | **100% Hardcoded Indonesian Strings & Static Currency**: 140+ string teks bahasa Indonesia mentah tertanam langsung tanpa helper `{{ __('purchasing.key') }}` serta format mata uang JavaScript `new Intl.NumberFormat('id-ID')` statis. | **P1** | Fitur pengalih bahasa (ID/EN) gagal menerjemahkan modul PO; format mata uang asing tidak adaptif terhadap konfigurasi bisnis merchant mancanegara. | Ekstraksi seluruh teks ke `lang/id/purchasing.php` dan `lang/en/purchasing.php`, serta sediakan kamus JS `window.COOCA_I18N.purchasing`. |
| **07** | 🏢 Multi-Industry | [`create.blade.php:9, 127-140`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L9-L140)<br>[`index.blade.php:111-118`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L111-L118) | **Missing Dynamic Module Gating**: Tipe "PO Pelanggan (B2B Sales)" muncul secara default dan bebas dipilih pada tenant F&B / Ritel yang modul `customer_po` / `b2b_sales`-nya nonaktif (`fnb_resto`, `fnb_cafe`, `retail_reseller`). | **P2** | Mengacaukan model mental operasional kasir/owner resto/kafe yang tidak memiliki kanal B2B; mencemari KPI agregat antara Omzet Penjualan dan Biaya Pembelian. | Terapkan Dynamic Context-Aware Auto-Hiding: sembunyikan toggle/filter PO Pelanggan via `@if($business->isModuleEnabled('customer_po'))` dan kunci default ke `supplier`. |
| **08** | 🎨 UI Panel Consistency | [`index.blade.php:113, 121`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L113-L121) | **Violation of Tab Architecture & State Loss**: Filter tipe dan status menggunakan HTML native `<select onchange="this.form.submit()">` yang memicu full reload halaman, bukan Segmented Control Tabs Bento Apple HIG dengan sinkronisasi URL deep-linking (`?status=...`). | **P2** | UI terasa kaku, tidak selaras dengan macOS Sonoma Source List standar Cooca, dan state tab ter-reset saat browser di-refresh. | Ganti dengan Segmented Control Tabs Bento Apple HIG dengan watcher Alpine.js yang otomatis memperbarui URL search query (`window.history.replaceState`). |
| **09** | 📱 Responsive UI/UX | [`create.blade.php:211-277`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L211-L277)<br>[`show.blade.php:181-224`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/show.blade.php#L181-L224) | **Desktop Table Trap & Horizontal Scroll pada Layar Ponsel**: Rincian baris item formulir dan detail dokumen hanya disajikan dalam tag `<table>` 6 kolom tanpa Card View responsif di layar ponsel (360px–390px). | **P2** | Pengguna smartphone terpaksa melakukan scroll horizontal berulang kali untuk memasukkan item, satuan, kuantitas, dan harga satuan. | Sediakan Card List View khusus mobile (`sm:hidden`) dengan layout 2 kolom padat dan sembunyikan tabel desktop di layar kecil (`hidden sm:block`). |
| **10** | 📱 Responsive UI/UX | [`create.blade.php:148, 172, 259, 263`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L148-L263) | **iOS Safari Auto-Zoom Trap**: Input form menggunakan kelas tipografi `text-[13px]` dan `text-[12px]`. Font di bawah 16px memicu auto-zoom paksa pada peramban WebKit/Safari iOS iPhone. | **P2** | Tampilan antarmuka di iPhone tiba-tiba membesar (*zoomed-in*), memotong tombol aksi dan merusak tata letak bento. | Ubah seluruh kelas font input dan select menjadi `text-[16px] sm:text-[13px]`. |
| **11** | 📱 Responsive UI/UX | [`index.blade.php:220, 226, 248`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L220-L248)<br>[`create.blade.php:268`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L268) | **Substandard Tap Target (< 44px)**: Tombol cetak, download PDF, hapus draft, dan hapus baris item berukuran `h-7 w-7` (28×28px). | **P2** | Sangat sulit ditekan dengan jari jempol pada layar sentuh, rentan salah sentuh (*fat-finger errors*) bagi pengguna usia 40–65 tahun. | Ubah tombol aksi sentuh menjadi minimum `min-h-[44px] min-w-[44px]` dengan padding sentuh ergonomis. |
| **12** | 🛡️ Security & Fraud | [`create.blade.php:334`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L334)<br>[`index.blade.php:419`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L419) | **Missing Double-Submit Protection**: Tombol "Terbitkan Purchase Order" dan "Beli & Tambah ke Stok" tidak memiliki proteksi penonaktifan tombol saat pengiriman (`:disabled="submitting"`). | **P2** | Pengguna yang mengklik tombol dua kali secara cepat saat koneksi lambat akan menerbitkan 2 nomor PO ganda dan mengurangi kuota transaksi 2 kali. | Pasang state `:disabled="isSubmitting"` dengan spinner status pada tombol kirim form. |
| **13** | 🔄 System Workflow | [`index.blade.php:365-426`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L365-L426)<br>[`GoodsReceiptWebController.php:124-136`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Purchasing/GoodsReceiptWebController.php#L124-L136) | **Incomplete Instant Stock-In Scope & Accounting Blindspot**: Fitur "1-Klik Beli ke Stok" hanya menerima `product_id` (tidak bisa memilih bahan baku `material_id`), serta menambah stok fisik tanpa mencatat pengeluaran kas (`CashLedger`) atau jurnal akuntansi pembelian tunai. | **P2** | Bahan mentah pasar (telur, tepung, sparepart bengkel) tidak bisa diinput cepat; neraca keuangan menjadi tidak seimbang (*inventory asset up, cash ledger not deducted*). | Tambahkan opsi pemilihan `material_id` pada modal, dan bukukan kas keluar pada akun kas kecil laci/bank via `CashLedgerService`. |
| **14** | 🔄 System Workflow | [`PurchaseOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php)<br>[`PurchaseOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php) | **Zero Tri-Channel Notification Dispatch**: Tidak ada emisi notifikasi sistem (In-App Bell, Email HTML, dan WhatsApp Meta API) saat PO dibuat, butuh persetujuan, dikonfirmasi, atau dibatalkan. | **P2** | Pemilik bisnis dan staf logistik terlambat merespons pengadaan bahan baku, proses pengadaan terhambat tanpa jejak komunikasi otomatis. | Dispatch event `PurchaseOrderCreatedEvent` dan `PurchaseOrderConfirmedEvent` untuk mentrigger notifikasi tri-channel. |
| **15** | 🛡️ Security & Fraud | [`PurchaseOrderWebController.php:154`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L154) | **Missing Comprehensive Audit Trail Logging**: Perubahan status PO (`confirmed`, `cancelled`, `invoice_generated`, `deleted`) tidak tercatat dalam tabel `audit_logs`. | **P2** | Sulit membuktikan pertanggungjawaban hukum (*non-repudiation*) jika terjadi manipulasi data pengadaan atau klaim fiktif antar staf gudang dan kasir. | Catat setiap mutasi status dokumen ke `App\Models\AuditLog` lengkap dengan user ID, IP address, action, dan alasan perubahan. |
| **16** | 🏢 Multi-Industry | [`create.blade.php:180-205`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L180-L205) | **Rigid Single-Entity Item Selection**: Form PO tidak menyediakan tab pemisah antara "Produk Jadi / Barang Dagangan" dan "Bahan Baku / Komponen", membingungkan industri manufaktur dan kuliner. | **P2** | Staf dapur resto sulit membedakan antara bahan mentah resep (BOM) dengan produk retail siap jual di display kasir. | Sediakan toggle pemilihan jenis entitas (Produk vs Bahan Baku) secara intuitif dengan katalog harga beli terakhir (*last purchase cost*). |
| **17** | 🔄 System Workflow | [`PurchaseOrderService.php:126`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php#L126) | **Dangling Approval Requests on Cancelled PO**: Pembatalan PO tidak mengupdate status approval request terkait, membiarkan status tetap `pending` di dashboard manajer. | **P2** | Manajer/Approver melihat antrean persetujuan fiktif atas transaksi yang sebenarnya sudah dibatalkan oleh pembuatnya. | Update status approval request terkait menjadi `cancelled` saat PO dibatalkan. |
| **18** | 🎨 UI Panel Consistency | [`show.blade.php:1-35`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/show.blade.php#L1-L35) | **Inconsistent Page Header Structure**: Halaman detail PO tidak mengikuti format standar 3-Baris Cooca (Baris 1: Breadcrumb + Tag/Status, Baris 2: Judul Utama + Aksi Primer, Baris 3: Deskripsi Kontekstual). | **P3** | Pengalaman pengguna terfragmentasi dibandingkan modul Master Data, Produk, dan Kasir POS yang sudah terstandarisasi. | Tata ulang header menjadi struktur 3-baris terpadu menggunakan token Apple HIG. |
| **19** | ⚡ Cooca Directive | [`print.blade.php:2`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/print.blade.php#L2) | **Static Indonesian Document Metadata**: Berkas cetak PO meng-hardcode atribut `<html lang="id">` dan format tanggal statis tanpa lokalisasi peramban. | **P3** | Dokumen cetak yang dikirimkan ke pemasok internasional mengalami inkonsistensi representasi bahasa dan tanggal. | Ganti dengan `<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">` dan format tanggal adaptif. |
| **20** | 🏢 Multi-Industry | [`index.blade.php:32`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L32) | **Generic Stock-In Modal Nomenclature**: Istilah pada tombol aksi cepat "Beli ke Stok" tidak mencerminkan terminology industri jasa/bengkel ("Beli Sparepart Cepat") atau F&B ("Belanja Pasar Subuh"). | **P3** | Penurunan adopsi fitur karena UMKM merasa aplikasi terlalu kaku dan tidak mencerminkan rutinitas harian bisnis mereka. | Sesuaikan label dinamis berdasarkan jenis industri bisnis (`$business->industry`). |

---

## 🔄 3. Pemetaan 11 Simpul Eksekusi Hulu-ke-Hilir

```mermaid
sequenceDiagram
    autonumber
    actor Actor as Pengguna (Staff / Kasir / Owner)
    participant UI as Blade View (Bento HIG)
    participant Alpine as Alpine.js Reactive Engine
    participant Route as Route & Middleware (Auth, Tenant, Perms, Entitlement)
    participant Ctrl as PurchaseOrderWebController
    participant Req as Request Validation (Tenant-Scoped)
    participant Svc as PurchaseOrderService
    participant Approval as ApprovalWorkflowService
    participant Model as Eloquent Model & DB Schema
    participant FinStock as Stock & Auto-Journal (via GRN/Invoice)
    participant Notif as Tri-Channel Notification (In-App, WA, Email)
    participant Guard as AuditLog & Guardrails (Bcrypt PIN)

    Actor->>UI: Akses Menu /purchase-orders/create
    UI->>Alpine: Inisialisasi Katalog Produk/Bahan & Watcher Tipe PO
    Actor->>UI: Input Baris Item, Diskon, Pajak & Klik "Terbitkan PO"
    UI->>Route: POST /purchase-orders (CSRF + Entitlement Check)
    Route->>Ctrl: store(Request $request)
    Ctrl->>Req: Validasi Form (Scoped business_id pada supplier, produk, satuan)
    Req-->>Ctrl: Validated Array Clean
    
    rect rgb(240, 248, 255)
        Note over Ctrl,Model: DB::transaction Atomic Execution
        Ctrl->>Svc: createPurchaseOrder($business, $validated, $items)
        Svc->>Model: PurchaseOrder::create() & PurchaseOrderItem::create()
        Svc->>Model: Recalculate Subtotal, PPN, Snapshot HPP
    end

    Ctrl->>Approval: evaluateAndCreateRequest(DOC_PURCHASE_ORDER, $po->total_amount)
    Approval-->>Ctrl: ApprovalRequest Created / Auto-Approved
    
    Ctrl->>Notif: dispatch(PurchaseOrderCreatedEvent -> In-App, WA Owner, Email)
    Ctrl->>Guard: recordAuditLog('po.created', $po->id, $user)
    
    Ctrl-->>UI: Redirect to show($po->id) with Flash Success Toast
    UI-->>Actor: Tampilkan Halaman Detail PO + Stepper Otorisasi Interaktif
```

---

## 🔀 4. State Machine Transisi Status Purchase Order

```mermaid
stateDiagram-v2
    [*] --> DRAFT : store() [User / Purchasing Staff]
    
    state DRAFT {
        [*] --> InReview : Total > Threshold (ApprovalRule Active)
        InReview --> Approved : Approver Otorisasi
        InReview --> Rejected : Approver Menolak (Rejection Reason)
        Approved --> ReadyToConfirm
        [*] --> ReadyToConfirm : Tanpa Approval Rule
    }

    DRAFT --> CANCELLED : cancel() [Wajib Supervisor PIN + Alasan]
    DRAFT --> DELETED : destroy() [Hanya Draft Tanpa Faktur]
    
    ReadyToConfirm --> CONFIRMED : confirm() [Otorisasi Manager]

    state CONFIRMED {
        note right of CONFIRMED
            PO Pelanggan: Siap Terbit Faktur
            PO Supplier: Siap Terima Barang (GRN)
        end note
    }

    CONFIRMED --> CANCELLED : cancel() [Hanya jika GRN = 0 & Invoice = 0]

    %% Alur PO Pelanggan (B2B Sales)
    CONFIRMED --> FULLY_INVOICED : generateInvoice() [InvoiceService::createFromPurchaseOrder]
    FULLY_INVOICED --> COMPLETED : Pelunasan Faktur Penjualan (Paid)

    %% Alur PO Supplier (Procurement)
    CONFIRMED --> PARTIALLY_RECEIVED : GoodsReceipt (Sebagian Qty)
    PARTIALLY_RECEIVED --> COMPLETED : GoodsReceipt (Seluruh Qty Diterima Penuh)
    
    COMPLETED --> [*]
    CANCELLED --> [*]
    DELETED --> [*]
```

---

## 🏢 5. Matriks Kepatuhan 20 Sektor Industri (6 Klaster)

| Sektor Industri | Modul Customer PO | Ketentuan Tampilan & Operasional (Do's & Don'ts) |
| :--- | :---: | :--- |
| **F&B Restoran & Kafe** | **NONAKTIF** | **DON'T**: Dilarang memunculkan opsi PO Pelanggan dan filter B2B.<br>**DO**: Sediakan PO Vendor untuk bahan mentah & 1-Klik Beli ke Stok pasar. |
| **F&B Katering & Bakery** | **KONDISIONAL** | **DO**: Aktifkan Customer PO untuk pesanan pesta/pernikahan B2B.<br>**DO**: Hubungkan kebutuhan bahan baku dengan formula resep BOM. |
| **Manufaktur & Produksi** | **AKTIF PENUH** | **DO**: Aktifkan kedua tipe PO (Vendor bahan baku & Customer PO produk jadi).<br>**DO**: Wajibkan otorisasi berjenjang (MAR) untuk nominal besar. |
| **Ritel & Toko Obat** | **NONAKTIF** | **DON'T**: Sembunyikan PO Pelanggan.<br>**DO**: Fokuskan pada PO Pemasok & Penerimaan Barang Barcode Gudang. |
| **Bengkel & Otomotif** | **KONDISIONAL** | **DO**: Pengadaan suku cadang dan oli ke supplier distributor.<br>**DON'T**: Dilarang memunculkan terminologi dapur/resto pada antarmuka PO bengkel. |

---

## 🛠️ 6. Rencana Aksi Remediasi (Master Implementation Plan)

1. **Fase 1 (Selesai)**: Hardening Validasi Multi-Tenant IDOR (`Rule::exists()->where('business_id', ...)`).
2. **Fase 2 (Selesai)**: Pasang Explicit Tenant Isolation Shield `abort_unless($purchaseOrder->business_id === $business->id, 403);` di seluruh controller action.
3. **Fase 3 (Selesai)**: DB Transaction Atomicity pada `PurchaseOrderService` dan Non-Destructive Quota Metering.
4. **Fase 4 (Selesai)**: Three-Way Matching & Supervisor PIN Bcrypt guardrail pada pembatalan PO.
5. **Fase 5 (Selesai)**: Ekstraksi kamus dwibahasa `lang/id/purchasing.php` dan `lang/en/purchasing.php`.
6. **Fase 6 (In Progress)**: Refactor `create.blade.php` (Auto-Hiding Customer PO, Mobile Card View, Font Min 16px, Double Submit Guard, i18n).
7. **Fase 7 (In Progress)**: Refactor `index.blade.php` (Bento Segmented Control Tabs, URL deep-linking, 1-Click Stok Bahan Baku + Kas Keluar, Tap Target >= 44px).
8. **Fase 8 (In Progress)**: Refactor `show.blade.php` & `print.blade.php` (3-Row Header, Card View Mobile, Supervisor PIN Modal, i18n).
9. **Fase 9 (Pending)**: Ekstensi `GoodsReceiptWebController@instantStockIn` untuk mendukung `material_id` dan pemotongan akun kas tunai.
10. **Fase 10 (Pending)**: Automated Feature Test Suite `PurchaseOrderWebControllerTest.php` dan verifikasi 100% green pass.
