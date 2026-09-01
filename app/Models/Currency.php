<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_base',
        'decimal_places',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_base' => 'boolean',
            'decimal_places' => 'integer',
        ];
    }
}
