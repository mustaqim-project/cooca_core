<?php

declare(strict_types=1);

return [
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
