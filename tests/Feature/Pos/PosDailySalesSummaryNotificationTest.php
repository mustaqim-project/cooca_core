<?php

declare(strict_types=1);

namespace Tests\Feature\Pos;

use App\Domain\Report\Pos\DTOs\PosKpiSummaryDTO;
use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReportingService;
use App\Models\Business;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\PosDailySalesSummaryNotification;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PosDailySalesSummaryNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->business = Business::create([
            'name' => 'Kopi Bento Nusantara',
            'slug' => 'kopi-bento-nusantara',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@kopibento.com',
            'phone' => '081298765432',
            'password' => bcrypt('secret123'),
        ]);
        $this->owner->forceFill(['email_verified_at' => now(), 'active_business_id' => $this->business->id])->save();

        $this->business->users()->attach($this->owner->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        Context::setBusiness($this->business);

        $this->location = Location::create([
            'business_id' => $this->business->id,
            'name' => 'Outlet Senopati',
            'code' => 'SNP-01',
            'is_active' => true,
        ]);
    }

    private function createDummyKpi(
        float $grossSales,
        float $netSales,
        float $totalHpp,
        float $grossProfit,
        float $grossMarginPercent,
        int $totalOrders
    ): PosKpiSummaryDTO {
        return new PosKpiSummaryDTO(
            grossSales: $grossSales,
            totalDiscount: 0.0,
            orderDiscount: 0.0,
            voucherDiscount: 0.0,
            pointsDiscount: 0.0,
            itemDiscount: 0.0,
            grossRevenue: $grossSales,
            refundAmount: 0.0,
            netSales: $netSales,
            subtotal: $netSales,
            taxAmount: 0.0,
            serviceChargeAmount: 0.0,
            roundingAmount: 0.0,
            grandTotal: $netSales,
            totalHpp: $totalHpp,
            grossProfit: $grossProfit,
            grossMarginPercent: $grossMarginPercent,
            totalOrders: $totalOrders,
            totalItemsSold: (float) ($totalOrders * 2),
            averageOrderValue: $totalOrders > 0 ? $netSales / $totalOrders : 0.0,
            averageItemsPerTransaction: 2.0,
            averageSellingPrice: 25000.0,
            averageCostPrice: 10000.0,
            goodsRevenue: $netSales,
            goodsQuantity: (float) ($totalOrders * 2),
            servicesRevenue: 0.0,
            servicesQuantity: 0.0,
            cashSales: $netSales,
            nonCashSales: 0.0,
            totalPayments: $netSales,
            todayRevenue: $netSales,
            todayOrders: $totalOrders
        );
    }

    public function test_notification_generates_correct_database_payload(): void
    {
        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today(),
            endDate: Carbon::today()
        );

        $kpi = $this->createDummyKpi(
            grossSales: 1500000.0,
            netSales: 1450000.0,
            totalHpp: 600000.0,
            grossProfit: 850000.0,
            grossMarginPercent: 58.62,
            totalOrders: 35
        );

        $notification = new PosDailySalesSummaryNotification(
            business: $this->business,
            filter: $filter,
            kpi: $kpi,
            channels: collect([
                (object) [
                    'channel' => 'pos_direct',
                    'channel_label' => 'POS Walk-in',
                    'order_count' => 20,
                    'gross_sales' => 900000.0,
                    'net_merchant_payout' => 900000.0,
                ],
                (object) [
                    'channel' => 'shopeefood',
                    'channel_label' => 'ShopeeFood',
                    'order_count' => 15,
                    'gross_sales' => 600000.0,
                    'net_merchant_payout' => 480000.0,
                ],
            ]),
            topProducts: collect([
                (object) [
                    'product_name' => 'Kopi Susu Aren',
                    'total_qty' => 40,
                    'total_sales' => 800000.0,
                ],
            ])
        );

        $payload = $notification->toDatabase($this->owner);

        $this->assertEquals('pos_daily_sales_summary', $payload['type']);
        $this->assertEquals($this->business->id, $payload['business_id']);
        $this->assertEquals(1450000.0, $payload['net_sales']);
        $this->assertEquals(850000.0, $payload['gross_profit']);
        $this->assertEquals(35, $payload['total_orders']);
        $this->assertEquals(58.62, $payload['margin_percent']);
        $this->assertEquals(2, $payload['channels_count']);
        $this->assertStringContainsString('pos/reports', $payload['action_url']);
    }

    public function test_notification_renders_responsive_html_email(): void
    {
        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::today(),
            endDate: Carbon::today()
        );

        $kpi = $this->createDummyKpi(
            grossSales: 2000000.0,
            netSales: 2000000.0,
            totalHpp: 800000.0,
            grossProfit: 1200000.0,
            grossMarginPercent: 60.0,
            totalOrders: 40
        );

        $notification = new PosDailySalesSummaryNotification(
            business: $this->business,
            filter: $filter,
            kpi: $kpi,
            channels: collect([
                (object) [
                    'channel' => 'gofood',
                    'channel_label' => 'GoFood',
                    'order_count' => 10,
                    'gross_sales' => 500000.0,
                    'net_merchant_payout' => 400000.0,
                ],
            ]),
            topProducts: collect([
                (object) [
                    'product_name' => 'Croissant Butter',
                    'total_qty' => 25,
                    'total_sales' => 625000.0,
                ],
            ])
        );

        $mailMessage = $notification->toMail($this->owner);

        $this->assertStringContainsString('Kopi Bento Nusantara', $mailMessage->subject);
        $this->assertEquals('emails.pos_daily_sales_summary', $mailMessage->view);
        $this->assertArrayHasKey('kpi', $mailMessage->viewData);
        $this->assertArrayHasKey('channels', $mailMessage->viewData);
    }

    public function test_notification_generates_formatted_whatsapp_message(): void
    {
        $filter = new PosReportFilterDTO(
            businessId: $this->business->id,
            startDate: Carbon::parse('2026-10-04'),
            endDate: Carbon::parse('2026-10-04')
        );

        $kpi = $this->createDummyKpi(
            grossSales: 3500000.0,
            netSales: 3500000.0,
            totalHpp: 1400000.0,
            grossProfit: 2100000.0,
            grossMarginPercent: 60.0,
            totalOrders: 50
        );

        $notification = new PosDailySalesSummaryNotification(
            business: $this->business,
            filter: $filter,
            kpi: $kpi,
            channels: collect([
                (object) [
                    'channel' => 'grabfood',
                    'channel_label' => 'GrabFood',
                    'order_count' => 20,
                    'gross_sales' => 1500000.0,
                    'net_merchant_payout' => 1200000.0,
                ],
            ]),
            topProducts: collect([
                (object) [
                    'product_name' => 'Signature Latte',
                    'total_qty' => 30,
                    'total_sales' => 1050000.0,
                ],
            ])
        );

        $waText = $notification->toWhatsAppMessage('Pak Budi');

        $this->assertStringContainsString('*RINGKASAN PENJUALAN POS & SALURAN DIGITAL*', $waText);
        $this->assertStringContainsString('Kopi Bento Nusantara', $waText);
        $this->assertStringContainsString('Pak Budi', $waText);
        $this->assertStringContainsString('Rp 3.500.000', $waText);
        $this->assertStringContainsString('Rp 2.100.000', $waText);
        $this->assertStringContainsString('60.0%', $waText);
        $this->assertStringContainsString('*GrabFood*', $waText);
        $this->assertStringContainsString('Signature Latte', $waText);
    }

    public function test_console_command_dispatches_tri_channel_notifications_successfully(): void
    {
        Notification::fake();

        $register = PosRegister::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'name' => 'Kasir 1',
            'code' => 'REG-01',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'business_id' => $this->business->id,
            'name' => 'Cup',
            'code' => 'cup',
            'symbol' => 'c',
            'category' => Unit::CATEGORY_QUANTITY,
            'is_active' => true,
        ]);

        $category = ProductCategory::create([
            'business_id' => $this->business->id,
            'name' => 'Coffee',
            'slug' => 'coffee',
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'output_unit_id' => $unit->id,
            'name' => 'Americano',
            'code' => 'AME-01',
            'is_active' => true,
        ]);

        $order = PosOrder::create([
            'business_id' => $this->business->id,
            'location_id' => $this->location->id,
            'user_id' => $this->owner->id,
            'order_number' => 'ORD-CMD-001',
            'order_date' => Carbon::today()->toDateString(),
            'status' => PosOrder::STATUS_COMPLETED,
            'sales_channel' => 'pos_direct',
            'subtotal' => 50000.0,
            'total_amount' => 50000.0,
            'paid_amount' => 50000.0,
        ]);

        PosOrderItem::create([
            'pos_order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Americano',
            'quantity' => 2,
            'unit_price' => 25000.0,
            'cost_price' => 10000.0,
            'subtotal' => 50000.0,
            'total' => 50000.0,
        ]);

        $this->artisan('pos:send-daily-summary', [
            '--business' => $this->business->id,
            '--channel' => 'all',
        ])
        ->assertSuccessful()
        ->expectsOutputToContain('Pengiriman Ringkasan Selesai:');

        Notification::assertSentTo($this->owner, PosDailySalesSummaryNotification::class);
    }
}
