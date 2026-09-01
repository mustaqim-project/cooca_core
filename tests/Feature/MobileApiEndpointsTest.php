<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\Category;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class MobileApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Business $business;
    private string $token;
    private Location $location;
    private Unit $unit;
    private Product $product;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Mobile Tester',
            'email' => 'mobile@cooca.id',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Cooca Coffee Mobile',
            'currency' => 'IDR',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        BusinessSubscription::create([
            'business_id' => $this->business->id,
            'plan_code' => BusinessSubscription::PLAN_CORE,
            'status' => BusinessSubscription::STATUS_ACTIVE,
            'ai_tokens_monthly_allowance' => 10000000,
            'ai_tokens_remaining' => 10000000,
            'starts_at' => now(),
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Main Outlet',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'porsi',
            'category' => Unit::CATEGORY_QUANTITY,
            'default_precision' => 0,
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi Susu Gula Aren',
            'selling_price' => 20000,
            'base_cost' => 8000,
            'output_unit_id' => $this->unit->id,
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->token = $this->user->createToken('mobile-token')->plainTextToken;
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Business-Id' => $this->business->id,
            'Accept' => 'application/json',
        ];
    }

    public function test_mobile_dashboard_summary(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'business' => ['id', 'name', 'currency', 'currency_symbol'],
                'pos' => ['today_transactions', 'today_revenue', 'month_transactions', 'month_revenue'],
                'invoicing' => ['unpaid_count', 'total_receivable', 'overdue_count', 'payments_this_month'],
                'finance' => ['net_revenue_this_month', 'expenses_this_month', 'net_profit_this_month'],
                'inventory' => ['low_stock_count'],
                'purchasing' => ['open_purchase_orders'],
                'customers' => ['total_active', 'new_this_month'],
                'revenue_trend',
                'top_products',
                'generated_at',
            ]);
    }

    public function test_mobile_profile_and_business(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'businesses',
            ]);

        $updateResponse = $this->withHeaders($this->authHeaders())
            ->putJson('/api/v1/profile', [
                'name' => 'Mobile Tester Updated',
            ]);

        $updateResponse->assertOk()
            ->assertJsonPath('user.name', 'Mobile Tester Updated');
    }

    public function test_mobile_pos_terminal_and_checkout(): void
    {
        $bootstrap = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/pos/terminal/bootstrap');

        $bootstrap->assertOk()
            ->assertJsonStructure(['business', 'user', 'active_shift', 'recent_held_orders']);

        $search = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/pos/terminal/search-products?q=Kopi');

        $search->assertOk()
            ->assertJsonStructure(['products']);

        $checkout = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/pos/terminal/checkout', [
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'product_name' => $this->product->name,
                        'unit_price' => 20000,
                        'quantity' => 2,
                    ],
                ],
                'payments' => [
                    [
                        'payment_method' => 'cash',
                        'amount' => 40000,
                        'tendered' => 50000,
                    ],
                ],
                'customer_id' => $this->customer->id,
            ]);

        $checkout->assertCreated()
            ->assertJsonStructure(['message', 'order']);
    }

    public function test_mobile_inventory_stocks_and_adjustments(): void
    {
        $stocks = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/inventory/stocks');

        $stocks->assertOk()
            ->assertJsonStructure(['stocks', 'pagination']);

        $adjust = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/inventory/adjustments', [
                'product_id' => $this->product->id,
                'location_id' => $this->location->id,
                'type' => 'in',
                'quantity' => 100,
                'reason' => 'Stok awal modal',
            ]);

        $adjust->assertOk()
            ->assertJsonPath('new_quantity', 100);
    }

    public function test_mobile_purchasing_instant_stock_in(): void
    {
        $supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'Supplier Utama',
        ]);

        $stockIn = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/purchasing/stock-in', [
                'supplier_id' => $supplier->id,
                'location_id' => $this->location->id,
                'receipt_date' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'product_name' => $this->product->name,
                        'unit_id' => $this->unit->id,
                        'quantity' => 50,
                        'unit_cost' => 7500,
                    ],
                ],
            ]);

        $stockIn->assertCreated()
            ->assertJsonStructure(['message', 'receipt']);
    }

    public function test_mobile_sales_quotations_and_invoices(): void
    {
        $quotation = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/sales/quotations', [
                'customer_id' => $this->customer->id,
                'date' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'unit_price' => 20000,
                        'quantity' => 5,
                    ],
                ],
            ]);

        $quotation->assertCreated()
            ->assertJsonStructure(['message', 'quotation']);

        $invoices = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/sales/invoices');

        $invoices->assertOk()
            ->assertJsonStructure(['invoices', 'kpi', 'pagination']);
    }

    public function test_mobile_crm_customers_and_loyalty(): void
    {
        $list = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/crm/customers');

        $list->assertOk()
            ->assertJsonStructure(['customers', 'pagination']);

        $loyalty = $this->withHeaders($this->authHeaders())
            ->getJson("/api/v1/crm/loyalty/customers/{$this->customer->id}");

        $loyalty->assertOk()
            ->assertJsonStructure(['customer', 'points_balance', 'transactions']);
    }

    public function test_mobile_finance_dashboard_and_expenses(): void
    {
        $dashboard = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/finance/dashboard');

        $dashboard->assertOk()
            ->assertJsonStructure(['period', 'summary', 'expenses_by_category', 'revenue_trend']);

        $expense = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/finance/expenses', [
                'expense_date' => now()->toDateString(),
                'category' => 'Operasional',
                'amount' => 50000,
                'payment_method' => 'cash',
                'description' => 'Beli kantong plastik & es batu',
            ]);

        $expense->assertCreated()
            ->assertJsonStructure(['message', 'expense']);
    }

    public function test_mobile_ai_assistant_endpoints(): void
    {
        $tokenUsage = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/ai/token-usage');

        $tokenUsage->assertOk()
            ->assertJsonStructure(['plan', 'is_core_plan', 'ai_tokens_monthly_allowance', 'ai_tokens_remaining']);

        $forecasting = $this->withHeaders($this->authHeaders())
            ->getJson('/api/v1/ai/forecasting?days=7');

        $forecasting->assertOk()
            ->assertJsonStructure(['forecast', 'total_forecast_revenue', 'trend_direction']);

        $chat = $this->withHeaders($this->authHeaders())
            ->postJson('/api/v1/ai/chat', [
                'query' => 'Berapa penjualan hari ini?',
            ]);

        $chat->assertOk()
            ->assertJsonStructure(['headline', 'details']);
    }
}
