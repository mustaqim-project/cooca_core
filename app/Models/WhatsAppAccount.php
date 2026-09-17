<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class WhatsAppAccount
 *
 * Repositori kredensial dan konfigurasi akun resmi WhatsApp Cloud API (Meta)
 * untuk setiap tenant merchant (business_id) di platform COOCA.
 *
 * @property int $id
 * @property string $business_id
 * @property string $waba_id
 * @property string $phone_number_id
 * @property string $phone_number
 * @property string|null $display_phone_number
 * @property string|null $verified_name
 * @property string|null $code_verification_status
 * @property string $quality_rating
 * @property string $messaging_limit_tier
 * @property string $access_token
 * @property string $token_type
 * @property \Illuminate\Support\Carbon|null $token_expires_at
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $webhook_verified_at
 * @property array|null $settings
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Business $business
 */
class WhatsAppAccount extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'business_id',
        'waba_id',
        'phone_number_id',
        'phone_number',
        'display_phone_number',
        'verified_name',
        'code_verification_status',
        'quality_rating',
        'messaging_limit_tier',
        'access_token',
        'token_type',
        'token_expires_at',
        'status',
        'webhook_verified_at',
        'settings',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Auto-encryption & decryption via Laravel APP_KEY (AES-256-CBC)
            'access_token'        => 'encrypted',
            'token_expires_at'    => 'datetime',
            'webhook_verified_at' => 'datetime',
            'settings'            => 'array',
            'metadata'            => 'array',
        ];
    }

    /**
     * Relasi ke tenant Business.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Relasi ke riwayat pesan log WhatsApp tenant.
     */
    public function messageLogs(): HasMany
    {
        return $this->hasMany(WhatsAppMessageLog::class, 'business_id', 'business_id');
    }

    /**
     * Scope query berdasarkan business_id (Tenant Isolation).
     */
    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    /**
     * Scope query untuk akun yang berstatus aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Cek apakah token akses telah kedaluwarsa.
     */
    public function isTokenExpired(): bool
    {
        if ($this->token_expires_at === null) {
            return false;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Cek apakah akun WhatsApp terhubung.
     */
    public function isConnected(): bool
    {
        $status = strtolower($this->status ?? '');

        return in_array($status, ['active', 'connected', 'live'], true) && ! $this->isTokenExpired();
    }

    /**
     * Cek apakah akun WhatsApp aktif dan siap mengirim pesan.
     */
    public function isActive(): bool
    {
        return $this->isConnected();
    }

    /**
     * Cek apakah rating kualitas nomor Meta dalam kategori baik.
     */
    public function isQualityGood(): bool
    {
        return in_array(strtoupper($this->quality_rating), ['GREEN', 'UNKNOWN'], true);
    }

    /**
     * Ambil setting spesifik dengan nilai default.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];

        return data_get($settings, $key, $default);
    }
}
