<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetActiveBusinessContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $businessId = $request->header('X-Business-Id')
                ?: ($request->hasSession() ? $request->session()->get('active_business_id') : null)
                ?: $user->active_business_id;

            if ($businessId !== null) {
                /** @var BusinessMembership|null $membership */
                $membership = BusinessMembership::where('business_id', $businessId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($membership !== null) {
                    $business = Business::find($businessId);

                    if ($business !== null) {
                        Context::setBusiness($business, $membership);
                    }
                }
            }
        }

        return $next($request);
    }
}
