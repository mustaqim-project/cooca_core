<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common;

use App\Domain\Storage\AdminStorage;
use App\Http\Controllers\Controller;
use App\Models\PaymentSettlement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class SettlementProofController extends Controller
{
    /**
     * Display or stream the protected payout transfer proof image.
     * Strictly authorized to Superadmin (guard: admin) OR the Merchant Owner/Staff (guard: web).
     */
    public function show(Request $request, PaymentSettlement $settlement): BinaryFileResponse
    {
        $isAdmin = Auth::guard('admin')->check();
        $isMerchant = false;

        if (! $isAdmin && Auth::guard('web')->check()) {
            /** @var User $user */
            $user = Auth::guard('web')->user();
            if ($user && ($user->current_business_id === $settlement->business_id || $user->business_id === $settlement->business_id)) {
                $isMerchant = true;
            }
        }

        abort_unless($isAdmin || $isMerchant, 403, 'Akses bukti transfer ditolak. Dokumen rahasia keuangan hanya dapat diakses oleh Admin atau Merchant bersangkutan.');

        $path = $settlement->proof_image_path;
        abort_unless(! empty($path), 404, 'Bukti transfer belum diunggah.');

        // 1. Check private disk ('local')
        if (Storage::disk(AdminStorage::DISK_PRIVATE)->exists($path)) {
            return Storage::disk(AdminStorage::DISK_PRIVATE)->response($path);
        }

        // 2. Check public disk fallback (for legacy proofs)
        if (Storage::disk(AdminStorage::DISK_PUBLIC)->exists($path)) {
            return Storage::disk(AdminStorage::DISK_PUBLIC)->response($path);
        }

        // 3. Check direct public_path
        if (file_exists(public_path($path))) {
            return response()->file(public_path($path));
        }

        abort(404, 'Berkas fisik bukti transfer tidak ditemukan di server.');
    }
}
