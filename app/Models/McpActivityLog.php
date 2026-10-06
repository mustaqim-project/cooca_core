<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class McpActivityLog extends Model
{
    use HasFactory;

    protected $table = 'mcp_activity_logs';

    public const UPDATED_AT = null;

    protected $fillable = [
        'business_id',
        'token_id',
        'tool_name',
        'client_provider',
        'arguments_payload',
        'response_status',
        'execution_time_ms',
        'ip_address',
        'error_message',
    ];

    protected $casts = [
        'arguments_payload' => 'array',
        'execution_time_ms' => 'integer',
        'created_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(McpAccessToken::class, 'token_id');
    }
}
