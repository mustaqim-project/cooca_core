<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessTypeTemplate extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'business_type_templates';

    protected $fillable = [
        'code',
        'name',
        'industry_category',
        'description',
        'recommended_costing_method',
        'default_cost_components',
        'default_allocation_rules',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_cost_components' => 'array',
            'default_allocation_rules' => 'array',
        ];
    }
}
