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

        // Try Biteship Rates calculation first if destination is given
        if (! empty($destinationPostalCode) || ! empty($destinationCoordinates['latitude'])) {
            try {
                $origin = [
                    'postal_code' => $storeSetting?->origin_postal_code,
                    'latitude'    => $storeSetting?->origin_latitude,
                    'longitude'   => $storeSetting?->origin_longitude,
                    'area_id'     => $storeSetting?->origin_area_id,
                    'address'     => $storeSetting?->origin_address,
                ];

                $destination = [
                    'postal_code' => $destinationPostalCode,
                    'latitude'    => $destinationCoordinates['latitude'] ?? null,
                    'longitude'   => $destinationCoordinates['longitude'] ?? null,
                    'address'     => $destinationAddress,
                ];

                $orderItems = ! empty($items) ? $items : [
                    [
                        'name'     => 'Paket Belanja',
                        'value'    => $subtotal,
                        'weight'   => 250,
                        'quantity' => 1,
                    ],
                ];

                $couriers = $storeSetting?->biteship_enabled_couriers ?? ['jne', 'sicepat', 'jnt', 'anteraja', 'gosend', 'grab'];

                $rateRes = $this->biteshipService->getRates($origin, $destination, $orderItems, $couriers);

                if (($rateRes['success'] ?? false) && ! empty($rateRes['pricing'])) {
                    $options = [];
                    $selectedOption = null;
                    $serviceFee = (float) ($rateRes['service_fee'] ?? $this->biteshipService->getServiceFee());

                    foreach ($rateRes['pricing'] as $pricing) {
                        $fee = (float) ($pricing['price'] ?? 0);
                        $isFree = ($fee <= 0.0);

                        $opt = [
                            'id'                   => $pricing['id'],
                            'name'                 => $pricing['description'],
                            'rule_type'            => 'biteship',
                            'fee'                  => $fee,
                            'service_fee'          => $serviceFee,
                            'total_fee'            => $fee + $serviceFee,
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
                            'service_fee'          => (float) ($selectedOption['service_fee'] ?? $serviceFee),
                            'total_shipping_fee'   => (float) $selectedOption['fee'] + (float) ($selectedOption['service_fee'] ?? $serviceFee),
                            'is_free'              => $selectedOption['is_free'],
                            'courier_code'         => $selectedOption['courier_code'] ?? null,
                            'courier_service_code' => $selectedOption['courier_service_code'] ?? null,
                            'courier_name'         => $selectedOption['courier_name'] ?? null,
                            'service_name'         => $selectedOption['courier_service_name'] ?? null,
                            'duration'             => $selectedOption['duration'] ?? null,
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
                'applied_rule_id'   => $selectedRule?->id,
                'applied_rule_name' => $selectedRule?->name ?? 'Kurir Logistik',
                'shipping_fee'      => $finalFee,
                'is_free'           => $finalFee <= 0.0,
                'options'           => $options,
            ];
        }

        // If rules are empty and no destination is specified, return empty options
        return [
            'applied_rule_id'   => null,
            'applied_rule_name' => 'Kurir Logistik',
            'shipping_fee'      => 0.0,
            'is_free'           => true,
            'options'           => [],
        ];
    }
}
