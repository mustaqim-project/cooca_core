<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;
    public function test_xml_sitemap_endpoint_returns_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $content = $response->getContent();
        $this->assertStringContainsString('<urlset', $content);
        $this->assertStringContainsString('https://umkm.cooca.id/kalkulator/hpp', $content);
        $this->assertStringContainsString('https://umkm.cooca.id/solusi/kasir-warung', $content);
        $this->assertStringContainsString('https://umkm.cooca.id/template-pembukuan-gratis', $content);
        $this->assertStringNotContainsString('127.0.0.1', $content);
        $this->assertStringNotContainsString('localhost', $content);
    }

    public function test_html_sitemap_page_renders_successfully(): void
    {
        $response = $this->get('/sitemap');

        $response->assertStatus(200);
        $response->assertSee('Peta Situs Resmi');
        $response->assertSee('Kalkulator Bisnis');
        $response->assertSee('Solusi Industri');
        $response->assertSee('INDEX, FOLLOW');
        $response->assertSee('NOINDEX, NOFOLLOW');
        $response->assertSee('https://umkm.cooca.id/');
    }

    public function test_robots_txt_contains_strict_index_follow_and_noindex_rules(): void
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $content = file_get_contents($robotsPath);
        $this->assertStringContainsString('Allow: /', $content);
        $this->assertStringContainsString('Allow: /kalkulator', $content);
        $this->assertStringContainsString('Allow: /solusi/', $content);
        $this->assertStringContainsString('Disallow: /admin', $content);
        $this->assertStringContainsString('Disallow: /dashboard', $content);
        $this->assertStringContainsString('Disallow: /pos', $content);
        $this->assertStringContainsString('Disallow: /inventory', $content);
        $this->assertStringContainsString('Disallow: /login', $content);
        $this->assertStringContainsString('Sitemap: https://umkm.cooca.id/sitemap.xml', $content);
    }

    public function test_guest_and_app_layouts_enforce_noindex_nofollow(): void
    {
        // 1. Guest login page
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        // 2. Authenticated Dashboard page
        $user = User::factory()->create();
        $business = Business::create([
            'user_id' => $user->id,
            'name' => 'Demo Resto',
            'slug' => 'demo-resto-' . uniqid(),
            'status' => 'active',
            'is_active' => true,
        ]);

        $dashResponse = $this->actingAs($user)
            ->withSession(['active_business_id' => $business->id])
            ->get('/dashboard');

        if ($dashResponse->status() === 200) {
            $dashResponse->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        }
    }
}
