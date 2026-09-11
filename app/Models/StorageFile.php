<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class StorageFile extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public const CATEGORY_BUSINESS_LOGO = 'business_logo';
    public const CATEGORY_PRODUCT_IMAGE = 'product_image';
    public const CATEGORY_LANDING_PAGE_IMAGE = 'landing_page_image';
    public const CATEGORY_CUSTOMER_ATTACHMENT = 'customer_attachment';
    public const CATEGORY_SUPPLIER_ATTACHMENT = 'supplier_attachment';
    public const CATEGORY_INVOICE_ATTACHMENT = 'invoice_attachment';
    public const CATEGORY_PURCHASE_ORDER_ATTACHMENT = 'purchase_order_attachment';
    public const CATEGORY_DOCUMENT = 'document';
    public const CATEGORY_COMMUNITY_IMAGE = 'community_image';
    public const CATEGORY_FEEDBACK_ATTACHMENT = 'feedback_attachment';
    public const CATEGORY_OWNER_AVATAR = 'owner_avatar';
    public const CATEGORY_IMPORT_TEMPORARY = 'import_temporary';
    public const CATEGORY_OTHER = 'other';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DELETED = 'deleted';

    public const CATEGORIES = [
        self::CATEGORY_BUSINESS_LOGO => 'Logo Bisnis',
        self::CATEGORY_PRODUCT_IMAGE => 'Foto Produk',
        self::CATEGORY_LANDING_PAGE_IMAGE => 'Website Landing Page',
        self::CATEGORY_CUSTOMER_ATTACHMENT => 'Lampiran Pelanggan',
        self::CATEGORY_SUPPLIER_ATTACHMENT => 'Lampiran Supplier',
        self::CATEGORY_INVOICE_ATTACHMENT => 'Lampiran Invoice',
        self::CATEGORY_PURCHASE_ORDER_ATTACHMENT => 'Lampiran Purchase Order',
        self::CATEGORY_DOCUMENT => 'Dokumen Bisnis',
        self::CATEGORY_COMMUNITY_IMAGE => 'Foto Komunitas',
        self::CATEGORY_FEEDBACK_ATTACHMENT => 'Lampiran Masukan/Bug',
        self::CATEGORY_OWNER_AVATAR => 'Foto Profil Owner',
        self::CATEGORY_IMPORT_TEMPORARY => 'Import Sementara',
        self::CATEGORY_OTHER => 'File Lainnya',
    ];

    protected $fillable = [
        'owner_id',
        'business_id',
        'user_id',
        'file_name',
        'file_path',
        'disk',
        'mime_type',
        'file_size',
        'category',
        'module',
        'is_temporary',
        'status',
        'metadata',
        'uploaded_at',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_temporary' => 'boolean',
            'metadata' => 'array',
            'uploaded_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)->whereNull('deleted_at');
    }

    public function scopeTracked(Builder $query): Builder
    {
        return $query->where('is_temporary', false)
            ->where('category', '!=', self::CATEGORY_IMPORT_TEMPORARY);
    }

    public function scopeForOwner(Builder $query, string $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }

    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Lainnya';
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }
        return Storage::disk($this->disk ?? 'public')->url($this->file_path);
    }
}
