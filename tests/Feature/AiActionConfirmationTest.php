<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Ai\AiSalesAnalysisService;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AiActionConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private Customer $customer;
    private Product $product;
    private AiSalesAnalysisService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Owner Cafe',
            'email' => 'owner_cafe@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create(['name' => 'Cooca Cafe']);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->customer = Customer::create([
            'business_id' => $this->business->id,
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'code' => 'CUP',
            'name' => 'Cup',
            'symbol' => 'cup',
            'category' => Unit::CATEGORY_QUANTITY,
        ]);

        $cat = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Kopi',
            'slug' => 'kopi',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'output_unit_id' => $unit->id,
            'code' => 'KOP-01',
            'name' => 'Kopi Susu Gula Aren',
            'selling_price' => 20000,
            'base_cost' => 8000,
            'is_active' => true,
        ]);

        $this->aiService = new AiSalesAnalysisService();
    }

    public function test_ai_creates_action_proposal_requiring_confirmation(): void
    {
        $response = $this->aiService->askNaturalLanguage($this->business, 'Tolong buatkan draf invoice');

        $this->assertEquals('action_proposal', $response['type']);
        $this->assertEquals('create_invoice', $response['action_type']);
        $this->assertTrue($response['requires_confirmation']);
        $this->assertArrayHasKey('payload', $response);
        $this->assertArrayHasKey('action_id', $response);

        // Verify NO invoice created yet (Human-in-the-Loop safety)
        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_user_can_confirm_and_execute_ai_action_draft(): void
    {
        $this->actingAs($this->user);
        session(['active_business_id' => $this->business->id]);

        $response = $this->postJson(route('pos.ai.execute-action'), [
            'action_type' => 'create_invoice',
            'payload' => [
                'customer_id' => $this->customer->id,
                'customer_name' => $this->customer->name,
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'quantity' => 10,
                'unit_price' => 20000,
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify invoice is NOW created in database
        $this->assertDatabaseHas('invoices', [
            'business_id' => $this->business->id,
            'customer_id' => $this->customer->id,
            'total_amount' => 200000,
            'total_hpp_cost' => 80000,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'unit_price' => 20000,
        ]);
    }
}
