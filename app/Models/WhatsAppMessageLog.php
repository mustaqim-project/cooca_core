<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessageLog extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $table = 'whatsapp_message_logs';

    protected $fillable = [
        'business_id',
        'type',
        'recipient_phone',
        'recipient_name',
        'message',
        'status',
        'order_id',
        'error_message',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }
}
