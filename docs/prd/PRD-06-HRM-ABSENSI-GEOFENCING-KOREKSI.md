# PRD-06: HRM Presensi Geofencing, Mode Bebas Lokasi & Tiket Perbaikan Absensi

**ID Dokumen:** `PRD-06-HRM-ATTENDANCE`  
**Modul:** SDM (HRM), Presensi Geofencing, Tiket Koreksi & Otomasi Payroll  
**Penanggung Jawab:** Principal Full-Stack Engineer & HRM Domain Architect  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Karyawan Toko/Outlet, Sales Canvasser/Lapangan, Manajer HRD, Pemilik Bisnis  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **Controller HRM:** [`app/Http/Controllers/Web/Hrm/HrmWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/Hrm/HrmWebController.php) (508 baris)
* **Mesin Penggajian:** [`app/Domain/HRM/PayrollCalculationService.php`](file:///c:/laragon/www/cooca_core/app/Domain/HRM/PayrollCalculationService.php), [`app/Domain/HRM/PayrollRunService.php`](file:///c:/laragon/www/cooca_core/app/Domain/HRM/PayrollRunService.php)
* **Model:** [`BusinessMembership.php`](file:///c:/laragon/www/cooca_core/app/Models/BusinessMembership.php), [`Payroll.php`](file:///c:/laragon/www/cooca_core/app/Models/Payroll.php), [`PayrollItem.php`](file:///c:/laragon/www/cooca_core/app/Models/PayrollItem.php), [`Location.php`](file:///c:/laragon/www/cooca_core/app/Models/Location.php)
* **View HRM:** [`resources/views/app/hrm/index.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/app/hrm/index.blade.php) (memiliki tab Karyawan, Payroll, dan Kasbon)

### B. Temuan & Masalah Sistem Existing
1. Modul HRM saat ini sudah sangat bagus dalam hal: data master karyawan, penetapan gaji pokok & tunjangan, kasbon/pinjaman terpotong otomatis, dan kalkulasi BPJS/PPh 21 bulanan.
2. **Ketiadaan Modul Presensi Harian (Attendance Gap):**
   - Tidak ada fitur pencatatan absensi masuk (*clock-in*) dan absensi pulang (*clock-out*).
   - Pemilik toko masih harus menginput jumlah hari kehadiran staf secara manual setiap kali menjalankan batch penggajian bulanan.
   - Tidak ada validasi lokasi kerja: rawan karyawan melakukan absensi palsu dari rumah saat mereka seharusnya berada di gerai toko.
   - Tidak ada mekanisme bagi staf lapangan (sales canvasser / kurir) yang membutuhkan absensi fleksibel bebas lokasi.
   - Tidak ada sistem tiket resmi jika karyawan lupa tap absensi pulang atau baterai ponsel mati, sehingga pemotongan gaji sering memicu konflik internal.

---

## 2. Perubahan & Penambahan Sistem (To-Be State)

### A. Dua Mode Kebijakan Presensi (Location Policy)
1. **Mode Geofenced (Wajib di Radius Kantor):**
   - Untuk kasir, barista, pelayan resto, koki dapur, staf gudang, mekanik bengkel.
   - Titik kantor ditetapkan di tabel `locations` (`latitude`, `longitude`, `geofence_radius_meters`).
   - Rumus **Haversine** di server PHP memverifikasi jarak perangkat karyawan vs koordinat kantor.
   - Jika jarak > radius kantor: **Absensi ditolak seketika**.
2. **Mode Bebas Lokasi (Free Location):**
   - Untuk sales canvasser, kurir logistik, supir armada, teknisi servis luar, staf WFH/remote.
   - Ditetapkan di profil karyawan (`business_users.attendance_mode = 'free'`).
   - Bebas absen dari mana saja; sistem tetap merekam titik koordinat GPS riil, alamat peta (*reverse geocoding*), dan stempel waktu server untuk transparansi audit.

---

### B. Skema Database Database Migrations

