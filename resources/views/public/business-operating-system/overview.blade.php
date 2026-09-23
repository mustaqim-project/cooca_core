@extends('public.partials.subpage_layout', [
    'title' => 'Overview',
    'category' => 'Business Operating System',
    'badge' => 'Central Business Core',
    'icon' => 'cpu',
    'headline' => 'Business Operating System: Pusat Kendali Terpadu Seluruh Operasional',
    'subtitle' => 'Bukan sekadar software kasir atau pencatatan stok biasa. COOCA adalah Business Operating System terintegrasi yang menyatukan operasional, keuangan, penjualan, inventory, omnichannel, hingga otomasi AI dalam satu dasbor.',
    'features' => [
        [
            'icon' => 'activity',
            'title' => 'Single Source of Truth',
            'desc' => 'Semua data penjualan, stok gudang, dan keuangan tercatat secara real-time dalam satu database tanpa duplikasi data.'
        ],
        [
            'icon' => 'zap',
            'title' => 'Otomasi Workflow End-to-End',
            'desc' => 'Dari pesanan masuk via marketplace atau kasir hingga jurnal akuntansi dan notifikasi WA pelanggan berjalan otomatis.'
        ],
        [
            'icon' => 'shield-check',
            'title' => 'Isolasi Tenant & Keamanan Data',
            'desc' => 'Setiap bisnis memiliki proteksi data independen berstandar enterprise dengan audit trail dan enkripsi ketat.'
        ],
        [
            'icon' => 'cpu',
            'title' => 'AI Copilot & Smart Insight',
            'desc' => 'Asisten pintar yang memprediksi tren omzet, mendeteksi kebocoran modal HPP, dan merekomendasikan restock tepat waktu.'
        ],
        [
            'icon' => 'layers',
            'title' => 'Arsitektur Modular Fleksibel',
            'desc' => 'Pilih modul sesuai skala bisnis Anda: mulai dari warung, kafe, bengkel, toko retail, hingga manufaktur bertingkat.'
        ],
        [
            'icon' => 'smartphone',
            'title' => 'Multi-Device Responsive',
            'desc' => 'Akses mulus dari smartphone kasir, tablet counter, hingga komputer desktop back-office manajer.'
        ]
    ]
])
