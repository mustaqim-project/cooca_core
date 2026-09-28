# Checklist Teknis Audit Workflow Sistem (11 Layer Code-to-System Trace)

Dokumen ini adalah panduan inspeksi baris-per-baris untuk membedah kode sumber saat menjalankan **Fase 2 (Trace Hulu-ke-Hilir)** pada Skill `system-workflow-audit`.

---

## 🔍 Layer 1: Aktor & Persona Pengguna
- [ ] Siapa aktor utama yang menginisiasi aksi ini?
  - `Superadmin` (Platform Operator)
  - `Business Owner` (Pemilik Bisnis Multi-Tenant)
  - `Store Manager` / `Supervisor`
  - `Cashier` (Kasir POS Terminal)
  - `Kitchen / Bar Staff` (KDS Dapur)
  - `Warehouse / Logistics Staff` (Staf Gudang)
  - `Customer` (Pelanggan Storefront / Walk-in)
  - `System Worker / Cron Job` (Otomasi Terjadwal)
- [ ] Apakah alur ini melibatkan serah-terima antar-aktor? (Contoh: Staf Gudang request transfer ➔ Supervisor approve).

---

## 🎨 Layer 2: Antarmuka Pengguna (Blade View & Alpine.js)
- [ ] **Form Submission**:
  - Apakah menggunakan tag `<form>` standar dengan `@csrf`?
  - Apakah menggunakan method spoofing: `@method('PUT')`, `@method('DELETE')`?
- [ ] **Alpine.js Interactivity**:
  - Struktur state pada `x-data`: Variabel form, loading spinner, error message, modal toggle.
  - Event listener: `@click`, `@change`, `x-on:submit.prevent`, `x-cloak`.
  - Pemanggilan asynchronous: `fetch(...)`, `axios`, URL endpoint yang dipanggil.
- [ ] **Modal & Progressive Disclosure**:
  - Apakah form dibuka via modal pop-up full-size (`max-w-5xl`, `max-w-6xl`, atau bottom sheet mobile)?
  - Apakah ada query parameter URL untuk membuka modal secara langsung (`?add=warehouse`, `?action=adjust`)?
- [ ] **Aksesibilitas & Ergonomi (Bento Apple HIG)**:
  - Input teks/angka: Apakah ada format ribuan otomatis (`tabular-nums`)?
  - Mobile ergonomics: Ukuran font input minimal 16px (mencegah zoom Safari iOS), touch target tombol minimal 48px.
  - No-panic microcopy: Pesan penenang jika aksi bersifat krusial atau melibatkan data sensitif.

---

## 🛣️ Layer 3: Routing & Middleware Pipeline
- [ ] **File Route**: Di mana endpoint didefinisikan?
  - `routes/owner.php` (Portal Tenant & Operasional Bisnis)
  - `routes/admin.php` (Admin Platform SaaS)
  - `routes/customer.php` (Toko Online Publik & Member Storefront)
  - `routes/api.php` (Mobile App / POS Hardware / Integrasi Eksternal)
- [ ] **Route Definition**:
  - HTTP Verb: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
  - URL Pattern: Apakah memakai Route Model Binding (misal `/warehouse/{location}`)?
  - Route Name: Format penamaan baku (`warehouse.index`, `warehouse.store`, dsb.).
- [ ] **Middleware Security Pipeline**:
  - `auth`: Wajib login pengguna.
  - `require.permission:<permission_name>`: Pengecekan RBAC hak akses.
  - `entitlement:<feature_name>`: Pengecekan kuota / paket langganan SaaS tenant.
  - `throttle:<limit>`: Rate limiting pada aksi berisiko tinggi.
  - `tenant.context`: Injeksi konteks bisnis aktif (`Context::requireBusiness()`).

---

