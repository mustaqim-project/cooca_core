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
        SystemSetting::set('meta_wa_phone_number_id', '1344185355444409', 'whatsapp');
        SystemSetting::set('meta_wa_waba_id', '1546059137323420', 'whatsapp');
        SystemSetting::set('meta_wa_token', 'EAAUrMrnYomIBSkhDNZCNn1G3xl2cZC8hWEPeW8JWWTSTmTqZC8CZBue46fZCXOe0MbZBSqPDFsOZCB8vqwx18wA7r7Ps7PBNHFo9fDRjVOWNI5zTWkrM8sGyGitpXTHa5Qka86uoiORUZBeMtO3uosVoZA9JHGTwWFGUdHZBluHzxT7wV4kE6NMZCWQ6kGFEEhTCloacwZDZD', 'whatsapp', isSecret: true);

        SystemSetting::set('instagram_app_id', '1813131243044390', 'social_media');
        SystemSetting::set('instagram_app_name', 'Cooca-IG', 'social_media');
        SystemSetting::set('instagram_account_id', '17841439846162016', 'social_media');
        SystemSetting::set('instagram_graph_user_id', '28475871372070145', 'social_media');
        SystemSetting::set('instagram_username', 'cooca.indonesia', 'social_media');
        SystemSetting::set('instagram_access_token', 'IGAAZAxCIOsPiZABZAGFuUnVkMjh3bXFPSUJMM1M5YVdNdXAzNnd5Mm1qWnVLNUl0c0dLYnNCMmRPWFN6OTFYUjhiLWl3WFFjS2pISXBMMWRjSUZAXMzBwT2ZA2Q2hVN1FfZA0t5RDA0ZAWEyUEJYNEVmRTREN1BXbGt6OUR0bVdSOWZAIdwZDZD', 'social_media', isSecret: true);
        SystemSetting::set('instagram_status', 'active', 'social_media');
        SystemSetting::set('instagram_verified_at', now()->toIso8601String(), 'social_media');

        // Facebook Official Page Settings (Cooca Indonesia)
        SystemSetting::set('social_media_app_token', 'EAAUrMrnYomIBSkhDNZCNn1G3xl2cZC8hWEPeW8JWWTSTmTqZC8CZBue46fZCXOe0MbZBSqPDFsOZCB8vqwx18wA7r7Ps7PBNHFo9fDRjVOWNI5zTWkrM8sGyGitpXTHa5Qka86uoiORUZBeMtO3uosVoZA9JHGTwWFGUdHZBluHzxT7wV4kE6NMZCWQ6kGFEEhTCloacwZDZD', 'social_media', isSecret: true);
        SystemSetting::set('social_media_page_id', '1340316975827711', 'social_media');
        SystemSetting::set('social_media_page_name', 'Cooca Indonesia', 'social_media');
        SystemSetting::set('social_media_page_token', 'EAAUrMrnYomIBSnmQvQYJZANBTnhStlxZBEkXpZAblx2jVe1q4m4EfZAOMsK4Xas2ZCsV4CqnxCugEv0EMM2ihA7Ut5gJyPS0spHv2a9riMqu4fsjiNjrtxYxxZBrk07wE3b2JgpaFrGZAKMOJHFTUhboK5uZCarNBaU2nVfAGJCAeZB4BxoUw3PhTBtwarTJZBoy2RTgLvkkfz', 'social_media', isSecret: true);
        SystemSetting::set('social_media_page_status', 'active', 'social_media');

        // Threads Official Platform Settings (cooca.indonesia)
        SystemSetting::set('threads_user_id', '1585093700053942', 'social_media');
        SystemSetting::set('threads_username', 'cooca.indonesia', 'social_media');

        $this->info('Instagram, Facebook, and Threads credentials configured successfully.');

        return self::SUCCESS;
    }
}
