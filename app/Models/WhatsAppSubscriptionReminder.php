<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppSubscriptionReminder extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_subscription_reminders';

    protected $fillable = [
        'business_id',
        'business_subscription_id',
        'owner_id',
        'owner_name',
        'business_name',
        'recipient_phone',
        'reminder_type',
        'subscription_ends_at',
        'message',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'subscription_ends_at' => 'date',
        'sent_at'              => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BusinessSubscription::class, 'business_subscription_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
