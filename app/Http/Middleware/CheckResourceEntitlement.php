<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Billing\EntitlementService;
use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckResourceEntitlement
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $resourceType  'product' | 'recipe' | 'invoice' | 'ai'
     */
    public function handle(Request $request, Closure $next, string $resourceType): Response
    {
        $business = Context::business();
        if (!$business) {
            return $next($request);
        }

        $canProceed = match ($resourceType) {
            'product' => $this->entitlementService->canCreateProduct($business),
            'recipe' => $this->entitlementService->canCreateRecipe($business),
            'invoice' => $this->entitlementService->canCreateInvoiceThisMonth($business),
            'ai' => $this->entitlementService->canAccessAi($business),
            default => true,
        };

        if (!$canProceed) {
            $labels = [
                'product' => 'Katalog Produk (Maks. 50 pada paket Free)',
                'recipe' => 'Resep HPP / BOM (Maks. 20 pada paket Free)',
                'invoice' => 'Faktur Penjualan (Maks. 10 per bulan pada paket Free)',
                'ai' => 'Fitur Asisten & Prediksi AI (Khusus Paket Core)',
            ];
            $label = $labels[$resourceType] ?? $resourceType;

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'RESOURCE_LIMIT_EXCEEDED',
                    'message' => "Batas kuota {$label} telah tercapai. Tingkatkan ke Cooca Core (Rp129.000/bln) untuk akses tanpa batas.",
                    'upgrade_url' => route('billing.limits'),
                ], 403);
            }

            return redirect()->route('billing.limits')
                ->with('error', "Batas kuota {$label} telah tercapai. Data lama Anda tetap aman (No Data Punishment). Silakan tingkatkan paket Anda untuk menambah data baru.");
        }

        return $next($request);
    }
}
