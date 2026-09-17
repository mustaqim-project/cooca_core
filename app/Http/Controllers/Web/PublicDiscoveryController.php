<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicDiscoveryController extends Controller
{
    /**
     * Display public discovery directory of active UMKM businesses and storefronts.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('kategori', ''));
        $capability = trim((string) $request->query('fitur', ''));
        $location = trim((string) $request->query('lokasi', ''));

        $query = Business::where('is_active', true)
            ->whereHas('storeSetting', function ($q): void {
                $q->where('is_storefront_enabled', true)
                  ->where('is_discoverable', true);
            })
            ->with([
                'storeSetting',
                'landingPage',
                'locations' => fn ($q) => $q->where(function ($sub): void {
                    $sub->where('is_primary', true)
                        ->orWhere('is_active', true);
                })->orderByDesc('is_primary'),
            ])
            ->withCount([
                'products' => fn ($q) => $q->where('is_active', true),
            ]);

        // 1. Fulltext / partial search by name, description, address, or product names
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('landingPage', function ($lq) use ($search): void {
                      $lq->where('headline', 'like', "%{$search}%")
                         ->orWhere('subheadline', 'like', "%{$search}%");
                  })
                  ->orWhereHas('products', function ($pq) use ($search): void {
                      $pq->where('name', 'like', "%{$search}%")
                         ->where('is_active', true);
                  });
            });
        }

        // 2. Industry category / template filter
        if ($category !== '' && $category !== 'all') {
            $query->where(function ($q) use ($category): void {
                $q->where('industry_category', $category)
                  ->orWhere('template_code', 'like', "{$category}%");
            });
        }

        // 3. Location filter
        if ($location !== '') {
            $query->where(function ($q) use ($location): void {
                $q->where('address', 'like', "%{$location}%")
                  ->orWhereHas('locations', fn ($lq) => $lq->where('name', 'like', "%{$location}%"));
            });
        }

        // 4. Feature / Capability filter
        if ($capability !== '') {
            $query->whereHas('storeSetting', function ($sq) use ($capability): void {
                match ($capability) {
                    'pickup' => $sq->where('allow_pickup', true),
                    'delivery' => $sq->where('allow_delivery', true),
                    'scheduled' => $sq->where('allow_scheduled_order', true),
                    'request' => $sq->where('allow_request_order', true),
                    'po' => $sq->where('allow_customer_po', true),
                    'reservation' => $sq->where('allow_reservation', true),
                    default => null,
                };
            });
        }

        $businesses = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        // Statistical counts for category filter pills
        $totalStores = Business::where('is_active', true)
            ->whereHas('storeSetting', fn ($q) => $q->where('is_storefront_enabled', true)->where('is_discoverable', true))
            ->count();

        $categories = [
            'fnb' => [
                'label' => 'Kuliner & Kafe',
                'icon' => 'utensils',
                'count' => Business::where('is_active', true)
                    ->whereHas('storeSetting', fn ($q) => $q->where('is_storefront_enabled', true)->where('is_discoverable', true))
                    ->where(fn ($q) => $q->where('industry_category', 'fnb')->orWhere('template_code', 'like', 'fnb%'))
                    ->count(),
            ],
            'retail' => [
                'label' => 'Ritel & Butik',
                'icon' => 'shopping-bag',
                'count' => Business::where('is_active', true)
                    ->whereHas('storeSetting', fn ($q) => $q->where('is_storefront_enabled', true)->where('is_discoverable', true))
                    ->where(fn ($q) => $q->where('industry_category', 'retail')->orWhere('template_code', 'like', 'retail%'))
                    ->count(),
            ],
            'service' => [
                'label' => 'Jasa & Perawatan',
                'icon' => 'sparkles',
                'count' => Business::where('is_active', true)
                    ->whereHas('storeSetting', fn ($q) => $q->where('is_storefront_enabled', true)->where('is_discoverable', true))
                    ->where(fn ($q) => $q->where('industry_category', 'service')->orWhere('template_code', 'like', 'service%'))
                    ->count(),
            ],
            'manufacture' => [
                'label' => 'Katering & Produsen',
                'icon' => 'factory',
                'count' => Business::where('is_active', true)
                    ->whereHas('storeSetting', fn ($q) => $q->where('is_storefront_enabled', true)->where('is_discoverable', true))
                    ->where(fn ($q) => $q->where('industry_category', 'manufacture')->orWhere('template_code', 'like', 'mfg%'))
                    ->count(),
            ],
        ];

        return view('public.discovery.index', compact(
            'businesses',
            'search',
            'category',
            'capability',
            'location',
            'totalStores',
            'categories'
        ));
    }
}
