<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Commerce\Storefront\ReservationBookingService;
use App\Http\Controllers\Controller;
use App\Models\CommerceReservation;
use App\Models\PosTable;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class MerchantReservationController extends Controller
{
    public function __construct(
        private readonly ReservationBookingService $reservationService = new ReservationBookingService(),
    ) {}

    /**
     * Display merchant reservations dashboard & calendar list.
     */
    public function index(Request $request): View
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        $statusTab = $request->query('tab', 'all');
        $selectedDate = $request->query('date', Carbon::today()->toDateString());

        $query = CommerceReservation::where('business_id', $business->id)
            ->with(['posTable', 'product'])
            ->orderBy('reservation_date')
            ->orderBy('time_slot');

        if ($statusTab === 'pending') {
            $query->where('status', CommerceReservation::STATUS_PENDING_CONFIRMATION);
        } elseif ($statusTab === 'confirmed') {
            $query->whereIn('status', [CommerceReservation::STATUS_CONFIRMED, CommerceReservation::STATUS_SEATED]);
        } elseif ($statusTab === 'today') {
            $query->whereDate('reservation_date', Carbon::today()->toDateString());
        } elseif ($statusTab === 'completed') {
            $query->where('status', CommerceReservation::STATUS_COMPLETED);
        } elseif ($statusTab === 'cancelled') {
            $query->whereIn('status', [CommerceReservation::STATUS_CANCELLED, CommerceReservation::STATUS_NO_SHOW]);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('reservation_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $reservations = $query->paginate(20)->withQueryString();

        $pendingCount = CommerceReservation::where('business_id', $business->id)
            ->where('status', CommerceReservation::STATUS_PENDING_CONFIRMATION)
            ->count();

        $todayCount = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', Carbon::today()->toDateString())
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ])
            ->count();

        $tables = PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('table_number')
            ->get();

        return view('app.storefront.reservations.index', compact(
            'business',
            'reservations',
            'statusTab',
            'pendingCount',
            'todayCount',
            'tables',
            'selectedDate'
        ));
    }

    /**
     * Update reservation status (confirm, seated, complete, cancel).
     */
    public function updateStatus(Request $request, CommerceReservation $reservation): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $reservation->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:confirmed,seated,completed,cancelled,no_show'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->reservationService->updateReservationStatus(
                reservation: $reservation,
                status: $validated['status'],
                reason: $validated['reason'] ?? null
            );

            return back()->with('success', "Status reservasi #{$reservation->reservation_code} berhasil diperbarui.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assign a table to a reservation.
     */
    public function assignTable(Request $request, CommerceReservation $reservation): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $reservation->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.reservations.manage'), 403);

        $validated = $request->validate([
            'pos_table_id' => ['required', 'uuid', 'exists:pos_tables,id'],
        ]);

        try {
            $table = PosTable::where('business_id', $business->id)->findOrFail($validated['pos_table_id']);
            $this->reservationService->assignTable($reservation, $table);

            return back()->with('success', "Meja #{$table->table_number} ({$table->name}) berhasil dialokasikan untuk reservasi {$reservation->reservation_code}.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
