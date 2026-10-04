# Panduan Keamanan & Isolasi Tenant COOCA AI

> **Layer:** Enterprise Security, Tenant Isolation, Prompt Injection Defense, & Anti-Fraud  
> **Source:** `App\Domain\Ai\` & Multi-Tenant Architecture Standard

---

## 1. Isolasi Data Multi-Tenant (Tenant Data Isolation)

Dalam ekosistem multi-tenant COOCA, setiap tenant bisnis harus terisolasi secara mutlak:

1. **Scoping Wajib via `business_id`:**
   Semua kueri data konteks dalam `AiContextBuilder` dan seluruh `AiTool` secara ketat difilter menggunakan `$business->id`. Tidak ada kueri global tanpa batasan `business_id`.
2. **Uji Penetrasi Antar-Tenant (Cross-Tenant Access Test):**
   Telah diverifikasi melalui pengujian fitur `AiDigitalCompanyTest`:
   - Tenant B tidak dapat membaca atau melihat proposal aksi milik Tenant A (`assertEmpty`).
   - Tenant B yang mencoba menyetujui proposal milik Tenant A akan ditolak secara mutlak dengan status HTTP 403 Forbidden atau exception wewenang.

---

## 2. Pencegahan Eksekusi SQL Liar (Zero Raw SQL Injection)

Beberapa implementasi AI Agent umum rentan terhadap halusinasi di mana model menghasilkan sintaks `SELECT ... FROM users` atau bahkan `DROP TABLE`.

Pada COOCA AI:
- **LLM TIDAK DIBERIKAN AKSES KONEKSI DATABASE LANGSUNG.**
- AI hanya dapat memanggil nama alat bantu yang terdaftar di `AiToolRegistry` beserta parameter yang terstruktur JSON schema.
- Eksekusi pembacaan dan penulisan dilakukan secara deterministik oleh kode PHP Laravel yang telah tervalidasi dan teruji keamanan tipenya (`strict_types=1`).

---

## 3. Pertahanan terhadap Prompt Injection & Manipulasi Instruksi

1. **Pemisahan Tegas System vs User Prompt:**
   Persona agen dan batasan wewenang ditanamkan di level `system` prompt yang tidak dapat ditimpa oleh masukan pengguna di level `user`.
2. **Validasi Parameter Sisi Server:**
   Parameter numerik (seperti `quantity`, `unit_price`, atau `days`) divalidasi dan di-cast ke tipe primitif PHP sebelum diproses. Masukan non-numerik akan ditolak atau dipotong sesuai batas wajar.
3. **Pemberian Hak Akses Maker-Checker:**
   Bahkan jika pengguna berhasil memanipulasi model untuk menyetujui "diskon 99%", model hanya dapat menghasilkan sebuah proposal berstatus `PENDING` dengan tingkat risiko `HIGH`. Mutasi tersebut tidak akan pernah menjadi transaksi aktif tanpa persetujuan eksplisit pemilik usaha di Action Center.
