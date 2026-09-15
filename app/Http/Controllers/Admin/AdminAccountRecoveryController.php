<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Http\Controllers\Controller;
use App\Models\AccountRecoveryRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AdminAccountRecoveryController extends Controller
{
    /**
     * Tampilkan antrean permohonan pemulihan akun bagi Administrator.
     */
    public function index(Request $request): View
    {
        $status = (string) $request->get('status', 'all');
        $search = $request->get('search');

        $counts = [
            'all' => AccountRecoveryRequest::count(),
            'pending' => AccountRecoveryRequest::where('status', AccountRecoveryRequest::STATUS_PENDING)->count(),
            'approved' => AccountRecoveryRequest::where('status', AccountRecoveryRequest::STATUS_APPROVED)->count(),
            'rejected' => AccountRecoveryRequest::where('status', AccountRecoveryRequest::STATUS_REJECTED)->count(),
        ];

        $query = AccountRecoveryRequest::with(['user', 'business', 'approver', 'rejector'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('applicant_name', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%")
                    ->orWhere('old_email', 'like', "%{$search}%")
                    ->orWhere('new_email', 'like', "%{$search}%")
                    ->orWhere('old_phone', 'like', "%{$search}%")
                    ->orWhere('new_phone', 'like', "%{$search}%");
            });
        }

        $recoveries = $query->paginate(15)->withQueryString();

        return view('admin.account-recoveries.index', [
            'recoveries' => $recoveries,
            'counts' => $counts,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Tampilkan detail verifikasi bukti & dokumen permohonan pemulihan akun.
     */
    public function show(AccountRecoveryRequest $recovery): View
    {
        $recovery->load(['user.activeBusiness', 'business', 'approver', 'rejector']);

        return view('admin.account-recoveries.show', [
            'recovery' => $recovery,
        ]);
    }

    /**
     * Sajikan dokumen bukti pemulihan akun secara privat bagi Administrator terotentikasi.
     */
    public function document(AccountRecoveryRequest $recovery, string $type): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless(auth('admin')->check(), 403);

        $path = match ($type) {
            'identity' => $recovery->identity_card_path,
            'business' => $recovery->business_proof_path,
            'selfie' => $recovery->selfie_proof_path,
            default => null,
        };

        if (! $path) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        // Cek disk privat (local) terlebih dahulu, dengan fallback disk public untuk berkas legacy
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('local')->response($path);
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
        }

        abort(404, 'Berkas fisik dokumen tidak ditemukan di server.');
    }

    /**
     * Setujui permohonan pemulihan akun dan sinkronkan data pengguna.
     */
    public function approve(
        Request $request,
        AccountRecoveryRequest $recovery,
        AdminWhatsAppService $adminWa
    ): RedirectResponse {
        $admin = auth('admin')->user();
        abort_unless($admin !== null, 403);

        if ($recovery->status !== AccountRecoveryRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan ini telah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $recovery, $admin, $validated) {
            // Temukan user terkait
            $user = $recovery->user ?: User::where('email', $recovery->old_email)->first();

            if ($user) {
                $user->email = $recovery->new_email;
                $user->phone = $recovery->new_phone;
                $user->email_verified_at = now();
                $user->save();

                // Sinkronkan nomor pada profil bisnis aktif jika diperlukan
                if ($user->activeBusiness && (empty($user->activeBusiness->phone) || $user->activeBusiness->phone === $recovery->old_phone)) {
                    $user->activeBusiness->update(['phone' => $recovery->new_phone]);
                }
            }

            $recovery->update([
                'status' => AccountRecoveryRequest::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? 'Diverifikasi dan disetujui resmi oleh Administrator Platform.',
            ]);
        });

        // Kirim konfirmasi via WhatsApp ke nomor baru pemohon
        try {
            $loginUrl = route('login');
            $msg = "Halo *{$recovery->applicant_name}*,\n\n"
                . "Pengajuan pemulihan akun Cooca UMKM (Tiket: *{$recovery->ticket_number}*) untuk bisnis *{$recovery->business_name}* telah *DISETUJUI* oleh Administrator.\n\n"
                . "Data login Anda telah diperbarui ke:\n"
                . "- Email Baru: *{$recovery->new_email}*\n"
                . "- WhatsApp Baru: *{$recovery->new_phone}*\n\n"
                . "Email Anda telah ditandai terverifikasi. Silakan masuk kembali ke dashboard Cooca melalui:\n"
                . "{$loginUrl}\n\n"
                . "Terima kasih atas kerja samanya.";

            $adminWa->sendMessage($recovery->new_phone, $msg);
        } catch (\Throwable) {
            // Lanjutkan jika pengiriman WA terhambat (network/admin disconnected)
        }

        return redirect()->route('admin.account-recoveries.show', $recovery)
            ->with('success', "Permohonan #{$recovery->ticket_number} berhasil disetujui! Email & nomor WhatsApp pengguna telah diperbarui.");
    }

    /**
     * Tolak permohonan pemulihan akun dengan mencantumkan alasan.
     */
    public function reject(
        Request $request,
        AccountRecoveryRequest $recovery,
        AdminWhatsAppService $adminWa
    ): RedirectResponse {
        $admin = auth('admin')->user();
        abort_unless($admin !== null, 403);

        if ($recovery->status !== AccountRecoveryRequest::STATUS_PENDING) {
            return back()->with('warning', 'Pengajuan ini telah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'rejection_reason.required' => 'Wajib memberikan alasan penolakan permohonan.',
        ]);

        $recovery->update([
            'status' => AccountRecoveryRequest::STATUS_REJECTED,
            'rejected_by' => $admin->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Beritahu pemohon via WhatsApp ke nomor baru
        try {
            $msg = "Halo *{$recovery->applicant_name}*,\n\n"
                . "Mohon maaf, permohonan pemulihan akun Cooca UMKM (Tiket: *{$recovery->ticket_number}*) untuk bisnis *{$recovery->business_name}* *BELUM DAPAT DISETUJUI* oleh tim verifikasi.\n\n"
                . "Alasan:\n\"{$validated['rejection_reason']}\"\n\n"
                . "Silakan ajukan kembali dengan melampirkan berkas identitas & dokumen usaha yang jelas dan sesuai.";

            $adminWa->sendMessage($recovery->new_phone, $msg);
        } catch (\Throwable) {
            // Lanjutkan jika jaringan WA terhambat
        }

        return redirect()->route('admin.account-recoveries.show', $recovery)
            ->with('warning', "Permohonan #{$recovery->ticket_number} telah ditolak.");
    }
}
