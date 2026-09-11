<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppSession;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppWebController extends Controller
{
    public function __construct(protected WhatsAppGatewayService $gateway) {}

    /**
     * Main WhatsApp Gateway integration page.
     */
    public function index(): View
    {
        $business  = Context::requireBusiness();
        $waSession = WhatsAppSession::where('business_id', $business->id)->first();

        // Hanya baca status dari database lokal — jangan hit WA server saat halaman dibuka.
        // QR akan diambil via AJAX (/qr endpoint) hanya saat user klik tombol secara eksplisit.
        $qrDataUrl  = null;
        $liveStatus = strtolower($waSession?->status ?? 'disconnected');

        return view('app.whatsapp.index', compact('business', 'waSession', 'qrDataUrl', 'liveStatus'));
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
     * Disconnect from WhatsApp.
     */
    public function disconnect(): JsonResponse
    {
        $business = Context::requireBusiness();
        $this->gateway->disconnect($business);

        return response()->json(['success' => true, 'message' => 'Session WhatsApp berhasil diputus.']);
    }

    /**
     * Save WhatsApp gateway settings (auto-send receipt, footer note).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'auto_send_receipt' => 'boolean',
            'receipt_template'  => 'nullable|string|max:1000',
        ]);

        WhatsAppSession::updateOrCreate(
            ['business_id' => $business->id],
            [
                'session_id'        => $this->gateway->sessionId($business),
                'auto_send_receipt' => $validated['auto_send_receipt'] ?? false,
                'receipt_template'  => $validated['receipt_template'] ?? null,
            ]
        );

        return back()->with('success', 'Pengaturan WhatsApp Gateway berhasil disimpan.');
    }

    /**
     * Send a test message to the business owner's phone.
     */
    public function testSend(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'phone'   => 'required|string|min:8|max:20',
            'message' => 'required|string|min:1|max:1000',
        ]);

        $result = $this->gateway->sendMessage($business, $validated['phone'], $validated['message']);

        WhatsAppMessageLog::create([
            'business_id'    => $business->id,
            'type'           => 'test',
            'recipient_phone' => $validated['phone'],
            'recipient_name' => 'Test Send',
            'message'        => $validated['message'],
            'status'         => ($result['success'] ?? false) ? 'sent' : 'failed',
            'error_message'  => $result['error'] ?? null,
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

        // Ensure order belongs to this business
        if ($order->business_id !== $business->id) {
            abort(403);
        }

        $phone  = $request->input('phone') ?: $order->customer?->phone;
        $force  = (bool) $request->input('force', false);
        $ok     = $this->gateway->sendReceipt($order, $phone, $force);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Struk berhasil dikirim via WhatsApp! ✅' : 'Gagal mengirim struk. Pastikan WhatsApp terhubung.',
        ]);
    }

    /**
     * Log history page.
     */
    public function logs(): View
    {
        $business = Context::requireBusiness();

        $logs = WhatsAppMessageLog::where('business_id', $business->id)
            ->latest()
            ->paginate(30);

        return view('app.whatsapp.logs', compact('business', 'logs'));
    }
}
