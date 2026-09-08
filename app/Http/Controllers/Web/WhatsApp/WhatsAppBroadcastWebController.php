<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppSession;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppBroadcastWebController extends Controller
{
    public function __construct(protected WhatsAppGatewayService $gateway) {}

    /**
     * List all broadcast campaigns for this business.
     */
    public function index(): View
    {
        $business  = Context::requireBusiness();

        $campaigns = WhatsAppBroadcastCampaign::where('business_id', $business->id)
            ->latest()
            ->paginate(15);

        $stats = [
            'total_campaigns'   => WhatsAppBroadcastCampaign::where('business_id', $business->id)->count(),
            'total_sent'        => WhatsAppBroadcastCampaign::where('business_id', $business->id)->sum('total_sent'),
            'total_recipients'  => WhatsAppBroadcastCampaign::where('business_id', $business->id)->sum('total_recipients'),
            'success_rate'      => 0,
        ];

        if ($stats['total_recipients'] > 0) {
            $stats['success_rate'] = round(($stats['total_sent'] / $stats['total_recipients']) * 100, 1);
        }

        return view('app.whatsapp.broadcast', compact('business', 'campaigns', 'stats'));
    }

    /**
     * Show the create broadcast form.
     */
    public function create(): View
    {
        $business         = Context::requireBusiness();
        $waSession        = WhatsAppSession::where('business_id', $business->id)->first();
        $customerCount    = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        // Tier counts for filter preview
        $tierCounts = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->selectRaw('LOWER(membership_tier) as tier, COUNT(*) as count')
            ->groupBy('tier')
            ->pluck('count', 'tier')
            ->toArray();

        return view('app.whatsapp.create', compact('business', 'waSession', 'customerCount', 'tierCounts'));
    }

    /**
     * Store and execute a new broadcast campaign.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'message'       => 'required|string|max:2000',
            'media_url'     => 'nullable|url|max:500',
            'target_filter' => 'required|in:all,bronze,silver,gold,vip',
        ]);

        // Ensure WA is connected before creating campaign
        $waSession = WhatsAppSession::where('business_id', $business->id)->first();
        if (! $waSession || $waSession->status !== 'connected') {
            return back()->withErrors(['whatsapp' => 'WhatsApp Gateway belum terhubung. Harap scan QR code terlebih dahulu di halaman Integrasi WhatsApp.'])->withInput();
        }

        $campaign = WhatsAppBroadcastCampaign::create([
            'business_id'   => $business->id,
            'title'         => $validated['title'],
            'message'       => $validated['message'],
            'media_url'     => $validated['media_url'] ?? null,
            'target_filter' => $validated['target_filter'],
            'status'        => 'processing',
        ]);

        // Run broadcast synchronously (for small/medium lists)
        // For production scale, dispatch a queued job instead
        try {
            $this->gateway->sendBroadcast($business, $campaign);
        } catch (\Throwable $e) {
            $campaign->update(['status' => 'failed']);
            return back()->withErrors(['broadcast' => 'Terjadi kesalahan saat mengirim blast: ' . $e->getMessage()]);
        }

        return redirect()->route('whatsapp.broadcast.show', $campaign)
            ->with('success', "Blast promosi \"{$campaign->title}\" berhasil dikirim ke {$campaign->total_sent} pelanggan! 🚀");
    }

    /**
     * Show detail & recipient status for a campaign.
     */
    public function show(WhatsAppBroadcastCampaign $campaign): View
    {
        $business = Context::requireBusiness();

        if ($campaign->business_id !== $business->id) {
            abort(403);
        }

        $recipients = $campaign->recipients()->latest()->paginate(30);

        return view('app.whatsapp.broadcast_detail', compact('business', 'campaign', 'recipients'));
    }

    /**
     * AJAX: Return estimated recipient count for a given filter.
     */
    public function estimateRecipients(Request $request)
    {
        $business = Context::requireBusiness();
        $filter   = $request->input('filter', 'all');

        $query = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if (! in_array($filter, ['all', 'custom'], true)) {
            $query->whereRaw('LOWER(membership_tier) = ?', [strtolower($filter)]);
        }

        return response()->json(['count' => $query->count()]);
    }
}
