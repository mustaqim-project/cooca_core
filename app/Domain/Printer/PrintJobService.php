<?php

declare(strict_types=1);

namespace App\Domain\Printer;

use App\Domain\Printer\Connectors\FileConnector;
use App\Domain\Printer\Connectors\NetworkConnector;
use App\Domain\Printer\Connectors\WindowsConnector;
use App\Models\Business;
use App\Models\PosPrinter;
use App\Models\PosPrintJob;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class PrintJobService
{
    /**
     * Create a new asynchronous or queued print job.
     */
    public function createJob(
        Business $business,
        ?PosPrinter $printer,
        string $documentType,
        string $payloadRaw,
        ?string $documentId = null,
        ?string $locationId = null
    ): PosPrintJob {
        return PosPrintJob::create([
            'business_id' => $business->id,
            'location_id' => $locationId ?? $printer?->location_id,
            'printer_id' => $printer?->id,
            'document_type' => $documentType,
            'document_id' => $documentId,
            'payload_raw' => $payloadRaw,
            'status' => PosPrintJob::STATUS_PENDING,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Execute/dispatch a queued print job directly to hardware.
     *
     * @return array{
     *     success: bool,
     *     message: string
     * }
     */
    public function processJob(PosPrintJob $job): array
    {
        $printer = $job->printer;
        if (! $printer) {
            $job->markAsFailed('Printer tidak ditemukan atau telah dihapus.');
            return ['success' => false, 'message' => 'Printer tidak ditemukan.'];
        }

        $job->update(['status' => PosPrintJob::STATUS_PROCESSING]);

        $connector = match ($printer->connection_type) {
            PosPrinter::TYPE_LAN, PosPrinter::TYPE_WIFI => new NetworkConnector(),
            PosPrinter::TYPE_WINDOWS => new WindowsConnector(),
            PosPrinter::TYPE_SERIAL, PosPrinter::TYPE_USB => new FileConnector(),
            default => null,
        };

        if (!$connector) {
            // For agent-type printers, job remains pending until polled by Local Agent
            return [
                'success' => true,
                'message' => 'Job antrean siap diambil oleh Local POS Agent.',
            ];
        }

        try {
            $rawBytes = base64_decode($job->payload_raw, true) ?: $job->payload_raw;
            $result = $connector->send($printer, (string) $rawBytes);

            if ($result['success']) {
                $job->markAsPrinted();
            } else {
                $job->markAsFailed($result['message']);
            }

            return $result;
        } catch (Throwable $e) {
            $job->markAsFailed($e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memproses job cetak: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get pending print jobs waiting for Local POS Agent sync.
     *
     * @return Collection<int, PosPrintJob>
     */
    public function getPendingJobsForAgent(Business $business, ?string $locationId = null): Collection
    {
        $query = PosPrintJob::where('business_id', $business->id)
            ->where('status', PosPrintJob::STATUS_PENDING)
            ->with(['printer', 'location'])
            ->oldest('created_at');

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->where('location_id', $locationId)
                  ->orWhereNull('location_id');
            });
        }

        return $query->limit(20)->get();
    }
}
