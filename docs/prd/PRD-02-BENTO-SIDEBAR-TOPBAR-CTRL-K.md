# PRD-02: Restrukturisasi Sidebar Bento Apple HIG & Topbar Spotlight Search (Ctrl + K)

**ID Dokumen:** `PRD-02-BENTO-NAV-SPOTLIGHT`  
**Modul:** Shell Navigasi, Tata Letak UI & Spotlight Command Palette  
**Penanggung Jawab:** Principal UI/UX & Frontend Engineer  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Seluruh Peran (Owner, Kasir, Manajer Keuangan, Staf Gudang, Admin)  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **Sidebar Layout:** [`resources/views/layouts/partials/sidebar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/sidebar.blade.php) (182 KB, 2.392 baris)
* **Topbar Layout:** [`resources/views/layouts/partials/topbar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/topbar.blade.php) (14 KB, 320 baris)
* **Style Guideline:** Bento Apple HIG (macOS Sonoma & iOS 18 rail, zero-emoji, calm typography)

### B. Temuan & Masalah Sistem Existing
1. **Sidebar Monolitik 2.392 Baris:**
   - Grup menu tercampur aduk tanpa hierarki yang jelas.
   - Sub-dashboard (POS, Penjualan B2B, Biaya Produksi, Toko Online) tercecer di dalam menu anak alih-alih terkonsolidasi di satu pintu pantau.
   - Laporan tersebar di berbagai sudut menu (`pos.reports`, `finance.reports`, `inventory.reports`, `crm.reports`) sehingga pengguna kesulitan mencari laporan yang dibutuhkan.
2. **Ketiadaan Fitur Pencarian Cepat (Command Palette):**
   - COOCA memiliki lebih dari 68 rute aktif. Saat pengguna ingin mengakses menu spesifik (misal: "Pengaturan Printer Kasir" atau "Rekap Kasbon"), mereka harus menelusuri sidebar yang panjang secara manual.
   - Topbar saat ini hanya memiliki tombol notifikasi dan profil user, tanpa kotak pencarian (*search bar*).

---

## 2. Perubahan & Penambahan Sistem (To-Be State)

### A. Restrukturisasi Sidebar Menjadi 8 Grup Bento Apple HIG
Seluruh 68 rute sistem dikelompokkan secara konsisten ke dalam 8 grup utama:

1. **`OVERVIEW` (Ringkasan & Dasbor Eksekutif):**
   - Dasbor Utama Bisnis (`dashboard.index`)
   - *Switcher Sub-Dashboard Pintar (Berdasarkan Modul Aktif):*
     - Dasbor Kasir POS (`pos.dashboard`)
     - Dasbor Penjualan B2B (`sales.pipeline`)
     - Dasbor Toko Online (`storefront.orders.index`)
     - Dasbor Biaya & HPP (`costing.analytics`)
2. **`KASIR & PENJUALAN`:**
   - Kasir POS Terminal (`pos.terminal.index`)
   - Pesanan & Transaksi Kasir (`pos.orders.index`)
   - Dapur & KDS (`pos.kitchen.index`)
   - Meja & Sesi Dine-In (`pos.tables.index`)
   - Penjualan B2B / Sales Orders (`sales.orders.index`)
   - Invoice Tagihan Pelanggan (`invoices.index`)
   - Retur Penjualan (`sales.returns.index`)
3. **`PRODUK & PERSEDIAAN`:**
   - Katalog Produk & Layanan (`products.index`)
   - Bahan Baku & Resep BOM (`materials.index`, `bom.index`)
   - Stok Multi-Cabang & Mutasi (`inventory.index`, `inventory.movements`)
   - Transfer Antar Cabang (`inventory.transfers.index`)
   - Stok Opname Fisik (`inventory.opnames.index`)
   - Penyesuaian Stok (`inventory.adjustments.index`)
   - Manajemen Gudang (`warehouse.index`)
4. **`PEMBELIAN & SUPPLIER`:**
   - Pesanan Pembelian / PO (`purchase-orders.index`)
   - Penerimaan Barang / GRN (`goods-receipts.index`)
   - Tagihan Supplier / Bills (`purchasing.bills.index`)
   - Retur Pembelian (`purchase.returns.index`)
   - Daftar Pemasok / Supplier (`suppliers.index`)
5. **`PELANGGAN & PEMASARAN`:**
   - Buku Pelanggan & CRM (`customers.index`)
   - Member & Poin Loyalitas (`crm.members.index`)
   - Voucher Diskon Promosi (`crm.vouchers.index`)
   - *Sub-grup Toko Online:*
     - Pesanan Toko Online (`storefront.orders.index`)
     - Desain Halaman Toko (`landing-page.edit`)
     - Reservasi & Booking (`storefront.reservations.index`) — *kondisional*
     - Ongkir & Ekspedisi (`storefront.shipping.index`)
     - Pengaturan Etalase (`storefront.settings.index`)
   - WhatsApp Blast & Broadcast (`whatsapp.index`)
   - Manajemen Media Sosial (`social-media.index`)
