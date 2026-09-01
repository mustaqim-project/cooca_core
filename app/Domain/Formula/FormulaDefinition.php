<?php

declare(strict_types=1);

namespace App\Domain\Formula;

final class FormulaDefinition
{
    /**
     * @param  array<string, mixed>  $ast
     * @param  array<int, string>  $variables
     */
    public function __construct(
        public readonly string $expression,
        public readonly array $ast,
        public readonly array $variables,
        public readonly int $version = 1
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'expression' => $this->expression,
            'ast' => $this->ast,
            'variables' => $this->variables,
            'version' => $this->version,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            expression: (string) ($data['expression'] ?? ''),
            ast: (array) ($data['ast'] ?? []),
            variables: (array) ($data['variables'] ?? []),
            version: (int) ($data['version'] ?? 1)
        );
    }
}
