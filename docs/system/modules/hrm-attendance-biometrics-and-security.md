# Modul Presensi Biometrik Wajah, Geofencing, & Keamanan Anti-Fraud (HRM Attendance & Biometrics Engine)

> **Status:** PRODUCTION READY  
> **Domain Terkait:** `app/Domain/HRM/`, `app/Domain/HRM/Biometrics/`, `app/Domain/HRM/AttendanceService.php`, `app/Domain/HRM/AttendanceExceptionService.php`  
> **Tabel Basis Data:** `attendances`, `attendance_corrections`, `attendance_exceptions`, `locations`, `business_users`, `audit_logs`  
> **Antarmuka Utama:** `/portal` (`resources/views/app/portal/index.blade.php`), `/hrm?tab=attendance` (`resources/views/app/hrm/index.blade.php`)

---

## 1. Arsitektur Komputer Vision & Biometrik Wajah

Sistem presensi COOCA memisahkan secara tegas 4 konsep biometric komputer vision:

```
+---------------------+     +--------------------------+     +--------------------------+     +--------------------------+
| 1. Face Detection   | --> | 2. Liveness Detection    | --> | 3. Face Embedding Vector | --> | 4. Biometric Matching    |
| (Deteksi Keberadaan |     | (Tantangan Gerak Dinamis |     | (Ekstraksi Vektor 128-D  |     | (Cosine Similarity       |
| Wajah di Kamera)    |     | Kepala Acak & Anti-Foto) |     | Luminance Gradient)      |     | Ambang Batas >= 80%)     |
+---------------------+     +--------------------------+     +--------------------------+     +--------------------------+
```

### 1.1 Active Liveness Challenge Engine
1. **Dynamic Randomization Sequence:** Setiap sesi kamera mengacak 3 gerakan kepala dari himpunan `['left', 'right', 'up', 'down']` (misal: *Kiri $\to$ Atas $\to$ Kanan*).
2. **Optical Motion Tracking:** Client-side tracking menghitung diferensial optik pergerakan frame secara real-time. Jika frame statis (foto cetak/layar HP) atau video rekaman berulang disodorkan, sistem menolak dan meminta pengulangan gerakan.
3. **Pemberitahuan Manusiawi:** Instruksi disajikan dalam bahasa Indonesia interaktif ramah pengguna tanpa kode error teknis yang membingungkan.

### 1.2 Face Embedding & Cosine Similarity Matcher
1. **Ekstraksi Deskriptor 128-Dimensi:** Wajah dinormalisasi ke ukuran $64 \times 64$ piksel, dibagi ke dalam $8 \times 8$ grid spatial (64 blok). Setiap blok mengekstrak gradien luminans horizontal dan vertikal ($\Delta x, \Delta y$), menghasilkan vektor float 128-D.
2. **Normalisasi Vektor $L_2$:** Vektor dinormalisasi sehingga $\|\mathbf{v}\|_2 = 1.0$.
3. **Pencocokan Cosine Similarity:**
   $$\text{Similarity}(\mathbf{A}, \mathbf{B}) = \frac{\mathbf{A} \cdot \mathbf{B}}{\|\mathbf{A}\|_2 \|\mathbf{B}\|_2} = \sum_{i=1}^{128} A_i B_i$$
   - Ambang batas kelulusan: $\ge 0.80$ ($80\%$).
   - Vektor dienkripsi menggunakan **AES-256-CBC** sebelum disimpan ke kolom `face_biometric_template` pada tabel `business_users`.
   - Vektor biometric disembunyikan (`$hidden`) dari serialisasi JSON API / Eloquent.

### 1.3 Zero Permanent Photo Retention (Kepatuhan Privasi UU PDP & GDPR)
- Foto selfie dan frame kamera hanya didekode sementara di memori server atau file sementara (`sys_get_temp_dir()`).
- File sementara dijamin selalu dihapus pada blok `finally { @unlink($tempPath); }`.
- Kolom `clock_in_photo` dan `clock_out_photo` disetel `null`, mencegah risiko kebocoran data biometrik visual karyawan.

---

