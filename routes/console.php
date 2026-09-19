<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Schedule daily check for monthly AI token resets (Setiap pukul 00:05)
Schedule::command('cooca:reset-ai-tokens')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron.log'));

// 2. Schedule daily check for SaaS subscription expirations and lifecycle (Setiap pukul 00:01)
Schedule::command('cooca:process-subscriptions')
    ->dailyAt('00:01')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron.log'));

// 3. Schedule daily WhatsApp notifications for subscription expiry: H-7, H-3, H-1, Hari H (Setiap pukul 09:00)
Schedule::command('subscriptions:send-wa-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron-wa.log'));

// 4. Publish scheduled social media posts and purge temporary files (Setiap menit)
Schedule::command('social-media:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/social-media.log'));

// 5. Purge published social media posts, media files, and inactive credentials after 1 day (Setiap pukul 02:00)
Schedule::command('social-media:purge-published')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/social-media-purge.log'));



