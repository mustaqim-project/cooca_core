<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Expense;
use App\Models\McpAccessToken;
use App\Models\McpActivityLog;
use App\Models\Product;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class McpProtocolTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private string $plainToken;
    private McpAccessToken $mcpToken;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name'     => 'MCP Merchant Owner',
            'email'    => 'mcp_owner@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->business = Business::create([
            'name' => 'Bengkel & Toko Bagema',
            'slug' => 'bengkel-bagema',
        ]);

        $this->business->users()->attach($this->user->id, [
            'id'        => Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);

        app(\App\Domain\Billing\EntitlementService::class)->upgradeToCore($this->business, 'monthly');

        \App\Models\Location::create([
            'business_id' => $this->business->id,
            'name'        => 'Toko Pusat',
            'code'        => 'LOC-01',
            'is_active'   => true,
            'is_primary'  => true,
        ]);

        app(\App\Domain\Finance\CashLedgerService::class)->recordInflow(
            $this->business,
            1000000,
            'initial_capital',
            'init-1',
            'Modal Awal Kas Toko',
            'cash',
            $this->user->id
        );

        $generated = McpAccessToken::generateToken(
            business: $this->business,
            user: $this->user,
            name: 'Claude Desktop Test',
            abilities: ['*'],
            providerHint: 'claude'
        );

        $this->plainToken = $generated['token'];
        $this->mcpToken = $generated['model'];
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/mcp/message', [
            'jsonrpc' => '2.0',
            'method'  => 'ping',
            'id'      => 1,
        ]);

        $response->assertStatus(401);
        $this->assertSame(-32000, $response->json('error.code'));
    }

    public function test_initialize_handshake_returns_protocol_capabilities(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'initialize',
                'params'  => [
                    'protocolVersion' => '2024-11-05',
                ],
                'id'      => 1,
            ]);

        $response->assertStatus(200);
        $this->assertSame('2.0', $response->json('jsonrpc'));
        $this->assertSame('cooca-erp-mcp', $response->json('result.serverInfo.name'));
        $this->assertSame('2024-11-05', $response->json('result.protocolVersion'));
        $this->assertArrayHasKey('tools', $response->json('result.capabilities'));
    }

    public function test_tools_list_returns_all_registered_tools(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/list',
                'id'      => 2,
            ]);

        $response->assertStatus(200);
        $tools = $response->json('result.tools');
        $this->assertIsArray($tools);
        $this->assertCount(19, $tools);

        $toolNames = array_column($tools, 'name');
        $this->assertContains('finance_record_expense', $toolNames);
        $this->assertContains('inventory_create_product', $toolNames);
        $this->assertContains('report_get_profit_loss', $toolNames);
        $this->assertContains('whatsapp_send_notification', $toolNames);
        $this->assertContains('modifier_manage', $toolNames);
        $this->assertContains('inventory_manage_material', $toolNames);
        $this->assertContains('inventory_manage_bom', $toolNames);
        $this->assertContains('inventory_stock_opname', $toolNames);
        $this->assertContains('purchasing_manage_supplier', $toolNames);
        $this->assertContains('purchasing_manage_order', $toolNames);
        $this->assertContains('sales_manage_quotation', $toolNames);
        $this->assertContains('sales_manage_order', $toolNames);
        $this->assertContains('finance_manage_invoice', $toolNames);
    }

    public function test_finance_record_expense_creates_expense_and_activity_log(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'finance_record_expense',
                    'arguments' => [
                        'expense_date'   => '2026-10-07',
                        'category'       => 'Operasional',
                        'amount'         => 75000,
                        'payment_method' => 'cash',
                        'description'    => 'Beli Kertas Struk Kasir',
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $this->assertFalse((bool) $response->json('result.isError', false));
        $content = $response->json('result.content.0.text');
        $this->assertStringContainsString('75000', $content);

        // Verifikasi mutasi database
        $this->assertDatabaseHas('expenses', [
            'business_id' => $this->business->id,
            'category'    => 'Operasional',
            'amount'      => 75000,
        ]);

        // Verifikasi audit log aktivitas MCP
        $this->assertDatabaseHas('mcp_activity_logs', [
            'business_id'     => $this->business->id,
            'tool_name'       => 'finance_record_expense',
            'response_status' => 'success',
        ]);
    }

    public function test_inventory_create_product_and_check_stock(): void
    {
        $createResponse = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'inventory_create_product',
                    'arguments' => [
                        'name'          => 'Kopi Susu Gula Aren',
                        'cost_price'    => 8000,
                        'selling_price' => 18000,
                        'initial_stock' => 50,
                        'category_name' => 'Minuman',
                    ],
                ],
                'id'      => 4,
            ]);

        $createResponse->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'business_id'   => $this->business->id,
            'name'          => 'Kopi Susu Gula Aren',
            'base_cost'     => 8000,
            'selling_price' => 18000,
        ]);

        // Cek stok via tool inventory_check_stock
        $checkResponse = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'inventory_check_stock',
                    'arguments' => [
                        'search_query' => 'Kopi Susu',
                    ],
                ],
                'id'      => 5,
            ]);

        $checkResponse->assertStatus(200);
        $this->assertStringContainsString('Kopi Susu Gula Aren', $checkResponse->json('result.content.0.text'));
    }

    public function test_openapi_schema_endpoint_returns_valid_specification(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->getJson('/api/v1/mcp/openapi.json');

        $response->assertStatus(200);
        $this->assertSame('3.1.0', $response->json('openapi'));
        $this->assertArrayHasKey('/tools/finance_record_expense/execute', $response->json('paths'));
        $this->assertArrayHasKey('/tools/inventory_create_product/execute', $response->json('paths'));
    }

    public function test_direct_rest_tool_execution_bridge(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/tools/finance_get_cash_and_bank_balances/execute', []);

        $response->assertStatus(200);
        $this->assertSame('success', $response->json('status'));
        $this->assertArrayHasKey('total_liquidity', $response->json('data'));
    }

    public function test_multi_tenant_isolation_prevents_cross_tenant_access_and_mutation(): void
    {
        $otherBusiness = Business::create([
            'name' => 'Kedai Kopi Sebelah',
            'slug' => 'kedai-sebelah',
        ]);

        $otherUnit = \App\Models\Unit::create([
            'business_id' => $otherBusiness->id,
            'name'        => 'Pcs',
            'code'        => 'pcs',
            'category'    => 'quantity',
        ]);

        Product::create([
            'business_id'    => $otherBusiness->id,
            'name'           => 'Resep Rahasia Kedai Sebelah',
            'sku'            => 'SECRET-01',
            'base_cost'      => 5000,
            'selling_price'  => 25000,
            'output_unit_id' => $otherUnit->id,
            'is_active'      => true,
        ]);

        // Cari via token bisnis pertama (bengkel-bagema)
        $searchResponse = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'inventory_check_stock',
                    'arguments' => [
                        'search_query' => 'Resep Rahasia Kedai Sebelah',
                    ],
                ],
                'id'      => 10,
            ]);

        $searchResponse->assertStatus(200);
        $content = json_decode($searchResponse->json('result.content.0.text'), true);
        $this->assertSame(0, $content['total_found']);
        $this->assertEmpty($content['items']);

        // Pastikan pencatatan biaya oleh token ini selalu terikat pada bisnis token
        $expenseResponse = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'finance_record_expense',
                    'arguments' => [
                        'expense_date'   => '2026-10-07',
                        'category'       => 'Peralatan',
                        'amount'         => 30000,
                        'payment_method' => 'cash',
                        'description'    => 'Beli Kunci Pas',
                    ],
                ],
                'id'      => 11,
            ]);

        $expenseResponse->assertStatus(200);
        $this->assertDatabaseMissing('expenses', [
            'business_id' => $otherBusiness->id,
            'amount'      => 30000,
        ]);
        $this->assertDatabaseHas('expenses', [
            'business_id' => $this->business->id,
            'amount'      => 30000,
        ]);
    }

    public function test_token_scope_ability_enforces_least_privilege(): void
    {
        $scoped = McpAccessToken::generateToken(
            business: $this->business,
            user: $this->user,
            name: 'Stock Checker Only',
            abilities: ['mcp:products:read'],
            providerHint: 'ollama'
        );

        $scopedToken = $scoped['token'];

        // tools/list hanya menampilkan tools yang diizinkan untuk ability token
        $listResponse = $this->withHeader('Authorization', "Bearer {$scopedToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/list',
                'id'      => 20,
            ]);

        $listResponse->assertStatus(200);
        $tools = $listResponse->json('result.tools');
        $this->assertCount(1, $tools);
        $this->assertSame('inventory_check_stock', $tools[0]['name']);

        // Mencoba memanggil tools yang membutuhkan mcp:expenses:write harus ditolak dengan error code -32001
        $callResponse = $this->withHeader('Authorization', "Bearer {$scopedToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'finance_record_expense',
                    'arguments' => [
                        'expense_date'   => '2026-10-07',
                        'category'       => 'Operasional',
                        'amount'         => 50000,
                        'payment_method' => 'cash',
                    ],
                ],
                'id'      => 21,
            ]);

        $callResponse->assertStatus(400);
        $this->assertSame(-32001, $callResponse->json('error.code'));
        $this->assertStringContainsString('lacks ability', $callResponse->json('error.message'));
    }

    public function test_social_schedule_post_and_report_tools(): void
    {
        // 1. Tool social_schedule_post
        $socialResponse = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'social_schedule_post',
                    'arguments' => [
                        'caption'      => 'Promo Spesial Kopi Aren Beli 1 Gratis 1! #cooca #promo',
                        'channels'     => ['instagram', 'facebook'],
                        'scheduled_at' => '2026-10-15T10:00:00+07:00',
                    ],
                ],
                'id'      => 30,
            ]);

        $socialResponse->assertStatus(200);
        $this->assertFalse((bool) $socialResponse->json('result.isError', false));
        $this->assertDatabaseHas('social_media_posts', [
            'business_id' => $this->business->id,
            'status'      => 'scheduled',
        ]);

        // 2. Tool report_get_profit_loss
        $reportResponse = $this->withHeader('Authorization', "Bearer {$this->plainToken}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'report_get_profit_loss',
                    'arguments' => [
                        'start_date' => '2026-10-01',
                        'end_date'   => '2026-10-31',
                    ],
                ],
                'id'      => 31,
            ]);

        $reportResponse->assertStatus(200);
        $this->assertFalse((bool) $reportResponse->json('result.isError', false));
        $content = json_decode($reportResponse->json('result.content.0.text'), true);
        $this->assertSame('success', $content['status']);
        $this->assertArrayHasKey('financial_summary', $content);
        $this->assertArrayHasKey('net_profit', $content['financial_summary']);
    }

    public function test_owner_panel_mcp_settings_view_is_accessible(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/settings/integrations/mcp');

        $response->assertStatus(200);
        $response->assertSee(__('mcp.page_title'));
        $response->assertSee('Claude Desktop');
        $response->assertSee('Claude Code CLI');
        $response->assertSee('Cursor');
        $response->assertSee('ChatGPT');
        $response->assertSee('Google Gemini');
        $response->assertSee('Local / Ollama');
        $response->assertSee('Dify');
        $response->assertSee('LangChain');
        $response->assertSee(__('mcp.tools_catalog_title'));
        $response->assertSee(__('mcp.troubleshooting_title'));
    }

    public function test_free_plan_mcp_api_request_is_rejected_with_forbidden(): void
    {
        $freeBusiness = Business::create([
            'name' => 'Free Merchant Toko',
            'slug' => 'free-merchant-toko',
        ]);
        $freeUser = User::create([
            'name'     => 'Free Owner',
            'email'    => 'free_owner@example.com',
            'password' => bcrypt('password123'),
        ]);
        $freeBusiness->users()->attach($freeUser->id, [
            'id'        => Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);
        $freeUser->update(['active_business_id' => $freeBusiness->id]);

        $freeTokenData = McpAccessToken::generateToken(
            business: $freeBusiness,
            user: $freeUser,
            name: 'Free Claude Token',
            abilities: ['*'],
            providerHint: 'claude'
        );

        $response = $this->withHeader('Authorization', "Bearer {$freeTokenData['token']}")
            ->postJson('/api/v1/mcp/message', [
                'jsonrpc' => '2.0',
                'method'  => 'ping',
                'id'      => 99,
            ]);

        $response->assertStatus(403);
        $this->assertSame(-32001, $response->json('error.code'));
        $this->assertStringContainsString('langganan paket aktif', $response->json('error.message'));
    }

    public function test_free_plan_cannot_access_mcp_settings_view_and_is_redirected_to_limits(): void
    {
        $freeBusiness = Business::create([
            'name' => 'Free Merchant Web',
            'slug' => 'free-merchant-web',
        ]);
        $freeUser = User::create([
            'name'     => 'Free Web Owner',
            'email'    => 'free_web_owner@example.com',
            'password' => bcrypt('password123'),
        ]);
        $freeBusiness->users()->attach($freeUser->id, [
            'id'        => Str::uuid(),
            'role'      => 'owner',
            'is_active' => true,
        ]);
        $freeUser->update(['active_business_id' => $freeBusiness->id]);

        $response = $this->actingAs($freeUser)
            ->get('/settings/integrations/mcp');

        $response->assertRedirect(route('billing.limits'));
    }
}
