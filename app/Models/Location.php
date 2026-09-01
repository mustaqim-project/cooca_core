<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use BelongsToBusiness, HasFactory, HasSlug, HasUuid;

    protected $table = 'locations';

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'type',
        'code',
        'phone',
        'address',
        'is_primary',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function stocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function posRegisters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PosRegister::class);
    }

    public function posOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PosOrder::class);
    }
}
