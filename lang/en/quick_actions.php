<?php

declare(strict_types=1);

return [
    // Modal 1: Quick Expense
    'expense' => [
        'title' => 'Record Quick Expense',
        'subtitle' => 'Automated journal for business operations',
        'name_label' => 'Expense Description / Item *',
        'name_placeholder' => 'e.g. LPG Gas 3kg, Plastic Packaging',
        'amount_label' => 'Amount (Rp) *',
        'amount_placeholder' => '25,000',
        'payment_method_label' => 'Payment Method',
        'method_cash' => 'Cash Drawer',
        'method_bank' => 'Bank Transfer',
        'method_qris' => 'QRIS / e-Wallet',
        'category_label' => 'Expense Category',
        'cat_operational' => 'Store Operations',
        'cat_consumables' => 'Consumable Supplies (Packaging)',
        'cat_utilities' => 'Electricity, Water & Gas',
        'cat_logistics' => 'Transportation & Logistics',
        'cat_other' => 'Other Expenses',
        'supervisor_pin_label' => 'Supervisor PIN (Expense Authorization) *',
        'supervisor_pin_hint' => '≥ Rp 500,000',
        'supervisor_pin_placeholder' => 'Enter 6-digit PIN',
        'submit_btn' => 'Save Expense',
        'success_msg' => 'Cash expense recorded and journalized successfully.',
        'error_msg' => 'Failed to record cash expense.',
        'pin_required_msg' => 'Expenses ≥ Rp 500,000 require Supervisor PIN authorization.',
        'pin_invalid_msg' => 'Invalid Supervisor PIN or insufficient authorization privilege.',
    ],

    // Modal 2: Quick Stock In
    'stock_in' => [
        'title' => 'Quick Stock-In Purchase',
        'subtitle' => 'Increase inventory & asset valuation',
        'material_label' => 'Raw Material / Product *',
        'select_material' => '-- Select Raw Material --',
        'loading_materials' => 'Loading materials list...',
        'qty_label' => 'Quantity In *',
        'qty_placeholder' => '10',
        'unit_cost_label' => 'Purchase Price / Unit (Rp) *',
        'unit_cost_placeholder' => '15,000',
        'supplier_label' => 'Supplier / Store Name',
        'supplier_placeholder' => 'e.g. Central Market, Bakery Supplies Store',
        'submit_btn' => 'Record Stock In',
        'success_msg' => 'Stock-in recorded successfully and COGS updated.',
        'error_msg' => 'Failed to record stock-in.',
    ],

    // Modal 3: Quick Material Creation
    'material' => [
        'title' => 'Add Raw Material',
        'subtitle' => 'Register a new recipe material without leaving the page',
        'name_label' => 'Material Name *',
        'name_placeholder' => 'e.g. Wheat Flour Premium',
        'cost_label' => 'Base Purchase Price (Rp) *',
        'cost_placeholder' => '12,000',
        'unit_label' => 'Unit of Measure',
        'select_unit' => 'Select Unit',
        'submit_btn' => 'Add Material',
        'success_msg' => 'New raw material registered successfully.',
        'error_msg' => 'Failed to register raw material.',
    ],

    // Modal 4: Mobile Action Sheet
    'sheet' => [
        'title' => 'Quick Instant Actions',
        'expense_title' => 'Record Expense',
        'expense_desc' => 'Operational cost',
        'stock_title' => 'Buy Stock',
        'stock_desc' => 'Increase inventory',
        'material_title' => 'Raw Material',
        'material_desc' => 'Master BOM ingredient',
        'hpp_title' => 'COGS Calculator',
        'hpp_desc' => '3-Pillar selling price',
        'kds_title' => 'Kitchen (KDS)',
        'kds_desc' => 'Live kitchen display',
        'tables_title' => 'Tables & QR',
        'tables_desc' => 'Dine-in self order',
        'services_title' => 'Services & SPK',
        'services_desc' => 'Workshop work orders',
    ],
];
