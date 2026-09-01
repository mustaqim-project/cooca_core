<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Sales;

use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SalesOrderWebController extends Controller
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = SalesOrder::with('customer', 'items')
            ->where('business_id', $business->id)
            ->latest('order_date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $salesOrders = $query->paginate(15)->withQueryString();

        return view('app.sales-orders.index', compact('business', 'salesOrders'));
    }

    public function show(SalesOrder $salesOrder): View
    {
        $business = Context::requireBusiness();
        abort_unless($salesOrder->business_id === $business->id, 403);

        $salesOrder->load('customer', 'items.product', 'quotation', 'invoices');

        return view('app.sales-orders.show', compact('business', 'salesOrder'));
    }

    public function generateInvoice(Request $request, SalesOrder $salesOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($salesOrder->business_id === $business->id, 403);

        $invoice = $this->salesPipelineService->generateInvoiceFromSalesOrder($salesOrder);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Faktur Penjualan {$invoice->invoice_number} berhasil diterbitkan dari Pesanan {$salesOrder->so_number}.");
    }
}
