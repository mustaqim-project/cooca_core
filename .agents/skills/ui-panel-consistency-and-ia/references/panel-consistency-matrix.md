# Matriks Konsistensi Bentuk UI Antar 3 Panel COOCA

Dokumen ini adalah standar teknis spesifikasi visual, tata letak (*layout*), hierarki judul (*page title*), kartu bento, navigasi, dan struktur tab di seluruh **Admin Panel**, **Owner Panel**, dan **Customer Panel**.

---

## 🧭 1. Perbandingan Komparatif 3 Panel

| Dimensi | 1. Admin Panel (`admin.*`) | 2. Owner / User Panel (`app.*`) | 3. Customer Panel (`customer.*` / Storefront) |
|---|---|---|---|
| **Audience Target** | Superadmin platform SaaS, Technical Operator | Pemilik Usaha (UMKM), Kasir, Staf Gudang, Akuntan | Pembeli publik, member toko, tamu reservasi |
| **Prinsip Utama** | High Glanceability, Platform Monitor, Multi-Tenant Overview | Zero-Manual UI, Lapang, Ramah Sentuhan Layar Kasir (48–52px) | Toko Online Modern, Estetik, Visual-Heavy, Frictionless Checkout |
| **Layout Shell** | `<x-admin-layout>` | `<x-app-layout>` | `<x-customer-layout>` |
| **Max-Width Wrapper** | `max-w-[1600px] mx-auto px-4 sm:px-6` | `max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8` | `max-w-[1280px] mx-auto px-4 sm:px-6` |
| **Background Kanvas** | `#F2F2F7` (Light) / `#000000` (Dark) | `#F2F2F7` (Light) / `#000000` (Dark) | `#FAFAFA` (Light) / `#09090B` (Dark) |
| **Permukaan Kartu** | `#FFFFFF` (Light) / `#1C1C1E` (Dark) | `#FFFFFF` (Light) / `#1C1C1E` (Dark) | `#FFFFFF` (Light) / `#18181B` (Dark) |
| **Border Radius Bento** | `rounded-2xl` (16px) | `rounded-[20px]`–`rounded-[24px]` (Desktop), `rounded-2xl` (Mobile) | `rounded-2xl`–`rounded-3xl` |
| **Primary Color** | Apple System Blue `#007AFF` | Apple System Blue `#007AFF` | Accent Brand Toko / Netral Obsidian |
| **Ukuran Font Input Mobile**| 16px (Anti auto-zoom) | **16px Mutlak** (Anti auto-zoom Safari) | **16px Mutlak** |

---

## 🏷️ 2. Standar Struktur Header & Title Halaman

Setiap halaman di seluruh panel **WAJIB** menerapkan struktur header 3 baris yang konsisten tanpa pengecualian:

### Contoh Struktur Blade Lengkap:
```blade
<div class="mb-6 sm:mb-8">
    <!-- Baris 1: Overline Kicker Kategori / Breadcrumb -->
    <div class="flex items-center gap-2 mb-1.5">
        <a href="{{ route('products.index') }}" class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
            Katalog & Inventori
        </a>
        <span class="text-black/20 dark:text-white/20 text-xs">•</span>
        <span class="text-[11px] sm:text-[12px] font-semibold uppercase tracking-wider text-black/60 dark:text-white/60">
            Daftar Produk
        </span>
    </div>

    <!-- Baris 2: H1 Title + Badge Status + Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white">
                Produk & Layanan
            </h1>
            <!-- Badge Status Siklus Entitas (Opsional) -->
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20">
                24 Aktif
            </span>
        </div>

        <!-- Action Bar (Tombol Aksi Utama & Sekunder) -->
        <div class="flex items-center gap-2.5">
            <button type="button" @click="showExportModal = true" class="px-3.5 py-2 text-sm font-medium rounded-xl border border-black/[0.08] dark:border-white/[0.1] bg-white dark:bg-[#1C1C1E] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.04] transition active:scale-[0.98]">
                Ekspor
            </button>
            <button type="button" @click="showCreateModal = true" class="px-4 py-2 text-sm font-semibold rounded-xl bg-[#007AFF] text-white hover:bg-[#0062CC] shadow-sm transition active:scale-[0.98] inline-flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Produk</span>
            </button>
        </div>
    </div>

    <!-- Baris 3: Subtitle Ringkas Faktual ATAU Filter Bar Segmented -->
    <p class="mt-2 text-sm text-black/60 dark:text-white/60 max-w-3xl leading-relaxed">
        Kelola harga jual, takaran bahan baku resep BOM, dan kuota stok gudang di seluruh cabang.
    </p>
</div>
```

