<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Sales;

use App\Domain\Commerce\SalesReturnService;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\SalesReturn;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class SalesReturnController extends Controller
{
    public function __construct(private readonly SalesReturnService $service = new SalesReturnService) {}
    public function index(): JsonResponse { Context::requireBusiness(); return response()->json(['returns' => SalesReturn::with(['invoice', 'posOrder', 'items'])->latest()->paginate(20)]); }
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate(['invoice_id' => ['required', 'exists:invoices,id'], 'reason' => ['required', 'string', 'max:255'], 'refund_method' => ['nullable', 'in:credit_note,cash_refund,store_credit'], 'items' => ['required', 'array', 'min:1'], 'items.*.invoice_item_id' => ['required', 'exists:invoice_items,id'], 'items.*.quantity' => ['required', 'numeric', 'gt:0']]);
        $invoice = Invoice::findOrFail($validated['invoice_id']);
        if ($invoice->business_id !== $business->id) return response()->json(['message' => 'Invoice tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        try { $return = $this->service->createFromInvoice($invoice, $validated['items'], array_merge($validated, ['created_by' => $request->user()?->id])); }
        catch (InvalidArgumentException $exception) { return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY); }
        return response()->json(['return' => $return], Response::HTTP_CREATED);
    }
    public function approve(Request $request, SalesReturn $return): JsonResponse { $this->guard($return); return response()->json(['return' => $this->service->approve($return, $request->user()?->id)]); }
    public function complete(Request $request, SalesReturn $return): JsonResponse { $this->guard($return); try { return response()->json(['return' => $this->service->complete($return, $request->user()?->id)]); } catch (InvalidArgumentException $exception) { return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY); } }
    private function guard(SalesReturn $return): void { abort_unless($return->business_id === Context::requireBusiness()->id, Response::HTTP_NOT_FOUND); }
}
