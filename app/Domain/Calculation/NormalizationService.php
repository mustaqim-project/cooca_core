<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Models\CostModel;
use InvalidArgumentException;

final class NormalizationService
{
    /**
     * Validate and normalize a cost model before executing calculation.
     *
     * @throws InvalidArgumentException
     */
    public function validateAndNormalize(CostModel $costModel): void
    {
        $costModel->loadMissing([
            'bomHeader.items.material.latestPrice',
            'bomHeader.items.unit',
            'labors.laborRate',
            'machines.machine',
            'activities.activity.costPool',
        ]);

        // 1. Validate BOM Items
        if ($costModel->bomHeader !== null) {
            foreach ($costModel->bomHeader->items as $item) {
                if (! $item->isSubAssembly()) {
                    $material = $item->material;
                    if ($material === null) {
                        throw new InvalidArgumentException("BOM item {$item->id} is missing its material reference.");
                    }

                    if ($material->isDiscontinued()) {
                        throw new InvalidArgumentException("Material '{$material->name}' in BOM is discontinued and cannot be calculated.");
                    }

                    if ($material->latestPrice === null) {
                        throw new InvalidArgumentException("Material '{$material->name}' has no active purchase price recorded.");
                    }
                }
            }
        }

        // 2. Validate Labor Items
        foreach ($costModel->labors as $labor) {
            if ($labor->laborRate === null) {
                throw new InvalidArgumentException("Labor assignment {$labor->id} has an invalid or missing labor rate.");
            }
        }

        // 3. Validate Machine Items
        foreach ($costModel->machines as $machineItem) {
            $machine = $machineItem->machine;
            if ($machine === null) {
                throw new InvalidArgumentException("Machine assignment {$machineItem->id} has an invalid machine reference.");
            }

            if ($machine->useful_life_hours <= 0) {
                throw new InvalidArgumentException("Machine '{$machine->name}' has zero useful life hours.");
            }
        }
    }
}
