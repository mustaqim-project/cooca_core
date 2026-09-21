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
        'province',
        'city',
        'district',
        'village',
        'postal_code',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'biteship_area_id',
        'is_primary',
        'is_active',
        'is_online_fulfillment',
        'allow_storefront_pickup',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
            'is_online_fulfillment' => 'boolean',
            'allow_storefront_pickup' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'geofence_radius_meters' => 'integer',
        ];
    }

    public function getFormattedFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->village,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
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

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Calculate distance in meters to a given coordinate using Haversine formula.
     */
    public function distanceTo(float $targetLat, float $targetLon): ?int
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $earthRadius = 6371000; // meters
        $latDelta = deg2rad($targetLat - (float) $this->latitude);
        $lonDelta = deg2rad($targetLon - (float) $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad((float) $this->latitude)) * cos(deg2rad($targetLat)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }
}
