<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_RECURRING = 'recurring';
    public const TYPE_SPECIFIC_DATE = 'specific_date';

    public const DAY_MONDAY = 'monday';
    public const DAY_TUESDAY = 'tuesday';
    public const DAY_WEDNESDAY = 'wednesday';
    public const DAY_THURSDAY = 'thursday';
    public const DAY_FRIDAY = 'friday';
    public const DAY_SATURDAY = 'saturday';
    public const DAY_SUNDAY = 'sunday';

    protected $table = 'employee_schedules';

    protected $fillable = [
        'business_id',
        'user_id',
        'location_id',
        'work_shift_id',
        'schedule_type',
        'day_of_week',
        'specific_date',
        'is_off_day',
        'effective_date',
        'end_date',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'specific_date' => 'date',
            'effective_date' => 'date',
            'end_date' => 'date',
            'is_off_day' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function shift(): BelongsTo
    {
        return $this->workShift();
    }

    /**
     * Scope to recurring schedules.
     */
    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('schedule_type', self::TYPE_RECURRING);
    }

    /**
     * Scope to specific date schedules.
     */
    public function scopeSpecificDate(Builder $query): Builder
    {
        return $query->where('schedule_type', self::TYPE_SPECIFIC_DATE);
    }

    /**
     * Scope to schedules effective on a given date.
     */
    public function scopeEffectiveOn(Builder $query, Carbon|string $date): Builder
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : (string) $date;

        return $query->where(function (Builder $q) use ($dateStr) {
            $q->whereNull('effective_date')
                ->orWhereDate('effective_date', '<=', $dateStr);
        })->where(function (Builder $q) use ($dateStr) {
            $q->whereNull('end_date')
                ->orWhereDate('end_date', '>=', $dateStr);
        });
    }
}
