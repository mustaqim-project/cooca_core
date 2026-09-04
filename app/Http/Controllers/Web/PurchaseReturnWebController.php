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
use Illuminate\View\View;
use InvalidArgumentException;

final class PurchaseReturnWebController extends Controller
{
    public function __construct(private readonly PurchaseReturnService $service = new PurchaseReturnService) {}
    public function index(): View { Context::requireBusiness(); return view('app.purchasing.returns.index', ['returns' => PurchaseReturn::with('supplier')->latest()->paginate(20)]); }
    public function create(): View { Context::requireBusiness(); return view('app.purchasing.returns.create', ['receipts' => GoodsReceipt::with(['supplier', 'items.product'])->where('status', 'completed')->latest()->limit(50)->get()]); }
    public function store(Request $request): RedirectResponse
    {
        $receipt = GoodsReceipt::findOrFail($request->string('goods_receipt_id')->toString());
        abort_unless($receipt->business_id === Context::requireBusiness()->id, 403);
        $validated = $request->validate(['goods_receipt_id' => ['required', 'exists:goods_receipts,id'], 'reason' => ['required', 'string', 'max:255'], 'items' => ['required', 'array', 'min:1'], 'items.*.goods_receipt_item_id' => ['required', 'exists:goods_receipt_items,id'], 'items.*.quantity' => ['required', 'numeric', 'gt:0']]);
        try { $return = $this->service->createFromGoodsReceipt($receipt, $validated['items'], array_merge($validated, ['created_by' => auth()->id()])); }
        catch (InvalidArgumentException $exception) { return back()->withInput()->withErrors(['items' => $exception->getMessage()]); }
        return redirect()->route('purchase.returns.show', $return)->with('success', 'Draft retur pembelian dibuat.');
    }
    public function show(PurchaseReturn $return): View { abort_unless($return->business_id === Context::requireBusiness()->id, 403); return view('app.purchasing.returns.show', ['return' => $return->load(['supplier', 'goodsReceipt', 'items'])]); }
    public function approve(PurchaseReturn $return): RedirectResponse { abort_unless($return->business_id === Context::requireBusiness()->id, 403); $this->service->approve($return, auth()->id()); return back()->with('success', 'Retur disetujui.'); }
    public function complete(PurchaseReturn $return): RedirectResponse { abort_unless($return->business_id === Context::requireBusiness()->id, 403); try { $this->service->complete($return, auth()->id()); } catch (InvalidArgumentException $exception) { return back()->withErrors(['return' => $exception->getMessage()]); } return back()->with('success', 'Retur selesai dan stok dikurangi.'); }
}
