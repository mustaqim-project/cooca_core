# Modul Kalkulasi Biaya & Costing Engine

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Domain/Costing/`, `app/Domain/Calculation/`, `app/Domain/Formula/`, `app/Domain/Labor/`, `app/Domain/Machine/`, `app/Domain/Overhead/`  
> **Tabel Basis Data:** `cost_models`, `boms`, `bom_items`, `labor_rates`, `machines`, `overheads`, `costing_runs`, `costing_results`, `product_cost_versions`

---

## 1. Tujuan & Nilai Bisnis

Modul Costing Engine adalah fondasi ilmiah penentuan harga di Cooca. Tujuannya adalah menghitung **Harga Pokok Penjualan (HPP / COGS)** per unit produk secara presisi, terstruktur, dan dinamis untuk beragam jenis industri (F&B / Kuliner, Manufaktur, Ritel, Jasa / Servis, Konveksi / Percetakan).

Dengan modul ini, pemilik usaha tidak lagi menebak-nebak harga jual (*pricing by guesswork*), melainkan mengetahui secara pasti struktur modal, margin keuntungan kotor dan bersih, serta batas toleransi diskon.

---

## 2. Struktur Biaya Multi-Tier (4 Pilar Komponen HPP)

Biaya total per unit produk dihitung dari akumulasi 4 elemen:

$$\text{HPP Total} = \text{Biaya Bahan Baku} + \text{Biaya Tenaga Kerja} + \text{Biaya Mesin} + \text{Overhead Pabrik/Toko}$$

```
┌─────────────────────────────────────────────────────────────┐
│                   TOTAL BIAYA HPP PRODUK                    │
├──────────────┬──────────────┬───────────────┬───────────────┤
│ 1. BAHAN     │ 2. TENAGA    │ 3. MESIN &    │ 4. OVERHEAD   │
│    BAKU      │    KERJA     │    DEPRESIASI │    UMUM       │
│ (Resep BOM)  │ (Labor Rate) │ (Machine/kWh) │ (Sewa/Listrik)│
└──────────────┴──────────────┴───────────────┴───────────────┘
```

1. **Biaya Bahan Baku (*Raw Materials / BOM*):**  
   Kalkulasi kuantitas bahan yang terpakai dikalikan harga beli acuan atau harga beli rata-rata (*Moving Average Cost*). Mendukung konversi satuan (misal beli per Karung 25kg, pakai per 150 gram).
2. **Biaya Tenaga Kerja Langsung (*Direct Labor Cost*):**  
   Dihitung dari tarif upah kerja per jam/menit atau borongan dikalikan durasi waktu proses produksi (*cycle time*).
3. **Biaya Mesin & Peralatan (*Machine & Tooling Overhead*):**  
   Beban depresiasi mesin per jam, konsumsi listrik (kWh), bahan bakar, dan biaya perawatan alat selama proses produksi.
4. **Biaya Overhead Umum (*Factory / General Overhead*):**  
   Alokasi biaya sewa tempat, internet, gas/air, dan utilitas operasional yang dibebankan per unit berdasarkan persentase atau unit driver (*Activity-Based Costing / ABC*).

---

## 3. Fitur Unggulan

### 3.1 Mesin Kalkulator HPP Reaktif (Interactive HPP Calculator)
* Menghitung HPP secara instan di antarmuka tanpa reload halaman (*reactive calculation via Alpine.js*).
* Menyajikan rekomendasi harga jual berdasarkan target margin keuntungan:
  $$\text{Harga Jual Acuan} = \frac{\text{HPP}}{1 - \text{Target Margin \%}}$$
* **One-Click Apply to Product:** Hasil kalkulasi dapat langsung diterapkan ke master produk untuk memperbarui modal standar dan harga jual kasir.
* **Quick Create Product:** Membuat master produk baru langsung dari draf perhitungan kalkulator.
* **Ekspor Excel:** Mengunduh rincian lembar kerja kalkulasi HPP ke format `.xlsx`.

### 3.2 Simulator Skenario "What-If" (What-If Scenario Simulator)
* Menguji ketahanan margin usaha terhadap inflasi:
  - *Simulasi Kenaikan Bahan Baku:* Mengukur dampak jika harga telur atau tepung naik $15\%$ terhadap laba kotor.
  - *Simulasi Sensitivitas Harga:* Mengetahui batas maksimum diskon promosi sebelum bisnis menderita rugi kotor (*Negative Margin Alert*).

### 3.3 Analisis Titik Impas (BEP & Profitability Analyzer)
* Menghitung **BEP Unit** (jumlah porsi/barang minimal yang harus terjual per bulan) dan **BEP Rupiah** (minimal omzet bulanan untuk menutup seluruh biaya tetap dan variabel).
* Menghitung **Margin of Safety (MoS)**: batas toleransi penurunan penjualan sebelum usaha mulai merugi.

---

## 4. Validasi & Aturan Bisnis (Business Rules)

* **RULE-COST-001 (Non-Zero BOM Quantity):** Setiap bahan baku dalam resep BOM wajib memiliki kuantitas pakai $> 0$.
* **RULE-COST-002 (Immutable Historical Cost):** Kalkulasi HPP baru tidak boleh mengubah HPP transaksi masa lalu yang sudah selesai tercatat di nota penjualan.
* **RULE-COST-003 (Currency Rounding):** Pembulatan harga jual mengikuti aturan pembulatan bisnis aktif (misal dibulatkan ke ratusan terdekat `100` atau ribuan terdekat `1000`).

---

## 5. Keterkaitan Lintas Modul (Cross-Module Dependencies)

* **Ke Modul Material:** Mengambil harga acuan bahan baku dan faktor konversi satuan.
* **Ke Modul Product:** Menerapkan harga pokok dan harga jual ke master katalog.
* **Ke Modul POS & Inventory:** Resep BOM digunakan oleh mesin POS untuk memotong stok bahan baku mentah secara otomatis saat produk terjual.
