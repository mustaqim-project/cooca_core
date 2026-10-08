# COOCA MY OWN — B2B MOBILE APP OPERATING SYSTEM
## Master Product Requirements Document (PRD), Architecture & Implementation Blueprint

---

## 1. Executive Summary & Product Vision
**COOCA MY OWN** adalah mobile companion resmi untuk ekosistem ERP multi-tenant COOCA. Dirancang sebagai **Mobile Business Operating System** untuk pemilik usaha, manajer, kasir, koki, dan staf operasional, aplikasi ini memungkinkan eksekusi bisnis harian berjalan lincah dari smartphone tanpa mengharuskan pengguna membuka komputer desktop atau laptop.

### Core Tenet & Single Source of Truth
* **Backend COOCA Web adalah Satu-Satunya Sumber Kebenaran (*Single Source of Truth*):** Seluruh kalkulasi finansial, harga modal (HPP), logika stok, pemotongan resep (BOM), aturan diskon, pajak, validasi multi-tenant, dan otorisasi hak akses diatur dan dieksekusi oleh Backend Laravel COOCA.
* **Mobile Bukan Otoritas Bisnis:** Aplikasi mobile bertindak sebagai *presentation and interaction layer* cerdas berkinerja tinggi, dilengkapi *offline caching* lokal yang aman untuk operasi kasir dan presensi.
* **Bukan Marketplace Pembeli:** COOCA My Own terisolasi 100% dari pengalaman belanja konsumen publik. Tidak ada katalog marketplace konsumen di dalam aplikasi ini.

---

## 2. Scope & Strict Non-Goals

### In-Scope (Lingkup Fitur Resmi)
1. **Multi-Role Adaptive Workspace:** Dashboard dinamis berdasar permission pengguna (Owner, Manager, Supervisor, Admin, Cashier, Kitchen, Warehouse, Finance, Staff).
2. **Context Switching Instan:** Ganti profil bisnis (*Multi-Business*) dan cabang/outlet (*Multi-Location*) dalam satu ketukan tanpa login ulang.
3. **Mobile POS Terminal (Kasir Cepat):** Katalog produk, varian, add-on modifier, keranjang dinamis, diskon, pajak, multi-metode pembayaran (Tunai, Transfer, QRIS), split payment, print struk thermal bluetooth/WiFi, hold/resume order, dan otorisasi supervisor PIN.
4. **Kitchen Display System (KDS):** Antrean tiket pesanan dapur real-time berbasis industri FnB, kolom status (*Masuk, Dimasak, Siap Saji*), timer keterlambatan, station routing, dan audio/visual chime alert.
5. **Smart HRM & Geofenced Attendance:** Presensi mandiri karyawan berbasis verifikasi koordinat GPS (akurasi tinggi <250m), biometrik wajah anti-spoofing, deteksi keterlambatan berbasis shift & jam operasional, kalkulasi lembur, pengajuan koreksi absen, serta ringkasan slip gaji.
6. **Operational Inventory & Stock Movements:** Monitoring stok real-time, pencatatan mutasi stok cepat, stock opname fisik gudang, dan transfer antar cabang.
7. **Native COOCA Marketplace Orders Hub:** Pengelolaan pesanan masuk dari toko online internal COOCA (`commerce_orders`), verifikasi pembayaran, request pickup kurir ekspedisi via Biteship API, cetak label thermal resi pengiriman AWB, dan pelacakan status.
8. **Reporting & Executive Analytics:** Grafik omzet, laba kotor, tren penjualan, jam sibuk (*hourly heatmap*), produk terlaris, dan kasir terbaik dengan filter fleksibel.

### Strict Non-Goals (Dilarang Masuk ke Aplikasi Mobile)
* ❌ **Integrasi Eksternal Shopee, TikTok Shop, & Tokopedia:** Tidak ada login Shopee/TikTok/Tokopedia, tidak ada sinkronisasi API marketplace pihak ketiga, dan tidak ada pesanan marketplace luar di aplikasi mobile ini. Fitur omnichannel marketplace eksternal tetap berada di **COOCA Web Omnichannel Hub**.
* ❌ **Registrasi & Pembelian Konsumen Publik:** Aplikasi ini tidak menyediakan registrasi untuk pembeli umum atau keranjang belanja antar-toko.
* ❌ **Mesin Akuntansi & Jurnal Mandiri di Ponsel:** Ponsel tidak menghitung atau menyusun jurnal pembukuan ganda secara lokal; seluruh jurnal akuntansi otomatis digenerate oleh backend service saat transaksi tervalidasi.

