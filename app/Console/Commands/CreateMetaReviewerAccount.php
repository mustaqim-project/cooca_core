<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateMetaReviewerAccount extends Command
{
    protected $signature = 'reviewer:create';

    protected $description = 'Create a Meta App Review test account for reviewer@cooca.id';

    public function handle(): int
    {
        $email    = 'reviewer@cooca.id';
        $password = 'MetaReview2026!';

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            // Update password in case it was wrong
            $user->update([
                'password'          => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->info("User '{$email}' already exists — password reset successfully.");
        } else {
            // Create new user
            $user = User::create([
                'name'              => 'Meta App Reviewer',
                'email'             => $email,
                'password'          => Hash::make($password),
                'email_verified_at' => now(),
                'onboarding_completed'    => true,
                'onboarding_completed_at' => now(),
                'onboarding_current_step' => 99,
                'onboarding_version'      => 1,
            ]);
            $this->info("User '{$email}' created successfully.");
        }

        // Create or find a demo business for the reviewer
        $business = Business::where('slug', 'meta-reviewer-store')->first();

        if (! $business) {
            $business = Business::create([
                'name'              => 'Meta Reviewer Demo Store',
                'slug'              => 'meta-reviewer-store',
                'description'       => 'Demo store for Meta App Review testing',
                'phone'             => '628123456789',
                'email'             => $email,
                'address'           => 'Jakarta, Indonesia',
                'currency'          => 'IDR',
                'rounding_strategy' => Business::ROUNDING_ROUND,
                'currency_precision' => 0,
                'industry_category' => 'retail',
                'is_active'         => true,
            ]);
            $this->info("Business 'Meta Reviewer Demo Store' created.");
        } else {
            $this->info("Business 'Meta Reviewer Demo Store' already exists.");
        }

        // Attach user as owner of the business
        $membership = BusinessMembership::where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if (! $membership) {
            BusinessMembership::create([
                'user_id'     => $user->id,
                'business_id' => $business->id,
                'role'        => 'owner',
            ]);
            $this->info("User attached as owner of business.");
        }

        // Set active business
        $user->update(['active_business_id' => $business->id]);

        $this->newLine();
        $this->info('=== META REVIEWER ACCOUNT READY ===');
        $this->info("Email:    {$email}");
        $this->info("Password: {$password}");
        $this->info("Business: {$business->name} (/{$business->slug})");
        $this->info("Login:    https://cooca.id/login");

        return self::SUCCESS;
    }
}
