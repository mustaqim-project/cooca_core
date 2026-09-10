<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Pos;

use App\Domain\Pos\PosTableService;
use App\Domain\Pos\TableQrCodeService;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PosTable;
use App\Models\PosTableSession;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Throwable;

final class PosTableWebController extends Controller
{
    public function __construct(
        private readonly PosTableService $tableService = new PosTableService,
        private readonly TableQrCodeService $qrCodeService = new TableQrCodeService
    ) {}

    /**
     * Display table layout, status grid, and active sessions.
     */
    public function index(Request $request): View|JsonResponse
    {
        $business = Context::requireBusiness();
        $locations = Location::where('business_id', $business->id)->where('is_active', true)->get();

        $selectedLocationId = $request->query('location_id');
        $tables = $this->tableService->getTables($business, $selectedLocationId);

        // AJAX call from POS terminal's fetchTables()
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tables'  => $tables->map(fn (PosTable $t) => [
                    'id'             => $t->id,
                    'table_number'   => $t->table_number,
                    'name'           => $t->name,
                    'capacity'       => $t->capacity,
                    'status'         => $t->status,
                    'active_session' => $t->activeSession ? [
                        'id'     => $t->activeSession->id,
                        'orders' => $t->activeSession->orders->map(fn ($o) => [
                            'id'           => $o->id,
                            'order_number' => $o->order_number,
                            'status'       => $o->status,
                            'total_amount' => (float) $o->total_amount,
                            'customer_name_guest' => $o->customer_name_guest,
                            'items'        => $o->items->map(fn ($item) => [
                                'product_name'     => $item->product_name,
                                'quantity'         => (float) $item->quantity,
                                'modifiers_summary'=> $item->modifiers_display_text ?? '',
                                'notes'            => $item->notes,
                            ])->values()->all(),
                        ])->values()->all(),
                    ] : null,
                ])->values()->all(),
            ]);
        }

        $stats = [
            'total_tables'    => $tables->count(),
            'available_tables'=> $tables->where('status', PosTable::STATUS_AVAILABLE)->count(),
            'occupied_tables' => $tables->whereIn('status', [
                PosTable::STATUS_OCCUPIED,
                PosTable::STATUS_ORDERING,
                PosTable::STATUS_PREPARING,
                PosTable::STATUS_SERVING,
                PosTable::STATUS_WAITING_PAYMENT,
            ])->count(),
            'total_capacity'  => $tables->sum('capacity'),
        ];

        return view('app.pos.tables', compact('business', 'locations', 'selectedLocationId', 'tables', 'stats'));
    }

    /**
     * Store a newly created table.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'location_id' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $table = $this->tableService->createTable($business, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Meja berhasil ditambahkan.', 'table' => $table]);
            }

            return redirect()->route('pos.tables.index')->with('success', "Meja {$table->table_number} berhasil ditambahkan.");
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing table.
     */
    public function update(Request $request, PosTable $table): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            abort(403);
        }

        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'location_id' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->tableService->updateTable($table, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Meja berhasil diperbarui.', 'table' => $table]);
            }

            return redirect()->route('pos.tables.index')->with('success', "Meja {$table->table_number} berhasil diperbarui.");
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a table safely.
     */
    public function destroy(Request $request, PosTable $table): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            abort(403);
        }

        try {
            $this->tableService->deleteTable($table);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Meja berhasil dihapus.']);
            }

            return redirect()->route('pos.tables.index')->with('success', 'Meja berhasil dihapus.');
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Regenerate cryptographic QR token for a table.
     */
    public function regenerateQr(Request $request, PosTable $table): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            abort(403);
        }

        try {
            $newToken = $this->tableService->regenerateQrToken($table);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'QR Code meja berhasil diperbarui. QR lama sudah tidak berlaku.',
                    'new_token' => $newToken,
                    'qr_url' => $table->qr_url,
                ]);
            }

            return redirect()->route('pos.tables.index')->with('success', "QR Code Meja {$table->table_number} berhasil diperbarui.");
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Render printable QR Standee Card view.
     */
    public function qrCard(PosTable $table): View
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            abort(403);
        }

        $qrSvg = $this->qrCodeService->generateForTable($table, $business, 500);

        return view('app.pos.qr-card', compact('business', 'table', 'qrSvg'));
    }

    /**
     * Download crisp vector SVG of table QR.
     */
    public function downloadSvg(PosTable $table): Response
    {
        $business = Context::requireBusiness();
        if ($table->business_id !== $business->id) {
            abort(403);
        }

        $svg = $this->qrCodeService->generateForTable($table, $business, 600);
        $filename = 'QR-Meja-' . str_replace(' ', '-', $table->table_number) . '.svg';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Close a table session and release table.
     */
    public function closeSession(Request $request, PosTableSession $session): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($session->business_id !== $business->id) {
            abort(403);
        }

        try {
            $this->tableService->closeSession($session);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Sesi meja berhasil ditutup. Meja kembali tersedia.']);
            }

            return redirect()->route('pos.tables.index')->with('success', 'Sesi meja berhasil ditutup.');
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
