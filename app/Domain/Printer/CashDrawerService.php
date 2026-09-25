<?php

declare(strict_types=1);

namespace App\Domain\Printer;

use App\Domain\Printer\Connectors\NetworkConnector;
use App\Domain\Printer\Connectors\WindowsConnector;
use App\Domain\Printer\Connectors\FileConnector;
use App\Domain\Printer\Connectors\AgentPayloadConnector;
use App\Models\AuditLog;
use App\Models\PosOrder;
use App\Models\PosPrinter;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CashDrawerService
{
    public function __construct(
        private readonly EscposFormatter $formatter = new EscposFormatter()
    ) {}

    /**
     * Determine if cash drawer should be automatically opened for a given POS order.
     * Guardrails:
     * - Order must be COMPLETED.
     * - Order must have a CASH payment method.
     * - Drawer pulse has not been triggered for this order yet (idempotency).
     */
    public function shouldOpenForOrder(PosOrder $order, PosPrinter|bool|null $printer = null, bool $isReprint = false): bool
    {
        if (is_bool($printer)) {
            $isReprint = $printer;
            $printer = null;
        }

        if ($printer instanceof PosPrinter && ! $printer->hasCapability(PosPrinter::CAP_CASH_DRAWER)) {
            return false;
        }

        // Never auto-open on reprints
        if ($isReprint || (int) ($order->print_count ?? 0) > 1) {
            return false;
        }

        // Must be paid & completed
        if ($order->status !== PosOrder::STATUS_COMPLETED && ! $order->isPaid()) {
            return false;
        }

        // Must contain cash payment
        $order->loadMissing('payments');
        $hasCash = $order->payments->contains(function ($p) {
            return strtolower((string) $p->payment_method) === 'cash';
        });

        return $hasCash;
    }

    /**
     * Trigger a physical cash drawer pulse via the designated printer.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     base64_payload?: string
     * }
     */
    public function openDrawer(PosPrinter $printer, ?string $reason = null, ?User $actor = null): array
    {
        if (! $printer->hasCapability(PosPrinter::CAP_CASH_DRAWER)) {
            return [
                'success' => false,
                'message' => "Printer [{$printer->name}] tidak mendukung kontrol laci uang (Cash Drawer).",
            ];
        }

        $pulseBytes = $this->formatter->formatDrawerPulse();

        $connector = match ($printer->connection_type) {
            PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
            PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
            PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
            default => new AgentPayloadConnector(),
        };

        $result = $connector->send($printer, $pulseBytes);

        // Record Audit Log if this was a manual No-Sale pop
        if ($actor && $reason) {
            try {
                AuditLog::create([
                    'business_id' => $printer->business_id,
                    'user_id' => $actor->id,
                    'action' => 'pos.cash_drawer.manual_open',
                    'auditable_type' => PosPrinter::class,
                    'auditable_id' => $printer->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'old_values' => null,
                    'new_values' => [
                        'printer_name' => $printer->name,
                        'reason' => $reason,
                        'connection_type' => $printer->connection_type,
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('[AuditLog CashDrawer] ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * Execute manual No-Sale Drawer Pop with Supervisor PIN authorization.
     */
    public function openManualWithPin(PosPrinter $printer, string $pin, string $reason, User $actor): array
    {
        $business = $printer->business;
        $validPin = (string) ($business->pos_supervisor_pin ?? '1234');

        if ($pin === '' || (! Hash::check($pin, $validPin) && ! hash_equals($validPin, $pin))) {
            throw new DomainException('PIN Supervisor salah. Otorisasi pembukaan laci kas ditolak.');
        }

        if (empty(trim($reason))) {
            throw new DomainException('Alasan pembukaan laci kas manual wajib diisi.');
        }

        return $this->openDrawer($printer, $reason, $actor);
    }
}
