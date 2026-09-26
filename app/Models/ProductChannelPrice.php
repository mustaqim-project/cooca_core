<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductChannelPrice extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'product_channel_prices';

    public const CHANNEL_DINE_IN = 'dine_in';
    public const CHANNEL_TAKEAWAY = 'takeaway';
    public const CHANNEL_GOFOOD = 'gofood';
    public const CHANNEL_GRABFOOD = 'grabfood';
    public const CHANNEL_SHOPEEFOOD = 'shopeefood';

    protected $fillable = [
        'business_id',
        'product_id',
        'channel',
        'price',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
        ];
    }

    /**
     * Associated product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
