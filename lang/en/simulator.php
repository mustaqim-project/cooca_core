<?php

declare(strict_types=1);

return [
    'title' => 'What-If & Sensitivity Simulator',
    'header_title' => 'What-If & Sensitivity Simulator',
    'header_subtitle' => 'Simulate the impact of raw material price hikes, minimum wage increases, and overhead on COGS and profit without altering master data',

    'nav' => [
        'calculator' => 'Live COGS Calculator',
        'simulator' => 'What-If Simulator',
        'sandbox_badge' => 'Sandbox',
        'products' => 'Products & BOM Catalog',
        'materials' => 'Raw Materials Catalog',
    ],

    'breadcrumb' => [
        'dashboard' => 'Dashboard',
        'costing' => 'Costing & Calculators',
        'simulator' => 'What-If Simulator',
    ],

    'selector' => [
        'label' => 'Product Cost Model:',
        'choose' => '-- Select Product Cost Model --',
        'baseline_hpp' => 'Baseline COGS:',
        'selling_price' => 'Selling Price:',
        'product_code' => 'SKU: :code',
    ],

    'sliders' => [
        'title' => 'Sensitivity Simulation Parameters',
        'material' => 'Raw Material Cost Change',
        'labor' => 'Labor Rate Change',
        'machine' => 'Machine & Utility Cost Change',
        'overhead' => 'Factory Overhead Change',
        'markup' => 'Target Profit Markup',
        'current_val' => 'Change:',
        'target_markup_val' => 'Target Markup:',
        'calculating' => 'Calculating...',
        'scale_fixed' => '0% (Fixed)',
        'markup_thin' => '10% (Slim)',
        'markup_standard' => '40% (Standard)',
        'markup_premium' => '150% (Premium)',
    ],

    'presets' => [
        'title' => 'Quick Scenarios (Presets):',
        'badge' => 'Instant Market Shocks',
        'subtitle' => 'Click a button to test immediate COGS response',
        'inflation' => 'Raw Material Inflation (+15%)',
        'wage_hike' => 'Minimum Wage Hike (+10%)',
        'energy_crisis' => 'Power Tariff Hike (+20%)',
        'overhead_hike' => 'Overhead Hike (+15%)',
        'supply_crisis' => 'Supply Crisis (+20% Mat, +10% Wage, +15% Mach)',
        'all_ten' => 'Uniform Increase +10%',
        'reset' => 'Reset Baseline (0%)',
    ],

    'kpis' => [
        'baseline_hpp' => 'Initial Total COGS (Baseline)',
        'simulated_hpp' => 'Simulated COGS',
        'hpp_delta' => 'COGS Nominal Increase',
        'recommended_price' => 'Recommended New Selling Price',
        'recommended_price_short' => 'Recommended Price',
        'hpp_shift' => 'COGS Shift',
        'current_margin' => 'Estimated Profit Margin',
        'simulated_margin' => 'New Profit Margin',
        'safe_margin' => 'Healthy Margin',
        'compressed_margin' => 'Compressed Margin',
    ],

    'table' => [
        'title' => 'COGS Component Breakdown Comparison',
        'subtitle' => 'Nominal cost comparison across each cost element',
        'col_component' => 'Cost Component',
        'col_baseline' => 'Baseline Cost',
        'col_simulated' => 'Simulated Cost',
        'col_diff' => 'Nominal Difference',
        'col_change_pct' => 'Change (%)',
        'comp_materials' => 'Raw Materials & BOM Recipe',
        'comp_labor' => 'Direct Labor',
        'comp_machine' => 'Machine & Utility Expense',
        'comp_overhead' => 'Factory Overhead Expense',
        'comp_total' => 'TOTAL COGS PER UNIT',
    ],

    'chart' => [
        'title' => 'Cost Component Composition: Baseline vs Simulation',
        'subtitle' => 'Visual comparison across each cost element',
        'baseline_label' => 'Initial Baseline',
        'simulated_label' => 'Simulated Result',
        'labels' => [
            'materials' => 'Raw Materials',
            'labor' => 'Direct Labor',
            'machine' => 'Machine & Utilities',
            'overhead' => 'Overhead',
            'total_hpp' => 'Total COGS',
        ],
    ],

    'insights' => [
        'title' => 'Strategic Insights & Recommendations',
        'recommendation_prefix' => 'If this scenario occurs in real terms, you are advised to raise the selling price to at least',
        'increase' => 'increase of',
        'recommendation_suffix' => 'in order to maintain your target markup of',
        'price_adjust' => 'To preserve your target gross profit margin of :markup%, the recommended new selling price is at least :price.',
        'margin_warning' => 'If selling prices remain unchanged, your gross profit margin will compress by :pts percentage points.',
        'material_driver' => 'Raw materials represent the highest cost driver (:pct% of COGS). Supplier renegotiation or recipe yield optimization is top priority.',
    ],

    'empty' => [
        'title' => 'No Active Cost Model Selected',
        'subtitle' => 'To run What-If simulations, create at least one product with a bill of materials (BOM) or cost model in the COGS calculator.',
        'button' => 'Open Products Catalog',
    ],
];
