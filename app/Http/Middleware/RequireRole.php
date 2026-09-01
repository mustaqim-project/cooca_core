<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $currentRole = Context::role();

        if ($currentRole === null || ! in_array($currentRole, $roles, true)) {
            return response()->json([
                'message' => 'You do not have the required role to perform this action.',
                'error_code' => 'FORBIDDEN_ROLE',
                'required_roles' => $roles,
                'current_role' => $currentRole,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
