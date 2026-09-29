<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Printer\CashDrawerService;
use App\Domain\Printer\PrinterIpValidator;
use App\Domain\Printer\PrinterManager;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosPrinter;
use App\Models\ProductCategory;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class PosPrinterWebController extends Controller
{
    public function __construct(
        private readonly PrinterManager $printerManager = new PrinterManager(),
        private readonly CashDrawerService $cashDrawerService = new CashDrawerService()
    ) {}

    /**
     * Display list of configured printers for the current business.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $locations = Location::where('business_id', $business->id)
            ->where('is_active', true)
            ->get();

        $selectedLocationId = $request->query('location_id');

        $query = PosPrinter::where('business_id', $business->id)
            ->with('location')
            ->latest();

        if ($selectedLocationId) {
            $query->where(function ($q) use ($selectedLocationId) {
                $q->where('location_id', $selectedLocationId)
                  ->orWhereNull('location_id');
            });
        }

        $printers = $query->get();
        $categories = ProductCategory::where('business_id', $business->id)->get();

        // Summary counts
        $totalPrinters = $printers->count();
        $onlinePrinters = $printers->where('last_status', 'online')->count();
        $cashierPrinters = $printers->filter(fn($p) => $p->supportsUsage(PosPrinter::USAGE_CASHIER_RECEIPT))->count();
        $kitchenPrinters = $printers->filter(fn($p) => $p->supportsUsage(PosPrinter::USAGE_KITCHEN_ORDER) || $p->supportsUsage(PosPrinter::USAGE_BAR_ORDER))->count();

        return view('app.pos.printers.index', compact(
            'business',
            'printers',
            'locations',
            'selectedLocationId',
            'categories',
            'totalPrinters',
            'onlinePrinters',
            'cashierPrinters',
            'kitchenPrinters'
        ));
    }

    /**
     * Store a new hardware printer profile.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'connection_type' => ['required', 'string', 'in:lan,wifi,usb,bluetooth,windows,serial,agent'],
            'interface_address' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'paper_width' => ['required', 'string', 'in:58mm,80mm'],
            'character_set' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'capabilities' => ['nullable', 'array'],
            'assigned_usages' => ['nullable', 'array'],
            'assigned_category_ids' => ['nullable', 'array'],
        ]);

        if (in_array($validated['connection_type'], ['lan', 'wifi'], true)) {
            if (! PrinterIpValidator::isSafe($validated['interface_address'])) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('printer.invalid_ip_host'),
                        'errors' => [
                            'interface_address' => [__('printer.invalid_ip_host')],
                        ],
                    ], 422);
                }
                return back()->withErrors([
                    'interface_address' => __('printer.invalid_ip_host'),
                ])->withInput();
            }
        }

        if (!empty($validated['is_default'])) {
            // Unset previous defaults for this business/location
            PosPrinter::where('business_id', $business->id)
                ->where('location_id', $validated['location_id'] ?? null)
                ->update(['is_default' => false]);
        }

        $printer = PosPrinter::create([
            'business_id' => $business->id,
            'location_id' => $validated['location_id'] ?? null,
            'name' => $validated['name'],
            'connection_type' => $validated['connection_type'],
            'interface_address' => $validated['interface_address'],
            'port' => $validated['port'] ?? 9100,
            'paper_width' => $validated['paper_width'],
            'character_set' => $validated['character_set'] ?? 'CP437',
            'is_active' => $request->boolean('is_active', true),
            'is_default' => $request->boolean('is_default', false),
            'capabilities' => $validated['capabilities'] ?? [
                PosPrinter::CAP_PRINT_TEXT,
                PosPrinter::CAP_QR_CODE,
                PosPrinter::CAP_CUT,
                PosPrinter::CAP_CASH_DRAWER,
            ],
            'assigned_usages' => $validated['assigned_usages'] ?? [PosPrinter::USAGE_CASHIER_RECEIPT],
            'assigned_category_ids' => $validated['assigned_category_ids'] ?? [],
            'last_status' => 'unknown',
        ]);

        // Automatically run initial diagnostic
        $this->printerManager->diagnosePrinter($printer);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('printer.printer_created', ['name' => $printer->name]),
                'printer' => $printer,
            ]);
        }

        return redirect()->route('pos.printers.index')
            ->with('success', __('printer.printer_created', ['name' => $printer->name]));
    }

    /**
     * Update an existing printer profile.
     */
    public function update(Request $request, PosPrinter $printer): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($printer->business_id === $business->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location_id' => ['nullable', 'uuid', 'exists:locations,id'],
            'connection_type' => ['required', 'string', 'in:lan,wifi,usb,bluetooth,windows,serial,agent'],
            'interface_address' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'paper_width' => ['required', 'string', 'in:58mm,80mm'],
            'character_set' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'capabilities' => ['nullable', 'array'],
            'assigned_usages' => ['nullable', 'array'],
            'assigned_category_ids' => ['nullable', 'array'],
        ]);

        if (in_array($validated['connection_type'], ['lan', 'wifi'], true)) {
            if (! PrinterIpValidator::isSafe($validated['interface_address'])) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('printer.invalid_ip_host'),
                        'errors' => [
                            'interface_address' => [__('printer.invalid_ip_host')],
                        ],
                    ], 422);
                }
                return back()->withErrors([
                    'interface_address' => __('printer.invalid_ip_host'),
                ])->withInput();
            }
        }

        if (!empty($validated['is_default'])) {
            PosPrinter::where('business_id', $business->id)
                ->where('location_id', $validated['location_id'] ?? null)
                ->where('id', '!=', $printer->id)
                ->update(['is_default' => false]);
        }

        $printer->update([
            'location_id' => $validated['location_id'] ?? null,
            'name' => $validated['name'],
            'connection_type' => $validated['connection_type'],
            'interface_address' => $validated['interface_address'],
            'port' => $validated['port'] ?? 9100,
            'paper_width' => $validated['paper_width'],
            'character_set' => $validated['character_set'] ?? 'CP437',
            'is_active' => $request->boolean('is_active', true),
            'is_default' => $request->boolean('is_default', false),
            'capabilities' => $validated['capabilities'] ?? $printer->capabilities,
            'assigned_usages' => $validated['assigned_usages'] ?? $printer->assigned_usages,
            'assigned_category_ids' => $validated['assigned_category_ids'] ?? $printer->assigned_category_ids,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('printer.printer_updated', ['name' => $printer->name]),
                'printer' => $printer,
            ]);
        }

        return redirect()->route('pos.printers.index')
            ->with('success', __('printer.printer_updated', ['name' => $printer->name]));
    }

    /**
     * Delete a printer profile.
     */
    public function destroy(PosPrinter $printer): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($printer->business_id === $business->id, 403);

        $name = $printer->name;
        $printer->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('printer.printer_deleted', ['name' => $name]),
            ]);
        }

        return redirect()->route('pos.printers.index')
            ->with('success', __('printer.printer_deleted', ['name' => $name]));
    }

    /**
     * Test print a sample diagnostic receipt.
     */
    public function testPrint(PosPrinter $printer): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($printer->business_id === $business->id, 403);

        $result = $this->printerManager->testPrint($printer);

        return response()->json($result);
    }

    /**
     * Test cash drawer pulse.
     */
    public function testDrawer(PosPrinter $printer): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($printer->business_id === $business->id, 403);

        $result = $this->printerManager->testCashDrawer($printer, auth()->user());

        return response()->json($result);
    }

    /**
     * Test printer connectivity and latency.
     */
    public function diagnose(PosPrinter $printer): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($printer->business_id === $business->id, 403);

        $result = $this->printerManager->diagnosePrinter($printer);

        return response()->json($result);
    }

    /**
     * Direct print receipt from terminal / orders list via ESC/POS.
     */
    public function directPrint(Request $request, PosOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($order->business_id === $business->id, 403);

        $printerId = $request->input('printer_id');
        $printer = $printerId ? PosPrinter::where('business_id', $business->id)->find($printerId) : null;

        $openDrawer = $request->has('open_drawer') ? $request->boolean('open_drawer') : null;

        $options = [
            'actor_id' => auth()->id(),
            'is_reprint' => $request->boolean('is_reprint'),
        ];
        if ($openDrawer !== null) {
            $options['open_drawer'] = $openDrawer;
        }

        $result = $this->printerManager->printReceipt($order, $printer, $options);

        // Also attempt kitchen print if requested and order is not yet printed to kitchen
        $kitchenResults = [];
        if ($request->boolean('print_kitchen')) {
            $kitchenResults = $this->printerManager->printKitchenOrders($order, $order->location_id);
        }

        return response()->json(array_merge($result, [
            'kitchen_results' => $kitchenResults,
        ]));
    }

    /**
     * Direct print Kitchen Order Ticket (KOT) for an order.
     */
    public function printKitchen(PosOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($order->business_id === $business->id, 403);

        $results = $this->printerManager->printKitchenOrders($order, $order->location_id);

        return response()->json([
            'success' => true,
            'message' => count($results) > 0 ? __('printer.kitchen_ticket_sent') : __('printer.no_kitchen_printer'),
            'results' => $results,
        ]);
    }

    /**
     * Manual No-Sale Drawer Pop with Supervisor PIN verification.
     */
    public function manualDrawerPop(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        $validated = $request->validate([
            'printer_id' => ['nullable', 'uuid', 'exists:pos_printers,id'],
            'supervisor_pin' => ['nullable', 'string', 'max:8'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $printer = !empty($validated['printer_id'])
            ? PosPrinter::where('business_id', $business->id)->find($validated['printer_id'])
            : $this->printerManager->resolveCashierPrinter($business);

        if (!$printer) {
            return response()->json([
                'success' => false,
                'message' => __('printer.no_cash_drawer_printer'),
            ], 422);
        }

        $reason = $validated['reason'] ?? 'Manual Pop via Pos Terminal';
        $pin = (string) ($validated['supervisor_pin'] ?? '');

        // If user is owner/supervisor and pin is empty, fallback to business PIN
        if ($pin === '' && (Context::isOwner() || in_array(Context::role(), ['owner', 'admin'], true))) {
            $pin = (string) ($business->pos_supervisor_pin ?? '');
        }

        if ($pin === '' || empty($business->pos_supervisor_pin)) {
            return response()->json([
                'success' => false,
                'message' => __('printer.drawer_pin_unconfigured'),
            ], 422);
        }

        try {
            $result = $this->cashDrawerService->openManualWithPin(
                $printer,
                $pin,
                $reason,
                $user
            );

            return response()->json($result);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
