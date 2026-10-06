<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Ai;

use App\Domain\Ai\Execution\AiActionExecutor;
use App\Domain\Ai\Organization\AgentRole;
use App\Domain\Ai\Organization\CompanyHierarchy;
use App\Domain\Ai\Organization\Department;
use App\Domain\Ai\Organization\ExecutiveRole;
use App\Domain\Ai\Orchestration\AiOrchestrator;
use App\Domain\Ai\Policy\AiActionPolicy;
use App\Domain\Ai\Providers\AiProviderManager;
use App\Http\Controllers\Controller;
use App\Domain\Ai\Organization\AiOffice;
use App\Domain\Ai\Tools\DetectAnomaliesAndFraudTool;
use App\Domain\Ai\Tools\GetCustomerSummaryTool;
use App\Domain\Ai\Tools\GetFinancialHealthTool;
use App\Domain\Ai\Tools\GetSalesSummaryTool;
use App\Domain\Ai\Tools\GetStockLevelsTool;
use App\Domain\Ai\Tools\GetTopProductsTool;
use App\Models\AiActionProposal;
use App\Models\AiProviderConfig;
use App\Models\AiTask;
use App\Models\AiWorkHistory;
use App\Models\Customer;
use App\Models\MarketplaceAccount;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SocialMediaPost;
use App\Models\Supplier;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

final class AiCompanyWebController extends Controller
{
    public function __construct(
        private readonly AiOrchestrator $orchestrator = new AiOrchestrator(),
        private readonly AiActionExecutor $actionExecutor = new AiActionExecutor(),
        private readonly AiActionPolicy $actionPolicy = new AiActionPolicy(),
        private readonly AiProviderManager $providerManager = new AiProviderManager(),
    ) {}

    /**
     * Legacy /ai endpoint - redirect to the new Main AI Office Entry (Lobby).
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('cooca-ai.index');
    }

    /**
     * Main AI Office Entry / Digital Company Lobby.
     * Route: /cooca-ai
     */
    public function lobby(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        // 1. Gather live stats for the 3 Offices
        $executiveStats = AiOffice::EXECUTIVE->getOfficeStats($business);
        $operationsStats = AiOffice::OPERATIONS->getOfficeStats($business);
        $growthStats = AiOffice::GROWTH->getOfficeStats($business);

        $officesStats = [
            'executive' => $executiveStats,
            'operations' => $operationsStats,
            'growth' => $growthStats,
        ];

        // 2. Company-wide totals
        $companyTotals = [
            'total_agents' => count(AgentRole::cases()),
            'active_agents' => $executiveStats['active_agents_count'] + $operationsStats['active_agents_count'] + $growthStats['active_agents_count'],
            'active_tasks' => $executiveStats['active_tasks_count'] + $operationsStats['active_tasks_count'] + $growthStats['active_tasks_count'],
            'pending_approvals' => $executiveStats['pending_approvals_count'] + $operationsStats['pending_approvals_count'] + $growthStats['pending_approvals_count'],
        ];

        // 3. Pending Proposals requiring Human Decision
        $pendingProposals = AiActionProposal::where('business_id', $business->id)
            ->where('status', AiActionProposal::STATUS_PENDING)
            ->latest()
            ->take(5)
            ->get();

        $pendingCount = $companyTotals['pending_approvals'];

        // 4. Cross-Office Collaboration Workflows & Recent Activity
        $recentTasks = AiTask::where('business_id', $business->id)
            ->latest()
            ->take(8)
            ->get();

        $recentHistories = AiWorkHistory::where('business_id', $business->id)
            ->latest('recorded_at')
            ->take(6)
            ->get();

        $resolvedProvider = $this->providerManager->resolveForBusiness($business);
        $teams = CompanyHierarchy::getTeams();

        return view('app.ai.lobby', compact(
            'business',
            'user',
            'officesStats',
            'companyTotals',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider',
            'teams'
        ));
    }

