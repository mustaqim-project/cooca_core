<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AdminPaymentAccountController extends Controller
{
    /**
     * Display a listing of platform payment & bank accounts.
     */
    public function index(): View
    {
        // If empty, populate with default accounts
        if (PaymentAccount::count() === 0) {
            foreach (PaymentAccount::getDefaultAccounts() as $account) {
                PaymentAccount::create($account);
            }
        }

        $accounts = PaymentAccount::ordered()->get();
        $activeCount = $accounts->where('is_active', true)->count();
        $qrisCount = $accounts->where('type', PaymentAccount::TYPE_QRIS)->count();

        return view('admin.payment_accounts.index', compact('accounts', 'activeCount', 'qrisCount'));
    }

    /**
     * Show the form for creating a new payment account.
     */
    public function create(): View
    {
        return view('admin.payment_accounts.create');
    }

    /**
     * Store a newly created payment account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:32', 'unique:payment_accounts,bank_code'],
            'bank_name' => ['required', 'string', 'max:128'],
            'account_name' => ['required', 'string', 'max:128'],
            'account_number' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', Rule::in([PaymentAccount::TYPE_BANK_TRANSFER, PaymentAccount::TYPE_QRIS, PaymentAccount::TYPE_E_WALLET])],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:64'],
            'color' => ['nullable', 'string', 'max:32'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'qr_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ]);

        $bankCode = Str::slug(strtolower($validated['bank_code']), '_');

        $qrImagePath = null;
        if ($request->hasFile('qr_image')) {
            $qrImagePath = $request->file('qr_image')->store('qris-codes', 'public');
        }

        PaymentAccount::create([
            'bank_code' => $bankCode,
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'type' => $validated['type'],
            'instructions' => $validated['instructions'] ?? null,
            'icon' => $validated['icon'] ?: ($validated['type'] === PaymentAccount::TYPE_QRIS ? 'qr-code' : 'credit-card'),
            'color' => $validated['color'] ?: 'indigo',
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->has('is_active'),
            'qr_image_path' => $qrImagePath,
        ]);

        return redirect()->route('admin.payment-accounts.index')
            ->with('success', "Rekening {$validated['bank_name']} berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified payment account.
     */
    public function edit(PaymentAccount $paymentAccount): View
    {
        return view('admin.payment_accounts.edit', compact('paymentAccount'));
    }

    /**
     * Update the specified payment account.
     */
    public function update(Request $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:32', Rule::unique('payment_accounts', 'bank_code')->ignore($paymentAccount->id)],
            'bank_name' => ['required', 'string', 'max:128'],
            'account_name' => ['required', 'string', 'max:128'],
            'account_number' => ['required', 'string', 'max:64'],
            'type' => ['required', 'string', Rule::in([PaymentAccount::TYPE_BANK_TRANSFER, PaymentAccount::TYPE_QRIS, PaymentAccount::TYPE_E_WALLET])],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', 'string', 'max:64'],
            'color' => ['nullable', 'string', 'max:32'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'is_active' => ['nullable', 'boolean'],
            'qr_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ]);

        $bankCode = Str::slug(strtolower($validated['bank_code']), '_');

        $qrImagePath = $paymentAccount->qr_image_path;
        if ($request->hasFile('qr_image')) {
            if ($qrImagePath && Storage::disk('public')->exists($qrImagePath)) {
                Storage::disk('public')->delete($qrImagePath);
            }
            $qrImagePath = $request->file('qr_image')->store('qris-codes', 'public');
        }

        $paymentAccount->update([
            'bank_code' => $bankCode,
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'type' => $validated['type'],
            'instructions' => $validated['instructions'] ?? null,
            'icon' => $validated['icon'] ?: $paymentAccount->icon,
            'color' => $validated['color'] ?: $paymentAccount->color,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->has('is_active'),
            'qr_image_path' => $qrImagePath,
        ]);

        return redirect()->route('admin.payment-accounts.index')
            ->with('success', "Rekening {$paymentAccount->bank_name} berhasil diperbarui.");
    }

    /**
     * Toggle active/inactive status of payment account.
     */
    public function toggleStatus(PaymentAccount $paymentAccount): RedirectResponse
    {
        $paymentAccount->update(['is_active' => ! $paymentAccount->is_active]);

        $statusText = $paymentAccount->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Rekening {$paymentAccount->bank_name} berhasil {$statusText}.");
    }

    /**
     * Remove the specified payment account.
     */
    public function destroy(PaymentAccount $paymentAccount): RedirectResponse
    {
        if ($paymentAccount->qr_image_path && Storage::disk('public')->exists($paymentAccount->qr_image_path)) {
            Storage::disk('public')->delete($paymentAccount->qr_image_path);
        }

        $name = $paymentAccount->bank_name;
        $paymentAccount->delete();

        return redirect()->route('admin.payment-accounts.index')
            ->with('success', "Rekening {$name} telah dihapus.");
    }
}
