<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Protocol;

use App\Domain\Mcp\Formatters\McpSchemaFormatter;
use App\Domain\Mcp\Tools\McpToolRegistry;
use App\Models\Business;
use App\Models\McpAccessToken;
use App\Models\McpActivityLog;
use App\Models\User;
use App\Support\Context;
use Throwable;

final class McpProtocolEngine
{
    public function __construct(
        private readonly McpToolRegistry $toolRegistry = new McpToolRegistry(),
        private readonly McpSchemaFormatter $schemaFormatter = new McpSchemaFormatter()
    ) {}

    /**
     * Process an incoming JSON-RPC 2.0 MCP request array.
     *
     * @param  array<string, mixed>  $requestPayload
     * @return array<string, mixed>|null
     */
    public function handle(array $requestPayload, ?McpAccessToken $token = null): ?array
    {
        $id = $requestPayload['id'] ?? null;
        $method = (string) ($requestPayload['method'] ?? '');
        $params = (array) ($requestPayload['params'] ?? []);

        // Notification (no id, no response needed)
        if ($method === 'notifications/initialized') {
            return null;
        }

        try {
            $result = match ($method) {
                'initialize'       => $this->handleInitialize($params),
                'ping'             => new \stdClass(),
                'tools/list'       => $this->handleToolsList($token),
                'tools/call'       => $this->handleToolsCall($params, $token),
                'resources/list'   => ['resources' => []],
                'prompts/list'     => ['prompts' => []],
                'logging/setLevel' => new \stdClass(),
                default            => throw new \InvalidArgumentException("Method '{$method}' not supported.", -32601),
            };

            return [
                'jsonrpc' => '2.0',
                'result'  => $result,
                'id'      => $id,
            ];
        } catch (Throwable $e) {
            $code = $e->getCode() !== 0 ? (int) $e->getCode() : -32002;

            return [
                'jsonrpc' => '2.0',
                'error'   => [
                    'code'    => $code,
                    'message' => $e->getMessage(),
                ],
                'id'      => $id,
            ];
        }
    }

    /**
     * Handle MCP 'initialize' handshake.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function handleInitialize(array $params): array
    {
        return [
            'protocolVersion' => '2024-11-05',
            'serverInfo'      => [
                'name'    => 'cooca-erp-mcp',
                'version' => '1.0.0',
            ],
            'capabilities'    => [
                'tools'     => [
                    'listChanged' => false,
                ],
                'resources' => [
                    'subscribe'   => false,
                    'listChanged' => false,
                ],
                'prompts'   => [
                    'listChanged' => false,
                ],
            ],
            'instructions'    => 'COOCA ID Multi-Tenant MCP Server. You can query financial records, record expenses from receipts, manage product catalogs, check inventory, schedule social posts, and send WhatsApp messages. You do NOT need to specify business_id because your session is strictly bound to the active tenant.',
        ];
    }

    /**
     * Handle 'tools/list'.
     *
     * @return array<string, mixed>
     */
    private function handleToolsList(?McpAccessToken $token): array
    {
        $tools = $this->toolRegistry->getToolsForToken($token);

        return [
            'tools' => $this->schemaFormatter->toMcp($tools),
        ];
    }

    /**
     * Handle 'tools/call'.
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function handleToolsCall(array $params, ?McpAccessToken $token): array
    {
        $toolName = (string) ($params['name'] ?? '');
        $arguments = (array) ($params['arguments'] ?? []);

        if (! $this->toolRegistry->hasTool($toolName)) {
            throw new \InvalidArgumentException("Tool '{$toolName}' not found in COOCA catalog.", -32601);
        }

        $tool = $this->toolRegistry->getTool($toolName);

        // Scope check
        if ($token !== null && ! $token->hasAbility($tool->getRequiredAbility())) {
            throw new \RuntimeException("Access token lacks ability '{$tool->getRequiredAbility()}' for tool '{$toolName}'.", -32001);
        }

        // Multi-tenant bound check
        $business = Context::requireBusiness();
        /** @var User|null $user */
        $user = Context::user() ?? $token?->user;

        $startTime = microtime(true);
        $status = 'success';
        $errorMessage = null;
        $output = [];

        try {
            $output = $tool->execute($business, $user, $arguments);

            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ],
                ],
            ];
        } catch (Throwable $e) {
            $status = 'error';
            $errorMessage = $e->getMessage();

            return [
                'isError' => true,
                'content' => [
                    [
                        'type' => 'text',
                        'text' => "Error executing '{$toolName}': " . $e->getMessage(),
                    ],
                ],
            ];
        } finally {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            // Sanitize sensitive arguments for audit log
            $sanitizedArgs = $arguments;
            if (isset($sanitizedArgs['receipt_image_base64'])) {
                $sanitizedArgs['receipt_image_base64'] = '[BASE64_IMAGE_STRIPPED (' . strlen((string) $arguments['receipt_image_base64']) . ' bytes)]';
            }

            McpActivityLog::create([
                'business_id'       => $business->id,
                'token_id'          => $token?->id,
                'tool_name'         => $toolName,
                'client_provider'   => $token?->provider_hint ?? 'custom',
                'arguments_payload' => $sanitizedArgs,
                'response_status'   => $status,
                'execution_time_ms' => $durationMs,
                'ip_address'        => request()->ip(),
                'error_message'     => $errorMessage,
            ]);
        }
    }
}
