<?php

declare(strict_types=1);

namespace App\Domain\Formula;

use InvalidArgumentException;

final class FormulaTokenizer
{
    public const TYPE_NUMBER = 'NUMBER';

    public const TYPE_IDENTIFIER = 'IDENTIFIER';

    public const TYPE_OPERATOR = 'OPERATOR';

    public const TYPE_LPAREN = 'LPAREN';

    public const TYPE_RPAREN = 'RPAREN';

    public const BLACKLISTED_KEYWORDS = [
        'eval', 'exec', 'system', 'passthru', 'shell_exec', 'popen', 'proc_open',
        'file_get_contents', 'file_put_contents', 'unlink', 'include', 'require',
        'phpinfo', 'assert', 'var_dump', 'print_r',
    ];

    /**
     * Tokenize a math formula string using strict whitelist.
     *
     * @return array<int, array{type: string, value: string|float}>
     *
     * @throws InvalidArgumentException
     */
    public function tokenize(string $expression): array
    {
        $tokens = [];
        $length = strlen($expression);
        $i = 0;

        while ($i < $length) {
            $char = $expression[$i];

            // 1. Whitespace
            if (ctype_space($char)) {
                $i++;

                continue;
            }

            // 2. Operators & Parentheses
            if (in_array($char, ['+', '-', '*', '/'], true)) {
                $tokens[] = ['type' => self::TYPE_OPERATOR, 'value' => $char];
                $i++;

                continue;
            }

            if ($char === '(') {
                $tokens[] = ['type' => self::TYPE_LPAREN, 'value' => '('];
                $i++;

                continue;
            }

            if ($char === ')') {
                $tokens[] = ['type' => self::TYPE_RPAREN, 'value' => ')'];
                $i++;

                continue;
            }

            // 3. Numbers
            if (ctype_digit($char) || ($char === '.' && isset($expression[$i + 1]) && ctype_digit($expression[$i + 1]))) {
                $start = $i;
                $hasDot = ($char === '.');
                $i++;

                while ($i < $length && (ctype_digit($expression[$i]) || ($expression[$i] === '.' && ! $hasDot))) {
                    if ($expression[$i] === '.') {
                        $hasDot = true;
                    }
                    $i++;
                }

                $numStr = substr($expression, $start, $i - $start);
                $tokens[] = ['type' => self::TYPE_NUMBER, 'value' => (float) $numStr];

                continue;
            }

            // 4. Identifiers (Variable Names)
            if (ctype_alpha($char) || $char === '_') {
                $start = $i;
                $i++;

                while ($i < $length && (ctype_alnum($expression[$i]) || $expression[$i] === '_')) {
                    $i++;
                }

                $ident = substr($expression, $start, $i - $start);

                if (in_array(strtolower($ident), self::BLACKLISTED_KEYWORDS, true)) {
                    throw new InvalidArgumentException("Illegal keyword '{$ident}' detected in formula.");
                }

                $tokens[] = ['type' => self::TYPE_IDENTIFIER, 'value' => $ident];

                continue;
            }

            // 5. Unrecognized / Illegal Character
            throw new InvalidArgumentException("Illegal character '{$char}' at position {$i} in formula.");
        }

        return $tokens;
    }
}
