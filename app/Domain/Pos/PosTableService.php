<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosTable;
use App\Models\PosTableSession;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class PosTableService
{
    /**
     * Get list of tables for a business, optionally filtered by location.
     *
     * @return Collection<int, PosTable>
     */
    public function getTables(Business $business, ?string $locationId = null): Collection
    {
        $query = PosTable::where('business_id', $business->id)
            ->with([
                'activeSession.orders.items.modifiers',
                'activeSession.orders.items.product.outputUnit',
                'location'
            ])
            ->orderBy('table_number');

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)->orWhereNull('location_id');
            });
        }

        return $query->get();
    }

    /**
     * Create a new table.
     *
     * @param array<string, mixed> $data
     */
    public function createTable(Business $business, array $data): PosTable
    {
        $tableNumber = trim((string) ($data['table_number'] ?? ''));
        if ($tableNumber === '') {
            throw new DomainException('Nomor meja wajib diisi.');
        }

        // Check uniqueness within business
        $exists = PosTable::where('business_id', $business->id)
            ->where('table_number', $tableNumber)
            ->exists();

        if ($exists) {
            throw new DomainException("Meja dengan nomor '{$tableNumber}' sudah terdaftar.");
        }

        return PosTable::create([
            'business_id' => $business->id,
            'location_id' => $data['location_id'] ?? null,
            'table_number' => $tableNumber,
            'name' => $data['name'] ?? null,
            'capacity' => (int) ($data['capacity'] ?? 4),
            'status' => PosTable::STATUS_AVAILABLE,
            'qr_token' => Str::random(32),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update table details.
     *
     * @param array<string, mixed> $data
     */
    public function updateTable(PosTable $table, array $data): PosTable
    {
        if (isset($data['table_number'])) {
            $tableNumber = trim((string) $data['table_number']);
            if ($tableNumber === '') {
                throw new DomainException('Nomor meja tidak boleh kosong.');
            }

            $duplicate = PosTable::where('business_id', $table->business_id)
                ->where('table_number', $tableNumber)
                ->where('id', '!=', $table->id)
                ->exists();

            if ($duplicate) {
                throw new DomainException("Nomor meja '{$tableNumber}' sudah digunakan oleh meja lain.");
            }
            $table->table_number = $tableNumber;
        }

        if (array_key_exists('name', $data)) {
            $table->name = $data['name'];
        }

        if (array_key_exists('capacity', $data)) {
            $table->capacity = max(1, (int) $data['capacity']);
        }

        if (array_key_exists('location_id', $data)) {
            $table->location_id = $data['location_id'];
        }

        if (array_key_exists('is_active', $data)) {
            $table->is_active = (bool) $data['is_active'];
            if (! $table->is_active && $table->status === PosTable::STATUS_AVAILABLE) {
                $table->status = PosTable::STATUS_INACTIVE;
            } elseif ($table->is_active && $table->status === PosTable::STATUS_INACTIVE) {
                $table->status = PosTable::STATUS_AVAILABLE;
            }
        }

        if (array_key_exists('notes', $data)) {
            $table->notes = $data['notes'];
        }

        $table->save();

        return $table;
    }

    /**
     * Delete a table safely if it has no active session or orders.
     */
    public function deleteTable(PosTable $table): bool
    {
        if ($table->activeSession()->exists()) {
            throw new DomainException('Meja sedang digunakan dalam sesi aktif dan tidak dapat dihapus.');
        }

        $hasUnfinishedOrders = $table->orders()
            ->whereNotIn('status', [PosOrder::STATUS_COMPLETED, PosOrder::STATUS_VOIDED, PosOrder::STATUS_REJECTED])
            ->exists();

        if ($hasUnfinishedOrders) {
            throw new DomainException('Meja memiliki pesanan yang belum selesai dan tidak dapat dihapus.');
        }

        return (bool) $table->delete();
    }

    /**
     * Regenerate table QR token. Invalidates previous QR immediately.
     */
    public function regenerateQrToken(PosTable $table): string
    {
        return $table->regenerateQrToken();
    }

    /**
     * Get or create an active table session for a customer.
     */
    public function getOrCreateActiveSession(PosTable $table, string $customerName, string $customerPhone): PosTableSession
    {
        return DB::transaction(function () use ($table, $customerName, $customerPhone) {
            $active = PosTableSession::where('pos_table_id', $table->id)
                ->where('status', PosTableSession::STATUS_OPEN)
                ->latest('opened_at')
                ->first();

            if ($active) {
                return $active;
            }

            // Generate unique session number: TS-YYYYMMDD-XXXX
            $datePrefix = Carbon::now()->format('Ymd');
            $countToday = PosTableSession::where('business_id', $table->business_id)
                ->whereDate('created_at', Carbon::today())
                ->count() + 1;
            $sessionNumber = sprintf('TS-%s-%04d', $datePrefix, $countToday);

            $session = PosTableSession::create([
                'business_id' => $table->business_id,
                'pos_table_id' => $table->id,
                'session_number' => $sessionNumber,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'status' => PosTableSession::STATUS_OPEN,
                'opened_at' => Carbon::now(),
            ]);

            $table->update(['status' => PosTable::STATUS_OCCUPIED]);

            return $session;
        });
    }

    /**
     * Close a table session and mark table as available if all orders are paid/closed.
     */
    public function closeSession(PosTableSession $session): void
    {
        DB::transaction(function () use ($session) {
            if (! $session->canBeClosed()) {
                throw new DomainException('Sesi meja tidak dapat ditutup karena masih ada pesanan yang belum dibayar atau sedang diproses.');
            }

            $session->update([
                'status' => PosTableSession::STATUS_CLOSED,
                'closed_at' => Carbon::now(),
            ]);

            $table = $session->table;
            if ($table && $table->is_active) {
                $table->update(['status' => PosTable::STATUS_AVAILABLE]);
            }
        });
    }

    /**
     * Synchronize table status from its active orders.
     */
    public function syncTableStatus(PosTable $table): void
    {
        if (! $table->is_active) {
            $table->update(['status' => PosTable::STATUS_INACTIVE]);
            return;
        }

        $activeSession = $table->activeSession;
        if (! $activeSession) {
            $table->update(['status' => PosTable::STATUS_AVAILABLE]);
            return;
        }

        $orders = $activeSession->orders;
        if ($orders->isEmpty()) {
            $table->update(['status' => PosTable::STATUS_ORDERING]);
            return;
        }

        $statuses = $orders->pluck('status')->all();

        if (in_array(PosOrder::STATUS_PREPARING, $statuses, true)) {
            $table->update(['status' => PosTable::STATUS_PREPARING]);
        } elseif (in_array(PosOrder::STATUS_READY, $statuses, true) || in_array(PosOrder::STATUS_SERVED, $statuses, true)) {
            $table->update(['status' => PosTable::STATUS_SERVING]);
        } elseif (in_array(PosOrder::STATUS_WAITING_PAYMENT, $statuses, true)) {
            $table->update(['status' => PosTable::STATUS_WAITING_PAYMENT]);
        } elseif ($activeSession->canBeClosed()) {
            $table->update(['status' => PosTable::STATUS_CLOSED]);
        } else {
            $table->update(['status' => PosTable::STATUS_OCCUPIED]);
        }
    }
}
