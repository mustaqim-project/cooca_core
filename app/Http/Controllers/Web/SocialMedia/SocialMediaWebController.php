<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\SocialMedia;

use App\Domain\SocialMedia\SocialMediaService;
use App\Http\Controllers\Controller;
use App\Models\SocialMediaAccount;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\StorageFile;
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
                'message'   => __('social_media.meta_not_configured'),
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
                    'error'   => __('social_media.meta_token_invalid'),
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
                    'error'   => __('social_media.no_pages_found'),
                ], 422);
            }

            // 3. Persist to database with tenant isolation
            $connected = $this->socialService->connectPages($business, $pages);

            return response()->json([
                'success' => true,
                'count'   => count($connected),
                'message' => __('social_media.assets_connected_count', ['count' => count($connected)]),
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
                ->with('error', __('social_media.tiktok_not_configured'));
        }

        $state = Str::random(40);
        $request->session()->put('tiktok_oauth_state', $state);

        try {
            $redirectUri = route('social-media.tiktok.callback');
            $authUrl = $provider->getAuthUrl($redirectUri, $state);

            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.tiktok_connection_failed', ['error' => $e->getMessage()]));
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
                ->with('error', __('social_media.tiktok_oauth_invalid_state'));
        }

        $code = (string) $request->query('code', '');
        $error = (string) $request->query('error', '');
        $errorDesc = (string) $request->query('error_description', '');

        if (! empty($error)) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.tiktok_oauth_denied', ['error' => $errorDesc ?: $error]));
        }

        if (empty($code)) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.tiktok_oauth_code_missing'));
        }

        try {
            $redirectUri = route('social-media.tiktok.callback');
            $authData = $this->socialMediaManager->getProvider('tiktok')->handleAuthCallback($code, $redirectUri);

            $openId = (string) ($authData['open_id'] ?? '');
            if (empty($openId)) {
                throw new \RuntimeException(__('social_media.tiktok_openid_missing'));
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
                ->with('success', __('social_media.account_connected', ['platform' => 'TikTok', 'username' => $account->account_name]));
        } catch (\Throwable $e) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.tiktok_connect_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Redirect merchant to LinkedIn OAuth 2.0 Authorization Screen.
     */
    public function getLinkedInAuthUrl(Request $request): RedirectResponse
    {
        $provider = $this->socialMediaManager->getProvider('linkedin');
        if (! $provider->isConfigured()) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.linkedin_not_configured'));
        }

        $state = Str::random(40);
        $request->session()->put('linkedin_oauth_state', $state);

        try {
            $redirectUri = route('social-media.linkedin.callback');
            $authUrl = $provider->getAuthUrl($redirectUri, $state);

            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.linkedin_connection_failed', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Handle LinkedIn OAuth 2.0 callback and persist merchant connection.
     */
    public function handleLinkedInCallback(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $state = (string) $request->query('state', '');
        $savedState = (string) $request->session()->pull('linkedin_oauth_state', '');

        if (empty($state) || empty($savedState) || ! hash_equals($savedState, $state)) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.linkedin_oauth_invalid_state'));
        }

        $code = (string) $request->query('code', '');
        $error = (string) $request->query('error', '');
        $errorDesc = (string) $request->query('error_description', '');

        if (! empty($error)) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.linkedin_oauth_denied', ['error' => $errorDesc ?: $error]));
        }

        if (empty($code)) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.linkedin_oauth_code_missing'));
        }

        try {
            $redirectUri = route('social-media.linkedin.callback');
            $authData = $this->socialMediaManager->getProvider('linkedin')->handleAuthCallback($code, $redirectUri);

            $memberId = (string) ($authData['open_id'] ?? '');
            if (empty($memberId)) {
                throw new \RuntimeException(__('social_media.linkedin_member_id_missing'));
            }

            // Persist or update LinkedIn account with tenant isolation
            $account = SocialMediaAccount::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'platform'    => 'linkedin',
                    'account_id'  => $memberId,
                ],
                [
                    'provider'                 => 'linkedin',
                    'account_name'             => $authData['account_name'] ?? 'LinkedIn Member',
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

            return redirect()->route('social-media.index')
                ->with('success', __('social_media.account_connected', ['platform' => 'LinkedIn', 'username' => $account->account_name]));
        } catch (\Throwable $e) {
            return redirect()->route('social-media.index')
                ->with('error', __('social_media.linkedin_connect_failed', ['error' => $e->getMessage()]));
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

        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        $canSchedulePost = $entitlement->canScheduleSocialPostThisMonth($business);
        $hasSocialAddon = $entitlement->hasActiveSocialMediaSubscription($business);
        $postsUsedThisMonth = $entitlement->getMonthlyUsage($business, \App\Models\QuotaMonthlyUsage::TYPE_SOCIAL_POST);
        $socialPostLimit = \App\Domain\Billing\EntitlementService::FREE_SOCIAL_POST_MONTHLY_LIMIT;

        return view('app.social_media.posts', compact(
            'business', 'posts', 'accounts', 'status', 'platform',
            'canSchedulePost', 'hasSocialAddon', 'postsUsedThisMonth', 'socialPostLimit'
        ));
    }

    /**
     * Unified Composer: Create and publish/schedule a new social media post across channels.
     */
    public function storePost(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $entitlement = app(\App\Domain\Billing\EntitlementService::class);
        if (! $entitlement->canScheduleSocialPostThisMonth($business)) {
            return redirect()->back()->withInput()->with('error', __('social_media.quota_exceeded', ['used' => 3, 'limit' => 3]));
        }

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

        // Anti-SSRF Validation for external media URLs
        if (! empty($validated['media_url'])) {
            $url = trim($validated['media_url']);
            $parsed = parse_url($url);
            $scheme = strtolower($parsed['scheme'] ?? '');
            if ($scheme !== 'https') {
                return redirect()->back()->withInput()->with('error', __('social_media.media_url_https_required'));
            }

            $host = $parsed['host'] ?? '';
            if (empty($host)) {
                return redirect()->back()->withInput()->with('error', __('social_media.media_url_hostname_invalid'));
            }

            // Immediately block localhost, loopbacks and unspecified
            if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
                return redirect()->back()->withInput()->with('error', __('social_media.media_url_private_ip_blocked'));
            }

            $ip = gethostbyname($host);
            if ($ip === $host && ! filter_var($ip, FILTER_VALIDATE_IP)) {
                return redirect()->back()->withInput()->with('error', __('social_media.media_url_host_resolution_failed'));
            }

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return redirect()->back()->withInput()->with('error', __('social_media.media_url_private_ip_blocked'));
            }

            // Cloud metadata block (169.254.x.x link-local)
            if (str_starts_with($ip, '169.254.')) {
                return redirect()->back()->withInput()->with('error', __('social_media.media_url_cloud_metadata_blocked'));
            }
        }

        // Resolve Target Accounts (Unified multi-select or single fallback)
        $selectedAccountIds = ! empty($validated['target_accounts'])
            ? $validated['target_accounts']
            : (! empty($validated['social_media_account_id']) ? [$validated['social_media_account_id']] : []);

        if (empty($selectedAccountIds)) {
            return redirect()->back()->withInput()->with('error', __('social_media.min_one_target_channel_required'));
        }

        $accounts = SocialMediaAccount::where('business_id', $business->id)
            ->whereIn('id', $selectedAccountIds)
            ->where('status', 'active')
            ->get();

        if ($accounts->isEmpty()) {
            abort(404, __('social_media.selected_account_not_found'));
        }

        // 1. STRICT COOCA VALIDATION: Caption Length & Max 5 Hashtags (Global & Per-Channel)
        $validator = $this->socialMediaManager->getValidator();
        $globalHashtagValidation = $validator->validateHashtags($validated['content']);
        if (! $globalHashtagValidation['is_valid']) {
            return redirect()->back()->withInput()->with('error', $globalHashtagValidation['error']);
        }

        // 2. Validate per-channel effective captions (Official API Limits & Separate Caption Support)
        foreach ($accounts as $account) {
            $channel = strtolower((string) $account->platform);
            $customCaption = $validated['custom_captions'][$account->id] ?? null;
            $effectiveCaption = (! empty($customCaption) && trim($customCaption) !== '')
                ? trim($customCaption)
                : trim($validated['content']);

            $captionLength = mb_strlen($effectiveCaption, 'UTF-8');
            $maxLimit = $validator->getCaptionLimit($channel);

            // Threads 500 character strict API guardrail
            if ($channel === 'threads' && $captionLength > 500) {
                return redirect()->back()->withInput()->with(
                    'error',
                    __('social_media.threads_caption_over_limit', ['count' => $captionLength])
                );
            }

            // General channel limit guardrail
            if ($captionLength > $maxLimit) {
                return redirect()->back()->withInput()->with(
                    'error',
                    __('social_media.channel_caption_over_limit', [
                        'account' => $account->account_name,
                        'channel' => $channel,
                        'count'   => $captionLength,
                        'limit'   => $maxLimit,
                    ])
                );
            }

            // Validate hashtags on custom caption if provided
            if (! empty($customCaption) && trim($customCaption) !== '') {
                $customHashtagCheck = $validator->validateHashtags($customCaption);
                if (! $customHashtagCheck['is_valid']) {
                    return redirect()->back()->withInput()->with(
                        'error',
                        __('social_media.custom_caption_error_prefix', [
                            'account' => $account->account_name,
                            'channel' => $channel,
                            'error'   => $customHashtagCheck['error'],
                        ])
                    );
                }
            }
        }

        // 3. Handle Media Uploads (Single or Multiple for Carousel)
        $uploadedMedia = [];
        $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_SOCIAL_MEDIA);
        $trackingService = app(StorageTrackingService::class);
        $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);

        if ($request->hasFile('media_files')) {
            $files = $request->file('media_files');

            foreach ($files as $idx => $file) {
                $mime = (string) $file->getMimeType();
                $ext = $file->getClientOriginalExtension() ?: 'bin';
                $filename = (string) Str::uuid() . '.' . $ext;
                $storedPath = $file->storeAs($dir, $filename, 'public');

                if ($owner) {
                    $trackingService->recordUpload(
                        file: $file,
                        filePath: $storedPath,
                        category: StorageFile::CATEGORY_SOCIAL_MEDIA,
                        module: 'social_media',
                        owner: $owner,
                        business: $business,
                        uploader: $request->user(),
                        isTemporary: true
                    );
                }

                $uploadedMedia[] = [
                    'sort_order' => $idx + 1,
                    'media_type' => str_starts_with($mime, 'video/') ? 'video' : 'image',
                    'media_url'  => TenantStorage::url($storedPath) ?? asset('storage/' . $storedPath),
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

            if ($owner) {
                $trackingService->recordUpload(
                    file: $file,
                    filePath: $storedPath,
                    category: StorageFile::CATEGORY_SOCIAL_MEDIA,
                    module: 'social_media',
                    owner: $owner,
                    business: $business,
                    uploader: $request->user(),
                    isTemporary: true
                );
            }

            $uploadedMedia[] = [
                'sort_order' => 1,
                'media_type' => str_starts_with($mime, 'video/') ? 'video' : 'image',
                'media_url'  => TenantStorage::url($storedPath) ?? asset('storage/' . $storedPath),
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

        // 4. Maker-Checker & Bank Phishing Heuristic Evaluation
        $currentUser = $request->user();
        $isOwnerOrManager = Context::isAdminOrOwner()
            || in_array(Context::role(), ['owner', 'admin', 'manager', 'store_manager'], true);

        $riskFlags = [];

        // Heuristic Scan: Rekening Bank Liar
        $detectedBankAccounts = $this->scanBankAccountsInCaption($validated['content']);
        if (! empty($detectedBankAccounts)) {
            $registeredAccounts = array_filter(array_merge(
                [$business->bank_account_number],
                \App\Models\CommercePaymentMethod::where('business_id', $business->id)->pluck('account_number')->all()
            ));
            $normalizedRegistered = array_filter(array_map(
                fn ($acc) => preg_replace('/[^0-9]/', '', (string) $acc),
                $registeredAccounts
            ));

            $unauthorized = array_diff($detectedBankAccounts, $normalizedRegistered);
            if (! empty($unauthorized)) {
                $riskFlags[] = 'unregistered_bank_account_detected';
            }
        }

        // Determine approval status:
        // Staf non-owner/manager, ATAU postingan memuat nomor rekening bank tak terdaftar mewajibkan approval
        $requiresApproval = (! $isOwnerOrManager) || in_array('unregistered_bank_account_detected', $riskFlags, true);
        $approvalStatus = $requiresApproval ? 'pending_review' : 'approved';

        // 5. Create Parent Social Media Post with Audit Trail
        $post = SocialMediaPost::create([
            'business_id'             => $business->id,
            'user_id'                 => $currentUser?->id,
            'social_media_account_id' => $firstAccount->id,
            'platform'                => $firstAccount->platform,
            'content'                 => $validated['content'],
            'media_type'              => $primaryMediaType,
            'media_urls'              => ! empty($uploadedMedia) ? array_column($uploadedMedia, 'media_url') : null,
            'local_media_paths'       => ! empty($uploadedMedia) ? array_filter(array_column($uploadedMedia, 'local_path')) : null,
            'status'                  => ($approvalStatus === 'pending_review') ? 'pending_review' : 'publishing',
            'approval_status'         => $approvalStatus,
            'reviewed_by'             => ($approvalStatus === 'approved') ? $currentUser?->id : null,
            'reviewed_at'             => ($approvalStatus === 'approved') ? now() : null,
            'risk_flags'              => ! empty($riskFlags) ? $riskFlags : null,
            'scheduled_at'            => $globalScheduledAt,
        ]);

        // 6. Create Post Media Records
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

        // 7. Create Targets for each selected account with independent schedule support
        $immediateTargets = [];
        $hasScheduledTargets = false;

        foreach ($accounts as $account) {
            $provider = $account->provider ?: ($account->platform === 'tiktok' ? 'tiktok' : ($account->platform === 'linkedin' ? 'linkedin' : 'meta'));
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
                } elseif ($channel === 'linkedin') {
                    $contentType = $primaryMediaType === 'video' ? 'video' : ($primaryMediaType === 'image' || $primaryMediaType === 'photo' ? 'photo' : 'text');
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

            $targetStatus = ($approvalStatus === 'pending_review')
                ? 'pending_review'
                : ($isTargetScheduled ? 'scheduled' : 'pending');

            $target = \App\Models\SocialPostTarget::create([
                'social_media_post_id'    => $post->id,
                'social_media_account_id' => $account->id,
                'provider'                => $provider,
                'channel'                 => $channel,
                'content_type'            => $contentType,
                'custom_caption'          => $customCaption ?: null,
                'status'                  => $targetStatus,
                'scheduled_at'            => $isTargetScheduled ? $targetSchedTime : null,
                'retry_count'             => 0,
            ]);

            if ($approvalStatus === 'approved' && ! $isTargetScheduled) {
                $immediateTargets[] = $target;
            }
        }

        // 8. Handle Maker-Checker Hold
        if ($approvalStatus === 'pending_review') {
            $post->syncStatusFromTargets();

            // Track quota usage for free tier businesses
            if (! $entitlement->hasActiveSocialMediaSubscription($business)) {
                $entitlement->incrementMonthlyUsage($business, \App\Models\QuotaMonthlyUsage::TYPE_SOCIAL_POST, \App\Domain\Billing\EntitlementService::FREE_SOCIAL_POST_MONTHLY_LIMIT);
                $entitlement->clearUsageCache($business);
            }

            $warningMessage = in_array('unregistered_bank_account_detected', $riskFlags, true)
                ? __('social_media.post_held_unregistered_bank')
                : __('social_media.post_pending_manager_approval');

            return redirect()->route('social-media.posts.index')
                ->with('warning', $warningMessage);
        }

        // 9. Dispatch or Execute Immediate Targets
        if (count($immediateTargets) === 1 && $accounts->count() === 1 && in_array($firstAccount->platform, ['facebook', 'instagram', 'threads'])) {
            // Single target immediate execution for Meta
            $this->socialService->publishPost($business, $post);
            $immediateTargets[0]->update([
                'status'           => $post->status,
                'platform_post_id' => $post->platform_post_id,
                'published_at'     => $post->published_at,
                'error_message'    => $post->error_message,
            ]);
        } elseif (! empty($immediateTargets)) {
            // Multi-target or standalone omnichannel (TikTok, LinkedIn) immediate dispatch
            foreach ($immediateTargets as $immTarget) {
                \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($immTarget->id);
            }
        }

        $post->syncStatusFromTargets();

        // 10. Track quota usage for free tier businesses
        if (! $entitlement->hasActiveSocialMediaSubscription($business)) {
            $entitlement->incrementMonthlyUsage($business, \App\Models\QuotaMonthlyUsage::TYPE_SOCIAL_POST, \App\Domain\Billing\EntitlementService::FREE_SOCIAL_POST_MONTHLY_LIMIT);
            $entitlement->clearUsageCache($business);
        }

        // 11. User feedback response
        if ($hasScheduledTargets && ! empty($immediateTargets)) {
            return redirect()->route('social-media.posts.index')
                ->with('success', __('social_media.post_published'));
        } elseif ($hasScheduledTargets) {
            return redirect()->route('social-media.posts.index')
                ->with('success', __('social_media.post_scheduled', ['time' => $validated['scheduled_at'] ?? __('social_media.default_schedule_time')]));
        }

        if ($accounts->count() === 1 && $post->status === 'failed') {
            return redirect()->route('social-media.posts.index')
                ->with('error', $post->error_message ?? __('common.unauthorized'));
        }

        if ($accounts->count() === 1 && $post->status === 'published') {
            return redirect()->route('social-media.posts.index')
                ->with('success', __('social_media.post_published'));
        }

        return redirect()->route('social-media.posts.index')
            ->with('success', __('social_media.post_published'));
    }

    /**
     * Retry publishing a failed target.
     */
    public function retryTarget(Request $request, \App\Models\SocialPostTarget $target): RedirectResponse
    {
        $business = Context::requireBusiness();

        $post = $target->post;
        if (! $post || $post->business_id !== $business->id) {
            abort(403, __('social_media.target_access_denied'));
        }

        if (! $target->canRetry()) {
            return redirect()->back()->with('error', __('social_media.target_not_in_failed_state'));
        }

        $target->update([
            'status'        => 'pending',
            'error_message' => null,
        ]);

        $post->update(['status' => 'publishing']);

        \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($target->id);

        return redirect()->back()->with('success', __('social_media.retrying_target', ['channel' => $target->channel]));
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
                'message' => __('social_media.reply_sent_successfully'),
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
            'message' => $ok ? __('social_media.account_disconnected_success') : __('social_media.account_not_found'),
        ]);
    }

    /**
     * Maker-Checker: Approve a pending social media post and dispatch publishing.
     */
    public function approvePost(Request $request, SocialMediaPost $post): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($post->business_id === $business->id, 404);

        $isOwnerOrManager = Context::isAdminOrOwner()
            || in_array(Context::role(), ['owner', 'admin', 'manager', 'store_manager'], true)
            || Context::hasPermission('social_media.manage');

        abort_unless($isOwnerOrManager, 403, __('social_media.approval_permission_denied'));

        if ($post->approval_status === 'approved') {
            return redirect()->route('social-media.posts.index')
                ->with('info', __('social_media.post_already_approved'));
        }

        $post->update([
            'approval_status' => 'approved',
            'reviewed_by'     => $request->user()?->id,
            'reviewed_at'     => now(),
            'status'          => 'publishing',
        ]);

        $post->load(['targets.account']);

        $immediateTargets = [];
        $hasScheduledTargets = false;

        foreach ($post->targets as $target) {
            $isScheduled = $target->scheduled_at && $target->scheduled_at->isFuture();
            $newStatus = $isScheduled ? 'scheduled' : 'pending';
            if ($isScheduled) {
                $hasScheduledTargets = true;
            } else {
                $immediateTargets[] = $target;
            }
            $target->update(['status' => $newStatus]);
        }

        $firstAccount = $post->account;

        if (count($immediateTargets) === 1 && $post->targets->count() === 1 && in_array($firstAccount?->platform, ['facebook', 'instagram', 'threads'])) {
            $this->socialService->publishPost($business, $post);
            $immediateTargets[0]->update([
                'status'           => $post->status,
                'platform_post_id' => $post->platform_post_id,
                'published_at'     => $post->published_at,
                'error_message'    => $post->error_message,
            ]);
        } elseif (! empty($immediateTargets)) {
            foreach ($immediateTargets as $immTarget) {
                \App\Jobs\SocialMedia\PublishSocialMediaTargetJob::dispatch($immTarget->id);
            }
        }

        $post->syncStatusFromTargets();

        return redirect()->route('social-media.posts.index')
            ->with('success', __('social_media.post_approved_and_processing'));
    }

    /**
     * Maker-Checker: Reject a pending social media post with reason.
     */
    public function rejectPost(Request $request, SocialMediaPost $post): RedirectResponse
    {
        $business = Context::requireBusiness();
        abort_unless($post->business_id === $business->id, 404);

        $isOwnerOrManager = Context::isAdminOrOwner()
            || in_array(Context::role(), ['owner', 'admin', 'manager', 'store_manager'], true)
            || Context::hasPermission('social_media.manage');

        abort_unless($isOwnerOrManager, 403, __('social_media.rejection_permission_denied'));

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $post->update([
            'approval_status'  => 'rejected',
            'reviewed_by'      => $request->user()?->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $validated['reason'] ?? __('social_media.rejected_by_management'),
            'status'           => 'rejected',
        ]);

        $post->targets()->update(['status' => 'cancelled']);

        return redirect()->route('social-media.posts.index')
            ->with('info', __('social_media.post_rejected_info'));
    }

    /**
     * Scan caption for potential bank account numbers.
     *
     * @return array<int, string>
     */
    protected function scanBankAccountsInCaption(string $text): array
    {
        $detected = [];

        // 1. Keyword-adjacent sequences: rek/rekening/bca/mandiri/bri/bni/bsi/cimb/permata/danamon/bank/an
        if (preg_match_all('/(?:rek(?:ening)?|transfer|bca|mandiri|bri|bni|bsi|cimb|permata|danamon|bank|a\.?n\.?)\s*[:\-\.]?\s*([0-9\-\. ]{8,22})/i', $text, $matches)) {
            foreach ($matches[1] as $match) {
                $digits = preg_replace('/[^0-9]/', '', (string) $match);
                if (strlen($digits) >= 8 && strlen($digits) <= 18) {
                    $detected[] = $digits;
                }
            }
        }

        // 2. Standalone 9-18 digit sequences, excluding typical Indonesian mobile phone numbers (08... or 628...)
        if (preg_match_all('/\b(\d{9,18})\b/', $text, $matches)) {
            foreach ($matches[1] as $match) {
                $digits = (string) $match;
                if (str_starts_with($digits, '08') || str_starts_with($digits, '628')) {
                    continue;
                }
                $detected[] = $digits;
            }
        }

        return array_values(array_unique($detected));
    }
}
