# Modul: POS Hardware, ESC/POS Printer & Cash Drawer Integration

> **Layer 2: System Module Documentation**  
> **Lokasi Berkas:** `docs/system/modules/pos-hardware-and-printers.md`  
> **Status:** Production-Ready (Laravel 13 & PHP 8.3+)  
> **Package Inti:** `mike42/escpos-php: ^5.0`, `mike42/gfx-php: ^1.0`  

---

## 1. Ikhtisar & Arsitektur Sistem

Modul POS Hardware & Printer menyediakan integrasi perangkat keras kasir berstandar industri untuk ekosistem multi-tenant COOCA POS. Modul ini mendukung pencetakan langsung berkecepatan tinggi menggunakan format binary raw ESC/POS, manajemen laci kas (RJ-11/RJ-12 solenoid pulse), perutean tiket pesanan dapur/bar (KOT), pengujian diagnostik konektivitas real-time, serta Local POS Agent bridge untuk perangkat lokal (Bluetooth/USB serial).

```
                    COOCA POS Terminal / Web App
                                 │
                         PrinterManager
                                 │
                   ┌─────────────┴─────────────┐
                   │                           │
          Direct ESC/POS Service         Browser Print
                   │                     (Fallback Mode)
         EscposFormatter
                   │
      ┌────────────┼────────────┬────────────┐
      │            │            │            │
 NetworkConnector  │     WindowsConnector    │
   (LAN/Wi-Fi)     │      (Print Spooler)    │
  TCP Port 9100    │                         │
             FileConnector          AgentPayloadConnector
              (Direct USB/          (Local Agent Polling /
              Serial /dev/lp)         WebSocket Bridge)
                   │                         │
                   └────────────┬────────────┘
                                │
                      Thermal Printer (58mm / 80mm)
                                │
                     ┌──────────┴──────────┐
                     │                     │
               Receipt / KOT          Cash Drawer
               Ticket Print           (RJ-11 Kick Pulse)
```

---

## 2. Model & Skema Database

### 2.1 Tabel `pos_printers`
Menyimpan konfigurasi profil hardware printer per bisnis dan outlet.

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | UUID (PK) | Unique Identifier printer |
| `business_id` | UUID (FK) | Multi-tenant isolation |
| `location_id` | UUID (FK, Nullable) | Scope outlet / cabang |
| `name` | VARCHAR(100) | Label printer (misal: "Kasir Depan", "Dapur Utama") |
| `connection_type` | ENUM | `'lan'`, `'wifi'`, `'usb'`, `'bluetooth'`, `'windows'`, `'serial'`, `'agent'` |
| `interface_address`| VARCHAR(255) | IP Address (`192.168.1.100`), Queue Windows (`POS-80`), Port USB (`/dev/usb/lp0`), atau Device BT ID |
| `port` | INT (Nullable) | TCP port (default: 9100) |
| `paper_width` | ENUM | `'58mm'`, `'80mm'` |
| `character_set` | VARCHAR(50) | Default: `'CP437'` |
| `capabilities` | JSON Array | `['print_text', 'print_image', 'barcode', 'qr_code', 'cut', 'cash_drawer', 'beep']` |
| `assigned_usages` | JSON Array | `['cashier_receipt', 'kitchen_order', 'bar_order', 'shift_summary', 'customer_display']` |
| `assigned_category_ids`| JSON Array | Array UUID kategori menu yang diarahkan ke printer ini |
| `is_default` | BOOLEAN | Default printer untuk outlet terkait |
| `is_active` | BOOLEAN | Status operasional printer |
| `last_status` | ENUM | `'online'`, `'offline'`, `'error'`, `'unknown'` |
| `last_status_checked_at` | TIMESTAMP | Waktu pemeriksaan ping/koneksi terakhir |

### 2.2 Tabel `pos_print_jobs`
Antrean pencetakan asinkron untuk Local POS Agent dan retry queue.

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | UUID (PK) | Unique Identifier print job |
| `business_id` | UUID (FK) | Multi-tenant isolation |
| `location_id` | UUID (FK, Nullable) | Scope outlet |
| `pos_printer_id` | UUID (FK) | Target hardware printer |
| `document_type` | ENUM | `'receipt'`, `'kitchen_order'`, `'shift_report'`, `'test_print'`, `'drawer_pulse'` |
| `document_id` | VARCHAR(100) | ID dokumen sumber (order_id, shift_id) |
| `payload` | LONGTEXT | Raw binary ESC/POS encoded base64 |
| `payload_format` | VARCHAR(20) | Default: `'base64'` |
| `status` | ENUM | `'pending'`, `'processing'`, `'printed'`, `'failed'`, `'cancelled'` |
| `attempts` | INT | Jumlah percobaan cetak |
| `error_message` | TEXT (Nullable) | Pesan kegagalan terakhir |
| `printed_at` | TIMESTAMP (Nullable)| Waktu sukses dicetak |

