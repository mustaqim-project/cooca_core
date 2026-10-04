<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Supported locales in COOCA.
     */
    public const SUPPORTED_LOCALES = ['id', 'en'];

    /**
     * Handle an incoming request and bind active locale deterministically.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;
        if (! $locale) {
            $locale = $request->cookie('cooca_locale', config('app.locale', 'id'));
        }

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = 'id';
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
