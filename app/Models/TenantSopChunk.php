<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantSopChunk extends Model
{
    use BelongsToBusiness, HasUuid;

    protected $fillable = [
        'business_id',
        'document_id',
        'page_number',
        'section_title',
        'content_text',
        'keywords',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Parent SOP document.
     *
     * @return BelongsTo<TenantSopDocument, $this>
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(TenantSopDocument::class, 'document_id');
    }
}
