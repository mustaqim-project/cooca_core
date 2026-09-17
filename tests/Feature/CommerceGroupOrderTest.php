<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Commerce\GroupOrder\CommerceGroupOrderService;
use App\Models\Business;
use App\Models\CommerceGroupOrder;
use App\Models\CommerceGroupOrderItem;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\GlobalCustomer;
use App\Models\Location;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Database\Seeders\DefaultUnitSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CommerceGroupOrderTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $merchantUser;
    private Location $location;
    private CommercePaymentMethod $paymentMethod;
    private Product $product1;
    private Product $product2;
    private GlobalCustomer $hostCustomer;
    private GlobalCustomer $memberCustomer;
    private GlobalCustomer $memberCustomer2;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        Carbon::setTestNow('2026-09-16 10:00:00'); // Wednesday

        $this->seed(DefaultUnitSeeder::class);

        $this->merchantUser = User::create([
            'name' => 'Owner Dapur',
            'email' => 'owner@dapur.test',
            'phone' => '08123456780',
            'password' => bcrypt('secret123'),
        ]);
        $this->merchantUser->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Dapur Sedap Rasa Group',
            'slug' => 'dapur-sedap-rasa-group',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
            'allow_negative_stock' => true,
        ]);

        $this->business->users()->attach($this->merchantUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);
        $this->merchantUser->update(['active_business_id' => $this->business->id]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Pusat',
            'is_primary' => true,
            'is_active' => true,
        ]);

        CommerceStoreSetting::create([
            'business_id' => $this->business->id,
            'is_storefront_enabled' => true,
            'is_discoverable' => true,
            'allow_pickup' => true,
            'allow_delivery' => true,
            'allow_customer_po' => true,
            'daily_order_quota' => 150,
            'quota_metric' => 'quantity',
            'preorder_quota_unit' => 'PCS',
            'operating_days' => ['friday'],
        ]);

        $this->paymentMethod = CommercePaymentMethod::create([
            'business_id' => $this->business->id,
            'type' => 'bank_transfer',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Dapur Sedap Rasa',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::create(['name' => 'Pieces', 'code' => 'pcs']);

        $this->product1 = Product::create([
            'business_id' => $this->business->id,
            'code' => 'PRD-01',
            'name' => 'Nasi Box Ayam Bakar',
            'type' => 'goods',
            'output_unit_id' => $pcs->id,
            'selling_price' => 35000,
            'is_active' => true,
            'is_preorder' => true,
        ]);

        $this->product2 = Product::create([
            'business_id' => $this->business->id,
            'code' => 'PRD-02',
            'name' => 'Nasi Liwet Komplit',
            'type' => 'goods',
            'output_unit_id' => $pcs->id,
            'selling_price' => 40000,
            'is_active' => true,
            'is_preorder' => true,
        ]);

        $this->hostCustomer = GlobalCustomer::create([
            'name' => 'Ahmad Pratama (Host Mandiri)',
            'email' => 'ahmad.host@mandiri.id',
            'phone' => '081234567890',
            'password' => Hash::make('password'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $this->memberCustomer = GlobalCustomer::create([
            'name' => 'Budi Rekan (Mandiri)',
            'email' => 'budi.member@mandiri.id',
            'phone' => '081987654321',
            'password' => Hash::make('password'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $this->memberCustomer2 = GlobalCustomer::create([
            'name' => 'Siti Rekan (Mandiri)',
            'email' => 'siti.member@mandiri.id',
            'phone' => '081122334455',
            'password' => Hash::make('password'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_authenticated_customer_can_create_group_order_session(): void
    {
        $response = $this->actingAs($this->hostCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order", [
                'title' => 'Makan Siang Kantor Mandiri Lt. 8',
                'scheduled_date' => '2026-09-18',
                'delivery_address' => 'Plaza Mandiri Lantai 8, Jl. Gatot Subroto',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('share_token'));

        $this->assertDatabaseHas('commerce_group_orders', [
            'business_id' => $this->business->id,
            'host_customer_id' => $this->hostCustomer->id,
            'title' => 'Makan Siang Kantor Mandiri Lt. 8',
            'status' => CommerceGroupOrder::STATUS_OPEN,
        ]);
    }

    public function test_members_can_add_different_items_to_shared_group_cart(): void
    {
        $service = new CommerceGroupOrderService();
        $group = $service->createGroup($this->business, $this->hostCustomer, [
            'title' => 'Makan Siang Tim Mandiri',
            'scheduled_date' => '2026-09-18',
        ]);

        // 1. Host Ahmad adds 1x Nasi Box Ayam Bakar (Pedas)
        $this->actingAs($this->hostCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/items", [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'notes' => 'Pedas banget ya',
            ])
            ->assertStatus(200);

        // 2. Member Budi adds 2x Nasi Liwet Komplit (Tanpa timun)
        $this->actingAs($this->memberCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/items", [
                'product_id' => $this->product2->id,
                'quantity' => 2,
                'notes' => 'Tanpa timun',
            ])
            ->assertStatus(200);

        // 3. Member Siti adds 1x Nasi Box Ayam Bakar (Manis)
        $this->actingAs($this->memberCustomer2, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/items", [
                'product_id' => $this->product1->id,
                'quantity' => 1,
                'notes' => 'Manis gurih',
            ])
            ->assertStatus(200);

        $this->assertDatabaseCount('commerce_group_order_items', 3);

        $group->refresh();
        $this->assertEquals(4, $group->total_quantity); // 1 + 2 + 1 = 4
        $this->assertEquals(150000, $group->subtotal); // 35k + (2*40k) + 35k = 150k
        $this->assertEquals(3, $group->members_count);
    }

    public function test_split_bill_correctly_groups_items_and_subtotals_per_member(): void
    {
        $service = new CommerceGroupOrderService();
        $group = $service->createGroup($this->business, $this->hostCustomer, [
            'title' => 'Makan Siang Tim Mandiri',
        ]);

        $service->addItem($group, $this->hostCustomer, $this->product1, 2, 'Ahmad 2 porsi');
        $service->addItem($group, $this->memberCustomer, $this->product2, 1, 'Budi 1 porsi');

        $splitBill = $group->getSplitBillSummary();

        $this->assertCount(2, $splitBill);

        // Host is first
        $this->assertSame($this->hostCustomer->id, $splitBill[0]['customer_id']);
        $this->assertTrue($splitBill[0]['is_host']);
        $this->assertEquals(2, $splitBill[0]['item_count']);
        $this->assertEquals(70000, $splitBill[0]['subtotal']); // 2 * 35000

        // Budi is second
        $this->assertSame($this->memberCustomer->id, $splitBill[1]['customer_id']);
        $this->assertFalse($splitBill[1]['is_host']);
        $this->assertEquals(1, $splitBill[1]['item_count']);
        $this->assertEquals(40000, $splitBill[1]['subtotal']); // 1 * 40000
    }

    public function test_member_can_only_modify_own_item_while_host_can_manage_all(): void
    {
        $service = new CommerceGroupOrderService();
        $group = $service->createGroup($this->business, $this->hostCustomer, ['title' => 'Makan Bareng']);

        $itemAhmad = $service->addItem($group, $this->hostCustomer, $this->product1, 1);
        $itemBudi = $service->addItem($group, $this->memberCustomer, $this->product2, 1);

        // Budi tries to delete Ahmad's item -> Forbidden (422 DomainException)
        $this->actingAs($this->memberCustomer, 'customer')
            ->deleteJson("/b/{$this->business->slug}/group-order/{$group->share_token}/items/{$itemAhmad->id}")
            ->assertStatus(422)
            ->assertJson(['success' => false]);

        // Budi updates his own item -> OK
        $this->actingAs($this->memberCustomer, 'customer')
            ->putJson("/b/{$this->business->slug}/group-order/{$group->share_token}/items/{$itemBudi->id}", [
                'quantity' => 3,
                'notes' => 'Budi ganti jadi 3',
            ])
            ->assertStatus(200);

        $this->assertEquals(3, $itemBudi->fresh()->quantity);

        // Host Ahmad can remove Budi's item if needed -> OK
        $this->actingAs($this->hostCustomer, 'customer')
            ->deleteJson("/b/{$this->business->slug}/group-order/{$group->share_token}/items/{$itemBudi->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('commerce_group_order_items', ['id' => $itemBudi->id]);
    }

    public function test_host_can_lock_and_unlock_group_order(): void
    {
        $service = new CommerceGroupOrderService();
        $group = $service->createGroup($this->business, $this->hostCustomer, ['title' => 'Makan Bareng']);

        // Non-host tries to lock -> 422
        $this->actingAs($this->memberCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/lock")
            ->assertStatus(422);

        // Host locks -> OK
        $this->actingAs($this->hostCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/lock")
            ->assertStatus(200);

        $this->assertTrue($group->fresh()->isLocked());

        // While locked, member cannot add items
        $this->actingAs($this->memberCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/items", [
                'product_id' => $this->product1->id,
                'quantity' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Pesanan bersama ini telah dikunci atau diselesaikan oleh Host.']);

        // Host unlocks -> OK
        $this->actingAs($this->hostCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/unlock")
            ->assertStatus(200);

        $this->assertTrue($group->fresh()->isOpen());
    }

    public function test_host_checkout_group_order_creates_single_commerce_order_with_member_notes(): void
    {
        $service = new CommerceGroupOrderService();
        $group = $service->createGroup($this->business, $this->hostCustomer, [
            'title' => 'Makan Siang Mandiri Lt 8',
            'scheduled_date' => '2026-09-18',
            'delivery_address' => 'Plaza Mandiri Lantai 8',
        ]);

        $service->addItem($group, $this->hostCustomer, $this->product1, 1, 'Pedas');
        $service->addItem($group, $this->memberCustomer, $this->product2, 2, 'Tanpa timun');

        $response = $this->actingAs($this->hostCustomer, 'customer')
            ->postJson("/b/{$this->business->slug}/group-order/{$group->share_token}/checkout", [
                'payment_method_id' => $this->paymentMethod->id,
                'delivery_address' => 'Plaza Mandiri Lantai 8, Jakarta',
                'delivery_notes' => 'Titip di satpam lobi timur',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('tracking_url'));

        $group->refresh();
        $this->assertTrue($group->isCheckedOut());
        $this->assertNotNull($group->commerce_order_id);

        $order = CommerceOrder::find($group->commerce_order_id);
        $this->assertNotNull($order);
        $this->assertSame('Ahmad Pratama (Host Mandiri)', $order->customer_name);
        $this->assertEquals(115000, $order->total_amount); // 35k + (2*40k) = 115k
        $this->assertStringContainsString('Pesanan Bersama: Makan Siang Mandiri Lt 8', $order->notes);

        $order->load('items');
        $this->assertCount(2, $order->items);
        $this->assertStringContainsString('[Ahmad Pratama (Host Mandiri)]', $order->items[0]->notes);
        $this->assertStringContainsString('[Budi Rekan (Mandiri)]', $order->items[1]->notes);
    }

    public function test_group_order_respects_batch_quota_and_rejects_when_overbooked(): void
    {
        $service = new CommerceGroupOrderService();

        // 1. Group Mandiri orders 100 PCS
        $groupMandiri = $service->createGroup($this->business, $this->hostCustomer, [
            'title' => 'Grup Kantor Mandiri',
            'scheduled_date' => '2026-09-18',
        ]);
        $service->addItem($groupMandiri, $this->hostCustomer, $this->product1, 100);

        $orderMandiri = $service->checkoutGroup($groupMandiri, $this->hostCustomer, [
            'payment_method_id' => $this->paymentMethod->id,
            'delivery_address' => 'Plaza Mandiri Lt 8',
        ]);
        $this->assertNotNull($orderMandiri);

        // 2. Group BCA orders 50 PCS
        $hostBca = GlobalCustomer::create([
            'name' => 'Budi BCA (Host)',
            'email' => 'bca.host@bca.co.id',
            'phone' => '081888999000',
            'password' => Hash::make('password'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $groupBca = $service->createGroup($this->business, $hostBca, [
            'title' => 'Grup Kantor BCA',
            'scheduled_date' => '2026-09-18',
        ]);
        $service->addItem($groupBca, $hostBca, $this->product2, 50);

        $orderBca = $service->checkoutGroup($groupBca, $hostBca, [
            'payment_method_id' => $this->paymentMethod->id,
            'delivery_address' => 'Menara BCA Lt 12',
        ]);
        $this->assertNotNull($orderBca);

        // 3. Group BNI tries to order 1 PCS -> Must be rejected with quota exception
        $hostBni = GlobalCustomer::create([
            'name' => 'Charlie BNI (Host)',
            'email' => 'bni.host@bni.co.id',
            'phone' => '081777888999',
            'password' => Hash::make('password'),
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
        ]);

        $groupBni = $service->createGroup($this->business, $hostBni, [
            'title' => 'Grup Kantor BNI',
            'scheduled_date' => '2026-09-18',
        ]);
        $service->addItem($groupBni, $hostBni, $this->product1, 1);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Sisa kuota untuk batch tanggal 2026-09-18');
        $this->expectExceptionMessage('tersisa 0 PCS');

        $service->checkoutGroup($groupBni, $hostBni, [
            'payment_method_id' => $this->paymentMethod->id,
            'delivery_address' => 'Grha BNI',
        ]);
    }
}
