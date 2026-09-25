# COOCA - Otomasi, Testing Wajib, & Production Hardening (Referensi Lengkap)

## 1. Mandat Otomasi Sistem Penuh

COOCA dirancang bekerja **secara otonom untuk pengguna**, bukan menuntut input manual berulang. Segala alur yang dapat diotomasi **wajib** diotomasi - namun jangan menambah otomasi yang belum dipahami dampaknya terhadap data, workflow, dan integrasi.

1. **Auto-Journaling** - Transaksi penjualan POS, order toko online, pembelian bahan baku/PO, pengeluaran kas, penerimaan piutang, dan retur barang wajib otomatis menghasilkan jurnal akuntansi berimbang (Debit = Kredit) tanpa pemilik toko perlu paham kode akun.
2. **Auto-Stock & Auto-BOM** - Setiap penjualan menu/racikan/paket barang otomatis memotong saldo stok bahan baku berdasarkan resep (_Bill of Materials_).
3. **Auto-Invoice & WhatsApp Dispatch** - Sesaat setelah pesanan dibayar/dibuat, sistem otomatis menerbitkan invoice digital dan mengirim WhatsApp berisi ringkasan nota + tautan struk resmi, tanpa kasir mengetik manual.
4. **Auto-Reminder Piutang & Hutang** - Pengingat otomatis via WhatsApp & dashboard untuk invoice mendekati/lewat jatuh tempo, bahasa Indonesia santun dan profesional.
5. **Auto-Reconciliation & Status Engine** - Transisi status _Menunggu Pembayaran → Diproses → Siap Diambil/Dikirim → Selesai_ berjalan otomatis dipicu webhook pembayaran atau aksi kasir 1-klik.

Area otomasi lain yang perlu diaudit & dipertimbangkan: jurnal akuntansi, pemotongan stok, BOM/resep, invoice, nota digital, notifikasi WhatsApp, pengingat piutang/hutang, rekonsiliasi, sinkronisasi status, audit log, scheduler & queue.

---

## 2. Arsitektur Notifikasi Sistem Terpadu (Tri-Channel Notifications: UI, Email, & WhatsApp)

COOCA mengadopsi arsitektur notifikasi multi-saluran (*Tri-Channel Event-Driven Notification Engine*) yang memastikan informasi operasional, transaksi, peringatan stok, dan deteksi fraud tersampaikan secara andal, cepat, dan tidak mengganggu alur kerja pengguna:

```
                  ┌─────────────────────────────────────┐
                  │    DOMAIN EVENT / FRAUD TRIGGER     │
                  │   (OrderPaid, StockLow, FraudAlert) │
                  └──────────────────┬──────────────────┘
                                     │
                 ┌───────────────────┴───────────────────┐
                 │       Laravel Queue (Asynchronous)    │
                 └─┬─────────────────┬─────────────────┬─┘
                   │                 │                 │
     ┌─────────────▼─────────┐ ┌─────▼──────────┐ ┌────▼──────────────┐
     │      SALURAN UI       │ │ SALURAN EMAIL  │ │  SALURAN WHATSAPP │
     │ • In-App Notif Center │ │ • HTML Apple   │ │ • Meta Cloud API  │
     │ • Toast Notification  │ │ • Daily Digest │ │ • Digital E-Nota  │
     │ • Action Modal Sheet  │ │ • Urgent Alert │ │ • Owner Alert WA  │
     └───────────────────────┘ └────────────────┘ └─────────┬─────────┘
                                                            │ (Fail-Safe)
                                                  ┌─────────▼─────────┐
                                                  │ Fallback Manual   │
                                                  │ 1-Klik wa.me / App│
                                                  └───────────────────┘
```

### 2.1 Saluran UI (In-App Notification Center & Toast Feedback)

1. **Notification Center Bell Dropdown (Header)**:
   - Ikon Lucide `bell` di header dengan unread badge counter (`tabular-nums`).
   - Dropdown Bento Apple HIG dengan segmentasi kategori:
     - 🔔 **Transaksi & Kasir**: Pesanan masuk, pembayaran terverifikasi, order toko online baru.
     - 🚨 **Keamanan & Fraud**: Peringatan selisih kas tutup shift, void abnormal, login dari IP/perangkat baru, mutasi role/permission.
     - 📦 **Stok & Inventaris**: Stok mencapai batas minimum (*Low Stock Reorder Alert*), batch kadaluarsa, transfer stok menunggu konfirmasi.
     - 👥 **Otorisasi & Approval (Maker-Checker)**: Pengajuan diskon khusus, penyesuaian stok bernilai besar, purchase order menunggu persetujuan.
     - ⚙️ **Sistem & Billing**: Masa aktif paket langganan, update modul, backup database.
