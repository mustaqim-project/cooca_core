<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Domain\Purchasing\SupplierInvoiceService;
use App\Http\Controllers\Controller;
use App\Models\SupplierInvoice;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class SupplierInvoiceController extends Controller
{
    public function __construct(private readonly SupplierInvoiceService $service = new SupplierInvoiceService) {}

    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $query = SupplierInvoice::with(['supplier', 'goodsReceipt', 'purchaseOrder'])
            ->where('business_id', $business->id)
            ->latest('invoice_date');
        if ($request->filled('status')) $query->where('status', $request->string('status')->toString());
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->string('supplier_id')->toString());
        if ($request->filled('search')) $query->where('invoice_number', 'like', '%' . $request->string('search')->toString() . '%');
        if ($request->filled('date_from')) $query->whereDate('invoice_date', '>=', $request->date('date_from'));
        if ($request->filled('date_to')) $query->whereDate('invoice_date', '<=', $request->date('date_to'));
        $invoices = $query->paginate(min(100, max(1, $request->integer('per_page', 20))));
        return response()->json([
            'supplier_invoices' => $invoices->items(),
            'summary' => [
                'total_amount' => (float) SupplierInvoice::where('business_id', $business->id)->sum('total_amount'),
                'paid_amount' => (float) SupplierInvoice::where('business_id', $business->id)->sum('paid_amount'),
                'balance_due' => (float) SupplierInvoice::where('business_id', $business->id)->sum('balance_due'),
            ],
            'pagination' => ['current_page' => $invoices->currentPage(), 'last_page' => $invoices->lastPage(), 'per_page' => $invoices->perPage(), 'total' => $invoices->total()],
        ]);
    }

    public function show(SupplierInvoice $supplierInvoice): JsonResponse
    {
        $this->guard($supplierInvoice);
        return response()->json(['supplier_invoice' => $supplierInvoice->load(['supplier', 'goodsReceipt', 'purchaseOrder', 'payments'])]);
    }

    public function recordPayment(Request $request, SupplierInvoice $supplierInvoice): JsonResponse
    {
        $this->guard($supplierInvoice);
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,qris'],
            'payment_number' => ['nullable', 'string', 'max:64'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        try {
            $payment = $this->service->recordPayment($supplierInvoice, array_merge($validated, ['created_by' => $request->user()?->id]));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return response()->json(['message' => 'Pembayaran supplier berhasil dicatat.', 'payment' => $payment, 'supplier_invoice' => $supplierInvoice->fresh(['supplier', 'payments'])], Response::HTTP_CREATED);
    }

    private function guard(SupplierInvoice $invoice): void
    {
        abort_unless($invoice->business_id === Context::requireBusiness()->id, Response::HTTP_NOT_FOUND);
    }
}
