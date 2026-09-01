<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\SetActiveBusinessRequest;
use App\Http\Requests\Business\StoreBusinessRequest;
use App\Http\Resources\BusinessResource;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class BusinessController extends Controller
{
    /**
     * List all businesses where current user is a member.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $businesses = $user->businesses()->get();

        return response()->json([
            'businesses' => BusinessResource::collection($businesses),
        ], Response::HTTP_OK);
    }

    /**
     * Create a new business and attach current user as owner.
     */
    public function store(StoreBusinessRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        /** @var Business $business */
        $business = Business::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $membershipId = (string) Str::uuid();
        $business->users()->attach($user->id, [
            'id' => $membershipId,
            'role' => 'owner',
        ]);

        if ($user->active_business_id === null) {
            $user->update(['active_business_id' => $business->id]);
        }

        return response()->json([
            'message' => 'Business created successfully.',
            'business' => new BusinessResource($business),
        ], Response::HTTP_CREATED);
    }

    /**
     * Show business details by ID or slug.
     */
    public function show(Request $request, Business $business): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $isMember = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $isMember) {
            return response()->json([
                'message' => 'You do not have access to this business.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'business' => new BusinessResource($business),
        ], Response::HTTP_OK);
    }

    /**
     * Set active business for current user session/context.
     */
    public function setActive(SetActiveBusinessRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $businessId = $request->validated()['business_id'];

        /** @var BusinessMembership|null $membership */
        $membership = BusinessMembership::where('business_id', $businessId)
            ->where('user_id', $user->id)
            ->first();

        if ($membership === null) {
            return response()->json([
                'message' => 'You are not a member of the specified business.',
            ], Response::HTTP_FORBIDDEN);
        }

        $business = Business::find($businessId);
        $user->update(['active_business_id' => $businessId]);
        Context::setBusiness($business, $membership);

        return response()->json([
            'message' => 'Active business updated successfully.',
            'active_business' => new BusinessResource($business),
        ], Response::HTTP_OK);
    }
}
