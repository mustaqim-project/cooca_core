<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Commerce\InvoiceService;
use App\Domain\Commerce\PurchaseOrderService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PurchaseOrderWebController extends Controller
{
    public function __construct(
        private readonly PurchaseOrderService $poService = new PurchaseOrderService,
        private readonly InvoiceService $invoiceService = new InvoiceService
    ) {}

    /**
     * Display a listing of purchase orders.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = PurchaseOrder::with(['customer', 'supplier', 'items'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('po_type')) {
            $query->where('po_type', $request->get('po_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();

        // Status KPIs
        $totalOrders = PurchaseOrder::count();
        $totalConfirmed = PurchaseOrder::where('status', PurchaseOrder::STATUS_CONFIRMED)->count();
        $totalInvoiced = PurchaseOrder::whereIn('status', [PurchaseOrder::STATUS_PARTIALLY_INVOICED, PurchaseOrder::STATUS_FULLY_INVOICED])->count();
        $totalSum = (float) PurchaseOrder::where('status', '!=', PurchaseOrder::STATUS_CANCELLED)->sum('total_amount');

        $products = \App\Models\Product::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $locations = \App\Models\Location::where('business_id', $business->id)->where('is_active', true)->get();
        $suppliers = \App\Models\Supplier::where('business_id', $business->id)->orderBy('name')->get();

        return view('app.purchase-orders.index', compact(
            'business',
            'purchaseOrders',
            'totalOrders',
            'totalConfirmed',
            'totalInvoiced',
            'totalSum',
            'products',
            'locations',
            'suppliers'
        ));
    }

    /**
     * Show form for creating a new purchase order.
     */
    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::where('is_active', true)->with('outputUnit')->orderBy('name')->get();
        $materials = Material::with(['unit', 'prices'])->orderBy('name')->get();
        $units = Unit::all();

        $defaultType = $request->get('type', PurchaseOrder::TYPE_CUSTOMER);
        $selectedCustomerId = $request->get('customer_id');

        return view('app.purchase-orders.create', compact(
            'business',
            'customers',
            'suppliers',
            'products',
            'materials',
            'units',
            'defaultType',
            'selectedCustomerId'
        ));
    }

    /**
     * Store a new purchase order with dynamic line items.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'po_type' => ['required', 'string', 'in:customer,supplier'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', 'required_if:po_type,customer', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'required_if:po_type,supplier', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'tax_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'terms_and_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.material_id' => ['nullable', 'exists:materials,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $po = $this->poService->createPurchaseOrder($business, $validated, $validated['items']);

        return redirect()->route('purchase-orders.show', $po->id)->with('success', "Pesanan {$po->po_number} berhasil dibuat.");
    }

    /**
     * Display purchase order details and action bar.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $business = Context::requireBusiness();
        $purchaseOrder->load(['customer', 'supplier', 'items.product', 'items.material', 'items.unit', 'invoices']);

        return view('app.purchase-orders.show', compact('business', 'purchaseOrder'));
    }

    /**
     * Confirm a draft PO.
     */
    public function confirm(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->poService->confirm($purchaseOrder);

        return back()->with('success', "Pesanan {$purchaseOrder->po_number} telah dikonfirmasi.");
    }

    /**
     * Cancel a PO.
     */
    public function cancel(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->poService->cancel($purchaseOrder);

        return back()->with('success', "Pesanan {$purchaseOrder->po_number} telah dibatalkan.");
    }

    /**
     * Generate a Commercial Sales Invoice directly from Customer PO.
     */
    public function generateInvoice(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->po_type !== PurchaseOrder::TYPE_CUSTOMER) {
            return back()->with('error', 'Hanya PO Pelanggan yang dapat di-generate menjadi Faktur Penjualan.');
        }

        try {
            $invoice = $this->invoiceService->createFromPurchaseOrder($purchaseOrder);

            return redirect()->route('invoices.show', $invoice->id)->with('success', "Faktur {$invoice->invoice_number} berhasil diterbitkan dari PO {$purchaseOrder->po_number}.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menerbitkan faktur: ' . $e->getMessage());
        }
    }

    /**
     * Render professional purchase order print-ready layout (A4).
     */
    public function print(PurchaseOrder $purchaseOrder): View
    {
        $business = Context::requireBusiness();
        $purchaseOrder->load(['customer', 'supplier', 'items.unit']);

        return view('app.purchase-orders.print', compact('business', 'purchaseOrder'));
    }

    /**
     * Delete a purchase order.
     */
    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->invoices()->exists()) {
            return back()->with('error', 'Pesanan ini sudah terhubung dengan faktur aktif dan tidak dapat dihapus.');
        }

        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')->with('success', 'Pesanan berhasil dihapus.');
    }
}
