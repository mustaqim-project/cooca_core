<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Template\BusinessTemplateService;
use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessTypeTemplateResource;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class BusinessTypeTemplateController extends Controller
{
    /**
     * List all available industry presets.
     */
    public function index(Request $request): JsonResponse
    {
        $templates = BusinessTypeTemplate::all();

        return response()->json([
            'data' => BusinessTypeTemplateResource::collection($templates),
        ], Response::HTTP_OK);
    }

    /**
     * Show template details.
     */
    public function show(Request $request, BusinessTypeTemplate $businessTypeTemplate): JsonResponse
    {
        return response()->json([
            'template' => new BusinessTypeTemplateResource($businessTypeTemplate),
        ], Response::HTTP_OK);
    }

    /**
     * Apply industry template to a business.
     */
    public function apply(Request $request, Business $business, BusinessTemplateService $service): JsonResponse
    {
        $validated = $request->validate([
            'template_code' => ['required', 'string', 'exists:business_type_templates,code'],
        ]);

        /** @var BusinessTypeTemplate $template */
        $template = BusinessTypeTemplate::where('code', $validated['template_code'])->firstOrFail();

        $result = $service->apply($business, $template);

        return response()->json([
            'message' => "Template '{$template->name}' applied successfully to business.",
            'result' => $result,
        ], Response::HTTP_OK);
    }
}
