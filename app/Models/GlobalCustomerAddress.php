<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class GlobalCustomerAddress extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'global_customer_addresses';

    protected $fillable = [
        'global_customer_id',
        'label',
        'recipient_name',
        'recipient_phone',
        'full_address',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'biteship_area_id',
        'latitude',
        'longitude',
        'notes',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'latitude'   => 'float',
            'longitude'  => 'float',
            'is_default' => 'boolean',
        ];
    }

    // ---------------------------------------------------------
    // Relations
    // ---------------------------------------------------------

    public function globalCustomer(): BelongsTo
    {
        return $this->belongsTo(GlobalCustomer::class, 'global_customer_id');
    }

    // ---------------------------------------------------------
    // Helpers & Accessors
    // ---------------------------------------------------------

    /**
     * Mark this address as the default for the customer.
     */
    public function markAsDefault(): self
    {
        DB::transaction(function (): void {
            self::where('global_customer_id', $this->global_customer_id)
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);

            $this->is_default = true;
            $this->save();

            // Sync shipping_address on parent customer profile for backward compatibility
            if ($this->globalCustomer) {
                $this->globalCustomer->update([
                    'shipping_address' => $this->formatted_address,
                ]);
            }
        });

        return $this;
    }

    /**
     * Get complete human-readable formatted address with administrative area and postal code.
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->full_address,
            $this->village,
            $this->district,
            $this->city,
            $this->province ? $this->province . ($this->postal_code ? ' ' . $this->postal_code : '') : $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted administrative area (Kelurahan, Kecamatan, Kota, Provinsi).
     */
    public function getFormattedAreaAttribute(): string
    {
        $parts = array_filter([
            $this->village,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if address has valid GPS coordinates.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null && $this->latitude != 0 && $this->longitude != 0;
    }
}
