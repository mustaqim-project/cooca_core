<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PosTable extends Model
{
    use Auditable, BelongsToBusiness, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ORDERING = 'ordering';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_SERVING = 'serving';
    public const STATUS_WAITING_PAYMENT = 'waiting_payment';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'business_id',
        'location_id',
        'table_number',
        'name',
        'capacity',
        'status',
        'qr_token',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $table): void {
            if (empty($table->qr_token)) {
                $table->qr_token = Str::random(32);
            }
        });
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return HasMany<PosTableSession, $this>
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(PosTableSession::class, 'pos_table_id');
    }

    /**
     * @return HasOne<PosTableSession, $this>
     */
    public function activeSession(): HasOne
    {
        return $this->hasOne(PosTableSession::class, 'pos_table_id')->where('status', PosTableSession::STATUS_OPEN)->latestOfMany();
    }

    /**
     * @return HasMany<PosOrder, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'pos_table_id');
    }

    /**
     * Regenerate cryptographic QR token.
     */
    public function regenerateQrToken(): string
    {
        $newToken = Str::random(32);
        $this->update(['qr_token' => $newToken]);
        return $newToken;
    }

    /**
     * Get the public ordering URL for this table.
     */
    public function getQrUrlAttribute(): string
    {
        return url('/t/' . $this->qr_token);
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'Tersedia',
            self::STATUS_ORDERING => 'Memilih Menu',
            self::STATUS_OCCUPIED => 'Terisi',
            self::STATUS_PREPARING => 'Menyiapkan',
            self::STATUS_SERVING => 'Disajikan',
            self::STATUS_WAITING_PAYMENT => 'Menunggu Pembayaran',
            self::STATUS_CLOSED => 'Selesai / Ditutup',
            self::STATUS_INACTIVE => 'Nonaktif',
            default => ucfirst($this->status),
        };
    }
}
