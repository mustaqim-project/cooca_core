<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessageTemplate extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'whatsapp_message_templates';

    protected $fillable = [
        'waba_id',
        'business_id',
        'meta_template_id',
        'name',
        'category',
        'language',
        'status',
        'components',
        'rejected_reason',
        'quality_score',
        'synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'components' => 'array',
            'synced_at'  => 'datetime',
        ];
    }

    /**
     * Relasi ke Business (Tenant jika di-assign khusus).
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Scope query untuk template yang sudah berstatus APPROVED.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'APPROVED');
    }

    /**
     * Cek apakah template sudah disetujui Meta.
     */
    public function isApproved(): bool
    {
        return strtoupper((string) $this->status) === 'APPROVED';
    }

    /**
     * Ekstrak teks body pesan dari array komponen.
     */
    public function getBodyText(): string
    {
        $components = $this->components ?? [];
        foreach ($components as $component) {
            if (strtoupper((string) ($component['type'] ?? '')) === 'BODY') {
                return (string) ($component['text'] ?? '');
            }
        }

        return '';
    }

    /**
     * Ekstrak teks header pesan dari array komponen jika ada.
     */
    public function getHeaderText(): ?string
    {
        $components = $this->components ?? [];
        foreach ($components as $component) {
            if (strtoupper((string) ($component['type'] ?? '')) === 'HEADER') {
                return $component['text'] ?? null;
            }
        }

        return null;
    }

    /**
     * Ekstrak teks footer pesan dari array komponen jika ada.
     */
    public function getFooterText(): ?string
    {
        $components = $this->components ?? [];
        foreach ($components as $component) {
            if (strtoupper((string) ($component['type'] ?? '')) === 'FOOTER') {
                return $component['text'] ?? null;
            }
        }

        return null;
    }

    /**
     * Ekstrak daftar buttons dari array komponen jika ada.
     */
    public function getButtons(): array
    {
        $components = $this->components ?? [];
        foreach ($components as $component) {
            if (strtoupper((string) ($component['type'] ?? '')) === 'BUTTONS') {
                return $component['buttons'] ?? [];
            }
        }

        return [];
    }
}
