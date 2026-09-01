<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class DynamicMailConfig
{
    /**
     * Bootstrap dynamic mail configuration from system settings in database.
     */
    public static function bootstrap(): void
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return;
            }

            $mailer = SystemSetting::get('mail_mailer');
            $host = SystemSetting::get('mail_host');
            $port = SystemSetting::get('mail_port');
            $username = SystemSetting::get('mail_username');
            $password = SystemSetting::get('mail_password');
            $encryption = SystemSetting::get('mail_encryption');
            $fromAddress = SystemSetting::get('mail_from_address');
            $fromName = SystemSetting::get('mail_from_name');

            if (! empty($mailer)) {
                Config::set('mail.default', $mailer);
            }

            if (! empty($host)) {
                Config::set('mail.mailers.smtp.host', $host);
            }

            if (! empty($port)) {
                Config::set('mail.mailers.smtp.port', (int) $port);
            }

            if ($username !== null) {
                Config::set('mail.mailers.smtp.username', $username);
            }

            if ($password !== null) {
                Config::set('mail.mailers.smtp.password', $password);
            }

            if ($encryption !== null) {
                Config::set('mail.mailers.smtp.encryption', $encryption === 'none' || $encryption === '' ? null : $encryption);
            }

            if (! empty($fromAddress)) {
                Config::set('mail.from.address', $fromAddress);
            }

            if (! empty($fromName)) {
                Config::set('mail.from.name', $fromName);
            }
        } catch (Throwable) {
            // Silently fall back to default configuration if database is unreachable or during migrations
        }
    }
}
