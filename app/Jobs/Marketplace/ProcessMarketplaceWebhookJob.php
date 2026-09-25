<?php

declare(strict_types=1);

namespace App\Jobs\Marketplace;

use App\Domain\Marketplace\MarketplaceOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMarketplaceWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        public string $channel,
        public array $eventData
    ) {}

    public function handle(MarketplaceOrderService $orderService): void
    {
        $orderService->processWebhook($this->channel, $this->eventData);
    }
}
