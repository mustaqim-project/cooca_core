<?php

declare(strict_types=1);

namespace App\Domain\Product;

use App\Models\BomHeader;
use App\Models\BomItem;
use InvalidArgumentException;

final class BomCircularValidator
{
    /**
     * Validate that adding candidate sub-BOM into parent BOM will not create a circular reference.
     *
     * @throws InvalidArgumentException
     */
    public function validate(BomHeader $parentHeader, BomHeader $candidateSubBom): void
    {
        if ($parentHeader->id === $candidateSubBom->id) {
            throw new InvalidArgumentException('A BOM/Recipe cannot include itself as a sub-assembly component.');
        }

        // Check if parentHeader is reachable from candidateSubBom
        $visited = [];
        $queue = [$candidateSubBom->id];

        while (! empty($queue)) {
            $currentId = array_shift($queue);

            if ($currentId === $parentHeader->id) {
                throw new InvalidArgumentException(
                    "Circular BOM reference detected! BOM '{$parentHeader->id}' is already a descendant of sub-BOM '{$candidateSubBom->id}'."
                );
            }

            if (isset($visited[$currentId])) {
                continue;
            }
            $visited[$currentId] = true;

            $childSubBomIds = BomItem::where('bom_header_id', $currentId)
                ->whereNotNull('sub_bom_header_id')
                ->pluck('sub_bom_header_id')
                ->all();

            foreach ($childSubBomIds as $childId) {
                if (! isset($visited[$childId])) {
                    $queue[] = $childId;
                }
            }
        }
    }
}
