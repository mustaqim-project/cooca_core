<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Currency\CurrencyConversionService;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ExchangeRateController extends Controller
{
    /**
     * List all exchange rates.
     */
    public function index(Request $request): JsonResponse
    {
        $rates = ExchangeRate::with(['fromCurrency', 'toCurrency'])
            ->latest('snapshot_date')
            ->get();

        return response()->json([
            'data' => ExchangeRateResource::collection($rates),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new exchange rate.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_currency_id' => ['required', 'string', 'exists:currencies,id'],
            'to_currency_id' => ['required', 'string', 'exists:currencies,id'],
            'rate' => ['required', 'numeric', 'gt:0'],
            'snapshot_date' => ['nullable', 'date'],
        ]);

        $business = Context::business();

        /** @var ExchangeRate $rate */
        $rate = ExchangeRate::create([
            'business_id' => $business?->id,
            'from_currency_id' => $validated['from_currency_id'],
            'to_currency_id' => $validated['to_currency_id'],
            'rate' => $validated['rate'],
            'snapshot_date' => $validated['snapshot_date'] ?? now()->toDateString(),
        ]);

        $rate->load(['fromCurrency', 'toCurrency']);

        return response()->json([
            'message' => 'Exchange rate recorded successfully.',
            'exchange_rate' => new ExchangeRateResource($rate),
        ], Response::HTTP_CREATED);
    }

    /**
     * Convert currency live.
     */
    public function convert(Request $request, CurrencyConversionService $service): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric'],
            'from_code' => ['required', 'string', 'exists:currencies,code'],
            'to_code' => ['required', 'string', 'exists:currencies,code'],
            'snapshot_date' => ['nullable', 'date'],
        ]);

        /** @var Currency $from */
        $from = Currency::where('code', $validated['from_code'])->firstOrFail();
        /** @var Currency $to */
        $to = Currency::where('code', $validated['to_code'])->firstOrFail();

        $converted = $service->convert(
            (float) $validated['amount'],
            $from,
            $to,
            $validated['snapshot_date'] ?? null
        );

        return response()->json([
            'original_amount' => (float) $validated['amount'],
            'from_currency' => $from->code,
            'to_currency' => $to->code,
            'converted_amount' => $converted,
        ], Response::HTTP_OK);
    }
}
