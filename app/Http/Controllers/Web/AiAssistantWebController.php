<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Domain\Ai\AiSystemGuideService;
use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class AiAssistantWebController extends Controller
{
    public function __construct(
        private readonly AiSystemGuideService $guideService = new AiSystemGuideService(),
    ) {}

    /**
     * Ask a question to the AI Assistant.
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:1000'],
            'scope' => ['nullable', 'string', 'in:all,sop,system'],
            'conversation_id' => ['nullable', 'string'],
        ]);

        $business = Context::requireBusiness();
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $scope = $validated['scope'] ?? 'all';
            $conversationId = $validated['conversation_id'] ?? null;

            $result = $this->guideService->ask(
                $business,
                $user,
                $validated['query'],
                $scope,
                $conversationId,
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pertanyaan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get tailored quick prompts for the active tenant.
     */
    public function getPrompts(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $prompts = $this->guideService->getQuickPrompts($business, $user);

        return response()->json([
            'success' => true,
            'data' => $prompts,
        ]);
    }

    /**
     * Get recent conversation messages.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $conversation = AiConversation::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with(['messages' => fn($q) => $q->latest()->limit(20)])
            ->latest('last_activity_at')
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => null,
                    'messages' => [],
                ],
            ]);
        }

        $messages = $conversation->messages->reverse()->values()->map(fn($m) => [
            'id' => $m->id,
            'role' => $m->role,
            'content' => $m->content,
            'sources' => $m->sources ?? [],
            'action_buttons' => $m->action_buttons ?? [],
            'created_at' => $m->created_at->format('H:i'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'messages' => $messages,
            ],
        ]);
    }

    /**
     * Clear active conversation history.
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $business = Context::requireBusiness();
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        AiConversation::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat obrolan asisten telah dibersihkan.',
        ]);
    }
}
