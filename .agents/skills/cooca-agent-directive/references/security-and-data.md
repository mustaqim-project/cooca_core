# COOCA - Keamanan, Data, & Matriks Audit Kesenjangan (Referensi Lengkap)

## 1. Jaminan Integritas Finansial (Non-Destruktif)

**Dilarang tanpa persetujuan eksplisit**:

- Mengubah rumus Subtotal, Diskon, Pajak/PPN, Biaya Kirim, Total Akhir.
- Mengubah rumus HPP/COGS (Moving Average / Weighted Average).
- Mengubah kalkulasi Margin Laba Kotor & Laba Bersih.
- Mengubah logika Saldo Kas, Rekonsiliasi Bank, Jurnal Akuntansi Otomatis (_double-entry_).
- Memodifikasi nilai transaksi pada nota/invoice/PO/penerimaan barang yang berstatus **selesai/paid**.
- Menghapus data produksi atau mengubah status transaksi selesai.
- Mengubah struktur database yang berisiko.

Semua perubahan finansial wajib dianalisis dan diuji secara khusus sebelum implementasi.

## 2. Isolasi Multi-Tenant (Strict Tenant Isolation)

**Dilarang** melakukan query database tanpa scoping tenant. Setiap query Eloquent/Query Builder pada entitas milik tenant wajib menyertakan scope bisnis aktif:

```php
// BENAR
$business = \App\Support\Context::requireBusiness();
$products = Product::where('business_id', $business->id)->get();

// SALAH - Kebocoran data lintas tenant!
$products = Product::all();
```

**Dilarang** membypass middleware keamanan inti: `auth:web`, `auth:admin`, `auth:customer`, `wa.otp`, `business.active`, `verified`, `require.permission:*`, `require.role:*`, `entitlement:*`.

## 3. Integritas Formulir & Proteksi Eksploitasi

- Setiap `<form>` wajib mempertahankan `@csrf`. Form `PUT`/`PATCH`/`DELETE` wajib `@method('PUT')` dst.
- Dilarang melemahkan validasi input (`required`, `numeric`, `min`, `max`, `exists`, `unique`).
- Dilarang memasukkan input mentah ke `DB::raw` tanpa parameter binding aman (cegah SQL Injection).
- Dilarang merender output HTML belum di-escape - gunakan `{{ $var }}` default Blade, hindari `{!! !!}` kecuali HTML yang sudah tersanitasi.

## 4. Larangan Penghapusan Sepihak (Zero Silent Deletions)

Penghapusan/penggabungan route lama **wajib** menyediakan redirect atau alias rute untuk menjamin backward-compatibility dan mencegah broken links pada bookmark pengguna. Setiap perombakan alur kerja wajib melewati **Interactive Confirmation Gate** terlebih dahulu.

## 5. Checklist Audit Keamanan Umum

Setiap perubahan wajib diperiksa terhadap: Authentication, Authorization, Role & permission, IDOR, CSRF, Mass assignment, Validasi request, SQL Injection, XSS, Upload berbahaya, Route exposure, Privilege escalation, Webhook security, API security, Rate limiting, Audit log, Isolasi perusahaan & cabang, Kebocoran data antar pengguna/cabang.

> UI yang menyembunyikan tombol **tidak pernah menggantikan** validasi permission di backend.

---

## 6. Matriks Audit Kesenjangan (Gap Analysis) 4-Dimensi

Setiap modul/fitur yang ditinjau wajib dianalisis batas keamanan (_security boundary_) dan kesenjangan pengalaman (_experience gap_) antar 4 kuadran:

```
┌─────────────────────────────────────────────────────────────┐
│                    SUPERADMIN (Backoffice)                  │
│   • Pengawasan Platform   • Manajemen Tenant   • Billing    │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Isolasi Ketat / Audit Trail)
┌──────────────────────────────▼──────────────────────────────┐
│                  BUSINESS OWNER & TIM KASIR                 │
│   • POS Kasir   • Stok/Gudang   • Keuangan   • Pengaturan   │
└──────────────────────────────┬──────────────────────────────┘
                               │ (Gated Checkout / IDOR Shield)
┌──────────────────────────────▼──────────────────────────────┐
│                    CUSTOMER / PEMBELI AKHIR                 │
│   • Toko Online (Storefront)   • Portal Pesanan   • Lacak   │
└──────────────────────────────▲──────────────────────────────┘
                               │ (Webhook / Fail-Safe Messaging)
┌──────────────────────────────┴──────────────────────────────┐
│                SUBSISTEM OTOMASI & BACKGROUND               │
│   • WhatsApp Gateway   • Auto-Journal   • Cron Scheduler    │
└─────────────────────────────────────────────────────────────┘
```

