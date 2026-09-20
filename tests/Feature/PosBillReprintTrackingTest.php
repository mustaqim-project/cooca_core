<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Pos\PosReceiptImageService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosBillReprintTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private Business $business;
    private Location $location;
    private PosOrder $order;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->cashier = User::create([
            'name' => 'Kasir Alpha',
            'email' => 'kasir.alpha@example.com',
            'phone' => '081299990001',
            'password' => 'password123',
        ]);
        $this->cashier->forceFill(['email_verified_at' => now()])->save();

        $this->business = Business::create([
            'name' => 'Resto Sedap Nusantara',
            'address' => 'Jl. Kuliner No. 10, Bandung',
            'phone' => '0221234567',
            'pos_receipt_footer_note' => 'Terima kasih atas kunjungan Anda!',
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Cabang Utama',
            'code' => 'BDG-01',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Porsi',
            'code' => 'PRS',
            'symbol' => 'prs',
            'category' => 'quantity',
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Makanan Utama',
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Nasi Goreng Spesial',
            'code' => 'NASGOR-01',
            'product_category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'sale_price' => 35000,
            'is_active' => true,
        ]);

        $this->order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-20260921-001',
            'order_date' => now()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'dine_in',
            'table_or_reference' => 'Meja 05',
            'customer_name_guest' => 'Bpk. Hendra',
            'subtotal' => 70000,
            'total_amount' => 70000,
            'paid_amount' => 100000,
            'change_amount' => 30000,
            'print_count' => 0,
            'reprint_count' => 0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $this->order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 35000,
            'subtotal' => 70000,
            'total_price' => 70000,
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $this->order->id,
            'payment_method' => 'cash',
            'amount' => 100000,
        ]);
    }

    public function test_first_time_opening_receipt_marks_original_print(): void
    {
        $this->assertSame(0, $this->order->print_count);
        $this->assertSame(0, $this->order->reprint_count);

        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.receipt', $this->order->id));

        $response->assertOk();

        $this->order->refresh();
        $this->assertSame(1, $this->order->print_count);
        $this->assertSame(0, $this->order->reprint_count);
        $this->assertNotNull($this->order->first_printed_at);
        $this->assertNotNull($this->order->last_printed_at);
        $this->assertSame($this->cashier->id, $this->order->last_printed_by);

        // Cetakan asli: TIDAK TERCANTUM "cetakan ke berapa"
        $response->assertDontSee('SALINAN (CETAKAN KE-');
        $response->assertDontSee('*** SALINAN');
        $response->assertSee('Cetakan Asli');
        $response->assertSee('Cetak Bill');
    }

    public function test_reprint_via_post_increments_print_and_reprint_count_and_creates_audit_log(): void
    {
        // First print
        $this->order->recordPrint($this->cashier->id);
        $this->assertSame(1, $this->order->print_count);
        $this->assertSame(0, $this->order->reprint_count);

        // Trigger reprint
        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->post(route('pos.receipt.reprint', $this->order->id), [
                'reason' => 'Pelanggan meminta salinan struk',
            ]);

        $response->assertRedirect(route('pos.receipt', $this->order->id));

        $this->order->refresh();
        $this->assertSame(2, $this->order->print_count);
        $this->assertSame(1, $this->order->reprint_count);
        $this->assertTrue($this->order->isReprint());

        // Verify forensic audit log created under business
        $audit = AuditLog::where('business_id', $this->business->id)
            ->where('auditable_id', $this->order->id)
            ->where('action', 'bill_reprinted')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('bill_reprinted', $audit->action);
        $this->assertSame('medium', $audit->risk_level);
        $this->assertStringContainsString('Salinan ke-1 (Cetakan ke-2)', $audit->notes);
        $this->assertSame(1, $audit->old_values['print_count']);
        $this->assertSame(2, $audit->new_values['print_count']);

        // Follow redirect to receipt page
        $receiptResponse = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.receipt', $this->order->id));

        $receiptResponse->assertOk();
        $receiptResponse->assertSee('*** SALINAN (CETAKAN KE-2) ***');
        $receiptResponse->assertSee('SALINAN (CETAKAN KE-2)');
        $receiptResponse->assertSee('Waktu Re-Print:');
        $receiptResponse->assertSee('Operator:');
        $receiptResponse->assertSee('Salinan (Ke-2)');
    }

    public function test_multiple_reprints_elevates_audit_risk_level_to_high(): void
    {
        $this->order->recordPrint($this->cashier->id); // 1st: original
        $this->order->recordReprint($this->cashier->id); // 2nd: medium
        $this->order->recordReprint($this->cashier->id); // 3rd: high (> 2 prints)

        $this->order->refresh();
        $this->assertSame(3, $this->order->print_count);
        $this->assertSame(2, $this->order->reprint_count);

        $latestAudit = AuditLog::where('business_id', $this->business->id)
            ->where('auditable_id', $this->order->id)
            ->where('action', 'bill_reprinted')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($latestAudit);
        $this->assertSame('high', $latestAudit->risk_level);
        $this->assertStringContainsString('Cetakan ke-3', $latestAudit->notes);
    }

    public function test_query_parameter_reprint_triggers_reprint_action(): void
    {
        $this->order->recordPrint($this->cashier->id);
        $this->assertSame(1, $this->order->print_count);

        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.receipt', ['order' => $this->order->id, 'reprint' => '1']));

        $response->assertOk();

        $this->order->refresh();
        $this->assertSame(2, $this->order->print_count);
        $this->assertSame(1, $this->order->reprint_count);
        $response->assertSee('*** SALINAN (CETAKAN KE-2) ***');
    }

    public function test_reprint_json_api_response(): void
    {
        $this->order->recordPrint($this->cashier->id);

        $response = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->postJson(route('pos.receipt.reprint', $this->order->id), [
                'reason' => 'Test API reprint',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'print_count' => 2,
            'reprint_count' => 1,
        ]);
    }

    public function test_receipt_image_service_includes_watermark_only_on_reprint(): void
    {
        $service = app(PosReceiptImageService::class);

        // 1. Original print
        $this->order->recordPrint($this->cashier->id);
        $pngOriginal = $service->generate($this->order);
        $this->assertNotEmpty($pngOriginal);

        // 2. Reprint
        $this->order->recordReprint($this->cashier->id);
        $pngReprint = $service->generate($this->order);
        $this->assertNotEmpty($pngReprint);

        // Image bytes should differ because of the watermark notice
        $this->assertNotSame($pngOriginal, $pngReprint);
    }

    public function test_multi_tenant_isolation_prevents_unauthorized_reprint(): void
    {
        $otherBusiness = Business::create(['name' => 'Warung Lain']);
        $otherUser = User::create([
            'name' => 'Kasir Lain',
            'email' => 'kasir.lain@example.com',
            'phone' => '081299990099',
            'password' => 'password123',
        ]);
        $otherBusiness->users()->attach($otherUser->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $response = $this->actingAs($otherUser)
            ->withSession(['active_business_id' => $otherBusiness->id])
            ->post(route('pos.receipt.reprint', $this->order->id));

        // BelongsToBusiness / 404
        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }
}
