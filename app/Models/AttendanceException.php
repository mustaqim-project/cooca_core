<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceException extends Model
{
    use BelongsToBusiness, HasUuid;

    public const MODE_WFH = 'wfh';
    public const MODE_WFA = 'wfa';
    public const MODE_FIELD_WORK = 'field_work';
    public const MODE_BUSINESS_TRIP = 'business_trip';
    public const MODE_TEMPORARY_ASSIGNMENT = 'temporary_assignment';

    // Aliases for TYPE
    public const TYPE_WFH = self::MODE_WFH;
    public const TYPE_WFA = self::MODE_WFA;
    public const TYPE_FIELD_WORK = self::MODE_FIELD_WORK;
    public const TYPE_BUSINESS_TRIP = self::MODE_BUSINESS_TRIP;
    public const TYPE_TEMPORARY_ASSIGNMENT = self::MODE_TEMPORARY_ASSIGNMENT;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVOKED = 'revoked';
    public const STATUS_EXPIRED = 'expired';

    protected $table = 'attendance_exceptions';

    protected $fillable = [
        'business_id',
        'user_id',
        'exception_mode',
        'start_date',
        'end_date',
        'allowed_latitude',
        'allowed_longitude',
        'allowed_radius_meters',
        'location_name',
        'reason',
        'notes',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'allowed_latitude' => 'decimal:7',
            'allowed_longitude' => 'decimal:7',
            'allowed_radius_meters' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Employee requesting/assigned this exception.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Reviewer / Approver of this exception policy.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope only approved active policies.
     */
    public function scopeActiveOnDate(Builder $query, Carbon|string $date): Builder
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : $date;
        return $query->where('status', self::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr);
    }

    /**
     * Check if exception is active for a given date.
     */
    public function isActiveFor(Carbon|string $date): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $targetDate = $date instanceof Carbon ? $date->startOfDay() : Carbon::parse($date)->startOfDay();
        return $targetDate->gte($this->start_date->startOfDay()) && $targetDate->lte($this->end_date->endOfDay());
    }

    /**
     * Check whether coordinates are permitted by this exception policy.
     */
    public function allowsCoordinates(?float $lat, ?float $lng, int $calculatedDistance = 0): bool
    {
        if ($this->exception_mode === self::MODE_WFA) {
            return true;
        }

        if ($this->allowed_latitude === null || $this->allowed_longitude === null) {
            return true;
        }

        if ($lat === null || $lng === null) {
            return false;
        }

        $maxRadius = $this->allowed_radius_meters ?: 100;
        return $calculatedDistance <= $maxRadius;
    }
}
