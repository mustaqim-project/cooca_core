<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuditLogController extends Controller
{
    /**
     * List audit logs for current active business.
     */
    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::latest('created_at')->paginate(25);

        return response()->json([
            'data' => AuditLogResource::collection($logs),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ], Response::HTTP_OK);
    }
}
