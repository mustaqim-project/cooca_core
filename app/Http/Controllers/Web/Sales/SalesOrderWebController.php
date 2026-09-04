<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Sales;

use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
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

    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        $customers  = Customer::where('business_id', $business->id)->orderBy('name')->get();
        $products   = Product::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->salesPipelineService->generateSalesOrderNumber($business);

        // Quotations yang belum dikonversi ke SO
        $quotations = Quotation::with('customer', 'items')
            ->where('business_id', $business->id)
            ->whereDoesntHave('salesOrder')
            ->whereIn('status', [Quotation::STATUS_SENT, Quotation::STATUS_ACCEPTED])
            ->orderByDesc('date')
            ->get();

        // Pre-fill dari query string ?quotation_id=xxx
        $prefillQuotation = null;
        if ($quotationId = $request->query('quotation_id')) {
            $prefillQuotation = Quotation::with('customer', 'items')
                ->where('business_id', $business->id)
                ->find($quotationId);
        }

        return view('app.sales-orders.create', compact(
            'business', 'customers', 'products', 'nextNumber', 'quotations', 'prefillQuotation'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'customer_id'             => ['required', 'exists:customers,id'],
            'so_number'               => ['nullable', 'string', 'max:64'],
            'order_date'              => ['required', 'date'],
            'expected_delivery_date'  => ['nullable', 'date', 'after_or_equal:order_date'],
            'shipping_address'        => ['nullable', 'string', 'max:500'],
            'quotation_id'            => ['nullable', 'exists:quotations,id'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'tax_amount'              => ['nullable', 'numeric', 'min:0'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['nullable', 'exists:products,id'],
            'items.*.product_name'    => ['required_without:items.*.product_id', 'nullable', 'string'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'items.*.quantity'        => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $salesOrder = $this->salesPipelineService->createSalesOrder($business, $validated);

        return redirect()->route('sales.orders.show', $salesOrder)
            ->with('success', "Pesanan Penjualan {$salesOrder->so_number} berhasil dibuat!");
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