2. **Toast Feedback Non-Intrusif**:
   - Tampil melayang di sudut atas layar dengan styling frosted glass Apple HIG (`backdrop-blur-md bg-white/90 dark:bg-zinc-900/90 border border-black/5 dark:border-white/10 rounded-[18px] shadow-lg`).
   - Durasi auto-dismiss 3–5 detik, dilengkapi ikon Lucide semantik (tanpa emoji Unicode), teks lugas dan menenangkan.
3. **Actionable Approval Cards (Modal Sheet)**:
   - Notifikasi yang membutuhkan otorisasi (Maker-Checker) langsung memunculkan kartu aksi ringkas dengan tombol `[ Setujui ]` (Emerald) dan `[ Tolak ]` (Rose) berukuran minimal 44px tanpa perlu berpindah-pindah menu.

### 2.2 Saluran Email (Responsive HTML Templates)

1. **Standar Desain Email Apple HIG**:
   - Template HTML responsif multi-device (kompatibel dengan Gmail, Apple Mail, Outlook) dengan tipografi bersih, layout bento berjarak lapang, border hairline lembut, dan logo resmi tenant/COOCA.
2. **Matriks Penggunaan Email**:
   - **Laporan Ringkasan Eksekutif Harian & Mingguan (Daily/Weekly Business Digest)**: Dikirim setiap pagi/malam ke Business Owner (Omzet harian, margin laba kotor, item terlaris, rekap kas, dan daftar anomali/selisih kas).
   - **Peringatan Keamanan & Fraud Kritis (Critical Security Alerts)**: Dikirim seketika saat terdeteksi anomali (misal: 3x void berturut-turut, selisih kas > toleransi, reset PIN supervisor, perubahan rekening bank).
   - **Faktur Tagihan & Invoice Resmi B2B**: Dokumen faktur berformat PDF terlampir atau link unduhan aman berwaktu kedaluwarsa (*signed temporary URL*).
   - **Pemulihan Akun & Verifikasi**: Reset password aman dan notifikasi ganti kredensial.

### 2.3 Saluran WhatsApp (Instant Meta Cloud API & Gateway Dispatch)

1. **Karakteristik & Format Pesan WhatsApp**:
   - Pesan WhatsApp dirancang dengan gaya komunikasi Indonesia yang santun, profesional, dan ringkas:
     - Gunakan penekanan format standar WhatsApp: `*teks tebal*` untuk judul/nominal penting, `_teks miring_` untuk nomor nota/keterangan, bullet point `-` yang rapi.
     - **Dilarang keras memakai rentetan emoji berlebihan**. Cukup gunakan penanda fungsional minimal jika relevan (mis. `[ COOCA ]` atau `*PENTING*`).
2. **Matriks Penggunaan WhatsApp**:
   - **Nota & Struk Digital Kasir POS**: Link invoice/struk digital resmi dikirim instan ke nomor WhatsApp pelanggan setelah pembayaran sukses (hemat kertas printer).
   - **Update Pesanan Toko Online (Storefront)**: Konfirmasi pesanan diterima, link lacak pesanan kurir, dan pemberitahuan pesanan siap diambil / dalam perjalanan.
   - **Pengingat Jatuh Tempo Piutang & Hutang (Auto-Reminder)**: Pengingat otomatis terjadwal yang sopan (H-3 jatuh tempo, Hari H pembayaran, dan H+3 follow-up ramah) lengkap dengan total tagihan dan tombol/link pembayaran instan.
   - **Urgent Fraud & Operational Alert ke WhatsApp Owner**: Notifikasi instan langsung ke nomor WhatsApp pribadi pemilik usaha saat terjadi event berisiko tinggi (Void kasir, selisih tutup kasir > batas, stok bahan baku habis di tengah shift, pengajuan approval mendesak).

### 2.4 Robustness & Prinsip Operasional Notifikasi

