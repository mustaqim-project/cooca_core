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

        // Hanya ambil status existing — jangan preload QR saat halaman dibuka.
        // QR akan diambil via AJAX (/qr endpoint) hanya saat user klik tombol "Tampilkan QR Code".
        $liveStatus = strtolower($waStatus['status'] ?? 'disconnected');
        $qrDataUrl  = null;

        return view('admin.whatsapp.index', compact(
            'tab',
            'waStatus',
            'liveStatus',
            'qrDataUrl',
            'dueData',
            'templates',
            'recentReminders',
            'blasts',
            'targetCounts'
        ));
    }

    /**
     * AJAX: Get QR Code data URL and live status for admin session.
     */
    public function getQr(): JsonResponse
    {
        $data = $this->adminWa->getQrCode();

        return response()->json($data);
    }

    /**
     * AJAX: Get live status.
     */
    public function checkStatus(): JsonResponse
    {
        $data = $this->adminWa->getStatus();

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
    public function startSession(): JsonResponse
    {
        $result = $this->adminWa->startSession();

        $success = $result['success'] ?? true;

        return response()->json($result, $success ? 200 : 502);
    }

    /**
     * Disconnect Admin WhatsApp.
     */
    public function disconnect(): JsonResponse
    {
        $this->adminWa->disconnect();

        return response()->json(['success' => true, 'message' => 'Sesi WhatsApp Admin berhasil diputus.']);
    }

    /**
     * Test sending from Admin Bot.
     */
    public function testSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone'   => 'required|string|min:8|max:20',
            'message' => 'required|string|min:1|max:1000',
        ]);

        $result = $this->adminWa->sendMessage($validated['phone'], $validated['message']);

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
}
