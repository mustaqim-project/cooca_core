<?php

declare(strict_types=1);

namespace App\Domain\Material;

use App\Models\MaterialUnitConversion;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Support\Context;
use InvalidArgumentException;
use SplQueue;

final class UnitConversionService
{
    /**
     * Convert quantity from one unit to another using Graph Pathfinding (BFS).
     *
     * @throws InvalidArgumentException
     */
    public function convert(float $qty, Unit|string $from, Unit|string $to, ?string $materialId = null, ?string $supplierId = null): float
    {
        $fromUnit = $this->resolveUnit($from);
        $toUnit = $this->resolveUnit($to);

        // Identical unit
        if ($fromUnit->id === $toUnit->id) {
            return $qty;
        }

        $materialSpecificFactor = $this->resolveMaterialSpecificFactor($fromUnit, $toUnit, $materialId, $supplierId);
        if ($materialSpecificFactor !== null) {
            return $qty * $materialSpecificFactor;
        }

        // Fetch all available conversions (system default + current business)
        $conversions = UnitConversion::available()->get();

        // Build adjacency graph: graph[fromId][] = ['to' => toId, 'factor' => factor]
        $graph = [];

        foreach ($conversions as $conv) {
            $fromId = $conv->from_unit_id;
            $toId = $conv->to_unit_id;
            $factor = (float) $conv->factor;

            if ($factor <= 0) {
                continue;
            }

            // Forward edge
            $graph[$fromId][] = ['to' => $toId, 'factor' => $factor];
            // Reverse edge (inverse factor)
            $graph[$toId][] = ['to' => $fromId, 'factor' => 1.0 / $factor];
        }

        // BFS Search to find shortest conversion path
        /** @var SplQueue<array{unit_id: string, cumulative_factor: float}> $queue */
        $queue = new SplQueue;
        $queue->enqueue([
            'unit_id' => $fromUnit->id,
            'cumulative_factor' => 1.0,
        ]);

        $visited = [$fromUnit->id => true];

        while (! $queue->isEmpty()) {
            $current = $queue->dequeue();
            $currentId = $current['unit_id'];
            $currentFactor = $current['cumulative_factor'];

            if ($currentId === $toUnit->id) {
                return $qty * $currentFactor;
            }

            if (isset($graph[$currentId])) {
                foreach ($graph[$currentId] as $edge) {
                    $nextId = $edge['to'];
                    $edgeFactor = $edge['factor'];

                    if (! isset($visited[$nextId])) {
                        $visited[$nextId] = true;
                        $queue->enqueue([
                            'unit_id' => $nextId,
                            'cumulative_factor' => $currentFactor * $edgeFactor,
                        ]);
                    }
                }
            }
        }

        throw new InvalidArgumentException(
            "Cannot convert from [{$fromUnit->code}] to [{$toUnit->code}]. Incompatible categories or missing conversion factor."
        );
    }

    /**
     * Resolve unit model by instance, id or code.
     */
    private function resolveUnit(Unit|string $unit): Unit
    {
        if ($unit instanceof Unit) {
            return $unit;
        }

        /** @var Unit|null $resolved */
        $resolved = Unit::available()
            ->where(function ($query) use ($unit): void {
                $query->where('id', $unit)
                    ->orWhere('code', $unit);
            })
            ->first();

        if ($resolved === null) {
            throw new InvalidArgumentException("Unit [{$unit}] was not found.");
        }

        return $resolved;
    }

    /**
     * Resolve a material/supplier-specific conversion prior to falling back to the global graph.
     */
    private function resolveMaterialSpecificFactor(Unit $fromUnit, Unit $toUnit, ?string $materialId = null, ?string $supplierId = null): ?float
    {
        if ($materialId === null) {
            return null;
        }

        $businessId = Context::hasBusiness() ? Context::business()?->id : null;

        $query = MaterialUnitConversion::query()
            ->where('material_id', $materialId)
            ->where('from_unit_id', $fromUnit->id)
            ->where('to_unit_id', $toUnit->id);

        if ($businessId !== null) {
            $query->where('business_id', $businessId);
        }

        if ($supplierId !== null) {
            $query->where(function ($subQuery) use ($supplierId): void {
                $subQuery->where('supplier_id', $supplierId)
                    ->orWhereNull('supplier_id');
            });
        }

        $row = $query
            ->orderByRaw('supplier_id IS NULL')
            ->orderByDesc('is_default')
            ->orderByDesc('effective_from')
            ->first();

        if ($row === null) {
            return null;
        }

        return (float) $row->factor;
    }
}
