# Modul SaaS Billing, Langganan & Batas Kuota (SaaS Subscriptions)

> **Status:** VERIFIED  
> **Domain Terkait:** `app/Domain/Billing/`, `app/Domain/System/`, `app/Domain/Mail/`  
> **Tabel Basis Data:** `billing_packages`, `saas_subscriptions`, `saas_entitlements`, `subscription_payments`, `quota_monthly_usages`, `businesses`

---

## 1. Tujuan & Nilai Bisnis

Modul SaaS Billing mengelola monetisasi platform Cooca, mencakup katalog paket langganan (Free Trial, Starter, Pro, Enterprise), pemenuhan hak akses fitur (*feature entitlements*), pembatasan kuota bulanan (*monthly quotas*), serta siklus penagihan dan perpanjangan langganan tenant.

---

## 2. Arsitektur Paket Langganan & Batas Kuota (4-Tier Model §Blueprint v2.3)

Setiap bisnis tenant terikat pada paket langganan (`business_subscriptions`) dengan arsitektur 4-tier:
* **Free (Rp 0):** Solusi operasional mandiri 1 kasir (1 entitas bisnis per owner, 10 produk, 3 BOM resep, 1 lokasi, 0 meja kasir dine-in, 30 transaksi kasir POS/bln, kuota storage owner 1 GB).
* **Standard (Rp 29.000/bln | Rp 290.000/thn):** UMKM berkembang (1 entitas bisnis per owner, 100 produk, 20 BOM resep, 1 lokasi, 5 meja kasir dine-in, 1.000 transaksi kasir POS/bln, 15 PO/bln, 15 Faktur/bln, kuota storage owner 3 GB, 3 staf, ekspor Excel lengkap).
* **Premium (Rp 89.000/bln | Rp 890.000/thn):** Multi-cabang & manufaktur (hingga 3 entitas bisnis per owner, unlimited produk & resep, meja kasir dine-in unlimited, 5 cabang/outlet, transfer stok antar gudang, penetapan harga per cabang / multi-pricing, KDS dapur, HRM lanjutan: komisi kasir & kasbon pinjaman, kuota storage owner 10 GB, 10 staf).
* **Prestige (Rp 199.000/bln | Rp 1.990.000/thn):** Enterprise & kepatuhan penuh (unlimited entitas bisnis per owner, semua fitur Premium, unlimited cabang & staf, kalkulasi PPh 21 TER A/B/C PP 58/2023 & Pasal 17, otomasi kirim slip gaji WhatsApp, 1.000 notifikasi WA/bln, kuota storage owner 30 GB).

> **Storage & Kuota Multi-Bisnis:** Kapasitas penyimpanan media melekat pada **Akun Owner**, bukan per cabang. Kuota dihitung berdasarkan tier tertinggi di antara seluruh bisnis aktif milik Owner.  
> **Kebijakan Domain Toko (Strictly Zero Custom Domain):** Seluruh etalase publik / storefront online toko beroperasi secara terpusat di bawah domain resmi `cooca.id/{slug-bisnis}` (dengan rute fallback alias `cooca.id/b/{slug}`) untuk semua tier tanpa pengecualian. Tidak ada dependensi atau fitur custom domain.  
> **Payment Gateway Eksklusif:** Pembayaran langganan SaaS menggunakan gateway **TriPay** secara eksklusif (QRIS Dinamis & Virtual Account otomatis tanpa perlu upload bukti transfer fisik maupun kode unik verifikasi manual).

---

## 3. Fitur Utama Modul SaaS Billing

### 3.1 Katalog Paket Langganan (Package Catalog)
* **Paket Free:** Diberikan otomatis saat pendaftaran bisnis baru tanpa batas waktu uji coba (*freemium*).
* **Paket Berbayar (Standard / Premium / Prestige):** Menyediakan opsi periode Bulanan dan Tahunan (diskon 2 bulan gratis: bayar 10 bulan aktif 12 bulan).

### 3.2 Penegakan Batas Kuota Bulanan (Quota & Entitlement Enforcement)
* Middleware platform `CheckResourceEntitlement` secara otomatis memeriksa kuota penggunaan bulanan (`quota_monthly_usages`) dan entitlement fitur:
  - *Batas Entitas Bisnis per Owner (`getBusinessLimit`, `canCreateBusiness`).*
  - *Batas Meja Dine-in Kasir POS (`getTableLimit`, `canCreateTable`).*
  - *Batas Transaksi Kasir POS, PO, dan Faktur per Bulan.*
  - *Batas Jumlah Master Produk, Bahan Baku, dan Resep.*
  - *Gating Fitur Lanjutan:* Transfer Stok, KDS Dapur, Multi-Pricing Cabang, Komisi Kasir, Kasbon, BPJS, THR, PPh 21 TER.
