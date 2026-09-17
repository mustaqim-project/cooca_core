<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceStoreSetting extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'commerce_store_settings';

    protected $fillable = [
        'business_id',
        'is_storefront_enabled',
        'is_discoverable',
        'allow_pickup',
        'allow_delivery',
        'min_order_amount',
        'order_auto_cancel_minutes',
        'lead_time_hours',
        'operating_days',
        'available_slots',
        'cut_off_time',
        'max_capacity_per_slot',
        'daily_order_quota',
        'allow_request_order',
        'allow_scheduled_order',
        'allow_customer_po',
        'allow_reservation',
        'allow_custom_date',
        'quota_metric',
        'preorder_quota_unit',
        'batch_dates_mode',
        'custom_batch_dates',
        'order_notes_placeholder',
        'announcement_text',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_storefront_enabled' => 'boolean',
            'is_discoverable' => 'boolean',
            'allow_pickup' => 'boolean',
            'allow_delivery' => 'boolean',
            'allow_request_order' => 'boolean',
            'allow_scheduled_order' => 'boolean',
            'allow_customer_po' => 'boolean',
            'allow_reservation' => 'boolean',
            'allow_custom_date' => 'boolean',
            'quota_metric' => 'string',
            'preorder_quota_unit' => 'string',
            'batch_dates_mode' => 'string',
            'custom_batch_dates' => 'array',
            'min_order_amount' => 'float',
            'order_auto_cancel_minutes' => 'integer',
            'lead_time_hours' => 'integer',
            'operating_days' => 'array',
            'available_slots' => 'array',
            'max_capacity_per_slot' => 'integer',
            'daily_order_quota' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
