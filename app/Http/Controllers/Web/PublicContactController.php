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
    public function submit(Request $request, \App\Domain\WhatsApp\AdminWhatsAppService $adminWa): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:120',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        \Illuminate\Support\Facades\Log::info('Public contact inquiry received', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'ip' => $request->ip(),
        ]);

        // Simpan sebagai lead inquiry kontak
        \App\Models\TemplateLead::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?: 'N/A',
            'email' => $validated['email'],
            'business_name' => 'Inquiry: ' . $validated['subject'],
            'template_slug' => 'contact-inquiry',
            'template_name' => $validated['subject'],
            'ip_address' => $request->ip(),
        ]);

        // Kirimkan notifikasi instan ke nomor WhatsApp admin jika aktif
        $adminPhone = \App\Models\SystemSetting::get('admin_whatsapp_number') ?: '6282337499577';
        if ($adminPhone) {
            $text = "📩 *Pesan Baru dari Formulir Kontak Web COOCA*\n\n"
                  . "👤 *Nama:* {$validated['name']}\n"
                  . "📧 *Email:* {$validated['email']}\n"
                  . "📱 *WhatsApp:* " . ($validated['phone'] ?: '-') . "\n"
                  . "📌 *Subjek:* {$validated['subject']}\n"
                  . "💬 *Pesan:* {$validated['message']}";

            try {
                $adminWa->sendMessage($adminPhone, $text);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to notify admin via WA on contact submit: ' . $e->getMessage());
            }
        }

        return back()->with('success_message', 'Pesan Anda berhasil terkirim. Tim support COOCA akan segera menghubungi Anda.');
    }
}
