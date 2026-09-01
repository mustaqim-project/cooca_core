<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Mail\DynamicMailConfig;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

final class AdminSmtpController extends Controller
{
    /**
     * Display CMS SMTP & Mail Configuration.
     */
    public function index(): View
    {
        $mailMailer = SystemSetting::get('mail_mailer') ?? config('mail.default', 'smtp');
        $mailHost = SystemSetting::get('mail_host') ?? config('mail.mailers.smtp.host', 'smtp.gmail.com');
        $mailPort = SystemSetting::get('mail_port') ?? (string) config('mail.mailers.smtp.port', 587);
        $mailUsername = SystemSetting::get('mail_username') ?? config('mail.mailers.smtp.username', '');
        $mailPassword = SystemSetting::get('mail_password') ?? config('mail.mailers.smtp.password', '');
        $mailEncryption = SystemSetting::get('mail_encryption') ?? config('mail.mailers.smtp.encryption', 'tls');
        $mailFromAddress = SystemSetting::get('mail_from_address') ?? config('mail.from.address', 'no-reply@cooca.id');
        $mailFromName = SystemSetting::get('mail_from_name') ?? config('mail.from.name', 'Cooca Core Platform');

        return view('admin.smtp.index', compact(
            'mailMailer',
            'mailHost',
            'mailPort',
            'mailUsername',
            'mailPassword',
            'mailEncryption',
            'mailFromAddress',
            'mailFromName'
        ));
    }

    /**
     * Update SMTP & Email settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'string', 'in:smtp,sendmail,log'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'numeric', 'min:1|max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        SystemSetting::set('mail_mailer', $validated['mail_mailer'], 'mail');
        SystemSetting::set('mail_host', $validated['mail_host'] ?? '', 'mail');
        SystemSetting::set('mail_port', (string) ($validated['mail_port'] ?? '587'), 'mail');
        SystemSetting::set('mail_username', $validated['mail_username'] ?? '', 'mail');

        if (! empty($validated['mail_password'])) {
            SystemSetting::set('mail_password', $validated['mail_password'], 'mail', true);
        }

        SystemSetting::set('mail_encryption', $validated['mail_encryption'] ?? 'tls', 'mail');
        SystemSetting::set('mail_from_address', $validated['mail_from_address'] ?? 'no-reply@cooca.id', 'mail');
        SystemSetting::set('mail_from_name', $validated['mail_from_name'] ?? 'Cooca Core Platform', 'mail');

        // Immediately update runtime config
        DynamicMailConfig::bootstrap();

        return redirect()->route('admin.smtp.index')->with('success', 'Konfigurasi SMTP Email berhasil diperbarui dan diterapkan ke seluruh sistem.');
    }

    /**
     * Send test email to verify SMTP connection.
     */
    public function test(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            DynamicMailConfig::bootstrap();

            $appName = SystemSetting::get('app_name', 'Cooca Core');
            $testEmail = $validated['test_email'];

            Mail::raw(
                "Halo,\n\nIni adalah email uji coba dari panel Admin {$appName} (CMS SMTP).\n\nJika Anda menerima email ini, konfigurasi SMTP server Anda telah berhasil terhubung dan berfungsi dengan sempurna!\n\nWaktu Kirim: " . now()->format('d M Y H:i:s T') . "\n\nSalam,\nTim Administrator {$appName}",
                function ($message) use ($testEmail, $appName) {
                    $message->to($testEmail)
                        ->subject("[Uji Coba Berhasil] Tes Koneksi SMTP {$appName}");
                }
            );

            return redirect()->route('admin.smtp.index')
                ->with('success', "Email uji coba berhasil dikirim ke {$testEmail}! Server SMTP Anda berfungsi dengan baik.");
        } catch (Throwable $e) {
            return redirect()->route('admin.smtp.index')
                ->with('error', 'Gagal mengirim email uji coba: ' . $e->getMessage());
        }
    }
}
