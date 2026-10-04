# Kebijakan Persetujuan & Gerbang Maker-Checker (Approval Gate)

> **Layer:** Human-in-the-Loop Governance, Risk Classification, & Authorization Policy  
> **Source:** `App\Domain\Ai\Policy\AiActionPolicy` & `App\Domain\Ai\Execution\AiActionExecutor`

---

## 1. Prinsip Utama Maker-Checker

Sesuai filosofi keamanan enterprise COOCA, AI tidak diizinkan memiliki otoritas mutlak untuk mengeksekusi mutasi basis data secara otonom tanpa pengawasan manusia.

Setiap mutasi bisnis (penerbitan faktur berbayar, pengiriman pesanan pembelian ke supplier, perubahan harga katalog, atau publikasi promosi eksternal) diperlakukan sebagai **Proposal Aksi (Action Proposal)**.

---

## 2. Klasifikasi Tingkat Risiko (Risk Classification)

| Tingkat Risiko | Kriteria | Memerlukan Persetujuan Manusia? | Wewenang Penyetuju |
| :--- | :--- | :---: | :--- |
| **Low** | Analisis data, ringkasan laporan, insight tren tanpa dampak finansial/eksternal. | **Tidak** (Auto-executable / Langsung ditampilkan) | Sistem / Semua Pengguna |
| **Medium** | Draft promosi marketing, draf postingan media sosial, template penawaran. | **Ya** (Bila mempublikasikan ke luar atau bernilai > Rp 500.000) | Owner, Admin, atau Supervisor |
| **High** | Penerbitan faktur tagihan pelanggan (Invoice), penerbitan PO ke supplier, pengeluaran kas. | **WAJIB** | Owner, Admin, atau Supervisor berizin |
| **Critical** | Penyesuaian stok manual volume besar, pembatalan piutang, perubahan harga produk massal. | **WAJIB KETAT** | Hanya Pemilik Usaha (Owner) |

---

## 3. Matriks Otorisasi Pengguna (`canApprove`)

Otorisasi dievaluasi secara berlapis oleh `AiActionPolicy::canApprove()`:
1. **Verifikasi Keanggotaan Bisnis (Membership Role):**
   - Pengguna harus terdaftar pada tabel `business_users` milik bisnis target.
   - Pengguna dengan peran `owner` atau `admin` secara otomatis memiliki wewenang mengeksekusi proposal di bisnisnya.
2. **Izin Khusus (Granular RBAC):**
   - Jika pengguna memegang peran staf, aksi `high` atau `critical` memerlukan izin supervisor `pos.supervisor_pin` atau `approval.manage`.
   - Aksi operasional lainnya memerlukan izin `ai.approve`.

---

## 4. Mekanisme Proteksi & Idempotensi

1. **Idempotency Key:**
   Setiap proposal dilengkapi UUID idempotensi unik berformat `ACT-xxxx`. Upaya persetujuan berulang (misal akibat double-click pengguna pada tombol setujui) ditangkap oleh `AiActionExecutor`:
   ```php
   if ($proposal->isExecuted()) {
       return [
           'success' => true,
           'message' => 'Idempotent: Proposal sudah dieksekusi sebelumnya.',
           'data' => $proposal->execution_result,
       ];
   }
   ```
2. **Re-Validasi Status Terkini:**
   Sebelum `DB::transaction` dieksekusi, sistem memastikan bahwa entitas terkait (misal pelanggan atau produk) belum dihapus secara manual oleh pengguna lain.
3. **Audit Trail Permanen:**
   Setiap persetujuan mencatat rekaman ke tabel `audit_logs` dan `ai_work_histories` dengan detail `user_id`, timestamp presisi, dan payload hasil eksekusi.
