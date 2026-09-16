# COOCA — Otomasi, Testing Wajib, & Production Hardening (Referensi Lengkap)

## 1. Mandat Otomasi Sistem Penuh

COOCA dirancang bekerja **secara otonom untuk pengguna**, bukan menuntut input manual berulang. Segala alur yang dapat diotomasi **wajib** diotomasi — namun jangan menambah otomasi yang belum dipahami dampaknya terhadap data, workflow, dan integrasi.

1. **Auto-Journaling** — Transaksi penjualan POS, order toko online, pembelian bahan baku/PO, pengeluaran kas, penerimaan piutang, dan retur barang wajib otomatis menghasilkan jurnal akuntansi berimbang (Debit = Kredit) tanpa pemilik toko perlu paham kode akun.
2. **Auto-Stock & Auto-BOM** — Setiap penjualan menu/racikan/paket barang otomatis memotong saldo stok bahan baku berdasarkan resep (*Bill of Materials*).
3. **Auto-Invoice & WhatsApp Dispatch** — Sesaat setelah pesanan dibayar/dibuat, sistem otomatis menerbitkan invoice digital dan mengirim WhatsApp berisi ringkasan nota + tautan struk resmi, tanpa kasir mengetik manual.
4. **Auto-Reminder Piutang & Hutang** — Pengingat otomatis via WhatsApp & dashboard untuk invoice mendekati/lewat jatuh tempo, bahasa Indonesia santun dan profesional.
5. **Auto-Reconciliation & Status Engine** — Transisi status *Menunggu Pembayaran → Diproses → Siap Diambil/Dikirim → Selesai* berjalan otomatis dipicu webhook pembayaran atau aksi kasir 1-klik.

Area otomasi lain yang perlu diaudit & dipertimbangkan: jurnal akuntansi, pemotongan stok, BOM/resep, invoice, nota digital, notifikasi WhatsApp, pengingat piutang/hutang, rekonsiliasi, sinkronisasi status, audit log, scheduler & queue.

## 2. Testing Wajib

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

**Standar 100% Lolos**: dilarang menyatakan `PASS` jika masih ada test gagal, error sintaks, atau exception 500. Wajib sajikan bukti hasil testing nyata (*all tests passed*) kepada pengguna — bukan asumsi.

## 3. Production Hardening & Test Data Purge

Fokus kesiapan produksi ada pada **source code**, bukan sekadar `.env`.

**De-mocking & Eliminasi Bypass**:
- Hapus seluruh logika mock/stub testing, OTP statis sementara (`123456`), bypass hak akses sementara, atau mock response di service.
- Pastikan controller & domain service menjalankan logika produksi asli secara aman dan tangguh (*hardened real execution*).

**Sanitasi Kode (Zero Debug Leftovers)** — dilarang menyisakan:
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
