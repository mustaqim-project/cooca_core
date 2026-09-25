<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Pos;

use App\Domain\Printer\PrintJobService;
use App\Http\Controllers\Controller;
use App\Models\PosPrinter;
use App\Models\PosPrintJob;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PosAgentApiController extends Controller
{
    public function __construct(
        private readonly PrintJobService $jobService = new PrintJobService()
    ) {}

    /**
     * Get pending print jobs waiting for Local POS Agent execution.
     */
    public function getPendingJobs(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $locationId = $request->query('location_id');

        $jobs = $this->jobService->getPendingJobsForAgent($business, $locationId);

        return response()->json([
            'success' => true,
            'count' => $jobs->count(),
            'jobs' => $jobs->map(fn(PosPrintJob $job) => [
                'id' => $job->id,
                'document_type' => $job->document_type,
                'document_id' => $job->document_id,
                'printer' => [
                    'id' => $job->printer?->id,
                    'name' => $job->printer?->name,
                    'connection_type' => $job->printer?->connection_type,
                    'interface_address' => $job->printer?->interface_address,
                    'port' => $job->printer?->port,
                    'paper_width' => $job->printer?->paper_width,
                ],
                'payload_base64' => $job->payload_raw,
                'created_at' => $job->created_at?->toIso8601String(),
            ]),
        ], Response::HTTP_OK);
    }

    /**
     * Report print job completion or failure back to server.
     */
    public function updateJobStatus(Request $request, PosPrintJob $job): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($job->business_id !== $business->id) {
            return response()->json(['message' => 'Job tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:printed,failed'],
            'error_message' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === PosPrintJob::STATUS_PRINTED) {
            $job->markAsPrinted();
        } else {
            $job->markAsFailed($validated['error_message'] ?? 'Gagal dicetak oleh Local POS Agent.');
        }

        return response()->json([
            'success' => true,
            'message' => "Status job #{$job->id} diperbarui ke {$job->status}.",
            'job' => $job,
        ], Response::HTTP_OK);
    }

    /**
     * Sync printer hardware status (online, offline, paper out) from Local POS Agent.
     */
    public function syncDeviceStatus(Request $request, PosPrinter $printer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($printer->business_id !== $business->id) {
            return response()->json(['message' => 'Printer tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:online,offline,error'],
            'error_message' => ['nullable', 'string'],
        ]);

        $printer->update([
            'last_status' => $validated['status'],
            'last_status_checked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status perangkat berhasil disinkronkan.',
            'printer' => $printer,
        ], Response::HTTP_OK);
    }

    /**
     * Sync general device status from Local POS Agent.
     */
    public function syncDevice(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'location_id' => ['nullable', 'uuid'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'agent_version' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status agen perangkat lokal berhasil disinkronkan.',
        ], Response::HTTP_OK);
    }

    /**
     * Get configured printers list for Local POS Agent initialization.
     */
    public function getPrinters(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $locationId = $request->query('location_id');

        $printers = PosPrinter::where('business_id', $business->id)
            ->active()
            ->forLocation($locationId)
            ->get();

        return response()->json([
            'success' => true,
            'printers' => $printers,
        ], Response::HTTP_OK);
    }
}
