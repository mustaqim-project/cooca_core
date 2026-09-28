# Template Dokumen PRD Sistem Adaptif Lintas Industri (Multi-Industry Adaptive PRD)

Gunakan cetak biru ini saat menyusun dokumen PRD untuk penataan antarmuka sadar konteks (*Context-Aware UI*), penegakan Do's & Don'ts, dan adaptasi formulir untuk 20 sektor industri Cooca.

---

```markdown
# Product Requirement Document (PRD): Sistem Adaptif & Penegakan Do's/Don'ts [Nama Modul]
## Antarmuka Sadar Konteks untuk 20 Sektor Industri Cooca

> **Versi Dokumen:** 1.0  
> **Status:** PROPOSED & READY FOR IMPLEMENTATION  
> **Modul Target:** [Contoh: Master Produk, POS Terminal, Manajemen Pesanan, Sidebar Navigasi]  
> **Ruang Lingkup Industri:** 20 Industri Resmi Cooca (6 Klaster: F&B, Manufaktur, Ritel, Jasa Harian, Proyek, Distribusi)  
> **Prinsip Utama:** Zero Irrelevant Clutter, Boomer Ergonomics, Seamless Module Gating.

---

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

### 1.1 Konteks Bisnis
Sistem Cooca melayani 20 jenis industri yang memiliki alur operasional dan kebutuhan formulir yang sangat berbeda. Saat ini, beberapa halaman/formulir pada modul `[Nama Modul]` masih menampilkan elemen antarmuka atau kolom input secara generik/monolitik sehingga menimbulkan beban kognitif (*cognitive overload*) bagi pengguna UMKM.

### 1.2 Masalah Spesifik & Pelanggaran Do's/Don'ts
Sebutkan temuan audit konkret:
- Industri Non-FnB (misal Bengkel atau Toko Baju) masih melihat menu/opsi yang hanya relevan untuk restoran (seperti Meja, KDS, Saluran Ojol).
- Form input mewajibkan data yang tidak relevan bagi industri tertentu (misal mewajibkan berat atau barcode pada jasa bengkel/salon).
- Terminologi bahasa terdengar kaku dan tidak mencerminkan istilah bisnis lokal pengguna.

### 1.3 Dampak Operasional Jika Dibiarkan
- Pengguna merasa aplikasi "terlalu rumit dan membingungkan" (*user churn*).
- Kasir atau operator melakukan kesalahan input (*human error*).
- Waktu onboarding pengguna baru menjadi lambat.

---

## 2. Sasaran & Metrik Keberhasilan (OKRs / Success Metrics)

| Metrik Keberhasilan | Kondisi Saat Ini | Target Pasca-Implementasi |
|---|:---:|:---:|
| **Kebocoran Menu / Fitur Tak Relevan** | Ditemukan [X] elemen bocor di industri non-target | 0 Elemen Bocor (100% Terfilter Kondisional) |
| **Beban Kolom Formulir (Field Count)** | [X] kolom ditampilkan sekaligus ke semua industri | Berkurang 30% - 50% mengikuti konteks industri aktif |
| **Akurasi Validasi Backend** | Gagal submit karena field tersembunyi berstatus required | 100% Validasi adaptif mengikuti modul aktif |
| **Tingkat Kepuasan Pengguna (CSAT)** | Pengguna mengeluhkan menu tidak relevan | Tampilan bersih, lapang, dan langsung dipahami |

---

## 3. Matriks Kebutuhan Fitur Lintas 6 Klaster Industri

| Klaster Industri | Modul Wajib Tampil (DO) | Modul Wajib Sembunyi (DON'T) | Penyesuaian Kolom Form Khusus |
|---|---|---|---|
| **1. Kuliner & F&B** | `pos_dinein`, `recipe_bom`, Ojol Pricing | `b2b_sales`, `labor_machines` | Tampilkan Resep Gramasi & Add-on Varian |
| **2. Manufaktur** | `recipe_bom`, `labor_machines`, `b2b_sales` | `pos_dinein`, `pos_retail` | Tampilkan Upah Buruh, Jam Mesin & DP 50% |
| **3. Ritel & Apotek** | `pos_retail`, `inventory_warehouse`, Barcode | `pos_dinein`, `recipe_bom` | Tampilkan Scanner Barcode & Tier Grosir |
| **4. Jasa Harian (Bengkel/Salon)** | SPK Servis, Mekanik/Stylist, Reservasi | `pos_dinein`, Resep Makanan | Tampilkan Data Kendaraan / Slot Waktu |
| **5. Jasa Proyek** | Penawaran B2B, Faktur Termin, PO Subkon | `pos_retail`, `pos_dinein` | Tampilkan Milestone Termin & Lampiran PDF |
| **6. Distribusi & Agro** | Multi-Gudang, Surat Jalan (DO), Plafon Piutang | `pos_retail`, `pos_dinein` | Tampilkan Validasi Credit Limit & Truk Ekspedisi |

---

## 4. Kebutuhan Fungsional Rinci (Functional Requirements)

### FR-01: Gating Kondisional Antarmuka Blade
- **Deskripsi:** Seluruh blok antarmuka (Bento Box, tab, menu, tombol) yang spesifik untuk fitur modular wajib dibungkus dengan pemeriksaan `$business->isModuleEnabled($moduleKey)`.
- **Kriteria Penerimaan (Acceptance Criteria - Gherkin):**
  ```gherkin
  Scenario: Menyembunyikan Bento Box Resep BOM pada bisnis Bengkel
    Given Bisnis aktif memiliki template_code = 'service_workshop' (recipe_bom dinonaktifkan)
    When Pengguna membuka modal Tambah / Ubah Produk
    Then Bento Box "Resep Formula BOM" tidak ditampilkan di layar
    And Layout modal menyesuaikan secara proporsional tanpa ruang kosong aneh
  ```

### FR-02: Kamus Istilah Semantik Dinamis (Dynamic Terminology)
- **Deskripsi:** Sistem menyediakan helper terminologi untuk menyesuaikan label tombol dan placeholder form berdasarkan klaster industri aktif.
- **Kriteria Penerimaan (Acceptance Criteria - Gherkin):**
  ```gherkin
  Scenario: Menyesuaikan label navigasi POS kasir pada bisnis Salon
    Given Bisnis aktif adalah industri Salon ('service_barbershop')
    When Kasir membuka antarmuka terminal
    Then Label "Pilih Meja" otomatis berubah menjadi "Pilih Kursi / Stylist"
  ```

### FR-03: Validasi Backend Sadar Konteks (Context-Aware FormRequest)
- **Deskripsi:** Validasi input backend tidak boleh mewajibkan data dari modul yang dinonaktifkan untuk bisnis tersebut.
- **Kriteria Penerimaan (Acceptance Criteria - Gherkin):**
  ```gherkin
  Scenario: Memvalidasi simpan produk tanpa error pada field yang disembunyikan
    Given Bisnis aktif tidak mengaktifkan modul recipe_bom
    When Pengguna menyimpan produk baru tanpa mengirimkan data resep
    Then Backend menerima dan menyimpan data produk secara sukses
    And Tidak ada error validasi 422 untuk kolom resep
  ```

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Performance**: Pemeriksaan `isModuleEnabled()` berjalan in-memory melalui atribut model `Business::$disabled_modules` tanpa query database tambahan.
2. **Backward Compatibility**: Pengguna lama yang tidak memiliki konfigurasi `disabled_modules` secara default mempertahankan seluruh modul aktif (`fallback: true`).
3. **No Data Punishment**: Mengubah template industri tidak boleh menghapus data yang sudah tersimpan sebelumnya di database.

---

## 6. Spesifikasi Desain Bento Apple HIG

- **Adaptive Modal Layout**: Saat section bento disembunyikan, grid bento secara cerdas beralih dari multi-kolom ke single-kolom lebar penuh (`col-span-full`) tanpa menyisakan ruang putih menganga (*blank whitespace*).
- **Zero Irrelevant Helper Text**: Teks panduan hanya menjelaskan hal-hal yang relevan dengan industri pengguna.
```
