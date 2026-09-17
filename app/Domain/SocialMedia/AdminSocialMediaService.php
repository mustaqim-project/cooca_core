<?php

declare(strict_types=1);

namespace App\Domain\SocialMedia;

use App\Models\Business;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaPost;
use App\Models\SystemSetting;

class AdminSocialMediaService
{
    /**
     * Get platform-wide Meta Social Media configuration settings.
     */
    public function getPlatformSettings(): array
    {
        return [
            'app_id'               => (string) SystemSetting::get('social_media_app_id', ''),
            'app_secret'           => (string) SystemSetting::get('social_media_app_secret', ''),
            'webhook_verify_token' => (string) SystemSetting::get('social_media_webhook_verify_token', 'cooca_meta_social_webhook_token'),
            'graph_version'        => (string) SystemSetting::get('social_media_graph_version', 'v21.0'),
            'graph_url'            => (string) SystemSetting::get('social_media_graph_url', 'https://graph.facebook.com'),
            'webhook_url'          => url('/api/v1/social-media/meta/webhook'),

            // TikTok Settings
            'tiktok_client_key'    => (string) SystemSetting::get('tiktok_client_key', ''),
            'tiktok_client_secret' => (string) SystemSetting::get('tiktok_client_secret', ''),
            'tiktok_redirect_uri'  => route('social-media.tiktok.callback'),
        ];
    }

    /**
     * Save platform Meta & TikTok Social Media configuration settings.
     */
    public function savePlatformSettings(array $data): void
    {
        if (array_key_exists('app_id', $data)) {
            SystemSetting::set('social_media_app_id', trim((string) $data['app_id']));
        }
        if (array_key_exists('app_secret', $data) && ! empty($data['app_secret'])) {
            SystemSetting::set('social_media_app_secret', trim((string) $data['app_secret']), group: 'social_media', isSecret: true);
        }
        if (array_key_exists('webhook_verify_token', $data)) {
            SystemSetting::set('social_media_webhook_verify_token', trim((string) $data['webhook_verify_token']));
        }
        if (array_key_exists('graph_version', $data)) {
            SystemSetting::set('social_media_graph_version', trim((string) $data['graph_version']));
        }
        if (array_key_exists('graph_url', $data)) {
            SystemSetting::set('social_media_graph_url', trim((string) $data['graph_url']));
        }

        // TikTok Settings
        if (array_key_exists('tiktok_client_key', $data)) {
            SystemSetting::set('tiktok_client_key', trim((string) $data['tiktok_client_key']));
        }
        if (array_key_exists('tiktok_client_secret', $data) && ! empty($data['tiktok_client_secret'])) {
            SystemSetting::set('tiktok_client_secret', trim((string) $data['tiktok_client_secret']), group: 'social_media', isSecret: true);
        }
    }

    /**
     * Get overall platform statistics for superadmin dashboard.
     */
    public function getPlatformSummary(): array
    {
        $accounts = SocialMediaAccount::all();

        return [
            'total_connected_merchants' => $accounts->pluck('business_id')->unique()->count(),
            'total_accounts'            => $accounts->count(),
            'facebook_pages_count'      => $accounts->where('platform', 'facebook')->count(),
            'instagram_accounts_count'  => $accounts->where('platform', 'instagram')->count(),
            'threads_accounts_count'    => $accounts->where('platform', 'threads')->count(),
            'tiktok_accounts_count'     => $accounts->where('platform', 'tiktok')->count(),
            'total_posts'               => SocialMediaPost::where('status', 'published')->count(),
        ];
    }

    /**
     * Get paginated merchants with their social media connection statuses.
     */
    public function getConnectedMerchantsList(int $perPage = 15)
    {
        return Business::with(['socialMediaAccounts' => function ($q) {
            $q->where('status', 'active');
        }])
        ->whereHas('socialMediaAccounts')
        ->paginate($perPage);
    }
}
