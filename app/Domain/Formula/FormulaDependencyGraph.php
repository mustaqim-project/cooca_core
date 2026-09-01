<?php

declare(strict_types=1);

namespace App\Domain\Formula;

use InvalidArgumentException;

final class FormulaDependencyGraph
{
    /**
     * Check if a dependency graph contains circular cycles.
     *
     * @param  array<string, array<int, string>>  $graph  Key is variable, values are variables it references
     *
     * @throws InvalidArgumentException
     */
    public function detectCircular(array $graph): void
    {
        $visited = [];
        $recursionStack = [];

        foreach (array_keys($graph) as $node) {
            if ($this->hasCycleDfs($node, $graph, $visited, $recursionStack)) {
                throw new InvalidArgumentException("Circular formula dependency detected involving '{$node}'.");
            }
        }
    }

    /**
     * @param  array<string, array<int, string>>  $graph
     * @param  array<string, bool>  $visited
     * @param  array<string, bool>  $recursionStack
     */
    private function hasCycleDfs(string $node, array $graph, array &$visited, array &$recursionStack): bool
    {
        if (isset($recursionStack[$node]) && $recursionStack[$node]) {
            return true;
        }

        if (isset($visited[$node]) && $visited[$node]) {
            return false;
        }

        $visited[$node] = true;
        $recursionStack[$node] = true;

        if (isset($graph[$node])) {
            foreach ($graph[$node] as $neighbor) {
                if ($this->hasCycleDfs($neighbor, $graph, $visited, $recursionStack)) {
                    return true;
                }
            }
        }

        $recursionStack[$node] = false;

        return false;
    }
}
