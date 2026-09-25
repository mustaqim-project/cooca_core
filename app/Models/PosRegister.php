<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PosRegister extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $fillable = [
        'business_id',
        'location_id',
        'name',
        'code',
        'device_identifier',
        'default_receipt_printer_id',
        'default_kitchen_printer_id',
        'default_cash_drawer_name',
        'last_seen_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<PosPrinter, $this>
     */
    public function defaultReceiptPrinter(): BelongsTo
    {
        return $this->belongsTo(PosPrinter::class, 'default_receipt_printer_id');
    }

    /**
     * @return BelongsTo<PosPrinter, $this>
     */
    public function defaultKitchenPrinter(): BelongsTo
    {
        return $this->belongsTo(PosPrinter::class, 'default_kitchen_printer_id');
    }

    /**
     * @return HasMany<PosShift, $this>
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(PosShift::class);
    }

    /**
     * @return HasOne<PosShift, $this>
     */
    public function activeShift(): HasOne
    {
        return $this->hasOne(PosShift::class)->where('status', PosShift::STATUS_OPEN)->latest('opened_at');
    }

    /**
     * @return HasMany<PosOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class);
    }

    /**
     * Touch device heartbeat timestamp.
     */
    public function touchLastSeen(): void
    {
        $this->updateQuietly(['last_seen_at' => now()]);
    }
}
