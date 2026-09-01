<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Context::hasBusiness()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'No active business context selected. Please select an active business first.',
                    'error_code' => 'ACTIVE_BUSINESS_REQUIRED',
                ], Response::HTTP_CONFLICT);
            }

            return redirect()->route('businesses.select');
        }

        return $next($request);
    }
}