---

## 3. Komponen Layanan & Abstraksi (`App\Domain\Printer`)

### 3.1 `PrinterManager`
Orkestrator utama pencetakan hardware.
- `printReceipt(PosOrder $order, ?PosPrinter $printer = null, array $options = []): array`
- `printKitchenOrders(PosOrder $order, ?string $locationId = null): array`
- `printShiftReport(PosShift $shift, ?PosPrinter $printer = null): array`
- `testPrint(PosPrinter $printer): array`
- `testCashDrawer(PosPrinter $printer, ?User $actor = null): array`
- `diagnosePrinter(PosPrinter $printer): array`

### 3.2 `EscposFormatter`
Generator raw binary ESC/POS stream berbasis `mike42/escpos-php`.
- **Profil Kolom:** Otomatis menghitung batas karakter baris (32 kolom untuk 58mm, 48 kolom untuk 80mm).
- **Rata Kiri-Kanan:** Pemformatan `formatTwoColumn()` dengan proteksi auto-truncate label panjang.
- **Tanda Salinan & Anti-Fraud:** Menyisipkan header tegas `*** SALINAN (CETAKAN KE-N) ***` pada reprint.
- **QR Code Native:** Render QR Code 2D native (`Printer::QR_ECLEVEL_M`) untuk tautan verifikasi struk digital `route('public.receipt')`.
- **Pemotong Kertas (Cut):** Perintah pemotong kertas parsial (`\x1D\x56\x01`).

### 3.3 `CashDrawerService`
Layanan kontrol keamanan dan pencegahan fraud laci kas fisik.
- **Aturan Buka Otomatis:**
  1. Status order **wajib `COMPLETED`** / Lunas.
  2. Metode pembayaran **wajib mengandung `CASH`** (Tunai).
  3. Tidak berlaku untuk reprint (`isReprint = true` atau `print_count > 1`).
  4. Tidak membuka laci untuk transaksi non-tunai murni (QRIS, Kartu Debit/Kredit, Transfer Bank).
- **Pembukaan Manual (No-Sale Pop):**
  - Wajib memasukkan **PIN Supervisor** yang valid (`business.pos_supervisor_pin`).
  - Wajib menyertakan **alasan tertulis** (misal: "Tukar uang kembalian pecahan 5.000").
  - Dicatat otomatis ke `audit_logs` dengan IP address, user ID, dan timestamp forensik.

### 3.4 `KitchenRoutingService`
Perutean otomatis pesanan multi-station kitchen & bar:
- Mengelompokkan item pesanan berdasarkan `assigned_category_ids` printer.
- Heuristik cerdas untuk minuman (kategori mengandung *minuman, drink, tea, coffee, bar*) diarahkan ke printer bar (`usage = bar_order`).
- Item makanan diarahkan ke printer dapur (`usage = kitchen_order`).
- Menghasilkan tiket terpisah per station lengkap dengan modifikasi/topping dan catatan khusus koki.

---

## 4. Local POS Agent Bridge

Untuk lingkungan di mana server Laravel berada di cloud (VPS/Cloud Server) dan printer kasir berada di jaringan lokal kasir menggunakan Bluetooth atau USB Serial, COOCA menyediakan **COOCA POS Agent**:

- **Teknologi:** Node.js daemon ringan (`hardware-agent/agent.js`).
- **Komunikasi:**
  - Mode Polling: HTTP GET `/api/v1/pos/agent/jobs` secara berkala (interval 3 detik).
  - Mode Local Server: Localhost HTTP POST `http://127.0.0.1:9898/print` dipanggil langsung oleh browser kasir.
- **Keamanan:**
  - Autentikasi menggunakan Bearer Token (Sanctum) atau Device Token.
  - Header isolasi tenant: `X-Business-Id` dan `X-Location-Id`.

---

## 5. Antarmuka Pengguna (Apple HIG Bento UI)

1. **Menu Pengaturan Printer (`/pos/printers`):**
   - 4 KPI Bento summary cards (Total Printer, Online, Kasir, Dapur/Bar).
   - Filter tab outlet / cabang.
   - Tabel profil printer desktop + Card layout touch-friendly mobile.
   - Modal form Tambah/Edit profil dengan preset tombol cepat LAN, Wi-Fi, USB, Bluetooth, Windows.
   - Tombol Uji Koneksi (Ping Latency), Uji Cetak Struk, dan Uji Buka Laci Kas dengan konfirmasi.
