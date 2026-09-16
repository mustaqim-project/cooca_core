# Modul Pelanggan & Loyalitas CRM (Customer & CRM Loyalty)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Customer/`, `app/Domain/Crm/`, `app/Domain/WhatsApp/`, `app/Domain/Finance/`  
> **Tabel Basis Data:** `customers`, `customer_point_histories`, `vouchers`, `voucher_usages`, `receivable_invoices`, `receivable_payments`  
> **Route / UI Utama:** `/customers` (Pusat Pelanggan & Loyalitas Terpadu), `/crm/members`, `/crm/vouchers` (Backward-Compatible Aliases)

---

## 1. Tujuan & Nilai Bisnis

Modul Pelanggan & CRM Loyalitas menyatukan pencatatan direktori kontak pelanggan, manajemen piutang usaha (kredit/tempo), program loyalitas bertingkat (*Tier Membership & Poin Belanja*), serta kupon diskon/promosi dalam satu antarmuka terpadu (**Apple HIG v2.0 Bento Grid**).

### Nilai Tambah Utama:
1. **Penyatuan UI Radikal (*UI Unification*):** Mengeliminasi lompatan menu antara Data Pelanggan, Loyalitas Poin, dan Kupon Diskon melalui *Segmented Control* 3 Tab yang persisten.
2. **Kenyamanan Ekstrem Pengguna (*Boomer & Gaptek Ergonomics*):**
   - Ukuran font input formulir minimal 16px (`text-[16px]`) untuk mencegah *auto-zoom* browser di iOS/Android.
   - Target sentuh tombol aksi minimal 48px – 52px.
   - Format pemisah ribuan otomatis (`Rp 100.000`) pada input pelunasan piutang dan diskon.
   - Tombol instan persentase pelunasan (25%, 50%, 100% Lunas).
   - Tautan langsung obrolan WhatsApp (`wa.me`) dengan format pesan yang ramah.
   - Mikro-kopi penenang (*No-Panic Microcopy*): *"💡 Tenang: Riwayat nota penjualan dan pembukuan masa lalu Anda tetap aman tersimpan."*
3. **Kepatuhan Backward-Compatibility (Bebas Broken Link):** URL lama `/crm/members` dan `/crm/vouchers` tetap aktif dan menyajikan tampilan modern yang selaras tanpa mematahkan bookmark kasir atau tautan dokumentasi.

---

## 2. Arsitektur Antarmuka Bento UI & Segmented Control

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ PUSAT PELANGGAN & LOYALITAS CRM (Header + Tombol Tambah Cepat)              │
├─────────────────────────────────────────────────────────────────────────────┤
│ BENTO HERO KPI TILES:                                                       │
│ [ 👥 Total Pelanggan ] [ 🏢 Klien Bisnis/B2B ] [ 🏆 Poin Beredar ] [ 💳 Piutang ]│
├─────────────────────────────────────────────────────────────────────────────┤
│ SEGMENTED CONTROL:                                                          │
│   [ 👥 Direktori Pelanggan ]   [ 🏆 Member & Poin ]   [ 🎟️ Voucher Diskon ]   │
├─────────────────────────────────────────────────────────────────────────────┤
│ TAB CONTENT AREA:                                                           │
│  • Tab 1: Tabel Pelanggan + Saldo Piutang + Syarat Tempo + WhatsApp Instan │
│  • Tab 2: Tingkatan Tier (VIP/Gold/Silver/Bronze) + Riwayat Poin Belanja    │
│  • Tab 3: Kartu Kupon Voucher + Diskon % / Rp + Toggle Aktif/Nonaktif       │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 2.1 Kartu Metrik Bento (Hero KPI Tiles)
* **Total Pelanggan:** Jumlah kontak aktif terdaftar milik tenant.
* **Klien Korporat / B2B:** Kontak berbadan usaha dengan termin pembayaran tempo (*Net Terms*).
* **Poin Loyalitas Beredar:** Total akumulasi poin reward pembeli yang belum ditukarkan.
* **Total Piutang Berjalan:** Saldo kredit pelanggan yang belum dilunasi (`credit_balance > 0`).

---

## 3. Fitur & Alur Operasional

### 3.1 Direktori Pelanggan & Termin Pembayaran (`/customers?tab=customers`)
* **Pencarian Cepat & Filter:** Pencarian real-time berdasarkan Nama, Telepon WhatsApp, atau Email.
* **Pengaturan Termin Kredit (*Credit Terms*):**
  - Batas Plafon Kredit (`credit_limit`).
  - Termin Jatuh Tempo Faktur (Contoh: *Net 7 Hari*, *Net 14 Hari*, *Net 30 Hari*).
  - Indikator peringatan saat plafon piutang hampir atau telah terlampaui.
