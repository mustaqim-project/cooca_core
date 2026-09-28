# Matriks Kerentanan, Modus Fraud, & Pola Human Error (Katalog 5-Pilar)

Dokumen ini adalah katalog referensi mendalam bagi AI Agent untuk memverifikasi setiap kemungkinan celah keamanan, fraud operasional, dan human error saat mengaudit kode di ekosistem Cooca.

---

## 🌐 Pilar 1: Cyber Security & Web Application Vulnerabilities

| Vektor Serangan | Modus / Cara Kerja Celah | Titik Rawan di Kode | Pola Remediasi Defensif Wajib |
| :--- | :--- | :--- | :--- |
| **BOLA / IDOR (Broken Object-Level Authorization)** | Penyerang mengganti parameter ID (UUID) di URL atau JSON payload untuk melihat/mengubah/menghapus data milik tenant lain. | Controller menggunakan `Model::find($id)` tanpa membatasi `where('business_id', $businessId)` atau Route Model Binding tanpa scoping. | Gunakan scoped query: `Context::requireBusiness()->products()->findOrFail($id)` atau route model binding dengan `whereBelongsTo`. |
| **Mass Assignment** | Penyerang menyisipkan field tersembunyi (misal: `business_id`, `role`, `is_admin`, `is_active`, `balance`) via form/API payload. | Controller memanggil `Model::create($request->all())` atau `Model::update($request->all())`. | Gunakan `FormRequest` dengan validasi eksplisit `$request->validated()` dan perketat `$fillable` pada Model. |
| **SQL Injection (SQLi)** | Karakter escape atau query injection disisipkan lewat kolom pencarian, sorting dinamis, atau filter query string. | `DB::raw("name LIKE '%{$query}%'")` atau `orderByRaw($sortBy)`. | Gunakan query builder bawaan Eloquent: `where('name', 'like', "%{$query}%")` atau parameterized binding `whereRaw('col = ?', [$val])`. |
| **Cross-Site Scripting (XSS)** | Skrip JavaScript jahat tersimpan di database (nama produk, catatan, alamat) dan tereksekusi di browser user lain. | Merender variabel menggunakan unescaped syntax Blade: `{!! $product->description !!}` tanpa filter. | Gunakan default escaping Blade `{{ $product->description }}`. Jika butuh HTML rich-text, filter dengan library sanitizer (HTML Purifier / Purify). |
| **Insecure File Upload** | Mengunggah berkas PHP/executable yang disamarkan sebagai gambar produk (`shell.php.png` atau file SVG berisi `<script>`). | Hanya mengecek ekstensi berkas atau tidak memvalidasi MIME type dan ukuran berkas. | Validasi ketat: `image`, `mimes:jpeg,png,webp`, `max:2048`. Jangan gunakan nama asli dari user; generate nama acak UUID, simpan di non-executable storage disk. |
| **Zero Plaintext Credential Exposure** | Kredensial rahasia (API key, token, supervisor PIN, webhook secret) bocor ke tampilan atau inspeksi jaringan. | Lupa menambahkan `$hidden` pada model atau menampilkan token mentah di form input HTML. | Tambahkan field ke `$hidden` model Eloquent. Pada form pengaturan, terapkan masking (`••••••••••••••••`) dan hanya tampilkan form ubah baru. |
| **Missing Rate Limiting** | Serangan brute-force pada verifikasi PIN kasir, voucher promo, atau pencarian barang. | Endpoint route tidak memiliki middleware throttling. | Pasang `throttle:60,1` untuk request standar, dan perketat `throttle:5,1` untuk verifikasi PIN kasir / password / OTP. |

---

## 🏢 Pilar 2: Multi-Tenancy & External Fraud Abuse

