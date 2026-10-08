<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Commerce\PurchaseOrderService;
use App\Domain\Purchasing\GoodsReceiptService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Location;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PurchasingManageOrderTool implements McpToolInterface
{
    public function __construct(
        private readonly PurchaseOrderService $poService = new PurchaseOrderService(),
        private readonly GoodsReceiptService $goodsReceiptService = new GoodsReceiptService()
    ) {}

    public function getName(): string
    {
        return 'purchasing_manage_order';
    }

    public function getDescription(): string
    {
        return 'Mengelola Purchase Order (PO) pembelian ke supplier & pesanan customer: buat PO baru (material/produk), lihat status PO, konfirmasi PO, dan catat penerimaan barang (Goods Receipt) ke gudang/outlet.';
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
                    'enum' => ['list', 'create', 'confirm', 'receive_goods'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar PO), create (buat PO baru), confirm (konfirmasi draft PO), receive_goods (terima barang masuk ke gudang).',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'confirmed', 'partially_invoiced', 'fully_invoiced', 'cancelled'],
                    'description' => 'Filter status saat list PO.',
                ],
                'po_type' => [
                    'type' => 'string',
                    'enum' => ['supplier', 'customer'],
                    'default' => 'supplier',
                    'description' => 'Jenis PO: supplier (pembelian ke vendor) atau customer (pesanan masuk dari klien).',
                ],
                'supplier_id' => [
                    'type' => 'string',
                    'description' => 'ID Supplier (wajib jika po_type supplier).',
                ],
                'customer_id' => [
                    'type' => 'string',
                    'description' => 'ID Customer (jika po_type customer).',
                ],
                'purchase_order_id' => [
                    'type' => 'string',
                    'description' => 'ID Purchase Order (wajib untuk confirm atau receive_goods).',
                ],
                'order_date' => [
                    'type' => 'string',
                    'description' => 'Tanggal order (format YYYY-MM-DD). Default hari ini.',
                ],
                'expected_delivery_date' => [
                    'type' => 'string',
                    'description' => 'Estimasi tanggal pengiriman/kedatangan barang (format YYYY-MM-DD).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan internal atau keterangan pesanan pembelian.',
                ],
                'items' => [
                    'type' => 'array',
                    'description' => 'Daftar item untuk create PO atau receive_goods.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'item_type' => [
                                'type' => 'string',
                                'enum' => ['material', 'product'],
                                'description' => 'Tipe item: material (bahan baku) atau product (produk jadi).',
                            ],
                            'material_id' => [
                                'type' => 'string',
                                'description' => 'ID Material jika item_type material.',
                            ],
                            'product_id' => [
                                'type' => 'string',
                                'description' => 'ID Product jika item_type product.',
                            ],
                            'item_name' => [
                                'type' => 'string',
                                'description' => 'Nama item (opsional jika material_id/product_id diberikan).',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Jumlah kuantitas barang.',
                            ],
                            'unit_price' => [
                                'type' => 'number',
                                'description' => 'Harga beli satuan (Rp).',
                            ],
                            'notes' => [
                                'type' => 'string',
                                'description' => 'Catatan per baris item.',
                            ],
                        ],
                        'required' => ['quantity'],
                    ],
                ],
                'location_id' => [
                    'type' => 'string',
                    'description' => 'ID Gudang / Outlet tujuan penerimaan barang (wajib untuk receive_goods).',
                ],
                'receipt_date' => [
                    'type' => 'string',
                    'description' => 'Tanggal penerimaan fisik barang (YYYY-MM-DD). Default hari ini.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'default' => 20,
                    'description' => 'Batas jumlah data untuk action list.',
                ],
            ],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $action = $arguments['action'] ?? 'list';

        return match ($action) {
            'list' => $this->handleList($business, $arguments),
            'create' => $this->handleCreate($business, $user, $arguments),
            'confirm' => $this->handleConfirm($business, $user, $arguments),
            'receive_goods' => $this->handleReceiveGoods($business, $user, $arguments),
            default => throw new InvalidArgumentException("Aksi '{$action}' tidak didukung oleh purchasing_manage_order."),
        };
    }

    private function handleList(Business $business, array $args): array
    {
        $query = PurchaseOrder::where('business_id', $business->id)
            ->with(['supplier:id,name,phone', 'customer:id,name,phone', 'items']);

        if (! empty($args['po_type'])) {
            $query->where('po_type', $args['po_type']);
        }

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (! empty($args['supplier_id'])) {
            $query->where('supplier_id', $args['supplier_id']);
        }

        $limit = min((int) ($args['limit'] ?? 20), 50);
        $orders = $query->orderByDesc('created_at')->limit($limit)->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar Purchase Order.',
            'count' => $orders->count(),
            'purchase_orders' => $orders->map(fn (PurchaseOrder $po) => [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'po_type' => $po->po_type,
                'status' => $po->status,
                'order_date' => $po->order_date ? Carbon::parse($po->order_date)->format('Y-m-d') : null,
                'expected_delivery_date' => $po->expected_delivery_date ? Carbon::parse($po->expected_delivery_date)->format('Y-m-d') : null,
                'supplier_name' => $po->supplier?->name,
                'customer_name' => $po->customer?->name,
                'subtotal' => (float) $po->subtotal,
                'tax_amount' => (float) $po->tax_amount,
                'total_amount' => (float) $po->total_amount,
                'notes' => $po->notes,
                'items_count' => $po->items->count(),
                'items' => $po->items->map(fn ($item) => [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'sku' => $item->sku,
                    'item_type' => $item->item_type,
                    'quantity' => (float) $item->quantity,
                    'received_quantity' => (float) ($item->received_quantity ?? 0),
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ])->all(),
        ];
    }

    private function handleCreate(Business $business, ?User $user, array $args): array
    {
        $items = $args['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Pembuatan Purchase Order membutuhkan minimal satu baris item.');
        }

        $poType = $args['po_type'] ?? PurchaseOrder::TYPE_SUPPLIER;
        $supplierId = $args['supplier_id'] ?? null;
        if ($poType === PurchaseOrder::TYPE_SUPPLIER && ! $supplierId) {
            // Find default or first supplier if not specified
            $supplierId = Supplier::where('business_id', $business->id)->first()?->id;
            if (! $supplierId) {
                throw new InvalidArgumentException('Belum ada supplier terdaftar di bisnis ini. Silakan buat supplier terlebih dahulu melalui purchasing_manage_supplier.');
            }
        }

        $poData = [
            'po_type' => $poType,
            'supplier_id' => $supplierId,
            'customer_id' => $args['customer_id'] ?? null,
            'order_date' => $args['order_date'] ?? Carbon::today()->toDateString(),
            'expected_delivery_date' => $args['expected_delivery_date'] ?? null,
            'notes' => $args['notes'] ?? null,
            'status' => PurchaseOrder::STATUS_CONFIRMED, // Direct confirmed for AI efficiency
            'created_by' => $user?->id,
        ];

        $po = $this->poService->createPurchaseOrder($business, $poData, $items);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_purchase_order',
            'auditable_type' => PurchaseOrder::class,
            'auditable_id' => $po->id,
            'old_values' => null,
            'new_values' => [
                'po_number' => $po->po_number,
                'total_amount' => $po->total_amount,
                'supplier' => $po->supplier?->name,
                'items_count' => count($items),
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Purchase Order {$po->po_number} berhasil dibuat dengan status {$po->status}.",
            'purchase_order' => [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'status' => $po->status,
                'total_amount' => (float) $po->total_amount,
                'supplier_name' => $po->supplier?->name,
                'items_count' => $po->items->count(),
            ],
        ];
    }

    private function handleConfirm(Business $business, ?User $user, array $args): array
    {
        $poId = $args['purchase_order_id'] ?? null;
        if (! $poId) {
            throw new InvalidArgumentException('purchase_order_id wajib diisi untuk konfirmasi PO.');
        }

        $po = PurchaseOrder::where('business_id', $business->id)->findOrFail($poId);
        $this->poService->confirm($po);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_confirm_purchase_order',
            'auditable_type' => PurchaseOrder::class,
            'auditable_id' => $po->id,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => PurchaseOrder::STATUS_CONFIRMED],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Purchase Order {$po->po_number} berhasil dikonfirmasi (CONFIRMED).",
            'purchase_order' => [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'status' => $po->status,
            ],
        ];
    }

    private function handleReceiveGoods(Business $business, ?User $user, array $args): array
    {
        $poId = $args['purchase_order_id'] ?? null;
        if (! $poId) {
            throw new InvalidArgumentException('purchase_order_id wajib diisi untuk penerimaan barang.');
        }

        $po = PurchaseOrder::where('business_id', $business->id)->with('items')->findOrFail($poId);

        $locationId = $args['location_id'] ?? null;
        if (! $locationId) {
            $locationId = Location::where('business_id', $business->id)->where('is_primary', true)->value('id')
                ?? Location::where('business_id', $business->id)->value('id');
            if (! $locationId) {
                throw new InvalidArgumentException('Gudang/Outlet penerimaan tidak ditemukan.');
            }
        }

        // If items are not provided, default to receiving all remaining items from PO
        $itemsToReceive = $args['items'] ?? [];
        if (empty($itemsToReceive)) {
            foreach ($po->items as $item) {
                $remQty = (float) $item->quantity - (float) ($item->received_quantity ?? 0);
                if ($remQty > 0) {
                    $itemsToReceive[] = [
                        'material_id' => $item->material_id,
                        'product_id' => $item->product_id,
                        'item_name' => $item->item_name,
                        'quantity' => $remQty,
                        'unit_cost' => (float) $item->unit_price,
                    ];
                }
            }
        } else {
            foreach ($itemsToReceive as &$recItem) {
                if (! isset($recItem['unit_cost'])) {
                    $sourcePoItem = $po->items->first(fn ($pi) =>
                        ($pi->material_id && $pi->material_id === ($recItem['material_id'] ?? null)) ||
                        ($pi->product_id && $pi->product_id === ($recItem['product_id'] ?? null)) ||
                        ($pi->item_name === ($recItem['item_name'] ?? null))
                    );
                    $recItem['unit_cost'] = $sourcePoItem ? (float) $sourcePoItem->unit_price : 0.0;
                }
            }
            unset($recItem);
        }

        if (empty($itemsToReceive)) {
            throw new InvalidArgumentException("Semua item pada Purchase Order {$po->po_number} sudah diterima sepenuhnya.");
        }

        $receiptData = [
            'location_id' => $locationId,
            'receipt_date' => $args['receipt_date'] ?? Carbon::today()->toDateString(),
            'notes' => $args['notes'] ?? "Penerimaan via MCP AI untuk PO {$po->po_number}",
            'items' => $itemsToReceive,
        ];

        $receipt = $this->goodsReceiptService->receive($po, $receiptData, $user?->id);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_receive_goods',
            'auditable_type' => PurchaseOrder::class,
            'auditable_id' => $po->id,
            'old_values' => null,
            'new_values' => [
                'receipt_number' => $receipt->receipt_number,
                'location_id' => $locationId,
                'items_received' => count($itemsToReceive),
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Barang berhasil diterima untuk PO {$po->po_number}. Nomor Penerimaan (GR): {$receipt->receipt_number}. Stok telah ditambahkan ke gudang.",
            'goods_receipt' => [
                'id' => $receipt->id,
                'receipt_number' => $receipt->receipt_number,
                'receipt_date' => $receipt->receipt_date,
                'status' => $receipt->status,
                'items_count' => count($itemsToReceive),
            ],
        ];
    }
}
