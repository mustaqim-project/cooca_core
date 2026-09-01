<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomItem extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'bom_header_id',
        'material_id',
        'sub_bom_header_id',
        'quantity',
        'unit_id',
        'waste_percentage',
        'sort_order',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'waste_percentage' => 'float',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<BomHeader, $this>
     */
    public function header(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class, 'bom_header_id');
    }

    /**
     * @return BelongsTo<Material, $this>
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * @return BelongsTo<BomHeader, $this>
     */
    public function subBomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class, 'sub_bom_header_id');
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Check if this item is a sub-assembly.
     */
    public function isSubAssembly(): bool
    {
        return $this->sub_bom_header_id !== null;
    }
}
