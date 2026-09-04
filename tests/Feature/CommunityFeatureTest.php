<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Storage\OwnerStorageQuotaService;
use App\Models\Business;
use App\Models\CommunityComment;
use App\Models\CommunityLike;
use App\Models\CommunityPost;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommunityFeatureTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();
        Storage::fake('public');

        $this->owner = User::create(['name' => 'Owner Komunitas', 'email' => 'owner@community.test', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Kafe Komunitas']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
    }

    private function makeOtherOwner(): User
    {
        $other = User::create(['name' => 'Owner Lain', 'email' => 'other@community.test', 'password' => 'password']);
        $otherBusiness = Business::create(['name' => 'Warung Lain']);
        $otherBusiness->users()->attach($other->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $other->update(['active_business_id' => $otherBusiness->id]);
        return $other;
    }

    public function test_owner_can_view_community_feed(): void
    {
        $response = $this->actingAs($this->owner)->get(route('community.index'));
        $response->assertStatus(200);
        $response->assertSee('Komunitas Owner');
        $response->assertSee('Kuota Storage');
    }

    public function test_owner_can_create_text_post(): void
    {
        $response = $this->actingAs($this->owner)->post(route('community.store'), [
            'content' => 'Halo sesama owner, tips pemasaran UMKM!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('community_posts', [
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'image_path' => null,
        ]);
    }

    public function test_owner_can_create_post_with_image_and_uses_storage_quota(): void
    {
        $image = UploadedFile::fake()->image('promo.png', 400, 400);
        $quotaService = new OwnerStorageQuotaService();

        $response = $this->actingAs($this->owner)->post(route('community.store'), [
            'content' => 'Promo baru dengan foto!',
            'image' => $image,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $post = CommunityPost::firstOrFail();
        $this->assertNotNull($post->image_path);
        $this->assertStringContainsString("businesses/{$this->business->id}/community", $post->image_path);

        // Uploaded image is counted against owner storage quota.
        Storage::disk('public')->assertExists($post->image_path);
        $this->assertSame((int) $image->getSize(), $quotaService->getUsageBytes($this->owner));
    }

    public function test_other_owner_can_like_and_comment(): void
    {
        $post = CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => 'Postingan untuk dilike',
        ]);

        $other = $this->makeOtherOwner();
        Context::setBusiness($other->businesses()->first());

        // Like toggle on
        $this->actingAs($other)->postJson(route('community.like', $post))
            ->assertOk()
            ->assertJson(['success' => true, 'liked' => true, 'likes_count' => 1]);
        // Unlike toggle off
        $this->actingAs($other)->postJson(route('community.like', $post))
            ->assertOk()
            ->assertJson(['success' => true, 'liked' => false, 'likes_count' => 0]);

        // Comment
        $this->actingAs($other)->postJson(route('community.comment', $post), [
            'content' => 'Mantap, inspiratif sekali!',
        ])->assertOk()
            ->assertJson(['success' => true, 'comments_count' => 1]);

        $this->assertDatabaseHas('community_comments', ['post_id' => $post->id, 'user_id' => $other->id]);
    }

    public function test_owner_cannot_upload_image_when_storage_quota_exceeded(): void
    {
        // Set owner base limit to 0 bytes so any image upload is rejected.
        \App\Models\SystemSetting::set('owner_storage_limit_gb', '0', 'billing');

        $image = UploadedFile::fake()->image('big.png', 800, 800);

        $response = $this->actingAs($this->owner)->post(route('community.store'), [
            'content' => 'Postingan dengan gambar yang tidak bisa diunggah',
            'image' => $image,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('community_posts', ['content' => 'Postingan dengan gambar yang tidak bisa diunggah']);
    }

    public function test_feed_renders_posts_with_images_likes_and_comments(): void
    {
        $post = CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => "Tips UMKM hari ini:\n- Kenali pelanggan\n- Kelola stok",
            'image_path' => "businesses/{$this->business->id}/community/feed.png",
        ]);
        Storage::disk('public')->put($post->image_path, 'image-bytes');

        CommunityLike::create(['post_id' => $post->id, 'user_id' => $this->owner->id]);
        CommunityComment::create(['post_id' => $post->id, 'user_id' => $this->owner->id, 'content' => 'Keren, terima kasih tipsnya!']);

        $response = $this->actingAs($this->owner)->get(route('community.index'));

        $response->assertStatus(200);
        $response->assertSee('Tips UMKM hari ini');
        $response->assertSee('Keren, terima kasih tipsnya!');
        $response->assertSee('Suka');
        $response->assertSee('Komentar');
        $response->assertSee('feed.png');
        // Liked-by-me state is injected into the Alpine post card component (UUID in quotes).
        $response->assertSee("', true, 1, 1)", false);
    }

    public function test_owner_can_delete_own_post_only(): void
    {
        CommunityPost::create([
            'owner_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'content' => 'Postingan saya',
            'image_path' => "businesses/{$this->business->id}/community/dummy.png",
        ]);
        $post = CommunityPost::firstOrFail();
        Storage::disk('public')->put($post->image_path, 'dummy');

        $other = $this->makeOtherOwner();
        Context::setBusiness($other->businesses()->first());

        // Other owner cannot delete someone else's post.
        $this->actingAs($other)->delete(route('community.destroy', $post))->assertStatus(403);
        $this->assertDatabaseHas('community_posts', ['id' => $post->id]);

        // Original owner can delete.
        $this->actingAs($this->owner)->delete(route('community.destroy', $post))->assertStatus(302);
        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);
        Storage::disk('public')->assertMissing($post->image_path);
    }
}