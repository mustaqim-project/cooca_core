<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Commerce\InvoiceNumberGenerator;
use App\Domain\Commerce\InvoiceService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoiceWebController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService = new InvoiceService,
        private readonly InvoiceNumberGenerator $numberGenerator = new InvoiceNumberGenerator
    ) {}

    /**
     * Display a listing of invoices with KPIs.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Invoice::with(['customer', 'items', 'payments'])
            ->latest('invoice_date');

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Financial KPIs
        $totalInvoiced = (float) Invoice::where('status', '!=', Invoice::STATUS_VOID)->sum('total_amount');
        $totalPaid = (float) Invoice::where('status', '!=', Invoice::STATUS_VOID)->sum('paid_amount');
        $totalReceivables = (float) Invoice::whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_UNPAID, Invoice::STATUS_PARTIALLY_PAID, Invoice::STATUS_OVERDUE])->sum('balance_due');
        $totalGrossProfit = (float) Invoice::where('status', '!=', Invoice::STATUS_VOID)->sum('total_gross_profit');

        return view('app.invoices.index', compact(
            'business',
            'invoices',
            'totalInvoiced',
            'totalPaid',
            'totalReceivables',
            'totalGrossProfit'
        ));
    }

    /**
     * Show form for creating a new invoice (from products, PO, or HPP calculator).
     */
    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->with('outputUnit')->orderBy('name')->get();
        $units = Unit::all();

        // Available confirmed POs that can be converted
        $availablePurchaseOrders = PurchaseOrder::where('po_type', PurchaseOrder::TYPE_CUSTOMER)
            ->whereIn('status', [PurchaseOrder::STATUS_CONFIRMED, PurchaseOrder::STATUS_PARTIALLY_INVOICED])
            ->with(['customer', 'items.unit'])
            ->latest()
            ->get();

        // Check if pre-filled from PO
        $prefillPoId = $request->get('po_id');
        $selectedPo = $prefillPoId ? PurchaseOrder::with(['customer', 'items.unit'])->find($prefillPoId) : null;

        // Check if pre-filled from Calculator HPP
        $prefillProductId = $request->get('product_id');
        $prefillQty = (float) ($request->get('qty', 1));
        $prefillPrice = (float) ($request->get('price', 0));
        $prefillCost = (float) ($request->get('cost', 0));

        $prefillProduct = $prefillProductId ? Product::find($prefillProductId) : null;

        return view('app.invoices.create', compact(
            'business',
            'customers',
            'products',
            'units',
            'availablePurchaseOrders',
            'selectedPo',
            'prefillProduct',
            'prefillQty',
            'prefillPrice',
            'prefillCost'
        ));
    }

    /**
     * Store a new invoice with line items.
     */
    public function store(Request $request): RedirectResponse
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

        return redirect()->route('invoices.show', $invoice->id)->with('success', "Faktur {$invoice->invoice_number} berhasil diterbitkan.");
    }

    /**
     * Display detailed invoice view.
     */
    public function show(Invoice $invoice): View
    {
        $business = Context::requireBusiness();
        $invoice->load(['customer', 'purchaseOrder', 'items.product', 'items.unit', 'payments.creator']);

        return view('app.invoices.show', compact('business', 'invoice'));
    }

    /**
     * Record payment receipt for an invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:bank_transfer,cash,qris,credit_card,cheque'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->invoiceService->recordPayment(
            $invoice,
            (float) $validated['amount'],
            $validated['payment_method'],
            [
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]
        );

        return back()->with('success', "Pembayaran sebesar Rp " . number_format((float) $validated['amount'], 0, ',', '.') . " berhasil dicatat.");
    }

    /**
     * Render professional commercial tax invoice print-ready layout (A4).
     */
    public function print(Invoice $invoice): View
    {
        $business = Context::requireBusiness();
        $invoice->load(['customer', 'purchaseOrder', 'items.unit', 'payments']);

        return view('app.invoices.print', compact('business', 'invoice'));
    }

    /**
     * Export all invoices to Excel CSV format.
     */
    public function exportExcel(): StreamedResponse
    {
        $business = Context::requireBusiness();

        $invoices = Invoice::with(['customer', 'items', 'payments'])
            ->latest('invoice_date')
            ->get();

        $filename = 'Laporan_Faktur_Penjualan_' . \Illuminate\Support\Str::slug($business->name) . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($invoices, $business): void {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compatibility

            fputcsv($file, ['LAPORAN LENGKAP FAKTUR PENJUALAN & PENAGIHAN']);
            fputcsv($file, ['Nama Bisnis', $business->name]);
            fputcsv($file, ['Mata Uang', $business->currency_code . ' (' . $business->currency_symbol . ')']);
            fputcsv($file, ['Waktu Export', date('d F Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'No',
                'Nomor Faktur',
                'Tanggal Faktur',
                'Jatuh Tempo',
                'Nama Pelanggan',
                'Perusahaan',
                'No. PO Referensi',
                'Status',
                'Subtotal',
                'Diskon',
                'PPN / Pajak',
                'Ongkos Kirim',
                'Total Tagihan',
                'Sudah Dibayar',
                'Sisa Saldo (Piutang)',
                'Snapshot Total HPP',
                'Laba Kotor Riil',
                'Termin Bayar',
            ]);

            $no = 1;
            foreach ($invoices as $inv) {
                fputcsv($file, [
                    $no++,
                    $inv->invoice_number,
                    $inv->invoice_date?->format('Y-m-d'),
                    $inv->due_date?->format('Y-m-d'),
                    $inv->customer?->name ?? '-',
                    $inv->customer?->company_name ?? '-',
                    $inv->purchaseOrder?->po_number ?? '-',
                    strtoupper($inv->status),
                    round((float) $inv->subtotal),
                    round((float) $inv->discount_amount),
                    round((float) $inv->tax_amount),
                    round((float) $inv->shipping_cost),
                    round((float) $inv->total_amount),
                    round((float) $inv->paid_amount),
                    round((float) $inv->balance_due),
                    round((float) $inv->total_hpp_cost),
                    round((float) $inv->total_gross_profit),
                    $inv->payment_terms ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete an invoice (only if draft or void).
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Faktur yang telah menerima pembayaran tidak dapat dihapus.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Faktur berhasil dihapus.');
    }
}
