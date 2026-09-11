<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequirePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        // Owner and Admin (internal superuser) automatically have all permissions
        if (Context::isAdminOrOwner()) {
            return $next($request);
        }

        $userPermissions = Context::permissions();

        // Check if user has at least one of the required permissions
        $hasAnyPermission = false;
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions, true)) {
                $hasAnyPermission = true;
                break;
            }
        }

        if (! $hasAnyPermission) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses (permission) untuk melakukan tindakan ini.',
                    'error_code' => 'FORBIDDEN_PERMISSION',
                    'required_permissions' => $permissions,
                    'current_role' => Context::role(),
                ], Response::HTTP_FORBIDDEN);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki izin akses untuk halaman tersebut.');
        }

        return $next($request);
    }
}
