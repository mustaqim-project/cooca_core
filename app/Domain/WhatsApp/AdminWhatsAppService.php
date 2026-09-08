<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SystemSetting;
use App\Models\WhatsAppAdminBlast;
use App\Models\WhatsAppAdminBlastRecipient;
use App\Models\WhatsAppSubscriptionReminder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminWhatsAppService
{
    public const ADMIN_SESSION_ID = 'admin_platform';

    public function __construct(
        protected WhatsAppGatewayService $gateway
    ) {}

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
     * Start / trigger WhatsApp session for admin.
     */
    public function startSession(): array
    {
        $sessionId = $this->getSessionId();
        $baseUrl   = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        try {
            $response = $this->client(30)->post("{$baseUrl}/api/sessions/start", [
                'sessionId'  => $sessionId,
                'webhookUrl' => url('/api/wa/admin-webhook'),
            ]);

            return $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::error("[AdminWA] startSession error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get QR code data URL and connection status for admin session.
     */
    public function getQrCode(): array
    {
        $sessionId = $this->getSessionId();
        $baseUrl   = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        try {
            $response = $this->client(15)->get("{$baseUrl}/api/sessions/{$sessionId}/qr");
            return $response->json() ?? [];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'disconnected', 'qrDataUrl' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get live status of admin WhatsApp session.
     */
    public function getStatus(): array
    {
        $sessionId = $this->getSessionId();
        $baseUrl   = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        try {
            $response = $this->client(15)->get("{$baseUrl}/api/sessions/{$sessionId}/status");
            return $response->json() ?? ['status' => 'disconnected'];
        } catch (\Throwable) {
            return ['status' => 'disconnected'];
        }
    }

    /**
     * Disconnect admin WhatsApp session.
     */
    public function disconnect(): void
    {
        $sessionId = $this->getSessionId();
        $baseUrl   = rtrim(config('services.wa_server.url', 'http://127.0.0.1:3000'), '/');

        try {
            $this->client(15)->delete("{$baseUrl}/api/sessions/{$sessionId}");
        } catch (\Throwable $e) {
            Log::warning("[AdminWA] disconnect error: " . $e->getMessage());
        }
    }

    /**
     * Send message using the official Admin WhatsApp session.
     */
    public function sendMessage(string $phone, string $message, array $options = []): array
    {
        return $this->gateway->sendRawMessage($this->getSessionId(), $phone, $message, $options);
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
        $blast->update(['status' => 'processing']);

        $businesses = $this->resolveBlastRecipients($blast->target_filter);
        $blast->update(['total_recipients' => $businesses->count()]);

        $sent   = 0;
        $failed = 0;

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
                $options['url'] = $blast->media_url;
            }

            $result = $this->sendMessage($phone, $message, $options);
            $ok     = $result['success'] ?? false;

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

            // Throttle 1.5s per message
            usleep(1_500_000);
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
            'core_monthly' => 'Cooca Core (Bulanan)',
            'core_annual'  => 'Cooca Core (Tahunan)',
            'free'         => 'Cooca Gratis',
            null           => 'Paket Standar',
            default        => strtoupper(str_replace('_', ' ', $code)),
        };
    }
}