## 2. Geofencing & Anti-Fake GPS Security

### 2.1 Kalkulasi Jarak Geodesik Haversine
Perhitungan jarak antara koordinat GPS karyawan $(\text{lat}_1, \text{lon}_1)$ dan titik pusat outlet $(\text{lat}_2, \text{lon}_2)$ dihitung menggunakan formula Haversine:
$$a = \sin^2\left(\frac{\Delta\text{lat}}{2}\right) + \cos(\text{lat}_1)\cos(\text{lat}_2)\sin^2\left(\frac{\Delta\text{lon}}{2}\right)$$
$$c = 2 \cdot \text{atan2}\left(\sqrt{a}, \sqrt{1-a}\right)$$
$$d = R \cdot c \quad (R = 6.371.000\text{ meter})$$

### 2.2 Proteksi Akurasi Sinyal & Mock Location
- Ambang batas akurasi GPS: $\text{accuracy} \le 100\text{ meter}$.
- Permintaan presensi dengan $\text{accuracy} > 100\text{m}$ otomatis ditolak dengan pesan: *"Akurasi sinyal GPS perangkat Anda terlalu rendah (>100m) atau terdeteksi sinyal simulasi/palsu."*

### 2.3 Exception & Dispensasi Kerja (WFH / WFA / Perjalanan Dinas)
- Sistem mendukung izin khusus WFH (*Work From Home*), WFA (*Work From Anywhere*), dan Dinas Luar melalui tabel `attendance_exceptions`.
- Jika staf memiliki tiket dispensasi aktif berstatus `approved` pada tanggal berjalan, validasi radius geofence dilewati secara aman dan status tercatat sebagai `free_location`.

---

## 3. Konkurensi & Anti-Race Condition

```
Client Rapid Clicks (3x)
   │
   ├── Request 1 ──> DB Transaction + lockForUpdate() ──> Record Created ──> 200 OK
   │
   ├── Request 2 ──> Waits for Lock ──> Evaluates Existing Record ──> 422 Rejection (Sudah Clock-in)
   │
   └── Request 3 ──> Waits for Lock ──> Evaluates Existing Record ──> 422 Rejection (Sudah Clock-in)
```

1. **Pessimistic Locking (`lockForUpdate()`):** Seluruh proses clock-in dan clock-out dibungkus dalam `DB::transaction()` dengan pessimistic row lock pada kombinasi `(business_id, user_id, date)`.
2. **Authoritative Server Time Berbasis Timezone Outlet:** Penentuan jam masuk/keluar, keterlambatan (*late minutes*), dan lembur (*overtime minutes*) 100% dihitung menggunakan jam server yang dikonversi ke timezone spesifik outlet/cabang (`TimezoneHelper::resolve($business, $targetLocation)`: WIB, WITA, WIT), kebal manipulasi jam pada smartphone klien ataupun perbedaan timezone server hosting.
3. **Audit Trail Imutabel:** Setiap aktivitas presensi dicatat ke tabel `audit_logs` dengan informasi timestamp server, jarak ke outlet, skor similarity wajah, status, dan IP address.

---

## 4. Alur Kerja Koreksi Presensi (Attendance Correction Workflow)

1. **Pengajuan Tiket Mandiri:** Karyawan dapat mengajukan tiket koreksi jika lupa presensi atau terkendala teknis melalui rute `/portal/corrections/store`.
2. **Otorisasi Manajer/Owner:** Hanya pengguna dengan hak akses `users.manage` yang dapat menyetujui (`hrm.attendance.corrections.approve`) atau menolak (`hrm.attendance.corrections.reject`) tiket koreksi.
3. **Anti-Self-Approval Enforcement:** Pengaju tidak dapat menyetujui tiket koreksinya sendiri.
4. **Sinkronisasi Data Otomatis:** Saat tiket disetujui, catatan `Attendance` disinkronkan secara otomatis dan diberi flag `is_corrected = true`.

---

## 5. Sistem Jam Kerja, Shift, Roster & Timezone Multi-Branch