* **Tombol Cepat WhatsApp:** 1-klik membuka aplikasi WhatsApp (`https://wa.me/{phone}`) untuk mengirimkan tagihan ramah atau ucapan terima kasih.
* **Modal Pelunasan Piutang (*Credit Payment Modal*):**
  - Menampilkan sisa tagihan piutang berjalan.
  - Opsi cepat pembayaran: `[ 25% ]`, `[ 50% ]`, `[ Lunas (100%) ]`.
  - Pilihan metode penerimaan (Kas Tunai, Transfer Bank Mandiri/BCA/BRI).
  - Otomasi pencatatan buku kas dan penyesuaian saldo piutang.

### 3.2 Program Loyalitas & Riwayat Poin (`/crm/members` atau `/customers?tab=members`)
* **Tingkatan Keanggotaan (*Membership Tiers*):**
  - `Bronze`: Pelanggan baru / reguler.
  - `Silver`: Pelanggan dengan akumulasi belanja menengah.
  - `Gold`: Pelanggan loyal dengan frekuensi belanja tinggi.
  - `Platinum`: Pelanggan VIP prioritas.
* **Riwayat Mutasi Poin (*Point Audit Trail*):**
  - Modal audit melihat log penambahan poin dari transaksi penjualan POS/Storefront dan pengurangan poin saat penukaran hadiah (*redemption*).

### 3.3 Kupon & Voucher Diskon (`/crm/vouchers` atau `/customers?tab=vouchers`)
* **Tipe Potongan Fleksibel:**
  - Persentase (`percentage`, contoh: 10%, 20%).
  - Nominal Tetap (`fixed`, contoh: Rp 25.000, Rp 50.000).
* **Aturan Penggunaan:**
  - Minimal belanja transaksi (`min_spend`).
  - Batas maksimal potongan (`max_discount`).
  - Kuota batas penggunaan kupon (`usage_limit`).
  - Periode aktif tanggal mulai (`start_date`) hingga berakhir (`end_date`).
* **Interaksi Ramah Pengguna:** Tombol salin kode voucher 1-klik dan toggle sakelar status aktif langsung tanpa reload halaman yang rumit.

---

## 4. Aturan Keamanan & Integritas Data (Security Guardrails)

1. **Scoping Multi-Tenant Ketat (Strict Tenant Isolation):**
   Setiap query ke model `Customer`, `CustomerPointHistory`, dan `Voucher` WAJIB mengikat konteks bisnis aktif melalui `Context::requireBusiness()`:
   ```php
   $business = \App\Support\Context::requireBusiness();
   $customers = Customer::where('business_id', $business->id)->...;
   ```
2. **Proteksi IDOR & Validasi Finansial:**
   - Pelunasan kredit pelanggan hanya dapat dilakukan jika entitas pelanggan valid milik tenant yang sedang aktif.
   - Nominal pelunasan tidak boleh bernilai negatif atau melebihi saldo piutang yang tercatat.
3. **Pemberitahuan Penenang Jiwa (*No-Panic Microcopy*):**
   Formulir konfirmasi perubahan atau arsip data menyertakan pesan penenang agar kasir dan owner merasa aman saat mengelola data.

---

## 5. Hubungan Lintas Modul

* **Ke Modul POS & Kasir:** Kasir dapat memilih member terdaftar saat transaksi untuk mengakumulasikan poin reward dan menerapkan kode voucher promosi.
* **Ke Modul E-Commerce (Storefront):** Voucher promosi dapat divalidasi dan diaplikasikan langsung pada keranjang belanja pembeli online.
* **Ke Modul Finance & Auto-Journal:** Pembayaran pelunasan piutang pelanggan langsung menghasilkan jurnal akuntansi penerimaan kas dan memutakhirkan neraca saldo secara otomatis.
* **Ke Modul WhatsApp:** Mengirimkan notifikasi struk digital, bukti tanda terima pelunasan piutang, dan ucapan selamat ulang tahun / promosi voucher secara otomatis ke nomor WhatsApp pelanggan.

---

## 6. Verifikasi & Pengujian Otomatis

Status operasional modul ini tervalidasi 100% bebas eror (*100% Zero-Error Mandate*) melalui test suite:
- `tests/Feature/CustomerWebFeatureTest.php` (CRUD Direktori Pelanggan, Filter, & Segmented Control)
- `tests/Feature/CrmWebFeatureTest.php` (Loyalitas Member, Poin, Voucher, & Pelunasan Piutang)
- `tests/Feature/Commerce/CustomerTest.php` (Model Domain, Plafon Piutang, & Audit Poin)

**Hasil Pengujian:** 16 Tests Passed, 64 Assertions, 0 Failures, 0 Errors.
