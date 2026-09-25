<?php

declare(strict_types=1);

namespace App\Domain\Printer\Connectors;

use App\Models\PosPrinter;

interface PrinterConnectorInterface
{
    /**
     * Send raw ESC/POS binary data to the physical device or queue.
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     bytes_sent?: int,
     *     job_id?: string|null
     * }
     */
    public function send(PosPrinter $printer, string $rawData): array;

    /**
     * Test connection to the printer device.
     *
     * @return array{
     *     connected: bool,
     *     latency_ms?: float,
     *     message: string
     * }
     */
    public function testConnection(PosPrinter $printer): array;
}
