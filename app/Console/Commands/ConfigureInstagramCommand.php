<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;

class ConfigureInstagramCommand extends Command
{
    protected $signature = 'instagram:configure';

    protected $description = 'Configure Instagram credentials for Cooca Indonesia';

    public function handle(): int
    {
        SystemSetting::set('social_media_app_id', '1454871749894754', 'social_media');
        SystemSetting::set('instagram_app_id', '1813131243044390', 'social_media');
        SystemSetting::set('instagram_app_name', 'Cooca-IG', 'social_media');
        SystemSetting::set('instagram_account_id', '17841439846162016', 'social_media');
        SystemSetting::set('instagram_graph_user_id', '28475871372070145', 'social_media');
        SystemSetting::set('instagram_username', 'cooca.indonesia', 'social_media');
        SystemSetting::set('instagram_access_token', 'IGAAZAxCIOsPiZABZAGFuUnVkMjh3bXFPSUJMM1M5YVdNdXAzNnd5Mm1qWnVLNUl0c0dLYnNCMmRPWFN6OTFYUjhiLWl3WFFjS2pISXBMMWRjSUZAXMzBwT2ZA2Q2hVN1FfZA0t5RDA0ZAWEyUEJYNEVmRTREN1BXbGt6OUR0bVdSOWZAIdwZDZD', 'social_media', isSecret: true);
        SystemSetting::set('instagram_status', 'active', 'social_media');
        SystemSetting::set('instagram_verified_at', now()->toIso8601String(), 'social_media');

        $this->info('Instagram credentials configured successfully.');

        return self::SUCCESS;
    }
}
