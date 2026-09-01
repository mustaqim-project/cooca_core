<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Formula\FormulaAstBuilder;
use App\Domain\Formula\FormulaDefinition;
use App\Domain\Formula\FormulaEvaluator;
use App\Domain\Formula\FormulaTokenizer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class FormulaController extends Controller
{
    public function __construct(
        private readonly FormulaTokenizer $tokenizer = new FormulaTokenizer,
        private readonly FormulaAstBuilder $astBuilder = new FormulaAstBuilder,
        private readonly FormulaEvaluator $evaluator = new FormulaEvaluator
    ) {}

    /**
     * Dry-run evaluate formula string against provided variables.
     */
    public function evaluate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expression' => ['required', 'string', 'max:1000'],
            'variables' => ['required', 'array'],
        ]);

        try {
            $tokens = $this->tokenizer->tokenize($validated['expression']);
            $parsed = $this->astBuilder->build($tokens);
            $result = $this->evaluator->evaluate($parsed['ast'], $validated['variables']);

            return response()->json([
                'expression' => $validated['expression'],
                'result' => $result,
                'variables_used' => $parsed['variables'],
                'ast' => $parsed['ast'],
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_code' => 'FORMULA_EVALUATION_ERROR',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Validate formula syntax and extract required variables.
     */
    public function validateFormula(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expression' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $tokens = $this->tokenizer->tokenize($validated['expression']);
            $parsed = $this->astBuilder->build($tokens);
            $formulaDef = new FormulaDefinition(
                expression: $validated['expression'],
                ast: $parsed['ast'],
                variables: $parsed['variables'],
                version: 1
            );

            return response()->json([
                'valid' => true,
                'formula_definition' => $formulaDef->toArray(),
            ], Response::HTTP_OK);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage(),
                'error_code' => 'FORMULA_SYNTAX_ERROR',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
