<?php

declare(strict_types=1);

namespace App\Domain\Ai\Support;

final class AiStructuredOutputParser
{
    /**
     * Parses a raw string from LLM which may be JSON, markdown-wrapped JSON,
     * truncated JSON, or plain text, returning an associative array with structured keys.
     *
     * @return array<string, mixed>
     */
    public static function parse(string $raw): array
    {
        $clean = trim($raw);

        // Strip markdown code fences if present
        if (preg_match('/^```(?:json)?\s*([\s\S]*?)\s*(?:```|$)/i', $clean, $matches)) {
            $clean = trim($matches[1]);
        }

        // Find outermost opening brace '{'
        $firstBrace = strpos($clean, '{');
        if ($firstBrace !== false) {
            $clean = substr($clean, $firstBrace);
        }

        // 1. Direct json_decode
        $decoded = json_decode($clean, true);
        if (is_array($decoded) && (isset($decoded['summary']) || isset($decoded['findings']) || isset($decoded['recommendations']))) {
            return $decoded;
        }

        // 2. Truncated JSON repair (handle token limit cutoff)
        $repaired = self::repairTruncatedJson($clean);
        $decoded = json_decode($repaired, true);
        if (is_array($decoded) && (isset($decoded['summary']) || isset($decoded['findings']) || isset($decoded['recommendations']))) {
            return $decoded;
        }

        // 3. Targeted Regex Fallback for key sections
        $result = [];
        if (preg_match('/"summary"\s*:\s*"((?:[^"\\\\]|\\\\.)*)"/s', $clean, $m)) {
            $result['summary'] = json_decode('"' . $m[1] . '"') ?? stripslashes($m[1]);
        }

        if (preg_match('/"findings"\s*:\s*\[(.*?)\](?:\s*,\s*"(?:recommendations|actions)"|\s*})/s', $clean, $m)) {
            if (preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/s', $m[1], $items)) {
                $result['findings'] = array_map(fn($item) => json_decode('"' . $item . '"') ?? stripslashes($item), $items[1]);
            }
        }

        if (preg_match('/"recommendations"\s*:\s*\[(.*?)\](?:\s*,\s*"(?:actions)"|\s*})/s', $clean, $m)) {
            if (preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/s', $m[1], $items)) {
                $result['recommendations'] = array_map(fn($item) => json_decode('"' . $item . '"') ?? stripslashes($item), $items[1]);
            }
        }

        if (! empty($result['summary']) || ! empty($result['findings']) || ! empty($result['recommendations'])) {
            return $result;
        }

        // 4. Raw text fallback: If LLM returned text, treat it as the summary
        $cleanText = preg_replace('/^\{\s*"summary"\s*:\s*"/s', '', $clean);
        $cleanText = preg_replace('/"[\s\S]*$/s', '', $cleanText);
        $cleanText = trim($cleanText);

        return [
            'summary' => ! empty($cleanText) ? $cleanText : $raw,
            'findings' => [],
            'recommendations' => [],
            'actions' => [],
            'raw' => $raw,
        ];
    }

    private static function repairTruncatedJson(string $json): string
    {
        $lastValidCommaOrBrace = max(strrpos($json, '}'), strrpos($json, ']'));
        if ($lastValidCommaOrBrace === false) {
            return $json;
        }

        $candidate = substr($json, 0, $lastValidCommaOrBrace + 1);

        // Strip trailing comma if present
        $candidate = rtrim($candidate, " \t\n\r,");

        // Count open vs closed braces and brackets
        $openBraces = substr_count($candidate, '{') - substr_count($candidate, '}');
        $openBrackets = substr_count($candidate, '[') - substr_count($candidate, ']');

        for ($i = 0; $i < $openBrackets; $i++) {
            $candidate .= ']';
        }
        for ($i = 0; $i < $openBraces; $i++) {
            $candidate .= '}';
        }

        return $candidate;
    }
}
