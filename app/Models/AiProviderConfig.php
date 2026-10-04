<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiProviderConfig extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_GEMINI = 'gemini';
    public const PROVIDER_ANTHROPIC = 'anthropic';
    public const PROVIDER_OPENROUTER = 'openrouter';

    public const STATUS_CONNECTED = 'connected';
    public const STATUS_ERROR = 'error';
    public const STATUS_UNTESTED = 'untested';

    protected $table = 'ai_provider_configs';

    protected $fillable = [
        'business_id',
        'provider',
        'api_key',
        'model',
        'is_active',
        'is_default',
        'settings',
        'tested_at',
        'status',
        'last_error',
    ];

    /**
     * Never leak API keys to JSON or array serializations.
     */
    protected $hidden = [
        'api_key',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'settings' => 'array',
            'tested_at' => 'datetime',
        ];
    }

    /**
     * Masked representation for UI display.
     */
    public function getMaskedApiKeyAttribute(): string
    {
        if (empty($this->attributes['api_key'])) {
            return '';
        }

        return '••••••••••••••••';
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
