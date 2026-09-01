<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CostPoolOverhead extends Pivot
{
    use HasUuid;

    protected $table = 'cost_pool_overheads';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'cost_pool_id',
        'overhead_id',
    ];
}
