<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BugReport extends Model
{
    use HasFactory, HasUuid;

    public const STATUSES = ['open', 'triaged', 'in_progress', 'resolved', 'closed', 'rejected'];
    public const SEVERITIES = ['low', 'normal', 'high', 'critical'];
    public const CATEGORIES = ['bug', 'performance', 'security', 'ui', 'data', 'other'];

    protected $fillable = ['business_id', 'reporter_id', 'assigned_admin_id', 'title', 'category', 'severity', 'description', 'steps_to_reproduce', 'expected_behavior', 'actual_behavior', 'environment', 'attachment_path', 'status', 'priority', 'progress_percent', 'admin_notes', 'resolution', 'resolved_at', 'closed_at'];

    protected function casts(): array
    {
        return ['progress_percent' => 'integer', 'resolved_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_id'); }
    public function assignedAdmin(): BelongsTo { return $this->belongsTo(Admin::class, 'assigned_admin_id'); }
    public function updates(): MorphMany { return $this->morphMany(FeedbackUpdate::class, 'trackable')->latest(); }
}
