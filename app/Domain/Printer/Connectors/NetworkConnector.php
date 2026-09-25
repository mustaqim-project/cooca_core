<?php

declare(strict_types=1);

namespace App\Domain\Printer\Connectors;

use App\Models\PosPrinter;
use Illuminate\Support\Facades\Log;
use Throwable;

class NetworkConnector implements PrinterConnectorInterface
{
    protected float $timeoutSeconds = 2.5;

    public function send(PosPrinter $printer, string $rawData): array
    {
        $ip = trim($printer->interface_address);
        $port = $printer->port ?: 9100;

        $fp = @fsockopen($ip, $port, $errno, $errstr, $this->timeoutSeconds);

        if (!$fp) {
            $msg = "Gagal terhubung ke printer LAN [{$ip}:{$port}]: {$errstr} ({$errno})";
            Log::warning("[POS Network Printer] " . $msg);
            return [
                'success' => false,
                'message' => $msg,
            ];
        }

        try {
            stream_set_timeout($fp, (int) ceil($this->timeoutSeconds));
            $bytesSent = fwrite($fp, $rawData);
            fflush($fp);
            fclose($fp);

            return [
                'success' => true,
                'message' => "Struk berhasil dikirim ke printer LAN [{$ip}:{$port}].",
                'bytes_sent' => (int) $bytesSent,
            ];
        } catch (Throwable $e) {
            if (is_resource($fp)) {
                fclose($fp);
            }
            Log::error("[POS Network Printer Exception] " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Terjadi kesalahan saat mencetak ke printer LAN: " . $e->getMessage(),
            ];
        }
    }

    public function testConnection(PosPrinter $printer): array
    {
        $ip = trim($printer->interface_address);
        $port = $printer->port ?: 9100;

        $startTime = microtime(true);
        $fp = @fsockopen($ip, $port, $errno, $errstr, $this->timeoutSeconds);
        $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

        if (!$fp) {
            return [
                'connected' => false,
                'latency_ms' => $latencyMs,
                'message' => "Printer offline atau tidak terjangkau di {$ip}:{$port} ({$errstr})",
            ];
        }

        fclose($fp);

        return [
            'connected' => true,
            'latency_ms' => $latencyMs,
            'message' => "Printer LAN online (Response time: {$latencyMs} ms)",
        ];
    }
}
