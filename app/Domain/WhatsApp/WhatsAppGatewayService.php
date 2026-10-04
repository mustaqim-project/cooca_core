<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp;

use App\Domain\WhatsApp\CloudApi\WhatsAppClient;
use App\Models\Business;
use App\Models\Customer;
use App\Models\PosOrder;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppBroadcastRecipient;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver;

class WhatsAppGatewayService
{
    protected string $baseUrl;
    protected string $token;
    protected MetaWhatsAppCloudDriver $metaDriver;

    public function __construct(?MetaWhatsAppCloudDriver $metaDriver = null)
    {
        $this->baseUrl = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');
        $this->token   = config('services.wa_server.token', 'secret-worker-token');
        $this->metaDriver = $metaDriver ?? app(MetaWhatsAppCloudDriver::class);
    }

    /**
     * Derive a deterministic, per-business session ID from the business UUID.
     */
    public function sessionId(Business $business): string
    {
        return 'biz_' . str_replace('-', '', substr($business->id, 0, 8));
    }

    /**
     * Create an authenticated HTTP client (kept for backwards compatibility).
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
     * Start (or reconnect) a WhatsApp session for the given business (official Meta Cloud).
     */
    public function startSession(Business $business): array
    {
        $sessionId = $this->sessionId($business);
        $account   = WhatsAppAccount::where('business_id', $business->id)->first();

        if ($account && $account->isConnected()) {
            return ['success' => true, 'status' => 'connected'];
        }

        return ['success' => false, 'status' => 'disconnected', 'message' => 'Gunakan Meta Embedded Signup resmi.'];
    }

    /**
     * Disconnect and clear the WhatsApp session.
     */
    public function disconnectSession(Business $business): array
    {
        $sessionId = $this->sessionId($business);

        $account = WhatsAppAccount::where('business_id', $business->id)->first();
        if ($account) {
            $account->status = 'disconnected';
            $account->save();
        }

        $waSession = WhatsAppSession::where('business_id', $business->id)->first();
        if ($waSession) {
            $waSession->status            = 'disconnected';
            $waSession->phone_number      = null;
            $waSession->device_name       = null;
            $waSession->last_connected_at = null;
            $waSession->save();
        }

        return ['success' => true, 'message' => 'Sesi WhatsApp diputus.'];
    }

    /**
     * Alias for disconnectSession().
     */
    public function disconnect(Business $business): array
    {
        return $this->disconnectSession($business);
    }

    /**
     * Get live session status (checks official Meta WhatsAppAccount or local session).
     */
    public function getSessionStatus(Business $business): array
    {
        $account = WhatsAppAccount::where('business_id', $business->id)->first();
        if ($account && $account->isConnected()) {
            return [
                'status' => 'connected',
                'phone'  => $account->display_phone_number ?: $account->phone_number,
                'driver' => 'meta_cloud',
            ];
        }

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        if ($session && $session->status === 'connected') {
            return [
                'status' => 'connected',
                'phone'  => $session->phone_number,
                'driver' => $session->provider ?? 'meta_cloud',
            ];
        }

        return ['status' => 'disconnected', 'phone' => null];
    }

    /**
     * Alias for getSessionStatus().
     */
    public function getStatus(Business $business): array
    {
        return $this->getSessionStatus($business);
    }

    /**
     * Get live QR code and session metadata (stubbed for backward compatibility).
     */
    public function getQrData(Business $business): array
    {
        $status = $this->getSessionStatus($business);
        return [
            'status'    => $status['status'],
            'qrDataUrl' => null,
            'phone'     => $status['phone'] ?? null,
        ];
    }

    /**
     * Get live QR code for the session (string only).
     */
    public function getQrCode(Business $business): ?string
    {
        return null;
    }

    /**
     * Send a WhatsApp message via official Meta Cloud API for a given business.
     */
    public function sendMessage(Business $business, string $phone, string $message, array $options = []): array
    {
        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        if (! $entitlement->canSendWhatsAppThisMonth($business)) {
            return [
                'success' => false,
                'error'   => 'Batas kuota pesan WhatsApp gratis bulan ini (10 pesan) telah tercapai. Upgrade ke Cooca Core (Rp 49.000/bln) untuk kirim tanpa batas!',
            ];
        }

        $result = $this->executeSendMessage($business, $phone, $message, $options);

        if ($result['success'] ?? false) {
            $entitlement->incrementMonthlyUsage($business, \App\Models\QuotaMonthlyUsage::TYPE_WHATSAPP, \App\Domain\Billing\EntitlementService::FREE_WHATSAPP_MONTHLY_LIMIT);
            $entitlement->clearUsageCache($business);
        }

        return $result;
    }

