<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Traits\HasSlug;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DummyProductModel extends Model
{
    use HasSlug, HasUuid;

    protected $table = 'dummy_product_models';

    protected $guarded = [];
}

final class HasSlugTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('dummy_product_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('business_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });
    }

    public function test_it_generates_slug_from_name_automatically(): void
    {
        $product = DummyProductModel::create(['name' => 'Nasi Goreng Spesial']);

        $this->assertSame('nasi-goreng-spesial', $product->slug);
    }

    public function test_it_appends_numeric_suffix_when_duplicate_slug_exists(): void
    {
        $product1 = DummyProductModel::create(['name' => 'Kopi Susu']);
        $product2 = DummyProductModel::create(['name' => 'Kopi Susu']);
        $product3 = DummyProductModel::create(['name' => 'Kopi Susu']);

        $this->assertSame('kopi-susu', $product1->slug);
        $this->assertSame('kopi-susu-2', $product2->slug);
        $this->assertSame('kopi-susu-3', $product3->slug);
    }

    public function test_it_respects_tenant_scoping_for_slug_uniqueness(): void
    {
        $businessA = '00000000-0000-0000-0000-000000000001';
        $businessB = '00000000-0000-0000-0000-000000000002';

        $productA = DummyProductModel::create([
            'business_id' => $businessA,
            'name' => 'Roti Bakar',
        ]);

        $productB = DummyProductModel::create([
            'business_id' => $businessB,
            'name' => 'Roti Bakar',
        ]);

        $this->assertSame('roti-bakar', $productA->slug);
        $this->assertSame('roti-bakar', $productB->slug);
    }
}
