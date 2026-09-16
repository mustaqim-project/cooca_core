<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Http\Controllers\Controller;
use App\Models\BusinessSubscription;
use App\Models\WhatsAppAdminBlast;
use App\Models\WhatsAppSubscriptionReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AdminWhatsAppController extends Controller
{
    public function __construct(
        private readonly AdminWhatsAppService $adminWa
    ) {}

    /**
     * WhatsApp Admin Center main dashboard.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'reminders');

        // Status & Session Data
        $waStatus = $this->adminWa->getStatus();

        // Due Subscriptions (H-7, H-3, H-1, Hari H)
        $dueData = $this->adminWa->getDueSubscriptions();

        // Templates
        $templates = [
            'h-7'    => $this->adminWa->getTemplate('h-7'),
            'h-3'    => $this->adminWa->getTemplate('h-3'),
            'h-1'    => $this->adminWa->getTemplate('h-1'),
            'hari_h' => $this->adminWa->getTemplate('hari_h'),
        ];

        // Recent Reminders Log
        $recentReminders = WhatsAppSubscriptionReminder::with(['business', 'owner'])
            ->latest()
            ->paginate(20, ['*'], 'reminder_page');

        // Admin Blasts
        $blasts = WhatsAppAdminBlast::latest()->paginate(15, ['*'], 'blast_page');

        // Target Counts for Blast
        $targetCounts = [
            'all_owners'         => $this->adminWa->resolveBlastRecipients('all_owners')->count(),
            'active_subscribers' => $this->adminWa->resolveBlastRecipients('active_subscribers')->count(),
            'expiring_soon'      => $this->adminWa->resolveBlastRecipients('expiring_soon')->count(),
            'free_tier'          => $this->adminWa->resolveBlastRecipients('free_tier')->count(),
        ];

        // Hanya ambil status existing - jangan preload QR saat halaman dibuka.
        // QR akan diambil via AJAX (/qr endpoint) hanya saat user klik tombol "Tampilkan QR Code".
        $liveStatus = strtolower($waStatus['status'] ?? 'disconnected');
        $qrDataUrl  = null;

        // Dual Gateway (Meta WhatsApp Cloud API vs Baileys QR)
        $otpDriver    = $this->adminWa->getOtpDriver();
        $blastDriver  = $this->adminWa->getBlastDriver();
        $isOtpActive  = $this->adminWa->isOtpActive();
        $isBlastActive = $this->adminWa->isBlastActive();
        $metaCreds    = $this->adminWa->getMetaCredentials();

        // Multi-Session Admin Pool
        $adminSessions = $this->adminWa->getSessions();

        return view('admin.whatsapp.index', compact(
            'tab',
            'waStatus',
            'liveStatus',
            'qrDataUrl',
            'dueData',
            'templates',
            'recentReminders',
            'blasts',
            'targetCounts',
            'otpDriver',
            'blastDriver',
            'isOtpActive',
            'isBlastActive',
            'metaCreds',
            'adminSessions'
        ));
    }

    /**
     * AJAX: Get list of all admin WhatsApp sessions.
     */
    public function getSessions(): JsonResponse
    {
        $sessions = $this->adminWa->getSessions();

        return response()->json([
            'success'  => true,
            'sessions' => $sessions,
        ]);
    }

    /**
     * AJAX: Create and start a new admin WhatsApp session in pool.
     */
    public function createSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $session = $this->adminWa->createSession($validated['name'] ?? null);

        return response()->json([
            'success'   => true,
            'session'   => $session,
            'sessionId' => $session->session_id,
            'message'   => "Sesi WhatsApp '{$session->name}' berhasil dibuat.",
        ]);
    }

    /**
     * AJAX: Get QR Code data URL and live status for specific admin session.
     */
    public function getQr(Request $request, ?string $sessionId = null): JsonResponse
    {
        $resolvedSessionId = $sessionId ?: (string) (
            $request->route('sessionId')
            ?? $request->route('session')
            ?? $request->query('sessionId')
            ?? $request->query('session_id')
            ?? ''
        );
        $data = $this->adminWa->getQrCode($resolvedSessionId ?: null);

        return response()->json($data);
    }

    /**
     * AJAX: Get live status for specific admin session or aggregate.
     */
    public function checkStatus(Request $request, ?string $sessionId = null): JsonResponse
    {
        $resolvedSessionId = $sessionId ?: (string) (
            $request->route('sessionId')
            ?? $request->route('session')
            ?? $request->query('sessionId')
            ?? $request->query('session_id')
            ?? ''
        );
        $data = $this->adminWa->getStatus($resolvedSessionId ?: null);

        $userId = $data['user']['id'] ?? null;
        $phone = $data['phone'] ?? ($userId ? explode(':', (string) $userId)[0] : null);

        return response()->json([
            ...$data,
            'status' => strtolower((string) ($data['status'] ?? 'disconnected')),
            'phone' => $phone,
        ]);
    }

    /**
     * Start / trigger session on wa-server.
     */
    public function startSession(Request $request, ?string $sessionId = null): JsonResponse
    {
        $resolvedSessionId = $sessionId ?: (string) (
            $request->route('sessionId')
            ?? $request->route('session')
            ?? $request->input('sessionId')
            ?? $request->input('session_id')
            ?? ''
        );
        $result = $this->adminWa->startSession($resolvedSessionId ?: null);

        $success = $result['success'] ?? true;

        return response()->json($result, $success ? 200 : 502);
    }

    /**
     * Disconnect Admin WhatsApp session.
     */
    public function disconnect(Request $request, ?string $sessionId = null): JsonResponse
    {
        $resolvedSessionId = $sessionId ?: (string) (
            $request->route('sessionId')
            ?? $request->route('session')
            ?? $request->input('sessionId')
            ?? $request->input('session_id')
            ?? ''
        );
        $this->adminWa->disconnect($resolvedSessionId ?: null);

        return response()->json(['success' => true, 'message' => 'Sesi WhatsApp Admin berhasil diputus.']);
    }

    /**
     * AJAX: Disconnect specific session.
     */
    public function disconnectSession(string $sessionId): JsonResponse
    {
        $this->adminWa->disconnectSession($sessionId);

        return response()->json([
            'success' => true,
            'message' => "Sesi {$sessionId} berhasil diputus.",
        ]);
    }

    /**
     * AJAX: Delete specific session from database and wa-server.
     */
    public function deleteSession(string $sessionId): JsonResponse
    {
        $this->adminWa->deleteSession($sessionId);

        return response()->json([
            'success' => true,
            'message' => "Nomor WhatsApp berhasil dihapus.",
        ]);
    }

    /**
     * AJAX: Toggle session inclusion in random pool.
     */
    public function toggleSessionActive(string $sessionId): JsonResponse
    {
        $isActive = $this->adminWa->toggleSessionActive($sessionId);

        return response()->json([
            'success'   => true,
            'is_active' => $isActive,
            'message'   => $isActive ? 'Sesi diaktifkan dalam rotasi acak.' : 'Sesi dinonaktifkan dari rotasi acak.',
        ]);
    }

    /**
     * Test sending from Admin Bot (Baileys or Meta Cloud).
     */
    public function testSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'   => 'required|string|min:8|max:20',
            'message' => 'required|string|min:1|max:1000',
            'driver'  => 'nullable|string|in:baileys,meta_cloud',
        ]);

        $options = [];
        if (!empty($validated['driver'])) {
            $options['driver'] = $validated['driver'];
        }

        $result = $this->adminWa->sendMessage($validated['phone'], $validated['message'], $options);

        return response()->json($result);
    }

    /**
     * AJAX: Verify Meta WhatsApp Cloud API credentials.
     */
    public function verifyMetaCredentials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token'           => ['nullable', 'string', 'max:500'],
            'phone_number_id' => ['nullable', 'string', 'max:100'],
        ]);

        $creds = $this->adminWa->getMetaCredentials();
        $token = trim((string) ($validated['token'] ?? $creds['token']));
        $phoneId = trim((string) ($validated['phone_number_id'] ?? $creds['phone_number_id']));

        if (empty($token) || empty($phoneId)) {
            return response()->json([
                'success' => false,
                'error'   => 'Token dan Phone Number ID Meta wajib diisi terlebih dahulu untuk pengujian verifikasi.',
            ], 422);
        }

        /** @var \App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver $metaDriver */
        $metaDriver = app(\App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver::class);
        $result = $metaDriver->verifyCredentials($token, $phoneId);

        return response()->json($result);
    }

    /**
     * Send single subscription reminder now.
     */
    public function sendSingleReminder(Request $request, BusinessSubscription $subscription): JsonResponse
    {
        $type = $request->input('type', 'manual');

        $result = $this->adminWa->sendReminder($subscription, $type);

        return response()->json([
            'success' => $result['success'] ?? false,
            'message' => ($result['success'] ?? false)
                ? "Pengingat {$type} berhasil dikirim ke nomor owner!"
                : "Gagal: " . ($result['error'] ?? 'WhatsApp tidak terhubung'),
        ]);
    }

    /**
     * Send all pending due reminders today.
     */
    public function sendAllReminders(): RedirectResponse
    {
        $res = $this->adminWa->sendAllDueReminders();

        return back()->with('success', "Proses pengingat selesai: {$res['sent']} terkirim, {$res['failed']} gagal, {$res['skipped']} dilewati.");
    }

    /**
     * Save reminder message templates.
     */
    public function updateTemplates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_h7' => 'required|string|max:2000',
            'template_h3' => 'required|string|max:2000',
            'template_h1' => 'required|string|max:2000',
            'template_h0' => 'required|string|max:2000',
        ]);

        $this->adminWa->saveTemplate('h-7', $validated['template_h7']);
        $this->adminWa->saveTemplate('h-3', $validated['template_h3']);
        $this->adminWa->saveTemplate('h-1', $validated['template_h1']);
        $this->adminWa->saveTemplate('hari_h', $validated['template_h0']);

        return back()->with('success', 'Template pengingat WhatsApp berhasil diperbarui.');
    }

    /**
     * Create and dispatch Admin Blast to business owners.
     */
    public function storeBlast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'message'       => 'required|string|max:3000',
            'media_url'     => 'nullable|url|max:500',
            'target_filter' => 'required|in:all_owners,active_subscribers,expiring_soon,free_tier',
        ]);

        $admin = auth('admin')->user();

        $blast = WhatsAppAdminBlast::create([
            'admin_id'      => $admin?->id,
            'title'         => $validated['title'],
            'message'       => $validated['message'],
            'media_url'     => $validated['media_url'] ?? null,
            'target_filter' => $validated['target_filter'],
            'status'        => 'processing',
        ]);

        try {
            $this->adminWa->sendAdminBlast($blast);
        } catch (\Throwable $e) {
            $blast->update(['status' => 'failed']);
            return back()->withErrors(['blast' => 'Error: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.whatsapp.blasts.show', $blast)
            ->with('success', "Blast '{$blast->title}' berhasil dikirim ke {$blast->total_sent} bisnis owner!");
    }

    /**
     * Show detail of an admin blast.
     */
    public function showBlast(WhatsAppAdminBlast $blast): View
    {
        $recipients = $blast->recipients()->latest()->paginate(30);

        return view('admin.whatsapp.blast_show', compact('blast', 'recipients'));
    }

    /**
     * Save Dual Gateway WhatsApp configuration (Drivers & Meta credentials).
     */
    public function updateGatewayConfig(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'otp_driver'            => ['required', 'in:meta_cloud,baileys,disabled'],
            'blast_driver'          => ['required', 'in:baileys,meta_cloud,disabled'],
            'otp_active'            => ['nullable', 'boolean'],
            'blast_active'          => ['nullable', 'boolean'],
            'meta_token'            => ['nullable', 'string', 'max:500'],
            'meta_phone_number_id'  => ['nullable', 'string', 'max:100'],
            'meta_waba_id'          => ['nullable', 'string', 'max:100'],
            'meta_otp_template'     => ['nullable', 'string', 'max:100'],
        ]);

        $this->adminWa->saveGatewaySettings([
            'otp_driver'            => $validated['otp_driver'],
            'blast_driver'          => $validated['blast_driver'],
            'otp_active'            => $request->boolean('otp_active'),
            'blast_active'          => $request->boolean('blast_active'),
            'meta_token'            => isset($validated['meta_token']) ? trim((string) $validated['meta_token']) : null,
            'meta_phone_number_id'  => isset($validated['meta_phone_number_id']) ? trim((string) $validated['meta_phone_number_id']) : null,
            'meta_waba_id'          => isset($validated['meta_waba_id']) ? trim((string) $validated['meta_waba_id']) : null,
            'meta_otp_template'     => isset($validated['meta_otp_template']) ? trim((string) $validated['meta_otp_template']) : 'cooca_otp',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pilihan jalur WhatsApp berhasil disimpan otomatis.',
                'data'    => [
                    'otp_driver'   => $validated['otp_driver'],
                    'blast_driver' => $validated['blast_driver'],
                    'otp_active'   => $request->boolean('otp_active'),
                    'blast_active' => $request->boolean('blast_active'),
                ],
            ]);
        }

        return back()->with('success', 'Konfigurasi Dual Gateway WhatsApp (OTP & Blast) berhasil disimpan.');
    }
}
