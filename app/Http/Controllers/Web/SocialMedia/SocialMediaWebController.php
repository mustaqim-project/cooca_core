<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SocialMedia;

use App\Domain\SocialMedia\SocialMediaService;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SocialMediaWebController extends Controller
{
    public function __construct(
        protected SocialMediaService $socialService,
        protected \App\Domain\SocialMedia\SocialMediaManager $socialMediaManager
    ) {}

    /**
     * Social Media Accounts Cockpit & Onboarding.
     */
    public function index(): View
    {
        $business = Context::requireBusiness();
        $accounts = SocialMediaAccount::where('business_id', $business->id)->get();
        $summary = $this->socialService->getSummary($business);
        $recentPosts = SocialMediaPost::where('business_id', $business->id)
            ->with(['targets.account', 'account'])
            ->latest()
            ->take(5)
            ->get();

        return view('app.social_media.index', compact('business', 'accounts', 'summary', 'recentPosts'));
    }

    /**
     * JSON: Fetch Meta OAuth parameters for the popup Facebook Login for Business.
     */
    public function getOAuthConfig(Request $request): JsonResponse
    {
        $client = $this->socialService->getClient();
        $redirectUri = route('social-media.index');
        $state = csrf_token();

        if (! $client->isConfigured()) {
            return response()->json([
                'success'   => false,
                'message'   => 'Konfigurasi Meta App (App ID & Secret) belum diisi oleh Superadmin di Pengaturan Platform.',
                'app_id'    => null,
                'version'   => $client->getGraphVersion(),
                'login_url' => null,
            ]);
        }

        return response()->json([
            'success'   => true,
            'app_id'    => $client->getAppId(),
            'version'   => $client->getGraphVersion(),
            'login_url' => $client->getLoginUrl($redirectUri, $state),
        ]);
    }

    /**
     * AJAX: Exchange OAuth code or User Token for Long-Lived Token & connect Pages.
     */
    public function exchangeToken(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $client = $this->socialService->getClient();

        $validated = $request->validate([
            'code'         => ['nullable', 'string'],
            'access_token' => ['nullable', 'string'],
        ]);

        try {
            $userToken = $validated['access_token'] ?? null;

            if (empty($userToken) && ! empty($validated['code'])) {
                $tokenData = $client->exchangeCodeForUserToken($validated['code'], route('social-media.index'));
                $userToken = $tokenData['access_token'] ?? null;
            }

            if (empty($userToken)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Token otorisasi Meta tidak valid atau tidak ditemukan.',
                ], 422);
            }

            // 1. Exchange for Long-Lived User Access Token (60 days)
            $longLivedData = $client->exchangeTokenForLongLived($userToken);
            $longLivedToken = $longLivedData['access_token'] ?? $userToken;

            // 2. Fetch Facebook Pages with permanent Page Access Tokens & linked IG Accounts
            $pages = $client->getManageablePages($longLivedToken);

            if (empty($pages)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Tidak ada Facebook Page atau Instagram Bisnis yang ditemukan di akun Anda.',
                ], 422);
            }

            // 3. Persist to database with tenant isolation
            $connected = $this->socialService->connectPages($business, $pages);

            return response()->json([
                'success' => true,
                'count'   => count($connected),
                'message' => count($connected) . ' aset media sosial berhasil dihubungkan ke toko Anda.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Redirect merchant to TikTok OAuth 2.0 Authorization Screen.
     */
    public function getTikTokAuthUrl(Request $request): RedirectResponse
    {
        $provider = $this->socialMediaManager->getProvider('tiktok');
        if (! $provider->isConfigured()) {
            return redirect()->route('social-media.index')
                ->with('error', 'Kredensial TikTok Developer (Client Key & Secret) belum dikonfigurasi oleh Superadmin di Pengaturan Platform.');
        }

        $state = Str::random(40);
        $request->session()->put('tiktok_oauth_state', $state);

        try {
            $redirectUri = route('social-media.tiktok.callback');
            $authUrl = $provider->getAuthUrl($redirectUri, $state);

            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            return redirect()->route('social-media.index')
                ->with('error', 'Gagal memulai koneksi TikTok: ' . $e->getMessage());
        }
    }

    /**
     * Handle TikTok OAuth 2.0 callback and persist merchant connection.
     */
    public function handleTikTokCallback(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $state = (string) $request->query('state', '');
        $savedState = (string) $request->session()->pull('tiktok_oauth_state', '');

        if (empty($state) || empty($savedState) || ! hash_equals($savedState, $state)) {
            return redirect()->route('social-media.index')
                ->with('error', 'Validasi keamanan OAuth TikTok gagal (state tidak valid). Silakan coba lagi.');
        }

        $code = (string) $request->query('code', '');
        $error = (string) $request->query('error', '');
        $errorDesc = (string) $request->query('error_description', '');

        if (! empty($error)) {
            return redirect()->route('social-media.index')
                ->with('error', "Otorisasi TikTok ditolak atau dibatalkan: {$errorDesc}");
        }

        if (empty($code)) {
            return redirect()->route('social-media.index')
                ->with('error', 'Otorisasi TikTok gagal: Authorization code tidak ditemukan.');
        }

        try {
            $redirectUri = route('social-media.tiktok.callback');
            $authData = $this->socialMediaManager->getProvider('tiktok')->handleAuthCallback($code, $redirectUri);

            $openId = (string) ($authData['open_id'] ?? '');
            if (empty($openId)) {
                throw new \RuntimeException('TikTok OpenID tidak ditemukan dalam respons otorisasi.');
            }

            // Persist or update TikTok account with tenant isolation
            $account = SocialMediaAccount::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'platform'    => 'tiktok',
                    'account_id'  => $openId,
                ],
                [
                    'provider'                 => 'tiktok',
                    'account_name'             => $authData['account_name'] ?? 'TikTok Account',
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

            return redirect()->route('social-media.index')
                ->with('success', "Akun TikTok [{$account->account_name}] berhasil dihubungkan ke toko Anda!");
        } catch (\Throwable $e) {
            return redirect()->route('social-media.index')
                ->with('error', 'Gagal menghubungkan akun TikTok: ' . $e->getMessage());
        }
    }

    /**
     * Content Publishing Feed & Scheduler.
     */
    public function posts(Request $request): View
    {
        $business = Context::requireBusiness();
        $status = (string) $request->query('status', 'all');
        $platform = (string) $request->query('platform', 'all');

        $query = SocialMediaPost::where('business_id', $business->id)
            ->with(['account', 'targets.account', 'media'])
            ->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($platform !== 'all') {
            $query->where(function ($q) use ($platform) {
                $q->where('platform', $platform)
                    ->orWhereHas('targets', fn ($t) => $t->where('channel', $platform));
            });
        }

        $posts = $query->paginate(15);
        $accounts = SocialMediaAccount::where('business_id', $business->id)->where('status', 'active')->get();

        return view('app.social_media.posts', compact('business', 'posts', 'accounts', 'status', 'platform'));
    }

    /**
     * Unified Composer: Create and publish/schedule a new social media post across channels.
     */
    public function storePost(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'social_media_account_id'  => ['nullable', 'uuid'],
            'target_accounts'          => ['nullable', 'array'],
            'target_accounts.*'        => ['uuid'],
            'content'                  => ['required', 'string', 'max:5000'],
            'custom_captions'          => ['nullable', 'array'],
            'custom_captions.*'        => ['nullable', 'string', 'max:5000'],
            'content_types'            => ['nullable', 'array'],
            'content_types.*'          => ['nullable', 'string', 'in:feed,photo,carousel,reel,video,story,text'],
            'media_type'               => ['nullable', 'string', 'in:text,image,video,reels,carousel'],
            'media_format'             => ['nullable', 'string', 'in:photo,video,reels,text,carousel'],
            'media_file'               => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,gif,mp4,mov', 'max:102400'], // 100MB
            'media_files'              => ['nullable', 'array', 'max:10'],
            'media_files.*'            => ['file', 'mimes:jpeg,png,jpg,webp,gif,mp4,mov', 'max:102400'],
            'media_url'                => ['nullable', 'url', 'max:1000'],
            'schedule_mode'            => ['nullable', 'string', 'in:all_now,all_same,per_channel'],
            'channel_schedule_modes'   => ['nullable', 'array'],
            'channel_schedule_modes.*' => ['nullable', 'string', 'in:now,schedule'],
            'channel_scheduled_at'     => ['nullable', 'array'],
            'channel_scheduled_at.*'   => ['nullable', 'date'],
            'scheduled_at'             => ['nullable', 'date'],
        ]);

        // Resolve Target Accounts (Unified multi-select or single fallback)
        $selectedAccountIds = ! empty($validated['target_accounts'])
            ? $validated['target_accounts']
            : (! empty($validated['social_media_account_id']) ? [$validated['social_media_account_id']] : []);

        if (empty($selectedAccountIds)) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu akun/saluran media sosial tujuan.');
        }

        $accounts = SocialMediaAccount::where('business_id', $business->id)
            ->whereIn('id', $selectedAccountIds)
            ->where('status', 'active')
            ->get();

        if ($accounts->isEmpty()) {
            abort(404, 'Akun media sosial yang dipilih tidak ditemukan.');
        }

        // 1. STRICT COOCA HASHTAG VALIDATION (Max 5 Unique Hashtags per post)
        $validator = $this->socialMediaManager->getValidator();
        $hashtagValidation = $validator->validateHashtags($validated['content']);
        if (! $hashtagValidation['is_valid']) {
            return redirect()->back()->withInput()->with('error', $hashtagValidation['error']);
        }

        // 2. Validate custom captions if provided
        if (! empty($validated['custom_captions']) && is_array($validated['custom_captions'])) {
            foreach ($validated['custom_captions'] as $accId => $customCaption) {
                if (! empty($customCaption)) {
                    $customHashtagCheck = $validator->validateHashtags($customCaption);
                    if (! $customHashtagCheck['is_valid']) {
                        return redirect()->back()->withInput()->with('error', "Custom Caption: {$customHashtagCheck['error']}");
                    }
                }
            }
        }

        // 3. Handle Media Uploads (Single or Multiple for Carousel)
        $uploadedMedia = [];
        $dir = "social-media/temp/{$business->id}";

        if ($request->hasFile('media_files')) {
            $files = $request->file('media_files');
            foreach ($files as $idx => $file) {
                $mime = (string) $file->getMimeType();
                $ext = $file->getClientOriginalExtension() ?: 'bin';
                $filename = (string) Str::uuid() . '.' . $ext;
                $storedPath = $file->storeAs($dir, $filename, 'public');

                $uploadedMedia[] = [
                    'sort_order' => $idx + 1,
                    'media_type' => str_starts_with($mime, 'video/') ? 'video' : 'image',
                    'media_url'  => asset('storage/' . $storedPath),
                    'local_path' => $storedPath,
                    'file_size'  => $file->getSize(),
                ];
            }
        } elseif ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $mime = (string) $file->getMimeType();
            $ext = $file->getClientOriginalExtension() ?: 'bin';
            $filename = (string) Str::uuid() . '.' . $ext;
            $storedPath = $file->storeAs($dir, $filename, 'public');

            $uploadedMedia[] = [
                'sort_order' => 1,
                'media_type' => str_starts_with($mime, 'video/') ? 'video' : 'image',
                'media_url'  => asset('storage/' . $storedPath),
                'local_path' => $storedPath,
                'file_size'  => $file->getSize(),
            ];
        } elseif (! empty($validated['media_url'])) {
            $uploadedMedia[] = [
                'sort_order' => 1,
                'media_type' => 'image',
                'media_url'  => $validated['media_url'],
                'local_path' => null,
                'file_size'  => null,
            ];
        }

        // Determine primary media type
        $mediaFormat = $validated['media_format'] ?? null;
        $primaryMediaType = 'text';
        if (count($uploadedMedia) > 1) {
            $primaryMediaType = 'carousel';
        } elseif (count($uploadedMedia) === 1) {
            $primaryMediaType = ($mediaFormat === 'reels') ? 'reels' : ($validated['media_type'] ?? $uploadedMedia[0]['media_type']);
        }

        $timingMode = (string) ($validated['schedule_mode'] ?? (! empty($validated['scheduled_at']) ? 'all_same' : 'all_now'));
        $channelScheduleModes = (array) ($validated['channel_schedule_modes'] ?? []);
        $channelScheduledAts = (array) ($validated['channel_scheduled_at'] ?? []);
        $globalScheduledAt = ! empty($validated['scheduled_at']) ? \Illuminate\Support\Carbon::parse($validated['scheduled_at']) : null;

        $firstAccount = $accounts->first();

        // 4. Create Parent Social Media Post
        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'social_media_account_id' => $firstAccount->id,
            'platform'                => $firstAccount->platform,
            'content'                 => $validated['content'],
            'media_type'              => $primaryMediaType,
            'media_urls'              => ! empty($uploadedMedia) ? array_column($uploadedMedia, 'media_url') : null,
            'local_media_paths'       => ! empty($uploadedMedia) ? array_filter(array_column($uploadedMedia, 'local_path')) : null,
            'status'                  => 'publishing',
            'scheduled_at'            => $globalScheduledAt,
        ]);

        // 5. Create Post Media Records
        foreach ($uploadedMedia as $item) {
            \App\Models\SocialPostMedia::create([
                'social_media_post_id' => $post->id,
                'sort_order'           => $item['sort_order'],
                'media_type'           => $item['media_type'],
                'media_url'            => $item['media_url'],
                'local_path'           => $item['local_path'],
                'file_size'            => $item['file_size'],
            ]);
        }

        // 6. Create Targets for each selected account with independent schedule support
        $immediateTargets = [];
        $hasScheduledTargets = false;

        foreach ($accounts as $account) {
            $provider = $account->provider ?: ($account->platform === 'tiktok' ? 'tiktok' : 'meta');
            $channel = $account->platform;

            // Resolve content type for channel
            $contentType = $validated['content_types'][$account->id] ?? null;
            if (empty($contentType)) {
                if ($channel === 'instagram') {
                    $contentType = count($uploadedMedia) > 1 ? 'carousel' : ($primaryMediaType === 'video' ? 'reel' : 'photo');
                } elseif ($channel === 'tiktok') {
                    $contentType = $primaryMediaType === 'video' ? 'video' : 'photo';
                } elseif ($channel === 'facebook') {
                    $contentType = $primaryMediaType === 'video' ? 'video' : 'feed';
                } else {
                    $contentType = $primaryMediaType;
                }
            }

            $customCaption = $validated['custom_captions'][$account->id] ?? null;

            // Resolve target timing
            $targetTiming = 'now';
            $targetSchedTime = null;

            if ($timingMode === 'per_channel') {
                $targetTiming = $channelScheduleModes[$account->id] ?? 'now';
                if ($targetTiming === 'schedule' && ! empty($channelScheduledAts[$account->id])) {
                    $targetSchedTime = \Illuminate\Support\Carbon::parse($channelScheduledAts[$account->id]);
                }
            } elseif ($timingMode === 'all_same') {
                $targetTiming = 'schedule';
                $targetSchedTime = $globalScheduledAt;
            }

            $isTargetScheduled = ($targetTiming === 'schedule') && $targetSchedTime && $targetSchedTime->isFuture();
            if ($isTargetScheduled) {
                $hasScheduledTargets = true;
            }

            $target = \App\Models\SocialPostTarget::create([
                'social_media_post_id'    => $post->id,
                'social_media_account_id' => $account->id,
                'provider'                => $provider,
                'channel'                 => $channel,
                'content_type'            => $contentType,
                'custom_caption'          => $customCaption ?: null,
                'status'                  => $isTargetScheduled ? 'scheduled' : 'pending',
                'scheduled_at'            => $isTargetScheduled ? $targetSchedTime : null,
                'retry_count'             => 0,
            ]);

            if (! $isTargetScheduled) {
                $immediateTargets[] = $target;
            }
        }

        // 7. Dispatch or Execute Immediate Targets
        if (count($immediateTargets) === 1 && $accounts->count() === 1) {
            // Single target immediate execution
            $this->socialService->publishPost($business, $post);
            $immediateTargets[0]->update([
                'status'           => $post->status,
                'platform_post_id' => $post->platform_post_id,
                'published_at'     => $post->published_at,
                'error_message'    => $post->error_message,
            ]);
        } elseif (! empty($immediateTargets)) {
            // Multi-target immediate dispatch
            foreach ($immediateTargets as $immTarget) {
                \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($immTarget->id);
            }
        }

        $post->syncStatusFromTargets();

        // 8. User feedback response
        if ($hasScheduledTargets && ! empty($immediateTargets)) {
            return redirect()->route('social-media.posts.index')
                ->with('success', 'Sebagian saluran berhasil dikirim untuk dipublikasikan langsung, dan saluran lainnya dijadwalkan sesuai waktu yang ditentukan.');
        } elseif ($hasScheduledTargets) {
            return redirect()->route('social-media.posts.index')
                ->with('success', 'Postingan berhasil dijadwalkan ke ' . $accounts->count() . ' saluran! Eksekusi otomatis akan dilakukan oleh cron scheduler.');
        }

        if ($accounts->count() === 1 && $post->status === 'published') {
            return redirect()->route('social-media.posts.index')
                ->with('success', "Postingan ({$primaryMediaType}) berhasil dipublikasikan ke {$firstAccount->platform}!");
        }

        return redirect()->route('social-media.posts.index')
            ->with('success', 'Postingan sedang dipublikasikan ke ' . $accounts->count() . ' saluran di latar belakang.');
    }

    /**
     * Retry publishing a failed target.
     */
    public function retryTarget(Request $request, \App\Models\SocialPostTarget $target): RedirectResponse
    {
        $business = Context::requireBusiness();

        $post = $target->post;
        if (! $post || $post->business_id !== $business->id) {
            abort(403, 'Akses tidak diizinkan untuk target postingan ini.');
        }

        if (! $target->canRetry()) {
            return redirect()->back()->with('error', 'Target postingan ini tidak dalam status gagal.');
        }

        $target->update([
            'status'        => 'pending',
            'error_message' => null,
        ]);

        $post->update(['status' => 'publishing']);

        \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($target->id);

        return redirect()->back()->with('success', "Memulai ulang publikasi untuk target {$target->channel}...");
    }

    /**
     * Content Scheduling Calendar.
     */
    public function calendar(Request $request): View
    {
        $business = Context::requireBusiness();
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $startDate = \Illuminate\Support\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $posts = SocialMediaPost::where('business_id', $business->id)
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->with(['targets.account', 'account'])
            ->orderBy('scheduled_at')
            ->get();

        return view('app.social_media.calendar', compact('business', 'posts', 'startDate', 'month', 'year'));
    }

    /**
     * Inbox & Comments Management.
     */
    public function inbox(Request $request): View
    {
        $business = Context::requireBusiness();
        $status = (string) $request->query('status', 'all');

        $query = SocialMediaComment::where('business_id', $business->id)
            ->where('is_from_page', false)
            ->with(['account', 'post'])
            ->latest('created_time');

        if ($status === 'unread') {
            $query->where('status', 'unread');
        } elseif ($status === 'replied') {
            $query->where('status', 'replied');
        }

        $comments = $query->paginate(20);

        return view('app.social_media.inbox', compact('business', 'comments', 'status'));
    }

    /**
     * AJAX: Reply to a comment.
     */
    public function replyComment(Request $request, SocialMediaComment $comment): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $reply = $this->socialService->replyComment($business, $comment, $validated['message']);

            return response()->json([
                'success' => true,
                'message' => 'Balasan berhasil dikirim.',
                'reply'   => $reply,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Insights & Analytics.
     */
    public function insights(Request $request): View
    {
        $business = Context::requireBusiness();

        $posts = SocialMediaPost::where('business_id', $business->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->take(15)
            ->get();

        $totalImpressions = 0;
        $totalReach = 0;
        $totalLikes = 0;
        $totalComments = 0;
        $totalShares = 0;

        foreach ($posts as $p) {
            $totalImpressions += $p->getMetric('impressions');
            $totalReach += $p->getMetric('reach');
            $totalLikes += $p->getMetric('likes');
            $totalComments += $p->getMetric('comments');
            $totalShares += $p->getMetric('shares');
        }

        $analytics = [
            'total_impressions' => $totalImpressions,
            'total_reach'       => $totalReach,
            'total_engagement'  => $totalLikes + $totalComments + $totalShares,
            'total_likes'       => $totalLikes,
            'total_comments'    => $totalComments,
            'total_shares'      => $totalShares,
        ];

        return view('app.social_media.insights', compact('business', 'posts', 'analytics'));
    }

    /**
     * AJAX: Sync live metrics for a post.
     */
    public function syncInsights(Request $request, SocialMediaPost $post): JsonResponse
    {
        $business = Context::requireBusiness();

        try {
            $metrics = $this->socialService->syncPostMetrics($business, $post);

            return response()->json([
                'success' => true,
                'metrics' => $metrics,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Disconnect social media account.
     */
    public function disconnect(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'account_id' => ['required', 'uuid'],
        ]);

        $ok = $this->socialService->disconnectAccount($business, $validated['account_id']);

        return response()->json([
            'success' => $ok,
            'message' => $ok ? 'Akun berhasil diputuskan.' : 'Akun tidak ditemukan.',
        ]);
    }
}
