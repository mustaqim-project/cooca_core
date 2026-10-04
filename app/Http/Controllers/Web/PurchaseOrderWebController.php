<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Commerce\InvoiceService;
use App\Domain\Commerce\PurchaseOrderService;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CashAccount;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Material;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

        $query = PurchaseOrder::where('business_id', $business->id)
            ->with(['customer', 'supplier', 'items'])
            ->latest('order_date');

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")->orWhere('company_name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('po_type')) {
            $query->where('po_type', $request->get('po_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();

        // Status KPIs with strict tenant scoping
        $totalOrders = PurchaseOrder::where('business_id', $business->id)->count();
        $totalConfirmed = PurchaseOrder::where('business_id', $business->id)->where('status', PurchaseOrder::STATUS_CONFIRMED)->count();
        $totalInvoiced = PurchaseOrder::where('business_id', $business->id)->whereIn('status', [PurchaseOrder::STATUS_PARTIALLY_INVOICED, PurchaseOrder::STATUS_FULLY_INVOICED])->count();
        $totalSum = (float) PurchaseOrder::where('business_id', $business->id)->where('status', '!=', PurchaseOrder::STATUS_CANCELLED)->sum('total_amount');

        $products = Product::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $materials = Material::where('business_id', $business->id)->orderBy('name')->get();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();
        $suppliers = Supplier::where('business_id', $business->id)->orderBy('name')->get();
        $cashAccounts = CashAccount::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();

        return view('app.purchase-orders.index', compact(
            'business',
            'purchaseOrders',
            'totalOrders',
            'totalConfirmed',
            'totalInvoiced',
            'totalSum',
            'products',
            'materials',
            'locations',
            'suppliers',
            'cashAccounts'
        ));
    }

    /**
     * Show form for creating a new purchase order.
     */
    public function create(Request $request): View
    {
        $business = Context::requireBusiness();

        $customers = Customer::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('business_id', $business->id)->orderBy('name')->get();
        $products = Product::where('business_id', $business->id)->where('is_active', true)->with('outputUnit')->orderBy('name')->get();
        $materials = Material::where('business_id', $business->id)->with(['unit', 'prices'])->orderBy('name')->get();
        $units = Unit::all();

        // Context-aware: jika modul customer_po / b2b_sales mati, default otomatis ke supplier
        $canCustomerPo = $business->isModuleEnabled('customer_po') || $business->isModuleEnabled('b2b_sales');
        $defaultType = $canCustomerPo ? $request->get('type', PurchaseOrder::TYPE_CUSTOMER) : PurchaseOrder::TYPE_SUPPLIER;
        $selectedCustomerId = $request->get('customer_id');

        return view('app.purchase-orders.create', compact(
            'business',
            'customers',
            'suppliers',
            'products',
            'materials',
            'units',
            'defaultType',
            'selectedCustomerId',
            'canCustomerPo'
        ));
    }

    /**
     * Store a new purchase order with dynamic line items.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $businessId = $business->id;

        $validated = $request->validate([
            'po_type' => ['required', 'string', 'in:customer,supplier'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'customer_id' => [
                'nullable',
                'required_if:po_type,customer',
                Rule::exists('customers', 'id')->where('business_id', $businessId),
            ],
            'supplier_id' => [
                'nullable',
                'required_if:po_type,supplier',
                Rule::exists('suppliers', 'id')->where('business_id', $businessId),
            ],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'gte:0'],
            'tax_percentage' => ['nullable', 'numeric', 'gte:0', 'lte:100'],
            'terms_and_conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('business_id', $businessId),
            ],
            'items.*.material_id' => [
                'nullable',
                Rule::exists('materials', 'id')->where('business_id', $businessId),
            ],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        $sub = $entitlement->getSubscription($business);
        if (! $sub->isCorePlan()) {
            if (! $entitlement->canCreatePurchaseOrderThisMonth($business)) {
                return redirect()->route('billing.limits')->with('error', __('purchasing.limits.monthly_exceeded'));
            }
        }

        $po = $this->poService->createPurchaseOrder($business, $validated, $validated['items']);

        // Pemotongan kuota HANYA setelah dokumen sukses terbit 100%
        if (! $sub->isCorePlan()) {
            $entitlement->incrementMonthlyUsage($business, \App\Models\QuotaMonthlyUsage::TYPE_PO, \App\Domain\Billing\EntitlementService::FREE_PO_MONTHLY_LIMIT);
        }

        $user = Context::user();
        $approvalService = app(\App\Domain\Approval\ApprovalWorkflowService::class);
        $approvalRequest = $approvalService->evaluateAndCreateRequest(
            $business,
            \App\Models\ApprovalRule::DOC_PURCHASE_ORDER,
            $po->id,
            (float) $po->total_amount,
            $user
        );

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => $user?->id,
            'auditable_type' => PurchaseOrder::class,
            'auditable_id' => $po->id,
            'action' => 'po.created',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);

        $msg = $approvalRequest
            ? __('purchasing.messages.created_with_approval', ['number' => $po->po_number, 'levels' => $approvalRequest->total_levels])
            : __('purchasing.messages.created', ['number' => $po->po_number]);

        return redirect()->route('purchase-orders.show', $po->id)->with('success', $msg);
    }

    /**
     * Display purchase order details and action bar.
     */
    public function show(PurchaseOrder $purchaseOrder): View
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        $purchaseOrder->load(['customer', 'supplier', 'items.product', 'items.material', 'items.unit', 'invoices', 'goodsReceipts', 'approvalRequest.logs.approver', 'approvalRequest.rule']);

        $approvalData = app(\App\Domain\Approval\ApprovalWorkflowService::class)->getDocumentStepperData(
            \App\Models\ApprovalRule::DOC_PURCHASE_ORDER,
            $purchaseOrder->id,
            Context::user()
        );

        return view('app.purchase-orders.show', compact('business', 'purchaseOrder', 'approvalData'));
    }

    /**
     * Confirm a draft PO.
     */
    public function confirm(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        if ($purchaseOrder->status === PurchaseOrder::STATUS_CANCELLED) {
            return back()->with('error', 'Pesanan yang dibatalkan tidak dapat dikonfirmasi.');
        }

        $approvalService = app(\App\Domain\Approval\ApprovalWorkflowService::class);
        $req = $purchaseOrder->approvalRequest;

        // Auto-evaluate rule if corporate mode and request doesn't exist yet
        if (! $req && $business->isCorporate()) {
            $req = $approvalService->evaluateAndCreateRequest(
                $business,
                \App\Models\ApprovalRule::DOC_PURCHASE_ORDER,
                $purchaseOrder->id,
                (float) $purchaseOrder->total_amount,
                Context::user()
            );
        }

        if ($req && $req->isPending()) {
            return back()->with('error', "Pesanan {$purchaseOrder->po_number} tidak dapat dikonfirmasi karena masih menunggu otorisasi persetujuan (Level {$req->current_level} dari {$req->total_levels}).");
        }

        if ($req && $req->isRejected()) {
            return back()->with('error', "Pesanan {$purchaseOrder->po_number} ditolak oleh penyetuju: \"{$req->rejection_reason}\". Konfirmasi dibatalkan.");
        }

        $this->poService->confirm($purchaseOrder);

        AuditLog::create([
            'business_id' => $business->id,
            'user_id' => Context::user()?->id,
            'action' => 'po.confirmed',
            'module' => 'Purchasing',
            'record_type' => PurchaseOrder::class,
            'record_id' => $purchaseOrder->id,
            'reason_notes' => "Pesanan {$purchaseOrder->po_number} dikonfirmasi siap proses.",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', __('purchasing.messages.confirmed', ['number' => $purchaseOrder->po_number]));
    }

    /**
     * Cancel a PO with Three-Way Matching Guard and optional Supervisor PIN.
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        // Guard 1: Three-Way Matching Protection
        if ($purchaseOrder->goodsReceipts()->exists()) {
            return back()->with('error', __('purchasing.errors.cannot_cancel_received'));
        }

        // Guard 2: Invoice Integrity Protection
        if ($purchaseOrder->invoices()->exists()) {
            return back()->with('error', __('purchasing.errors.cannot_cancel_invoiced'));
        }

        // Guard 3: Supervisor PIN Verification for Confirmed orders
        if ($purchaseOrder->status === PurchaseOrder::STATUS_CONFIRMED && ! empty($business->pos_supervisor_pin)) {
            $pin = (string) $request->input('supervisor_pin', '');
            if (! Hash::check($pin, $business->pos_supervisor_pin)) {
                return back()->with('error', __('purchasing.errors.invalid_supervisor_pin'));
            }
        }

        DB::transaction(function () use ($purchaseOrder, $business, $request): void {
            $this->poService->cancel($purchaseOrder);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => Context::user()?->id,
                'auditable_type' => PurchaseOrder::class,
                'auditable_id' => $purchaseOrder->id,
                'action' => 'po.cancelled',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        });

        return back()->with('success', __('purchasing.messages.cancelled', ['number' => $purchaseOrder->po_number]));
    }

    /**
     * Generate a Commercial Sales Invoice directly from Customer PO.
     */
    public function generateInvoice(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        if ($purchaseOrder->po_type !== PurchaseOrder::TYPE_CUSTOMER) {
            return back()->with('error', __('purchasing.errors.only_customer_po_invoice'));
        }

        try {
            $invoice = $this->invoiceService->createFromPurchaseOrder($purchaseOrder);

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => Context::user()?->id,
                'auditable_type' => PurchaseOrder::class,
                'auditable_id' => $purchaseOrder->id,
                'action' => 'po.invoice_generated',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            return redirect()->route('invoices.show', $invoice->id)->with('success', __('purchasing.messages.invoice_generated', ['invoice' => $invoice->invoice_number, 'po' => $purchaseOrder->po_number]));
        } catch (\Throwable $e) {
            return back()->with('error', __('purchasing.errors.invoice_generation_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Render professional purchase order print-ready layout (A4).
     */
    public function print(PurchaseOrder $purchaseOrder): View
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        $purchaseOrder->load(['customer', 'supplier', 'items.unit']);

        return view('app.purchase-orders.print', compact('business', 'purchaseOrder'));
    }

    /**
     * Delete a purchase order (Only allowed for draft orders; confirmed/final orders must be cancelled).
     */
    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($purchaseOrder->business_id === $business->id, 403);

        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', __('purchasing.errors.cannot_delete_non_draft', ['status' => $purchaseOrder->status]));
        }

        if ($purchaseOrder->invoices()->exists()) {
            return back()->with('error', __('purchasing.errors.cannot_delete_invoiced'));
        }

        $poNumber = $purchaseOrder->po_number;
        $poId = $purchaseOrder->id;

        DB::transaction(function () use ($purchaseOrder, $business, $poNumber, $poId): void {
            if ($purchaseOrder->approvalRequest) {
                $purchaseOrder->approvalRequest->logs()->delete();
                $purchaseOrder->approvalRequest->delete();
            }

            $purchaseOrder->delete();

            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => Context::user()?->id,
                'auditable_type' => PurchaseOrder::class,
                'auditable_id' => $poId,
                'action' => 'po.deleted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('purchase-orders.index')->with('success', __('purchasing.messages.deleted', ['number' => $poNumber]));
    }
}
