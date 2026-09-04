<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Commerce\SalesReturnService;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PosOrder;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

final class SalesReturnWebController extends Controller
{
    public function __construct(
        private readonly SalesReturnService $service = new SalesReturnService
    ) {}

    public function index(): View
    {
        $business = Context::requireBusiness();

        $returns = SalesReturn::where('business_id', $business->id)
            ->with(['invoice.customer', 'posOrder.customer', 'customer'])
            ->latest('return_date')
            ->latest('id')
            ->paginate(20);

        return view('app.sales.returns.index', compact('returns', 'business'));
    }

    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        // 1. Data Invoices
        $invoices = Invoice::with(['items.product', 'customer'])
            ->where('business_id', $business->id)
            ->whereNotIn('status', [Invoice::STATUS_DRAFT, Invoice::STATUS_VOID])
            ->latest('invoice_date')
            ->limit(100)
            ->get();

        $invoicesData = $invoices->map(function ($inv) {
            return [
                'id'            => $inv->id,
                'number'        => $inv->invoice_number,
                'customer_name' => $inv->customer?->name ?? 'Pelanggan Umum',
                'date'          => $inv->invoice_date?->format('d/m/Y') ?? '-',
                'total_amount'  => (float) $inv->total_amount,
                'status'        => $inv->status,
                'items'         => $inv->items->map(function ($item) {
                    $returnedQty = (float) SalesReturnItem::where('invoice_item_id', $item->id)
                        ->whereHas('salesReturn', fn($q) => $q->where('status', SalesReturn::STATUS_COMPLETED))
                        ->sum('quantity');
                    $maxReturnable = max(0, (float) $item->quantity - $returnedQty);

                    return [
                        'item_id'      => $item->id,
                        'product_name' => $item->item_name ?? $item->product?->name ?? 'Item Produk',
                        'sku'          => $item->sku ?? $item->product?->sku ?? '-',
                        'unit_price'   => (float) $item->unit_price,
                        'original_qty' => (float) $item->quantity,
                        'returned_qty' => $returnedQty,
                        'max_qty'      => $maxReturnable,
                        'selected'     => false,
                        'quantity'     => $maxReturnable > 0 ? 1 : 0,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        // 2. Data POS Orders (Kasir)
        $posOrders = PosOrder::with(['items.product', 'customer'])
            ->where('business_id', $business->id)
            ->whereIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_PARTIAL_REFUND])
            ->latest('order_date')
            ->latest('id')
            ->limit(100)
            ->get();

        $posOrdersData = $posOrders->map(function ($order) {
            return [
                'id'            => $order->id,
                'number'        => $order->order_number,
                'customer_name' => $order->customer?->name ?? ($order->customer_name_guest ?: 'Tamu Kasir'),
                'date'          => $order->order_date ? date('d/m/Y H:i', strtotime((string)$order->order_date)) : $order->created_at->format('d/m/Y H:i'),
                'total_amount'  => (float) $order->total_amount,
                'status'        => $order->status,
                'items'         => $order->items->map(function ($item) {
                    $returnedQty = (float) SalesReturnItem::where('pos_order_item_id', $item->id)
                        ->whereHas('salesReturn', fn($q) => $q->where('status', SalesReturn::STATUS_COMPLETED))
                        ->sum('quantity');
                    $maxReturnable = max(0, (float) $item->quantity - $returnedQty);

                    return [
                        'item_id'      => $item->id,
                        'product_name' => $item->product_name ?? $item->product?->name ?? 'Item Produk POS',
                        'sku'          => $item->product_code ?? $item->product?->sku ?? '-',
                        'unit_price'   => (float) $item->unit_price,
                        'original_qty' => (float) $item->quantity,
                        'returned_qty' => $returnedQty,
                        'max_qty'      => $maxReturnable,
                        'selected'     => false,
                        'quantity'     => $maxReturnable > 0 ? 1 : 0,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $prefillInvoiceId  = $request->query('invoice_id', '');
        $prefillPosOrderId = $request->query('pos_order_id', '');
        $initialSourceType = $prefillPosOrderId ? 'pos_order' : 'invoice';

        return view('app.sales.returns.create', compact(
            'business',
            'invoices',
            'invoicesData',
            'posOrders',
            'posOrdersData',
            'prefillInvoiceId',
            'prefillPosOrderId',
            'initialSourceType'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $business   = Context::requireBusiness();
        $sourceType = $request->input('source_type', 'invoice');

        // Filter items yang dipilih dan quantity > 0
        $rawItems = $request->input('items', []);
        $filteredItems = array_values(array_filter($rawItems, function ($it) {
            $isSelected = !empty($it['selected']) && ($it['selected'] === '1' || $it['selected'] === true || $it['selected'] === 'true');
            $qty = (float) ($it['quantity'] ?? 0);
            return $isSelected && $qty > 0;
        }));

        if (empty($filteredItems)) {
            $filteredItems = array_values(array_filter($rawItems, function ($it) {
                return (float) ($it['quantity'] ?? 0) > 0;
            }));
        }

        $request->merge(['items' => $filteredItems]);

        if ($sourceType === 'pos_order') {
            $order = PosOrder::findOrFail($request->string('pos_order_id')->toString());
            abort_unless($order->business_id === $business->id, 403);

            $validated = $request->validate([
                'pos_order_id'              => ['required', 'exists:pos_orders,id'],
                'reason'                    => ['required', 'string', 'max:255'],
                'refund_method'             => ['required', 'in:credit_note,cash_refund,store_credit'],
                'items'                     => ['required', 'array', 'min:1'],
                'items.*.pos_order_item_id' => ['required', 'exists:pos_order_items,id'],
                'items.*.quantity'          => ['required', 'numeric', 'gt:0'],
            ]);

            try {
                $return = $this->service->createFromPosOrder(
                    $order,
                    $validated['items'],
                    array_merge($validated, ['created_by' => auth()->id()])
                );
            } catch (InvalidArgumentException $exception) {
                return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
            }
        } else {
            $invoice = Invoice::findOrFail($request->string('invoice_id')->toString());
            abort_unless($invoice->business_id === $business->id, 403);

            $validated = $request->validate([
                'invoice_id'              => ['required', 'exists:invoices,id'],
                'reason'                  => ['required', 'string', 'max:255'],
                'refund_method'           => ['required', 'in:credit_note,cash_refund,store_credit'],
                'items'                   => ['required', 'array', 'min:1'],
                'items.*.invoice_item_id' => ['required', 'exists:invoice_items,id'],
                'items.*.quantity'        => ['required', 'numeric', 'gt:0'],
            ]);

            try {
                $return = $this->service->createFromInvoice(
                    $invoice,
                    $validated['items'],
                    array_merge($validated, ['created_by' => auth()->id()])
                );
            } catch (InvalidArgumentException $exception) {
                return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
            }
        }

        return redirect()->route('sales.returns.show', $return)
            ->with('success', "Draft retur penjualan {$return->return_number} berhasil dibuat.");
    }

    public function show(SalesReturn $return): View
    {
        $business = Context::requireBusiness();
        abort_unless($return->business_id === $business->id, 403);

        return view('app.sales.returns.show', [
            'business' => $business,
            'return'   => $return->load(['invoice.customer', 'posOrder.customer', 'customer', 'items.product']),
        ]);
    }

    public function approve(SalesReturn $return): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($return->business_id === $business->id, 403);

        $this->service->approve($return, (string) auth()->id());

        return back()->with('success', "Retur {$return->return_number} disetujui.");
    }

    public function complete(SalesReturn $return): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($return->business_id === $business->id, 403);

        try {
            $this->service->complete($return, (string) auth()->id());
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['return' => $exception->getMessage()]);
        }

        return back()->with('success', "Retur {$return->return_number} selesai dan stok berhasil dikembalikan ke inventori.");
    }
}
