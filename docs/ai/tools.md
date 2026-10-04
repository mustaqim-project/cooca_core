# Katalog Alat Bantu (Tools) COOCA AI Digital Company

> **Layer:** Tools Registry, Schema, Risk Classification, & Output Payloads  
> **Source:** `App\Domain\Ai\Tools\` & `App\Domain\Ai\Tools\AiToolRegistry`

---

## 1. Arsitektur Alat Bantu

Setiap alat bantu mengimplementasikan `AiToolInterface` dan menurunkan `BaseAiTool`. Alat bantu terbagi menjadi dua kategori fundamental:

1. **Read Tools (Alat Baca):** Menghimpun data analitik dan statistik bisnis tanpa memodifikasi status database. Hasilnya langsung dikembalikan ke pengguna dalam respons percakapan.
2. **Propose Tools (Alat Proposal Aksi):** Tidak pernah menulis langsung ke tabel operasional (seperti `invoices` atau `purchase_orders`). Alat ini membuat rekaman draft di tabel `ai_action_proposals` dengan status `PENDING` untuk ditinjau oleh pemegang otoritas (Maker-Checker Gate).

---

## 2. Daftar Lengkap 12 Alat Bantu

### 2.1 Read Tools

#### 1. `GetSalesSummaryTool`
- **Tujuan:** Mengambil ringkasan total omzet, jumlah transaksi, dan rata-rata nilai keranjang (*Average Basket Size*) dalam rentang hari tertentu (default 30 hari).
- **Parameter:** `days` (integer, default: 30).
- **Tingkat Risiko:** `low` (read-only).

#### 2. `GetSalesTrendTool`
- **Tujuan:** Menganalisis grafik tren penjualan harian untuk mengidentifikasi pola kenaikan atau penurunan omzet.
- **Parameter:** `days` (integer, default: 14).
- **Tingkat Risiko:** `low` (read-only).

#### 3. `GetTopProductsTool`
- **Tujuan:** Menampilkan daftar produk dengan volume penjualan tertinggi dan kontribusi pendapatan terbesar.
- **Parameter:** `limit` (integer, default: 5).
- **Tingkat Risiko:** `low` (read-only).

#### 4. `GetStockLevelsTool`
- **Tujuan:** Menginspeksi status stok pergudangan, menyaring produk dengan stok berada di bawah batas minimum (*low stock alerts*).
- **Parameter:** `only_low_stock` (boolean, default: true).
- **Tingkat Risiko:** `low` (read-only).

#### 5. `DetectAnomaliesAndFraudTool`
- **Tujuan:** Mendeteksi transaksi kasir mencurigakan, pembatalan (*void*) bernilai tinggi tanpa otorisasi supervisor, atau diskon di luar batas wajar.
- **Parameter:** `days` (integer, default: 7).
- **Tingkat Risiko:** `low` (read-only insight).

#### 6. `GetCustomerSummaryTool`
- **Tujuan:** Mengagregasi metrik pelanggan: total pelanggan terdaftar, pelanggan aktif bertransaksi dalam 30 hari, dan saldo piutang tertunggak.
- **Parameter:** -
- **Tingkat Risiko:** `low` (read-only).

#### 7. `GetFinancialHealthTool`
- **Tujuan:** Mengevaluasi rasio likuiditas, total kas & bank, piutang lancar, dan total hutang dagang jatuh tempo.
- **Parameter:** -
- **Tingkat Risiko:** `low` (read-only).

#### 8. `GetAttendanceSummaryTool`
- **Tujuan:** Memantau ringkasan kehadiran karyawan hari ini, total hadir tepat waktu, terlambat, izin sakit, dan tanpa keterangan.
- **Parameter:** `date` (string Y-m-d, default: hari ini).
- **Tingkat Risiko:** `low` (read-only).

---

### 2.2 Propose Tools (Action Gate)

#### 9. `DraftInvoiceProposalTool`
- **Tujuan:** Menyusun draft usulan penerbitan faktur penjualan (invoice) resmi ke pelanggan.
- **Parameter Wajib:** `customer_id`, `product_id`, `quantity`, `unit_price`.
- **Tingkat Risiko:** `high` (memerlukan persetujuan finansial).
- **Payload:** Disimpan ke `ai_action_proposals` dengan struktur validasi ketat.

#### 10. `DraftPurchaseOrderProposalTool`
- **Tujuan:** Menyusun draf usulan pesanan pembelian ke pemasok (supplier) untuk restok bahan/barang.
- **Parameter Wajib:** `supplier_id`, `items` (array).
- **Tingkat Risiko:** `high` (komitmen pengeluaran kas).

#### 11. `DraftMarketingCampaignProposalTool`
- **Tujuan:** Menyusun rancangan draf promosi atau pengumuman penawaran diskon broadcast pelanggan.
- **Parameter Wajib:** `campaign_name`, `target_segment`, `message_template`, `discount_rate`.
- **Tingkat Risiko:** `medium` (dampak citra bisnis dan batas diskon).

#### 12. `DraftSocialPostProposalTool`
- **Tujuan:** Menyusun draf konten media sosial (caption, jadwal terbit, dan platform tujuan).
- **Parameter Wajib:** `platform`, `caption`, `topic`.
- **Tingkat Risiko:** `medium` (publikasi eksternal).
