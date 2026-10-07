<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Mcp;

use App\Domain\Mcp\Formatters\McpSchemaFormatter;
use App\Domain\Mcp\Protocol\McpProtocolEngine;
use App\Domain\Mcp\Tools\McpToolRegistry;
use App\Http\Controllers\Controller;
use App\Models\McpAccessToken;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final class McpSseController extends Controller
{
    public function __construct(
        private readonly McpProtocolEngine $protocolEngine = new McpProtocolEngine(),
        private readonly McpToolRegistry $toolRegistry = new McpToolRegistry(),
        private readonly McpSchemaFormatter $schemaFormatter = new McpSchemaFormatter()
    ) {}

    /**
     * Unified Streamable HTTP & SSE MCP Endpoint Handler.
     * Compatible with Anthropic Claude Web Connectors, OpenAI Apps SDK, Cursor, and VS Code.
     */
    public function handleEndpoint(Request $request): Response
    {
        // 1. CORS Preflight
        if ($request->isMethod('OPTIONS')) {
            return response('', Response::HTTP_NO_CONTENT, [
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, HEAD',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, mcp-session-id, Accept, Last-Event-ID, x-mcp-token',
                'Access-Control-Max-Age'       => '86400',
            ]);
        }

        // 2. Direct JSON-RPC POST / Streamable HTTP (Spec 2024-11-05 & 2025-03)
        if ($request->isMethod('POST')) {
            return $this->message($request);
        }

        // 3. Server-Sent Events (SSE) Stream
        return $this->sse($request);
    }

    /**
     * Remote MCP HTTP Server-Sent Events (SSE) Endpoint.
     */
    public function sse(Request $request): StreamedResponse
    {
        $sessionId = Str::uuid()->toString();
        $tokenQuery = (string) $request->query('token', '');
        $tokenParam = $tokenQuery !== '' ? '&token=' . urlencode($tokenQuery) : '';
        $messageEndpoint = url("/api/v1/mcp/message?sessionId={$sessionId}{$tokenParam}");

        return response()->stream(function () use ($messageEndpoint): void {
            echo "event: endpoint\n";
            echo "data: {$messageEndpoint}\n\n";
            ob_flush();
            flush();
        }, Response::HTTP_OK, [
            'Content-Type'                => 'text/event-stream',
            'Cache-Control'               => 'no-cache, no-transform',
            'Connection'                  => 'keep-alive',
            'X-Accel-Buffering'           => 'no',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Process JSON-RPC 2.0 messages from SSE client or direct POST.
     */
    public function message(Request $request): JsonResponse
    {
        $corsHeaders = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS, HEAD',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, mcp-session-id, Accept, Last-Event-ID, x-mcp-token',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json([], Response::HTTP_NO_CONTENT, $corsHeaders);
        }

        /** @var McpAccessToken|null $token */
        $token = $request->attributes->get('mcp_token');

        $payload = $request->all();
        if (empty($payload)) {
            $raw = (string) $request->getContent();
            $payload = json_decode($raw, true) ?: [];
        }

        $response = $this->protocolEngine->handle($payload, $token);

        if ($response === null) {
            return response()->json([], Response::HTTP_ACCEPTED, $corsHeaders);
        }

        $statusCode = isset($response['error']) ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK;

        return response()->json($response, $statusCode, $corsHeaders);
    }

    /**
     * Dynamic OpenAPI 3.1.0 specification for ChatGPT Custom GPT Actions, n8n, Dify, and LangChain.
     */
    public function openapi(Request $request): JsonResponse
    {
        /** @var McpAccessToken|null $token */
        $token = $request->attributes->get('mcp_token');

        $tools = $this->toolRegistry->getToolsForToken($token);
        $serverUrl = url('/api/v1/mcp');

        $spec = $this->schemaFormatter->toOpenApi3($tools, $serverUrl);

        return response()->json($spec, Response::HTTP_OK, [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * REST Direct Tool Execution Bridge for non-MCP agents (Custom Actions, Gemini, Postman).
     */
    public function executeDirect(Request $request, string $tool): JsonResponse
    {
        /** @var McpAccessToken|null $token */
        $token = $request->attributes->get('mcp_token');

        if (! $this->toolRegistry->hasTool($tool)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Tool '{$tool}' tidak ditemukan.",
            ], Response::HTTP_NOT_FOUND);
        }

        $toolInstance = $this->toolRegistry->getTool($tool);

        if ($token !== null && ! $token->hasAbility($toolInstance->getRequiredAbility())) {
            return response()->json([
                'status'  => 'error',
                'message' => "Token tidak memiliki izin untuk tool '{$tool}'.",
            ], Response::HTTP_FORBIDDEN);
        }

        $business = Context::requireBusiness();
        $user = Context::user() ?? $token?->user;
        $arguments = $request->all();

        try {
            $result = $toolInstance->execute($business, $user, $arguments);

            return response()->json([
                'status' => 'success',
                'data'   => $result,
            ], Response::HTTP_OK);
        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
