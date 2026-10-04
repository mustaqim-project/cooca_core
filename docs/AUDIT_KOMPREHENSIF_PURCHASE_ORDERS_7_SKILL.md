# LAPORAN AUDIT KOMPREHENSIF PENUH: MODUL PURCHASE ORDERS (PO) COOCA
**Dokumen:** `docs/AUDIT_KOMPREHENSIF_PURCHASE_ORDERS_7_SKILL.md`  
**Target Modul:** `resources/views/app/purchase-orders/` & Ekosistem Terkait (`PurchaseOrderWebController`, `PurchaseOrderService`, `InvoiceService`, `GoodsReceiptService`, Model & Rute)  
**Metodologi:** Code-First Factuality (7 Skill Utama COOCA Diuji Simultan Berbasis Baris Kode Riil)  
**Status Evaluasi:** **NEEDS CRITICAL HARDENING & REFACTORING**  
**Prinsip Keamanan:** **MANDAT KESELAMATAN AKTIF (Confirmation Gate)**

---

## DELIVERABLE 1: Laporan Temuan Audit Komprehensif (Tabel 7 Kolom)

| No | Dimensi Skill | Lokasi File & Baris Kode | Kode Temuan / Root Cause | Severity | Dampak Risiko | Solusi / Rekomendasi |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **01** | 🛡️ Security & Fraud | [`PurchaseOrderWebController.php:120-135`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L120-L135) | **IDOR Multi-Tenant pada Validasi Request Form**: Aturan validasi `exists:customers,id`, `exists:suppliers,id`, `exists:products,id`, `exists:materials,id` tidak menyertakan klausa `where('business_id', $business->id)`. | **P1 (Kritis)** | Tenant A dapat menyuntikkan ID pelanggan/pemasok/bahan milik tenant B ke dalam dokumen PO-nya, memicu kebocoran data (*cross-tenant data leakage*). | Bungkus seluruh aturan relasi dengan `Rule::exists('table', 'id')->where('business_id', $business->id)`. |
| **02** | 🛡️ Security & Fraud | [`PurchaseOrderWebController.php:171,188,222,232,250`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L171-L250) | **Missing Explicit Tenant Isolation Shield**: Method `show()`, `confirm()`, `cancel()`, `generateInvoice()`, dan `print()` tidak memanggil `abort_unless($purchaseOrder->business_id === $business->id, 403);` (hanya `destroy()` yang memanggil). | **P1 (Kritis)** | Jika global scope ter-bypass atau diakses via endpoint sekunder, dokumen transaksi rahasia antar-tenant dapat diakses dan dicetak secara tidak sah. | Pasang `abort_unless($purchaseOrder->business_id === $business->id, 403);` di baris pertama setiap method controller. |
| **03** | 🛡️ Security & Fraud | [`PurchaseOrderWebController.php:222-227`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L222-L227)<br>[`PurchaseOrderService.php:123-130`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php#L123-L130) | **Zero-Guardrail Cancellation & Missing Supervisor PIN**: Pembatalan PO (`cancel`) dapat dilakukan langsung tanpa otorisasi Supervisor PIN (Bcrypt), tanpa memeriksa apakah barang fisik sudah diterima di gudang (`goodsReceipts()->exists()`), dan tanpa memeriksa faktur (`invoices()->exists()`). | **P1 (Kritis)** | **Potensi Fraud Penggelapan Barang**: Staf gudang/kasir dapat membatalkan PO yang barang fisiknya sudah masuk gudang, menciptakan *phantom inventory*, merusak *Three-Way Matching*, dan meninggalkan *dangling approval request*. | Wajibkan verifikasi `Hash::check($pin, $business->pos_supervisor_pin)`, tolak pembatalan jika memiliki relasi `goodsReceipts` atau `invoices`, dan catat `AuditLog`. |
| **04** | 🔄 Workflow Audit | [`PurchaseOrderService.php:38-109`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php#L38-L109) | **Missing Database Transaction Atomicity**: Pembuatan header PO (`PurchaseOrder::create`) dan perulangan baris item (`PurchaseOrderItem::create`) tidak dibungkus `DB::transaction(...)`. | **P1 (Kritis)** | Jika pembuatan item ke-3 gagal karena diskonnect/error validasi, header PO yatim (*orphan header without items*) tertinggal di basis data dengan nomor PO terpakai. | Bungkus seluruh alur pembuatan header, baris item, dan recalculation totals ke dalam satu blok closure `DB::transaction(...)`. |
| **05** | ⚡ Cooca Directive | [`PurchaseOrderWebController.php:143-147`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php#L143-L147) | **Quota Deduction Pre-Execution Risk**: Kuota bulanan dipotong (`incrementMonthlyUsage`) *sebelum* `createPurchaseOrder` berhasil dieksekusi. | **P2 (Tinggi)** | Jika terjadi *database crash* saat insert PO, kuota tenant Free tetap berkurang permanen padahal dokumen gagal terbit (*unjust quota punishment*). | Pindahkan pemanggilan `incrementMonthlyUsage` ke blok akhir setelah transaksi penyimpanan berhasil 100%. |
| **06** | 🏢 Multi-Industry | [`create.blade.php:9, 127-140`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L9-L140)<br>[`index.blade.php:111-118`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L111-L118) | **Missing Dynamic Module Gating**: Tipe "PO Pelanggan (Penjualan)" muncul secara default dan bebas dipilih pada tenant F&B / Ritel yang modul `customer_po` / `b2b_sales`-nya nonaktif (`fnb_resto`, `fnb_cafe`, `retail_reseller`). | **P2 (Tinggi)** | Mengacaukan model mental operasional kasir/owner resto/kafe yang tidak memiliki kanal B2B; mencemari KPI agregat antara Omzet Penjualan dan Biaya Pembelian. | Terapkan Dynamic Context-Aware Auto-Hiding: sembunyikan toggle/filter PO Pelanggan via `@if($business->isModuleEnabled('customer_po'))` dan kunci default ke `supplier`. |
| **07** | 🎨 UI Consistency | [`index.blade.php:113, 121`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L113-L121) | **Violation of Tab Architecture & State Loss**: Filter tipe dan status menggunakan HTML native `<select onchange="this.form.submit()">` yang memicu full reload halaman, bukan Segmented Control Tabs Bento Apple HIG dengan sinkronisasi URL deep-linking (`?tab=...`). | **P2 (Tinggi)** | UI terasa kaku, tidak selaras dengan macOS Sonoma Source List standar Cooca, dan state tab ter-reset saat browser di-refresh. | Ganti dengan Segmented Control Tabs Bento Apple HIG dengan watcher Alpine.js yang otomatis memperbarui URL search query (`window.history.replaceState`). |
| **08** | 📱 Responsive UX | [`create.blade.php:211-277`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L211-L277)<br>[`show.blade.php:181-224`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/show.blade.php#L181-L224) | **Desktop Table Trap & Horizontal Scroll pada Mobile**: Rincian item formulir dan detail dokumen hanya disajikan dalam tag `<table>` 6 kolom tanpa Card View responsif di layar ponsel (360px–390px). | **P2 (Tinggi)** | Pengguna smartphone terpaksa melakukan scroll horizontal berulang kali untuk memasukkan item, satuan, kuantitas, dan harga satuan. | Sediakan Card List View khusus mobile (`sm:hidden`) dengan layout 2 kolom padat dan sembunyikan tabel desktop di layar kecil (`hidden sm:block`). |
| **09** | 📱 Responsive UX | [`create.blade.php:148, 172, 259, 263`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L148-L263) | **iOS Safari Auto-Zoom Trap**: Input form menggunakan kelas tipografi `text-[13px]` dan `text-[12px]`. Font di bawah 16px memicu auto-zoom paksa pada peramban WebKit/Safari iOS. | **P2 (Tinggi)** | Tampilan aplikasi di iPhone tiba-tiba membesar (*zoomed-in*), memotong tombol aksi dan merusak tata letak bento. | Ubah seluruh kelas font input dan select menjadi `text-[16px] sm:text-[13px]`. |
| **10** | 📱 Responsive UX | [`index.blade.php:220, 226, 248`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L220-L248)<br>[`create.blade.php:268`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L268) | **Substandard Tap Target (< 44px)**: Tombol cetak, download PDF, hapus draft, dan hapus baris item berukuran `h-7 w-7` (28×28px). | **P2 (Tinggi)** | Sangat sulit ditekan dengan jari jempol pada layar sentuh, rentan salah sentuh (*fat-finger errors*) bagi pengguna usia 40–65 tahun. | Ubah tombol aksi sentuh menjadi minimum `min-h-[44px] min-w-[44px]` dengan padding sentuh ergonomis. |
| **11** | 🛡️ Security & Fraud | [`create.blade.php:334`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/create.blade.php#L334)<br>[`index.blade.php:419`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L419) | **Missing Double-Submit Protection**: Tombol "Terbitkan Purchase Order" dan "Beli & Tambah ke Stok" tidak memiliki proteksi disabling saat pengiriman (`:disabled="submitting"`). | **P2 (Tinggi)** | Pengguna yang mengklik tombol dua kali secara cepat akan menerbitkan 2 nomor PO ganda dan mengurangi kuota transaksi 2 kali. | Pasang state `:disabled="isSubmitting"` dengan spinner Lucide pada tombol kirim form. |
| **12** | 🔄 Workflow Audit | [`index.blade.php:365-426`](file:///c:/laragon/www/cooca_core/resources/views/app/purchase-orders/index.blade.php#L365-L426)<br>[`GoodsReceiptWebController.php:124-136`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Purchasing/GoodsReceiptWebController.php#L124-L136) | **Incomplete Instant Stock-In Scope & Accounting Blindspot**: Fitur "1-Klik Beli ke Stok" hanya menerima `product_id` (tidak bisa memilih bahan baku `material_id`), serta menambah stok fisik tanpa mencatat pengeluaran kas (`CashLedger`) atau jurnal akuntansi pembelian tunai. | **P2 (Tinggi)** | Bahan mentah pasar (telur, tepung, sparepart) tidak bisa diinput cepat; neraca keuangan menjadi tidak seimbang (*inventory asset up, cash ledger not deducted*). | Tambahkan opsi pemilihan `material_id` pada modal, dan bukukan kas keluar pada akun kas kecil laci/bank via `CashLedgerService`. |
| **13** | 🔄 Workflow Audit | [`PurchaseOrderWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PurchaseOrderWebController.php)<br>[`PurchaseOrderService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Commerce/PurchaseOrderService.php) | **Zero Tri-Channel Notification Dispatch**: Tidak ada emisi notifikasi sistem (In-App Bell, Email HTML, dan WhatsApp Meta API) saat PO dibuat, butuh persetujuan, dikonfirmasi, atau dibatalkan. | **P2 (Tinggi)** | Pemilik bisnis dan staf logistik terlambat merespons pengadaan bahan baku, proses pengadaan terhambat tanpa jejak komunikasi otomatis. | Dispatch event `PurchaseOrderCreatedEvent` dan `PurchaseOrderConfirmedEvent` untuk mentrigger notifikasi tri-channel. |
| **14** | 🌐 Multi-Language | Seluruh berkas Blade (`index`, `create`, `show`, `print`) & Controller PO | **100% Hardcoded Indonesian Strings & Static Currency**: 140+ string teks bahasa Indonesia mentah tertanam langsung tanpa helper `{{ __('purchasing.key') }}` dan JS `new Intl.NumberFormat('id-ID')` statis. | **P3 (Sedang)** | Fitur pengalih bahasa (ID/EN) gagal menerjemahkan modul PO; format mata uang asing tidak adaptif terhadap konfigurasi bisnis. | Ekstraksi seluruh teks ke `lang/id/purchasing.php` dan `lang/en/purchasing.php`, serta injeksi `window.COOCA_I18N.purchasing`. |

---

## DELIVERABLE 2: Pemetaan 11 Simpul Eksekusi & State Machine

### 2.1 State Machine Transisi Status Purchase Order

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

### 2.2 Sequence Diagram: Alur Data Hulu-ke-Hilir (11 Simpul Eksekusi)

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
    Ctrl->>Svc: createPurchaseOrder($business, $validated, $items)
    
    rect rgb(240, 248, 255)
        Note over Svc,Model: DB::transaction Atomic Execution
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

## DELIVERABLE 3: Rekomendasi Solusi & Komparasi Before vs After

### 3.1 Hardening Validasi Multi-Tenant IDOR (`PurchaseOrderWebController.php`)
```php
// BEFORE: Rentan IDOR
$validated = $request->validate([
    'customer_id' => ['nullable', 'required_if:po_type,customer', 'exists:customers,id'],
    'supplier_id' => ['nullable', 'required_if:po_type,supplier', 'exists:suppliers,id'],
    'items.*.product_id' => ['nullable', 'exists:products,id'],
    'items.*.material_id' => ['nullable', 'exists:materials,id'],
]);

// AFTER: Kebal IDOR Scoped Tenant Aktif
use Illuminate\Validation\Rule;
$businessId = $business->id;

$validated = $request->validate([
    'customer_id' => [
        'nullable',
        'required_if:po_type,customer',
        Rule::exists('customers', 'id')->where('business_id', $businessId),
    ],
    'supplier_id' => [
        'nullable',
        'required_if:po_type,supplier',
        Rule::exists('suppliers', 'id')->where('business_id', $businessId),
    ],
    'items.*.product_id' => [
        'nullable',
        Rule::exists('products', 'id')->where('business_id', $businessId),
    ],
    'items.*.material_id' => [
        'nullable',
        Rule::exists('materials', 'id')->where('business_id', $businessId),
    ],
]);
```

### 3.2 Pembatalan Berpenjaga & Proteksi Supervisor PIN (`PurchaseOrderWebController.php:cancel`)
```php
// BEFORE: Pembatalan sepihak tanpa otorisasi
public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
{
    $this->poService->cancel($purchaseOrder);
    return back()->with('success', "Pesanan {$purchaseOrder->po_number} telah dibatalkan.");
}

// AFTER: Three-Way Matching Guard + Bcrypt Supervisor PIN + Audit Log
public function cancel(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
{
    $business = Context::requireBusiness();
    abort_unless($purchaseOrder->business_id === $business->id, 403);

    if ($purchaseOrder->goodsReceipts()->exists()) {
        return back()->with('error', __('purchasing.errors.cannot_cancel_received'));
    }

    if ($purchaseOrder->invoices()->exists()) {
        return back()->with('error', __('purchasing.errors.cannot_cancel_invoiced'));
    }

    if ($purchaseOrder->status === PurchaseOrder::STATUS_CONFIRMED && ! empty($business->pos_supervisor_pin)) {
        $pin = (string) $request->input('supervisor_pin', '');
        if (! Hash::check($pin, $business->pos_supervisor_pin)) {
            return back()->with('error', __('purchasing.errors.invalid_supervisor_pin'));
        }
    }

    DB::transaction(function () use ($purchaseOrder, $business, $request): void {
        $this->poService->cancel($purchaseOrder);

        if ($purchaseOrder->approvalRequest && $purchaseOrder->approvalRequest->isPending()) {
            $purchaseOrder->approvalRequest->update([
                'status' => 'cancelled',
                'rejection_reason' => 'Dibatalkan oleh supervisor.',
            ]);
        }

        \App\Models\AuditLog::create([
            'business_id' => $business->id,
            'user_id' => Context::user()?->id,
            'action' => 'po.cancelled',
            'module' => 'Purchasing',
            'record_type' => PurchaseOrder::class,
            'record_id' => $purchaseOrder->id,
            'reason_notes' => $request->input('cancel_reason', 'Pembatalan resmi'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    });

    return back()->with('success', __('purchasing.messages.cancelled', ['number' => $purchaseOrder->po_number]));
}
```

---

## DELIVERABLE 4: Dokumen PRD (Product Requirement Document) Terpadu

### 4.1 Ruang Lingkup Sistem
Modul Purchase Order COOCA v2.0 menangani pengadaan bahan baku ke pemasok (*Vendor PO*) dan pesanan pembelian masuk dari mitra bisnis (*Customer PO*).

### 4.2 Matriks Do's & Don'ts 20 Sektor Industri (6 Klaster)

| Sektor Industri | Modul Customer PO | Ketentuan Tampilan & Operasional (Do's & Don'ts) |
| :--- | :--- | :--- |
| **F&B Restoran & Kafe** | **NONAKTIF** | **DON'T**: Dilarang memunculkan opsi PO Pelanggan dan filter B2B.<br>**DO**: Sediakan PO Vendor untuk bahan mentah & 1-Klik Beli ke Stok pasar. |
| **F&B Katering & Bakery** | **KONDISIONAL** | **DO**: Aktifkan Customer PO untuk pesanan pesta/pernikahan B2B.<br>**DO**: Hubungkan kebutuhan bahan baku dengan formula resep BOM. |
| **Manufaktur & Produksi** | **AKTIF PENUH** | **DO**: Aktifkan kedua tipe PO (Vendor bahan baku & Customer PO produk jadi).<br>**DO**: Wajibkan otorisasi berjenjang (MAR) untuk nominal besar. |
| **Ritel & Toko Obat** | **NONAKTIF** | **DON'T**: Sembunyikan PO Pelanggan.<br>**DO**: Fokuskan pada PO Pemasok & Penerimaan Barang Barcode Gudang. |
| **Bengkel & Otomotif** | **KONDISIONAL** | **DO**: Pengadaan suku cadang dan oli ke supplier distributor.<br>**DON'T**: Dilarang memunculkan terminologi dapur/resto pada antarmuka PO bengkel. |

---

## DELIVERABLE 5: Rencana Implementasi Bertahap (Roadmap Fase 1–10)

1. **Fase 1**: Hardening Validasi Request & Multi-Tenant IDOR Shield.
2. **Fase 2**: Atomic DB Transactions & Non-Destructive Quota Metering.
3. **Fase 3**: Three-Way Matching & Supervisor PIN Guardrail.
4. **Fase 4**: Dynamic Context-Aware Auto-Hiding (Multi-Industry).
5. **Fase 5**: Standarisasi Header 3-Baris & Segmented Control Tabs.
6. **Fase 6**: Mobile-First Responsive Overhaul & Anti-Auto-Zoom.
7. **Fase 7**: Penyempurnaan 1-Klik Beli ke Stok (Bahan Baku + Kas Keluar).
8. **Fase 8**: Ekstraksi Kamus Multi-Bahasa Dwibahasa (i18n ID & EN).
9. **Fase 9**: Arsitektur Real-Time Polling & Tri-Channel Notifications.
10. **Fase 10**: Pembuatan Automated Feature Tests (100% Test Coverage Lolos).

---

## 🛑 MANDAT KESELAMATAN: INTERACTIVE CONFIRMATION GATE
Sesuai aturan operasional COOCA Direktif Tahap 3, **AI Agent DILARANG KERAS** memodifikasi kode sebelum konfirmasi persetujuan dari pengguna.
