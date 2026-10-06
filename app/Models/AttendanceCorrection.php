<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    use BelongsToBusiness, HasUuid;

    public const TYPE_CLOCK_IN_ONLY = 'clock_in_only';
    public const TYPE_CLOCK_OUT_ONLY = 'clock_out_only';
    public const TYPE_FULL_DAY = 'full_day';
    public const TYPE_STATUS_ONLY = 'status_only';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVISION = 'revision_requested';

    protected $table = 'attendance_corrections';

    protected $appends = ['type_label'];

    protected $fillable = [
        'business_id',
        'user_id',
        'attendance_id',
        'correction_number',
        'target_date',
        'correction_type',
        'proposed_clock_in',
        'proposed_clock_out',
        'proposed_status',
        'reason',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Employee requesting the correction.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Target attendance record (if any existed before).
     *
     * @return BelongsTo<Attendance, $this>
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Manager or HRD who reviewed this request.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isRevisionRequested(): bool
    {
        return $this->status === self::STATUS_REVISION;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->correction_type) {
            self::TYPE_CLOCK_IN_ONLY => 'Lupa Clock-In (Masuk)',
            self::TYPE_CLOCK_OUT_ONLY => 'Lupa Clock-Out (Pulang)',
            self::TYPE_FULL_DAY => 'Perbaikan Seharian (Masuk & Pulang)',
            self::TYPE_STATUS_ONLY => 'Perbaikan Status Kehadiran',
            default => ucfirst(str_replace('_', ' ', $this->correction_type)),
        };
    }
}
