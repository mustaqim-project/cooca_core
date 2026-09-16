<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppAdminSession extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_admin_sessions';

    protected $fillable = [
        'session_id',
        'name',
        'phone_number',
        'device_name',
        'status',
        'qr_data_url',
        'is_active',
        'last_connected_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active'         => 'boolean',
            'last_connected_at' => 'datetime',
        ];
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected')->where('is_active', true);
    }
}
