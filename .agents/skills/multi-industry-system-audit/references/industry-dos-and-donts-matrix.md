# Matriks Lengkap Do's & Don'ts 20 Industri Cooca

Dokumen ini adalah katalog aturan resmi untuk membedah kesesuaian antarmuka, menu, fitur, formulir, dan validasi backend bagi **20 sektor industri bisnis** di ekosistem Cooca.

---

## 🍽️ Klaster 1: Kuliner & F&B (5 Industri)
*Cakupan: Restoran Dine-in (`fnb_resto`), Coffee Shop & Cafe (`fnb_cafe`), Bakery & Toko Roti (`fnb_bakery`), Cloud Kitchen (`fnb_cloud_kitchen`), Katering Prasmanan/Diet (`fnb_catering`).*

### 1. Karakteristik & Kebutuhan Inti
- Transaksi frekuensi tinggi, antrean cepat kasir, pengelolaan meja/sesi makan, pemisahan struk dapur vs kasir, pemotongan bahan baku resep takaran gram/ml, dan integrasi kurir pesan-antar makanan (GoFood/GrabFood/ShopeeFood).

### 2. Aturan Do's & Don'ts
| Area Sistem | DO (Wajib Tampil & Aktif) | DON'T (Wajib Sembunyi / Dilarang Muncul) |
|---|---|---|
| **Menu Navigasi & Sidebar** | • Denah Meja & Billing Meja (`pos_dinein`) *(khusus Resto & Cafe)*<br>• Kitchen Display System (KDS)<br>• Resep Formula BOM & Bahan Baku (`recipe_bom`)<br>• Shift Kasir & Blind Cash Count | • Penjualan Kontrak B2B & PO Penawaran (`b2b_sales`)<br>• Tarif Upah Jam Mesin Pabrik (`labor_machines`)<br>• Modul Servis/Kendaraan Bengkel |
| **Kasir POS Terminal** | • Pilihan Mode: Dine-in, Takeaway, Ojol (`gofood`, `grabfood`, `shopeefood`)<br>• Tombol Kirim Pesanan ke Dapur (Fire Ticket)<br>• Split Bill (Pisah Tagihan Meja)<br>• Modifier Varian (Level Pedas, Es, Gula, Extra Topping) | • Input Plat Nomor Kendaraan / Odometer<br>• Input Barcode Scanner Ritel Panjang<br>• Pilihan Termin Pembayaran Tempo B2B (Net 30) |
| **Form Master Produk** | • Bento Box: Resep BOM (Bahan Baku per Porsi)<br>• Bento Box: Multi-Harga Saluran POS (F&B Delivery)<br>• Pengaturan Modifier & Add-on Varian | • Kolom Nomor Seri / IMEI Hardware<br>• Kolom Garansi Purna Jual Servis<br>• Biaya Jam Mesin Produksi |
| **Terminologi Bahasa** | Gunakan: *"Daftar Menu"*, *"Bahan Masak"*, *"Meja"*, *"Dapur"*, *"Pelanggan / Tamu"* | Jangan gunakan: *"Suku Cadang"*, *"Barang Dagangan"*, *"Stall Servis"*, *"Mesin Bubut"* |

---

## 🏭 Klaster 2: Manufaktur, Konveksi & Percetakan (5 Industri)
*Cakupan: Konveksi Garment (`mfg_garment`), Percetakan & Offset (`mfg_printing`), Mebel Furniture (`mfg_furniture`), Bubut & Logam Presisi (`mfg_precision`), Kerajinan Craft (`mfg_craft`).*

### 1. Karakteristik & Kebutuhan Inti
- Produksi berbasis pesanan (Make-to-Order), akumulasi HPP kompleks multi-tier (Bahan Mentah + Upah Buruh Jahit/Operator + Depresiasi Listrik/Mesin), Down Payment (DP) 50%, Purchase Order bahan baku roll/rim/kayu, dan simulator titik impas (BEP).

### 2. Aturan Do's & Don'ts
| Area Sistem | DO (Wajib Tampil & Aktif) | DON'T (Wajib Sembunyi / Dilarang Muncul) |
|---|---|---|
| **Menu Navigasi & Sidebar** | • Kalkulator HPP Multi-Tier & Resep BOM (`recipe_bom`)<br>• Tarif Upah Kerja & Jam Mesin (`labor_machines`)<br>• Sales Order & Invoice B2B (`b2b_sales`)<br>• Purchase Order Pengadaan Bahan Baku (`procurement`)<br>• Simulator BEP & Target Margin | • Denah Meja Dine-in (`pos_dinein`)<br>• Layar Kitchen Display System (KDS)<br>• Kasir Cepat Struk Ritel (`pos_retail`) |
| **Formulir Transaksi** | • Input Termin DP & Pelunasan Bertahap<br>• Estimasi Tanggal Selesai Produksi (Lead Time)<br>• Lampiran Dokumen Spesifikasi Desain / Mockup | • Pilihan Meja Makan Resto<br>• Pilihan Delivery Ojek Online F&B |
| **Form Master Produk** | • Bento Box: Multi-Tier Resep BOM & Scrap/Waste Yield Tolerance<br>• Bento Box: Komponen Biaya Tenaga Kerja & Mesin<br>• Minimal Order Quantity (MOQ) | • Pilihan Suhu/Level Pedas F&B<br>• Saluran Harga Ojol (GoFood/GrabFood) |
| **Terminologi Bahasa** | Gunakan: *"Bahan Baku Mentah"*, *"Biaya Produksi / HPP"*, *"Surat Pesanan (SO)"*, *"Klien / Perusahaan"* | Jangan gunakan: *"Makanan"*, *"Minuman"*, *"Porsi"*, *"Pelayan / Waiter"* |

