<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Pos\PosReceiptImageService;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Models\WhatsAppSession;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PosReceiptImageTest extends TestCase
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
            'name' => 'Kasir Struk',
            'email' => 'kasir.struk@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Kopi Mantap Pos',
            'address' => 'Jl. Merdeka No. 45, Jakarta',
            'phone' => '081234567890',
            'pos_receipt_footer_note' => 'Terima kasih atas kunjungannya!',
        ]);

        $this->business->users()->attach($this->cashier->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
        ]);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Utama',
            'code' => 'OU-01',
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Minuman',
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Cup',
            'code' => 'CUP',
            'symbol' => 'cup',
            'category' => 'quantity',
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Es Kopi Gula Aren',
            'code' => 'KOP-001',
            'type' => 'finished_good',
            'selling_price' => 18000,
            'purchase_price' => 7000,
            'is_active' => true,
        ]);

        $this->order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->cashier->id,
            'order_number' => 'ORD-TEST-001',
            'order_date' => now(),
            'status' => PosOrder::STATUS_COMPLETED,
            'order_type' => 'dine_in',
            'table_or_reference' => 'Meja 5',
            'customer_name_guest' => 'Budi Santoso',
            'customer_phone_guest' => '081987654321',
            'subtotal' => 36000,
            'discount_amount' => 0,
            'total_amount' => 36000,
            'paid_amount' => 50000,
            'change_amount' => 14000,
            'total_hpp_cost' => 14000,
            'total_gross_profit' => 22000,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $this->order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->sku,
            'unit_price' => 18000,
            'unit_cost_hpp' => 7000,
            'quantity' => 2,
            'subtotal' => 36000,
            'discount_amount' => 0,
            'total_price' => 36000,
            'total_hpp' => 14000,
            'notes' => 'Less sugar, normal ice',
        ]);

        PosOrderPayment::create([
            'pos_order_id' => $this->order->id,
            'business_id' => $this->business->id,
            'payment_method' => 'cash',
            'amount' => 50000,
            'reference_number' => 'CASH-001',
        ]);
    }

    public function test_receipt_image_service_generates_valid_png_bytes(): void
    {
        $service = new PosReceiptImageService();
        $png = $service->generate($this->order);

        $this->assertNotEmpty($png);
        // PNG magic number check: \x89PNG\r\n\x1a\n
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);

        // Store check
        $storedPath = $service->generateAndStore($this->order);
        $this->assertFileExists(storage_path('app/public/' . $storedPath));
    }

    public function test_public_and_pos_receipt_image_routes_return_image_stream(): void
    {
        // Public route
        $publicRes = $this->get(route('public.receipt.image', $this->order->id));
        $publicRes->assertOk();
        $publicRes->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $publicRes->getContent());

        // Authenticated POS route
        Context::setBusiness($this->business);
        $posRes = $this->actingAs($this->cashier)
            ->withSession(['active_business_id' => $this->business->id])
            ->get(route('pos.receipt.image', $this->order->id));
        $posRes->assertOk();
        $posRes->assertHeader('Content-Type', 'image/png');
    }

    public function test_whatsapp_gateway_attaches_receipt_image_to_customer(): void
    {
        WhatsAppSession::create([
            'business_id' => $this->business->id,
            'session_id' => 'biz_' . str_replace('-', '', substr($this->business->id, 0, 8)),
            'status' => 'connected',
            'phone_number' => '62811111111',
            'auto_send_receipt' => true,
        ]);

        Http::fake([
            '*/send-message' => Http::response([
                'success' => true,
                'message' => 'Message sent',
            ], 200),
        ]);

        $gateway = app(WhatsAppGatewayService::class);
        $sent = $gateway->sendReceipt($this->order);

        $this->assertTrue($sent);

        Http::assertSent(function ($request) {
            $data = $request->data();
            return ($data['target'] ?? '') === '081987654321'
                && ($data['type'] ?? '') === 'image'
                && !empty($data['mediaUrl'])
                && str_contains($data['mediaUrl'], 'receipt_')
                && str_contains($data['message'] ?? '', 'STRUK PEMBELIAN');
        });
    }
}
