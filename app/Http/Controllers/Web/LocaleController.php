<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch application locale between ID and EN.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetLocaleMiddleware::SUPPORTED_LOCALES, true)) {
            $locale = 'id';
        }

        session(['locale' => $locale]);
        cookie()->queue(cookie('cooca_locale', $locale, 60 * 24 * 365));

        if (auth()->check()) {
            try {
                auth()->user()->update(['preferred_language' => $locale]);
            } catch (\Throwable) {
                // Silently ignore if preferred_language column not present
            }
        }

        return redirect()->back();
    }
}
