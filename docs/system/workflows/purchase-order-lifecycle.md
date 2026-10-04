# Alur Kerja Siklus Hidup Dokumen Purchase Order (Purchase Order Lifecycle Workflow)

> **Status:** COMPLETE  
> **Aktor Terlibat:** Manajer Pembelian, Kasir, Owner / Supervisor, Staf Gudang, Vendor Pemasok, Klien B2B  
> **Modul Terkait:** Commerce, Purchasing, Inventory, Finance, Accounting, Approvals  
> **Dokumen Rujukan:** [`docs/system/audits/purchase-orders-comprehensive-audit.md`](file:///c:/laragon/www/cooca_core/docs/system/audits/purchase-orders-comprehensive-audit.md)

---

## 1. Arsitektur 11 Simpul Eksekusi Hulu-ke-Hilir

```mermaid
sequenceDiagram
    autonumber
    actor Actor as Pengguna (Staff / Kasir / Owner)
    participant UI as Blade View (Bento HIG v2.0)
    participant Alpine as Alpine.js Reactive Engine
    participant Route as Route & Middleware (Auth, Tenant, RBAC, Entitlement)
    participant Ctrl as PurchaseOrderWebController
    participant Req as Request Validation (Tenant-Scoped IDOR Shield)
    participant Svc as PurchaseOrderService
    participant Approval as ApprovalWorkflowService (MAR)
    participant Model as Eloquent Model & DB Schema
    participant FinStock as Stock & CashLedger (via GRN/Invoice)
    participant Notif as Tri-Channel Notification (In-App, WA, Email)
    participant Guard as AuditLog & Guardrails (Bcrypt PIN)

    Actor->>UI: Input Baris Item & Klik "Terbitkan PO"
    UI->>Alpine: Validasi Reaktif Client-Side & Lock Submit Button
    UI->>Route: POST /purchase-orders (CSRF + Tenant Scope)
    Route->>Ctrl: store(Request $request)
    Ctrl->>Req: Validasi Form (Scoped business_id pada supplier, produk, satuan)
    Req-->>Ctrl: Payload Bersih Terverifikasi
    
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

## 2. State Machine Transisi Status Purchase Order

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

    DRAFT --> CANCELLED : cancel() [Alasan Pembatalan]
    DRAFT --> DELETED : destroy() [Hanya Draft Tanpa Faktur]
    
    ReadyToConfirm --> CONFIRMED : confirm() [Otorisasi Manager]

    state CONFIRMED {
        note right of CONFIRMED
            PO Pelanggan: Siap Terbit Faktur
            PO Supplier: Siap Terima Barang (GRN)
        end note
    }

    CONFIRMED --> CANCELLED : cancel() [Wajib Supervisor PIN + Zero Goods Receipt + Zero Invoice]

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

## 3. Matriks Skenario & Guardrail Bisnis

### A. Three-Way Matching Guardrail (Anti-Fraud Gudang)
1. **Aturan Kunci:** Dokumen Purchase Order berstatus `confirmed` yang telah memiliki rekaman penerimaan fisik barang (`GoodsReceipt`) atau faktur penjualan (`Invoice`) **DILARANG KERAS** dibatalkan.
2. **Mitigasi:** Mencegah terjadinya stok hantu (*phantom inventory*) dan selisih neraca keuangan akibat manipulasi status sepihak oleh kasir atau staf gudang.
3. **Pengecualian:** Jika pembatalan tetap diperlukan karena cacat transaksi, pembatalan harus melalui proses formal *Return to Vendor* (Retur Pembelian) dengan nota kredit resmi.

### B. Supervisor PIN Verification (Bcrypt)
1. Setiap pembatalan dokumen PO berstatus `confirmed` wajib memverifikasi PIN Supervisor terenkripsi (`$business->pos_supervisor_pin`).
2. Setiap percobaan verifikasi PIN (berhasil maupun gagal) dicatat ke dalam `AuditLog` dengan menyertakan IP pengguna dan user-agent untuk mencegah *brute-force PIN guessing*.

### C. Dynamic Context-Aware Auto-Hiding 20 Sektor Industri
1. **Klaster F&B & Ritel:** Pada sektor di mana modul `customer_po` atau `b2b_sales` dinonaktifkan, antarmuka secara otomatis menyembunyikan pemilih "PO Pelanggan", mengunci formulir secara cerdas ke mode "PO Vendor (Pengadaan)", dan menyembunyikan filter tipe transaksi pada tabel.
2. **Klaster Manufaktur & Proyek:** Menyediakan akses penuh terhadap kedua jenis PO dengan alur resep Bill of Materials (BOM) terintegrasi.
