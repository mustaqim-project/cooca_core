<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Pos\ModifierService;
use App\Models\Business;
use App\Models\Material;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosModifierStockAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Product $product;
    private ModifierGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->owner = User::create([
            'name' => 'Owner Cafe',
            'email' => 'owner.cafe@example.com',
            'password' => 'secret123',
        ]);

        $this->business = Business::create([
            'name' => 'Cafe Modifiers Test',
        ]);

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'PORSI',
            'symbol' => 'porsi',
            'category' => 'quantity',
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman',
        ]);

        $this->product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Kopi Tubruk',
            'code' => 'TUB-01',
            'type' => 'finished_good',
            'selling_price' => 15000,
            'is_active' => true,
        ]);

        $this->group = ModifierGroup::create([
            'business_id' => $this->business->id,
            'name' => 'Tingkat Manis',
            'selection_type' => 'single',
            'min_selection' => 1,
            'max_selection' => 1,
            'is_required' => true,
            'is_active' => true,
        ]);

        $this->group->products()->attach($this->product->id, ['id' => (string) Str::uuid()]);
    }

    public function test_modifier_without_material_deduction_is_always_in_stock_and_available(): void
    {
        $option = ModifierOption::create([
            'modifier_group_id' => $this->group->id,
            'name' => 'Normal Sugar (Tidak Potong Stok)',
            'price_delta' => 0,
            'affects_material' => false,
            'is_active' => true,
        ]);

        $this->assertTrue($option->isAvailableInStock());

        $groupsWithStock = $this->product->getAvailableModifierGroupsWithStock();
        $this->assertNotEmpty($groupsWithStock);

        $firstGroup = $groupsWithStock[0];
        $firstOpt = $firstGroup['options'][0];

        // Ensure both is_available and is_in_stock are true so POS and QR never block it
        $this->assertTrue($firstOpt['is_available']);
        $this->assertTrue($firstOpt['is_in_stock']);
        $this->assertFalse($firstOpt['affects_material']);
    }

    public function test_updating_modifier_to_not_affect_materials_deletes_residual_recipe_materials(): void
    {
        $unitGram = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Gram',
            'code' => 'GR',
            'symbol' => 'g',
            'category' => 'weight',
        ]);

        $material = Material::create([
            'business_id' => $this->business->id,
            'name' => 'Gula Pasir',
            'code' => 'GUL-01',
            'unit_id' => $unitGram->id,
        ]);

        $service = new ModifierService();
        $option = $service->addOption($this->group, [
            'name' => 'Ekstra Gula',
            'price_delta' => 2000,
            'affects_material' => true,
            'materials' => [
                [
                    'material_id' => $material->id,
                    'quantity' => 20,
                    'unit_id' => $unitGram->id,
                ],
            ],
        ]);

        $this->assertCount(1, $option->materials);

        // Update to NOT affect materials (affects_material = false)
        $service->updateOption($option, [
            'affects_material' => false,
        ]);

        $option->refresh();
        $this->assertFalse($option->affects_material);
        $this->assertCount(0, $option->materials);
        $this->assertTrue($option->isAvailableInStock());
    }
}
