# Panduan Information Architecture (IA): Pemisahan Operasional Harian vs Pusat Pengaturan (Settings Hub)

Dokumen ini mendefinisikan aturan baku penataan struktur menu, navigasi sidebar, dan pengelompokan fitur di antarmuka backoffice COOCA agar bebas dari kekacauan (*anti-clutter*) dan memisahkan secara tegas antara aktivitas transaksi harian dengan konfigurasi sistem.

---

## 🛑 Akar Masalah: Mengapa Menu Sering Kacau?

Pada sistem yang belum tertata, developer sering membuat menu level-1 baru di sidebar setiap kali menambah fitur konfigurasi kecil. Contoh menu yang salah kaprah berdiri sendiri di root sidebar:
* ❌ *Menu "Pengaturan Printer"* (Padahal hanya di-setting 1x saat buka toko)
* ❌ *Menu "Format Nomor Faktur"* (Hanya diatur saat setup awal)
* ❌ *Menu "Koneksi WhatsApp API"* (Hanya diatur saat integrasi)
* ❌ *Menu "Setting Pajak & PPN"* (Hanya diatur oleh pemilik bisnis)
* ❌ *Menu "Integrasi Ekspedisi Biteship"* (Konfigurasi teknis)
* ❌ *Menu "Pengaturan Toko Online"* (Konfigurasi tema & jam operasional)

**Dampaknya:**
1. Sidebar membengkak hingga 25–35 menu berderet.
2. Kasir dan staf gudang bingung mencari menu transaksi harian (POS, pesanan, surat jalan) karena tertimbun puluhan menu konfigurasi.
3. Beban kognitif (*cognitive overload*) tinggi bagi pengguna UMKM usia 40–65+ tahun.

---

## 🏛️ Solusi: 4 Klaster Menu Baku Backoffice COOCA

Sidebar utama backoffice COOCA **DIBATASI SECARA KETAT** hanya untuk 4 klaster terstruktur:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. KLASTER OPERASIONAL HARIAN (Daily Operations - Akses Kasir & Staf)       │
│    • 📊 Dashboard & Ringkasan Transaksi                                     │
│    • 💻 Kasir POS Terminal                                                  │
│    • 📋 Pesanan & Transaksi Masuk                                           │
│    • 🚗 SPK & PKB Servis (Khusus Bengkel) / 🍽️ Meja & KDS (Khusus F&B)     │
│    • 🚚 Pengiriman & Surat Jalan                                            │
├─────────────────────────────────────────────────────────────────────────────┤
│ 2. KLASTER MASTER DATA & KATALOG (Pengelolaan Barang & Hubungan Bisnis)     │
│    • 📦 Produk & Layanan (Katalog, Resep BOM, Varian)                       │
│    • 🏢 Multi-Gudang & Stok (Opname, Mutasi, Surat Penerimaan GR)           │
│    • 👥 Pelanggan & CRM (Member, Poin Loyalitas)                            │
│    • 🏭 Pemasok & Pembelian (Purchase Order PO)                             │
│    • 👔 Karyawan & Presensi (HRM & Payroll)                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│ 3. KLASTER LAPORAN & KEUANGAN (Analitik & Pembukuan)                        │
│    • 💰 Buku Kas & Bank (Buku Besar & Auto-Journal)                         │
│    • 📈 Laporan Penjualan & Laba Rugi                                       │
│    • 📑 Analitik Performa & Audit Log                                       │
├─────────────────────────────────────────────────────────────────────────────┤
│ 4. PUSAT PENGATURAN TERPADU (UNIFIED SETTINGS HUB - SATU MENU /settings)    │
│    • ⚙️ Pengaturan Bisnis (Unified Hub)                                     │
│      ├── Sub-Hub 1: Profil Usaha & Outlet (Nama, Alamat, Geofence, Logo)    │
│      ├── Sub-Hub 2: Kasir & Nota (Thermal ESC/POS, Footer Struk, Drawer)    │
│      ├── Sub-Hub 3: Pajak & Pembayaran (PPN 11%, PB1, QRIS, Akun Bank)     │
│      ├── Sub-Hub 4: Integrasi Saluran (WhatsApp Meta, Biteship, SMTP Mail)  │
│      ├── Sub-Hub 5: Hak Akses & Keamanan (Role, Permission, PIN Supervisor) │
│      └── Sub-Hub 6: Paket Langganan & Storage (Plan SaaS, Pruning Data)     │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## ⚙️ Blueprint Arsitektur Pusat Pengaturan (`/settings`)

Semua konfigurasi teknis dipusatkan ke dalam 1 route utama: `route('settings.index')` (`/settings`), yang menyajikan navigasi internal Bento Apple HIG:

### Layout Halaman `/settings`:
```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ PUSAT PENGATURAN BISNIS & SISTEM                                                       │
│ Kelola profil usaha, konfigurasi perangkat kasir, integrasi gateway, dan paket akun.   │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ [ Tab: Profil & Outlet ] [ Tab: Kasir & Nota ] [ Tab: Pajak ] [ Tab: Integrasi ] ...  │
├────────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                        │
│  ┌──────────────────────────────────────────┐ ┌──────────────────────────────────────┐ │
│  │ Bento Card: Printer Thermal Kasir        │ │ Bento Card: Format Nomor Nota        │ │
│  │ • Pilihan Kertas (58mm / 80mm)           │ │ • Prefix Nota (contoh: INV/2026/)    │ │
│  │ • Test Print Struk Kasir                 │ │ • Catatan Kaki Nota (Footer Text)    │ │
│  │ • Buka Laci Kas Otomatis (ESC/POS)       │ │ • Tampilkan Logo Toko di Struk       │ │
│  └──────────────────────────────────────────┘ └──────────────────────────────────────┘ │
│                                                                                        │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 📋 Checklist Audit Information Architecture (IA)

Saat melakukan review atau perbaikan menu pada file `sidebar.blade.php`, `topbar.blade.php`, atau `routes/owner.php`:

1. **Apakah ada menu konfigurasi yang bocor di root sidebar?**  
   *Jika YA:* Pindahkan segera ke dalam sub-tab di `/settings`.
2. **Apakah menu disaring berdasarkan modul aktif industri?**  
   *Wajib dibungkus:* `@if($business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_...))`
3. **Apakah jumlah menu root di sidebar tidak melebihi 10 item?**  
   *Standar ideal:* 6–9 menu utama yang dikelompokkan ke 4 klaster dengan divider tipis (`border-t border-black/[0.04] dark:border-white/[0.06]`).
4. **Apakah ada tab di dalam halaman yang tidak jelas hubungannya dengan halaman induk?**  
   *Jika YA:* Evaluasi apakah tab tersebut harus menjadi halaman mandiri di Klaster 1/2 atau dijadikan Modal Sheet.
