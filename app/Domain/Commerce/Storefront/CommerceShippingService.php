<?php

declare(strict_types=1);

namespace App\Domain\Commerce\Storefront;

use App\Models\Business;
use App\Models\CommerceShippingRule;
use Illuminate\Database\Eloquent\Collection;

final class CommerceShippingService
{
    /**
     * Retrieve all active shipping rules configured by the merchant.
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
     * Calculate shipping fee based on subtotal, distance, and optional rule selection.
     *
     * @return array{
     *     applied_rule_id: ?string,
     *     applied_rule_name: string,
     *     shipping_fee: float,
     *     is_free: bool,
     *     options: array<int, array{
     *         id: string,
     *         name: string,
     *         rule_type: string,
     *         fee: float,
     *         is_free: bool,
     *         description: string
     *     }>
     * }
     */
    public function calculateShipping(
        Business $business,
        float $subtotal,
        ?float $distanceKm = null,
        ?string $preferredRuleId = null
    ): array {
        $rules = $this->getAvailableRules($business->id);

        if ($rules->isEmpty()) {
            return [
                'applied_rule_id' => null,
                'applied_rule_name' => 'Kurir Toko',
                'shipping_fee' => 0.0,
                'is_free' => true,
                'options' => [],
            ];
        }

        $options = [];
        /** @var CommerceShippingRule|null $selectedRule */
        $selectedRule = null;
        $lowestFee = PHP_FLOAT_MAX;

        foreach ($rules as $rule) {
            $fee = $rule->calculateRate($subtotal, $distanceKm);

            // If rule doesn't match distance, skip from available options
            if ($fee === null) {
                continue;
            }

            $isFree = ($fee <= 0.0);
            $desc = match ($rule->rule_type) {
                CommerceShippingRule::TYPE_FREE_THRESHOLD => "Gratis ongkir untuk belanja min. Rp " . number_format((float) $rule->min_order_for_free, 0, ',', '.'),
                CommerceShippingRule::TYPE_DISTANCE_TIER => ($rule->min_distance_km ?? 0) . ' - ' . ($rule->max_distance_km ?? '∞') . ' KM',
                default => 'Tarif flat kurir toko',
            };

            $options[] = [
                'id' => $rule->id,
                'name' => $rule->name,
                'rule_type' => $rule->rule_type,
                'fee' => $fee,
                'is_free' => $isFree,
                'description' => $desc,
            ];

            if ($preferredRuleId !== null && $rule->id === $preferredRuleId) {
                $selectedRule = $rule;
                $lowestFee = $fee;
            } elseif ($preferredRuleId === null) {
                // Pick free threshold first, or lowest fee
                if ($isFree) {
                    $selectedRule = $rule;
                    $lowestFee = 0.0;
                } elseif ($fee < $lowestFee) {
                    $selectedRule = $rule;
                    $lowestFee = $fee;
                }
            }
        }

        // If preferred rule was not found or no rule matched, fallback to first available option
        if ($selectedRule === null && ! empty($options)) {
            $firstOption = $options[0];
            $selectedRule = $rules->firstWhere('id', $firstOption['id']);
            $lowestFee = (float) $firstOption['fee'];
        }

        $finalFee = max(0.0, $lowestFee === PHP_FLOAT_MAX ? 0.0 : $lowestFee);

        return [
            'applied_rule_id' => $selectedRule?->id,
            'applied_rule_name' => $selectedRule?->name ?? 'Kurir Toko',
            'shipping_fee' => $finalFee,
            'is_free' => $finalFee <= 0.0,
            'options' => $options,
        ];
    }
}
