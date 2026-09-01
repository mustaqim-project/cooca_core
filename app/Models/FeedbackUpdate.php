<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedbackUpdate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['status', 'progress_percent', 'comment', 'user_id', 'admin_id'];

    protected function casts(): array
    {
        return ['progress_percent' => 'integer'];
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
