<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Exports\WhatsAppLogsExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\PosOrder;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppSession;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WhatsAppWebController extends Controller
{
    public function __construct(protected WhatsAppGatewayService $gateway) {}

    /**
     * Main WhatsApp Gateway integration page.
     */
    public function index(): View
    {
        $business        = Context::requireBusiness();
        $waSession       = WhatsAppSession::where('business_id', $business->id)->first();
        $whatsAppAccount = \App\Models\WhatsAppAccount::where('business_id', $business->id)->first();

        // Hanya baca status dari database lokal - jangan hit WA server saat halaman dibuka.
        // QR akan diambil via AJAX (/qr endpoint) hanya saat user klik tombol secara eksplisit.
        $qrDataUrl  = null;
        $liveStatus = strtolower($waSession?->status ?? ($whatsAppAccount?->status ?? 'disconnected'));

        // Pastikan template standar sistem sudah ter-seed jika database masih kosong
        if (\App\Models\WhatsAppMessageTemplate::count() === 0) {
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::seedLocalTemplates($whatsAppAccount?->waba_id ?: 'platform_default');
        }

        $approvedTemplates = \App\Models\WhatsAppMessageTemplate::approved()
            ->where(function ($q) use ($business, $whatsAppAccount) {
                $q->whereNull('business_id');
                if ($whatsAppAccount?->waba_id) {
                    $q->orWhere('waba_id', $whatsAppAccount->waba_id);
                }
                $q->orWhere('business_id', $business->id);
            })
            ->orderBy('name')
            ->get();

        return view('app.whatsapp.index', compact('business', 'waSession', 'whatsAppAccount', 'qrDataUrl', 'liveStatus', 'approvedTemplates'));
    }

    /**
     * AJAX: Poll QR code data URL + live status from wa-server.
     */
    public function getQr(): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $data = $this->gateway->getQrData($business);

            // Sync database if status is already connected
            if (($data['status'] ?? '') === 'CONNECTED') {
                $waSession = WhatsAppSession::firstOrNew(['business_id' => $business->id]);
                $waSession->status = 'connected';
                $waSession->last_connected_at = now();
                $waSession->save();
            }

            return response()->json($data);
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'disconnected',
                'qrDataUrl' => null,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * AJAX: Check & sync current session status.
     */
    public function checkStatus(): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $data      = $this->gateway->getStatus($business);
            $waSession = WhatsAppSession::where('business_id', $business->id)->first();

            $rawStatus = strtoupper($data['status'] ?? '');
            if ($rawStatus === 'CONNECTED') {
                if ($waSession) {
                    $waSession->status = 'connected';
                    if (!empty($data['user']['id'])) {
                        $waSession->phone_number = explode(':', $data['user']['id'])[0];
                    }
                    $waSession->last_connected_at = now();
                    $waSession->save();
                }
            } elseif ($rawStatus === 'SCAN_QR') {
                if ($waSession && $waSession->status !== 'scan_qr') {
                    $waSession->status = 'scan_qr';
                    $waSession->save();
                }
            }

            return response()->json([
                'status'      => $waSession?->status ?? 'disconnected',
                'phone'       => $waSession?->phone_number,
                'device_name' => $waSession?->device_name,
                'live'        => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'      => 'disconnected',
                'phone'       => null,
                'device_name' => null,
                'live'        => ['status' => 'disconnected', 'error' => $e->getMessage()],
            ]);
        }
    }

    /**
     * Start / reconnect session (used by "Mulai Scan" button).
     */
    public function startSession(): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $result = $this->gateway->startSession($business);
            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Disconnect from WhatsApp. Enforces Supervisor PIN if configured.
     */
    public function disconnect(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        if ($business->hasSupervisorPin()) {
            $pin = (string) $request->input('pin', '');
            if (empty($pin)) {
                return response()->json([
                    'success'      => false,
                    'pin_required' => true,
                    'message'      => __('whatsapp.supervisor_pin_required'),
                ], 422);
            }

            if (! $business->verifySupervisorPin($pin)) {
                return response()->json([
                    'success' => false,
                    'message' => __('whatsapp.supervisor_pin_invalid'),
                ], 422);
            }
        }

        $this->gateway->disconnect($business);

        AuditLog::create([
            'business_id'    => $business->id,
            'user_id'        => auth()->id(),
            'action'         => 'whatsapp.disconnect',
            'auditable_type' => Business::class,
            'auditable_id'   => $business->id,
            'risk_level'     => AuditLog::RISK_HIGH,
            'risk_reason'    => 'Pemutusan integrasi WhatsApp Gateway toko.',
            'notes'          => 'Operator memutuskan koneksi sesi WhatsApp bisnis.',
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'created_at'     => now(),
        ]);

        return response()->json(['success' => true, 'message' => __('whatsapp.flash_session_disconnected')]);
    }

    /**
     * Save WhatsApp gateway settings (provider, credentials, auto-send receipt, footer note).
     * Non-destructive partial updates: only modifies fields explicitly provided in the request.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'auto_send_receipt'    => ['nullable', 'boolean'],
            'receipt_template'     => ['nullable', 'string', 'max:1000'],
            'provider'             => ['nullable', 'in:baileys,meta_cloud'],
            'is_active'            => ['nullable', 'boolean'],
            'meta_phone_number_id' => ['nullable', 'string', 'max:100'],
            'meta_access_token'    => ['nullable', 'string', 'max:500'],
            'meta_waba_id'         => ['nullable', 'string', 'max:100'],
            'meta_template_name'   => ['nullable', 'string', 'max:100'],
        ]);

        $sessionData = [];
        if ($request->has('provider')) {
            $sessionData['provider'] = $validated['provider'] ?? 'baileys';
        }
        if ($request->has('is_active')) {
            $sessionData['is_active'] = $request->boolean('is_active');
        }
        if ($request->filled('meta_phone_number_id')) {
            $sessionData['meta_phone_number_id'] = trim((string) $validated['meta_phone_number_id']);
        }
        if ($request->filled('meta_access_token')) {
            $sessionData['meta_access_token'] = trim((string) $validated['meta_access_token']);
        }
        if ($request->filled('meta_waba_id')) {
            $sessionData['meta_waba_id'] = trim((string) $validated['meta_waba_id']);
        }
        if ($request->filled('meta_template_name')) {
            $sessionData['meta_template_name'] = trim((string) $validated['meta_template_name']);
        }

        if (!empty($sessionData)) {
            $this->gateway->updateSessionProvider($business, $sessionData);
        }

        $session = WhatsAppSession::firstOrCreate(
            ['business_id' => $business->id],
            [
                'session_id' => $this->gateway->sessionId($business),
                'provider'   => 'meta_cloud',
                'is_active'  => true,
            ]
        );

        if ($session) {
            if ($request->has('auto_send_receipt')) {
                $session->auto_send_receipt = $request->boolean('auto_send_receipt');
            }
            if ($request->has('receipt_template')) {
                $session->receipt_template = $validated['receipt_template'] ?? null;
            }
            $session->save();
        }

        return back()->with('success', __('whatsapp.flash_settings_saved'));
    }

    /**
     * AJAX: Verify Meta WhatsApp Cloud API credentials for business.
     */
    public function verifyMetaCredentials(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $session = WhatsAppSession::where('business_id', $business->id)->first();

        $validated = $request->validate([
            'token'           => ['nullable', 'string', 'max:500'],
            'phone_number_id' => ['nullable', 'string', 'max:100'],
        ]);

        $token = trim((string) ($validated['token'] ?? ($session?->meta_access_token ?? '')));
        $phoneId = trim((string) ($validated['phone_number_id'] ?? ($session?->meta_phone_number_id ?? '')));

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
     * Send a test message to the business owner's phone.
     */
    public function testSend(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'phone'   => ['required', 'string', 'min:8', 'max:20', 'regex:/^(\+?62|08)[0-9]{7,15}$/'],
            'message' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $rawPhone = preg_replace('/[^0-9]/', '', (string) $validated['phone']);
        if (str_starts_with($rawPhone, '0')) {
            $phone = '62' . substr($rawPhone, 1);
        } else {
            $phone = $rawPhone;
        }

        $message = trim($validated['message']);

        $result = $this->gateway->sendMessage($business, $phone, $message);

        WhatsAppMessageLog::create([
            'business_id'     => $business->id,
            'type'            => 'test',
            'recipient_phone' => $phone,
            'recipient_name'  => 'Test Send',
            'message'         => $message,
            'status'          => ($result['success'] ?? false) ? 'sent' : 'failed',
            'error_message'   => $result['error'] ?? null,
        ]);

        return response()->json($result);
    }

    /**
     * AJAX: Send a POS order receipt via the business's WA bot.
     * Called from the POS success modal / receipt page.
     */
    public function sendOrderReceipt(Request $request, PosOrder $order): JsonResponse
    {
        $business = Context::requireBusiness();

        // Ensure order belongs to this business (404 to prevent IDOR enumeration)
        if ((string) $order->business_id !== (string) $business->id) {
            abort(404);
        }

        $customPhone   = $request->input('phone');
        $originalPhone = $order->customer?->phone ?: $order->customer_phone_guest;

        // Anti-Fraud Audit Trail: Catat jika kasir/operator mengalihkan nomor struk transaksi
        if (! empty($customPhone) && ! empty($originalPhone)) {
            $normCustom = preg_replace('/[^0-9]/', '', (string) $customPhone);
            if (str_starts_with($normCustom, '0')) {
                $normCustom = '62' . substr($normCustom, 1);
            }
            $normOriginal = preg_replace('/[^0-9]/', '', (string) $originalPhone);
            if (str_starts_with($normOriginal, '0')) {
                $normOriginal = '62' . substr($normOriginal, 1);
            }

            if ($normCustom !== $normOriginal) {
                // Rate-limit pengalihan nomor struk: maks 5 kali per kasir per jam / shift
                $userKey = 'receipt_override:' . $business->id . ':' . (auth()->id() ?? $request->ip());
                $attempts = (int) Cache::get($userKey, 0);

                if ($attempts >= 5) {
                    return response()->json([
                        'success' => false,
                        'message' => __('whatsapp.error_rate_limit_override'),
                    ], 429);
                }

                Cache::put($userKey, $attempts + 1, 3600);

                $riskLevel = ((float) $order->grand_total >= 500000) ? AuditLog::RISK_HIGH : AuditLog::RISK_MEDIUM;

                AuditLog::create([
                    'business_id'    => $business->id,
                    'user_id'        => auth()->id(),
                    'action'         => 'receipt.phone_override',
                    'auditable_type' => PosOrder::class,
                    'auditable_id'   => $order->id,
                    'risk_level'     => $riskLevel,
                    'risk_reason'    => 'Pengalihan nomor WhatsApp penerima struk transaksi kasir.',
                    'old_values'     => ['phone' => $originalPhone],
                    'new_values'     => ['phone' => $customPhone],
                    'notes'          => 'Kasir mengalihkan nomor struk digital transaksi senilai Rp ' . number_format((float)$order->grand_total, 0, ',', '.'),
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'created_at'     => now(),
                ]);
            }
        }

        $phone  = $customPhone ?: $originalPhone;
        $force  = (bool) $request->input('force', false);
        $ok     = $this->gateway->sendReceipt($order, $phone, $force);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? __('whatsapp.flash_receipt_sent') : __('whatsapp.flash_receipt_failed'),
        ]);
    }

    /**
     * Log history page with server-side filtering and deep-linked pagination.
     */
    public function logs(Request $request): View
    {
        $business = Context::requireBusiness();
        $type = $request->query('type');

        $logs = WhatsAppMessageLog::where('business_id', $business->id)
            ->when(!empty($type) && in_array($type, ['receipt', 'broadcast', 'test'], true), function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->latest()
            ->paginate(30)
            ->appends($request->query());

        return view('app.whatsapp.logs', compact('business', 'logs', 'type'));
    }

    /**
     * Export message logs as a two-sheet XLSX workbook with Bento KPIs & transactional ledger.
     */
    public function exportLogs(Request $request): StreamedResponse
    {
        $business = Context::requireBusiness();
        $isOwner  = Context::isOwner();

        $filters = [
            'type'       => $request->query('type'),
            'start_date' => $request->query('start_date'),
            'end_date'   => $request->query('end_date'),
        ];

        /** @var WhatsAppLogsExport $export */
        $export = app(WhatsAppLogsExport::class);

        return $export->download($business, $filters, $isOwner);
    }
}
