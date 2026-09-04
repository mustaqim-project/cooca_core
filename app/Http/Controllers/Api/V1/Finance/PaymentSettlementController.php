<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Finance;

use App\Domain\Finance\PaymentSettlementService;
use App\Http\Controllers\Controller;
use App\Models\PaymentSettlement;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class PaymentSettlementController extends Controller
{
    public function __construct(private readonly PaymentSettlementService $service = new PaymentSettlementService) {}

    public function index(): JsonResponse
    {
        $business = Context::requireBusiness();
        return response()->json(['settlements' => PaymentSettlement::with('allocations')->where('business_id', $business->id)->latest('settlement_date')->paginate(20)]);
    }

    public function show(PaymentSettlement $settlement): JsonResponse
    {
        $this->guard($settlement);
        return response()->json(['settlement' => $settlement->load('allocations')]);
    }

    public function reconcile(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'settlement_number' => ['required', 'string', 'max:64'],
            'settlement_date' => ['required', 'date'],
            'payment_channel' => ['required', 'string', 'max:50'],
            'gross_amount' => ['required', 'numeric', 'gt:0'],
            'fee_amount' => ['nullable', 'numeric', 'gte:0'],
            'net_amount' => ['nullable', 'numeric', 'gt:0'],
            'destination_bank' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.payment_type' => ['required', 'in:pos_order_payment,invoice_payment'],
            'allocations.*.payment_id' => ['required', 'string', 'max:64'],
            'allocations.*.amount' => ['required', 'numeric', 'gt:0'],
        ]);
        try {
            $settlement = $this->service->reconcile($business, $validated, $validated['allocations'], $request->user()?->id);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return response()->json(['message' => 'Settlement berhasil direkonsiliasi.', 'settlement' => $settlement], Response::HTTP_CREATED);
    }

    private function guard(PaymentSettlement $settlement): void
    {
        abort_unless($settlement->business_id === Context::requireBusiness()->id, Response::HTTP_NOT_FOUND);
    }
}