6. **`KEUANGAN & BIAYA`:**
   - Kas & Rekening Bank (`cash-accounts.index`)
   - Biaya Operasional / Beban (`expenses.index`)
   - Jurnal Umum & Pembukuan (`accounting.journals.index`)
   - Kalkulator HPP & Biaya Mesin (`cost-models.index`, `labor-machines.index`)
   - SDM, Absensi & Gaji Bulanan (`hrm.index`)
   - Pajak Usaha PPh / PPN (`tax.index`)
7. **`LAPORAN & ANALITIK` (Pusat Laporan Terpadu):**
   - Pusat Laporan Utama (`reports.index`)
   - Laporan Penjualan & Kasir
   - Laporan Laba Rugi
   - Laporan Arus Kas
   - Laporan Nilai Persediaan Stok
   - Laporan Analisis Margin Produk
   - Laporan Pajak
8. **`PENGATURAN USAHA`:**
   - Pengaturan Umum & Cabang (`settings.index`)
   - Pengguna & Hak Akses Peran (`settings.roles.index`, `settings.members`)
   - Kelola Modul Usaha (`settings.modules`)
   - Paket Langganan & Billing (`billing.index`)
   - Log Audit & Jejak Keamanan (`settings.audit-logs.index`)

---

### B. Topbar Spotlight Search Command Palette (`Ctrl + K` / `⌘K`)

Pada file [`topbar.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/layouts/partials/topbar.blade.php), ditambahkan kapsul pencarian bergaya macOS Sonoma yang memicu dialog modal interaktif Alpine.js:

```blade
<!-- Spotlight Trigger Capsule in Topbar -->
<button type="button" @click="$dispatch('open-spotlight')"
    class="hidden md:flex items-center gap-2.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/5 dark:border-white/10 hover:border-black/15 text-black/50 dark:text-white/50 text-[13px] transition-all cursor-pointer">
    <i data-lucide="search" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
    <span class="font-normal">Cari menu, fitur, atau laporan...</span>
    <kbd class="px-1.5 py-0.5 rounded-[5px] text-[10px] font-semibold bg-white dark:bg-[#2C2C2E] text-black/60 dark:text-white/60 shadow-xs border border-black/5 dark:border-white/10">Ctrl K</kbd>
</button>
```

#### Fitur Modal Spotlight Search (`command-palette`):
1. **Keyboard Listener Global:** Mendengarkan kombinasi tombol `Ctrl + K` (Windows/Linux) atau `⌘ + K` (macOS).
2. **Pencarian Reaktif Instan:** Mengindeks seluruh 68 rute sistem berdasarkan nama menu, deskripsi fitur, kata kunci sinonim, dan nama grup.
3. **Filter Kategori (Chips):** Tombol filter cepat: `Semua`, `Penjualan`, `Persediaan`, `Keuangan`, `Laporan`, `Pengaturan`.
4. **Navigasi Tombol Panah:** Pengguna dapat berpindah item hasil pencarian menggunakan tombol panah atas/bawah keyboard, lalu menekan `Enter` untuk langsung membuka halaman tujuan.
5. **Aksesibilitas & Kecepatan:** Bebas reload, terbuka di bawah 50 milidetik, otomatis menutup saat menekan tombol `Escape`.

---

## 3. Alur Kerja Navigasi Pengguna

```
[ PENGGUNA DI HALAMAN MANA PUN ]
               │
   ┌───────────┴───────────┐
   ▼                       ▼
[ KLIK MENU SIDEBAR ]   [ TEKAN Ctrl + K ]
   │                       │
   │                       ▼
   │            [ MUNCUL MODAL SPOTLIGHT ]
   │            (Ketik: "kasbon", "laba rugi", "stok")
   │                       │
   │                       ▼
   │            [ PILIH HASIL & TEKAN ENTER ]
   │                       │
   └───────────┬───────────┘
               ▼
   [ MENU TUJUAN TERBUKA INSTAN ]
```

---

## 4. Kriteria Keberhasilan (Definition of Done)
1. Seluruh 68 rute sistem terpetakan rapi ke dalam 8 grup Bento tanpa ada menu yang hilang atau URL yang rusak.
2. Saat modul dinonaktifkan di `disabled_modules`, seluruh menu terkait di dalam sidebar otomatis lenyap tanpa meninggalkan ruang kosong.
3. Menekan `Ctrl + K` atau `⌘K` dari halaman mana pun membuka modal spotlight search secara instan.
4. Navigasi keyboard (Panah Atas, Panah Bawah, Enter, Escape) berfungsi mulus 100%.
5. Tampilan mematuhi prinsip desain Apple HIG: tipografi tenang, tanpa emoji pada label menu resmi, dan kontras warna yang nyaman bagi mata.
