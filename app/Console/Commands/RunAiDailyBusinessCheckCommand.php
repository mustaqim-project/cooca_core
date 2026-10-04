<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Ai\Orchestration\AiOrchestrator;
use App\Models\Business;
use Illuminate\Console\Command;

final class RunAiDailyBusinessCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:ai-daily-business-check {--business= : UUID Bisnis tertentu (opsional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan evaluasi kesehatan bisnis harian oleh AI CEO & AI COO untuk seluruh tenant aktif';

    public function handle(AiOrchestrator $orchestrator): int
    {
        $bizUuid = $this->option('business');

        $query = Business::where('is_active', true);
        if ($bizUuid) {
            $query->where('id', $bizUuid);
        }

        $businesses = $query->get();
        $this->info("Menjalankan evaluasi AI Digital Company untuk {$businesses->count()} bisnis...");

        $successCount = 0;
        foreach ($businesses as $biz) {
            $this->line("Evaluating: {$biz->name} ({$biz->id})...");
            try {
                $result = $orchestrator->runDailyBusinessCheck($biz);
                if ($result['success'] ?? false) {
                    $this->info(" -> Berhasil. Task ID: {$result['task_id']}");
                    $successCount++;
                } else {
                    $this->warn(" -> Gagal: " . ($result['message'] ?? 'Unknown error'));
                }
            } catch (\Throwable $e) {
                $this->error(" -> Exception: {$e->getMessage()}");
            }
        }

        $this->info("Evaluasi AI selesai. {$successCount}/{$businesses->count()} berhasil.");
        return self::SUCCESS;
    }
}
