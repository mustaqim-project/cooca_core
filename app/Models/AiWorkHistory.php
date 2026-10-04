<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AiWorkHistory extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    protected $table = 'ai_work_histories';

    protected $fillable = [
        'business_id',
        'ai_task_id',
        'session_title',
        'executive_summary',
        'participating_agents',
        'insights_count',
        'actions_count',
        'approved_count',
        'rejected_count',
        'metadata',
        'recorded_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'participating_agents' => 'array',
            'metadata' => 'array',
            'insights_count' => 'integer',
            'actions_count' => 'integer',
            'approved_count' => 'integer',
            'rejected_count' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'ai_task_id');
    }

    public function getAgentAttribute(): string
    {
        $agents = $this->participating_agents;
        if (is_array($agents) && count($agents) > 0) {
            return (string) $agents[0];
        }

        return 'executive';
    }

    public function getActionAttribute(): string
    {
        return (string) ($this->session_title ?? 'Sesi Analisis AI');
    }

    public function getSummaryAttribute(): string
    {
        return (string) ($this->executive_summary ?? '');
    }
}