---

## 🎛️ 3. Panduan Implementasi Arsitektur Tab Navigasi

### Pola 1: Segmented Control Tabs (Filter Data Cepat)
Gunakan untuk filter status data di halaman index tabel:

```html
<div class="inline-flex p-1 bg-black/[0.05] dark:bg-white/[0.08] rounded-xl gap-1 mb-6">
    <button type="button" 
            @click="activeStatus = 'all'"
            :class="activeStatus === 'all' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
            class="px-3.5 py-1.5 text-xs sm:text-sm rounded-lg transition-all">
        Semua (48)
    </button>
    <button type="button" 
            @click="activeStatus = 'pending'"
            :class="activeStatus === 'pending' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
            class="px-3.5 py-1.5 text-xs sm:text-sm rounded-lg transition-all">
        Menunggu Pembayaran (12)
    </button>
    <button type="button" 
            @click="activeStatus = 'completed'"
            :class="activeStatus === 'completed' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
            class="px-3.5 py-1.5 text-xs sm:text-sm rounded-lg transition-all">
        Selesai (36)
    </button>
</div>
```

### Pola 2: Underline Tab Bar (Section Form / Detail Entitas)
Gunakan untuk membagi section formulir atau detail produk:

```html
<div class="flex items-center gap-6 border-b border-black/[0.06] dark:border-white/[0.08] mb-6 overflow-x-auto">
    <button type="button" 
            @click="activeTab = 'general'"
            :class="activeTab === 'general' ? 'border-[#007AFF] text-[#007AFF] font-semibold' : 'border-transparent text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white font-medium'"
            class="pb-3 text-sm sm:text-base border-b-2 transition whitespace-nowrap">
        Informasi Umum
    </button>
    <button type="button" 
            @click="activeTab = 'recipe'"
            :class="activeTab === 'recipe' ? 'border-[#007AFF] text-[#007AFF] font-semibold' : 'border-transparent text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white font-medium'"
            class="pb-3 text-sm sm:text-base border-b-2 transition whitespace-nowrap">
        Resep BOM & Takaran
    </button>
    <button type="button" 
            @click="activeTab = 'channels'"
            :class="activeTab === 'channels' ? 'border-[#007AFF] text-[#007AFF] font-semibold' : 'border-transparent text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white font-medium'"
            class="pb-3 text-sm sm:text-base border-b-2 transition whitespace-nowrap">
        Harga Saluran (F&B / Ojol)
    </button>
</div>
```

---

## 🚫 4. Checklist Kesalahan Umum & Solusinya

| Kesalahan Umum yang Sering Terjadi | Dampak Terhadap UX | Solusi Standardisasi COOCA |
|---|---|---|
| **Eyebrow pill di atas judul** (`🟢 AI Powered System`) | Tampilan terasa murahan seperti template AI generik. | Hapus pill, gunakan teks tipografis polos berhuruf kapital kecil (*Pure Overline*). |
| **Tombol aksi terpencar di sembarang tempat** | Pengguna bingung mencari tombol aksi utama. | Wajib kumpulkan di pojok kanan atas sejajar dengan H1 Title. |
| **Tab tidak sinkron dengan URL** saat di-refresh | Pengguna kehilangan form yang sedang diedit dan kembali ke tab 1. | Wajib tambahkan `$watch('activeTab')` yang memperbarui `window.history` dan query param `?tab=...`. |
| **Menu setting berserakan di sidebar root** | Sidebar sangat panjang dan membingungkan kasir/staf harian. | Pindahkan semua setting ke route terpadu `/settings` dengan Bento Hub Sub-Tabs. |
| **Font input di mobile di bawah 16px** | Browser Safari di iOS melakukan auto-zoom otomatis yang merusak layout. | Seluruh `<input>`, `<select>`, `<textarea>` wajib `text-[16px] sm:text-[14px]`. |
