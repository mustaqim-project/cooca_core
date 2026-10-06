<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Formatters;

use App\Domain\Mcp\Tools\McpToolInterface;

final class McpSchemaFormatter
{
    /**
     * Format to native Anthropic MCP Tools manifest.
     *
     * @param  array<string, McpToolInterface>  $tools
     * @return array<int, array<string, mixed>>
     */
    public function toMcp(array $tools): array
    {
        $manifest = [];
        foreach ($tools as $tool) {
            $manifest[] = [
                'name'        => $tool->getName(),
                'description' => $tool->getDescription(),
                'inputSchema' => $tool->getInputSchema(),
            ];
        }

        return $manifest;
    }

    /**
     * Format to OpenAI Function Calling format.
     *
     * @param  array<string, McpToolInterface>  $tools
     * @return array<int, array<string, mixed>>
     */
    public function toOpenAi(array $tools): array
    {
        $manifest = [];
        foreach ($tools as $tool) {
            $manifest[] = [
                'type'     => 'function',
                'function' => [
                    'name'        => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters'  => $tool->getInputSchema(),
                ],
            ];
        }

        return $manifest;
    }

    /**
     * Format to Google Gemini FunctionDeclaration format.
     *
     * @param  array<string, McpToolInterface>  $tools
     * @return array<int, array<string, mixed>>
     */
    public function toGemini(array $tools): array
    {
        $manifest = [];
        foreach ($tools as $tool) {
            $manifest[] = [
                'name'        => $tool->getName(),
                'description' => $tool->getDescription(),
                'parameters'  => $tool->getInputSchema(),
            ];
        }

        return $manifest;
    }

    /**
     * Format to OpenAPI 3.1.0 document (used by ChatGPT Custom GPT Actions, n8n, Dify, Postman).
     *
     * @param  array<string, McpToolInterface>  $tools
     * @return array<string, mixed>
     */
    public function toOpenApi3(array $tools, string $serverUrl): array
    {
        $paths = [];

        foreach ($tools as $tool) {
            $toolName = $tool->getName();
            $schema = $tool->getInputSchema();

            $paths["/tools/{$toolName}/execute"] = [
                'post' => [
                    'operationId' => $toolName,
                    'summary'     => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'requestBody' => [
                        'required' => true,
                        'content'  => [
                            'application/json' => [
                                'schema' => $schema,
                            ],
                        ],
                    ],
                    'responses'   => [
                        '200' => [
                            'description' => 'Tool execution succeeded',
                            'content'     => [
                                'application/json' => [
                                    'schema' => [
                                        'type'       => 'object',
                                        'properties' => [
                                            'status'  => ['type' => 'string'],
                                            'message' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        '400' => [
                            'description' => 'Validation error or invalid arguments',
                        ],
                        '401' => [
                            'description' => 'Unauthorized / Invalid MCP Token',
                        ],
                    ],
                ],
            ];
        }

        return [
            'openapi' => '3.1.0',
            'info'    => [
                'title'       => 'COOCA ERP Universal MCP Gateway',
                'description' => 'Multi-Tenant ERP AI Agent Actions for Businesses (Finance, Products, Inventory, Social Media, Reports, CRM, WhatsApp)',
                'version'     => '1.0.0',
            ],
            'servers' => [
                [
                    'url'         => rtrim($serverUrl, '/'),
                    'description' => 'COOCA Cloud MCP Gateway Server',
                ],
            ],
            'paths'   => $paths,
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type'         => 'http',
                        'scheme'       => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description'  => 'Masukkan token MCP COOCA Anda (diawali dengan cooca_mcp_live_...)',
                    ],
                ],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
        ];
    }
}
