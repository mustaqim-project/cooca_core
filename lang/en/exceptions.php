<?php

declare(strict_types=1);

return [
    'billing' => [
        'upgrade_simulation_local_only' => 'Simulating plan upgrade is only permitted in local development or test environments.',
        'only_owner_recalculate_storage' => 'Only the Owner or billing manager can recalculate storage.',
        'owner_not_found' => 'Account owner not found. Unable to recalculate storage.',
        'only_owner_delete_storage' => 'Only the Owner or billing manager can delete storage files.',
        'storage_file_not_owned' => 'Access denied. This file does not belong to the active business.',
        'only_owner_order_subscription' => 'Only the Owner or billing manager can place subscription orders.',
        'order_not_found' => 'Billing order was not found.',
        'order_cannot_be_cancelled' => 'Billing order with current status cannot be cancelled.',
        'quota_exceeded' => ':resource limit for your subscription plan (:plan) has been reached (:max :unit). Please upgrade your plan to continue.',
        'unauthorized_tenant_access' => 'You do not have permission to access or modify subscription data of other tenants (IDOR Shield).',
    ],
    'stock' => [
        'insufficient' => 'Insufficient stock for ":item" at :warehouse. Available: :available, requested: :requested.',
        'locked_in_transit' => 'Stock for ":item" is currently locked in inter-warehouse transit (Transfer Note #:do_number).',
        'negative_not_allowed' => 'Stock adjustment cannot result in negative final quantity (:qty).',
    ],
    'pos' => [
        'shift_not_opened' => 'POS terminal cannot process transactions because cashier shift is not opened yet.',
        'already_closed' => 'This cashier shift was already closed at :time.',
        'pin_locked' => 'Supervisor authorization is locked due to 5 failed attempts. Please wait :minutes minutes.',
        'invalid_pin' => 'Incorrect Supervisor PIN. Remaining attempts: :remaining_attempts.',
        'cannot_void_settled' => 'Transaction receipt #:number cannot be voided because cash settlement is already finalized.',
    ],
    'tenant' => [
        'unauthorized_access' => 'You do not have permission to access or modify other tenant data (IDOR Shield).',
        'quota_exceeded' => 'Product limit for your subscription plan (:plan) has been reached (:max items). Please upgrade to add more products.',
    ],
    'finance' => [
        'unbalanced_journal' => 'Accounting journal entry is unbalanced. Total Debit (IDR :debit) must equal Total Credit (IDR :credit).',
        'account_locked' => 'Cash/bank account ":account" is locked for financial audit period.',
    ],
];
