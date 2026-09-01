<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CurrencyController extends Controller
{
    /**
     * List all supported currencies.
     */
    public function index(Request $request): JsonResponse
    {
        $currencies = Currency::orderBy('code')->get();

        return response()->json([
            'currencies' => CurrencyResource::collection($currencies),
        ], Response::HTTP_OK);
    }
}
