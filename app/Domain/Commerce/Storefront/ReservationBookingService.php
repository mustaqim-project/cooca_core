<?php

declare(strict_types=1);

namespace App\Domain\Commerce\Storefront;

use App\Models\Business;
use App\Models\CommerceOrder;
use App\Models\CommerceReservation;
use App\Models\CommerceStoreSetting;
use App\Models\PosTable;
use App\Models\Product;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ReservationBookingService
{
    public function __construct(
        private readonly CommerceOrderService $orderService = new CommerceOrderService(),
    ) {}

    /**
     * Create a new reservation with table/service collision detection.
     *
     * @param  array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     * }  $customerData
     */
    public function createReservation(
        Business $business,
        array $customerData,
        string $date,
        string $timeSlot,
        int $guestCount = 1,
        ?string $tableId = null,
        ?string $productId = null,
        ?string $notes = null
    ): CommerceReservation {
        $setting = CommerceStoreSetting::where('business_id', $business->id)->first();
        if ($setting && ! $setting->allow_reservation) {
            throw new DomainException('Toko ini sedang tidak menerima layanan reservasi.');
        }

        $custName = trim($customerData['name'] ?? '');
        $custPhone = $this->orderService->normalizePhone((string) ($customerData['phone'] ?? ''));

        if ($custName === '') {
            throw new InvalidArgumentException('Nama pemesan wajib diisi.');
        }

        if ($custPhone === '' || strlen($custPhone) < 9) {
            throw new InvalidArgumentException('Nomor WhatsApp pemesan tidak valid.');
        }

        $reservationDate = Carbon::parse($date)->toDateString();

        return DB::transaction(function () use (
            $business,
            $customerData,
            $custName,
            $custPhone,
            $reservationDate,
            $timeSlot,
            $guestCount,
            $tableId,
            $productId,
            $notes
        ): CommerceReservation {
            // 1. Collision detection for table
            if ($tableId) {
                $table = PosTable::where('business_id', $business->id)->find($tableId);
                if (! $table) {
                    throw new InvalidArgumentException('Meja yang dipilih tidak ditemukan.');
                }

                $collision = CommerceReservation::where('business_id', $business->id)
                    ->where('pos_table_id', $tableId)
                    ->whereDate('reservation_date', $reservationDate)
                    ->where('time_slot', $timeSlot)
                    ->whereIn('status', [
                        CommerceReservation::STATUS_PENDING_CONFIRMATION,
                        CommerceReservation::STATUS_CONFIRMED,
                        CommerceReservation::STATUS_SEATED,
                    ])
                    ->lockForUpdate()
                    ->exists();

                if ($collision) {
                    throw new DomainException("Meja #{$table->table_number} ({$table->name}) sudah direservasi pada tanggal dan slot waktu tersebut.");
                }
            }

            // 2. Validate product/service if provided
            if ($productId) {
                $product = Product::where('business_id', $business->id)->find($productId);
                if (! $product) {
                    throw new InvalidArgumentException('Layanan yang dipilih tidak ditemukan.');
                }
            }

            // 3. Generate unique reservation code
            $datePrefix = Carbon::parse($reservationDate)->format('Ymd');
            $randomCode = strtoupper(Str::random(4));
            $reservationCode = "RSV-{$datePrefix}-{$randomCode}";

            $reservation = new CommerceReservation();
            $reservation->business_id = $business->id;
            $reservation->pos_table_id = $tableId;
            $reservation->product_id = $productId;
            $reservation->reservation_code = $reservationCode;
            $reservation->customer_name = $custName;
            $reservation->customer_phone = $custPhone;
            $reservation->customer_email = $customerData['email'] ?? null;
            $reservation->reservation_date = $reservationDate;
            $reservation->time_slot = $timeSlot;
            $reservation->guest_count = max(1, $guestCount);
            $reservation->status = CommerceReservation::STATUS_PENDING_CONFIRMATION;
            $reservation->notes = $notes;
            $reservation->save();

            return $reservation->load(['posTable', 'product']);
        });
    }

    /**
     * Check if a specific slot or table is available.
     */
    public function checkSlotAvailability(
        Business $business,
        string $date,
        string $timeSlot,
        ?string $tableId = null
    ): bool {
        $reservationDate = Carbon::parse($date)->toDateString();

        $query = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', $reservationDate)
            ->where('time_slot', $timeSlot)
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ]);

        if ($tableId) {
            $query->where('pos_table_id', $tableId);
            return ! $query->exists();
        }

        // If no table is specified, check if all tables are booked
        $totalTables = PosTable::where('business_id', $business->id)->where('is_active', true)->count();
        if ($totalTables === 0) {
            return true; // Flexible reservation
        }

        $bookedTablesCount = (clone $query)->whereNotNull('pos_table_id')->count();
        return $bookedTablesCount < $totalTables;
    }

    /**
     * Get list of currently available tables for a specific date, time slot, and guest count.
     *
     * @return Collection<int, PosTable>
     */
    public function getAvailableTables(
        Business $business,
        string $date,
        string $timeSlot,
        int $guestCount = 1
    ): Collection {
        $reservationDate = Carbon::parse($date)->toDateString();

        $bookedTableIds = CommerceReservation::where('business_id', $business->id)
            ->whereDate('reservation_date', $reservationDate)
            ->where('time_slot', $timeSlot)
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ])
            ->whereNotNull('pos_table_id')
            ->pluck('pos_table_id')
            ->toArray();

        return PosTable::where('business_id', $business->id)
            ->where('is_active', true)
            ->where('capacity', '>=', $guestCount)
            ->whereNotIn('id', $bookedTableIds)
            ->orderBy('capacity')
            ->get();
    }

    /**
     * Update status of reservation with state handling.
     */
    public function updateReservationStatus(
        CommerceReservation $reservation,
        string $status,
        ?string $reason = null
    ): CommerceReservation {
        return DB::transaction(function () use ($reservation, $status, $reason): CommerceReservation {
            switch ($status) {
                case CommerceReservation::STATUS_CONFIRMED:
                    $reservation->confirm();
                    break;
                case CommerceReservation::STATUS_SEATED:
                    $reservation->markAsSeated();
                    break;
                case CommerceReservation::STATUS_COMPLETED:
                    $reservation->markAsCompleted();
                    break;
                case CommerceReservation::STATUS_CANCELLED:
                    $reservation->cancel($reason);
                    break;
                case CommerceReservation::STATUS_NO_SHOW:
                    $reservation->update(['status' => CommerceReservation::STATUS_NO_SHOW]);
                    break;
                default:
                    throw new InvalidArgumentException("Status reservasi '{$status}' tidak valid.");
            }

            return $reservation->fresh(['posTable', 'product']);
        });
    }

    /**
     * Assign a table to a reservation.
     */
    public function assignTable(CommerceReservation $reservation, PosTable $table): CommerceReservation
    {
        if ($table->business_id !== $reservation->business_id) {
            throw new InvalidArgumentException('Meja tidak berada dalam bisnis yang sama.');
        }

        // Collision check
        $collision = CommerceReservation::where('business_id', $reservation->business_id)
            ->where('id', '!=', $reservation->id)
            ->where('pos_table_id', $table->id)
            ->whereDate('reservation_date', $reservation->reservation_date)
            ->where('time_slot', $reservation->time_slot)
            ->whereIn('status', [
                CommerceReservation::STATUS_PENDING_CONFIRMATION,
                CommerceReservation::STATUS_CONFIRMED,
                CommerceReservation::STATUS_SEATED,
            ])
            ->exists();

        if ($collision) {
            throw new DomainException("Meja #{$table->table_number} sudah direservasi pada tanggal dan jam tersebut.");
        }

        $reservation->update(['pos_table_id' => $table->id]);

        return $reservation->fresh(['posTable']);
    }
}
