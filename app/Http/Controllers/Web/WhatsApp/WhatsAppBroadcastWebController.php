<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\WhatsApp;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Http\Controllers\Controller;
use App\Jobs\WhatsApp\SendWhatsAppBroadcastJob;
use App\Models\Customer;
use App\Models\WhatsAppAccount;
use App\Models\WhatsAppBroadcastCampaign;
use App\Models\WhatsAppSession;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WhatsAppBroadcastWebController extends Controller
{
    public function __construct(protected WhatsAppGatewayService $gateway) {}

    /**
     * List all broadcast campaigns for this business and prepare modal data.
     */
    public function index(): View
    {
        $business  = Context::requireBusiness();

        $campaigns = WhatsAppBroadcastCampaign::where('business_id', $business->id)
            ->latest()
            ->paginate(15);

        $stats = [
            'total_campaigns'   => WhatsAppBroadcastCampaign::where('business_id', $business->id)->count(),
            'total_sent'        => (int) WhatsAppBroadcastCampaign::where('business_id', $business->id)->sum('total_sent'),
            'total_recipients'  => (int) WhatsAppBroadcastCampaign::where('business_id', $business->id)->sum('total_recipients'),
            'success_rate'      => 0.0,
        ];

        if ($stats['total_recipients'] > 0) {
            $stats['success_rate'] = round(($stats['total_sent'] / $stats['total_recipients']) * 100, 1);
        }

        $waSession        = WhatsAppSession::where('business_id', $business->id)->first();
        $whatsAppAccount  = WhatsAppAccount::where('business_id', $business->id)->first();
        $customerCount    = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        $tierCounts = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->selectRaw('LOWER(membership_tier) as tier, COUNT(*) as count')
            ->groupBy('tier')
            ->pluck('count', 'tier')
            ->toArray();

        return view('app.whatsapp.broadcast', compact('business', 'campaigns', 'stats', 'waSession', 'whatsAppAccount', 'customerCount', 'tierCounts'));
    }

    /**
     * Show the create broadcast form.
     */
    public function create(): View
    {
        $business         = Context::requireBusiness();
        $waSession        = WhatsAppSession::where('business_id', $business->id)->first();
        $whatsAppAccount  = WhatsAppAccount::where('business_id', $business->id)->first();
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

        return view('app.whatsapp.create', compact('business', 'waSession', 'whatsAppAccount', 'customerCount', 'tierCounts'));
    }

    /**
     * Store and execute a new broadcast campaign asynchronously via Laravel Queue.
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
        $whatsAppAccount = WhatsAppAccount::where('business_id', $business->id)->first();
        $waSession       = WhatsAppSession::where('business_id', $business->id)->first();
        $isConnected     = ($whatsAppAccount && $whatsAppAccount->isConnected()) || ($waSession && $waSession->isConnected());

        if (! $isConnected) {
            return back()->withErrors(['whatsapp' => 'Akun WhatsApp resmi Meta belum terhubung. Harap hubungkan nomor WhatsApp bisnis Anda di halaman Integrasi WhatsApp.'])->withInput();
        }

        $campaign = DB::transaction(function () use ($business, $validated) {
            return WhatsAppBroadcastCampaign::create([
                'business_id'   => $business->id,
                'title'         => trim($validated['title']),
                'message'       => trim($validated['message']),
                'media_url'     => !empty($validated['media_url']) ? trim($validated['media_url']) : null,
                'target_filter' => $validated['target_filter'],
                'status'        => 'processing',
            ]);
        });

        // Dispatch asynchronous background job
        SendWhatsAppBroadcastJob::dispatch((string) $business->id, (string) $campaign->id);

        return redirect()->route('whatsapp.broadcast.show', $campaign)
            ->with('success', "Blast promosi \"{$campaign->title}\" berhasil dijadwalkan dan sedang diproses di latar belakang.");
    }

    /**
     * Show detail & recipient status for a campaign (supports live JSON polling).
     */
    public function show(Request $request, WhatsAppBroadcastCampaign $campaign): View|JsonResponse
    {
        $business = Context::requireBusiness();

        if ((int)$campaign->business_id !== (int)$business->id) {
            abort(404);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'id'               => $campaign->id,
                'status'           => $campaign->status,
                'total_recipients' => $campaign->total_recipients,
                'total_sent'       => $campaign->total_sent,
                'total_failed'     => $campaign->total_failed,
            ]);
        }

        $recipients = $campaign->recipients()->latest()->paginate(30);

        return view('app.whatsapp.broadcast_detail', compact('business', 'campaign', 'recipients'));
    }

    /**
     * AJAX: Return estimated recipient count for a given filter.
     */
    public function estimateRecipients(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $validated = $request->validate([
            'filter' => ['nullable', 'string', 'in:all,bronze,silver,gold,vip,custom'],
        ]);
        $filter   = $validated['filter'] ?? 'all';

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