    /**
     * Office 01 — Executive Office.
     * Route: /cooca-ai/office/executive
     */
    public function executiveOffice(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $office = AiOffice::EXECUTIVE;
        $officeStats = $office->getOfficeStats($business);

        // Domain-specific Financial & Executive Data
        try {
            $financialHealth = (new GetFinancialHealthTool())->execute($business, $user);
        } catch (Throwable) {
            $financialHealth = [];
        }

        try {
            $salesSummary = (new GetSalesSummaryTool())->execute($business, $user);
        } catch (Throwable) {
            $salesSummary = [];
        }

        try {
            $anomalies = (new DetectAnomaliesAndFraudTool())->execute($business, $user);
        } catch (Throwable) {
            $anomalies = [];
        }

        $pendingProposals = AiActionProposal::where('business_id', $business->id)
            ->where('status', AiActionProposal::STATUS_PENDING)
            ->latest()
            ->take(5)
            ->get();

        $pendingCount = $pendingProposals->count();

        $recentTasks = AiTask::where('business_id', $business->id)
            ->whereIn('agent', array_map(fn($r) => $r->value, $office->agentRoles()))
            ->latest()
            ->take(10)
            ->get();

        $officeAgentSlugs = array_map(fn($r) => $r->value, $office->agentRoles());

        $recentHistories = AiWorkHistory::where('business_id', $business->id)
            ->where(function ($query) use ($officeAgentSlugs) {
                foreach ($officeAgentSlugs as $slug) {
                    $query->orWhereJsonContains('participating_agents', $slug);
                }
            })
            ->latest('recorded_at')
            ->take(6)
            ->get();

        $resolvedProvider = $this->providerManager->resolveForBusiness($business);

        return view('app.ai.offices.executive', compact(
            'business',
            'user',
            'office',
            'officeStats',
            'financialHealth',
            'salesSummary',
            'anomalies',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider'
        ));
    }

    /**
     * Office 02 — Operations Office.
     * Route: /cooca-ai/office/operations
     */
    public function operationsOffice(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $office = AiOffice::OPERATIONS;
        $officeStats = $office->getOfficeStats($business);

        // Domain-specific Operational Data
        try {
            $stockLevels = (new GetStockLevelsTool())->execute($business, $user);
        } catch (Throwable) {
            $stockLevels = [];
        }

        $productsCount = Product::where('business_id', $business->id)->where('is_active', true)->count();
        $suppliersCount = Supplier::where('business_id', $business->id)->count();
        $pendingPoCount = PurchaseOrder::where('business_id', $business->id)->whereIn('status', ['draft', 'pending', 'sent'])->count();
        $marketplacesCount = MarketplaceAccount::where('business_id', $business->id)->where('status', 'connected')->count();

        $operationalMetrics = [
            'total_products' => $productsCount,
            'total_suppliers' => $suppliersCount,
            'pending_pos' => $pendingPoCount,
            'connected_marketplaces' => $marketplacesCount,
        ];

        $pendingProposals = AiActionProposal::where('business_id', $business->id)
            ->whereIn('agent', array_map(fn($r) => $r->value, $office->agentRoles()))
            ->where('status', AiActionProposal::STATUS_PENDING)
            ->latest()
            ->get();

        $pendingCount = $pendingProposals->count();

        $recentTasks = AiTask::where('business_id', $business->id)
            ->whereIn('agent', array_map(fn($r) => $r->value, $office->agentRoles()))
            ->latest()
            ->take(10)
            ->get();

        $officeAgentSlugs = array_map(fn($r) => $r->value, $office->agentRoles());

        $recentHistories = AiWorkHistory::where('business_id', $business->id)
            ->where(function ($query) use ($officeAgentSlugs) {
                foreach ($officeAgentSlugs as $slug) {
                    $query->orWhereJsonContains('participating_agents', $slug);
                }
            })
            ->latest('recorded_at')
            ->take(6)
            ->get();

        $resolvedProvider = $this->providerManager->resolveForBusiness($business);

        return view('app.ai.offices.operations', compact(
            'business',
            'user',
            'office',
            'officeStats',
            'stockLevels',
            'operationalMetrics',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider'
        ));
    }

