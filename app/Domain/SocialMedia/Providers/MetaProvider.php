<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia\Providers;

use App\Domain\SocialMedia\Clients\MetaSocialMediaClient;
use App\Domain\SocialMedia\Contracts\SocialMediaProviderInterface;
use App\Models\SocialMediaAccount;
use App\Models\SocialPostTarget;

class MetaProvider implements SocialMediaProviderInterface
{
    public function __construct(
        protected MetaSocialMediaClient $client
    ) {}

    public function getClient(): MetaSocialMediaClient
    {
        return $this->client;
    }

    public function getProviderName(): string
    {
        return 'meta';
    }

    public function isConfigured(): bool
    {
        return $this->client->isConfigured();
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        return $this->client->getLoginUrl($redirectUri, $state);
    }

    public function handleAuthCallback(string $code, string $redirectUri): array
    {
        $tokenData = $this->client->exchangeCodeForUserToken($code, $redirectUri);
        $userToken = $tokenData['access_token'] ?? '';

        $longLived = $this->client->exchangeTokenForLongLived($userToken);
        $longLivedToken = $longLived['access_token'] ?? $userToken;

        $pages = $this->client->getManageablePages($longLivedToken);

        return [
            'user_token'       => $longLivedToken,
            'manageable_pages' => $pages,
        ];
    }

    public function refreshToken(SocialMediaAccount $account): SocialMediaAccount
    {
        // Meta page access tokens are permanent and do not require refresh.
        return $account;
    }

    public function publish(SocialMediaAccount $account, SocialPostTarget $target, array $mediaItems = []): array
    {
        $caption = $target->getEffectiveCaption();
        $channel = strtolower($target->channel);
        $contentType = strtolower($target->content_type);
        $firstMediaUrl = ! empty($mediaItems) ? ($mediaItems[0]['media_url'] ?? '') : '';

        if ($channel === 'facebook') {
            if ($contentType === 'video' || $contentType === 'reel') {
                if (empty($firstMediaUrl)) {
                    throw new \InvalidArgumentException('Video Facebook mewajibkan berkas video yang valid.');
                }

                return $this->client->publishFacebookVideo(
                    $account->account_id,
                    $account->access_token,
                    $caption,
                    $firstMediaUrl
                );
            }

            return $this->client->publishFacebookPost(
                $account->account_id,
                $account->access_token,
                $caption,
                null,
                $firstMediaUrl ?: null
            );
        }

        if ($channel === 'instagram') {
            if ($contentType === 'carousel') {
                return $this->client->publishInstagramCarousel(
                    $account->account_id,
                    $account->access_token,
                    $caption,
                    $mediaItems
                );
            }

            if (empty($firstMediaUrl)) {
                throw new \InvalidArgumentException('Instagram mewajibkan minimal 1 media untuk dipublikasikan.');
            }

            $igType = ($contentType === 'reel' || $contentType === 'video') ? 'REELS' : 'IMAGE';

            return $this->client->publishInstagramPost(
                $account->account_id,
                $account->access_token,
                $caption,
                $firstMediaUrl,
                $igType
            );
        }

        if ($channel === 'threads') {
            $threadsType = ($contentType === 'video' || $contentType === 'reel') ? 'VIDEO' : (! empty($firstMediaUrl) ? 'IMAGE' : 'TEXT');

            return $this->client->publishThreadsPost(
                $account->account_id,
                $account->access_token,
                $caption,
                $firstMediaUrl ?: null,
                $threadsType
            );
        }

        throw new \InvalidArgumentException("Saluran Meta [{$channel}] tidak didukung.");
    }

    public function getCreatorInfo(SocialMediaAccount $account): array
    {
        return [
            'account_id'   => $account->account_id,
            'account_name' => $account->account_name,
            'username'     => $account->username,
            'picture_url'  => $account->profile_picture_url,
            'metadata'     => $account->metadata ?? [],
        ];
    }

    public function syncMetrics(SocialMediaAccount $account, string $platformPostId): array
    {
        return $this->client->getPostInsights($platformPostId, $account->access_token);
    }
}
