<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Sales\SalesPipelineService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\SalesOrder;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

final class SalesManageOrderTool implements McpToolInterface
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService()
    ) {}

    public function getName(): string
    {
        return 'sales_manage_order';
    }

    public function getDescription(): string
    {
        return 'Mengelola Pesanan Penjualan (Sales Order / SO): buat order penjualan baru dari klien, lihat status pesanan, update status (confirmed/cancelled), dan 1-klik terbitkan Faktur Tagihan resmi (Invoice).';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:sales:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create', 'convert_to_invoice', 'update_status'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar SO), create (buat SO baru), convert_to_invoice (1-klik terbitkan tagihan/invoice), update_status (perbarui status SO).',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'confirmed', 'partially_fulfilled', 'fulfilled', 'cancelled'],
                    'description' => 'Filter status untuk list atau status baru untuk update_status.',
                ],
                'customer_id' => [
                    'type' => 'string',
                    'description' => 'ID Pelanggan (wajib untuk create jika tidak otomatis).',
                ],
                'sales_order_id' => [
                    'type' => 'string',
                    'description' => 'ID Sales Order (wajib untuk convert_to_invoice atau update_status).',
                ],
                'order_date' => [
                    'type' => 'string',
                    'description' => 'Tanggal pemesanan (YYYY-MM-DD). Default hari ini.',
                ],
                'expected_delivery_date' => [
                    'type' => 'string',
                    'description' => 'Estimasi tanggal pengiriman (YYYY-MM-DD).',
                ],
                'shipping_address' => [
                    'type' => 'string',
                    'description' => 'Alamat pengiriman pesanan.',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan pesanan penjualan.',
                ],
                'discount_amount' => [
                    'type' => 'number',
                    'description' => 'Diskon global (Rp).',
                ],
                'items' => [
                    'type' => 'array',
                    'description' => 'Daftar item pesanan penjualan (wajib untuk create).',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => [
                                'type' => 'string',
                                'description' => 'ID Produk COOCA (opsional).',
                            ],
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Nama produk / jasa.',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Jumlah pesanan.',
                            ],
                            'unit_price' => [
                                'type' => 'number',
                                'description' => 'Harga jual satuan (Rp).',
                            ],
                            'discount_amount' => [
                                'type' => 'number',
                                'description' => 'Diskon per baris item (Rp).',
                            ],
                            'notes' => [
                                'type' => 'string',
                                'description' => 'Keterangan item.',
                            ],
                        ],
                        'required' => ['quantity', 'unit_price'],
                    ],
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
            'convert_to_invoice' => $this->handleConvertToInvoice($business, $user, $arguments),
            'update_status' => $this->handleUpdateStatus($business, $user, $arguments),
            default => throw new InvalidArgumentException("Aksi '{$action}' tidak didukung oleh sales_manage_order."),
        };
    }

    private function handleList(Business $business, array $args): array
    {
        $query = SalesOrder::where('business_id', $business->id)
            ->with(['customer:id,name,phone,email', 'items']);

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (! empty($args['customer_id'])) {
            $query->where('customer_id', $args['customer_id']);
        }

        $limit = min((int) ($args['limit'] ?? 20), 50);
        $orders = $query->orderByDesc('created_at')->limit($limit)->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar Sales Order.',
            'count' => $orders->count(),
            'sales_orders' => $orders->map(fn (SalesOrder $so) => [
                'id' => $so->id,
                'so_number' => $so->so_number,
                'status' => $so->status,
                'customer_name' => $so->customer?->name,
                'order_date' => $so->order_date ? Carbon::parse($so->order_date)->format('Y-m-d') : null,
                'expected_delivery_date' => $so->expected_delivery_date ? Carbon::parse($so->expected_delivery_date)->format('Y-m-d') : null,
                'subtotal' => (float) $so->subtotal,
                'discount_amount' => (float) $so->discount_amount,
                'tax_amount' => (float) $so->tax_amount,
                'total_amount' => (float) $so->total_amount,
                'items_count' => $so->items->count(),
                'items' => $so->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'fulfilled_quantity' => (float) $item->fulfilled_quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ])->all(),
        ];
    }

    private function handleCreate(Business $business, ?User $user, array $args): array
    {
        $customerId = $args['customer_id'] ?? null;
        if (! $customerId) {
            $customerId = Customer::where('business_id', $business->id)->first()?->id;
            if (! $customerId) {
                throw new InvalidArgumentException('Belum ada pelanggan terdaftar di bisnis ini.');
            }
        }

        $items = $args['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Pesanan penjualan harus memiliki minimal satu baris item.');
        }

        $salesOrder = $this->salesPipelineService->createSalesOrder($business, [
            'customer_id' => $customerId,
            'order_date' => $args['order_date'] ?? Carbon::today()->toDateString(),
            'expected_delivery_date' => $args['expected_delivery_date'] ?? null,
            'discount_amount' => (float) ($args['discount_amount'] ?? 0),
            'shipping_address' => $args['shipping_address'] ?? null,
            'notes' => $args['notes'] ?? null,
            'status' => SalesOrder::STATUS_CONFIRMED,
            'items' => $items,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_sales_order',
            'auditable_type' => SalesOrder::class,
            'auditable_id' => $salesOrder->id,
            'old_values' => null,
            'new_values' => [
                'so_number' => $salesOrder->so_number,
                'customer_name' => $salesOrder->customer?->name,
                'total_amount' => $salesOrder->total_amount,
                'items_count' => $salesOrder->items->count(),
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Sales Order {$salesOrder->so_number} berhasil dibuat (CONFIRMED).",
            'sales_order' => [
                'id' => $salesOrder->id,
                'so_number' => $salesOrder->so_number,
                'status' => $salesOrder->status,
                'customer_name' => $salesOrder->customer?->name,
                'total_amount' => (float) $salesOrder->total_amount,
                'items_count' => $salesOrder->items->count(),
            ],
        ];
    }

    private function handleConvertToInvoice(Business $business, ?User $user, array $args): array
    {
        $soId = $args['sales_order_id'] ?? null;
        if (! $soId) {
            throw new InvalidArgumentException('sales_order_id wajib diisi untuk konversi ke Invoice.');
        }

        $salesOrder = SalesOrder::where('business_id', $business->id)->with('items')->findOrFail($soId);

        $invoice = $this->salesPipelineService->generateInvoiceFromSalesOrder($salesOrder);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_convert_so_to_invoice',
            'auditable_type' => SalesOrder::class,
            'auditable_id' => $salesOrder->id,
            'old_values' => null,
            'new_values' => [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total_amount' => $invoice->total_amount,
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Tagihan resmi {$invoice->invoice_number} berhasil diterbitkan dari Sales Order {$salesOrder->so_number}.",
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'customer_name' => $invoice->customer?->name,
                'total_amount' => (float) $invoice->total_amount,
                'balance_due' => (float) $invoice->balance_due,
                'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
            ],
        ];
    }

    private function handleUpdateStatus(Business $business, ?User $user, array $args): array
    {
        $soId = $args['sales_order_id'] ?? null;
        $status = $args['status'] ?? null;

        if (! $soId || ! $status) {
            throw new InvalidArgumentException('sales_order_id dan status wajib diisi untuk update status.');
        }

        $salesOrder = SalesOrder::where('business_id', $business->id)->findOrFail($soId);
        $oldStatus = $salesOrder->status;
        $salesOrder->update(['status' => $status]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_update_sales_order_status',
            'auditable_type' => SalesOrder::class,
            'auditable_id' => $salesOrder->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $status],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Status Sales Order {$salesOrder->so_number} berhasil diubah dari '{$oldStatus}' menjadi '{$status}'.",
            'sales_order' => [
                'id' => $salesOrder->id,
                'so_number' => $salesOrder->so_number,
                'status' => $salesOrder->status,
            ],
        ];
    }
}
