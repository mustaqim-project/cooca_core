<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

final class AdminProfileController extends Controller
{
    /**
     * Show admin profile and password management page.
     */
    public function index(): View
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        return view('admin.profile.index', compact('admin'));
    }

    /**
     * Update admin profile name and email.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:admins,email,' . $admin->id],
        ]);

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        return back()->with('success', 'Profil administrator berhasil diperbarui.');
    }

    /**
     * Update admin password (Ganti Kata Sandi).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed', PasswordRule::default()],
        ]);

        $admin->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi Administrator berhasil diubah.');
    }
}
