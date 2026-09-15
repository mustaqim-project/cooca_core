<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebSecurityHeaders
{
    /**
     * Handle an incoming web request and apply core security headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Clickjacking protection: only allow framing from same origin
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // MIME-type sniffing defense
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Cross-origin referrer information control
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // XSS filter legacy instruction
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}
