<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\WhatsApp;

use App\Domain\WhatsApp\CloudApi\WhatsAppWebhookService;
use App\Http\Controllers\Controller;
use App\Jobs\WhatsApp\ProcessWhatsAppWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Class MetaWhatsAppWebhookController
 *
 * Endpoint tunggal penerima webhook resmi WhatsApp Cloud API (Meta):
 * - GET: Penanganan verifikasi handshake / subscription challenge dari Meta.
 * - POST: Validasi signature kriptografis X-Hub-Signature-256 dan delegasi
 *         asinkron ke antrean Redis Job (<200ms response time).
 */
class MetaWhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppWebhookService $webhookService
    ) {}

    /**
     * Verifikasi webhook subscription (GET /api/v1/wa/meta/webhook).
     * Meta mengirimkan parameter: hub.mode, hub.verify_token, hub.challenge.
     */
    public function verify(Request $request): Response
    {
        // PHP mengubah titik menjadi garis bawah pada query string bawaan,
        // periksa kedua kemungkinan format parameter.
        $mode      = (string) ($request->query('hub.mode') ?? $request->query('hub_mode') ?? '');
        $token     = (string) ($request->query('hub.verify_token') ?? $request->query('hub_verify_token') ?? '');
        $challenge = (string) ($request->query('hub.challenge') ?? $request->query('hub_challenge') ?? '');

        $verifiedChallenge = $this->webhookService->verifyChallenge($mode, $token, $challenge);

        if ($verifiedChallenge !== null) {
            return response($verifiedChallenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    /**
     * Menerima event webhook masuk dari Meta (POST /api/v1/wa/meta/webhook).
     * Memvalidasi HMAC-SHA256 signature lalu langsung mendispatch ke Redis Queue.
     */
    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature  = $request->header('X-Hub-Signature-256');

        // 1. Verifikasi tanda tangan kriptografis
        if (! $this->webhookService->verifyWebhookSignature($rawPayload, $signature)) {
            Log::channel('daily')->warning('[Meta WA Webhook] Ditolak: Signature X-Hub-Signature-256 tidak valid atau tidak cocok.', [
                'ip'        => $request->ip(),
                'signature' => $signature,
            ]);

            return response()->json([
                'success' => false,
                'error'   => 'Invalid webhook signature',
            ], 403);
        }

        $payload = $request->all();

        // 2. Dispatch ke Redis Queue secara asinkron (memastikan respons < 200ms)
        ProcessWhatsAppWebhookJob::dispatch($payload)->onQueue('whatsapp');

        return response()->json([
            'status'  => 'EVENT_RECEIVED',
            'success' => true,
        ], 200);
    }
}
