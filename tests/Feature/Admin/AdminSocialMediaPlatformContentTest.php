<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\SocialMediaComment;
use App\Models\SocialMediaPost;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSocialMediaPlatformContentTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): Admin
    {
        return Admin::factory()->create([
            'name'      => 'Super Administrator',
            'email'     => 'superadmin@cooca.id',
            'password'  => Hash::make('password123'),
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_social_media_index_with_platform_posts_tab(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.social-media.index', ['tab' => 'posts']));

        $response->assertOk();
        $response->assertSee('Media Sosial Platform Admin Center');
        $response->assertSee('Kelola Konten Platform');
        $response->assertSee('Kotak Masuk Interaksi');
        $response->assertSee('Buat Postingan Baru');
    }

    public function test_admin_can_create_and_publish_platform_post_to_instagram(): void
    {
        $admin = $this->makeAdmin();
        Storage::fake('public');

        SystemSetting::set('instagram_account_id', '17841400000000000', 'social_media');
        SystemSetting::set('instagram_access_token', 'IGAA_mock_token_secret', 'social_media', true);

        Http::fake([
            'https://graph.instagram.com/v21.0/17841400000000000/media' => Http::response([
                'id' => 'container_creation_id_123',
            ], 200),
            'https://graph.instagram.com/v21.0/17841400000000000/media_publish' => Http::response([
                'id' => 'published_post_id_99999',
            ], 200),
        ]);

        $payload = [
            'platform'   => 'instagram',
            'content'    => 'Pengumuman Resmi Platform COOCA: Fitur POS Multi-Cabang Telah Rilis! #CoocaERP #UMKMIndonesia',
            'media_url'  => 'https://images.unsplash.com/photo-1556742049-0a67c5574f73',
            'media_type' => 'image',
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('admin.social-media.posts.store'), $payload);

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('social_media_posts', [
            'admin_id'         => $admin->id,
            'is_platform'      => true,
            'platform'         => 'instagram',
            'status'           => 'published',
            'platform_post_id' => 'published_post_id_99999',
        ]);
    }

    public function test_admin_can_create_post_with_uploaded_media_file(): void
    {
        $admin = $this->makeAdmin();
        Storage::fake('public');

        SystemSetting::set('instagram_account_id', '17841400000000000', 'social_media');
        SystemSetting::set('instagram_access_token', 'IGAA_mock_token_secret', 'social_media', true);

        Http::fake([
            'https://graph.instagram.com/v21.0/17841400000000000/media' => Http::response([
                'id' => 'container_creation_id_456',
            ], 200),
            'https://graph.instagram.com/v21.0/17841400000000000/media_publish' => Http::response([
                'id' => 'published_post_id_88888',
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('promo.jpg', 800, 800);

        $payload = [
            'platform'   => 'instagram',
            'content'    => 'Postingan dengan berkas unggahan langsung oleh Admin Cooca.',
            'media_file' => $file,
        ];

        $response = $this->actingAs($admin, 'admin')->post(route('admin.social-media.posts.store'), $payload);

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('social_media_posts', [
            'admin_id'    => $admin->id,
            'is_platform' => true,
            'platform'    => 'instagram',
            'status'      => 'published',
        ]);
    }

    public function test_admin_can_retry_failed_platform_post(): void
    {
        $admin = $this->makeAdmin();

        $post = SocialMediaPost::create([
            'admin_id'      => $admin->id,
            'business_id'   => null,
            'is_platform'   => true,
            'platform'      => 'instagram',
            'content'       => 'Postingan yang awalnya gagal dikirim.',
            'media_urls'    => ['https://images.unsplash.com/photo-1556742049-0a67c5574f73'],
            'media_type'    => 'image',
            'status'        => 'failed',
            'error_message' => 'Token expired',
        ]);

        SystemSetting::set('instagram_account_id', '17841400000000000', 'social_media');
        SystemSetting::set('instagram_access_token', 'IGAA_mock_token_secret', 'social_media', true);

        Http::fake([
            'https://graph.instagram.com/v21.0/17841400000000000/media' => Http::response([
                'id' => 'container_creation_id_retry',
            ], 200),
            'https://graph.instagram.com/v21.0/17841400000000000/media_publish' => Http::response([
                'id' => 'published_post_id_retry_success',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.social-media.posts.retry', $post));

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $post->refresh();
        $this->assertEquals('published', $post->status);
        $this->assertEquals('published_post_id_retry_success', $post->platform_post_id);
    }

    public function test_admin_can_delete_platform_post(): void
    {
        $admin = $this->makeAdmin();

        $post = SocialMediaPost::create([
            'admin_id'    => $admin->id,
            'business_id' => null,
            'is_platform' => true,
            'platform'    => 'instagram',
            'content'     => 'Postingan uji coba untuk dihapus.',
            'status'      => 'published',
        ]);

        $response = $this->actingAs($admin, 'admin')->delete(route('admin.social-media.posts.destroy', $post));

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'posts']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('social_media_posts', [
            'id' => $post->id,
        ]);
    }

    public function test_admin_can_reply_to_platform_comment(): void
    {
        $admin = $this->makeAdmin();

        $post = SocialMediaPost::create([
            'admin_id'    => $admin->id,
            'business_id' => null,
            'is_platform' => true,
            'platform'    => 'instagram',
            'content'     => 'Konten platform dengan komentar.',
            'status'      => 'published',
        ]);

        $comment = SocialMediaComment::create([
            'business_id'         => null,
            'is_platform'         => true,
            'social_media_post_id'=> $post->id,
            'platform'            => 'instagram',
            'platform_comment_id' => 'ig_comment_12345',
            'platform_post_id'    => 'ig_post_999',
            'from_name'           => 'Budi Santoso',
            'message'             => 'Apakah Cooca support untuk multi-outlet bengkel?',
            'status'              => 'unread',
        ]);

        SystemSetting::set('instagram_access_token', 'IGAA_mock_token_secret', 'social_media', true);

        Http::fake([
            'https://graph.instagram.com/v21.0/ig_comment_12345/replies' => Http::response([
                'id' => 'ig_reply_id_77777',
            ], 200),
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.social-media.comments.reply', $comment), [
            'message' => 'Halo Kak Budi, betul sekali! Cooca mendukung manajemen inventori dan kasir multi-outlet.',
        ]);

        $response->assertRedirect(route('admin.social-media.index', ['tab' => 'inbox']));
        $response->assertSessionHas('success');

        $comment->refresh();
        $this->assertEquals('replied', $comment->status);

        $this->assertDatabaseHas('social_media_comments', [
            'parent_comment_id'   => 'ig_comment_12345',
            'platform_comment_id' => 'ig_reply_id_77777',
            'is_platform'         => true,
        ]);
    }
}