1. **Wajib Asinkron (Background Queue Processing)**:
   - Seluruh pengiriman Email dan WhatsApp **WAJIB** mengimplementasikan `ShouldQueue` (Laravel Jobs / Queue).
   - Aksi kasir di POS atau checkout toko online **dilarang terhambat (zero blocking)** oleh latensi jaringan gateway eksternal (response time POS wajib sub-100ms).
2. **Fail-Safe & User Fallback Mechanism (Tombol Manual 1-Klik)**:
   - Jika koneksi WhatsApp Gateway / SMTP Mail server gagal, timeout, atau kehabisan kuota:
     - Jangan pernah membatalkan transaksi utama (*Graceful Degradation*).
     - Catat log kegagalan ke tabel `notification_logs` dengan status `FAILED` beserta pesan error.
     - Sediakan tombol fallback manual di UI: **`[ Kirim via WhatsApp Web / HP ]`** (`https://wa.me/{phone}?text={encoded_message}`) dengan format teks nota yang sudah siap kirim.
3. **Matriks Preferensi Notifikasi Tenant (Granular Notification Settings)**:
   - Pemilik usaha memiliki kontrol penuh untuk memilih saluran aktif per jenis event di menu Pengaturan Notifikasi (UI Saja, UI + WhatsApp, UI + Email, atau Ketiganya).
4. **Anti-Spam, Rate Limiting, & Throttling**:
   - Sistem wajib membatasi frekuensi pengiriman pesan berulang ke nomor yang sama (maksimal 1 pengingat piutang per 24 jam per transaksi, kecuali diminta manual oleh kasir).

---

## 3. Testing Wajib

Sebelum menyatakan pekerjaan selesai, jalankan pengujian nyata (bukan klaim):

```bash
php -l <file.php>
php artisan test
php artisan route:list
npm run build
php artisan route:cache
php artisan view:cache
```

Verifikasi menyeluruh tambahan:

- **Responsivitas Mobile (360–430px)**: zero horizontal overflow, teks tidak terpotong, bento card adaptif.
- **Skala Font Input Mobile**: seluruh `<input>`/`<select>`/`<textarea>` minimal 16px (`text-[16px] sm:text-[14px]`).
- **Skala Tipografi**: mengikuti Matriks Tipografi Apple HIG (kontras bobot jelas, tanpa font-black/900).
- **Touch Target**: tombol aksi utama minimal 44×44px hingga 52px, sela antar tombol minimal 12px.
- **Safe Area Mobile**: padding bawah aman (`pb-28`–`pb-32 lg:pb-10`/`lg:pb-12`) agar tidak tertutup bottom navbar.
- **Tabular Figures**: seluruh angka moneter, stok, tanggal, nomor nota memakai `tabular-nums`.
- Tidak ada error 500, route bentrok, view rusak, JavaScript error, console debug, broken link, permission bypass, atau data dummy tertinggal.

**Standar 100% Lolos**: dilarang menyatakan `PASS` jika masih ada test gagal, error sintaks, atau exception 500. Wajib sajikan bukti hasil testing nyata (_all tests passed_) kepada pengguna - bukan asumsi.

## 3. Production Hardening & Test Data Purge

Fokus kesiapan produksi ada pada **source code**, bukan sekadar `.env`.

**De-mocking & Eliminasi Bypass**:

- Hapus seluruh logika mock/stub testing, OTP statis sementara (`123456`), bypass hak akses sementara, atau mock response di service.
- Pastikan controller & domain service menjalankan logika produksi asli secara aman dan tangguh (_hardened real execution_).

**Sanitasi Kode (Zero Debug Leftovers)** - dilarang menyisakan:

```
dd()   dump()   ray()   var_dump()   console.log()
mock response   dummy data   bypass authentication
bypass authorization   OTP statis   test user   temporary token
```

**Pembersihan Data Testing (Test Data Purge)**:

- Hapus seluruh record dummy, order fiktif, user tester sementara, dan berkas sampah upload uji coba dari database & storage operasional (`storage/app/public/...`, `storage/tmp/`).
- Pastikan pengujian otomatis masa depan tetap terisolasi (`RefreshDatabase`/transaksi DB) agar tidak mencemari database produksi.

**Kompilasi Aset & Cache**:

- `npm run build` (bukan mode dev server `npm run dev`).
- `php artisan route:cache` dan `php artisan view:cache`.

**Pastikan juga**: validasi tetap aktif, permission tetap aktif, tidak ada credential/data sensitif di source code.