---

## 3. System Architecture & Topology

```
┌────────────────────────────────────────────────────────────────────────┐
│                        COOCA MY OWN MOBILE APP                         │
│   (Flutter / React Native — Apple HIG Bento Design, Local SQLite Cache)│
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │ HTTPS / REST JSON / SSE / WSS
                                   │ Bearer Token (Laravel Sanctum)
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│                         COOCA API GATEWAY (V1)                         │
│          (/api/v1/ — Throttle, Rate Limit, Context Middleware)         │
├────────────────────────────────────────────────────────────────────────┤
│  • auth:sanctum (Token Authentication & Device Security)              │
│  • business.active (Multi-Tenant Scoping via Context::requireBusiness) │
│  • require.permission:* (Granular Permission Gates)                   │
│  • entitlement:* (SaaS Subscription Tier Feature Enforcement)         │
├────────────────────────────────────────────────────────────────────────┤
│                           CORE DOMAIN SERVICES                         │
│  ┌─────────────────────────┬─────────────────────────┬──────────────┐  │
│  │ PosOrderService         │ AttendanceService       │ StockService │  │
│  │ PosShiftService         │ FaceVerificationService │ BiteshipApi  │  │
│  │ PosKitchenService       │ WorkScheduleService     │ TripayEngine │  │
│  │ LoyaltyService          │ TimezoneHelper          │ AuditLogger  │  │
│  └─────────────────────────┴─────────────────────────┴──────────────┘  │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│                        COOCA CORE DATABASE                             │
│       (PostgreSQL / MySQL — Multi-Tenant Shared Database Isolation)    │
│  users, business_memberships, locations, products, pos_orders,        │
│  commerce_orders, attendances, stock_movements, audit_logs            │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 4. User Personas & Adaptive Role Workspace

| Persona | Hak Akses Utama | Default Screen | Komponen UI Utama |
| :--- | :--- | :--- | :--- |
| **Owner (Pemilik Usaha)** | Akses Penuh Bisnis & Laporan Finansial | Executive Dashboard | Kartu Omzet Hari Ini, Margin Laba, Sisa Kas, Ringkasan Hadir Karyawan, Peringatan Stok Kritis, Pesanan Masuk Storefront. |
| **Manager / Supervisor** | Operasional Toko, Shift, Void POS, HR Approval | Operational Hub | Monitoring Shift Kasir Aktif, Otorisasi Void/Refund PIN, Approval Koreksi Absen, Log Mutasi Stok Gudang. |
| **Kasir (Cashier)** | Terminal POS, Buka/Tutup Shift, Transaksi | POS Register | Grid Katalog Produk, Pemindai Barcode, Keranjang Cepat, Split Bayar, Cetak Struk Thermal, Rekapitulasi Kas Blind Count. |
| **Koki / Kitchen Crew** | Layar Dapur KDS | Kitchen Display (KDS) | Tiket Antrean Pesanan Tiket Dapur, Timer Memasak, Rincian Resep/Modifier, Tombol Tandai Masak / Siap Saji. |
| **Staf Gudang (Warehouse)** | Penerimaan Barang, Stok, Opname | Gudang & Logistik | Daftar Stok per Lokasi Gudang, Form Mutasi Antar-Cabang, Rekonsiliasi Opname Fisik, Penerimaan Barang PO (GRN). |
| **Karyawan / Staf Biasa** | Presensi Mandiri, Jadwal, Profil | Portal Karyawan | Tombol Clock-In/Clock-Out GPS & Kamera Wajah, Jadwal Shift Hari Ini, Riwayat Presensi, Sisa Cuti, Slip Gaji Digital. |

---

## 5. Mobile Information Architecture (IA)

Navigasi mengadopsi **Adaptive Bottom Navigation Bar (Apple Human Interface Guidelines)** yang secara otomatis mengonfigurasi tab berdasarkan izin pengguna (*Dynamic Permission-Driven Navigation*):

```
┌─────────────────────────────────────────────────────────────────────────┐
│              COOCA MY OWN — DYNAMIC BOTTOM NAVIGATION BAR               │
├─────────────────────────────────────────────────────────────────────────┤
│ [ Tab 1: Home / Dashboard ]                                             │
│   ├── Owner/Manager: Executive Dashboard & Alert Center                 │
│   ├── Kasir: POS Home & Status Shift                                    │
│   ├── Dapur: KDS Live Monitor                                           │
│   └── Karyawan: Portal Presensi & Jadwal Kerja                          │
│                                                                         │
│ [ Tab 2: POS (Point of Sale) ] (Kondisional: pos.terminal)              │
│   ├── Register / Katalog / Barcode Scanner                              │
│   ├── Keranjang Belanja & Modifier Selector                             │
│   ├── Antrean Order Tertahan (Held Orders)                              │
│   └── Manajemen Kas & Shift (Buka/Tutup Kasir)                          │
│                                                                         │
│ [ Tab 3: Operasional / Pesanan ] (Kondisional: pos.orders / kds / marketplace) │
│   ├── Sub-tab 1: Dapur KDS (pos.kitchen + industry FnB)                │
│   ├── Sub-tab 2: Pesanan POS (Riwayat Nota & Void)                      │
│   └── Sub-tab 3: Pesanan Online COOCA (commerce_orders + Pickup Biteship)│
│                                                                         │
│ [ Tab 4: Bisnis & Laporan ] (Kondisional: reports.view / inventory.view) │
│   ├── Laporan Penjualan (Ringkasan, Top Produk, Metode Pembayaran)      │
│   ├── Manajemen Stok & Mutasi Gudang                                    │
│   └── Manajemen Karyawan & Otorisasi Absen                              │
│                                                                         │
│ [ Tab 5: Akun & Pengaturan ] (Selalu Tersedia)                          │
│   ├── Pemilih Bisnis & Outlet Aktif (Context Switcher)                  │
│   ├── Konfigurasi Printer Thermal (Bluetooth / Network IP)              │
│   ├── Keamanan (PIN Supervisor, Biometrik App Lock, Sesi Perangkat)     │
│   └── Bantuan & Logout                                                  │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 6. Detailed Feature Specifications

