<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CustomerWebController extends Controller
{
    /**
     * Display a unified listing of customers, members, and vouchers with segmented tabs.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $tab = (string) $request->get('tab', 'customers');
        if (! in_array($tab, ['customers', 'members', 'vouchers'], true)) {
            $tab = 'customers';
        }

        // Tab 1: Commercial & General Customers
        $customersQuery = Customer::where('business_id', $business->id)
            ->withCount(['invoices', 'purchaseOrders'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $customersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $customers = $customersQuery->paginate(15, ['*'], 'customers_page')->withQueryString();

        // Tab 2: CRM Members & Loyalty
        $membersQuery = Customer::where('business_id', $business->id)
            ->withCount('posOrders')
            ->latest('total_spent');

        if ($request->filled('tier') && $request->get('tier') !== 'all') {
            $tierVal = strtolower((string) $request->get('tier'));
            $membersQuery->whereRaw('LOWER(membership_tier) = ?', [$tierVal]);
        }

        if ($request->filled('segment') && $request->get('segment') !== 'all') {
            $membersQuery->where('segment', $request->get('segment'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $membersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('member_search')) {
            $mSearch = (string) $request->get('member_search');
            $membersQuery->where(function ($q) use ($mSearch) {
                $q->where('name', 'like', "%{$mSearch}%")
                    ->orWhere('phone', 'like', "%{$mSearch}%")
                    ->orWhere('email', 'like', "%{$mSearch}%");
            });
        }
        $members = $membersQuery->paginate(15, ['*'], 'members_page')->withQueryString();

        // Tab 3: Promotional Vouchers
        $vouchers = \App\Models\Voucher::where('business_id', $business->id)
            ->latest('created_at')
            ->paginate(15, ['*'], 'vouchers_page')
            ->withQueryString();

        // Unified High-Level Bento Metrics
        $totalCustomers = Customer::where('business_id', $business->id)->count();
        $totalCorporate = Customer::where('business_id', $business->id)
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->count();
        $avgPaymentTerms = (int) round((float) (Customer::where('business_id', $business->id)->avg('payment_terms_days') ?: 30));
        $totalPointsIssued = (int) Customer::where('business_id', $business->id)->sum('points_balance');
        $totalCreditReceivable = (float) Customer::where('business_id', $business->id)->sum('current_credit_balance');
        $totalVouchers = \App\Models\Voucher::where('business_id', $business->id)->count();
        $activeVouchers = \App\Models\Voucher::where('business_id', $business->id)->where('is_active', true)->count();

        return view('app.customers.index', compact(
            'business',
            'tab',
            'customers',
            'members',
            'vouchers',
            'totalCustomers',
            'totalCorporate',
            'avgPaymentTerms',
            'totalPointsIssued',
            'totalCreditReceivable',
            'totalVouchers',
            'activeVouchers'
        ));
    }

    /**
     * Store a new customer.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'code' => $validated['code'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'billing_address' => $validated['billing_address'] ?? null,
            'shipping_address' => $validated['shipping_address'] ?? null,
            'tax_identification_number' => $validated['tax_identification_number'] ?? null,
            'payment_terms_days' => (int) ($validated['payment_terms_days'] ?? 30),
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil ditambahkan.',
                'customer' => $customer,
            ], 201);
        }

        return redirect()->route('customers.index')->with('success', "Pelanggan '{$customer->name}' berhasil ditambahkan.");
    }

    /**
     * Update an existing customer.
     */
    public function update(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'tax_identification_number' => ['nullable', 'string', 'max:50'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pelanggan berhasil diperbarui.',
                'customer' => $customer,
            ]);
        }

        return redirect()->route('customers.index')->with('success', "Data pelanggan '{$customer->name}' berhasil diperbarui.");
    }

    /**
     * Delete a customer.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }
}
