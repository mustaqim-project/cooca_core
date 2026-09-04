<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CommunityPost extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'owner_id',
        'business_id',
        'content',
        'image_path',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommunityLike::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'post_id')->latest();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function isLikedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->likes()->where('user_id', $user->getAuthIdentifier())->exists();
    }
}