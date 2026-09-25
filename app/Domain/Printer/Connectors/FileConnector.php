<?php

declare(strict_types=1);

namespace App\Domain\Printer\Connectors;

use App\Models\PosPrinter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class FileConnector implements PrinterConnectorInterface
{
    public function send(PosPrinter $printer, string $rawData): array
    {
        $devicePath = trim($printer->interface_address);

        try {
            $fp = @fopen($devicePath, 'wb');
            if (!$fp) {
                return [
                    'success' => false,
                    'message' => "Tidak dapat membuka port perangkat [{$devicePath}]. Periksa izin akses port.",
                ];
            }

            $bytes = fwrite($fp, $rawData);
            fflush($fp);
            fclose($fp);

            return [
                'success' => true,
                'message' => "Data ESC/POS berhasil ditulis ke port [{$devicePath}].",
                'bytes_sent' => (int) $bytes,
            ];
        } catch (Throwable $e) {
            Log::error("[POS File/Serial Printer] " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Gagal menulis ke port [{$devicePath}]: " . $e->getMessage(),
            ];
        }
    }

    public function testConnection(PosPrinter $printer): array
    {
        $devicePath = trim($printer->interface_address);

        if (File::exists($devicePath) || @fopen($devicePath, 'rb')) {
            return [
                'connected' => true,
                'message' => "Port perangkat [{$devicePath}] terdeteksi dan dapat diakses.",
            ];
        }

        return [
            'connected' => false,
            'message' => "Port perangkat [{$devicePath}] tidak ditemukan.",
        ];
    }
}