### Peta Kesenjangan & Mitigasi

1. **Admin vs Owner** - _Risiko_: Superadmin tidak sengaja memodifikasi stok/kas tenant saat troubleshooting. _Mandat_: setiap aksi mutasi data oleh admin wajib audit log (`admin_id` tercatat), tidak boleh memotong validasi integritas finansial.
2. **Owner vs Customer (IDOR Shield)** - _Risiko_: Customer A melihat pesanan/nota Customer B lewat tebak ID (`/customer/orders/{id}`) atau manipulasi parameter URL. _Mandat_: akses portal customer wajib verifikasi ganda - identitas global customer (`auth:customer`) + nomor WhatsApp terverifikasi OTP. Dilarang query pesanan customer jika parameter identitas `null`.
3. **Owner vs POS Staff (Privilege & Fraud Prevention)** - _Risiko_: kasir melakukan void/refund sepihak untuk penggelapan dana. _Mandat_: aksi sensitif POS (Void, Refund, Buka Laci Kas Manual) wajib verifikasi `supervisor_pin` yang di-hash (Bcrypt) dan dibatasi frekuensi (`throttle:5,1`).
4. **Otomasi vs Kegagalan Jaringan (Fail-Safe Automation)** - _Risiko_: server WhatsApp terputus sehingga invoice/struk tidak terkirim, user panik mengira transaksi gagal. _Mandat_: otomasi wajib punya fallback ramah pengguna - tombol instan "Kirim Manual via WhatsApp Web/Aplikasi HP" (ikon Lucide, tanpa emoji) dengan teks nota yang sudah terformat rapi.

---

## 7. Audit & Perlindungan Skema Fraud Internal (Internal Fraud Protection Blueprint)

Sistem ERP & POS multi-tenant wajib memiliki proteksi bawaan (*built-in guardrails*) terhadap potensi celah kecurangan internal (fraud) yang kerap terjadi di operasional UMKM, ritel, F&B, dan bengkel:

### 7.1 Skema Fraud Kasir & Front-Desk (POS Cashier Fraud)

| Skema Fraud | Modus Operandi | Mekanisme Proteksi & Guardrail Wajib |
| :--- | :--- | :--- |
| **Post-Payment Cash Void (Void Pasca Bayar)** | Kasir mencetak bill proforma/sementara, menerima uang tunai dari pelanggan, lalu membatalkan (*void*) pesanan di sistem dan mengantongi uang tunai. | • Aksi Void setelah cetak bill/struk **WAJIB butuh `supervisor_pin`** (Bcrypt hash).<br>• Catat `void_reason`, `cashier_id`, `supervisor_id`, timestamp presisi, dan snapshot item pesanan ke tabel audit log immutable.<br>• Memicu **notifikasi instan ke WhatsApp & Email Owner** jika terjadi void di luar batas toleransi. |
| **Silent Line Item Deletion (Hapus Item Siluman)** | Kasir menghapus item bernilai tinggi dari keranjang setelah pelanggan membayar tunai penuh. | • Setiap penghapusan item dari pesanan aktif yang sudah dikirim ke dapur/bengkel wajib mencatat log event `order_item_removed` dan butuh otorisasi supervisor jika melewati batas waktu toleransi. |
| **Fictitious Refund & Returns (Retur & Refund Fiktif)** | Kasir membuat transaksi retur fiktif seolah-olah ada barang dikembalikan pelanggan dan mengambil uang kas dari laci. | • Refund/Retur tunai **WAJIB mencantumkan nomor faktur/nota penjualan asli yang sah**.<br>• Wajib otorisasi Supervisor PIN.<br>• Sistem otomatis mengembalikan stok barang ke inventaris (*auto-restock*) secara transparan dan menghasilkan jurnal pembalik kas & HPP.<br>• Notifikasi refund instan ke WhatsApp Owner. |
| **Unauthorized / Fake Discounts (Diskon Liar)** | Kasir memberikan diskon pertemanan manual tanpa izin, atau menagih harga penuh ke pelanggan lalu memasukkan diskon di sistem dan mengantongi selisihnya. | • Diskon manual di atas persentase/nominal tertentu (misal > 10% atau > Rp 50.000) wajib approval Supervisor.<br>• Setiap diskon manual wajib memiliki alasan terstruktur (`discount_reason_id` / catatan). |
| **No-Sale Drawer Pop (Buka Laci Kas Manual)** | Membuka laci kas fisik tanpa transaksi penjualan untuk mengambil uang kas kecil. | • Setiap trigger perintah `open_cash_drawer` (tanpa transaksi settlement) wajib mencatat log audit lengkap dengan alasan (`reason_text`).<br>• Rate limiting (`throttle:3,1`) dan alert ke dashboard owner jika frekuensi buka laci tanpa transaksi melebihi batas. |
| **Shift End Cash Tampering (Manipulasi Tutup Kasir)** | Kasir melihat total ekspektasi kas di sistem sebelum menghitung fisik, lalu mengambil kelebihan kas atau menutupi selisih minus dengan pengeluaran palsu. | • **Wajib Blind Cash Count (Tutup Kasir Buta)**: Kasir menginput total uang fisik di laci tanpa melihat angka ekspektasi sistem terlebih dahulu.<br>• Sistem membandingkan nominal fisik vs sistem, menghitung selisih kas (*over/short*), mengunci shift, membuat jurnal selisih kas otomatis, dan mengirim laporan tutup shift ke WhatsApp Owner. |