Sistem presensi COOCA menghubungkan proses clock-in/out dengan jam kerja dan shift secara deterministik dan production-ready:

### 5.1 Pemisahan Tegas: Operating Hours Outlet vs Work Shift Karyawan
- **Operating Hours:** Menentukan jam buka dan tutup toko/outlet untuk operasional bisnis (misal 08:00 - 22:00).
- **Work Shift Karyawan:** Menentukan jadwal kewajiban hadir individu karyawan (misal Shift Pagi 08:00 - 16:00, Shift Siang 14:00 - 22:00, Shift Malam 22:00 - 06:00).
- **Karyawan Tanpa Shift (Bebas Jadwal):** Jika karyawan tidak memiliki jadwal kerja atau shift yang ditugaskan, sistem tidak mengarang jam kerja dari operating hours. Status tercatat sebagai `Bebas Jadwal / Tanpa Shift` tanpa keterlambatan palsu (`late_minutes = 0`).

### 5.2 Hirarki Penentuan Shift Aktif (Resolution Priority)
Sistem (`WorkScheduleService::resolveActiveShift`) menentukan shift aktif dengan urutan prioritas:
1. **Overnight Shift Lookback:** Jika waktu clock-in saat ini adalah dini hari (< 12:00) dan kemarin karyawan memiliki jadwal shift malam lintas hari (*overnight*), sesi presensi dihubungkan ke shift kemarin.
2. **Specific Date Schedule:** Roster tanggal spesifik (`specific_date`) untuk penugasan shift khusus atau lembur tanggal tertentu.
3. **Recurring Weekly Schedule:** Roster mingguan berdasarkan hari (`day_of_week`: Senin - Minggu) yang berada dalam rentang `effective_date` dan `end_date`.
4. **Employee Default Shift:** Shift default yang diset pada profil karyawan (`business_users.default_shift_id`).
5. **Bebas Jadwal (Unscheduled):** Mode tanpa jadwal baku.

### 5.3 Shift Overnight (Lintas Tengah Malam)
- Shift seperti 22:00 - 06:00 (+1 hari) ditandai dengan flag `is_overnight = true` atau deteksi otomatis ($start\_time > end\_time$).
- Karyawan clock-in pada 05 Okt 22:00 dan clock-out pada 06 Okt 06:00 dicatat sebagai **satu sesi presensi tunggal** pada tanggal 05 Okt, bukan dua hari terpisah.

### 5.4 Grace Period & Kalkulasi Keterlambatan Presisi
- Grace period dapat dikonfigurasi per shift (0, 5, 10, 15, 30 menit).
- Jika toleransi keterlambatan = 0, clock-in 1 menit setelah jam mulai langsung berstatus `late`.
- Jika clock-in melewati batas grace period, keterlambatan dihitung dari jam mulai kerja terjadwal (`scheduled_start_at`), bukan dari akhir batas grace period.

### 5.5 Hari Libur (OFF Day) Enforcement
- Jadwal roster mendukung flag `is_off_day = true`.
- Jika karyawan mencoba clock-in pada hari libur terjadwal, sistem menolak dengan pesan validasi yang jelas bahwa hari tersebut adalah hari libur (OFF).

### 5.6 Imutabilitas Riwayat Presensi (Historical Immutability)
- Setiap record `attendances` menyimpan snapshot permanen: `work_shift_id`, `shift_name`, `scheduled_start_at`, `scheduled_end_at`, `late_minutes`, `early_in_minutes`, dan `timezone`.
- Perubahan shift atau pergantian jadwal di kemudian hari tidak akan pernah memutasi data historis absensi yang telah terjadi.

### 5.7 Umpan Balik Instan Karyawan (Immediate Rich Feedback)
- Saat clock-in berhasil di UI Portal Karyawan (`/portal`) maupun REST API (`/api/v1/attendance/check-in` & `/api/v1/attendance/clock-in`), modal dialog Apple HIG langsung menampilkan status kehadiran (`TEPAT WAKTU`, `TERLAMBAT`, `LEBIH AWAL`), jam masuk aktual, jam jadwal masuk, dan durasi keterlambatan secara transparan.

