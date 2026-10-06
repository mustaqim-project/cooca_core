<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use BelongsToBusiness, HasUuid;

    public const STATUS_PRESENT = 'present';
    public const STATUS_LATE = 'late';
    public const STATUS_HALF_DAY = 'half_day';
    public const STATUS_ABSENT = 'absent';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_SICK = 'sick';

    public const CLOCK_IN_ON_TIME = 'on_time';
    public const CLOCK_IN_LATE = 'late';
    public const CLOCK_IN_FREE_LOCATION = 'free_location';

    public const CLOCK_OUT_NORMAL = 'normal';
    public const CLOCK_OUT_EARLY = 'early_leave';
    public const CLOCK_OUT_OVERTIME = 'overtime';
    public const CLOCK_OUT_FREE_LOCATION = 'free_location';

    protected $table = 'attendances';

    protected $fillable = [
        'business_id',
        'user_id',
        'location_id',
        'work_shift_id',
        'shift_name',
        'date',
        'scheduled_start_at',
        'scheduled_end_at',
        'clock_in_at',
        'clock_in_lat',
        'clock_in_lng',
        'clock_in_distance_meters',
        'clock_in_address',
        'clock_in_photo',
        'clock_in_status',
        'clock_in_notes',
        'clock_out_at',
        'clock_out_lat',
        'clock_out_lng',
        'clock_out_distance_meters',
        'clock_out_address',
        'clock_out_photo',
        'clock_out_status',
        'clock_out_notes',
        'work_duration_minutes',
        'early_in_minutes',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'timezone',
        'status',
        'is_geofenced',
        'is_corrected',
        'attendance_correction_id',
        'face_verified',
        'face_similarity_score',
        'exception_policy_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'clock_in_lat' => 'float',
            'clock_in_lng' => 'float',
            'clock_out_lat' => 'float',
            'clock_out_lng' => 'float',
            'clock_in_distance_meters' => 'integer',
            'clock_out_distance_meters' => 'integer',
            'work_duration_minutes' => 'integer',
            'early_in_minutes' => 'integer',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'is_geofenced' => 'boolean',
            'is_corrected' => 'boolean',
            'face_verified' => 'boolean',
            'face_similarity_score' => 'float',
        ];
    }

    /**
     * User of this attendance.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Location/branch where attendance was recorded.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Work Shift assigned for this attendance session.
     *
     * @return BelongsTo<WorkShift, $this>
     */
    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'work_shift_id');
    }

    public function shift(): BelongsTo
    {
        return $this->workShift();
    }

    /**
     * Correction ticket linked to this attendance.
     *
     * @return BelongsTo<AttendanceCorrection, $this>
     */
    public function correction(): BelongsTo
    {
        return $this->belongsTo(AttendanceCorrection::class, 'attendance_correction_id');
    }

    /**
     * Exception policy linked to this attendance.
     *
     * @return BelongsTo<AttendanceException, $this>
     */
    public function exceptionPolicy(): BelongsTo
    {
        return $this->belongsTo(AttendanceException::class, 'exception_policy_id');
    }

    /**
     * Scope to current date.
     */
    public function scopeForToday(Builder $query): Builder
    {
        return $query->whereDate('date', now()->toDateString());
    }

    /**
     * Scope to month and year period.
     */
    public function scopeForPeriod(Builder $query, int $month, int $year): Builder
    {
        return $query->whereMonth('date', $month)->whereYear('date', $year);
    }

    /**
     * Scope to valid attended statuses (present, late, half_day).
     */
    public function scopeValidAttendances(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PRESENT, self::STATUS_LATE, self::STATUS_HALF_DAY]);
    }

    public function hasClockedOut(): bool
    {
        return $this->clock_out_at !== null;
    }

    public function isLate(): bool
    {
        return $this->late_minutes > 0 || $this->clock_in_status === self::CLOCK_IN_LATE;
    }

    /**
     * Get human-readable work duration.
     */
    public function getFormattedWorkDurationAttribute(): string
    {
        if ($this->work_duration_minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($this->work_duration_minutes, 60);
        $minutes = $this->work_duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}j {$minutes}m";
        }

        if ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$minutes} menit";
    }
}
