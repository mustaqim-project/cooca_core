<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Sales\SalesPipelineService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

final class SalesManageQuotationTool implements McpToolInterface
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService()
    ) {}

    public function getName(): string
    {
        return 'sales_manage_quotation';
    }

    public function getDescription(): string
    {
        return 'Mengelola Surat Penawaran Harga (Quotation) untuk klien/customer B2B: buat penawaran baru dengan item & diskon, lihat daftar penawaran, dan 1-klik konversi penawaran yang disetujui menjadi Sales Order resmi.';
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
                    'enum' => ['list', 'create', 'convert_to_sales_order'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar penawaran), create (buat penawaran baru), convert_to_sales_order (1-klik ubah jadi Sales Order).',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'],
                    'description' => 'Filter status penawaran untuk action list.',
                ],
                'customer_id' => [
                    'type' => 'string',
                    'description' => 'ID Pelanggan / Klien (wajib untuk create jika tidak dicari via search).',
                ],
                'quotation_id' => [
                    'type' => 'string',
                    'description' => 'ID Penawaran (wajib untuk convert_to_sales_order).',
                ],
                'date' => [
                    'type' => 'string',
                    'description' => 'Tanggal terbit penawaran (YYYY-MM-DD). Default hari ini.',
                ],
                'expiry_date' => [
                    'type' => 'string',
                    'description' => 'Batas masa berlaku penawaran (YYYY-MM-DD).',
                ],
                'discount_amount' => [
                    'type' => 'number',
                    'description' => 'Potongan harga global penawaran (Rp).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan atau syarat penawaran untuk klien.',
                ],
                'terms_and_conditions' => [
                    'type' => 'string',
                    'description' => 'Syarat dan ketentuan pembayaran & pengiriman.',
                ],
                'items' => [
                    'type' => 'array',
                    'description' => 'Daftar item penawaran (wajib untuk create).',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => [
                                'type' => 'string',
                                'description' => 'ID Produk COOCA (opsional jika custom product_name).',
                            ],
                            'product_name' => [
                                'type' => 'string',
                                'description' => 'Nama produk atau layanan yang ditawarkan.',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Jumlah barang/jasa.',
                            ],
                            'unit_price' => [
                                'type' => 'number',
                                'description' => 'Harga satuan (Rp).',
                            ],
                            'discount_amount' => [
                                'type' => 'number',
                                'description' => 'Diskon per baris item (Rp).',
                            ],
                            'notes' => [
                                'type' => 'string',
                                'description' => 'Keterangan spesifikasi item.',
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
            'convert_to_sales_order' => $this->handleConvertToSalesOrder($business, $user, $arguments),
            default => throw new InvalidArgumentException("Aksi '{$action}' tidak didukung oleh sales_manage_quotation."),
        };
    }

    private function handleList(Business $business, array $args): array
    {
        $query = Quotation::where('business_id', $business->id)
            ->with(['customer:id,name,phone,email', 'items']);

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (! empty($args['customer_id'])) {
            $query->where('customer_id', $args['customer_id']);
        }

        $limit = min((int) ($args['limit'] ?? 20), 50);
        $quotations = $query->orderByDesc('created_at')->limit($limit)->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar penawaran harga.',
            'count' => $quotations->count(),
            'quotations' => $quotations->map(fn (Quotation $q) => [
                'id' => $q->id,
                'quotation_number' => $q->quotation_number,
                'status' => $q->status,
                'customer_name' => $q->customer?->name,
                'date' => $q->date ? Carbon::parse($q->date)->format('Y-m-d') : null,
                'expiry_date' => $q->expiry_date ? Carbon::parse($q->expiry_date)->format('Y-m-d') : null,
                'subtotal' => (float) $q->subtotal,
                'discount_amount' => (float) $q->discount_amount,
                'tax_amount' => (float) $q->tax_amount,
                'total_amount' => (float) $q->total_amount,
                'items_count' => $q->items->count(),
                'items' => $q->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => (float) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'discount_amount' => (float) $item->discount_amount,
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
                throw new InvalidArgumentException('Belum ada pelanggan terdaftar di bisnis ini. Daftarkan pelanggan terlebih dahulu.');
            }
        }

        $items = $args['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Penawaran harga harus memiliki minimal satu baris item.');
        }

        $quotation = $this->salesPipelineService->createQuotation($business, [
            'customer_id' => $customerId,
            'date' => $args['date'] ?? Carbon::today()->toDateString(),
            'expiry_date' => $args['expiry_date'] ?? Carbon::today()->addDays(14)->toDateString(),
            'discount_amount' => (float) ($args['discount_amount'] ?? 0),
            'notes' => $args['notes'] ?? null,
            'terms_and_conditions' => $args['terms_and_conditions'] ?? null,
            'status' => Quotation::STATUS_SENT,
            'items' => $items,
        ]);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_quotation',
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
            'old_values' => null,
            'new_values' => [
                'quotation_number' => $quotation->quotation_number,
                'customer_name' => $quotation->customer?->name,
                'total_amount' => $quotation->total_amount,
                'items_count' => $quotation->items->count(),
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Surat Penawaran {$quotation->quotation_number} berhasil diterbitkan.",
            'quotation' => [
                'id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'status' => $quotation->status,
                'customer_name' => $quotation->customer?->name,
                'total_amount' => (float) $quotation->total_amount,
                'expiry_date' => $quotation->expiry_date ? Carbon::parse($quotation->expiry_date)->format('Y-m-d') : null,
            ],
        ];
    }

    private function handleConvertToSalesOrder(Business $business, ?User $user, array $args): array
    {
        $quotationId = $args['quotation_id'] ?? null;
        if (! $quotationId) {
            throw new InvalidArgumentException('quotation_id wajib diisi untuk konversi ke Sales Order.');
        }

        $quotation = Quotation::where('business_id', $business->id)->with('items')->findOrFail($quotationId);

        $salesOrder = $this->salesPipelineService->convertQuotationToSalesOrder($quotation);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_convert_quotation_to_sales_order',
            'auditable_type' => Quotation::class,
            'auditable_id' => $quotation->id,
            'old_values' => ['status' => $quotation->getOriginal('status')],
            'new_values' => [
                'so_id' => $salesOrder->id,
                'so_number' => $salesOrder->so_number,
                'total_amount' => $salesOrder->total_amount,
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Penawaran {$quotation->quotation_number} berhasil dikonversi menjadi Pesanan Penjualan (SO): {$salesOrder->so_number}.",
            'sales_order' => [
                'id' => $salesOrder->id,
                'so_number' => $salesOrder->so_number,
                'status' => $salesOrder->status,
                'total_amount' => (float) $salesOrder->total_amount,
                'customer_name' => $salesOrder->customer?->name,
                'items_count' => $salesOrder->items->count(),
            ],
        ];
    }
}
