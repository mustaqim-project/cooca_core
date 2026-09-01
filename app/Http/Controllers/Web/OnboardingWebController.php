<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class OnboardingWebController extends Controller
{
    /**
     * Get current user onboarding status and configuration state.
     */
    public function status(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();

        return response()->json([
            'completed' => (bool) $user->onboarding_completed,
            'current_step' => (int) ($user->onboarding_current_step ?? 1),
            'version' => (int) ($user->onboarding_version ?? 1),
            'completed_at' => $user->onboarding_completed_at?->toIso8601String(),
        ]);
    }

    /**
     * Update the user's progress in the product tour.
     */
    public function updateStep(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'step' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $user->update([
            'onboarding_current_step' => $validated['step'],
        ]);

        return response()->json([
            'success' => true,
            'current_step' => $user->onboarding_current_step,
        ]);
    }

    /**
     * Complete or skip the product tour permanently.
     */
    public function complete(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();

        $user->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
            'onboarding_current_step' => 1,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Onboarding product tour diselesaikan.',
            ]);
        }

        return back()->with('success', 'Panduan pengenalan sistem telah diselesaikan.');
    }

    /**
     * Restart the product tour from the beginning.
     */
    public function restart(Request $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();

        $user->update([
            'onboarding_completed' => false,
            'onboarding_completed_at' => null,
            'onboarding_current_step' => 1,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Panduan sistem dimulai ulang.',
            ]);
        }

        return redirect()->route('dashboard')->with('info', 'Panduan sistem dimulai ulang.');
    }
}
