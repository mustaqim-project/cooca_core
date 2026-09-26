# Aturan Bisnis Keamanan & Privasi (Security & Safety Rules)

> **Status:** COMPLETE  
> **Mandat:** Mandatory & Non-Negotiable. Seluruh pengembangan sistem wajib mematuhi aturan keamanan ini tanpa pengecualian.

---

## 📜 Katalog Aturan Bisnis Keamanan

### RULE-SEC-001: Strict Multi-Tenant Scoping Isolation
* **Name:** Kewajiban Scoping Tenant pada Seluruh Operasi Basis Data
* **Deskripsi:** Dilarang keras melakukan query data milik tenant tanpa membatasi `business_id`:
  ```php
  // BENAR
  $business = \App\Support\Context::requireBusiness();
  $orders = Order::where('business_id', $business->id)->get();

  // SALAH (Celah Kebocoran Data Lintas Bisnis!)
  $orders = Order::all();
  ```
* **Alasan:** Cooca adalah sistem multi-tenant berbasis shared database. Scoping memastikan bahwa satu bisnis tidak akan pernah dapat melihat atau memodifikasi data bisnis lain.

### RULE-SEC-002: Customer Order IDOR Shield
* **Name:** Proteksi Anti-IDOR pada Portal Pesanan Pelanggan
* **Deskripsi:** Pada endpoint pelacakan pesanan publik/pelanggan (`/customer/orders/{id}`), akses WAJIB diverifikasi ganda terhadap identitas pengguna yang sedang terautentikasi (`auth:customer`) dan nomor WhatsApp yang telah tervalidasi.
* **Perilaku:** Dilarang mengizinkan query pesanan customer jika parameter identitas bernilai `null`. Permintaan akses pesanan milik pihak lain wajib diblokir dengan respon HTTP 403 Forbidden.

### RULE-SEC-003: Supervisor PIN Verification on Sensitive POS Actions
* **Name:** Otorisasi PIN Supervisor pada Aksi Kasir Berisiko Tinggi
* **Deskripsi:** Aksi kasir sensitif (Pembatalan Pesanan / Void, Pengembalian Uang / Refund, dan Pembukaan Laci Kas Manual) WAJIB dilindungi oleh verifikasi `supervisor_pin`.
* **Guardrails:**
  - PIN disimpan dalam format hash terenkripsi Bcrypt.
  - Dilindungi pembatasan frekuensi (*Rate Limiting: throttle:5,1*).
  - Setiap eksekusi aksi sensitif mencatat record audit trail dengan `user_id` supervisor yang menyetujui.

### RULE-SEC-004: Anti-CSRF & Safe Method Spoofing
* **Name:** Kewajiban Token CSRF & Integritas Formulir
* **Deskripsi:** Setiap formulir POST/PUT/DELETE wajib mempertahankan `@csrf` dan direktif method spoofing `@method(...)`. Dilarang menonaktifkan middleware `VerifyCsrfToken` pada alur web internal.

### RULE-SEC-005: Protection Against Accidental Account Deletion
* **Name:** Larangan Penghapusan Sepihak Akun Bisnis
* **Deskripsi:** Akun pemilik usaha tidak boleh dihapus secara instan melalui tombol form sederhana untuk mencegah hilangnya data keuangan, stok, dan perpajakan historis.
* **Prosedur:** Permintaan pemulihan atau penggantian kontak darurat akun (WhatsApp / Email) wajib melalui alur **Account Recovery Request** resmi (`account_recovery_requests`) dengan melampirkan 3 bukti otentik: (1) Foto KTP asli pemilik, (2) Dokumen legalitas/bukti kepemilikan usaha (PDF/JPG/PNG), dan (3) Foto selfie bersama KTP.
* **Meja Verifikasi Administrator (`/admin/account-recoveries`):**
  - Mengadopsi arsitektur **Modal-First XXL Inspection Desk** di antrean index (`index.blade.php`) dan halaman detail mendalam (`show.blade.php`).
  - Administrator memeriksa kesesuaian nama pemohon, kecocokan dokumen usaha, dan nomor WhatsApp baru sebelum memberikan persetujuan (`approve`) atau penolakan (`reject`).
  - Persetujuan memperbarui kredensial `User` (`email` dan `phone`), menandai email terverifikasi, dan menyinkronkan profil bisnis terkait, disertai notifikasi otomatis via WhatsApp.

### RULE-SEC-006: Staff Portal Operational RBAC Scoping & Safe Navigation Guard
* **Name:** Pengalihan Aman Dashboard & Penyaringan Ketat Hak Akses Portal Karyawan
* **Deskripsi:** Menu Dashboard Eksekutif (`/dashboard`) wajib dilindungi izin `dashboard.view`. Pengguna tanpa izin ini wajib dialihkan secara aman ke Portal Karyawan (`/portal`) tanpa memicu error 403 atau redirect loop.
* **Perilaku Portal:**
  - Tile modul operasional cepat wajib disaring ketat berdasarkan `Context::hasPermission($permission)`.
  - Akses gudang fisik (`warehouse.view`) dan mutasi stok (`inventory.view`) wajib dipisahkan rutenya (`/warehouse` vs `/inventory/stocks`).
  - Staf tanpa izin modul operasional wajib disajikan Bento empty-state card tanpa link mati.

### RULE-SEC-007: Circular Product Bundle Prevention
* **Name:** Pencegahan Referensi Bundling Melingkar (Circular Bundle Protection)
* **Deskripsi:** Pada saat konfigurasi paket kombo (`is_bundle = true`), produk induk DILARANG memilih dirinya sendiri sebagai produk anak (`child_product_id != parent_product_id`).
* **Perilaku:** Validasi backend pada `ProductWebController::store()` dan `update()` wajib memverifikasi bahwa tidak ada `child_product_id` yang sama dengan ID produk induk untuk mencegah infinite recursion pada kalkulasi HPP dan pemotongan stok.


