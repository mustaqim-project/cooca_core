<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class InventoryStockOpnameTool implements McpToolInterface
{
    public function getName(): string
    {
        return 'inventory_stock_opname';
    }

    public function getDescription(): string
    {
        return 'Mengelola stok fisik gudang/outlet: melakukan penyesuaian stok opname (stock opname variance/rusak/hilang), transfer stok antar cabang, dan cek kartu saldo stok aktual.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:inventory:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['adjust', 'transfer', 'list_stocks'],
                    'default' => 'adjust',
                    'description' => 'Aksi: adjust (catat hasil stock opname fisik & sesuaikan stok), transfer (transfer stok antar cabang/gudang), list_stocks (lihat posisi stok barang di semua lokasi).',
                ],
                'product_id' => [
                    'type' => 'string',
                    'description' => 'UUID, kode SKU, atau nama produk/bahan baku.',
                ],
                'is_material' => [
                    'type' => 'boolean',
                    'default' => false,
                    'description' => 'Set true jika yang disesuaikan adalah bahan baku (material), bukan produk jadi.',
                ],
                'location_id' => [
                    'type' => 'string',
                    'description' => 'UUID atau nama lokasi/cabang usaha (opsional, jika kosong menggunakan cabang utama).',
                ],
                'physical_quantity' => [
                    'type' => 'number',
                    'description' => 'Jumlah fisik aktual hasil hitung opname di gudang/toko (wajib untuk adjust).',
                ],
                'reason_code' => [
                    'type' => 'string',
                    'enum' => ['opname_variance', 'damaged', 'expired', 'initial_balance', 'theft_loss', 'other'],
                    'default' => 'opname_variance',
                    'description' => 'Alasan penyesuaian: opname_variance (selisih opname), damaged (barang rusak), expired (kedaluwarsa), initial_balance (saldo awal), theft_loss (kehilangan), other (lainnya).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan keterangan hasil opname atau alasan penyesuaian stok.',
                ],
                'from_location_id' => [
                    'type' => 'string',
                    'description' => 'Lokasi/gudang asal pengiriman (wajib untuk transfer).',
                ],
                'to_location_id' => [
                    'type' => 'string',
                    'description' => 'Lokasi/gudang tujuan penerimaan (wajib untuk transfer).',
                ],
                'transfer_quantity' => [
                    'type' => 'number',
                    'description' => 'Jumlah kuantitas stok yang ditransfer (wajib untuk transfer).',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = trim((string) ($arguments['action'] ?? 'adjust'));

        return match ($action) {
            'transfer'    => $this->transferStock($business, $user, $arguments),
            'list_stocks' => $this->listStocks($business, $arguments),
            default       => $this->adjustStock($business, $user, $arguments),
        };
    }

    private function resolveLocation(Business $business, ?string $identifier): Location
    {
        if ($identifier) {
            $loc = Location::where('business_id', $business->id)
                ->where(function ($q) use ($identifier) {
                    $q->where('id', $identifier)->orWhere('name', $identifier);
                })
                ->first();
            if ($loc) {
                return $loc;
            }
        }

        $default = Location::where('business_id', $business->id)->where('is_primary', true)->first()
            ?? Location::where('business_id', $business->id)->first();

        if (! $default) {
            throw new InvalidArgumentException('Bisnis belum memiliki lokasi/cabang terdaftar.');
        }

        return $default;
    }

    private function adjustStock(Business $business, ?User $user, array $args): array
    {
        // Support batch or item payload in args['items']
        $firstItem = ! empty($args['items']) && is_array($args['items']) ? (array) $args['items'][0] : [];

        $targetId = trim((string) (
            $args['product_id']
            ?? $args['material_id']
            ?? $args['item_id']
            ?? ($firstItem['product_id'] ?? ($firstItem['material_id'] ?? ($firstItem['item_id'] ?? '')))
        ));

        if ($targetId === '') {
            throw new InvalidArgumentException('product_id atau material_id wajib diisi untuk penyesuaian opname.');
        }

        $location = $this->resolveLocation($business, $args['location_id'] ?? ($firstItem['location_id'] ?? null));

        $isMaterial = (bool) (
            $args['is_material']
            ?? (! empty($args['material_id']) || ! empty($firstItem['material_id']))
        );

        $reason = in_array($args['reason_code'] ?? ($args['reason'] ?? ''), ['opname_variance', 'damaged', 'expired', 'initial_balance', 'theft_loss', 'other'], true)
            ? ($args['reason_code'] ?? $args['reason'])
            : 'opname_variance';
        $notes = trim((string) ($args['notes'] ?? ($firstItem['notes'] ?? 'Stock opname via AI MCP')));

        $itemModel = null;
        $itemName = '';
        $unitCost = 0.0;
        $unitName = 'unit';

        if ($isMaterial) {
            $itemModel = Material::where('business_id', $business->id)
                ->where(function ($q) use ($targetId) {
                    $q->where('id', $targetId)->orWhere('code', $targetId)->orWhere('name', $targetId);
                })->first();
            if (! $itemModel) {
                throw new InvalidArgumentException("Bahan baku '{$targetId}' tidak ditemukan.");
            }
            $itemName = $itemModel->name;
            $unitCost = $itemModel->latestPrice ? (float) $itemModel->latestPrice->effective_cost : 0.0;
            $unitName = $itemModel->unit?->name ?? 'unit';
        } else {
            $itemModel = Product::where('business_id', $business->id)
                ->where(function ($q) use ($targetId) {
                    $q->where('id', $targetId)->orWhere('code', $targetId)->orWhere('name', $targetId);
                })->first();
            if (! $itemModel) {
                throw new InvalidArgumentException("Produk '{$targetId}' tidak ditemukan.");
            }
            $itemName = $itemModel->name;
            $unitCost = (float) $itemModel->base_cost;
            $unitName = $itemModel->outputUnit?->name ?? 'unit';
        }

        $rawPhysical = $args['physical_quantity'] ?? ($firstItem['physical_quantity'] ?? null);
        $deltaQuantity = $args['quantity'] ?? ($firstItem['quantity'] ?? null);
        $deltaType = $args['type'] ?? ($firstItem['type'] ?? 'subtraction');

        $stockRecord = DB::transaction(function () use ($business, $user, $location, $itemModel, $isMaterial, $rawPhysical, $deltaQuantity, $deltaType, $unitCost, $reason, $notes, $itemName) {
            $query = InventoryStock::where('business_id', $business->id)
                ->where('location_id', $location->id);

            if ($isMaterial) {
                $query->where('material_id', $itemModel->id);
            } else {
                $query->where('product_id', $itemModel->id);
            }

            $stock = $query->first();
            $systemQty = $stock ? (float) $stock->quantity : 0.0;

            if ($rawPhysical !== null) {
                $physicalQty = max(0.0, (float) $rawPhysical);
            } elseif ($deltaQuantity !== null) {
                if ($deltaType === 'subtraction') {
                    $physicalQty = max(0.0, $systemQty - (float) $deltaQuantity);
                } else {
                    $physicalQty = $systemQty + (float) $deltaQuantity;
                }
            } else {
                throw new InvalidArgumentException('physical_quantity atau quantity selisih wajib diisi.');
            }

            $diff = $physicalQty - $systemQty;

            if (! $stock) {
                $stock = InventoryStock::create([
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $isMaterial ? $itemModel->id : null,
                    'product_id' => ! $isMaterial ? $itemModel->id : null,
                    'quantity' => $physicalQty,
                    'reserved_quantity' => 0.0,
                    'last_cost' => $unitCost,
                    'avg_purchase_cost' => $unitCost,
                ]);
            } else {
                $stock->update([
                    'quantity' => $physicalQty,
                    'last_cost' => $unitCost > 0 ? $unitCost : $stock->last_cost,
                ]);
            }

            // Create formal Stock Adjustment audit record
            $adj = StockAdjustment::create([
                'business_id' => $business->id,
                'location_id' => $location->id,
                'user_id' => $user?->id,
                'adjustment_number' => 'ADJ-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'adjustment_date' => now()->toDateString(),
                'reason' => $reason,
                'notes' => $notes,
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            StockAdjustmentItem::create([
                'stock_adjustment_id' => $adj->id,
                'product_id' => ! $isMaterial ? $itemModel->id : null,
                'material_id' => $isMaterial ? $itemModel->id : null,
                'system_quantity' => $systemQty,
                'physical_quantity' => $physicalQty,
                'variance_quantity' => $diff,
                'unit_cost' => $unitCost,
                'total_cost' => abs($diff * $unitCost),
            ]);

            // Record Movement
            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $location->id,
                'product_id' => ! $isMaterial ? $itemModel->id : null,
                'material_id' => $isMaterial ? $itemModel->id : null,
                'movement_type' => StockMovement::TYPE_OPNAME,
                'reference_id' => $adj->id,
                'reference_number' => $adj->adjustment_number,
                'quantity_change' => $diff,
                'balance_after' => $physicalQty,
                'unit_cost' => $unitCost,
                'total_cost' => $diff * $unitCost,
                'notes' => "[{$reason}] {$notes}",
                'created_by' => $user?->id,
            ]);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.stock_opname_adjust',
                'auditable_type' => InventoryStock::class,
                'auditable_id' => $stock->id,
                'new_values' => ['system_qty' => $systemQty, 'physical_qty' => $physicalQty, 'diff' => $diff],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return [
                'system_qty' => $systemQty,
                'physical_qty' => $physicalQty,
                'variance' => $diff,
                'adj_number' => $adj->adjustment_number,
            ];
        });

        $diffText = $stockRecord['variance'] >= 0 ? "+{$stockRecord['variance']}" : "{$stockRecord['variance']}";

        return [
            'status' => 'success',
            'success' => true,
            'item_name' => $itemName,
            'location' => $location->name,
            'previous_system_qty' => $stockRecord['system_qty'],
            'new_physical_qty' => $stockRecord['physical_qty'],
            'variance' => $diffText,
            'unit' => $unitName,
            'reason' => $reason,
            'adjustment_number' => $stockRecord['adj_number'],
            'adjustment' => [
                'adjustment_number' => $stockRecord['adj_number'],
                'system_quantity' => $stockRecord['system_qty'],
                'physical_quantity' => $stockRecord['physical_qty'],
                'variance_quantity' => $stockRecord['variance'],
            ],
            'message' => "Stok {$itemName} di {$location->name} berhasil disesuaikan menjadi {$stockRecord['physical_qty']} {$unitName} (Selisih: {$diffText}). Nomor Audit: {$stockRecord['adj_number']}.",
        ];
    }

    private function transferStock(Business $business, ?User $user, array $args): array
    {
        $targetId = trim((string) ($args['product_id'] ?? ''));
        $qty = (float) ($args['transfer_quantity'] ?? 0.0);

        if ($targetId === '' || $qty <= 0) {
            throw new InvalidArgumentException('product_id dan transfer_quantity (> 0) wajib diisi.');
        }

        $fromLoc = $this->resolveLocation($business, $args['from_location_id'] ?? null);
        $toLoc = $this->resolveLocation($business, $args['to_location_id'] ?? null);

        if ($fromLoc->id === $toLoc->id) {
            throw new InvalidArgumentException('Lokasi asal dan lokasi tujuan transfer tidak boleh sama.');
        }

        $isMaterial = (bool) ($args['is_material'] ?? false);
        $itemModel = $isMaterial
            ? Material::where('business_id', $business->id)->where(fn ($q) => $q->where('id', $targetId)->orWhere('name', $targetId))->first()
            : Product::where('business_id', $business->id)->where(fn ($q) => $q->where('id', $targetId)->orWhere('name', $targetId)->orWhere('code', $targetId))->first();

        if (! $itemModel) {
            throw new InvalidArgumentException("Item '{$targetId}' tidak ditemukan.");
        }

        $res = DB::transaction(function () use ($business, $user, $fromLoc, $toLoc, $itemModel, $isMaterial, $qty) {
            // Source stock
            $fromStock = InventoryStock::where('business_id', $business->id)
                ->where('location_id', $fromLoc->id)
                ->where($isMaterial ? 'material_id' : 'product_id', $itemModel->id)
                ->first();

            if (! $fromStock || (float) $fromStock->quantity < $qty) {
                $avail = $fromStock ? (float) $fromStock->quantity : 0.0;
                throw new InvalidArgumentException("Stok di {$fromLoc->name} tidak mencukupi (Tersedia: {$avail}, Diminta: {$qty}).");
            }

            // Deduct from source
            $fromStock->decrement('quantity', $qty);

            // Add to destination
            $toStock = InventoryStock::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $toLoc->id,
                    $isMaterial ? 'material_id' : 'product_id' => $itemModel->id,
                ],
                [
                    'quantity' => 0.0,
                    'reserved_quantity' => 0.0,
                    'last_cost' => $fromStock->last_cost,
                    'avg_purchase_cost' => $fromStock->avg_purchase_cost,
                ]
            );
            $toStock->increment('quantity', $qty);

            // Record Transfer Movements
            $refNumber = 'TRF-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $fromLoc->id,
                $isMaterial ? 'material_id' : 'product_id' => $itemModel->id,
                'movement_type' => StockMovement::TYPE_TRANSFER_OUT,
                'reference_number' => $refNumber,
                'quantity_change' => -$qty,
                'balance_after' => (float) $fromStock->fresh()->quantity,
                'notes' => "Transfer ke {$toLoc->name}",
                'created_by' => $user?->id,
            ]);

            StockMovement::create([
                'business_id' => $business->id,
                'location_id' => $toLoc->id,
                $isMaterial ? 'material_id' : 'product_id' => $itemModel->id,
                'movement_type' => StockMovement::TYPE_TRANSFER_IN,
                'reference_number' => $refNumber,
                'quantity_change' => $qty,
                'balance_after' => (float) $toStock->fresh()->quantity,
                'notes' => "Transfer dari {$fromLoc->name}",
                'created_by' => $user?->id,
            ]);

            return [
                'ref' => $refNumber,
                'from_balance' => (float) $fromStock->fresh()->quantity,
                'to_balance' => (float) $toStock->fresh()->quantity,
            ];
        });

        return [
            'status' => 'success',
            'item_name' => $itemModel->name,
            'quantity_transferred' => $qty,
            'from_location' => $fromLoc->name,
            'to_location' => $toLoc->name,
            'remaining_at_origin' => $res['from_balance'],
            'new_at_destination' => $res['to_balance'],
            'transfer_number' => $res['ref'],
            'message' => "Stok {$itemModel->name} sebanyak {$qty} berhasil ditransfer dari {$fromLoc->name} ke {$toLoc->name}.",
        ];
    }

    private function listStocks(Business $business, array $args): array
    {
        $query = InventoryStock::where('business_id', $business->id)
            ->with(['location:id,name', 'product:id,name,code,base_cost,selling_price', 'material:id,name,code']);

        if (! empty($args['location_id'])) {
            $loc = $this->resolveLocation($business, $args['location_id']);
            $query->where('location_id', $loc->id);
        }

        $stocks = $query->limit(50)->get();

        $data = $stocks->map(function (InventoryStock $s) {
            $name = $s->product?->name ?? $s->material?->name ?? 'Unknown';
            $code = $s->product?->code ?? $s->material?->code ?? '-';

            return [
                'type' => $s->product_id ? 'product' : 'material',
                'name' => $name,
                'code' => $code,
                'location' => $s->location?->name,
                'quantity' => (float) $s->quantity,
                'valuation' => round((float) $s->quantity * (float) $s->last_cost, 2),
            ];
        });

        return [
            'status' => 'success',
            'total_records' => $stocks->count(),
            'stocks' => $data,
        ];
    }
}