2. **Terminal Kasir (`/pos/terminal`):**
   - Shortcut langsung status printer di Header kasir.
   - Tombol Buka Laci Kas (No-Sale) dengan Modal Input PIN Supervisor.
   - Modal Sukses Pembayaran: Bento Grid 2x2 Action Tiles:
     - **Cetak ESC/POS (Hardware Langsung)**
     - **Cetak Dapur (KOT Multi-Station)**
     - **Browser Print (Fallback)**
     - **Kirim WhatsApp / Struk Digital**
3. **Halaman Struk (`/pos/orders/{order}/receipt`):**
   - Tombol Direct ESC/POS dengan floating feedback toast.
   - Tombol Cetak Bill (Browser Print) 100% backward compatible.
   - Tombol Cetak Ulang Salinan dengan pencatatan audit log forensik.

---

## 6. Multi-Terminal POS, Multi-Kasir & Manajemen Shift

Modul POS mendukung ekosistem multi-cabang/outlet, multi-gudang, multi-terminal (`PosRegister`), dan multi-kasir (`PosShift`):

1. **Struktur Relasi Tenant & Lokasi:**
   ```text
   Business (Tenant)
      ├── Location A (Outlet)
      │     ├── PosRegister 01 ── Assigned Default Receipt & Kitchen Printer
      │     │     └── Active Shift (Cashier 1) ── Orders & Cash Reconciliation
      │     └── PosRegister 02 ── Assigned Default Receipt & Kitchen Printer
      │           └── Active Shift (Cashier 2) ── Orders & Cash Reconciliation
      ├── Location B (Outlet)
      │     └── PosRegister 03
      └── Location C (Warehouse / Gudang Pusat)
            └── Stock Inventory & Transfer
   ```

2. **Perlindungan Shift & Penghitungan Fisik (Blind Cash Count):**
   - **Buka Shift (`openShift`):** Mendukung perincian lembaran/keping pecahan uang tunai (`100k`, `50k`, `20k`, `10k`, `5k`, `2k`, `1k`, `koin`) yang otomatis mengakumulasi modal awal kasir.
   - **Concurrency Guard:** 1 terminal/register hanya dapat memiliki 1 shift aktif terbuka dalam satu waktu, mencegah tabrakan sesi kasir.
   - **Tutup Shift (`closeShift`):** Menerapkan standar *Blind Cash Count* di mana kasir menghitung fisik uang tanpa mengetahui ekspektasi sistem terlebih dahulu. Selisih kas (`cash_difference`) otomatis dihitung dan dilabeli:
     - `Seimbang` (`diff == 0`)
     - `Kurang / Short` (`diff < 0`)
     - `Lebih / Over` (`diff > 0`)
   - **Cetak Laporan Termal ESC/POS (`printShiftReport`):** Menghasilkan struk penutupan shift kasir berformat ESC/POS ke printer thermal register terkait, lengkap dengan rincian modal awal, penjualan tunai, penjualan non-tunai, kas masuk/keluar, ekspektasi kas, fisik aktual, dan tanda tangan kasir.

3. **Isolasi Pemotongan Stok Multi-Gudang/Outlet:**
   - Transaksi kasir pada Terminal POS di Outlet A secara ketat memotong stok inventory hanya pada `location_id = Outlet A`. Stok di Outlet B dan Gudang Pusat tetap terisolasi dan aman.

---

## 7. Verifikasi & Pengujian Otomatis

Modul ini diverifikasi melalui test suite lengkap:
- `tests/Feature/Pos/PosPrinterHardwareIntegrationTest.php` (5 test methods, 36 assertions, 100% Passed):
  1. `test_can_create_and_manage_printers_with_tenant_isolation`
  2. `test_escpos_formatter_builds_valid_stream_for_58mm_and_80mm`
  3. `test_cash_drawer_safety_rules_and_anti_fraud`
  4. `test_kitchen_routing_service_routes_items_by_category`
  5. `test_local_agent_api_endpoints_and_job_lifecycle`
- `tests/Feature/Pos/PosMultiRegisterShiftAndStockFeatureTest.php` (5 test methods, 45 assertions, 100% Passed):
  1. `test_multi_cashier_open_shift_with_denominations_and_register_isolation`
  2. `test_checkouts_are_tagged_with_register_and_deduct_isolated_location_stock`
  3. `test_shift_close_with_blind_cash_count_and_variance_calculation`
  4. `test_shift_report_and_receipt_resolves_register_default_printer_and_formats_escpos`
  5. `test_shift_web_controllers_and_routes_with_tenant_isolation`
- `tests/Feature/PosBillReprintTrackingTest.php` (7 test methods, 47 assertions, 100% Passed).

