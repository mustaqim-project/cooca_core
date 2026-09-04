<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureOwnerProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('complete-profile', 'complete-profile/*', 'logout', 'select-business', 'api/*', 'email/*', 'profile', 'profile/*')) {
            return $next($request);
        }

        if (app()->environment('testing')) {
            return $next($request);
        }

        $user = Context::user();
        $business = Context::business();

        if ($user && $business) {
            $isOwner = $business->users()->wherePivot('user_id', $user->id)->wherePivot('role', 'owner')->exists();

            // If user is owner and phone or name or business name is missing, redirect to complete-profile
            if ($isOwner) {
                $userPhoneMissing = empty(trim((string) ($user->phone ?? '')));
                $userNameMissing = empty(trim((string) ($user->name ?? '')));
                $businessNameMissing = empty(trim((string) ($business->name ?? ''))) || str_starts_with($business->name, 'Usaha Saya') || str_starts_with($business->name, 'Usaha Pengguna');

                if ($userPhoneMissing || $userNameMissing || $businessNameMissing) {
                    return redirect()->route('profile.complete');
                }
            }
        }

        return $next($request);
    }
}
