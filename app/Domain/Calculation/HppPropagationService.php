<?php

declare(strict_types=1);

namespace App\Domain\Calculation;

use App\Models\BomItem;
use App\Models\CostModel;
use App\Models\CostingRun;
use App\Models\Product;
use Throwable;

/**
 * Propagar perubahan harga material ke HPP produk terkait (via BOM) dan
 * memperbarui product.base_cost — biaya transaksi HISTORIS tidak pernah disentuh.
 *
 * Dipicu dari:
 * - MaterialWebController::storePrice (harga material diubah)
 * - GoodsReceiptWebController::store (harga beli aktual bertambah)
 * - DataImportService::executeMaterialImport (harga material di-update via import)
 */
final class HppPropagationService
{
    public function __construct(
        private readonly CalculationEngine $engine = new CalculationEngine,
        private readonly CostingResultService $costingService = new CostingResultService
    ) {}

    /**
     * Hitung ulang HPP semua product yang CostModel aktif memakai material ini.
     *
     * @return int jumlah product yang berhasil diperbarui base_cost-nya.
     */
    public function refreshForMaterial(string $materialId): int
    {
        $costModels = BomItem::where('material_id', $materialId)
            ->with(['header.costModel', 'header.costModel.product'])
            ->get()
            ->map(fn (BomItem $item) => $item->header?->costModel)
            ->filter()
            ->unique('id');

        $updated = 0;

        foreach ($costModels as $costModel) {
            if (! $costModel->is_active) {
                continue;
            }
            if ($this->refreshForCostModel($costModel)) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Hitung ulang HPP untuk satu CostModel & sinkronkan base_cost product.
     */
    public function refreshForCostModel(CostModel $costModel): bool
    {
        $product = $costModel->product;
        if ($product === null) {
            return false;
        }

        try {
            $costModel->load([
                'bomHeaders.items.material.latestPrice.purchaseUnit',
                'bomHeaders.items.material.latestPrice.currency',
                'bomHeaders.items.unit',
                'bomHeaders.items.subBomHeader',
                'labors.laborRate',
                'machines.machine',
                'product.outputUnit',
            ]);

            $dto = $this->engine->calculate($costModel);

            if ($dto->hppPerUnit > 0) {
                $this->costingService->persist($costModel, $dto, CostingRun::RUN_TYPE_MANUAL);
                $product->update(['base_cost' => round($dto->hppPerUnit, 2)]);
            }

            return true;
        } catch (Throwable) {
            // Cost model yang tidak bisa dihitung tidak menghalangi propagasi lainnya.
            return false;
        }
    }
}