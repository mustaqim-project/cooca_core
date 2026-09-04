<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Mail\DynamicMailConfig;
use App\Mail\PlanExpiryReminderMail;
use App\Models\BusinessSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessSubscriptionLifecycleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:process-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses siklus langganan SaaS: kedaluwarsa plan, pengingat H-7/H-3/H-1, dan pembersihan kuota';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pemrosesan siklus langganan dan pengingat kadaluarsa...');
        DynamicMailConfig::bootstrap();

        $today = Carbon::today();
        $now = Carbon::now();

        // 1. Check and Expire Overdue Subscriptions
        $expiredSubs = BusinessSubscription::where('status', BusinessSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now->toDateTimeString())
            ->get();

        $expiredCount = 0;
        foreach ($expiredSubs as $sub) {
            $sub->update([
                'status' => BusinessSubscription::STATUS_EXPIRED,
            ]);
            $expiredCount++;
            Log::info("Langganan Bisnis ID: {$sub->business_id} telah kedaluwarsa pada {$sub->ends_at} dan dinonaktifkan.");
        }
        $this->info("Berhasil menonaktifkan {$expiredCount} langganan yang telah melewati masa aktif.");

        // 2. Check and Send Expiry Reminders (H-7, H-3, H-1)
        $activeSubs = BusinessSubscription::where('status', BusinessSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', $now->toDateTimeString())
            ->with(['business.users'])
            ->get();

        $reminderCount = 0;
        foreach ($activeSubs as $sub) {
            if (!$sub->ends_at) continue;

            $daysRemaining = (int) $today->diffInDays($sub->ends_at->copy()->startOfDay(), false);

            if (in_array($daysRemaining, [7, 3, 1], true)) {
                $cacheKey = "sub_reminder_{$sub->id}_{$daysRemaining}_" . $today->toDateString();

                if (!Cache::has($cacheKey)) {
                    $owner = $sub->business?->users()->wherePivot('role', 'owner')->first()
                        ?? $sub->business?->users()->first();
                    $ownerEmail = $owner?->email ?? $sub->business?->email;

                    if ($ownerEmail) {
                        try {
                            Mail::to($ownerEmail)->send(new PlanExpiryReminderMail($sub, $daysRemaining));
                            Cache::put($cacheKey, true, now()->addDays(2));
                            $reminderCount++;
                            Log::info("Pengingat H-{$daysRemaining} terkirim ke {$ownerEmail} untuk Bisnis: {$sub->business?->name}");
                        } catch (\Throwable $e) {
                            Log::error("Gagal mengirim pengingat H-{$daysRemaining} ke {$ownerEmail}: " . $e->getMessage());
                        }
                    }
                }
            }
        }
        $this->info("Berhasil mengirim {$reminderCount} email pengingat masa langganan (H-7, H-3, H-1).");

        return Command::SUCCESS;
    }
}
