<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\BugReport;
use App\Models\Business;
use App\Models\FeatureRequest;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class FeedbackWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::create(['name' => 'Business Owner', 'email' => 'owner@feedback.test', 'password' => 'password']);
        $this->business = Business::create(['name' => 'Feedback Business']);
        $this->business->users()->attach($this->owner->id, ['id' => (string) Str::uuid(), 'role' => 'owner']);
        $this->owner->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);
        $this->admin = Admin::create(['name' => 'Feedback Admin', 'email' => 'admin@feedback.test', 'password' => Hash::make('password'), 'role' => 'super_admin', 'is_active' => true]);
    }

    public function test_owner_can_submit_bug_and_admin_can_track_progress(): void
    {
        $response = $this->actingAs($this->owner)->post(route('feedback.bugs.store'), [
            'title' => 'Stok tidak tersimpan', 'category' => 'bug', 'severity' => 'high',
            'description' => 'Perubahan stok hilang setelah refresh.', 'steps_to_reproduce' => 'Buka stok lalu simpan.',
            'expected_behavior' => 'Stok tersimpan.', 'actual_behavior' => 'Stok kembali ke nilai lama.', 'environment' => 'Chrome Windows',
        ]);
        $bug = BugReport::firstOrFail();
        $response->assertRedirect(route('feedback.bugs.show', $bug));
        $this->assertDatabaseHas('feedback_updates', ['trackable_id' => $bug->id, 'status' => 'open']);

        $response = $this->actingAs($this->admin, 'admin')->patch(route('admin.feedback.bugs.update', $bug), [
            'status' => 'in_progress', 'priority' => 'high', 'progress_percent' => 60,
            'assigned_admin_id' => $this->admin->id, 'comment' => 'Sedang direproduksi oleh tim engineering.', 'resolution' => '',
        ]);
        $response->assertSessionHas('success');
        $bug->refresh();
        $this->assertSame('in_progress', $bug->status);
        $this->assertSame(60, $bug->progress_percent);
        $this->assertCount(2, $bug->updates()->get());
    }

    public function test_owner_can_submit_feature_request_and_admin_can_release_it(): void
    {
        $response = $this->actingAs($this->owner)->post(route('feedback.features.store'), [
            'title' => 'Export laporan ke Excel', 'category' => 'reporting', 'priority' => 'normal',
            'description' => 'Owner membutuhkan export laporan.', 'business_value' => 'Mempercepat rekonsiliasi.',
            'use_case' => 'Owner memilih periode lalu mengunduh file.', 'proposed_solution' => 'Tambahkan tombol export.',
        ]);
        $feature = FeatureRequest::firstOrFail();
        $response->assertRedirect(route('feedback.features.show', $feature));

        $this->actingAs($this->admin, 'admin')->patch(route('admin.feedback.features.update', $feature), [
            'status' => 'released', 'priority' => 'high', 'progress_percent' => 100,
            'assigned_admin_id' => $this->admin->id, 'comment' => 'Fitur sudah dirilis.', 'admin_notes' => 'Tersedia mulai versi berikutnya.',
        ])->assertSessionHas('success');
        $feature->refresh();
        $this->assertSame('released', $feature->status);
        $this->assertNotNull($feature->released_at);
        $this->assertCount(2, $feature->updates()->get());
    }
}
