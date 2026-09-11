<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

final class ProfileWebController extends Controller
{
    /**
     * Show user profile and password management page.
     */
    public function edit(): View
    {
        $user = Context::user();
        $business = Context::business();

        return view('app.profile.edit', compact('user', 'business'));
    }

    /**
     * Update user profile details.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Context::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp,jpg', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $trackingService = app(\App\Domain\Storage\StorageTrackingService::class);
        $avatarPath = $user->avatar;

        if ($request->boolean('remove_avatar') && $avatarPath) {
            if (! str_starts_with($avatarPath, 'http')) {
                $trackingService->deleteFile($avatarPath, 'public');
            }
            $avatarPath = null;
        } elseif ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $trackingService->assertCanUpload($user, (int) $file->getSize(), 'avatar');
            $oldAvatar = $avatarPath;
            $avatarPath = $file->store('avatars/' . $user->id, 'public');
            $trackingService->recordUpload(
                file: $file,
                filePath: $avatarPath,
                category: \App\Models\StorageFile::CATEGORY_OWNER_AVATAR,
                module: 'profile',
                owner: $user,
                uploader: $user
            );
            if ($oldAvatar && ! str_starts_with($oldAvatar, 'http')) {
                $trackingService->deleteFile($oldAvatar, 'public');
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'avatar' => $avatarPath,
        ]);

        return back()->with('success', 'Profil akun Anda berhasil diperbarui.');
    }

    /**
     * Update user password (Ganti Password).
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Context::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', PasswordRule::default()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
