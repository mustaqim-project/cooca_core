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

        $response->assertStatus(200);
        $response->assertSee('Run Your Business.');
        $response->assertSee('One Operating');
        $response->assertSee('One Business. One System. One Central Center.');
        $response->assertSee('Semua yang Anda Butuhkan. Terhubung dalam Satu Sistem.');
        $response->assertSee('Your Business, Connected End-to-End');
        $response->assertSee('Lebih dari Sekadar ERP');
        $response->assertSee('Jangkau Pelanggan di Semua Channel');
        $response->assertSee('Kelola Konten, Maksimalkan Dampak');
        $response->assertSee('Temukan & Jual Lebih Mudah');
        $response->assertSee('Cocok untuk Berbagai Jenis Bisnis');
        $response->assertSee('Saatnya Beralih ke COOCA');
        $response->assertSee('One System, Endless Possibilities');

        // Verify NO calculator in navigation or primary landing sections
        $response->assertDontSee('Kalkulator HPP');
        $response->assertDontSee('Kalkulator BEP');
        $response->assertDontSee('Daftar Kalkulator');
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
