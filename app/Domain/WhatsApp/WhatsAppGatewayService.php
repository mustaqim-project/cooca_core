<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp;

use App\Models\Business;
use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppBroadcastRecipient;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');
        $this->token   = config('services.wa_server.token', 'secret-worker-token');
    }

    /**
     * Derive a deterministic, per-business session ID from the business UUID.
     */
    public function sessionId(Business $business): string
    {
        return 'biz_' . str_replace('-', '', substr($business->id, 0, 8));
    }

    /**
     * Create an authenticated HTTP client.
     */
    protected function client(int $timeout = 15)
    {
        return Http::timeout($timeout)
            ->withHeaders([
                'Authorization'  => 'Bearer ' . $this->token,
                'x-worker-token' => $this->token,
                'Accept'         => 'application/json',
            ]);
    }

    /**
     * Start (or reconnect) a WhatsApp session for the given business.
     */
    public function startSession(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        try {
            $response = $this->client(30)->post("{$this->baseUrl}/api/sessions/start", [
                'sessionId'  => $sessionId,
                'webhookUrl' => url('/api/wa/webhook'),
            ]);

            $waSession = WhatsAppSession::firstOrNew(['business_id' => $business->id]);
            $waSession->session_id = $sessionId;
            $waSession->status     = 'scan_qr';
            $waSession->save();

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("[WA] startSession error ({$sessionId}): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Disconnect and clear the WhatsApp session.
     */
    public function disconnectSession(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        try {
            $response = $this->client(15)->delete("{$this->baseUrl}/api/sessions/{$sessionId}");

            $waSession = WhatsAppSession::where('business_id', $business->id)->first();
            if ($waSession) {
                $waSession->status            = 'disconnected';
                $waSession->phone_number      = null;
                $waSession->device_name       = null;
                $waSession->last_connected_at = null;
                $waSession->save();
            }

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("[WA] disconnectSession error ({$sessionId}): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Alias for disconnectSession().
     */
    public function disconnect(Business $business): array
    {
        return $this->disconnectSession($business);
    }

    /**
     * Get live session status from the microservice.
     */
    public function getSessionStatus(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        try {
            $response = $this->client(10)->get("{$this->baseUrl}/api/sessions/{$sessionId}/status");
            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning("[WA] getSessionStatus error ({$sessionId}): " . $e->getMessage());
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }

    /**
     * Alias for getSessionStatus().
     */
    public function getStatus(Business $business): array
    {
        return $this->getSessionStatus($business);
    }

    /**
     * Get live QR code and session metadata.
     */
    public function getQrData(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        try {
            $response = $this->client(10)->get("{$this->baseUrl}/api/sessions/{$sessionId}/qr");

            if ($response->status() === 404) {
                // Session belum dibuat di wa-server.
                // Kembalikan disconnected agar user harus klik tombol secara eksplisit.
                return ['status' => 'disconnected', 'qrDataUrl' => null];
            }

            $data = $response->json();
            return is_array($data) ? $data : ['status' => 'disconnected', 'qrDataUrl' => null];
        } catch (\Throwable $e) {
            Log::warning("[WA] getQrData error ({$sessionId}): " . $e->getMessage());
            return ['status' => 'disconnected', 'qrDataUrl' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get live QR code for the session (string only).
     */
    public function getQrCode(Business $business): ?string
    {
        $data = $this->getQrData($business);
        return $data['qrDataUrl'] ?? null;
    }

    /**
     * Send a WhatsApp message via the microservice for a given business session.
     */
    public function sendMessage(Business $business, string $phone, string $message, array $options = []): array
    {
        $sessionId = $this->sessionId($business);
        return $this->sendRawMessage($sessionId, $phone, $message, $options);
    }

    /**
     * Send a raw message using any sessionId directly.
     */
    public function sendRawMessage(string $sessionId, string $phone, string $message, array $options = []): array
    {
        try {
            $payload = array_merge([
                'session' => $sessionId,
                'target'  => $phone,
                'message' => $message,
            ], $options);

            $response = $this->client(15)->post("{$this->baseUrl}/send-message", $payload);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("[WA] sendRawMessage error ({$sessionId}): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Format and send a POS receipt via bot WA to the customer's phone as an image receipt.
     * Prevents duplicate sending on double-click or rapid re-clicks.
     */
    public function sendReceipt(PosOrder $order, ?string $customPhone = null, bool $force = false): bool
    {
        $business = $order->business;
        $phone    = $customPhone ?? $order->customer?->phone ?? $order->customer_phone_guest ?? '';

        if (! $phone) {
            return false;
        }

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        if (! $session || $session->status !== 'connected') {
            return false;
        }

        // 1. Anti Double-Click: Atomic lock to prevent simultaneous execution
        $lockKey = "wa_receipt_sending_{$order->id}";
        if (! Cache::add($lockKey, true, 15)) {
            Log::info("[WA] Double-click terdeteksi untuk pesanan #{$order->order_number}. Mengabaikan kiriman duplikat.");
            return true;
        }

        try {
            // 2. Anti-Duplicate: Check if receipt was already sent successfully recently (within 60s)
            if (! $force) {
                $recentlySent = WhatsAppMessageLog::where('order_id', $order->id)
                    ->where('type', 'receipt')
                    ->where('status', 'sent')
                    ->where('created_at', '>=', now()->subSeconds(60))
                    ->exists();

                if ($recentlySent) {
                    Log::info("[WA] Struk pesanan #{$order->order_number} sudah berhasil dikirim. Menghindari pengiriman duplikat.");
                    return true;
                }
            }

            $message = $this->buildReceiptMessage($order);

            $options = [];
            try {
                /** @var \App\Domain\Pos\PosReceiptImageService $imageService */
                $imageService = app(\App\Domain\Pos\PosReceiptImageService::class);
                $relativePath = $imageService->generateAndStore($order);
                $imageUrl     = asset('storage/' . $relativePath);

                $localFile    = storage_path('app/public/' . $relativePath);
                if (! file_exists($localFile)) {
                    $localFile = public_path('storage/' . $relativePath);
                }

                $options = [
                    'url'      => $imageUrl,
                    'mediaUrl' => $imageUrl,
                    'filePath' => file_exists($localFile) ? $localFile : null,
                    'type'     => 'image',
                    'filename' => "struk-{$order->order_number}.png",
                ];
            } catch (\Throwable $e) {
                Log::warning('[WhatsAppGatewayService] Gagal membuat gambar struk: ' . $e->getMessage());
            }

            $result = $this->sendMessage($business, $phone, $message, $options);

            WhatsAppMessageLog::create([
                'business_id'     => $business->id,
                'type'            => 'receipt',
                'recipient_phone' => $phone,
                'recipient_name'  => $order->customer?->name ?? $order->customer_name_guest ?? 'Pelanggan',
                'message'         => $message,
                'status'          => ($result['success'] ?? false) ? 'sent' : 'failed',
                'order_id'        => $order->id,
                'error_message'   => $result['error'] ?? null,
            ]);

            return $result['success'] ?? false;
        } finally {
            Cache::forget($lockKey);
        }
    }

    /**
     * Build the receipt message caption for WhatsApp using the CMS template or default template.
     * Supports dynamic tags: {business_name}, {customer_name}, {order_number}, {date}, {cashier_name}, {receipt_link}, {footer_note}.
     */
    public function buildReceiptMessage(PosOrder $order): string
    {
        $business    = $order->business;
        $bizName     = $business?->name ?? 'COOCA POS';
        $custName    = $order->customer?->name ?? $order->customer_name_guest ?? null;
        $cashierName = $order->user?->name ?? 'Kasir';
        $orderDate   = $order->order_date ? $order->order_date->format('d/m/Y H:i') : now()->format('d/m/Y H:i');
        $receiptUrl  = route('public.receipt', $order->id);
        $footerNote  = $business?->pos_receipt_footer_note ?? '';

        $template = $business?->pos_receipt_wa_template;
        if (! $template) {
            $session  = WhatsAppSession::where('business_id', $business->id)->first();
            $template = $session?->receipt_template;
        }

        if (! empty(trim((string) $template))) {
            $replacements = [
                '{business_name}' => $bizName,
                '{customer_name}' => $custName ?? 'Pelanggan',
                '{order_number}'  => $order->order_number,
                '{date}'          => $orderDate,
                '{cashier_name}'  => $cashierName,
                '{receipt_link}'  => $receiptUrl,
                '{footer_note}'   => $footerNote,
            ];

            return strtr($template, $replacements);
        }

        $greeting = $custName ? "Halo Kak *{$custName}*! 🙏\n" : "Halo! 🙏\n";

        $text  = "🧾 *STRUK PEMBELIAN*\n";
        $text .= "*{$bizName}*\n\n";
        $text .= $greeting;
        $text .= "Terima kasih banyak telah berbelanja di *{$bizName}*.\n\n";
        $text .= "Terlampir gambar struk digital untuk transaksi Anda.\n\n";

        if ($footerNote) {
            $text .= $footerNote . "\n\n";
        }

        $text .= "Semoga hari Anda menyenangkan! ✨";

        return $text;
    }

    /**
     * Execute a broadcast campaign: resolve recipients and send messages with throttle.
     */
    public function sendBroadcast(Business $business, WhatsAppBroadcastCampaign $campaign): void
    {
        $campaign->update(['status' => 'processing']);

        $customers = $this->resolveBroadcastRecipients($business, $campaign->target_filter);

        $campaign->update(['total_recipients' => $customers->count()]);

        $sent   = 0;
        $failed = 0;

        foreach ($customers as $customer) {
            $phone = $customer->phone ?? '';
            if (! $phone) {
                continue;
            }

            $personalizedMsg = $this->personalizeMessage(
                $campaign->message,
                $customer,
                $business
            );

            $options = [];
            if ($campaign->media_url) {
                $options['url'] = $campaign->media_url;
            }

            $result = $this->sendMessage($business, $phone, $personalizedMsg, $options);
            $ok     = $result['success'] ?? false;

            WhatsAppBroadcastRecipient::create([
                'campaign_id'   => $campaign->id,
                'customer_id'   => $customer->id,
                'customer_name' => $customer->name,
                'phone_number'  => $phone,
                'status'        => $ok ? 'sent' : 'failed',
                'sent_at'       => $ok ? now() : null,
                'error_message' => $result['error'] ?? null,
            ]);

            $ok ? $sent++ : $failed++;

            // Throttle 1.5s per message to avoid WhatsApp spam filter
            usleep(1_500_000);
        }

        $campaign->update([
            'total_sent'   => $sent,
            'total_failed' => $failed,
            'status'       => 'completed',
        ]);
    }

    /**
     * Personalize a broadcast message with customer-specific variables.
     */
    protected function personalizeMessage(string $template, Customer $customer, Business $business): string
    {
        return str_replace(
            ['{nama}', '{poin}', '{tier}', '{bisnis}'],
            [
                $customer->name,
                number_format($customer->points_balance, 0, ',', '.'),
                $customer->membership_tier ?? 'Pelanggan',
                $business->name,
            ],
            $template
        );
    }

    /**
     * Resolve the list of customers for a given broadcast filter.
     */
    protected function resolveBroadcastRecipients(Business $business, string $filter)
    {
        $query = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if (! in_array($filter, ['all', 'custom'], true)) {
            $query->whereRaw('LOWER(membership_tier) = ?', [strtolower($filter)]);
        }

        return $query->get();
    }

    /**
     * Sync the local WhatsAppSession record with live status from wa-server.
     */
    protected function syncStatus(Business $business, string $rawStatus, array $data): void
    {
        $normalStatus = match (strtoupper($rawStatus)) {
            'CONNECTED'   => 'connected',
            'SCAN_QR'     => 'scan_qr',
            default       => 'disconnected',
        };

        $phoneNumber = null;
        if ($normalStatus === 'connected' && isset($data['user']['id'])) {
            $phoneNumber = explode(':', $data['user']['id'])[0] ?? null;
        }

        WhatsAppSession::updateOrCreate(
            ['business_id' => $business->id],
            [
                'session_id'        => $this->sessionId($business),
                'status'            => $normalStatus,
                'phone_number'      => $phoneNumber,
                'device_name'       => $data['user']['name'] ?? null,
                'last_connected_at' => $normalStatus === 'connected' ? now() : null,
            ]
        );
    }
}
