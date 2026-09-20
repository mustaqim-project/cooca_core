# PRD-04: Tata Kelola Dokumen Maker – Multi Approver – Releaser (MAR)

**ID Dokumen:** `PRD-04-GOVERNANCE-MAR`  
**Modul:** Tata Kelola Transaksi, Otorisasi Bertingkat & Pencegahan Fraud Internal  
**Penanggung Jawab:** Principal Enterprise Architect & Security Engineer  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Staf Pembuat Dokumen (Maker), Supervisor/Manajer/Direksi (Approver), Bendahara/Kasir (Releaser)  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **Model Otorisasi POS:** [`app/Models/PosApprovalRequest.php`](file:///c:/laragon/www/cooca_core/app/Models/PosApprovalRequest.php) (ada untuk diskon dan void kasir)
* **Dokumen Pengadaan:** [`app/Models/PurchaseOrder.php`](file:///c:/laragon/www/cooca_core/app/Models/PurchaseOrder.php), [`app/Models/SupplierInvoice.php`](file:///c:/laragon/www/cooca_core/app/Models/SupplierInvoice.php)
* **Dokumen Pengeluaran:** [`app/Models/Expense.php`](file:///c:/laragon/www/cooca_core/app/Models/Expense.php)

### B. Temuan & Masalah Sistem Existing
1. Di sistem existing, dokumen PO, Tagihan Supplier, dan Pengeluaran Biaya menggunakan hak akses tunggal (*flat permission*).
   - Pengguna dengan izin `purchasing.manage` atau `expenses.manage` bisa langsung membuat dan mencairkan dana sekaligus tanpa verifikasi pihak kedua.
2. Untuk UMKM kecil dengan 2–3 staf, alur cepat tanpa approval ini sangat cocok dan menghemat waktu.
3. Namun untuk **KORPORASI / Bisnis Berkembang**, ketiadaan pemisahan tugas (*Segregation of Duties - SoD*) membuka celah kebocoran dana perusahaan yang sangat berbahaya:
   - Staf pembelian bisa membuat PO fiktif dan langsung menyetujuinya sendiri.
   - Tidak ada verifikasi bertingkat (*multi-level approval*) untuk transaksi nominal besar di atas plafon tertentu (misal: pengeluaran di atas Rp 50.000.000 wajib persetujuan Direktur Utama).

---

## 2. Perubahan & Penambahan Sistem (To-Be State)

### A. Tiga Peran Kerja (Tri-Persona Governance)
1. **Maker (Pembuat Draf):**  
   Staf operasional / staf gudang yang menginput draf PO, mencatat rencana pengeluaran, atau mengajukan retur barang. Dokumen berstatus `Draft` / `Pending Approval`.
2. **Approver (Penyetuju Bertingkat 1–3 Level):**  
   - **Level 1 (Supervisor):** Memeriksa kelayakan fisik dan kebutuhan barang.
   - **Level 2 (Manajer Keuangan):** Memeriksa ketersediaan anggaran kas (*budget check*).
   - **Level 3 (Direktur / Owner):** Wajib jika transaksi melebihi batas nominal ambang (*threshold*, misal > Rp 50 juta).
3. **Releaser (Pencair / Eksekutor):**  
   Staf kasir bendahara (*Treasury*) yang bertugas mengeksekusi transfer bank atau staf logistik yang melepas barang keluar dari gudang setelah seluruh persetujuan lengkap.

---

### B. Skema Database Workflow Persetujuan

```sql
-- 1. Aturan Persetujuan Berdasarkan Plafon Nominal
CREATE TABLE approval_rules (
    id CHAR(36) PRIMARY KEY,
    business_id CHAR(36) NOT NULL,
    document_type VARCHAR(50) NOT NULL, -- 'purchase_order', 'supplier_bill', 'expense', 'sales_return'
    min_amount DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    max_amount DECIMAL(15, 2) NULL, -- NULL = tanpa batas atas
    required_levels INT UNSIGNED NOT NULL DEFAULT 1,
    approver_role_level_1 CHAR(36) NOT NULL, -- Role ID Supervisor
    approver_role_level_2 CHAR(36) NULL,     -- Role ID Manajer
    approver_role_level_3 CHAR(36) NULL,     -- Role ID Direktur
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    CONSTRAINT fk_app_rules_biz FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE
);

-- 2. Tiket Permohonan Persetujuan Dokumen
CREATE TABLE approval_requests (
    id CHAR(36) PRIMARY KEY,
    business_id CHAR(36) NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    document_id CHAR(36) NOT NULL,
    requester_id CHAR(36) NOT NULL, -- Maker
    current_level INT UNSIGNED NOT NULL DEFAULT 1,
    total_levels INT UNSIGNED NOT NULL DEFAULT 1,
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_app_req (business_id, document_type, status)
);

-- 3. Rekam Jejak Persetujuan (Audit Trail Approval)
CREATE TABLE approval_logs (
    id CHAR(36) PRIMARY KEY,
    approval_request_id CHAR(36) NOT NULL,
    level INT UNSIGNED NOT NULL,
    approver_id CHAR(36) NOT NULL,
    action VARCHAR(20) NOT NULL, -- 'approved', 'rejected'
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    
    CONSTRAINT fk_app_log_req FOREIGN KEY (approval_request_id) REFERENCES approval_requests(id) ON DELETE CASCADE
);
```

---

## 3. Desain Antarmuka Stepper Apple HIG & Approval Inbox

1. **Visual Stepper pada Dokumen Transaksi:**
   Pada bagian atas detail PO / Pengeluaran, disematkan *horizontal progress tracker* Apple HIG:
   ```
   [ ✓ Dibuat oleh Doni ] ──► [ ✓ Level 1: Disetujui Manajer ] ──► [ ⏳ Menunggu Direktur ] ──► [ Belum Dicairkan ]
   ```
2. **Pusat Kotak Masuk Persetujuan (Approval Inbox):**  
   Menu terpadu di Topbar / Navigasi untuk pimpinan. Menampilkan daftar tiket yang menunggu persetujuan mereka, dilengkapi rincian nominal, alasan pengajuan, dan tombol aksi 1-klik: **`[ Setujui ]`** atau **`[ Tolak ]`** (wajib menyertakan catatan penolakan tertulis).

---

## 4. Alur Kerja Tata Kelola MAR

```
[ STAF GUDANG / MAKER ]
Membuat Draf PO Senilai Rp 75.000.000
           │
           ▼
[ SISTEM MEMERIKSA approval_rules ]
Nominal > Rp 50.000.000 ──► Wajib 2 Level Persetujuan
           │
           ▼
[ APPROVER LEVEL 1: SUPERVISOR ]
Menerima notifikasi di Inbox & WhatsApp ──► KLIK [ APPROVE ]
           │
           ▼
[ APPROVER LEVEL 2: DIREKTUR UTAMA ]
Menerima notifikasi eskalasi ──► KLIK [ APPROVE ]
           │
           ▼
[ STATUS DOKUMEN: SIAP DICATAT / APPROVED ]
           │
           ▼
[ RELEASER: BENDAHARA KAS ]
Menerima perintah bayar ──► Transfer Bank & Tandai [ RELEASED ]
           │
           ▼
[ AutoJournalService ] ──► Catat jurnal pengeluaran kas resmi
```

---

## 5. Kriteria Keberhasilan (Definition of Done)
1. Dokumen yang terkena aturan persetujuan tidak dapat diubah statusnya menjadi bayar/cair sebelum disetujui semua level.
2. Setiap penolakan dokumen wajib disertai alasan tertulis yang dapat dibaca oleh pembuat draf (*Maker*).
3. Notifikasi persetujuan terkirim instan ke kotak masuk pengguna yang berhak menyetujui.
4. Jejak approval (siapa menyetujui, jam berapa, catatan) terekam permanen dan tidak dapat dihapus.
