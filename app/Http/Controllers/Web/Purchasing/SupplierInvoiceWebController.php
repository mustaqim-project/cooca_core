<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Purchasing;

use App\Domain\Purchasing\SupplierInvoiceService;
use App\Http\Controllers\Controller;
use App\Models\SupplierInvoice;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

final class SupplierInvoiceWebController extends Controller
{
    public function __construct(
        private readonly SupplierInvoiceService $supplierInvoiceService = new SupplierInvoiceService,
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $query = SupplierInvoice::query()->with('supplier')->latest('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('app.purchasing.bills.index', [
            'business' => $business,
            'invoices' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function show(SupplierInvoice $invoice): View
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        return view('app.purchasing.bills.show', [
            'business' => $business,
            'invoice' => $invoice->load(['supplier', 'goodsReceipt', 'purchaseOrder', 'payments']),
        ]);
    }

    public function recordPayment(Request $request, SupplierInvoice $invoice): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($invoice->business_id === $business->id, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,qris'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->supplierInvoiceService->recordPayment($invoice, array_merge($validated, [
                'created_by' => auth()->id(),
            ]));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['amount' => $exception->getMessage()]);
        }

        return redirect()->route('purchasing.bills.show', $invoice)
            ->with('success', 'Pembayaran hutang supplier berhasil dicatat.');
    }
}
