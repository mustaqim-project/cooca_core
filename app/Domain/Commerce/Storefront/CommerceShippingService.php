<?php

declare(strict_types=1);

namespace App\Domain\Commerce\Storefront;

use App\Domain\Shipping\BiteshipService;
use App\Models\Business;
use App\Models\CommerceShippingRule;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

final class CommerceShippingService
{
    private BiteshipService $biteshipService;

    public function __construct(?BiteshipService $biteshipService = null)
    {
        $this->biteshipService = $biteshipService ?? new BiteshipService();
    }

    /**
     * Retrieve all active legacy shipping rules configured by the merchant.
     *
     * @return Collection<int, CommerceShippingRule>
     */
    public function getAvailableRules(string $businessId): Collection
    {
        return CommerceShippingRule::where('business_id', $businessId)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Calculate shipping fee using Biteship API, with fallback handling.
     *
     * @param array<int, array{name: string, value: float|int, weight?: int, quantity: int}> $items
     * @param array{latitude?: ?float, longitude?: ?float}|null $destinationCoordinates
     * @return array{
     *     applied_rule_id: ?string,
     *     applied_rule_name: string,
     *     shipping_fee: float,
     *     is_free: bool,
     *     courier_code?: ?string,
     *     courier_service_code?: ?string,
     *     courier_name?: ?string,
     *     service_name?: ?string,
     *     duration?: ?string,
     *     options: array<int, array{
     *         id: string,
     *         name: string,
     *         rule_type: string,
     *         fee: float,
     *         is_free: bool,
     *         description: string,
     *         courier_code?: string,
     *         courier_name?: string,
     *         courier_service_code?: string,
     *         courier_service_name?: string,
     *         duration?: string
     *     }>
     * }
     */
    public function calculateShipping(
        Business $business,
        float $subtotal,
        ?float $distanceKm = null,
        ?string $preferredRuleId = null,
        ?string $destinationPostalCode = null,
        ?array $destinationCoordinates = null,
        array $items = [],
        ?string $destinationAddress = null
    ): array {
        $storeSetting = $business->commerceStoreSetting;
        $serviceFee = (float) (\App\Models\SystemSetting::get('biteship_service_fee') ?? config('services.biteship.service_fee', \App\Domain\Shipping\BiteshipService::SERVICE_FEE));

        // Pre-calculate total weight and order items dimensions
        $orderItems = [];
        $totalWeightGrams = 0;
        if (! empty($items)) {
            foreach ($items as $item) {
                $pId = $item['product_id'] ?? $item['id'] ?? null;
                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $val = (float) ($item['unit_price'] ?? $item['price'] ?? $item['value'] ?? 0);
                $name = (string) ($item['name'] ?? $item['product_name'] ?? 'Paket Belanja');
                $weight = isset($item['weight']) ? (int) $item['weight'] : 0;
                $length = isset($item['length']) && $item['length'] !== '' ? (int) $item['length'] : null;
                $width = isset($item['width']) && $item['width'] !== '' ? (int) $item['width'] : null;
                $height = isset($item['height']) && $item['height'] !== '' ? (int) $item['height'] : null;

                if ($pId) {
                    $product = \App\Models\Product::find($pId);
                    if ($product) {
                        $name = $product->name;
                        $val = $val > 0 ? $val : (float) $product->selling_price;
                        $weight = $weight > 0 ? $weight : (int) round((float) ($product->weight ?: 200));
                        $length = $length ?: ($product->length ? (int) $product->length : null);
                        $width = $width ?: ($product->width ? (int) $product->width : null);
                        $height = $height ?: ($product->height ? (int) $product->height : null);
                    }
                }

                if ($weight <= 0) {
                    $weight = 200;
                }

                $totalWeightGrams += ($weight * $qty);

                $mappedItem = [
                    'name'     => $name,
                    'value'    => $val > 0 ? $val : $subtotal,
                    'weight'   => $weight,
                    'quantity' => $qty,
                ];
                if ($length !== null) {
                    $mappedItem['length'] = $length;
                }
                if ($width !== null) {
                    $mappedItem['width'] = $width;
                }
                if ($height !== null) {
                    $mappedItem['height'] = $height;
                }

                $orderItems[] = $mappedItem;
            }
        }

        if (empty($orderItems)) {
            $orderItems = [
                [
                    'name'     => 'Paket Belanja',
                    'value'    => $subtotal,
                    'weight'   => 250,
                    'quantity' => 1,
                ],
            ];
            $totalWeightGrams = 250;
        }

        // Try Biteship Rates calculation first if destination is given
        if (! empty($destinationPostalCode) || ! empty($destinationCoordinates['latitude'])) {
            try {
                /** @var \App\Models\Location|null $originLocation */
                $originLocation = null;
                if (! empty($destinationCoordinates['latitude']) && ! empty($destinationCoordinates['longitude'])) {
                    $custLat = (float) $destinationCoordinates['latitude'];
                    $custLng = (float) $destinationCoordinates['longitude'];
                    $branches = \App\Models\Location::where('business_id', $business->id)
                        ->where('is_active', true)
                        ->where('is_online_fulfillment', true)
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->get();

                    if ($branches->isNotEmpty()) {
                        $stockedBranches = $branches->filter(function (\App\Models\Location $b) use ($items) {
                            return $this->locationHasStock($b->id, $items);
                        });

                        $candidates = $stockedBranches->isNotEmpty() ? $stockedBranches : $branches;

                        $originLocation = $candidates->sortBy(function (\App\Models\Location $b) use ($custLat, $custLng) {
                            return $b->distanceTo($custLat, $custLng) ?? PHP_INT_MAX;
                        })->first();
                    }
                }

                if (! $originLocation) {
                    $onlineBranches = \App\Models\Location::where('business_id', $business->id)
                        ->where('is_active', true)
                        ->where('is_online_fulfillment', true)
                        ->orderBy('is_primary', 'desc')
                        ->get();

                    if ($onlineBranches->isNotEmpty()) {
                        $stockedBranch = $onlineBranches->first(function (\App\Models\Location $b) use ($items) {
                            return $this->locationHasStock($b->id, $items);
                        });

                        $originLocation = $stockedBranch ?? $onlineBranches->first();
                    } else {
                        $originLocation = \App\Models\Location::where('business_id', $business->id)->where('is_primary', true)->first();
                    }
                }

                $origin = [
                    'postal_code' => $originLocation?->postal_code ?? $storeSetting?->origin_postal_code,
                    'latitude'    => $originLocation?->latitude ?? $storeSetting?->origin_latitude,
                    'longitude'   => $originLocation?->longitude ?? $storeSetting?->origin_longitude,
                    'area_id'     => $originLocation?->biteship_area_id ?? $storeSetting?->origin_area_id,
                    'address'     => $originLocation?->address ?? $storeSetting?->origin_address,
                ];

                $destination = [
                    'postal_code' => $destinationPostalCode,
                    'latitude'    => $destinationCoordinates['latitude'] ?? null,
                    'longitude'   => $destinationCoordinates['longitude'] ?? null,
                    'address'     => $destinationAddress,
                ];

                $couriers = $storeSetting?->biteship_enabled_couriers ?? ['jne', 'sicepat', 'jnt', 'anteraja', 'gosend', 'grab'];

                $rateRes = $this->biteshipService->getRates($origin, $destination, $orderItems, $couriers);

                if (($rateRes['success'] ?? false) && ! empty($rateRes['pricing'])) {
                    $options = [];
                    $selectedOption = null;
                    $biteshipFee = (float) ($rateRes['service_fee'] ?? $serviceFee);

                    foreach ($rateRes['pricing'] as $pricing) {
                        $fee = (float) ($pricing['price'] ?? 0);
                        $isFree = ($fee <= 0.0);

                        $opt = [
                            'id'                   => $pricing['id'],
                            'name'                 => $pricing['description'],
                            'rule_type'            => 'biteship',
                            'fee'                  => $fee,
                            'service_fee'          => $biteshipFee,
                            'total_fee'            => $fee + $biteshipFee,
                            'is_free'              => $isFree,
                            'description'          => $pricing['description'],
                            'courier_code'         => $pricing['courier_code'],
                            'courier_name'         => $pricing['courier_name'],
                            'courier_service_code' => $pricing['courier_service_code'],
                            'courier_service_name' => $pricing['courier_service_name'],
                            'duration'             => $pricing['duration'],
                        ];

                        $options[] = $opt;

                        if ($preferredRuleId !== null && $pricing['id'] === $preferredRuleId) {
                            $selectedOption = $opt;
                        }
                    }

                    if ($selectedOption === null && ! empty($options)) {
                        $selectedOption = $options[0];
                    }

                    if ($selectedOption !== null) {
                        return [
                            'applied_rule_id'      => $selectedOption['id'],
                            'applied_rule_name'    => $selectedOption['name'],
                            'shipping_fee'         => (float) $selectedOption['fee'],
                            'service_fee'          => (float) ($selectedOption['service_fee'] ?? $biteshipFee),
                            'total_shipping_fee'   => (float) $selectedOption['fee'] + (float) ($selectedOption['service_fee'] ?? $biteshipFee),
                            'total_weight_grams'   => $totalWeightGrams,
                            'is_free'              => $selectedOption['is_free'],
                            'courier_code'         => $selectedOption['courier_code'] ?? null,
                            'courier_service_code' => $selectedOption['courier_service_code'] ?? null,
                            'courier_name'         => $selectedOption['courier_name'] ?? null,
                            'service_name'         => $selectedOption['courier_service_name'] ?? null,
                            'duration'             => $selectedOption['duration'] ?? null,
                            'origin_location_id'   => $originLocation?->id,
                            'origin_location_name' => $originLocation?->name,
                            'options'              => $options,
                        ];
                    }
                }
            } catch (Throwable) {
                // Fallback to legacy or standard options below
            }
        }

        // Check legacy rules for backward compatibility (e.g. Unit tests)
        $rules = $this->getAvailableRules($business->id);

        if (! $rules->isEmpty()) {
            $options = [];
            /** @var CommerceShippingRule|null $selectedRule */
            $selectedRule = null;
            $lowestFee = PHP_FLOAT_MAX;

            foreach ($rules as $rule) {
                $fee = $rule->calculateRate($subtotal, $distanceKm);

                if ($fee === null) {
                    continue;
                }

                $isFree = ($fee <= 0.0);
                $desc = match ($rule->rule_type) {
                    CommerceShippingRule::TYPE_FREE_THRESHOLD => "Gratis ongkir untuk belanja min. Rp " . number_format((float) $rule->min_order_for_free, 0, ',', '.'),
                    CommerceShippingRule::TYPE_DISTANCE_TIER => ($rule->min_distance_km ?? 0) . ' - ' . ($rule->max_distance_km ?? '∞') . ' KM',
                    default => $rule->name,
                };

                $options[] = [
                    'id'          => $rule->id,
                    'name'        => $rule->name,
                    'rule_type'   => $rule->rule_type,
                    'fee'         => $fee,
                    'service_fee' => $serviceFee,
                    'total_fee'   => $fee + $serviceFee,
                    'is_free'     => $isFree,
                    'description' => $desc,
                ];

                if ($preferredRuleId !== null && $rule->id === $preferredRuleId) {
                    $selectedRule = $rule;
                    $lowestFee = $fee;
                } elseif ($preferredRuleId === null) {
                    if ($isFree) {
                        $selectedRule = $rule;
                        $lowestFee = 0.0;
                    } elseif ($fee < $lowestFee) {
                        $selectedRule = $rule;
                        $lowestFee = $fee;
                    }
                }
            }

            if ($selectedRule === null && ! empty($options)) {
                $firstOption = $options[0];
                $selectedRule = $rules->firstWhere('id', $firstOption['id']);
                $lowestFee = (float) $firstOption['fee'];
            }

            $finalFee = max(0.0, $lowestFee === PHP_FLOAT_MAX ? 0.0 : $lowestFee);

            return [
                'applied_rule_id'    => $selectedRule?->id,
                'applied_rule_name'  => $selectedRule?->name ?? 'Kurir Logistik',
                'shipping_fee'       => $finalFee,
                'service_fee'        => $serviceFee,
                'total_shipping_fee' => $finalFee + $serviceFee,
                'total_weight_grams' => $totalWeightGrams,
                'is_free'            => $finalFee <= 0.0,
                'options'            => $options,
            ];
        }

        // If rules are empty and no destination is specified, return empty options
        return [
            'applied_rule_id'    => null,
            'applied_rule_name'  => 'Kurir Logistik',
            'shipping_fee'       => 0.0,
            'service_fee'        => $serviceFee,
            'total_shipping_fee' => $serviceFee,
            'total_weight_grams' => $totalWeightGrams,
            'is_free'            => true,
            'options'            => [],
        ];
    }

    /**
     * Check whether a specific location has sufficient available inventory for the given items.
     *
     * @param string $locationId
     * @param array<int, array{product_id?: string, quantity?: float|int}> $items
     * @return bool
     */
    public function locationHasStock(string $locationId, array $items): bool
    {
        if (empty($items)) {
            return true;
        }

        foreach ($items as $item) {
            $productId = (string) ($item['product_id'] ?? '');
            $qty = (float) ($item['quantity'] ?? 0);
            if (! $productId || $qty <= 0) {
                continue;
            }

            $product = \App\Models\Product::find($productId);
            if (! $product || ! $product->isGoods()) {
                continue;
            }

            $stock = \App\Models\InventoryStock::where('location_id', $locationId)
                ->where('product_id', $productId)
                ->first();

            $available = $stock ? (float) ($stock->quantity - ($stock->reserved_quantity ?? 0)) : 0.0;
            if ($available < $qty) {
                return false;
            }
        }

        return true;
    }
}
