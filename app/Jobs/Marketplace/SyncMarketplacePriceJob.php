<?php

declare(strict_types=1);

namespace App\Jobs\Marketplace;

use App\Domain\Marketplace\MarketplaceSyncService;
use App\Models\MarketplaceProductMapping;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncMarketplacePriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public MarketplaceProductMapping $mapping) {}

    public function handle(MarketplaceSyncService $syncService): void
    {
        $syncService->syncProductPrice($this->mapping);
    }
}
