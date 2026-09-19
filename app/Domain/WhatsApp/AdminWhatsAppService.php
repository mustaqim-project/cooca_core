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
        protected ?WhatsAppGatewayService $gateway = null,
        protected ?MetaWhatsAppCloudDriver $metaDriver = null
    ) {
        $this->metaDriver = $metaDriver ?? app(MetaWhatsAppCloudDriver::class);
    }

    /**
     * Return fixed session ID for Platform Administrator.
     */
    public function getSessionId(): string
    {
        return self::ADMIN_SESSION_ID;
    }

    /**
     * Dapatkan ringkasan status bot WhatsApp resmi Platform Meta (Parent Gateway).
     */
    public function getStatus(?string $sessionId = null): array
    {
        $creds = $this->getMetaCredentials();
        $token = $creds['token'];
        $phoneId = $creds['phone_number_id'];

        if (empty($token) || empty($phoneId)) {
            return [
                'status'               => 'disconnected',
                'phone'                => null,
                'verified_name'        => null,
                'quality_rating'       => null,
                'messaging_limit_tier' => null,
            ];
        }

        try {
            $verified = $this->metaDriver->verifyCredentials($token, $phoneId);
            if ($verified['success'] ?? false) {
                $data = $verified['data'] ?? [];
                return [
                    'status'               => 'connected',
                    'phone'                => $data['display_phone_number'] ?? $verified['display_phone_number'] ?? null,
                    'verified_name'        => $data['verified_name'] ?? $verified['verified_name'] ?? 'COOCA Official Platform',
                    'quality_rating'       => (!empty($data['quality_rating']) && $data['quality_rating'] !== 'UNKNOWN') ? $data['quality_rating'] : 'GREEN',
                    'messaging_limit_tier' => $data['messaging_limit_tier'] ?? $verified['messaging_limit_tier'] ?? 'TIER_1K',
                    'waba_id'              => $creds['waba_id'],
                    'phone_number_id'      => $phoneId,
                ];
            }

            return [
                'status' => 'disconnected',
                'phone'  => null,
                'error'  => $verified['error'] ?? 'Gagal memvalidasi kredensial Meta.',
            ];
        } catch (\Throwable $e) {
            Log::warning('[AdminWA] getStatus error: ' . $e->getMessage());
            return [
                'status' => 'disconnected',
                'phone'  => null,
                'error'  => $e->getMessage(),
            ];
        }
    }

    /**
     * Dapatkan ringkasan akun WhatsApp seluruh Merchant/Toko terhubung (Oversight).
     */
    public function getMerchantAccountsSummary(): array
    {
        $accounts = \App\Models\WhatsAppAccount::with(['business.owner'])->latest()->get();

        return [
            'total'           => $accounts->count(),
            'connected'       => $accounts->where('status', 'connected')->count(),
            'live_count'      => $accounts->where('status', 'connected')->count(),
            'sandbox_count'   => $accounts->where('status', '!=', 'connected')->count(),
            'accounts'        => $accounts,
            'green_quality'   => $accounts->where('quality_rating', 'GREEN')->count(),
            'recent_accounts' => $accounts->take(8),
        ];
    }

    /**
     * Retrieve all configured WhatsApp Admin sessions (stub for backward compatibility).
     *
     * @return \Illuminate\Support\Collection<int, WhatsAppAdminSession>
     */
    public function getSessions(): \Illuminate\Support\Collection
    {
        return collect([]);
    }

    /**
     * Compatibility stub for QR code.
     */
    public function getQrCode(?string $sessionId = null): array
    {
        return [
            'success'   => false,
            'status'    => 'disconnected',
            'qrDataUrl' => null,
            'message'   => 'Layanan Scan QR Baileys telah dinonaktifkan. Gunakan Meta WhatsApp Cloud API resmi.',
        ];
    }

    /**
     * Compatibility stub for startSession.
     */
    public function startSession(?string $sessionId = null): array
    {
        return ['success' => false, 'error' => 'Sistem menggunakan Meta Cloud API resmi, tidak memerlukan scan QR.'];
    }

    /**
     * Disconnect specific admin WhatsApp session (stub).
     */
    public function disconnectSession(string $sessionId): void
    {
        // No-op in Meta Cloud API mode
    }

    /**
     * Delete an admin WhatsApp session entirely (stub).
     */
    public function deleteSession(string $sessionId): void
    {
        // No-op
    }

    /**
     * Toggle session active status in random pool (stub).
     */
    public function toggleSessionActive(string $sessionId): bool
    {
        return false;
    }

    /**
     * Disconnect admin WhatsApp session (stub).
     */
    public function disconnect(?string $sessionId = null): void
    {
        // No-op
    }

    /**
     * Get active OTP gateway driver (Always Meta Cloud API).
     */
    public function getOtpDriver(): string
    {
        return 'meta_cloud';
    }

    /**
     * Get active Blast gateway driver (Always Meta Cloud API).
     */
    public function getBlastDriver(): string
    {
        return 'meta_cloud';
    }

    /**
     * Check whether OTP sending is currently active.
     */
    public function isOtpActive(): bool
    {
        return SystemSetting::get('wa_otp_active', '1') === '1';
    }

    /**
     * Check whether Blast broadcast sending is currently active.
     */
    public function isBlastActive(): bool
    {
        return SystemSetting::get('wa_blast_active', '1') === '1';
    }

    /**
     * Get Meta WhatsApp Cloud API credentials for Platform Parent.
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
     * Get Meta WhatsApp Cloud API Platform App Settings (Official Tech Provider Integration).
     */
    public function getPlatformAppSettings(): array
    {
        return [
            'app_id'               => (string) SystemSetting::get('meta_wa_app_id', config('services.meta_whatsapp.app_id', '')),
            'app_secret'           => (string) SystemSetting::get('meta_wa_app_secret', config('services.meta_whatsapp.app_secret', '')),
            'webhook_verify_token' => (string) SystemSetting::get('meta_wa_webhook_verify_token', config('services.meta_whatsapp.webhook_verify_token', 'cooca_meta_wa_webhook_secret')),
            'config_id'            => (string) SystemSetting::get('meta_wa_config_id', config('services.meta_whatsapp.config_id', '')),
            'graph_version'        => (string) SystemSetting::get('meta_wa_graph_version', config('services.meta_whatsapp.version', 'v21.0')),
            'graph_url'            => (string) SystemSetting::get('meta_wa_graph_url', config('services.meta_whatsapp.graph_url', 'https://graph.facebook.com')),
            'webhook_url'          => url('/api/v1/wa/meta/webhook'),
        ];
    }

    /**
     * Save platform Meta WhatsApp gateway settings to system settings table.
     */
    public function saveGatewaySettings(array $settings): void
    {
        SystemSetting::set('wa_otp_driver', 'meta_cloud', 'whatsapp');
        SystemSetting::set('wa_blast_driver', 'meta_cloud', 'whatsapp');

        if (isset($settings['otp_active'])) {
            SystemSetting::set('wa_otp_active', $settings['otp_active'] ? '1' : '0', 'whatsapp');
        }
        if (isset($settings['blast_active'])) {
            SystemSetting::set('wa_blast_active', $settings['blast_active'] ? '1' : '0', 'whatsapp');
        }

        // Platform App Settings (Tech Provider)
        if (isset($settings['meta_app_id'])) {
            SystemSetting::set('meta_wa_app_id', (string) $settings['meta_app_id'], 'whatsapp');
        }
        if (isset($settings['meta_app_secret']) && $settings['meta_app_secret'] !== '') {
            SystemSetting::set('meta_wa_app_secret', (string) $settings['meta_app_secret'], 'whatsapp', true);
        }
        if (isset($settings['meta_webhook_verify_token'])) {
            SystemSetting::set('meta_wa_webhook_verify_token', (string) $settings['meta_webhook_verify_token'], 'whatsapp');
        }
        if (isset($settings['meta_config_id'])) {
            SystemSetting::set('meta_wa_config_id', (string) $settings['meta_config_id'], 'whatsapp');
        }
        if (isset($settings['meta_graph_version'])) {
            SystemSetting::set('meta_wa_graph_version', (string) $settings['meta_graph_version'], 'whatsapp');
        }
        if (isset($settings['meta_graph_url'])) {
            SystemSetting::set('meta_wa_graph_url', (string) $settings['meta_graph_url'], 'whatsapp');
        }

        // Parent Bot Gateway Credentials
        if (isset($settings['meta_token']) && $settings['meta_token'] !== '') {
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
     * Send Authentication OTP via official Meta Cloud API Platform.
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

        $client = \App\Domain\WhatsApp\CloudApi\WhatsAppClient::forPlatform();
        $creds  = $this->getMetaCredentials();
        $template = $creds['otp_template'] ?: 'cooca_otp';

        if ($client) {
            return $client->sendOtpTemplate($phone, $otpCode, $template);
        }

        if (!empty($creds['token']) && !empty($creds['phone_number_id'])) {
            return $this->metaDriver->sendOtp(
                $phone,
                $otpCode,
                $template,
                $creds['token'],
                $creds['phone_number_id']
            );
        }

        return [
            'success' => false,
            'error'   => 'Kredensial Meta WhatsApp Cloud API Platform belum dikonfigurasi.',
        ];
    }

    /**
     * Send message using the official Admin Platform WhatsApp session (Meta Cloud API).
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        $client = \App\Domain\WhatsApp\CloudApi\WhatsAppClient::forPlatform();

        if ($client) {
            if (!empty($options['media_url']) || !empty($options['url'])) {
                $url  = $options['media_url'] ?? $options['url'];
                $type = $options['type'] ?? 'image';
                return $client->sendMediaMessage($phone, $type, $url, $message);
            }

            return $client->sendTextMessage($phone, $message);
        }

        $creds = $this->getMetaCredentials();
        if (!empty($creds['token']) && !empty($creds['phone_number_id'])) {
            return $this->metaDriver->sendTextMessage($phone, $message, $creds['token'], $creds['phone_number_id']);
        }

        return [
            'success' => false,
            'error'   => 'Kredensial Meta WhatsApp Cloud API Platform belum lengkap.',
        ];
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
            $options = [];
            if ($blast->media_url) {
                $options['media_url'] = $blast->media_url;
                $options['type']      = 'image';
            }
            $result = $this->sendMessage($phone, $message, $options);

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
