<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccountRecoveryRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AccountRecoveryWebController extends Controller
{
    /**
     * Tampilkan formulir pengajuan pemulihan akses akun.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = auth('web')->user();

        // Jika user sudah memiliki permohonan yang sedang berjalan, arahkan langsung ke halaman status tiketnya
        if ($user) {
            $activeRequest = AccountRecoveryRequest::where('user_id', $user->id)
                ->where('status', AccountRecoveryRequest::STATUS_PENDING)
                ->latest('created_at')
                ->first();

            if ($activeRequest) {
                return redirect()->route('account-recovery.status', $activeRequest->ticket_number)
                    ->with('info', 'Anda telah memiliki permohonan pemulihan akses akun yang sedang dalam peninjauan.');
            }
        }

        $business = $user?->activeBusiness ?: $user?->businesses()->first();

        return view('auth.account-recovery.create', [
            'user' => $user,
            'prefill' => [
                'applicant_name' => $user?->name ?? old('applicant_name', ''),
                'business_name' => $business?->name ?? old('business_name', ''),
                'old_email' => $user?->email ?? old('old_email', ''),
                'old_phone' => $user?->phone ?? ($business?->phone ?? old('old_phone', '')),
            ],
        ]);
    }

    /**
     * Simpan pengajuan pemulihan akun dan unggah berkas bukti.
     */
    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('AccountRecoveryWebController::store received', [
            'all' => $request->except(['identity_card', 'business_proof', 'selfie_proof']),
            'has_id_card' => $request->hasFile('identity_card'),
            'has_biz_proof' => $request->hasFile('business_proof'),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length'),
        ]);

        $user = auth('web')->user();
        if (! $user && ! empty($request->input('old_email'))) {
            $user = User::where('email', $request->input('old_email'))->first();
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'applicant_name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'old_email' => ['required', 'email', 'max:255'],
            'old_phone' => ['nullable', 'string', 'max:25'],
            'new_email' => [
                'required',
                'email',
                'max:255',
                $user ? \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id) : 'unique:users,email',
            ],
            'new_phone' => ['required', 'string', 'min:9', 'max:25'],
            'issue_type' => ['required', 'in:phone_lost,email_inaccessible,both'],
            'reason_description' => ['required', 'string', 'min:5', 'max:3000'],
            'identity_card' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'business_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'selfie_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'new_email.unique' => 'Alamat email baru tersebut sudah digunakan oleh akun lain di sistem. Gunakan email lain.',
            'identity_card.required' => 'Foto Kartu Identitas (KTP/SIM/Paspor) wajib dilampirkan.',
            'identity_card.mimes' => 'Format berkas KTP harus JPG, PNG, WEBP, atau PDF.',
            'business_proof.required' => 'Bukti Kepemilikan Usaha (NIB/SIUP/SKU/Foto Toko) wajib dilampirkan.',
            'business_proof.mimes' => 'Format berkas bukti usaha harus JPG, PNG, WEBP, atau PDF.',
            'reason_description.min' => 'Jelaskan kronologi kendala minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('AccountRecoveryWebController::store validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);

            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Gagal mengirim pengajuan: ' . $validator->errors()->first());
        }

        $validated = $validator->validated();

        $normalizedNewPhone = $this->normalizePhone((string) $validated['new_phone']);
        if ($normalizedNewPhone === null) {
            \Illuminate\Support\Facades\Log::warning('AccountRecoveryWebController: Phone normalization failed', [
                'raw_phone' => $validated['new_phone'],
            ]);

            return back()
                ->withErrors(['new_phone' => 'Nomor WhatsApp baru tidak valid. Gunakan format 08123456789 atau 628123456789.'])
                ->withInput()
                ->with('error', 'Nomor WhatsApp baru tidak valid. Gunakan format 08123456789 atau 628123456789.');
        }

        // Generate unique ticket number
        $ticket = 'REC-' . date('Ym') . '-' . strtoupper(Str::random(6));

        // Upload files securely to private local storage (prevents public PII exposure)
        $idCardPath = $request->file('identity_card')->store("recoveries/{$ticket}", 'local');
        $bizProofPath = $request->file('business_proof')->store("recoveries/{$ticket}", 'local');
        $selfiePath = $request->hasFile('selfie_proof')
            ? $request->file('selfie_proof')->store("recoveries/{$ticket}", 'local')
            : null;

        $businessId = $user?->active_business_id ?: $user?->businesses()->first()?->id;

        $recovery = AccountRecoveryRequest::create([
            'ticket_number' => $ticket,
            'user_id' => $user?->id,
            'business_id' => $businessId,
            'business_name' => $validated['business_name'],
            'applicant_name' => $validated['applicant_name'],
            'old_email' => $validated['old_email'],
            'old_phone' => $validated['old_phone'] ? ($this->normalizePhone($validated['old_phone']) ?? $validated['old_phone']) : null,
            'new_email' => $validated['new_email'],
            'new_phone' => $normalizedNewPhone,
            'issue_type' => $validated['issue_type'],
            'reason_description' => $validated['reason_description'],
            'identity_card_path' => $idCardPath,
            'business_proof_path' => $bizProofPath,
            'selfie_proof_path' => $selfiePath,
            'status' => AccountRecoveryRequest::STATUS_PENDING,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('account-recovery.status', $recovery->ticket_number)
            ->with('success', 'Permohonan pemulihan akun berhasil diajukan! Simpan Nomor Tiket Anda untuk memantau status persetujuan.');
    }

    /**
     * Tampilkan halaman status tiket pemulihan.
     */
    public function status(string $ticket): View
    {
        $recovery = AccountRecoveryRequest::where('ticket_number', $ticket)->firstOrFail();

        return view('auth.account-recovery.status', [
            'recovery' => $recovery,
        ]);
    }

    /**
     * Form pencarian tiket status pemulihan.
     */
    public function checkForm(Request $request): View|RedirectResponse
    {
        if ($request->filled('ticket')) {
            $ticket = trim((string) $request->input('ticket'));
            $exists = AccountRecoveryRequest::where('ticket_number', $ticket)->exists();

            if ($exists) {
                return redirect()->route('account-recovery.status', $ticket);
            }

            return back()->withErrors(['ticket' => "Nomor tiket '{$ticket}' tidak ditemukan di sistem."]);
        }

        return view('auth.account-recovery.status', [
            'recovery' => null,
        ]);
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        } elseif (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return preg_match('/^62[1-9][0-9]{7,13}$/', $phone) ? $phone : null;
    }
}