* Saat kuota mendekati batas ($80\%$ dan $100\%$), sistem menampilkan notifikasi ramah pengguna yang menyarankan peningkatan paket (*Upgrade Plan*) tanpa memblokir mendadak operasional kasir yang sedang melayani antrean pelanggan.

### 3.3 Alur Pembayaran Langganan (TriPay Exclusive Flow)
* Transaksi checkout otomatis menerbitkan invoice pembayaran melalui payment gateway TriPay.
* Mendukung saluran QRIS Dinamis (semua e-Wallet & Mobile Banking) dan Virtual Account bank nasional (BCA, Mandiri, BRI, BNI, Permata, CIMB).
* Transaksi diverifikasi secara otomatis dan instan 24/7 melalui callback webhook TriPay (`/api/tripay/callback`).

### 3.4 Siklus Hidup Langganan 3 Tahap (Subscription 3-Phase Lifecycle)
Command konsol `app:process-subscription-lifecycle` dijalankan terjadwal untuk mengelola siklus hidup:
1. **Fase 1 (Pemberitahuan Menjelang Kedaluwarsa H-7):** Mengirimkan peringatan invoice dan email reminder kepada Owner agar segera melakukan perpanjangan.
2. **Fase 2 (Masa Tenggang / Grace Period Hari 1–3):** Status langganan berubah menjadi `past_due`. **Kasir POS tetap 100% operasional (`isOperational() === true`)** agar antrean transaksi pelanggan tidak terganggu. Penambahan data baru pada back-office (produk baru, cabang baru) dikunci sementara.
3. **Fase 3 (Kedaluwarsa Hari 4+ / Downgrade ke Free):** Status menjadi `expired`, akun secara otomatis di-downgrade ke paket Free.
* **Komitmen No Data Punishment:** Tidak ada data transaksi, produk, resep, atau laporan keuangan yang dihapus atau disembunyikan saat terjadi downgrade. Seluruh data historis tetap dapat dilihat dan diekspor (read-only) untuk entitas yang melebihi batas Free.

### 3.5 CMS Katalog Paket Billing Platform & Harga Default Cooca
* Terletak pada rute Superadmin `/admin/billing-packages/{type?}` (`AdminBillingPackageController`).
* Mengelola 3 tab katalog terpadu:
  1. **Paket & Durasi Subscription:** Paket langganan Core bertempo (30 hari, 90 hari, 365 hari) dengan opsi kuota bonus token AI.
  2. **Paket Top Up Token AI:** Kuota instan pemrosesan model kecerdasan buatan Cooca dengan masa berlaku hari tertentu.
  3. **Paket Top Up Storage:** Kuota ruang penyimpanan permanen yang diakumulasikan ke kapasitas dasar akun owner.
* **Single Source of Truth Default Pricing:**
  - Panel konfigurasi fallback bawaan platform (`subscription_price_monthly`, `subscription_price_annual`, `subscription_ai_tokens_monthly`, `subscription_annual_discount_badge`, `ai_token_topup_price`, `ai_token_topup_amount`, `storage_topup_price`, `storage_topup_gb`, `owner_storage_limit_gb`).
* **Kepatuhan Desain Apple HIG v2.0:** Mengadopsi Bento Cards squircle `rounded-[22px]`, Apple Pill Segmented Control, modal edit inset dialog `rounded-[28px]`, input anti auto-zoom iOS `text-[16px] sm:text-[13px]`, tipografi angka murni `tabular-nums`, serta kepatuhan Anti-Pill-Abuse (maksimal 1 badge status resmi `Aktif`/`Nonaktif`, nol fake pulse dots) dan aturan Zero Unicode Emoji.

---

## 4. Aturan Bisnis Billing (Business Rules)

* **RULE-BILL-001 (Non-Destructive Suspension / No Data Punishment):** Suspensi akun karena langganan habis tidak boleh menghapus data historis transaksi atau persediaan tenant.
* **RULE-BILL-002 (Package Snapshot on Payment):** Setiap transaksi pembayaran langganan wajib menyimpan snapshot spesifikasi paket saat pembayaran dilakukan (`billing_package_snapshot`) agar tidak terpengaruh jika harga paket platform diubah di kemudian hari.
* **RULE-BILL-003 (Cashier Guarantee during Grace Period):** Selama 3 hari pertama masa tenggang (status `past_due`), transaksi kasir POS wajib diizinkan tetap beroperasi tanpa hambatan.
* **RULE-BILL-004 (Strict Storefront Hosting & Canonical Direct Slug):** Seluruh etalase publik tenant wajib dilayani secara kanonikal di bawah `cooca.id/{slug-bisnis}` dengan rute alias `/b/{slug}` untuk backward compatibility. Bebas 100% dari dependensi custom domain.
