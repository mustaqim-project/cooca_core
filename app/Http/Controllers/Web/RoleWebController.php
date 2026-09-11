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
        $this->authorizeView();
        $roles = Role::with('permissions')
            ->where(function ($query) use ($business): void {
                $query->whereNull('business_id')->orWhere('business_id', $business->id);
            })->orderBy('business_id')->orderBy('name')->get();
        $permissions = Permission::orderBy('category')->orderBy('name')->get()->groupBy('category');
        $members = $business->memberships()->with(['user', 'customRole'])->get();
        return view('app.roles.index', compact('business', 'roles', 'permissions', 'members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->authorizeManage();
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
        $this->authorizeManage();

        $isCustom = $role->business_id === $business->id;
        $isPreset = is_null($role->business_id);

        // Hanya custom role milik bisnis ini, atau preset role sistem yang boleh diedit
        abort_unless($isCustom || $isPreset, 403, 'Role tidak dapat diedit.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,slug'],
        ]);

        if ($isCustom) {
            // Custom role: boleh update nama, deskripsi, dan permissions
            $role->update(['name' => $validated['name'], 'description' => $validated['description'] ?? null]);
        }
        // Preset role: hanya update permissions (nama & slug terlindungi)
        $role->permissions()->sync(Permission::whereIn('slug', $validated['permissions'] ?? [])->pluck('id'));

        return back()->with('success', $isPreset
            ? 'Hak akses preset role berhasil diperbarui.'
            : 'Role dan permission berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $business = Context::requireBusiness();
        $this->authorizeManage();
        $this->assertCustomRole($role, $business->id);
        if ($role->memberships()->exists()) {
            return back()->withErrors(['role' => 'Role masih digunakan oleh anggota tim. Ubah role anggota terlebih dahulu.']);
        }
        $role->delete();
        return back()->with('success', 'Role custom berhasil dihapus.');
    }

    private function authorizeView(): void
    {
        abort_unless(Context::isOwner() || Context::hasPermission('roles.view'), 403, 'Anda tidak memiliki wewenang untuk melihat role dan hak akses.');
    }

    private function authorizeManage(): void
    {
        abort_unless(Context::isOwner() || Context::hasPermission('roles.manage'), 403, 'Hanya Owner atau pengguna dengan wewenang kelola role yang dapat mengatur role.');
    }

    private function assertCustomRole(Role $role, string $businessId): void
    {
        // Hanya custom role (business_id sesuai) yang bisa dihapus
        abort_unless($role->business_id === $businessId, 403, 'Hanya role custom milik bisnis ini yang dapat dihapus. Preset role sistem tidak dapat dihapus.');
    }
}
