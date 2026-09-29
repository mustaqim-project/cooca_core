<?php

declare(strict_types=1);

return [
    'supervisor_pin_not_configured' => 'Supervisor PIN has not been configured by the business owner. Please configure a 6-digit PIN in Business Settings first.',
    'supervisor_pin_invalid' => 'Incorrect Supervisor PIN or invalid authorization.',
    'supervisor_pin_required' => 'Supervisor PIN is required.',
    'supervisor_pin_invalid_attempts' => 'Incorrect Supervisor PIN. :remaining attempt(s) remaining before security lockout.',
    'supervisor_auth_verified' => 'Supervisor Authorization Verified.',
    'order_voided_successfully' => 'Transaction #:order_number has been voided successfully.',
    'order_refunded_successfully' => 'Transaction #:order_number has been refunded / returned.',
    'partial_refund_successful' => 'Partial refund for transaction #:order_number completed.',
    'void_reason_mandatory' => 'Transaction void reason is required.',
    'refund_reason_mandatory' => 'Transaction refund reason is required.',
    'order_already_paid' => 'This order is already fully paid.',
    'missing_tripay_reference' => 'This order does not have a TriPay payment reference.',
    'tripay_sync_success' => 'TriPay payment status has been synchronized (:status).',
    'shift_opened' => 'Cashier shift session opened with opening cash of :amount.',
    'shift_closed' => 'Cashier shift session closed. Shift cash summary report is ready for printing.',
    'shift_already_open' => 'You already have an active shift session on this register.',
    'shift_not_open' => 'You have not opened a cashier shift session. Please open a shift to process orders.',
    'table_created' => 'Dining table / area has been created successfully.',
    'table_updated' => 'Dining table / area details have been updated.',
    'table_deleted' => 'Dining table has been deleted.',
    'table_status_changed' => 'Table availability status updated.',
    'kitchen_item_status_updated' => 'Kitchen ticket status for item :item updated to :status.',
    'kitchen_order_completed' => 'All kitchen items for table/ticket #:number are completed.',
];
