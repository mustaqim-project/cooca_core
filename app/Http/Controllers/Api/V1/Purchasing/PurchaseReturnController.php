<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Domain\Purchasing\PurchaseReturnService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchaseReturn;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class PurchaseReturnController extends Controller
{
    public function __construct(private readonly PurchaseReturnService $service = new PurchaseReturnService) {}
    public function index(): JsonResponse { Context::requireBusiness(); return response()->json(['returns' => PurchaseReturn::with(['supplier', 'goodsReceipt', 'items'])->latest()->paginate(20)]); }
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate(['goods_receipt_id' => ['required', 'exists:goods_receipts,id'], 'reason' => ['required', 'string', 'max:255'], 'items' => ['required', 'array', 'min:1'], 'items.*.goods_receipt_item_id' => ['required', 'exists:goods_receipt_items,id'], 'items.*.quantity' => ['required', 'numeric', 'gt:0']]);
        $receipt = GoodsReceipt::findOrFail($validated['goods_receipt_id']);
        if ($receipt->business_id !== $business->id) return response()->json(['message' => 'Goods Receipt tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        try { $return = $this->service->createFromGoodsReceipt($receipt, $validated['items'], array_merge($validated, ['created_by' => $request->user()?->id])); }
        catch (InvalidArgumentException $exception) { return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY); }
        return response()->json(['return' => $return], Response::HTTP_CREATED);
    }
    public function approve(Request $request, PurchaseReturn $return): JsonResponse { $this->guard($return); return response()->json(['return' => $this->service->approve($return, $request->user()?->id)]); }
    public function complete(Request $request, PurchaseReturn $return): JsonResponse { $this->guard($return); try { return response()->json(['return' => $this->service->complete($return, $request->user()?->id)]); } catch (InvalidArgumentException $exception) { return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY); } }
    private function guard(PurchaseReturn $return): void { abort_unless($return->business_id === Context::requireBusiness()->id, Response::HTTP_NOT_FOUND); }
}
