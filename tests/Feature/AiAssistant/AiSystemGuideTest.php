<?php

declare(strict_types=1);

namespace Tests\Feature\AiAssistant;

use App\Domain\Ai\AiSystemGuideService;
use App\Domain\Ai\MarkdownKnowledgeService;
use App\Domain\Ai\TenantSopIngestionService;
use App\Models\Business;
use App\Models\TenantSopChunk;
use App\Models\TenantSopDocument;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AiSystemGuideTest extends TestCase
{
    use RefreshDatabase;

    private Business $businessA;
    private Business $businessB;
    private User $ownerA;
    private User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        $this->seed(\Database\Seeders\RbacSeeder::class);

        Storage::fake('local');

        $this->ownerA = User::factory()->create([
            'name' => 'Owner Bengkel Motor Jaya',
            'email' => 'bengkel@cooca.test',
        ]);

        $this->businessA = Business::create([
            'user_id' => $this->ownerA->id,
            'name' => 'Bengkel Motor Jaya',
            'slug' => 'bengkel-motor-jaya',
            'template_code' => 'workshop',
            'industry_category' => 'workshop',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        \App\Models\BusinessMembership::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->ownerB = User::factory()->create([
            'name' => 'Owner Kafe Nusantara',
            'email' => 'kafe@cooca.test',
        ]);

        $this->businessB = Business::create([
            'user_id' => $this->ownerB->id,
            'name' => 'Kafe Nusantara',
            'slug' => 'kafe-nusantara',
            'template_code' => 'fnb',
            'industry_category' => 'fnb',
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'is_active' => true,
        ]);

        \App\Models\BusinessMembership::create([
            'business_id' => $this->businessB->id,
            'user_id' => $this->ownerB->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_markdown_knowledge_service_indexes_system_guides(): void
    {
        $service = new MarkdownKnowledgeService();
        $knowledge = $service->getSystemKnowledge();

        $this->assertNotEmpty($knowledge, 'System knowledge index should contain guide chunks.');
        
        $actionLinks = $service->resolveActionLinks('Saya mau setting printer kasir dan whatsapp');
        $this->assertNotEmpty($actionLinks, 'Action links should resolve matching keywords.');
        
        $urls = array_column($actionLinks, 'url');
        $this->assertContains('/settings/pos/printers', $urls);
        $this->assertContains('/settings/whatsapp', $urls);
    }

    public function test_strict_multi_tenant_sop_isolation(): void
    {
        // 1. Create SOP Chunk for Business A (Bengkel)
        $docA = TenantSopDocument::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'title' => 'SOP Servis Motor & Garansi 7 Hari',
            'file_name' => 'sop_bengkel.pdf',
            'file_path' => 'tenants/' . $this->businessA->id . '/sops/sop.pdf',
            'total_pages' => 3,
            'total_chunks' => 1,
            'status' => TenantSopDocument::STATUS_READY,
        ]);

        TenantSopChunk::create([
            'business_id' => $this->businessA->id,
            'document_id' => $docA->id,
            'page_number' => 1,
            'section_title' => 'Ketentuan Garansi Servis',
            'content_text' => 'Setiap pelanggan servis motor berhak mendapatkan garansi servis 7 hari kerja gratis jika keluhan sama terulang.',
            'keywords' => 'garansi servis motor pelanggan gratis keluhan bengkel',
        ]);

        // 2. Create SOP Chunk for Business B (Kafe)
        $docB = TenantSopDocument::create([
            'business_id' => $this->businessB->id,
            'user_id' => $this->ownerB->id,
            'title' => 'SOP Resep Rahasia Kopi Nusantara',
            'file_name' => 'sop_kafe.pdf',
            'file_path' => 'tenants/' . $this->businessB->id . '/sops/sop.pdf',
            'total_pages' => 2,
            'total_chunks' => 1,
            'status' => TenantSopDocument::STATUS_READY,
        ]);

        TenantSopChunk::create([
            'business_id' => $this->businessB->id,
            'document_id' => $docB->id,
            'page_number' => 2,
            'section_title' => 'Resep Rahasia Espresso Creamy',
            'content_text' => 'Takaran espresso rahasia adalah 18 gram kopi arabika gayo dengan 40ml susu evaporasi dan sirup gula aren murni.',
            'keywords' => 'resep rahasia espresso kopi arabika gayo susu gula aren kafe',
        ]);

        $guideService = new AiSystemGuideService();

        // 3. Query as Business A asking about "garansi"
        $resultA = $guideService->ask(
            $this->businessA,
            $this->ownerA,
            'Bagaimana aturan garansi servis di tempat kita?',
            'sop',
        );

        $this->assertStringContainsString('Garansi Servis', $resultA['content']);
        $this->assertStringNotContainsString('Espresso', $resultA['content'], 'Business A must NEVER see Business B secret recipe!');

        // 4. Query as Business A asking about "resep espresso"
        $resultAForBSecret = $guideService->ask(
            $this->businessA,
            $this->ownerA,
            'Apa takaran resep rahasia espresso?',
            'sop',
        );

        $this->assertStringNotContainsString('18 gram kopi arabika gayo', $resultAForBSecret['content'], 'Cross-tenant secret recipe is 100% blocked from Business A!');

        // 5. Query as Business B asking about "resep espresso"
        $resultB = $guideService->ask(
            $this->businessB,
            $this->ownerB,
            'Apa takaran resep rahasia espresso kita?',
            'sop',
        );

        $this->assertStringContainsString('18 gram kopi arabika gayo', $resultB['content'], 'Business B can access its own SOP chunks.');
    }

    public function test_ai_assistant_ask_http_endpoint(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);
        session(['business_id' => $this->businessA->id]);

        $response = $this->postJson(route('assistant.ask'), [
            'query' => 'Bagaimana cara setting printer kasir pos?',
            'scope' => 'system',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'conversation_id',
                'message_id',
                'role',
                'content',
                'sources',
                'action_buttons',
                'scope',
            ],
        ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_ai_assistant_prompts_http_endpoint(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);
        session(['business_id' => $this->businessA->id]);

        $response = $this->getJson(route('assistant.prompts'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);

        $prompts = $response->json('data');
        $this->assertNotEmpty($prompts);
    }

    public function test_tenant_sop_upload_and_ingestion(): void
    {
        $this->actingAs($this->ownerA);
        Context::setBusiness($this->businessA);
        session(['business_id' => $this->businessA->id]);

        // Create a fake PDF file
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /Contents 4 0 R >>\nendobj\n4 0 obj\n<< /Length 120 >>\nstream\nBT\n/F1 12 Tf\n(SOP Penerimaan Servis Motor Bengkel: Seluruh mekanik wajib cek rem dan oli.) Tj\nET\nendstream\nendobj\nxref\n0 5\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        
        $file = UploadedFile::fake()->createWithContent('sop_bengkel.pdf', $pdfContent);

        $response = $this->post(route('settings.sop.store'), [
            'title' => 'SOP Penerimaan Servis Bengkel',
            'sop_file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenant_sop_documents', [
            'business_id' => $this->businessA->id,
            'title' => 'SOP Penerimaan Servis Bengkel',
            'status' => 'ready',
        ]);

        $this->assertDatabaseHas('tenant_sop_chunks', [
            'business_id' => $this->businessA->id,
        ]);
    }
}