| Skema Celah / Fraud | Modus Operandi | Dampak Terhadap Bisnis | Mekanisme Proteksi & Guardrail |
| :--- | :--- | :--- | :--- |
| **Client-Side Price Tampering (Ubah Harga di Keranjang)** | Pembeli mengubah nilai harga produk di JavaScript keranjang belanja atau intercept payload HTTP saat checkout storefront. | Barang mahal terbeli dengan harga murah (misal Rp 1.000). | **Harga Wajib Ditarik dari Database**: Payload checkout hanya boleh mengirimkan `product_id` dan `quantity`. Sistem backend yang menghitung ulang subtotal, diskon, dan total tagihan berdasarkan database resmi. |
| **Negative Quantity Order** | Mengirimkan nilai `quantity = -1` pada salah satu item di keranjang untuk mengurangi total belanjaan item lainnya. | Total tagihan invoice menjadi berkurang atau minus. | Pasang aturan validasi backend: `'items.*.quantity' => ['required', 'integer', 'min:1']`. Tolak kuantitas nol atau minus. |
| **Race Condition Stok Botol Leher (Bottleneck)** | Dua pembeli men-checkout item produk/kombo terakhir secara bersamaan dalam detik yang sama. | Stok fisik menjadi minus (*oversold*) dan toko gagal memenuhi pesanan. | Gunakan database pessimistic locking `lockForUpdate()` saat pemotongan stok di dalam `DB::transaction()` atau optimistik concurrency. |
| **Fake Payment Webhook Spoofing** | Penyerang mengirimkan request webhook tiruan seolah-olah pembayaran Midtrans/Xendit/TriPay telah lunas (*settlement*). | Pesanan diproses dan barang dikirim padahal uang belum masuk. | Validasi cryptographic signature / hash HMAC dari payment gateway sebelum mengubah status pesanan. Verifikasi IP whitelist gateway. |
| **Storefront Coupon / Voucher Abuse** | Menggunakan satu kupon berulang kali lewat banyak tab browser atau bypass batasan kuota per pelanggan. | Kerugian margin promosi merchant UMKM. | Kunci record kupon dengan `lockForUpdate()`, validasi riwayat pemakaian per pelanggan (`user_id` / nomor WhatsApp), dan decrement kuota secara atomik. |

---

## 💼 Pilar 3: Internal Operational Fraud (Kasir, Gudang, Keuangan)

| Modus Fraud Internal | Praktik Lapangan Oknum Karyawan | Titik Rawan di Sistem | Proteksi Wajib Sistem |
| :--- | :--- | :--- | :--- |
| **Post-Payment Cash Void (Void Pasca Bayar)** | Kasir menerima uang tunai dari pelanggan, lalu membatalkan (*void*) transaksi di POS dan mengantongi uangnya. | Tombol Void dapat ditekan bebas tanpa izin atasan. | • Tombol Void **Wajib verifikasi Supervisor PIN**.<br>• Catat snapshot order ke `audit_logs`.<br>• Kirim alert instan ke WhatsApp & Email Owner. |
| **Fictitious Refund / Return (Retur Fiktif)** | Kasir membuat nota retur fiktif seolah pembeli mengembalikan barang, lalu menarik uang dari laci kas. | Retur tidak mewajibkan nomor nota asli atau tanpa supervisor check. | • Retur/Refund wajib mencantumkan nomor faktur sah.<br>• Wajib otorisasi Supervisor PIN.<br>• Auto-restock transparan & jurnal pembalik kas. |
| **Diskon Liar / Teman Kasir** | Kasir memberikan diskon manual tanpa persetujuan, atau menagih harga normal lalu menginput diskon dan mengambil selisihnya. | Field diskon manual di POS tidak memiliki batasan nominal/persentase. | Diskon manual di atas batas (misal > 10% atau > Rp 50.000) wajib persetujuan Supervisor PIN dan alasan baku. |
| **Blind Cash Count Bypass (Manipulasi Tutup Kasir)** | Kasir melihat angka ekspektasi sistem sebelum menghitung uang fisik, lalu menutupi selisih minus. | Layar tutup kasir menampilkan angka omzet sebelum uang fisik dihitung. | **Blind Cash Count**: Kasir wajib input nominal fisik di laci terlebih dahulu tanpa melihat nominal sistem. Sistem yang menghitung *over/short*. |
| **Phantom Stock Write-off (Penyesuaian Stok Minus)** | Staf gudang mengurangi stok barang di sistem dengan alasan "rusak/pecah", padahal barangnya dicuri. | Fitur penyesuaian stok bebas diinput staf gudang tanpa kontrol atasan. | Stock adjustment minus bernilai > batas (misal Rp 100.000 / > 5 unit) wajib sistem Maker-Checker (Approval Supervisor) dan upload foto Berita Acara. |
| **Two-Step Transfer Bypass (Transfer Gelap Cabang)** | Mengeluarkan stok dengan dalih kirim ke cabang lain, namun barang tidak dikirim dan dijual pribadi. | Pengurangan stok langsung bertambah di cabang tujuan tanpa konfirmasi fisik. | Terapkan **Two-Step Verification**: Status barang `In-Transit`. Stok baru diakui masuk setelah cabang penerima memverifikasi fisik (`Received`). |
| **Fictitious Supplier & Mark-up PO** | Membuat PO ke vendor rekanan dengan harga dinaikkan demi komisi pribadi. | Pendaftaran supplier bebas tanpa approval atau pembayaran AP tanpa verifikasi barang. | Terapkan **Three-Way Matching**: PO ↔ Bukti Penerimaan Barang (GR) ↔ Faktur Tagihan Supplier (AP Invoice) wajib cocok sebelum kas keluar. |
| **Silent Ledger / Journal Manipulation** | Mengedit angka mutasi akuntansi di masa lalu untuk menyamarkan pencurian kas. | Jurnal pembukuan bisa di-update atau di-delete langsung di database/UI. | **Prinsip Double-Entry Immutability**: Jurnal yang sudah diposting dilarang diedit/dihapus. Koreksi wajib melalui *Jurnal Penyesuaian / Pembalik*. |