    /**
     * Internal implementation of Meta Cloud API / WABA sending.
     */
    protected function executeSendMessage(Business $business, string $phone, string $message, array $options = []): array
    {
        // 1. Prioritaskan Akun Meta WhatsApp Cloud API resmi toko (WhatsAppAccount)
        $account = WhatsAppAccount::where('business_id', $business->id)->first();
        if ($account && $account->isConnected()) {
            $client = WhatsAppClient::forAccount($account);

            // A. Mode Template Resmi Meta (Mandatori Anti-Blokir untuk Outbound Proaktif)
            if (! empty($options['template_name'])) {
                $components = $options['components'] ?? $this->buildTemplateComponentsFromParams($options['template_params'] ?? [], $options);
                return $client->sendTemplateMessage(
                    $phone,
                    $options['template_name'],
                    $options['template_language'] ?? 'id',
                    $components
                );
            }

            if (! empty($options['url'])) {
                return $client->sendMediaMessage(
                    $phone,
                    $options['type'] ?? 'image',
                    $options['url'],
                    $message,
                    $options['filename'] ?? null
                );
            }
            return $client->sendTextMessage($phone, $message);
        }

        // 2. Cek Legacy WhatsAppSession jika telah menyimpan kredensial Meta manual
        $session = WhatsAppSession::where('business_id', $business->id)->first();
        if ($session && ! $session->is_active) {
            return [
                'success' => false,
                'error'   => 'Layanan WhatsApp bisnis sedang dinonaktifkan.',
            ];
        }

        if ($session && ! empty($session->meta_access_token) && ! empty($session->meta_phone_number_id)) {
            $token   = $session->meta_access_token;
            $phoneId = $session->meta_phone_number_id;
            return $this->metaDriver->sendTextMessage($phone, $message, $token, $phoneId);
        }

        // 3. Fallback: Toko belum menghubungkan Meta WhatsApp resmi
        return [
            'success' => false,
            'error'   => 'Akun WhatsApp Business resmi (Meta WABA) belum terhubung ke toko Anda. Silakan hubungkan melalui menu WhatsApp Gateway.',
        ];
    }