### 7.2 Skema Fraud Gudang, Logistik, & Pengadaan (Inventory & Procurement Fraud)

| Skema Fraud | Modus Operandi | Mekanisme Proteksi & Guardrail Wajib |
| :--- | :--- | :--- |
| **Phantom Stock Write-off (Penyesuaian Stok Minus Fiktif)** | Staf gudang melakukan *stock adjustment* minus dengan dalih barang rusak/hilang/kadaluarsa untuk menutupi pencurian fisik barang. | • Penyesuaian stok manual bernilai di atas threshold (misal > Rp 100.000 atau > 5 unit) **WAJIB persetujuan Supervisor/Owner (Maker-Checker)**.<br>• Wajib mengunggah foto bukti fisik / Berita Acara dan memilih alasan baku.<br>• Audit trail mencatat `stock_before`, `stock_after`, nilai rupiah selisih, dan identitas pemohon serta penyetujui. |
| **Ghost Vendors & Purchase Mark-up (Vendor Siluman & Mark-up PO)** | Membuat pesanan pembelian (PO) ke supplier fiktif atau membeli barang dengan harga mark-up tidak wajar demi kickback pribadi. | • Standar **Three-Way Matching**: Pencairan tagihan pembelian (AP) wajib mencocokkan Purchase Order (PO) ↔ Bukti Penerimaan Barang (GRN) ↔ Faktur Tagihan Supplier (Supplier Invoice).<br>• Master data supplier dan rekening bank supplier dilindungi permission khusus (`suppliers.manage`). |
| **Receiving Discrepancies (Penerimaan Barang Kurang)** | Barang fisik yang datang kurang dari pesanan (misal 80 dari 100 pcs), namun dicatat diterima penuh di sistem demi bagi hasil dengan oknum kurir. | • Penerimaan barang wajib mencatat nama penerima, foto surat jalan, nomor batch/serial, dan status penerimaan parsial (*Partially Received*) yang jelas.<br>• Notifikasi otomatis ke bagian pengadaan dan owner jika kuantitas diterima tidak sesuai PO. |
| **Unauthorized Inter-Branch Stock Transfers (Transfer Gelap Antar-Cabang)** | Mengeluarkan stok dengan status "Transfer ke Cabang Lain", namun barang tidak pernah dikirim dan dijual secara pribadi. | • **Two-Step Transfer Verification**: Cabang asal mengeluarkan stok dengan status `In-Transit`. Stok baru diakui bertambah di cabang tujuan setelah cabang tujuan memverifikasi dan mengonfirmasi penerimaan fisik (`Received`).<br>• Jika dalam waktu $X$ jam/hari transfer belum dikonfirmasi, sistem otomatis membunyikan alert anomali transfer di dashboard dan email logistik. |
| **Phantom Scrap / Waste Declaration (Limbah Fiktif Produksi/BOM)** | Mencatat bahan baku terbuang dalam jumlah berlebih saat proses perakitan/resep untuk mengambil sisa bahan baku asli. | • Sistem membandingkan konsumsi aktual vs batas toleransi standar resep (*BOM Yield Tolerance*). Jika variansi scrap melampaui toleransi, sistem menandai transaksi sebagai `Anomali Produksi` dan menuntut konfirmasi Supervisor. |

### 7.3 Skema Fraud Keuangan, Akuntansi, & Piutang (Financial & AR Fraud)

