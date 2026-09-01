<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostingResultItem extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'costing_result_items';

    protected $fillable = [
        'costing_result_id',
        'item_type',
        'item_name',
        'amount',
        'meta',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'meta' => 'array',
        ];
    }

    /**
     * @return BelongsTo<CostingResult, $this>
     */
    public function costingResult(): BelongsTo
    {
        return $this->belongsTo(CostingResult::class);
    }
}
