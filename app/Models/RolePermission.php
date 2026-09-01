<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

final class RolePermission extends Pivot
{
    use HasUuid;

    protected $table = 'role_permissions';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'role_id',
        'permission_id',
    ];
}