### 6.1 Authentication & Context Switching
* **Login Multi-Faktor:** Mendukung email & password via endpoint `/api/v1/auth/login`, menerbitkan token personal Laravel Sanctum.
* **Biometric App Lock:** Setelah login pertama kali, aplikasi dapat dikunci menggunakan FaceID / Fingerprint lokal perangkat (menggunakan iOS Keychain / Android Keystore) tanpa perlu request ulang token ke server.
* **Business & Outlet Switcher:**
  - `GET /api/v1/me/businesses` mengambil daftar bisnis yang berhak diakses pengguna.
  - `POST /api/v1/me/active-business` menyimpan `active_business_id` pada sesi token.
  - `GET /api/v1/locations` mengambil daftar outlet/cabang aktif pada bisnis terpilih.
  - Header request otomatis menyertakan konteks lokasi: `X-Location-ID: {location_uuid}`.
* **Manajemen Sesi:** Fitur *Logout from Other Devices* via `/api/v1/profile/logout-all` yang mencabut seluruh token Sanctum lainnya demi keamanan saat perangkat hilang.

### 6.2 Mobile POS (Point-of-Sale) Engine
* **Bootstrap Dataset Cepat (`GET /api/v1/pos/terminal/bootstrap`):** Memuat kategori produk, katalog produk terdaftar, stok aktual cabang, daftar pelanggan aktif, pesanan tertahan (*held orders*), dan status shift aktif dalam 1 payload JSON terkompresi.
* **Transaksi Kasir & Split Payment:**
  - Mendukung pesanan *Takeaway*, *Dine-in* (dengan nomor meja), dan *Delivery*.
  - Mendukung array pembayaran fleksibel: kombinasi Tunai + Transfer Bank, Tunai + QRIS, atau Kartu Debit.
  - Mendukung voucher promo, diskon persentase/nominal, dan tukar poin loyalitas CRM.
* **Otorisasi Supervisor Terintegrasi:**
  - Operasi sensitif (*Void Pesanan, Refund Uang, Diskon Manual Melebihi Batas Kasir*) memicu popup aman **Supervisor Authorization PIN** (`POST /api/v1/pos/verify-pin`).
  - Rate limiting ketat: 5 percobaan gagal per menit mengunci otorisasi sementara untuk mencegah serangan brute-force.
