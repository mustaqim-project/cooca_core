<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\Business;
use App\Models\Customer;
use App\Models\User;
use InvalidArgumentException;

final class CrmSearchCustomerTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'crm_search_customer';
    }

    public function getDescription(): string
    {
        return 'Mencari profil pelanggan berdasarkan nama atau nomor telepon WhatsApp, melihat poin loyalitas dan riwayat belanja.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:crm:read';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Nama pelanggan, nomor HP/WhatsApp, plat kendaraan, atau email.',
                ],
            ],
            'required' => ['query'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $search = trim((string) ($arguments['query'] ?? ''));
        if ($search === '') {
            throw new InvalidArgumentException('Query pencarian pelanggan tidak boleh kosong.');
        }

        $term = '%' . $search . '%';

        $customers = Customer::where('business_id', $business->id)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('code', 'like', $term)
                    ->orWhere('vehicle_license_plate', 'like', $term);
            })
            ->take(20)
            ->get();

        $list = $customers->map(fn ($c) => [
            'id' => $c->id,
            'code' => $c->code,
            'name' => $c->name,
            'phone' => $c->phone,
            'email' => $c->email,
            'membership_tier' => $c->membership_tier ?? 'reguler',
            'points_balance' => (int) ($c->points_balance ?? 0),
            'total_spent' => (float) ($c->total_spent ?? 0),
            'formatted_total_spent' => 'Rp ' . number_format((float) ($c->total_spent ?? 0), 0, ',', '.'),
            'total_orders' => (int) ($c->total_orders_count ?? 0),
            'vehicle_info' => $c->vehicle_license_plate ? "{$c->vehicle_license_plate} ({$c->vehicle_model})" : null,
        ]);

        return [
            'status' => 'success',
            'query' => $search,
            'total_found' => $list->count(),
            'customers' => $list,
        ];
    }
}