---

## 👥 Pilar 4: Human Error & Operational Slips (Pencegahan Kesalahan Manusia)

| Potensi Human Error | Skenario Kesalahan Lapangan | Dampak Operasional | Solusi Desain UI & Backend |
| :--- | :--- | :--- | :--- |
| **Double-Click Submission** | Pengguna menekan tombol "Simpan" atau "Bayar" berkali-kali karena koneksi internet lambat. | Terbuat transaksi ganda, invoice dobel, atau stok terpotong 2x. | • Frontend: Alpine `x-bind:disabled="submitting"` + loading spinner.<br>• Backend: Idempotency key atau validasi token unik transaksi. |
| **Typo Angka Ribuan / Jutaan** | Mengetik harga `15000` padahal maksudnya `150.000` (kurang nol), atau sebaliknya kelebihan nol. | Kerugian finansial fatal atau harga jual tidak masuk akal. | • Formatter otomatis format ribuan saat mengetik (`Rp 150.000`).<br>• Konfirmasi dialog jika harga jual di bawah HPP produk. |
| **Konversi Satuan (UOM Slip)** | Menginput resep BOM: 1 kg gula dimasukkan sebagai 1 gram, atau sebaliknya. | HPP kacau balau dan stok gula berkurang drastis secara salah. | Tampilkan label satuan yang jelas di samping kolom input, dengan preview nilai total per unit secara transparan. |
| **Accidental Product Deletion** | Menghapus produk yang masih memiliki sisa stok gudang atau riwayat penjualan aktif. | Data relasi di laporan penjualan menjadi error / hilang. | Larang *hard delete* jika produk memiliki stok > 0 atau ada di transaksi aktif. Terapkan soft delete / nonaktifkan status (`is_active = false`). |
| **Salah Pilih Lokasi Gudang** | Kasir/staf menginput penerimaan barang di Gudang Cabang A padahal barang masuk ke Gudang Cabang B. | Stok Cabang A fiktif, stok Cabang B kurang. | Tampilkan nama lokasi yang jelas dengan badge warna pembeda dan modal konfirmasi sebelum finalisasi transfer. |

---

## ⚙️ Pilar 5: System Resilience, Integritas Transaksi & Audit Trail

| Aspek Ketahanan | Risiko Jika Diabaikan | Standar Wajib Sistem Cooca |
| :--- | :--- | :--- |
| **Atomic Transaction Rollback** | Terjadi error di tengah proses (misal: header order tersimpan, namun item order gagal). Data menjadi korup dan gantung (*orphan data*). | Bungkus seluruh rantai mutasi ke dalam `DB::transaction(function () { ... })`. Pastikan rollback otomatis jika ada exception. |
| **Audit Trail Immutability** | Terjadi kecurangan internal, tetapi tidak ada bukti siapa yang mengubah harga, siapa yang menghapus pesanan, dan kapan terjadi. | Catat setiap aksi berisiko ke tabel `audit_logs` dengan format terstruktur: `user_id`, `action`, `payload_before`, `payload_after`, `ip_address`, `timestamp`. |
| **Graceful External Fallback** | Layanan pihak ketiga (WhatsApp API, Email server, Biteship kurir) mengalami gangguan/timeout. | Aplikasi POS/Web tidak boleh crash atau hang. Gunakan asynchronous queue (`ShouldQueue`) dan sediakan tombol fallback manual (`wa.me`). |
| **No Data Punishment pada Billing** | Masa langganan tenant habis / downgrade, lalu sistem menghapus data produk atau transaksi. | **DILARANG KERAS MENGHAPUS DATA**. Data over-quota hanya di-suspend sementara dari penjualan, dan otomatis terbuka saat di-top-up (*Auto-Reactivation*). |
