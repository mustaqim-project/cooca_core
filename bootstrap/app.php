<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureActiveBusiness;
use App\Http\Middleware\RequireRole;
use App\Http\Middleware\RequireWhatsAppOtp;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetActiveBusinessContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            if ($request->is('customer') || $request->is('customer/*') || $request->is('b/*')) {
                return route('customer.login');
            }

            return route('login');
        });

        $middleware->redirectUsersTo(function (Request $request) {
            if (auth('admin')->check()) {
                return route('admin.dashboard');
            }

            if (auth('customer')->check()) {
                return route('customer.dashboard');
            }

            return route('dashboard');
        });

        $middleware->alias([
            'business.context'   => SetActiveBusinessContext::class,
            'business.active'    => EnsureActiveBusiness::class,
            'profile.complete'   => \App\Http\Middleware\EnsureOwnerProfileComplete::class,
            'require.role'       => RequireRole::class,
            'wa.otp'             => RequireWhatsAppOtp::class,
            'require.permission' => \App\Http\Middleware\RequirePermission::class,
            'entitlement'        => \App\Http\Middleware\CheckResourceEntitlement::class,
            // Customer portal gates
            'customer.profile'   => \App\Http\Middleware\RequireCustomerProfile::class,
            'customer.otp'       => \App\Http\Middleware\RequireCustomerOtp::class,
            'verified'           => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\WebSecurityHeaders::class,
            SetActiveBusinessContext::class,
        ]);
        $middleware->api(append: [
            SecurityHeaders::class,
            SetActiveBusinessContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
