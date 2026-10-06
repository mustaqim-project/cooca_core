<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceProductReview extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    protected $table = 'commerce_product_reviews';

    protected $fillable = [
        'business_id',
        'product_id',
        'commerce_order_id',
        'commerce_order_item_id',
        'global_customer_id',
        'rating',
        'review_text',
        'images_payload',
        'is_verified_purchase',
        'seller_reply',
        'seller_replied_at',
        'is_published',
    ];

    protected $casts = [
        'rating' => 'integer',
        'images_payload' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_published' => 'boolean',
        'seller_replied_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(CommerceOrder::class, 'commerce_order_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(CommerceOrderItem::class, 'commerce_order_item_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'global_customer_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified_purchase', true);
    }
}
