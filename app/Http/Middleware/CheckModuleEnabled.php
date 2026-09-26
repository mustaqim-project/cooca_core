<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Template\ModuleRegistry;
use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckModuleEnabled
{
    /**
     * Map friendly module alias names to canonical ModuleRegistry constant slugs.
     *
     * @var array<string, string>
     */
    private const ALIAS_MAP = [
        'pos_dinein'           => ModuleRegistry::MODULE_POS_DINEIN,
        'dinein'               => ModuleRegistry::MODULE_POS_DINEIN,
        'kds'                  => ModuleRegistry::MODULE_POS_DINEIN,
        'pos_retail'           => ModuleRegistry::MODULE_POS_RETAIL,
        'pos'                  => ModuleRegistry::MODULE_POS_RETAIL,
        'b2b_sales'            => ModuleRegistry::MODULE_B2B_SALES,
        'sales'                => ModuleRegistry::MODULE_B2B_SALES,
        'recipe_bom'           => ModuleRegistry::MODULE_RECIPE_BOM,
        'bom'                  => ModuleRegistry::MODULE_RECIPE_BOM,
        'materials'            => ModuleRegistry::MODULE_RECIPE_BOM,
        'labor_machines'       => ModuleRegistry::MODULE_LABOR_MACHINES,
        'inventory_warehouse'  => ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
        'warehouse'            => ModuleRegistry::MODULE_INVENTORY_WAREHOUSE,
        'procurement'          => ModuleRegistry::MODULE_PROCUREMENT,
        'purchasing'           => ModuleRegistry::MODULE_PROCUREMENT,
        'crm_loyalty'          => ModuleRegistry::MODULE_CRM_LOYALTY,
        'crm'                  => ModuleRegistry::MODULE_CRM_LOYALTY,
        'channels_marketing'   => ModuleRegistry::MODULE_CHANNELS_MARKETING,
        'marketing'            => ModuleRegistry::MODULE_CHANNELS_MARKETING,
        'whatsapp'             => ModuleRegistry::MODULE_CHANNELS_MARKETING,
        'storefront_checkout'  => ModuleRegistry::MODULE_STOREFRONT_CHECKOUT,
        'storefront'           => ModuleRegistry::MODULE_STOREFRONT_CHECKOUT,
        'order_request'        => ModuleRegistry::MODULE_ORDER_REQUEST,
        'scheduled_order'      => ModuleRegistry::MODULE_SCHEDULED_ORDER,
        'customer_po'          => ModuleRegistry::MODULE_CUSTOMER_PO,
        'reservation'          => ModuleRegistry::MODULE_RESERVATION,
        'merchant_shipping'    => ModuleRegistry::MODULE_MERCHANT_SHIPPING,
        'shipping'             => ModuleRegistry::MODULE_MERCHANT_SHIPPING,
        'accounting_corporate' => ModuleRegistry::MODULE_ACCOUNTING_CORPORATE,
        'accounting'           => ModuleRegistry::MODULE_ACCOUNTING_CORPORATE,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $business = Context::business();

        if ($business && ! empty($modules)) {
            $hasAnyModuleEnabled = false;

            foreach ($modules as $module) {
                $canonicalSlug = self::ALIAS_MAP[$module] ?? $module;

                if ($business->isModuleEnabled($canonicalSlug)) {
                    $hasAnyModuleEnabled = true;
                    break;
                }
            }

            if (! $hasAnyModuleEnabled) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success'          => false,
                        'message'          => 'Modul fitur ini sedang dinonaktifkan untuk bisnis Anda.',
                        'error_code'       => 'MODULE_DISABLED',
                        'required_modules' => $modules,
                    ], Response::HTTP_FORBIDDEN);
                }

                $definitions = ModuleRegistry::definitions();
                $moduleNames = array_map(function (string $mod) use ($definitions): string {
                    $canonical = self::ALIAS_MAP[$mod] ?? $mod;
                    return $definitions[$canonical]['name'] ?? ucfirst(str_replace('_', ' ', $mod));
                }, $modules);

                return redirect()->route('dashboard')
                    ->with('error', 'Modul "' . implode(', ', $moduleNames) . '" sedang dinonaktifkan di pengaturan bisnis Anda.');
            }
        }

        return $next($request);
    }
}
