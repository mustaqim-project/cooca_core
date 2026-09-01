<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminUserController extends Controller
{
    /**
     * Display a listing of registered users.
     */
    public function index(Request $request): View
    {
        $query = User::with(['activeBusiness', 'businesses'])->latest();

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Export all users to CSV format (Nama, Email, Bisnis, Tanggal Daftar, Google Login).
     */
    public function export(): StreamedResponse
    {
        $fileName = 'users_export_'.now()->format('Ymd_His').'.csv';

        $users = User::with('activeBusiness')->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($users): void {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header Row
            fputcsv($file, [
                'ID',
                'Nama Lengkap',
                'Email',
                'Bisnis Aktif',
                'Metode Login',
                'Tanggal Registrasi',
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->activeBusiness?->name ?? 'Belum Diatur',
                    $user->google_id ? 'Google OAuth' : 'Email/Password',
                    $user->created_at?->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Remove the specified user from system.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "Pengguna {$user->name} berhasil dihapus.");
    }
}
