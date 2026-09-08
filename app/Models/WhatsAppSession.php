<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppSession extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $table = 'whatsapp_sessions';

    protected $fillable = [
        'business_id',
        'session_id',
        'phone_number',
        'device_name',
        'status',
        'auto_send_receipt',
        'receipt_template',
        'last_connected_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auto_send_receipt' => 'boolean',
            'last_connected_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }
}
