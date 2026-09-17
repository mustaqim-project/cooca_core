<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Domain\SocialMedia\Contracts\SocialMediaProviderInterface;
use App\Domain\SocialMedia\Providers\MetaProvider;
use App\Domain\SocialMedia\Providers\TikTokProvider;
use App\Domain\SocialMedia\Validation\SocialMediaContentValidator;

class SocialMediaManager
{
    public function __construct(
        protected MetaProvider $metaProvider,
        protected TikTokProvider $tiktokProvider,
        protected SocialMediaContentValidator $validator
    ) {}

    public function getProvider(string $name): SocialMediaProviderInterface
    {
        $normalized = strtolower(trim($name));

        return match ($normalized) {
            'tiktok' => $this->tiktokProvider,
            default  => $this->metaProvider,
        };
    }

    public function getProviderForChannel(string $channel): SocialMediaProviderInterface
    {
        $normalized = strtolower(trim($channel));

        return match ($normalized) {
            'tiktok' => $this->tiktokProvider,
            default  => $this->metaProvider,
        };
    }

    public function getValidator(): SocialMediaContentValidator
    {
        return $this->validator;
    }

    public function getMetaProvider(): MetaProvider
    {
        return $this->metaProvider;
    }

    public function getTikTokProvider(): TikTokProvider
    {
        return $this->tiktokProvider;
    }
}
