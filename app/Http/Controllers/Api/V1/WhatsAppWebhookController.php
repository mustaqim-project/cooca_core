<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle incoming webhook notifications from wa-server (Baileys on Render).
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verify Worker Token Security
        $expectedToken = (string) config('services.wa_server.token', 'secret-worker-token');
        $authHeader    = (string) $request->header('Authorization', '');
        $bearerToken   = str_starts_with($authHeader, 'Bearer ') ? substr($authHeader, 7) : null;
        $customToken   = (string) ($request->header('x-worker-token') ?? $request->header('x-device-token') ?? '');

        $providedToken = $bearerToken ?? ($customToken !== '' ? $customToken : (string) $request->input('token', ''));

        if ($expectedToken === '' || $expectedToken === 'secret-worker-token') {
            Log::critical('[WA Webhook] WA_WORKER_TOKEN is not configured or uses insecure default.');

            return response()->json(['success' => false, 'error' => 'Server misconfiguration'], 500);
        }

        if ($providedToken === '' || ! hash_equals($expectedToken, (string) $providedToken)) {
            Log::warning('[WA Webhook] Unauthorized attempt with invalid token.');

            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $sessionId = $request->input('session') ?? $request->input('sessionId');
        $sender    = $request->input('sender');
        $message   = $request->input('message');
        $fromMe    = (bool) $request->input('fromMe', false);
        $status    = $request->input('status');

        // Redact message content - may contain sensitive user data (PINs, account numbers, etc.)
        Log::info("[WA Webhook] Received payload for session '{$sessionId}' from {$sender} [message redacted]");

        // 2. Update session status if status update payload
        if ($status && $sessionId) {
            WhatsAppSession::where('session_id', $sessionId)->update([
                'status'     => strtolower($status),
                'updated_at' => now(),
            ]);
        }

        // 3. Log incoming message to message logs if associated with a session
        if ($sessionId && $message && ! $fromMe) {
            $waSession = WhatsAppSession::where('session_id', $sessionId)->first();
            if ($waSession && $waSession->business_id) {
                WhatsAppMessageLog::create([
                    'business_id'     => $waSession->business_id,
                    'type'            => 'incoming',
                    'recipient_phone' => (string) $sender,
                    'recipient_name'  => (string) $request->input('pushName', 'Customer'),
                    'message'         => (string) $message,
                    'status'          => 'received',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received successfully',
        ]);
    }
}
