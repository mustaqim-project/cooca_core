<?php

declare(strict_types=1);

return [
    'title' => 'Inventory & Stock',
    'header_title' => 'Inventory & Stock Management',
    'header_subtitle' => 'Manage physical stock balances, raw materials, stock opname adjustments, and stock card movement history.',

    'breadcrumbs' => [
        'dashboard' => 'Dashboard',
        'inventory' => 'Inventory',
        'stocks' => 'Warehouse Stocks',
        'movements' => 'Stock Movements',
        'adjustments' => 'Stock Adjustments',
        'transfers' => 'Inter-Warehouse Transfers',
        'opname' => 'Stock Opname',
    ],

    'kpis' => [
        'total_sku' => 'Total SKUs / Commodities',
        'total_valuation' => 'Total Inventory Asset Value',
        'low_stock_items' => 'Items Needing Restock',
        'pending_approvals' => 'Pending Authorizations',
    ],

    'tabs' => [
        'stocks' => 'Actual Stock Balances',
        'receipts' => 'Goods Receipts (GRN)',
        'movements' => 'Stock Movement History',
        'adjustments' => 'Adjustments & Opname',
        'transfers' => 'Inter-Warehouse Transfers',
    ],

    'movement_types' => [
        'po_receipt' => 'PO Receiving (GRN)',
        'pos_sale' => 'POS Cashier Sale',
        'sales_order' => 'B2B Order Dispatch',
        'adjustment_in' => 'Positive Stock Adjustment',
        'adjustment_out' => 'Negative Stock Adjustment',
        'transfer_in' => 'Transfer Inward',
        'transfer_out' => 'Transfer Outward',
        'opname_variance' => 'Stock Opname Discrepancy',
        'bom_production_in' => 'Finished Goods Output (BOM)',
        'bom_production_out' => 'Raw Material Consumption (BOM)',
        'return_vendor' => 'Purchase Return to Vendor',
        'return_customer' => 'Customer Sales Return',
    ],

    'reasons' => [
        'variance' => 'Routine Physical Opname Discrepancy',
        'damaged' => 'Damaged / Defective Stock',
        'expired' => 'Expired Goods',
        'shrinkage' => 'Shrinkage / Waste',
        'sample' => 'Sample / Marketing Usage',
        'opening' => 'Opening Balance',
        'production_loss' => 'Production Loss / Scrap',
        'other' => 'Other Reason (Notes Required)',
    ],

    'supervisor_pin_not_configured' => 'Supervisor PIN has not been configured by the business owner. Please configure a PIN in Business Settings first.',
    'supervisor_pin_shrinkage_required' => 'Stock shrinkage reduction exceeds tolerance threshold (quantity > 10 units or value > IDR 100,000). A valid 6-digit Supervisor PIN is required.',
    'quick_adjustment_success' => 'Quick stock adjustment saved successfully.',
    'quick_adjustment_pending' => 'High-value stock adjustment (IDR :amount) has been submitted and is pending Owner / Supervisor approval.',
    'adjustment_pending_approval' => 'High-value stock adjustment (IDR :amount) has been submitted and is pending Owner / Supervisor approval.',
    'adjustment_success' => 'Stock adjusted successfully and stock card updated.',
    'adjustment_approved' => 'Stock adjustment request approved successfully and stock card updated.',
    'adjustment_rejected' => 'Stock adjustment request has been rejected.',
    'adjustment_not_found_or_processed' => 'Stock adjustment request not found or has already been processed.',
    'unauthorized_approval' => 'Only Business Owners or Supervisors are authorized to approve high-value stock adjustments.',
    'notes_other_min_length' => 'For "Other" reason, explanation notes must be at least 10 characters long.',
    'stock_transfer_success' => 'Stock transfer processed successfully.',
];
