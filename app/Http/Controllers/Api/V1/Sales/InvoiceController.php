<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Sales;

use App\Domain\Commerce\InvoiceService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService = new InvoiceService
    ) {}

    /**
     * List invoices with KPI summary.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = Invoice::where('business_id', $business->id)
            ->with(['customer:id,name,company_name,phone', 'payments'])
            ->latest('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(20);

        // Financial KPIs
        $totalInvoiced = (float) Invoice::where('business_id', $business->id)->where('status', '!=', Invoice::STATUS_VOID)->sum('total_amount');
        $totalPaid = (float) Invoice::where('business_id', $business->id)->where('status', '!=', Invoice::STATUS_VOID)->sum('paid_amount');
        $totalReceivables = (float) Invoice::where('business_id', $business->id)
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_UNPAID, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE])
            ->sum('balance_due');
        $totalGrossProfit = (float) Invoice::where('business_id', $business->id)->where('status', '!=', Invoice::STATUS_VOID)->sum('total_gross_profit');

        return response()->json([
            'invoices' => $invoices->items(),
            'kpi' => [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'total_receivables' => $totalReceivables,
                'total_gross_profit' => $totalGrossProfit,
            ],
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Create invoice from line items.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,sent,unpaid'],
            'discount_type' => ['nullable', 'string', 'in:fixed,percentage'],
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'tax_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'shipping_cost' => ['nullable', 'numeric', 'gte:0'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'items.*.unit_hpp' => ['nullable', 'numeric', 'gte:0'],
        ]);

        /** @var Customer $customer */
        $customer = Customer::findOrFail($validated['customer_id']);
        $invoice = $this->invoiceService->createFromProducts($business, $customer, $validated['items'], $validated);

        return response()->json([
            'message' => "Faktur {$invoice->invoice_number} berhasil diterbitkan.",
            'invoice' => $invoice->load(['customer', 'items', 'payments']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show invoice detail with payment history.
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($invoice->business_id !== $business->id) {
            return response()->json(['message' => 'Faktur tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $invoice->load(['customer', 'items.product', 'items.unit', 'payments', 'purchaseOrder']);

        return response()->json([
            'invoice' => $invoice,
        ], Response::HTTP_OK);
    }

    /**
     * Record payment against invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($invoice->business_id !== $business->id) {
            return response()->json(['message' => 'Faktur tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $user = $request->user();

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,qris,credit,check,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $this->invoiceService->recordPayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            array_merge($validated, ['created_by' => $user?->id])
        );

        return response()->json([
            'message' => 'Pembayaran berhasil dicatat.',
            'payment' => $payment,
            'invoice' => $invoice->fresh(['payments']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Delete draft invoice.
     */
    public function destroy(Request $request, Invoice $invoice): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($invoice->business_id !== $business->id) {
            return response()->json(['message' => 'Faktur tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return response()->json(['message' => 'Hanya faktur berstatus draft yang dapat dihapus.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice->items()->delete();
        $invoice->delete();

        return response()->json([
            'message' => 'Faktur berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
