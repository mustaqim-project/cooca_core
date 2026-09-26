# COOCA ERP - Standard Operating Procedure for AI Agent

## Siklus Kerja Wajib 6-Tahap (6-Stage Mandatory Operating Lifecycle)

Setiap pekerjaan rekayasa sistem pada repositori COOCA **WAJIB** mengikuti alur 6 tahap terstruktur:

1. **Audit Sistem & Analisis End-to-End**
   - Lakukan analisa sistem mendalam dan audit menyeluruh sebelum melakukan perubahan apa pun.
   - Pahami konteks sistem, fitur yang diperbaiki, kebutuhan bisnis, arsitektur, standar, alur bisnis, dan praktik pengembangan yang seharusnya agar perbaikan tepat sasaran.
   - Petakan traceability rantai penuh: `User → UI/Blade → Alpine.js/AJAX → Route → Middleware → Controller → Request Validation → Service/Action/Domain → Eloquent Model → Database Schema & Indexing → Event/Job/Queue → Notification Tri-Channel (UI/Email/WA) → Final Response`.
   - Identifikasi bug, inkonsistensi, duplikasi, potensi masalah, technical debt, gap keamanan 4-kuadran, skema fraud internal, serta bagian yang masih dapat dioptimalkan.
   - **Dilarang keras langsung melakukan perubahan sebelum proses audit dan analisa konteks selesai.**

2. **Buat Dokumen Rencana Perbaikan**
   - Berdasarkan hasil audit & analisa end-to-end, susun dokumen rencana perbaikan yang menjelaskan:
     1. Temuan masalah.
     2. Penyebab masalah (Root Cause Analysis).
     3. Dampak masalah.
     4. Solusi yang direkomendasikan.
     5. File / modul yang terdampak.
     6. Risiko perubahan.
     7. Prioritas perbaikan (P1/P2/P3).
     8. Urutan implementasi.
   - Rencana harus cukup jelas dan transparan sehingga dapat direview sebelum implementasi dilakukan.

3. **Minta Persetujuan (Interactive Confirmation Gate)**
   - Setelah rencana perbaikan selesai, **JANGAN LANGSUNG MELAKUKAN PERUBAHAN APAPUN**.
   - Tampilkan rencana tersebut dan minta persetujuan pengguna terlebih dahulu.
   - Hanya lakukan implementasi setelah mendapatkan persetujuan yang jelas (*explicit confirmation*).

4. **Implementasikan Perbaikan & Testing Nyata**
   - Setelah disetujui, lakukan perbaikan sesuai rencana secara *surgical*, terarah, dan tepat sasaran.
   - Hindari perubahan di luar scope yang telah disetujui (*anti-scope creep*).
   - Pastikan perubahan tidak merusak fitur atau modul yang sudah berjalan.
   - Jalankan pengujian nyata (`php -l`, `php artisan test`, `php artisan route:list`, `npm run build`) dan pastikan 100% lolos (0 error, 0 failure).

5. **Catat History Pekerjaan AI (`docs/AiWorkHistory.md`)**
   - Setelah perbaikan selesai, catat seluruh pekerjaan yang dilakukan pada: `docs/AiWorkHistory.md`.
   - Catatan minimal mencakup 7 komponen wajib:
     1. Tanggal/waktu
     2. Tujuan pekerjaan
     3. Hasil audit
     4. Perbaikan yang dilakukan
     5. File/module yang diubah
     6. Hasil pengujian
     7. Catatan atau risiko yang masih tersisa

6. **Update Dokumentasi Sistem (`docs/system/` & `docs/SYSTEM_GUIDE.md`)**
   - Periksa dan perbarui dokumentasi pada `docs/system/` dan `docs/SYSTEM_GUIDE.md` apabila memengaruhi salah satu dari 10 aspek sistem:
     1. Arsitektur sistem
     2. Struktur database
     3. Modul atau fitur
     4. Business flow
     5. Integrasi
     6. Konfigurasi
     7. API / Endpoint
     8. Permission / role
     9. Workflow
     10. Struktur file atau komponen penting lainnya.
   - Pastikan dokumentasi selalu mencerminkan kondisi sistem terbaru.

### Aturan Utama
**Audit & Analisa End-to-End → Rencana Perbaikan → Persetujuan → Implementasi → Testing → Catat History → Update Dokumentasi**

*Jangan melakukan perubahan langsung tanpa audit dan tanpa persetujuan terhadap rencana perbaikan, kecuali pengguna secara eksplisit meminta perubahan langsung.*
