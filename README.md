# 🏪 COOCA CORE — Platform Ekosistem SaaS Manajemen Bisnis & POS UMKM

[![Production](https://img.shields.io/badge/Production-umkm.cooca.id-emerald?style=flat-square&logo=googlechrome)](https://umkm.cooca.id)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue?style=flat-square&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-slate?style=flat-square)](#)

**COOCA Core** adalah platform manajemen bisnis all-in-one yang dirancang untuk UMKM Indonesia, mencakup kalkulasi Harga Pokok Penjualan (HPP) berbasis aktivitas (ABC), Point of Sale (POS), manajemen inventori multi-gudang, purchase & sales order, invoicing, CRM/loyalty, landing page builder publik, hingga integrasi WhatsApp Gateway otomatis.

---

## 🌐 Production Deployment & Domain

- **Domain Aplikasi Production:** **[https://umkm.cooca.id](https://umkm.cooca.id)**
- **Subdomain Hostinger:** `umkm` di bawah domain utama `cooca.id`
- **Folder Root Web Server:** `public_html/umkm.cooca.id` (atau `public_html/umkm`)
- **API Base URL:** `https://umkm.cooca.id/api/v1`
- **Microservice WhatsApp:** Terhubung mandiri ke repository [`mustaqim-project/cooca-wa-server`](https://github.com/mustaqim-project/cooca-wa-server)

---

## 🚀 Fitur Utama COOCA Core

1. **POS & Terminal Kasir Modern**:
   - Dukungan scan barcode, hold/resume transaksi kasir, split bill, cetak struk A4/thermal, dan kirim struk instan via WhatsApp.
2. **Kalkulator HPP & Manajemen BOM (Bill of Materials)**:
   - Kalkulasi biaya bahan baku, konversi multi-satuan, depresiasi mesin, dan tarif tenaga kerja per jam.
3. **Purchasing & Sales Workflow**:
   - Alur surat penawaran (Quotation) $\rightarrow$ Pesanan Penjualan (Sales Order) $\rightarrow$ Faktur Penjualan (Invoice) dengan konversi 1-klik.
4. **Inventori & Pergudangan**:
   - Stock opname fisik, rekonsiliasi selisih stok, dan transfer barang antar cabang/outlet.
5. **CMS & Landing Page Bisnis Publik**:
   - Setiap tenant bisnis memiliki halaman katalog produk, slider menu/layanan, galeri foto, kontak sosial media, dan integrasi WhatsApp (`/b/{slug}`).
6. **Sistem Notifikasi & Custom Popup (AppAlert)**:
   - Zero native alert/confirm — 100% menggunakan custom popup modal dialog dan toast responsif modern yang aman dari XSS dan mendukung Dark/Light Mode.
7. **Integrasi WhatsApp Gateway Otomatis**:
   - Kirim struk belanja otomatis, broadcast promo, dan reminder invoice yang terhubung dengan microservice Baileys di VPS / Cloud.

---

## 💻 Panduan Instalasi Lokal (Development)

### Prasyarat:
- PHP 8.2+
- Composer
- MySQL 8.0+ / MariaDB
- Node.js 18+ & NPM
- Laragon / XAMPP

### Langkah Instalasi:
```bash
# 1. Clone repository
git clone https://github.com/mustaqim-project/cooca_core.git
cd cooca_core

# 2. Install dependensi
composer install
npm install

# 3. Salin environment
cp .env.example .env
php artisan key:generate

# 4. Migrasi Database & Seeder
php artisan migrate --seed

# 5. Jalankan server lokal
php artisan serve --port=1986
npm run dev
```

Akses lokal di browser: `http://127.0.0.1:1986`.

---

## 📖 Dokumentasi Deployment

- 📘 [Panduan Deployment Hostinger (umkm.cooca.id)](docs/HOSTINGER_DEPLOYMENT_GUIDE.md)
- 📗 [Panduan Microservice WhatsApp Gateway](docs/GOOGLE_CLOUD_FREE_VPS_WA_GUIDE.md)

---

## 🔒 Lisensi
Hak Cipta © 2026 **COOCA UMKM**. Seluruh hak cipta dilindungi undang-undang.