| Skema Fraud | Modus Operandi | Mekanisme Proteksi & Guardrail Wajib |
| :--- | :--- | :--- |
| **Accounts Receivable Skimming / Lapping (Penggelapan Setoran Piutang)** | Pelanggan melunasi piutang/kasbon secara tunai atau transfer, namun staf tidak mencatat pelunasan, menggunakan uangnya, lalu menutupinya dengan setoran pelanggan berikutnya (*lapping*). | • Setiap pelunasan piutang **otomatis menerbitkan kuitansi/tanda terima digital** dan mengirim WhatsApp/Email konfirmasi langsung ke nomor pelanggan.<br>• Pelunasan piutang otomatis membukukan kas/bank masuk secara instan tanpa jeda.<br>• Fitur pengingat piutang otomatis (*Auto-Reminder*) berjalan langsung ke pelanggan — jika pelanggan menerima tagihan padahal sudah membayar, pelanggan akan langsung melapor ke Owner. |
| **Backdating & Future-dating (Manipulasi Tanggal Transaksi)** | Mengubah tanggal transaksi mundur ke periode masa lalu untuk mengubah angka laba/rugi, menutupi selisih kas, atau memanipulasi laporan pajak. | • **Accounting Period Lock (Kunci Buku)**: Transaksi di tanggal sebelum periode aktif terkunci tidak dapat dibuat, diubah, atau dihapus.<br>• Sistem membedakan secara mutlak antara `transaction_date` (tanggal bisnis) dan `created_at` (timestamp nyata server). Selisih tanggal > 1 hari otomatis dicatat ke log anomali. |
| **Silent Journal Modification (Modifikasi Jurnal Akuntansi)** | Mengedit atau menghapus record jurnal akuntansi secara langsung untuk menyamarkan pengeluaran gelap. | • **Prinsip Double-Entry Immutability**: Jurnal yang telah terposting (*posted journal*) dilarang keras di-hard-delete atau diedit nilainya.<br>• Koreksi pembukuan WAJIB menggunakan **Jurnal Pembalik / Jurnal Penyesuaian (Adjustment Entry)** yang mereferensikan nomor jurnal asal. |
| **Ghost Bank Ledger / Mutasi Kas Gelap** | Mencatat mutasi kas keluar tanpa bukti pengeluaran riil atau mengarahkan pembayaran ke rekening pribadi. | • Rekonsiliasi Bank berkala dengan pencatatan nomor referensi bank dan upload bukti mutasi.<br>• Kode QRIS statis/dinamis dan nomor rekening pembayaran pada checkout dilindungi middleware dan terenkripsi. |

### 7.4 Standar Kontrol Internal, Maker-Checker, & Audit Trail Immutability

1. **Struktur Record Audit Log Immutable**:
   Setiap aksi berisiko (Void, Refund, Stock Adjustment, Manual Discount, Kas Keluar, Perubahan Role/Permission, Reset Shift) wajib menyimpan data terstruktur ke tabel `audit_logs`:
   ```php
   [
       'business_id'   => $businessId,
       'branch_id'     => $branchId,
       'user_id'       => $userId,
       'action'        => 'pos.order.void', // Format dot-notation standar
       'module'        => 'POS',
       'record_type'   => Order::class,
       'record_id'     => $order->id,
       'ip_address'    => request()->ip(),
       'user_agent'    => request()->userAgent(),
       'payload_before'=> json_encode($originalState),
       'payload_after' => json_encode($updatedState),
       'reason_notes'  => $validatedReason,
       'authorized_by' => $supervisorUserId, // Null jika aksi standar
       'created_at'    => now(),
   ]
   ```
2. **Maker-Checker Principle (Dual Authorization)**:
   Aksi yang bernilai finansial signifikan atau merusak data tidak boleh dieksekusi oleh satu orang saja. Pembuat aksi (*Maker*) mengajukan permohonan, dan pihak berwenang (*Checker/Approver*) memvalidasi sebelum mutasi data resmi diaplikasikan.
3. **Anomaly Detection Rules & Auto-Flagging**:
   Sistem wajib mendeteksi pola anomali berulang secara otomatis:
   - Lebih dari 3x Void dalam 1 shift kasir.
   - Lebih dari 2x pembukaan laci kas tanpa transaksi (*No-sale drawer pop*) dalam 1 jam.
   - Nilai selisih kas tutup shift > Rp 20.000 atau > 2% dari omzet shift.
   - Penyesuaian stok minus berkali-kali pada SKU bernilai tinggi.
   - Transaksi dilakukan di luar jam operasional toko yang ditentukan.
   Saat terdeteksi, sistem otomatis mengibarkan bendera anomali (`flagged_suspicious`) dan memicu notifikasi peringatan ke UI, Email, dan WhatsApp Owner.
