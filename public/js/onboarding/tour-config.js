/**
 * Universal HPP Calculator — Guided Product Tour Configuration
 * Defines step metadata, target elements, placements, routes, and rich descriptions.
 */
window.TOUR_STEPS = [
    {
        id: 'welcome',
        target: null, // Modal center
        title: 'Selamat Datang di HPP Calculator!',
        badge: 'Pengenalan Sistem',
        icon: 'sparkles',
        description: 'Platform enterprise kalkulator HPP & penetapan harga cerdas multi-industri. Mari ikuti panduan interaktif untuk memilih industri bisnis Anda dan mencoba menghitung HPP secara presisi.',
        placement: 'center',
        route: '/dashboard'
    },
    {
        id: 'select-industry',
        target: null,
        type: 'industry_picker', // Interactive Industry Picker Modal
        title: 'Pilih Industri Bisnis Anda',
        badge: 'Setup Industri',
        icon: 'layers',
        description: 'Pilih template industri yang paling sesuai dengan bisnis Anda agar sistem otomatis menyiapkan komponen biaya, satuan ukur, dan kategori standar:',
        placement: 'center',
        route: '/dashboard'
    },
    {
        id: 'dashboard-kpis',
        target: '#tour-dashboard-kpis',
        title: 'Ringkasan Eksekutif & KPI Bisnis',
        badge: 'Executive Dashboard',
        icon: 'layout-dashboard',
        description: 'Pantau total katalog produk jadi, inventori bahan baku, model biaya aktif, dan alokasi total overhead bulanan bisnis Anda secara terpusat dalam satu tampilan ringkas.',
        placement: 'bottom',
        route: '/dashboard'
    },
    {
        id: 'quick-calc',
        target: '#tour-quick-calc',
        title: 'Coba Hitung HPP & Margin Instan',
        badge: 'Simulasi Langsung',
        icon: 'calculator',
        description: 'Kalkulator Cepat: Geser slider target margin atau masukkan estimasi HPP di bawah untuk melihat rekomendasi harga jual, laba kotor per unit, dan persentase markup secara instan.',
        placement: 'top',
        route: '/dashboard'
    },
    {
        id: 'menu-calculator',
        target: '#tour-nav-calculator',
        title: 'Engine Kalkulator HPP Live',
        badge: 'Core Calculation',
        icon: 'sparkles',
        description: 'Jantung utama perhitungan HPP presisi. Menghitung HPP aktual, biaya bahan baku, tenaga kerja langsung, mesin, serta alokasi overhead pabrik dengan 9 pilihan metode kalkulasi.',
        placement: 'right',
        route: '/dashboard'
    },
    {
        id: 'menu-materials',
        target: '#tour-nav-materials',
        title: 'Katalog Bahan Baku & Pemasok',
        badge: 'Cost Component',
        icon: 'boxes',
        description: 'Kelola harga akuisisi bahan, rendemen (yield %), susut (waste %), riwayat fluktuasi harga supplier, serta data vendor pemasok bahan mentah Anda.',
        placement: 'right',
        route: '/dashboard'
    },
    {
        id: 'menu-products',
        target: '#tour-nav-products',
        title: 'Produk Jadi & Resep / BOM',
        badge: 'Master Resep',
        icon: 'package',
        description: 'Strukturkan Bill of Materials (BOM) multi-level dan formula resep produk. Setiap produk dapat memiliki beberapa versi kalkulasi HPP dengan status draft maupun disetujui (approved).',
        placement: 'right',
        route: '/dashboard'
    },
    {
        id: 'menu-labor-machines',
        target: '#tour-nav-labor-machines',
        title: 'Tenaga Kerja & Depresiasi Mesin',
        badge: 'Direct Costs',
        icon: 'users-2',
        description: 'Hitung tarif upah langsung per jam/menit (labor rate) dan biaya operasional mesin (depresiasi aset per jam, konsumsi listrik kW, serta biaya pemeliharaan berkala).',
        placement: 'right',
        route: '/dashboard'
    },
    {
        id: 'menu-simulator',
        target: '#tour-nav-simulator',
        title: 'What-If Scenario Simulator',
        badge: 'Strategic Simulation',
        icon: 'sliders',
        description: 'Simulasikan dampak kenaikan harga bahan baku, kenaikan UMR gaji karyawan, atau lonjakan tarif listrik terhadap HPP dan laba kotor sebelum mengambil keputusan bisnis nyata.',
        placement: 'right',
        route: '/dashboard'
    },
    {
        id: 'menu-profitability',
        target: '#tour-nav-profitability',
        title: 'Analisis BEP & Margin Keamanan',
        badge: 'Profitability',
        icon: 'trending-up',
        description: 'Ketahui titik impas (Break-Even Point) dalam satuan unit penjualan maupun nominal rupiah, rasio Margin of Safety, serta target volume penjualan untuk mencapai laba tertentu.',
        placement: 'right',
        route: '/dashboard'
    },
    {
        id: 'menu-settings',
        target: '#tour-nav-settings',
        title: '20 Preset Template Industri & Master CMS',
        badge: 'Configuration',
        icon: 'settings',
        description: 'Terapkan struktur biaya default dari 20 template industri siap pakai serta kelola CMS Supplier, Satuan Ukur, dan Kategori secara mandiri.',
        placement: 'right',
        route: '/dashboard'
    },
    {
        id: 'finish',
        target: null,
        title: 'Luar Biasa! Siap Menghitung HPP',
        badge: 'Setup Selesai',
        icon: 'check-circle-2',
        description: 'Industri telah disiapkan dan Anda telah mengenal modul kalkulator HPP. Klik tombol di bawah untuk langsung menuju Kalkulator HPP!',
        placement: 'center',
        route: '/dashboard',
        actionUrl: '/calculator'
    }
];
