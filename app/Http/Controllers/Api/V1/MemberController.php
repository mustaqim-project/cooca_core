<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Billing\EntitlementService;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Role;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class MemberController extends Controller
{
    public function __construct(
        private readonly EntitlementService $entitlementService
    ) {}

    /**
     * List all members of the business with role and permissions.
     */
    public function index(Request $request, Business $business): JsonResponse
    {
        $sub = $this->entitlementService->getSubscription($business);
        $userCount = $business->users()->count();
        $isCore = $sub->isCorePlan();
        $canAddMore = $isCore || $userCount < 1;

        $members = $business->users()->get()->map(function (User $user) use ($business) {
            $roleSlug = $user->pivot->role;
            $roleModel = Role::where('slug', $roleSlug)->first();

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'avatar' => $user->avatar ?? null,
                'role' => $roleSlug,
                'role_label' => $roleModel?->name ?? ucfirst($roleSlug),
                'role_description' => $roleModel?->description,
                'is_owner' => $roleSlug === 'owner',
                'joined_at' => $user->pivot->created_at?->toIso8601String(),
            ];
        });

        $availableRoles = Role::select('name', 'slug', 'description')->get();

        return response()->json([
            'success' => true,
            'members' => $members,
            'available_roles' => $availableRoles,
            'quota' => [
                'used_users' => $userCount,
                'max_users' => $isCore ? null : 1,
                'is_core' => $isCore,
                'can_add_more' => $canAddMore,
                'plan_name' => $isCore ? 'Cooca Core' : 'Free Plan',
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Add a member to the business with Entitlement quota enforcement.
     */
    public function store(Request $request, Business $business): JsonResponse
    {
        // Enforce Entitlement limit (Free Plan allows max 1 user)
        if (! $this->entitlementService->canAddMember($business)) {
            return response()->json([
                'success' => false,
                'error_code' => 'RESOURCE_LIMIT_EXCEEDED',
                'message' => 'Paket Free Plan dibatasi untuk 1 pengguna (Solo Owner). Silakan upgrade ke Cooca Core untuk menambahkan karyawan tanpa batas.',
                'upgrade_url' => '/billing/limits',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:owner,admin,cashier,warehouse,finance,staff,viewer'],
        ]);

        /** @var User|null $targetUser */
        $targetUser = User::where('email', $validated['email'])->first();

        // If user does not exist, create new user account
        if ($targetUser === null) {
            $initialPassword = $validated['password'] ?? Str::random(10);
            $targetUser = User::create([
                'id' => (string) Str::uuid(),
                'name' => $validated['name'] ?? explode('@', $validated['email'])[0],
                'email' => $validated['email'],
                'password' => Hash::make($initialPassword),
                'active_business_id' => $business->id,
                'onboarding_completed' => true,
            ]);
        }

        $alreadyMember = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $targetUser->id)
            ->exists();

        if ($alreadyMember) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna ini sudah terdaftar sebagai anggota bisnis ini.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $business->users()->attach($targetUser->id, [
            'id' => (string) Str::uuid(),
            'role' => $validated['role'],
        ]);

        $roleModel = Role::where('slug', $validated['role'])->first();

        return response()->json([
            'success' => true,
            'message' => "Karyawan {$targetUser->name} berhasil ditambahkan sebagai {$validated['role']}.",
            'member' => [
                'user_id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
                'role' => $validated['role'],
                'role_label' => $roleModel?->name ?? ucfirst($validated['role']),
                'is_owner' => $validated['role'] === 'owner',
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Update a member's role.
     */
    public function update(Request $request, Business $business, string $userId): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:owner,admin,cashier,warehouse,finance,staff,viewer'],
        ]);

        /** @var BusinessMembership|null $membership */
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $userId)
            ->first();

        if ($membership === null) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota bisnis tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Protect last owner
        if ($membership->role === 'owner' && $validated['role'] !== 'owner') {
            $ownerCount = BusinessMembership::where('business_id', $business->id)
                ->where('role', 'owner')
                ->count();
            if ($ownerCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah peran satu-satunya Owner bisnis.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $membership->update(['role' => $validated['role']]);
        $roleModel = Role::where('slug', $validated['role'])->first();

        return response()->json([
            'success' => true,
            'message' => 'Peran anggota berhasil diperbarui.',
            'role' => $validated['role'],
            'role_label' => $roleModel?->name ?? ucfirst($validated['role']),
        ], Response::HTTP_OK);
    }

    /**
     * Remove a member from the business.
     */
    public function destroy(Request $request, Business $business, string $userId): JsonResponse
    {
        /** @var BusinessMembership|null $membership */
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $userId)
            ->first();

        if ($membership === null) {
            return response()->json([
                'success' => false,
                'message' => 'Anggota bisnis tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Protect last owner
        if ($membership->role === 'owner') {
            $ownerCount = BusinessMembership::where('business_id', $business->id)
                ->where('role', 'owner')
                ->count();
            if ($ownerCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus satu-satunya Owner bisnis.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $membership->delete();

        return response()->json([
            'success' => true,
            'message' => 'Anggota berhasil dihapus dari workspace bisnis.',
        ], Response::HTTP_OK);
    }
}

