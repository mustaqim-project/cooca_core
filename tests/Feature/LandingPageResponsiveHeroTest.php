<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LandingPageResponsiveHeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
    }

    public function test_hero_section_contains_responsive_viewport_and_safe_padding(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        // Modern 100svh viewport targeting
        $response->assertSee('min-h-[calc(100svh-4rem)]', false);
        $response->assertSee('lg:min-h-[calc(100svh-84px)]', false);
        // Bottom clearance for mobile fixed bottom navigation dock
        $response->assertSee('pb-[calc(5rem+env(safe-area-inset-bottom,0px))]', false);
    }

    public function test_hero_content_has_responsive_typography_and_side_by_side_ctas(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        // Eyebrow overline kicker
        $response->assertSee('Business Operating System &amp; Omnichannel ERP', false);
        // Headline
        $response->assertSee('Satu Sistem Operasi untuk Seluruh', false);
        $response->assertSee('Denyut Bisnis Anda.', false);
        // Responsive headline scale
        $response->assertSee('text-[1.35rem] xs:text-2xl sm:text-4xl', false);
        // Compact description with line clamping on mobile
        $response->assertSee('line-clamp-2 sm:line-clamp-none', false);
        // Left-aligned layout on mobile & desktop
        $response->assertSee('text-left flex flex-col items-start', false);
        // Compact side-by-side action buttons (left-aligned)
        $response->assertSee('flex flex-row items-center justify-start gap-2 sm:gap-3.5', false);
        $response->assertSee('Mulai Coba Gratis');
        $response->assertSee('Coba Live Demo');
        // Reassurance checkpoints
        $response->assertSee('100% Gratis');
        $response->assertSee('Tanpa Kartu Kredit');
        $response->assertSee('Siap 2 Menit');
        // 3-Metric Bento Tiles
        $response->assertSee('10.000+');
        $response->assertSee('99.8%');
        $response->assertSee('100%');
    }

    public function test_hero_cockpit_and_floating_cards_are_rendered_without_being_hidden_on_mobile(): void
    {
        $response = $this->get(route('landing'));

        $response->assertStatus(200);
        // Top-Right Floating Card (Revenue / Resi) - visible on mobile, scaled on desktop
        $response->assertSee('Total Pendapatan');
        $response->assertSee('Rp 128.4j');
        $response->assertSee('+8.4% bulan ini');
        // Bottom-Left Floating Card (Bisnis Aktif / SKU Sync) - visible on mobile, scaled on desktop
        $response->assertSee('Bisnis Aktif');
        $response->assertSee('12 Unit');
        $response->assertSee('All Online');
        // Cockpit Top Bar & URL
        $response->assertSee('https://cooca.id/app/dashboard', false);
        $response->assertSee('Keuangan');
        $response->assertSee('Marketplace');
        // Slide 1 elements: 4 metrics, Spline chart, Auto-Journal
        $response->assertSee('Total Omset');
        $response->assertSee('Lisensi POS');
        $response->assertSee('Tenant Aktif');
        $response->assertSee('Laba Bersih');
        $response->assertSee('chartGradientHero', false);
        $response->assertSee('Kasir POS #TRX-2049', false);
        $response->assertSee('+Rp 21.600', false);
        // Slide 2 elements: Marketplace & Shipping Hub
        $response->assertSee('Pesanan MP');
        $response->assertSee('Live SKU');
        $response->assertSee('Pick Up');
        $response->assertSee('Hemat Ongkir');
        $response->assertSee('#SHP-8821', false);
        $response->assertSee('Cetak Label Thermal', false);
        $response->assertSee('Resi WA Otomatis', false);

        // Mobile Clean Executive Cockpit assertions
        $response->assertSee('Executive Cockpit', false);
        $response->assertSee('hidden lg:flex pt-0.5 sm:pt-2 w-full justify-start', false);
        $response->assertSee('grid-cols-2 sm:grid-cols-4', false);
        $response->assertSee('h-16 xs:h-20 sm:h-24 lg:h-28', false);
    }
}
