@extends('public.partials.subpage_layout', [
    'title' => 'Solusi Manufaktur & Pabrikasi',
    'category' => 'Solutions',
    'badge' => 'Produksi & Pabrik',
    'icon' => 'factory',
    'headline' => 'Solusi Manufaktur: Perintah Produksi, BOM, & Alokasi Tenaga Kerja',
    'subtitle' => 'Cocok untuk konveksi pakaian, pabrik makanan olahan, pengrajin kayu/mebel, dan industri rumahan. Kelola bahan mentah menjadi produk jadi siap jual.',
    'features' => [
        ['icon' => 'layers', 'title' => 'Bill of Materials (BOM) Multi-Tingkat', 'desc' => 'Definisikan formula bahan baku, bahan penolong, dan kemasan untuk setiap unit produk jadi.'],
        ['icon' => 'play-circle', 'title' => 'Perintah Kerja Produksi (Work Orders)', 'desc' => 'Jadwalkan batch produksi dan alokasikan bahan mentah dari gudang ke area pabrik.'],
        ['icon' => 'coins', 'title' => 'Perhitungan Harga Pokok Produksi (HPP)', 'desc' => 'Gabungkan biaya bahan baku (Direct Material), upah buruh (Direct Labor), dan overhead pabrik.'],
        ['icon' => 'shield-alert', 'title' => 'Kontrol Kualitas & Produk Cacat (Scrap)', 'desc' => 'Catat barang reject atau sisa bahan produksi untuk evaluasi efisiensi mesin dan operator.'],
        ['icon' => 'history', 'title' => 'Nomor Seri & Batch Tracking', 'desc' => 'Lacak riwayat nomor batch produksi untuk mempermudah audit mutu dan regulasi BPOM/SNI.'],
        ['icon' => 'package-plus', 'title' => 'Pencatatan Masuk Produk Jadi (FG)', 'desc' => 'Setelah inspeksi lolos, produk jadi otomatis masuk ke stok gudang penjualan.'],
    ]
])
