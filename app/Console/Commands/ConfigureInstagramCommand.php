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
        SystemSetting::set('social_media_app_secret', '73b4d4fcdb8e5178c27ed47eeedd83d8', 'social_media', isSecret: true);
        SystemSetting::set('instagram_app_secret', '73b4d4fcdb8e5178c27ed47eeedd83d8', 'social_media', isSecret: true);
        SystemSetting::set('meta_wa_app_id', '1454871749894754', 'whatsapp');
        SystemSetting::set('meta_wa_app_secret', '73b4d4fcdb8e5178c27ed47eeedd83d8', 'whatsapp', isSecret: true);
        SystemSetting::set('meta_wa_config_id', '1402426331846165', 'whatsapp');
        SystemSetting::set('meta_wa_token', 'EAAUrMrnYomIBSkhDNZCNn1G3xl2cZC8hWEPeW8JWWTSTmTqZC8CZBue46fZCXOe0MbZBSqPDFsOZCB8vqwx18wA7r7Ps7PBNHFo9fDRjVOWNI5zTWkrM8sGyGitpXTHa5Qka86uoiORUZBeMtO3uosVoZA9JHGTwWFGUdHZBluHzxT7wV4kE6NMZCWQ6kGFEEhTCloacwZDZD', 'whatsapp', isSecret: true);

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
