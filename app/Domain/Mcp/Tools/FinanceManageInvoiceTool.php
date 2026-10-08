<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Commerce\InvoiceService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

final class FinanceManageInvoiceTool implements McpToolInterface
{
    public function __construct(
        private readonly InvoiceService $invoiceService = new InvoiceService()
    ) {}

    public function getName(): string
    {
        return 'finance_manage_invoice';
    }

    public function getDescription(): string
    {
        return 'Mengelola Faktur Tagihan Penjualan (Commercial Invoice): buat faktur baru, lihat daftar piutang & status tagihan, konfirmasi & potong stok gudang (confirm and release), serta catat pembayaran pelanggan (record payment) dengan penjurnalan otomatis.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:finance:manage';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'create', 'confirm_and_release', 'record_payment'],
                    'default' => 'list',
                    'description' => 'Aksi: list (daftar invoice), create (buat faktur baru), confirm_and_release (rilis faktur & potong stok), record_payment (catat pembayaran).',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'unpaid', 'partially_paid', 'paid', 'overdue', 'void'],
                    'description' => 'Filter status invoice untuk action list.',
                ],
                'customer_id' => [
                    'type' => 'string',
                    'description' => 'ID Pelanggan (wajib untuk create jika tidak otomatis).',
                ],
                'invoice_id' => [
                    'type' => 'string',
                    'description' => 'ID Invoice (wajib untuk confirm_and_release atau record_payment).',
                ],
                'location_id' => [
                    'type' => 'string',
                    'description' => 'ID Lokasi/Gudang pemotongan stok saat rilis invoice.',
                ],
                'amount' => [
                    'type' => 'number',
                    'description' => 'Nominal pembayaran yang diterima (Rp) untuk action record_payment.',
                ],
                'payment_method' => [
                    'type' => 'string',
                    'enum' => ['bank_transfer', 'cash', 'qris', 'credit_card', 'cheque'],
                    'default' => 'bank_transfer',
                    'description' => 'Metode pembayaran untuk action record_payment.',
                ],
                'reference_number' => [
                    'type' => 'string',
                    'description' => 'Nomor referensi / nomor mutasi bank.',
                ],
                'payment_date' => [
                    'type' => 'string',
                    'description' => 'Tanggal pembayaran (YYYY-MM-DD). Default hari ini.',
                ],
                'invoice_date' => [
                    'type' => 'string',
                    'description' => 'Tanggal invoice (YYYY-MM-DD). Default hari ini.',
                ],
                'due_date' => [
                    'type' => 'string',
                    'description' => 'Jatuh tempo pembayaran invoice (YYYY-MM-DD).',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Catatan invoice atau keterangan pembayaran.',
                ],
                'auto_release' => [
                    'type' => 'boolean',
                    'default' => true,
                    'description' => 'Jika true saat create, langsung rilis faktur dan potong stok inventori.',
                ],
                'items' => [
                    'type' => 'array',
                    'description' => 'Daftar item produk pada invoice (wajib untuk create).',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'product_id' => [
                                'type' => 'string',
                                'description' => 'ID Produk COOCA.',
                            ],
                            'item_name' => [
                                'type' => 'string',
                                'description' => 'Nama item jika tidak menggunakan ID Produk.',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'description' => 'Kuantitas barang.',
                            ],
                            'unit_price' => [
                                'type' => 'number',
                                'description' => 'Harga jual satuan (Rp).',
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Deskripsi baris item.',
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
            'confirm_and_release' => $this->handleConfirmAndRelease($business, $user, $arguments),
            'record_payment' => $this->handleRecordPayment($business, $user, $arguments),
            default => throw new InvalidArgumentException("Aksi '{$action}' tidak didukung oleh finance_manage_invoice."),
        };
    }

    private function handleList(Business $business, array $args): array
    {
        $query = Invoice::where('business_id', $business->id)
            ->with(['customer:id,name,phone,email', 'payments', 'items']);

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        if (! empty($args['customer_id'])) {
            $query->where('customer_id', $args['customer_id']);
        }

        $limit = min((int) ($args['limit'] ?? 20), 50);
        $invoices = $query->orderByDesc('created_at')->limit($limit)->get();

        return [
            'success' => true,
            'message' => 'Berhasil mengambil daftar Faktur Tagihan (Invoice).',
            'count' => $invoices->count(),
            'invoices' => $invoices->map(fn (Invoice $inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'status' => $inv->status,
                'customer_name' => $inv->customer?->name,
                'invoice_date' => $inv->invoice_date ? Carbon::parse($inv->invoice_date)->format('Y-m-d') : null,
                'due_date' => $inv->due_date ? Carbon::parse($inv->due_date)->format('Y-m-d') : null,
                'subtotal' => (float) $inv->subtotal,
                'tax_amount' => (float) $inv->tax_amount,
                'total_amount' => (float) $inv->total_amount,
                'paid_amount' => (float) $inv->paid_amount,
                'balance_due' => (float) $inv->balance_due,
                'payments_count' => $inv->payments->count(),
                'items_count' => $inv->items->count(),
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

        $customer = Customer::where('business_id', $business->id)->findOrFail($customerId);

        $items = $args['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException('Invoice harus memiliki minimal satu baris item.');
        }

        $locationId = $args['location_id'] ?? null;
        if (! $locationId) {
            $locationId = Location::where('business_id', $business->id)->where('is_primary', true)->value('id')
                ?? Location::where('business_id', $business->id)->value('id');
        }

        $attributes = [
            'invoice_date' => $args['invoice_date'] ?? Carbon::today()->toDateString(),
            'due_date' => $args['due_date'] ?? null,
            'location_id' => $locationId,
            'notes' => $args['notes'] ?? null,
            'created_by' => $user?->id,
        ];

        $invoice = $this->invoiceService->createFromProducts($business, $customer, $items, $attributes);

        // Auto release if requested (deducts stock and records journals)
        $autoRelease = $args['auto_release'] ?? true;
        if ($autoRelease) {
            $invoice = $this->invoiceService->confirmAndRelease($invoice, $locationId);
        }

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_create_invoice',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'old_values' => null,
            'new_values' => [
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $customer->name,
                'total_amount' => $invoice->total_amount,
                'status' => $invoice->status,
                'released' => $autoRelease,
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Faktur Tagihan {$invoice->invoice_number} berhasil diterbitkan (Status: {$invoice->status}).",
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'customer_name' => $customer->name,
                'total_amount' => (float) $invoice->total_amount,
                'balance_due' => (float) $invoice->balance_due,
                'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
            ],
        ];
    }

    private function handleConfirmAndRelease(Business $business, ?User $user, array $args): array
    {
        $invoiceId = $args['invoice_id'] ?? null;
        if (! $invoiceId) {
            throw new InvalidArgumentException('invoice_id wajib diisi untuk konfirmasi dan pelepasan faktur.');
        }

        $invoice = Invoice::where('business_id', $business->id)->findOrFail($invoiceId);
        $locationId = $args['location_id'] ?? $invoice->location_id;

        $invoice = $this->invoiceService->confirmAndRelease($invoice, $locationId);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_confirm_and_release_invoice',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => $invoice->status, 'location_id' => $locationId],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Invoice {$invoice->invoice_number} berhasil dikonfirmasi dan dirilis. Stok gudang telah dipotong dan jurnal penjualan dicatat.",
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'balance_due' => (float) $invoice->balance_due,
            ],
        ];
    }

    private function handleRecordPayment(Business $business, ?User $user, array $args): array
    {
        $invoiceId = $args['invoice_id'] ?? null;
        $amount = (float) ($args['amount'] ?? 0);

        if (! $invoiceId || $amount <= 0) {
            throw new InvalidArgumentException('invoice_id dan amount (> 0) wajib diisi untuk mencatat pembayaran.');
        }

        $invoice = Invoice::where('business_id', $business->id)->findOrFail($invoiceId);

        $paymentMethod = $args['payment_method'] ?? InvoicePayment::METHOD_BANK_TRANSFER;
        $paymentAttributes = [
            'payment_date' => $args['payment_date'] ?? Carbon::today()->toDateString(),
            'reference_number' => $args['reference_number'] ?? null,
            'notes' => $args['notes'] ?? 'Pembayaran dicatat via MCP AI',
            'created_by' => $user?->id,
        ];

        $payment = $this->invoiceService->recordPayment($invoice, $amount, $paymentMethod, $paymentAttributes);
        $invoice->refresh();

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'action' => 'mcp_record_invoice_payment',
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'old_values' => null,
            'new_values' => [
                'payment_number' => $payment->payment_number,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'new_balance_due' => $invoice->balance_due,
                'invoice_status' => $invoice->status,
            ],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'COOCA-MCP-Agent/1.0',
        ]);

        return [
            'success' => true,
            'message' => "Pembayaran Rp " . number_format($amount, 0, ',', '.') . " berhasil dicatat untuk Invoice {$invoice->invoice_number}. Status faktur sekarang: {$invoice->status}.",
            'payment' => [
                'payment_number' => $payment->payment_number,
                'amount' => (float) $payment->amount,
                'payment_method' => $payment->payment_method,
            ],
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'status' => $invoice->status,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance_due' => (float) $invoice->balance_due,
            ],
        ];
    }
}
