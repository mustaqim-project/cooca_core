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
        SystemSetting::set('instagram_access_token', 'EAAUrMrnYomIBSnHFS1cqo1lWu4ASkv40YKkkZC7GRsejz4bEwKwwKDYlMpmrig9HsNfPB4zrY72exKIkGpgum5QIU1vw5sJOLqwjah14557GKCIBjdM87XXMFHwcWOiynb7fOt99eRwDuRbmCAKMrBjixqjbyPbK22hZAoNrIZCuKD4mVmAsvED3DyofQhZCvxBLR3g33D7AcRwXUcANPtBX4cWQflpSMENxwpuqAy233n5ZABN0MX6XLZBq0ZD', 'social_media', isSecret: true);
        SystemSetting::set('instagram_status', 'active', 'social_media');
        SystemSetting::set('instagram_verified_at', now()->toIso8601String(), 'social_media');

        // Facebook Official Page Settings (Cooca Indonesia)
        SystemSetting::set('social_media_app_token', 'EAAUrMrnYomIBSgRg9vc9IWZB2GacTjr2YQ5Na12pdJvmpVPr5yD0KgtfanHsICHwMPyB6XVuqrLcr1ZCHPFQNjheRBhZA4Y9aBh3RX80Wq2pArLdlq2taM1ZBFFrs9Uq5xvmwz3QeiRAUaGx4wS95sNCbdNxav8HP3FpG1XsQqzZBybGIfNelXFH41YXTc2clzCg9HZCZCEoxf4XZCEgzeTQDuzzD0gqIMQZAgZAO3VqAGMYX2EDQGe1f5', 'social_media', isSecret: true);
        SystemSetting::set('social_media_page_id', '1340316975827711', 'social_media');
        SystemSetting::set('social_media_page_name', 'Cooca Indonesia', 'social_media');
        SystemSetting::set('social_media_page_token', 'EAAUrMrnYomIBSnHFS1cqo1lWu4ASkv40YKkkZC7GRsejz4bEwKwwKDYlMpmrig9HsNfPB4zrY72exKIkGpgum5QIU1vw5sJOLqwjah14557GKCIBjdM87XXMFHwcWOiynb7fOt99eRwDuRbmCAKMrBjixqjbyPbK22hZAoNrIZCuKD4mVmAsvED3DyofQhZCvxBLR3g33D7AcRwXUcANPtBX4cWQflpSMENxwpuqAy233n5ZABN0MX6XLZBq0ZD', 'social_media', isSecret: true);
        SystemSetting::set('social_media_page_status', 'active', 'social_media');

        // Threads Official Platform Settings (cooca.indonesia)
        SystemSetting::set('threads_user_id', '1585093700053942', 'social_media');
        SystemSetting::set('threads_username', 'cooca.indonesia', 'social_media');

        $this->info('Instagram, Facebook, and Threads credentials configured successfully.');

        return self::SUCCESS;
    }
}
