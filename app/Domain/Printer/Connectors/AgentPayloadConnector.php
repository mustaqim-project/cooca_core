<?php

declare(strict_types=1);

namespace App\Domain\Printer\Connectors;

use App\Models\PosPrinter;
use App\Models\PosPrintJob;

class AgentPayloadConnector implements PrinterConnectorInterface
{
    public function send(PosPrinter $printer, string $rawData): array
    {
        $base64 = base64_encode($rawData);

        $job = PosPrintJob::create([
            'business_id' => $printer->business_id,
            'location_id' => $printer->location_id,
            'printer_id' => $printer->id,
            'document_type' => PosPrintJob::TYPE_RECEIPT,
            'payload_raw' => $base64,
            'status' => PosPrintJob::STATUS_PENDING,
            'created_by' => auth()->id(),
        ]);

        return [
            'success' => true,
            'message' => 'Job cetak berhasil dibuat untuk diteruskan ke Local POS Agent / Web Bluetooth.',
            'job_id' => $job->id,
            'base64_payload' => $base64,
            'printer_name' => $printer->name,
            'interface_address' => $printer->interface_address,
            'connection_type' => $printer->connection_type,
        ];
    }

    public function testConnection(PosPrinter $printer): array
    {
        return [
            'connected' => true,
            'message' => "Jalur Local Agent / Bluetooth [{$printer->name}] siap menerima tugas cetak.",
        ];
    }
}
