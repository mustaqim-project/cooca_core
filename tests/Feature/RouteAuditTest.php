<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class RouteAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected Business $business;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create or get Owner user with verified email & phone
        $this->owner = User::firstOrCreate(
            ['email' => 'audit_owner@cooca.id'],
            [
                'name' => 'Audit Owner',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'phone' => '081234567890',
                'phone_verified_at' => now(),
                'role' => 'user',
            ]
        );

        // 2. Create or get Admin user
        $this->admin = User::firstOrCreate(
            ['email' => 'audit_admin@cooca.id'],
            [
                'name' => 'Audit Admin',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'phone' => '081234567891',
                'phone_verified_at' => now(),
                'role' => 'admin',
            ]
        );

        // 3. Create or get Business
        $this->business = Business::firstOrCreate(
            ['slug' => 'audit-business-test'],
            [
                'name' => 'Audit Business Test',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'status' => 'active',
            ]
        );

        $this->business->users()->syncWithoutDetaching([
            $this->owner->id => ['id' => (string) Str::uuid(), 'role' => 'owner'],
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
    }

    public function test_all_sidebar_and_main_routes_render_without_500_errors(): void
    {
        $sidebarRoutes = [
            'dashboard' => [],
            'businesses.select' => [],
            'pos.ai.index' => [],
            'approvals.inbox' => [],
            'settings.audit-logs.index' => [],
            'pos.terminal' => [],
            'pos.orders.index' => [],
            'pos.kitchen.index' => [],
            'pos.tables.index' => [],
            'sales.orders.index' => [],
            'sales.quotations.index' => [],
            'invoices.index' => [],
            'sales.returns.index' => [],
            'products.index' => [],
            'services.index' => [],
            'materials.index' => [],
            'pos.modifiers.index' => [],
            'inventory.stocks' => [],
            'warehouse.index' => [],
            'inventory.opnames.index' => [],
            'inventory.transfers.index' => [],
            'inventory.movements' => [],
            'product-categories.index' => [],
            'material-categories.index' => [],
            'units.index' => [],
            'import.index' => [],
            'purchase-orders.index' => [],
            'purchasing.bills.index' => [],
            'suppliers.index' => [],
            'purchase.returns.index' => [],
            'customers.index' => [],
            'crm.members.index' => [],
            'crm.vouchers.index' => [],
            'storefront.orders.index' => [],
            'landing-page.edit' => [],
            'landing-page.popup.edit' => [],
            'storefront.reservations.index' => [],
            'storefront.shipping.index' => [],
            'storefront.settings.index' => [],
            'whatsapp.index' => [],
            'whatsapp.broadcast.index' => [],
            'whatsapp.logs.index' => [],
            'social-media.index' => [],
            'social-media.posts.index' => [],
            'social-media.calendar' => [],
            'social-media.inbox.index' => [],
            'finance.cash-bank.index' => [],
            'finance.expenses.index' => [],
            'finance.coa.index' => [],
            'finance.journals.index' => [],
            'finance.general-ledger' => [],
            'finance.balance-sheet' => [],
            'finance.trial-balance' => [],
            'finance.reconciliations.index' => [],
            'finance.receivables' => [],
            'finance.payables' => [],
            'finance.settlements.index' => [],
            'calculator.index' => [],
            'simulator.index' => [],
            'labor-machines.index' => [],
            'profitability.index' => [],
            'hrm.index' => [],
            'hrm.payrolls.index' => [],
            'tax.index' => [],
            'reports.index' => [],
            'analytics.index' => [],
            'pos.reports.index' => [],
            'profile.edit' => [],
            'settings.index' => [],
            'approval-rules.index' => [],
            'roles.index' => [],
            'billing.limits' => [],
            'feedback.bugs.index' => [],
            'billing.patungan' => [],
        ];

        $errors = [];
        $successes = [];

        foreach ($sidebarRoutes as $routeName => $params) {
            if (!Route::has($routeName)) {
                $errors[] = "Route [{$routeName}] is NOT registered in Laravel routes!";
                continue;
            }

            try {
                $url = route($routeName, $params);
                $response = $this->actingAs($this->owner)
                    ->withSession(['active_business_id' => $this->business->id])
                    ->get($url);

                $status = $response->getStatusCode();
                if ($status >= 500) {
                    $exception = $response->baseResponse->exception ?? null;
                    $errMsg = $exception ? $exception->getMessage() . " at " . $exception->getFile() . ":" . $exception->getLine() : substr(strip_tags($response->getContent()), 0, 300);
                    $errors[] = "Route [{$routeName}] ({$url}) returned HTTP {$status} ERROR: {$errMsg}";
                } elseif ($status >= 400 && $status < 500) {
                    $errors[] = "Route [{$routeName}] ({$url}) returned HTTP {$status} (Client error / forbidden / missing param)";
                } else {
                    $successes[] = "Route [{$routeName}] ({$url}) => {$status} OK";
                }
            } catch (\Throwable $e) {
                $errors[] = "Route [{$routeName}] THREW EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            }
        }

        echo "\n\n=== AUDIT RESULT: " . count($successes) . " PASSED, " . count($errors) . " FAILED ===\n";
        if (!empty($errors)) {
            echo "FAILED ROUTES:\n";
            foreach ($errors as $err) {
                echo "❌ " . $err . "\n";
            }
        }

        $this->assertEmpty($errors, "Some sidebar routes returned errors:\n" . implode("\n", $errors));
    }

    public function test_all_public_routes(): void
    {
        $publicRoutes = [
            'landing',
            'public.pricing',
            'public.about',
            'contact',
            'public.support',
            'public.privacy',
            'public.terms',
            'marketplace.index',
            'marketplace.search',
            'kalkulator.index',
            'kalkulator.hpp',
            'kalkulator.bep',
            'kalkulator.harga-jual',
            'kalkulator.laba-bersih',
            'template.index',
            'public.solutions.fnb',
            'blog.index',
        ];

        $errors = [];
        foreach ($publicRoutes as $routeName) {
            if (!Route::has($routeName)) {
                $errors[] = "Public route [{$routeName}] is not registered!";
                continue;
            }
            try {
                $url = route($routeName);
                $response = $this->get($url);
                $status = $response->getStatusCode();
                if ($status >= 500) {
                    $exception = $response->baseResponse->exception ?? null;
                    $errMsg = $exception ? $exception->getMessage() . " at " . $exception->getFile() . ":" . $exception->getLine() : substr(strip_tags($response->getContent()), 0, 300);
                    $errors[] = "Public route [{$routeName}] ({$url}) returned {$status} ERROR: {$errMsg}";
                }
            } catch (\Throwable $e) {
                $errors[] = "Public route [{$routeName}] EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            }
        }

        $this->assertEmpty($errors, "Some public routes returned errors:\n" . implode("\n", $errors));
    }

    public function test_all_admin_routes(): void
    {
        $adminRoutes = [
            'admin.login',
            'admin.dashboard',
            'admin.businesses.index',
            'admin.users.index',
            'admin.account-recoveries.index',
            'admin.ai-tokens.index',
            'admin.settings.index',
            'admin.smtp.index',
            'admin.payment-accounts.index',
            'admin.subscriptions.index',
            'admin.billing-packages.index',
            'admin.settlements.index',
            'admin.templates.index',
            'admin.leads.index',
            'admin.posts.index',
            'admin.legal-pages.index',
            'admin.feedback.bugs.index',
            'admin.feedback.features.index',
            'admin.error-logs.index',
            'admin.whatsapp.index',
            'admin.social-media.index',
            'admin.profile.index',
        ];

        $errors = [];
        foreach ($adminRoutes as $routeName) {
            if (!Route::has($routeName)) {
                $errors[] = "Admin route [{$routeName}] is not registered!";
                continue;
            }
            try {
                $url = route($routeName);
                $response = $this->actingAs($this->admin, 'admin')->get($url);
                $status = $response->getStatusCode();
                if ($status >= 500) {
                    $exception = $response->baseResponse->exception ?? null;
                    $errMsg = $exception ? $exception->getMessage() . " at " . $exception->getFile() . ":" . $exception->getLine() : substr(strip_tags($response->getContent()), 0, 300);
                    $errors[] = "Admin route [{$routeName}] ({$url}) returned {$status} ERROR: {$errMsg}";
                }
            } catch (\Throwable $e) {
                $errors[] = "Admin route [{$routeName}] EXCEPTION: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            }
        }

        $this->assertEmpty($errors, "Some admin routes returned errors:\n" . implode("\n", $errors));
    }
}
