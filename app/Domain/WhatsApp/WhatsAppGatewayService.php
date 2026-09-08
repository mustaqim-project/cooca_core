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
     * Start (or reconnect) a WhatsApp session for the given business.
     */
    public function startSession(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        $response = Http::timeout(10)->post("{$this->baseUrl}/api/sessions/start", [
            'sessionId'  => $sessionId,
            'webhookUrl' => url('/api/wa/webhook'),
        ]);

        $waSession = WhatsAppSession::firstOrNew(['business_id' => $business->id]);
        $waSession->session_id = $sessionId;
        $waSession->status     = 'scan_qr';
        $waSession->save();

        return $response->json() ?? [];
    }

    /**
     * Return the current QR code (base64 data URL) and connection status from wa-server.
     */
    public function getQrCode(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        try {
            $response = Http::timeout(8)->get("{$this->baseUrl}/api/sessions/{$sessionId}/qr", [
                'Accept' => 'application/json',
            ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'disconnected', 'qrDataUrl' => null, 'error' => $e->getMessage()];
        }

        $data = $response->json() ?? [];

        // Sync local session record with live status
        $this->syncStatus($business, $data['status'] ?? 'disconnected', $data);

        return $data;
    }

    /**
     * Check & sync current session status.
     */
    public function getStatus(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        try {
            $response = Http::timeout(8)->get("{$this->baseUrl}/api/sessions/{$sessionId}/status");
            $data     = $response->json() ?? [];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'disconnected'];
        }

        $this->syncStatus($business, $data['status'] ?? 'disconnected', $data);

        return $data;
    }

    /**
     * Disconnect and delete the session from wa-server, clean local record.
     */
    public function disconnect(Business $business): void
    {
        $sessionId = $this->sessionId($business);

        try {
            Http::timeout(10)->delete("{$this->baseUrl}/api/sessions/{$sessionId}");
        } catch (\Throwable $e) {
            Log::warning("[WA] disconnect failed for {$sessionId}: " . $e->getMessage());
        }

        WhatsAppSession::where('business_id', $business->id)->update([
            'status'       => 'disconnected',
            'phone_number' => null,
            'device_name'  => null,
        ]);
    }

    /**
     * Send a plain text or media message via wa-server.
     */
    public function sendMessage(Business $business, string $phone, string $message, array $options = []): array
    {
        return $this->sendRawMessage($this->sessionId($business), $phone, $message, $options);
    }

    /**
     * Send message using any raw session ID (e.g. admin_platform).
     */
    public function sendRawMessage(string $sessionId, string $phone, string $message, array $options = []): array
    {
        try {
            $payload = array_merge([
                'session' => $sessionId,
                'target'  => $phone,
                'message' => $message,
            ], $options);

            $response = Http::timeout(20)->post("{$this->baseUrl}/send-message", $payload);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("[WA] sendRawMessage error ({$sessionId}): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Format and send a POS receipt via bot WA to the customer's phone.
     */
    public function sendReceipt(PosOrder $order, ?string $customPhone = null): bool
    {
        $business = $order->business;
        $phone    = $customPhone ?? $order->customer?->phone ?? '';

        if (! $phone) {
            return false;
        }

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        if (! $session || $session->status !== 'connected') {
            return false;
        }

        $message = $this->buildReceiptMessage($order);

        $result = $this->sendMessage($business, $phone, $message);

        WhatsAppMessageLog::create([
            'business_id'    => $business->id,
            'type'           => 'receipt',
            'recipient_phone' => $phone,
            'recipient_name' => $order->customer?->name ?? $order->customer_name_guest ?? 'Pelanggan',
            'message'        => $message,
            'status'         => ($result['success'] ?? false) ? 'sent' : 'failed',
            'order_id'       => $order->id,
            'error_message'  => $result['error'] ?? null,
        ]);

        return $result['success'] ?? false;
    }

    /**
     * Build a formatted receipt message string from a PosOrder.
     */
    public function buildReceiptMessage(PosOrder $order): string
    {
        $business       = $order->business;
        $currencySymbol = $business?->currency_symbol ?? 'Rp';
        $bizName        = $business?->name ?? 'COOCA POS';

        $order->loadMissing(['items', 'payments', 'customer', 'user']);

        $text  = "🧾 *STRUK PEMBELIAN DIGITAL*\n";
        $text .= "📍 *{$bizName}*\n";
        $text .= "--------------------------------\n";
        $text .= "No. Order : #{$order->order_number}\n";
        $text .= "Tanggal   : " . $order->order_date->format('d/m/Y H:i') . "\n";
        $text .= "Kasir     : " . ($order->user?->name ?? 'Kasir') . "\n";
        if ($order->customer) {
            $text .= "Pelanggan : {$order->customer->name} ({$order->customer->membership_tier})\n";
        }
        $text .= "--------------------------------\n";

        foreach ($order->items as $item) {
            $qty      = rtrim(rtrim((string) $item->quantity, '0'), '.');
            $subtotal = number_format($item->total_price, 0, ',', '.');
            $price    = number_format($item->unit_price, 0, ',', '.');
            $text .= "{$item->product_name}\n";
            $text .= "  {$qty} x {$currencySymbol}{$price} = {$currencySymbol}{$subtotal}\n";
        }

        $text .= "--------------------------------\n";
        $text .= "Subtotal   : {$currencySymbol}" . number_format($order->subtotal, 0, ',', '.') . "\n";

        if ($order->discount_amount > 0 || $order->voucher_discount_amount > 0) {
            $disc  = $order->discount_amount + $order->voucher_discount_amount;
            $text .= "Diskon     : -{$currencySymbol}" . number_format($disc, 0, ',', '.') . "\n";
        }

        if ($order->tax_amount > 0) {
            $text .= "Pajak PPN  : {$currencySymbol}" . number_format($order->tax_amount, 0, ',', '.') . "\n";
        }

        $text .= "*TOTAL     : {$currencySymbol}" . number_format($order->total_amount, 0, ',', '.') . "*\n";
        $text .= "Bayar      : {$currencySymbol}" . number_format($order->paid_amount, 0, ',', '.') . "\n";
        $text .= "Kembalian  : {$currencySymbol}" . number_format($order->change_amount, 0, ',', '.') . "\n";

        if ($order->points_earned > 0) {
            $text .= "Poin Baru  : +{$order->points_earned} Poin 🎉\n";
        }

        $text .= "--------------------------------\n";

        if ($business?->pos_receipt_footer_note) {
            $text .= $business->pos_receipt_footer_note . "\n";
        } else {
            $text .= "Terima kasih atas kunjungan Anda! 🙏\n";
        }

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
