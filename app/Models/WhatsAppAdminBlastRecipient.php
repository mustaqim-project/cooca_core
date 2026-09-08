<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppAdminBlastRecipient extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_admin_blast_recipients';

    protected $fillable = [
        'blast_id',
        'business_id',
        'owner_id',
        'owner_name',
        'business_name',
        'phone_number',
        'status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function blast(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAdminBlast::class, 'blast_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
