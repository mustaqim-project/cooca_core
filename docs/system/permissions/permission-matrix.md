# Matriks Hak Akses & Peran (Role & Permission Matrix)

> **Status:** COMPLETE  
> **Sistem RBAC:** Spatie Laravel-Permission / Core RBAC Models (`roles`, `permissions`, `business_users`)  
> **Cakupan:** 7 Peran Utama Sistem Cooca.

---

## 👥 Definisi Peran Utama (User Roles)

1. **Superadmin Platform (`superadmin`):** Administrator backoffice penyedia SaaS Cooca. Mengelola pendaftaran tenant, paket langganan, dan pemantauan sistem global.
2. **Business Owner (`owner`):** Pemilik usaha berdaulat penuh atas satu bisnis tertentu. Memiliki wewenang mutlak atas seluruh data keuangan, stok, karyawan, dan pengaturan toko miliknya.
3. **Manajer Toko (`store_manager`):** Mengawasi operasional harian kasir dan gudang. Memiliki PIN otorisasi supervisor untuk menyetujui void/refund kasir.
4. **Kasir POS (`cashier`):** Bertugas melayani transaksi checkout pelanggan di terminal kasir, membuka/menutup shift kasir, dan menerima pembayaran.
5. **Staf Gudang / Dapur (`warehouse_staff`):** Mengelola penerimaan barang fisik (*Goods Receipt*), stock opname, dan pemrosesan pesanan dapur.
6. **Pelanggan Storefront (`customer`):** Konsumen publik yang berbelanja mandiri di toko online (`/b/{slug}`), melacak status pesanan, dan mengelola profil belanja.
7. **Subsistem Otomasi (`automation_worker`):** Pekerja latar belakang (Cron / Queue) yang mengeksekusi auto-journal, auto-stock deduction, dan pengiriman notifikasi WhatsApp.

---

## 📊 Matriks Wewenang Fitur per Modul

| Modul / Fitur | Superadmin | Business Owner | Store Manager | Kasir POS | Staf Gudang | Pelanggan | Otomasi |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| **Kalkulator HPP & Costing** | ❌ | ✅ Penuh | ✅ Lihat/Hitung | ❌ | ❌ | ❌ | ❌ |
| **Master Produk & Bahan** | ❌ | ✅ Penuh | ✅ Edit/Kelola | 👁️ Lihat Saja | 👁️ Lihat Saja | 👁️ Katalog Publik | ❌ |
| **Terminal Kasir POS** | ❌ | ✅ Penuh | ✅ Penuh | ✅ Transaksi | ❌ | ❌ | ❌ |
| **Void & Refund Kasir (PIN)** | ❌ | ✅ Wewenang | ✅ Wewenang | ❌ Butuh PIN | ❌ | ❌ | ❌ |
| **Goods Receipt (GR) & Stok** | ❌ | ✅ Penuh | ✅ Penuh | ❌ | ✅ Kelola GR | ❌ | ⚡ Auto-Potong |
| **Purchase Order & Supplier** | ❌ | ✅ Penuh | ✅ Buat PO | ❌ | 👁️ Lihat Saja | ❌ | ❌ |
| **Kas & Rekening Bank** | ❌ | ✅ Penuh | 👁️ Kas Toko | 👁️ Kas Shift | ❌ | ❌ | ⚡ Mutasi Saldo |
| **Jurnal Umum & Laporan Laba** | ❌ | ✅ Penuh | ❌ | ❌ | ❌ | ❌ | ⚡ Auto-Journal |
| **Toko Online & Bukti Bayar** | ❌ | ✅ Penuh | ✅ Verifikasi | ❌ | 👁️ Lihat Order | ✅ Belanja Mandiri | ⚡ Status Update |
| **Paket SaaS & Billing Tenant** | ✅ Penuh | ✅ Bayar/Pilih | ❌ | ❌ | ❌ | ❌ | ⚡ Auto-Reminder |
| **Pengawasan Tenant Global** | ✅ Penuh | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

*Keterangan Simbol:*
- ✅ : Hak akses penuh (View, Create, Edit, Delete).
- 👁️ : Hak akses terbatas hanya melihat (*Read-Only*).
- ❌ : Tidak memiliki hak akses (Akses ditolak).
- ⚡ : Dieksekusi otomatis oleh sistem di latar belakang tanpa intervensi manual.
