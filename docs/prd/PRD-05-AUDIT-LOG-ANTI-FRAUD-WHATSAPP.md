# PRD-05: Audit Log Explorer & Deteksi Anti-Fraud dengan Notifikasi Real-Time WhatsApp

**ID Dokumen:** `PRD-05-SECURITY-AUDIT-FRAUD`  
**Modul:** Keamanan Sistem, Kepatuhan Audit & Deteksi Fraud Internal  
**Penanggung Jawab:** Principal Security Auditor & Backend Architect  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Pemilik Bisnis (Owner), Direktur Operasional, Auditor Eksternal/Internal  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **Model Log Audit:** [`app/Models/AuditLog.php`](file:///c:/laragon/www/cooca_core/app/Models/AuditLog.php)
* **Trait Pencatat Otomatis:** [`app/Models/Traits/Auditable.php`](file:///c:/laragon/www/cooca_core/app/Models/Traits/Auditable.php)
* **Tabel Database:** `audit_logs` (kolom `user_id`, `business_id`, `event`, `auditable_type`, `auditable_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`)

### B. Temuan & Masalah Sistem Existing
1. **Perekaman Backend Sudah Aktif:**  
   Backend COOCA sebenarnya **sudah mencatat setiap perubahan data penting** (event: `created`, `updated`, `deleted`) lengkap dengan nilai sebelum (`old_values`) dan sesudah (`new_values`) dalam format JSON di database.
2. **Ketiadaan Antarmuka Visual (UI):**  
   Data log berharga ini tidak memiliki antarmuka tampilan di web! Pemilik bisnis tidak bisa melihat siapa yang mengubah harga produk, siapa yang menghapus struk kasir, atau dari IP address mana login dilakukan.
3. **Ketiadaan Klasifikasi Risiko & Peringatan Dini (Alerting):**  
   Tidak ada klasifikasi tingkat bahaya (Tinggi, Sedang, Rendah). Tindakan berbahaya seperti **pembatalan pesanan kasir (*void order*)** atau **perubahan nomor rekening bank supplier** terjadi secara diam-diam tanpa ada notifikasi langsung ke ponsel pemilik bisnis.

---

## 2. Perubahan & Penambahan Sistem (To-Be State)

### A. Halaman Penjelajah Jejak Audit (Audit Trail Explorer UI)
* **Lokasi Rute:** `/settings/audit-logs` (di bawah grup `PENGATURAN USAHA`).
* **Fitur Utama:**
  1. **Filter Pencarian Multi-Kriteria:** Filter berdasarkan Tanggal, Pelaku (Nama Pengguna / Karyawan), Modul (Kasir POS, Stok, Keuangan, Karyawan), dan Tingkat Risiko (Tinggi, Sedang, Rendah).
  2. **Visual Diff Viewer (Old vs. New):**  
     Modal Sheet Apple HIG yang menampilkan perbandingan visual jelas:
     - Teks merah dicoret: Nilai data lama (`old_value`).
     - Teks hijau tebal: Nilai data baru yang diubah (`new_value`).
  3. **Identitas Forensik Digital:** Menampilkan Alamat IP pengguna, jenis peramban/perangkat (*User-Agent*), dan stempel waktu atom.

---

### B. Matriks Klasifikasi Risiko Fraud & Notifikasi WhatsApp Real-Time

Sistem membagi aktivitas bisnis menjadi 3 tingkat risiko:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                        MATRIKS RISIKO FRAUD & ALERTING                                 │
├─────────┬───────────────────────────────────────────────┬──────────────────────────────┤
│ RISIKO  │ JENIS KEGIATAN SPESIFIK                       │ RESPON SISTEM                │
├─────────┼───────────────────────────────────────────────┼──────────────────────────────┤
│ 🔴 TINGGI│ • Pembatalan Pesanan Kasir (Void Order)       │ 1. Beri badge merah menyala  │
│ (HIGH)  │ • Perubahan Nomor Rekening Bank Supplier      │ 2. Kirim notifikasi seketika │
│         │ • Pemberian Diskon Kasir di atas 20%          │    ke WhatsApp Owner pribadi │
│         │ • Perubahan Hak Akses / Role Pengguna         │ 3. Wajibkan catatan alasan   │
│         │ • Penghapusan Transaksi Jurnal Manual         │                              │
│         │ • Selisih Stok Opname Minus bernilai besar    │                              │
├─────────┼───────────────────────────────────────────────┼──────────────────────────────┤
│ 🟡 SEDANG│ • Penyesuaian Stok (Stock Adjustment kecil)   │ Dicatat di tabel log audit   │
│ (MEDIUM)│ • Perubahan Harga Jual Produk                 │ dengan badge kuning amber    │
│         │ • Pengeditan Data Pelanggan CRM               │                              │
├─────────┼───────────────────────────────────────────────┼──────────────────────────────┤
│ 🟢 RENDAH│ • Pembuatan Transaksi POS Normal              │ Dicatat sebagai audit trail  │
│ (LOW)   │ • Pembuatan Pesanan Pembelian (PO) Biasa      │ reguler tanpa peringatan WA  │
│         │ • Clock-In / Clock-Out Absensi Staf           │                              │
└─────────┴───────────────────────────────────────────────┴──────────────────────────────┘
```

---

### C. Format Pesan WhatsApp Security Alert kepada Owner

Jika terjadi aksi berisiko tinggi, mesin WhatsApp Gateway COOCA mengirimkan pesan format resmi:

```text
🚨 [PERINGATAN KEAMANAN COOCA] 🚨

Halo Bapak/Ibu Owner,
Sistem mendeteksi aktivitas berisiko tinggi pada bisnis Anda:

• Aktivitas: Pembatalan Pesanan Kasir (Void Order)
• Dokumen: Struk #POS-202609-0042
• Nilai Transaksi: Rp 450.000
• Dilakukan Oleh: Kasir Rian (Kasir 1)
• Waktu: 20 September 2026, 14:23 WIB
• Lokasi: Gerai Sudirman (IP: 182.253.11.4)
• Alasan Diinput: "Pelanggan salah pesan makanan"

Silakan periksa detail jejak audit di:
https://cooca.id/settings/audit-logs
```

---

## 3. Alur Kerja Deteksi Fraud

```
[ STAF MELAKUKAN AKSI KRITIS ]
(Contoh: Kasir membatalkan struk pembayaran Rp 500.000)
              │
              ▼
[ Auditable Trait & Model Observer ]
Merekam old_values dan new_values ke tabel audit_logs
              │
              ▼
[ FraudDetectionService::evaluateRisk() ]
Memeriksa aturan: Apakah event masuk kategori RISIKO TINGGI?
              │
       ┌──────┴──────┐
       │             │
   [ YA ]          [ TIDAK ]
       │             │
       │             ▼
       │      [ Catat Log Selesai ]
       ▼
[ TRIGGER REAL-TIME ACTIONS ]
  1. Tandai log dengan risk_level = 'high'.
  2. Susun pesan template WhatsApp keamanan.
  3. Kirim via WhatsApp Engine langsung ke nomor Owner.
  4. Munculkan notifikasi lonceng merah di Dashboard Utama.
```

---

## 4. Kriteria Keberhasilan (Definition of Done)
1. Halaman `/settings/audit-logs` dapat diakses oleh Owner dan menampilkan riwayat perubahan data secara kronologis.
2. Modal *Visual Diff Viewer* menampilkan teks lama (merah coret) dan teks baru (hijau) dengan presisi.
3. Aksi berisiko tinggi (void kasir, ubah nomor rekening bank supplier, hapus jurnal) memicu notifikasi WhatsApp ke Owner dalam waktu < 5 detik.
4. Tabel `audit_logs` terlindungi secara mutlak: tidak ada peran pengguna apa pun (termasuk admin) yang dapat menghapus atau mengedit baris log audit (*Append-Only Immutable Table*).
