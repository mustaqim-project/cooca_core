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

        // 1. Check and Process Subscriptions: Active -> Past Due (Day 1-3 Grace Period) -> Expired (Day 4+)
        $pastDueCutoff = $now->copy()->subDays(3);

        // A. Active subs where ends_at has passed but within 3 days -> Move to PAST_DUE
        $toPastDueSubs = BusinessSubscription::withoutGlobalScopes()
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now->toDateTimeString())
            ->where('ends_at', '>=', $pastDueCutoff->toDateTimeString())
            ->get();

        $pastDueCount = 0;
        foreach ($toPastDueSubs as $sub) {
            $sub->update([
                'status' => BusinessSubscription::STATUS_PAST_DUE,
            ]);
            $pastDueCount++;
            Log::info("Langganan Bisnis ID: {$sub->business_id} memasuki masa tenggang toleransi (past_due) selama 3 hari.");
        }

        // B. Active or Past Due subs where ends_at has passed the 3-day grace period -> Move to EXPIRED
        $expiredSubs = BusinessSubscription::withoutGlobalScopes()
            ->whereIn('status', [BusinessSubscription::STATUS_ACTIVE, BusinessSubscription::STATUS_PAST_DUE])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $pastDueCutoff->toDateTimeString())
            ->get();

        $expiredCount = 0;
        foreach ($expiredSubs as $sub) {
            $sub->update([
                'status' => BusinessSubscription::STATUS_EXPIRED,
            ]);
            $expiredCount++;
            Log::info("Langganan Bisnis ID: {$sub->business_id} telah melewati masa tenggang toleransi 3 hari pada {$sub->ends_at} dan dinonaktifkan (EXPIRED).");
        }
        $this->info("Berhasil memproses siklus: {$pastDueCount} masuk masa tenggang (past_due), {$expiredCount} kedaluwarsa (expired).");

        // 2. Check and Send Expiry Reminders (H-7, H-3, H-1)
        $activeSubs = BusinessSubscription::withoutGlobalScopes()
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
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
