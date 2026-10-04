<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Purchasing\PurchaseReturnService;
use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\PurchaseReturn;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use InvalidArgumentException;

final class PurchaseReturnWebController extends Controller
{
    public function __construct(private readonly PurchaseReturnService $service = new PurchaseReturnService) {}

    public function index(): View
    {
        $business = Context::requireBusiness();

        return view('app.purchasing.returns.index', [
            'business' => $business,
            'returns' => PurchaseReturn::where('business_id', $business->id)
                ->with(['supplier', 'goodsReceipt'])
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $business = Context::requireBusiness();

        return view('app.purchasing.returns.create', [
            'business' => $business,
            'receipts' => GoodsReceipt::where('business_id', $business->id)
                ->with(['supplier', 'items.product', 'items.material'])
                ->where('status', 'completed')
                ->latest()
                ->limit(50)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $businessId = $business->id;

        $validated = $request->validate([
            'goods_receipt_id' => ['required', Rule::exists('goods_receipts', 'id')->where('business_id', $businessId)],
            'reason' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.goods_receipt_item_id' => [
                'required',
                Rule::exists('goods_receipt_items', 'id')->where(function ($query) use ($request): void {
                    $query->where('goods_receipt_id', $request->input('goods_receipt_id'));
                }),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ]);

        $receipt = GoodsReceipt::where('business_id', $businessId)->findOrFail($validated['goods_receipt_id']);

        try {
            $return = $this->service->createFromGoodsReceipt($receipt, $validated['items'], array_merge($validated, [
                'created_by' => auth()->id(),
            ]));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['items' => $exception->getMessage()]);
        }

        return redirect()->route('purchase.returns.show', $return)->with('success', __('purchasing.messages.return_created'));
    }

    public function show(PurchaseReturn $return): View
    {
        $business = Context::requireBusiness();
        abort_unless($return->business_id === $business->id, 403);

        return view('app.purchasing.returns.show', [
            'business' => $business,
            'return' => $return->load(['supplier', 'goodsReceipt', 'items.product', 'items.material']),
        ]);
    }

    public function approve(Request $request, PurchaseReturn $return): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($return->business_id === $business->id, 403);

        if (! empty($business->pos_supervisor_pin)) {
            $pin = $request->input('pin') ?? $request->input('supervisor_pin');
            if (empty($pin) || ! Hash::check((string) $pin, $business->pos_supervisor_pin)) {
                return back()->withErrors(['pin' => __('purchasing.messages.invalid_supervisor_pin')]);
            }
        }

        $this->service->approve($return, auth()->id());

        return back()->with('success', __('purchasing.messages.return_approved'));
    }

    public function complete(Request $request, PurchaseReturn $return): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($return->business_id === $business->id, 403);

        if (! empty($business->pos_supervisor_pin)) {
            $pin = $request->input('pin') ?? $request->input('supervisor_pin');
            if (empty($pin) || ! Hash::check((string) $pin, $business->pos_supervisor_pin)) {
                return back()->withErrors(['pin' => __('purchasing.messages.invalid_supervisor_pin')]);
            }
        }

        try {
            $this->service->complete($return, auth()->id());
        } catch (\Throwable $exception) {
            return back()->withErrors(['return' => $exception->getMessage()]);
        }

        return back()->with('success', __('purchasing.messages.return_completed'));
    }
}
