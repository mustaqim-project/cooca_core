<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Mail\DynamicMailConfig;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminPasswordResetController extends Controller
{
    /**
     * Display admin forgot password form.
     */
    public function showForgot(): View
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Send password reset link to admin email.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $admin = Admin::where('email', $request->email)->where('is_active', true)->first();

        if (! $admin) {
            return back()->withErrors([
                'email' => 'Kami tidak dapat menemukan administrator aktif dengan alamat email tersebut.',
            ])->onlyInput('email');
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $admin->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = route('admin.password.reset', [
            'token' => $token,
            'email' => $admin->email,
        ]);

        try {
            DynamicMailConfig::bootstrap();
            $appName = SystemSetting::get('app_name', 'Cooca UMKM');

            Mail::raw(
                "Halo {$admin->name},\n\nKami menerima permintaan untuk mereset kata sandi akun Administrator Anda pada sistem {$appName}.\n\nSilakan klik tautan di bawah ini untuk mengatur ulang kata sandi Anda:\n{$resetUrl}\n\nTautan ini akan kedaluwarsa dalam 60 menit.\nJika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini.\n\nSalam,\nTim Keamanan {$appName}",
                function ($message) use ($admin, $appName) {
                    $message->to($admin->email)
                        ->subject("[Reset Kata Sandi Admin] {$appName}");
                }
            );

            return back()->with('status', 'Tautan untuk mengatur ulang kata sandi telah dikirim ke email administrator Anda.');
        } catch (\Throwable $e) {
            return back()->withErrors([
                'email' => 'Gagal mengirim email reset password: ' . $e->getMessage(),
            ])->onlyInput('email');
        }
    }

    /**
     * Display admin reset password form.
     */
    public function showReset(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle resetting admin password.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors([
                'email' => 'Token reset kata sandi tidak valid atau telah kedaluwarsa.',
            ])->onlyInput('email');
        }

        // Check if token is older than 60 minutes
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors([
                'email' => 'Token reset kata sandi telah kedaluwarsa. Silakan ajukan permohonan baru.',
            ])->onlyInput('email');
        }

        $admin = Admin::where('email', $request->email)->first();

        if (! $admin) {
            return back()->withErrors([
                'email' => 'Akun administrator tidak ditemukan.',
            ])->onlyInput('email');
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('admin.login')->with('status', 'Kata sandi Administrator Anda telah berhasil direset. Silakan login kembali.');
    }
}
