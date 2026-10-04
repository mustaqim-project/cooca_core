# Alur Kerja & Orkestrasi (Workflows) COOCA AI

> **Layer:** End-to-End Orchestration & Business Execution Workflows  
> **Source:** `App\Domain\Ai\Orchestration\AiOrchestrator` & `App\Domain\Ai\Execution\AiActionExecutor`

---

## 1. Alur Konsultasi & Permintaan Pengguna (`Ask Workflow`)

```
1. Pengguna membuka AI Office UI (/ai) dan mengetik pertanyaan / instruksi.
2. AiCompanyWebController memvalidasi sesi, tenant, dan rate-limiting.
3. AiOrchestrator menginstruksikan CompanyHierarchy::routeIntent() untuk menentukan:
   - Divisi (Department)
   - Agen Spesialis (AgentRole)
   - Eksekutif Pengawas (ExecutiveRole)
4. AiContextBuilder mengumpulkan data snapshot bisnis tenant (Omzet, Stok, Finansial).
5. AiModelRouter memilih provider AI aktif (OpenAI, Gemini, Anthropic, OpenRouter, atau Offline Fallback).
6. Provider memproses instruksi dengan System Prompt persona agen dan data kontekstual.
7. Jika hasil berupa rekomendasi analitik:
   - Dikembalikan langsung ke antarmuka obrolan pengguna.
8. Jika hasil memerlukan pembuatan aksi nyata (misal: "Buatkan faktur untuk Kopi Kenangan 10 box"):
   - Tool memvalidasi ketersediaan data master (Customer & Produk).
   - Menghasilkan record ai_action_proposals dengan status 'pending'.
   - Mencatat ai_tasks dan audit history.
   - Mengembalikan ringkasan proposal ke pengguna dengan tombol pintas ke Action Center (/ai/actions).
```

---

## 2. Alur Maker-Checker & Persetujuan Proposal (`Approval Workflow`)

```
1. Proposal baru tersimpan di tabel ai_action_proposals:
   - status: 'pending'
   - idempotency_key: 'ACT-xxxx'
   - risk_level: 'medium' | 'high' | 'critical'
2. Pemilik usaha (Owner) atau Supervisor membuka Action Center (/ai/actions).
3. Muncul kartu proposal dengan rincian lengkap:
   - WHY: Alasan rekomendasi agen
   - WHAT: Rincian item dan nominal
   - IMPACT: Perkiraan dampak finansial & operasional
   - CHANGES: Snapshot data yang akan dimutasi
4. Pengguna memilih aksi:
   a. Tolak (Reject):
      - Memasukkan alasan penolakan.
      - Proposal beralih status ke 'rejected'.
      - Tidak ada perubahan data operasional.
   b. Setujui (Approve):
      - Verifikasi wewenang pengguna via AiActionPolicy::canApprove().
      - AiActionExecutor mengunci transaksi (DB lock & idempotency check).
      - Re-validasi ketersediaan data fisik (misal: apakah customer masih aktif).
      - Transaksi database dieksekusi atomik (DB::transaction).
      - Status proposal beralih ke 'executed'.
      - Dicatat ke audit_logs dan ai_work_histories secara permanen.
```

---

## 3. Alur Pengecekan Kesehatan Bisnis Harian Otomatis (`Daily Business Check`)

1. **Jadwal Eksekusi:** Terjadwal setiap hari pukul **07:00 WIB** via Artisan Scheduler:
   ```bash
   php artisan cooca:ai-daily-business-check
   ```
2. **Tahapan Pemrosesan:**
   - Menyaring seluruh entitas bisnis aktif (`is_active = true`).
   - AI CEO mengkaji performa penjualan 30 hari terakhir dan tren pergerakan kas.
   - AI COO memeriksa stok barang kritis dan mendeteksi anomali transaksi kasir.
   - Menghasilkan log evaluasi ke tabel `ai_work_histories` dengan level risiko dan rekomendasi prioritas.
   - Jika ditemukan masalah mendesak (stok kritis atau anomali transaksi), AI secara otomatis menyusun draft proposal aksi di Action Center agar pemilik usaha dapat langsung meninjau saat membuka aplikasi di pagi hari.
