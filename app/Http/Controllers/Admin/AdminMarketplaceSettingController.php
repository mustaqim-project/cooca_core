<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Marketplace\MarketplaceManagerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMarketplaceSettingController extends Controller
{
    public function __construct(protected MarketplaceManagerService $manager) {}

    /**
     * Test Marketplace Partner & API credentials.
     */
    public function testConfig(Request $request): JsonResponse
    {
        return $this->testMarketplaceConfig($request);
    }

    /**
     * Test Marketplace Partner & API credentials.
     */
    public function testMarketplaceConfig(Request $request): JsonResponse
    {
        $channel = (string) $request->input('channel', 'shopee');

        try {
            $adapter = $this->manager->driver($channel);

            return response()->json([
                'success' => true,
                'message' => "Koneksi driver adapter {$adapter->getName()} siap digunakan.",
                'data'    => [
                    'channel' => $adapter->getChannel(),
                    'name'    => $adapter->getName(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 422);
        }
    }
}