    /**
     * Office 03 — Growth Office.
     * Route: /cooca-ai/office/growth
     */
    public function growthOffice(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $office = AiOffice::GROWTH;
        $officeStats = $office->getOfficeStats($business);

        // Domain-specific Growth Data
        try {
            $customerSummary = (new GetCustomerSummaryTool())->execute($business, $user);
        } catch (Throwable) {
            $customerSummary = [];
        }

        try {
            $topProducts = (new GetTopProductsTool())->execute($business, $user);
        } catch (Throwable) {
            $topProducts = [];
        }

        $totalCustomers = Customer::where('business_id', $business->id)->count();
        $scheduledPosts = SocialMediaPost::where('business_id', $business->id)->whereIn('status', ['draft', 'scheduled'])->count();

        $growthMetrics = [
            'total_customers' => $totalCustomers,
            'scheduled_posts' => $scheduledPosts,
        ];

        $pendingProposals = AiActionProposal::where('business_id', $business->id)
            ->whereIn('agent', array_map(fn($r) => $r->value, $office->agentRoles()))
            ->where('status', AiActionProposal::STATUS_PENDING)
            ->latest()
            ->get();

        $pendingCount = $pendingProposals->count();

        $recentTasks = AiTask::where('business_id', $business->id)
            ->whereIn('agent', array_map(fn($r) => $r->value, $office->agentRoles()))
            ->latest()
            ->take(10)
            ->get();

        $officeAgentSlugs = array_map(fn($r) => $r->value, $office->agentRoles());

        $recentHistories = AiWorkHistory::where('business_id', $business->id)
            ->where(function ($query) use ($officeAgentSlugs) {
                foreach ($officeAgentSlugs as $slug) {
                    $query->orWhereJsonContains('participating_agents', $slug);
                }
            })
            ->latest('recorded_at')
            ->take(6)
            ->get();

        $resolvedProvider = $this->providerManager->resolveForBusiness($business);

        return view('app.ai.offices.growth', compact(
            'business',
            'user',
            'office',
            'officeStats',
            'customerSummary',
            'topProducts',
            'growthMetrics',
            'pendingProposals',
            'pendingCount',
            'recentTasks',
            'recentHistories',
            'resolvedProvider'
        ));
    }

