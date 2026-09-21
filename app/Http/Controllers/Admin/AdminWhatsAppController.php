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
     * WhatsApp Admin Center main dashboard (Parent Setup & Platform Operations).
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'parent_setup');
        if (!in_array($tab, ['parent_setup', 'reminders', 'blast', 'templates', 'merchants'], true)) {
            $tab = 'parent_setup';
        }

        // Live Platform Meta WhatsApp status
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

        $liveStatus = strtolower($waStatus['status'] ?? 'disconnected');

        $isOtpActive   = $this->adminWa->isOtpActive();
        $isBlastActive = $this->adminWa->isBlastActive();
        $metaCreds     = $this->adminWa->getMetaCredentials();

        // Merchant Accounts Overview (Platform Parent Oversight)
        $merchantSummary = $this->adminWa->getMerchantAccountsSummary();

        // Platform App settings (Official Tech Provider Integration)
        $platformApp = $this->adminWa->getPlatformAppSettings();

        $otpDriver   = 'meta_cloud';
        $blastDriver = 'meta_cloud';
        $metaTemplates = $this->adminWa->getLocalTemplates();

        return view('admin.whatsapp.index', compact(
            'tab',
            'waStatus',
            'liveStatus',
            'dueData',
            'templates',
            'recentReminders',
            'blasts',
            'targetCounts',
            'isOtpActive',
            'isBlastActive',
            'metaCreds',
            'merchantSummary',
            'platformApp',
            'otpDriver',
            'blastDriver',
            'metaTemplates'
        ));
    }

    /**
     * AJAX: Check live status of Platform Meta WhatsApp.
     */
    public function checkStatus(): JsonResponse
    {
        $data = $this->adminWa->getStatus();

        return response()->json($data);
    }

    /**
     * Graceful stub for legacy getSessions.
     */
    public function getSessions(): JsonResponse
    {
        return response()->json(['success' => true, 'sessions' => []]);
    }

    /**
     * Graceful stub for legacy getQr.
     */
    public function getQr(): JsonResponse
    {
        return response()->json([
            'success'   => false,
            'status'    => 'disconnected',
            'qrDataUrl' => null,
            'message'   => 'Layanan Scan QR telah dinonaktifkan. Sistem menggunakan Meta WhatsApp Cloud API resmi.',
        ]);
    }

    /**
     * Graceful stub for legacy startSession.
     */
    public function startSession(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error'   => 'Scan QR tidak lagi digunakan. Gunakan konfigurasi Meta Cloud API resmi.',
        ], 400);
    }

    /**
     * Graceful stub for legacy disconnect.
     */
    public function disconnect(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Status diperbarui.']);
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
     * AJAX: Send official WhatsApp OTP (template cooca_otp) to any phone number.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'    => 'required|string|min:8|max:20',
            'otp_code' => 'nullable|string|min:4|max:10',
        ]);

        $phone = trim((string) $validated['phone']);
        $otpCode = ! empty($validated['otp_code']) ? trim((string) $validated['otp_code']) : (string) random_int(100000, 999999);

        $result = $this->adminWa->sendOtp($phone, $otpCode);

        $isOk = ($result['success'] ?? false) === true;

        return response()->json([
            'success'  => $isOk,
            'message'  => $isOk
                ? "Pesan OTP resmi [{$otpCode}] berhasil dikirim ke nomor {$phone}!"
                : ($result['error'] ?? 'Gagal mengirimkan pesan OTP Meta WhatsApp.'),
            'phone'    => $phone,
            'otp_code' => $otpCode,
            'error'    => $isOk ? null : ($result['error'] ?? 'Pengiriman OTP ditolak.'),
        ], $isOk ? 200 : 422);
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
     * Save Meta WhatsApp Cloud API Platform configuration (Parent Setup & Tech Provider).
     */
    public function updateGatewayConfig(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'otp_active'                 => ['nullable', 'boolean'],
            'blast_active'               => ['nullable', 'boolean'],
            'meta_app_id'                => ['nullable', 'string', 'max:100'],
            'meta_app_secret'            => ['nullable', 'string', 'max:255'],
            'meta_webhook_verify_token'  => ['nullable', 'string', 'max:100'],
            'meta_config_id'             => ['nullable', 'string', 'max:100'],
            'meta_graph_version'         => ['nullable', 'string', 'max:20'],
            'meta_graph_url'             => ['nullable', 'string', 'url', 'max:255'],
            'meta_token'                 => ['nullable', 'string', 'max:500'],
            'meta_phone_number_id'       => ['nullable', 'string', 'max:100'],
            'meta_waba_id'               => ['nullable', 'string', 'max:100'],
            'meta_otp_template'          => ['nullable', 'string', 'max:100'],
        ]);

        $this->adminWa->saveGatewaySettings([
            'otp_active'                 => $request->boolean('otp_active', true),
            'blast_active'               => $request->boolean('blast_active', true),
            'meta_app_id'                => isset($validated['meta_app_id']) ? trim((string) $validated['meta_app_id']) : null,
            'meta_app_secret'            => isset($validated['meta_app_secret']) ? trim((string) $validated['meta_app_secret']) : null,
            'meta_webhook_verify_token'  => isset($validated['meta_webhook_verify_token']) ? trim((string) $validated['meta_webhook_verify_token']) : null,
            'meta_config_id'             => isset($validated['meta_config_id']) ? trim((string) $validated['meta_config_id']) : null,
            'meta_graph_version'         => isset($validated['meta_graph_version']) ? trim((string) $validated['meta_graph_version']) : 'v26.0',
            'meta_graph_url'             => isset($validated['meta_graph_url']) ? trim((string) $validated['meta_graph_url']) : 'https://graph.facebook.com',
            'meta_token'                 => isset($validated['meta_token']) ? trim((string) $validated['meta_token']) : null,
            'meta_phone_number_id'       => isset($validated['meta_phone_number_id']) ? trim((string) $validated['meta_phone_number_id']) : null,
            'meta_waba_id'               => isset($validated['meta_waba_id']) ? trim((string) $validated['meta_waba_id']) : null,
            'meta_otp_template'          => isset($validated['meta_otp_template']) ? trim((string) $validated['meta_otp_template']) : 'cooca_otp',
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi Meta WhatsApp Platform berhasil disimpan otomatis.',
                'data'    => [
                    'otp_active'   => $request->boolean('otp_active', true),
                    'blast_active' => $request->boolean('blast_active', true),
                ],
            ]);
        }

        return back()->with('success', 'Konfigurasi Meta WhatsApp Cloud API Platform berhasil disimpan.');
    }

    /**
     * AJAX: Sync message templates from Meta Graph API v26.0 into local database.
     */
    public function syncMetaTemplates(Request $request): JsonResponse
    {
        $wabaId = $request->input('waba_id');
        $result = $this->adminWa->syncTemplatesFromMeta($wabaId ? (string) $wabaId : null);

        if ($result['success']) {
            $count = $result['count'] ?? ($result['synced_count'] ?? ($result['synced'] ?? 0));
            return response()->json([
                'success'      => true,
                'count'        => $count,
                'synced_count' => $count,
                'message'      => "Sinkronisasi template dari Meta v26.0 berhasil ({$count} template disinkronkan).",
                'data'         => $result,
                'templates'    => $this->adminWa->getLocalTemplates(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Gagal melakukan sinkronisasi template dari Meta.',
        ], 422);
    }

    /**
     * AJAX: Create a new message template on Meta Graph API v26.0.
     */
    public function createMetaTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'category'     => ['required', 'string', 'in:AUTHENTICATION,MARKETING,UTILITY'],
            'language'     => ['required', 'string', 'max:15'],
            'body_text'    => ['required', 'string'],
            'header_type'  => ['nullable', 'string', 'in:TEXT'],
            'header_text'  => ['nullable', 'string', 'max:60'],
            'footer_text'  => ['nullable', 'string', 'max:60'],
            'buttons'      => ['nullable', 'array'],
        ], [
            'name.regex'         => 'Nama template hanya boleh huruf kecil (a-z), angka (0-9), dan garis bawah (_).',
            'category.in'        => 'Kategori template harus berupa UTILITY, MARKETING, atau AUTHENTICATION.',
            'body_text.required' => 'Isi teks pesan (body) wajib diisi.',
        ]);

        $result = $this->adminWa->createTemplate($validated);

        if ($result['success']) {
            return response()->json([
                'success'   => true,
                'message'   => "Template '{$validated['name']}' berhasil diajukan ke Meta (Status: " . ($result['template']['status'] ?? 'PENDING') . ').',
                'data'      => $result,
                'templates' => $this->adminWa->getLocalTemplates(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Gagal membuat template di Meta.',
        ], 422);
    }

    /**
     * AJAX: Delete a message template from Meta Graph API and local database.
     */
    public function deleteMetaTemplate(string $template): JsonResponse
    {
        $result = $this->adminWa->deleteTemplate($template);

        if ($result['success']) {
            return response()->json([
                'success'   => true,
                'message'   => 'Template pesan berhasil dihapus dari Meta dan database lokal.',
                'templates' => $this->adminWa->getLocalTemplates(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Gagal menghapus template pesan.',
        ], 422);
    }
}
