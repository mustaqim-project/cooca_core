<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Billing\EntitlementService;
use App\Models\BusinessSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetMonthlyAiTokensCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:reset-ai-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset monthly AI token allowance (10M tokens) for active Core plan subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pengecekan reset siklus bulanan AI token...');

        $activeSubscriptions = BusinessSubscription::whereIn('plan_code', [
            BusinessSubscription::PLAN_CORE_MONTHLY,
            BusinessSubscription::PLAN_CORE_ANNUAL,
        ])
            ->where('status', BusinessSubscription::STATUS_ACTIVE)
            ->get();

        $resetCount = 0;
        $today = Carbon::today();

        foreach ($activeSubscriptions as $sub) {
            $lastReset = $sub->last_token_reset_at ? Carbon::parse($sub->last_token_reset_at) : null;

            // Reset if never reset before or if at least 1 month has passed since last reset
            if (!$lastReset || $lastReset->diffInDays($today) >= 30) {
                $sub->update([
                    'ai_tokens_remaining' => EntitlementService::CORE_AI_MONTHLY_TOKENS,
                    'last_token_reset_at' => $today->toDateString(),
                ]);

                $resetCount++;
                Log::info("AI Token reset ke 10M untuk Bisnis ID: {$sub->business_id}");
            }
        }

        $this->info("Selesai! Sebanyak {$resetCount} langganan Core telah di-reset ke 10.000.000 token AI.");
        return self::SUCCESS;
    }
}
