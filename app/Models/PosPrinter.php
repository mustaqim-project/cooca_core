<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosPrinter extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_LAN = 'lan';
    public const TYPE_WIFI = 'wifi';
    public const TYPE_USB = 'usb';
    public const TYPE_BLUETOOTH = 'bluetooth';
    public const TYPE_WINDOWS = 'windows';
    public const TYPE_SERIAL = 'serial';
    public const TYPE_AGENT = 'agent';

    public const USAGE_CASHIER_RECEIPT = 'cashier_receipt';
    public const USAGE_KITCHEN_ORDER = 'kitchen_order';
    public const USAGE_BAR_ORDER = 'bar_order';
    public const USAGE_SHIFT_REPORT = 'shift_report';
    public const USAGE_LABEL = 'label';

    public const CAP_PRINT_TEXT = 'print_text';
    public const CAP_PRINT_IMAGE = 'print_image';
    public const CAP_PRINT_LOGO = 'print_logo';
    public const CAP_BARCODE = 'barcode';
    public const CAP_QR_CODE = 'qr_code';
    public const CAP_CUT = 'cut';
    public const CAP_CASH_DRAWER = 'cash_drawer';
    public const CAP_OPEN_DRAWER = 'open_drawer';
    public const CAP_BEEP = 'beep';

    protected $fillable = [
        'business_id',
        'location_id',
        'name',
        'connection_type',
        'interface_address',
        'port',
        'paper_width',
        'character_set',
        'is_active',
        'is_default',
        'capabilities',
        'assigned_usages',
        'assigned_category_ids',
        'last_status',
        'last_status_checked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'capabilities' => 'array',
            'assigned_usages' => 'array',
            'assigned_category_ids' => 'array',
            'last_status_checked_at' => 'datetime',
        ];
    }

    /**
     * Location relationship.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Print jobs relationship.
     *
     * @return HasMany<PosPrintJob, $this>
     */
    public function printJobs(): HasMany
    {
        return $this->hasMany(PosPrintJob::class, 'printer_id');
    }

    /**
     * Check if printer supports specific capability.
     */
    public function hasCapability(string $capability): bool
    {
        $caps = $this->capabilities ?? [
            self::CAP_PRINT_TEXT,
            self::CAP_BARCODE,
            self::CAP_QR_CODE,
            self::CAP_CUT,
            self::CAP_CASH_DRAWER,
        ];

        return in_array($capability, $caps, true);
    }

    /**
     * Check if printer is assigned for specific usage.
     */
    public function supportsUsage(string $usage): bool
    {
        $usages = $this->assigned_usages ?? [self::USAGE_CASHIER_RECEIPT];
        return in_array($usage, $usages, true);
    }

    /**
     * Check if printer is assigned for a given product category ID (KDS/KOT routing).
     */
    public function supportsCategory(?string $categoryId): bool
    {
        if ($categoryId === null) {
            return false;
        }

        $cats = $this->assigned_category_ids ?? [];
        if (empty($cats)) {
            return true; // If no specific categories filtered, accept all
        }

        return in_array($categoryId, $cats, true);
    }

    /**
     * Get maximum text character columns based on paper width.
     */
    public function getMaxColumns(): int
    {
        return $this->paper_width === '58mm' ? 32 : 48;
    }

    /**
     * Scope for active printers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific location / outlet.
     */
    public function scopeForLocation($query, ?string $locationId)
    {
        if ($locationId) {
            return $query->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                  ->orWhereNull('location_id');
            });
        }

        return $query;
    }
}
