<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily check for monthly AI token resets
Schedule::command('cooca:reset-ai-tokens')->dailyAt('00:05');

// Schedule daily check for SaaS subscription expirations and H-7, H-3, H-1 reminders
Schedule::command('cooca:process-subscriptions')->dailyAt('00:01');