## 🎮 Layer 4: Controller & Action Dispatcher
- [ ] **Nama Berkas & Method**:
  - Lokasi file controller (misal `app/Http/Controllers/Web/WarehouseWebController.php`).
  - Method yang dieksekusi (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`).
- [ ] **Dependency Injection**:
  - Service apa saja yang diinjeksi ke controller constructor atau method parameter?
- [ ] **Tipe Respons**:
  - Redirect dengan flash message: `return redirect()->route(...)->with('success', '...')`.
  - Render View Blade: `return view('app.warehouse.show', compact(...))`.
  - JSON API Response: `return response()->json(['success' => true, ...])`.

---

## 🛡️ Layer 5: Validasi Request & Sanitasi Payload
- [ ] **Mekanisme Validasi**:
  - Apakah menggunakan `FormRequest` terpisah atau inline `$request->validate([...])`?
- [ ] **Aturan Validasi Kritis**:
  - Validasi keberadaan & tipe data: `required`, `string`, `numeric`, `uuid`, `boolean`, `array`.
  - Validasi keunikan bertingkat tenant (*Scoped Unique Rule*): `Rule::unique('locations')->where('business_id', $businessId)`.
  - Validasi foreign key: `exists:locations,id`.
- [ ] **Sanitasi Data**:
  - Pembersihan format angka (menghapus titik ribuan sebelum disimpan ke integer database).
  - Sanitasi string / HTML injection prevention.

---

## ⚙️ Layer 6: Domain Service & Logika Bisnis (DDD)
- [ ] **Koleksi Service Terkait**:
  - Di mana logika utama diproses? (Misal: `app/Domain/Inventory/Services/StockService.php`).
- [ ] **State Transitions & Status Lifecycle**:
  - Apakah status entitas berubah? (Contoh: `draft` ➔ `approved` ➔ `received`).
  - Apakah ada state machine atau enum yang membatasi transisi status?
- [ ] **Transactional Boundary**:
  - Apakah proses dibungkus dalam `DB::transaction(function () { ... })`?
  - Apakah rollback dieksekusi secara otomatis jika salah satu simpul gagal?

---

## 💾 Layer 7: Model Eloquent & Skema Basis Data
- [ ] **Tabel & Kolom**:
  - Tabel utama dan tabel pivot/anak yang terpengaruh.
  - Migrasi basis data terkait (periksa file di `database/migrations/`).
  - Kolom-kolom kunci: PK (UUID), FK (`business_id`, `parent_id`, `user_id`), flag status, timestamp.
- [ ] **Relasi Eloquent**:
  - Relasi yang dipanggil: `belongsTo`, `hasMany`, `belongsToMany`, self-referencing.
- [ ] **Tenant Scoping & Global Scopes**:
  - Apakah model mematuhi `business_id` scoping?
  - Apakah ada proteksi soft delete (`SoftDeletes`) atau foreign key cascade delete?

---

## 💰 Layer 8: Dampak Finansial & Jurnal Akuntansi Otomatis
- [ ] **Auto-Journal Trigger**:
  - Apakah alur ini memicu mutasi akuntansi pembukuan berpasangan?
  - Apakah memanggil `AutoJournalService::record(...)`?
- [ ] **Akun CoCOA (Chart of Accounts)**:
  - Akun Debit: (Contoh: Persediaan Bahan Baku, Beban Pokok Penjualan).
  - Akun Kredit: (Contoh: Kas Utama, Utang Usaha / AP, Piutang Usaha / AR).
- [ ] **Keseimbangan Finansial**:
  - Apakah Debit = Kredit 100% seimbang?
  - Apakah mutasi mencatat riwayat transaksi buku besar (*General Ledger*) yang immutable?

---

## 📦 Layer 9: Mutasi Fisik Stok & Bahan Baku (BOM)
- [ ] **Stock Movement Record**:
  - Apakah ada pencatatan di tabel `stock_movements`?
  - Tipe mutasi: `in`, `out`, `transfer`, `adjustment`, `loss`.
- [ ] **Hierarki Lokasi & Gudang**:
  - Di gudang/lokasi mana stok bertambah atau berkurang?
  - Jika transaksi terjadi di Cabang, dari sub-gudang mana stok ditarik?
- [ ] **BOM (Bill of Materials) Auto-Deduction**:
  - Jika menjual produk jadi, apakah bahan baku resep berkurang secara otomatis?

---

## 📲 Layer 10: Notifikasi Sistem Terpadu (Tri-Channel)
- [ ] **Penyebaran Notifikasi**:
  - **In-App**: Notifikasi database pada Notification Center bell icon.
  - **WhatsApp**: Pengiriman pesan teks/template otomatis via WhatsApp Cloud API Meta.
  - **Email**: Pengiriman email HTML responsif.
- [ ] **Queue Asynchronous**:
  - Apakah pengiriman pesan dibungkus dalam Laravel Job (`implements ShouldQueue`) agar tidak membebani proses HTTP kasir/operasional?
- [ ] **Fail-Safe & Manual Fallback**:
  - Apakah sistem tetap berjalan jika gateway eksternal offline?
  - Apakah tersedia tombol fallback kirim manual via `wa.me`?

---

## 🔒 Layer 11: Keamanan, Anti-Fraud, & Batas Tenant
- [ ] **Isolasi Multi-Tenant**:
  - Apakah ada potensi kebocoran data jika `location_id` atau ID transaksi dimanipulasi di URL/payload (Anti-IDOR)?
  - Apakah query selalu memverifikasi kepemilikan tenant melalui `business_id`?
- [ ] **Otorisasi Supervisor & Anti-Fraud**:
  - Apakah aksi sensitif (void nota, penyesuaian stok minus, refund) membutuhkan `supervisor_pin`?
  - Apakah ada pencatatan audit log immutable (`created_by`, `updated_by`, IP address)?
