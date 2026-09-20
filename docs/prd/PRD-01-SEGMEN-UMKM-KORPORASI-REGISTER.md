# PRD-01: Seleksi Segmen UMKM vs. Korporasi Saat Registrasi Owner

**ID Dokumen:** `PRD-01-SEGMEN-REGISTER`  
**Modul:** Registrasi Akun & Tenant Onboarding  
**Penanggung Jawab:** Principal Full-Stack Engineer & Product Architect  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Calon Pemilik Usaha (Owner) baru saat mendaftar di COOCA  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **View Form Registrasi:** [`resources/views/auth/register.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/auth/register.blade.php) (33 KB)
* **Controller Registrasi:** [`app/Http/Controllers/Web/AuthWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/AuthWebController.php) (metode `showRegisterForm()`, `register()`)
* **Service Template Industri:** [`app/Domain/Template/BusinessTypeTemplateService.php`](file:///c:/laragon/www/cooca_core/app/Domain/Template/BusinessTypeTemplateService.php)
* **Model Bisnis:** [`app/Models/Business.php`](file:///c:/laragon/www/cooca_core/app/Models/Business.php)
* **Tabel Database:** `businesses` (kolom `industry_template`, `disabled_modules`, `tier`, `scale`)

### B. Alur & Perilaku Existing
1. Form registrasi saat ini menggunakan wizard 2-langkah via Alpine.js:
   - **Langkah 1:** Data Akun Pengguna (Nama Lengkap, Email, WhatsApp, Password).
   - **Langkah 2:** Data Bisnis (Nama Usaha, Jenis Industri / Template, Alamat, Paket).
2. Pilihan template saat ini berupa dropdown sederhana atau grid kartu industri (F&B, Ritel, Jasa, dsb).
3. **Kendala / Gap Existing:**
   - **Tidak ada pembedaan skala bisnis:** Pemilik warung kelontong kecil (UMKM) mendapatkan opsi dan kompleksitas modul yang sama dengan distributor multi-cabang (Korporasi).
   - Jika memilih template industri, sistem memuat `disabled_modules` bawaan template, namun seringkali masih menyisakan menu enterprise (B2B Sales, Kasbon, Overheads) yang membingungkan pengusaha mikro (usia 40–65 tahun).
   - Tidak ada pilihan deklaratif di awal yang memberikan ketenangan bahwa sistem ini "Bisa Simpel untuk UMKM" dan "Bisa Canggih untuk Korporasi".

---

## 2. Perubahan & Penambahan Sistem (To-Be State)

### A. Penambahan Kolom Database (`businesses`)
```sql
ALTER TABLE businesses 
    ADD COLUMN business_scale VARCHAR(20) NOT NULL DEFAULT 'umkm' AFTER industry_template;
    -- Nilai: 'umkm' (Usaha Mikro, Kecil, Menengah) atau 'corporate' (Korporasi / Multi-Cabang)
```

### B. Logika Inisialisasi Modul (`AuthWebController.php`)
1. **Jika Memilih UMKM (`business_scale = 'umkm'`):**
   - Sistem mengaktifkan paket modul esensial ringkas: Kasir POS, Manajemen Produk Ritel/Menu, Catatan Keuangan Sederhana (Pemasukan/Pengeluaran Kas), dan Laporan Laba Rugi Cepat.
   - Sistem secara otomatis memasukkan modul-modul kompleks ke dalam `disabled_modules`:
     `['b2b_sales', 'labor_machines', 'inventory_warehouse', 'approval_workflow', 'corporate_ledger']`
   - Hasil: Sidebar langsung ramping (hanya 4–5 grup Bento), ramah lansia/pemula, cepat dipahami tanpa manual book.
2. **Jika Memilih KORPORASI (`business_scale = 'corporate'`):**
   - Sistem mengaktifkan seluruh modul lengkap (15 modul aktif).
   - Membuka fitur: B2B Quotation & Sales Order, Multi-Warehouse, Multi-Cabang Fulfillment, Maker-Approver-Release (MAR), Akuntansi Multi-Ledger, dan Audit Trail.
   - Hasil: Tampilan ERP 8-Grup Bento Apple HIG yang komprehensif.
3. **Fleksibilitas:** Owner dapat berpindah skala atau mengaktifkan/menonaktifkan modul kapan saja via menu `Pengaturan Usaha -> Kelola Modul`.

---

## 3. Desain Antarmuka Bento Apple HIG pada `register.blade.php`

Pada Langkah 2 Form Registrasi, sebelum memilih dropdown industri, dihadirkan **2 Kartu Pilihan Segmen Interaktif (Bento Segment Selector)**:

```blade
<!-- Segment Selection Bento Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
    <!-- Kartu UMKM -->
    <label class="relative flex flex-col p-4 rounded-[14px] border cursor-pointer transition-all"
        :class="businessScale === 'umkm' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-sm' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 hover:border-black/20'">
        <input type="radio" name="business_scale" value="umkm" x-model="businessScale" class="sr-only">
        <div class="flex items-center justify-between mb-2">
            <div class="w-8 h-8 rounded-[8px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center font-bold">
                <i data-lucide="store" class="w-4 h-4"></i>
            </div>
            <span x-show="businessScale === 'umkm'" class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">Terpilih</span>
        </div>
        <h4 class="text-[14px] font-bold text-black dark:text-white">UMKM &amp; Toko Mandiri</h4>
        <p class="text-[11px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
            Untuk warung, kafe, butik, bengkel, atau toko 1–3 cabang. Tampilan ringkas, tanpa istilah akuntansi rumit, siap jualan 5 menit.
        </p>
    </label>

    <!-- Kartu Korporasi -->
    <label class="relative flex flex-col p-4 rounded-[14px] border cursor-pointer transition-all"
        :class="businessScale === 'corporate' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-sm' : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/10 dark:border-white/10 hover:border-black/20'">
        <input type="radio" name="business_scale" value="corporate" x-model="businessScale" class="sr-only">
        <div class="flex items-center justify-between mb-2">
            <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                <i data-lucide="building-2" class="w-4 h-4"></i>
            </div>
            <span x-show="businessScale === 'corporate'" class="text-[11px] font-semibold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">Terpilih</span>
        </div>
        <h4 class="text-[14px] font-bold text-black dark:text-white">Korporasi &amp; Multi-Cabang</h4>
        <p class="text-[11px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
            Untuk perusahaan berkembang, distributor, waralaba, &amp; multi-gudang. Fitur approval bertingkat, multi-ledger, &amp; audit trail lengkap.
        </p>
    </label>
</div>
```

---

## 4. Alur Kerja Registrasi (Workflow End-to-End)

```
[ OWNER BUKA /register ]
          │
          ▼
[ LANGKAH 1: ISI DATA AKUN ]
(Nama, Email, No. WhatsApp, Kata Sandi)
          │
          ▼
[ LANGKAH 2: PROFIL BISNIS & PILIH SEGMEN ]
   ├── Pilih: [ UMKM ]  atau  [ KORPORASI ]
   ├── Pilih Jenis Industri (20 Template COOCA)
   └── Isi Nama Bisnis & Alamat
          │
          ▼
[ SUBMIT FORM REGISTRASI ]
          │
          ▼
[ AuthWebController::register() ]
   ├── Validasi 'business_scale' in:umkm,corporate
   ├── Buat entitas User & Business
   ├── Inisialisasi disabled_modules sesuai skala
   ├── Assign Role 'owner'
   └── Auto-login & Redirect ke Dashboard
          │
          ▼
[ DASHBOARD UTAMA COOCA ]
   ├── Jika UMKM: Tampil antarmuka bersih, cepat, 4 grup Bento.
   └── Jika Korporasi: Tampil antarmuka lengkap ERP 8 grup Bento.
```

---

## 5. Kriteria Keberhasilan (Definition of Done)
1. Kolom `business_scale` berhasil dimigrasikan ke tabel `businesses`.
2. Halaman registrasi menampilkan 2 Bento Card yang reaktif via Alpine.js tanpa glitch visual.
3. Bisnis yang mendaftar dengan segmen UMKM memiliki `disabled_modules` yang otomatis menyembunyikan modul berat.
4. Bisnis yang mendaftar dengan segmen Korporasi memiliki seluruh modul aktif.
5. Pemilik UMKM dapat sewaktu-waktu mengaktifkan modul korporasi melalui Pengaturan Modul tanpa kehilangan data satu pun.
