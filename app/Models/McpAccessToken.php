<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

final class McpAccessToken extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mcp_access_tokens';

    protected $fillable = [
        'business_id',
        'user_id',
        'name',
        'token_hash',
        'abilities',
        'provider_hint',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'abilities' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(McpActivityLog::class, 'token_id');
    }

    /**
     * Check if token has a specific ability.
     */
    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        if (in_array('*', $abilities, true)) {
            return true;
        }

        if (in_array($ability, $abilities, true)) {
            return true;
        }

        // Wildcard check, e.g. 'mcp:expenses:*' matches 'mcp:expenses:write'
        foreach ($abilities as $pattern) {
            if (str_ends_with($pattern, ':*')) {
                $prefix = substr($pattern, 0, -1);
                if (str_starts_with($ability, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if token has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Determine if token is usable.
     */
    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired();
    }

    /**
     * Mark token as last used.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Generate and store a new secure MCP access token.
     *
     * @param  array<int, string>  $abilities
     * @return array{token: string, model: self}
     */
    public static function generateToken(
        Business $business,
        User $user,
        string $name,
        array $abilities,
        ?string $providerHint = 'all',
        ?Carbon $expiresAt = null
    ): array {
        $plainText = 'cooca_mcp_live_' . Str::random(40);
        $hash = hash('sha256', $plainText);

        $model = self::create([
            'business_id' => $business->id,
            'user_id' => $user->id,
            'name' => trim($name),
            'token_hash' => $hash,
            'abilities' => array_values(array_unique($abilities)),
            'provider_hint' => $providerHint ?: 'all',
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return [
            'token' => $plainText,
            'model' => $model,
        ];
    }
}
