<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SupplierWebController extends Controller
{
    /**
     * Display a listing of suppliers with search and material counts.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();

        $query = Supplier::withCount('materials')
            ->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();

        return view('app.suppliers.index', compact('business', 'suppliers'));
    }

    /**
     * Store a new supplier (via regular form or AJAX modal).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier = Supplier::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('purchasing.supplier.messages.created_success'),
                'supplier' => $supplier,
            ], 201);
        }

        return back()->with('success', __('purchasing.supplier.messages.created_success'));
    }

    /**
     * Update an existing supplier.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($supplier->business_id === $business->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('purchasing.supplier.messages.updated_success', ['name' => $supplier->name]),
                'supplier' => $supplier,
            ]);
        }

        return redirect()->route('suppliers.index')->with('success', __('purchasing.supplier.messages.updated_success', ['name' => $supplier->name]));
    }

    /**
     * Delete a supplier.
     */
    public function destroy(Supplier $supplier): RedirectResponse|JsonResponse
    {
        $business = Context::requireBusiness();
        abort_unless($supplier->business_id === $business->id, 403);

        $supplier->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('purchasing.supplier.messages.deleted_success'),
            ]);
        }

        return back()->with('success', __('purchasing.supplier.messages.deleted_success'));
    }
}
