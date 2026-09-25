<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Marketplace;

use App\Domain\Marketplace\MarketplaceManagerService;
use App\Http\Controllers\Controller;
use App\Jobs\Marketplace\ProcessMarketplaceWebhookJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MarketplaceWebhookController extends Controller
{
    public function __construct(protected MarketplaceManagerService $manager) {}

    /**
     * Handle incoming webhook requests from marketplaces.
     */
    public function handle(string $provider, Request $request): JsonResponse
    {
        $normalizedChannel = match (strtolower($provider)) {
            'shopee'                           => 'shopee',
            'tiktok', 'tiktok-tokopedia'      => 'tiktok_shop',
            'tokopedia'                        => 'tokopedia',
            default                            => abort(404),
        };

        try {
            $adapter   = $this->manager->driver($normalizedChannel);
            $rawBody   = (string) $request->getContent();
            $headers   = $request->headers->all();

            $eventData = $adapter->handleWebhook($request, $rawBody, $headers);

            if (! ($eventData['is_valid'] ?? false)) {
                Log::warning("Invalid marketplace webhook signature from {$provider}", ['headers' => $headers]);
                return response()->json(['success' => false, 'error' => 'Invalid signature'], 401);
            }

            // Dispatch background job to process webhook asynchronously
            ProcessMarketplaceWebhookJob::dispatch($normalizedChannel, $eventData);

            return response()->json(['success' => true, 'message' => 'Webhook received']);
        } catch (\Throwable $e) {
            Log::error("Error handling marketplace webhook [{$provider}]: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
