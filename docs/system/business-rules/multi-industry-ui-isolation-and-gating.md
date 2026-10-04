# Multi-Industry UI Isolation & Gating (Zero Feature Leakage)

> **Status:** CURRENT STATE (Kondisi Sistem Berjalan)  
> **Mandat:** Panduan teknis dan aturan bisnis isolasi fitur antarmuka lintas 25 templat industri COOCA untuk menjamin *Zero Feature Leakage* (tidak ada kebocoran tombol, modal, atau navigasi milik industri lain).  
> **Hierarki Kebenaran:** Aktual Implementasi > Database Schema > Tests > Dokumentasi Lama > Asumsi (DILARANG).

---

## 1. Ikhtisar & Prinsip Dasar
COOCA mendukung 25 templat industri (*Business Templates*) yang terbagi dalam klaster F&B, Ritel & Dagang, Layanan/Jasa Operasional, Jasa Profesional, dan Manufaktur/Produksi. Setiap sektor memiliki proses bisnis khas yang tidak boleh mencemari sektor lain:

1. **Prinsip *Zero Feature Leakage*:**
   - Fitur restoran (nomor meja, order QR, antrean dapur KDS) **DILARANG** muncul pada akun non-F&B (Ritel, Bengkel, Apotek, dsb.).
   - Bisnis F&B tanpa layanan makan di tempat (seperti katering, diet meal prep, atau bakery produksi) **DILARANG** menampilkan denah meja fisik atau buku reservasi meja.
   - Fitur layanan vertikal spesifik (SPK bengkel, timbangan laundry kiloan, nomor batch/ED apotek) **HANYA** boleh tampil pada sektor industrinya masing-masing.
   - Sektor non-manufaktur (seperti ritel dan agensi) **DILARANG** menampilkan kalkulator jam kerja/mesin atau menu resep formula bahan baku jika modul bersangkutan dinonaktifkan.

---

## 2. Matriks Gating Fitur Antarmuka Utama

| Modul / Komponen UI | Kondisi Gating / Pemeriksaan Domain | Sektor yang Melihat | Sektor yang Disembunyikan Otomatis |
| :--- | :--- | :--- | :--- |
| **F&B Order QR, Meja Resto & KDS** (`terminal.blade.php`, `topbar.blade.php`, `app.blade.php`) | `$activeBiz->hasDineInFeature()` | F&B Resto, Cafe, Warung Makan | Ritel, Apotek, Bengkel, Laundry, Manufaktur, Katering Non-Dine-In |
| **Badge Meja Tamu Pesanan** (`orders.blade.php`) | `$o->table_or_reference` | Pesanan F&B Dine-In | Transaksi Ritel, Reseller, Jasa Umum |
| **SPK Bengkel & Kendaraan** (`terminal.blade.php`) | `$business->isWorkshop() && !isLaundry() && !isPharmacy() && !isFoodIndustry()` | Bengkel Mobil/Motor, Servis, Cuci Kendaraan | Seluruh sektor selain bengkel |
| **Timbangan Cucian & Rak** (`terminal.blade.php`) | `$business->isLaundry()` | Laundry Kiloan, Dry Clean | Seluruh sektor selain laundry |
| **Mode Apotek & Batch/ED** (`terminal.blade.php`) | `$business->isPharmacy()` | Toko Obat, Apotek, Farmasi | Seluruh sektor selain apotek |
| **B2B Sales Group** (`sidebar.blade.php`, `topbar.blade.php`) | `$activeBiz->isModuleEnabled(MODULE_B2B_SALES)` | Distributor, B2B, Manufaktur, Toko Bahan | Ritel Kasir Langsung, Barbershop, Laundry |
| **Pengadaan (Procurement)** (`sidebar.blade.php`) | `$activeBiz->isModuleEnabled(MODULE_PROCUREMENT)` | Manufaktur, Grosir, Restoran Besar | Agensi Kreatif, Jasa Acara, Barbershop |
| **Bahan Baku & Resep BOM** (`sidebar.blade.php`, `topbar.blade.php`) | `$activeBiz->isModuleEnabled(MODULE_RECIPE_BOM)` | F&B, Bakery, Manufaktur Garment/Mebel | Reseller Ritel Pakaian, Agensi, Jasa Umum |
| **Upah Kerja Langsung & Mesin** (`sidebar.blade.php`, `calculator.blade.php`, `profitability/index.blade.php`, `labor-machines/index.blade.php`) | `$activeBiz->isModuleEnabled(MODULE_LABOR_MACHINES)` | Pabrik, Konveksi, Percetakan, Manufaktur | Ritel Reseller, Toko Obat, Agribisnis |
| **Gudang Logistik Multi-Outlet** (`topbar.blade.php`) | `$activeBiz->isModuleEnabled(MODULE_INVENTORY_WAREHOUSE)` | Multi-Cabang, Ritel Berantai, Manufaktur | Usaha Mikro Jasa (Barbershop, Laundry Kecil) |

---

## 3. Implementasi Teknis & Best Practice

### 3.1 Resolusi Variabel Industri di POS Terminal (`terminal.blade.php`)
```php
@php
    $isWorkshop = $business && $business->isWorkshop() && ! $business->isLaundry() && ! $business->isPharmacy() && ! $business->isFoodIndustry();
    $isLaundry = $business && $business->isLaundry();
    $isPharmacy = $business && $business->isPharmacy();
    $isFnB = $business && $business->isFoodIndustry();
    $hasDineIn = $business && $business->hasDineInFeature();
    $isRetail = ! $isWorkshop && ! $isLaundry && ! $isPharmacy && ! $isFnB;
@endphp
```
*Catatan Penting:* Hindari penggunaan `isModuleEnabled()` pada modul yang tidak terdaftar dalam konstanta `ModuleRegistry`, karena method tersebut memeriksa ketiadaan dalam `$business->disabled_modules` sehingga nama modul yang tidak dikenali akan selalu mengembalikan `true`.

### 3.2 Modal Detail Item Adaptif
Header modal item di POS terminal menyesuaikan industri secara otomatis:
- **Apotek:** "Detail & Dosis Obat" dengan badge hijau `#34C759` dan ikon obat.
- **Industri Lain:** "Detail & Catatan Item" dengan badge biru `#007AFF` dan ikon catatan pensil.

### 3.3 Resolusi Rute Sub-Nav Kalkulator HPP
Seluruh referensi rute kalkulator HPP pada sub-navigasi tab wajib menggunakan `route('calculator.index')` (bukan `route('calculator')`) untuk menjamin resolusi rute konsisten dengan deklarasi `routes/owner.php`.

---

## 4. Verifikasi & Pengujian
Seluruh aturan isolasi ini diuji secara ketat dan terotomatisasi pada:
- [`tests/Feature/LayoutMultiIndustryAutoHidingTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/LayoutMultiIndustryAutoHidingTest.php)
- [`tests/Feature/Pos/PosTerminalIndustryGatingTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/Pos/PosTerminalIndustryGatingTest.php)
- [`tests/Feature/LayoutFinalRemediationAcceptanceTest.php`](file:///c:/laragon/www/cooca_core/tests/Feature/LayoutFinalRemediationAcceptanceTest.php)
