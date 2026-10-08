<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Material;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\ModifierOptionMaterial;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class ModifierManageTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'modifier_manage';
    }

    public function getDescription(): string
    {
        return 'Mengelola kelompok modifier (varian/topping/level pedas/ukuran) produk COOCA: melihat daftar modifier, membuat kelompok baru, menambahkan opsi varian harga, dan menautkan ke produk.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:products:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create_group', 'add_option', 'assign_products'],
                    'default' => 'list',
                    'description' => 'Aksi pengelolaan modifier: list (melihat daftar), create_group (buat grup modifier baru), add_option (tambah opsi pilihan), assign_products (tautkan grup ke produk).',
                ],
                'group_id' => [
                    'type' => 'string',
                    'description' => 'UUID grup modifier (diperlukan untuk add_option atau assign_products).',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Nama grup modifier (misal: "Level Pedas", "Pilihan Topping") atau nama opsi (misal: "Extra Shot", "Keju Mozzarella").',
                ],
                'selection_type' => [
                    'type' => 'string',
                    'enum' => ['single', 'multiple'],
                    'default' => 'single',
                    'description' => 'Jenis pemilihan opsi: single (pilih salah satu) atau multiple (bisa pilih banyak).',
                ],
                'min_selection' => [
                    'type' => 'integer',
                    'default' => 0,
                    'description' => 'Jumlah minimal pilihan yang wajib dipilih.',
                ],
                'max_selection' => [
                    'type' => 'integer',
                    'default' => 1,
                    'description' => 'Jumlah maksimal pilihan yang diperbolehkan.',
                ],
                'is_required' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Apakah pelanggan/kasir wajib memilih opsi dari kelompok ini.',
                ],
                'price_adjustment' => [
                    'type' => 'number',
                    'default' => 0,
                    'description' => 'Tambahan harga (Rp) jika opsi ini dipilih. Bernilai 0 jika gratis.',
                ],
                'material_id' => [
                    'type' => 'string',
                    'description' => 'Opsional UUID bahan baku yang akan dipotong saat opsi ini dipilih.',
                ],
                'material_quantity' => [
                    'type' => 'number',
                    'default' => 0,
                    'description' => 'Jumlah takaran bahan baku yang dipotong saat opsi ini dipilih.',
                ],
                'product_ids' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Daftar ID produk atau nama produk yang akan ditautkan ke grup modifier ini.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'list'));

        return match ($action) {
            'create_group'   => $this->createGroup($business, $user, $arguments),
            'add_option'     => $this->addOption($business, $user, $arguments),
            'assign_products'=> $this->assignProducts($business, $user, $arguments),
            default          => $this->listGroups($business),
        };
    }

    private function listGroups(Business $business): array
    {
        $groups = ModifierGroup::where('business_id', $business->id)
            ->with(['options.materials.material.unit', 'products:id,name,code'])
            ->orderBy('sort_order')
            ->get();

        $data = $groups->map(function (ModifierGroup $g) {
            return [
                'id' => $g->id,
                'name' => $g->name,
                'selection_type' => $g->selection_type,
                'is_required' => (bool) $g->is_required,
                'min_selection' => $g->min_selection,
                'max_selection' => $g->max_selection,
                'options_count' => $g->options->count(),
                'options' => $g->options->map(fn (ModifierOption $o) => [
                    'id' => $o->id,
                    'name' => $o->name,
                    'price_delta' => (float) $o->price_delta,
                    'price_formatted' => 'Rp ' . number_format((float) $o->price_delta, 0, ',', '.'),
                    'affects_material' => (bool) $o->affects_material,
                    'materials' => $o->materials->map(fn (ModifierOptionMaterial $m) => [
                        'material_name' => $m->material?->name,
                        'quantity' => (float) $m->quantity,
                        'unit' => $m->unit?->name ?? $m->material?->unit?->name,
                    ]),
                ]),
                'linked_products' => $g->products->map(fn (Product $p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->code,
                ]),
            ];
        });

        return [
            'status' => 'success',
            'success' => true,
            'total_groups' => $groups->count(),
            'count' => $groups->count(),
            'groups' => $data,
            'modifier_groups' => $data,
        ];
    }

    private function createGroup(Business $business, ?User $user, array $args): array
    {
        $name = trim((string) ($args['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Nama kelompok modifier (name) tidak boleh kosong.');
        }

        $type = in_array($args['selection_type'] ?? '', ['single', 'multiple'], true) ? $args['selection_type'] : 'single';
        $isRequired = (bool) ($args['is_required'] ?? false);
        $min = max(0, (int) ($args['min_selection'] ?? ($isRequired ? 1 : 0)));
        $max = max(1, (int) ($args['max_selection'] ?? 1));

        $group = DB::transaction(function () use ($business, $user, $name, $type, $isRequired, $min, $max, $args) {
            $g = ModifierGroup::create([
                'business_id' => $business->id,
                'name' => $name,
                'description' => trim((string) ($args['description'] ?? '')),
                'selection_type' => $type,
                'is_required' => $isRequired,
                'min_selection' => $min,
                'max_selection' => $max,
                'sort_order' => (int) ($args['sort_order'] ?? 0),
                'is_active' => true,
            ]);

            if (! empty($args['product_ids']) && is_array($args['product_ids'])) {
                $productIds = Product::where('business_id', $business->id)
                    ->where(function ($q) use ($args) {
                        $q->whereIn('id', $args['product_ids'])
                            ->orWhereIn('name', $args['product_ids']);
                    })
                    ->pluck('id')
                    ->all();
                $g->products()->sync($productIds);
            }

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.modifier_create_group',
                'auditable_type' => ModifierGroup::class,
                'auditable_id' => $g->id,
                'new_values' => ['name' => $name, 'selection_type' => $type],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return $g;
        });

        // If options were passed during create_group, create them automatically
        $optionsCreated = [];
        if (! empty($args['options']) && is_array($args['options'])) {
            foreach ($args['options'] as $opt) {
                if (! empty($opt['name'])) {
                    $optModel = ModifierOption::create([
                        'modifier_group_id' => $group->id,
                        'name' => trim((string) $opt['name']),
                        'price_delta' => (float) ($opt['price_delta'] ?? ($opt['price_adjustment'] ?? 0)),
                        'is_active' => true,
                    ]);
                    $optionsCreated[] = [
                        'id' => $optModel->id,
                        'name' => $optModel->name,
                        'price_delta' => (float) $optModel->price_delta,
                    ];
                }
            }
        }

        return [
            'status' => 'success',
            'success' => true,
            'group_id' => $group->id,
            'name' => $group->name,
            'selection_type' => $group->selection_type,
            'is_required' => $group->is_required,
            'modifier_group' => [
                'id' => $group->id,
                'name' => $group->name,
                'options' => $optionsCreated,
            ],
            'message' => "Kelompok modifier '{$group->name}' berhasil dibuat di COOCA.",
        ];
    }

    private function addOption(Business $business, ?User $user, array $args): array
    {
        $groupId = trim((string) ($args['group_id'] ?? ''));
        $name = trim((string) ($args['name'] ?? ''));

        if ($groupId === '' || $name === '') {
            throw new InvalidArgumentException('group_id dan name opsi modifier wajib diisi.');
        }

        $group = ModifierGroup::where('business_id', $business->id)
            ->where(function ($q) use ($groupId) {
                $q->where('id', $groupId)->orWhere('name', $groupId);
            })
            ->first();

        if (! $group) {
            throw new InvalidArgumentException("Grup modifier '{$groupId}' tidak ditemukan.");
        }

        $priceDelta = (float) ($args['price_adjustment'] ?? $args['price_delta'] ?? 0.0);
        $materialId = trim((string) ($args['material_id'] ?? ''));
        $materialQty = (float) ($args['material_quantity'] ?? 0.0);

        $option = DB::transaction(function () use ($business, $user, $group, $name, $priceDelta, $materialId, $materialQty) {
            $opt = ModifierOption::create([
                'modifier_group_id' => $group->id,
                'name' => $name,
                'price_delta' => $priceDelta,
                'affects_material' => ($materialId !== '' && $materialQty > 0),
                'sort_order' => $group->options()->count() + 1,
                'is_active' => true,
            ]);

            if ($materialId !== '' && $materialQty > 0) {
                $mat = Material::where('business_id', $business->id)
                    ->where(function ($q) use ($materialId) {
                        $q->where('id', $materialId)->orWhere('name', $materialId);
                    })
                    ->first();

                if ($mat) {
                    ModifierOptionMaterial::create([
                        'modifier_option_id' => $opt->id,
                        'material_id' => $mat->id,
                        'quantity' => $materialQty,
                        'unit_id' => $mat->unit_id,
                    ]);
                }
            }

            return $opt;
        });

        return [
            'status' => 'success',
            'option_id' => $option->id,
            'name' => $option->name,
            'price_delta' => $priceDelta,
            'group_name' => $group->name,
            'message' => "Opsi modifier '{$option->name}' (+Rp " . number_format($priceDelta, 0, ',', '.') . ") berhasil ditambahkan ke grup '{$group->name}'.",
        ];
    }

    private function assignProducts(Business $business, ?User $user, array $args): array
    {
        $groupId = trim((string) ($args['group_id'] ?? ''));
        $productInputs = (array) ($args['product_ids'] ?? []);

        if ($groupId === '' || empty($productInputs)) {
            throw new InvalidArgumentException('group_id dan product_ids wajib disertakan.');
        }

        $group = ModifierGroup::where('business_id', $business->id)
            ->where(function ($q) use ($groupId) {
                $q->where('id', $groupId)->orWhere('name', $groupId);
            })
            ->first();

        if (! $group) {
            throw new InvalidArgumentException("Grup modifier '{$groupId}' tidak ditemukan.");
        }

        $products = Product::where('business_id', $business->id)
            ->where(function ($q) use ($productInputs) {
                $q->whereIn('id', $productInputs)
                    ->orWhereIn('name', $productInputs);
            })
            ->get();

        $group->products()->syncWithoutDetaching($products->pluck('id')->all());

        return [
            'status' => 'success',
            'group_name' => $group->name,
            'assigned_products_count' => $products->count(),
            'products' => $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name]),
            'message' => "Grup modifier '{$group->name}' berhasil ditautkan ke {$products->count()} produk.",
        ];
    }
}
