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
        $tab = (string) $request->query('tab', 'settings');
        $validTabs = ['settings', 'merchants', 'app_review'];
        if (! in_array($tab, $validTabs, true)) {
            $tab = 'settings';
        }

        $platform = $this->adminService->getPlatformSettings();
        $summary = $this->adminService->getPlatformSummary();
        $merchants = $this->adminService->getConnectedMerchantsList(20);

        return view('admin.social_media.index', compact('tab', 'platform', 'summary', 'merchants'));
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
        ]);

        $this->adminService->savePlatformSettings($validated);

        return redirect()->route('admin.social-media.index', ['tab' => 'settings'])
            ->with('success', 'Konfigurasi Meta App & TikTok Developer berhasil disimpan.');
    }
}
