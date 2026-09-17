<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SocialPostMedia
 *
 * Representasi berkas media per pos (mendukung multiple media berurutan untuk
 * Instagram Carousel 2-10 items, Video, maupun Photo Album).
 *
 * @property string $id
 * @property string $social_media_post_id
 * @property int $sort_order
 * @property string $media_type
 * @property string $media_url
 * @property string|null $local_path
 * @property int|null $file_size
 * @property array|null $metadata
 * @property-read \App\Models\SocialMediaPost $post
 */
class SocialPostMedia extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'social_post_media';

    protected $fillable = [
        'social_media_post_id',
        'sort_order',
        'media_type',
        'media_url',
        'local_path',
        'file_size',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'file_size'  => 'integer',
            'metadata'   => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialMediaPost::class, 'social_media_post_id');
    }

    public function isImage(): bool
    {
        return $this->media_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->media_type === 'video';
    }
}