* **Manajemen Shift Kasir (*Blind Cash Count*):**
  - Kasir wajib membuka shift dengan mencatat modal awal kas (*opening cash*).
  - Saat tutup shift, kasir memasukkan total uang fisik di laci kas tanpa diberitahu jumlah kalkulasi sistem (*Blind Count*).
  - Sistem otomatis menghitung selisih kas (*cash variance*), mencatat audit log, dan menerbitkan struk penutupan shift ke printer thermal.
* **Cetak Struk Thermal (ESC/POS):**
  - Driver printer bawaan di mobile app terhubung langsung via Bluetooth SPP/BLE atau Ethernet TCP/IP ke printer thermal 58mm dan 80mm.
  - Template struk memuat nama outlet, nomor nota, nama kasir, item belanja, rincian pajak, diskon, cara bayar, kembalian, dan QR Code verifikasi.

### 6.3 Kitchen Display System (KDS)
* **Kriteria Akses:** Menu KDS hanya aktif jika:
  1. Pengguna memiliki permission `pos.kitchen`.
  2. Industri bisnis adalah Food & Beverage / Resto / Cafe (`pos_dinein` module active).
  3. Kuota paket langganan mencakup fitur KDS (`entitlement:kds`).
* **Siklus Status Pesanan KDS:**
  - **Pesanan Baru (Incoming):** Tiket pesanan baru berkedip dengan indikator suara denting lembut, menampilkan nomor meja, daftar menu, varian pedas/manis, dan catatan khusus pelanggan.
  - **Sedang Dimasak (Cooking):** Koki menekan tiket untuk mengubah status menjadi sedang diproses. Timer aktif menghitung durasi memasak. Jika melebihi 15 menit, kartu berganti warna kuning/merah sebagai peringatan urgensi.
  - **Siap Saji (Ready):** Makanan selesai dimasak; waiter/kasir menerima sinyal bahwa pesanan siap diantar ke meja pelanggan.
  - **Selesai (Completed):** Pesanan telah disajikan.
* **Protokol Sinkronisasi:** Menggunakan **Smart Adaptive Polling** (interval 3-5 detik saat layar aktif, 30 detik saat diminimalkan) atau Server-Sent Events (SSE) `/api/v1/pos/kitchen/stream` untuk memastikan nol keterlambatan koordinasi antar-dapur dan kasir.

### 6.4 Smart HRM & Attendance Engine
* **Penentuan Waktu Berbasis Multi-Timezone Otomatis:**
  - Waktu transaksi dan presensi dihitung menggunakan `TimezoneHelper::resolve($business, $location)`.
  - Sistem melarang keras penggunaan jam lokal smartphone atau UTC statis; jam server disinkronkan ke zona waktu resmi outlet (WIB, WITA, atau WIT).
* **Clock-In & Clock-Out Cerdas (`POST /api/v1/attendance/check-in`):**
  - **GPS Geofence Validation:** Menghitung jarak geodesic menggunakan formula Haversine (`AttendanceService::calculateDistanceMeters`). Presensi ditolak jika berada di luar radius toleransi cabang (default 50–100 meter).
  - **Anti-Mock / Fake GPS Shield:** Memeriksa akurasi sinyal GPS (`accuracy <= 250m`) dan mendeteksi flag *mock location* dari sistem operasi Android/iOS.
  - **Verifikasi Biometrik Wajah:** Mengambil foto swafoto (*selfie*) karyawan dan mencocokkannya dengan template embedding wajah terdaftar (`face_data`) via `FaceVerificationService` dengan threshold kemiripan standar 80%.
  - **Deteksi Keterlambatan Berbasis Shift:** Sistem mencocokkan jam masuk dengan jadwal shift karyawan (`WorkScheduleService`). Jika melewati jam kerja yang ditentukan, status presensi otomatis ditandai sebagai **Terlambat (*Late*)** beserta durasi keterlambatannya (menit).
* **Pengajuan Koreksi & Izin Mandiri:**
  - Karyawan dapat mengajukan perbaikan presensi lupa absen via `/api/v1/attendance/corrections` beserta alasan dan foto bukti.
  - Manajer menerima notifikasi dan dapat menyetujui (*Approve*) atau menolak (*Reject*) langsung dari aplikasi mobile.

