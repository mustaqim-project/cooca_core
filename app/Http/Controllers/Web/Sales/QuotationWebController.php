<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Sales;

use App\Domain\Sales\SalesPipelineService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class QuotationWebController extends Controller
{
    public function __construct(
        private readonly SalesPipelineService $salesPipelineService = new SalesPipelineService
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Quotation::with('customer')
            ->where('business_id', $business->id)
            ->latest('date');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
            });
        }

        $quotations = $query->paginate(15)->withQueryString();

        return view('app.quotations.index', compact('business', 'quotations'));
    }

    public function create(): View
    {
        $business = Context::requireBusiness();
        $customers = Customer::where('business_id', $business->id)->orderBy('name')->get();
        $products = Product::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $nextNumber = $this->salesPipelineService->generateQuotationNumber($business);

        return view('app.quotations.create', compact('business', 'customers', 'products', 'nextNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'quotation_number' => ['nullable', 'string', 'max:64'],
            'date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_name' => ['required_without:items.*.product_id', 'nullable', 'string'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $quotation = $this->salesPipelineService->createQuotation($business, $validated);

        return redirect()->route('sales.quotations.show', $quotation)
            ->with('success', "Surat Penawaran {$quotation->quotation_number} berhasil diterbitkan.");
    }

    public function show(Quotation $quotation): View
    {
        $business = Context::requireBusiness();
        abort_unless($quotation->business_id === $business->id, 403);

        $quotation->load('customer', 'items.product', 'salesOrder');

        return view('app.quotations.show', compact('business', 'quotation'));
    }

    public function convertToSalesOrder(Quotation $quotation): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($quotation->business_id === $business->id, 403);

        if ($quotation->salesOrder) {
            return redirect()->route('sales.orders.show', $quotation->salesOrder)
                ->with('info', "Penawaran ini sudah dikonversi ke Pesanan {$quotation->salesOrder->so_number}.");
        }

        $salesOrder = $this->salesPipelineService->convertQuotationToSalesOrder($quotation);

        return redirect()->route('sales.orders.show', $salesOrder)
            ->with('success', "Penawaran {$quotation->quotation_number} berhasil diubah menjadi Pesanan Penjualan {$salesOrder->so_number}!");
    }
}
