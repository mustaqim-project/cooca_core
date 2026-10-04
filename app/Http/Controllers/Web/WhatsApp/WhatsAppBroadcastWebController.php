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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

        $locations = \App\Models\Location::where('business_id', $business->id)
            ->whereIn('type', ['outlet', 'store', 'central_kitchen'])
            ->orderBy('name')
            ->get();

        if ($locations->isEmpty()) {
            $locations = \App\Models\Location::where('business_id', $business->id)->orderBy('name')->get();
        }

        $outletCounts = [];
        foreach ($locations as $loc) {
            $customerIds = \App\Models\PosOrder::where('business_id', $business->id)
                ->where('location_id', $loc->id)
                ->whereNotNull('customer_id')
                ->pluck('customer_id')
                ->unique();

            $outletCounts[$loc->id] = Customer::where('business_id', $business->id)
                ->whereIn('id', $customerIds)
                ->where('is_active', true)
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->count();
        }

        // Pastikan template standar sistem sudah ter-seed jika database masih kosong
        if (\App\Models\WhatsAppMessageTemplate::count() === 0) {
            \App\Domain\WhatsApp\CloudApi\Templates\CoocaStandardTemplates::seedLocalTemplates($whatsAppAccount?->waba_id ?: 'platform_default');
        }

        // Ambil template Meta resmi yang disetujui (APPROVED) untuk bisnis ini atau global platform
        $approvedTemplates = \App\Models\WhatsAppMessageTemplate::approved()
            ->where(function ($q) use ($business, $whatsAppAccount) {
                $q->whereNull('business_id');
                if ($whatsAppAccount?->waba_id) {
                    $q->orWhere('waba_id', $whatsAppAccount->waba_id);
                }
                $q->orWhere('business_id', $business->id);
            })
            ->orderBy('name')
            ->get();

        return view('app.whatsapp.broadcast', compact('business', 'campaigns', 'stats', 'waSession', 'whatsAppAccount', 'customerCount', 'tierCounts', 'locations', 'outletCounts', 'approvedTemplates'));
    }

    /**
     * Gracefully redirect the legacy standalone create page to the Modal Sheet composer in index.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('whatsapp.broadcast.index', ['open_composer' => 1]);
    }

    /**
     * Store and execute a new broadcast campaign asynchronously via Laravel Queue.
     */
    public function store(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'message'           => 'required|string|max:2000',
            'media_url'         => 'nullable|url|max:500',
            'target_filter'     => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($business) {
                    if (in_array($value, ['all', 'bronze', 'silver', 'gold', 'vip'], true)) {
                        return;
                    }
                    if (str_starts_with($value, 'outlet:')) {
                        $locId = substr($value, 7);
                        if (\App\Models\Location::where('business_id', $business->id)->where('id', $locId)->exists()) {
                            return;
                        }
                    }
                    $fail('Target audiens yang dipilih tidak valid.');
                },
            ],
            'template_name'     => 'nullable|string|max:128',
            'template_language' => 'nullable|string|max:16',
            'template_params'   => 'nullable|array',
        ]);

        $mediaUrl = trim((string) ($validated['media_url'] ?? ''));
        if ($mediaUrl !== '') {
            $parsed = parse_url($mediaUrl);
            $scheme = strtolower((string) ($parsed['scheme'] ?? ''));
            if ($scheme !== 'https') {
                throw ValidationException::withMessages([
                    'media_url' => 'URL media wajib menggunakan protokol https:// yang aman.',
                ]);
            }

            $host = strtolower((string) ($parsed['host'] ?? ''));
            if (empty($host) || in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0', '169.254.169.254'], true)) {
                throw ValidationException::withMessages([
                    'media_url' => 'URL media tidak valid atau mengarah ke alamat jaringan lokal/privat.',
                ]);
            }

            $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : @gethostbyname($host);
            if (! $ip || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw ValidationException::withMessages([
                    'media_url' => 'URL media tidak valid atau mengarah ke alamat jaringan lokal/privat.',
                ]);
            }
        }

        // Ensure WA is connected before creating campaign
        $whatsAppAccount = WhatsAppAccount::where('business_id', $business->id)->first();
        $waSession       = WhatsAppSession::where('business_id', $business->id)->first();
        $isConnected     = ($whatsAppAccount && $whatsAppAccount->isConnected()) || ($waSession && $waSession->isConnected());

        if (! $isConnected) {
            return back()->withErrors(['whatsapp' => __('whatsapp.error_whatsapp_not_connected')])->withInput();
        }

        // Idempotency Lock: Mencegah penembakan broadcast duplikat dalam kurun waktu 300 detik (5 menit)
        $idempotencyHash = md5($business->id . ':' . trim((string) $validated['title']) . ':' . trim((string) $validated['message']));
        $lockKey         = "broadcast_lock_{$idempotencyHash}";
        if (! Cache::add($lockKey, true, 300)) {
            return back()->withErrors([
                'title' => 'Kampanye broadcast yang identik baru saja dijadwalkan. Mohon tunggu proses pengiriman selesai untuk mencegah pesan duplikat ke pelanggan.',
            ])->withInput();
        }

        $campaign = DB::transaction(function () use ($business, $validated) {
            return WhatsAppBroadcastCampaign::create([
                'business_id'       => $business->id,
                'title'             => trim($validated['title']),
                'message'           => trim($validated['message']),
                'media_url'         => !empty($validated['media_url']) ? trim($validated['media_url']) : null,
                'target_filter'     => $validated['target_filter'],
                'template_name'     => !empty($validated['template_name']) ? trim($validated['template_name']) : null,
                'template_language' => !empty($validated['template_language']) ? trim($validated['template_language']) : 'id',
                'template_params'   => $validated['template_params'] ?? null,
                'status'            => 'processing',
            ]);
        });

        // Dispatch asynchronous background job
        SendWhatsAppBroadcastJob::dispatch((string) $business->id, (string) $campaign->id);

        return redirect()->route('whatsapp.broadcast.show', $campaign)
            ->with('success', __('whatsapp.flash_broadcast_scheduled', ['title' => $campaign->title]));
    }

    /**
     * Show detail & recipient status for a campaign (supports live JSON polling).
     */
    public function show(Request $request, WhatsAppBroadcastCampaign $campaign): View|JsonResponse
    {
        $business = Context::requireBusiness();

        if ((string) $campaign->business_id !== (string) $business->id) {
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
            'filter' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($business) {
                    if (in_array($value, ['all', 'bronze', 'silver', 'gold', 'vip', 'custom'], true)) {
                        return;
                    }
                    if (str_starts_with($value, 'outlet:')) {
                        $locId = substr($value, 7);
                        if (\App\Models\Location::where('business_id', $business->id)->where('id', $locId)->exists()) {
                            return;
                        }
                    }
                    $fail('Filter target audiens tidak valid.');
                },
            ],
        ]);
        $filter = $validated['filter'] ?? 'all';

        $query = Customer::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNotNull('phone')
            ->where('phone', '!=', '');

        if (str_starts_with($filter, 'outlet:')) {
            $locationId = substr($filter, 7);
            $customerIds = \App\Models\PosOrder::where('business_id', $business->id)
                ->where('location_id', $locationId)
                ->whereNotNull('customer_id')
                ->pluck('customer_id')
                ->unique();

            $query->whereIn('id', $customerIds);
        } elseif (! in_array($filter, ['all', 'custom'], true)) {
            $query->whereRaw('LOWER(membership_tier) = ?', [strtolower($filter)]);
        }

        return response()->json(['count' => $query->count()]);
    }
}