### 6.5 Native COOCA Marketplace Order Management Hub
* **Bukan Integrasi Eksternal:** Khusus mengelola pesanan yang dibuat oleh pembeli melalui etalase toko online resmi COOCA (`commerce_orders`).
* **Alur Pemenuhan Pesanan Merchant:**
  1. **Pesanan Masuk (*Order Placed*):** Merchant menerima push notification instan saat ada pesanan baru.
  2. **Verifikasi Pembayaran:**
     - Jika via TriPay Gateway: Status otomatis terverifikasi lunas via webhook backend (`payment_status = paid`).
     - Jika via Transfer Bank Manual: Merchant meninjau foto bukti bayar (`commerce_payment_proofs`) lalu menekan tombol `[ Verifikasi & Proses ]` atau `[ Minta Bukti Ulang ]`.
  3. **Penyiapan Paket (*Packing*):** Merchant mengemas barang dan memilih opsi pengiriman.
  4. **Ekedisi Terintegrasi Biteship:**
     - Merchant menekan tombol `[ Request Pickup Biteship ]`. Backend memanggil Biteship Order API, memesan penjemputan kurir (JNE/SiCepat/J&T/GoSend), dan menerima nomor resi AWB (`shipping_waybill_id`).
     - Merchant mencetak label resi pengiriman berformat thermal 100x150mm langsung ke printer Bluetooth.
  5. **Pelacakan Live:** Status pesanan secara otomatis berpindah ke *Shipped* ➔ *In Transit* ➔ *Delivered* ➔ *Completed*.

---

## 7. Offline & Synchronization Architecture

Operasional kasir ritel dan restoran tidak boleh terhenti saat koneksi internet terputus. COOCA My Own menerapkan strategi **Safe Offline-First POS Resilience**:

```
┌────────────────────────────────────────────────────────────────────────┐
│                         OFFLINE EVENT LIFECYCLE                        │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Local Cache Storage (SQLite Encrypted / WatermelonDB)               │
│    • Katalog produk, harga, varian, dan data pelanggan di-cache lokal. │
│ 2. Offline Cash Transaction Queue                                      │
│    • Saat offline, HANYA transaksi tunai (Cash) yang diizinkan.        │
│    • Transaksi QRIS/Transfer dinonaktifkan karena butuh verifikasi bank│
│    • Diberikan UUID Client Idempotency: idempotency_key = UUIDv4       │
│ 3. Background Sync Queue Worker                                        │
│    • Saat internet kembali terhubung: antrean pesanan dikirim bertahap │
│    • Backend memvalidasi idempotency key untuk mencegah order ganda.   │
│ 4. Conflict Resolution & Stock Deduction                               │
│    • Pengurangan stok fisik diproses secara berurutan di server.       │
│    • Jika terjadi minus stok, sistem mencatat 'negative stock alert'   │
│      tanpa membatalkan nota yang sudah diberikan ke pembeli.           │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 8. Role & Permission Matrix (Backend Parity)

| Permission Slug | Owner | Manager | Admin | Cashier | Kitchen | Warehouse | Staff |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| `dashboard.view` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `pos.terminal` | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| `pos.kitchen` | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| `pos.orders` | ✅ | ✅ | ✅ | ✅ (shift sendiri)| ❌ | ❌ | ❌ |
| `pos.supervisor_pin` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `inventory.view` | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `inventory.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| `reports.view` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `reports.financial` | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `costing.view_margin`| ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `attendance.self` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `users.manage` (HR) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `marketplace.manage` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 9. API Gap Analysis & Endpoints Inventory

