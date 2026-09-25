<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Providers;

use App\Domain\SocialMedia\Clients\LinkedInClient;
use App\Domain\SocialMedia\Contracts\SocialMediaProviderInterface;
use App\Models\SocialMediaAccount;
use App\Models\SocialPostTarget;
use Illuminate\Support\Facades\Log;

class LinkedInProvider implements SocialMediaProviderInterface
{
    public function __construct(
        protected LinkedInClient $client
    ) {}

    public function getClient(): LinkedInClient
    {
        return $this->client;
    }

    public function getProviderName(): string
    {
        return 'linkedin';
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        return $this->client->getAuthUrl($redirectUri, $state);
    }

    public function handleAuthCallback(string $code, string $redirectUri): array
    {
        $tokenData = $this->client->exchangeCodeForToken($code, $redirectUri);
        $accessToken = (string) ($tokenData['access_token'] ?? '');
        $refreshToken = (string) ($tokenData['refresh_token'] ?? '');
        $expiresIn = (int) ($tokenData['expires_in'] ?? 5184000); // 60 days default

        $profile = [];
        try {
            $profile = $this->client->getProfile($accessToken);
        } catch (\Throwable $e) {
            Log::warning('LinkedInProvider handleAuthCallback getProfile failed: ' . $e->getMessage());
        }

        $sub = (string) ($profile['sub'] ?? '');
        $displayName = (string) ($profile['name'] ?? trim(($profile['given_name'] ?? '') . ' ' . ($profile['family_name'] ?? '')) ?: 'LinkedIn Member');
        $email = (string) ($profile['email'] ?? '');
        $picture = (string) ($profile['picture'] ?? '');

        return [
            'open_id'       => $sub,
            'username'      => $email ?: $displayName,
            'account_name'  => $displayName,
            'avatar_url'    => $picture ?: null,
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken ?: null,
            'expires_at'    => now()->addSeconds($expiresIn),
            'creator_info'  => $profile,
            'raw'           => $tokenData,
        ];
    }

    public function refreshToken(SocialMediaAccount $account): SocialMediaAccount
    {
        // LinkedIn access tokens typically last 60 days. If refresh token is available, exchange it.
        return $account;
    }

    public function publish(SocialMediaAccount $account, SocialPostTarget $target, array $mediaItems = []): array
    {
        $caption = $target->getEffectiveCaption();
        $authorSubOrUrn = $account->account_id;

        return $this->client->publishPost(
            $authorSubOrUrn,
            $account->access_token,
            $caption,
            $mediaItems
        );
    }

    public function getCreatorInfo(SocialMediaAccount $account): array
    {
        return $this->client->getProfile($account->access_token);
    }

    public function syncMetrics(SocialMediaAccount $account, string $platformPostId): array
    {
        return $this->client->getPostInsights($platformPostId, $account->access_token);
    }
}
