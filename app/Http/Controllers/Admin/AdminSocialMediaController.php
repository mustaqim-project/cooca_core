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
        $validTabs = ['posts', 'inbox', 'settings', 'merchants', 'app_review'];
        if (! in_array($tab, $validTabs, true)) {
            $tab = 'posts';
        }

        $platform = $this->adminService->getPlatformSettings();
        $summary = $this->adminService->getPlatformSummary();
        $merchants = $this->adminService->getConnectedMerchantsList(20);
        $platformPosts = $this->adminService->getPlatformPosts(12);
        $platformComments = $this->adminService->getPlatformComments(20);

        return view('admin.social_media.index', compact('tab', 'platform', 'summary', 'merchants', 'platformPosts', 'platformComments'));
    }

    /**
     * Create and publish/schedule a new post for Cooca's official social media.
     */
    public function storePost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform'              => ['nullable', 'string', 'in:instagram,facebook,threads,tiktok'],
            'platforms'             => ['nullable', 'array'],
            'platforms.*'           => ['string', 'in:instagram,facebook,threads,tiktok'],
            'timing_mode'           => ['nullable', 'string', 'in:now,schedule_all,per_channel'],
            'platform_timing'       => ['nullable', 'array'],
            'platform_timing.*'     => ['nullable', 'string', 'in:now,schedule'],
            'platform_scheduled_at' => ['nullable', 'array'],
            'platform_scheduled_at.*' => ['nullable', 'date'],
            'content'               => ['required', 'string', 'max:2200'],
            'media_type'            => ['nullable', 'string', 'in:image,video,reels,carousel,text'],
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

        $post = $this->adminService->createAndPublishPlatformPost($admin, [
            'platforms'             => $selectedPlatforms,
            'platform'              => $selectedPlatforms[0] ?? 'instagram',
            'timing_mode'           => $validated['timing_mode'] ?? 'now',
            'platform_timing'       => $validated['platform_timing'] ?? [],
            'platform_scheduled_at' => $validated['platform_scheduled_at'] ?? [],
            'content'               => $validated['content'],
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
            return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
                ->with('error', 'Gagal mempublikasikan postingan: ' . ($post->error_message ?? 'Periksa log atau kredensial akun.'));
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

        return redirect()->route('admin.social-media.index', ['tab' => 'posts'])
            ->with('error', 'Gagal mempublikasikan ulang postingan: ' . ($post->error_message ?? 'Periksa kredensial.'));
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
