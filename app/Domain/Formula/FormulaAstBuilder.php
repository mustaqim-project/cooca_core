<?php

declare(strict_types=1);

namespace App\Domain\Formula;

use InvalidArgumentException;

final class FormulaAstBuilder
{
    /** @var array<int, array{type: string, value: string|float}> */
    private array $tokens = [];

    private int $position = 0;

    /** @var array<string, bool> */
    private array $variablesFound = [];

    /**
     * Build an AST from token stream.
     *
     * @param  array<int, array{type: string, value: string|float}>  $tokens
     * @return array{ast: array<string, mixed>, variables: array<int, string>}
     *
     * @throws InvalidArgumentException
     */
    public function build(array $tokens): array
    {
        $this->tokens = $tokens;
        $this->position = 0;
        $this->variablesFound = [];

        if (empty($this->tokens)) {
            throw new InvalidArgumentException('Formula expression cannot be empty.');
        }

        $ast = $this->parseExpression();

        if ($this->position < count($this->tokens)) {
            $remaining = $this->tokens[$this->position]['value'];
            throw new InvalidArgumentException("Unexpected token '{$remaining}' at position {$this->position}.");
        }

        return [
            'ast' => $ast,
            'variables' => array_keys($this->variablesFound),
        ];
    }

    /**
     * Expression: Term (('+' | '-') Term)*
     *
     * @return array<string, mixed>
     */
    private function parseExpression(): array
    {
        $node = $this->parseTerm();

        while ($this->matchOperator(['+', '-'])) {
            $op = (string) $this->previous()['value'];
            $right = $this->parseTerm();
            $node = [
                'type' => 'BINARY_OP',
                'op' => $op,
                'left' => $node,
                'right' => $right,
            ];
        }

        return $node;
    }

    /**
     * Term: Factor (('*' | '/') Factor)*
     *
     * @return array<string, mixed>
     */
    private function parseTerm(): array
    {
        $node = $this->parseFactor();

        while ($this->matchOperator(['*', '/'])) {
            $op = (string) $this->previous()['value'];
            $right = $this->parseFactor();
            $node = [
                'type' => 'BINARY_OP',
                'op' => $op,
                'left' => $node,
                'right' => $right,
            ];
        }

        return $node;
    }

    /**
     * Factor: NUMBER | IDENTIFIER | '(' Expression ')'
     *
     * @return array<string, mixed>
     */
    private function parseFactor(): array
    {
        if ($this->position >= count($this->tokens)) {
            throw new InvalidArgumentException('Unexpected end of formula expression.');
        }

        $token = $this->tokens[$this->position];

        // Number
        if ($token['type'] === FormulaTokenizer::TYPE_NUMBER) {
            $this->position++;

            return [
                'type' => 'NUMBER',
                'value' => (float) $token['value'],
            ];
        }

        // Variable Identifier
        if ($token['type'] === FormulaTokenizer::TYPE_IDENTIFIER) {
            $this->position++;
            $varName = (string) $token['value'];
            $this->variablesFound[$varName] = true;

            return [
                'type' => 'VARIABLE',
                'name' => $varName,
            ];
        }

        // Parentheses
        if ($token['type'] === FormulaTokenizer::TYPE_LPAREN) {
            $this->position++;
            $node = $this->parseExpression();

            if ($this->position >= count($this->tokens) || $this->tokens[$this->position]['type'] !== FormulaTokenizer::TYPE_RPAREN) {
                throw new InvalidArgumentException("Missing closing parenthesis ')' in formula.");
            }
            $this->position++; // Consume ')'

            return $node;
        }

        throw new InvalidArgumentException("Unexpected token '{$token['value']}' where expression factor expected.");
    }

    /**
     * @param  array<int, string>  $operators
     */
    private function matchOperator(array $operators): bool
    {
        if ($this->position < count($this->tokens)) {
            $token = $this->tokens[$this->position];
            if ($token['type'] === FormulaTokenizer::TYPE_OPERATOR && in_array($token['value'], $operators, true)) {
                $this->position++;

                return true;
            }
        }

        return false;
    }

    /**
     * @return array{type: string, value: string|float}
     */
    private function previous(): array
    {
        return $this->tokens[$this->position - 1];
    }
}
