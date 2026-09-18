<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCluster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPostAndClusterTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name'      => 'Super Administrator',
            'email'     => 'admin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_post_index_with_segmented_tabs(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.posts.index'));

        $response->assertOk();
        $response->assertSee('CMS Artikel &amp; Edukasi UMKM', false);
        $response->assertSee('Semua Artikel');
        $response->assertSee('Kategori Post');
        $response->assertSee('Cluster Konten');
    }

    public function test_admin_can_view_create_post_page_with_tinymce(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.posts.create'));

        $response->assertOk();
        $response->assertSee('tinymce.min.js', false);
        $response->assertSee('post-content');
        $response->assertSee('Cluster Konten');
        $response->assertSee('Kategori Baru');
    }

    public function test_admin_can_store_post_with_dual_sync(): void
    {
        $admin = $this->makeAdmin();

        $cluster = PostCluster::firstOrCreate(
            ['code' => 'tutorial'],
            [
                'name' => 'Cluster K - Tutorial',
                'slug' => 'cluster-k-tutorial',
                'is_active' => true,
            ]
        );

        $category = PostCategory::firstOrCreate(
            ['name' => 'HPP & Biaya'],
            [
                'slug' => 'hpp-biaya',
                'is_active' => true,
            ]
        );

        $response = $this->actingAs($admin, 'admin')->post(route('admin.posts.store'), [
            'title' => 'Panduan Menghitung HPP Kafe 2026',
            'cluster_id' => $cluster->id,
            'category_id' => $category->id,
            'excerpt' => 'Ringkasan panduan cara hitung HPP kafe kekinian.',
            'content' => '<p>Langkah pertama dalam menentukan HPP adalah merinci resep.</p>',
            'author_name' => 'Tim Finansial COOCA',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', [
            'title' => 'Panduan Menghitung HPP Kafe 2026',
            'cluster_id' => $cluster->id,
            'cluster' => 'tutorial',
            'category_id' => $category->id,
            'category' => 'HPP & Biaya',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_update_post(): void
    {
        $admin = $this->makeAdmin();

        $post = Post::create([
            'title' => 'Judul Awal',
            'slug' => 'judul-awal',
            'cluster' => 'edukasi',
            'category' => 'Umum',
            'content' => '<p>Isi awal</p>',
            'is_published' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.posts.update', $post), [
            'title' => 'Judul Baru yang Diedit',
            'slug' => 'judul-baru-yang-diedit',
            'cluster' => 'tutorial',
            'category' => 'Operasional',
            'content' => '<p>Isi baru artikel yang lebih lengkap</p>',
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Judul Baru yang Diedit',
            'cluster' => 'tutorial',
            'category' => 'Operasional',
            'is_published' => true,
        ]);
    }

    public function test_admin_can_toggle_post_status(): void
    {
        $admin = $this->makeAdmin();

        $post = Post::create([
            'title' => 'Post Toggle Test',
            'slug' => 'post-toggle-test',
            'cluster' => 'tutorial',
            'category' => 'Testing',
            'content' => '<p>Konten</p>',
            'is_published' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.posts.toggle-status', $post));

        $response->assertRedirect();
        $this->assertTrue($post->fresh()->is_published);
    }

    public function test_admin_can_create_category_via_ajax_and_form(): void
    {
        $admin = $this->makeAdmin();

        // AJAX creation (quick-add modal)
        $ajaxResponse = $this->actingAs($admin, 'admin')->postJson(route('admin.posts.categories.store'), [
            'name' => 'Pemasaran Digital',
            'description' => 'Strategi marketing online',
        ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJsonFragment([
            'success' => true,
            'name' => 'Pemasaran Digital',
        ]);
        $this->assertDatabaseHas('post_categories', ['name' => 'Pemasaran Digital']);

        // Form update
        $category = PostCategory::where('name', 'Pemasaran Digital')->first();
        $updateResponse = $this->actingAs($admin, 'admin')->put(route('admin.posts.categories.update', $category), [
            'name' => 'Pemasaran & Iklan',
            'slug' => 'pemasaran-dan-iklan',
            'description' => 'Strategi marketing dan iklan berbayar',
            'icon' => 'megaphone',
            'is_active' => '1',
            'sort_order' => 5,
        ]);

        $updateResponse->assertRedirect(route('admin.posts.index', ['tab' => 'categories']));
        $this->assertDatabaseHas('post_categories', [
            'id' => $category->id,
            'name' => 'Pemasaran & Iklan',
            'sort_order' => 5,
        ]);

        // Delete
        $deleteResponse = $this->actingAs($admin, 'admin')->delete(route('admin.posts.categories.destroy', $category));
        $deleteResponse->assertRedirect(route('admin.posts.index', ['tab' => 'categories']));
        $this->assertDatabaseMissing('post_categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_and_manage_cluster(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.posts.clusters.store'), [
            'code' => 'studi-kasus',
            'name' => 'Studi Kasus UMKM',
            'description' => 'Kisah sukses dan bedah kasus UMKM',
            'icon' => 'briefcase',
        ]);

        $response->assertRedirect(route('admin.posts.index', ['tab' => 'clusters']));
        $this->assertDatabaseHas('post_clusters', ['code' => 'studi-kasus']);

        $cluster = PostCluster::where('code', 'studi-kasus')->first();
        $updateResponse = $this->actingAs($admin, 'admin')->put(route('admin.posts.clusters.update', $cluster), [
            'code' => 'studi-kasus',
            'name' => 'Studi Kasus & Analisis UMKM',
            'slug' => 'studi-kasus-analisis-umkm',
            'description' => 'Kisah sukses dan bedah kasus mendalam',
            'icon' => 'award',
            'is_active' => '1',
            'sort_order' => 10,
        ]);

        $updateResponse->assertRedirect(route('admin.posts.index', ['tab' => 'clusters']));
        $this->assertDatabaseHas('post_clusters', [
            'id' => $cluster->id,
            'name' => 'Studi Kasus & Analisis UMKM',
        ]);

        $deleteResponse = $this->actingAs($admin, 'admin')->delete(route('admin.posts.clusters.destroy', $cluster));
        $deleteResponse->assertRedirect(route('admin.posts.index', ['tab' => 'clusters']));
        $this->assertDatabaseMissing('post_clusters', ['id' => $cluster->id]);
    }

    public function test_no_unicode_emojis_in_posts_views(): void
    {
        $views = [
            resource_path('views/admin/posts/index.blade.php'),
            resource_path('views/admin/posts/create.blade.php'),
            resource_path('views/admin/posts/edit.blade.php'),
        ];

        foreach ($views as $viewPath) {
            $this->assertFileExists($viewPath);
            $content = file_get_contents($viewPath);
            $hasEmoji = preg_match('/[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content);
            $this->assertEquals(0, $hasEmoji, "Emoji found in view file: {$viewPath}");
        }
    }
}
