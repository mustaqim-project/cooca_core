<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Approval\ApprovalWorkflowService;
use App\Models\ApprovalLog;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRule;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Context;
use Database\Seeders\DefaultCostCategorySeeder;
use Database\Seeders\DefaultUnitSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class DocumentApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private User $supervisor;
    private User $manager;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->seed(RbacSeeder::class);
        $this->seed(DefaultUnitSeeder::class);
        $this->seed(DefaultCostCategorySeeder::class);

        // Setup corporate business
        $this->business = Business::create([
            'name' => 'PT Nusantara Sejahtera',
            'business_scale' => 'corporate',
        ]);

        $this->owner = User::create([
            'name' => 'Direktur Utama',
            'email' => 'owner@nusantara.test',
            'password' => 'password123',
        ]);

        $this->supervisor = User::create([
            'name' => 'Spv Pengadaan',
            'email' => 'spv@nusantara.test',
            'password' => 'password123',
        ]);

        $this->manager = User::create([
            'name' => 'Manager Operasional',
            'email' => 'manager@nusantara.test',
            'password' => 'password123',
        ]);

        $this->staff = User::create([
            'name' => 'Staff Gudang Maker',
            'email' => 'maker@nusantara.test',
            'password' => 'password123',
        ]);

        // Attach roles in business
        $ownerRole = Role::where('slug', 'owner')->first();
        $managerRole = Role::where('slug', 'manager')->first();

        $spvRole = Role::firstOrCreate(
            ['slug' => 'supervisor', 'business_id' => null],
            ['name' => 'Supervisor', 'description' => 'Supervisor approval level 1']
        );
        $spvPermissions = \App\Models\Permission::whereIn('slug', ['approvals.view', 'approvals.manage', 'purchasing.view'])->pluck('id');
        $spvRole->permissions()->sync($spvPermissions);

        $staffRole = Role::where('slug', 'staff')->first() ?? Role::firstOrCreate(
            ['slug' => 'staff', 'business_id' => null],
            ['name' => 'Staff', 'description' => 'Staff']
        );

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'role_id' => $ownerRole?->id,
        ]);

        $this->business->users()->attach($this->supervisor->id, [
            'id' => (string) Str::uuid(),
            'role' => 'supervisor',
            'role_id' => $spvRole->id,
        ]);

        $this->business->users()->attach($this->manager->id, [
            'id' => (string) Str::uuid(),
            'role' => 'manager',
            'role_id' => $managerRole?->id,
        ]);

        $this->business->users()->attach($this->staff->id, [
            'id' => (string) Str::uuid(),
            'role' => 'staff',
            'role_id' => $staffRole->id,
        ]);

        $this->owner->update(['active_business_id' => $this->business->id]);
        $this->supervisor->update(['active_business_id' => $this->business->id]);
        $this->manager->update(['active_business_id' => $this->business->id]);
        $this->staff->update(['active_business_id' => $this->business->id]);
    }

    public function test_corporate_po_with_threshold_creates_pending_approval_request(): void
    {
        // 1. Create approval rule for PO >= 5,000,000 with 2 levels
        ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'min_amount' => 5000000,
            'max_amount' => null,
            'required_levels' => 2,
            'level_1_role' => 'supervisor',
            'level_2_role' => 'manager',
            'level_3_role' => null,
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'business_id' => $this->business->id,
            'name' => 'PT Mitra Supplier Utama',
        ]);

        // 2. Maker creates PO of 12,000,000 (exceeding min_amount 5M)
        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-TEST-001',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 12000000,
            'total_amount' => 12000000,
            'created_by' => $this->staff->id,
        ]);

        $service = app(ApprovalWorkflowService::class);
        $request = $service->evaluateAndCreateRequest($po, (float) $po->total_amount, $this->staff);

        $this->assertNotNull($request);
        $this->assertEquals(ApprovalRequest::STATUS_PENDING, $request->status);
        $this->assertEquals(1, $request->current_level);
        $this->assertEquals(2, $request->required_levels);
        $this->assertEquals($this->staff->id, $request->requester_id);
    }

    public function test_po_confirmation_is_blocked_when_approval_is_pending(): void
    {
        ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'min_amount' => 1000000,
            'required_levels' => 1,
            'level_1_role' => 'manager',
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-TEST-PENDING',
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 3000000,
            'total_amount' => 3000000,
            'created_by' => $this->staff->id,
        ]);

        $service = app(ApprovalWorkflowService::class);
        $service->evaluateAndCreateRequest($po, 3000000, $this->staff);

        // Manager attempts to confirm PO while pending approval
        $response = $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/purchase-orders/{$po->id}/confirm");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(PurchaseOrder::STATUS_DRAFT, $po->fresh()->status);
    }

    public function test_multi_level_approval_progression_and_release(): void
    {
        ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'min_amount' => 5000000,
            'required_levels' => 2,
            'level_1_role' => 'supervisor',
            'level_2_role' => 'manager',
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-MULTI-LEVEL',
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 8000000,
            'total_amount' => 8000000,
            'created_by' => $this->staff->id,
        ]);

        $service = app(ApprovalWorkflowService::class);
        $approvalRequest = $service->evaluateAndCreateRequest($po, 8000000, $this->staff);

        // Step 1: Supervisor approves Level 1
        $responseL1 = $this->actingAs($this->supervisor)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/approvals/{$approvalRequest->id}/approve", [
                'notes' => 'Disetujui spesifikasi barang oleh SPV',
            ]);

        $responseL1->assertRedirect();
        $responseL1->assertSessionHas('success');

        $approvalRequest->refresh();
        $this->assertEquals(ApprovalRequest::STATUS_PENDING, $approvalRequest->status);
        $this->assertEquals(2, $approvalRequest->current_level);

        $this->assertDatabaseHas('approval_logs', [
            'approval_request_id' => $approvalRequest->id,
            'approver_id' => $this->supervisor->id,
            'level' => 1,
            'action' => ApprovalLog::ACTION_APPROVED,
        ]);

        // Still blocked from confirmation because Level 2 is pending
        $blockedConfirm = $this->actingAs($this->staff)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/purchase-orders/{$po->id}/confirm");
        $blockedConfirm->assertSessionHas('error');
        $this->assertEquals(PurchaseOrder::STATUS_DRAFT, $po->fresh()->status);

        // Step 2: Manager approves Level 2
        $responseL2 = $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/approvals/{$approvalRequest->id}/approve", [
                'notes' => 'Disetujui anggaran final oleh Manager',
            ]);

        $responseL2->assertRedirect();
        $responseL2->assertSessionHas('success');

        $approvalRequest->refresh();
        $this->assertEquals(ApprovalRequest::STATUS_APPROVED, $approvalRequest->status);
        $this->assertNotNull($approvalRequest->approved_at);

        $this->assertDatabaseHas('approval_logs', [
            'approval_request_id' => $approvalRequest->id,
            'approver_id' => $this->manager->id,
            'level' => 2,
            'action' => ApprovalLog::ACTION_APPROVED,
        ]);

        // Step 3: Now confirmation succeeds by Releaser
        $confirmResponse = $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/purchase-orders/{$po->id}/confirm");

        $confirmResponse->assertRedirect();
        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->fresh()->status);
    }

    public function test_approval_rejection_workflow_stops_document_release(): void
    {
        ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'min_amount' => 1000000,
            'required_levels' => 1,
            'level_1_role' => 'supervisor',
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-REJECT-TEST',
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 2000000,
            'total_amount' => 2000000,
            'created_by' => $this->staff->id,
        ]);

        $service = app(ApprovalWorkflowService::class);
        $approvalRequest = $service->evaluateAndCreateRequest($po, 2000000, $this->staff);

        // Supervisor rejects ticket with mandatory reason
        $rejectResponse = $this->actingAs($this->supervisor)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/approvals/{$approvalRequest->id}/reject", [
                'notes' => 'Overbudget - vendor terlalu mahal',
            ]);

        $rejectResponse->assertRedirect();
        $rejectResponse->assertSessionHas('success');

        $approvalRequest->refresh();
        $this->assertEquals(ApprovalRequest::STATUS_REJECTED, $approvalRequest->status);
        $this->assertEquals('Overbudget - vendor terlalu mahal', $approvalRequest->rejection_reason);

        $this->assertDatabaseHas('approval_logs', [
            'approval_request_id' => $approvalRequest->id,
            'approver_id' => $this->supervisor->id,
            'action' => ApprovalLog::ACTION_REJECTED,
            'notes' => 'Overbudget - vendor terlalu mahal',
        ]);

        // Confirmation fails even when attempted by manager
        $confirmResponse = $this->actingAs($this->manager)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/purchase-orders/{$po->id}/confirm");

        $confirmResponse->assertSessionHas('error');
        $this->assertEquals(PurchaseOrder::STATUS_DRAFT, $po->fresh()->status);
    }

    public function test_maker_cannot_approve_own_document_unless_owner(): void
    {
        ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'min_amount' => 1000000,
            'required_levels' => 1,
            'level_1_role' => 'supervisor',
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-MAKER-SELF',
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 2500000,
            'total_amount' => 2500000,
            'created_by' => $this->supervisor->id, // SPV is the maker!
        ]);

        $service = app(ApprovalWorkflowService::class);
        $approvalRequest = $service->evaluateAndCreateRequest($po, 2500000, $this->supervisor);

        // Service check should strictly return false for self-approval
        $this->assertFalse($service->canUserApprove($approvalRequest, $this->supervisor));

        // Attempting to post approval should be forbidden
        $response = $this->actingAs($this->supervisor)
            ->withSession(['active_business_id' => $this->business->id])
            ->post("/approvals/{$approvalRequest->id}/approve", [
                'notes' => 'Mencoba approve dokumen sendiri',
            ]);

        $response->assertForbidden();
    }

    public function test_umkm_scale_bypasses_mandatory_multi_level_approval(): void
    {
        $umkmBiz = Business::create([
            'name' => 'Warung Kopi Berkah UMKM',
            'business_scale' => 'umkm',
        ]);

        $umkmOwner = User::create([
            'name' => 'Owner Warkop',
            'email' => 'warkop@umkm.test',
            'password' => 'password123',
        ]);

        $umkmBiz->users()->attach($umkmOwner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $umkmBiz->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-UMKM-001',
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 500000,
            'total_amount' => 500000,
            'created_by' => $umkmOwner->id,
        ]);

        // Evaluate workflow service
        $service = app(ApprovalWorkflowService::class);
        $request = $service->evaluateAndCreateRequest($po, 500000, $umkmOwner);

        // For UMKM without explicit rules, no approval ticket is created
        $this->assertNull($request);

        // Direct confirm works immediately
        $response = $this->actingAs($umkmOwner)
            ->withSession(['active_business_id' => $umkmBiz->id])
            ->post("/purchase-orders/{$po->id}/confirm");

        $response->assertRedirect();
        $this->assertEquals(PurchaseOrder::STATUS_CONFIRMED, $po->fresh()->status);
    }

    public function test_cross_tenant_isolation_prevents_unauthorized_approval(): void
    {
        $otherBiz = Business::create([
            'name' => 'PT Kompetitor Jaya',
            'business_scale' => 'corporate',
        ]);

        $otherUser = User::create([
            'name' => 'Manager Luar',
            'email' => 'manager@kompetitor.test',
            'password' => 'password123',
        ]);

        $managerRole = Role::where('slug', 'manager')->whereNull('business_id')->first();
        $otherBiz->users()->attach($otherUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'manager',
            'role_id' => $managerRole?->id,
        ]);
        $otherUser->update(['active_business_id' => $otherBiz->id]);

        ApprovalRule::create([
            'business_id' => $this->business->id,
            'document_type' => 'purchase_order',
            'min_amount' => 1000000,
            'required_levels' => 1,
            'level_1_role' => 'manager',
            'is_active' => true,
        ]);

        $po = PurchaseOrder::create([
            'business_id' => $this->business->id,
            'po_type' => 'supplier',
            'po_number' => 'PO-ISOLATION-01',
            'order_date' => now(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'subtotal' => 2000000,
            'total_amount' => 2000000,
            'created_by' => $this->staff->id,
        ]);

        $service = app(ApprovalWorkflowService::class);
        $approvalRequest = $service->evaluateAndCreateRequest($po, 2000000, $this->staff);

        // Attempt approval using other user belonging to other business
        $response = $this->actingAs($otherUser)
            ->withSession(['active_business_id' => $otherBiz->id])
            ->post("/approvals/{$approvalRequest->id}/approve", [
                'notes' => 'Mencoba akses cross tenant',
            ]);

        $response->assertNotFound();
    }

    public function test_approval_rules_crud_by_owner(): void
    {
        // 1. Index rules
        $indexResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('approval-rules.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Aturan Plafon Otorisasi Dokumen', false);

        // 2. Store rule
        $storeResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('approval-rules.store'), [
                'document_type' => 'expense',
                'min_amount' => 2000000,
                'max_amount' => 10000000,
                'required_levels' => 2,
                'level_1_role' => 'supervisor',
                'level_2_role' => 'manager',
                'is_active' => 1,
            ]);

        $storeResponse->assertRedirect(route('approval-rules.index'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('approval_rules', [
            'business_id' => $this->business->id,
            'document_type' => 'expense',
            'required_levels' => 2,
        ]);

        $rule = ApprovalRule::where('business_id', $this->business->id)->where('document_type', 'expense')->firstOrFail();

        // 3. Update rule
        $updateResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->put(route('approval-rules.update', $rule), [
                'min_amount' => 3000000,
                'max_amount' => 15000000,
                'required_levels' => 3,
                'level_1_role' => 'supervisor',
                'level_2_role' => 'manager',
                'level_3_role' => 'owner',
                'is_active' => 1,
            ]);

        $updateResponse->assertRedirect(route('approval-rules.index'));
        $this->assertEquals(3, $rule->fresh()->required_levels);
        $this->assertEquals(3000000, (float) $rule->fresh()->min_amount);

        // 4. Delete rule
        $deleteResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->delete(route('approval-rules.destroy', $rule));

        $deleteResponse->assertRedirect(route('approval-rules.index'));
        $this->assertDatabaseMissing('approval_rules', ['id' => $rule->id]);
    }

    public function test_approval_inbox_and_history_views_render_properly(): void
    {
        // 1. Inbox view
        $inboxResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('approvals.inbox'));

        $inboxResponse->assertOk();
        $inboxResponse->assertSee('Kotak Masuk Persetujuan', false);
        $inboxResponse->assertSee('Riwayat Selesai', false);

        // 2. History view
        $historyResponse = $this->actingAs($this->owner)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('approvals.history'));

        $historyResponse->assertOk();
        $historyResponse->assertSee('Riwayat Persetujuan Dokumen', false);
        $historyResponse->assertSee('Kembali ke Kotak Masuk', false);
    }
}
