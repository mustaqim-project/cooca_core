# Modul SaaS Billing, Langganan & Batas Kuota (SaaS Subscriptions)

> **Status:** VERIFIED  
> **Domain Terkait:** `app/Domain/Billing/`, `app/Domain/System/`, `app/Domain/Mail/`  
> **Tabel Basis Data:** `billing_packages`, `saas_subscriptions`, `saas_entitlements`, `subscription_payments`, `quota_monthly_usages`, `businesses`

---

## 1. Tujuan & Nilai Bisnis

Modul SaaS Billing mengelola monetisasi platform Cooca, mencakup katalog paket langganan (Free Trial, Starter, Pro, Enterprise), pemenuhan hak akses fitur (*feature entitlements*), pembatasan kuota bulanan (*monthly quotas*), serta siklus penagihan dan perpanjangan langganan tenant.

---

## 2. Arsitektur Paket Langganan & Batas Kuota

Setiap bisnis tenant terikat pada satu langganan aktif (`saas_subscriptions`):

```
┌─────────────────────────────────────────────────────────────┐
│             PAKET LANGGANAN (BILLING PACKAGES)              │
│   • Free Promo Trial   • Starter UMKM   • Pro Enterprise    │
└──────────────────────────────┬──────────────────────────────┘
                               │ Menentukan
                               ▼
┌─────────────────────────────────────────────────────────────┐
│          HAK AKSES FITUR & BATAS KUOTA BULANAN              │
│   • Max Pengguna / Kasir     • Max Transaksi Bulanan        │
│   • Max Produk & Bahan       • Akses Modul Akuntansi        │
│   • Kuota Penyimpanan Media  • Akses Integrasi WhatsApp     │
└─────────────────────────────────────────────────────────────┘
```

---

## 3. Fitur Utama Modul SaaS Billing

### 3.1 Katalog Paket Langganan (Package Catalog)
* **Paket Uji Coba Gratis (Free Promo Trial):** Diberikan otomatis saat pendaftaran bisnis baru untuk masa uji coba tanpa komitmen kartu kredit.
* **Paket Berbayar (Starter / Pro):** Menyediakan fleksibilitas periode langganan (Bulanan / Tahunan) dengan diskon pembayaran tahunan.

### 3.2 Penegakan Batas Kuota Bulanan (Quota Enforcement)
* Middleware platform secara otomatis memeriksa kuota penggunaan bulanan (`quota_monthly_usages`):
  - *Batas Transaksi Kasir POS per Bulan.*
  - *Batas Jumlah Master Produk & Bahan Baku.*
  - *Batas Kuota Pengiriman Pesan WhatsApp.*
  - *Batas Jumlah Pengguna / Kasir Tambahan.*
* Saat kuota mendekati batas ($80\%$ dan $100\%$), sistem menampilkan notifikasi ramah pengguna yang menyarankan peningkatan paket (*Upgrade Plan*) tanpa memblokir mendadak operasional kasir yang sedang melayani antrean pelanggan.

### 3.3 Alur Pembayaran Langganan (Subscription Payments)
* Mendukung pembayaran transfer bank manual atau payment gateway resmi.
* Mengunggah bukti bayar paket langganan dengan verifikasi cepat oleh Superadmin di panel `/admin/subscriptions`.
* Sesaat setelah diverifikasi lunas, tanggal kedaluwarsa langganan (`expires_at`) otomatis diperpanjang dan hak fitur langsung aktif.

### 3.4 Pengingat Jatuh Tempo & Masa Tenggang (Grace Period & Reminders)
* Notifikasi email dan WhatsApp otomatis sebelum paket langganan habis (H-7, H-3, H-1).
* **Masa Tenggang (Grace Period):** Bisnis diberikan masa tenggang beberapa hari dengan akses terbatas (read-only mode atau fallback paket free) sebelum data disuspensi, menjamin data keuangan historis tetap aman.

---

## 4. Aturan Bisnis Billing (Business Rules)

* **RULE-BILL-001 (Non-Destructive Suspension):** Suspensi akun karena langganan habis tidak boleh menghapus data historis transaksi atau persediaan tenant.
* **RULE-BILL-002 (Package Snapshot on Payment):** Setiap transaksi pembayaran langganan wajib menyimpan snapshot spesifikasi paket saat pembayaran dilakukan (`billing_package_snapshot`) agar tidak terpengaruh jika harga paket platform diubah di kemudian hari.
