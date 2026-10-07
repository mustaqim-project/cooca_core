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
        // 1. Always permit CORS Preflight OPTIONS without requiring token
        if ($request->isMethod('OPTIONS')) {
            return response('', Response::HTTP_NO_CONTENT, [
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, HEAD',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, mcp-session-id, Accept, Last-Event-ID, x-mcp-token',
                'Access-Control-Max-Age'       => '86400',
            ]);
        }

        $rawToken = $request->bearerToken()
            ?: (string) $request->header('x-mcp-token', '')
            ?: (string) $request->query('token', '');

        if ($rawToken === '') {
            return $this->unauthorizedResponse($request, 'MCP Bearer Token is required. Include Authorization: Bearer <token> or ?token=<token> in the URL.');
        }

        $token = $this->authenticator->authenticate($rawToken);

        if ($token === null) {
            return $this->unauthorizedResponse($request, 'Invalid, deactivated, or expired MCP token.');
        }

        // Enforce active paid subscription requirement (Standard, Premium, Prestige)
        $business = $token->business;
        if ($business !== null) {
            $entitlementService = app(\App\Domain\Billing\EntitlementService::class);
            if (! $entitlementService->canAccessMcp($business)) {
                return $this->forbiddenResponse(
                    $request,
                    'Akses gateway MCP memerlukan langganan paket aktif (Standard, Premium, atau Prestige). Silakan tingkatkan paket usaha Anda di https://cooca.id.'
                );
            }
        }

        if ($requiredAbility !== null && ! $token->hasAbility($requiredAbility)) {
            return $this->forbiddenResponse($request, "Token lacks required ability: '{$requiredAbility}'.");
        }

        $request->attributes->set('mcp_token', $token);

        $response = $next($request);

        // Attach CORS header to outgoing response
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, HEAD');
        $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, mcp-session-id, Accept, Last-Event-ID, x-mcp-token');

        return $response;
    }

    private function unauthorizedResponse(Request $request, string $message): JsonResponse
    {
        $headers = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, mcp-session-id, Accept, Last-Event-ID, x-mcp-token',
        ];

        if ($request->isJson() || $request->has('jsonrpc') || $request->has('method')) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => -32000,
                    'message' => $message,
                ],
                'id'      => $request->input('id'),
            ], Response::HTTP_UNAUTHORIZED, $headers);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], Response::HTTP_UNAUTHORIZED, $headers);
    }

    private function forbiddenResponse(Request $request, string $message): JsonResponse
    {
        $headers = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, mcp-session-id, Accept, Last-Event-ID, x-mcp-token',
        ];

        if ($request->isJson() || $request->has('jsonrpc') || $request->has('method')) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => -32001,
                    'message' => $message,
                ],
                'id'      => $request->input('id'),
            ], Response::HTTP_FORBIDDEN, $headers);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $message,
        ], Response::HTTP_FORBIDDEN, $headers);
    }
}
