<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Crm;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPointHistory;
use App\Models\LoyaltyProgram;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CrmLoyaltyController extends Controller
{
    /**
     * Get loyalty program info.
     */
    public function program(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $program = LoyaltyProgram::where('business_id', $business->id)->first();

        if (!$program) {
            return response()->json([
                'program' => null,
                'message' => 'Program loyalitas belum dikonfigurasi.',
            ], Response::HTTP_OK);
        }

        $stats = [
            'total_members' => Customer::where('business_id', $business->id)->where('points_balance', '>', 0)->count(),
            'total_points_issued' => (int) CustomerPointHistory::where('business_id', $business->id)->where('points_change', '>', 0)->sum('points_change'),
            'total_points_redeemed' => abs((int) CustomerPointHistory::where('business_id', $business->id)->where('points_change', '<', 0)->sum('points_change')),
        ];

        return response()->json([
            'program' => $program,
            'stats' => $stats,
        ], Response::HTTP_OK);
    }

    /**
     * Get customer loyalty account and transaction history.
     */
    public function customerAccount(Request $request, Customer $customer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($customer->business_id !== $business->id) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $transactions = CustomerPointHistory::where('customer_id', $customer->id)
            ->latest()
            ->limit(30)
            ->get();

        return response()->json([
            'customer' => $customer->only(['id', 'name', 'code', 'phone', 'email', 'membership_tier']),
            'points_balance' => (int) $customer->points_balance,
            'total_spent' => (float) $customer->total_spent,
            'transactions' => $transactions,
        ], Response::HTTP_OK);
    }

    /**
     * Manually adjust loyalty points.
     */
    public function adjustPoints(Request $request, Customer $customer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($customer->business_id !== $business->id) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:add,deduct'],
            'points' => ['required', 'integer', 'min:1'],
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $points = (int) $validated['points'];
        $type = (string) $validated['type'];
        $notes = (string) $validated['notes'];

        if ($type === 'deduct' && $customer->points_balance < $points) {
            return response()->json([
                'message' => "Poin tidak mencukupi. Saldo: {$customer->points_balance} poin.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $delta = $type === 'add' ? $points : -$points;
        $customer->increment('points_balance', $delta);

        CustomerPointHistory::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'type' => CustomerPointHistory::TYPE_MANUAL,
            'points_change' => $delta,
            'balance_after' => $customer->fresh()->points_balance,
            'notes' => $notes,
        ]);

        return response()->json([
            'message' => ($type === 'add' ? 'Penambahan' : 'Pengurangan') . " {$points} poin berhasil.",
            'new_balance' => (int) $customer->fresh()->points_balance,
        ], Response::HTTP_OK);
    }
}
