<?php

declare(strict_types=1);

return [
    'billing' => [
        'plan_upgraded' => 'Business subscription upgraded to :plan (:cycle).',
        'payment_approved' => 'Invoice payment order #:order verified and paid.',
        'payment_rejected' => 'Invoice payment order #:order rejected or expired.',
        'storage_recalculated' => 'Storage disk capacity recalculation executed.',
        'storage_file_deleted' => 'Storage file :file (:size MB) deleted from server.',
        'free_promo_activated' => 'Free promo trial package :package activated.',
    ],
    'pos' => [
        'drawer_opened_no_sale' => 'Cash drawer opened manually without sales transaction.',
        'order_voided' => 'Cashier receipt #:number voided by supervisor.',
        'shift_opened' => 'Cashier shift opened with starting cash :amount.',
        'shift_closed' => 'Cashier shift closed with total sales :amount.',
    ],
    'products' => [
        'price_changed' => 'Product price for :product changed from :old to :new.',
    ],
    'users' => [
        'role_modified' => 'User role permissions for :user changed to :role.',
    ],
];
