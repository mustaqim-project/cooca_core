<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\SocialMedia\AdminSocialMediaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSocialMediaController extends Controller
{
    public function __construct(
        protected AdminSocialMediaService $adminService
    ) {}

    /**
     * Display Social Media Platform Admin Center.
     */
    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'posts');
        $validTabs = ['posts', 'analytics', 'inbox', 'settings', 'merchants', 'app_review'];
        if (! in_array($tab, $validTabs, true)) {
            $tab = 'posts';
        }

        $platform = $this->adminService->getPlatformSettings();
        $summary = $this->adminService->getPlatformSummary();
        $merchants = $this->adminService->getConnectedMerchantsList(20);
        $platformPosts = $this->adminService->getPlatformPosts(12);
        $platformComments = $this->adminService->getPlatformComments(20);
        $platformAccounts = $this->adminService->getPlatformAccounts();
        $analytics = $this->adminService->getPlatformAnalytics($request->boolean('refresh_analytics', false));

        return view('admin.social_media.index', compact('tab', 'platform', 'summary', 'merchants', 'platformPosts', 'platformComments', 'platformAccounts', 'analytics'));
    }

    /**
     * Create and publish/schedule a new post for Cooca's official social media.
     */
    public function storePost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform'              => ['nullable', 'string', 'in:instagram,facebook,threads,tiktok,linkedin'],
            'platforms'             => ['nullable', 'array'],
            'platforms.*'           => ['string', 'in:instagram,facebook,threads,tiktok,linkedin'],
            'timing_mode'           => ['nullable', 'string', 'in:now,schedule_all,per_channel'],
            'platform_timing'       => ['nullable', 'array'],
            'platform_timing.*'     => ['nullable', 'string', 'in:now,schedule'],
            'platform_scheduled_at' => ['nullable', 'array'],
            'platform_scheduled_at.*' => ['nullable', 'date'],
            'content'               => ['nullable', 'string', 'max:5000'],
            'media_type'            => ['nullable', 'string', 'in:image,video,reels,story,carousel,text'],
            'media_url'             => ['nullable', 'url', 'max:1000'],
            'media_file'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,mp4,mov', 'max:102400'],
            'scheduled_at'          => ['nullable', 'date'],
        ]);

        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        $selectedPlatforms = ! empty($validated['platforms'])
            ? $validated['platforms']
            : (! empty($validated['platform']) ? [$validated['platform']] : ['instagram']);
        $selectedPlatforms = array_values(array_unique($selectedPlatforms));

        $mediaUrls = [];
        $localPaths = [];

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $filename = (string) \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = \App\Domain\Storage\AdminStorage::storePublicFile($file, 'social-media-assets/platform', $filename);
            $mediaUrls[] = \App\Domain\Storage\AdminStorage::publicUrl($path);
            $localPaths[] = $path;
        } elseif (! empty($validated['media_url'])) {
            $mediaUrls[] = $validated['media_url'];
        }

        $mediaType = $validated['media_type'] ?? (! empty($mediaUrls) ? 'image' : 'text');
        $content = (string) ($validated['content'] ?? '');

        if (empty($content) && empty($mediaUrls)) {
            return back()->with('error', 'Postingan memerlukan isi caption teks atau unggahan berkas media (foto/video).');
        }

        $post = $this->adminService->createAndPublishPlatformPost($admin, [
            'platforms'             => $selectedPlatforms,
            'platform'              => $selectedPlatforms[0] ?? 'instagram',
            'timing_mode'           => $validated['timing_mode'] ?? 'now',
            'platform_timing'       => $validated['platform_timing'] ?? [],
            'platform_scheduled_at' => $validated['platform_scheduled_at'] ?? [],
            'content'               => $content,
            'media_type'            => $mediaType,
            'media_urls'            => $mediaUrls,
            'local_media_paths'     => $localPaths,
            'scheduled_at'          => $validated['scheduled_at'] ?? null,
        ]);

        $platformListStr = implode(', ', array_map('ucfirst', $selectedPlatforms));

        if ($post->status === 'published') {
            return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
                ->with('success', "Postingan resmi platform berhasil dipublikasikan ke {$platformListStr}!");
        } elseif (in_array($post->status, ['scheduled', 'partially_published'], true)) {
            $schedNotice = $post->status === 'partially_published'
                ? "Sebagian saluran telah tayang langsung, dan saluran lainnya berhasil dijadwalkan!"
                : "Postingan platform ({$platformListStr}) berhasil disimpan sesuai jadwal penayangan.";
            return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
                ->with('success', $schedNotice);
        } else {
            $targetErr = $post->targets()->whereNotNull('error_message')->value('error_message');
            $errMsg = $targetErr ?: ($post->error_message ?? 'Periksa log atau kredensial akun.');

            return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
                ->with('error', 'Gagal mempublikasikan postingan: ' . $errMsg);
        }
    }

    /**
     * Retry publishing a failed platform post.
     */
    public function retryPost(\App\Models\SocialMediaPost $post): RedirectResponse
    {
        $this->adminService->retryPlatformPost($post);

        if ($post->status === 'published') {
            return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
                ->with('success', 'Postingan platform berhasil dipublikasikan ulang!');
        }

        $targetErr = $post->targets()->whereNotNull('error_message')->value('error_message');
        $errMsg = $targetErr ?: ($post->error_message ?? 'Periksa kredensial.');

        return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
            ->with('error', 'Gagal mempublikasikan ulang postingan: ' . $errMsg);
    }

    /**
     * Delete a platform post.
     */
    public function destroyPost(\App\Models\SocialMediaPost $post): RedirectResponse
    {
        $this->adminService->deletePlatformPost($post);

        return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
            ->with('success', 'Postingan platform berhasil dihapus.');
    }

    /**
     * Reply to a comment on a platform post.
     */
    public function replyComment(Request $request, \App\Models\SocialMediaComment $comment): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $admin = auth('admin')->user();
        if (! $admin) {
            abort(403);
        }

        try {
            $this->adminService->replyPlatformComment($comment, $validated['message'], $admin);

            return redirect()->route('admin.social-media.index', ['tab' => 'inbox'])
                ->with('success', 'Balasan komentar berhasil dikirimkan sebagai Cooca Indonesia!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.social-media.index', ['tab' => 'inbox'])
                ->with('error', 'Gagal membalas komentar: ' . $e->getMessage());
        }
    }

    /**
     * Redirect admin to LinkedIn OAuth 2.0 Authorization Screen.
     */
    public function getLinkedInAuthUrl(Request $request): RedirectResponse
    {
        $provider = app(\App\Domain\SocialMedia\SocialMediaManager::class)->getProvider('linkedin');
        if (! $provider->isConfigured()) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Kredensial LinkedIn Developer (Client ID & Client Secret) belum dikonfigurasi di Pengaturan Platform.');
        }

        $state = \Illuminate\Support\Str::random(40);
        $request->session()->put('admin_linkedin_oauth_state', $state);

        try {
            $redirectUri = route('admin.social-media.linkedin.callback');
            $authUrl = $provider->getAuthUrl($redirectUri, $state);

            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Gagal memulai otorisasi LinkedIn: ' . $e->getMessage());
        }
    }

    /**
     * Handle LinkedIn OAuth 2.0 callback and persist official platform connection.
     */
    public function handleLinkedInCallback(Request $request): RedirectResponse
    {
        $state = (string) $request->query('state', '');
        $savedState = (string) $request->session()->pull('admin_linkedin_oauth_state', '');

        if (empty($state) || empty($savedState) || ! hash_equals($savedState, $state)) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Validasi keamanan OAuth LinkedIn gagal (state tidak valid). Silakan coba lagi.');
        }

        $code = (string) $request->query('code', '');
        $error = (string) $request->query('error', '');
        $errorDesc = (string) $request->query('error_description', '');

        if (! empty($error)) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', "Otorisasi LinkedIn ditolak atau dibatalkan: {$errorDesc}");
        }

        if (empty($code)) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Otorisasi LinkedIn gagal: Authorization code tidak ditemukan.');
        }

        try {
            $redirectUri = route('admin.social-media.linkedin.callback');
            $authData = app(\App\Domain\SocialMedia\SocialMediaManager::class)->getProvider('linkedin')->handleAuthCallback($code, $redirectUri);

            $memberId = (string) ($authData['open_id'] ?? '');
            if (empty($memberId)) {
                throw new \RuntimeException('LinkedIn Member ID (URN) tidak ditemukan dalam respons otorisasi.');
            }

            // Persist or update platform LinkedIn account (is_platform = true, business_id = null)
            $account = \App\Models\SocialMediaAccount::updateOrCreate(
                [
                    'is_platform' => true,
                    'platform'    => 'linkedin',
                    'account_id'  => $memberId,
                ],
                [
                    'business_id'              => null,
                    'is_platform'              => true,
                    'provider'                 => 'linkedin',
                    'account_name'             => $authData['account_name'] ?? 'Cooca Official (LinkedIn)',
                    'username'                 => $authData['username'] ?? ($authData['creator_info']['email'] ?? null),
                    'profile_picture_url'      => $authData['avatar_url'] ?? null,
                    'access_token'             => $authData['access_token'],
                    'refresh_token'            => $authData['refresh_token'] ?? null,
                    'token_expires_at'         => $authData['expires_at'] ?? now()->addDays(60),
                    'refresh_token_expires_at' => $authData['refresh_token_expires_at'] ?? now()->addYear(),
                    'token_type'               => 'bearer',
                    'status'                   => 'active',
                    'metadata'                 => $authData['creator_info'] ?? [],
                    'scopes'                   => $authData['scopes'] ?? ['openid', 'profile', 'email', 'w_member_social'],
                ]
            );

            return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
                ->with('success', "Akun LinkedIn Resmi Cooca [{$account->account_name}] berhasil terhubung ke Platform!");
        } catch (\Throwable $e) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Gagal menghubungkan akun LinkedIn platform: ' . $e->getMessage());
        }
    }

    /**
     * Redirect admin to TikTok OAuth 2.0 Authorization Screen.
     */
    public function getTikTokAuthUrl(Request $request): RedirectResponse
    {
        $provider = app(\App\Domain\SocialMedia\SocialMediaManager::class)->getProvider('tiktok');
        if (! $provider->isConfigured()) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Kredensial TikTok Developer belum dikonfigurasi di Pengaturan Platform.');
        }

        $state = \Illuminate\Support\Str::random(40);
        $request->session()->put('admin_tiktok_oauth_state', $state);

        try {
            $redirectUri = route('admin.social-media.tiktok.callback');
            $authUrl = $provider->getAuthUrl($redirectUri, $state);

            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Gagal memulai otorisasi TikTok: ' . $e->getMessage());
        }
    }

    /**
     * Handle TikTok OAuth 2.0 callback and persist official platform connection.
     */
    public function handleTikTokCallback(Request $request): RedirectResponse
    {
        $state = (string) $request->query('state', '');
        $savedState = (string) $request->session()->pull('admin_tiktok_oauth_state', '');

        if (empty($state) || empty($savedState) || ! hash_equals($savedState, $state)) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Validasi keamanan OAuth TikTok gagal (state tidak valid). Silakan coba lagi.');
        }

        $code = (string) $request->query('code', '');
        $error = (string) $request->query('error', '');
        $errorDesc = (string) $request->query('error_description', '');

        if (! empty($error)) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', "Otorisasi TikTok ditolak: {$errorDesc}");
        }

        if (empty($code)) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Otorisasi TikTok gagal: Authorization code tidak ditemukan.');
        }

        try {
            $redirectUri = route('admin.social-media.tiktok.callback');
            $authData = app(\App\Domain\SocialMedia\SocialMediaManager::class)->getProvider('tiktok')->handleAuthCallback($code, $redirectUri);

            $openId = (string) ($authData['open_id'] ?? '');
            if (empty($openId)) {
                throw new \RuntimeException('TikTok OpenID tidak ditemukan dalam respons otorisasi.');
            }

            $account = \App\Models\SocialMediaAccount::updateOrCreate(
                [
                    'is_platform' => true,
                    'platform'    => 'tiktok',
                    'account_id'  => $openId,
                ],
                [
                    'business_id'              => null,
                    'is_platform'              => true,
                    'provider'                 => 'tiktok',
                    'account_name'             => $authData['account_name'] ?? 'Cooca Official (TikTok)',
                    'username'                 => $authData['username'] ?? null,
                    'profile_picture_url'      => $authData['avatar_url'] ?? null,
                    'access_token'             => $authData['access_token'],
                    'refresh_token'            => $authData['refresh_token'] ?? null,
                    'token_expires_at'         => $authData['expires_at'] ?? now()->addDay(),
                    'refresh_token_expires_at' => now()->addDays(365),
                    'token_type'               => 'bearer',
                    'status'                   => 'active',
                    'metadata'                 => $authData['creator_info'] ?? [],
                ]
            );

            return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
                ->with('success', "Akun TikTok Resmi Cooca [{$account->account_name}] berhasil terhubung ke Platform!");
        } catch (\Throwable $e) {
            return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
                ->with('error', 'Gagal menghubungkan akun TikTok platform: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect an official platform social media account.
     */
    public function disconnectAccount(Request $request, \App\Models\SocialMediaAccount $account): RedirectResponse
    {
        $accountName = $account->account_name;
        $platform = ucfirst($account->platform);

        $this->adminService->disconnectPlatformAccount($account);

        return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
            ->with('success', "Koneksi akun {$platform} [{$accountName}] berhasil diputus.");
    }

    /**
     * Save Meta Social Media Platform settings (App ID, Secret, Webhook Token, etc.).
     */
    public function updateConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_id'               => ['nullable', 'string', 'max:100'],
            'app_secret'           => ['nullable', 'string', 'max:150'],
            'webhook_verify_token' => ['nullable', 'string', 'max:150'],
            'graph_version'        => ['nullable', 'string', 'max:20'],
            'graph_url'            => ['nullable', 'url', 'max:200'],

            // TikTok
            'tiktok_client_key'    => ['nullable', 'string', 'max:100'],
            'tiktok_client_secret' => ['nullable', 'string', 'max:150'],

            // Instagram Platform
            'instagram_app_id'     => ['nullable', 'string', 'max:100'],
            'instagram_app_name'   => ['nullable', 'string', 'max:100'],
            'instagram_app_secret' => ['nullable', 'string', 'max:150'],
            'instagram_account_id' => ['nullable', 'string', 'max:100'],
            'instagram_username'   => ['nullable', 'string', 'max:100'],
            'instagram_access_token' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->adminService->savePlatformSettings($validated);

        return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
            ->with('success', 'Konfigurasi Meta App & TikTok Developer berhasil disimpan.');
    }
}