    /**
     * Interactive AI Consultation / Chat Endpoint (AJAX).
     */
    /**
     * Interactive Multi-Agent Query Endpoint.
     */
    public function ask(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        $validated = $request->validate([
            'query' => ['required', 'string', 'max:1000'],
            'team' => ['nullable', 'string', 'in:executive,marketing,sales,operations,finance,people,auto'],
            'agent' => ['nullable', 'string', 'max:50'],
        ]);

        // Enforce user-provided AI Provider API Key (BYOAI requirement)
        $hasConfiguredProvider = $business->aiProviderConfigs()
            ->where('is_active', true)
            ->whereNotNull('api_key')
            ->where('api_key', '!=', '')
            ->exists();

        if (! $hasConfiguredProvider && ! (app()->environment('testing') && ! $request->boolean('enforce_provider_check'))) {
            return response()->json([
                'success' => false,
                'needs_provider' => true,
                'redirect_url' => route('cooca-ai.providers'),
                'message' => 'Anda belum melakukan konfigurasi AI Provider. Silakan masukkan API Key Anda terlebih dahulu.',
            ], 422);
        }

        $agentToTeam = [
            'ceo' => 'executive',
            'business' => 'executive',
            'cfo' => 'finance',
            'finance' => 'finance',
            'reporting' => 'finance',
            'coo' => 'operations',
            'inventory' => 'operations',
            'purchasing' => 'operations',
            'marketplace' => 'operations',
            'cmo' => 'marketing',
            'marketing' => 'marketing',
            'content' => 'marketing',
            'social_media' => 'marketing',
            'sales' => 'sales',
            'customer' => 'sales',
            'sales_director' => 'sales',
            'hr' => 'people',
            'hr_lead' => 'people',
        ];

        $targetTeam = ($validated['team'] ?? 'auto') !== 'auto' ? ($validated['team'] ?? null) : null;
        $targetAgent = ! empty($validated['agent']) ? trim((string) $validated['agent']) : null;
        if (! $targetTeam && $targetAgent && isset($agentToTeam[$targetAgent])) {
            $targetTeam = $agentToTeam[$targetAgent];
        }

        try {
            $result = $this->orchestrator->process($business, $user, $validated['query'], 'chat', $targetTeam, $targetAgent);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses interaksi AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger Manual Daily Business Review.
     */
    public function triggerDailyCheck(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        try {
            $result = $this->orchestrator->runDailyBusinessCheck($business, $user);

            return response()->json([
                'success' => true,
                'message' => 'Evaluasi kesehatan bisnis harian berhasil dijalankan.',
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan evaluasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Action Center (Maker-Checker Proposals).
     */
    public function actions(Request $request): View
    {
        $business = Context::requireBusiness();
        $filter = $request->query('status', 'all');

        $query = AiActionProposal::where('business_id', $business->id)->latest();

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $proposals = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => AiActionProposal::where('business_id', $business->id)->count(),
            'pending' => AiActionProposal::where('business_id', $business->id)->where('status', AiActionProposal::STATUS_PENDING)->count(),
            'approved' => AiActionProposal::where('business_id', $business->id)->where('status', AiActionProposal::STATUS_APPROVED)->count(),
            'completed' => AiActionProposal::where('business_id', $business->id)->where('status', AiActionProposal::STATUS_COMPLETED)->count(),
            'rejected' => AiActionProposal::where('business_id', $business->id)->where('status', AiActionProposal::STATUS_REJECTED)->count(),
            'failed' => AiActionProposal::where('business_id', $business->id)->where('status', AiActionProposal::STATUS_FAILED)->count(),
        ];

        return view('app.ai.actions', compact('business', 'proposals', 'filter', 'counts'));
    }

    /**
     * Approve and execute an Action Proposal.
     */
    public function approveAction(Request $request, AiActionProposal $proposal): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Autentikasi diperlukan.'], 401);
        }

        if ($proposal->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Proposal tidak ditemukan.'], 404);
        }

        if (! $this->actionPolicy->canApprove($business, $user, $proposal)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk menyetujui aksi ini.'], 403);
        }

        try {
            if ($request->has('payload') && is_array($request->input('payload'))) {
                $current = $proposal->payload ?? [];
                $merged = array_merge($current, $request->input('payload'));
                if (isset($merged['quantity']) && (isset($merged['unit_price']) || isset($merged['unit_cost']))) {
                    $cost = (float) $merged['quantity'] * (float) ($merged['unit_price'] ?? $merged['unit_cost']);
                    $merged['total_amount'] = $cost;
                    $proposal->estimated_cost = $cost;
                }
                $proposal->payload = $merged;
            }

            $proposal->update([
                'status' => AiActionProposal::STATUS_APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Execute through the verified execution pipeline
            $execResult = $this->actionExecutor->executeApprovedAction($business, $user, $proposal);

            if ($request->expectsJson()) {
                return response()->json($execResult);
            }

            return back()->with('success', $execResult['message']);
        } catch (Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengeksekusi aksi: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Gagal mengeksekusi aksi: ' . $e->getMessage());
        }
    }

    /**
     * Revise proposal parameters before approval (Human-in-the-Loop Re-proposal).
     */
    public function reviseAction(Request $request, AiActionProposal $proposal): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Autentikasi diperlukan.'], 401);
        }

        if ($proposal->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Proposal tidak ditemukan.'], 404);
        }

        if (! $this->actionPolicy->canApprove($business, $user, $proposal)) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses untuk merevisi usulan ini.'], 403);
        }

        $revised = $request->input('payload', []);
        $current = $proposal->payload ?? [];
        $merged = array_merge($current, $revised);

        $cost = $proposal->estimated_cost;
        if (isset($merged['quantity']) && (isset($merged['unit_price']) || isset($merged['unit_cost']))) {
            $cost = (float) $merged['quantity'] * (float) ($merged['unit_price'] ?? $merged['unit_cost']);
            $merged['total_amount'] = $cost;
        }

        $proposal->update([
            'payload' => $merged,
            'estimated_cost' => $cost,
            'status' => AiActionProposal::STATUS_PENDING,
            'reason' => ($proposal->reason ? $proposal->reason . ' ' : '') . "(Direvisi oleh pemilik: {$user->name})",
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Parameter usulan '{$proposal->title}' berhasil diperbarui.",
                'proposal' => $proposal->fresh(),
            ]);
        }

        return back()->with('success', "Parameter usulan '{$proposal->title}' berhasil diperbarui.");
    }

    /**
     * Reject an Action Proposal.
     */
    public function rejectAction(Request $request, AiActionProposal $proposal): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();
        $user = Context::user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Autentikasi diperlukan.'], 401);
        }

        if ($proposal->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Proposal tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $proposal->update([
            'status' => AiActionProposal::STATUS_REJECTED,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $validated['reason'] ?? 'Ditolak secara manual oleh Business Owner.',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Proposal '{$proposal->title}' telah ditolak.",
            ]);
        }

