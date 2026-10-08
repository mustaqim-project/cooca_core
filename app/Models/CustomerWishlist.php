<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CustomerWishlist extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'customer_wishlists';

    protected $fillable = [
        'global_customer_id',
        'product_id',
    ];

    /** @return BelongsTo<GlobalCustomer, $this> */
    public function globalCustomer(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'global_customer_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
