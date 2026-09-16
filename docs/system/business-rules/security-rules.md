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
* **Prosedur:** Permintaan penutupan akun harus melewati alur **Account Recovery / Deactivation Request** resmi dengan masa tunggu dan verifikasi identitas pemilik.
