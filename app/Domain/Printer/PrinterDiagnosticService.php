<?php

declare(strict_types=1);

namespace App\Domain\Printer;

use App\Domain\Printer\Connectors\FileConnector;
use App\Domain\Printer\Connectors\NetworkConnector;
use App\Domain\Printer\Connectors\WindowsConnector;
use App\Domain\Printer\Connectors\AgentPayloadConnector;
use App\Models\PosPrinter;
use Throwable;

class PrinterDiagnosticService
{
    /**
     * Test printer connectivity and update its status.
     *
     * @return array{
     *     connected: bool,
     *     status: string,
     *     latency_ms?: float,
     *     message: string
     * }
     */
    public function diagnose(PosPrinter $printer): array
    {
        $connector = match ($printer->connection_type) {
            PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
            PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
            PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
            default => new AgentPayloadConnector(),
        };

        try {
            $result = $connector->testConnection($printer);
            $status = $result['connected'] ? 'online' : 'offline';

            $printer->update([
                'last_status' => $status,
                'last_status_checked_at' => now(),
            ]);

            return array_merge($result, ['status' => $status]);
        } catch (Throwable $e) {
            $printer->update([
                'last_status' => 'error',
                'last_status_checked_at' => now(),
            ]);

            return [
                'connected' => false,
                'status' => 'error',
                'message' => 'Kesalahan saat pengujian koneksi: ' . $e->getMessage(),
            ];
        }
    }
}
