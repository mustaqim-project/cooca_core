# Prosedur Penggabungan & Konsolidasi Pengetahuan Sistem (Consolidation Protocol)

Dokumen ini adalah instruksi operasional untuk **Fase 5 (Penggabungan & Konsolidasi Pengetahuan)** pada Skill `system-workflow-audit`. Tujuannya adalah memastikan hasil audit terintegrasi rapi ke seluruh dokumentasi resmi Cooca tanpa dokumen yatim (*orphaned files*), tanpa duplikasi, dan tanpa kontradiksi.

---

## 🗺️ 1. Prosedur Sinkronisasi ke `docs/system/INDEX.md`

`docs/system/INDEX.md` adalah indeks sentral Layer 2 yang merepresentasikan kondisi berjalan sistem saat ini (*Current State*).

### Langkah 1: Registrasi pada Peta Navigasi Pengetahuan
Tentukan kategori dokumen baru/yang diperbarui:
- **`### 2. Modul Sistem (Modules)`**: Jika dokumen mendefinisikan keseluruhan domain fungsional (misal: inventori, costing, POS, crm).
- **`### 3. Alur Kerja End-to-End (Workflows)`**: Jika dokumen mendefinisikan alur proses transaksi hulu-ke-hilir lintas modul (misal: alur kasir, alur pengadaan PO ➔ GR, transfer stok gudang).
- **`### 4. Aturan Bisnis (Business Rules)`**: Jika dokumen memuat kebijakan validasi, kalkulasi, atau pencegahan fraud.

Format penambahan baris:
```markdown
* [`docs/system/workflows/[file-slug].md`](file:///c:/laragon/www/cooca_core/docs/system/workflows/[file-slug].md) - [Ringkasan Padat 1 Baris dengan Alur Panah ➔]. `[COMPLETE]`
```

### Langkah 2: Pembaruan Tabel Matriks Status Dokumentasi
Perbarui atau tambahkan baris pada tabel `## 📊 Matriks Status Dokumentasi Sistem (Documentation Maturity Status)`:

| Modul / Domain | Status | Referensi Source Code / Migrasi | Terakhir Diverifikasi |
| :--- | :---: | :--- | :---: |
| **[Nama Modul / Fitur]** | `COMPLETE` | `[Path Berkas / Controller / Migrasi Terkait]` | YYYY-MM-DD |

**Panduan Pemilihan Status:**
- `DOCUMENTED`: Struktur dokumen lengkap tetapi belum diverifikasi terhadap basis data atau pengujian.
- `VERIFIED`: Telah diverifikasi terhadap logika kode aktual dan skema basis data.
- `COMPLETE`: Telah diverifikasi penuh, bebas kontradiksi, dan tervalidasi dengan pengujian otomatis (`php artisan test`).
- `NEEDS_REVIEW`: Ada perubahan kode terbaru yang memerlukan peninjauan ulang dokumentasi.
- `OUTDATED`: Implementasi telah berubah dan dokumentasi harus segera diperbarui.

---

## 📖 2. Prosedur Sinkronisasi ke `docs/SYSTEM_GUIDE.md`

`docs/SYSTEM_GUIDE.md` adalah buku induk kurasi tertinggi (Layer 3) yang dibaca oleh Pemilik Usaha, Developer, QA, dan AI Agent.

### Langkah 1: Perbarui Bab 3 (Panduan Pemilik Usaha)
- Tambahkan atau perbarui sub-bab operasional (contoh `3.3 Manajemen Stok & Penerimaan Bahan (Gudang & GR)`).
- Gunakan bahasa Indonesia yang ramah, hangat, dan mudah dipahami oleh pemilik usaha usia 40–65+ tahun (*Zero-Manual UI Philosophy*).
- Jelaskan:
  1. **Kapan Digunakan?** (Konteks bisnis riil).
  2. **Cara Kerjanya Langkah Demi Langkah** (1, 2, 3 langkah praktis di aplikasi).
  3. **Manfaat Nyata** (Pencegahan kecurangan, akurasi stok, ketenangan pikiran).

### Langkah 2: Perbarui Bab 4 (Panduan Rekayasa Developer & AI Agent)
- Tambahkan atau perbarui sub-bab teknis pada Bab 4 (contoh `4.18 Arsitektur Multi-Hierarchy Branch & Warehouse`).
- Dokumentasikan:
  1. Arsitektur data & model relasi Eloquent (`parent_id`, cascade rules, scopes).
  2. Aturan scoping tenant `Context::requireBusiness()`.
  3. Integrasi background jobs, event listener, dan notifikasi.
  4. Proteksi keamanan & guardrails anti-fraud internal.

### Langkah 3: Perbarui Bab 5 (Matriks Penelusuran Pengetahuan)
- Pastikan tabel penelusuran di Bab 5 menautkan topik terkait ke file dokumentasi Layer 2 di `docs/system/`.

---

## ⚖️ 3. Resolusi Konflik & Deduplikasi Dokumen (Anti-Duplication)

Saat melakukan audit, seringkali ditemukan dokumen lama yang membahas topik yang sama atau beririsan (misal: sudah ada `docs/system/workflows/multi-hierarchy-branch-and-warehouse-flow.md` saat mengaudit `resources/views/app/warehouse`).

**Aturan Penanganan:**
1. **DILARANG Membuat Dokumen Ganda**:
   Jangan membuat file baru dengan nama mirip (misal `warehouse-workflow.md` dan `gudang-flow.md`).
2. **Audit Perbandingan Kode vs Dokumen**:
   Bandingkan fakta di kode saat ini dengan isi dokumen yang sudah ada.
3. **Surgical Merge (Integrasi Bedah)**:
   - Jika dokumen lama valid namun kurang lengkap, lengkapi bagian yang hilang (tambahkan sequence diagram, perbarui skema tabel, tambahkan tabel eksekusi end-to-end).
   - Jika dokumen lama menyebutkan logika yang sudah usang (*deprecated*), perbarui teksnya agar mencerminkan kondisi kode aktual saat ini.
   - Cantumkan tanggal verifikasi terbaru pada header dokumen.

---

## 📝 4. Pencatatan Audit Trail ke `docs/AiWorkHistory.md`

Setiap kali audit dan dokumentasi selesai dikonsolidasikan, tambahkan entri terstruktur pada `docs/AiWorkHistory.md` dengan **7 komponen minimal**:

1. **Tanggal/waktu**: Tanggal eksekusi pekerjaan (YYYY-MM-DD).
2. **Tujuan pekerjaan**: Audit workflow modul [Nama Modul] dari folder [Path Folder] dan konsolidasi dokumentasi ke `docs/system/`.
3. **Hasil audit**: Ringkasan temuan alur, simpul integrasi, permissions, dan relasi database.
4. **Perbaikan / Dokumentasi yang Dihasilkan**: Rincian dokumen baru atau perubahan yang telah diintegrasikan.
5. **File/module yang diubah/dianalisis**: Daftar lengkap path berkas controller, model, view, route, migration, dan docs.
6. **Hasil pengujian / verifikasi**: Bukti verifikasi kode, linting, route list, atau pengujian otomatis.
7. **Catatan atau risiko yang masih tersisa**: Rekomendasi perbaikan arsitektur atau optimasi lanjutan.
