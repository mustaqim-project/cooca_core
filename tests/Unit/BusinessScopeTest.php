<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Support\Context;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DummyTenantModel extends Model
{
    use BelongsToBusiness, HasUuid;

    protected $table = 'dummy_tenant_models';

    protected $guarded = [];
}

final class BusinessScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        Schema::create('dummy_tenant_models', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function test_it_filters_queries_automatically_by_active_business_context(): void
    {
        $bizA = new Business;
        $bizA->id = '11111111-1111-1111-1111-111111111111';
        $bizA->name = 'Business A';

        $bizB = new Business;
        $bizB->id = '22222222-2222-2222-2222-222222222222';
        $bizB->name = 'Business B';

        // Insert records under different businesses
        Context::setBusiness($bizA);
        DummyTenantModel::create(['title' => 'Item Biz A 1']);
        DummyTenantModel::create(['title' => 'Item Biz A 2']);

        Context::setBusiness($bizB);
        DummyTenantModel::create(['title' => 'Item Biz B 1']);

        // Assert query under Biz B context only sees 1 item
        $this->assertCount(1, DummyTenantModel::all());
        $this->assertSame('Item Biz B 1', DummyTenantModel::first()?->title);

        // Switch to Biz A context and assert only sees Biz A items
        Context::setBusiness($bizA);
        $this->assertCount(2, DummyTenantModel::all());
        $this->assertEqualsCanonicalizing(
            ['Item Biz A 1', 'Item Biz A 2'],
            DummyTenantModel::pluck('title')->all()
        );
    }

    public function test_it_can_bypass_scope_with_without_global_scopes(): void
    {
        $bizA = new Business;
        $bizA->id = '11111111-1111-1111-1111-111111111111';
        $bizA->name = 'Business A';

        $bizB = new Business;
        $bizB->id = '22222222-2222-2222-2222-222222222222';
        $bizB->name = 'Business B';

        Context::setBusiness($bizA);
        DummyTenantModel::create(['title' => 'Item A']);

        Context::setBusiness($bizB);
        DummyTenantModel::create(['title' => 'Item B']);

        $allItems = DummyTenantModel::withoutGlobalScopes()->get();
        $this->assertCount(2, $allItems);
    }
}
