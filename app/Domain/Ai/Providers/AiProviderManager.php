<?php

declare(strict_types=1);

namespace App\Domain\Ai\Providers;

use App\Models\AiProviderConfig;
use App\Models\Business;
use InvalidArgumentException;

final class AiProviderManager
{
    /**
     * @var array<string, AiProviderInterface>
     */
    private array $providers = [];

    public function __construct()
    {
        $this->registerProvider(new OpenAiProvider());
        $this->registerProvider(new GeminiProvider());
        $this->registerProvider(new AnthropicProvider());
        $this->registerProvider(new OpenRouterProvider());
        $this->registerProvider(new RuleBasedFallbackProvider());
    }

    public function registerProvider(AiProviderInterface $provider): void
    {
        $this->providers[$provider->getProviderName()] = $provider;
    }

    public function getProvider(string $name): AiProviderInterface
    {
        if (! isset($this->providers[$name])) {
            throw new InvalidArgumentException("Provider AI '{$name}' tidak dikenali.");
        }

        return $this->providers[$name];
    }

    /**
     * Get list of all supported providers and their models (including custom added models).
     *
     * @return array<string, array{name: string, models: array<int, array{id: string, name: string, tier: string}>}>
     */
    public function getCatalog(?Business $business = null): array
    {
        $catalog = [
            'openai' => [
                'name' => 'OpenAI',
                'models' => $this->providers['openai']->getAvailableModels(),
            ],
            'gemini' => [
                'name' => 'Google Gemini',
                'models' => $this->providers['gemini']->getAvailableModels(),
            ],
            'anthropic' => [
                'name' => 'Anthropic Claude',
                'models' => $this->providers['anthropic']->getAvailableModels(),
            ],
            'openrouter' => [
                'name' => 'OpenRouter',
                'models' => $this->providers['openrouter']->getAvailableModels(),
            ],
        ];

        if ($business) {
            $configs = AiProviderConfig::where('business_id', $business->id)->get();
            foreach ($configs as $config) {
                if (isset($catalog[$config->provider])) {
                    if (is_array($config->settings) && isset($config->settings['available_models']) && is_array($config->settings['available_models'])) {
                        $existingIds = array_column($catalog[$config->provider]['models'], 'id');
                        foreach ($config->settings['available_models'] as $m) {
                            if (is_array($m) && ! empty($m['id']) && ! in_array($m['id'], $existingIds, true)) {
                                $catalog[$config->provider]['models'][] = [
                                    'id' => (string) $m['id'],
                                    'name' => (string) ($m['name'] ?? $m['id']),
                                    'tier' => (string) ($m['tier'] ?? 'balanced'),
                                ];
                                $existingIds[] = $m['id'];
                            }
                        }
                    }

                    $customList = [];
                    if (is_array($config->settings) && isset($config->settings['custom_models']) && is_array($config->settings['custom_models'])) {
                        $customList = $config->settings['custom_models'];
                    }
                    if (! empty($config->model)) {
                        $customList[] = $config->model;
                    }

                    $existingIds = array_column($catalog[$config->provider]['models'], 'id');
                    foreach (array_unique($customList) as $customId) {
                        $customId = trim((string) $customId);
                        if ($customId !== '' && ! in_array($customId, $existingIds, true)) {
                            $catalog[$config->provider]['models'][] = [
                                'id' => $customId,
                                'name' => $customId . ' (Custom Model)',
                                'tier' => 'custom',
                            ];
                            $existingIds[] = $customId;
                        }
                    }
                }
            }
        }

        return $catalog;
    }

    /**
     * Resolve the active AI provider, decrypted key, and model for a business.
     *
     * @return array{provider: AiProviderInterface, api_key: string, model: string, config: ?AiProviderConfig}
     */
    public function resolveForBusiness(?Business $business): array
    {
        if ($business) {
            // 1. Check database config for default active provider
            /** @var AiProviderConfig|null $config */
            $config = AiProviderConfig::where('business_id', $business->id)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->first();

            if ($config && isset($this->providers[$config->provider]) && ! empty($config->api_key)) {
                return [
                    'provider' => $this->providers[$config->provider],
                    'api_key' => $config->api_key, // Auto-decrypted by Eloquent cast
                    'model' => $config->model,
                    'config' => $config,
                ];
            }
        }

        // 2. Check system environment fallbacks
        $geminiKey = (string) (config('services.gemini.key') ?? env('GEMINI_API_KEY') ?? '');
        if ($geminiKey !== '') {
            return [
                'provider' => $this->providers['gemini'],
                'api_key' => $geminiKey,
                'model' => 'gemini-2.5-flash',
                'config' => null,
            ];
        }

        $openaiKey = (string) (config('services.openai.key') ?? env('OPENAI_API_KEY') ?? '');
        if ($openaiKey !== '') {
            return [
                'provider' => $this->providers['openai'],
                'api_key' => $openaiKey,
                'model' => 'gpt-4o-mini',
                'config' => null,
            ];
        }

        // 3. Fallback to 100% offline native rule-based engine
        return [
            'provider' => $this->providers['fallback'],
            'api_key' => '',
            'model' => 'cooca-rule-engine-v1',
            'config' => null,
        ];
    }

    /**
     * Test connection for a provider.
     *
     * @return array{success: bool, message: string}
     */
    public function testConnection(string $providerName, string $apiKey, string $model): array
    {
        $provider = $this->getProvider($providerName);
        return $provider->testConnection($apiKey, $model);
    }

    /**
     * Discover available models from the provider for the given API key.
     *
     * @return array<int, array{id: string, name: string, tier: string}>
     */
    public function discoverModels(string $providerName, string $apiKey): array
    {
        $provider = $this->getProvider($providerName);
        if (method_exists($provider, 'fetchAvailableModels')) {
            return $provider->fetchAvailableModels($apiKey);
        }

        return $provider->getAvailableModels();
    }
}
