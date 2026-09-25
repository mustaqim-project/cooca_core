<?php

declare(strict_types=1);

namespace App\Domain\Printer;

use App\Domain\Printer\Connectors\FileConnector;
use App\Domain\Printer\Connectors\NetworkConnector;
use App\Domain\Printer\Connectors\WindowsConnector;
use App\Domain\Printer\Connectors\AgentPayloadConnector;
use App\Models\Business;
use App\Models\PosOrder;
use App\Models\PosPrinter;
use App\Models\PosShift;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class PrinterManager
{
    public function __construct(
        private readonly EscposFormatter $formatter = new EscposFormatter(),
        private readonly CashDrawerService $cashDrawer = new CashDrawerService(),
        private readonly KitchenRoutingService $kitchenRouter = new KitchenRoutingService(),
        private readonly PrinterDiagnosticService $diagnostic = new PrinterDiagnosticService(),
        private readonly PrintJobService $jobService = new PrintJobService()
    ) {}

    /**
     * Resolve default cashier printer for a business / location / register.
     */
    public function resolveCashierPrinter(Business $business, ?string $locationId = null, ?\App\Models\PosRegister $register = null): ?PosPrinter
    {
        // 0. If POS Register has a default receipt printer assigned, use it
        if ($register && $register->defaultReceiptPrinter && $register->defaultReceiptPrinter->is_active) {
            return $register->defaultReceiptPrinter;
        }

        $query = PosPrinter::where('business_id', $business->id)
            ->active()
            ->forLocation($locationId);

        // 1. Try printer assigned specifically for cashier_receipt
        $printer = (clone $query)->whereJsonContains('assigned_usages', PosPrinter::USAGE_CASHIER_RECEIPT)
            ->orderByDesc('is_default')
            ->first();

        if ($printer) {
            return $printer;
        }

        // 2. Fallback to default active printer
        return (clone $query)->orderByDesc('is_default')->first();
    }

    /**
     * Print POS Order receipt to designated hardware printer.
     *
     * @param array{
     *     open_drawer?: bool,
     *     is_reprint?: bool,
     *     reprint_count?: int,
     *     actor_id?: string|null
     * } $options
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     base64_payload?: string,
     *     printer_name?: string,
     *     connection_type?: string,
     *     drawer_opened?: bool,
     *     print_count?: int
     * }
     */
    public function printReceipt(PosOrder $order, ?PosPrinter $printer = null, array $options = []): array
    {
        $business = $order->business;
        $targetPrinter = $printer ?? $this->resolveCashierPrinter($business, $order->location_id, $order->posRegister);

        $isReprint = !empty($options['is_reprint']) || (int) ($order->print_count ?? 0) > 0;

        // Record print tracking on the order model
        if ($isReprint) {
            $order->recordReprint($options['actor_id'] ?? auth()->id());
        } else {
            $order->recordPrint($options['actor_id'] ?? auth()->id());
        }

        // Evaluate cash drawer pulse safety
        $openDrawer = false;
        if (isset($options['open_drawer'])) {
            $openDrawer = (bool) $options['open_drawer'];
        } else {
            $openDrawer = $this->cashDrawer->shouldOpenForOrder($order, $isReprint);
        }

        // If no hardware printer is registered, return raw payload for browser/agent delivery
        if (!$targetPrinter) {
            // Build fallback default printer representation (80mm)
            $dummyPrinter = new PosPrinter([
                'business_id' => $business->id,
                'name' => 'Default Virtual Printer',
                'paper_width' => '80mm',
                'connection_type' => PosPrinter::TYPE_AGENT,
                'capabilities' => [
                    PosPrinter::CAP_PRINT_TEXT,
                    PosPrinter::CAP_QR_CODE,
                    PosPrinter::CAP_CUT,
                    PosPrinter::CAP_CASH_DRAWER,
                ],
            ]);

            $rawBytes = $this->formatter->formatReceipt($order, $dummyPrinter, [
                'open_drawer' => $openDrawer,
                'is_reprint' => $isReprint,
                'reprint_count' => (int) $order->print_count,
            ]);

            $base64 = base64_encode($rawBytes);

            return [
                'success' => true,
                'message' => 'Struk ESC/POS berhasil dibuat untuk browser / printer virtual.',
                'base64_payload' => $base64,
                'printer_name' => 'Browser / Virtual Agent',
                'connection_type' => 'agent',
                'drawer_opened' => $openDrawer,
                'print_count' => (int) $order->print_count,
            ];
        }

        try {
            $rawBytes = $this->formatter->formatReceipt($order, $targetPrinter, [
                'open_drawer' => $openDrawer,
                'is_reprint' => $isReprint,
                'reprint_count' => (int) $order->print_count,
            ]);

            $connector = match ($targetPrinter->connection_type) {
                PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
                PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
                PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
                default => new AgentPayloadConnector(),
            };

            $result = $connector->send($targetPrinter, $rawBytes);

            return array_merge($result, [
                'base64_payload' => base64_encode($rawBytes),
                'printer_name' => $targetPrinter->name,
                'connection_type' => $targetPrinter->connection_type,
                'drawer_opened' => $openDrawer,
                'print_count' => (int) $order->print_count,
            ]);
        } catch (Throwable $e) {
            Log::error("[PrinterManager printReceipt] " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mencetak struk: ' . $e->getMessage(),
                'printer_name' => $targetPrinter->name,
                'drawer_opened' => false,
                'print_count' => (int) $order->print_count,
            ];
        }
    }

    /**
     * Route and print Kitchen Order Tickets (KOT) to kitchen and bar stations.
     *
     * @return array<int, array{
     *     station: string,
     *     printer: string,
     *     success: bool,
     *     message: string,
     *     base64_payload?: string
     * }>
     */
    public function printKitchenOrders(PosOrder $order, ?string $locationId = null): array
    {
        $business = $order->business;
        $routedStations = $this->kitchenRouter->routeOrderItems($order, $business, $locationId);

        if (empty($routedStations)) {
            return [];
        }

        $results = [];

        foreach ($routedStations as $route) {
            $printer = $route['printer'];
            $station = $route['station_name'];
            $items = $route['items'];

            try {
                $rawBytes = $this->formatter->formatKitchenOrder($order, $printer, $items, $station);

                $connector = match ($printer->connection_type) {
                    PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
                    PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
                    PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
                    default => new AgentPayloadConnector(),
                };

                $sendResult = $connector->send($printer, $rawBytes);

                $results[] = [
                    'station' => $station,
                    'printer' => $printer->name,
                    'success' => $sendResult['success'],
                    'message' => $sendResult['message'],
                    'base64_payload' => base64_encode($rawBytes),
                ];
            } catch (Throwable $e) {
                $results[] = [
                    'station' => $station,
                    'printer' => $printer->name,
                    'success' => false,
                    'message' => 'Gagal mencetak KOT: ' . $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Print Cashier Shift Report (Blind Cash Count).
     */
    public function printCashierShift(PosShift $shift, ?PosPrinter $printer = null): array
    {
        $business = $shift->business;
        $targetPrinter = $printer ?? $this->resolveCashierPrinter($business, $shift->location_id, $shift->register);

        if (!$targetPrinter) {
            $dummyPrinter = new PosPrinter([
                'business_id' => $business->id,
                'name' => 'Default Virtual Printer',
                'paper_width' => '80mm',
                'connection_type' => PosPrinter::TYPE_AGENT,
                'capabilities' => [
                    PosPrinter::CAP_PRINT_TEXT,
                    PosPrinter::CAP_QR_CODE,
                    PosPrinter::CAP_CUT,
                    PosPrinter::CAP_CASH_DRAWER,
                ],
            ]);

            $rawBytes = $this->formatter->formatCashierShiftReport($shift, $dummyPrinter);
            return [
                'success' => true,
                'message' => 'Laporan shift ESC/POS berhasil dibuat untuk browser / printer virtual.',
                'base64_payload' => base64_encode($rawBytes),
                'printer_name' => 'Browser / Virtual Agent',
                'connection_type' => 'agent',
            ];
        }

        try {
            $rawBytes = $this->formatter->formatCashierShiftReport($shift, $targetPrinter);

            $connector = match ($targetPrinter->connection_type) {
                PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
                PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
                PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
                default => new AgentPayloadConnector(),
            };

            $result = $connector->send($targetPrinter, $rawBytes);

            return array_merge($result, [
                'base64_payload' => base64_encode($rawBytes),
                'printer_name' => $targetPrinter->name,
                'connection_type' => $targetPrinter->connection_type,
            ]);
        } catch (Throwable $e) {
            Log::error("[PrinterManager printCashierShift] " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mencetak laporan shift: ' . $e->getMessage(),
                'printer_name' => $targetPrinter->name,
            ];
        }
    }

    /**
     * Alias for printCashierShift.
     */
    public function printShiftReport(PosShift $shift, ?PosPrinter $printer = null): array
    {
        return $this->printCashierShift($shift, $printer);
    }

    /**
     * Run Test Print Diagnostic for a printer.
     */
    public function testPrint(PosPrinter $printer): array
    {
        try {
            $rawBytes = $this->formatter->formatTestPrint($printer);

            $connector = match ($printer->connection_type) {
                PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
                PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
                PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
                default => new AgentPayloadConnector(),
            };

            $result = $connector->send($printer, $rawBytes);

            return array_merge($result, [
                'base64_payload' => base64_encode($rawBytes),
                'printer_name' => $printer->name,
            ]);
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal melakukan tes cetak: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Run Test Cash Drawer Pulse.
     */
    public function testCashDrawer(PosPrinter $printer, ?User $actor = null): array
    {
        return $this->cashDrawer->openDrawer($printer, 'Uji coba laci uang (Test Cash Drawer)', $actor);
    }

    /**
     * Diagnose printer connectivity.
     */
    public function diagnosePrinter(PosPrinter $printer): array
    {
        return $this->diagnostic->diagnose($printer);
    }
}
