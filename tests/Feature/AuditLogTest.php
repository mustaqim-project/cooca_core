<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Traits\Auditable;
use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use App\Models\User;
use App\Support\Context;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AuditableSampleItem extends Model
{
    use Auditable, BelongsToBusiness, HasUuid;

    protected $table = 'auditable_sample_items';

    protected $guarded = [];
}

final class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        Schema::create('auditable_sample_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });
    }

    public function test_creating_and_updating_auditable_model_automatically_writes_audit_logs(): void
    {
        $user = User::create([
            'name' => 'Owner Audit',
            'email' => 'audit_owner@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create(['name' => 'Resto Audit']);
        $business->users()->attach($user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        Context::setBusiness($business);
        $this->actingAs($user, 'sanctum');

        // Create item
        $item = AuditableSampleItem::create([
            'name' => 'Item Original',
            'price' => 15000.00,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'auditable_type' => AuditableSampleItem::class,
            'auditable_id' => $item->id,
            'action' => 'created',
        ]);

        // Update item
        $item->update(['name' => 'Item Updated', 'price' => 20000.00]);

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'auditable_type' => AuditableSampleItem::class,
            'auditable_id' => $item->id,
            'action' => 'updated',
        ]);

        // Delete item
        $item->delete();

        $this->assertDatabaseHas('audit_logs', [
            'business_id' => $business->id,
            'auditable_type' => AuditableSampleItem::class,
            'auditable_id' => $item->id,
            'action' => 'deleted',
        ]);
    }

    public function test_owner_can_fetch_audit_logs_while_staff_is_forbidden(): void
    {
        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner_audit_api@example.com',
            'password' => 'password123',
        ]);

        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff_audit_api@example.com',
            'password' => 'password123',
        ]);

        $business = Business::create(['name' => 'Kafe Audit']);
        $business->users()->attach($owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $business->users()->attach($staff->id, ['id' => (string) Str::uuid(), 'role' => 'staff']);

        $owner->update(['active_business_id' => $business->id]);
        $staff->update(['active_business_id' => $business->id]);

        // Owner request
        $this->actingAs($owner, 'sanctum')
            ->withHeader('X-Business-Id', $business->id)
            ->getJson('/api/v1/audit-logs')
            ->assertOk()
            ->assertJsonStructure(['data', 'pagination']);

        // Staff request is forbidden (403)
        $this->actingAs($staff, 'sanctum')
            ->withHeader('X-Business-Id', $business->id)
            ->getJson('/api/v1/audit-logs')
            ->assertForbidden();
    }
}
