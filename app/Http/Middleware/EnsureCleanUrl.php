<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCleanUrl
{
    /**
     * Handle an incoming request.
     *
     * Ensures requests containing "/public" in their URI or base URL are canonically
     * redirected (301) to clean URLs, preventing URL pollution in route() and asset() generators.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $uri = (string) $request->getRequestUri();

        // 1. If the request URI explicitly contains /public/ or starts with /public, redirect 301
        if (preg_match('#^/public(?:/(.*))?$#i', $uri, $matches)) {
            $cleanPath = '/' . ($matches[1] ?? '');
            if ($cleanPath === '//') {
                $cleanPath = '/';
            }

            return redirect($cleanPath, 301, [
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        }

        // 2. Prevent Symfony Request from propagating '/public' as base URL
        if ($request->getBaseUrl() === '/public' || str_starts_with($request->getBaseUrl(), '/public/')) {
            $request->server->set('SCRIPT_NAME', preg_replace('#^/public#i', '', (string) $request->server->get('SCRIPT_NAME', '')));
        }

        // 3. Lock URL generator to current clean root URL (prevent /public leaks while preserving local development host & port)
        $host = (string) $request->getHost();
        $isLocal = in_array($host, ['127.0.0.1', 'localhost', '::1'], true)
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local')
            || app()->environment('local');

        if ($isLocal) {
            $rootUrl = rtrim($request->getSchemeAndHttpHost(), '/');
            \Illuminate\Support\Facades\URL::forceRootUrl($rootUrl);
            if ($request->isSecure()) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            } else {
                \Illuminate\Support\Facades\URL::forceScheme('http');
            }
        } else {
            $appUrl = (string) config('app.url');
            if ($appUrl !== '') {
                \Illuminate\Support\Facades\URL::forceRootUrl(rtrim($appUrl, '/'));
                if (str_starts_with($appUrl, 'https://')) {
                    \Illuminate\Support\Facades\URL::forceScheme('https');
                }
            }
        }

        return $next($request);
    }
}
