<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Finance\MerchantPayoutAccountService;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\MerchantPayoutBankAccount;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class MerchantPayoutAccountWebController extends Controller
{
    public function __construct(
        private readonly MerchantPayoutAccountService $service = new MerchantPayoutAccountService
    ) {}

    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $accounts = MerchantPayoutBankAccount::where('business_id', $business->id)
            ->with('location')
            ->orderByDesc('is_primary')
            ->latest()
            ->get();

        $locations = Location::where('business_id', $business->id)->where('is_active', true)->orderBy('name')->get();
        $owner = $business->owner;
        $expectedOwnerName = trim((string) ($owner?->name ?? $business->name));

        return view('app.finance.payout-accounts.index', compact(
            'business',
            'accounts',
            'locations',
            'expectedOwnerName'
        ));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:30'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'min:6', 'max:50'],
            'account_holder_name' => ['required', 'string', 'min:2', 'max:150'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        try {
            $account = $this->service->registerAccount($business, $validated, auth()->id());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Rekening penarikan berhasil diverifikasi dan didaftarkan.',
                    'account' => $account,
                ]);
            }

            return redirect()->route('finance.payout-accounts.index')
                ->with('success', "Rekening {$account->bank_name} ({$account->account_number}) atas nama {$account->account_holder_name} berhasil diverifikasi dan terdaftar.");
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['account_holder_name' => $e->getMessage()]);
        }
    }

    public function setPrimary(MerchantPayoutBankAccount $account): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($account->business_id !== $business->id) {
            abort(403);
        }

        $this->service->setPrimary($account);

        return back()->with('success', "Rekening {$account->bank_name} ({$account->account_number}) berhasil dijadikan rekening utama pencairan.");
    }

    public function destroy(MerchantPayoutBankAccount $account): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($account->business_id !== $business->id) {
            abort(403);
        }

        $this->service->deleteAccount($account);

        return back()->with('success', 'Rekening penarikan berhasil dihapus.');
    }
}
