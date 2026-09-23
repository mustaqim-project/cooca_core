<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Post;
use App\Models\TemplateLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicViewsProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders_and_submission_persists_inquiry(): void
    {
        $response = $this->get('/kontak');
        $response->assertOk();
        $response->assertSee('Hubungi Tim Kami');

        $submitResponse = $this->post('/kontak', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'subject' => 'Pertanyaan Kemitraan Cooca',
            'message' => 'Halo tim Cooca, saya ingin bermitra untuk 5 cabang warung kopi.',
        ]);

        $submitResponse->assertSessionHas('success_message');
        $this->assertDatabaseHas('template_leads', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'template_slug' => 'contact-inquiry',
        ]);
    }

    public function test_all_nine_business_calculators_render_successfully(): void
    {
        $calculators = [
            '/kalkulator',
            '/kalkulator/hpp',
            '/kalkulator/bep',
            '/kalkulator/harga-jual',
            '/kalkulator/laba-bersih',
            '/kalkulator/gaji-karyawan',
            '/kalkulator/pph-final',
            '/kalkulator/omzet-harian',
            '/kalkulator/simulasi-what-if',
        ];

        foreach ($calculators as $url) {
            $response = $this->get($url);
            $response->assertOk();
        }
    }

    public function test_solutions_page_renders_with_dynamic_data(): void
    {
        $response = $this->get('/solusi/kasir-warung');
        $response->assertOk();
        $response->assertSee('Toko Kelontong');
    }

    public function test_templates_index_and_lead_capture_work(): void
    {
        $indexResponse = $this->get('/template-pembukuan-gratis');
        $indexResponse->assertOk();

        $showResponse = $this->get('/template/pembukuan-warung-excel');
        $showResponse->assertOk();

        $captureResponse = $this->postJson('/template/pembukuan-warung-excel/download', [
            'name' => 'Siti Rahma',
            'phone' => '08987654321',
            'business_name' => 'Warung Siti Berkah',
            'email' => 'siti@example.com',
        ]);

        $captureResponse->assertOk();
        $captureResponse->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('template_leads', [
            'name' => 'Siti Rahma',
            'template_slug' => 'pembukuan-warung-excel',
        ]);
    }

    public function test_blog_index_and_article_detail_render_properly(): void
    {
        $post = Post::create([
            'title' => 'Cara Menghitung HPP Warung Makan',
            'slug' => 'cara-menghitung-hpp-warung-makan',
            'cluster' => 'tutorial',
            'category' => 'Finansial UMKM',
            'excerpt' => 'Panduan lengkap cara menghitung HPP warung makan...',
            'content' => '<h2>1. Menghitung Bahan Baku</h2><p>Langkah pertama dalam menghitung HPP adalah mencatat bahan baku...</p><h2>2. Biaya Operasional</h2><p>Langkah kedua...</p>',
            'author_name' => 'Tim Finansial Cooca',
            'read_time' => 5,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $relatedPost = Post::create([
            'title' => 'Strategi Menentukan Margin Keuntungan',
            'slug' => 'strategi-menentukan-margin-keuntungan',
            'cluster' => 'tutorial',
            'category' => 'Finansial UMKM',
            'excerpt' => 'Menentukan margin keuntungan yang sehat...',
            'content' => '<p>Tips margin untuk warung dan UMKM...</p>',
            'author_name' => 'Tim Finansial Cooca',
            'read_time' => 4,
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $indexResponse = $this->get('/blog');
        $indexResponse->assertOk();
        $indexResponse->assertSee('Panduan Praktis');

        $showResponse = $this->get('/blog/' . $post->slug);
        $showResponse->assertOk();
        $showResponse->assertSee($post->title);
        $showResponse->assertSee('Daftar Isi Artikel');
        $showResponse->assertSee('Tanya Gratis via WhatsApp');
        $showResponse->assertSee('Artikel Lain yang Sebaiknya Anda Baca');
        $showResponse->assertSee($relatedPost->title);

        // Zero-emoji verification on blog show
        $content = $showResponse->getContent();
        $hasEmoji = preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content);
        $this->assertEquals(0, $hasEmoji, 'Blog article show page must have zero emoji as per Apple HIG guidelines');
    }

    public function test_all_four_public_resource_pages_render_without_syntax_errors(): void
    {
        $pages = [
            '/resources/blog' => 'Kembangkan Usaha Anda dengan Wawasan Finansial',
            '/resources/case-studies' => 'Kisah Nyata UMKM yang Berhasil Menutup Kebocoran Kasir',
            '/resources/faq' => 'Semua Jawaban Jelas untuk Menjalankan Usaha',
            '/resources/guides' => 'Langkah Praktis Membangun Operasional Gerai yang Rapi',
        ];

        foreach ($pages as $url => $headlineSnippet) {
            $response = $this->get($url);
            $response->assertOk();
            $response->assertSee($headlineSnippet);

            // Verify zero-emoji across all resource pages
            $content = $response->getContent();
            $hasEmoji = preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content);
            $this->assertEquals(0, $hasEmoji, "Page {$url} must have zero emoji as per Apple HIG guidelines");
        }
    }

    public function test_pricing_page_renders_with_bento_apple_hig_and_zero_emoji(): void
    {
        $response = $this->get('/pricing');
        $response->assertOk();

        // Check key copy elements for UMKM 40-65 y.o.
        $response->assertSee('Investasi Jujur');
        $response->assertSee('Tanpa Biaya Pasang');
        $response->assertSee('Bebas Ikatan Kontrak');
        $response->assertSee('Bandingkan Semua Fitur');
        $response->assertSee('Standard');
        $response->assertSee('Premium');
        $response->assertSee('Prestige');

        // Verify zero-emoji in pricing view
        $content = $response->getContent();
        // Common emoji regex range
        $hasEmoji = preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content);
        $this->assertEquals(0, $hasEmoji, 'Pricing page must have zero emoji as per Apple HIG guidelines');
    }
}
