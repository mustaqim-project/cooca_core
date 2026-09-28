# Template Rencana Implementasi Bertahap (Implementation Plan)

Gunakan cetak biru ini saat menyusun rencana teknis implementasi bertahap untuk mengeksekusi rekomendasi dari PRD keamanan.

---

```markdown
# Rencana Implementasi Bertahap (Implementation Plan)
## Perbaikan Keamanan, Mitigasi Fraud, & Proteksi Human Error

> **Dokumen Terkait:** [PRD-[Nama-Modul]](file:///c:/laragon/www/cooca_core/docs/...)  
> **Status:** READY FOR STAGED EXECUTION  
> **Target Modul:** [Nama Modul, contoh: Products / POS]  
> **Prinsip Eksekusi:** Surgical Modification, Zero Regression, 100% Automated Testing.

---

## 📅 1. Roadmap Eksekusi Bertahap (Phased Roadmap)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: CRITICAL SECURITY & TENANT SCOPING HOTFIXES (Hari 1)                │
│         • Tutup celah IDOR • Ketatkan FormRequest • Eliminasikan Mass Assign│
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 2: INTERNAL FRAUD & OPERATIONAL GUARDRAILS (Hari 2)                    │
│         • Supervisor PIN verifikasi • Maker-Checker • Audit Log Immutable   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 3: HUMAN ERROR PREVENTION & UI/UX HARDENING (Hari 3)                   │
│         • Anti double-submit • Auto format ribuan • Modal konfirmasi        │
├─────────────────────────────────────────────────────────────────────────────┤
│                                      ↓                                      │
│ FASE 4: AUTOMATED TESTING, REGRESSION TEST & DOKUMENTASI (Hari 4)           │
│         • Tulis Feature Test baru • php artisan test 100% PASS • Update docs│
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🗂️ 2. Matriks Berkas Terdampak (Affected Files Matrix)

| Berkas Sumber Kode | Lapisan Arsitektur | Rencana Modifikasi Spesifik |
|---|---|---|
| `app/Http/Requests/[Module]Request.php` | Validation Layer | Tambahkan aturan validasi ketat, scoped unique, sanitasi format |
| `app/Http/Controllers/Web/[Module]Controller.php` | Controller Layer | Gunakan `$request->validated()`, pasang verifikasi `supervisor_pin` |
| `app/Domain/[Module]/Services/[Service].php` | Domain Service | Bungkus dalam `DB::transaction()`, catat mutasi ke `audit_logs` |
| `app/Models/[Model].php` | Model Layer | Perketat `$fillable`, tambahkan field rahasia ke `$hidden` |
| `resources/views/app/[module]/index.blade.php` | UI / Bento Blade | Pasang `x-bind:disabled="submitting"`, format ribuan otomatis |
| `database/migrations/[timestamp]_add_guard.php` | Database Layer | Tambahkan kolom status atau audit trail jika belum tersedia |
| `tests/Feature/[Module]SecurityAndFraudTest.php` | Automated Testing | Buat skenario pengujian celah keamanan dan pencegahan fraud |

---

## 🛠️ 3. Panduan Langkah Eksekusi Bedah (Surgical Steps)

### Langkah 1: Penguatan Validasi & Scoping Tenant (Fase 1)
- Buat atau perbarui `FormRequest` khusus untuk modul ini.
- Pastikan pengecekan tenant scoping terikat pada `Context::requireBusiness()`.

### Langkah 2: Pemasangan Guardrail Anti-Fraud (Fase 2)
- Tambahkan pengecekan `supervisor_pin` pada aksi bernilai tinggi.
- Pasang event pencatatan audit log immutable ke tabel `audit_logs`.

### Langkah 3: Proteksi Antarmuka & Pencegahan Human Error (Fase 3)
- Modifikasi tombol submit Blade/Alpine:
  ```html
  <button type="submit" 
          :disabled="isSubmitting" 
          class="disabled:opacity-50 disabled:cursor-not-allowed ...">
      <span x-show="!isSubmitting">Simpan Transaksi</span>
      <span x-show="isSubmitting" class="inline-flex items-center gap-2">
          <svg class="animate-spin h-4 w-4 ..."></svg> Memproses...
      </span>
  </button>
  ```
- Pasang format ribuan otomatis pada input nominal uang (`tabular-nums`).

### Langkah 4: Pembuatan Automated Test Suite (Fase 4)
- Buat file test baru: `tests/Feature/[Module]SecurityAndFraudTest.php`.
- Skenario pengujian minimal:
  1. Uji IDOR: Tenant B dilarang mengubah data Tenant A.
  2. Uji Mass Assignment: Field terlarang diabaikan saat simpan.
  3. Uji Anti-Fraud: Aksi ditolak jika PIN Supervisor salah.
  4. Uji Atomisitas Transaksi: Rollback bekerja sempurna jika ada kegagalan internal.

---

## 🧪 4. Checklist Verifikasi & Jaminan Kualitas (Verification Checklist)

- [ ] Seluruh file PHP lolos pemeriksaan sintaks: `php -l [file]` (0 syntax error).
- [ ] Route list terdaftar valid: `php artisan route:list --name=[module]` (0 exception).
- [ ] Pengujian fitur dieksekusi: `php artisan test --filter=[Module]SecurityAndFraudTest` (100% PASS).
- [ ] Tidak ada regresi pada pengujian modul lain: `php artisan test` (0 error, 0 failure).
- [ ] Catat riwayat pekerjaan ke `docs/AiWorkHistory.md` (7 komponen wajib).
- [ ] Sinkronkan pembaruan sistem ke `docs/system/` dan `docs/SYSTEM_GUIDE.md`.
```
