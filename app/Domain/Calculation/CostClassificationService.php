<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Models\CostComponent;
use App\Models\CostModel;

final class CostClassificationService
{
    /**
     * Classify active components of a cost model.
     *
     * @return array{
     *     direct_cost_components: array<int, string>,
     *     indirect_cost_components: array<int, string>,
     *     fixed_cost_components: array<int, string>,
     *     variable_cost_components: array<int, string>
     * }
     */
    public function classify(CostModel $costModel): array
    {
        $costModel->loadMissing('components');

        $direct = [];
        $indirect = [];
        $fixed = [];
        $variable = [];

        foreach ($costModel->components as $component) {
            if ($component->traceability === CostComponent::TRACEABILITY_DIRECT) {
                $direct[] = $component->name;
            } else {
                $indirect[] = $component->name;
            }

            if ($component->behavior === CostComponent::BEHAVIOR_FIXED) {
                $fixed[] = $component->name;
            } else {
                $variable[] = $component->name;
            }
        }

        return [
            'direct_cost_components' => $direct,
            'indirect_cost_components' => $indirect,
            'fixed_cost_components' => $fixed,
            'variable_cost_components' => $variable,
        ];
    }
}
