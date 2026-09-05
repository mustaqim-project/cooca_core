/**
 * Cooca UMKM - Complete sidebar and workflow tour configuration.
 */
window.TOUR_VERSION = 3;

const tourStep = (id, target, title, description, icon = 'circle-help', badge = 'Panduan Menu') => ({
    id,
    target,
    title,
    badge,
    icon,
    description,
    placement: target ? 'right' : 'center',
    route: '/dashboard'
});

window.TOUR_STEPS = [
    tourStep('welcome', null, 'Selamat Datang di Cooca UMKM', 'Panduan ini mengenalkan seluruh menu bisnis Anda dari data dasar sampai laporan.', 'sparkles', 'Mulai'),
    {
        id: 'select-industry',
        target: null,
        type: 'industry_picker',
        title: 'Pilih template bisnis Anda',
        badge: 'Setup Data',
        icon: 'layers',
        description: 'Pilih template industri agar Cooca UMKM membantu menyiapkan kategori, satuan, dan struktur biaya awal bisnis Anda.',
        placement: 'center',
        route: '/dashboard'
    },
    tourStep('active-business', '#tour-active-business', 'Bisnis Aktif', 'Pastikan bisnis yang sedang digunakan sudah benar sebelum menginput data.'),
    tourStep('switch-business', '#tour-switch-business', 'Ganti Bisnis', 'Berpindah ke bisnis lain yang Anda kelola.'),
    tourStep('dashboard', '#tour-nav-dashboard', 'Dashboard', 'Ringkasan omzet, transaksi, margin, persediaan, dan indikator operasional bisnis.'),
    tourStep('ai', '#tour-nav-ai', 'AI Assistant', 'Asisten analisis bisnis berbasis AI. Saat ini masih ditandai Segera.', 'bot'),
    tourStep('community', '#tour-nav-community', 'Komunitas Owner', 'Forum owner untuk berbagi pengalaman dan strategi bisnis. Saat ini masih ditandai Segera.', 'users'),

    tourStep('group-sales', '#tour-group-sales', 'Kasir & Penjualan', 'Group transaksi, pelanggan, pesanan, dan retur penjualan.', 'shopping-cart'),
    tourStep('pos-terminal', '#tour-nav-pos-terminal', 'Terminal Kasir POS', 'Buat transaksi penjualan dan cetak struk.', 'calculator'),
    tourStep('invoices', '#tour-nav-invoices', 'Faktur & Piutang', 'Buat dan pantau faktur penjualan serta pembayaran pelanggan.', 'receipt'),
    tourStep('pos-orders', '#tour-nav-pos-orders', 'Riwayat Transaksi & Shift', 'Periksa riwayat transaksi POS, shift kasir, dan rekonsiliasi kas.', 'shopping-bag'),
    tourStep('customers', '#tour-nav-customers', 'Pelanggan & CRM', 'Simpan pelanggan, kontak, dan riwayat hubungan pelanggan.', 'users'),
    tourStep('sales-orders', '#tour-nav-sales-orders', 'Pesanan & Penawaran', 'Kelola quotation dan sales order sebelum menjadi transaksi.', 'check-square'),
    tourStep('sales-returns', '#tour-nav-sales-returns', 'Retur Penjualan', 'Catat pengembalian barang atau transaksi dari pelanggan.', 'undo-2'),

    tourStep('group-master-data', '#tour-group-master-data', 'Master Data', 'Data dasar yang dipakai pembelian, inventori, produk, dan perhitungan HPP.', 'database'),
    tourStep('suppliers', '#tour-nav-suppliers', 'Supplier', 'Kelola pemasok, kontak, dan sumber pembelian bahan atau barang.', 'truck'),
    tourStep('material-categories', '#tour-nav-material-categories', 'Kategori Bahan', 'Kelompokkan bahan baku agar katalog dan laporan mudah dicari.', 'layers'),
    tourStep('product-categories', '#tour-nav-product-categories', 'Kategori Produk', 'Kelompokkan produk jadi, menu, atau barang yang dijual.', 'folder'),
    tourStep('units', '#tour-nav-units', 'Satuan & Konversi', 'Buat satuan dasar dan konversi agar pemakaian bahan dan stok konsisten.', 'scale'),

    tourStep('group-purchasing', '#tour-group-purchasing', 'Pembelian & Vendor', 'Group purchase order, tagihan supplier, dan retur pembelian.', 'truck'),
    tourStep('purchase-orders', '#tour-nav-purchase-orders', 'Purchase Order (PO)', 'Buat pesanan pembelian kepada supplier dan pantau penerimaannya.', 'file-text'),
    tourStep('purchasing-bills', '#tour-nav-purchasing-bills', 'Tagihan & Hutang Supplier', 'Catat dan pantau tagihan pembelian serta hutang supplier.', 'receipt'),
    tourStep('purchase-returns', '#tour-nav-purchase-returns', 'Retur Pembelian', 'Catat barang yang dikembalikan kepada supplier.', 'corner-up-left'),

    tourStep('group-inventory', '#tour-group-inventory', 'Produk & Inventori', 'Group gudang, katalog produk, bahan, stok, mutasi, transfer, dan opname.', 'package'),
    tourStep('warehouse', '#tour-nav-warehouse', 'Gudang & Lokasi', 'Atur lokasi gudang untuk penerimaan dan perpindahan stok.', 'warehouse'),
    tourStep('products', '#tour-nav-products', 'Katalog Produk & Resep', 'Buat produk, SKU, satuan output, harga jual, dan resep BOM.', 'package'),
    tourStep('materials', '#tour-nav-materials', 'Bahan Baku & Harga', 'Input nama bahan, satuan, harga beli, kategori, dan supplier.', 'boxes'),
    tourStep('inventory-stocks', '#tour-nav-inventory-stocks', 'Stok Real-Time', 'Pantau jumlah stok bahan dan produk yang tersedia.', 'layers'),
    tourStep('inventory-movements', '#tour-nav-inventory-movements', 'Mutasi Stok (Kartu Stok)', 'Telusuri setiap stok masuk, keluar, penyesuaian, dan sumbernya.', 'arrow-left-right'),
    tourStep('inventory-transfers', '#tour-nav-inventory-transfers', 'Transfer Stok Gudang', 'Pindahkan stok antar lokasi gudang dan simpan riwayatnya.', 'repeat'),
    tourStep('inventory-opnames', '#tour-nav-inventory-opnames', 'Stock Opname Fisik', 'Cocokkan stok sistem dengan hasil hitung fisik di gudang.', 'clipboard-check'),

    tourStep('group-costing', '#tour-group-costing', 'HPP & Produksi', 'Group HPP, biaya tenaga kerja, profitabilitas, dan simulasi.', 'calculator'),
    tourStep('calculator', '#tour-nav-calculator', 'Kalkulator HPP 3-Pilar', 'Pilih mode cepat atau detail BOM. Isi bahan, tenaga kerja, overhead, dan margin, lalu cek HPP per unit.', 'sparkles'),
    tourStep('labor-machines', '#tour-nav-labor-machines', 'Upah Kerja & Mesin', 'Masukkan tarif tenaga kerja dan biaya mesin untuk melengkapi HPP.', 'users-2'),
    tourStep('profitability', '#tour-nav-profitability', 'BEP & Profitabilitas', 'Analisis titik impas, margin keamanan, dan target penjualan.', 'target'),
    tourStep('simulator', '#tour-nav-simulator', 'Simulasi What-If', 'Uji dampak perubahan harga bahan, upah, listrik, dan margin.', 'sliders'),

    tourStep('group-finance', '#tour-group-finance', 'Keuangan & Kas', 'Group kas, ledger, beban, piutang, hutang, dan jurnal.', 'landmark'),
    tourStep('cash-bank', '#tour-nav-cash-bank', 'Kas & Rekening Bank', 'Kelola saldo kas dan rekening bank bisnis.', 'landmark'),
    tourStep('cash-ledger', '#tour-nav-cash-ledger', 'Buku Kas & Ledger', 'Telusuri arus masuk dan keluar pada buku kas.', 'book'),
    tourStep('expenses', '#tour-nav-expenses', 'Beban Operasional', 'Catat biaya operasional agar laba dan arus kas akurat.', 'wallet'),
    tourStep('receivables', '#tour-nav-receivables', 'Piutang Usaha (AR Aging)', 'Pantau piutang pelanggan berdasarkan umur dan status pembayaran.', 'arrow-down-left'),
    tourStep('payables', '#tour-nav-payables', 'Hutang Usaha (AP Aging)', 'Pantau hutang supplier dan jatuh tempo pembayaran.', 'arrow-up-right'),
    tourStep('journals', '#tour-nav-journals', 'Jurnal Akuntansi Otomatis', 'Periksa jurnal yang dibuat otomatis dari transaksi.', 'book-open'),

    tourStep('group-reports', '#tour-group-reports', 'Laporan & Analitik', 'Group laporan bisnis dan laporan kasir POS.', 'bar-chart-3'),
    tourStep('reports', '#tour-nav-reports', 'Laporan & Analitik Bisnis', 'Baca ringkasan penjualan, biaya, laba, margin, dan performa bisnis.', 'bar-chart-3'),
    tourStep('pos-reports', '#tour-nav-pos-reports', 'Laporan Kasir POS', 'Periksa transaksi, shift, kasir, dan performa penjualan POS.', 'file-pie-chart'),

    tourStep('settings', '#tour-nav-settings', 'Pengaturan Toko & Tim', 'Atur identitas bisnis, rekening, template, anggota tim, dan konfigurasi toko.', 'settings'),
    tourStep('roles', '#tour-nav-roles', 'Kontrol Akses & Role', 'Atur role dan hak akses anggota tim.', 'shield-check'),
    tourStep('billing', '#tour-nav-billing', 'Paket & Kuota', 'Pantau paket, batas penggunaan, dan status kuota bisnis.', 'sparkles'),
    tourStep('feedback', '#tour-nav-feedback', 'Dukungan Produk', 'Kirim laporan bug, masukan, atau permintaan fitur.', 'life-buoy'),

    {
        id: 'finish',
        target: null,
        title: 'Tour Cooca UMKM selesai',
        badge: 'Selesai',
        icon: 'check-circle-2',
        description: 'Anda sudah melihat seluruh menu utama Cooca UMKM. Mulai dari Master Data, input bahan, buat produk dan BOM, hitung HPP, lalu jalankan penjualan.',
        placement: 'center',
        route: '/dashboard',
        actionUrl: '/dashboard'
    }
];
