<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the authenticated GlobalCustomer has a phone number on file.
 * If not, redirect them to the profile completion page before they can
 * proceed to checkout or reservation.
 */
final class RequireCustomerProfile
{
    public function handle(Request $request, Closure $next): Response
    {
        $customer = auth('customer')->user();

        if ($customer === null) {
            return $next($request);
        }

        if ($customer->isProfileComplete()) {
            return $next($request);
        }

        if (! $request->session()->has('url.intended')) {
            $request->session()->put('url.intended', $request->header('Referer') ?: $request->fullUrl());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'          => false,
                'requires_profile' => true,
                'redirect_url'     => route('customer.profile.complete'),
                'message'          => 'Lengkapi nomor WhatsApp Anda sebelum melanjutkan.',
            ], 403);
        }

        return redirect()->route('customer.profile.complete')
            ->with('info', 'Lengkapi nomor WhatsApp Anda sebelum melanjutkan.');
    }
}