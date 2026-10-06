<?php

declare(strict_types=1);

return [
    'created' => ':entity has been successfully added to the system.',
    'updated' => ':entity has been successfully updated.',
    'deleted' => ':entity has been successfully deleted. Past transaction records remain safely archived.',
    'restored' => ':entity has been successfully restored.',

    'billing' => [
        'upgrade_success' => 'Congratulations! Your business is now active on :cycle plan. All transactions, products, and AI features are fully unlocked.',
        'storage_recalculated_success' => "Storage recalculation for ':business' completed. Files scanned: :scanned | Untracked added: :added | Orphans cleaned: :cleaned | Used by this business: :used MB / :limit GB.",
        'storage_file_deleted_named' => "File ':name' (:size MB) successfully deleted. Your cloud storage capacity has been updated.",
        'free_package_activated_success' => "Congratulations! Special promo plan ':name' (:days Days Pro Trial) has been activated instantly with no payment needed.",
        'order_created_success' => 'Order #:order created successfully. Please complete your payment.',
        'upload_proof_unnecessary' => 'Subscription payments are verified automatically in real-time by TriPay Payment Gateway. You do not need to upload payment proof.',
        'order_cancelled_success' => 'Subscription order was cancelled successfully.',
    ],

    'pos' => [
        'order_placed' => 'Order receipt #:number has been successfully saved and printed.',
        'shift_closed' => 'Cashier shift has been closed. Cash summary report generated.',
        'drawer_opened' => 'Cash drawer manually opened (Recorded in Audit Log).',
        'table_status_reset' => 'Table #:number session completed and table is ready for next guests.',
    ],

    'inventory' => [
        'stock_adjusted' => 'Stock adjustment for :count items has been successfully posted.',
        'transfer_sent' => 'Inter-warehouse transfer note #:number has been issued.',
        'transfer_received' => 'Goods receipt from warehouse :origin has been verified.',
    ],
    'error' => 'A system error occurred. Please try again in a moment.',
];
