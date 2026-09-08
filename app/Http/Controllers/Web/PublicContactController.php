<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicContactController extends Controller
{
    /**
     * Show official contact page.
     */
    public function show(): View
    {
        return view('public.contact.index', [
            'officialWhatsapp' => '0823 3749 9577',
            'officialWhatsappRaw' => '6282337499577',
            'officialEmail' => 'support@cooca.id',
            'officeLocation' => 'Jakarta Selatan, DKI Jakarta, Indonesia',
        ]);
    }

    /**
     * Handle contact form submit.
     */
    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:120',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        return back()->with('success_message', 'Pesan Anda berhasil terkirim. Tim support COOCA akan segera menghubungi Anda.');
    }
}
