<?php

declare(strict_types=1);

return [
    'title' => 'What-If & Sensitivity Simulator',
    'header_title' => 'What-If & Sensitivity Simulator',
    'header_subtitle' => 'Simulasikan dampak fluktuasi harga bahan, kenaikan UMR, dan overhead terhadap HPP dan laba tanpa merusak data master',

    'nav' => [
        'calculator' => 'Kalkulator HPP Live',
        'simulator' => 'What-If Simulator',
        'sandbox_badge' => 'Sandbox',
        'products' => 'Katalog Produk & BOM',
        'materials' => 'Katalog Bahan Baku',
    ],

    'breadcrumb' => [
        'dashboard' => 'Dashboard',
        'costing' => 'Biaya & Kalkulator',
        'simulator' => 'What-If Simulator',
    ],

    'selector' => [
        'label' => 'Model Biaya Produk:',
        'choose' => '-- Pilih Model Biaya Produk --',
        'baseline_hpp' => 'HPP Dasar:',
        'selling_price' => 'Harga Jual:',
        'product_code' => 'SKU: :code',
    ],

    'sliders' => [
        'title' => 'Parameter Simulasi Sensitivitas',
        'material' => 'Perubahan Biaya Bahan Baku',
        'labor' => 'Perubahan Upah Tenaga Kerja',
        'machine' => 'Perubahan Biaya Mesin & Listrik',
        'overhead' => 'Perubahan Biaya Overhead Pabrik',
        'markup' => 'Target Markup Margin Laba',
        'current_val' => 'Perubahan:',
        'target_markup_val' => 'Target Markup:',
        'calculating' => 'Menghitung...',
        'scale_fixed' => '0% (Tetap)',
        'markup_thin' => '10% (Tipis)',
        'markup_standard' => '40% (Standar)',
        'markup_premium' => '150% (Premium)',
    ],

    'presets' => [
        'title' => 'Skenario Cepat (Presets):',
        'badge' => 'Guncangan Pasar Instan',
        'subtitle' => 'Klik tombol untuk menguji respon HPP langsung',
        'inflation' => 'Inflasi Bahan Baku (+15%)',
        'wage_hike' => 'Kenaikan UMR (+10%)',
        'energy_crisis' => 'Lonjakan Listrik (+20%)',
        'overhead_hike' => 'Kenaikan Overhead (+15%)',
        'supply_crisis' => 'Krisis Pasokan (+20% Bahan, +10% UMR, +15% Mesin)',
        'all_ten' => 'Kenaikan Merata +10%',
        'reset' => 'Reset Baseline (0%)',
    ],

    'kpis' => [
        'baseline_hpp' => 'Total HPP Awal (Baseline)',
        'simulated_hpp' => 'HPP Hasil Simulasi',
        'hpp_delta' => 'Kenaikan HPP Nominal',
        'recommended_price' => 'Rekomendasi Harga Jual Baru',
        'recommended_price_short' => 'Rekomendasi Jual',
        'hpp_shift' => 'Pergeseran HPP',
        'current_margin' => 'Estimasi Margin Laba',
        'simulated_margin' => 'Margin Laba Baru',
        'safe_margin' => 'Margin Sehat',
        'compressed_margin' => 'Margin Tertekan',
    ],

    'table' => [
        'title' => 'Rincian Komparasi Komponen HPP',
        'subtitle' => 'Perbandingan nominal riil antar elemen biaya',
        'col_component' => 'Komponen Biaya',
        'col_baseline' => 'Biaya Dasar (Awal)',
        'col_simulated' => 'Biaya Hasil Simulasi',
        'col_diff' => 'Selisih Nominal',
        'col_change_pct' => 'Perubahan (%)',
        'comp_materials' => 'Bahan Baku & Resep BOM',
        'comp_labor' => 'Tenaga Kerja Langsung',
        'comp_machine' => 'Biaya Mesin & Listrik',
        'comp_overhead' => 'Beban Overhead Pabrik',
        'comp_total' => 'TOTAL HPP PER UNIT',
    ],

    'chart' => [
        'title' => 'Grafik Komposisi Biaya: Baseline vs Simulasi',
        'subtitle' => 'Visualisasi selisih tiap elemen biaya',
        'baseline_label' => 'Baseline Awal',
        'simulated_label' => 'Hasil Simulasi',
        'labels' => [
            'materials' => 'Bahan Baku',
            'labor' => 'Tenaga Kerja',
            'machine' => 'Mesin & Listrik',
            'overhead' => 'Overhead',
            'total_hpp' => 'Total HPP',
        ],
    ],

    'insights' => [
        'title' => 'Analisis Strategis & Rekomendasi AI',
        'recommendation_prefix' => 'Jika biaya skenario ini terjadi secara riil, Anda disarankan menaikkan harga jual produk minimal menjadi',
        'increase' => 'kenaikan',
        'recommendation_suffix' => 'guna mempertahankan target markup',
        'price_adjust' => 'Untuk mempertahankan margin laba target sebesar :markup%, harga jual baru disarankan minimal :price.',
        'margin_warning' => 'Jika harga jual tidak dinaikkan, margin laba kotor bisnis Anda akan tertekan turun sebesar :pts poin persentase.',
        'material_driver' => 'Bahan baku merupakan kontributor beban terbesar (:pct% dari HPP). Negosiasi harga pasokan atau substitusi vendor prioritas tertinggi.',
    ],

    'empty' => [
        'title' => 'Belum Ada Model Biaya Aktif',
        'subtitle' => 'Untuk menjalankan simulasi What-If, buat minimal satu produk dengan susunan resep (BOM) atau model biaya di kalkulator HPP.',
        'button' => 'Buka Katalog Produk',
    ],
];