```sql
-- 1. Penambahan Koordinat Geofence pada Master Cabang (locations)
ALTER TABLE locations 
    ADD COLUMN latitude DECIMAL(10, 7) NULL AFTER address,
    ADD COLUMN longitude DECIMAL(10, 7) NULL AFTER latitude,
    ADD COLUMN geofence_radius_meters INT UNSIGNED NOT NULL DEFAULT 50 AFTER longitude;

-- 2. Penambahan Mode Presensi pada Profil Karyawan (business_users)
ALTER TABLE business_users 
    ADD COLUMN attendance_mode VARCHAR(20) NOT NULL DEFAULT 'geofenced' AFTER primary_location_id; 
    -- Nilai: 'geofenced' (Wajib di kantor), 'free' (Bebas lokasi)

-- 3. Tabel Master Rekap Kehadiran Harian (attendances)
CREATE TABLE attendances (
    id CHAR(36) PRIMARY KEY,
    business_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    location_id CHAR(36) NULL,
    date DATE NOT NULL,
    
    clock_in_at DATETIME NULL,
    clock_in_lat DECIMAL(10, 7) NULL,
    clock_in_lng DECIMAL(10, 7) NULL,
    clock_in_distance_meters INT UNSIGNED NULL,
    clock_in_address TEXT NULL,
    clock_in_photo VARCHAR(255) NULL,
    clock_in_status VARCHAR(20) NOT NULL DEFAULT 'on_time', -- 'on_time', 'late', 'free_location'
    clock_in_notes VARCHAR(255) NULL,
    
    clock_out_at DATETIME NULL,
    clock_out_lat DECIMAL(10, 7) NULL,
    clock_out_lng DECIMAL(10, 7) NULL,
    clock_out_distance_meters INT UNSIGNED NULL,
    clock_out_address TEXT NULL,
    clock_out_photo VARCHAR(255) NULL,
    clock_out_status VARCHAR(20) NOT NULL DEFAULT 'normal', -- 'normal', 'early_leave', 'overtime', 'free_location'
    clock_out_notes VARCHAR(255) NULL,
    
    work_duration_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    late_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    early_leave_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    overtime_minutes INT UNSIGNED NOT NULL DEFAULT 0,
    
    status VARCHAR(20) NOT NULL DEFAULT 'present', -- 'present', 'late', 'half_day', 'absent', 'leave', 'sick'
    is_corrected BOOLEAN NOT NULL DEFAULT FALSE,
    attendance_correction_id CHAR(36) NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    CONSTRAINT fk_att_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_location FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL,
    UNIQUE KEY uk_business_user_date (business_id, user_id, date),
    INDEX idx_att_lookup (business_id, date, status)
);

-- 4. Tabel Permohonan Tiket Perbaikan Absensi (attendance_corrections)
CREATE TABLE attendance_corrections (
    id CHAR(36) PRIMARY KEY,
    business_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,
    attendance_id CHAR(36) NULL,
    correction_number VARCHAR(64) NOT NULL,
    target_date DATE NOT NULL,
    
    correction_type VARCHAR(30) NOT NULL, -- 'clock_in_only', 'clock_out_only', 'full_day', 'status_only'
    proposed_clock_in TIME NULL,
    proposed_clock_out TIME NULL,
    proposed_status VARCHAR(20) NOT NULL DEFAULT 'present',
    
    reason TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    reviewed_by CHAR(36) NULL,
    reviewed_at DATETIME NULL,
    review_notes TEXT NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    CONSTRAINT fk_att_cor_business FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_cor_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_cor_att FOREIGN KEY (attendance_id) REFERENCES attendances(id) ON DELETE SET NULL,
    CONSTRAINT fk_att_cor_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY uk_business_cor_number (business_id, correction_number),
    INDEX idx_att_cor_status (business_id, status)
);
```

---

## 3. Alur Kerja Tiket Perbaikan Absensi (Attendance Correction)

```
[ KARYAWAN LUPA CLOCK-OUT / KENDALA TEKNIS ]
                   │
                   ▼
[ KARYAWAN ISI TIKET PERBAIKAN DI TAB HRM ]
  • Pilih tanggal kejadian
  • Pilih jenis: [ Lupa Clock-Out ]
  • Masukkan jam yang seharusnya (cth: 18:00)
  • Tulis alasan & lampirkan foto bukti
                   │
                   ▼
[ TIKET TERSIMPAN STATUS: PENDING ]
                   │
                   ▼
[ ATASAN / HRD MENERIMA NOTIFIKASI DI APPROVAL INBOX ]
  • Memeriksa Visual Diff: Jam Sistem vs Jam Pengajuan
  • Memeriksa alasan tertulis & bukti foto
                   │
           ┌───────┴───────┐
           │               │
       [ APPROVE ]     [ REJECT ]
           │               │
           │               ▼
           │     [ Tiket Ditolak dengan Alasan ]
           ▼
[ SINKRONISASI OTOMATIS KE ATTENDANCES & PAYROLL ]
  1. Record absensi diperbarui dengan status 'is_corrected = true'.
  2. Durasi kerja & lembur dihitung ulang otomatis.
  3. PayrollRunService otomatis menyerap data kehadiran bersih.
  4. Kirim notifikasi WhatsApp ke staf: "Tiket koreksi disetujui".
```

---

## 4. Fitur Keamanan Anti-Fraud Presensi
1. **Server-Side Timestamp:** Jam absensi mutlak menggunakan waktu server (`now()`), kebal manipulasi jam HP lokal.
2. **Pendeteksi Fake GPS:** Memeriksa atribut `accuracy` peramban. Jika akurasi > 100 meter (sinyal GPS simulasi/palsu), presensi ditolak dan diminta mengaktifkan sensor presisi tinggi.
3. **Perekaman Wajah (Selfie Camera):** Mengambil jepretan kamera depan saat tombol clock-in ditekan guna mencegah joki absensi antar rekan kerja.

---

## 5. Kriteria Keberhasilan (Definition of Done)
1. Karyawan dengan mode `geofenced` ditolak absensinya jika berada di luar radius lokasi cabang yang disetting.
2. Karyawan dengan mode `free` dapat absen dari mana saja dan koordinat GPS tercatat transparan.
3. Pengajuan tiket koreksi absensi yang disetujui atasan otomatis memulihkan jam kerja dan menghapus potongan denda absensi di modul Payroll.
4. Semua data absensi tersimpan dengan aman per tenant (*tenant-isolated*).
