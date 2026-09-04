<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class RoleWebController extends Controller
{
    public function index(): View
    {
        $business = Context::requireBusiness();
        $this->authorizeOwner();
        $roles = Role::with('permissions')
            ->where(function ($query) use ($business): void {
                $query->whereNull('business_id')->orWhere('business_id', $business->id);
            })->orderBy('business_id')->orderBy('name')->get();
        $permissions = Permission::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $members = $business->memberships()->with(['user', 'customRole'])->get();
        return view('app.settings.roles', compact('business', 'roles', 'permissions', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->authorizeOwner();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);
        $slug = Str::slug($validated['name']);
        if (Role::where('business_id', $business->id)->where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Nama role tersebut sudah digunakan di bisnis ini.']);
        }
        $role = Role::create([
            'business_id' => $business->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);
        $role->permissions()->sync(Permission::whereIn('slug', $validated['permissions'] ?? [])->pluck('id'));
        return back()->with('success', 'Role custom berhasil dibuat.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->authorizeOwner();
        $this->assertCustomRole($role, $business->id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);
        $role->update(['name' => $validated['name'], 'description' => $validated['description'] ?? null]);
        $role->permissions()->sync(Permission::whereIn('slug', $validated['permissions'] ?? [])->pluck('id'));
        return back()->with('success', 'Role dan permission berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->authorizeOwner();
        $this->assertCustomRole($role, $business->id);
        if ($role->memberships()->exists()) {
            return back()->withErrors(['role' => 'Role masih digunakan oleh anggota tim. Ubah role anggota terlebih dahulu.']);
        }
        $role->delete();
        return back()->with('success', 'Role custom berhasil dihapus.');
    }

    private function authorizeOwner(): void
    {
        abort_unless(Context::isOwner() && Context::hasPermission('roles.manage'), 403, 'Hanya Owner yang dapat mengatur role dan permission.');
    }

    private function assertCustomRole(Role $role, string $businessId): void
    {
        abort_unless($role->business_id === $businessId, 403, 'Role tidak berada pada bisnis aktif.');
    }
}
