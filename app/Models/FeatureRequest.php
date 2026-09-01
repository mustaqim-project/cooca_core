<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FeatureRequest extends Model
{
    use HasFactory, HasUuid;

    public const STATUSES = ['submitted', 'reviewing', 'planned', 'in_progress', 'released', 'declined'];
    public const CATEGORIES = ['reporting', 'inventory', 'sales', 'finance', 'ai', 'integration', 'mobile', 'other'];

    protected $fillable = ['business_id', 'requester_id', 'assigned_admin_id', 'title', 'category', 'description', 'business_value', 'use_case', 'proposed_solution', 'status', 'priority', 'progress_percent', 'admin_notes', 'released_at'];

    protected function casts(): array
    {
        return ['progress_percent' => 'integer', 'released_at' => 'datetime'];
    }

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requester_id'); }
    public function assignedAdmin(): BelongsTo { return $this->belongsTo(Admin::class, 'assigned_admin_id'); }
    public function updates(): MorphMany { return $this->morphMany(FeedbackUpdate::class, 'trackable')->latest(); }
}
