<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTermReminder extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const TYPE_UPCOMING_H3 = 'upcoming_h3';

    public const TYPE_DUE_DATE = 'due_date';

    public const TYPE_OVERDUE = 'overdue';

    public const TYPE_CREDIT_BALANCE = 'credit_balance';

    public const TYPE_MANUAL = 'manual';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_BOTH = 'both';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'business_id',
        'customer_id',
        'invoice_id',
        'reminder_type',
        'channel',
        'recipient_phone',
        'recipient_email',
        'status',
        'wa_status',
        'email_status',
        'error_message',
        'sent_date',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_date' => 'date',
            'sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