---

## 🛒 Klaster 3: Ritel Toko Kelontong, Minimarket & Apotek (2 Industri)
*Cakupan: Minimarket / Kelontong / Toko Baju (`retail_reseller`), Apotek & Toko Obat (`retail_pharmacy`).*

### 1. Karakteristik & Kebutuhan Inti
- Ribuan SKU barang fisik, scanning barcode kilat (USB/Bluetooth), stock opname berkala, pemisahan satuan bertingkat (1 Dus = 24 Pack = 144 Pcs), batas minimum stok menipis (*low stock alert*), dan tier harga grosir (beli banyak lebih murah).

### 2. Aturan Do's & Don'ts
| Area Sistem | DO (Wajib Tampil & Aktif) | DON'T (Wajib Sembunyi / Dilarang Muncul) |
|---|---|---|
| **Menu Navigasi & Sidebar** | • Terminal Kasir POS Barcode Cepat (`pos_retail`)<br>• Multi-Gudang & Rak Etalase (`inventory_warehouse`)<br>• Stock Opname Fisik Digital<br>• Poin Loyalitas & Kupon Member (`crm_loyalty`) | • Denah Meja Makan Resto (`pos_dinein`)<br>• Resep Formula BOM Dapur (`recipe_bom`)<br>• Biaya Jam Mesin Pabrik (`labor_machines`) |
| **Kasir POS Terminal** | • Fokus Kursor Otomatis ke Input Barcode Scanner<br>• Dukungan Timbangan Digital / Scanner Barcode Berat<br>• Modal Buka Laci Kas Otomatis (ESC/POS Drawer Trigger)<br>• Format Struk Ramping Kertas Thermal 58mm / 80mm | • Pemilihan Nomor Meja Makan<br>• Tombol Kirim ke KDS Dapur<br>• Input No. Rangka Kendaraan |
| **Form Master Produk** | • Kolom Barcode / SKU / No. Registrasi BPOM<br>• Konversi Multi-Satuan (Dus ➔ Pack ➔ Pcs)<br>• Tier Harga Grosir Bertingkat (Wholesale Pricing)<br>• Peringatan Batas Minimum Stok Menipis | • Bento Resep Makanan BOM<br>• Bento Biaya Tenaga Kerja Per Jam<br>• Saluran Harga Delivery F&B |
| **Terminologi Bahasa** | Gunakan: *"Produk / Barang Jadi"*, *"Stok di Rak"*, *"Kasir Ritel"*, *"Member / Pelanggan"* | Jangan gunakan: *"Menu Sajian"*, *"Bahan Racikan"*, *"Jasa Perbaikan"* |

---

## 🔧 Klaster 4: Jasa Operasional Harian (4 Industri)
*Cakupan: Bengkel Mobil & Motor (`service_workshop`), Salon & Barbershop (`service_barbershop`), Laundry Kiloan & Satuan (`service_laundry`), Cuci Mobil & Detailing (`service_autodetailing`).*

### 1. Karakteristik & Kebutuhan Inti
- Pemisahan tegas antara **Jasa Tenaga Kerja** vs **Suku Cadang / Barang Terpakai**, penugasan teknisi/mekanik/stylist per pekerjaan, Surat Perintah Kerja (PKB / SPK), data kendaraan (plat nomor, tipe, KM) untuk bengkel, booking antrean/waktu untuk salon, dan kalkulator berat timbangan (kg) untuk laundry.

