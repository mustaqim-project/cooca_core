<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosOrderItemModifier extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'pos_order_item_id',
        'modifier_group_id',
        'modifier_option_id',
        'modifier_group_name',
        'modifier_option_name',
        'unit_price',
        'quantity',
        'subtotal',
        'material_snapshot',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'float',
            'quantity' => 'float',
            'subtotal' => 'float',
            'material_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<PosOrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(PosOrderItem::class, 'pos_order_item_id');
    }

    /**
     * @return BelongsTo<ModifierGroup, $this>
     */
    public function modifierGroup(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    /**
     * @return BelongsTo<ModifierOption, $this>
     */
    public function modifierOption(): BelongsTo
    {
        return $this->belongsTo(ModifierOption::class, 'modifier_option_id');
    }
}
