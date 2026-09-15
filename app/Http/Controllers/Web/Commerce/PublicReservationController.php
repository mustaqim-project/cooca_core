<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Commerce\Storefront\ReservationBookingService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class PublicReservationController extends Controller
{
    public function __construct(
        private readonly ReservationBookingService $reservationService = new ReservationBookingService(),
    ) {}

    /**
     * Submit online table / service reservation from public storefront.
     */
    public function submitReservation(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'min:2', 'max:150'],
            'customer_phone' => ['required', 'string', 'min:8', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:150'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', 'string', 'max:50'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:50'],
            'pos_table_id' => ['nullable', 'uuid', 'exists:pos_tables,id'],
            'product_id' => ['nullable', 'uuid', 'exists:products,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $reservation = $this->reservationService->createReservation(
                business: $business,
                customerData: [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                ],
                date: $validated['reservation_date'],
                timeSlot: $validated['time_slot'],
                guestCount: (int) $validated['guest_count'],
                tableId: $validated['pos_table_id'] ?? null,
                productId: $validated['product_id'] ?? null,
                notes: $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Permintaan reservasi Anda berhasil dikirim. Toko akan segera mengonfirmasi jadwal Anda.',
                'reservation' => [
                    'id' => $reservation->id,
                    'code' => $reservation->reservation_code,
                    'customer_name' => $reservation->customer_name,
                    'reservation_date' => $reservation->reservation_date->toDateString(),
                    'time_slot' => $reservation->time_slot,
                    'guest_count' => $reservation->guest_count,
                    'status' => $reservation->status,
                    'table' => $reservation->posTable ? [
                        'table_number' => $reservation->posTable->table_number,
                        'name' => $reservation->posTable->name,
                    ] : null,
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check table or slot availability for reservation.
     */
    public function checkAvailability(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $date = (string) $request->query('date', now()->toDateString());
        $timeSlot = (string) $request->query('time_slot', '');
        $guestCount = (int) $request->query('guest_count', 1);

        if (empty($timeSlot)) {
            return response()->json(['success' => false, 'message' => 'Slot waktu wajib ditentukan.'], 422);
        }

        $availableTables = $this->reservationService->getAvailableTables(
            business: $business,
            date: $date,
            timeSlot: $timeSlot,
            guestCount: $guestCount
        );

        return response()->json([
            'success' => true,
            'is_available' => $availableTables->isNotEmpty(),
            'available_tables' => $availableTables->map(fn ($t) => [
                'id' => $t->id,
                'table_number' => $t->table_number,
                'name' => $t->name,
                'capacity' => $t->capacity,
            ]),
        ]);
    }
}
