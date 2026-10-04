<?php

declare(strict_types=1);

return [
    'billing' => [
        'payment_approved_wa' => "Hello *{{owner}}*,\nYour Cooca subscription payment for *{{plan}}* of *{{amount}}* has been verified automatically!\n\nValid until: {{expires_at}}\nOfficial Invoice: {{invoice_url}}\n\nThank you for your business!",
        'payment_expired_wa' => "Dear *{{owner}}*,\nSubscription order #{{order_number}} for {{amount}} has expired. Please place a new order on Cooca if you still need quota upgrades.",
        'grace_period_wa' => "⚠️ *Grace Period Notice*\nBusiness: {{business}}\nYour subscription has passed its due date. POS cashier will remain active for the next 3 days. Please settle your bill to prevent feature limitations.",
    ],
    'whatsapp' => [
        'pos_receipt' => "Hello *{{name}}*,\nThank you for shopping at *{{business}}*!\n\nReceipt: #{{order_number}}\nTotal: {{total}}\nPayment: {{payment_method}}\n\nDownload e-receipt: {{link}}\nHave a great day!",
        'debt_reminder' => "Dear *{{customer}}*,\nThis is a friendly reminder that invoice *#{{invoice_number}}* for *{{amount}}* is due on *{{due_date}}*.\n\nPayment details: {{link}}\nThank you for your business.",
        'low_stock_owner' => "⚠️ *Low Stock Warning*\nOutlet: {{outlet}}\nItem: {{item}}\nRemaining stock: *{{remaining}} {{unit}}* (Minimum threshold: {{threshold}} {{unit}}).\nPlease issue a purchase order soon!",
    ],
    'email' => [
        'daily_digest_subject' => 'Daily Business Summary - :date (:business)',
        'invoice_subject' => 'Invoice #:number from :business',
    ],
];
