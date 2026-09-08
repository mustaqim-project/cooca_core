<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppAdminBlast extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_admin_blasts';

    protected $fillable = [
        'admin_id',
        'title',
        'message',
        'media_url',
        'target_filter',
        'total_recipients',
        'total_sent',
        'total_failed',
        'status',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppAdminBlastRecipient::class, 'blast_id');
    }
}
