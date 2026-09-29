---
name: ui-panel-consistency-and-ia
description: Audit dan standardisasi konsistensi UI/UX antar 3 panel COOCA (Admin Panel, Owner/User Backoffice Panel, Customer/Storefront Panel), arsitektur layout, page title/header terpadu, struktur tab navigasi (Segmented Control vs Underline Tabs, URL deep-linking), serta Information Architecture (IA) dengan pengelompokan tegas antara Operasional Harian (Daily Ops), Master Data, Laporan, dan Pusat Pengaturan Terpadu (Settings Hub). Aktifkan saat user meminta audit layout UI, merapikan menu sidebar, menata tab yang tidak sejalan, memperbaiki title/header antar panel, atau memisahkan menu setting dari menu operasional.
---

# UI PANEL CONSISTENCY, TAB ARCHITECTURE & INFORMATION ARCHITECTURE (IA) SKILL

Skill operasional ini memandu AI Agent dalam menegakkan **standar konsistensi bentuk antarmuka (UI Consistency)**, **arsitektur tab navigasi**, dan **pengelompokan struktur menu (Information Architecture / IA)** di seluruh ekosistem COOCA.

Skill ini menyelesaikan 3 masalah krusial UX yang sering terjadi:
1. **Inkonsistensi Bentuk Antar 3 Panel**: Perbedaan title, layout wrapper, padding, style kartu, dan tombol aksi antara **Admin Panel**, **Owner/User Panel**, dan **Customer Panel**.
2. **Kekacauan Tab Navigasi (*Tab Clutter & Desynchronization*)**: Tab di dalam layout yang tidak sejalan, membingungkan pengguna, tidak terhubung dengan sidebar, atau kehilangan state saat refresh.
3. **Pencampuran Menu Operasional & Pengaturan (*Operational vs Settings Pollution*)**: Fitur pengaturan (yang hanya diakses sesekali) malah berdiri sendiri menjadi menu level-1 di sidebar dan mengotori menu operasional harian.

---

## 🧭 File Referensi Pendukung

