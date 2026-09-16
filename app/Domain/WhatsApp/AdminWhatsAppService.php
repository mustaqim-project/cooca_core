<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SystemSetting;
use App\Models\WhatsAppAdminBlast;
use App\Models\WhatsAppAdminBlastRecipient;
use App\Models\WhatsAppAdminSession;
use App\Models\WhatsAppSubscriptionReminder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Domain\WhatsApp\Drivers\MetaWhatsAppCloudDriver;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminWhatsAppService
{
    public const ADMIN_SESSION_ID = 'admin_platform';

    public function __construct(
        protected WhatsAppGatewayService $gateway,
        protected ?MetaWhatsAppCloudDriver $metaDriver = null
    ) {
        $this->metaDriver = $metaDriver ?? app(MetaWhatsAppCloudDriver::class);
    }

    /**
     * Return fixed session ID for Platform Administrator.
     */
    public function getSessionId(): string
    {
        return config('services.wa_server.admin_session', self::ADMIN_SESSION_ID);
    }

    /**
     * Create authenticated client with resilient timeout.
     */
    protected function client(int $timeout = 25)
    {
        $token = config('services.wa_server.token', 'secret-worker-token');

        return Http::timeout($timeout)
            ->retry(2, 600, throw: false)
            ->withHeaders([
                'Authorization'  => 'Bearer ' . $token,
                'x-worker-token' => $token,
                'Accept'         => 'application/json',
            ]);
    }

    /**
     * Retrieve all configured WhatsApp Admin sessions.
     * Auto-initializes default session if table is empty.
     *
     * @return \Illuminate\Support\Collection<int, WhatsAppAdminSession>
     */
    public function getSessions(): \Illuminate\Support\Collection
    {
        $sessions = WhatsAppAdminSession::orderBy('id')->get();

        if ($sessions->isEmpty()) {
            $default = WhatsAppAdminSession::create([
                'session_id' => $this->getSessionId(),
                'name'       => 'Nomor Admin Utama',
                'status'     => 'disconnected',
                'is_active'  => true,
            ]);

            return collect([$default]);
        }

        return $sessions;
    }

    /**
     * Create a new Admin WhatsApp session in pool and trigger start.
     */
    public function createSession(?string $name = null, ?string $customSessionId = null): WhatsAppAdminSession
    {
        $count = WhatsAppAdminSession::count() + 1;
        $sessionName = $name ?: "Nomor WhatsApp Admin {$count}";
        $sessionId = $customSessionId ?: ('admin_wa_' . time() . '_' . strtolower(Str::random(4)));

        $session = WhatsAppAdminSession::create([
            'session_id' => $sessionId,
            'name'       => $sessionName,
            'status'     => 'scan_qr',
            'is_active'  => true,
        ]);

        $this->startSession($sessionId);

        return $session;
    }

    /**
     * Start / trigger WhatsApp session for admin.
     */
    public function startSession(?string $sessionId = null): array
    {
        $sessionId = $sessionId ?: $this->getSessionId();
        $baseUrl   = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        // Ensure record exists in DB
        WhatsAppAdminSession::firstOrCreate(
            ['session_id' => $sessionId],
            ['name' => 'Nomor WhatsApp Admin', 'status' => 'scan_qr', 'is_active' => true]
        );

        try {
            $response = $this->client(30)->post("{$baseUrl}/api/sessions/start", [
                'sessionId'  => $sessionId,
                'webhookUrl' => url('/api/wa/admin-webhook'),
            ]);

            WhatsAppAdminSession::where('session_id', $sessionId)->update([
                'status'     => 'scan_qr',
                'updated_at' => now(),
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("[AdminWA] startSession error ({$sessionId}): " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get QR code data URL and connection status for admin session.
     */
    public function getQrCode(?string $sessionId = null): array
    {
        $sessionId = $sessionId ?: $this->getSessionId();
        $baseUrl   = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        try {
            $response = $this->client(15)->get("{$baseUrl}/api/sessions/{$sessionId}/qr");

            if ($response->status() === 404) {
                // Auto-start session on wa-server if not in active memory
                $this->startSession($sessionId);
                return ['success' => false, 'status' => 'scan_qr', 'qrDataUrl' => null];
            }

            $data = $response->json() ?? [];
            if (!empty($data['qrDataUrl'])) {
                WhatsAppAdminSession::where('session_id', $sessionId)->update([
                    'qr_data_url' => $data['qrDataUrl'],
                    'status'      => 'scan_qr',
                    'updated_at'  => now(),
                ]);
            }

            if (($data['status'] ?? null) === 'CONNECTED') {
                WhatsAppAdminSession::where('session_id', $sessionId)->update([
                    'status'            => 'connected',
                    'last_connected_at' => now(),
                    'updated_at'        => now(),
                ]);
            }

            return $data;
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'disconnected', 'qrDataUrl' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get live status of admin WhatsApp session (single or aggregated).
     */
    public function getStatus(?string $sessionId = null): array
    {
        $baseUrl = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        if ($sessionId !== null) {
            try {
                $response = $this->client(15)->get("{$baseUrl}/api/sessions/{$sessionId}/status");
                $data = $response->json() ?? ['status' => 'disconnected'];

                $status = strtolower($data['status'] ?? 'disconnected');
                $phone  = $data['phone'] ?? ($data['user']['id'] ? explode(':', (string) $data['user']['id'])[0] : null);

                $updateData = ['status' => $status, 'updated_at' => now()];
                if ($phone) {
                    $updateData['phone_number'] = $phone;
                }
                if ($status === 'connected') {
                    $updateData['last_connected_at'] = now();
                }

                WhatsAppAdminSession::where('session_id', $sessionId)->update($updateData);

                return [
                    ...$data,
                    'status' => $status,
                    'phone'  => $phone,
                ];
            } catch (\Throwable) {
                return ['status' => 'disconnected'];
            }
        }

        // Single / Aggregate status for backward compatibility:
        $targetSessionId = $this->getSessionId();
        try {
            $response = $this->client(15)->get("{$baseUrl}/api/sessions/{$targetSessionId}/status");
            $data = $response->json() ?? ['status' => 'disconnected'];
            $status = strtolower($data['status'] ?? 'disconnected');
            $phone  = $data['phone'] ?? ($data['user']['id'] ? explode(':', (string) $data['user']['id'])[0] : null);

            // Sync default record
            $update = ['status' => $status, 'updated_at' => now()];
            if ($phone) {
                $update['phone_number'] = $phone;
            }
            if ($status === 'connected') {
                $update['last_connected_at'] = now();
            }
            WhatsAppAdminSession::where('session_id', $targetSessionId)->update($update);

            // If default is not connected, but another session is connected, show aggregate status connected
            if ($status !== 'connected') {
                $anyConnected = WhatsAppAdminSession::where('status', 'connected')->where('is_active', true)->first();
                if ($anyConnected) {
                    $status = 'connected';
                    $phone  = $anyConnected->phone_number ?: $phone;
                    $data['status'] = 'connected';
                }
            }

            return [
                ...$data,
                'status' => $status,
                'phone'  => $phone,
            ];
        } catch (\Throwable) {
            $anyConnected = WhatsAppAdminSession::where('status', 'connected')->where('is_active', true)->first();
            if ($anyConnected) {
                return [
                    'status' => 'connected',
                    'phone'  => $anyConnected->phone_number,
                ];
            }
            return ['status' => 'disconnected'];
        }
    }

    /**
     * Disconnect specific admin WhatsApp session.
     */
    public function disconnectSession(string $sessionId): void
    {
        $baseUrl = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        try {
            $this->client(15)->delete("{$baseUrl}/api/sessions/{$sessionId}");
        } catch (\Throwable $e) {
            Log::warning("[AdminWA] disconnectSession ({$sessionId}) error: " . $e->getMessage());
        }

        WhatsAppAdminSession::where('session_id', $sessionId)->update([
            'status'            => 'disconnected',
            'phone_number'      => null,
            'qr_data_url'       => null,
            'last_connected_at' => null,
            'updated_at'        => now(),
        ]);
    }

    /**
     * Delete an admin WhatsApp session entirely.
     */
    public function deleteSession(string $sessionId): void
    {
        $this->disconnectSession($sessionId);
        WhatsAppAdminSession::where('session_id', $sessionId)->delete();
    }

    /**
     * Toggle session active status in random pool.
     */
    public function toggleSessionActive(string $sessionId): bool
    {
        $session = WhatsAppAdminSession::where('session_id', $sessionId)->first();
        if ($session) {
            $session->update(['is_active' => !$session->is_active]);
            return (bool) $session->is_active;
        }

        return false;
    }

    /**
     * Get a random connected session ID for load balancing and anti-ban rotation.
     */
    public function getRandomConnectedSessionId(): string
    {
        $connectedSessions = WhatsAppAdminSession::where('status', 'connected')
            ->where('is_active', true)
            ->get();

        if ($connectedSessions->isNotEmpty()) {
            return $connectedSessions->random()->session_id;
        }

        return $this->getSessionId();
    }

    /**
     * Disconnect admin WhatsApp session (default session or specific).
     */
    public function disconnect(?string $sessionId = null): void
    {
        $sessionId = $sessionId ?: $this->getSessionId();
        $this->disconnectSession($sessionId);
    }

    /**
     * Get active OTP gateway driver ('meta_cloud', 'baileys', 'disabled').
     */
    public function getOtpDriver(): string
    {
        return (string) SystemSetting::get('wa_otp_driver', env('WA_OTP_DRIVER', 'baileys'));
    }

    /**
     * Get active Blast gateway driver ('baileys', 'meta_cloud', 'disabled').
     */
    public function getBlastDriver(): string
    {
        return (string) SystemSetting::get('wa_blast_driver', env('WA_BLAST_DRIVER', 'baileys'));
    }

    /**
     * Check whether OTP sending is currently active.
     */
    public function isOtpActive(): bool
    {
        return SystemSetting::get('wa_otp_active', '1') === '1' && $this->getOtpDriver() !== 'disabled';
    }

    /**
     * Check whether Blast broadcast sending is currently active.
     */
    public function isBlastActive(): bool
    {
        return SystemSetting::get('wa_blast_active', '1') === '1' && $this->getBlastDriver() !== 'disabled';
    }

    /**
     * Get Meta WhatsApp Cloud API credentials.
     */
    public function getMetaCredentials(): array
    {
        return [
            'token'           => (string) SystemSetting::get('meta_wa_token', config('services.meta_whatsapp.token', '')),
            'phone_number_id' => (string) SystemSetting::get('meta_wa_phone_number_id', config('services.meta_whatsapp.phone_number_id', '')),
            'waba_id'         => (string) SystemSetting::get('meta_wa_waba_id', config('services.meta_whatsapp.waba_id', '')),
            'otp_template'    => (string) SystemSetting::get('meta_wa_otp_template', config('services.meta_whatsapp.otp_template', 'cooca_otp')),
        ];
    }

    /**
     * Save dual gateway settings to system settings table.
     */
    public function saveGatewaySettings(array $settings): void
    {
        if (isset($settings['otp_driver'])) {
            SystemSetting::set('wa_otp_driver', (string) $settings['otp_driver'], 'whatsapp');
        }
        if (isset($settings['blast_driver'])) {
            SystemSetting::set('wa_blast_driver', (string) $settings['blast_driver'], 'whatsapp');
        }
        if (isset($settings['otp_active'])) {
            SystemSetting::set('wa_otp_active', $settings['otp_active'] ? '1' : '0', 'whatsapp');
        }
        if (isset($settings['blast_active'])) {
            SystemSetting::set('wa_blast_active', $settings['blast_active'] ? '1' : '0', 'whatsapp');
        }
        if (isset($settings['meta_token'])) {
            SystemSetting::set('meta_wa_token', (string) $settings['meta_token'], 'whatsapp', true);
        }
        if (isset($settings['meta_phone_number_id'])) {
            SystemSetting::set('meta_wa_phone_number_id', (string) $settings['meta_phone_number_id'], 'whatsapp');
        }
        if (isset($settings['meta_waba_id'])) {
            SystemSetting::set('meta_wa_waba_id', (string) $settings['meta_waba_id'], 'whatsapp');
        }
        if (isset($settings['meta_otp_template'])) {
            SystemSetting::set('meta_wa_otp_template', (string) $settings['meta_otp_template'], 'whatsapp');
        }
    }

    /**
     * Send Authentication OTP via configured driver (Meta Cloud API or Baileys).
     */
    public function sendOtp(string $phone, string $otpCode): array
    {
        if (!$this->isOtpActive()) {
            Log::info("[AdminWA] OTP channel is disabled. OTP for {$phone} skipped.");
            return [
                'success' => false,
                'error'   => 'Kanal WhatsApp OTP dinonaktifkan oleh administrator.',
            ];
        }

        $driver = $this->getOtpDriver();

        if ($driver === 'meta_cloud') {
            $creds = $this->getMetaCredentials();
            return $this->metaDriver->sendOtp(
                $phone,
                $otpCode,
                $creds['otp_template'] ?: 'cooca_otp',
                $creds['token'] ?: null,
                $creds['phone_number_id'] ?: null
            );
        }

        // Default Baileys Gateway (Pilih nomor acak dari pool WA admin terhubung)
        $message = "Kode OTP keamanan Cooca Anda adalah *{$otpCode}*. Kode ini berlaku 10 menit. Jangan bagikan kode ini kepada siapa pun.";
        return $this->sendMessage($phone, $message, ['driver' => 'baileys']);
    }

    /**
     * Send message using the official Admin WhatsApp session (Baileys or Meta Cloud).
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        $driver = $options['driver'] ?? $this->getBlastDriver();
        if ($driver === 'meta_cloud') {
            $creds = $this->getMetaCredentials();
            if (!empty($creds['token']) && !empty($creds['phone_number_id'])) {
                return $this->metaDriver->sendTextMessage($phone, $message, $creds['token'], $creds['phone_number_id']);
            }
        }

        // Driver Baileys: pilih session ID acak dari nomor-nomor yang sedang aktif terhubung
        $sessionId = $options['session_id'] ?? $this->getRandomConnectedSessionId();

        return $this->gateway->sendRawMessage($sessionId, $phone, $message, $options);
    }

    /**
     * Default reminder message templates.
     */
    public function getDefaultTemplate(string $type): string
    {
        return match ($type) {
            'h-7' => "Halo *{owner}* ({bisnis}) 👋\n\nMasa patungan/langganan paket *{paket}* Cooca Anda akan berakhir dalam *7 hari* (pada {tanggal_habis}).\n\nUntuk memastikan operasional kasir & POS tetap berjalan lancar tanpa terhenti, silakan lakukan perpanjangan langganan melalui link berikut:\n🔗 {link_bayar}\n\nTerima kasih! 🙏\n_Tim Cooca Platform_",
            'h-3' => "Halo *{owner}* ({bisnis}) ⚠️\n\nPengingat penting: Masa patungan/langganan paket *{paket}* Cooca Anda tinggal *3 hari lagi* (jatuh tempo {tanggal_habis}).\n\nYuk perpanjang sekarang agar fitur POS & laporan keuangan Anda tidak dinonaktifkan:\n🔗 {link_bayar}\n\n_Tim Cooca Platform_",
            'h-1' => "Halo *{owner}* ({bisnis}) 🚨\n\n*H-1 SEBELUM EXPIRED!*\nMasa langganan paket *{paket}* untuk bisnis *{bisnis}* akan berakhir *BESOK* ({tanggal_habis}).\n\nSegera amankan layanan bisnis Anda sekarang:\n🔗 {link_bayar}\n\nButuh bantuan? Hubungi admin resmi di 0823 3749 9577.\n_Tim Cooca Platform_",
            'hari_h' => "Halo *{owner}* ({bisnis}) 🛑\n\nMasa patungan/langganan paket *{paket}* bisnis Anda *BERAKHIR HARI INI* ({tanggal_habis}).\n\nAkses fitur premium Cooca akan dialihkan ke paket gratis jika perpanjangan belum dilakukan.\nPerpanjang sekarang:\n🔗 {link_bayar}\n\n_Tim Cooca Platform_",
            default => "Halo *{owner}* ({bisnis}), pengingat masa aktif langganan Anda akan berakhir pada {tanggal_habis}. Silakan perpanjang di {link_bayar}.",
        };
    }

    /**
     * Retrieve reminder message template with database fallback.
     */
    public function getTemplate(string $type): string
    {
        return SystemSetting::get('wa_reminder_' . $type, $this->getDefaultTemplate($type));
    }

    /**
     * Save updated template into system_settings.
     */
    public function saveTemplate(string $type, string $content): void
    {
        SystemSetting::set('wa_reminder_' . $type, $content, 'whatsapp');
    }

    /**
     * Get all subscriptions due for H-7, H-3, H-1, and Hari H reminders.
     */
    public function getDueSubscriptions(): array
    {
        $today = Carbon::today();

        $intervals = [
            'h-7'    => $today->copy()->addDays(7)->toDateString(),
            'h-3'    => $today->copy()->addDays(3)->toDateString(),
            'h-1'    => $today->copy()->addDays(1)->toDateString(),
            'hari_h' => $today->toDateString(),
        ];

        $results = [
            'h-7'    => [],
            'h-3'    => [],
            'h-1'    => [],
            'hari_h' => [],
            'stats'  => [
                'total_due'     => 0,
                'total_pending' => 0,
                'total_sent'    => 0,
            ],
        ];

        foreach ($intervals as $type => $targetDate) {
            $subscriptions = BusinessSubscription::with(['business.users'])
                ->whereDate('ends_at', $targetDate)
                ->whereIn('status', [BusinessSubscription::STATUS_ACTIVE, BusinessSubscription::STATUS_PAST_DUE])
                ->get();

            foreach ($subscriptions as $sub) {
                $business = $sub->business;
                if (! $business) {
                    continue;
                }

                $owner = $business->users()->wherePivot('role', 'owner')->first()
                    ?? $business->users()->first();

                $phone = $owner?->phone ?: $business->phone;

                // Check if already sent for this period and reminder type
                $alreadySent = WhatsAppSubscriptionReminder::where('business_subscription_id', $sub->id)
                    ->where('reminder_type', $type)
                    ->where('subscription_ends_at', $targetDate)
                    ->where('status', 'sent')
                    ->exists();

                $item = [
                    'subscription' => $sub,
                    'business'     => $business,
                    'owner'        => $owner,
                    'phone'        => $phone,
                    'ends_at'      => $sub->ends_at,
                    'already_sent' => $alreadySent,
                    'plan_name'    => $this->formatPlanName($sub->plan_code),
                ];

                $results[$type][] = $item;
                $results['stats']['total_due']++;
                $alreadySent ? $results['stats']['total_sent']++ : $results['stats']['total_pending']++;
            }
        }

        return $results;
    }

    /**
     * Send a single subscription reminder.
     */
    public function sendReminder(BusinessSubscription $subscription, string $type, ?string $customMessage = null): array
    {
        $business = $subscription->business;
        $owner    = $business?->users()->wherePivot('role', 'owner')->first()
            ?? $business?->users()->first();

        $phone = $owner?->phone ?: $business?->phone;

        if (! $phone) {
            return ['success' => false, 'error' => 'Nomor HP owner/bisnis tidak ditemukan.'];
        }

        $template = $customMessage ?? $this->getTemplate($type);
        $message  = $this->formatReminderMessage($template, $subscription, $owner, $business);

        $result = $this->sendMessage($phone, $message);
        $ok     = $result['success'] ?? false;

        WhatsAppSubscriptionReminder::create([
            'business_id'              => $business->id,
            'business_subscription_id' => $subscription->id,
            'owner_id'                 => $owner?->id,
            'owner_name'               => $owner?->name ?? 'Pemilik Usaha',
            'business_name'            => $business->name,
            'recipient_phone'          => $phone,
            'reminder_type'            => $type,
            'subscription_ends_at'     => $subscription->ends_at?->toDateString(),
            'message'                  => $message,
            'status'                   => $ok ? 'sent' : 'failed',
            'error_message'            => $result['error'] ?? null,
            'sent_at'                  => now(),
        ]);

        return $result;
    }

    /**
     * Send all pending due reminders for today (H-7, H-3, H-1, Hari H).
     */
    public function sendAllDueReminders(): array
    {
        $dueData = $this->getDueSubscriptions();
        $sent    = 0;
        $failed  = 0;
        $skipped = 0;

        foreach (['h-7', 'h-3', 'h-1', 'hari_h'] as $type) {
            foreach ($dueData[$type] as $item) {
                if ($item['already_sent'] || empty($item['phone'])) {
                    $skipped++;
                    continue;
                }

                $res = $this->sendReminder($item['subscription'], $type);
                if ($res['success'] ?? false) {
                    $sent++;
                } else {
                    $failed++;
                }

                // Throttle 1.5s per message
                usleep(1_500_000);
            }
        }

        return [
            'sent'    => $sent,
            'failed'  => $failed,
            'skipped' => $skipped,
        ];
    }

    /**
     * Personalize template with subscription details.
     */
    protected function formatReminderMessage(string $template, BusinessSubscription $sub, $owner, Business $business): string
    {
        $endsAt = $sub->ends_at ? $sub->ends_at->format('d/m/Y') : '-';
        $payUrl = route('billing.index');

        return str_replace(
            ['{owner}', '{bisnis}', '{paket}', '{tanggal_habis}', '{link_bayar}'],
            [
                $owner?->name ?? 'Pemilik Usaha',
                $business->name,
                $this->formatPlanName($sub->plan_code),
                $endsAt,
                $payUrl,
            ],
            $template
        );
    }

    /**
     * Resolve target recipients for Admin Blast to Business Owners.
     */
    public function resolveBlastRecipients(string $filter): Collection
    {
        $query = Business::query()->where('is_active', true)->with(['users', 'subscription']);

        return match ($filter) {
            'active_subscribers' => $query->whereHas('subscription', function ($q) {
                $q->where('status', BusinessSubscription::STATUS_ACTIVE)
                    ->where('ends_at', '>', now());
            })->get(),

            'expiring_soon' => $query->whereHas('subscription', function ($q) {
                $q->where('status', BusinessSubscription::STATUS_ACTIVE)
                    ->whereBetween('ends_at', [now(), now()->addDays(7)]);
            })->get(),

            'free_tier' => $query->where(function ($q) {
                $q->whereDoesntHave('subscription')
                    ->orWhereHas('subscription', function ($sq) {
                        $sq->where('status', '!=', BusinessSubscription::STATUS_ACTIVE)
                            ->orWhere('ends_at', '<=', now());
                    });
            })->get(),

            default => $query->get(), // 'all_owners'
        };
    }

    /**
     * Execute Admin WhatsApp Blast to business owners.
     */
    public function sendAdminBlast(WhatsAppAdminBlast $blast): void
    {
        if (!$this->isBlastActive()) {
            $blast->update([
                'status' => 'failed',
                'total_recipients' => 0,
                'total_sent' => 0,
                'total_failed' => 0,
            ]);
            Log::warning("[AdminWA] Blast dispatch aborted: Blast channel is currently disabled.");
            return;
        }

        $blast->update(['status' => 'processing']);

        $businesses = $this->resolveBlastRecipients($blast->target_filter);
        $blast->update(['total_recipients' => $businesses->count()]);

        $sent   = 0;
        $failed = 0;
        $driver = $this->getBlastDriver();
        $creds  = $this->getMetaCredentials();

        foreach ($businesses as $biz) {
            $owner = $biz->users()->wherePivot('role', 'owner')->first() ?? $biz->users()->first();
            $phone = $owner?->phone ?: $biz->phone;

            if (! $phone) {
                continue;
            }

            $sub = $biz->subscription;

            $message = str_replace(
                ['{owner}', '{bisnis}', '{paket}', '{tanggal_habis}'],
                [
                    $owner?->name ?? 'Pemilik Usaha',
                    $biz->name,
                    $this->formatPlanName($sub?->plan_code ?? 'free'),
                    $sub?->ends_at ? $sub->ends_at->format('d/m/Y') : 'Gratis / Belum Aktif',
                ],
                $blast->message
            );

            if ($driver === 'meta_cloud') {
                $result = $this->metaDriver->sendTextMessage(
                    $phone,
                    $message,
                    $creds['token'] ?: null,
                    $creds['phone_number_id'] ?: null
                );
            } else {
                $options = [];
                if ($blast->media_url) {
                    $options['url'] = $blast->media_url;
                }
                $result = $this->sendMessage($phone, $message, $options);
            }

            $ok = $result['success'] ?? false;

            WhatsAppAdminBlastRecipient::create([
                'blast_id'      => $blast->id,
                'business_id'   => $biz->id,
                'owner_id'      => $owner?->id,
                'owner_name'    => $owner?->name ?? 'Pemilik Usaha',
                'business_name' => $biz->name,
                'phone_number'  => $phone,
                'status'        => $ok ? 'sent' : 'failed',
                'sent_at'       => $ok ? now() : null,
                'error_message' => $result['error'] ?? null,
            ]);

            $ok ? $sent++ : $failed++;

            // Safe humanized pacing delay if using Baileys to reduce ban risk (skipped in testing)
            if (!app()->environment('testing')) {
                if ($driver === 'baileys') {
                    if ($sent > 0 && $sent % 10 === 0) {
                        sleep(10); // Cool-down every 10 messages
                    } else {
                        usleep(rand(3_000_000, 6_000_000)); // 3-6s humanized delay
                    }
                } else {
                    usleep(300_000); // 300ms for official Meta Cloud API
                }
            }
        }

        $blast->update([
            'total_sent'   => $sent,
            'total_failed' => $failed,
            'status'       => 'completed',
        ]);
    }

    public function formatPlanName(?string $code): string
    {
        return match ($code) {
            'core_monthly' => 'Cooca (Bulanan)',
            'core_annual'  => 'Cooca (Tahunan)',
            'free'         => 'Cooca Gratis',
            null           => 'Paket Standar',
            default        => strtoupper(str_replace('_', ' ', $code)),
        };
    }
}