| Domain / Fitur | Endpoint URL | HTTP Method | Status di Backend Saat Ini | Kebutuhan Implementasi / Penyesuaian |
| :--- | :--- | :---: | :--- | :--- |
| **Auth** | `/api/v1/auth/login` | POST | ✅ Sudah Ada | Tambahkan response payload daftar bisnis & permissions user. |
| **Auth** | `/api/v1/auth/me` | GET | ✅ Sudah Ada | Sertakan token abilities & role detail. |
| **Context** | `/api/v1/me/active-business` | POST | ✅ Sudah Ada | Sudah mendukung pemindahan tenant aktif. |
| **POS** | `/api/v1/pos/terminal/bootstrap` | GET | ✅ Sudah Ada | Siap pakai; mengembalikan produk, kategori, pelanggan, shift. |
| **POS** | `/api/v1/pos/terminal/checkout` | POST | ✅ Sudah Ada | Sudah mendukung split payment, order type, voucher, diskon. |
| **POS** | `/api/v1/pos/verify-pin` | POST | ✅ Sudah Ada | Sudah mendukung validasi supervisor PIN dengan rate limit 5 req/min. |
| **POS Shift** | `/api/v1/pos/shifts/open` & `/close` | POST | ✅ Sudah Ada | Sudah mendukung blind cash count & rekonsiliasi kas. |
| **KDS** | `/api/v1/pos/kitchen/orders` | GET | ⚠️ Hanya di Web Route | **Wajib Ditambahkan:** Ekspos endpoint `/api/v1/pos/kitchen/orders` ke `routes/api.php` dengan Sanctum auth. |
| **KDS** | `/api/v1/pos/kitchen/{order}/status`| POST | ⚠️ Hanya di Web Route | **Wajib Ditambahkan:** Ekspos update status tiket dapur ke `routes/api.php`. |
| **Attendance** | `/api/v1/attendance/check-in` | POST | ✅ Sudah Ada | Sudah mendukung GPS geofence, akurasi, dan verifikasi wajah. |
| **Attendance** | `/api/v1/attendance/check-out` | POST | ✅ Sudah Ada | Sudah menghitung jam kerja, lembur, dan durasi shift. |
| **Attendance** | `/api/v1/attendance/today` | GET | ✅ Sudah Ada | Mengambil status presensi hari ini untuk antarmuka karyawan. |
| **Storefront Order**| `/api/v1/commerce/orders` | GET | ⚠️ Hanya di Web Route | **Wajib Ditambahkan:** API listing pesanan online merchant untuk diproses di mobile app. |
| **Storefront Order**| `/api/v1/commerce/orders/{id}/pickup` | POST | ⚠️ Hanya di Web Controller | **Wajib Ditambahkan:** API request pickup kurir Biteship langsung dari mobile app. |
| **Push Notification**| `/api/v1/notifications/device-token` | POST | ❌ Belum Ada | **Wajib Dibuat:** Pendaftaran token FCM/APNs per user device. |

---

## 10. Security & Anti-Fraud Architecture
1. **Zero-Trust Mobile Client:** Mobile tidak pernah menentukan harga jual, total bayar, potongan diskon, atau keabsahan voucher. Semua nilai dikalkulasi ulang di backend.
2. **Anti-Tampering Device Clock:** Waktu kehadiran dan transaksi diambil dari jam atomik server database yang diselaraskan dengan zona waktu cabang (`TimezoneHelper`), mengabaikan manipulasi jam manual di ponsel.
3. **Supervisor PIN Hash Bcrypt:** PIN otorisasi supervisor tidak pernah disimpan di lokal penyimpanan ponsel, melainkan dikirim terenkripsi HTTPS dan dicocokkan via Bcrypt hash di backend.
4. **Screenshot & Screen Recording Protection:** Layar laporan finansial laba rugi dan otorisasi PIN dapat diaktifkan proteksi `FLAG_SECURE` (Android) dan pemburaman saat multitasking (iOS) demi mencegah kebocoran data sensitif perusahaan.
5. **Session Expiry & Revocation:** Jika karyawan dinonaktifkan atau diubah permission-nya di web backoffice, sesi token Sanctum di mobile app seketika ditolak dengan respon HTTP 401/403.

---

## 11. UI/UX Design System Guidelines (Apple HIG Bento)
* **Karakter Visual:** Minimalis, bersih, profesional, terinspirasi oleh Apple iOS HIG dan Bento Grid Architecture.
* **Tipografi:** SF Pro Display / Inter dengan angka presisi berformat `tabular-nums` (`font-mono` / `tabular-nums`) untuk nominal uang dan sisa stok.
* **Aksesibilitas Usia 40–65 Tahun:**
  - Ukuran touch target tombol aksi utama minimal **48px hingga 52px**.
  - Ukuran teks kolom formulir minimal **16px** (mencegah auto-zoom Safari iOS).
  - Kontras teks tinggi (WCAG AAA) tanpa teks redup yang sulit dibaca di bawah terik matahari.
  - Zero-Emoji pada label sistem operasional; menggunakan icon Lucide yang jelas dan konsisten.
* **Form & Modal Sheet:** Mengadopsi **Full Bottom Sheet** dengan grab-handle khas iOS, memungkinkan pengisian data cepat dengan satu jempol (*One-Thumb Reachability*).
