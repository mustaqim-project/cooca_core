<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PurchasingManageSupplierTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'purchasing_manage_supplier';
    }

    public function getDescription(): string
    {
        return 'Mengelola data pemasok/vendor bisnis: mencari daftar supplier rekanan, informasi kontak, dan mendaftarkan supplier baru ke sistem COOCA.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:purchasing:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar supplier) atau create (tambah supplier baru).',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Kata kunci nama supplier atau nomor telepon untuk pencarian.',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama supplier atau nama PT/CV vendor (wajib untuk create).',
                ],
                'contact_person' => [
                    'type' => 'string',
                    'description' => 'Nama narahubung / sales representative supplier.',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Nomor WhatsApp atau telepon supplier.',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Alamat email supplier.',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Alamat kantor / gudang supplier.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create' => $this->createSupplier($business, $user, $arguments),
            default  => $this->listSuppliers($business, $arguments),
        };
    }

    private function listSuppliers(Business $business, array $args): array
    {
        $query = Supplier::where('business_id', $business->id)->where('is_active', true);

        if (! empty($args['search'])) {
            $search = trim((string) $args['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->limit(50)->get();

        $data = $suppliers->map(function (Supplier $s) {
            return [
                'id' => $s->id,
                'name' => $s->name,
                'contact_person' => $s->contact_person,
                'phone' => $s->phone,
                'email' => $s->email,
                'address' => $s->address,
            ];
        });

        return [
            'status' => 'success',
            'success' => true,
            'total' => $suppliers->count(),
            'count' => $suppliers->count(),
            'suppliers' => $data,
        ];
    }

    private function createSupplier(Business $business, ?User $user, array $args): array
    {
        $name = trim((string) ($args['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama supplier (name) wajib diisi.');
        }

        $supplier = DB::transaction(function () use ($business, $user, $name, $args) {
            $s = Supplier::create([
                'business_id' => $business->id,
                'name' => $name,
                'contact_person' => trim((string) ($args['contact_person'] ?? '')),
                'phone' => trim((string) ($args['phone'] ?? '')),
                'email' => trim((string) ($args['email'] ?? '')),
                'address' => trim((string) ($args['address'] ?? '')),
                'is_active' => true,
            ]);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.supplier_create',
                'auditable_type' => Supplier::class,
                'auditable_id' => $s->id,
                'new_values' => ['name' => $name, 'phone' => $s->phone],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return $s;
        });

        return [
            'status' => 'success',
            'success' => true,
            'supplier_id' => $supplier->id,
            'name' => $supplier->name,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
            ],
            'contact_person' => $supplier->contact_person,
            'phone' => $supplier->phone,
            'message' => "Pemasok '{$supplier->name}' berhasil didaftarkan.",
        ];
    }
}
