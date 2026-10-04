# Alur Kerja Siklus Hidup SaaS Billing, Pembayaran Gateway & Entitlement Kuota (Billing & Entitlement Workflow)

> **Status Dokumen:** `CURRENT STATE (VERIFIED)`  
> **Aktor Terlibat:** Business Owner, Staf Keuangan/Kasir, Superadmin Platform, Gateway TriPay API, Background Cron Worker  
> **Modul Terkait:** SaaS Billing, Limits, Finance/CashLedger, Cloud Storage, AI Engine, Tri-Channel Notifications  
> **Dokumen Rujukan:** [`docs/AUDIT_KOMPREHENSIF_BILLING_7_SKILL.md`](file:///c:/laragon/www/cooca_core/docs/AUDIT_KOMPREHENSIF_BILLING_7_SKILL.md) & [`docs/system/modules/saas-billing.md`](file:///c:/laragon/www/cooca_core/docs/system/modules/saas-billing.md)

---

## 1. Arsitektur 11 Simpul Eksekusi Hulu-ke-Hilir

```mermaid
sequenceDiagram
    autonumber
    actor Owner as Business Owner (User)
    participant UI as Blade View (Bento HIG v2.0)
    participant Alpine as Alpine.js Reactive Engine
    participant Route as Route & Middleware (Auth, Tenant, Perms: billing.manage)
    participant Ctrl as SubscriptionCheckoutWebController
    participant Req as Request Validation (Tenant-Scoped IDOR Shield)
    participant Svc as EntitlementService & TripayService
    participant Model as Eloquent (SubscriptionPayment & BusinessSubscription)
    participant Fin as AutoJournal & Expense Ledger (Tenant Cash Account)
    participant Gateway as TriPay Payment Gateway API
    participant Notif as Tri-Channel Notification (WA, Email, Bell In-App)
    participant Guard as Storage Pruning & Supervisor Guard

    %% Step 1: Inisialisasi Pesanan
    Owner->>UI: Buka /billing/checkout & Pilih Paket (Siklus Bulanan/Tahunan)
    UI->>Alpine: Reaktif Update Kalkulasi Harga & Kanal Pembayaran
    Owner->>Alpine: Pilih Kanal Bayar (QRIS / Virtual Account) & Submit
    Alpine->>UI: Kunci Tombol (:disabled="isSubmitting", Loading Spinner)
    UI->>Route: POST /billing/order (require.permission:billing.manage)
    Route->>Ctrl: store(Request $request)
    Ctrl->>Req: Validasi Form (order_type, package_id, cycle, tier, payment_method)
    Req-->>Ctrl: Payload Bersih Terverifikasi
    
    rect rgb(240, 248, 255)
        Note over Ctrl,Model: Transaksi Pembuatan Pesanan
        Ctrl->>Svc: createPaymentOrder(...)
        Svc->>Model: SubscriptionPayment::create(status=pending, unique_code=0)
        Svc->>Gateway: createSubscriptionTransaction(TriPay API)
        Gateway-->>Svc: Response (pay_code, qr_string, qr_url, expired_time)
        Svc->>Model: Update data gateway pada SubscriptionPayment
    end

    Ctrl-->>UI: Redirect ke /billing/payments/{payment}
    UI-->>Owner: Tampilkan Instruksi Bayar (QRIS / VA) & Mulai Polling Adaptif

    %% Step 2: Pembayaran & Webhook Callback
    Owner->>Gateway: Pembeli Menyelesaikan Pembayaran (m-Banking / Scan QRIS)
    Gateway->>Route: Webhook POST /api/v1/payments/tripay/callback
    Route->>Ctrl: TripayCallbackController::handle()
    Ctrl->>Svc: verifyCallbackSignature(HMAC-SHA256)
    
    rect rgb(240, 255, 240)
        Note over Ctrl,Fin: DB::transaction Atomic Activation
        Ctrl->>Svc: approvePayment(payment)
        Svc->>Model: SubscriptionPayment status = approved, approved_at = now()
        Svc->>Model: BusinessSubscription status = active, perpanjang ends_at
        Svc->>Svc: Injeksi Kuota / Topup Token AI / Storage Quota
        Svc->>Svc: clearUsageCache($business)
        Svc->>Fin: Catat Beban Langganan SaaS pada Buku Kas Pengeluaran Tenant
    end

    Ctrl->>Notif: Dispatch SubscriptionPaymentCompletedEvent (WA Meta, Email, Bell)
    Ctrl->>Guard: Catat immutable AuditLog 'subscription.activated'

    %% Step 3: Reaktivitas Frontend Tanpa Reload
    Alpine->>Ctrl: Polling GET /billing/payments/{payment}/status (Adaptive)
    Ctrl-->>Alpine: JSON { success: true, is_paid: true, status: 'approved' }
    Alpine->>UI: Update Reaktif State (is_paid = true, Render Kartu Sukses)
    Note over UI,Alpine: Bebas window.location.reload()! Transisi DOM Mulus
```

---

## 2. State Machine Transisi Status Pembayaran & Entitlement Tenant

```mermaid
stateDiagram-v2
    direction TB

    state "Siklus Pembayaran (Subscription Payment)" as PAYMENT_FLOW {
        [*] --> PENDING : store() [Owner Checkout]
        
        PENDING --> APPROVED : Webhook Gateway TriPay (PAID) / Instant Free Trial
        PENDING --> AWAITING_APPROVAL : Upload Bukti Manual (Legacy)
        PENDING --> CANCELLED : Batas Waktu Expired (24 Jam) / Dibatalkan
        
        AWAITING_APPROVAL --> APPROVED : Superadmin Verifikasi Sah
        AWAITING_APPROVAL --> REJECTED : Superadmin Tolak (Alasan Spesifik)
        
        note right of APPROVED
            1. Perpanjang ends_at pada BusinessSubscription
            2. Update plan_code (Standard/Premium/Prestige)
            3. Injeksi Kuota / Token AI / Storage Top-up
            4. Reset Kuota Bulanan & Invalidation Cache
            5. Auto-Journal Beban Operasional Tenant
            6. Dispatch Notifikasi Tri-Channel
        end note
        
        APPROVED --> [*]
        REJECTED --> [*]
        CANCELLED --> [*]
    }

    state "Siklus Entitlement Tenant (Business Subscription)" as ENTITLEMENT_FLOW {
        [*] --> ACTIVE : Masa Berlaku Aktif (starts_at s/d ends_at)
        ACTIVE --> PAST_DUE : Lewat Jatuh Tempo (Hari ke-1 s/d ke-3)
        
        note right of PAST_DUE
            Masa Tenggang (Grace Period):
            - Kasir POS tetap beroperasi penuh tanpa gangguan
            - Penambahan data baru dikunci sementara (Read-Only)
            - Peringatan banner kuning (No-Panic Microcopy)
            - NO DATA PUNISHMENT: Data tidak pernah dihapus!
        end note
        
        PAST_DUE --> ACTIVE : Tagihan Terbayar Sah (APPROVED)
        PAST_DUE --> EXPIRED : Melewati Hari ke-3 (Downgrade Otomatis)
        
        note right of EXPIRED
            Downgrade ke Free Solo:
            - Batasan kuota diturunkan ke default Free Solo
            - Seluruh data historis eksisting AMAN & TERBACA
            - Disediakan fitur Storage Pruning Preview
        end note
        
        EXPIRED --> ACTIVE : Upgrade / Perpanjang Paket
    }
```

---

## 3. Rincian 11 Simpul Eksekusi Nyata

### Simpul 1: User / Aktor
- **Business Owner:** Pemegang otoritas penuh keuangan; memilih paket, melakukan pembayaran via TriPay, memantau penggunaan kuota, dan mengelola pembersihan storage.
- **Staf / Kasir:** Dibatasi ketat hanya dengan hak akses operasional kasir; diblokir dari rute pembayaran dan konfigurasi paket (`require.permission:billing.manage`).
- **Superadmin:** Mengelola katalog paket (`BillingPackage`), memantau log webhook pembayaran, dan memverifikasi pesanan manual bila diperlukan.
- **Payment Gateway (TriPay API):** Aktor eksternal otomatis yang menghasilkan nomor Virtual Account, string QRIS dinamis, dan mengirimkan callback webhook HMAC-SHA256 saat dana diterima dari bank pembayar.

### Simpul 2: UI / Blade
- Mengadopsi prinsip **Bento Apple HIG v2.0**:
  - Struktur 3-Baris Page Header terpadu (`<x-module-header>`): Overline modul, H1 Title + Status Badge lisensi, dan Subtitle informatif.
  - Tab navigasi segmented control terpadu (`<x-module-tabs module="billing" />`) dengan deep-linking URL query `?tab=...`.
  - Format angka moneter dengan font monospace tabular `font-mono tabular-nums`.
  - Menghilangkan scroll horizontal pada layar sempit 320px–360px dengan stacked card view responsif.

### Simpul 3: Alpine.js / AJAX Reaktivitas
- **Smart Adaptive Polling:** Polling status mutasi gateway dihentikan secara otomatis saat tab browser diminimalkan (`document.hidden`) menggunakan Page Visibility API.
- **Zero Page Reload:** Menghapus total `window.location.reload()`. Ketika pembayaran sah terdeteksi, state `isPaid = true` mengubah tampilan secara reaktif di DOM seketika.
- **Double-Submit Protection:** Tombol submit checkout dan order dikunci dengan `:disabled="isSubmitting"` untuk mencegah multi-order duplikat.

### Simpul 4: Route & Middleware
- Seluruh rute dilindungi oleh isolasi tenant `Context::requireBusiness()` dan permission bertingkat:
  - Hak Akses Baca (`require.permission:billing.view`): `/billing/limits`, `/billing/history`, `/billing/payments/{payment}`, `/billing/payments/{payment}/invoice`.
  - Hak Akses Kelola (`require.permission:billing.manage`): `/billing/checkout`, `/billing/order`, `/billing/storage/recalculate`, `/billing/storage/files/{storageFile}`.
- Rute bypass bebas bayar `/billing/upgrade` ditutup secara permanen pada lingkungan produksi.

### Simpul 5: Controller
- [`SubscriptionCheckoutWebController`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Billing/SubscriptionCheckoutWebController.php): Mengelola alur checkout, inisialisasi TriPay, polling status, faktur, dan riwayat pesanan.
- [`BillingAndLimitWebController`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Billing/BillingAndLimitWebController.php): Mengelola dasbor pemantauan kuota, sinkronisasi storage, dan daftar file tenant.
- [`TripayCallbackController`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Api/V1/Payment/TripayCallbackController.php): Memverifikasi tanda tangan HMAC-SHA256 callback dan mengeksekusi aktivasi lisensi otomatis.

### Simpul 6: Request Validation
- Memvalidasi pilihan paket terhadap tabel `billing_packages` yang aktif:
  ```php
  'package_id' => ['nullable', 'uuid', Rule::exists('billing_packages', 'id')->where('is_active', true)],
  'cycle' => ['nullable', 'string', 'in:monthly,annual'],
  'tier' => ['nullable', 'string', 'in:standard,premium,prestige'],
  'payment_method' => ['required', 'string', 'in:' . implode(',', $validCodes)],
  ```

### Simpul 7: Domain Action / Service
- [`EntitlementService`](file:///c:/laragon/www/cooca_core/app/Domain/Billing/EntitlementService.php): Mengatur alokasi kuota 4-tier, perpanjangan masa aktif `ends_at`, serta pembersihan cache entitlement (`clearUsageCache`).
- [`TripayService`](file:///c:/laragon/www/cooca_core/app/Domain/Payment/TripayService.php): Membuka transaksi API ke TriPay, menghitung fee transaksi, dan memverifikasi HMAC signature.
- [`StorageTrackingService`](file:///c:/laragon/www/cooca_core/app/Domain/Storage/StorageTrackingService.php): Melacak kapasitas fisik disk dan menghapus berkas aman strictly per-tenant.

### Simpul 8: Eloquent Model & DB Schema
- `BusinessSubscription`: Menyimpan status lisensi (`active`, `past_due`, `expired`), plan code (`standard_monthly`, dll.), kuota AI, serta timestamps masa aktif.
- `SubscriptionPayment`: Menyimpan detail transaksi, nomor pesanan (`SUB-XXXX`), kode bayar VA, URL gambar QRIS, nominal total, dan status verifikasi.
- `StorageFile`: Menyimpan metadata berkas (nama, ukuran dalam bytes, kategori, modul, path disk, status).

### Simpul 9: Auto-Journal & Buku Kas Tenant
- Ketika pembayaran langganan disetujui (`status = approved`), sistem membukukan pencatatan pengeluaran operasional software pada buku kas tenant:
  - **Kategori Beban:** Beban Operasional / Langganan Sistem SaaS
  - **Arus Kas:** Kas Keluar (*Cash Outflow*) pada `CashTransaction` tenant untuk menjaga keakuratan laporan laba rugi bulanan UMKM.

### Simpul 10: Notifikasi Tri-Channel
- **In-App Bell:** Notifikasi real-time ke ikon lonceng dashboard pemilik usaha saat pesanan dibuat dan saat pembayaran berhasil diverifikasi.
- **Email:** Mengirimkan salinan kuitansi resmi dan faktur PDF ke email pemilik akun via `PaymentApprovedInvoiceMail`.
- **WhatsApp Gateway:** Mengirimkan pesan konfirmasi pelunasan otomatis beserta tautan invoice resmi langsung ke nomor WhatsApp pemilik usaha.

### Simpul 11: Guardrails & Perlindungan Human Error
- **No Data Punishment:** Saat tenant mengalami penurunan lisensi (*downgrade*) atau keterlambatan bayar, data produk, transaksi, dan histori tidak pernah dihapus secara sepihak oleh sistem.
- **Storage Pruning Preview:** Menyediakan dialog konfirmasi dua langkah (*No-Panic Microcopy*) sebelum menghapus berkas penyimpanan cloud, lengkap dengan verifikasi keterikatan berkas aktif.
- **Grace Period (3 Hari):** Memberikan toleransi keterlambatan bayar selama 3 hari di mana kasir POS tetap dapat bertransaksi secara normal.

---

## 4. Matriks Isolasi Multi-Industri (Dynamic Context-Aware Auto-Hiding)

Modul Billing menyesuaikan visualisasi kartu kuota berdasarkan 20 sektor industri aktif:
- **Sektor Jasa (Bengkel, Car Wash, Barbershop, Salon, Laundry):** Kartu kuota **Meja Kasir POS (Dine-In)** dan **Resep HPP (BOM)** otomatis disembunyikan menggunakan pengecekan `@if($business->isModuleEnabled(...))`.
- **Sektor Ritel & Grosir (Minimarket, Toko Kelontong, Fashion, Bangunan):** Menampilkan kuota Produk, Outlet Gudang, Pelanggan, dan Struk Transaksi Kasir, sementara kuota Meja Makan disembunyikan.
- **Sektor Kuliner & F&B (Restoran, Kafe, Bakery):** Menampilkan seluruh metrik kuota secara lengkap termasuk Meja Dine-In, Resep Bahan Baku (BOM), dan Kitchen Display System (KDS).