### 2. Aturan Do's & Don'ts
| Area Sistem | DO (Wajib Tampil & Aktif) | DON'T (Wajib Sembunyi / Dilarang Muncul) |
|---|---|---|
| **Menu Navigasi & Sidebar** | • SPK & Perintah Kerja Layanan Servis<br>• Penugasan Teknisi / Mekanik / Stylist<br>• Jadwal Reservasi Jam Layanan (`reservation`)<br>• Riwayat Servis Pelanggan (Service History) | • Denah Meja Makan Resto (`pos_dinein`)<br>• Layar Dapur KDS<br>• Resep Masakan Bahan Pangan |
| **Formulir Transaksi / POS** | • Bengkel: Input Plat Nomor, Merk/Tipe Kendaraan, Odometer KM<br>• Salon: Pilih Kursi / Stylist & Slot Jam<br>• Laundry: Input Berat Timbangan (Kg) & Estimasi Ambil<br>• Pemisahan Baris: Ongkos Jasa vs Suku Cadang Fisik | • Pilihan Meja Makan Resto<br>• Saluran Ojek Online Makanan (GoFood/ShopeeFood)<br>• Pilihan Level Gula / Es Minuman |
| **Form Master Layanan / Produk** | • Pemilihan Tipe Entitas: `Jasa Murni (Bebas Stok)` vs `Barang Fisik (Ada Stok)`<br>• Estimasi Durasi Pengerjaan (Menit / Jam)<br>• Komisi Teknisi / Mekanik per Pekerjaan<br>• Masa Garansi Servis (Hari / Bulan) | • Resep BOM Bahan Pangan Dapur<br>• Pilihan Harga Saluran Ojol<br>• Biaya Depresiasi Jam Mesin Pabrik |
| **Terminologi Bahasa** | Gunakan: *"Jasa & Suku Cadang"*, *"Kendaraan / Pelat Nomor"*, *"Mekanik / Terapis"*, *"Surat Perintah Kerja (PKB)"* | Jangan gunakan: *"Daftar Menu"*, *"Koki"*, *"Meja Makan"*, *"Bahan Masakan"* |

---

## 🏗️ Klaster 5: Jasa Proyek, Kontraktor & Agensi (3 Industri)
*Cakupan: Kontraktor Bangunan (`service_contractor`), Event & Wedding Organizer (`service_event`), Digital Creative & IT Agency (`service_agency`).*

### 1. Karakteristik & Kebutuhan Inti
- Kontrak kerja jangka panjang, pembayaran bertahap berbasis progres/termin (Milestone / Down Payment ➔ Termin 1 ➔ Termin 2 ➔ Pelunasan), klaim biaya operasional proyek, penawaran harga formal (Quotation), dan purchase order sub-kontraktor.

### 2. Aturan Do's & Don'ts
| Area Sistem | DO (Wajib Tampil & Aktif) | DON'T (Wajib Sembunyi / Dilarang Muncul) |
|---|---|---|
| **Menu Navigasi & Sidebar** | • Surat Penawaran & Invoice Termin B2B (`b2b_sales`)<br>• Pengadaan Subkon & PO Supplier (`procurement`)<br>• Akuntansi Lengkap Proyek (`accounting_corporate`) | • Kasir POS Cepat Ritel (`pos_retail`)<br>• Denah Meja Restoran (`pos_dinein`)<br>• Resep BOM Makanan Dapur (`recipe_bom`) |
| **Formulir Transaksi** | • Termin Pembayaran Berdasarkan Milestone Proyek<br>• Batas Waktu Berlakunya Penawaran (Quotation Validity)<br>• Rincian Rekening Bank Perusahaan untuk Transfer Formal | • Tombol Cetak Struk Kasir Thermal Kasir<br>• Pilihan Saluran Pengantaran Ojol |
| **Terminologi Bahasa** | Gunakan: *"Proyek / Pekerjaan"*, *"Surat Penawaran"*, *"Faktur Termin"*, *"Klien Perusahaan"* | Jangan gunakan: *"Menu Makanan"*, *"Kasir Toko"*, *"Struk Belanja"* |

---

## 🚚 Klaster 6: Distribusi, Grosir & Agro (2 Industri)
*Cakupan: Distributor Grosir FMCG (`distributor_fmcg`), Usaha Pertanian & Peternakan (`agri_farming`).*

### 1. Karakteristik & Kebutuhan Inti
- Pengiriman volume besar dalam jumlah karton/ton, multi-lokasi gudang pusat vs cabang, surat jalan / Delivery Order (DO), plafon batas kredit piutang toko langganan (*credit limit check*), dan harga khusus per kontrak langganan.

### 2. Aturan Do's & Don'ts
| Area Sistem | DO (Wajib Tampil & Aktif) | DON'T (Wajib Sembunyi / Dilarang Muncul) |
|---|---|---|
| **Menu Navigasi & Sidebar** | • Multi-Gudang Logistik & Transfer Stok (`inventory_warehouse`)<br>• Penjualan Grosir B2B & Surat Jalan (`b2b_sales`)<br>• Kontrol Plafon Piutang Toko (AR Credit Limit)<br>• Purchase Order Pengadaan Grosir (`procurement`) | • Kasir Cepat Ritel Toko (`pos_retail`)<br>• Denah Meja Resto (`pos_dinein`)<br>• KDS Layar Dapur |
| **Formulir Transaksi** | • Validasi Batas Kredit Piutang (Tolak jika piutang toko over-limit)<br>• Alamat Pengiriman Gudang Penerima & Ekspedisi Truk<br>• Nomor Surat Jalan (DO) & Berita Acara Timbang | • Pemilihan Meja Makan Resto<br>• Opsi Ojol Delivery |
| **Terminologi Bahasa** | Gunakan: *"Gudang Logistik"*, *"Surat Jalan"*, *"Faktur Grosir"*, *"Mitra Toko / Agen"* | Jangan gunakan: *"Meja Kafe"*, *"Pelayan"*, *"Porsi"* |
