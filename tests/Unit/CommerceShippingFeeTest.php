<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Commerce\Storefront\CommerceShippingService;
use App\Models\Business;
use App\Models\CommerceShippingRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceShippingFeeTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private CommerceShippingService $shippingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Toko Kopi Sejahtera',
            'slug' => 'toko-kopi-sejahtera',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        $this->shippingService = new CommerceShippingService();
    }

    public function test_flat_shipping_rule_calculates_correct_fee(): void
    {
        $rule = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Instan Flat',
            'rule_type' => CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 15000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertEquals(15000, $rule->calculateRate(50000));
        $this->assertFalse($rule->isFreeFor(50000));

        $quote = $this->shippingService->calculateShipping($this->business, 50000);
        $this->assertEquals(15000, $quote['shipping_fee']);
        $this->assertFalse($quote['is_free']);
        $this->assertEquals($rule->id, $quote['applied_rule_id']);
    }

    public function test_free_threshold_rule_triggers_zero_fee_when_subtotal_qualifies(): void
    {
        $rule = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Gratis Ongkir Promo',
            'rule_type' => CommerceShippingRule::TYPE_FREE_THRESHOLD,
            'rate_amount' => 20000,
            'min_order_for_free' => 100000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Subtotal below threshold
        $this->assertEquals(20000, $rule->calculateRate(75000));
        $this->assertFalse($rule->isFreeFor(75000));

        // Subtotal meets or exceeds threshold
        $this->assertEquals(0.0, $rule->calculateRate(100000));
        $this->assertTrue($rule->isFreeFor(100000));

        $this->assertEquals(0.0, $rule->calculateRate(150000));
        $this->assertTrue($rule->isFreeFor(150000));

        $quote = $this->shippingService->calculateShipping($this->business, 120000);
        $this->assertEquals(0.0, $quote['shipping_fee']);
        $this->assertTrue($quote['is_free']);
    }

    public function test_distance_tier_shipping_rules(): void
    {
        $tierNear = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Radius Dekat (0-5 KM)',
            'rule_type' => CommerceShippingRule::TYPE_DISTANCE_TIER,
            'min_distance_km' => 0.0,
            'max_distance_km' => 5.0,
            'rate_amount' => 10000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $tierFar = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Radius Menengah (5.1-10 KM)',
            'rule_type' => CommerceShippingRule::TYPE_DISTANCE_TIER,
            'min_distance_km' => 5.1,
            'max_distance_km' => 10.0,
            'rate_amount' => 25000,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // At 3 km distance: tierNear matches, tierFar returns null
        $this->assertEquals(10000, $tierNear->calculateRate(50000, 3.0));
        $this->assertNull($tierFar->calculateRate(50000, 3.0));

        // At 7 km distance: tierFar matches, tierNear returns null
        $this->assertNull($tierNear->calculateRate(50000, 7.0));
        $this->assertEquals(25000, $tierFar->calculateRate(50000, 7.0));

        // Service calculation at 7 km
        $quote = $this->shippingService->calculateShipping($this->business, 50000, 7.0);
        $this->assertEquals(25000, $quote['shipping_fee']);
        $this->assertEquals($tierFar->id, $quote['applied_rule_id']);
        $this->assertCount(1, $quote['options']); // Only tierFar matched
    }

    public function test_inactive_shipping_rules_are_ignored(): void
    {
        CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Non-Aktif',
            'rule_type' => CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 5000,
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $quote = $this->shippingService->calculateShipping($this->business, 50000);
        $this->assertEmpty($quote['options']);
        $this->assertEquals(0.0, $quote['shipping_fee']);
    }

    public function test_preferred_rule_selection_is_honored(): void
    {
        $ruleEconomy = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Reguler Toko',
            'rule_type' => CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 10000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $ruleExpress = CommerceShippingRule::create([
            'business_id' => $this->business->id,
            'name' => 'Kurir Kilat Toko',
            'rule_type' => CommerceShippingRule::TYPE_FLAT,
            'rate_amount' => 25000,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $quoteExpress = $this->shippingService->calculateShipping(
            $this->business,
            50000,
            null,
            $ruleExpress->id
        );

        $this->assertEquals(25000, $quoteExpress['shipping_fee']);
        $this->assertEquals($ruleExpress->id, $quoteExpress['applied_rule_id']);
    }
}
