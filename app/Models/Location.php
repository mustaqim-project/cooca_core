<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasSlug, HasUuid;

    protected $table = 'locations';

    protected $fillable = [
        'business_id',
        'parent_id',
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
        'timezone',
        'timezone_mode',
        'operating_hours_mode',
        'operating_hours',
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
            'operating_hours' => 'array',
        ];
    }

    public function getTimezone(): string
    {
        return \App\Support\TimezoneHelper::resolve($this->business, $this);
    }

    public function getOperatingHours(): array
    {
        $mode = $this->operating_hours_mode ?? 'inherit';
        if ($mode === 'custom' && ! empty($this->operating_hours)) {
            return \App\Support\TimezoneHelper::normalizeOperatingHours($this->operating_hours);
        }

        // Inherit from business
        if ($this->relationLoaded('business') && $this->business) {
            return $this->business->getOperatingHours();
        }

        if (! empty($this->business_id)) {
            $b = Business::find($this->business_id);
            if ($b) {
                return $b->getOperatingHours();
            }
        }

        return \App\Support\TimezoneHelper::defaultOperatingHours();
    }

    public function localNow(): \Carbon\Carbon
    {
        return \App\Support\TimezoneHelper::now(null, $this);
    }

    public function localToday(): string
    {
        return \App\Support\TimezoneHelper::todayString(null, $this);
    }

    public function isOperatingAt(?\Carbon\Carbon $localTime = null): bool
    {
        $time = $localTime ?? $this->localNow();
        return \App\Support\TimezoneHelper::isOperatingAt($this->getOperatingHours(), $time);
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

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childWarehouses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('type', 'warehouse');
    }

    public function isRoot(): bool
    {
        return empty($this->parent_id);
    }

    public function isSubWarehouse(): bool
    {
        return ! empty($this->parent_id);
    }

    public function isCentralWarehouse(): bool
    {
        return $this->type === 'warehouse' && empty($this->parent_id);
    }

    public function scopeRootLocations(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubLocations(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeOutlets(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('type', ['outlet', 'store', 'central_kitchen']);
    }

    /**
     * Resolve location ID and any child sub-warehouse IDs for stock aggregation.
     *
     * @return array<int, string>
     */
    public static function resolveLocationIds(string $locationId): array
    {
        $childIds = self::where('parent_id', $locationId)->pluck('id')->all();

        return array_merge([$locationId], $childIds);
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

    public function workShifts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WorkShift::class);
    }

    public function employeeSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function storeEdcTerminals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreEdcTerminal::class);
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

    /**
     * Get the resolved effective timezone for this location.
     */
    public function getEffectiveTimezone(?Business $business = null): string
    {
        return \App\Support\TimezoneHelper::resolve($business ?? $this->business, $this);
    }

    /**
     * Get the resolved effective operating hours for this location.
     *
     * @return array<string, mixed>
     */
    public function getEffectiveOperatingHours(?Business $business = null): array
    {
        return \App\Support\TimezoneHelper::operatingHours($business ?? $this->business, $this);
    }

    /**
     * Check if this location is currently open.
     */
    public function isCurrentlyOpen(?\Carbon\Carbon $at = null): bool
    {
        return \App\Support\TimezoneHelper::isOpen($this->business, $this, $at);
    }
}