        return back()->with('success', "Proposal '{$proposal->title}' telah ditolak.");
    }

    /**
     * AI Work History log view.
     */
    public function history(Request $request): View
    {
        $business = Context::requireBusiness();

        $histories = AiWorkHistory::where('business_id', $business->id)
            ->latest('recorded_at')
            ->paginate(15);

        return view('app.ai.history', compact('business', 'histories'));
    }

    /**
     * BYOAI Provider Settings view.
     */
    public function providers(Request $request): View
    {
        $business = Context::requireBusiness();

        $catalog = $this->providerManager->getCatalog($business);
        $configured = AiProviderConfig::where('business_id', $business->id)->get()->keyBy('provider');

        return view('app.ai.providers', compact('business', 'catalog', 'configured'));
    }

    /**
     * Save BYOAI Provider Configuration.
     */
    public function storeProvider(Request $request): JsonResponse|RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:openai,gemini,anthropic,openrouter'],
            'api_key' => ['nullable', 'string', 'max:1000'],
            'model' => ['required', 'string', 'max:150'],
            'custom_model' => ['nullable', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $existing = AiProviderConfig::where('business_id', $business->id)
            ->where('provider', $validated['provider'])
            ->first();

        // If no new api_key provided, retain existing encrypted key
        $apiKey = ! empty($validated['api_key'])
            ? trim($validated['api_key'], " \t\n\r\0\x0B\"'")
            : ($existing?->api_key ?? '');

        if (str_starts_with(strtolower($apiKey), 'bearer ')) {
            $apiKey = trim(substr($apiKey, 7));
        }

        if (empty($apiKey)) {
            return back()->with('error', 'API Key wajib diisi untuk mengaktifkan provider.');
        }

        // Determine effective model
        $targetModel = trim($validated['model']);
        if ($targetModel === 'custom' && ! empty($validated['custom_model'])) {
            $targetModel = trim($validated['custom_model']);
        }

        if ($targetModel === 'custom' || $targetModel === '') {
            $targetModel = ! empty($validated['custom_model']) ? trim($validated['custom_model']) : 'default';
        }

        // Handle custom models persistence in settings
        $settings = is_array($existing?->settings) ? $existing->settings : [];
        $customModels = $settings['custom_models'] ?? [];
        if (! empty($validated['custom_model'])) {
            $cModel = trim($validated['custom_model']);
            if (! in_array($cModel, $customModels, true)) {
                $customModels[] = $cModel;
            }
        }
        if (! in_array($targetModel, $customModels, true) && ! empty($targetModel)) {
            $customModels[] = $targetModel;
        }
        $settings['custom_models'] = array_values(array_unique($customModels));

        $isDefault = (bool) ($validated['is_default'] ?? false);
        if ($isDefault) {
            // Remove is_default from other providers of this business
            AiProviderConfig::where('business_id', $business->id)->update(['is_default' => false]);
        }

        AiProviderConfig::updateOrCreate(
            [
                'business_id' => $business->id,
                'provider' => $validated['provider'],
            ],
            [
                'api_key' => $apiKey, // Automatically encrypted by Eloquent cast
                'model' => $targetModel,
                'is_active' => (bool) ($validated['is_active'] ?? true),
                'is_default' => $isDefault,
                'settings' => $settings,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Konfigurasi AI Provider berhasil disimpan.',
            ]);
        }

        return back()->with('success', 'Konfigurasi AI Provider berhasil disimpan.');
    }

    /**
     * Test connection to an AI provider.
     */
    public function testProvider(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:openai,gemini,anthropic,openrouter'],
            'api_key' => ['nullable', 'string'],
            'model' => ['required', 'string'],
            'custom_model' => ['nullable', 'string', 'max:150'],
        ]);

        $apiKey = trim((string) ($validated['api_key'] ?? ''), " \t\n\r\0\x0B\"'");
        if (str_starts_with(strtolower($apiKey), 'bearer ')) {
            $apiKey = trim(substr($apiKey, 7));
        }

        if (empty($apiKey)) {
            // Fetch from stored config
            $existing = AiProviderConfig::where('business_id', $business->id)
                ->where('provider', $validated['provider'])
                ->first();
            $apiKey = trim((string) ($existing?->api_key ?? ''), " \t\n\r\0\x0B\"'");
            if (str_starts_with(strtolower($apiKey), 'bearer ')) {
                $apiKey = trim(substr($apiKey, 7));
            }
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Kunci API kosong. Masukkan API Key terlebih dahulu.',
            ], 422);
        }

        $targetModel = trim($validated['model']);
        if ($targetModel === 'custom' && ! empty($request->input('custom_model'))) {
            $targetModel = trim((string) $request->input('custom_model'));
        }

        $result = $this->providerManager->testConnection(
            $validated['provider'],
            $apiKey,
            $targetModel
        );

        // Update status in database if config exists
        $config = AiProviderConfig::where('business_id', $business->id)
            ->where('provider', $validated['provider'])
            ->first();

        if ($config) {
            $updates = [
                'status' => $result['success'] ? AiProviderConfig::STATUS_CONNECTED : AiProviderConfig::STATUS_ERROR,
                'tested_at' => now(),
                'last_error' => $result['success'] ? null : $result['message'],
            ];

            if ($result['success'] && ! empty($result['available_models'])) {
                $settings = is_array($config->settings) ? $config->settings : [];
                $settings['available_models'] = $result['available_models'];
                $updates['settings'] = $settings;
            }

            $config->update($updates);
        }

        $result['status'] = $result['success'] ? AiProviderConfig::STATUS_CONNECTED : AiProviderConfig::STATUS_ERROR;
        $result['tested_at'] = now()->toIso8601String();
        $result['tested_at_human'] = now()->format('d M Y H:i');

        return response()->json($result);
    }

    /**
     * Discover available models from an AI provider for an entered or stored API key.
     */
    public function detectModels(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'provider' => ['required', 'string', 'in:openai,gemini,anthropic,openrouter'],
            'api_key' => ['nullable', 'string'],
        ]);

        $apiKey = trim((string) ($validated['api_key'] ?? ''), " \t\n\r\0\x0B\"'");
        if (str_starts_with(strtolower($apiKey), 'bearer ')) {
            $apiKey = trim(substr($apiKey, 7));
        }

        if (empty($apiKey)) {
            $existing = AiProviderConfig::where('business_id', $business->id)
                ->where('provider', $validated['provider'])
                ->first();
            $apiKey = trim((string) ($existing?->api_key ?? ''), " \t\n\r\0\x0B\"'");
            if (str_starts_with(strtolower($apiKey), 'bearer ')) {
                $apiKey = trim(substr($apiKey, 7));
            }
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API Key belum diisi. Masukkan API Key terlebih dahulu.',
            ], 422);
        }

        try {
            $models = $this->providerManager->discoverModels($validated['provider'], $apiKey);

            // Update settings if config exists
            $config = AiProviderConfig::where('business_id', $business->id)
                ->where('provider', $validated['provider'])
                ->first();

            if ($config) {
                $settings = is_array($config->settings) ? $config->settings : [];
                $settings['available_models'] = $models;
                $config->update(['settings' => $settings]);
            }

            return response()->json([
                'success' => true,
                'provider' => $validated['provider'],
                'models' => $models,
                'message' => 'Berhasil mendeteksi ' . count($models) . ' model aktif untuk ' . ucfirst($validated['provider']) . '.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendeteksi model: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all agent avatar customizations and presets.
     */
    public function getAgentAvatars(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $avatars = \App\Models\AiAgentAvatar::getAvatarsForBusiness($business->id);
        $presets = \App\Models\AiAgentAvatar::getPresets();

        return response()->json([
            'success' => true,
            'avatars' => $avatars,
            'presets' => $presets,
        ]);
    }

    /**
     * Save custom avatar configuration for a specific agent.
     */
    public function updateAgentAvatar(Request $request, string $role): JsonResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'custom_name' => ['nullable', 'string', 'max:100'],
            'preset' => ['nullable', 'string', 'max:50'],
            'suit_color' => ['nullable', 'string', 'max:20'],
            'skin_tone' => ['nullable', 'string', 'max:20'],
            'hair_color' => ['nullable', 'string', 'max:20'],
            'hair_style' => ['nullable', 'string', 'max:50'],
            'accessory' => ['nullable', 'string', 'max:50'],
        ]);

        $avatar = \App\Models\AiAgentAvatar::saveAvatar($business->id, $role, $validated);
        $allAvatars = \App\Models\AiAgentAvatar::getAvatarsForBusiness($business->id);

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => 'Avatar agen ' . ($validated['custom_name'] ?? $validated['name'] ?? $role) . ' berhasil diperbarui.',
            'avatar' => $allAvatars[$role] ?? $avatar,
            'all_avatars' => $allAvatars,
        ]);
    }

    /**
     * Get live dual-monitor metrics for all 17 agent and executive roles.
     * Route: /cooca-ai/live-metrics
     */
    public function liveMetrics(Request $request, \App\Domain\Ai\Services\AiAgentBusinessMetricsService $metricsService): JsonResponse
    {
        $business = Context::requireBusiness();
        $role = $request->query('role');

        if ($role) {
            $data = $metricsService->getMetricsForRole($business, (string) $role);
        } else {
            $data = $metricsService->getAllMetrics($business);
        }

        return response()->json([
            'success' => true,
            'business_id' => $business->id,
            'timestamp' => now()->toIso8601String(),
            'metrics' => $data,
        ]);
    }
}

