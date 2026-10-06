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
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkShift extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'work_shifts';

    protected $fillable = [
        'business_id',
        'location_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'grace_period_minutes',
        'is_overnight',
        'is_active',
        'color',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'break_duration_minutes' => 'integer',
            'grace_period_minutes' => 'integer',
            'is_overnight' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isOvernight(): bool
    {
        if ($this->is_overnight) {
            return true;
        }

        if (! empty($this->start_time) && ! empty($this->end_time)) {
            return strcmp($this->start_time, $this->end_time) > 0;
        }

        return false;
    }

    /**
     * Human-readable formatted hours representation.
     */
    public function getFormattedHoursAttribute(): string
    {
        $overnightLabel = $this->isOvernight() ? ' (+1 hari)' : '';

        return "{$this->start_time} - {$this->end_time}{$overnightLabel}";
    }

    /**
     * Resolve scheduled start Carbon instance in specific timezone.
     */
    public function getScheduledStartCarbon(string $date, string $tz = 'Asia/Jakarta'): Carbon
    {
        return Carbon::parse("{$date} {$this->start_time}", $tz);
    }

    /**
     * Resolve scheduled end Carbon instance in specific timezone, handling overnight correctly.
     */
    public function getScheduledEndCarbon(string $date, string $tz = 'Asia/Jakarta'): Carbon
    {
        $end = Carbon::parse("{$date} {$this->end_time}", $tz);

        if ($this->isOvernight()) {
            $end->addDay();
        }

        return $end;
    }

    /**
     * Total work duration in minutes excluding break.
     */
    public function getNetWorkMinutesAttribute(): int
    {
        $start = Carbon::parse("2026-01-01 {$this->start_time}");
        $end = Carbon::parse("2026-01-01 {$this->end_time}");

        if ($this->isOvernight()) {
            $end->addDay();
        }

        $gross = (int) $start->diffInMinutes($end);
        $net = max(0, $gross - $this->break_duration_minutes);

        return $net;
    }
}
