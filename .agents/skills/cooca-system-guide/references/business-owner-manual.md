# Ringkasan Panduan Operasional Pemilik Usaha (14 Sub-Modul Cooca)

Dokumen ini adalah intisari dari **Bab 3 `docs/SYSTEM_GUIDE.md`** yang disajikan khusus dalam bahasa yang ramah, lugas, dan bebas jargon teknis untuk pemilik bisnis UMKM (usia 40–65+ tahun).

---

## 1. Menentukan Modal & Harga Jual Ilmiah (Costing & HPP)
- **Kapan Digunakan?** Saat merilis menu baru, membuat produk kemasan, atau mengevaluasi margin keuntungan di tengah kenaikan harga bahan.
- **Cara Kerja 1-2-3:**
  1. Buka menu **Kalkulator HPP**.
  2. Masukkan takaran bahan baku (misal: *Kopi 18g, Susu 120ml, Cup 1 pcs*).
  3. Sistem otomatis menghitung total modal per porsi dan menyarankan harga jual ideal berdasarkan target margin (misal 60%).

## 2. Operasional Kasir Harian (Terminal Kasir POS)
- **Kapan Digunakan?** Melayani pesanan pelanggan setiap hari secara cepat dan akurat.
- **Fitur Kunci:**
  - **Pilih Saluran:** Dine-in (makan di tempat), Takeaway (bawa pulang), atau Ojek Online (GoFood/GrabFood/ShopeeFood) dengan harga yang menyesuaikan otomatis.
  - **Kirim ke Dapur:** Satu klik mengirim tiket pesanan ke layar koki dapur.
  - **Tutup Kasir Buta (Blind Cash Count):** Kasir menghitung uang fisik di laci tanpa melihat angka sistem terlebih dahulu, mencegah pencurian uang kas.

## 3. Manajemen Stok & Penerimaan Bahan (Gudang & GR)
- **Kapan Digunakan?** Saat kiriman bahan dari supplier tiba atau memindahkan stok antar-gudang.
- **Fitur Kunci:**
  - Penerimaan barang fisik via Surat Penerimaan Barang (Goods Receipt).
  - Gudang Pusat vs Sub-Gudang Cabang (misal Gudang Belakang vs Etalase Depan Kasir).
  - Transfer stok dua langkah (*Two-Step In-Transit*): Stok hanya bertambah di cabang tujuan setelah diverifikasi fisik diterima.

## 4. Toko Online & Checkout Mandiri (Storefront)
- **Kapan Digunakan?** Membuka cabang digital agar pelanggan bisa pesan dari rumah via WhatsApp atau link toko online.
- **Fitur Kunci:**
  - Katalog digital responsif di HP pembeli.
  - Pelanggan upload bukti transfer bank secara aman (Anti-IDOR).
  - Integrasi kurir otomatis (Biteship) untuk cek ongkir instan.

## 5. Pembukuan Otomatis Tanpa Pusing Akuntansi (Keuangan)
- **Kapan Digunakan?** Memantau arus kas masuk dan keluar tanpa perlu menyewa akuntan khusus.
- **Fitur Kunci:**
  - **Auto-Journal:** Setiap nota kasir terbayar otomatis menjurnal kas masuk dan HPP berkurang.
  - Pengingat piutang otomatis via WhatsApp ke pelanggan yang belum lunas.

## 6. Manajemen Katalog Produk, Resep BOM & Paket Kombo
- **Kapan Digunakan?** Mengatur varian ukuran, topping tambahan, dan paket hemat kombo.
- **Fitur Kunci:**
  - Paket Kombo hemat (misal Nasi + Ayam + Es Teh) otomatis menghitung total modal gabungan dan mendeteksi ketersediaan stok botol leher (*bottleneck*).

## 7. Bahan Baku, Pemasok & Layanan Jasa Bebas Stok
- **Kapan Digunakan?** Membedakan barang yang memiliki stok fisik (barang dagangan/sparepart) dengan layanan jasa murni yang bebas stok (ongkos mekanik, potong rambut).

## 8. Toko Online & Website Landing Page Studio
- **Kapan Digunakan?** Membangun website profil usaha dengan 25 pilihan preset industri tanpa koding.

## 9. Saluran WhatsApp Resmi (Meta Cloud API)
- **Kapan Digunakan?** Mengirim nota digital kasir otomatis ke nomor WhatsApp pembeli dan mengirim broadcast promo resmi anti-blokir.

## 10. Pengelolaan Media Sosial Terpadu (Meta, TikTok, LinkedIn)
- **Kapan Digunakan?** Menjadwalkan posting konten promosi ke Instagram, Facebook, TikTok, dan LinkedIn langsung dari satu dashboard.

## 11. Kepatuhan Pajak UMKM & Penggajian Karyawan (HRM & Tax)
- **Kapan Digunakan?** Menghitung gaji bulanan staf (gaji pokok, uang makan, lembur, komisi) dan kalkulasi pajak UMKM 0.5% (PP 55) serta PPh 21 TER otomatis.

## 12. Analitik Bisnis & Tren Pertumbuhan (Analytics Suite)
- **Kapan Digunakan?** Melihat laporan performa bisnis mingguan/bulanan: jam tersibuk kasir, menu terlaris, dan proyeksi arus kas.

## 13. Pusat Otorisasi Dokumen (MAR Engine)
- **Kapan Digunakan?** Otorisasi berjenjang Maker-Approver-Releaser untuk transaksi berisiko tinggi (pembelian mesin, kas keluar besar).

## 14. Portal Karyawan & Presensi Mandiri
- **Kapan Digunakan?** Staf operasional absensi via HP masing-masing dengan validasi titik GPS lokasi toko dan swafoto, serta melihat slip gaji mandiri tanpa bisa mengintip laba rugi perusahaan.
