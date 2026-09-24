<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicPagesStructureTest extends TestCase
{
    use RefreshDatabase;
    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('landing'));

        $response->assertSee('Satu Sistem Operasi untuk Seluruh');
        $response->assertSee('Denyut Bisnis Anda');
        $response->assertSee('Business Operating System &amp; Omnichannel ERP', false);
        $response->assertSee('Mulai Coba Gratis');
        $response->assertSee('100% Gratis Selamanya');
        $response->assertSee('cooca.id/app/dashboard');
        $response->assertSee('Realtime Cloud Sync');
        $response->assertSee('Pengurangan Otomatis Bahan Baku');

        // Verify Anti-Slop & Zero-Emoji: No music note emoji, no dots acting as logos
        $response->assertDontSee('♪');
        $response->assertDontSee('w-5 h-5 rounded-full bg-pink-500 inline-block');

        // Verify authentic vector SVG signatures exist
        $response->assertSee('badge-dollar-sign', false); // Finance badge icon
    }

    public function test_business_operating_system_subpages(): void
    {
        $this->get(route('public.bos.overview'))->assertStatus(200);
        $this->get(route('public.bos.how-it-works'))->assertStatus(200);
        $this->get(route('public.bos.why-cooca'))->assertStatus(200);
    }

    public function test_omnichannel_erp_subpages(): void
    {
        $this->get(route('public.erp.erp'))->assertStatus(200);
        $this->get(route('public.erp.pos'))->assertStatus(200);
        $this->get(route('public.erp.finance'))->assertStatus(200);
        $this->get(route('public.erp.inventory'))->assertStatus(200);
        $this->get(route('public.erp.crm'))->assertStatus(200);
        $this->get(route('public.erp.hrm'))->assertStatus(200);
        $this->get(route('public.erp.accounting'))->assertStatus(200);
        $this->get(route('public.erp.analytics'))->assertStatus(200);
    }

    public function test_omnichannel_subpages(): void
    {
        $this->get(route('public.omnichannel.social-media'))->assertStatus(200);
        $this->get(route('public.omnichannel.whatsapp'))->assertStatus(200);
        $this->get(route('public.omnichannel.marketplace'))->assertStatus(200);
        $this->get(route('public.omnichannel.orders'))->assertStatus(200);
        $this->get(route('public.omnichannel.customer'))->assertStatus(200);
    }

    public function test_content_automation_subpages(): void
    {
        $this->get(route('public.content.creation'))->assertStatus(200);
        $this->get(route('public.content.calendar'))->assertStatus(200);
        $this->get(route('public.content.publishing'))->assertStatus(200);
        $this->get(route('public.content.analytics'))->assertStatus(200);
    }

    public function test_solutions_subpages(): void
    {
        $this->get(route('public.solutions.fnb'))->assertStatus(200);
        $this->get(route('public.solutions.retail'))->assertStatus(200);
        $this->get(route('public.solutions.workshop'))->assertStatus(200);
        $this->get(route('public.solutions.laundry'))->assertStatus(200);
        $this->get(route('public.solutions.manufacturing'))->assertStatus(200);
        $this->get(route('public.solutions.services'))->assertStatus(200);
    }

    public function test_resources_and_core_pages(): void
    {
        $this->get(route('public.resources.guides'))->assertStatus(200);
        $this->get(route('public.resources.case-studies'))->assertStatus(200);
        $this->get(route('public.resources.faq'))->assertStatus(200);

        $this->get(route('public.pricing'))->assertStatus(200);
        $this->get(route('public.demo'))->assertStatus(200);
        $this->get(route('public.about'))->assertStatus(200);
        $this->get(route('public.support'))->assertStatus(200);
    }
}