    /**
     * Update or save session provider and credentials for a business.
     */
    public function updateSessionProvider(Business $business, array $data): WhatsAppSession
    {
        $session = WhatsAppSession::firstOrNew(['business_id' => $business->id]);
        $session->session_id = $session->session_id ?: $this->sessionId($business);

        if (isset($data['provider'])) {
            $session->provider = $data['provider'];
        }
        if (isset($data['is_active'])) {
            $session->is_active = (bool) $data['is_active'];
        }
        if (array_key_exists('meta_phone_number_id', $data)) {
            $session->meta_phone_number_id = $data['meta_phone_number_id'];
        }
        if (! empty($data['meta_access_token'])) {
            $session->meta_access_token = $data['meta_access_token'];
        }
        if (array_key_exists('meta_waba_id', $data)) {
            $session->meta_waba_id = $data['meta_waba_id'];
        }
        if (array_key_exists('meta_template_name', $data)) {
            $session->meta_template_name = $data['meta_template_name'];
        }

        if ($session->provider === 'meta_cloud' && ! empty($session->meta_access_token) && ! empty($session->meta_phone_number_id)) {
            $session->status = 'connected';
            $session->last_connected_at = now();
        }

        $session->save();

        return $session;
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

        $metaAccount = WhatsAppAccount::where('business_id', $business->id)
            ->where('status', 'active')
            ->first();

        if ($metaAccount && $metaAccount->isActive()) {
            $result = app(\App\Domain\WhatsApp\CloudApi\WhatsAppTemplateService::class)
                ->sendPosReceipt($order, $phone, $force);

            return (bool) ($result['success'] ?? false);
        }

        $session = WhatsAppSession::where('business_id', $business->id)->first();
        if (! $session || ! $session->is_active) {
            return false;
        }

        if ($session->provider === 'baileys' && $session->status !== 'connected') {
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
                $imageUrl     = \App\Domain\Storage\TenantStorage::url($relativePath) ?? asset('storage/' . $relativePath);

                $localFile    = storage_path('app/public/' . $relativePath);
                if (! file_exists($localFile)) {
                    $localFile = public_path($relativePath);
                }
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

        $greeting = $custName ? "Halo Kak *{$custName}*!\n" : "Halo!\n";

        $text  = "*STRUK PEMBELIAN*\n";
        $text .= "*{$bizName}*\n\n";
        $text .= $greeting;
        $text .= "Terima kasih banyak telah berbelanja di *{$bizName}*.\n\n";
        $text .= "Terlampir gambar struk digital untuk transaksi Anda.\n\n";

        if ($footerNote) {
            $text .= $footerNote . "\n\n";
        }

        $text .= "Semoga hari Anda menyenangkan.";

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

            // Meta Official Template Enforcement to prevent account ban
            $metaAccount = WhatsAppAccount::where('business_id', $business->id)->where('status', 'active')->first();
            if ($metaAccount && $metaAccount->isActive()) {
                $templateName = $campaign->template_name ?: \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::PROMO_BROADCAST;
                $options['template_name']     = $templateName;
                $options['template_language'] = $campaign->template_language ?? 'id';
                $options['template_params']   = $this->resolveBroadcastTemplateParams($campaign, $customer, $business, $templateName);
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

            // Throttling to respect Meta Cloud API rate limits
            usleep(150_000); // 150ms delay between messages
        }

        $campaign->update([
            'total_sent'   => $sent,
            'total_failed' => $failed,
            'status'       => 'completed',
        ]);
    }

    /**
     * Personalize a broadcast message with customer-specific and multi-industry contextual variables.
     */
    protected function personalizeMessage(string $template, Customer $customer, Business $business): string
    {
        $templateCode = (string) ($business->template_code ?? 'retail_general');
        $extraReplacements = [];

        // Check if customer has latest POS order for contextual details
        $lastOrder = $customer->posOrders()->latest()->first();

        // 1. F&B & Culinary ({meja})
        if (str_starts_with($templateCode, 'fnb_')) {
            $extraReplacements['{meja}'] = $lastOrder?->posTable?->table_number 
                ?? $lastOrder?->table_or_reference 
                ?? 'Meja Pelanggan';
        }

        // 2. Bengkel / Otomotif ({nopol}, {servis_terakhir})
        if ($templateCode === 'service_workshop') {
            $extraReplacements['{nopol}'] = $lastOrder?->vehicle_license_plate 
                ?? ($lastOrder?->vehicle_model ? $lastOrder->vehicle_model : 'Kendaraan Anda');
            $extraReplacements['{servis_terakhir}'] = $lastOrder?->created_at 
                ? $lastOrder->created_at->format('d/m/Y') 
                : 'Servis Berkala';
        }

        // 3. Laundry / Jasa Cuci ({no_rak}, {berat_kg})
        if ($templateCode === 'service_laundry') {
            $extraReplacements['{no_rak}'] = 'Rak Penyimpanan';
            $extraReplacements['{berat_kg}'] = 'Cucian Anda';
        }

        // 4. Manufaktur, Konveksi & Tailor ({no_spk}, {produk})
        if (str_starts_with($templateCode, 'mfg_') || $templateCode === 'tailor') {
            $extraReplacements['{no_spk}'] = 'SPK Produksi';
            $extraReplacements['{produk}'] = $lastOrder?->items()->first()?->product_name ?? 'Pesanan Khusus';
        }

        // 5. Kontraktor & Desain Interior ({proyek}, {termin})
        if ($templateCode === 'service_contractor') {
            $extraReplacements['{proyek}'] = 'Proyek Berjalan';
            $extraReplacements['{termin}'] = 'Termin Saat Ini';
        }

        // 6. Apotek & Klinik Farmasi ({no_resep})
        if ($templateCode === 'retail_pharmacy') {
            $extraReplacements['{no_resep}'] = 'Resep Obat';
        }

        // Default fallbacks for all tags if still present in template regardless of template_code
        $defaultFallbacks = [
            '{meja}'            => $lastOrder?->posTable?->table_number ?? $lastOrder?->table_or_reference ?? 'Meja',
            '{nopol}'           => $lastOrder?->vehicle_license_plate ?? 'Kendaraan Anda',
            '{servis_terakhir}' => $lastOrder?->created_at ? $lastOrder->created_at->format('d/m/Y') : 'Servis Berkala',
            '{no_rak}'          => 'Rak Penyimpanan',
            '{berat_kg}'        => 'Cucian Anda',
            '{no_spk}'          => 'SPK Produksi',
            '{produk}'          => $lastOrder?->items()->first()?->product_name ?? 'Pesanan Khusus',
            '{proyek}'          => 'Proyek Anda',
            '{termin}'          => 'Termin Berjalan',
            '{no_resep}'        => 'Resep Obat',
        ];

        $mergedExtras = array_merge($defaultFallbacks, $extraReplacements);

        $search = array_merge(['{nama}', '{poin}', '{tier}', '{bisnis}'], array_keys($mergedExtras));
        $replace = array_merge([
            $customer->name,
            number_format($customer->points_balance, 0, ',', '.'),
            $customer->membership_tier ?? 'Pelanggan',
            $business->name,
        ], array_values($mergedExtras));

        return str_replace($search, $replace, $template);
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

        if (str_starts_with($filter, 'outlet:')) {
            $locationId = substr($filter, 7);
            $customerIds = \App\Models\PosOrder::where('business_id', $business->id)
                ->where('location_id', $locationId)
                ->whereNotNull('customer_id')
                ->pluck('customer_id')
                ->unique();

            $query->whereIn('id', $customerIds);
        } elseif (! in_array($filter, ['all', 'custom'], true)) {
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

    /**
     * Susun komponen parameter template untuk Meta Cloud API.
     */
    protected function buildTemplateComponentsFromParams(array $params, array $options = []): array
    {
        $components = [];

        if (! empty($options['url'])) {
            $type = $options['type'] ?? 'image';
            $components[] = [
                'type'       => 'header',
                'parameters' => [
                    [
                        'type' => $type,
                        $type  => ['link' => $options['url']],
                    ],
                ],
            ];
        }

        if (! empty($params)) {
            $bodyParams = [];
            foreach ($params as $val) {
                $bodyParams[] = [
                    'type' => 'text',
                    'text' => (string) $val,
                ];
            }
            $components[] = [
                'type'       => 'body',
                'parameters' => $bodyParams,
            ];
        }

        return $components;
    }

    /**
     * Resolusi parameter dinamis untuk template siaran promosi & notifikasi.
     */
    protected function resolveBroadcastTemplateParams(
        WhatsAppBroadcastCampaign $campaign,
        Customer $customer,
        Business $business,
        string $templateName
    ): array {
        $customerName = (string) ($customer->name ?: 'Pelanggan Setia');
        $storeName    = (string) ($business->name ?: 'Toko Kami');
        $lastOrder    = $customer->posOrders()->latest()->first();

        return match ($templateName) {
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::PROMO_BROADCAST => [
                $customerName,
                $storeName,
                (string) ($campaign->template_params['offer'] ?? $campaign->message),
                (string) ($campaign->template_params['voucher_code'] ?? 'HEMAT'),
                (string) ($campaign->template_params['valid_until'] ?? now()->addDays(7)->format('d/m/Y')),
            ],
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::CUSTOMER_WELCOME => [
                $customerName,
                $storeName,
                $customer->membership_tier ? ucfirst((string) $customer->membership_tier) . ' Member' : 'Member Setia',
                (string) ($customer->loyalty_points ?? 0),
            ],
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::ORDER_STATUS => [
                $customerName,
                $storeName,
                (string) ($campaign->template_params['reference'] ?? ($lastOrder?->order_number ?? 'Pesanan')),
                (string) ($campaign->template_params['status'] ?? 'Diproses'),
                (string) ($campaign->template_params['notes'] ?? 'Terima kasih atas pesanan Anda'),
            ],
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::RESERVATION_REMINDER => [
                $customerName,
                $storeName,
                (string) ($campaign->template_params['booking_code'] ?? 'RSV-' . rand(1000, 9999)),
                (string) ($campaign->template_params['schedule'] ?? now()->addDay()->format('d/m/Y H:i') . ' WIB'),
                (string) ($campaign->template_params['details'] ?? 'Reservasi Layanan Pelanggan'),
            ],
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::MARKETPLACE_RECEIPT => [
                $customerName,
                (string) ($campaign->template_params['order_number'] ?? ($lastOrder?->order_number ?? 'ORD-' . date('YmdHis'))),
                $storeName,
                (string) ($campaign->template_params['amount'] ?? 'Rp ' . number_format((float) ($lastOrder?->total_amount ?? 0), 0, ',', '.')),
                (string) ($campaign->template_params['payment_method'] ?? 'QRIS / Transfer Bank'),
            ],
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::SHIPPING_TRACKING => [
                $customerName,
                (string) ($campaign->template_params['order_number'] ?? ($lastOrder?->order_number ?? 'ORD-' . date('YmdHis'))),
                $storeName,
                (string) ($campaign->template_params['courier'] ?? 'J&T Express'),
                (string) ($campaign->template_params['tracking_number'] ?? 'JT' . rand(1000000000, 9999999999)),
            ],
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::CART_REMINDER => [
                $customerName,
                $storeName,
                (string) ($campaign->template_params['items_summary'] ?? 'Item di keranjang Anda'),
                (string) ($campaign->template_params['offer'] ?? 'Diskon 10% kupon HEMAT'),
                (string) ($campaign->template_params['valid_until'] ?? 'Hari ini 23:59 WIB'),
            ],
            default => array_values($campaign->template_params ?? [$customerName, $storeName, $campaign->title, $campaign->message]),
        };
    }
}

