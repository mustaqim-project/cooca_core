# Modul Diagnostik Sistem & Pemantauan Error Log (System Diagnostics & Error Logs)

> **Status:** COMPLETE  
> **Domain Terkait:** `app/Http/Controllers/Admin/AdminErrorLogController.php`, `resources/views/admin/error-logs/`  
> **Aktor Utama:** Superadmin (Platform Operations)  
> **Rute Basis:** `/admin/error-logs` (`routes/admin.php`)  
> **Terakhir Diverifikasi:** 2026-09-16 (100% Passed Automated Tests)

---

## 1. Tujuan & Nilai Bisnis

Modul Diagnostik Sistem & Error Log menyediakan fasilitas observabilitas dan telemetri langsung (*real-time system observability*) bagi Superadministrator Cooca. Modul ini memungkinkan tim rekayasa dan operasional platform untuk:
1. **Deteksi Cepat Insiden Runtime (*Instant MTTR Reduction*):** Mengidentifikasi *exception*, kegagalan koneksi basis data, kesalahan integrasi API pihak ketiga (seperti WhatsApp Gateway atau Google OAuth), dan peringatan ambang batas memori tanpa perlu akses SSH ke server produksi.
2. **Diagnostik Ramah Pengguna Berbasis Bento UI Apple HIG:** Menyajikan data teknis rumit (Monolog logs) ke dalam format visual modular yang terstruktur rapi, elegan, dan mudah dipahami, lengkap dengan indikator keparahan (Error, Warning, Info, Debug).
3. **Pemberdayaan Operasional Mandiri:** Memfasilitasi unduhan berkas mentah untuk analisis forensik lanjut dan pembersihan berkas log (*log file clear*) dengan perlindungan konfirmasi modal sheet.

---

## 2. Arsitektur & Aliran Data

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       STORAGE LOG DIRECTORY (storage/logs/)                 │
│         • laravel.log   • worker.log   • audit-*.log   • custom-*.log       │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │ (SplFileObject Streaming / 3000 Lines)
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                 AdminErrorLogController (App\Http\Controllers\Admin)        │
│   • Path Traversal Shield (basename)   • Regex Parser (Monolog Standard)    │
│   • Memory-Safe Bounded Window         • Pre-calculated KPI Aggregations    │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │
                    ┌──────────────────┴──────────────────┐
                    ▼                                     ▼
