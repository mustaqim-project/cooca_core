<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BusinessMembership extends Pivot
{
    use HasUuid;

    protected $table = 'business_users';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'business_id',
        'user_id',
        'role',
        'role_id',
        'job_title',
        'employment_type',
        'join_date',
        'base_salary',
        'daily_rate',
        'hourly_rate',
        'fixed_allowances',
        'variable_allowances',
        'pin_hash',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'whatsapp_number',
        'tax_ptkp_status',
        'bpjs_tk_enabled',
        'bpjs_kes_enabled',
        'primary_location_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'base_salary' => 'decimal:2',
            'daily_rate' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'fixed_allowances' => 'decimal:2',
            'variable_allowances' => 'decimal:2',
            'bpjs_tk_enabled' => 'boolean',
            'bpjs_kes_enabled' => 'boolean',
        ];
    }

    /**
     * Get user of this membership.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get business of this membership.
     *
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'owner') {
            return true;
        }

        if ($this->customRole) {
            return $this->customRole->permissions()->where('permissions.slug', $permission)->exists();
        }

        if ($this->role) {
            $role = Role::where('slug', $this->role)
                ->where(function ($query) {
                    $query->where('business_id', $this->business_id)->orWhereNull('business_id');
                })
                ->orderByRaw('business_id IS NULL')
                ->first();

            return $role?->permissions()->where('permissions.slug', $permission)->exists() ?? false;
        }

        return false;
    }
}
