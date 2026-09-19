<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        \App\Domain\Mail\DynamicMailConfig::bootstrap();
        \Carbon\Carbon::setLocale('id');

        // Self-heal: Ensure colliding public/admin/social-media directory does not intercept Laravel routes
        $collidingDir = public_path('admin/social-media');
        if (is_dir($collidingDir)) {
            try {
                \Illuminate\Support\Facades\File::deleteDirectory($collidingDir);
            } catch (\Throwable) {
                // Ignore filesystem edge cases
            }
        }
    }
}
