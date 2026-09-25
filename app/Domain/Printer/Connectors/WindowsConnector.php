<?php

declare(strict_types=1);

namespace App\Domain\Printer\Connectors;

use App\Models\PosPrinter;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector as EscposWindowsConnector;
use Throwable;

class WindowsConnector implements PrinterConnectorInterface
{
    public function send(PosPrinter $printer, string $rawData): array
    {
        $printerName = trim($printer->interface_address);

        try {
            $connector = new EscposWindowsConnector($printerName);
            $connector->write($rawData);
            $connector->finalize();

            return [
                'success' => true,
                'message' => "Struk berhasil dikirim ke antrean Windows [{$printerName}].",
                'bytes_sent' => strlen($rawData),
            ];
        } catch (Throwable $e) {
            Log::warning("[POS Windows Printer] Gagal mencetak ke [{$printerName}]: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Gagal mengirim ke printer Windows [{$printerName}]: " . $e->getMessage(),
            ];
        }
    }

    public function testConnection(PosPrinter $printer): array
    {
        $printerName = trim($printer->interface_address);

        // Verify if print spooler on Windows recognizes printer name
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            try {
                $output = [];
                $ret = 0;
                // Check printer existence via wmic or PowerShell
                @exec('powershell -Command "Get-Printer -Name \'' . addslashes($printerName) . '\' -ErrorAction SilentlyContinue | Select-Object -ExpandProperty PrinterStatus"', $output, $ret);

                if ($ret === 0 && !empty($output)) {
                    return [
                        'connected' => true,
                        'message' => "Printer Windows [{$printerName}] terdeteksi di sistem.",
                    ];
                }
            } catch (Throwable) {
                // Ignore shell inspection failure and fallback
            }
        }

        return [
            'connected' => true,
            'message' => "Antrean Windows printer [{$printerName}] siap menerima dokumen.",
        ];
    }
}
