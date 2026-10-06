<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Mcp\Auth\McpTokenAuthenticator;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureMcpTokenValid
{
    public function __construct(
        private readonly McpTokenAuthenticator $authenticator = new McpTokenAuthenticator()
    ) {}

    /**
     * Handle an incoming MCP request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $requiredAbility = null): Response
    {
        $rawToken = $request->bearerToken() ?: (string) $request->query('token', '');

        if ($rawToken === '') {
            return $this->unauthorizedResponse($request, 'MCP Bearer Token is required.');
        }

        $token = $this->authenticator->authenticate($rawToken);

        if ($token === null) {
            return $this->unauthorizedResponse($request, 'Invalid, deactivated, or expired MCP token.');
        }

        if ($requiredAbility !== null && ! $token->hasAbility($requiredAbility)) {
            return $this->forbiddenResponse($request, "Token lacks required ability: '{$requiredAbility}'.");
        }

        $request->attributes->set('mcp_token', $token);

        return $next($request);
    }

    private function unauthorizedResponse(Request $request, string $message): JsonResponse
    {
        if ($request->isJson() || $request->has('jsonrpc')) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => -32000,
                    'message' => $message,
                ],
                'id'      => $request->input('id'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED);
    }

    private function forbiddenResponse(Request $request, string $message): JsonResponse
    {
        if ($request->isJson() || $request->has('jsonrpc')) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => -32001,
                    'message' => $message,
                ],
                'id'      => $request->input('id'),
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], Response::HTTP_FORBIDDEN);
    }
}