┌──────────────────────────────────────┐  ┌───────────────────────────────────┐
│     BENTO DASHBOARD VIEW (BLADE)     │  │        JSON TELEMETRY API         │
│   • Bento KPI Cards (Adaptive 2/4)   │  │   • Live Refresh Polling (10s)    │
│   • Filter Level & Pencarian Fulltext│  │   • Endpoint `wantsJson()` ready  │
│   • Collapsible Stack Trace          │  │   • Multi-Format Response         │
│   • Modal Sheet Konfirmasi Hapus     │  └───────────────────────────────────┘
└──────────────────────────────────────┘
```

---

## 3. Fitur-Fitur Utama Modul

### 3.1 Rotasi Log Harian Otomatis & Retensi 30 Hari (*Daily Log Rotation & Retention*)
* **Konfigurasi Saluran Log Harian:** Saluran default logging diatur ke driver `daily` (`LOG_CHANNEL=daily`, `LOG_STACK=daily`, `LOG_DAILY_DAYS=30`) pada `config/logging.php` dan `.env`.
* **Format Berkas Tanggal Monolog:** Setiap hari, sistem secara otonom memutar berkas log dengan pola penamaan kalender `storage/logs/laravel-YYYY-MM-DD.log` (contoh: `laravel-2026-09-16.log`).
* **Siklus Pembersihan Otomatis:** Berkas log yang lebih tua dari batas retensi (default 30 hari) dihapus otomatis oleh Monolog `RotatingFileHandler`, mencegah kepenuhan ruang simpan server produksi.
* **Resolusi Berkas Hari Ini (*Today-First Resolution*):** `AdminErrorLogController` selalu memastikan berkas log hari ini tersedia dalam dropdown dan terpilih secara default, sehingga Superadmin langsung melihat status runtime hari berjalan (*0 error clean state* atau insiden terkini).
* **Label Humanized Dropdown:** Berkas disajikan dengan label yang ramah pengguna Boomer & non-teknis:
  - `📅 Hari Ini - 16 Sep 2026 (laravel-2026-09-16.log)`
  - `📅 Kemarin - 15 Sep 2026 (laravel-2026-09-15.log)`
  - `📁 14 Sep 2026 (laravel-2026-09-14.log)`
* **IDOR & Path Traversal Shield:** Parameter nama berkas dari permintaan HTTP wajib disanitasi menggunakan `basename()` dan divalidasi terhadap daftar berkas resmi yang tersedia. Upaya memanipulasi parameter seperti `../../etc/passwd` dicegah secara mutlak.

### 3.2 Pemrosesan Aliran Memori Aman (*Memory-Safe Streaming Bounds*)
* Berkas log server dapat berukuran puluhan hingga ratusan megabyte.
* Parser log memanfaatkan pointer `\SplFileObject::seek()` untuk membatasi pembacaan pada rentang **3.000 baris terakhir**:
  ```php
  $file = new \SplFileObject($filePath, 'r');
  $file->seek(PHP_INT_MAX);
  $totalLines = $file->key();
  $startLine = max(0, $totalLines - 3000);
  $file->seek($startLine);
  ```
* Menjamin konsumsi memori PHP tetap stabil di bawah 16MB dan mencegah risiko *Out-of-Memory (OOM)* pada server produksi.

### 3.3 Penataan Logika & Standar Monolog
* Ekstraksi baris log mematuhi pola resmi Monolog PSR-3:
  `^\[(?P<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<env>\w+)\.(?P<level>[A-Z]+): (?P<message>.*)`
* Baris berkelanjutan di bawah header log dikelompokkan secara cerdas ke dalam properti `stack` sebagai *diagnostic stack trace*.
* Log ditampilkan secara kronologis terbalik (*newest first*) sehingga peristiwa paling mutakhir langsung terlihat dalam 3 detik pertama.

### 3.4 Kartu Metrik Diagnostik Bento (Bento KPI Cards)
* **Total Baris Log:** Total entri log yang diproses dalam berkas aktif.
* **Errors & Critical:** Agregasi tingkat keparahan tinggi (`ERROR`, `CRITICAL`, `EMERGENCY`, `ALERT`) beraksen merah System Red Apple (`#FF3B30`).
* **Warnings:** Peringatan kinerja dan batasan sumber daya beraksen kuning/oranye System Orange (`#FF9500`).
* **Info & Debug:** Jejak audit, telemetri alur kerja, dan informasi sistem beraksen biru System Blue / Teal.

### 3.5 Pemfilteran Tingkat Lanjut & Pencarian Full-Text
* **Filter Level:** Menyaring instan berdasarkan keparahan (Semua, Error & Critical, Warning, Info, Debug).
* **Pencarian Kata Kunci:** Mencocokkan teks pada pesan error, nama berkas exception, nomor baris, maupun cuplikan stack trace.
* **Reset 1-Klik:** Mengembalikan filter ke kondisi default tanpa perlu me-refresh halaman secara manual.

### 3.6 Antarmuka Pengguna Apple HIG Bento UI
* **Salin Stack Trace 1-Sentuh:** Tombol *Salin Trace* menyalin seluruh jejak kode stack trace ke clipboard dengan indikator status hijau (*"Tersalin!"*).
* **Live Refresh Mode:** Tombol toggle live refresh (interval 10 detik) memanfaatkan `localStorage` untuk persistensi preferensi antar sesi penelusuran.
* **Modal Sheet Pembersihan Aman:** Aksi pengosongan isi berkas log dilindungi modal sheet bergaya iOS 18 dengan rekomendasi pengunduhan salinan cadangan sebelum dieksekusi.

---

## 4. Spesifikasi Teknis & Endpoint Rute

| Metode | Endpoint URL | Nama Rute | Deskripsi & Kontrak |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/error-logs` | `admin.error-logs.index` | Merender antarmuka indeks bento atau mengembalikan payload JSON jika `wantsJson()` bernilai true. |
| `GET` | `/admin/error-logs/download` | `admin.error-logs.download` | Mengunduh berkas fisik mentah dengan header `Content-Type: text/plain`. |
| `DELETE` | `/admin/error-logs/clear` | `admin.error-logs.clear` | Mengosongkan isi berkas log target tanpa menghapus file dari filesystem (`File::put($path, '')`). Dilindungi proteksi CSRF. |

---

## 5. Hak Akses & Matriks Keamanan

* **Middleware:** Dikelompokkan dalam grup rute berpelindung `auth:admin` (`App\Http\Middleware\Authenticate:admin`).
* **IDOR Shield:** Parameter berkas dibatasi ketat ke direktori `storage/logs/` yang sah.
* **CSRF Shield:** Aksi pembersihan log `DELETE` wajib menyertakan token `@csrf`.
* **Zero Silent Failure:** Jika berkas log tidak ditemukan, pengguna diarahkan kembali dengan notifikasi flash error yang jelas dan santun.

---

## 6. Verifikasi & Pengujian Otomatis

Modul ini diverifikasi melalui rangkaian pengujian unit dan fungsional di `tests/Feature/Admin/AdminErrorLogTest.php`:
* `test_unauthenticated_user_cannot_access_error_logs` (Proteksi autentikasi guard admin).
* `test_admin_can_access_error_logs_and_view_diagnostic_stats` (Verifikasi parsing metrik bento).
* `test_admin_can_filter_logs_by_level` (Verifikasi penyaringan tingkat keparahan).
* `test_admin_can_search_logs_by_keyword` (Verifikasi pencarian pesan dan exception).
* `test_admin_can_request_logs_as_json` (Verifikasi kontrak respons JSON telemetri).
* `test_admin_can_download_raw_log_file` (Verifikasi unduhan berkas teks aman).
* `test_admin_can_clear_log_file` (Verifikasi pengosongan berkas log).
* `test_invalid_file_name_falls_back_safely` (Verifikasi proteksi manipulasi parameter).
