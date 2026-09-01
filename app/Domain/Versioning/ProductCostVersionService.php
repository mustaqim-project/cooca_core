<?php

declare(strict_types=1);

namespace App\Domain\Versioning;

use App\Domain\Calculation\DTO\CostingResultDTO;
use App\Models\CostModel;
use App\Models\Product;
use App\Models\ProductCostVersion;
use App\Support\Context;

final class ProductCostVersionService
{
    /**
     * Create an immutable snapshot version of a product's costing.
     */
    public function createVersion(
        Product $product,
        CostModel $costModel,
        CostingResultDTO $dto,
        ?string $label = null,
        ?string $effectiveFrom = null
    ): ProductCostVersion {
        $user = Context::user();

        $latestVersion = ProductCostVersion::where('product_id', $product->id)
            ->max('version_number');
        $nextVersionNumber = ($latestVersion ?? 0) + 1;

        $versionLabel = $label ?? "v{$nextVersionNumber}.0.0";

        /** @var ProductCostVersion $version */
        $version = ProductCostVersion::create([
            'business_id' => $product->business_id,
            'product_id' => $product->id,
            'cost_model_id' => $costModel->id,
            'version_number' => $nextVersionNumber,
            'version_label' => $versionLabel,
            'status' => ProductCostVersion::STATUS_DRAFT,
            'total_hpp' => $dto->totalHpp,
            'hpp_per_unit' => $dto->hppPerUnit,
            'hpp_snapshot' => $dto->toArray(),
            'formula_definition_snapshot' => $costModel->formula_definition,
            'status_history' => [
                [
                    'status' => ProductCostVersion::STATUS_DRAFT,
                    'changed_by' => $user?->id,
                    'changed_at' => now()->toIso8601String(),
                    'notes' => 'Version initial creation',
                ],
            ],
            'effective_from' => $effectiveFrom ?? now(),
            'created_by' => $user?->id,
        ]);

        return $version;
    }
}
