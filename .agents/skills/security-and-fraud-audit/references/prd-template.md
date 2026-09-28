# Template Dokumen PRD Keamanan, Anti-Fraud, & Human Error (Product Requirement Document)

Gunakan cetak biru ini saat menyusun dokumen PRD perbaikan keamanan dan anti-fraud setelah audit selesai.

---

```markdown
# Product Requirement Document (PRD): [Nama Modul / Inisiatif Perbaikan]
## Hardening Keamanan, Proteksi Fraud, & Pencegahan Human Error

> **Versi Dokumen:** 1.0  
> **Status:** PROPOSED & READY FOR IMPLEMENTATION  
> **Modul Target:** [Contoh: Products, POS, Warehouse, Finance]  
> **Ruang Lingkup Kode:** [Daftar file Controller, Service, Model, View, Route yang diaudit]  
> **Tingkat Risiko Tertinggi:** [CRITICAL / HIGH / MEDIUM]

---

## 1. Ringkasan Eksekutif & Latar Belakang Masalah

### 1.1 Konteks Bisnis
Jelaskan modul yang diaudit, perannya dalam rantai bisnis Cooca, dan bagaimana fitur ini berinteraksi dengan pengguna (Owner, Kasir, Gudang, atau Pelanggan Toko Online).

### 1.2 Ringkasan Masalah & Risiko Temuan Audit
Rangkum temuan audit utama:
- Celah keamanan cyber (misal: potensi IDOR atau Mass Assignment).
- Celah kecurangan internal (misal: tidak adanya otorisasi PIN saat pembatalan/penyesuaian).
- Celah fraud eksternal (misal: manipulasi harga di sisi klien).
- Titik rawan human error (misal: double-click form submit atau salah ketik ribuan).

### 1.3 Dampak Finansial & Operasional Jika Dibiarkan
Jelaskan skenario kerugian riil bagi merchant UMKM jika celah ini tidak diperbaiki.

---

## 2. Sasaran & Metrik Keberhasilan (OKRs / Success Metrics)

| Metrik Keberhasilan | Kondisi Saat Ini (Current State) | Target Pasca-Implementasi (Target State) |
|---|:---:|:---:|
| **Celah IDOR & Multi-Tenant** | Ditemukan [X] endpoint tanpa verifikasi tenant | 100% Endpoint terproteksi `Context::requireBusiness()` |
| **Proteksi Otorisasi Supervisor** | Aksi sensitif dapat dieksekusi bebas staf | 100% Memerlukan verifikasi PIN Supervisor |
| **Kejadian Double-Submit** | Rentan klik ganda saat koneksi internet lambat | 0 Insiden (Tombol disabled otomatis + Idempotency) |
| **Audit Trail Aksi Sensitif** | Tidak ada pencatatan riwayat perubahan nilai | 100% Tercatat di tabel `audit_logs` secara immutable |
| **Lolos Pengujian Otomatis** | Belum ada tes keamanan untuk skenario ini | 100% PASS pada unit & feature test suite |

---

## 3. Batasan Ruang Lingkup (Scope & Non-Scope)

### Dalam Cakupan (In-Scope):
- [Sebutkan file, route, form, atau service yang akan diperbaiki secara spesifik]
- [Sebutkan aturan validasi baru yang akan ditambahkan]
- [Sebutkan komponen UI Bento Apple HIG yang akan dipasang pengaman]

### Di Luar Cakupan (Non-Scope):
- [Sebutkan modul lain yang tidak disentuh untuk mencegah scope creep]

---

## 4. Kebutuhan Fungsional Rinci (Functional Requirements)

### FR-01: [Judul Kebutuhan, misal: Scoping Multi-Tenant & Proteksi IDOR]
- **Deskripsi:** Backend wajib memvalidasi kepemilikan tenant aktif sebelum menampilkan, mengubah, atau menghapus record.
- **Kriteria Penerimaan (Acceptance Criteria - Gherkin):**
  ```gherkin
  Scenario: Mencegah akses entitas milik bisnis lain melalui manipulasi UUID
    Given Pengguna terautentikasi pada Tenant A
    When Pengguna mencoba mengakses atau mengirim payload untuk entitas milik Tenant B
    Then Sistem mengembalikan respons 404 Not Found atau 403 Forbidden
    And Tidak ada kebocoran data yang ditampilkan di antarmuka
  ```

### FR-02: [Judul Kebutuhan, misal: Proteksi Fraud Internal via Supervisor PIN]
- **Deskripsi:** Aksi berisiko tinggi wajib meminta input PIN Supervisor sebelum transaksi final dieksekusi.
- **Kriteria Penerimaan (Acceptance Criteria - Gherkin):**
  ```gherkin
  Scenario: Memverifikasi PIN Supervisor saat melakukan aksi sensitif
    Given Staf operasional memilih aksi sensitif (Void / Stock Adjustment Minus)
    When Modal sheet verifikasi PIN muncul dan staf memasukkan PIN yang salah
    Then Sistem menolak aksi dan menambah hitungan kegagalan (rate limit 5x percobaan)
    When Staf memasukkan PIN Supervisor yang valid
    Then Aksi dieksekusi dan log tercatat lengkap di audit_logs
  ```

### FR-03: [Judul Kebutuhan, misal: Pencegahan Human Error Double-Submit]
- **Deskripsi:** Form transaksi wajib mencegah pengiriman berulang saat proses simpan sedang berjalan.
- **Kriteria Penerimaan (Acceptance Criteria - Gherkin):**
  ```gherkin
  Scenario: Mencegah pembuatan data ganda saat tombol submit ditekan berulang
    Given Pengguna telah mengisi seluruh formulir dengan valid
    When Pengguna menekan tombol "Simpan"
    Then Tombol langsung berubah ke status disabled dengan animasi spinner
    And Sistem memproses request secara tunggal tanpa menduplikasi record di basis data
  ```

---

## 5. Kebutuhan Non-Fungsional (Non-Functional Requirements)

1. **Security & Zero Plaintext Credential Exposure**:
   - Seluruh PIN dan password wajib di-hash menggunakan `Hash::make()` (Bcrypt).
   - Kredensial sensitif dilarang tampil di DOM atau payload API (`$hidden`).
2. **Performance & Concurrency**:
   - Pengecekan stok dan penguncian transaksi atomik wajib selesai dalam waktu < 200ms.
3. **Database Integrity**:
   - Seluruh mutasi terkait wajib dibungkus dalam `DB::transaction()` untuk menjamin atomisitas (ACID).

---

## 6. Perubahan Skema Data & Model Basis Data

### Migrasi Basis Data (Jika Diperlukan):
| Tabel | Jenis Perubahan | Kolom / Indeks Baru | Tipe Data | Keterangan |
|---|---|---|---|---|
| `[nama_tabel]` | Tambah Kolom | `[kolom_baru]` | VARCHAR / UUID | ... |

---

## 7. Spesifikasi UI/UX & Ergonomi Bento Apple HIG

- **Modal Sheet Pop-Up**: Menggunakan Full Size XXL di desktop dan Bottom Sheet di mobile.
- **Sentuhan Ergonomis**: Touch target tombol minimal 48px, input font minimal 16px (mencegah auto-zoom iOS Safari).
- **No-Panic Microcopy**:
  > *"Tenang: Riwayat data Anda tetap aman dan transaksi sebelumnya tidak akan terhapus."*

---

## 8. Penanganan Kasus Tepi & Rollback (Edge Cases)

- Jika terjadi kegagalan jaringan di tengah proses: Transaksi di-rollback penuh tanpa data gantung.
- Jika nomor telepon/WhatsApp pelanggan tidak valid: Notifikasi eksternal gagal secara tenang (*graceful failure*) tanpa membatalkan transaksi utama di kasir.
```
