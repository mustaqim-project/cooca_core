<?php

declare(strict_types=1);

return [
    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'alpha' => 'The :attribute may only contain letters.',
    'alpha_num' => 'The :attribute may only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'between' => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'string' => 'The :attribute must be between :min and :max characters.',
        'array' => 'The :attribute must have between :min and :max items.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'date' => 'The :attribute is not a valid date.',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => 'The selected :attribute is invalid.',
    'in' => 'The selected :attribute is invalid.',
    'integer' => 'The :attribute must be an integer.',
    'max' => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
        'string' => 'The :attribute may not be greater than :max characters.',
        'array' => 'The :attribute may not have more than :max items.',
    ],
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'numeric' => 'The :attribute must be a number.',
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute must be a string.',
    'unique' => 'The :attribute has already been taken.',
    'uuid' => 'The :attribute must be a valid UUID.',

    'custom' => [
        'payment_method' => [
            'required' => 'Please select a payment method.',
            'in' => 'The selected payment method is not supported.',
        ],
        'cycle' => [
            'in' => 'The billing cycle must be monthly or annual.',
        ],
        'tier' => [
            'in' => 'The selected subscription tier is invalid.',
        ],
    ],

    'attributes' => [
        'payment_method' => 'Payment Method',
        'cycle' => 'Billing Cycle',
        'tier' => 'Plan Tier',
        'order_type' => 'Order Type',
        'package_id' => 'Subscription Package',
    ],
];
