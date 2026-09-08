<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppBroadcastCampaign extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $table = 'whatsapp_broadcast_campaigns';

    protected $fillable = [
        'business_id',
        'title',
        'message',
        'media_url',
        'target_filter',
        'total_recipients',
        'total_sent',
        'total_failed',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_recipients' => 'integer',
            'total_sent' => 'integer',
            'total_failed' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppBroadcastRecipient::class, 'campaign_id');
    }
}
