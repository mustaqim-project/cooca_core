<?php

declare(strict_types=1);

namespace App\Domain\Formula;

use InvalidArgumentException;

final class FormulaEvaluator
{
    /**
     * Evaluate an AST against a key-value dictionary of variables.
     *
     * @param  array<string, mixed>  $node
     * @param  array<string, float|int>  $variables
     *
     * @throws InvalidArgumentException
     */
    public function evaluate(array $node, array $variables): float
    {
        $type = $node['type'] ?? null;

        if ($type === 'NUMBER') {
            return (float) $node['value'];
        }

        if ($type === 'VARIABLE') {
            $name = (string) $node['name'];
            if (! array_key_exists($name, $variables)) {
                throw new InvalidArgumentException("Undefined variable '{$name}' in formula evaluation.");
            }

            return (float) $variables[$name];
        }

        if ($type === 'BINARY_OP') {
            $op = $node['op'];
            $left = $this->evaluate((array) $node['left'], $variables);
            $right = $this->evaluate((array) $node['right'], $variables);

            return match ($op) {
                '+' => $left + $right,
                '-' => $left - $right,
                '*' => $left * $right,
                '/' => (function () use ($left, $right): float {
                    if ($right == 0.0) {
                        throw new InvalidArgumentException('Division by zero in formula calculation.');
                    }

                    return $left / $right;
                })(),
                default => throw new InvalidArgumentException("Unknown operator '{$op}' in AST node."),
            };
        }

        throw new InvalidArgumentException('Unrecognized AST node structure: '.json_encode($node));
    }
}
