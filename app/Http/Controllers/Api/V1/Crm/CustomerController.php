<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Crm;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CustomerController extends Controller
{
    /**
     * List customers with loyalty info.
     */
    public function index(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $query = Customer::where('business_id', $business->id)
            ->withCount(['invoices', 'posOrders'])
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('membership_tier')) {
            $query->where('membership_tier', $request->get('membership_tier'));
        }

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        $customers = $query->paginate(20);

        return response()->json([
            'customers' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Create new customer.
     */
    public function store(Request $request): JsonResponse
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
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create([
            'business_id' => $business->id,
            ...$validated,
            'payment_terms_days' => (int) ($validated['payment_terms_days'] ?? 30),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => "Pelanggan '{$customer->name}' berhasil ditambahkan.",
            'customer' => $customer,
        ], Response::HTTP_CREATED);
    }

    /**
     * Show customer detail with statistics.
     */
    public function show(Request $request, Customer $customer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($customer->business_id !== $business->id) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $customer->loadCount(['invoices', 'posOrders'])
            ->load(['invoices' => fn($q) => $q->latest()->limit(5)]);

        $totalSpent = (float) $customer->total_spent;
        $avgOrderValue = $customer->invoices_count > 0
            ? $totalSpent / $customer->invoices_count
            : 0.0;

        return response()->json([
            'customer' => $customer,
            'stats' => [
                'total_spent' => $totalSpent,
                'total_invoices' => $customer->invoices_count,
                'total_pos_orders' => $customer->pos_orders_count,
                'avg_order_value' => round($avgOrderValue, 2),
                'points_balance' => (int) $customer->points_balance,
                'current_credit_balance' => (float) $customer->current_credit_balance,
                'credit_limit' => (float) $customer->credit_limit,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Update customer.
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($customer->business_id !== $business->id) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

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
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => "Data pelanggan '{$customer->name}' berhasil diperbarui.",
            'customer' => $customer->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Delete customer.
     */
    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        $business = Context::requireBusiness();
        if ($customer->business_id !== $business->id) {
            return response()->json(['message' => 'Pelanggan tidak ditemukan.'], Response::HTTP_NOT_FOUND);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Pelanggan berhasil dihapus.',
        ], Response::HTTP_OK);
    }
}
