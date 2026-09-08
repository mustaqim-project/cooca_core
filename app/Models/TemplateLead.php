<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'business_name',
        'template_slug',
        'template_name',
        'ip_address',
    ];

    /**
     * Get WhatsApp formatted link for quick follow-up.
     */
    public function getWhatsappUrlAttribute(): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $message = urlencode("Halo {$this->name}, terima kasih telah mengunduh {$this->template_name} dari Cooca UMKM. Ada yang bisa kami bantu seputar pembukuan usaha Anda?");

        return "https://wa.me/{$cleanPhone}?text={$message}";
    }
}
