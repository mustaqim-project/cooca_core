<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SupplierController extends Controller
{
    /**
     * List all suppliers for active business.
     */
    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::latest()->paginate(25);

        return response()->json([
            'data' => SupplierResource::collection($suppliers),
            'pagination' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Create a new supplier.
     */
    public function store(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        /** @var Supplier $supplier */
        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'Supplier created successfully.',
            'supplier' => new SupplierResource($supplier),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show supplier details.
     */
    public function show(Request $request, Supplier $supplier): JsonResponse
    {
        return response()->json([
            'supplier' => new SupplierResource($supplier),
        ], Response::HTTP_OK);
    }

    /**
     * Update supplier details.
     */
    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Supplier updated successfully.',
            'supplier' => new SupplierResource($supplier),
        ], Response::HTTP_OK);
    }

    /**
     * Delete supplier.
     */
    public function destroy(Request $request, Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully.',
        ], Response::HTTP_OK);
    }
}