* [`references/panel-consistency-matrix.md`](file:///c:/laragon/www/cooca_core/.agents/skills/ui-panel-consistency-and-ia/references/panel-consistency-matrix.md) — Matriks spesifikasi layout, header, title, tokens Bento Apple HIG, dan styling lintas 3 panel (Admin, Owner, Customer).
* [`references/settings-vs-operations-ia.md`](file:///c:/laragon/www/cooca_core/.agents/skills/ui-panel-consistency-and-ia/references/settings-vs-operations-ia.md) — Blueprint Information Architecture (IA), 4 klaster menu sidebar, dan arsitektur Unified Settings Hub (`/settings`).
* [`c:/laragon/www/cooca_core/.agents/skills/cooca-agent-directive/references/design-system.md`](file:///c:/laragon/www/cooca_core/.agents/skills/cooca-agent-directive/references/design-system.md) — Master Design System Bento Apple HIG.

---

## 🏛️ 1. Karakteristik & Konsistensi 3 Panel COOCA

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                         STANDARISASI 3 PANEL UTAMA COOCA                               │
├─────────────────────────┬──────────────────────────┬───────────────────────────────────┤
│ 1. ADMIN PANEL (Platform│ 2. OWNER / USER PANEL    │ 3. CUSTOMER PANEL (Storefront /   │
│    Superadmin)          │    (Tenant Backoffice)   │    Public Portal / Member Area)   │
├─────────────────────────┼──────────────────────────┼───────────────────────────────────┤
│ • Layout: Full Admin    │ • Layout: Bento Business │ • Layout: Responsive Storefront   │
│   Shell, high density   │   OS, touch-first mobile │   clean catalog, cart drawer      │
│ • Audience: Internal    │ • Audience: Owner UMKM,  │ • Audience: Pembeli publik,       │
│   SaaS Operator & Tech  │   Kasir, Staf Gudang     │   pelanggan setia, tamu reservasi │
│ • Header: Title + Audit │ • Header: Unified Action │ • Header: Brand Logo + Cart +     │
│   Breadcrumb + System   │   Title + Outlet/Status  │   Status Pesanan Tracker          │
│   Status                │ • Rule: Zero-Manual UI   │ • Rule: Zero Internal Terminology │
└─────────────────────────┴──────────────────────────┴───────────────────────────────────┘
```

---

## 📐 2. Standar Struktur Page Header & Title Terpadu

Setiap halaman di ketiga panel **WAJIB** mengikuti struktur header 3 baris yang konsisten:

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ Baris 1: Breadcrumb (Multi-level) ATAU Typographic Overline (Tanpa Kapsul/Pill)        │
│          Contoh: PENJUALAN & TRANSAKSI  atau  Katalog Produk > Detail Resep BOM        │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ Baris 2: [H1 Title Besar] [Badge Status Entitas]                 [Action Buttons]      │
│          Produk & Layanan   [Aktif: 24 Item]                      [+ Tambah Produk]    │
├────────────────────────────────────────────────────────────────────────────────────────┤
│ Baris 3: [Subtitle Ringkas 1 Baris] ATAU [Segmented Control Tabs / Filter Bar]         │
│          Kelola katalog produk, takaran resep BOM, dan harga saluran jual.             │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

### Aturan Header & Title:
1. **Tipografi Judul**: Gunakan `text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white`.
2. **Dilarang Eyebrow Pills**: Dilarang membungkus overline dalam kapsul badge warna-warni. Gunakan teks polos `text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40`.
3. **Action Button Utama**: Berada di pojok kanan atas sejajar dengan H1 Title, menggunakan Apple Primary Blue (`bg-[#007AFF] text-white hover:bg-[#0062CC] rounded-xl px-4 py-2 font-medium text-sm transition active:scale-[0.98]`).
4. **Subtitle Faktual**: Maksimal 1 kalimat padat (10–15 kata) yang menjelaskan fungsi halaman, **DILARANG** slogan promosi/marketing fluff.

---

## 📑 3. Arsitektur Tab Layout & Solusi Tab Desynchronization

Kasus tab yang membingungkan atau tidak sejalan dengan sidebar diselesaikan dengan aturan baku:

### A. Kapan Menggunakan Tab vs Halaman Baru vs Modal Sheet
| Jenis Kebutuhan | Pola yang Benar | Alasan UX |
|---|---|---|
| **Membagi sudut pandang pada entitas yang sama** (misal di Detail Produk: Ringkasan, Resep BOM, Saluran Harga) | **Gunakan Tab Internal** | Menjaga pengguna tetap dalam konteks 1 produk tanpa kehilangan form state. |
| **Filter status pada daftar tabel yang sama** (misal di Pesanan: Semua, Menunggu Bayar, Diproses, Selesai) | **Gunakan Segmented Control Tab** | Filter cepat dalam 1 klik tanpa memuat ulang struktur tabel. |
| **Alur kerja independen frekuensi tinggi** (misal: Kasir POS vs Laporan Kasir vs Manajemen Shift) | **Wajib Halaman / Route Mandiri** | Kasir butuh fokus layar penuh, tidak boleh disembunyikan di dalam tab kecil. |
| **Form tambah/ubah data cepat** (misal: Tambah Bahan, Edit Supplier) | **Wajib Modal Sheet Full-Size XXL** | Menghindari navigasi berpindah halaman yang merusak flow input. |

### B. Dua Standar Visual Tab COOCA
1. **Pill Segmented Control (Untuk Filter Status & Sub-Kategori Cepat)**:
   ```html
   <div class="inline-flex p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-xl gap-1">
       <button type="button" class="px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-lg bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm">Semua</button>
       <button type="button" class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white">Menunggu Bayar</button>
       <button type="button" class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white">Diproses</button>
   </div>
   ```
2. **Underline Tab Bar (Untuk Halaman Master-Detail / Modul Multi-Section)**:
   ```html
   <div class="flex items-center gap-6 border-b border-black/[0.06] dark:border-white/[0.08] mb-6 overflow-x-auto">
       <button type="button" class="pb-3 text-sm sm:text-base font-semibold text-[#007AFF] border-b-2 border-[#007AFF] whitespace-nowrap">Ringkasan Info</button>
       <button type="button" class="pb-3 text-sm sm:text-base font-medium text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white whitespace-nowrap">Resep BOM</button>
       <button type="button" class="pb-3 text-sm sm:text-base font-medium text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white whitespace-nowrap">Harga Saluran</button>
   </div>
   ```

### C. Mandat Sinkronisasi URL (Deep-Linking Tabs)
- Tab internal wajib mendukung parameter URL (contoh: `?tab=bom` atau `?tab=channel-pricing`).
- Ketika pengguna me-refresh halaman atau membagikan link ke rekan kerja, sistem **WAJIB membuka tab yang dituju**, bukan mereset ke tab pertama.
- Gunakan Alpine.js watcher:
  ```html
  <div x-data="{ activeTab: new URLSearchParams(window.location.search).get('tab') || 'overview' }"
       x-init="$watch('activeTab', val => {
           const url = new URL(window.location);
           url.searchParams.set('tab', val);
           window.history.replaceState({}, '', url);
       })">
  ```

---

## 🗂️ 4. Mandat Information Architecture (IA): Pemisahan Operasional vs Settings Hub

### Masalah Utama yang Dilarang Keras:
Dilarang keras menaruh menu-menu pengaturan konfigurasi teknis (seperti *"Pengaturan Printer"*, *"Format Nota"*, *"Integrasi WA"*, *"Setting Pajak"*, *"Setting Biaya Ongkir"*, *"Akun Bank"*) sebagai menu level-1 di sidebar utama. Hal ini menyebabkan sidebar membengkak menjadi 25+ menu dan membingungkan kasir/staf operasional.

### Standar 4 Klaster Menu Sidebar Backoffice:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 1. KLASTER OPERASIONAL HARIAN (Daily Operations - Akses Kasir & Staf)       │
│    • 📊 Dashboard & Ringkasan                                               │
│    • 💻 Kasir POS Terminal                                                  │
│    • 📋 Pesanan & Transaksi Masuk                                           │
│    • 🚗 SPK & PKB Servis (Khusus Bengkel) / 🍽️ Meja & KDS (Khusus F&B)     │
│    • 🚚 Surat Jalan & Pengiriman                                            │
├─────────────────────────────────────────────────────────────────────────────┤
│ 2. KLASTER MASTER DATA & KATALOG (Pengelolaan Barang & Hubungan Bisnis)     │
│    • 📦 Produk & Layanan (Katalog, Resep BOM, Varian)                       │
│    • 🏢 Multi-Gudang & Stok (Opname, Mutasi, Surat Penerimaan GR)           │
│    • 👥 Pelanggan & CRM (Member, Poin Loyalitas)                            │
│    • 🏭 Pemasok & Pembelian (Purchase Order PO)                             │
│    • 👔 Karyawan & Presensi (HRM)                                           │
├─────────────────────────────────────────────────────────────────────────────┤
│ 3. KLASTER LAPORAN & KEUANGAN (Analitik & Pembukuan)                        │
│    • 💰 Buku Kas & Bank (Auto-Journal)                                      │
│    • 📈 Laporan Penjualan & Laba Rugi                                       │
│    • 📑 Analitik Performa Bisnis                                            │
├─────────────────────────────────────────────────────────────────────────────┤
│ 4. PUSAT PENGATURAN TERPADU (UNIFIED SETTINGS HUB - SATU MENU /settings)    │
│    • ⚙️ Pengaturan Bisnis (Settings Hub Terpusat)                           │
│      ├── Tab 1: Profil Usaha & Outlet (Nama, Alamat, Geofence, Logo)        │
│      ├── Tab 2: Kasir & Nota (Thermal Printer ESC/POS, Footer Nota, Laci)   │
│      ├── Tab 3: Pajak & Pembayaran (PPN, PB1, QRIS, Akun Bank Default)     │
│      ├── Tab 4: Integrasi Saluran (WhatsApp Meta API, Biteship Kurir, SMTP) │
│      ├── Tab 5: Hak Akses & Keamanan (Role, Permission, PIN Supervisor)     │
│      └── Tab 6: Paket Langganan & Storage (Limitasi Plan, Pruning Log/Data)│
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Protokol Audit Konsistensi UI & IA (5-Tahap)

Saat menjalankan audit UI layout, tab, dan navigasi:

1. **Audit Shell & Layout Wrapper**:
   - Periksa apakah Admin Panel menggunakan `<x-admin-layout>`, Owner Panel menggunakan `<x-app-layout>`, dan Customer menggunakan `<x-customer-layout>`.
   - Pastikan max-width wrapper konsisten (`max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8`).
2. **Audit Hierarki Header & Title**:
   - Periksa keberadaan overline tipografis tanpa kapsul pill.
   - Cek H1 font weight dan ukuran tipografi.
   - Pastikan tombol aksi berada di baris judul kanan atas.
3. **Audit Tab & Sub-Navigasi**:
   - Identifikasi tab yang salah tempat (tab yang seharusnya jadi menu mandiri atau modal sheet).
   - Pastikan styling tab mematuhi Segmented Control atau Underline Tab Bento Apple HIG.
   - Uji apakah tab mendukung deep-linking `?tab=...` saat reload.
4. **Audit Information Architecture Sidebar (Pembersihan Polusi Menu Settings)**:
   - Cari menu konfigurasi teknis yang berceceran di sidebar root.
   - Pindahkan seluruh konfigurasi ke dalam modul terpadu `/settings` dengan navigasi tab bento.
5. **Interactive Confirmation Gate**:
   - Sajikan laporan temuan inkonsistensi UI, peta migrasi menu sidebar, dan rencana perbaikan terstruktur sebelum mengubah file Blade.
